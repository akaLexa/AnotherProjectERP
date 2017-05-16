<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.12.2016
 * Пользователи системы
 **/
namespace build\erp\inc;
use build\erp\inc\interfaces\iConfigurable;
use mwce\db\Connect;
use mwce\Tools\Date;
use mwce\Models\Model;

class User extends Model implements iConfigurable
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
 tug.*,
 if(tu.col_deputyID is not NULL ,f_getUserFIO(tu.col_deputyID),'-') as col_deputy
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
     * @param null $grpID
     * @return array
     */
    public static function getRoleList($grpID = null){
        if(is_null($grpID))
            $grpID =0;

        if(empty(self::$sdata['RoleList'][$grpID])){
            $db = Connect::start();
            $roles = array();
            if($grpID < 1)
                $query = $db->query("SELECT * FROM tbl_user_roles ORDER BY col_roleName"); //todo: есть похожая функция в UserRoleList подумать, как убрать дублирование кода. и нужно ли.
            else
                $query = $db->query("SELECT 
  tur.* 
FROM 
  tbl_user_roles tur,
  tbl_roles_in_group rig
WHERE
  tur.col_roleID = rig.col_roleID
  and rig.col_gID = $grpID");

            while ($res = $query->fetch()){
                $roles[$res['col_roleID']] = $res['col_roleName'];
            }

            self::$sdata['RoleList'][$grpID] = $roles;
            asort(self::$sdata['RoleList'][$grpID]);
        }
        return self::$sdata['RoleList'][$grpID];
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
     * @param bool $withoutUniv без универсальных групп
     * @return array
     */
    public static function getGropList($withoutUniv = true){
        if(empty(self::$sdata['GropList'])){
            $db = Connect::start();
            $roles = array();
            $query = $db->query("SELECT * FROM tbl_user_groups ORDER BY col_gName");
            while ($res = $query->fetch()){
                if($withoutUniv && $res['col_gID'] > 1 && $res['col_gID'] <= 4)
                    continue;
                $roles[$res['col_gID']] = $res['col_gName'];
            }

            self::$sdata['GropList'] = $roles;
            asort(self::$sdata['GropList']);
        }
        return self::$sdata['GropList'];
    }

    /**
     * список всех пользователей
     * @param bool $withBlocked включая заблокированных?
     * @return array
     */
    public static function getUserList($withBlocked = false){

        $db = Connect::start();
        $ar = array();
        $f = '';

        if($withBlocked)
            $f =' WHERE tu.col_isBaned = 1';
        else
            $f =' WHERE tu.col_isBaned = 0';

        $q = $db->query("SELECT CONCAT(tu.col_Sername,' ',COALESCE(LEFT(tu.col_Name,1),'?'),'.',COALESCE(LEFT(tu.col_Lastname,1),'?'),'.') as col_uName, tu.col_uID FROM tbl_user tu $f ORDER by tu.col_Sername");

        while ($r = $q->fetch()){
            $ar[$r['col_uID']] = $r['col_uName'];
        }

        return $ar;
    }

    /**
     * список активных пользователей по стадии
     * @param int $stageID
     * @return array
     */
    public static function getUserListByStage($stageID){
        $db = Connect::start();
        $ar = array();
        $q = $db->query("SELECT
  tu.col_uID,
  CONCAT(tu.col_Sername,' ',LEFT(tu.col_Name,1),'.',COALESCE(CONCAT(LEFT(tu.col_Sername,1),'.'),'')) AS col_user
FROM
  tbl_project_stage_group tpsg,
  tbl_project_stage_role tpsr,
  tbl_user tu
WHERE
  tpsg.col_StageID = $stageID
  AND tpsr.col_psgID = tpsg.col_psgID
  and tu.col_roleID = tpsr.col_roleID
  AND tu.col_isBaned != 1");

        while ($r = $q->fetch()){
            $ar[$r['col_uID']] = $r['col_user'];
        }

        return $ar;
    }

    /**
     * список пользователей по id группы
     * @param int $groupId
     * @param bool $withBlocked
     * @return array
     */
    public static function getUserGropuList($groupId,$withBlocked = false){

        $db = Connect::start();
        $ar = array();
        $f = '';

        if($withBlocked)
            $f =' AND tu.col_isBaned = 1';

        $q = $db->query("SELECT 
  CONCAT(tu.col_Sername,' ',COALESCE(LEFT(tu.col_Name,1),'?'),'.',COALESCE(LEFT(tu.col_Lastname,1),'?'),'.') as col_uName, 
  tu.col_uID 
FROM 
  tbl_user tu,
  tbl_roles_in_group trig
WHERE
  trig.col_roleID = tu.col_roleID
  AND trig.col_gID = $groupId
$f 
ORDER by tu.col_Sername");

        while ($r = $q->fetch()){
            $ar[$r['col_uID']] = $r['col_uName'];
        }

        return $ar;
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_regDate':
            case 'col_blockDate':
            case 'col_banDate':
                if(!empty($value)){
                    parent::_adding($name.'Legend', Date::transDate($value,true));
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
        return self::getUserList();
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
        $_ = self::getUserList();
        $return = [];

        foreach ($_ as $id => $user){
            $return[] = array(
                'id' => $id,
                'item' => $user
            );
        }

        return $return;
    }
}