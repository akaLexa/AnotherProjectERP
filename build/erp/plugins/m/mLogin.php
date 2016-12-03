<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.12.2016
 *
 **/
namespace build\erp\plugins\m;
use build\erp\inc\User;
use mwce\Connect;

class mLogin extends User
{
    public static function auth($user,$pwd){
        $db= Connect::start();

        $filter = "AND tu.col_pwd = '".self::PwdCrypt($pwd)."' ";
        $filter.= "AND tu.col_login = '$user'";
        return $db->query("SELECT 
 tu.*,
 ur.*,
 tug.*
FROM 
tbl_user tu,
tbl_user_roles ur 
LEFT JOIN tbl_roles_in_group ug ON ug.col_roleID = ur.col_roleID
LEFT JOIN tbl_user_groups tug ON tug.col_gID = ug.col_gID
WHERE ur.col_roleID = tu.col_roleID
$filter")->fetch(static::class);
    }
}