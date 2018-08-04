Yii 2 App
=========

This is a customized Yii 2 app from "Yii 2 Basic Application Template" to serve as a backend engine to utilize yii 2 capabilities such as 
elastic search, Bootstrap library facilitation and others.

It keeps minimal directories to work and remove unnecessary directories or files. 
Removed directories or files can be added back (take reference from Yii 2 Basic app) whenever needed if required.

DIRECTORY STRUCTURE
-------------------

```php

<remove>      assets/             contains assets definition
<remove>      commands/           contains console commands (controllers)
<keep>        config/             contains application configurations
<remove>      controllers/        contains Web controller classes
<remove>      mail/               contains view files for e-mails
<remove>      models/             contains model classes
<keep>        runtime/            contains files generated during runtime
<remove>      tests/              contains various tests for the basic application
<remove>      vendor/             contains dependent 3rd-party packages
<remove>      views/              contains view files for the Web application
<remove>      web/                contains the entry script and Web resources

```

Note: Yii 2 core libraries "vendor" folders should be installed outside of this app. Refer to Installation Guide.


CONFIGURATION
-------------
### Elasticsearch
Edit the file `config/web.php` with real data, for example:

```php
'components' => [
    'elasticsearch' => [
        'class' => 'yii\elasticsearch\Connection',
        'nodes' => [
            ['http_address' => 'localhost:9200'],
            // configure more hosts if you have a cluster
        ],
    ],
],
```

### Database
Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

**NOTE:** Yii won't create the database for you, this has to be done manually before you can access it.

Also check and edit the other files in the `config/` directory to customize your application.

REFERENCE
------------
Refer to Yii 2 Basic Applicatino README.md

