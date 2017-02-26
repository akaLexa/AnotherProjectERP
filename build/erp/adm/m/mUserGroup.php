<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 05.11.2016
 *
 **/
namespace  build\erp\adm\m;
use mwce\Connect;
use mwce\Model;

class mUserGroup extends Model
{
    /**
     * @param null $params
     * @return array
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        return $db->query("SELECT grps.*,tp.col_projectID FROM tbl_user_groups grps LEFT JOIN tbl_project tp ON tp.col_gID = grps.col_gID")->fetchAll(static::class);
    }

    /**
     * @param int $id
     * @return mUserGroup
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT 
  ug.*,
  GROUP_CONCAT(trig.col_roleID SEPARATOR ',') AS col_roleList 
FROM 
  tbl_user_groups ug LEFT JOIN tbl_roles_in_group trig ON trig.col_gID = ug.col_gID
WHERE 
  ug.col_gID = $id")->fetch(static::class);
    }

    public function DelGroup(){
        $this->db->exec("DELETE FROM tbl_roles_in_group WHERE col_gID = ".$this['col_gID']);
        $this->db->exec("DELETE FROM tbl_user_groups WHERE col_gID = ".$this['col_gID']);
    }

    /**
     * пустой список записей для loops
     * @return array
     */
    public static function getEmptyList(){
        return array(
            0 => [
                'col_gID' => '',
                'col_gName' => ''
            ]
        );
    }

    /**
     * maxlength 250
     * @param string $name
     * @return mUserGroup
     */
    public static function Add($name){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_user_groups (col_gName) VALUE ('$name')");
        return self::getCurModel($db->lastId('tbl_user_groups'));
    }

    /**
     * добавить список ролей для группы
     * @param array $roles
     */
    public function addRoles($roles){
        if(is_array($roles)){
            $q = '';
            foreach ($roles as $role) {
                if(!empty($q))
                    $q.=',';
                $q.="({$this['col_gID']},$role)";
            }

            if(!empty($q)){
                $this->db->exec("INSERT INTO tbl_roles_in_group (col_gID,col_roleID) VALUES $q");
            }
        }
    }


    /**
     * maxlength 250
     * @param string $name
     * @param null|array $roles
     */
    public function edit($name,$roles = null){
        $this->db->exec("UPDATE tbl_user_groups SET col_gName = '$name' WHERE col_gID =".$this['col_gID']);
        $this->db->exec("DELETE FROM tbl_roles_in_group  WHERE col_gID =".$this['col_gID']);
        if(is_array($roles)){
            self::addRoles($roles);
        }
    }

}