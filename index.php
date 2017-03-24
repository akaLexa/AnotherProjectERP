<?php
/**
 * MuWebCloneEngine
 * Version: 1.6.4
 * User: epmak
 * 18.02.2017
 * ->
 **/

require __DIR__ . DIRECTORY_SEPARATOR . 'mwce' . DIRECTORY_SEPARATOR . 'Routing' . DIRECTORY_SEPARATOR . 'autoload.php';

if (extension_loaded('zlib'))
    ob_start('ob_gzhandler');
else
    ob_start();

$app = mwce\Routing\router::start();
$app->startPlugins();
$app->startModules();
$app->show();

ob_end_flush();
