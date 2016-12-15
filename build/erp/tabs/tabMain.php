<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.12.2016
 *
 **/
namespace build\erp\tabs;
use build\erp\inc\eController;
use build\erp\inc\iProjectTabs;

class tabMain extends eController implements iProjectTabs
{

    /**
     * главный вид по умолчанию
     * @return void
     */
    public function In()
    {
        $this->view->out('main',$this->className);
    }

    /**
     * настройки для модуля
     * @return array
     */
    public function getProperties()
    {
        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg'.DIRECTORY_SEPARATOR.$this->className.'.php';
        if(file_exists($path))
            return require $path;
        else
            return [];
    }

    /**
     * выполнение каких-либо функций
     * @param string $action название метода
     * @param array $params параметры
     * @return mixed
     */
    public function exec($action, $params)
    {
        // TODO: Implement exec() method.
    }
}