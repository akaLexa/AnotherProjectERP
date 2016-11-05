<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 16.04.2016
 *
 **/
namespace mwce\Exceptions;

/**
 * Class DBException
 * @package mwce\Exceptions
 */
class DBException extends \Exception
{
    public function __construct($message='', $code=1, \Exception $previous =null)
    {
        parent::__construct($message, $code, $previous);
    }
}