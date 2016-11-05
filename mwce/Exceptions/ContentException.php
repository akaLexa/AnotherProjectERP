<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 16.04.2016
 *
 **/
namespace mwce\Exceptions;

/**
 * ошибки в шаблонизаторе
 * Class ContentException
 * @package mwce\Exceptions
 */
class ContentException extends \Exception
{
    
    public function __construct($message="", $code =4, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}