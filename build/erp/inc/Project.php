<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.12.2016
 * проект
 **/
namespace build\erp\inc;

use mwce\Configs;
use mwce\Connect;
use mwce\date_;
use mwce\Model;
use mwce\router;

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
        $db->exec("INSERT INTO tbl_project_stage (col_projectID,col_statusID,col_respID,col_dateStart,col_dateEnd,col_comment,col_stageID,col_dateEndPlan) VALUE ($prId,1,".router::getCurUser().",NOW(),NOW(),'Создан автоматически при заведении проекта',{$cfg['startStageID']}, DATE_ADD(NOW(), INTERVAL {$cfg['countDefStartDays']} DAY));");
        return self::getCurModel($prId);
    }

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
  tps.col_stageID, 
  tps.col_prevStageID
$filter
limit {$params['pageFrom']},{$params['pageTo']}")->fetchAll(static::class);
    }

    /**
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
  AND ths.col_StatusID = tps.col_statusID';
        return $queryString;
    }

    /**
     * настройки для проекта
     * @return array|bool
     */
    public static function getCfg(){
        if(empty(self::$projectCfg)){
            self::$projectCfg = Configs::readCfg('project',tbuild);
        }
        return self::$projectCfg;
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
  tps.col_dateCreate, 
  tps.col_dateStart, 
  tps.col_dateEnd, 
  tps.col_dateEndPlan, 
  tps.col_dateEndFact, 
  tps.col_comment, 
  tps.col_stageID, 
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
                parent::_adding($name.'Legend', date_::transDate($value));
                parent::_adding($name.'LegendDT', date_::transDate($value,true));
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
        }
        parent::_adding($name, $value);
    }
}