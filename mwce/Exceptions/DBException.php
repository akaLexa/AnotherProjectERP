<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 16.04.2016
 *
 **/
namespace mwce\Exceptions;

use mwce\Logs;

/**
 * Class DBException
 * @package mwce\Exceptions
 */
class DBException extends \Exception
{
    public function __construct($message='', $code=1, \Exception $previous =null)
    {
        parent::__construct($message, 1, $previous);
        Logs::textLog(1,'['.date('H:i:s').'] '. $this->getMessage().' on file '.$this->getFile().' on line '.$this->getLine());
    }
}