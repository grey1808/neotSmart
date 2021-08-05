<?php


namespace app\modules\doctor\models;

use yii\db\ActiveRecord;

class ActionType extends ActiveRecord
{
    public static function tableName()
    {
        return 'actiontype';
    }
}