<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 04.01.2017
 *
 **/
namespace build\erp\main\m;
use build\erp\inc\Task;
use build\erp\inc\TaskComments;
use mwce\db\Connect;
use mwce\Tools\Configs;
use mwce\Tools\Date;
use mwce\Tools\Tools;

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

    /**
     * можно ли продлить задачу? не создан ли уже запрос?
     * @return bool
     */
    public function mayContinue(){
        if(empty($this['col_conToDate']))
            return true;
        return false;
    }

    /**
     * запросить продление
     * @param string $desc
     * @param string $data
     */
    public function ContinueRequest($desc,$data){

        $this->db->exec("UPDATE tbl_tasks SET col_continueDes = '$desc <br> новая плановая дата завершения: ".Date::transDate($data)."', col_conToDate='$data' WHERE col_taskID = ".$this['col_taskID']);
        TaskComments::Add([
            'col_taskID' => $this['col_taskID'],
            'col_UserID' => Configs::userID(),
            'col_text' => '<b>Запрос на продление задачи до '.Date::transDate($data).' по причине:</b> '.$desc,
        ]);
    }

    /**
     * согласие с запросом на продление
     */
    public function ContinueAccept($desc = null){
        $this->db->exec("UPDATE tbl_tasks SET col_conToDate = null,col_endPlan = '{$this['col_conToDate']}' WHERE col_taskID = ".$this['col_taskID']);
        TaskComments::Add([
            'col_taskID' => $this['col_taskID'],
            'col_UserID' => Configs::userID(),
            'col_text' => '<b class="success">Задача была продлена с '.$this['col_endPlanLegend'].' до '.Date::transDate($this['col_conToDate']).'</b>'.(!is_null($desc) ? ' <u>Комментарий</u>: '.$desc : ''),
        ]);
    }

    /**
     * отклонение запроса на продление
     * @param null|string $desc
     */
    public function ContinueReject($desc = null)
    {
        $this->db->exec("UPDATE tbl_tasks SET col_conToDate = null WHERE col_taskID = " . $this['col_taskID']);
        TaskComments::Add([
            'col_taskID' => $this['col_taskID'],
            'col_UserID' => Configs::userID(),
            'col_text' => '<b class="fail">Запрос был отклонен</b> '.(!is_null($desc) ? 'по причине: '.$desc : ''),
        ]);
    }
}