<?php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host='.readConfig('database','dbhost').';dbname='.readDBConfig('dbname'),
    'username' => readDBConfig('username'),
    'password' => readDBConfig('password'),
    'charset' => 'utf8',
];