<?php


namespace app\modules\doctor\controllers;

/*
 * Контроллер для записи на прием пациентов,
 * первый этап запись в кабинет вакцинации COVID-19
 *
 * */

use app\modules\doctor\models\Action;
use app\modules\doctor\models\ActionProperty;
use app\modules\doctor\models\ActionProperty_Action;
use app\modules\doctor\models\Client;
use app\modules\doctor\models\Event;
use Yii;

class AppointmentController extends AppDoctorController
{
    public function actionDoctorList($client_id){
        $session = Yii::$app->session;
        $session->open();
        $session->set('client_id', $client_id);

        $query = "
         SELECT
      e.id as 'EventId',
      a.id AS 'actionId',
      IFNULL(apqa1.value, '')  AS 'apqaValue',
      apqa1.index AS 'apqaIndex',
      o.bookkeeperCode AS 'code_structure', -- Код структурного
      o.name AS 'otdelenie',
      p.id AS 'id_doctor',  -- id врача
      CONCAT_WS(' ', p.lastName, p.firstName, p.patrName) AS 'doctor',  -- Врач
      s.id AS 'spec_id',                -- id специальности
      s.name AS 'specialty',            -- Специальность

      DATE_FORMAT(e.setDate, '%d %M %Y, %W') AS 'date_priem',  -- дата приема
      TIME_FORMAT(aptime.value, '%H: %i') AS 'time_priem',    -- время приема
      COUNT(p.id) AS 'count_num',
      a1.note AS 'примечание'
      FROM Person p
      LEFT JOIN rbSpeciality s ON p.speciality_id = s.id
      LEFT JOIN OrgStructure o ON p.orgStructure_id = o.id

      LEFT JOIN Event e ON e.setPerson_id = p.id
      LEFT JOIN EventType et ON et.id = e.eventType_id
      LEFT JOIN rbmedicalaidtype r ON et.medicalAidType_id = r.id
      LEFT JOIN Action a ON a.event_id = e.id
      LEFT JOIN ActionType atp ON atp.id = a.actionType_id
      --
      LEFT JOIN ActionPropertyType aptq1 ON aptq1.actionType_id = a.actionType_id AND aptq1.name='queue'
      LEFT JOIN ActionProperty apq1 ON apq1.action_id = a.id AND apq1.type_id = aptq1.id AND apq1.deleted=0
      LEFT JOIN ActionProperty_Action apqa1 ON apqa1.id = apq1.id AND apqa1.index IS NOT NULL
      LEFT JOIN Action a1 ON apqa1.value = a1.id
      --
      LEFT JOIN ActionPropertyType aptype_times ON aptype_times.actionType_id=atp.id AND aptype_times.name LIKE 'times'
      LEFT JOIN ActionProperty ap_times ON ap_times.action_id=a.id AND ap_times.type_id=aptype_times.id AND ap_times.deleted=0
      LEFT JOIN ActionProperty_Time aptime ON aptime.id=ap_times.id AND aptime.index=apqa1.index

      LEFT JOIN ActionProperty APESS ON APESS.action_id = a.id AND APESS.type_id = (select id from ActionPropertyType where name  = 'notExternalSystems')
      LEFT JOIN ActionProperty_Integer APESSI ON APESS.id = APESSI.id AND APESSI.index  = aptime.index
      WHERE
     --     p.availableForExternal = 1
           NOT ISNULL(s.id)
          AND e.deleted = 0
          AND a.deleted = 0
          AND et.code = '0'
          AND atp.code = 'amb'
          AND aptime.value IS NOT NULL  -- есть планируемое время номерка
          AND e.setDate >= CURDATE()
          AND e.setDate <= IF(p.lastAccessibleTimelineDate OR p.timelineAccessibleDays,
                     IF(p.lastAccessibleTimelineDate,
                      p.lastAccessibleTimelineDate,
                        IF(p.timelineAccessibleDays,
                            ADDDATE(CURRENT_DATE(), INTERVAL (p.timelineAccessibleDays) DAY),
                            ADDDATE(CURRENT_DATE(), INTERVAL (14) DAY))),
                      ADDDATE(CURRENT_DATE(), INTERVAL (14) DAY)) -- ограничение кол-ва видимых дней расписания врача

          AND (p.lastAccessibleTimelineDate IS NULL OR p.lastAccessibleTimelineDate = '0000-00-00' OR DATE(e.setDate)<=p.lastAccessibleTimelineDate)
          AND (p.timelineAccessibleDays IS NULL OR p.timelineAccessibleDays <= 0 OR DATE(e.setDate)<=ADDDATE(CURRENT_DATE(), p.timelineAccessibleDays))
          AND e.id NOT IN (SELECT Event.id FROM Event
                    LEFT JOIN Action ON Action.event_id = Event.id
                    INNER JOIN ActionProperty ON ActionProperty.action_id = Action.id
                    INNER JOIN ActionProperty_rbReasonOfAbsence ON ActionProperty_rbReasonOfAbsence.id = ActionProperty.id
                    LEFT JOIN ActionType ON ActionType.id = Action.actionType_id
                   WHERE ActionType.code = 'timeLine') -- убираем Events в которых есть причина отсутствия сотрудника
    --    AND e.setDate = CURDATE()  -- Ограничиваем по дате = нужна процедура!!!
        AND o.name LIKE '%COVID%' -- Ограничиваем по подразделению

           AND IFNULL(apqa1.value, '') = ''
      GROUP BY doctor
      ORDER BY setDate, apqaIndex, specialty ASC
        ";

        $resoults = Client::findbysql($query)->asArray()->all();

        return $this->render('__formdoctor',compact('resoults'));
    } // получить список доступных врачей


    public function actionDateTime($idSpeciality,$id_doctor){
        $query = "
         SELECT
      e.id as 'EventId',
      a.id AS 'actionId',
      apq1.id AS 'ActionProperty',
      IFNULL(apqa1.value, '')  AS 'apqaValue',
      apqa1.index AS 'apqaIndex',
      o.bookkeeperCode AS 'код структурного', -- Код структурного
      o.name AS 'otdelenie',
      p.id AS 'id_doctor',  -- id врача
      CONCAT_WS(' ', p.lastName, p.patrName, p.firstName) AS 'doctor',  -- Врач
      s.id AS 'spec_id',                -- id специальности
      s.name AS 'specialty',            -- Специальность
      DATE_FORMAT(e.setDate, '%Y-%m-%d') AS 'setDate',
      DATE_FORMAT(e.setDate, '%d.%m.%Y') AS 'date_priem',  -- дата приема
      TIME_FORMAT(aptime.value, '%H:%i') AS 'time_priem',    -- время приема
      o.organisation_id AS 'orgstructure',
      p.office AS 'cabinet',
      a1.note AS 'примечание'
      FROM Person p
      LEFT JOIN rbSpeciality s ON p.speciality_id = s.id
      LEFT JOIN OrgStructure o ON p.orgStructure_id = o.id

      LEFT JOIN Event e ON e.setPerson_id = p.id
      LEFT JOIN EventType et ON et.id = e.eventType_id
      LEFT JOIN rbmedicalaidtype r ON et.medicalAidType_id = r.id
      LEFT JOIN Action a ON a.event_id = e.id
      LEFT JOIN ActionType atp ON atp.id = a.actionType_id
      --
      LEFT JOIN ActionPropertyType aptq1 ON aptq1.actionType_id = a.actionType_id AND aptq1.name='queue'
      LEFT JOIN ActionProperty apq1 ON apq1.action_id = a.id AND apq1.type_id = aptq1.id AND apq1.deleted=0
      LEFT JOIN ActionProperty_Action apqa1 ON apqa1.id = apq1.id AND apqa1.index IS NOT NULL
      LEFT JOIN Action a1 ON apqa1.value = a1.id
      --
      LEFT JOIN ActionPropertyType aptype_times ON aptype_times.actionType_id=atp.id AND aptype_times.name LIKE 'times'
      LEFT JOIN ActionProperty ap_times ON ap_times.action_id=a.id AND ap_times.type_id=aptype_times.id AND ap_times.deleted=0
      LEFT JOIN ActionProperty_Time aptime ON aptime.id=ap_times.id AND aptime.index=apqa1.index

      LEFT JOIN ActionProperty APESS ON APESS.action_id = a.id AND APESS.type_id = (select id from ActionPropertyType where name  = 'notExternalSystems')
      LEFT JOIN ActionProperty_Integer APESSI ON APESS.id = APESSI.id AND APESSI.index  = aptime.index
      WHERE
     --     p.availableForExternal = 1
           NOT ISNULL(s.id)
          AND e.deleted = 0
          AND a.deleted = 0
          AND et.code = '0'
          AND atp.code = 'amb'
          AND aptime.value IS NOT NULL  -- есть планируемое время номерка
          AND e.setDate >= CURDATE()
          AND e.setDate <= IF(p.lastAccessibleTimelineDate OR p.timelineAccessibleDays,
                     IF(p.lastAccessibleTimelineDate,
                      p.lastAccessibleTimelineDate,
                        IF(p.timelineAccessibleDays,
                            ADDDATE(CURRENT_DATE(), INTERVAL (p.timelineAccessibleDays) DAY),
                            ADDDATE(CURRENT_DATE(), INTERVAL (14) DAY))),
                      ADDDATE(CURRENT_DATE(), INTERVAL (14) DAY)) -- ограничение кол-ва видимых дней расписания врача

          AND (p.lastAccessibleTimelineDate IS NULL OR p.lastAccessibleTimelineDate = '0000-00-00' OR DATE(e.setDate)<=p.lastAccessibleTimelineDate)
          AND (p.timelineAccessibleDays IS NULL OR p.timelineAccessibleDays <= 0 OR DATE(e.setDate)<=ADDDATE(CURRENT_DATE(), p.timelineAccessibleDays))
          AND e.id NOT IN (SELECT Event.id FROM Event
                    LEFT JOIN Action ON Action.event_id = Event.id
                    INNER JOIN ActionProperty ON ActionProperty.action_id = Action.id
                    INNER JOIN ActionProperty_rbReasonOfAbsence ON ActionProperty_rbReasonOfAbsence.id = ActionProperty.id
                    LEFT JOIN ActionType ON ActionType.id = Action.actionType_id
                   WHERE ActionType.code = 'timeLine') -- убираем Events в которых есть причина отсутствия сотрудника
        AND s.id = $idSpeciality  -- Ограничиваем по специальности = нужна процедура!!!
    --    AND e.setDate = CURDATE()  -- Ограничиваем по дате = нужна процедура!!!
            AND o.name LIKE '%COVID%' -- Ограничиваем по подразделению
  AND p.id = $id_doctor

           AND IFNULL(apqa1.value, '') = ''
      --      GROUP BY doctor
      ORDER BY setDate, apqaIndex, specialty ASC
        ";


        $resoults = Client::findbysql($query)->asArray()->all();






        return $this->render('__formdatetime',compact('resoults'));
    } // получить список достпных дат и времени

    public function actionSetAppointmentLocalCovid(
        $orgstructure_id, // id головной организации
        $actionProperty_id, // созданная запись - график
        $apqaIndex, // порядковый номер талона
        $doctor_id, // id врача, тот на которого расписание
        $cabinet,  // кабинет врача
        $setDate // Дата приема
    ){
        $session = Yii::$app->session;
        $client_id = $session->get('client_id');
        $session->close();
/*
 * Это код методами yii2, почему то не отрабатыет функция Yii::$app->db->getLastInsertID();
 * почему пока не известно
        $event = new Event();
        $event->createDatetime = date("Y-m-d H:i:s"); // текущая дата время
        $event->createPerson_id = \Yii::$app->user->identity->id;  // id пользователя программы
        $event->modifyDatetime = date("Y-m-d H:i:s"); // текущая дата время
        $event->modifyPerson_id = \Yii::$app->user->identity->id;
        $event->eventType_id = 29;
        $event->org_id = $orgstructure_id;
        $event->client_id = $client_id;
        $event->setDate = $setDate;
        $event->isPrimary = 1;
        if($event->save()){
            $action = new Action();
            $action->createDatetime = date("Y-m-d H:i:s");
            $action->createPerson_id = \Yii::$app->user->identity->id;
            $action->modifyDatetime = date("Y-m-d H:i:s");
            $action->modifyPerson_id = \Yii::$app->user->identity->id;
            $action->actionType_id = 19;
//            $action->event_id = Yii::$app->db->getLastInsertID();
            $action->event_id = $event->id;
            $action->directionDate = date("Y-m-d H:i:s");
            $action->status = 1;
            $action->setPerson_id = NULL;
//            $action->note = 'Неотложка планшеты';
            $action->note = 'callcenter';
            $action->person_id = $doctor_id;
            $action->office = $cabinet;
            $action->sourceId = 1;
            if($action->save()){
                $actionProperty_Action = new ActionProperty_Action();
                $actionProperty_Action->id = $actionProperty_id;
                $actionProperty_Action->index = $apqaIndex;
//                $actionProperty_Action->value = Yii::$app->db->getLastInsertID();
                $actionProperty_Action->value = $action->id;
                if($actionProperty_Action->save()){
                    \Yii::$app->session->setFlash('addEvent',"<strong>Успешно!</strong> Пациент записан, номер записи- <strong>$event->id</strong>!");

                }
            }
        }
*/

        $curdate = date("Y-m-d H:i:s");// текущая дата время
        $personid = \Yii::$app->user->identity->id;  // id пользователя программы

        $queryevent = "
            /*создаем event*/
        INSERT INTO Event (`createDatetime`, `createPerson_id`, `modifyDatetime`, `modifyPerson_id`, `eventType_id`, `org_id`, `client_id`, `setDate`, `isPrimary`)
        VALUES ('$curdate', '$personid', '$curdate', '$personid', 29, '$orgstructure_id', '$client_id', '$setDate', 1);
        ";
        $resoultsevent = Yii::$app->db->createCommand($queryevent)->execute();


        $queryid = "SELECT LAST_INSERT_ID()";
        $idevent = Yii::$app->db->createCommand($queryid)->queryOne();
        $idevent = $idevent['LAST_INSERT_ID()'];
        $queryaction = "
        /*создаем action*/
        INSERT INTO Action (`createDatetime`, `createPerson_id`, `modifyDatetime`, `modifyPerson_id`, `actionType_id`, `event_id`, `directionDate`, `status`,
`setPerson_id`, `note`, `person_id`, `office`, `sourceId`)
        VALUES ('$curdate', $personid, '$curdate', $personid, 19, $idevent, '$setDate', 1, $personid, 'callcenter', $doctor_id, '$cabinet', 1);
        ";
        $resoultsaction = Yii::$app->db->createCommand($queryaction)->execute();
        $idaction = Yii::$app->db->createCommand($queryid)->queryOne();
        $idaction = $idaction['LAST_INSERT_ID()'];

        $queryproperty = "
        INSERT INTO ActionProperty_Action (`id`, `index`, `value`)
  VALUES
  ($actionProperty_id, $apqaIndex, $idaction)

  ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
        ";
        $resoultsproperty = Yii::$app->db->createCommand($queryproperty)->execute();
        if ($resoultsproperty == true){
            $date = Yii::$app->formatter->asDate($setDate,'php:d.m.Y');
            $time = Yii::$app->formatter->asDate($setDate,'php:H:i');
            \Yii::$app->session->setFlash('addEvent',"<strong>Успешно!</strong> Пациент записан на вакцинацию <strong>$date</strong> на <strong>$time</strong>!");
            return $this->goHome();
        }








    } // подтверждение записи на дом локально

}