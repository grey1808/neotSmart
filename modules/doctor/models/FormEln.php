<?php


namespace app\modules\doctor\models;


use yii\base\Model;

class FormEln extends Model
{
    public $elnNum;
    public $dateIssue;
    public $cause;
    public $dop_cause;
    public $diagnosis;
    public $amb;
    public $continuation_eln;
    public $continuation_eln_current;
    public $letswork;


    public function rules(){
        return [
            [['elnNum','dateIssue','cause','diagnosis'],'required'],
            [['elnNum','cause','amb','dop_cause','continuation_eln','continuation_eln_current'],'integer'],
            [['diagnosis'],'string'],
            [['dateIssue','letswork'],'safe'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'elnNum' => 'Номер больничного',
            'dateIssue' => 'Дата выдачи',
            'cause' => 'Причина нетрудоспособности',
            'dop_cause' => 'Доп. нетрудоспособности',
            'diagnosis' => 'Диагноз',
            'amb' => 'Амбулатория',
            'continuation_eln' => 'Номер продолженного ЭЛН',
            'continuation_eln_current' => 'Этот лист продолжен с номером ЭЛН',
            'letswork' => 'Приступить к работе',
        ];
    }
}