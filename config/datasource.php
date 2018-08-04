<?php
return [
    'connectionString' => 'mysql:host='.readConfig('database','dbhost').';dbname='.readDBConfig('dbname'),
    'emulatePrepare' => true,
    'username' => readDBConfig('username'),
    'password' => readDBConfig('password'),
    'charset' => 'utf8',
    'pdoClass' => 'NestedPDO',
    //'enableProfiling'=>true,
    //'enableParamLogging'=>true,
];