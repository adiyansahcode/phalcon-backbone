<?php

declare(strict_types=1);

$routeX = new Phalcon\Mvc\Micro\Collection();
$routeX->setHandler("Pbackbone\Controller\RobotController", true);
$routeX->setPrefix('/robot');
$routeX->post('/', 'createDataAction');
$routeX->get('/', 'readDataAction');
$routeX->get('/{id:[0-9]+}', 'readDataByIdAction');
$routeX->put('/{id:[0-9]+}', 'updateDataByPutAction');
$routeX->patch('/{id:[0-9]+}', 'updateDataByPatchAction');
$routeX->delete('/', 'deleteAllDataAction');
$routeX->delete('/{id:[0-9]+}', 'deleteDataByIdAction');
$app->mount($routeX);
