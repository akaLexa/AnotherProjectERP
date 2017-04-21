<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 20.04.2017
 *
 **/
namespace build\erp\user\m;
use build\erp\inc\Project;
use build\erp\inc\User;

class mUserCart extends User
{
    /**
     * Статистика по задачам за текущий месяц
     * @param int $user
     * @return array
     */
    public function getTaskStatistic($user){
        $begin = date('Y-m-01');
        $end = date('Y-m-t');

        $result = $this->db->query("SELECT
  COUNT(*) AS col_count
FROM
  tbl_tasks tt
WHERE
  tt.col_respID = $user
  AND tt.col_startFact BETWEEN '$begin 00:00:00' AND '$end 23:59:59' 
  AND tt.col_endFact BETWEEN '$begin 00:00:00' AND '$end 23:59:59'

UNION ALL

SELECT
  COUNT(*) AS col_count
FROM
  tbl_tasks tt
WHERE
  tt.col_respID = $user
  AND tt.col_StatusID = 3
  AND (tt.col_startFact BETWEEN '$begin 00:00:00' AND '$end 23:59:59' OR tt.col_endFact BETWEEN '$begin 00:00:00' AND '$end 23:59:59')

UNION ALL

SELECT
  COUNT(*) AS col_count
FROM
  tbl_tasks tt
WHERE
  tt.col_respID = $user
  AND (tt.col_startFact BETWEEN '$begin 00:00:00' AND '$end 23:59:59' OR tt.col_endFact BETWEEN '$begin 00:00:00' AND '$end 23:59:59')
  AND DATEDIFF(tt.col_endPlan,COALESCE(tt.col_endFact,NOW())) < 0
")->fetchAll();

        return [
            'total' => $result[0]['col_count'],
            'success' => $result[1]['col_count'],
            'outOfDate' => $result[2]['col_count'],
        ];

    }

    /**
     * Статистика по стадиям за текущий месяц
     * @param int $user
     * @return array
     */
    public function getStageStatistic($user){
        $begin = date('Y-m-01');
        $end = date('Y-m-t');
        $cfg = Project::getCfg();

        $result = $this->db->query("SELECT DISTINCT
  COUNT(*) AS col_count
FROM
  tbl_project_stage tps
WHERE
  tps.col_respID = $user
  AND tps.col_dateStart BETWEEN '$begin 00:00:00' AND '$end 23:59:59' 
  AND tps.col_dateEndFact BETWEEN '$begin 00:00:00' AND '$end 23:59:59'

UNION ALL

SELECT DISTINCT 
  COUNT(*) AS col_count
FROM
  tbl_project_stage tps
WHERE
  tps.col_respID = $user
  AND (tps.col_statusID = 3 OR tps.col_stageID IN ({$cfg['endStagesID']}))
  AND (tps.col_dateStart BETWEEN '$begin 00:00:00' AND '$end 23:59:59' OR tps.col_dateEndFact BETWEEN '$begin 00:00:00' AND '$end 23:59:59')

UNION ALL

SELECT
  COUNT(*) AS col_count
FROM
  tbl_project_stage tps
WHERE
  tps.col_respID = $user
  AND (tps.col_dateStart BETWEEN '$begin 00:00:00' AND '$end 23:59:59' OR tps.col_dateEndFact BETWEEN '$begin 00:00:00' AND '$end 23:59:59')
  AND (DATEDIFF(tps.col_dateEndPlan,COALESCE(tps.col_dateEndFact,NOW())) < 0 OR DATEDIFF(tps.col_dateEnd,COALESCE(tps.col_dateEndFact,NOW())))")->fetchAll();

        return [
            'totalStage' => $result[0]['col_count'],
            'successStage' => $result[1]['col_count'],
            'outOfDateStage' => $result[2]['col_count'],
        ];

    }

    /**
     * Активные стадии
     * @param int $user
     * @return mixed
     */
    public function getProjectStageList($user){
        return $this->db->query("SELECT
  tps.col_projectID,
  tp.col_projectName,
  thps.col_StageName,
  tps.col_dateEndPlan
FROM
  tbl_project_stage tps,
  tbl_hb_project_stage thps,
  tbl_project tp
WHERE
  tps.col_statusID IN (1,4)
  AND tps.col_respID = $user
  AND thps.col_StageID = tps.col_stageID
  AND tp.col_projectID = tps.col_projectID")->fetchAll();
    }
}