<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 17.04.2017
 *
 **/
namespace build\erp\reports\m;
use mwce\db\Connect;
use mwce\Models\Model;
use mwce\Tools\Date;

class mProjectStageReport extends Model
{

    public static function getModels($params = null)
    {
        $db = Connect::start();
        $filter = '';

        if(!empty($params['prName'])){
            $filter.= " AND tp.col_projectName like '%{$params['prName']}%'";
        }

        if(!empty($params['curManager'])){
            $filter.= " AND tp.col_founderID = ".$params['curManager'];
        }

        if(!empty($params['curResp'])){
            $filter.= " AND tps.col_respID = ".$params['curResp'];
        }

        if(!empty($params['curStage'])){
            $filter.= " AND tps.col_stageID = ".$params['curStage'];
        }

        if(!empty($params['prNum'])){
            $filter.= " AND tp.col_pnID = ".$params['prNum'];
        }

        if(!empty($params['dBegin'])){
            $filter.= " AND tps.col_dateStart BETWEEN '{$params['dBegin']} 00:00:00' AND '{$params['dBegin']} 23:59:59'";
        }

        if(!empty($params['dEndPlan'])){
            $filter.= " AND tps.col_dateEndPlan BETWEEN '{$params['dEndPlan']} 00:00:00' AND '{$params['dEndPlan']} 23:59:59'";
        }

        if(!empty($params['dEndFact'])){
            $filter.= " AND tps.col_dateEndFact BETWEEN '{$params['dEndFact']} 00:00:00' AND '{$params['dEndFact']} 23:59:59'";
        }

        return $db->query("SELECT
  tp.col_projectID,
  tp.col_pnID,
  tp.col_projectName,
  tp.col_founderID,
  f_getUserFIO(tp.col_founderID) as col_founder,
  thps.col_StageName,
  tps.col_statusID,
  tps.col_respID,
  f_getUserFIO(tps.col_respID) as col_resp,
  DATEDIFF(tps.col_dateEndPlan,COALESCE(tps.col_dateEndFact,NOW())) AS col_freeDays,
  tps.col_dateStart,
  tps.col_dateEndFact,
  tps.col_dateEndPlan
FROM
  tbl_project tp,
  tbl_project_stage tps,
  tbl_hb_project_stage thps
WHERE
  tps.col_projectID = tp.col_projectID
  AND tps.col_statusID !=5
  AND thps.col_StageID = tps.col_stageID
  $filter")->fetchAll(static::class);
    }

    public static function getCurModel($id)
    {

    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_dateStart':
            case 'col_dateEndFact':
            case 'col_dateEndPlan':
                parent::_adding($name.'Legend', Date::transDate($value,true));
                break;
        }
        parent::_adding($name, $value);
    }
}