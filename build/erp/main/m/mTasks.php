<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 04.01.2017
 *
 **/
namespace build\erp\main\m;
use build\erp\inc\Task;
use mwce\Connect;

class mTasks extends Task
{
    /**
     * @param null|array $params
     * @return int
     */
    public static function getCount($params = null){

        $query = self::qBuilder($params);

        if(empty($query))
            return 0;

        $db = Connect::start();
        $res = $db->query("SELECT count(*) as cnt $query")->fetch();
        return $res['cnt'];
    }
}