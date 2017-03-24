<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.12.2016
 *
 **/
namespace build\erp\project\m;
use mwce\Tools\Configs;
use mwce\Models\Model;

class m_inProject extends Model
{
    /**
     * @param int $group
     * @param int $role
     * @return array|mixed
     */
    public static function GetTabList($group,$role){
        $fileCache = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'_dat'.DIRECTORY_SEPARATOR.'generatedTabs'.$role;
        if(file_exists($fileCache)){
            $uns = unserialize(file_get_contents($fileCache));
            if(!empty($uns))
                return $uns;
        }

        $tabs = array();

        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg';
        $dirs = scandir($path);
        unset($dirs[0],$dirs[1]);
        if(!empty($dirs)){

            foreach ($dirs as $dir) {
                $curCfg = require $path.DIRECTORY_SEPARATOR.$dir;
                if(!empty($curCfg) && (int)$curCfg['state'] > 0){
                    $group_ = explode(',',$curCfg['groupAccessR']);
                    $groupRW = explode(',',$curCfg['groupAccessRW']);
                    $role_ = explode(',',$curCfg['userAccessR']);
                    $roleRW = explode(',',$curCfg['userAccessRW']);

                    if(in_array($group,$group_) || in_array($group,$groupRW) || in_array(3,$groupRW) || in_array(3,$group_)
                        || in_array($role,$role_) || in_array($role,$roleRW)){
                        $tabs[$curCfg['num']] = array(
                            'tabName' => $curCfg['name'],
                            'tabIcon' => $curCfg['icon'],
                            'tabTitle' => $curCfg['title'],
                            'customClass' => $curCfg['isActive'] == 1 ? 'active':'',
                        );
                    }
                }
            }
        }
        ksort($tabs);
        $ser = serialize($tabs);
        file_put_contents($fileCache,$ser,LOCK_EX);

        return $tabs;
    }

    /**
     * перечень найденных вкладок
     * @return array
     */
    public static function getAllTabs(){
        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg';
        $dirs = scandir($path);
        unset($dirs[0],$dirs[1]);

        $tabs = array();

        if(!empty($dirs)){
            foreach ($dirs as $dir) {
                $curCfg = require $path.DIRECTORY_SEPARATOR.$dir;
                if(!empty($curCfg)){
                    $tabs[$curCfg['name']] = $curCfg['title'];
                }
            }
        }

        return $tabs;
    }

    public static function getModels($params = null)
    {

    }

    public static function getCurModel($id)
    {

    }
}