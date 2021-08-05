<?php
$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$contactcenter = require __DIR__ . '/contactcenter.php';
$kladr = require __DIR__ . '/kladr.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'timeZone' => 'Europe/Moscow',
    'defaultRoute' => 'list',
    'modules' => [
        'doctor' => [
            'class' => 'app\modules\doctor\Module',
            'layout' => 'main',
            'defaultRoute' => 'monitor/index',
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'formatter' => [
            'defaultTimeZone' => 'Europe/Moscow',
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'IP0twM9GpT4MnHSEiCWNI-nRHTi_aYKw', // зашифровать куки
            'enableCookieValidation' => false, // временно отключить шифрование кук
//            'cookieValidationKey' => false,
            'baseUrl' => '',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true, // автоматический вход на сайт если стояла галочка запомнить меня
//            'loginUrl' => Yii::$app->request->redirect(Yii::$app->user->returnUrl),
//            'loginUrl' => 'login',
//            'loginUrl' => [auth/login], // путь к контроллеру если пользователь не авторизован
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
            'maxSourceLines' => 20,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'assetManager' => [ // отключить кэширование
            'linkAssets' => true
        ],
        'db' => $db,
        'contactcenter' => $contactcenter,
        'kladr' => $kladr,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
//                'history' => 'history/index',
                '' => 'doctor/monitor/list',
                'list' => 'doctor/monitor/list',
                'monitor' => 'doctor/monitor/index',
                'ssmp' => 'doctor/ssmp/index',
                'ssmp11' => 'doctor/ssmp11/index',
                'ssmp11/get-ssmp-list' => 'doctor/ssmp11/get-ssmp-list',
                'ssmp11/get-call-info' => 'doctor/ssmp11/get-call-info',
                'ssmp11/upd-event' => 'doctor/ssmp11/upd-event',
                'ssmp11/add-event' => 'doctor/ssmp11/add-event',
                'ssmp11/search-client' => 'doctor/ssmp11/search-client',
                'ssmp11/get-diary' => 'doctor/ssmp11/get-diary',
                'ssmp11/set-appeal' => 'doctor/ssmp11/set-appeal',
                'ssmp11/get-portal' => 'doctor/ssmp11/get-portal',
                'ssmp11/auth' => 'doctor/ssmp11/auth',
                'ssmp11/get-list' => 'doctor/ssmp11/get-list',
                'ssmp11/get-list-and-ssmp' => 'doctor/ssmp11/get-list-and-ssmp',
                'ssmp11/get-reports' => 'doctor/ssmp11/get-reports',
                'ssmp11/get-mkb' => 'doctor/ssmp11/get-mkb',
                'ssmp11/add-going-person' => 'doctor/ssmp11/add-going-person',
                'ssmp11/delete-going-person' => 'doctor/ssmp11/delete-going-person',
                'ssmp11/download' => 'doctor/ssmp11/download',
                'ssmp11/download/<name:.+>'=>'doctor/ssmp11/download',
//                'download/<name:.+>'=>'ssmp11/download',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
        'allowedIPs' => ['10.26.1.26','192.168.1.126','10.10.18.165','85.172.11.152', '::1'],
        
		
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'], // доступ к gii
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
