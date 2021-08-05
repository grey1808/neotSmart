<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Eventtype_action extends ActiveRecord
{
    public static function tableName()
    {
        return 'eventtype_action';
    }

    public function getActionType(){
        return $this->hasOne(ActionType::className(),['id'=>'actionType_id']);
    }
}