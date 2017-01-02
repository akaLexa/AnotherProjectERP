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
     * @return mTabTasks
     */
    public static function Add($params){
        $query = self::genInsertSt($params);
        if(!empty($query)){
            $db = Connect::start();
            $db->query("INSERT INTO tbl_tasks $query");
            $lid = $db->lastId('tbl_tasks');

            $curTask = new mTabTasks();
            foreach ($params as $id=>$param) {
                $curTask[$id] = $params;
            }

            return $curTask;
        }
    }

}