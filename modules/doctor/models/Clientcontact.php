<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Clientcontact extends ActiveRecord
{
    public static function tableName()
    {
        return 'clientcontact';
    }
}