<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.12.2016
 * Пользователи системы
 **/
namespace build\erp\inc;
use mwce\Connect;
use mwce\date_;
use mwce\Model;

class User extends Model
{
    /**
     * @param null|array $params
     * array|User
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        $filter = '';

        //по ид пользователя
        if(!empty($params['col_uID'])){
            $filter.= ' AND tu.col_uID ='.$params['col_uID'];
        }

        //по имени
        if(!empty($params['col_Name'])){
            $filter.= " AND tu.col_Name like '%{$params['col_Name']}%'";
        }

        //по фамилии
        if(!empty($params['col_Sername'])){
            $filter.= " AND tu.col_Sername like '%{$params['col_Sername']}%'";
        }

        //заблокированный или нет
        if(isset($params['col_isBaned'])){
            $filter.=' AND tu.col_isBaned = '.$params['col_isBaned'];
        }

        //по группе
        if(!empty($params['col_gID'])){
            $filter.=' AND tug.col_gID = '.$params['col_gID'];
        }

        //по роли
        if(!empty($params['col_roleID'])){
            $filter.=' AND tu.col_roleID = '.$params['col_roleID'];
        }

        return $db->query("SELECT 
 tu.*,
 ur.*,
 tug.*
FROM 
tbl_user tu,
tbl_user_roles ur 
LEFT JOIN tbl_roles_in_group ug ON ug.col_roleID = ur.col_roleID
LEFT JOIN tbl_user_groups tug ON tug.col_gID = ug.col_gID
WHERE
ur.col_roleID = tu.col_roleID
$filter
ORDER BY tu.col_Sername")->fetchAll(static::class);
    }

    /**
     * @param int $id
     * @return User
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();

        return $db->query("SELECT 
 tu.*,
 ur.*,
 tug.*
FROM 
tbl_user tu,
tbl_user_roles ur 
LEFT JOIN tbl_roles_in_group ug ON ug.col_roleID = ur.col_roleID
LEFT JOIN tbl_user_groups tug ON tug.col_gID = ug.col_gID
WHERE
ur.col_roleID = tu.col_roleID
AND tu.col_uID = $id")->fetch(static::class);
    }

    /**
     * Список ролей
     * @return array
     */
    public static function getRoleList(){
        if(empty(self::$sdata['RoleList'])){
            $db = Connect::start();
            $roles = array();
            $query = $db->query("SELECT * FROM tbl_user_roles ORDER BY col_roleName");
            while ($res = $query->fetch()){
                $roles[$res['col_roleID']] = $res['col_roleName'];
            }

            self::$sdata['RoleList'] = $roles;
            asort(self::$sdata['RoleList']);
        }
        return self::$sdata['RoleList'];
    }

    /**
     * хеширование пароля
     * @param string $pwd
     * @return string
     */
    public static function PwdCrypt($pwd){
        return hash('sha256',$pwd);
    }

    /**
     * Список групп
     * @return array
     */
    public static function getGropList(){
        if(empty(self::$sdata['GropList'])){
            $db = Connect::start();
            $roles = array();
            $query = $db->query("SELECT * FROM tbl_user_groups ORDER BY col_gName");
            while ($res = $query->fetch()){
                if($res['col_gID'] == 4)
                    continue;
                $roles[$res['col_gID']] = $res['col_gName'];
            }

            self::$sdata['GropList'] = $roles;
            asort(self::$sdata['GropList']);
        }
        return self::$sdata['GropList'];
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_regDate':
            case 'col_blockDate':
                if(!empty($value)){
                    parent::_adding($name.'Legend', date_::transDate($value,true));
                }
                break;
            case 'col_isBaned':
                if($value == 1){
                    parent::_adding($name.'Legend', 'Блокирован');
                }
                else{
                    parent::_adding($name.'Legend', 'Активен');
                }
                break;
        }
        parent::_adding($name, $value);
    }
}