<?php

// phpcs:disable
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');
// phpcs:enable

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
        "baseUri"        => $_ENV['APP_URL'],
        "publicUrl"      => $_ENV['APP_URL'] . "/public",
        "publicPath"     => $_SERVER['DOCUMENT_ROOT'] . "/phalcon-backbone/public",
        "routersDir"     => APP_PATH . "/routers/",
        "controllersDir" => APP_PATH . "/controllers/",
        "middlewaresDir" => APP_PATH . "/middlewares/",
        "modelsDir"      => APP_PATH . "/models/",
        "validationsDir" => APP_PATH . "/validations/",
        'logInDb' => true,
        'migrationsTsBased' => false, // true - Use TIMESTAMP as version name, false - use versions
        'no-auto-increment' => true,
        'skip-ref-schema' => true,
        'skip-foreign-checks' => true,
        'migrationsDir' => 'database/migrations',
        'exportDataFromTables' => [
            // Tables names
            // Attention! It will export data every new migration
        ],
    ]
]);
