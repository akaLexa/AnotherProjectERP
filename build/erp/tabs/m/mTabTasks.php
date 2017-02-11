<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 02.01.2017
 *
 **/
namespace build\erp\tabs\m;
use build\erp\inc\Task;
use mwce\Connect;

class mTabTasks extends Task
{
    /**
     * @param array $params
     * @return mTabTasks|bool
     */
    public static function Add($params){
        $query = self::genInsertSt($params);
        if(!empty($query)){
            $db = Connect::start();
            $db->query("INSERT INTO tbl_tasks $query");
            return Task::getCurModel($db->lastId('tbl_tasks'));
        }
        return false;
    }

}