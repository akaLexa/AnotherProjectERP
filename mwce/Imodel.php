<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 12.04.2016
 *
 **/
namespace mwce;

interface Imodel
{
    public static function getModels($params = null);
    public static function getCurModel($id);
}