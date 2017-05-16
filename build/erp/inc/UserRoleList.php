<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 16.05.2017
 * список ролей пользователя
 **/
namespace build\erp\inc;
use build\erp\inc\interfaces\iConfigurable;
use mwce\db\Connect;
use mwce\Models\Model;

class UserRoleList extends Model implements iConfigurable
{
    private static $groupList = [];
    private static $groupMultiList = [];

    /**
     * @param null $params
     * @return mixed|UserRoleList
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        if(empty($params['group'])){
            $q = "SELECT * FROM tbl_user_roles ORDER BY col_roleName";
        }
        else{
            $q = "SELECT 
  tur.*,
  tig.col_gID 
FROM 
  tbl_user_roles tur,
  tbl_roles_in_group rig
WHERE
  tur.col_roleID = rig.col_roleID
  and rig.col_gID = ".$params['group'];
        }

        return $db->query($q)->fetchAll();
    }

    /**
     * @param $id
     * @return mixed|UserRoleList
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT 
  tur.*,
  tig.col_gID 
FROM 
  tbl_user_roles tur,
  tbl_roles_in_group rig
WHERE
  rig.col_roleID = tur.col_roleID
  and tur.col_roleID = $id")->fetch();
    }

    /**
     * массив для генерации выпадающего списка
     * [
     *  [1] => позиция 1
     *  [2] => позиция 2
     * ]
     * @return array
     */
    public static function getSelectList()
    {
        if(empty(self::$groupList)){
            $db = Connect::start();

            $query = $db->query("SELECT * FROM tbl_user_roles ORDER BY col_roleName");

            while ($res = $query->fetch()){
                self::$groupList[$res['col_roleID']] = $res['col_roleName'];
            }

            asort(self::$groupList);
        }
        return self::$groupList;
    }

    /**
     * массив для генерации списка, где можно
     * выбрать несколько значений
     * [
     *   [0]=>['id' => 1,'item' => 'Позиция 1'],
     *   [1]=>['id' => 2,'item' => 'Позиция 2'],
     * ]
     * @return mixed
     */
    public static function getMultiSelectList()
    {
        if(empty(self::$groupMultiList)){
            $db = Connect::start();

            $query = $db->query("SELECT * FROM tbl_user_roles ORDER BY col_roleName");

            while ($res = $query->fetch()){
                self::$groupMultiList[] = [ 'id' => $res['col_roleID'], 'item' => $res['col_roleName'] ];
            }
        }
        return self::$groupMultiList;
    }
}