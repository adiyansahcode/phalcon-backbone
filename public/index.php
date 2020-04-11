<?php

declare(strict_types=1);

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;

// phpcs:disable
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
// phpcs:enable

try {
    /**
     * The FactoryDefault Dependency Injector automatically registers the services that
     * provide a full stack framework. These default services can be overidden with custom ones.
     */
    $di = new FactoryDefault();

    /**
     * Include Services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Starting the application
     * Assign service locator to the application
     */
    $app = new Micro($di);

    /**
     * default URL
     */
    $app->get('/', function () use ($app) {
        $responseData = [
            "status" => "success",
            "data " => null,
        ];
        $app->response->setStatusCode(200);
        $app->response->setContent(json_encode($responseData));
        $app->response->send();
    });

    /**
     * default URL
     */
    $app->get('/404', function () use ($app) {
        $responseStatus = "fail";
        $responseErrors[] = [
            "code" => 404,
            "title" => "UrlnotFound",
            "detail" => "url not found",
        ];

        $app->response->setStatusCode(404);
        $app->response->setContent(json_encode(
            [
                "status" => $responseStatus,
                "errors " => $responseErrors,
            ]
        ));
        $app->response->send();
        return false;
    });

    /**
     * Not found handler
     */
    $app->notFound(function () use ($app) {
        $responseStatus = "fail";
        $responseErrors[] = [
            "code" => 404,
            "title" => "UrlnotFound",
            "detail" => "url not found",
        ];

        $app->response->setStatusCode(404);
        $app->response->setContent(json_encode(
            [
                "status" => $responseStatus,
                "errors " => $responseErrors,
            ]
        ));
        $app->response->send();
    });

    /**
     * read all file router
     */
    foreach (glob($config->application->routersDir . "/*Router.php") as $filename) {
        include $filename;
    }

    /**
     * Handle the request
     */
    if (empty($_GET)) {
        $uri = '/';
    } else {
        $uri = $_GET['_url'];
    }
    $app->handle($uri);
} catch (\PDOException $e) { // * get PDO Exception
    $responseStatus = "error";
    $responseErrors[] = [
        "code" => $e->getCode(),
        "title" => get_class($e),
        "detail" => $e->getMessage(),
    ];

    // * send response
    $response = new \Phalcon\Http\Response();
    $response->setStatusCode(500);
    $response->setContent(json_encode(
        [
            "status" => $responseStatus,
            "errors " => $responseErrors,
        ]
    ));
    $response->send();
} catch (\Phalcon\Mvc\Model\Exception $e) { // * get Exception
    $responseStatus = "error";
    $responseErrors[] = [
        "code" => $e->getCode(),
        "title" => get_class($e),
        "detail" => $e->getMessage(),
    ];

    // * send response
    $response = new \Phalcon\Http\Response();
    $response->setStatusCode(500);
    $response->setContent(json_encode(
        [
            "status" => $responseStatus,
            "errors " => $responseErrors,
        ]
    ));
    $response->send();
} catch (\Exception $e) { // * get Exception
    $responseStatus = "error";
    $responseErrors[] = [
        "code" => $e->getCode(),
        "title" => get_class($e),
        "detail" => $e->getMessage(),
    ];

    // * send response
    $response = new \Phalcon\Http\Response();
    $response->setStatusCode(500);
    $response->setContent(json_encode(
        [
            "status" => $responseStatus,
            "errors " => $responseErrors,
        ]
    ));
    $response->send();
}
