<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 22.12.2016
 *
 **/
namespace build\erp\inc;
use mwce\Connect;
use mwce\date_;
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

    /**
     * @param null $params
     * @return bool| Task
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        $query = self::qBuilder($params);
        if(empty($query))
            return false;

        $q = "SELECT
  tt.*,
  ths.col_StatusName,
  f_getUserFIO(tt.col_initID) AS col_init,
  f_getUserFIO(tt.col_respID) AS col_resp,
  f_getUserFIO(tt.col_curatorID) AS col_curator,
  COALESCE(tt.col_startFact,tt.col_startPlan) AS col_dateStart,
  DATEDIFF(COALESCE(tt.col_endFact,tt.col_endPlan),COALESCE(tt.col_startFact,tt.col_startPlan)) AS col_dayDifs,
  tps.col_pstageID,
  tp.col_projectName,
  tp.col_pnID,
  tp.col_founderID
$query
";
        if(isset($params['min'])){
            $q.=" LIMIT ".$params['min'];
            if(!empty($params['max']))
                $q.=" , ".$params['max'];
        }

        return $db->query($q)->fetchAll(static::class);
    }

    /**
     * @param null|array $params
     * @return string
     */
    protected static function qBuilder($params = null){
        //
        $q = 'FROM
  tbl_tasks tt,
  tbl_hb_status ths,
  tbl_project_stage tps,
  tbl_project tp
WHERE
  ths.col_StatusID = tt.col_StatusID
  
  AND tp.col_projectID = tps.col_projectID';

        if(!empty($params['projectID']))
            $q.=" AND tps.col_projectID = ".$params['projectID']." AND tps.col_statusID IN (1,4)";
        else
            $q.=" AND tps.col_pstageID = tt.col_pstageID";

        if(!empty($params['taskName']))
            $q.= " AND tt.col_taskName like '%{$params['taskName']}%'";

        if(!empty($params['taskStatus']))
            $q.= " AND tt.col_StatusID =".$params['taskStatus'];

        if(!empty($params['taskInit']))
            $q.= " AND tt.col_initID =".$params['taskInit'];

        if(!empty($params['taskResp']))
            $q.= " AND tt.col_respID =".$params['taskResp'];

        if(!empty($params['taskCurator']))
            $q.= " AND tt.col_curatorID =".$params['taskCurator'];

        if(!empty($params['dbegin'])){
            if($params['taskStatus'] == 5){
                $q.= " AND tt.col_startPlan BETWEEN '{$params['dbegin']} 00:00:00' AND '{$params['dbegin']} 23:59:59'";
            }
            else{
                $q.= " AND tt.col_startFact BETWEEN '{$params['dbegin']} 00:00:00' AND '{$params['dbegin']} 23:59:59'";
            }
        }

        if(!empty($params['endPlan']))
            $q.= " AND tt.col_endPlan BETWEEN '{$params['endPlan']} 00:00:00' AND '{$params['endPlan']} 23:59:59'";

        if(!empty($params['endFact']))
            $q.= " AND tt.col_endFact BETWEEN '{$params['endFact']} 00:00:00' AND '{$params['endFact']} 23:59:59'";

        if(!empty($params['projectName']))
            $q.= " AND tp.col_projectName like '%{$params['projectName']}%'";

        return $q;
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

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_createDate':
            case 'col_startPlan':
            case 'col_endPlan':
            case 'col_dateStart':
                parent::_adding($name.'Legend', date_::transDate($value));
                parent::_adding($name.'LegendTD', date_::transDate($value,true));
                break;
        }
        parent::_adding($name, $value);
    }
}