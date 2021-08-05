<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Orgstructure extends ActiveRecord
{
    public static function tableName()
    {
        return 'orgstructure';
    }

}