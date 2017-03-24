<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 09.11.2016
 *
 **/
namespace  build\erp\adm\m;


use mwce\Tools\Configs;
use mwce\db\Connect;
use mwce\Tools\DicBuilder;
use mwce\Models\Model;
use mwce\Tools\Tools;

class mMenuManager extends Model
{
    /**
     * перечень ролей и
     * @param string $menu
     * @return array
     */
    public function getAccessList($menu){

        $config = Configs::readCfg('plugin_mainMenu',Configs::currentBuild());
        if(!empty($config)){
            $m = $config;
            foreach ($m as $name =>$item) {
                $config[$name] = explode(',',$item);
            }
        }

        $groups = mUserRole::getRoleList();

        if(empty($config[$menu])){
            $config[$menu] = [];
        }

        $checked = [];
        foreach ($groups as $id=>$group) {
            if(in_array($id,$config[$menu])){
                $checked[$id] = 1;
            }
            else{
                $checked[$id] = 0;
            }
        }
        return ['roles'=>$groups,'access'=>$checked];

    }

    /**
     * список меню
     * @return array
     */
    public static function getMenuList()
    {
        if(!empty(self::$sdata['MenuList']))
            return self::$sdata['MenuList'];

        $db = Connect::start();

        $q = $db->query("SELECT * FROM tbl_menu_type");
        $ma = array();
        while($result = $q->fetch())
        {
            $ma[$result["col_id"]]=$result["col_ttitle"];
        }
        self::$sdata['MenuList'] = $ma;
        return $ma;
    }

    /**
     * @param string $name
     */
    public static function addMenu($name){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_menu_type (col_ttitle,col_seq) VALUE ('$name',99)");
    }

    /**
     * узнать очередность отображения меню
     * @param int $id
     * @return int
     */
    public static function KnowMenuSequence($id){
        $db = Connect::start();
        $r = $db->query("SELECT col_seq FROM tbl_menu_type WHERE col_id = $id")->fetch();
        return $r['col_seq'];
    }

    /**
     * @param int $id
     * @param int $val
     */
    public static function SetMenuSequence($id,$val){
        $db = Connect::start();
        $db->exec("UPDATE tbl_menu_type SET col_seq = $val WHERE col_id = $id");
    }

    /**
     * список позиций в меню
     * @param null $params
     * @return array|mMenuManager
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        if(empty($params['menuId']))
            return [];

        $q = $db->query("SELECT
 mm.col_id,
 mm.col_mtitle,
 mm.col_link,
 mt.col_ttitle as mtype,
 mm.col_Seq
FROM
 tbl_menu mm,
 tbl_menu_type mt
WHERE
 mt.col_id = '{$params['menuId']}'
 AND mt.col_id = mm.col_mtype
ORDER BY mm.col_Seq");

        $return = array();



        while ($r = $q->fetch())
        {
            $r["col_link"] = empty($r["col_link"]) ? 'Заголовок' : $r["col_link"];

            $return[$r["col_id"]] = array(
                "mtitle"=>$r["col_mtitle"],
                "link"=>$r["col_link"],
                "mtype"=>$r["mtype"],
                'col_Seq'=>$r['col_Seq'],
                'id'=>$r['col_id']
            );
        }

        return $return;
    }

    /**
     * @param int $id
     */
    public static function delMenu($id){
        $db = Connect::start();
        $db->exec("DELETE from tbl_menu WHERE col_mtype = $id; DELETE FROM tbl_menu_type WHERE col_id = $id");
    }

    /**
     * @param int $id
     */
    public static function delPosMenu($id){
        $db = Connect::start();
        $db->exec("DELETE from tbl_menu WHERE col_id = $id;");
    }

    /**
     * @param int $id
     * @return mMenuManager
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_menu_type WHERE col_id = $id")->fetch(static::class);

    }

    /**
     * позиция в меню
     * @param int $id
     * @return mMenuManager
     */
    public static function getCurentPos($id){
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_menu mm WHERE mm.col_id = $id")->fetch(static::class);
    }

    /**
     * редактирование позиции в меню
     * @param string $mtitle
     * @param string $link
     * @param string $modul
     * @param int $col_Seq
     */
    public function editCurrentPos($mtitle,$link,$modul,$col_Seq){
        $this->db->exec("UPDATE tbl_menu SET col_mtitle='$mtitle',col_link='$link',col_modul='$modul',col_Seq=$col_Seq WHERE col_id =".$this['col_id']);
    }

    public static function addToMenu($title,$type,$link,$modul,$seq)
    {
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_menu (col_mtitle,col_mtype,col_link,col_modul,col_Seq) VALUES ('$title',$type,'{$link}','{$modul}',$seq)");
    }

    public function pageList()
    {
        $array = array(-1=>"...");
        $q = $this->db->query("SELECT col_moduleName,col_title FROM tbl_modules ORDER BY col_moduleName");

        $lpath = "build".DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.curLang.DIRECTORY_SEPARATOR."titles.php";
        $lang = DicBuilder::getLang($lpath);

        while ($r = $q->fetch())
        {
            if(!empty($lang[$r["col_title"]]))
                $array[$r["col_moduleName"]] = $lang[$r["col_title"]];
            else
                $array[$r["col_moduleName"]] = $r["col_title"];
        }

        return $array;
    }

    /**
     * очитка файлов кеша
     */
    public static function RefreshCache(){

        $path = $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . '_dat' . DIRECTORY_SEPARATOR .'cache';
        $files = scandir($path);
        foreach ($files as $file) {
            if(stripos($file,'plugin_mainMenu') !== false){
                unlink($path . DIRECTORY_SEPARATOR . $file);
            }
        }
    }

}