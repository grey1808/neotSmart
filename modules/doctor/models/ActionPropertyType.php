<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class ActionPropertyType extends ActiveRecord
{
    public static function tableName()
    {
        return 'actionpropertytype';
    }
}