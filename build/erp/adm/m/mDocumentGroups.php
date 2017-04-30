<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 08.01.2017
 *
 **/
namespace build\erp\adm\m;

use mwce\db\Connect;
use mwce\Models\Model;

class mDocumentGroups extends Model
{
    /**
     * @param $name
     * @return mDocumentGroups
     */
    public static function Add($name){
        $db = \mwce\db\Connect::start();
        $db->exec("INSERT INTO tbl_hb_doc_group (col_docGroupName) VALUE('$name')");
        return self::getCurModel($db->lastId('tbl_hb_doc_group'));
    }

    /**
     * @param null $params
     * @return array|mDocumentGroups
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_hb_doc_group WHERE  col_isDel = 0 ORDER BY col_docGroupName")->fetchAll(static::class);
    }

    /**
     * массив с доступами:
     * [roleID] => (DocumentGroupID,access)
     * @return array
     */
    public static function getRolesAccess(){
        $db = Connect::start();
        $ar = array();
        $q = $db->query("SELECT * FROM tbl_doc_group_access");
        while ($r = $q->fetch()){
            $ar[$r['col_roleID']] = array($r['col_dgID'],$r['col_access']);
        }
        return $ar;
    }

    /**
     * @param $id
     * @return mDocumentGroups
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_hb_doc_group WHERE col_dgID = $id")->fetch(static::class);
    }

    public function edit($name){
        $this->db->exec("UPDATE tbl_hb_doc_group SET col_docGroupName = '$name' WHERE col_dgID = {$this['col_dgID']}");
    }

    public function editAccess($newAccess){
        $this->db->exec("DELETE FROM tbl_doc_group_access WHERE col_dgID = {$this['col_dgID']}");
        if(!empty($newAccess)){
            $q = '';

            foreach ($newAccess as $role => $acs){
                if(!empty($q))
                    $q.=',';
                $q.="({$this['col_dgID']},$role,$acs)";
            }

            if(!empty($q)){
                $this->db->exec("INSERT INTO tbl_doc_group_access (col_dgID,col_roleID,col_access) VALUES $q");
            }
        }
    }

    public function delete(){
        $this->db->exec("UPDATE tbl_hb_doc_group SET col_isDel = 1 WHERE col_dgID = {$this['col_dgID']}");
    }
}