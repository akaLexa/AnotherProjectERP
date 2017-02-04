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
                mTabMessages::addEvent($this['col_projectID'],"Пользователь «{$famList[router::getCurUser()]}» сменил менеджера проекта с «{$famList[$this['col_founderID']]}» на «{$famList[$params['col_founderID']]}»");
            }
        }
    }

    /**
     * принять стадию
     */
    public function stageAgree(){
        $this->db->exec("UPDATE tbl_project_stage SET col_statusID = 1,col_dateStart = NOW() WHERE col_pstageID = ".$this['col_pstageID']);
    }

    /**
     * отказаться от стадии, указав комментарий
     * @param string $comment
     */
    public function stageDisagree($comment){
        $this->db->exec("UPDATE tbl_project_stage SET col_statusID = 2,col_dateStart = NOW(),col_dateEndFact=NOW(),col_comment='$comment' WHERE col_pstageID = ".$this['col_pstageID']);
        $this->db->exec("INSERT INTO tbl_project_stage(col_projectID,col_statusID,col_dateCreate,col_dateStartPlan,col_dateStart,col_dateEndPlan,col_comment,col_stageID,col_prevStageID,col_respID)
SELECT col_projectID,1,NOW(),NOW(),NOW(),DATE_ADD(NOW(), INTERVAL 1 DAY),'Исполнитель отказался от стадии по причине: $comment',col_stageID,col_prevStageID,col_respID FROM tbl_project_stage WHERE col_pstageID = ".$this['col_pstageID']);
    }

    /**
     * передать стадию
     * @param string $comment
     * @param int $receiver
     * @param int $stage
     * @param date $toDate
     */
    public function sendStage($comment,$receiver,$stage,$toDate){
        $this->db->exec("INSERT INTO tbl_project_stage(col_projectID,col_statusID,col_dateCreate,col_dateStartPlan,col_dateStart,col_dateEndPlan,col_comment,col_stageID,col_prevStageID,col_respID) VALUE ({$this['col_projectID']},4,NOW(),NOW(),NOW(),'$toDate',$comment,$stage,{$this['col_pstageID']},$receiver)");
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
            mTabMessages::addEvent($this['col_projectID'],'Функция автоплана проекта была отключена {userID:'.router::getCurUser().'} по причине: '.$descOff);
            return true;
        }
        //включение
        else{
            $next = self::getNextStageID();

            $this->db->exec("CALL sp_CalcProjectPlan({$this['col_projectID']},'".date_::intransDate('now')."');");
            $this->db->closeCursor();

            // нет следующей стадии, ничего не делаем!
            if(!$next)
                return false;

            if(!empty($descLate))
                $descLate = "CONCAT(COALESCE(col_comment,''),' Причина просрочки: $descLate')";
            else
                $descLate = "CONCAT(COALESCE(col_comment,''),'')";

            $this->db->exec("UPDATE tbl_project_stage SET col_statusID = 3, col_comment = $descLate, col_dateEndFact = NOW() WHERE  col_pstageID = ".$this['col_pstageID']);
            $this->db->exec("UPDATE tbl_project_stage SET col_statusID = 1, col_dateStart = NOW(),col_prevStageID={$this['col_pstageID']} WHERE col_pstageID = $next");
            $this->db->exec("UPDATE tbl_project SET col_ProjectPlanState = 1 WHERE col_projectID = ".$this['col_projectID']);
            $this->db->exec("CALL sp_StartTaskPlan({$next})");
            $this->db->closeCursor();
            return true;
        }
    }

    /**
     * узнать следующую стадию, что идет по плану
     * @return bool|int
     */
    protected function getNextStageID(){
        $result = $this->db->query("SELECT
  tps.col_pstageID
FROM
  tbl_project_stage tps
WHERE
  tps.col_statusID = 5
  AND tps.col_projectID = {$this['col_projectID']}
ORDER BY
  tps.col_seq, tps.col_pstageID ASC
LIMIT 1")->fetch();

        if(empty($result) && empty($result['col_pstageID']))
            return false;

        return $result['col_pstageID'];
    }
}