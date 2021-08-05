<?php


namespace app\modules\doctor\models;


use yii\base\Model;

class FormListSearch extends Model
{
    public $dateOne;


    public function rules()
    {
        return [
            [['dateOne'], 'required'],
            [['dateOne'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'dateOne' => 'Дата приема',
        ];
    }

}