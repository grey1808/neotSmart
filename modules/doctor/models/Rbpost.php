<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Rbpost extends ActiveRecord
{
    public static function tableName()
    {
        return 'rbpost';
    }
}