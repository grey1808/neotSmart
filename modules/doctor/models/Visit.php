<?php


namespace app\modules\doctor\models;

use yii\db\ActiveRecord;

class Visit extends ActiveRecord
{
    public static function tableName()
    {
        return 'visit';
    }
}