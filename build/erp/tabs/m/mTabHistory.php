<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.01.2017
 *
 **/
namespace build\erp\tabs\m;

use mwce\Connect;
use mwce\date_;
use mwce\Model;

class mTabHistory extends Model
{

    public static function getModels($params = null)
    {
        $db= Connect::start();
        return $db->query("SELECT 
  tps.*,
  thps.col_StageName,
  ths.col_StatusName,
  f_getUserFIO(tps.col_respID) as col_resp,
  tt.col_taskID,
  tt.col_taskName,
  tt.col_respID,
  f_getUserFIO(tt.col_respID) as col_taskResp,
  tt.col_startFact,
  tt.col_endFact,
  tt.col_endPlan,
  tt.col_seq AS col_taskSeq,
  tt.col_startPlan as col_taskStartPlan,
  tt.col_startFact as col_taskstartFact,
  tt.col_endPlan as col_taskendPlan,
  tt.col_endFact as col_taskendFact,
  tt.col_taskDur,
  tt.col_nextID,
  tt.col_StatusID AS col_taskStatusID,
  tt.col_failDes,
  tths.col_StatusName AS col_taskStatusName
FROM 
  tbl_project_stage tps 
    LEFT JOIN tbl_tasks tt ON tt.col_pstageID = tps.col_pstageID
    LEFT JOIN tbl_hb_status tths ON tths.col_StatusID = tt.col_StatusID ,
  tbl_hb_project_stage thps,
  tbl_hb_status ths 
WHERE 
  tps.col_projectID = {$params['col_projectID']}  
  AND thps.col_StageID = tps.col_stageID
  AND ths.col_StatusID = tps.col_statusID
  ORDER BY 
  tps.col_seq, 
  tt.col_seq ASC, 
  tps.col_pstageID ASC, 
  tt.col_startFact, 
  tt.col_taskDur DESC, 
  tt.col_taskID ASC")->fetchAll( static::class);
    }

    public static function getCurModel($id)
    {
        /* nop */
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
            case 'col_endFact':
            case 'col_endPlan':
            case 'col_taskendPlan':
            case 'col_taskendFact':
            case 'col_startFact':
            case 'col_taskStartPlan':
            case 'col_taskstartFact':
                parent::_adding($name.'Legend', date_::transDate($value));
                parent::_adding($name.'LegendDT', date_::transDate($value,true));
                break;
        }
        parent::_adding($name, $value);
    }
}