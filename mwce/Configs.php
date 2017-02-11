<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 08.04.2016
 *
 **/
namespace mwce;

/**
 * Class Configs
 * запись/чтение/восстановление/хранение конфигов сайта
 * @method static array buildCfg( @param string )
 * @method static array globalCfg( @param string )
 * @method static int userID()
 * @method static int curRole()
 * @method static int curGroup()
 * @method static string currentBuild()
 */
class Configs
{
    /**
     * @var array
     */
    private $Cfgs = array();

    /**
     * @var null|Configs
     */
    private static $instance = null;

    /**
     * @param array $config  - массив с параметрами
     * @param string $filename  - название конфига (без расширения)
     * @param string $build - билд, по умолчанию default
     * создание/запись в новый/существующий конфиг
     */
    public static function writeCfg($config,$filename,$build = "default")
    {
        if($build!="main")
            $configDir = baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.$build.DIRECTORY_SEPARATOR."configs";
        else
            $configDir = baseDir.DIRECTORY_SEPARATOR."configs";

        $path = $configDir.DIRECTORY_SEPARATOR.$filename.".cfg";
        $repath = $configDir.DIRECTORY_SEPARATOR.$filename.".cfg.bkc";

        if (file_exists($path)) //если есть конфиг - делаем бекапчик
        {
            @rename($path,$repath);
        }

        $handle = fopen($path,"w");
        fwrite($handle,serialize($config));
        fclose($handle);
    }

    /**
     * @param string $cname - название файла конфигурации (без расширения)
     * @param string $build - требуемый билд, по умолчанию "default"
     * @return bool|array - возвращает конфигурацию в виде ассоциативного массива или же false, в случае неудачи
     */
    public static function readCfg($cname,$build = "default")
    {
        if($build!="main")
            $configDir = baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.$build.DIRECTORY_SEPARATOR."configs";
        else
            $configDir = baseDir.DIRECTORY_SEPARATOR."configs";

        $path = "$configDir".DIRECTORY_SEPARATOR."$cname.cfg";
        
        if (file_exists($path))
        {
            $ar = unserialize(trim(file_get_contents($path)));
            return $ar;
        }
        return false;
    }

    /**
     * @param string $cname название конфига (без расширения)
     * @param string $build билд, по умолчанию "default"
     * @return bool true в случае удачи и false в противном случае
     * Восстанавливает файл конфигурации в случае, если есть копия
     */
    public static function recoverCfg($cname,$build="default")
    {
        if($build!="main")
            $configDir = baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.$build.DIRECTORY_SEPARATOR."configs";
        else
            $configDir = baseDir.DIRECTORY_SEPARATOR."configs";

        $path = $configDir.DIRECTORY_SEPARATOR.$cname.".cfg.bkc";

        if (file_exists($path)) //если есть бекап на конфиг, возвращаем конфиг
        {
            @rename($path,$configDir.DIRECTORY_SEPARATOR.$cname.".cfg");
            return true;
        }

        return false;
    }

    /**
     * @param null|array $params
     * @return Configs|null
     */
    public static function initConfigs($params = null){
        if(is_null(self::$instance))
            self::$instance = new self($params);

        return self::$instance;
    }

    /**
     * @param $name
     * @param null|string $args
     * @return bool|mixed
     */
    protected static function getParam($name,$args = null){
        if(!empty(self::$instance->Cfgs[$name]))
        {
            if(is_null($args))
                return self::$instance->Cfgs[$name];
            else{
                if(!empty(self::$instance->Cfgs[$name][$args]))
                    return self::$instance->Cfgs[$name][$args];
            }
        }

        return false;
    }

    protected function __construct($params)
    {
        $this->Cfgs = $params;
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::getParam($name,!empty($arguments[0]) ? $arguments[0] : null);
    }
}