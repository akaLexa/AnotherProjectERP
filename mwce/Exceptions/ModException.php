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
 * ошибки в модулях
 * Class ModException
 * @package mwce\Exceptions
 */

class ModException extends \Exception
{
    public function __construct($message='', $code=3, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        Logs::log($this);
    }
}