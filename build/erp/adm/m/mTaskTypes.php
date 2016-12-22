<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 22.12.2016
 *
 **/
namespace build\erp\adm\m;
use mwce\Connect;
use mwce\Model;

class mTaskTypes extends Model
{
    /**
     * @param null|array $params
     * @return mixed|mTaskTypes|array
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        $filter = '';
        if(empty($params['all'])){
            $filter.= ' col_isDel = 0 ';
        }

        if(!empty($filter))
            $filter = ' WHERE '.$filter;

        return $db->query("SELECT * FROM tbl_hb_task_types  $filter order by col_tName")->fetchAll(static::class);
    }

    /**
     * @param string $name
     */
    public static function Add($name){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_hb_task_types (col_tName) VALUE ('$name')");
    }

    /**
     * @param int $id
     * @return mixed|mTaskTypes
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_hb_task_types WHERE col_tttID = $id")->fetch(static::class);
    }

    public function delete(){
        $this->db->exec("UPDATE tbl_hb_task_types SET col_isDel = '1' WHERE col_tttID = {$this['col_tttID']}");
    }

    /**
     * @param string $name
     */
    public function edit($name){
        $this->db->exec("UPDATE tbl_hb_task_types SET col_tName = '$name' WHERE col_tttID = {$this['col_tttID']}");
    }
}