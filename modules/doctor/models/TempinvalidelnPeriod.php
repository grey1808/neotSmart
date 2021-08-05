<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class TempinvalidelnPeriod extends ActiveRecord
{
    public static function tableName()
    {
        return 'tempinvalideln_period';
    }
}