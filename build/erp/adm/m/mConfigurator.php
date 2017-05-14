<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 30.04.2017
 * настройки
 **/
namespace build\erp\adm\m;
use build\erp\inc\Project;
use build\erp\inc\User;
use mwce\Models\Model;
use mwce\Tools\Configs;
use mwce\Tools\DicBuilder;

class mConfigurator extends Model
{
    protected static $ignoredCfgs = array(
        'plugin_mainMenu',
        'project',
    );

    public static $notDelete = array(
        'main',
    );

    private static $curNamesDic = array();
    private static $curDescDic = array();
    private static $curDataLists = array();

    public static $avaliableCfgTypeList = array(
        1 => 'Пользователь',
        11 => 'Пользователь мультивыбор',
        2 => 'Стадии',
        22 => 'Стадии мультивыбор',
        3 => 'Список да/нет',
        4 => 'Текст/Цифры',
    );

    /**
     * @param int $type
     * @return array|mixed
     */
    public static function getCurDataType($type){
        switch ($type){
            case 1:
                if(empty(self::$curDataLists[$type])){
                    self::$curDataLists[$type] = User::getSelectList();
                }
                break;
            case 11:
                if(empty(self::$curDataLists[$type])){
                    self::$curDataLists[$type] = User::getMultiSelectList();
                }
                break;
            case 2:
                if(empty(self::$curDataLists[$type])){
                    self::$curDataLists[$type] = Project::getSelectList();
                }
                break;
            case 22:
                if(empty(self::$curDataLists[$type])){
                    self::$curDataLists[$type] = Project::getMultiSelectList();
                }
                break;
            case 3:
                if(empty(self::$curDataLists[$type])){
                    self::$curDataLists[$type] = [0=>'Нет',1=>'Да'];
                }
                break;
            case 4:
                self::$curDataLists[$type] = '';
                break;
            default:
                return [];
        }

        return self::$curDataLists[$type];
    }

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

        if(in_array($name,self::$notDelete)){
            return ['error'=>'Этот файл конфигурации не может быть удален'];
        }

        $path = baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
        if(file_exists($path.$name.'.cfg')){
            unlink($path.$name.'.cfg');
            $names = new DicBuilder(baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_names.php');
            $names->delFromDic($name);

            $desc = new DicBuilder(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_desc.php');
            $desc->delFromDic($name);

            if(file_exists(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_'.$name.'.php')){
                unlink(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_'.$name.'.php');
            }

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

    /**
     * чтение параметров конфига
     * @return array|bool
     */
    public function getParams(){
        $cfg = Configs::readCfg($this['name'],Configs::currentBuild());
        if (empty($cfg))
            return [];

        $dictionary = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_'.$this['name'].'.php');
        $array = [];
        foreach ($cfg as $cID => $cVal){
            if(strstr($cID,'_s_cfg') === false){
                $typeNum = !empty($cfg[$cID.'_s_cfg']) ? (int)$cfg[$cID.'_s_cfg'] : 4;
                $array[]= array(
                    $cID =>[
                        'value' => $cVal,
                        'typeData' => self::getCurDataType(!empty($typeNum) ? $typeNum : 4),
                        'typeNum' => $typeNum,
                        'legend' => (!empty($dictionary[$cID]) ? $dictionary[$cID] : ''),
                        'desc' => (!empty($dictionary[$cID.'_desc']) ? $dictionary[$cID.'_desc'] : ''),
                        'typeLegend' => (!empty(self::$avaliableCfgTypeList[$typeNum]) ? self::$avaliableCfgTypeList[$typeNum] : '?'),
                    ]
                );
            }
        }

        return $array;
    }

    /**
     * сохранение конфига
     * @param array $params
     */
    public function setParams($params){
        $cfg = Configs::readCfg($this['name'],Configs::currentBuild());
        $isNewCfg = false;

        if(empty($cfg)){
            $cfg = [];
            $isNewCfg = true;
        }

        foreach ($params as $paramName => $paramValue){
            if(isset($cfg[$paramName]) || $isNewCfg){

                $cfg[$paramName] = $paramValue;

                if(!isset($cfg[$paramName.'_s_cfg']))
                    $cfg[$paramName.'_s_cfg'] = 4;

            }
        }

        if(!empty($cfg))
            Configs::writeCfg($cfg,$this['name'],Configs::currentBuild());

    }

    /**
     * @param string $name
     */
    public function deleteParam($name){
        $cfg = Configs::readCfg($this['name'],Configs::currentBuild());
        $dictionary = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_'.$this['name'].'.php');

        if(!empty($cfg[$name])){
            unset($cfg[$name]);

            if(!empty($cfg[$name.'_s_cfg']))
                unset($cfg[$name.'_s_cfg']);

            if(!empty($dictionary[$name]))
                unset($dictionary[$name]);

            if(!empty($dictionary[$name.'_desc']))
                unset($dictionary[$name.'_desc']);

            $db = new DicBuilder();
            $db->buildDic($dictionary,baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_'.$this['name'].'.php');
            Configs::writeCfg($cfg,$this['name'],Configs::currentBuild());
        }
    }

    /**
     * добавление структуры конфигурации
     * @param array $param
     */
    public function addNewParameter($param){

        $curCfg = Configs::readCfg($this['name'],Configs::currentBuild());
        $curCfg[$param['name']] = $param['value'];
        $curCfg[$param['name'].'_s_cfg'] = $param['type'];
        Configs::writeCfg($curCfg,$this['name'],Configs::currentBuild());

        $dictionary = new DicBuilder(baseDir . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'cfg_'.$this['name'].'.php');

        if(!is_null($param['legend'])){
            $dictionary->add2Dic($param['legend'],$param['name']);
        }

        if(!is_null($param['desc'])){
            $dictionary->add2Dic($param['desc'],$param['name'].'_desc');
        }

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