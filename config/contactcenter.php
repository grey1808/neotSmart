<?php

/*
 * Настройка нескольких баз данныз в одном прилоржении:
 * https://ru.stackoverflow.com/questions/530062/2-%D0%B1%D0%B0%D0%B7%D1%8B-%D0%B4%D0%B0%D0%BD%D0%BD%D1%8B%D1%85-yii2
 *
 * */


return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=192.168.1.201;dbname=contactcenter',
    'username' => 'dbuser',
    'password' => 'dbpassword',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];