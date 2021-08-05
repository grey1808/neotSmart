<?php


class NetricaPatientConditionOnAdmission extends \yii\db\ActiveRecord
{
    /*
     * Таблица состония:
     * удовлетворительное
     * средней тяжести и тд
     *
     * */
    public static function tableName()
    {
        return 'netricaPatientConditionOnAdmission';
    }
}