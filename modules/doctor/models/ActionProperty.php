<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class ActionProperty extends ActiveRecord
{
    public static function tableName()
    {
        return 'actionproperty';
    }
}