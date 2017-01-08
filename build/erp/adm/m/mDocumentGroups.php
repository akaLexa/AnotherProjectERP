<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 08.01.2017
 *
 **/
namespace build\erp\adm\m;

use mwce\Connect;
use mwce\Model;

class mDocumentGroups extends Model
{
    /**
     * @param $name
     * @return mDocumentGroups
     */
    public static function Add($name){
        $db = Connect::start();
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
     * @param $id
     * @return mDocumentGroups
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_hb_doc_group WHERE col_gaID = $id")->fetch(static::class);
    }

    public function delete(){
        $this->db->exec("UPDATE tbl_hb_doc_group SET col_isDel = 1 WHERE col_gaID = {$this['col_gaID']}");
    }
}