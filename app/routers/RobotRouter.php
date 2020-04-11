<?php

declare(strict_types=1);

$routeX = new Phalcon\Mvc\Micro\Collection();
$routeX->setHandler("Pbackbone\Controller\RobotController", true);
$routeX->setPrefix('/robot');
$routeX->options('/', 'optionsAction');
$routeX->head('/', 'readDataAction');
$routeX->post('/', 'createDataAction');
$routeX->get('/', 'readDataAction');
$routeX->delete('/', 'deleteAllDataAction');
$routeX->options('/{id:[0-9]+}', 'optionsByIdAction');
$routeX->head('/{id:[0-9]+}', 'readDataByIdAction');
$routeX->get('/{id:[0-9]+}', 'readDataByIdAction');
$routeX->put('/{id:[0-9]+}', 'updateDataByPutAction');
$routeX->patch('/{id:[0-9]+}', 'updateDataByPatchAction');
$routeX->delete('/{id:[0-9]+}', 'deleteDataByIdAction');

// * related type link
$routeX->options('/{id:[0-9]+}/type', 'optionsTypeRelatedAction');
$routeX->head('/{id:[0-9]+}/type', 'readTypeRelatedAction');
$routeX->get('/{id:[0-9]+}/type', 'readTypeRelatedAction');
$routeX->options('/{id:[0-9]+}/type/{typeId:[0-9]+}', 'optionsTypeRelatedByIdAction');
$routeX->head('/{id:[0-9]+}/type/{typeId:[0-9]+}', 'readTypeRelatedByIdAction');
$routeX->get('/{id:[0-9]+}/type/{typeId:[0-9]+}', 'readTypeRelatedByIdAction');

// * related part link
$routeX->options('/{id:[0-9]+}/part', 'optionsPartRelatedAction');
$routeX->head('/{id:[0-9]+}/part', 'readPartRelatedAction');
$routeX->get('/{id:[0-9]+}/part', 'readPartRelatedAction');
$routeX->options('/{id:[0-9]+}/part/{partId:[0-9]+}', 'optionsPartRelatedByIdAction');
$routeX->head('/{id:[0-9]+}/part/{partId:[0-9]+}', 'readPartRelatedByIdAction');
$routeX->get('/{id:[0-9]+}/part/{partId:[0-9]+}', 'readPartRelatedByIdAction');

$app->mount($routeX);
