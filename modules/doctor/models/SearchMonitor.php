<?php

/**
 * Created by PhpStorm.
 * User: Belya
 * Date: 16.03.2019
 * Time: 12:57
 */

namespace app\modules\doctor\models;
//use yii\db\ActiveRecord;
use yii\base\Model;


class SearchMonitor extends Model
{
    public $ID;
    public $surname;
    public $name;
    public $patronymic;
    public $birthdate;
    public $snils;
    public $series;
    public $number;
    public $callNumberId;


    public function attributeLabels()
    {
        return [
            'ID' => 'Карта',
            'surname' => 'Фамилия',
            'name' => 'Имя',
            'patronymic' => 'Отчество',
            'birthdate' => 'Дата рождения',
            'snils' => 'СНИЛС',
            'series' => 'Серия',
            'number' => 'Номер',
            'callNumberId' => 'Номер вызова ССМП',
        ];
    }


    public function rules()
    {
        return [
            [['ID','surname','name','patronymic','birthdate','snils','series','number','callNumberId'],'safe'],
//            [['DATN','TARIF','KUSL','CODE_MO'],'required'],
//            ['TARIF','number'],
//            ['TARIF','trim'],
//            ['old_id','safe']

        ];
    }

}