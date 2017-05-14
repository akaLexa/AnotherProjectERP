<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 14.05.2017
 * список тем
 **/
namespace build\erp\inc;

use build\erp\inc\interfaces\iConfigurable;
use mwce\Tools\Configs;

class ThemeList implements iConfigurable
{
    private static $blindDirs = array(
        'scripts',
        'imgs',
    );
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
        $dirs = scandir(baseDir . DIRECTORY_SEPARATOR . 'theme');
        if(!empty($dirs)){
            unset($dirs[0],$dirs[1]);
            if(!empty($dirs)){
                $r = [];
                foreach ($dirs as $dir){
                    if(is_dir(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $dir) && !in_array($dir,self::$blindDirs)){
                        $r[$dir] = $dir;
                    }

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
        $dirs = scandir(baseDir . DIRECTORY_SEPARATOR . 'theme');
        if(!empty($dirs)){
            unset($dirs[0],$dirs[1]);
            if(!empty($dirs)){
                $r = [];
                foreach ($dirs as $dir){
                    if(is_dir(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $dir) && !in_array($dir,self::$blindDirs)){
                        $r[] = ['id'=>$dir,'item'=>$dir];
                    }
                }
                return $r;
            }
        }
        return [];
    }
}