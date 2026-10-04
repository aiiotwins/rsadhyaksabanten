<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=absen',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'on afterOpen' => function($event) {
            // +07:00 untuk WIB, +08:00 untuk WITA, +09:00 untuk WIT
            $event->sender->createCommand("SET time_zone = '+07:00'")->execute();
            $event->sender->createCommand("SET SESSION wait_timeout = 30")->execute();
        },
    'attributes' => [
        PDO::ATTR_PERSISTENT => false,
    ],

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600 * 24,
    'schemaCache' => 'cache',
];
