<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Rbdiagnosticresult extends ActiveRecord
{
    public static function tableName()
    {
        return 'rbdiagnosticresult';
    }
}