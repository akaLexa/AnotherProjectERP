<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 08.04.2017
 *
 **/
namespace build\mailer\mail;
use mwce\db\Connect;
use mwce\Models\Model;
use mwce\Tools\Date;
use mwce\Tools\Tools;

class lettersList extends Model
{

    public static function getModels($params = null)
    {
        $db = Connect::start();

        return $db->query("SELECT
  te.col_object,
  te.col_etID,
  te.col_dateCreate,
  te.col_userID,
  thet.col_etName,
  te.col_comment,
  tu.col_login,
  te.col_evID
FROM
  tbl_events te,
  tbl_hb_events_relation ther,
  tbl_hb_event_type thet,
  tbl_user tu
WHERE
  ther.col_erID = te.col_etID
  AND thet.col_etID = ther.col_etID
  AND te.col_dateCreate BETWEEN '{$params['start']}' AND '{$params['end']}'
  AND te.col_isNoticed = 0
  AND te.col_isMailed = 0
  AND tu.col_uID = te.col_userID
ORDER BY te.col_userID, te.col_etID, te.col_dateCreate")->fetchAll(static::class);
    }

    public static function Getlink($typeID,$objectID){
        switch ($typeID){
            // новая стадия
            case 1 :
                return 'page/inProject.html?id='.$objectID.'#tabMain';
                break;
            // стадия из плана проекта
            case 2 :
                return 'page/inProject.html?id='.$objectID.'#tabMain';
                break;
            // запуск задачи
            case 3 :
                // задача завершена
            case 4 :
                //курируемая задача
            case 5 :
                // новая задача
            case 6 :
                // задача из плана проекта
            case 7 :
                // курируемая задача завершена
            case 12 :
                //отказ от курируемой задачи
            case 13 :
                //отклонение задачи
            case 14 :
                //перезапуск курируемой задачи
            case 15 :
            case 16 :
            case 18 :
            case 19 :
                return 'page/tasks/In.html?id='.$objectID;
                break;
            // новость
            case 8 :
                return '';
                break;
            //запущен план проекта
            case 9 :
                //остановлен план проекта
            case 10 :
                //запуск плана проекта после останова
            case 17 :
                return 'page/inProject.html?id='.$objectID.'#tabProjectPlan';
                break;
            //переписка по проекту
            case 11 :
                return 'page/inProject.html?id='.$objectID.'#tabMessages';
                break;

            default:
                return '';
                break;
        }
    }

    public static function getCurModel($id)
    {
        /* nop */
    }

    public static function updateEvents($nums){
        if(is_array($nums)){
            Connect::start()->exec("UPDATE tbl_events SET col_isMailed = 1 WHERE col_evID IN (".implode(',',$nums).")");
        }
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_dateCreate':
                parent::_adding($name.'Legend', Date::transDate($value,true));
                break;
        }
        parent::_adding($name, $value);
    }
}