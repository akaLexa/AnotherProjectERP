<?php
/**
 * MuWebCloneEngine
 * Version: 1.6.4
 * User: epmak
 * 18.02.2017
 * ->
 **/

require __DIR__ . DIRECTORY_SEPARATOR . 'mwce' . DIRECTORY_SEPARATOR . 'Routing' . DIRECTORY_SEPARATOR . 'autoload.php';

$app = mwce\Routing\Router::start();
$app->startPlugins();
$app->startModules();
$app->show();
