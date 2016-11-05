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
 * запись/чтение/восстановление конфигов сайта
 */
class Configs
{
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
}