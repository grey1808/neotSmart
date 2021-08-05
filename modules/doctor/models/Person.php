<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class Person extends ActiveRecord
{

    public static function tableName()
    {
        return 'person';
    }

    public function getOrgstructure(){
        return $this->hasOne(Orgstructure::className(),['id'=>'orgStructure_id']);
    }

    public function getRbspeciality(){
        return $this->hasOne(Rbspeciality::className(),['id'=>'speciality_id']);
    }
    public function getRbpost(){
        return $this->hasOne(Rbpost::className(),['id'=>'post_id']);
    }
}