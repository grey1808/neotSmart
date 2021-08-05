<?php
/**
 * Created by PhpStorm.
 * User: Belya
 * Date: 16.04.2019
 * Time: 15:36
 */

namespace app\modules\doctor\controllers;
use app\modules\doctor\models\ActionProperty;
use app\modules\doctor\models\ActionProperty_String;
use app\modules\doctor\models\FormListSearch;
use app\modules\doctor\models\Smpevents;
use yii\web\NotFoundHttpException;
use app\modules\doctor\models\ActionType;
use app\modules\doctor\models\Client;
use app\modules\doctor\models\SearchMonitor;
use app\modules\doctor\models\Action;
use app\modules\doctor\models\Client_tmp;
use app\modules\doctor\models\Diagnosis;
use app\modules\doctor\models\Diagnostic;
use app\modules\doctor\models\Event;
use app\modules\doctor\models\Eventtype_action;
use app\modules\doctor\models\Mkb_tree;
use app\modules\doctor\models\Orgstructure;
use app\modules\doctor\models\FormMIS;
use app\modules\doctor\models\FormEvent;
use app\modules\doctor\models\Person;
use app\modules\doctor\models\Rbdiagnosticresult;
use app\modules\doctor\models\Rbresult;
use app\modules\doctor\models\Visit;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\db\ActiveRecord;
use app\modules\doctor\models\Callconfig;
use SoapClient;


class MonitorController extends AppDoctorController
{


    public function actionIndex(){

        $this->getView()->title = 'Контакт центр | Монитор врача';
        // если есть поисковый запрос

        if(Yii::$app->request->post('SearchMonitor')){
            $search = Yii::$app->request->post('SearchMonitor');
            if(!empty($search['birthdate'])){
                $search['birthdate'] = Yii::$app->formatter->asDate($search['birthdate'], 'php:Y-m-d');
            }
            if(!empty($search['snils'])){
                $search['snils'] = preg_replace("/-/i", "", preg_replace("/ /i", "", $search['snils']));
            }
            if(!empty($search['callNumberId'])){
                $session = Yii::$app->session;
                $session['callNumberId'] = $search['callNumberId']; // номер вызова
                $session['date_callNumberId'] = date('Y-m-d H:i:s'); // дата время вызова создание переменной callNumberId
            }
        }
        else{
            $search = array();
            $search['ID'] = '';
            $search['surname'] = '';
            $search['name'] = '';
            $search['patronymic'] = '';
            $search['birthdate'] = '';
            $search['snils'] = '';
        }
        // получаем поля для таблицы поциентов
        $query_pacients = '

SELECT 
       c.id,               -- Идентификатор пациента
       c.lastName,         -- Фамилия
       c.firstName,        -- Имя
       c.patrName,         -- Отчество
       DATE_FORMAT(c.birthDate, \'%d.%m.%Y\') AS birthDate,        -- Дата рождения
       c.sex,              -- Пол (0-неопределено, 1-М, 2-Ж)
       c.SNILS,            -- Снилс
       k.SOCR AS \'name_type\',    -- Тип населенного пункта
       k.NAME AS \'name_city\',    -- Имя населнного пункта
       s.SOCR AS \'street_type\',  -- Тип улицы
       s.NAME AS \'street_name\',  -- Имя улицы
       ah.number,          -- Номер дома
       ah.corpus,          -- Корпус
       IFNULL(c2.contact, \' \')  AS \'contact\'          -- Контактный номер

  FROM client c 
  LEFT JOIN clientaddress c3       -- Адрес пациента
  ON c.id = c3.client_id            -- Адрес пациента
  AND c3.deleted=0
  AND c3.type = 1
  LEFT JOIN address ad             -- Адреса общие
  ON c3.address_id = ad.id          -- Адреса общие
  LEFT JOIN addresshouse ah        -- Кладр
  ON ad.house_id = ah.id            -- Кладр
  LEFT JOIN kladr.kladr k          -- Таблица с Кладром Населнного пункта
  ON ah.KLADRCode = k.CODE          -- Таблица с Кладром Населнного пункта
  LEFT JOIN kladr.street s         -- Таблица с улицами
  ON ah.KLADRStreetCode = s.CODE    -- Таблица с улицами
  LEFT JOIN clientcontact c2       -- Таблица с контактами пациента
  ON c.id = c2.client_id            -- Таблица с контактами пациента
  AND c2.deleted = 0
   WHERE 
      c.id LIKE \'%'.$search['ID'].'%\'
  AND c.lastName LIKE \''.$search['surname'].'%\'
  AND c.firstName LIKE \''.$search['name'].'%\'  
  AND c.patrName LIKE \''.$search['patronymic'].'%\'
  AND c.birthDate LIKE \'%'.$search['birthdate'].'%\' 
  AND c.SNILS LIKE \'%'.$search['snils'].'%\'
  GROUP BY c2.client_id
  ORDER BY  c.lastName ASC
  LIMIT 50
        ';

        $pacients = Client::findbysql($query_pacients)->asArray()->all();


        $search = new SearchMonitor();


        $model= new FormEvent();
        $model->person_id = Yii::$app->user->identity->getId();
        $person = Person::findOne(Yii::$app->user->identity->getId());
        $model->orgstructure = $person->orgstructure->name;
        $model->setDate = date('Y-m-d H:i');
        $eventType = $this->actionGetEventType($person->orgstructure->id); // тип действия

        if($search->load(Yii::$app->request->post())){
            return $this->render('index',compact('pacients','search','model','eventType'));
        }
        if ($model->load(Yii::$app->request->post())){
            $this->actionInsertEvent($model,$person);
        }

        return $this->render('index',compact('pacients','search','model','eventType'));
    }


    public function actionGetPatient($id){

        $query = 'SELECT birthDate AS BirthDate, lastName AS FamilyName, firstName AS GivenName,  patrName AS MiddleName, id AS IdPatientMis, sex AS sexCode, \'urn:oid:1.2.643.5.1.13.2.1.1.156\' AS `System` FROM Client 
                    WHERE Client.`id` = '.$id.' LIMIT 0, 1;'; // получить фио
        $client = Yii::$app->db->createCommand($query)->queryAll();
        $result['client'] = $client[0];
        $query_phone = 'SELECT \'1\' AS `tipCode`, \'urn:oid:1.2.643.2.69.1.1.1.27\' AS `System`,   contact AS ContactValue FROM ClientContact
                        WHERE ((ClientContact.`client_id` = '.$id.') AND (ClientContact.`contact` IS NOT NULL) AND (ClientContact.`deleted` = 0));'; // получить телефон


        $phone = Yii::$app->db->createCommand($query_phone)->queryAll();
        if(isset($phone[0])){
            $result['contact'] = $phone[0];
        }else{
            $result['contact']['ContactValue'] = 'Нет номера';
        }
        $query_polis = 'SELECT ClientPolicy.id, clientpolicy.number AS DocN, clientpolicy.serial AS DocS, rbpolicykind.netrica_Code AS Code,   
\'urn:oid:1.2.643.2.69.1.1.1.59\' AS `spr`, clientpolicy.begDate AS IssuedDate, 
rbpolicykind.name AS `polis`,  rbpolicykind.code AS `code`,  rbpolicykind.federalCode AS `federalCode`
                        FROM ClientPolicy  INNER JOIN rbpolicykind ON clientpolicy.policyKind_id = rbpolicykind.id WHERE ((ClientPolicy.`client_id` = '.$id.')
                        AND (ClientPolicy.`deleted` = 0))
                        ORDER BY ClientPolicy.id DESC LIMIT 0, 1;';
        $polis = Yii::$app->db->createCommand($query_polis)->queryAll();
        $result['polis'] = $polis[0];



        $query_doc = 'SELECT netrica_Code, -- тип документа
                        number, -- номер
                        `serial`, -- серия
                        `date`, -- дата выдачи
                        `origin`, -- кем выдан
                        `code`, -- тип документа
                        `name`, -- тип документа текстом
                        `federalCode` -- тип документа федеральный
                            FROM ClientDocument
                              INNER JOIN rbDocumentType ON ((ClientDocument.`documentType_id` = rbDocumentType.`id`) AND (ClientDocument.`deleted` = 0))
                                WHERE ((ClientDocument.`client_id` = '.$id.') AND (ClientDocument.`deleted` = 0) AND (rbDocumentType.`regionalCode` != \'\'))
                                LIMIT 0, 1;';

        $doc = Yii::$app->db->createCommand($query_doc)->queryAll(); // получаем id адреса
        $result['document'] = $doc[0]; // добавляем документ


        return $result;
    } // получаем информацию о пациента

    public function actionGetDoctor(){
        $id = Yii::$app->request->post('value');
        $query = "SELECT DISTINCT
  vrbPerson.`code`,
  vrbPerson.`name`,
  vrbPerson.`orgStructure_id`,
  Person.`federalCode`,
  Person.`post_id`,
  vrbPerson.`speciality_id`,
  vrbPerson.`retireDate`,
  Person.`academicDegree`,
  Person.`id` as 'person_id',
  vrbPerson.`id`
FROM vrbPerson
  INNER JOIN Person
    ON Person.`id` = vrbPerson.`id`
WHERE vrbPerson.`orgStructure_id`= $id /*тут код оргструктуры*/
ORDER BY vrbPerson.`id` DESC LIMIT 0, 300";

        $array = Yii::$app->db->createCommand($query)->queryAll();
        $option = '';
        foreach ($array as $item) {
            $option .= '<option value='.$item['person_id'].'>'.$item['code'].' '.$item['name'].'</option>';
        }


        return json_encode($option);



    } // возвращем список врачей в зависимости от выбранного подразделения // метод не используется, но пока оставил на всякий случай

    public function actionGetEventType($orgstructure_id){
        if ($orgstructure_id == false){
            Yii::$app->session->setFlash('orgstructure_id',"<p><strong>Ошибка!</strong> Вы авторизованы под пользователем, который не прикреплен к неотложной помощи! Дальнейшая работа невозможна</p>");
            return false;
        }
        $query = "SELECT
  e.id,
 e.name
FROM orgstructure_eventtype oe
  INNER JOIN orgstructure o
    ON oe.master_id = o.id
  INNER JOIN eventtype e
    ON oe.eventType_id = e.id
  WHERE o.id = $orgstructure_id
  AND o.deleted=0
  AND e.deleted=0;";

        $array = Yii::$app->db->createCommand($query)->queryAll();
        return $array;
    } // получить тип события

    public function actionGetMkb(){
        $q = Yii::$app->request->get('term');
        $model = Mkb_tree::find()->where(['LIKE', 'DiagID', $q])->all();
        $arr = '[';
        foreach ($model as $key => $item) {
            if($key == 0){
                $arr .= '{"label": "'.$item->DiagID.' | '.$item->DiagName.'", "value": "'.$item->DiagID.'"}';
            }else{
                $arr .= ',{"label": "'.$item->DiagID.' | '.$item->DiagName.'", "value": "'.$item->DiagID.'"}';
            }

        }
        $arr .= ']';
        return $arr;
    } // Ищем МКБ в форме autocomplate

    /*Цепочка для создания обращения*/

    public function actionInsertEvent($model,$person){
        $client = Client::findOne($model->client_id);
        $doc = new \ArrayObject($this->actionGetPatient($model->client_id));
        $client_tmp = new Client_tmp();
        $client_tmp->client_id = $client->id;
        $client_tmp->client_id = $model->mkb;
        $rbresult = Rbresult::find()->where(['code'=>304])->one(); // результат лечения
        $contract = $this->getContract($model);

        $orgstructure = Orgstructure::find()->where(['id'=>$person->orgstructure->id])->one();
        $event = new Event();
        $event->createDatetime = date('Y-m-d H:i:s'); // Дата создания записи
        $event->createPerson_id = $person->id; // Автор записи {Person}
        $event->modifyDatetime = date('Y-m-d H:i:s'); // Дата изменения записи
        $event->modifyPerson_id = $person->id; // Автор изменения записи {Person}
        $event->externalId = ''; // varchar(30) NOT NULL COMMENT 'внешний идентификатор полученный при импорте ф131 и т.п.',
        $event->eventType_id = $model->eventType; // Тип события {EventType}
        $event->org_id = $orgstructure->organisation_id; // Место проведения {Organisation}
        $event->client_id = $client->id; // Пациент к которому относится действие {Client}
        $event->contract_id = $contract['id']; // Договор {Contract}
        $event->prevEventDate = NULL; // Дата предыдущего события (для указания периодического осмотра в ДМО)
        $event->setDate = $model->setDate; // Дата начала
        $event->setPerson_id = $person->id /*NULL*/; // DEFAULT NULL COMMENT 'Направивший сотрудник ЛПУ {Person}',
        $event->execDate = date('Y-m-d H:i:s');; // Дата выполнения
        $event->execPerson_id = $person->id; //  DEFAULT NULL COMMENT 'Выполнивший сотрудник ЛПУ {Person}',
        $event->isPrimary = 1; // Признак первичности (1-первичный, 2-повторный, 3-активное посещение, 4-перевозка)
        $event->order = 8; // Порядок наступления (1-плановый, 2-экстренный, 3-самотёком, 4-принудительный, 5-неотложный)
        $event->result_id = $rbresult->id; // Результат {rbResult} // Лечение продолжено
        $event->nextEventDate = NULL; // Дата след. явки
        $event->payStatus = 0; // Флаги финансирования
        $event->typeAsset_id = NULL; // Тип актива {rbEmergencyTypeAsset}
        $event->note = ''; // Примечание
        $event->curator_id = NULL; // Куратор {Person}
        $event->assistant_id = NULL; // Ассистент {Person}
        $event->pregnancyWeek = '0'; // Срок беременности, 0-нет беременности, NOT NULL DEFAULT 0
        $event->MES_id = NULL; // МЭС {mes.MES}
        $event->HTG_id = NULL; //
        $event->KSG_id = NULL; //
        $event->mesSpecification_id = NULL; // Особенность выполнения МЭС {rbMesSpecification}
        $event->ksgService_id = NULL; // КСГ {rbService}
        $event->relegateOrg_id = NULL; // Направитель {Organisation}
        $event->relegateNum = ''; // Номер направления
        $event->totalCost = '0'; // Сумма по услугам
        $event->patientModel_id = NULL; // Модель пациента {rbPatientModel}
        $event->cureType_id = NULL; // Вид лечения {rbCureType}
        $event->cureMethod_id = NULL; // Метод лечения {rbCureMethod}
        $event->prevEvent_id = NULL; // Является продолжением События{Event}
        $event->isClosed = 0; // Закрыто событие или нет (0-не закрыто, 1-закрыто)
        $event->clientPolicy_id = $doc->polis->id; // Полис пациента для данного случая {ClientPolicy}
        $event->littleStranger_id = NULL; //
        $event->referral_id = NULL; //
        $event->armyReferral_id = NULL; //
        $event->goal_id = NULL; // Цель
        $event->outgoingOrg_id = NULL; //
        $event->outgoingRefNumber = NULL; //
        $event->hmpKind_id = NULL; //
        $event->hmpMethod_id = NULL; //
        $event->eventCostPrinted = 0; //
        $event->exposeConfirmed = 0; // Добавлять ли событие к выставлению в счет (имеет значение только для событий, в типе которых exposeConfirmation = 1)
        $event->ZNOFirst = 0; // ЗНО установлен впервые
        $event->ZNOMorph = 0; // ЗНО подтверждено морфологически
        $event->hospParent = 0; // Госпитализация с родителем/представителем
        $event->cycleDay = 0; // День цикла (для беременных) [i2582]
        $event->locked = 0; // Обращение заблокировано для редактирования
        $event->dispByMobileTeam = 0; // Флаг "Диспансеризация(проф.осмотр) проведена мобильной выездной бригадой"
        $event->duration = NULL; // DEFAULT NULL COMMENT 'Длительность лечения',
        $event->orgStructure_id = NULL /*$model->orgstructure*/; // DEFAULT NULL COMMENT 'Подразделение {OrgStructure}',
        $event->MSE = NULL; // DEFAULT 0 COMMENT 'Флаг "Передано направление на МСЭ в бюро МСЭ"',
        $event->RKEY = NULL; // DEFAULT NULL,
        $event->signedDocuments = 0; // tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Отметка об успешном подписании документа',
        $event->signDateTime = NULL; // Дата подписи
        $event->note = 'Запись через планшет - неотложка';

        if($event->save()){
            $action = $this->actionInsertAction($model,$person,$event);
            $diagnosis = $this->actionInsertDiagnosis($model,$person,$client);
            $diagnostic = $this->actionInsertDiagnostic($model,$person,$event,$diagnosis);
            $visit = $this->actionInstartVisit($model,$person,$event,$action);

            if (Yii::$app->request->post('Dnevnic')){
                $actionType = \app\modules\doctor\models\ActionType::find()->where(['name'=>'Дневник неотложной помощи','deleted'=>0])->one(); // дневник
                $dnevnic = Yii::$app->request->post('Dnevnic');
                $action = $this->actionInsertAction($model,$person,$event,$actionType); // добавляем дневник в action
                $this->actionInsertActionProperty($model,$person,$action,$dnevnic); // добавляем значения в дневник
            } // если есть заполненный дневник

            Yii::$app->session->setFlash('addEvent',"<strong>Успешно!</strong> Обращение создано, номер - <strong>$event->id</strong>!");
        }

    } // Добавить событие / создать обращение

    public function actionInsertAction($model,$person,$event,$actionType = null){
        $eventtype_action = Eventtype_action::find()->where(['eventType_id'=>$model->eventType])->andWhere(['speciality_id'=>$person->speciality_id])->one();
        if(isset($actionType)){
            $eventtype_action->actionType_id = $actionType->id;
        }
        $action = new Action();
        $action->createDatetime = $model->setDate;
        $action->createPerson_id = $person->id;
        $action->modifyDatetime = date('Y-m-d H:i:s');
        $action->endDate = date('Y-m-d H:i:s');
        $action->modifyPerson_id = $person->id;
        $action->actionType_id = $eventtype_action->actionType_id;
        $action->event_id = $event->id;
        $action->directionDate = date('Y-m-d H:i:s');;
        $action->status = 2;
        $action->setPerson_id = $person->id;
        $action->begDate = date('Y-m-d H:i:s');
        $action->note = 'Запись через планшет - неотложка';
        $action->person_id = $person->id;
        $action->amount = 1;
        $action->payStatus = 0;
        $action->finance_id = 2;
        $action->save();
        return $action;
    } // добавляем action для того чтобы в обращении на вкладке статус что то было

    public function actionInsertDiagnosis($model,$person,$client){
        $diagnosis = new Diagnosis();
        $diagnosis->createDatetime = $model->setDate;
        $diagnosis->createPerson_id = $person->id;
        $diagnosis->modifyDatetime = date('Y-m-d H:i:s');
        $diagnosis->modifyPerson_id = $person->id;
        $diagnosis->client_id = $client->id;
        $diagnosis->diagnosisType_id = 2; /*тип - основной*/
        $diagnosis->MKB = $model->mkb;
        $diagnosis->person_id = $person->id;
        $diagnosis->setDate = $model->setDate;
        $diagnosis->endDate = date('Y-m-d H:i:s');
        $diagnosis->save();
        return $diagnosis;
    } // для диагноза и других полей

    public function actionInsertDiagnostic($model,$person,$event,$diagnosis){
        $diagnostic = new Diagnostic();
        $rbdiagnosticresult = Rbdiagnosticresult::find()->where(['code'=>304])->andWhere(['filterResults'=>'true'])->one();

        $diagnostic->createDatetime = $model->setDate;
        $diagnostic->createPerson_id = $person->id;
        $diagnostic->modifyDatetime = date('Y-m-d H:i:s');
        $diagnostic->modifyPerson_id = $person->id;
        $diagnostic->speciality_id = $person->speciality_id;
        $diagnostic->event_id = $event->id;
        $diagnostic->diagnosis_id = $diagnosis->id;
        $diagnostic->diagnosisType_id = 2; /*тип - основной*/
        $diagnostic->character_id = 1; /*тип - острое*/
        $diagnostic->person_id = $person->id;
        $diagnostic->result_id = $rbdiagnosticresult->id;
        $diagnostic->setDate = $model->setDate;
        $diagnostic->endDate = date('Y-m-d H:i:s');
        $diagnostic->notes = 'Запись через контакт центр модуль неотложной помощи';
        $diagnostic->save();
        return $diagnostic;
    } // для диагноза и других полей

    public function actionInstartVisit($model,$person,$event){
        $visit = new Visit();
        $action = Action::find()->where(['event_id'=>$event->id])->one();

        $visit->createDatetime = $model->setDate; // Дата создания записи
        $visit->createPerson_id = $person->id; // Автор записи {Person}
        $visit->modifyDatetime = date('Y-m-d H:i:s'); // Дата изменения записи
        $visit->modifyPerson_id = $person->id; //Автор изменения записи {Person}
        $visit->event_id = $event->id; // Событие {Event}
        $visit->scene_id = 2; // 'Место (может менять базовый сервис) {rbScene} // 1 - поликлиника, 2 - на дому, 3 - актив на дому, 4 - на выезде, 5 - приемные покой
        $visit->date = date('Y-m-d H:i:s'); // Дата посещения (есть в событии?)
        $visit->visitType_id = 1; // Тип визита (может менять базовый сервис) {rbVisitType}
        $visit->person_id =  $person->id;
        $visit->isPrimary = 0; // Признак первичности (1-первичный, 2-повторный)
        $visit->finance_id = 2; // Тип финансирования - только для статистики, заполняется по врачу {rbFinance}
        $visit->service_id = $action->actionType->nomenclativeService_id; // Оказанная услуга {rbService}
        $visit->save();
        return $visit;


    } // добавляем визит, для того чтобы было место выполнения в обращении

    public function actionInsertActionProperty($model,$person,$action,$dnevnic){
        foreach ($dnevnic as $item) {
            $actionproperty = new ActionProperty();
            $actionproperty->createDatetime = $model->setDate;
            $actionproperty->createPerson_id = $person->id;
            $actionproperty->modifyDatetime = date('Y-m-d H:i:s');
            $actionproperty->modifyPerson_id = $person->id;
            $actionproperty->action_id = $action->id;
            $actionproperty->type_id = $item['id'];
            $actionproperty->norm = '';
            $actionproperty->isAssigned = 0;
            $actionproperty->save();
            if (isset($item['content'])){
                $ActionProperty_String = new ActionProperty_String();
                $ActionProperty_String->id = $actionproperty->id;
                $ActionProperty_String->index = 0;
                $ActionProperty_String->value = $item['content'];
                $ActionProperty_String->save(); // сохраняемзначения дневника
            } // если есть хоть какое то значение в ячейке дневника
        }

    } // делаем связь конкретного дневника (action_id) и его свойств значений(type_id -> ActionPropertyType)

    public function getContract($model){
        if ($model->region == 1){
            $like = "AND c.number LIKE '%ино%'";
        }elseif ($model->region == 0){
            $like = "AND c.number NOT LIKE '%ино%'";
        }
        $contract_query = "SELECT 
  c.id,
  c.resolution,
  c.number
FROM eventtype e
  INNER JOIN contract_specification cs
  ON e.id=cs.eventType_id
  INNER JOIN contract c
  ON cs.master_id=c.id
  WHERE e.id=145
  $like
  AND c.deleted=0
  AND e.deleted=0
  AND cs.deleted=0
  LIMIT 1
  ";
        $contract = Yii::$app->db->createCommand($contract_query)->queryAll();
        $contract_id = $contract[0];
        return $contract_id;
    } // получить идентификатор контракта

    /*Цепочка для создания обращения*/

    public function actionList(){
        $this->getView()->title = 'Неотложная помощь | Список пациентов';
        // если есть поисковый запрос






        $model= new FormEvent();
        $model->person_id = Yii::$app->user->identity->getId();
        $person = Person::findOne(Yii::$app->user->identity->getId());
        $model->orgstructure = $person->orgstructure->name;
        $model->setDate = date('Y-m-d H:i');
        $eventType = $this->actionGetEventType($person->orgstructure->id); // тип действия

        $formsearchlist = new FormListSearch();
        if(!$formsearchlist->load(Yii::$app->request->post()))
        {
            $formsearchlist->dateOne = date('d.m.Y');
        }


        if ($model->load(Yii::$app->request->post())){
            $this->actionInsertEvent($model,$person);
//            $formsearchlist = Yii::$app->formatter->asDate($model->dateOne,'php:d.m.Y');
            $session = Yii::$app->session;
            if(isset($session['callNumberId'])){
                $callNumberId = $session['callNumberId'];
                $date_callNumberId = $session['date_callNumberId'];
                $this->actionAddEvent($callNumberId);
                unset($session['callNumberId']);
                unset($session['date_callNumberId']);
            } // если есть переменная, то нужно вызвать функцию обработки неотложки, вызов нужно закрыть
        }



        $pacients = Action::find()
            ->where(['LIKE','directionDate', Yii::$app->formatter->asDate($formsearchlist->dateOne,'php:Y-m-d')])
            ->andWhere(['deleted' => 0])
            ->andWhere(['person_id' => Yii::$app->user->identity->getId()])
            ->andWhere(['actionType_id' => 19])
            ->groupBy('event_id')
            ->all();

        return $this->render('list',compact('pacients','model','eventType','formsearchlist'));
    }

    public function actionSetForm($client_id){
        $this->getView()->title = 'Неотложная помощь | Список пациентов';



        $model= new FormEvent();
        $model->person_id = Yii::$app->user->identity->getId();
        $person = Person::findOne(Yii::$app->user->identity->getId());
        $model->orgstructure = $person->orgstructure->name;
        $model->setDate = date('Y-m-d H:i');
        $model->client_id = $client_id;
        $eventType = $this->actionGetEventType($person->orgstructure->id); // тип действия
        if ($model->load(Yii::$app->request->post())){
            $this->actionInsertEvent($model,$person);
        }

        return $this->render('__form',compact('model','eventType'));

    } // Обработка формы

    public function actionAddEvent($callNumberId){
        date_default_timezone_set("Europe/Moscow");
        $WSDL_SSMP = \app\modules\doctor\models\Callconfig::find()->where(['title'=>'wsdl_ssmp'])->one();
        $LPU = Callconfig::find()->where(['title'=>'lpu'])->one();

        // Метод получает идентификатор вызова, на его основании получает массив и передает его интеграционной платформе ССМП

//        $callNumberId = 5800000000269785;
        $ssmpresoult = 6; // идентификатор события из метода getSprCallEventType с параметром eventAccess
        $ssmpresoult_text = 'Вызов выполнен'; // название опции
        $note = ''; // комментарий
//        $smpevents = Smpevents::find()->where(['callNumberId'=>$callNumberId])->one();
        $request = array(
            'lpuCode'=>(int)$LPU->content,
            'idCallNumber'=>$callNumberId,
            'idCallEventType'=>$ssmpresoult,
            'note'=>$note,
            'eventTime'=>date("H:i:s"), PHP_EOL,
            'transferUser'=>Yii::$app->user->identity->login,
        );
        $client = new SoapClient($WSDL_SSMP->content, array("trace"=>1,'exceptions' => 0));
        $resoult = $client->addEvent($request);
        $local = Smpevents::addEventLocal($callNumberId,$ssmpresoult_text,$note,$ssmpresoult); // запись значения в базу, перенес в модель

//        $this->actionAddEventLocal($callNumberId,$ssmpresoult_text,$note,$ssmpresoult); // запись значения в базу

    } // отметить что вызов(или событие) был принят в ССМП АльтСистем






}