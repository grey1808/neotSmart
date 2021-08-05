<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Rbtempinvalidreason extends ActiveRecord
{
    public static function tableName()
    {
        return 'rbtempinvalidreason';
    }
    public function getFullName(){
        return $this->code.' | '.$this->name;
    }
}