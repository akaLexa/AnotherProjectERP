<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 31.12.2016
 * модель эвентов
 **/
namespace build\erp\inc;
use mwce\db\Connect;
use mwce\Tools\Date;
use mwce\Models\Model;


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

    public static function setAllRead($user){
        $db = Connect::start();
        $db->exec("UPDATE tbl_events SET col_isNoticed = 1 WHERE col_userID = $user");
    }

    protected static function queryBuilder($params = null){
        $filter = '';

        if(isset($params['isMailed']))
            $filter.= " AND te.col_isMailed = ".$params['isMailed'];

        if(!empty($params['userID']))
            $filter.= " AND te.col_userID = {$params['userID']}";

        if(isset($params['isNoticed'])){
            if(!empty($params['isTop']))
                $filter.= " AND (te.col_isNoticed = {$params['isNoticed']} or te.col_isTop = {$params['isTop']})";
            else
                $filter.= " AND te.col_isNoticed = {$params['isNoticed']}";
        }
        else{
            if(!empty($params['isTop']))
                $filter.= " AND te.col_isTop = {$params['isTop']}";
        }
        if(!empty($params['dFrom']) && !empty($params['dTo'])){
            $filter.= " AND col_dateCreate BETWEEN '{$params['dFrom']} 00:00:00' AND '{$params['dTo']} 23:59:59'";
        }

        if(!empty($params['eventType']))
            $filter.=" AND te.col_etID =".$params['eventType'];

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

        if(isset($params['min'])){
            $query.= " LIMIT {$params['min']}";
            if(!empty($params['max']))
                $query.= ", {$params['max']}";
        }

        return $query;
    }

    /**
     * @param int $id
     * @return mixed | Events
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT
  te.*,
  ther.col_message,
  thet.col_etName,
  thes.col_esName 
  FROM
  tbl_events te,
  tbl_hb_events_relation ther,
  tbl_hb_event_type thet,
  tbl_hb_event_state thes
WHERE
  te.col_etID = ther.col_erID
  AND thet.col_etID = ther.col_etID
  AND thes.col_esID = ther.col_esID
  AND te.col_evID = $id")->fetch(static::class);
    }

    /**
     * тип
     * @return array
     */
    public static function getType(){
        if(empty(self::$sdata['getType'])){
            $arrs = array();
            $db = Connect::start();
            $q = $db->query("SELECT * FROM tbl_hb_event_type ORDER BY col_etName");
            while ($r = $q->fetch()){
                $arrs[$r['col_etID']] = $r['col_etName'];
            }
            self::$sdata['getType'] = $arrs;
        }

        return self::$sdata['getType'];
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_dateCreate':
                parent::_adding($name.'Legend', Date::transDate($value,true));
                break;
            case 'col_comment':
                $value = strip_tags(htmlspecialchars_decode($value));
                break;
        }
        parent::_adding($name, $value);
    }
}