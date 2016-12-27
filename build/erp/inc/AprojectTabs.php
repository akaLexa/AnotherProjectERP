<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 27.12.2016
 * абстрактрый класс для описания общего
 * во вкладках проекта
 **/
namespace  build\erp\inc;

abstract class AprojectTabs extends eController implements iProjectTabs
{
    /**
     * настройки вкладки
     * @var array
     */
    protected $props;

    /**
     * настройки для модуля
     * @return array
     */
    public function getProperties()
    {
        if(!empty($this->props))
            return $this->props;

        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg'.DIRECTORY_SEPARATOR.$this->className.'.php';
        if(file_exists($path)) {
            $this->props = require $path;
            return $this->props;
        }
        else
            return [];
    }
}