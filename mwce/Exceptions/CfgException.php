<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 16.04.2016
 *
 **/
namespace mwce\Exceptions;
use mwce\Tools\Logs;


/**
 * файлы конфигов
 * Class CfgException
 * @package mwce\Exceptions
 */
class CfgException extends \Exception
{
    public function __construct($message='', $code = 2, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        Logs::log($this);
    }
}
