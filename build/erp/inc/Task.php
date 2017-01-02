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
use mwce\traits\tInsert;
use mwce\traits\tUpdate;

class Task extends Model
{
    use tUpdate;
    use tInsert;

    /**
     * список возможных связей задач
     * @var array
     */
    public static $resps = array(
      //  0 =>'Нет',
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

    public function edit($params){
        $qString = self::genUpdate($params);
        if(!empty($qString)){
            $this->db->exec("UPDATE tbl_tasks SET $qString WHERE col_taskID = {$this['col_taskID']}");
            $this->db->exec("CALL sp_setTaskPlanQuenue({$this['col_pstageID']},null,{$params['col_nextID']});");
            $this->db->closeCursor();
        }

    }

    public function delete(){
        if($this['col_StatusID'] == 5){
            $this->db->exec("UPDATE tbl_tasks SET col_bonding = 0, col_nextID = null WHERE col_nextID = {$this['col_taskID']}");
            $this->db->exec("DELETE FROM tbl_tasks WHERE col_taskID = {$this['col_taskID']}");
        }
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

        $list = array(0=>'Первая задача');
        $q = $db->query("SELECT col_taskID, col_taskName, col_seq FROM tbl_tasks WHERE col_pstageID = $stageId  $taskID");

        while ($r = $q->fetch()){
            $list[$r['col_taskID']] = "{$r['col_seq']}.{$r['col_taskName']}";
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
  tt.col_pstageID = $orderStage $curTaskID AND tt.col_nextID is null")->fetch();

        return $res['totalDur'];
    }

    public static function getModels($params = null)
    {

    }

    /**
     * @param int $id
     * @return mixed|Task
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT
  tt.*,
  ths.col_StatusName,
  f_getUserFIO(tt.col_initID) AS col_init,
  f_getUserFIO(tt.col_respID) AS col_resp,
  f_getUserFIO(tt.col_curatorID) AS col_curator
FROM
  tbl_tasks tt,
  tbl_hb_status ths
WHERE
  ths.col_StatusID = tt.col_StatusID
  AND tt.col_taskID = $id")->fetch(static::class);
    }
}