<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 11.03.2017
 *
 **/
namespace build\erp\inc;
use mwce\Connect;
use mwce\Model;
use mwce\Tools;

class PlanStage extends Model
{
    private $curPlan = array();

    /**
     * @param null|array $params
     * @return mixed|PlanStage
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        return $db->query("SELECT
  tppl.col_pstageID,
  tppl.col_stageSeq,
  tppl.col_stageDur,
  tppl.col_stageID,
  tppl.col_dateStartPlan,
  tppl.col_dateEndPlan,
  tppl.col_taskID,
  tppl.col_taskName,
  tppl.col_taskStart,
  tppl.col_taskEnd,
  tppl.col_taskSeq,
  tppl.col_taskDur,
  tppl.col_taskNextID,
  tppl.col_bonding
FROM
  tbl_project_plan_name tppn,
  tbl_project_plan_list tppl
WHERE
  tppl.col_ppnID = tppn.col_ppnID
  AND tppn.col_ppnID = ".$params['id'])->fetchAll(static::class);
    }

    /**
     * @param int $id
     * @return mixed|PlanStage
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT * FROM tbl_project_plan_name WHERE col_ppnID = $id")->fetch(static::class);
    }

    /**
     * список существующих сохраненных шаблонов плана
     * @return array
     */
    public static function getSavedList(){
        $db = Connect::start();
        $list = array();
        $q = $db->query("SELECT * FROM tbl_project_plan_name ORDER BY col_planName");
        while ($res = $q->fetch()){
            $list[$res['col_ppnID']] = $res['col_planName'];
        }
        return $list;
    }

    /**
     * @param int $id
     */
    public static function delPlan($id){
        $db = Connect::start();
        $db->exec("DELETE FROM tbl_project_plan_name WHERE col_ppnID = $id");
    }

    /**
     * @param string $name
     * @param int $projectID
     * @return PlanStage
     */
    public static function Add($name,$projectID){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_project_plan_name (col_planName) VALUE('$name')");
        $id = $db->lastId('tbl_project_plan_name');
        $db->exec("INSERT INTO tbl_project_plan_list (col_ppnID,col_pstageID,col_stageSeq,col_stageDur,col_stageID,col_dateStartPlan,col_dateEndPlan,col_taskID,col_taskName,col_taskStart,col_taskEnd,col_taskSeq,col_taskDur,col_taskNextID,col_bonding,col_taskDesc)
        SELECT $id,tps.col_pstageID,tps.col_seq,tps.col_duration,tps.col_stageID,tps.col_dateStartPlan,tps.col_dateEndPlan,tt.col_taskID,tt.col_taskName,tt.col_startPlan,tt.col_endPlan,tt.col_seq,tt.col_taskDur,tt.col_nextID,tt.col_bonding,tt.col_taskDesc
FROM 
  tbl_project_stage tps 
    LEFT JOIN tbl_tasks tt ON tt.col_pstageID = tps.col_pstageID AND tt.col_startPlan is NOT NULL
WHERE 
  tps.col_projectID = $projectID  
  AND tps.col_statusID IN (1,3,4,5)
  AND tps.col_dateStartPlan is NOT NULL
ORDER BY 
  tps.col_seq, 
  tt.col_seq ASC, 
  tt.col_bonding, 
  tps.col_pstageID ASC, 
  tt.col_startFact, 
  tt.col_taskDur DESC, 
  tt.col_taskID ASC");
        return self::getCurModel($id);
    }

    public static function ExportToPlan($id,$projectID){
        $db = Connect::start();
        $stageInfo = $db->query("SELECT * FROM tbl_project_stage WHERE col_projectID=$projectID order BY col_pstageID DESC limit 1")->fetch();
        $curSeq = $stageInfo['col_seq'] + 1;
        $curStage = 0;
        $curTask = 0;
        $stage = 0;
        $tasksPool = [];

        $q = $db->query("SELECT
  tppl.col_pstageID,
  tppl.col_stageSeq,
  tppl.col_stageDur,
  tppl.col_stageID,
  tppl.col_dateStartPlan,
  tppl.col_dateEndPlan,
  tppl.col_taskID,
  tppl.col_taskName,
  tppl.col_taskStart,
  tppl.col_taskEnd,
  tppl.col_taskSeq,
  tppl.col_taskDur,
  tppl.col_taskNextID,
  tppl.col_bonding,
  tppl.col_taskDesc
FROM
  tbl_project_plan_name tppn,
  tbl_project_plan_list tppl
WHERE
  tppl.col_ppnID = tppn.col_ppnID
  AND tppn.col_ppnID = $id");


        while ($res = $q->fetch()){
            if($curStage != $res['col_pstageID']){
                $db->exec("INSERT INTO tbl_project_stage (col_projectID,col_statusID,col_dateCreate,col_dateStartPlan,col_dateEndPlan,col_stageID,col_seq,col_duration) VALUE ($projectID,5,NOW(),'{$res['col_dateStartPlan']}','{$res['col_dateEndPlan']}',{$res['col_stageID']},{$curSeq},{$res['col_stageDur']})");
                $stage = $db->lastId('tbl_project_stage');

                $curStage = $res['col_pstageID'];
                $curSeq++;
            }

            if(!empty($res['col_taskID'])){
                if(!empty($res['col_taskDesc']))
                    $res['col_taskDesc'] = "'{$res['col_taskDesc']}'";
                else
                    $res['col_taskDesc'] = 'null';

                if(!empty($res['col_taskNextID'])){
                    if(!empty($tasksPool[$res['col_taskNextID']]))
                        $res['col_taskNextID'] = $tasksPool[$res['col_taskNextID']];
                    else
                        $res['col_taskNextID'] = 'null';
                }
                else
                    $res['col_taskNextID'] = 'null';

                $db->exec("INSERT INTO tbl_tasks (col_taskName,col_StatusID,col_pstageID,col_taskDesc,col_createDate,col_startPlan,col_endPlan,col_taskDur,col_seq,col_nextID,col_bonding,col_fromPlan) VALUE('{$res['col_taskName']}',5,$stage,{$res['col_taskDesc']},NOW(),'{$res['col_taskStart']}','{$res['col_taskEnd']}',{$res['col_taskDur']},{$res['col_taskSeq']},{$res['col_taskNextID']},{$res['col_bonding']},1)");

                $tasksPool[$res['col_taskID']] = $db->lastId('tbl_tasks');
            }
            //Tools::debug($res);
        }
    }
}