<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 30.04.2017
 * настройки
 **/
namespace build\erp\adm\m;
use mwce\Models\Model;
use mwce\Tools\Configs;
use mwce\Tools\DicBuilder;


class mConfigurator extends Model
{
    protected static $ignoredCfgs = array(
        'main',
        'plugin_mainMenu',
        'project',
    );

    private static $curNamesDic = array();
    private static $curDescDic = array();

    /**
     * @param null $params
     * @return array|mConfigurator
     */
    public static function getModels($params = null)
    {
        $cfgs = glob(baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR .'*.cfg');
        $configs = [];

        if(!empty($cfgs)){
            foreach ($cfgs as $cfg){
                $cfgName = basename($cfg,'.cfg');
                if(in_array($cfgName,self::$ignoredCfgs)){
                    continue;
                }

                $__ = new self();
                $__['name'] = $cfgName;
                $__['address'] = $cfg;

                $configs[] = $__;
            }
        }
        return $configs;
    }

    /**
     * @param string $id
     * @return array|mConfigurator
     */
    public static function getCurModel($id)
    {
        $cfgs = glob(baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR .'*.cfg');

        if(!empty($cfgs)){
            foreach ($cfgs as $cfg){
                $cfgName = basename($cfg,'.cfg');
                if($id == $cfgName) {
                    $__ = new self();
                    $__['name'] = $cfgName;
                    $__['address'] = $cfg;
                    return $__;
                }
            }
        }
        return [];
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'name':
                if(empty(self::$curNamesDic)) {
                    self::$curNamesDic = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_names.php');
                    self::$curDescDic = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_desc.php');
                }

                parent::_adding('legend', (!empty(self::$curNamesDic[$value]) ? self::$curNamesDic[$value] : $value));
                parent::_adding('desc', (!empty(self::$curDescDic[$value]) ? self::$curDescDic[$value] : ''));

                break;
        }
        parent::_adding($name, $value);
    }
}