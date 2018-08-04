<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'yii2engine_basic',
    'basePath' => dirname(__DIR__),
    'vendorPath' => readConfig('system','yii2Path').'/vendor',
    'bootstrap' => ['log'],
    //'modules' => [],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'INPUT_ANY_VALUE',//input any value
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',//see Api/models?
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'logger' => Yii::createObject('yii\log\Logger'),
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'flushInterval' => 3,   // default is 1000
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info', 'trace'],
                ],
            ],
        ],        
        'db' => require(__DIR__ . '/db.php'),
        'elasticsearch' => [
            'class' => 'yii\elasticsearch\Connection',
            'nodes' => [
                ['http_address' => readConfig('elasticsearch','host').':'.readConfig('elasticsearch','port')],
                // configure more hosts if you have a cluster
            ],
        ],                
    ],
    'params' => $params,
];

return $config;