<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 11.12.2016
 *
 **/
namespace build\erp\adm\m;
use mwce\Connect;
use mwce\Model;

class mStages extends Model
{
    /**
     * @param null $params
     * @return mStages
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        return $db->query("SELECT col_StageID,col_StageName FROM tbl_hb_project_stage WHERE col_isDel = 0 ORDER BY col_StageName")->fetchAll(static::class);
    }

    /**
     * @param int $id
     * @return mStages
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_hb_project_stage WHERE col_StageID = $id")->fetch(static::class);
    }

    /**
     * @param string $name
     * @return mStages
     */
    public static function Add($name){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_hb_project_stage (col_StageName) VALUE('$name')");
        $lid = $db->lastId('tbl_hb_project_stage');
        return self::getCurModel($lid);
    }

    public function delete(){
        $this->db->exec('UPDATE tbl_hb_project_stage SET col_isDel = 1 WHERE col_StageID='.$this['col_StageID']);
    }

    public function edit($name){
        $this->db->exec("UPDATE tbl_hb_project_stage SET col_StageName = '$name' WHERE col_StageID=".$this['col_StageID']);
    }
}