<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;
use Yii;

class Smpevents extends ActiveRecord
{

    public static function tableName()
    {
        return 'bsmpcallinfo';
    }


    public function rules()
    {
        return [
            // username and password are both required
            [['idCallNumber','status','isDone','active_person_id'],'integer'],
            ['createDateTime','required'],
            ['createDateTime','safe'],
            [['fullname_post'], 'string'],
            ['callOccasion', 'string'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'idCallNumber' => 'Номер вызова',
            'createDateTime' => 'Дата',
            'callOccasion' => 'Причина вызова',
            'isDone' => 'Законченные события',
        ];
    }

    public function search(){
        $smpevents = Smpevents::find()
            ->where(['LIKE','idCallNumber',$this->callNumberId])
            ->andWhere(['LIKE','createDateTime',Yii::$app->formatter->asDate($this->createDateTime, 'php:Y-m-d')])
            ->andWhere(['LIKE','callOccasion',$this->callOccasion])
            ->andWhere(['LIKE','isDone',$this->isDone])
            ->asArray()
            ->orderBy(['id'=>SORT_DESC])
            ->all();
        return $smpevents;
    }
    /*
 * Этот метод записывает результат вызова в базу и сразу его закрывает
 *
 * */
    public static function addEventLocal($callNumberId,$ssmpresoult_text,$note = null,$ssmpresoult){
        $smpevents = Smpevents::find()->where(['idCallNumber'=>$callNumberId])->one();
        $smpevents->result = $ssmpresoult_text; // наименование события
        $smpevents->note = $note; // комментарий

        /*
         * Если статус вызова ($ssmpresoult) = 6 вызов выполнен, 7 вызов передан на "03", 8 вызов безрезультатный (снят в ПНМП),
         * то закрываем вызов, если нет, то просто добавляем событие
         * */
        if($ssmpresoult == 3 || $ssmpresoult == 6 || $ssmpresoult == 8 || $ssmpresoult == 10){
            $smpevents->isDone = 1;
        }else{
            $smpevents->isDone = 0;
        }
        if ($smpevents->save()){
            return true;
        }else{
            return false;
        }
    } // Записывает в БД результат вызова Добавить событие к вызову

    // Получить адрес
    public function getAddress($item){
        if ($item->Settlement == 'None'){
            $result = 'Населенный пункт не указан';
        }else{
            $result = $item->Settlement;
        }
        if ($item->idStreet == 'None'){
            $result .= 'улица не указанна';
        }else{
            $result .= ' ' . $item->idStreet;
        }
        if ($item->houseFract == 'Не указано'){
            $result .= '';
        }else{
            $result .= ' дробь номера дома - ' . $item->houseFract;
        }

        if ($item->floor == '-1'){
            $result .= '';
        }else{
            $result .= ' этаж ' . $item->floor;
        }

        if ($item->flat == 'Не указано'){
            $result .= '';
        }else{
            $result .= ' квартира ' . $item->flat;
        }
        return $result;
    }

    // получить идентификатор
    public function getIdCallNumber($eventId){
        $bsmp = Bsmp::find()->where(['id_bsmp'=>$eventId])->one();
        return $bsmp->idCallNumber;
    }

    // Получить полное имя
    public function getFullName($item){
        if ($item->patronymic == "None")
            $item->patronymic = '';
        return $item->lastName.' '.$item->name.' '.$item->patronymic;
    }
}