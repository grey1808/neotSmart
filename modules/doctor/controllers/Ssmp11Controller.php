<?php


namespace app\modules\doctor\controllers;


use app\modules\doctor\models\Action;
use app\modules\doctor\models\ActionProperty;
use app\modules\doctor\models\ActionProperty_String;
use app\modules\doctor\models\Bsmp;
use app\modules\doctor\models\Callconfig;
use app\modules\doctor\models\Client;
use app\modules\doctor\models\Client_tmp;
use app\modules\doctor\models\Diagnosis;
use app\modules\doctor\models\Diagnostic;
use app\modules\doctor\models\Event;
use app\modules\doctor\models\Eventtype_action;
use app\modules\doctor\models\FormEvent;
use app\modules\doctor\models\FormListSearch;
use app\modules\doctor\models\Mkb_tree;
use app\modules\doctor\models\Orgstructure;
use app\modules\doctor\models\Person;
use app\modules\doctor\models\Rbdiagnosticresult;
use app\modules\doctor\models\Rbpost;
use app\modules\doctor\models\Rbresult;
use app\modules\doctor\models\SearchMonitor;
use app\modules\doctor\models\Smpevents;
use app\modules\doctor\models\SprStreet;
use app\modules\doctor\models\Visit;
use DirectoryIterator;
use SoapClient;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\helpers\Url;

class Ssmp11Controller extends Controller
{
//    /*Для портала врача*/
//    public $wsdl = 'http://10.0.1.179/EMK/PixService.svc?wsdl';
//    public $guid = '1BA2D440-509F-4FDA-8E24-249DE9E32E96';
//    public $idLPU = 'bf9c247f-810c-41c9-9d9f-a2a2e1d3ec86';
//    public $url_token = 'http://10.0.1.179/acs2/acs/connect/token';
//    /*Для портала врача*/

//    /**
//     * @var string Путь к файлам
//     */
//    private $path;
//
//    public function init()
//    {
//        parent::init();
//        $this->path = realpath(Yii::$app->basePath . '/update/');
//
//    }

    public function actionIndex(){
        $smpevents = Smpevents::find()->asArray()->where(['LIKE','isDone',0])->orderBy(['id'=>SORT_DESC])->all();
        if (empty($smpevents)){
            $array = $this->getError(0,'Ничего не найдено!');
            return $this->asJson($array);
        }
        $array = $this->getMessage(1,'Успех!',$smpevents);
        return $this->asJson($array);
    }

    public function actionGetSsmpList(){

        $callNumberId = Yii::$app->request->get('callNumberId');
        $date = Yii::$app->request->get('date');
        $fio = Yii::$app->request->get('fio');
        $status = (int)Yii::$app->request->get('status');
        if ($date==""){
            $date = date('d.m.Y');
        }

        if (isset($status) && $status != 0){
            $status = $status - 1;
        }else{
            $status = "";
        }


        $smpevents = Smpevents::find()
            ->asArray()
            ->where(['LIKE','callNumberId',$callNumberId])
            ->andFilterWhere(['LIKE','isDone',$status])
            ->andFilterWhere(['LIKE','callDate',Yii::$app->formatter->asDate($date, 'php:Y-m-d')])
            ->andFilterWhere(['LIKE','fio',$fio])
            ->orderBy(['id'=>SORT_DESC])
            ->all();
        if (empty($smpevents)){
            $array = $this->getError(0,'Ничего не найдено!');
            return $this->asJson($array);
        }
        $array = $this->getMessage(1,'Успех!',$smpevents);
        return $this->asJson($array);
    } // Получить вызовы

    public function actionGetCallInfo($callNumberId){
        $smpevents = Smpevents::find()->where(['LIKE','idCallNumber',$callNumberId])->orderBy(['id'=>SORT_DESC])->one();
        if (empty($smpevents)){
            $array = $this->getError(0,'Ничего не найдено!');
            return $this->asJson($array);
        }
        $result['lpuCode'] = $smpevents->OMS;
        $bsmp = Bsmp::find()->where(['idCallNumber'=>$smpevents->idCallNumber])->one();
        $result['eventId'] = $bsmp->id_bsmp;
        $result['eventTime'] = $smpevents->endReceivingCall;
        $result['callNumberId'] = $smpevents->idCallNumber;
        $result['callDate'] = $smpevents->createDateTime;
        $result['fio'] = $smpevents->lastName.' '.$smpevents->name.' '.$smpevents->patronymic;
        $result['sex'] = $smpevents->sex;
        $result['age'] = $smpevents->ageYears;
        $result['contact'] = $smpevents->telephone;
        $result['address'] = Smpevents::getAddress($smpevents);
        $result['landmarks'] = $smpevents->landmarks;
        $result['occasion'] = $smpevents->callOccasion;
        $result['callerName'] = $smpevents->callerName;
        $result['urgencyCategory'] = $smpevents->idUrgencyCategory;
        $result['callKind'] = $smpevents->idCallKind;
        $result['receiver'] = $smpevents->userReceiver;
        $result['result'] = $smpevents->result;
        $result['isDone'] = $smpevents->isDone;
        $result['status'] = $smpevents->status;
        $result['note'] = $smpevents->note;
        $result['active_person_id'] = $smpevents->active_person_id;
        $result['fullname_post'] = $smpevents->fullname_post;
        $result = (object)$result;

        $array = $this->getMessage(1,'Успех!',$result);
        return $this->asJson($array);
    } // Получить вызовы

    public function actionGetEventList(){

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

    public function actionUpdEvent(){
        date_default_timezone_set("Europe/Moscow");
        $WSDL_SSMP = Yii::$app->params['wsdl_ssmp'];
        $LPU = (int)Yii::$app->params['infisCode'];

        // Метод получает идентификатор вызова, на его основании получает массив и передает его интеграционной платформе ССМП
        $eventId = Yii::$app->request->get('eventId');

        $person_id = Yii::$app->request->get('person_id');
        $person = $person = Person::findOne($person_id);
        if (isset($person)){
            $fullName = $person->lastName . ' ' . $person->firstName . ' ' . $person->patrName;
        }else{
            $fullName = 'Планшет неотложная помощь';
        }

        $request = array(
            'lpuCode'=>$LPU,
            'id'=>$eventId, // id события на ССМП
            'eventTime'=>date('H:i:s'),
            'receivedUser'=>$fullName,
        );
        $client = new SoapClient($WSDL_SSMP, array("trace"=>1,'exceptions' => 0));

        $resoult = $client->updEvent($request);


        if($resoult->updEventResult == true){

            $smpevents = Smpevents::find()->where(['idCallNumber'=>Smpevents::getIdCallNumber($eventId)])->one();

            $smpevents->status = 1;
            if ($smpevents->save()){
                $smpevents = Smpevents::find()->where(['idCallNumber'=>Smpevents::getIdCallNumber($eventId)])->one();
                $array = $this->getMessage(1,"<strong>Успешно!</strong> Вызов <strong>$eventId</strong> принят!");
                return $this->asJson($array);
            }else{
                $array = $this->getMessage(0,"<strong>Ошибка!</strong> Вызов <strong>$eventId</strong> не принят! Сервер ССМП не принял ответ");
                return $this->asJson($array);
            }
        }else{
            $array = $this->getError(0,"<strong>Ошибка!</strong> Вызов <strong>$eventId</strong> не принят! Не получилось сохранить БД ответ");
            return $this->asJson($array);
        }




    } // Функция вызывается для передачи времени получения события в поликлинике и ФИО принявшего

    public function actionSetAppeal(){
        $person_id = Yii::$app->request->get('person_id');
        $client_id = Yii::$app->request->get('client_id');
        $action_id = Yii::$app->request->get('action_id');
        $mkb = Yii::$app->request->get('mkb');
        $orgstructure_id = Yii::$app->request->get('orgstructure_id');
        $json = json_decode(Yii::$app->request->get('json'));
        $setDate = date('Y-m-d H:i:s');

        $model = new FormEvent();
        $model->person_id = $person_id;
        $model->client_id = $client_id;
        $model->mkb = $mkb;
        $model->eventType = $orgstructure_id;
        $model->setDate = $setDate;
        $person = Person::findOne($person_id);
        $action = Action::findOne($action_id);


        $event = $this->actionInsertEvent($model,$person,$json);

        if ($event){
            if (isset($action)){
                $action->status=0;
                $action->save();
            } // изменить статус очереди
            $array = $this->getMessage(1,"<strong>Успешно!</strong> Обращение создано, номер - <strong>$event</strong>!");
        }else{
            $array = $this->getMessage(0,"<strong>Ошибка!</strong> Обращение не создано, попробуйте еще раз!");
        }

        return $this->asJson($array);

    }// Создать новое обращение

    public function actionAddEvent(){
        date_default_timezone_set("Europe/Moscow");
        $WSDL_SSMP = Yii::$app->params['wsdl_ssmp'];
        $LPU = (int)Yii::$app->params['infisCode'];
        $person_id = Yii::$app->request->get('person_id');
        $person = $person = Person::findOne($person_id);
        if (isset($person)){
            $fullName = $person->lastName . ' ' . $person->firstName . ' ' . $person->patrName;
        }else{
            $fullName = 'Планшет неотложная помощь';
        }

        // Метод получает идентификатор вызова, на его основании получает массив и передает его интеграционной платформе ССМП
        $callNumberId = (int)Yii::$app->request->get('callNumberId');
//        $callNumberId = 5800000000269785;
        $ssmpresoult = (int)Yii::$app->request->get('ssmpresoult'); // идентификатор события из метода getSprCallEventType с параметром eventAccess
        $ssmpresoult_text = Yii::$app->request->get('ssmpresoult_text'); // название опции
        $note = Yii::$app->request->get('note'); // комментарий

        if ($ssmpresoult == 1){
            $ssmpresoult = 3;
        }
        elseif ($ssmpresoult == 2){
            $ssmpresoult = 6;
        }
        elseif ($ssmpresoult == 3){
            $ssmpresoult = 8;
        }
        elseif ($ssmpresoult == 4){
            $ssmpresoult = 10;
        }
        else{
            $ssmpresoult = 6;
        }


        $request = array(
            'lpuCode'=>$LPU,
            'idCallNumber'=>$callNumberId,
            'idCallEventType'=>$ssmpresoult,
            'note'=>$note,
            'eventTime'=>date("H:i:s"), PHP_EOL,
            'transferUser'=>$fullName,
        );
        $client = new SoapClient($WSDL_SSMP, array("trace"=>1,'exceptions' => 0));
        $resoult = $client->addEvent($request);


        $local = Smpevents::addEventLocal($callNumberId,$ssmpresoult_text,$note,$ssmpresoult); // запись значения в базу, перенес в модель

        if ($local){
            $array = $this->getMessage(1,"<strong>Успешно!</strong> Результат вызова <strong>$callNumberId</strong> добавлен, статус <strong>$ssmpresoult_text</strong>!");
            return $this->asJson($array);
        }else{
            $array = $this->getMessage(0,"<strong>Ошибка!</strong> Вызов ССМП <strong>$callNumberId</strong> не принят! Не получилось сохранить БД ответ");
            return $this->asJson($array);
        }

    } // отметить что вызов(или событие) был принят в ССМП АльтСистем

    public function actionSearchClient(){
        $id = Yii::$app->request->get('id');
        $lastName = Yii::$app->request->get('lastName');
        $firstName = Yii::$app->request->get('firstName');
        $patrName = Yii::$app->request->get('patrName');
        $birthDate = Yii::$app->request->get('birthDate');
        $snils = Yii::$app->request->get('snils');


        if(!empty($birthDate)){
            $birthDate = Yii::$app->formatter->asDate($birthDate, 'php:Y-m-d');
        }
        if(!empty($snils)){
            $snils = preg_replace("/-/i", "", preg_replace("/ /i", "", $snils));
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
      c.id LIKE \'%'.$id.'%\'
  AND c.lastName LIKE \''.$lastName.'%\'
  AND c.firstName LIKE \''.$firstName.'%\'  
  AND c.patrName LIKE \''.$patrName.'%\'
  AND c.birthDate LIKE \'%'.$birthDate.'%\' 
  AND c.SNILS LIKE \'%'.$snils.'%\'
  GROUP BY c2.client_id
  ORDER BY  c.lastName ASC
  LIMIT 50
        ';

        $smpevents = Client::findbysql($query_pacients)->asArray()->all();


        if (empty($smpevents)){
            $array = $this->getError(0,'Ничего не найдено!');
            return $this->asJson($array);
        }
        $array = $this->getMessage(1,'Успех!',$smpevents);
        return $this->asJson($array);
    } // Поиск пациентов


    public function actionGetList(){
        $person_id = Yii::$app->request->get('person_id');
        $setDate = Yii::$app->request->get('setDate');


        if(!isset($setDate) || $setDate == '' || $setDate == null)
        {
            $setDate = date('Y-m-d');
        }


        $pacients = Action::find()
            ->where(['LIKE','directionDate', Yii::$app->formatter->asDate($setDate,'php:Y-m-d')])
            ->andWhere(['deleted' => 0])
            ->andWhere(['person_id' => $person_id])
            ->andWhere(['actionType_id' => 19])
            ->groupBy('event_id')
            ->all();


        foreach ($pacients as $key => $item){
            $array[$key]['action_id'] = $item->id;
            $array[$key]['client_id'] = $item->client->id;
            $array[$key]['status'] = $item->status;
            $array[$key]['directionDate'] = Yii::$app->formatter->asDate($item->directionDate,'php:H.i');
            $array[$key]['fullName'] = $item->client->lastName.' '.$item->client->firstName.' '.$item->client->patrName;
            $array[$key]['birthDate'] = Yii::$app->formatter->asDate($item->client->birthDate,'php:d.m.Y');
            if($item->client->sex == 1){
                $array[$key]['sex'] = 'M';
            }else{
                $array[$key]['sex'] = 'Ж';
            }
            $client = new \app\modules\doctor\models\Client();
            $client->getInfo($item->client->id);
            $array[$key]['registration'] = $client->registration;
            $array[$key]['residence'] = $client->residence;
            $array[$key]['contact'] = $client->contact;
            $array[$key]['snils'] = $item->client->SNILS;
        }
        if (isset($array)){
            $array = $this->getMessage(1,'Успех!',$array);
            return $this->asJson($array);
        }else{
            $array = $this->getError(0,'На этот день записанных пациентов нет!');
            return $this->asJson($array);
        }


    } // Получить список пациентов на указанную дату

    public function actionGetListAndSsmp(){
        $person_id = Yii::$app->request->get('person_id');
        $setDate = Yii::$app->request->get('setDate');


        $person = Person::findOne($person_id);
//        debug($person_id);
//        die();



        if(!isset($setDate) || $setDate == '' || $setDate == null)
        {
            $setDate = date('Y-m-d');
        }

        $pacients = Action::find()
            ->where(['LIKE','directionDate', Yii::$app->formatter->asDate($setDate,'php:Y-m-d')])
            ->andWhere(['deleted' => 0])
            ->andWhere(['person_id' => $person_id])
            ->andWhere(['actionType_id' => 19])
            ->groupBy('event_id')
            ->all();


        $smpevents = Smpevents::find()
            ->andFilterWhere(['LIKE','createDateTime',Yii::$app->formatter->asDate($setDate, 'php:Y-m-d')])
            ->orderBy(['id'=>SORT_DESC])
            ->all(); // получить вызовы скорой

        if (isset($smpevents)){
            foreach ($smpevents as $key => $item){
                $smpevents_array[$key]['action_id'] = null;
                $smpevents_array[$key]['client_id'] = null;
                $bsmp = Bsmp::find()->where(['idCallNumber'=>$item->idCallNumber])->one();
                $smpevents_array[$key]['eventId'] = $bsmp->id_bsmp;
                $smpevents_array[$key]['callNumberId'] = $item->idCallNumber;
                $smpevents_array[$key]['status'] = $item->status;
                $smpevents_array[$key]['isDone'] = $item->isDone;
                $smpevents_array[$key]['directionDate'] = Yii::$app->formatter->asDate($item->endReceivingCall,"php:H:i");;
                $smpevents_array[$key]['fullName'] = Smpevents::getFullName($item);
                $smpevents_array[$key]['birthDate'] = null;
                if((int)$item->sex == 1){
                    $smpevents_array[$key]['sex'] = 'M';
                }else{
                    $smpevents_array[$key]['sex'] = 'Ж';
                }
                $smpevents_array[$key]['registration'] = null;
                $smpevents_array[$key]['residence'] = $item->Settlement;
                $smpevents_array[$key]['residence'] = Smpevents::getAddress($item);;



                $smpevents_array[$key]['contact'] = $item->telephone;
                $smpevents_array[$key]['snils'] = null;
                $smpevents_array[$key]['active_person_id'] = $item->active_person_id;
            } // Изменить массив со скорой, добавить поля для того чтобы java смогла их распарсить
        }
//        debug($smpevents_array);
//        die();
        if (isset($pacients)){
            foreach ($pacients as $key => $item){
                $pacients_array[$key]['action_id'] = $item->id;
                $pacients_array[$key]['client_id'] = $item->client->id;
                $pacients_array[$key]['eventId'] = null;
                $pacients_array[$key]['callNumberId'] = null;
                $pacients_array[$key]['status'] = $item->status;
                $pacients_array[$key]['isDone'] = null;
                $pacients_array[$key]['directionDate'] = Yii::$app->formatter->asDate($item->directionDate,'php:H.i');
                $pacients_array[$key]['fullName'] = $item->client->lastName.' '.$item->client->firstName.' '.$item->client->patrName;
                $pacients_array[$key]['birthDate'] = Yii::$app->formatter->asDate($item->client->birthDate,'php:d.m.Y');
                if($item->client->sex == 1){
                    $pacients_array[$key]['sex'] = 'M';
                }else{
                    $pacients_array[$key]['sex'] = 'Ж';
                }
                $client = new \app\modules\doctor\models\Client();
                $client->getInfo($item->client->id);
                $pacients_array[$key]['registration'] = $client->registration;
                $pacients_array[$key]['residence'] = $client->residence;
                $pacients_array[$key]['contact'] = $client->contact;
                $pacients_array[$key]['snils'] = $item->client->SNILS;
                $pacients_array[$key]['active_person_id'] = null;
            } // добавить неостающие поля в массив с очередью для того чтобы java смогла их распарсить
        }
        if (isset($pacients_array) && isset($smpevents_array)){
            $array_all = ArrayHelper::merge($smpevents_array, $pacients_array); // объединить массивы
        }elseif (isset($pacients_array) && !isset($smpevents_array)){
            $array_all = $pacients_array;
        }elseif (!isset($pacients_array) && isset($smpevents_array)){
            $array_all = $smpevents_array;
        }
        ArrayHelper::multisort($array_all, ['directionDate'], [SORT_ASC]); // Сортировать объединенный массив по времени

        if (isset($array_all)){
            $array = $this->getMessage(1,'Успех!',$array_all);
            return $this->asJson($array);
        }else{
            $array = $this->getError(0,'На этот день записанных пациентов нет!');
            return $this->asJson($array);
        }

    } // Получить список пациентов на указанную дату и список скорой и объединить в один массив

    public function actionGetDiary(){
        $person_id = (int)Yii::$app->request->get('person_id');
        $client_id = (int)Yii::$app->request->get('client_id');
        $mkb = Yii::$app->request->get('mkb');
        $diary_name = Yii::$app->params['diary'];
        $person = Person::find()->where(['id'=>$person_id])->one();
        $eventType = $this->actionGetEventType($person->orgstructure->id); // тип действия

        if ($eventType === false){
            $array = $this->getError(0,"<p><strong>Ошибка!</strong> Вы авторизованы под пользователем, который не прикреплен ни к одному подразделению! Дальнейшая работа невозможна</p>");
            return $this->asJson($array);
        }

        $dnevnic = \app\modules\doctor\models\ActionType::find()->where(['name'=>$diary_name,'deleted'=>0])->one();

        $dinamicform = \app\modules\doctor\models\ActionPropertyType::find()->where(['actionType_id'=>$dnevnic->id,'deleted'=>0])->all();
        $diary = array();
        $diary['eventType']['id'] = $eventType['id'];
        $diary['eventType']['name'] = $eventType['name'];
        if (empty($dnevnic)){
            $array = $diary;
            $array['status'] = 0;
            $array = $this->getMessage(1,"<p><strong>Внимание!</strong> Дневник не найден! Проверьте корректность наименования дневника в настройках программы.</p>",$array);
            return $this->asJson($array);
        }else{
            $diary['status'] = 1;
            foreach ($dinamicform as $key => $item){
                $diary['diary'][$key]['id'] = $item->id;
                $diary['diary'][$key]['label'] = $item->name;
                $diary['diary'][$key]['typeName'] = $item->typeName;
            }
            $array = $this->getMessage(1,'Успех!',$diary);
            return $this->asJson($array);
        }



    }

    public function actionGetEventType($orgstructure_id){
        if ($orgstructure_id == false){
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
        return $array[0];
    } // получить тип события

    /*Цепочка для создания обращения*/

    public function actionInsertEvent($model,$person,$dnevnic){
        $client = Client::findOne($model->client_id);
        $doc = new \ArrayObject($this->actionGetPatient($model->client_id));
        $rbresult = Rbresult::find()->where(['code'=>304])->one(); // результат лечения
        $contract = $this->getContract($model,$doc);



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
        $event->isPrimary = 3; // Признак первичности (1-первичный, 2-повторный, 3-активное посещение, 4-перевозка)
        $event->order = 5; // Порядок наступления (1-плановый, 2-экстренный, 3-самотёком, 4-принудительный, 5-неотложный)
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
        $event->clientPolicy_id = $doc['polis']['id']; // Полис пациента для данного случая {ClientPolicy}
        $event->littleStranger_id = NULL; //
        $event->referral_id = NULL; //
        $event->armyReferral_id = NULL; //
        $event->goal_id = NULL; // Цель
        $event->outgoingOrg_id = NULL; //
        $event->outgoingRefNumber = '(пусто)'; //
        $event->hmpKind_id = NULL; //
        $event->hmpMethod_id = NULL; //
        $event->eventCostPrinted = 0; //
        $event->exposeConfirmed = 0; // Добавлять ли событие к выставлению в счет (имеет значение только для событий, в типе которых exposeConfirmation = 1)
        $event->ZNOFirst = 0; // ЗНО установлен впервые
        $event->ZNOMorph = 0; // ЗНО подтверждено морфологически
//        $event->hospParent = 0; // Госпитализация с родителем/представителем
//        $event->cycleDay = 0; // День цикла (для беременных) [i2582]
//        $event->locked = 0; // Обращение заблокировано для редактирования
//        $event->dispByMobileTeam = 0; // Флаг "Диспансеризация(проф.осмотр) проведена мобильной выездной бригадой"
//        $event->duration = NULL; // DEFAULT NULL COMMENT 'Длительность лечения',
//        $event->orgStructure_id = NULL /*$model->orgstructure*/; // DEFAULT NULL COMMENT 'Подразделение {OrgStructure}',
//        $event->MSE = NULL; // DEFAULT 0 COMMENT 'Флаг "Передано направление на МСЭ в бюро МСЭ"',
//        $event->RKEY = NULL; // DEFAULT NULL,
//        $event->signedDocuments = 0; // tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Отметка об успешном подписании документа',
//        $event->signDateTime = NULL; // Дата подписи
        $event->note = 'Запись через планшет неотложной помощи';

        if($event->save()){
            $action = $this->actionInsertAction($model,$person,$event);
            $diagnosis = $this->actionInsertDiagnosis($model,$person,$client);
            $diagnostic = $this->actionInsertDiagnostic($model,$person,$event,$diagnosis);
            $visit = $this->actionInstartVisit($model,$person,$event,$action);
            if ($dnevnic){
                $diary = Yii::$app->params['diary'];
                $actionType = \app\modules\doctor\models\ActionType::find()->where(['name'=>$diary,'deleted'=>0])->one(); // дневник
                $action = $this->actionInsertAction($model,$person,$event,$actionType); // добавляем дневник в action
                $this->actionInsertActionProperty($model,$person,$action,$dnevnic); // добавляем значения в дневник
            } // если есть заполненный дневник
            return $event->id;
        }

    } // Добавить событие / создать обращение

    public function actionInsertAction($model,$person,$event,$actionType = null){
        $eventtype_action = Eventtype_action::find()->where(['eventType_id'=>$model->eventType])->andWhere(['speciality_id'=>$person->speciality_id])->one();
//        debug($eventtype_action->actionType->code);
//        die();
        $actionTypeCode = $eventtype_action->actionType->code;
        $query = "
        SELECT
  MAX(at.id) as id
FROM actiontype at
  LEFT JOIN actiontype_service asr
    ON asr.master_id = at.id
  LEFT JOIN rbservice rs
    ON rs.id = asr.service_id
WHERE rs.code = '$actionTypeCode'
AND rs.begdate <> rs.enddate
AND at.deleted = 0
        ";

        $query_result = Yii::$app->db->createCommand($query)->queryAll();
        $actionType_id = $query_result[0]['id'];
//        debug($query_result[0]['id']);
//        die();

//        if(isset($phone[0])){
//            $result['contact'] = $phone[0];
//        }else{
//            $result['contact']['ContactValue'] = 'Нет номера';
//        }
        if(isset($actionType)){
            $eventtype_action->actionType_id = $actionType->id;
        }
        $action = new Action();
        $action->createDatetime = $model->setDate;
        $action->createPerson_id = $person->id;
        $action->modifyDatetime = date('Y-m-d H:i:s');
        $action->endDate = date('Y-m-d H:i:s');
        $action->modifyPerson_id = $person->id;
//        $action->actionType_id = $eventtype_action->actionType_id;
        $action->actionType_id = $actionType_id;
        $action->event_id = $event->id;
        $action->directionDate = date('Y-m-d H:i:s');;
        $action->status = 2;
        $action->setPerson_id = $person->id;
        $action->begDate = date('Y-m-d H:i:s');
        $action->note = 'Запись через планшет неотложной помощи';
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
//        $rbdiagnosticresult = Rbdiagnosticresult::find()
//            ->where(['code'=>304])
//            ->andWhere(['filterResults'=>'true'])
//            ->one();
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
        $diagnostic->result_id = 67;
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
            $actionproperty->type_id = $item->id;
            $actionproperty->norm = '';
            $actionproperty->isAssigned = 0;
            $actionproperty->save();
            if (isset($item->content) && $item->content != ""){
                $ActionProperty_String = new ActionProperty_String();
                $ActionProperty_String->id = $actionproperty->id;
                $ActionProperty_String->index = 0;
                $ActionProperty_String->value = $item->content;
                $ActionProperty_String->save(); // сохраняемзначения дневника
            } // если есть хоть какое то значение в ячейке дневника
        }

    } // делаем связь конкретного дневника (action_id) и его свойств значений(type_id -> ActionPropertyType)

    public function getContract($model,$doc){
        $insurer_id = ArrayHelper::getValue($doc, 'polis.insurer_id');
        if (empty($insurer_id)){
            $insurer_id = 'null';
        }
        $contract_query = "
        SELECT
  Contract.`id`,
  Contract.`number`,
  Contract.`date`,
  Contract.`resolution`,
  rbFinance.`code` AS `finance`
FROM Contract
  LEFT JOIN rbFinance
    ON rbFinance.`id` = Contract.`finance_id`
WHERE ((EXISTS (SELECT
    *
  FROM Contract_Specification
  WHERE ((((Contract_Specification.`master_id` = Contract.`id`)
  AND (Contract_Specification.`deleted` = 0)))
  AND (Contract_Specification.`eventType_id` = $model->eventType))))
AND (Contract.`finance_id` = 2)
AND (((NOT EXISTS (SELECT
    *
  FROM Contract_Contingent
  WHERE ((Contract_Contingent.`master_id` = Contract.`id`)
  AND (Contract_Contingent.`deleted` = 0))))
OR (EXISTS (SELECT
    *
  FROM Contract_Contingent
  WHERE ((((Contract_Contingent.`master_id` = Contract.`id`)
  AND (Contract_Contingent.`deleted` = 0)))
  AND (Contract_Contingent.`sex` IN (0, 1))
  AND (((((((Contract_Contingent.`insurer_id` = $insurer_id)
  OR (Contract_Contingent.`insurer_id` IS NULL)))
  AND (insurerServiceAreaMatch($insurer_id, Contract_Contingent.serviceArea, '2302300000100', '2300000000000'))
  AND (((Contract_Contingent.`policyType_id` = 1)
  OR (Contract_Contingent.`policyType_id` IS NULL))))))))))))
AND (((Contract.`maxClients` = 0)
OR (Contract.maxClients > (SELECT
    COUNT(DISTINCT Event.client_id)
  FROM Event
  WHERE Event.contract_id = Contract.id
  AND Event.deleted = 0)))))
ORDER BY rbFinance.`idx`, rbFinance.`code` DESC, Contract.`number`, Contract.`date`, Contract.`resolution`;
        ";
        $contract = Yii::$app->db->createCommand($contract_query)->queryAll();
        $contract_id = $contract[0];
        return $contract_id;
    } // получить идентификатор контракта

    /*Цепочка для создания обращения*/

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
        $query_polis = 'SELECT ClientPolicy.id,clientpolicy.insurer_id, clientpolicy.number AS DocN, clientpolicy.serial AS DocS, rbpolicykind.federalCode AS Code,   
\'urn:oid:1.2.643.2.69.1.1.1.59\' AS `spr`, clientpolicy.begDate AS IssuedDate, 
rbpolicykind.name AS `polis`,  rbpolicykind.code AS `code`,  rbpolicykind.federalCode AS `federalCode`
                        FROM ClientPolicy  INNER JOIN rbpolicykind ON clientpolicy.policyKind_id = rbpolicykind.id WHERE ((ClientPolicy.`client_id` = '.$id.')
                        AND (ClientPolicy.`deleted` = 0))
                        ORDER BY ClientPolicy.id DESC LIMIT 0, 1;';
//        debug($query_polis);
//        die();
        $polis = Yii::$app->db->createCommand($query_polis)->queryAll();
        $result['polis'] = $polis[0];


        $query_doc = 'SELECT documentType_id AS "netrica_Code", -- тип документа
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


    /*Для  портала врача*/
    public function actionGetPortal()
    {
        $client_id = Yii::$app->request->get('client_id');
        $person_id = Yii::$app->request->get('person_id');
        $wsdl = Yii::$app->params['wsdl_portal'];
        $guid = Yii::$app->params['guid'];
        $idLPU = Yii::$app->params['idLPU'];

        $client = new SoapClient($wsdl, array("trace"=>1));

        $request = array(
            'guid'=>$guid,
            'idLPU'=>$idLPU,
            'patient'=>array(
                'IdPatientMIS' => $client_id
//                'IdPatientMIS' => 3647128 // Григорович
//                'IdPatientMIS' => 5026467 // Ассаметс
//                'IdPatientMIS' => 879378 // Губин
//                'IdPatientMIS' => 2315336 // Беляков
            ),
            'idSource' =>'Reg'
        );

        $getPatient = json_decode(json_encode($client->GetPatient($request)),1);
//        debug($getPatient);
//        die();
        if (empty($getPatient['GetPatientResult'])){
            $array = $this->getError(0,'Этот пациент не найден на портале врача!');
            return $this->asJson($array);
        }

        if (!isset($getPatient['GetPatientResult']['PatientDto']['IdGlobal'])){
            foreach ($getPatient['GetPatientResult']['PatientDto'] as $item){
                $idGlobal = $item['IdGlobal'];
            }
        }else{
            $idGlobal = $getPatient['GetPatientResult']['PatientDto']['IdGlobal'];
        }
//        debug($idGlobal);
//        die();
        $token = $this->getToken($idGlobal,$person_id);
        $url = "http://10.0.1.91/EMKUI/Patient/$idGlobal/Encounters?access_token=$token";

        $array = $this->getMessage(1,'Успех!',$url);
        return $this->asJson($array);
    } // Генерация URL

    function getToken($idGlobal,$person_id){
        $person = $person = Person::findOne($person_id);

        $lastname = $person->lastName;
        $firstname = $person->firstName;
        $partname = $person->patrName;
        $snils = $person->SNILS;

        $url_token = Yii::$app->params['url_token'];

        $xml =<<<XML
<xacml-samlp:XACMLAuthzDecisionQuery xmlns:saml2p="urn:oasis:names:tc:SAML:2.0:protocol"
                                     xmlns:saml2="urn:oasis:names:tc:SAML:2.0:assertion"
                                     xmlns:xacml-samlp="urn:oasis:names:tc:xacml:3.0:profile:saml2.0:v2:schema:protocol:wd-14"
                                     xmlns:xacml-context="urn:oasis:names:tc:xacml:3.0:core:schema:wd-17"
                                     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                     ID="_fc31b400-e529-4ac0-a616-10f1e17c5b8b" Version="2.0"
                                     IssueInstant="2017-04-19T15:54:55.1061156Z"
                                     xsi:schemaLocation=" urn:oasis:names:tc:xacml:3.0:profile:saml2.0:v2:schema:protocol:wd-14 http://login-test.zdrav.netrika.ru:8090/xacml-3.0-profile-saml2.0-v2-schema-protocol-wd-14.xsd n3-healthcare-2018-06-21.xsd">
    <xacml-context:Request xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                           xmlns="urn:oasis:names:tc:xacml:3.0:core:schema:wd-17"
                           xmlns:n3="urn:netrika.ru:healthcare:n3:2018-06-21"
                           xsi:schemaLocation="urn:oasis:names:tc:xacml:3.0:core:schema:wd-17 http://docs.oasis-open.org/xacml/3.0/xacml-core-v3-schema-wd-17.xsd n3-healthcare-2018-06-21.xsd"
                           ReturnPolicyIdList="false" CombinedDecision="false">
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:access-subject">
            <xacml-context:Content>
                <n3:Identifier тип="медицинский работник">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.1.1.84">
                        <n3:СНИЛС номер="$snils"/>
                        <n3:ФИО фамилия="$lastname" имя="$firstname" отчество="$partname"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:recipient-subject">
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:intermediary-subject">
            <xacml-context:Content>
                <n3:Identifier тип="медицинская организация">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.1.1.64">
                        <n3:Организация guid="bf9c247f-810c-41c9-9d9f-a2a2e1d3ec86"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:codebase">
            <xacml-context:Content>
                <n3:Identifier тип="медицинская информационная система">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.2">
                        <n3:ИнформационнаяСистема oid="urn:oid:1.2.643.2.69.1.2.1"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:requesting-machine">
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:3.0:attribute-category:resource">
            <xacml-context:Content>
                <n3:Identifier тип="пациент">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.1.4">
                        <n3:IdGlobal value="$idGlobal"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:3.0:attribute-category:action">
            <xacml-context:Content>
                <n3:Identifier тип="действие">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.1.4">
                        <n3:Метод имя="читать"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:3.0:attribute-category:environment">
        </xacml-context:Attributes>
    </xacml-context:Request>
</xacml-samlp:XACMLAuthzDecisionQuery>
XML;
        $text = base64_encode($xml);
        $her = urlencode(mb_convert_encoding($text,'UTF-8'));
        $req_template = "grant_type=urn:ietf:params:oauth:client-assertion-type:saml2-bearer&assertion=".$her."&scope=iemk_portal+openid";



        $client = new \yii\httpclient\Client(
            ['baseUrl' => $url_token]
        );
        $response = $client->createRequest()
            ->setMethod('POST')
//            ->setUrl('/connect/token')
            ->setFormat(yii\httpclient\Client::FORMAT_JSON)
            ->addHeaders([
                'content-type' => 'application/x-www-form-urlencoded',
                'accept' => 'application/json',
                'authorization' => 'Basic VmlzdGFNZWQ6VldrWkszMGE='
            ])
            ->setContent($req_template)
            ->send();
        if ($response->isOk) {
            return $response->data['access_token'];
        }else{
            return $response->data['access_token'] = 'I don\'t get token' ;
        }


        /*
                //процедурный стиль, тупо php
                $opts = array(
                    'http' =>
                        array(
                            'method'  => 'POST',
                            'header'  =>
                                "content-Type: application/x-www-form-urlencoded\r\n".
                                "accept: application/json\r\n".
                                "authorization: Basic VmlzdGFNZWQ6VldrWkszMGE=\r\n",
                            'content' => $req_template
                        )
                );

                $context  = stream_context_create($opts);
                $result = file_get_contents($this->url_token, false, $context);

                print_r($result);
                die();

        */
    } // посылаем get запрос после успешного получения
    /*Для  портала врача*/

    public function actionAuth(){
        $login = Yii::$app->request->get('login');
        $password = Yii::$app->request->get('password');
        $person = Person::find()->select('id, login, lastName, firstName, patrName, SNILS')->where(['login' => $login,'password'=>$password])->one();
        if ($person){
            $array = $this->getMessage(1,'Успех!',$person);
            return $this->asJson($array);
        }else{
            $array = $this->getError(0,'Неверный логин/пароль!');
            return $this->asJson($array);
        }
    }

    /**Получить отчет*/
    public function actionGetReports(){
        $person_id = Yii::$app->request->get('person_id');
        $date_one = Yii::$app->request->get('date_one');
        $date_two = Yii::$app->request->get('date_two');
        $type = Yii::$app->request->get('type');

        if (isset($date_one)){
            $date_one = Yii::$app->formatter->asDate($date_one,'php:Y-m-d');
        }else{
            $array = $this->getError(0,'Вы не указазали дату начала!');
            return $this->asJson($array);
        }
        if (isset($date_two)){
            $date_two = Yii::$app->formatter->asDate($date_two,'php:Y-m-d');
        }else{
            $array = $this->getError(0,'Вы не указазали дату окончания!');
            return $this->asJson($array);
        }
        $person = Person::find()->where(['id'=>$person_id])->one();

        $eventType = $this->actionGetEventType($person->orgstructure->id); // тип действия

        if ((int)$type == 1){
            /** @var $event созданные обращения  */
            $event = Event::find()
                ->where(['eventType_id' => $eventType['id']])
                ->andWhere(['setPerson_id' => $person_id])
                ->andWhere(['deleted' => 0])
                ->andWhere(['between', 'createDatetime', $date_one, $date_two])
                ->count();
        }else{
            $event = "Возможно определить только для текущего врача";
        }

        /*Всего записанных пациентов на дом*/
        $action = Action::find()
            ->where(['actionType_id' => 19])
            ->andWhere(['person_id' => $person_id])
            ->andWhere(['deleted' => 0])
            ->andWhere(['between', 'directionDate', $date_one, $date_two])
            ->groupBy('event_id')
            ->count();

        /* Всего переданных вызовов*/
        $smpevents_all = Smpevents::find()
            ->where(['between', 'createDateTime', $date_one, $date_two])
            ->count(); // получить вызовы скорой

        /* Всего переданных вызовов принятых*/
        $smpevents_status = Smpevents::find()
            ->where(['status' => 1])
            ->andWhere(['between', 'createDateTime', $date_one, $date_two])
            ->count(); // получить вызовы скорой

        /* Всего переданных вызовов закрытых - обслуженных вызовов*/
        $smpevents_isDone = Smpevents::find()
            ->where(['isDone' => 1])
            ->andWhere(['between', 'createDateTime', $date_one, $date_two])
            ->count(); // получить вызовы скорой

        $eventType_name = $eventType['name'];
        $result = "
        <p><strong>Отчет по созданным обращениям на дом и переданным вызовам ССМП в неотложную помощь</strong></p>
        <p><strong>Врач:</strong> $person->lastName $person->firstName $person->patrName</p>
        <p><strong>Тип услуги:</strong> $eventType_name</p>
        <p><strong>Кол-во записанных пациентов на дом:</strong> $action</p>
        <p><strong>Кол-во созданных обращений:</strong> $event</p>
        <hr>
        <p><strong>Кол-во переданных вызовов ССМП:</strong> $smpevents_all</p>
        <p><strong>Кол-во принятых вызовов ССМП:</strong> $smpevents_status</p>
        <p><strong>Кол-во обслуженных вызовов ССМП:</strong> $smpevents_isDone</p>
        ";



//        return $this->asJson($result);
        $array = $this->getMessage(1,"Успех!",$result);
        return $this->asJson($array);

    }


    /*получить МКБ*/
    public function actionGetMkb(){
        $mkb = Mkb_tree::find()->select('id,DiagID, DiagName')->all();

        $str = "";
        if (!empty($mkb)){
            foreach ($mkb as $item){
                $str .= $item->DiagID . ' | ' . $item->DiagName . ", ";
            }
        }
        if (!empty($mkb)){
            $array = $this->getMessage(1,'Успех!',$str);
            return $this->asJson($array);
        }else{
            $array = $this->getError(0,'Не удалось получить МБК, запрос не выполнен!');
            return $this->asJson($array);
        }
    }


    public function getError($code,$message){
        $arr['status'] = $code;
        $arr['message'] = $message;
        return $arr;
    } // Вернуть сообщение об ошибке

    public function getMessage($code,$message,$array = null){
        $arr['status'] = $code;
        $arr['message'] = $message;
        $arr['result'] = $array;
        return $arr;
    }// Вернуть сообщение

    public function actionDownload($name) {
        $path = Yii::getAlias('@app');
        $file = $path.'/update/'.$name;
        if (file_exists($file)) {
            return Yii::$app->response->sendFile($file);
        }
        throw new \Exception('File not found');
    } // Вернуть файл на скачивание

    public function actionAddGoingPerson($person_id,$eventId){
        $smpevent = Smpevents::find()->where(['idCallNumber' => Smpevents::getIdCallNumber($eventId)])->one();
        $person = Person::find()
            ->where(['person.id' => $person_id])
            ->with(['rbpost'])
            ->one();
        $smpevent->active_person_id = $person_id;
        $smpevent->fullname_post = "$person->lastName $person->firstName $person->patrName " . $person->rbpost->name;

        if ($smpevent->save()) {
            $array = $this->getMessage(1,'Успех! Вызов номер <b>' .$eventId.'</b> обслуживает: <b>'.$smpevent->fullname_post.'</b>');
            return $this->asJson($array);
        }else{
            $array = $this->getError(0,'Запрос не выполнен!');
            return $this->asJson($array);
        }
    } // Забронировать вызов

    public function actionDeleteGoingPerson($person_id,$eventId){
        $smpevent = Smpevents::find()->where(['idCallNumber' => Smpevents::getIdCallNumber($eventId)])->one();

        if ($person_id == $smpevent->fullname_post){
            $array = $this->getError(0,'Отменить выезд может только врач который его забронировал!');
            return $this->asJson($array);
        }
        $smpevent->active_person_id = null;
        $smpevent->fullname_post = null;

        if ($smpevent->save()) {
            $array = $this->getMessage(1,'Успех! Вызов <b>' .$eventId.'</b> разблокирован для выезда!');
            return $this->asJson($array);
        }else{
            $array = $this->getError(0,'Запрос не выполнен!');
            return $this->asJson($array);
        }
    } // Разблокировать вызов
//
//    public function actionDownload() {
//        $files = array();
//
//        foreach (new DirectoryIterator($this->path) as $item) {
//            array_push($files, $item->getFilename());
//        }
//
//        return $this->render('files', array(
//            'files' => $files
//        ));
//    } // Отобразить список
//
//    public function actionDownloadFile($name) {
//        Url::to(['download','name'=> $name]);
//    } // Вернуть файл на скачивание


}