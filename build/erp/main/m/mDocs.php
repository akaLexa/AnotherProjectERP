<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 21.01.2017
 *
 **/
namespace build\erp\main\m;
use mwce\Connect;
use mwce\date_;
use mwce\Model;
use mwce\Tools;

class mDocs extends Model
{
    /**
     * @param null|array $params
     * @return bool|array|mDocs
     */
    public static function getModels($params = null)
    {
        if(is_null($params) || empty($params['role']))
            return false;

        $filter = '';

        if(!empty($params['isDel']))
            $filter.= ' AND tf.col_isDel = 1';
        else
            $filter.= ' AND tf.col_isDel = 0';

        if(!empty($params['isFolder']))
            $filter.= ' AND tf.col_isFolder = 1';

        if(!empty($params['uploader']))
            $filter.= ' AND tf.col_uploaderID = '.$params['uploader'];

        if(!empty($params['deleter']))
            $filter.= ' AND tf.col_deleterID = '.$params['deleter'];

        if(!empty($params['group']))
            $filter.= ' AND tf.col_groupID = '.$params['group'];

        if(!empty($params['files']))
            $filter.= ' AND tf.col_fID IN ('.$params['files'].')';
        else{
            if(!empty($params['subId']))
                $filter.= ' AND tf.col_parentID = '.$params['subId'];
            else
                $filter.= ' AND tf.col_parentID is null';
        }
        $db = Connect::start();

        return $db->query("SELECT
  tf.*,
  tdga.col_access,
  thdg.col_docGroupName,
  f_getUserFIO(tf.col_uploaderID) AS col_uploader,
  f_getUserFIO(tf.col_deleterID) AS col_deleter,
  IF(tf.col_parentID IS NOT NULL,NULL,(SELECT col_parentID FROM tbl_files WHERE col_fID = tf.col_parentID)) AS col_parent
FROM 
  tbl_doc_group_access tdga,
  tbl_hb_doc_group thdg,
  tbl_files tf
WHERE
   tdga.col_access > 0
  AND tdga.col_roleID = {$params['role']}
  AND tf.col_groupID = tdga.col_dgID
  AND thdg.col_dgID = tdga.col_dgID
  $filter
ORDER BY tf.col_isFolder DESC, thdg.col_docGroupName ASC")->fetchAll(static::class);
    }

    /**
     * @param int $id
     * @return mixed|mDocs
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT
  tf.*,
  thdg.col_docGroupName,
  f_getUserFIO(tf.col_uploaderID) AS col_uploader,
  f_getUserFIO(tf.col_deleterID) AS col_deleter,
  IF(tf.col_parentID IS NOT NULL,NULL,(SELECT col_parentID FROM tbl_files WHERE col_fID = tf.col_parentID)) AS col_parent
FROM 
  tbl_hb_doc_group thdg,
  tbl_files tf
WHERE
  tf.col_fID = $id
  AND thdg.col_dgID = tf.col_groupID")->fetch(static::class);
    }

    /**
     * пометить на удаление папку/файл
     * @param int $fID
     * @param int $role
     * @param int $user
     */
    public static function delFolder($fID,$role,$user){
        $db = Connect::start();
        $info = $db->query("SELECT
  tf.*,
  tdga.col_access,
  thdg.col_docGroupName,
  f_getUserFIO(tf.col_uploaderID) AS col_uploader,
  f_getUserFIO(tf.col_deleterID) AS col_deleter,
  IF(tf.col_parentID IS NOT NULL,NULL,(SELECT col_parentID FROM tbl_files WHERE col_fID = tf.col_parentID)) AS col_parent
FROM 
  tbl_doc_group_access tdga,
  tbl_hb_doc_group thdg,
  tbl_files tf
WHERE
   tdga.col_access > 0
  AND tdga.col_roleID = $role
  AND tf.col_groupID = tdga.col_dgID
  AND thdg.col_dgID = tdga.col_dgID
  AND tf.col_fID = $fID")->fetch();

        if(!empty($info)){
            $db->exec("UPDATE tbl_files SET col_isDel = 1,col_deleterID=$user,col_dDate = NOW() WHERE col_fID = $fID");
            $db->exec("UPDATE tbl_files SET col_isDel = 1,col_deleterID=$user,col_dDate = NOW() WHERE col_parentID = $fID");
        }
    }

    public static function delFiles($fIDs,$role,$user){
        $db = Connect::start();
        $info = $db->query("SELECT
  tf.*,
  tdga.col_access,
  thdg.col_docGroupName,
  f_getUserFIO(tf.col_uploaderID) AS col_uploader,
  f_getUserFIO(tf.col_deleterID) AS col_deleter,
  IF(tf.col_parentID IS NOT NULL,NULL,(SELECT col_parentID FROM tbl_files WHERE col_fID = tf.col_parentID)) AS col_parent
FROM 
  tbl_doc_group_access tdga,
  tbl_hb_doc_group thdg,
  tbl_files tf
WHERE
   tdga.col_access > 0
  AND tdga.col_roleID = $role
  AND tf.col_groupID = tdga.col_dgID
  AND thdg.col_dgID = tdga.col_dgID
  AND tf.col_fID in ($fIDs)")->fetchAll();


        if(!empty($info)){
            $q = '';
            foreach ($info as $inf){
                if(!empty($q))
                    $q.=';';
                $q.="UPDATE tbl_files SET col_isDel = 1,col_deleterID=$user,col_dDate = NOW() WHERE col_fID = {$inf['col_fID']}; UPDATE tbl_files SET col_isDel = 1,col_deleterID=$user,col_dDate = NOW() WHERE col_parentID = {$inf['col_fID']}";
            }
            if(!empty($q))
                $db->exec($q);
        }
    }

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

    /**
     * @param int $id
     * @param int $role
     * @return mDocs
     */
    public static function getFileByRole($id,$role){
        $db = Connect::start();
        return $db->query("SELECT
  tf.*,
  tdga.col_access,
  thdg.col_docGroupName,
  f_getUserFIO(tf.col_uploaderID) AS col_uploader,
  f_getUserFIO(tf.col_deleterID) AS col_deleter,
  IF(tf.col_parentID IS NOT NULL,NULL,(SELECT col_parentID FROM tbl_files WHERE col_fID = tf.col_parentID)) AS col_parent
FROM 
  tbl_doc_group_access tdga,
  tbl_hb_doc_group thdg,
  tbl_files tf
WHERE
   tdga.col_access > 0
  AND tdga.col_roleID = $role
  AND tf.col_groupID = tdga.col_dgID
  AND thdg.col_dgID = tdga.col_dgID
  AND tf.col_fID = $id")->fetch(static::class);
    }

    /**
     * @param int $id
     * @return int
     */
    public static function getUpperParent($id){
        $db = Connect::start();
        $res = $db->query("SELECT col_parentID FROM tbl_files WHERE col_fID = $id")->fetch();
        return !empty($res['col_parentID']) ? $res['col_parentID'] : 0;
    }

    /**
     * @param string $name
     * @param int $parent
     * @param int $user
     * @param int $group
     * @param int $project
     * @return int
     */
    public static function addFolder($name,$parent,$user,$group,$project){
        if(empty($parent))
            $parent = 'NULL';
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_files (col_fName,col_parentID,col_uploaderID,col_groupID,col_projectID,col_isFolder) VALUES('$name',$parent,$user,$group,$project,1)");
        return $db->lastId('tbl_files');
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_cDate':
            case 'col_dDate':
                parent::_adding($name.'Legend', date_::transDate($value,true));
                break;
            case 'col_parent':
                if(empty($value))
                    parent::_adding($name.'Legend', 0);
                else
                    parent::_adding($name.'Legend', $value);

                break;
        }
        parent::_adding($name, $value);
    }
}