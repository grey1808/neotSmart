<?php

namespace app\modules\doctor\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;

class FormEvent extends \yii\base\Model
{


    public $person_id;
    public $client_id;
    public $mkb;
    public $orgstructure;
    public $region;
    public $eventType;
    public $setDate;


    public function rules()
    {
        return [

            [['mkb','person_id','client_id','region','eventType','setDate'], 'required'],
            [['person_id','client_id','region','eventType'], 'integer'],
            [['orgstructure'], 'required', 'message' => 'Вы не прикреплены ни к какому подразделению, авторизуйтесь под другим пользователем или обратитесь к своему администратору'],
            [['mkb','orgstructure'], 'string'],
            [['setDate'], 'safe'],
//            [['name''], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'person_id' => 'Идентификатор врача',
            'client_id' => 'Идентификатор пациента',
            'mkb' => 'МКБ',
            'orgstructure' => 'Подразделение',
            'region' => 'Регион',
            'eventType' => 'Тип события',
            'setDate' => 'Дата начала',
        ];
    }


}