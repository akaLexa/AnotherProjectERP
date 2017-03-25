<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.12.2016
 * проект
 **/
namespace build\erp\inc;

use mwce\Tools\Configs;
use mwce\db\Connect;
use mwce\Tools\Date;
use mwce\Exceptions\DBException;
use mwce\Models\Model;

class Project extends Model
{
    /**
     * конфиг проекта
     * @var array
     */
    protected static $projectCfg = array();

    /**
     * @param string $prName
     * @param int $founder
     * @param int $serialNum
     * @return Project
     */
    public static function Add($prName,$founder,$serialNum=0){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_project(col_projectName,col_pnID,col_founderID) VALUE('$prName',f_setProjectNum($serialNum),$founder)");
        $prId = $db->lastId('tbl_project');
        $cfg = self::getCfg();
        $db->exec("INSERT INTO tbl_project_stage (col_projectID,col_statusID,col_respID,col_dateStart,col_dateEnd,col_comment,col_stageID,col_dateEndPlan,col_dateStartPlan) VALUE ($prId,1,".Configs::userID().",NOW(),NOW(),'Создан автоматически при заведении проекта.',{$cfg['startStageID']}, DATE_ADD(NOW(), INTERVAL {$cfg['countDefStartDays']} DAY),NOW());");
        return self::getCurModel($prId);
    }

    /**
     * @param null|array $params
     * @return mixed|Project
     */
    public static function getModels($params = null)
    {
        if(empty($params['pageFrom']))
            $params['pageFrom'] = 0;

        if(empty($params['pageTo']))
            $params['pageTo'] = 50;

        $db = Connect::start();
        $filter = self::queryBuilder($params);

        return $db->query("SELECT 
  tp.*,
  tpn.col_serNum,
  ths.col_StatusName,
  thps.col_StageName,
  tps.col_statusID,
  tps.col_respID,
  f_getUserFIO(tps.col_respID) as col_respName,
  f_getUserFIO(tp.col_founderID) as col_founder,
  tps.col_dateCreate, 
  tps.col_dateStart, 
  tps.col_dateEnd, 
  tps.col_dateEndPlan, 
  tps.col_dateEndFact, 
  tps.col_comment, 
  tps.col_pstageID,
  tps.col_stageID, 
  tps.col_prevStageID
$filter
limit {$params['pageFrom']},{$params['pageTo']}")->fetchAll(static::class);
    }

    /**
     * получить конткретынй проект по его id
     * @param int $id
     * @return Project
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT 
  tp.*,
  tpn.col_serNum,
  ths.col_StatusName,
  thps.col_StageName,
  tps.col_statusID,
  tps.col_respID,
  f_getUserFIO(tps.col_respID) as col_respName,
  f_getUserFIO(tp.col_founderID) as col_founder,
  tps.col_dateCreate, 
  tps.col_dateStart, 
  tps.col_dateEnd, 
  tps.col_dateEndPlan, 
  tps.col_dateEndFact, 
  tps.col_comment, 
  tps.col_stageID, 
  tps.col_pstageID, 
  tps.col_prevStageID
FROM 
  tbl_project tp,
  tbl_project_num tpn,
  tbl_project_stage tps,
  tbl_hb_project_stage thps,
  tbl_hb_status ths
WHERE 
  tpn.col_pnID = tp.col_pnID 
  AND tps.col_projectID = tp.col_projectID
  AND tps.col_statusID IN (1,4)
  AND thps.col_StageID = tps.col_stageID
  AND ths.col_StatusID = tps.col_statusID
  AND tp.col_projectID =".$id)->fetch(static::class);
    }

    /**
     * кол-во проектов согласно фильтру
     * @param null|array $params
     * @return mixed
     */
    public static function getCountProject($params = null){
        $db = Connect::start();
        $filter = self::queryBuilder($params);
        $res = $db->query("SELECT count(*) as cnt $filter")->fetch();

        return $res['cnt'];
    }

    public static function queryBuilder($params = null){

        $filter = '';

        if(!empty($params['projectNum'])){
            $filter.= " AND tp.col_pnID = ".$params['projectNum'];
        }

        if(!empty($params['projectName'])){
            $filter.= " AND tp.col_projectName like '%{$params['projectName']}%' ";
        }

        if(!empty($params['UserResponse'])){
            $filter.= " AND tps.col_respID = {$params['UserResponse']}";
        }

        if(!empty($params['UserManager'])){
            $filter.= " AND tp.col_founderID = {$params['UserManager']}";
        }

        if(!empty($params['startDate'])){
            $filter.= " AND tps.col_dateStart BETWEEN '{$params['startDate']} 00:00:00' AND '{$params['startDate']} 23:59:59'";
        }

        if(!empty($params['endDate'])){
            $filter.= " AND tps.col_dateEndPlan BETWEEN '{$params['endDate']} 00:00:00' AND '{$params['endDate']} 23:59:59'";
        }

        if(!empty($params['stageIds'])){
            $filter.= " AND tps.col_stageID IN ({$params['stageIds']})";
        }

        if(!empty($params['group'])){
            $filter.= " AND tp.col_gID = ".$params['group'];
        }

        if(!empty($params['isInside']))
            $filter.= " AND tp.col_gID IS NOT NULL";
        else
            $filter.= " AND tp.col_gID IS NULL";

        $queryString = 'FROM 
  tbl_project tp,
  tbl_project_num tpn,
  tbl_project_stage tps,
  tbl_hb_project_stage thps,
  tbl_hb_status ths
WHERE 
  tpn.col_pnID = tp.col_pnID 
  AND tps.col_projectID = tp.col_projectID
  AND tps.col_statusID IN (1,4)
  AND thps.col_StageID = tps.col_stageID
  AND ths.col_StatusID = tps.col_statusID '.$filter;
        return $queryString;
    }

    /**
     * список статусов
     * @return array
     */
    public static function getStates(){
        if(!empty(self::$sdata['getStates']))
            return self::$sdata['getStates'];
        $db = Connect::start();
        $ars = array();
        $q = $db->query("SELECT * FROM tbl_hb_status");
        while ($r = $q->fetch()){
            $ars[$r['col_StatusID']] = $r['col_StatusName'];
        }
        self::$sdata['getStates'] = $ars;

        return self::$sdata['getStates'];
    }

    /**
     * настройки для проекта
     * @return array|bool
     */
    public static function getCfg(){
        if(empty(self::$projectCfg)){
            //todo: распарсить сразу настройки, там где нужно в массивы
            self::$projectCfg = Configs::readCfg('project',Configs::currentBuild());
        }
        return self::$projectCfg;
    }

    /**
     * Список всех стадий в проекте
     * @return array
     */
    public static function getStagesList(){
        if(!empty(self::$sdata['StagesList']))
            return self::$sdata['StagesList'];
        else{
            $db = Connect::start();
            $stages = array();
            $q = $db->query("SELECT col_StageID,col_StageName FROM tbl_hb_project_stage WHERE col_isDel = 0 ORDER BY col_StageName");
            while ($r = $q->fetch()){
                $stages[$r['col_StageID']] = $r['col_StageName'];
            }
            //asort($stages);
            self::$sdata['StagesList'] = $stages;
            return $stages;
        }
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_CreateDate':
            case 'col_dateStart':
            case 'col_dateEndPlan':
                parent::_adding($name.'Legend', Date::transDate($value));
                parent::_adding($name.'LegendDT', Date::transDate($value,true));
                break;
            case 'col_Desc' :
                parent::_adding($name.'Legend', htmlspecialchars_decode($value));
                break;
            case 'col_ProjectPlanState':
                if($value >0)
                    parent::_adding($name.'Legend', 'Запущен');
                else
                    parent::_adding($name.'Legend', 'Не запущен');
                break;
            case 'col_comment':
                if(empty($value))
                    $value = 'Комментраиев к стадии нет';
                break;
            case 'col_repeat':
                if($value > 1)
                    parent::_adding($name.'Legend', '.'.$value);
                else
                    parent::_adding($name.'Legend', '');
                break;
        }
        parent::_adding($name, $value);
    }

    /**
     * передать стадию
     * @param string $comment
     * @param int $receiver
     * @param int $stage
     * @param date $toDate
     * @param null|string $failDesc
     */
    public function sendStage($comment,$receiver,$stage,$toDate,$failDesc = NULL){
        if(!is_null($failDesc) && !empty($failDesc))
            $failDesc = ' Причина просрочки: '.$failDesc;
        else
            $failDesc = '';

        $this->db->exec("UPDATE tbl_project_stage SET col_statusID = 3,col_dateEndFact=NOW(),col_comment = CONCAT(COALESCE (col_comment,''),'$failDesc') WHERE col_pstageID = {$this['col_pstageID']}"); //завершаем старую

        $this->db->exec("INSERT INTO tbl_project_stage(col_projectID,col_statusID,col_dateCreate,col_dateStartPlan,col_dateStart,col_dateEndPlan,col_comment,col_stageID,col_prevStageID,col_respID) VALUE ({$this['col_projectID']},4,NOW(),NOW(),NOW(),'$toDate',$comment,$stage,{$this['col_pstageID']},$receiver)");
    }

    /**
     * отказаться от стадии, указав комментарий
     * @param string $comment
     */
    public function stageDisagree($comment){
        $prevStage = $this->db->query("SELECT * FROM tbl_project_stage WHERE col_pstageID = ".$this['col_prevStageID'])->fetch();
        if(!empty($prevStage)){
            $this->db->exec("UPDATE tbl_project_stage SET col_statusID = 2,col_dateStart = NOW(),col_dateEndFact=NOW(),col_comment='$comment' WHERE col_pstageID = ".$this['col_pstageID']);
            $this->db->exec("INSERT INTO tbl_project_stage(col_projectID,col_statusID,col_dateCreate,col_dateStartPlan,col_dateStart,col_dateEndPlan,col_comment,col_stageID,col_prevStageID,col_respID)
SELECT col_projectID,1,NOW(),NOW(),NOW(),DATE_ADD(NOW(), INTERVAL 1 DAY),'Исполнитель отказался от стадии, см. имторию проекта',{$prevStage['col_stageID']},col_pstageID,{$prevStage['col_respID']} FROM tbl_project_stage WHERE col_pstageID = ".$this['col_pstageID']);

        }
    }

    /**
     * принять стадию
     */
    public function stageAgree(){
        $this->db->exec("UPDATE tbl_project_stage SET col_statusID = 1,col_dateStart = NOW() WHERE col_pstageID = ".$this['col_pstageID']);
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @return bool
     */
    public function setField($fieldName,$value){
        try{
            $this->db->exec("UPDATE tbl_project SET $fieldName = '$value' WHERE col_projectID =".$this['col_projectID']);
            return true;
        }
        catch (DBException $e){
            return false;
        }
    }
}