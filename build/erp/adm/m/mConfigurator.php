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

    public static $avaliableCfgTypeList = array(
        1 => 'Пользователь',
        2 => 'Стадии',
        3 => 'Список да/нет',
        4 => 'Текст/Цифры',
    );

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
     * создание файла конфигурации
     * @param string $name
     * @param string|null $legend
     * @param string|null $descr
     * @return array
     */
    public static function addNewCfg($name,$legend,$descr){
        $path = baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
        if(file_exists($path.$name.'.cfg')){
            return ['error'=>'Название файла уже используется.'];
        }

        $fH = fopen($path . $name .'.cfg','w');
        fclose($fH);

        if(!is_null($legend) && !empty($legend)){
            $names = new DicBuilder(baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_names.php');
            $names->add2Dic($legend,$name);
        }
        if(!is_null($descr) && !empty($descr)) {
            $desc = new DicBuilder(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_desc.php');
            $desc->add2Dic($descr,$name);
        }
        return ['state'=>1];
    }

    /**
     * удалить конфиг
     * @param string $name
     * @return array
     */
    public static function deleteCfg($name){
        $path = baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
        if(file_exists($path.$name.'.cfg')){
            unlink($path.$name.'.cfg');
            $names = new DicBuilder(baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_names.php');
            $names->delFromDic($name);

            $desc = new DicBuilder(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_desc.php');
            $desc->delFromDic($name);
            return ['state'=>1];
        }
        else
            return ['error'=>'конфига не существует'];
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