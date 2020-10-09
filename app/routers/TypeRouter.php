<?php

declare(strict_types=1);

$routeX = new Phalcon\Mvc\Micro\Collection();
$routeX->setHandler("Pbackbone\Controller\TypeController", true);
$routeX->setPrefix('/type');
$routeX->options('/', 'optionsAction');
$routeX->head('/', 'readDataAction');
$routeX->post('/', 'createDataAction');
$routeX->get('/', 'readDataAction');
$routeX->delete('/', 'deleteAllDataAction');
$routeX->options('/{id:[0-9]+}', 'optionsByIdAction');
$routeX->get('/{id:[0-9]+}', 'readDataByIdAction');
$routeX->head('/{id:[0-9]+}', 'readDataByIdAction');
$routeX->put('/{id:[0-9]+}', 'updateDataByPutAction');
$routeX->patch('/{id:[0-9]+}', 'updateDataByPatchAction');
$routeX->delete('/{id:[0-9]+}', 'deleteDataByIdAction');
$app->mount($routeX);
