<?php
/**
 * MuWebCloneEngine
 * Version: 1.6.2
 * User: epmak
 * 07.04.2016
 * ->
 **/
if (PHP_VERSION_ID < 50604)
    die('PHP version must be > 5.6.3');

$start_time = microtime();

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

$app = mwce\router::start();
$app->startPlugins();
$app->startModules();
$app->show();

//echo '<!-- '.\mwce\Connect::$queryCount.' -->';
//echo "<!--".round(microtime()-$start_time,4)."-->";
ob_end_flush();
