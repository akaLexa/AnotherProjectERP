<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 31.12.2016
 * модель эвентов
 **/
namespace build\erp\inc;
use mwce\Connect;
use mwce\Model;

class Events extends Model
{
    /**
     * @param null|array $params
     * @return mixed|Events
     */
    public static function getModels($params = null)
    {
        $query = self::queryBuilder($params);
        $db = Connect::start();
        return $db->query("SELECT
  te.*,
  ther.col_message,
  thet.col_etName,
  thes.col_esName
  $query")->fetchAll(static::class);
    }

    /**
     * @param null|array $params
     * @return int
     */
    public static function getCount($params = null){
        $query = self::queryBuilder($params);
        $db = Connect::start();
        $res = $db->query("SELECT count(*) as cnt $query")->fetch();
        return $res['cnt'];
    }

    protected static function queryBuilder($params = null){
        $filter = '';
        if(!empty($params['isMailed']))
            $filter.= " AND te.col_isMailed = 1";
        else
            $filter.= " AND te.col_isMailed = 0";

        if(!empty($params['isTop']))
            $filter.= " AND te.col_isTop = 1";
        else
            $filter.= " AND te.col_isTop = 0";

        if(!empty($params['isNoticed']))
            $filter.= " AND te.col_isNoticed = 1";
        else
            $filter.= " AND te.col_isNoticed = 0";

        $query = "FROM
  tbl_events te,
  tbl_hb_events_relation ther,
  tbl_hb_event_type thet,
  tbl_hb_event_state thes
WHERE
  te.col_etID = ther.col_erID
  AND thet.col_etID = ther.col_etID
  AND thes.col_esID = ther.col_esID
  $filter";

        if(!empty($params['min'])){
            $query.= " LIMIT {$params['min']}";
            if(!empty($params['max']))
                $query.= ", {$params['max']}";
        }

        return $query;
    }

    public static function getCurModel($id)
    {
        $db = Connect::start();
    }
}