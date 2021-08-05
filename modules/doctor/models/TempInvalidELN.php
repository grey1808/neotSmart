<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class TempInvalidELN extends ActiveRecord
{
    public static function tableName()
    {
        return 'tempInvalideln';
    }
}