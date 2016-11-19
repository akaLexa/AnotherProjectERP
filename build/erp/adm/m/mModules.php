<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.11.2016
 *
 **/
namespace build\erp\adm\m;
use mwce\Connect;
use mwce\Model;

class mModules extends Model
{
    /**
     * @param array $params
     */
    public static function Add($params){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_modules (col_title,col_path,col_cache,col_isClass) VALUE('{$params['titleList']}','{$params['adrCnt']}',{$params['cachSec']},{$params['isMVC']})");
        $id = $db->lastId('tbl_modules');

        if(!empty($params['roles']))
            self::addRolesToModule($id,$params['roles']);
    }

    /**
     * @param int $mpdule
     * @param array $groups
     */
    public static function addRolesToModule($mpdule,$groups){
        $db = Connect::start();
        $db->exec('DELETE FROM tbl_module_roles WHERE col_modID = '.$mpdule);

        $q = '';
        foreach ($groups as $group) {
            if(!empty($q))
                $q.=',';
            $q.= "($mpdule,$group)";
        }
        if(!empty($q)){

            $db->exec("INSERT INTO tbl_module_roles (col_modID,col_roleID) VALUES $q");
        }
    }

    public static function getModels($params = null)
    {
        $db = Connect::start();
        return $db->query("SELECT 
  mm.* 
FROM 
  tbl_modules mm 
order BY mm.col_title")->fetchAll(static::class);
    }

    /**
     * @param int $id
     * @return mModules
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT 
  mm.*,
   GROUP_CONCAT(col_roleID separator ',') as col_roles
FROM 
   tbl_modules mm,
   tbl_module_roles mr
WHERE 
  mm.col_modID = $id
  AND mr.col_modID = mm.col_modID
order BY mm.col_title")->fetch(static::class);
    }

    public function edit($params){
        $this->db->exec("UPDATE tbl_modules SET col_title='{$params['titleList']}',col_path='{$params['adrCnt']}',col_cache={$params['cachSec']},col_isClass = {$params['isMVC']} WHERE col_modID = ".$this['col_modID']);
    }

    public function delete(){
        $this->db->exec("DELETE FROM tbl_module_roles WHERE col_modID = {$this['col_modID']}");
        $this->db->exec("DELETE FROM tbl_modules WHERE col_modID = {$this['col_modID']}");
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_isClass':
                if($value == '1')
                    parent::_adding($name.'Legend', 'MVC');
                else
                    parent::_adding($name.'Legend', 'script');
                break;
        }
        parent::_adding($name, $value);
    }
}