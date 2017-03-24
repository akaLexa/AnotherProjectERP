<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 22.12.2016
 *
 **/
namespace build\erp\adm\m;
use mwce\db\Connect;
use mwce\Models\Model;

class mTaskTypes extends Model
{
    /**
     * список стадий
     * @param bool $nameID
     * @return array
     */
    public static function getTypesList($nameID = true){
        $db = Connect::start();
        $q = $db->query("SELECT * FROM tbl_hb_task_types WHERE col_isDel = 0 order by col_tName");
        $res =  array();
        while ($r = $q->fetch()){
            if($nameID)
                $res[$r['col_tName']] = $r['col_tName'];
            else
                $res[$r['col_tttID']] = $r['col_tName'];
        }

        return $res;
    }

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