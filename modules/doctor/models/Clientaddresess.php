<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Clientaddresess extends ActiveRecord
{
    public static function tableName()
    {
        return 'clientaddress';
    }
}