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

    /**
     * список ролей, что имеют доступы
     * @param int $group
     * @return array
     */
    public function getAccessedUsers($group){
        $list = array();
        $roleRelation = $this->db->query("SELECT col_psgID FROM tbl_project_stage_group WHERE col_gID = $group")->fetch();
        $q = $this->db->query("SELECT col_roleID FROM tbl_project_stage_role WHERE col_psgID = ".$roleRelation['col_psgID']);
        while ($res = $q->fetch()){
            $list[] = $res['col_roleID'];
        }

        return $list;
    }

    /**
     * проверяет наличие доступа к стадии для группы
     * @param int $group
     */
    public function checkGroupAccess($group){

        $r = $this->db->query("SELECT count(*) as cnt FROM tbl_project_stage_group WHERE col_gID = $group")->fetch();
        if($r['cnt']<1)
            $this->db->exec("INSERT INTO tbl_project_stage_group (col_gID) VALUE($group)");
    }

    /**
     * установка доступа к стадии для ролей
     * @param int $group
     * @param array $roles
     */
    public function checkRoleAccess($group,$roles){
        // это не говнокод, это лешкий способ делать выборку по отделам для стадии, когда слишком много пользователей
        $roleRelation = $this->db->query("SELECT col_psgID FROM tbl_project_stage_group WHERE col_gID = $group")->fetch();
        $this->db->exec("DELETE FROM tbl_project_stage_role WHERE col_psgID=".$roleRelation['col_psgID']);
        if(!empty($roles)){
            $q = '';
            foreach ($roles as $role) {
                if(!empty($q))
                    $q.=',';
                $q.="({$roleRelation['col_psgID']},$role)";
            }

            if(!empty($q))
                $this->db->exec("INSERT INTO tbl_project_stage_role (col_psgID,col_roleID)VALUES $q");
        }
    }

}