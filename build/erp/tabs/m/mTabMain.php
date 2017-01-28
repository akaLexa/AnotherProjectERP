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

}