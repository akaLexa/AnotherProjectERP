<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 06.01.2017
 *
 **/
namespace build\erp\inc;
use mwce\Connect;
use mwce\date_;
use mwce\Model;


class TaskComments extends Model
{
    /**
     * @param null|array $params
     * @return bool|TaskComments
     */
    public static function getModels($params = null)
    {
        if(!empty($params['taskID'])){
            $db = Connect::start();
            return $db->query("SELECT *, f_getUserFIO(col_UserID) as col_User FROM tbl_tasks_comments WHERE col_taskID = ".$params['taskID']." ORDER BY col_date DESC")->fetchAll(static::class);
        }
        return false;
    }

    /**
     * @param int $id
     * @return mixed|TaskComments
     */
    public static function getCurModel($id)
    {
        $db = Connect::start();
        return $db->query("SELECT *, f_getUserFIO(col_UserID) as col_User FROM tbl_tasks_comments WHERE col_tcID = $id")->fetch(static::class);
    }

    /**
     * @param $params
     * @return TaskComments
     */
    public static function Add($params){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_tasks_comments (col_taskID,col_UserID,col_text,col_date) VALUE({$params['col_taskID']},{$params['col_UserID']},'{$params['col_text']}',NOW())");

        $obj = new TaskComments();
        $params['col_tcID'] = $db->lastId('tbl_tasks_comments');
        $params['col_date'] = 'now';
        foreach ($params as $pId=>$pName){
            $obj->_adding($pId,$pName);
        }
        return $obj;
    }

    public function delete(){
        $this->db->exec("DELETE FROM tbl_tasks_comments WHERE col_tcID =".$this['col_tcID']);
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_date':
                parent::_adding($name.'Legend', date_::transDate($value));
                parent::_adding($name.'LegendDT', date_::transDate($value,true));
            break;
            case 'col_text':
                parent::_adding($name.'Legend', htmlspecialchars_decode($value));
                break;
        }

        parent::_adding($name, $value);
    }
}