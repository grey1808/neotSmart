<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Diagnosis extends ActiveRecord
{
    public static function tableName()
    {
        return 'diagnosis';
    }
}