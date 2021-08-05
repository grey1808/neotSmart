<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;
use function DeepCopy\deep_copy;

class Client extends ActiveRecord
{

    public $residence; // Адрес проживания
    public $registration; // Адрес регистрации
    public $contact; // Контактный номер

    public static function tableName()
    {
        return 'client';
    }

    public function getClientdocument(){
        return $this->hasOne(Clientdocument::className(),['id'=>'client_id']);
    }

    public function getClientcontact(){
        return $this->hasOne(Clientcontact::className(), ['id' => 'client_id']);
    }

    public function getPolis($client_id){

    }

    public function getInfo($client_id)
    {

        $this->residence = $this->getAddress($client_id,0); // Проживание
        $this->registration = $this->getAddress($client_id,1); // Регистрация

        $clientcontact = \app\modules\doctor\models\Clientcontact::find()
            ->where(['client_id' => $client_id])
            ->one();

        $this->contact = $clientcontact->contact; // получаем телевон, пока что один, возможно,е сли телевонов будет несколько, то это нужн изменить через цикл


    } // Проживание, регистрация пациента

    public function getAddress($client_id,$type){
        $clientaddress = \app\modules\doctor\models\Clientaddresess::find()
            ->where(['client_id' => $client_id])
            ->andWhere(['deleted' => 0])
            ->andWhere(['type' => $type]) // 0 регистрация, 1 проживание
            ->one();

        $address = \app\modules\doctor\models\Address::find()
            ->where(['id'=>$clientaddress->address_id])
            ->one();

        $addresshouse = \app\modules\doctor\models\Addresshouse::find()
            ->where(['id'=>$address->house_id])
            ->one();
        $kladr = \app\modules\doctor\models\Kladr::find()
            ->where(['code' => $addresshouse->KLADRCode])
            ->one();
        $street = \app\modules\doctor\models\Street::find()
            ->where(['CODE'=>$addresshouse->KLADRStreetCode])
            ->one();

        return $kladr->SOCR.' '. $kladr->NAME.' '.$street->SOCR.' '.$street->NAME.' '.$addresshouse->number.' '.$addresshouse->corpus;


    } // получить адрес регистрации или проживания
}