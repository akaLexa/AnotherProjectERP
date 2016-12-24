<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 21.12.2016
 *
 **/
namespace build\erp\tabs\m;
use mwce\Connect;
use mwce\date_;
use mwce\Model;
use mwce\Tools;

class mProjectPlan extends Model
{
    /**
     * @param null|array $params
     * @return mixed|mProjectPlan|array
     */
    public static function getModels($params = null)
    {
        $db = Connect::start();
        return $db->query("SELECT 
  tps.*,
  thps.col_StageName,
  ths.col_StatusName,
  f_getUserFIO(tps.col_respID) as col_resp,
  tt.col_taskID,
  tt.col_taskName,
  tt.col_respID,
  f_getUserFIO(tt.col_respID) as col_taskResp,
  COALESCE(tt.col_startFact,tt.col_startPlan) AS col_taskStart,
  COALESCE(tt.col_endFact,tt.col_endPlan) AS col_taskEnd,
  tt.col_seq AS col_taskSeq,
  tt.col_taskDur,
  tt.col_StatusID AS col_taskStatusID,
  tths.col_StatusName AS col_taskStatusName
FROM 
  tbl_project_stage tps 
    LEFT JOIN tbl_tasks tt ON tt.col_pstageID = tps.col_pstageID
    LEFT JOIN tbl_hb_status tths ON tths.col_StatusID = tt.col_StatusID ,
  tbl_hb_project_stage thps,
  tbl_hb_status ths 
WHERE 
  tps.col_projectID = {$params['col_projectID']}  
  AND tps.col_statusID IN (1,4,5)
  AND thps.col_StageID = tps.col_stageID
  AND ths.col_StatusID = tps.col_statusID
  ORDER BY tps.col_seq, tt.col_seq ASC, tps.col_pstageID ASC, tt.col_startFact, tt.col_taskDur DESC, tt.col_taskID ASC")->fetchAll(static::class);
    }

    /**
     * @param int $project
     * @param $date
     */
    public static function rebuildPlan($project,$date){
        $db = Connect::start();
        $db->exec("CALL sp_CalcProjectPlan($project,'$date');");
        $db->closeCursor();
    }

    /**
     * @param int $projectID
     * @param int $stageID
     * @param int $durability
     * @param int $user
     */
    public static function AddPlanState($projectID,$stageID,$durability,$user){
        $db = Connect::start();
        $stageList = mProjectPlan::getModels(['col_projectID'=>$projectID]);
        $curSettings = date('Y-m-d');

        foreach ($stageList as $item) {

            if(empty($item['col_dateEndFact']))
                $curSettings = $item['col_dateEndPlan'];
            else
                $curSettings = $item['col_dateEndFact'];
        }

        $db->exec("INSERT INTO tbl_project_stage (col_projectID,col_statusID,col_respID,col_dateCreate,col_dateStartPlan,col_dateEndPlan,col_stageID,col_duration) VALUE($projectID,5,$user,NOW(),'$curSettings',DATE_ADD('$curSettings', INTERVAL $durability DAY),$stageID,$durability)");
    }

    /**
     * @param int $id
     * @return mProjectPlan
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT 
  tps.*
FROM 
  tbl_project_stage tps
WHERE 
  tps.col_pstageID = {$id}")->fetch(static::class);
    }

    public function delete(){
        $this->db->exec("DELETE FROM tbl_tasks WHERE col_pstageID = {$this['col_pstageID']}");
        $this->db->exec("DELETE FROM tbl_project_stage WHERE col_pstageID = {$this['col_pstageID']}");
    }

    /**
     * @param int $stageID
     * @param int $durability
     * @param int $user
     * @param int $seq
     */
    public function edit($stageID,$durability,$user,$seq){

        $stageList = mProjectPlan::getModels($this);
        $curSettings = date('Y-m-d');

        foreach ($stageList as $item) {
            if($item['col_pstageID'] == $stageID)
                break;

            if(empty($item['col_dateEndFact']))
                $curSettings = $item['col_dateEndPlan'];
            else
                $curSettings = $item['col_dateEndFact'];
        }

        $this->db->exec("UPDATE tbl_project_stage SET col_respID = $user,col_dateStartPlan='$curSettings',col_dateEndPlan = DATE_ADD('$curSettings', INTERVAL $durability DAY),col_stageID = $stageID ,col_duration = $durability, col_seq = $seq WHERE col_pstageID = {$this['col_pstageID']}");
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_dateStart':
            case 'col_dateEnd':
            case 'col_dateEndPlan':
            case 'col_dateEndFact':
            case 'col_dateCreate':
            case 'col_dateStartPlan':
            case 'col_taskStart':
            case 'col_taskEnd':
                parent::_adding($name.'Legend', date_::transDate($value));
                parent::_adding($name.'LegendDT', date_::transDate($value,true));
                break;
        }
        parent::_adding($name, $value);
    }
}