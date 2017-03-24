<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.12.2016
 * добавление пользователя
 **/
namespace build\erp\adm\m;
use build\erp\inc\User;
use mwce\db\Connect;

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

    public function editUser($params)
    {
        if(!empty($params['pwd'])){
            $params['pwd'] = ",col_pwd ='".self::PwdCrypt($params['pwd']."'");
        }
        else{
            $params['pwd'] ='';
        }

        if($params['block'] != 0){
            $bdate = ',col_banDate = NOW()';
        }
        else{
            $bdate = '';
        }

        $this->db->exec("UPDATE tbl_user SET col_Name='{$params['name']}',col_Sername ='{$params['surname']}',col_Lastname='{$params['lastname']}',col_login='{$params['login']}',{$params['pwd']} col_roleID = {$params['role']},col_isBaned = {$params['block']} {$bdate} WHERE col_uID = {$this['col_uID']}");
    }
}