<?php

// phpcs:disable
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');
// phpcs:enable

return new \Phalcon\Config([
    'database' => [
        'adapter'  => 'Mysql',
        'host'     => 'localhost',
        'port'     => '3310',
        'username' => 'root',
        'password' => 'Code00',
        'dbname'   => 'adi-code-backend',
        'charset'  => 'utf8',
    ],

    'application' => [
        "baseUri"        => "http://localhost/phalcon-backbone",
        "publicUrl"      => "http://localhost/phalcon-backbone/public/",
        "publicPath"     => $_SERVER['DOCUMENT_ROOT'] . "/phalcon-backbone/public/",
        "routersDir"     => APP_PATH . "/routers/",
        "controllersDir" => APP_PATH . "/controllers/",
        "migrationsDir"  => APP_PATH . "/migrations/",
        "modelsDir"      => APP_PATH . "/models/",
        "validationsDir" => APP_PATH . "/validations/",
    ]
]);
