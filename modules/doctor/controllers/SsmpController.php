<?php
/**
 * Created by PhpStorm.
 * User: Belya
 * Date: 22.05.2019
 * Time: 9:52
 */

namespace app\modules\doctor\controllers;

use app\modules\doctor\models\Client;
use app\modules\doctor\models\SearchMonitor;
use Yii;
use SoapClient;
use app\modules\doctor\models\Smpevents;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\modules\doctor\models\Callconfig;
use yii\web\NotFoundHttpException;


class SsmpController extends AppDoctorController
{
    public $wsdl_ssmp;
    public $lpu;

    public function actionIndex($eventId = null){
        $this->getView()->title = 'Контакт центр | ССМП';
        $smpevents = Smpevents::find()->asArray()->where(['LIKE','isDone',0])->orderBy(['id'=>SORT_DESC])->all();
        $this->actionGetNewListSsmp(); // получить новые вызовы
        $smpevents_form = new Smpevents();
        $smpevents_form->callDate = date('d.m.Y');
        if($smpevents_form->load(Yii::$app->request->post())){
            $smpevents = $smpevents_form->search();
        }


        if(Yii::$app->request->post('SearchMonitor')){
            $search = Yii::$app->request->post('SearchMonitor');
            if(!empty($search['birthdate'])){
                $search['birthdate'] = Yii::$app->formatter->asDate($search['birthdate'], 'php:Y-m-d');
            }
            if(!empty($search['snils'])){
                $search['snils'] = preg_replace("/-/i", "", preg_replace("/ /i", "", $search['snils']));
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
        }

        $search = new SearchMonitor();
        return $this->render('index',compact('smpevents','smpevents_form','search','pacients'));
    }

    public function actionNumberssmp()
    {
        $callNumberId = Yii::$app->request->post('callNumberId');
        $res = Smpevents::find()->asArray()->where(['LIKE','callNumberId',$callNumberId])->all();
        foreach ($res as $item){
            $resoult =
                '<h2>Информация о вызове</h2>'.
                '<div class="ssmp_info bg-info">'.
                '<div class="col-md-6">'.
                '<p><span>Идентификатор вызова:</span> '.$item['callNumberId'].'</p>'.
                '<p><span>ФИО:</span> '.$item['fio'].'</p>'.
                '<p><span>Адрес:</span> '.$item['address'].'</p>'.
                '<p><span>Дата вызова:</span> '.date("d.m.Y", strtotime($item['callDate'])).'</p>'.
                '<p><span>Пол:</span> '.($item['sex'] = 0 ? 'муж' : 'жен').'</p>'.
                '<p><span>Вид вызова:</span> '.$item['callKind'].'</p>'.
                '</div><div class="col-md-6">'.
                '<p><span>Время вызова:</span> '.$item['eventTime'].'</p>'.
                '<p><span>Возраст:</span> '.$item['age'].'</p>'.
                '<p><span>ФИО вызывавшего:</span> '.$item['callerName'].'</p>'.
                '<p><span>Принявший вызов:</span> '.$item['receiver'].'</p>'.
                '<p><span>Контактный телефон:</span> '.$item['contact'].'</p>'.
                '<p><span>Категория срочности:</span> '.$item['urgencyCategory'].'</p></div></div>'
            ;
        }


        return json_encode($resoult);

    } // получаем подробную информацию о вызове с базы

    public function actionGetEventList(){
        $WSDL_SSMP = Callconfig::find()->where(['title'=>'wsdl_ssmp'])->one();
        $this->wsdl_ssmp = $WSDL_SSMP->content;
        $LPU = Callconfig::find()->where(['title'=>'lpu'])->one();
        $this->lpu = $LPU->content;
        $request = array(
            'lpuCode'=>$this->lpu,
        );


        ini_set('default_socket_timeout', 10000);
        $client = new SoapClient($this->wsdl_ssmp, array("trace"=>1,'exceptions' => 0));

        $getEventList = $client->getEventList($request);

        if(is_soap_fault($getEventList)){
            $resoult = false;
        }else{
            foreach($getEventList->getEventListResult->EventItem as $k => $v){ // для объекта
//                $resoult[$k] = json_decode(json_encode($v), true);
                $resoult[$k] = $v;
            }
        }

        return $resoult;



    } // получаем все вызовы через wsdl

    public function actionGetCallInfoStr($idCallNumber)
    {
        $WSDL_SSMP = Callconfig::find()->where(['title'=>'wsdl_ssmp'])->one();
        $this->wsdl_ssmp = $WSDL_SSMP->content;
        $LPU = Callconfig::find()->where(['title'=>'lpu'])->one();
        $this->lpu = $LPU->content;
        $request = array(
            'lpuCode'=>$this->lpu,
            'idCallNumber'=>$idCallNumber,
        );
        $client = new SoapClient($this->wsdl_ssmp, array("trace"=>1));
        $getCallInfoStr = $client->getCallInfoStr($request);
        foreach($getCallInfoStr as $k=>$v){
            $resoult[$k] = $v;
        }
        return json_decode(json_encode($resoult['getCallInfoStrResult']),true);
//        return $resoult['getCallInfoStrResult'];
    } // получаем один вызов через wsdl

    /*Тут изменить метод, убрать сравнение двух массивов, просто оставить получение данных
    * Полученные данные просто записать в базу со статусом "необработанно"
    */
    public function actionGetNewListSsmp(){

        $GetEventList = $this->actionGetEventList();  // получаем все вызовы через wsdl
        if ($GetEventList == false){return false;}

        if (!isset($GetEventList[0]->idCallNumber)){
            $result[0] = $GetEventList;
        }else{
            $result = $GetEventList;
        }

//        var_dump($result);
//        die();

//        debug($result);
//        echo '<br>';
        $result = json_decode(json_encode($result),true);
        if(!empty($result[0])){
            foreach ($result as $key => $n){
//                $n = json_decode(json_encode($n),true);
//                if (!is_array($n)){
//                    $n = ArrayHelper::toArray($n,[
//                        'idCallNumber',
//                        'eventTime',
//                        'eventAccess',
//                        'isTransfered',
//                        'idCallEventType',
//                        'note',
//                        'openingTime',
//                        'transferUser',
//                        'id',
//                    ]);
//                } // переводим объект в ассоциалтивный массив


//                var_dump($n);
//                var_dump($n['eventTime']);
//                var_dump($n['eventAccess']);
//                die();
//                echo '<hr><br>';

            if (is_array($n)){
                if (isset($n['idCallNumber'])){
                    $smpevents = Smpevents::find()->where(['callNumberId' => $n['idCallNumber']])->one();
                }
                if(!isset($smpevents)){
                    $res[$key] = $this->actionGetCallInfoStr($n['idCallNumber']); // из за того что требуется время на ответ от сервера в базу ложится только одно значение, но не все сразу
//                    var_dump($res);
//                    die();
                    $res[$key]['eventTime'] =  $n['eventTime'];
//                    $res[$key]['eventTime'] =  ArrayHelper::getValue($n, 'eventTime');
                    $res[$key]['eventAccess'] = $n['eventAccess'];
                    $res[$key]['isTransfered'] = $n['isTransfered'];
                    $res[$key]['idCallEventType'] = $n['idCallEventType'];
                    $res[$key]['note'] = $n['note'];
                    $res[$key]['openingTime'] = $n['openingTime'];
                    $res[$key]['transferUser'] = $n['transferUser'];
                    $res[$key]['id'] = $n['id'];
                }
            }
//            else{
//                if (isset($n->idCallNumber)){
//                    $smpevents = Smpevents::find()->where(['callNumberId' => $n->idCallNumber])->one();
//                }
//                if(!isset($smpevents)){
//                    $res[$key] = $this->actionGetCallInfoStr($n->idCallNumber); // из за того что требуется время на ответ от сервера в базу ложится только одно значение, но не все сразу
//                    $res[$key]['eventTime'] = $n->eventTime;
//                    $res[$key]['eventAccess'] = $n->eventAccess;
//                    $res[$key]['isTransfered'] = $n->isTransfered;
//                    $res[$key]['idCallEventType'] = $n->idCallEventType;
//                    $res[$key]['note'] = $n->note;
//                    $res[$key]['openingTime'] = $n->openingTime;
//                    $res[$key]['transferUser'] = $n->transferUser;
//                    $res[$key]['id'] = $n->id;
//                }
//            }

            }  // получаем подробную информацию через wsdl
        }
//       echo '<br>';
//        debug($res);
//        die();

        if(!empty($res)){
            $this->SsmpInsetr($res); // Добавить вызов в базу
        }


    } // получить новые вызовы


    public function SsmpInsetr($resarray){
        $smpevents = new Smpevents();
        foreach ($resarray as $item) {
            $smpevents->createDateTime = date("Y-m-d H:i:s"); // Дата и время добавления события
            $smpevents->eventId = $item['id']; // Ид события
            $smpevents->eventTime = $item['eventTime']; // Время приема события
            $smpevents->callNumberId = $item['idCallNumber']; // Ид вызова
            $smpevents->callDate = $item['callDate']; // Дата вызова
            $smpevents->fio = $item['lastName'].' '.$item['name'].' '.$item['patronymic']; // ФИО пациента
            $smpevents->sex = $item['sex']; // Пол пациента {0 - М, 1 - Ж}
            $smpevents->age = $item['ageYears']; // Полных лет
            $smpevents->contact = $item['telephone']; // Контактный телефон
            $smpevents->address =
                'нас. пункт: '.$item['settlement'].
                ' улица: '.$item['street'].
                ' дом: '.$item['house'].' '
//                .
//                ' /: '.$item['houseFract'].
//                ' корпус: '.$item['building'].
//                ' кв: '.$item['flat'].
//                ' подъезд: '.$item['porch'].
//                ' этаж: '.$item['floor']
            ; // Адрес

            if(!empty($item['houseFract'])){$smpevents->address.='/'.$item['houseFract'];}
            if(!empty($item['building'])){$smpevents->address.=' корпус: '.$item['building'];}
            if(!empty($item['flat'])){$smpevents->address.=' кв: '.$item['flat'];}
            if($item['porch'] > 0){$smpevents->address.=' подъезд: '.$item['porch'];}
            if($item['floor'] > 0){$smpevents->address.=' этаж: '.$item['floor'];}
//            $smpevents->address.=  empty($item['houseFract']) ?: '/'.$item['houseFract'];
//            $smpevents->address.= empty($item['building']) ?: ' корпус: '.$item['building'];
//            $smpevents->address.= empty($item['flat']) != '' ?: ' кв: '.$item['flat'];
//            $smpevents->address.= $item['porch'] > 0 ?: ' подъезд: '.$item['porch'];
//            $smpevents->address.= $item['floor'] > 0 ?: ' этаж: '.$item['floor'];

            $smpevents->landmarks = $item['landmarks']; // Ориентиры
            $smpevents->occasion = $item['callOccasion']; // Повод вызова
            $smpevents->callerName = $item['callerName']; // ФИО вызывающего
            $smpevents->urgencyCategory = $item['urgencyCategory']; // Категория срочности
            $smpevents->callKind = $item['callKind']; // Вид вызова
            $smpevents->receiver = $item['userReceiver']; // ФИО принявшего событие

            $smpevents->save();
        }

    } // Добавить вызов в базу

    public function actionAddEvent(){
        date_default_timezone_set("Europe/Moscow");
        $WSDL_SSMP = Callconfig::find()->where(['title'=>'wsdl_ssmp'])->one();
        $this->wsdl_ssmp = $WSDL_SSMP->content;
        $LPU = Callconfig::find()->where(['title'=>'lpu'])->one();
        $this->lpu = (int)$LPU->content;

        // Метод получает идентификатор вызова, на его основании получает массив и передает его интеграционной платформе ССМП
        $callNumberId = Yii::$app->request->post('callNumberId');
//        $callNumberId = 5800000000269785;
        $ssmpresoult = Yii::$app->request->post('ssmpresoult'); // идентификатор события из метода getSprCallEventType с параметром eventAccess
        $ssmpresoult_text = Yii::$app->request->post('ssmpresoult_text'); // название опции
        $note = Yii::$app->request->post('note'); // комментарий
//        $smpevents = Smpevents::find()->where(['callNumberId'=>$callNumberId])->one();
        $request = array(
            'lpuCode'=>$this->lpu,
            'idCallNumber'=>$callNumberId,
            'idCallEventType'=>$ssmpresoult,
            'note'=>$note,
            'eventTime'=>date("H:i:s"), PHP_EOL,
            'transferUser'=>Yii::$app->user->identity->login,
        );
        $client = new SoapClient($this->wsdl_ssmp, array("trace"=>1,'exceptions' => 0));
        $resoult = $client->addEvent($request);
        $local = Smpevents::addEventLocal($callNumberId,$ssmpresoult_text,$note,$ssmpresoult); // запись значения в базу, перенес в модель
//        $this->actionAddEventLocal($callNumberId,$ssmpresoult_text,$note,$ssmpresoult); // запись значения в базу
        return json_encode($resoult);
    } // отметить что вызов(или событие) был принят в ССМП АльтСистем

    public function actionUpdEvent(){
        date_default_timezone_set("Europe/Moscow");
        $WSDL_SSMP = Callconfig::find()->where(['title'=>'wsdl_ssmp'])->one();
        $this->wsdl_ssmp = $WSDL_SSMP->content;
        $LPU = Callconfig::find()->where(['title'=>'lpu'])->one();
        $this->lpu = (int)$LPU->content;

        // Метод получает идентификатор вызова, на его основании получает массив и передает его интеграционной платформе ССМП
        $eventId = Yii::$app->request->post('eventId');


        $request = array(
            'lpuCode'=>$this->lpu,
            'id'=>$eventId, // id события на ССМП
            'eventTime'=>date('H:i:s'),
            'receivedUser'=>Yii::$app->user->identity->login,
        );
//        return json_encode($request);
        $client = new SoapClient($this->wsdl_ssmp, array("trace"=>1,'exceptions' => 0));
        $resoult = $client->updEvent($request);
        if($resoult->updEventResult == true){
            $smpevents = Smpevents::find()->where(['eventId'=>$eventId])->one();
            $smpevents->status = 1;
            if ($smpevents->save()){
                Yii::$app->session->setFlash('ssmp_success',"<strong>Успешно!</strong> Вызов <strong>$eventId</strong> принят!");
                return json_encode($resoult);
            }else{
                Yii::$app->session->setFlash('ssmp_error',"<strong>Неудача!</strong> Вызов <strong>$eventId</strong> не принят! Сервер ССМП не принял ответ");
            }
        }else{
            Yii::$app->session->setFlash('ssmp_error',"<strong>Неудача!</strong> Вызов <strong>$eventId</strong> не принят! Не получилось сохранить БД ответ");
            return json_encode($resoult);
        }


    } // Функция вызывается для передачи времени получения события в поликлинике и ФИО принявшего



    public function actionCheckSsmp(){
        $this->actionGetNewListSsmp(); // получаем новые вызовы
        $smpevents = Smpevents::find()->where(['LIKE','status',0])->all();
        $link = Url::to(['ssmp/index']);
        if(count($smpevents) > 0){
            $resoult = "<strong>Поступил новый вызов ССМП!<br></strong><a href=".$link."> Перейти!</a>";
        }else{
            $resoult = false;
        }

        return json_encode($resoult);
    } // проверить есть ли что в базе

    public function end($eventId){
        $request = array(
            'lpuCode'=>$this->lpu,
            'id'=>$eventId, // id события на ССМП
            'eventTime'=>date('H:i:s'),
            'receivedUser'=>Yii::$app->user->identity->login,
        );
        $client = new SoapClient($this->wsdl_ssmp, array("trace"=>1,'exceptions' => 0));
        $resoult = $client->updEvent($request);
    }

    public function addBase($result){
        foreach ($result as $key => $n){
//            debug($n->idCallNumber);

////            $smpevents = Smpevents::find()->where(['callNumberId' => $n['idCallNumber']])->one();
            $smpevents = Smpevents::find()->where(['callNumberId' => $n->idCallNumber])->one();

            if(!isset($smpevents)){
                $res[$key] = $this->actionGetCallInfoStr($n['idCallNumber']);
                $res[$key]['eventTime'] = $n['eventTime'];
                $res[$key]['eventAccess'] = $n['eventAccess'];
                $res[$key]['isTransfered'] = $n['isTransfered'];
                $res[$key]['idCallEventType'] = $n['idCallEventType'];
                $res[$key]['note'] = $n['note'];
                $res[$key]['openingTime'] = $n['openingTime'];
                $res[$key]['transferUser'] = $n['transferUser'];
                $res[$key]['id'] = $n['id'];
//                echo 'Номер цикла '.$key.' есть запись! <br>';
            }


        }  // получаем подробную информацию через wsdl

        if(!empty($res)){
            $this->SsmpInsetr($res); // Добавить вызов в базу
        }
    } // это можно удалить

    public function actionSearch(){

        $birthdate = Yii::$app->request->post('birthdate');
        if(!empty($birthdate)){
            $search['birthdate'] = Yii::$app->formatter->asDate($birthdate, 'php:Y-m-d');
        }
        $snils = Yii::$app->request->post('snils');
        if(!empty($snils)){
            $search['snils'] = preg_replace("/-/i", "", preg_replace("/ /i", "", $snils));
        }
        $birthdate = Yii::$app->request->post('birthdate');
        $birthdate = Yii::$app->request->post('birthdate');
        $birthdate = Yii::$app->request->post('birthdate');

        if(Yii::$app->request->post('SearchMonitor')){
            $search = Yii::$app->request->post('SearchMonitor');


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

        return json_encode($pacients);

    }
}