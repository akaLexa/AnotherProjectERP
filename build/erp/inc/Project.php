<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.12.2016
 * проект
 **/
namespace build\erp\inc;

use mwce\Connect;
use mwce\date_;
use mwce\Model;

class Project extends Model
{
    /**
     * @param string $prName
     * @param int $founder
     * @param int $serialNum
     * @return Project
     */
    public static function Add($prName,$founder,$serialNum=0){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_project(col_projectName,col_pnID,col_founderID) VALUE('$prName',f_setProjectNum($serialNum),$founder)");
        $prId = $db->lastId('tbl_project');
        return self::getCurModel($prId);
    }

    public static function getModels($params = null)
    {

    }

    /**
     * получить конткретынй проект по его id
     * @param int $id
     * @return Project
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT tp.*,tpn.col_serNum 
FROM 
tbl_project tp,
tbl_project_num tpn 
WHERE 
tpn.col_pnID = tp.col_pnID AND tp.col_projectID =".$id)->fetch(static::class);
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case'col_CreateDate':
                parent::_adding($name.'Legend', date_::transDate($value));
                parent::_adding($name.'LegendDT', date_::transDate($value,true));
                break;
        }
        parent::_adding($name, $value);
    }
}