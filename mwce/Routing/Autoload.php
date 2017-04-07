<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 24.03.2017
 *
 **/

if (PHP_VERSION_ID < 50604)
    die('PHP version must be >= 5.6.4');

define('baseDir', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');

spl_autoload_register(function($class){

    $filename = baseDir . '/' .str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if(file_exists($filename))
        include $filename;
});