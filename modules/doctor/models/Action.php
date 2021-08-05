<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Action extends ActiveRecord
{
    public static function tableName()
    {
        return 'action';
    }
    public function getActionType(){
        return $this->hasOne(ActionType::className(),['id'=>'actionType_id']);
    }



    public function getClient(){
        return $this->hasOne(Client::className(), ['id' => 'client_id'])
            ->viaTable('event', ['id' => 'event_id']);
    }
    public function getEvent(){
        return $this->hasOne(Event::className(), ['id' => 'event_id']);
    }

    public function getClientcontact(){
        return $this->hasOne(Clientcontact::className(), ['id' => 'client_id'])
        ->viaTable('event', ['id' => 'event_id']);
//        ->via('event');
    }


}