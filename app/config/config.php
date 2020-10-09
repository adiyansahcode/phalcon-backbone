<?php

// Composer autoload
include BASE_PATH . "/vendor/autoload.php";

// Environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

return new \Phalcon\Config([
    'database' => [
        'adapter'  => $_ENV['DB_ADAPTER'],
        'host'     => $_ENV['DB_HOST'],
        'port'     => $_ENV['DB_PORT'],
        'dbname'   => $_ENV['DB_DATABASE'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'charset'  => $_ENV['DB_CHARSET'],
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
