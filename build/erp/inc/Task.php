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
     * список возможных связей задач
     * @var array
     */
    public static $resps = array(
        0 =>'Нет',
        1 =>'Окончаие -> Начало',
        2 =>'Начало -> Начало',
        3 =>'Окончание -> Окончание',
    );

    /**
     * @param array $params
     */
    public static function Add($params){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_tasks (col_taskName,col_StatusID,col_initID,col_respID,col_curatorID,col_pstageID,col_taskDesc,col_createDate,col_startPlan,col_endPlan,col_autoStart,col_taskDur,col_fromPlan,col_nextID,col_bonding) VALUE({$params['col_taskName']},{$params['col_StatusID']},{$params['col_initID']},{$params['col_respID']},{$params['col_curatorID']},{$params['col_pstageID']},{$params['col_taskDesc']},{$params['col_createDate']},{$params['col_startPlan']},{$params['col_endPlan']},{$params['col_autoStart']},{$params['col_taskDur']},{$params['col_fromPlan']},{$params['col_nextID']},{$params['col_bonding']})");

        $db->exec("CALL sp_setTaskPlanQuenue({$params['col_pstageID']},'{$params['col_startPlan']}',{$params['col_nextID']});");
        $db->closeCursor();
    }

    /**
     * возвращает список задач в стадии для создания связи
     * @param int $stageId
     * @param null|int $taskID
     * @return array
     */
    public static function getParentTasks($stageId,$taskID = null){
        $db = Connect::start();
        if(!is_null($taskID))
            $taskID = " AND col_taskID != $taskID";
        else
            $taskID = '';

        $list = array(0=>'Нет');
        $q = $db->query("SELECT col_taskID, col_taskName, col_seq FROM tbl_tasks WHERE col_pstageID = $stageId  $taskID");

        while ($r = $q->fetch()){
            $list[$r['col_taskID']] = "{$r['col_taskID']}.{$r['col_taskName']}";
        }
        return $list;
    }

    /**
     * возвращает кол-во дней от старта стадии
     * @param int $orderStage
     * @param int $curTaskID
     * @return mixed
     */
    public static function getSumDur($orderStage,$curTaskID = 0){
        $db = Connect::start();

        if($curTaskID >0){
            $curTaskID = "AND tt.col_taskID != $curTaskID
  AND tt.col_seq < (SELECT col_seq FROM tbl_tasks WHERE col_taskID = $curTaskID)";
        }
        else{
            $curTaskID ='';
        }

        $res = $db->query("SELECT 
  COALESCE(SUM(tt.col_taskDur),0) AS totalDur
FROM 
  tbl_tasks tt
WHERE
  tt.col_pstageID = $orderStage $curTaskID")->fetch();

        return $res['totalDur'];
    }

    public static function getModels($params = null)
    {

    }

    public static function getCurModel($id)
    {

    }
}