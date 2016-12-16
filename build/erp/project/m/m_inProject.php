<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.12.2016
 *
 **/
namespace build\erp\project\m;
use mwce\Model;

use mwce\Tools;

class m_inProject extends Model
{
    public static function GetTabList($group,$role){
        $fileCache = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.'_dat'.DIRECTORY_SEPARATOR.'generatedTabs'.$role;
        if(file_exists($fileCache)){
            $uns = unserialize(file_get_contents($fileCache));
            if(!empty($uns))
                return $uns;
        }

        $tabs = array();

        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg';
        $dirs = scandir($path);
        unset($dirs[0],$dirs[1]);
        if(!empty($dirs)){
            foreach ($dirs as $dir) {
                $curCfg = require $path.DIRECTORY_SEPARATOR.$dir;
                if(!empty($curCfg)){
                    $tabs[$curCfg['num']] = array(
                        'tabName' => $curCfg['name'],
                        'tabIcon' => $curCfg['icon'],
                        'tabTitle' => $curCfg['title'],
                        'customClass' => $curCfg['isActive'] == 1 ? 'active':'',
                    );
                }
            }
        }
        ksort($tabs);
        $ser = serialize($tabs);
        file_put_contents($fileCache,$ser,LOCK_EX);

        return $tabs;
    }

    public static function getModels($params = null)
    {

    }

    public static function getCurModel($id)
    {

    }
}