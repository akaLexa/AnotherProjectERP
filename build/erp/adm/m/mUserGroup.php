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
        return $db->query("SELECT * FROM tbl_user_groups")->fetchAll(static::class);
    }

    /**
     * @param int $id
     * @return mUserGroup
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_user_groups WHERE col_gID = $id")->fetch(static::class);
    }

    public function DelGroup(){
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
     */
    public static function Add($name){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_user_groups (col_gName) VALUE ('$name')");
    }

    /**
     * maxlength 250
     * @param string $name
     */
    public function edit($name){
        $this->db->exec("UPDATE tbl_user_groups SET col_gName = '$name' WHERE col_gID =".$this['col_gID']);
    }

}