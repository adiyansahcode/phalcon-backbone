<?php

/**
 * Registering an autoloader
 */

$loader = new \Phalcon\Loader();

$loader->registerFiles([
    BASE_PATH . "/vendor/autoload.php"
]);

// Register Folder
$loader->registerDirs(
    [
        $config->application->routersDir,
        $config->application->controllersDir,
        $config->application->migrationsDir,
        $config->application->modelsDir,
        $config->application->validationsDir,
    ]
);

// Register some namespaces
$loader->registerNamespaces(
    [
        'Pbackbone\Controller' => $config->application->controllersDir,
        'Pbackbone\Model'      => $config->application->modelsDir,
        'Pbackbone\Validation' => $config->application->validationsDir,
    ]
);

$loader->register();
