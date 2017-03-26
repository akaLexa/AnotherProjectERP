<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 16.12.2016
 *
 **/
namespace build\erp\project\m;
use mwce\Tools\Configs;
use mwce\Tools\DicBuilder;
use mwce\Models\Model;

class m_TabsCfgs extends Model
{

    public static function getModels($params = null)
    {
       /*nop*/
    }

    /**
     * @param string $id
     * @return bool|m_TabsCfgs
     */
    public static function getCurModel($id)
    {
        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg'.DIRECTORY_SEPARATOR.$id.'.php';
        if(!file_exists($path))
            return false;

        $cfg = DicBuilder::getLang($path);
        if(!empty($cfg)){
            $obj = new m_TabsCfgs();
            $lang = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::buildCfg('dlang').DIRECTORY_SEPARATOR.'tab_cfgs.php');

            foreach ($cfg as $id_=> $item) {

                $option = array(
                    'name'=>$id_,
                    'legend' => !empty($lang[$id_]) ? $lang[$id_] : $id_,
                    'value' => $item
                );

                $obj->_adding($id_, $option);
            }

            return $obj;
        }

        return false;
    }

    /**
     * @param array $newCfg
     */
    public function save($newCfg){
        if(!empty($newCfg)){
            foreach ($this as $id=>$item) {
                if(!isset($newCfg[$id]))
                    $newCfg[$id] = '';
            }

            $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg'.DIRECTORY_SEPARATOR.$this['name']['value'].'.php';

            $db = new DicBuilder($path);
            $db->buildDic($newCfg);

            self::delCache();
        }
    }

    /**
     * удалить кешированные списки видимости вкладок в проекте
     */
    public static function delCache(){
        $path = $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . '_dat';
        $files = scandir($path);
        foreach ($files as $file) {
            if(stripos($file,'generatedTabs') !== false){
                unlink($path . DIRECTORY_SEPARATOR . $file);
            }
        }
    }
}