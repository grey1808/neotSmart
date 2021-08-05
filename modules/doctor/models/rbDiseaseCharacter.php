<?php


class rbDiseaseCharacter extends \yii\db\ActiveRecord
{

    /*
     * острое, хроническое, обострение
     *
     * */
    public static function tableName()
    {
        return 'rbDiseaseCharacter';
    }
}