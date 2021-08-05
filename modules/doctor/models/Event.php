<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Event extends ActiveRecord
{
    public static function tableName()
    {
        return 'event';
    }
}