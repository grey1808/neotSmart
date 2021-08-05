<?php


namespace app\modules\doctor\models;


use yii\db\ActiveRecord;

class vrbPerson extends ActiveRecord
{
    public static function tableName()
    {
        return 'vrbPerson';
    }

    public function getOrgstructure(){
        return $this->hasOne(Orgstructure::className(),['id'=>'orgStructure_id']);
    }
}