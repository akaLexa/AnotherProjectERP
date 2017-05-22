<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 22.05.2017
 *
 **/
namespace build\erp\user\m;
use mwce\db\Connect;
use mwce\Models\Model;

class mUserHB extends Model
{

    public static function getModels($params = null)
    {
        $db = Connect::start();
        $filter = '';

        if(!empty($params['fio'])){
            $filter.= " AND tu.col_Sername like '%{$params['fio']}%'";
        }

        if(!empty($params['curGroupList'])){
            $filter.= " AND tgr.col_gID =".$params['curGroupList'];
        }

        if(!empty($params['curRoleList'])){
            $filter.= " AND tu.col_roleID =".$params['curRoleList'];
        }

        return $db->query("SELECT 
  tu.col_uID,
  tu.col_Name,
  tu.col_Sername,
  tu.col_Lastname,
  tu.col_login,
  tu.col_deputyID,
  tu.col_workPhone,
  tu.col_workMail,
  tu.col_privatePhone,
  tu.col_privateMail,
  tur.col_roleName,
  tug.col_gName
FROM
  tbl_user tu,
  tbl_user_groups tug,
  tbl_roles_in_group tgr,
  tbl_user_roles tur
WHERE
  tu.col_isBaned != 1
  AND tur.col_roleID = tu.col_roleID
  AND tgr.col_roleID = tu.col_roleID
  AND tug.col_gID = tgr.col_gID
  $filter")->fetchAll(static::class);
    }

    public static function getCurModel($id)
    {

    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_workPhone':
            case 'col_privatePhone':
            case 'col_privateMail':
            case 'col_Sername':
                if(empty($value))
                    $value = '-';
                break;
            case 'col_workMail':
                if(!empty($this['col_login'])){
                    if(empty($value))
                        $value = $this['col_login'].'@'.$_SERVER['HTTP_HOST'];
                }

        }
        parent::_adding($name, $value);
    }
}