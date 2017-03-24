<?php
/**
 * MuWebCloneEngine
 * Version: 1.6.3
 * User: epmak
 * 18.02.2017
 * ->
 **/
if (PHP_VERSION_ID < 50604)
    die('PHP version must be >= 5.6.4');


define('baseDir',__DIR__);

spl_autoload_register(function($class){

    $filename = baseDir . '/' .str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if(file_exists($filename))
        include $filename;
});

if (extension_loaded('zlib'))
    ob_start('ob_gzhandler');
else
    ob_start();

$app = mwce\Routing\router::start();
$app->startPlugins();
$app->startModules();
$app->show();

ob_end_flush();
