<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Clientdocument extends ActiveRecord
{
    public static function tableName()
    {
        return 'clientdocument';
    }
}