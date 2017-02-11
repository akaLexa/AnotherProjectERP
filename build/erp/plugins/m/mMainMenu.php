<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 05.12.2016
 *
 **/
namespace build\erp\plugins\m;
use mwce\Configs;
use mwce\Connect;
use mwce\Model;
use mwce\Tools;

class mMainMenu extends Model
{
    /**
     * @param array $params
     * @return array|bool
     */
    public static function getModels($params = null)
    {
        if(!empty(self::$sdata[Configs::currentBuild()])){
            return self::$sdata[Configs::currentBuild()];
        }

        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.curLang.DIRECTORY_SEPARATOR.'titles.php';
        if(file_exists($path))
            $lang = require $path;
        else
            $lang = array();

        $db = Connect::start();
        $list = $db->query("SELECT
  mmt.col_ttitle,
  mm.col_mtitle,
  mm.col_link,
  mm.col_modul,
  mm.col_Seq 
FROM 
  tbl_menu_type mmt,
  tbl_menu mm
WHERE
  mm.col_mtype = mmt.col_id
ORDER BY mmt.col_seq,mmt.col_ttitle, mm.col_Seq")->fetchAll();
        $menus = array();
        if(!empty($list)){
            foreach ($list as $item) {

                if(!empty($item['col_link']) && strpos('http',$item['col_link']) ==false){
                    $item["col_link"] = Tools::linkDec($item["col_link"]); //снимает экранирование с амперсанда
                    $item['col_link'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$item['col_link'];
                }

                if(!empty($lang[$item["col_mtitle"]])){
                    $item["col_mtitle"] = $lang[$item["col_mtitle"]];
                }

                $menus[$item['col_ttitle']][] = array(
                    'title' => $item['col_mtitle'],
                    'link' => $item['col_link'],
                    'modul' => $item['col_modul'],
                    'Seq' => $item['col_Seq'],
                );
            }

        }

        self::$sdata[Configs::currentBuild()] = $menus;
        return $menus;
    }

    public static function getCurModel($id)
    {
        return false;
    }
}