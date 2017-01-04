<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 04.01.2017
 *
 **/
namespace build\erp\main;
use build\erp\inc\eController;
use build\erp\inc\Project;
use build\erp\inc\tPaginate;
use build\erp\inc\User;
use build\erp\main\m\mTasks;
use mwce\html_;
use mwce\router;
use mwce\Tools;

class tasks extends eController
{
    use tPaginate;

    protected $postField = array(
        'projectNum' => ['type'=>self::INT],
        'isCurator' => ['type'=>self::INT],
        'taskStatus' => ['type'=>self::INT],
        'taskInit' => ['type'=>self::INT],
        'taskResp' => ['type'=>self::INT],
        'curPage' => ['type'=>self::INT],
        'projectName' => ['type'=>self::STR],
        'taskName' => ['type'=>self::STR],
        'dBegin' => ['type'=>self::DATE],
        'dEnd' => ['type'=>self::DATE],
        'dEndFact' => ['type'=>self::DATE],
    );

    protected $getField = array(
        'id' => ['type'=>self::INT],
    );

    public function actionIndex()
    {
        $users = User::getUserList();
        $users[0] = '...';

        $this->view
            ->set('stateList',html_::select(Project::getStates(),'taskStatus',1,'style="width:150px;" class="erpInput" onchange="filterTask();"'))
            ->set('initList',html_::select($users,'taskInit',0,'style="width:120px;" class="erpInput" onchange="filterTask();"'))
            ->set('respList',html_::select($users,'taskResp',router::getCurUser(),'style="width:120px;" class="erpInput" onchange="filterTask();"'))
            ->out('main',$this->className);
    }

    public function actionGetList(){

        $params['curPage'] = !empty($_POST['curPage']) ? $_POST['curPage'] : 1;

        if(!empty($_POST['projectNum']))
            $params['projectID'] = $_POST['projectNum'];

        if(!empty($_POST['isCurator']))
            $params['taskCurator'] = router::getCurUser();

        if(!empty($_POST['taskStatus']))
            $params['taskStatus'] = $_POST['taskStatus'];

        if(!empty($_POST['taskInit']))
            $params['taskInit'] = $_POST['taskInit'];

        if(!empty($_POST['taskResp']))
            $params['taskResp'] = $_POST['taskResp'];

        if(!empty($_POST['projectName']))
            $params['projectName'] = $_POST['projectName'];

        if(!empty($_POST['taskName']))
            $params['taskName'] = $_POST['taskName'];

        if(!empty($_POST['dBegin']))
            $params['dbegin'] = $_POST['dBegin'];

        if(!empty($_POST['dEnd']))
            $params['endPlan'] = $_POST['dEnd'];

        if(!empty($_POST['dEndFact']))
            $params['endFact'] = $_POST['dEndFact'];

        $cnt = mTasks::getCount($params);
        $countPage = Tools::paginate($cnt,50,$params['curPage']);
        $params['min'] = $countPage['min'];
        $params['max'] = $countPage['max'];

        $paginatorHTML = self::paginator($params['curPage'],$countPage['count'],5);

        $list = mTasks::getModels($params);
        if(!empty($list)){
            $ai = new \ArrayIterator($list);

            foreach ($ai as $item) {
                $this->view
                    ->add_dict($item)
                    ->out('center',$this->className);
            }

            if($countPage['count']>1){
                $this->view
                    ->set('paginate',$paginatorHTML)
                    ->out('paginator',$this->className);
            }
        }
        else
            $this->view->out('centerEmpty',$this->className);
    }

    public function actionIn(){
        if(!empty($_GET['id'])){
            $task = mTasks::getCurModel($_GET['id']);
            if(empty($task)){
                $this->view
                    ->set(['errTitle'=>'Ошибка','msg_desc'=>'Задача не найдена'])
                    ->out('error');
            }
            else{
                //Tools::debug($task);
                if(empty($task['col_startFactLegend']))
                    $task['col_startFactLegend'] = '?';
                if(empty($task['col_startFactLegend']))
                    $task['col_startFactLegend'] = '?';

                if(empty($task['col_taskDescLegend']))
                    $task['col_taskDescLegend'] = '<p>Описания к задаче почему-то нет...</p>';
                $this->view
                    ->add_dict($task)
                    ->out('in',$this->className);
            }

        }
    }

}