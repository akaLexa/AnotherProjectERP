<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 22.12.2016
 *
 **/
namespace build\erp\inc;
use mwce\Connect;
use mwce\Model;

class Task extends Model
{
    /**
     * @param array $params
     */
    public static function Add($params){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_tasks (col_taskName,col_StatusID,col_initID,col_respID,col_curatorID,col_pstageID,col_taskDesc,col_createDate,col_startPlan,col_endPlan,col_autoStart,col_taskDur) VALUE({$params['col_taskName']},{$params['col_StatusID']},{$params['col_initID']},{$params['col_respID']},{$params['col_curatorID']},{$params['col_pstageID']},{$params['col_taskDesc']},{$params['col_createDate']},{$params['col_startPlan']},{$params['col_endPlan']},{$params['col_autoStart']},{$params['col_taskDur']})");
    }
    public static function getModels($params = null)
    {

    }

    public static function getCurModel($id)
    {

    }
}