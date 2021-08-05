<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Kladr extends ActiveRecord
{
    public static function tableName()
    {
        return 'kladr';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return \Yii::$app->get('kladr');
    }
}