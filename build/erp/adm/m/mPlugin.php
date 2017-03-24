<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 26.11.2016
 *
 **/
namespace build\erp\adm\m;
use mwce\Tools\Configs;
use mwce\db\Connect;
use mwce\Models\Model;

class mPlugin extends Model
{

    public static function getModels($params = null)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_plugins  order by col_seq")->fetchAll(static::class);
    }

    /**
     * @param int $id
     * @return mPlugin
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT
 pl.*,
 (SELECT GROUP_CONCAT(pg.col_gID SEPARATOR ',') FROM  tbl_plugins_group pg WHERE pg.col_pID = pl.col_pID) AS col_groups,
 (SELECT GROUP_CONCAT(pr.col_roleID SEPARATOR ',') FROM  tbl_plugins_roles pr WHERE pr.col_pID = pl.col_pID) AS col_roles
FROM 
  tbl_plugins pl
WHERE
  pl.col_pID = $id")->fetch(static::class);
    }

    /**
     * список незарегистрированных плагинов
     * @return array
     */
    public static function getNonRegPlugins()
    {
        $db = Connect::start();
        $curPlugins = array();
        $pluginPath = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'plugins';

        if(!file_exists($pluginPath))
            return $curPlugins;

        $dir = scandir($pluginPath);

        foreach ($dir as $val)
        {
            if($val!="." && $val!=".."&& $val!="m")
            {
                $bname = basename($val,".php");
                $curPlugins[$bname] = $bname;
            }

        }

        $q = $db->query("SELECT col_pID,col_pluginName FROM tbl_plugins");

        while($r = $q->fetch())
        {
            if(isset($curPlugins[$r["col_pluginName"]]))
                unset($curPlugins[$r["col_pluginName"]]);
        }
        return $curPlugins;
    }

    public function edit($params){
        $this->db->exec("UPDATE tbl_plugins SET 
col_pluginName='{$params['pluginName']}',
col_pluginState={$params['pluginState']},
col_cache={$params['pluginCache']},
col_seq={$params['pluginSeq']},
col_isClass = {$params['isClass']} 
WHERE col_pID =".$this['col_pID']);
    }

    /**
     * @param array $roles
     */
    public function addRoles($roles){

        $this->db->exec('DELETE FROM tbl_plugins_roles WHERE col_pID = '.$this['col_pID']);

        if(empty($roles))
            return;

        $q = '';
        foreach ($roles as $role) {
            if(!empty($q))
                $q.=',';
            $q.= "({$this['col_pID']},$role)";
        }
        if(!empty($q)){
            $this->db->exec("INSERT INTO tbl_plugins_roles (col_pID,col_roleID) VALUES $q");
        }
    }

    /**
     * очищает кеш зарегитсрированных плагинов
     */
    public static function RefreshCache(){

        $path = $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . '_dat';
        $files = scandir($path);
        foreach ($files as $file) {
            if(stripos($file,'_plugins.php') !== false){
                unlink($path . DIRECTORY_SEPARATOR . $file);
            }
        }
    }

    /**
     * @param array $groups
     */
    public function addGroup($groups){

        $this->db->exec('DELETE FROM tbl_plugins_group WHERE col_pID = '.$this['col_pID']);

        if(empty($groups))
            return;

        $q = '';
        foreach ($groups as $group) {
            if(!empty($q))
                $q.=',';
            $q.= "({$this['col_pID']},$group)";
        }
        if(!empty($q)){
            $this->db->exec("INSERT INTO tbl_plugins_group (col_pID,col_gID) VALUES $q");
        }
    }

    public function delete(){
        $this->db->exec("DELETE FROM tbl_plugins_group WHERE col_pID =".$this['col_pID']);
        $this->db->exec("DELETE FROM tbl_plugins_roles WHERE col_pID =".$this['col_pID']);
        $this->db->exec("DELETE FROM tbl_plugins WHERE col_pID =".$this['col_pID']);
    }

    public static function Add($name){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_plugins (col_pluginName,col_pluginState) VALUE('{$name}',0)");
    }
}