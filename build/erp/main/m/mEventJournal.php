<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 31.12.2016
 *
 **/
namespace build\erp\main\m;
use build\erp\inc\Events;
use mwce\router;

class mEventJournal extends Events
{
    /**
     * @param int $typeID
     * @return string классы клифов для визуального отображения
     */
    public static function glyphType($typeID){
        switch ($typeID){
            // новая стадия
            case 1 :
                return 'glyphicon glyphicon-play';
                break;
            // стадия из плана проекта
            case 2 :
                return 'glyphicon glyphicon-forward';
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
                return 'glyphicon glyphicon-time';
                break;
            // новость
            case 8 :
                return 'glyphicon glyphicon-globe';
                break;
            //запущен план проекта
            case 9 :
            //остановлен план проекта
            case 10 :
            //запуск плана проекта после останова
            case 17 :
                return 'glyphicon glyphicon-exclamation-sign';
                break;
            //переписка по проекту
            case 11 :
                return 'glyphicon glyphicon-comment';
                break;

            default:
                return '';
                break;
        }
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
                return '';
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
    /**
     * закрепить / открепить запись
     * @return int
     */
    public function pushEvent(){
       $r =  $this->db->query("SELECT f_pushEvent({$this['col_evID']},".router::getCurUser().") as col_result")->fetch();
       return $r['col_result'];
    }

    /**
     * отметить как прочитанное
     */
    public function setIsRead(){
        $this->db->exec("UPDATE tbl_events SET col_isNoticed = 1 WHERE col_evID = {$this['col_evID']} AND col_userID=".router::getCurUser());
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_etID':
                parent::_adding('glyphInfo', self::glyphType($value));
                break;
            case 'col_object':
                parent::_adding('link', self::Getlink($this['col_etID'],$value));
                break;
        }

        parent::_adding($name, $value);
    }
}