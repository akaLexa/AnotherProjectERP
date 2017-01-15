<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 08.01.2017
 *
 **/
namespace build\erp\inc;
use mwce\Configs;
use mwce\Exceptions\ModException;
use mwce\Model;

class Files extends Model
{
    protected static $docPath;

    public static function getDocPath(){
        if(empty(self::$docPath)){
            $cfg = Configs::readCfg('project',tbuild);
            if(!empty($cfg['documentsFolder']))
                self::$docPath = $cfg['documentsFolder'];
            else
                throw new ModException('Не указан адрес, где хранятся файлы проекта!');
        }
    }

    public static function getModels($params = null)
    {

    }

    public static function getCurModel($id)
    {

    }
}