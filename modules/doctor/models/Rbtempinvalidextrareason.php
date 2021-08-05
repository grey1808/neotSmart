<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Rbtempinvalidextrareason extends ActiveRecord
{
    public static function tableName()
    {
        return 'rbtempinvalidextrareason';
    }
    public function getFullName(){
        return $this->code.' | '.$this->name;
    }
}