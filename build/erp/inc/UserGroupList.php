<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 16.05.2017
 *
 **/
namespace build\erp\inc;
use build\erp\inc\interfaces\iConfigurable;
use mwce\db\Connect;
use mwce\Models\Model;

class UserGroupList extends Model implements iConfigurable
{

    private static $roleList = [];
    private static $roleMultiList = [];

    /**
     * @param null $params
     * @return mixed|UserGroupList
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_user_groups ORDER BY col_gName")->fetchAll();
    }

    /**
     * @param $id
     * @return mixed|UserGroupList
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_user_groups WHERE col_gID = $id")->fetch();
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
        if(empty(self::$roleList)) {
            $db = Connect::start();
            $query = $db->query("SELECT * FROM tbl_user_groups ORDER BY col_gName");
            while ($res = $query->fetch()) {
                self::$roleList[$res['col_gID']] = $res['col_gName'];
            }
            asort(self::$roleList);
        }

        return self::$roleList;
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
        if (empty(self::$roleMultiList)) {
            $db = Connect::start();
            $query = $db->query("SELECT * FROM tbl_user_groups ORDER BY col_gName");
            while ($res = $query->fetch()) {
                self::$roleMultiList [] = [ 'id' => $res['col_gID'], 'item' => $res['col_gName'] ];
            }
        }

        return self::$roleMultiList;
    }
}