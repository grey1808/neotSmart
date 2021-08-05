<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class ActionProperty_Action extends ActiveRecord
{
    public static function tableName()
    {
        return 'actionproperty_action';
    }
}