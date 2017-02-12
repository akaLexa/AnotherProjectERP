<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 18.12.2016
 *
 **/
namespace build\erp\tabs\m;
use build\erp\inc\Project;
use build\erp\inc\User;
use mwce\Configs;
use mwce\date_;
use mwce\router;

class mTabMain extends Project
{
    /**
     * @param array $params
     */
    public function save($params){
        $q = '';
        foreach ($params as $id=>$v){
            if(!empty($q))
                $q.=',';
            $q.= "$id = $v";
        }

        if(!empty($q)){

            $this->db->exec("UPDATE tbl_project SET $q WHERE col_projectID=".$this['col_projectID']);
            if($this['col_founderID'] != $params['col_founderID']){
                $famList = User::getUserList();
                mTabMessages::addEvent($this['col_projectID'],"Пользователь «{$famList[Configs::userID()]}» сменил менеджера проекта с «{$famList[$this['col_founderID']]}» на «{$famList[$params['col_founderID']]}»");
            }
        }
    }

    /**
     * включеиние / отключение автоплана
     * @param int $state
     * @param string $descLate причина просрочки стадии
     * @param string $descOff причина выключения автоплана
     * @return bool
     */
    public function switchPlanState($state,$descLate,$descOff){
        //отключение
        if($state == 0){
            $this->db->exec("UPDATE tbl_project SET col_ProjectPlanState = 0 WHERE col_projectID = ".$this['col_projectID']);
            mTabMessages::addEvent($this['col_projectID'],'Функция автоплана проекта была отключена {userID:'.Configs::userID().'} по причине: '.$descOff);
            return true;
        }
        //включение
        else{
            return self::switchToNextPlanStage($descLate);
        }
    }

    /**
     * @param string|int $descLate причина просрочки
     * @return bool
     */
    public function switchToNextPlanStage($descLate=0){
        if($this['col_respID']!= Configs::userID())
            return false;

        $next = self::getNextStageID();

        $this->db->exec("CALL sp_CalcProjectPlan({$this['col_projectID']},'".date_::intransDate('now')."');");
        $this->db->closeCursor();

        // нет следующей стадии, ничего не делаем!
        if(!$next)
            return false;

        if(!empty($descLate))
            $descLate = "CONCAT(COALESCE(col_comment,''),' $descLate')";
        else
            $descLate = "CONCAT(COALESCE(col_comment,''),'')";

        $this->db->exec("UPDATE tbl_project_stage SET col_statusID = 3, col_comment = $descLate, col_dateEndFact = NOW() WHERE  col_pstageID = ".$this['col_pstageID']);
        $this->db->exec("UPDATE tbl_project_stage SET col_statusID = 1, col_dateStart = NOW(),col_prevStageID={$this['col_pstageID']} WHERE col_pstageID = {$next['col_pstageID']}");
        $this->db->exec("UPDATE tbl_project SET col_ProjectPlanState = 1 WHERE col_projectID = ".$this['col_projectID']);
        $this->db->exec("CALL sp_StartTaskPlan({$next['col_pstageID']})");
        $this->db->closeCursor();
        return true;
    }

    /**
     * узнать следующую стадию, что идет по плану
     * @return bool|array
     */
    public function getNextStageID(){
        $result = $this->db->query("SELECT
  tps.col_pstageID,
  htps.col_StageName,
  tps.col_respID,
  f_getUserFIO(tps.col_respID) as col_resp
FROM
  tbl_project_stage tps,
  tbl_hb_project_stage htps
WHERE
  tps.col_statusID = 5
  AND tps.col_projectID = {$this['col_projectID']}
  and htps.col_StageID = tps.col_stageID
ORDER BY
  tps.col_seq, tps.col_pstageID ASC
LIMIT 1")->fetch();

        if(empty($result) && empty($result['col_pstageID']))
            return false;

        return $result;
    }
}