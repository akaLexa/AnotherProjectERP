<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.12.2016
 * добавление пользователя
 **/
namespace build\erp\adm\m;
use build\erp\inc\User;
use mwce\Connect;

class mUser extends User
{
    /**
     * @param array $params
     */
    public static function AddUser($params){
        $db = Connect::start();
        $params['pwd'] = self::PwdCrypt($params['pwd']);
        $db->exec("INSERT INTO tbl_user (col_Name,col_Sername,col_Lastname,col_login,col_pwd,col_roleID,col_isBaned)VALUE('{$params['name']}','{$params['surname']}','{$params['lastname']}','{$params['login']}','{$params['pwd']}',{$params['role']},{$params['block']})");
    }
}