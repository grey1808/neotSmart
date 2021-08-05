<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Rbresult extends ActiveRecord
{
    public static function tableName()
    {
        return 'rbresult';
    }
}