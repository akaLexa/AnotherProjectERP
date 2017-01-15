<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.01.2017
 *
 **/
namespace build\erp\tabs\m;
use mwce\Connect;
use mwce\Model;

class mDocs extends Model
{
    public static function getDocGroups($roleID){
        if(empty(self::$sdata['DocGroups'.$roleID])) {
            $db = Connect::start();
            $q = $db->query("SELECT
  thdg.col_dgID,
  thdg.col_docGroupName
FROM 
  tbl_hb_doc_group thdg,
  tbl_doc_group_access tdga
WHERE
  thdg.col_isDel != 1
  AND tdga.col_dgID = thdg.col_dgID
  AND tdga.col_access > 0
  AND tdga.col_roleID = $roleID");
            $list = array();
            while ($r = $q->fetch()) {
                $list[$r['col_dgID']] = $r['col_docGroupName'];
            }
            self::$sdata['DocGroups'.$roleID] = $list;
        }
        return self::$sdata['DocGroups'.$roleID];

    }

    public static function getModels($params = null)
    {
        // TODO: Implement getModels() method.
    }

    public static function getCurModel($id)
    {
        // TODO: Implement getCurModel() method.
    }
}