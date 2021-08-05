<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Diagnostic extends ActiveRecord
{
    public static function tableName()
    {
        return 'diagnostic';
    }
}