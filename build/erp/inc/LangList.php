<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 14.05.2017
 * список доступных языков
 **/
namespace build\erp\inc;

use build\erp\inc\interfaces\iConfigurable;
use mwce\Tools\Configs;

class LangList implements iConfigurable
{

    /**
     * массив для генерации выпадающего списка
     * [
     *  [1] => позиция 1
     *  [2] => позиция 2
     * ]
     * @return array
     */
    public static function getSelectList()
    {
        $dirs = scandir(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang');
        if(!empty($dirs)){
            unset($dirs[0],$dirs[1]);
            if(!empty($dirs)){
                $r = [];
                foreach ($dirs as $dir){
                    $r[$dir] = $dir;
                }
                return $r;
            }
        }
        return [];
    }

    /**
     * массив для генерации списка, где можно
     * выбрать несколько значений
     * [
     *   [0]=>['id' => 1,'item' => 'Позиция 1'],
     *   [1]=>['id' => 2,'item' => 'Позиция 2'],
     * ]
     * @return mixed
     */
    public static function getMultiSelectList()
    {
        $dirs = scandir(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang');
        if(!empty($dirs)){
            unset($dirs[0],$dirs[1]);
            if(!empty($dirs)){
                $r = [];
                foreach ($dirs as $dir){
                    $r[] = ['id'=>$dir,'item'=>$dir];
                }
                return $r;
            }
        }
        return [];
    }
}