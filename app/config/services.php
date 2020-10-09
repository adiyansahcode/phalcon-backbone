<?php

declare(strict_types=1);

$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new \Phalcon\Url();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $params["host"] = $config->database->host;
    $params["username"] = $config->database->username;
    $params["password"] = $config->database->password;
    $params["dbname"] = $config->database->dbname;

    if (!empty($config->database->port)) {
        $params["port"] = $config->database->port;
    }

    if ($config->database->adapter != 'Postgresql') {
        $params["charset"] = $config->database->charset;
    }

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $connection = new $class($params);

    return $connection;
});

/**
 * Run filters
 */
$di->setShared(
    "filters",
    function () {
        return new Phalcon\Filter();
    }
);

/**
 * Run filters
 */
$di->setShared(
    "response",
    function () {
        return new Phalcon\Http\Response();
    }
);

/**
 * Run request
 */
$di->setShared(
    "request",
    function () {
        return new Phalcon\Http\Request();
    }
);

/**
 * transactions
 */
$di->setShared(
    'transactions',
    function () {
        return new Phalcon\Mvc\Model\Transaction\Manager();
    }
);

$di->setShared('myDateTime', function () {
    $datetime = new DateTime("", new DateTimeZone('Asia/Jakarta'));
    $data["timeUnixs"] = $datetime->getTimestamp(); // Unix Timestamp -- Since PHP 5.3
    $data["times"] = $datetime->format('H:i:s');    // MySQL datetime format
    $data["datetimes"] = $datetime->format('Y-m-d H:i:s');  // MySQL datetime format
    $data["dates"] = $datetime->format('Y-m-d');    // MySQL datetime format
    $data["months"] = $datetime->format('m');       // MySQL datetime format
    $data["days"] = $datetime->format('l');         // MySQL datetime format

    return (object) $data;
});

$di->setShared('publicUrl', function () {
    $config = $this->getConfig();
    $value = $config->application->publicUrl;

    return $value;
});

$di->setShared('publicPath', function () {
    $config = $this->getConfig();
    $value = $config->application->publicPath;

    return $value;
});
