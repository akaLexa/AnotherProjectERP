<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 12.11.2016
 *
 **/
namespace  build\erp\adm\m;

use mwce\Connect;
use mwce\Model;

class mUserRole extends Model
{
    /**
     * @param string $name
     */
    public static function AddRole($name){

        $db = Connect::start();
        $db->exec("INSERT INTO tbl_user_roles (col_roleName) VALUE ('$name')");
    }

    /**
     * @param null $params
     * @return mixed|array
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();

        if(empty($params['notInGroup']))
            return $db->query("SELECT * FROM tbl_user_roles ORDER BY col_roleName")->fetchAll(static::class);
        else
            return $db->query("SELECT ur.* 
FROM 
 tbl_user_roles ur 
WHERE 
  ur.col_roleID NOT IN (SELECT col_roleID FROM tbl_roles_in_group)
ORDER BY ur.col_roleName")->fetchAll(static::class);
    }

    /**
     * список ролей
     * @return array
     */
    public static function getRoleList(){
        if(!empty(self::$sdata['RoleList']))
            return self::$sdata['RoleList'];

        $db = Connect::start();
        $ar = array();
        $q = $db->query("SELECT * FROM tbl_user_roles WHERE col_isDel != 1 ORDER BY col_roleName");
        while ($r = $q->fetch()){
            $ar[$r['col_roleID']] = $r['col_roleName'];
        }

        self::$sdata['RoleList'] = $ar;

        return $ar;
    }
    /**
     * @param $id
     * @return mUserRole
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_user_roles WHERE col_roleID = $id")->fetch(static::class);
    }

    /**
     * @param string $name
     */
    public function edit($name){
        $this->db->exec("UPDATE tbl_user_roles SET col_roleName = '$name' WHERE col_roleID =".$this['col_roleID']);
    }

    public function delete(){
        $this->db->exec("DELETE FROM tbl_roles_in_group WHERE col_roleID=".$this['col_roleID']);
        $this->db->exec("DELETE FROM tbl_plugins_roles WHERE col_roleID=".$this['col_roleID']);
        $this->db->exec("DELETE FROM tbl_module_roles WHERE col_roleID=".$this['col_roleID']);
        $this->db->exec("DELETE FROM tbl_user_roles WHERE col_roleID =".$this['col_roleID']);
    }
}