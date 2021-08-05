<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Callconfig extends ActiveRecord
{
    public static function tableName()
    {
        return 'callconfig';
    }
}