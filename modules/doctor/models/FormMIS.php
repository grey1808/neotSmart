<?php


namespace app\modules\doctor\models;


use yii\base\Model;

class FormMIS extends Model
{
//    public $lpuCode; // string[5] //  код медицинской организации в системе ОМС
    public $mkb; // string[7] // Основной диагноз ЛПУ
    public $mkb_sub; // string[7] //  Сопутствующий диагноз ЛПУ
    public $mkb_final; // string[7] //  Заключительный диагноз ЛПУ
    public $ambulNumber; // string[60] // Номер истории болезни
    public $person_id; // Fio // идентификатор врача
    public $orgstructure; // подразделение врача
    public $eventType; // Тип события
    public $region; // Регион инокраевой = 1, краевой = 0

    // PrivateData // Персональные данные пациента
    public $lastName;
    public $firstName;
    public $patrName;
    public $age;
    public $sex;
    public $birthDate;


    public $doc_idType; // Тип документа
    public $doc_typeName; // тип УДЛ
    public $doc_series; //серия УДЛ
    public $doc_number; // номер УДЛ;
    public $doc_birth_place; // место рождения
    public $doc_issue_date; // дата выдачи УДЛ
    public $doc_organization; // организация выдавшая УДЛ

    public $polis_insuranceCompanyCode; // Код СМО
    public $polis_idType; // Тип документа ОМС
    public $polis_id; // Идентификатор полиса из БД
    public $polis_series; // Серия документа ОМС
    public $polis_number; // Номер документа ОМС

    public $snils;
    // PrivateData //  Персональные данные  представителя пациента
    public $personRepresentation_lastName; // Фамилия представителя пациент
    public $personRepresentation_name; // Имя представителя пациент
    public $personRepresentation_patronymic; // Отчество представителя пациент
    public $personRepresentation_age; // возраст представителя пациент
    public $personRepresentation_sex; // пол представителя пациент
    public $personRepresentation_birthDate; // дата рождения представителя пациент

    public $personRepresentation_doc_idType; // Тип документа
    public $personRepresentation_doc_typeName; // тип УДЛ
    public $personRepresentation_doc_series; //серия УДЛ
    public $personRepresentation_doc_number; // номер УДЛ;
    public $personRepresentation_doc_birth_place; // место рождения
    public $personRepresentation_doc_issue_date; // дата выдачи УДЛ
    public $personRepresentation_doc_organization; // организация выдавшая УДЛ

    public $personRepresentation_polis_insuranceCompanyCode;
    public $personRepresentation_polis_idType;
    public $personRepresentation_polis_series;
    public $personRepresentation_polis_number;

    public $personRepresentation_snils;

    public function rules()
    {
        return [
            [['mkb','ambulNumber','person','eventType','region'],'required'],
            [['mkb_sub','mkb_final','person_id',
                'lastName',
                'firstName',
                'patrName',
                'age','sex','birthDate','snils','polis_insuranceCompanyCode','polis_idType','polis_series','polis_number',
                'doc_typeName',
                'doc_series',
                'doc_number',
                'doc_birth_place',
                'doc_issue_date',
                'doc_organization',

                'personRepresentation_lastName',
                'personRepresentation_name',
                'personRepresentation_patronymic',

                'personRepresentation_age',
                'personRepresentation_sex',
                'personRepresentation_birthDate',

                'personRepresentation_doc_typeName',
                'personRepresentation_doc_series',
                'personRepresentation_doc_number',
                'personRepresentation_doc_birth_place',
                'personRepresentation_doc_issue_date',
                'personRepresentation_doc_organization',

                'personRepresentation_polis_insuranceCompanyCode',
                'personRepresentation_polis_idType',
                'personRepresentation_polis_series',
                'personRepresentation_polis_number',

                'personRepresentation_snils',

            ],'string'],
//            [['hospTime',],'safe'],
            [['ambulNumber','ambulNumber',
                'doc_idType','orgstructure','eventType','region','polis_id'],'integer'],

        ];
    }
    public function attributeLabels()
    {
        return [
            'mkb' => 'Основной диагноз',
            'mkb_sub' => 'Сопутствующий диагноз',
            'mkb_final' => 'Заключительный диагноз',
            'ambulNumber' => 'Номер истории болезни',
            'orgstructure' => 'Подразделение',
            'eventType' => 'Тип события',
            'region' => 'Регион',
            'person_id' => 'ФИО врача приёмного отделения',


            'lastName'=>'Фамилия пациента',
            'firstName'=>'Имя пациента',
            'patrName'=>'Отчество пациента',
            'age'=>'Возраст пациента',
            'sex'=>'Пол пациента', // 1=Ж, 0=М
            'birthDate'=>'Дата рождения пациента',
            'doc_idType'=>'Тип документа',
            'doc_typeName'=>'тип УДЛ',
            'doc_series'=>'серия УДЛ',
            'doc_number'=>'номер УДЛ',
            'doc_birth_place'=>'Место рождения',
            'doc_issue_date'=>'Дата выдачи УДЛ',
            'doc_organization'=>'Организация выдавшая УДЛ',

            'polis_insuranceCompanyCode'=>'Код СМО',
            'polis_idType'=>'Тип документа ОМС',
            'polis_series'=>'Серия документа ОМС',
            'polis_number'=>'Номер документа ОМС',
            'snils'=>'СНИЛС пациента',

            'personRepresentation_lastName'=>'Фамилия  представителя пациента',
            'personRepresentation_name'=>'Имя представителя пациента',
            'personRepresentation_patronymic'=>'Отчество представителя пациента',
            'personRepresentation_age'=>'Возраст представителя пациента',
            'personRepresentation_sex'=>'Пол представителя пациента', // 1=Ж, 0=М
            'personRepresentation_birthDate'=>'Дата рождения представителя пациента',

            'personRepresentation_doc_idType'=>'Тип УДЛ',
            'personRepresentation_doc_typeName'=>'тип УДЛ',
            'personRepresentation_doc_series'=>'серия УДЛ',
            'personRepresentation_doc_number'=>'номер УДЛ',
            'personRepresentation_doc_birth_place'=>'Место рождения',
            'personRepresentation_doc_issue_date'=>'Дата выдачи УДЛ',
            'personRepresentation_doc_organization'=>'Организация выдавшая УДЛ',

            'personRepresentation_polis_insuranceCompanyCode'=>'Код СМО',
            'personRepresentation_polis_idType'=>'Тип документа ОМС',
            'personRepresentation_polis_series'=>'Серия документа ОМС',
            'personRepresentation_polis_number'=>'Номер документа ОМС',
            'personRepresentation_snils'=>'СНИЛС представителя пациента',
        ];
    }
}