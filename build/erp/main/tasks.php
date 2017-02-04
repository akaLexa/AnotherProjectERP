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
use build\erp\inc\Task;
use build\erp\inc\TaskComments;
use build\erp\inc\tPaginate;
use build\erp\inc\User;
use build\erp\main\m\mTasks;
use mwce\date_;
use mwce\Exceptions\ModException;
use mwce\html_;
use mwce\router;
use mwce\Tools;
use mwce\traits\tInsert;

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

        'acceptReason' => ['type'=>self::STR],
        'stageChoose' => ['type'=>self::INT],
        'task' => ['type'=>self::INT],
        'taskComment' => ['type'=>self::STR],
        'taskCurator' => ['type'=>self::INT],
        'finishTo' => ['type'=>self::DATE],
        'finishToTime' => ['type'=>self::STR],
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

                if(!empty($_POST['stageChoose'])){
                    if($_POST['stageChoose'] == 2 && !empty($_POST['acceptReason']) && $task['col_StatusID'] == 1){ //отказ после принятия в работу
                        $task->fail($_POST['acceptReason']);
                        Tools::go();
                    }
                    elseif ($_POST['stageChoose'] == 3 && $task['col_StatusID'] == 1){ //завершение
                        if(strtotime($task['col_endPlan']) < time() && !empty($_POST['acceptReason'])){
                            $task->finish($_POST['acceptReason']);
                        }
                        else if(strtotime($task['col_endPlan']) >= time()){
                            $task->finish();
                        }
                        Tools::go();
                    }
                    elseif ($_POST['stageChoose'] == 99){//перезапуск

                    }
                    Tools::go();
                }
                else if(isset($_POST['taskActionAccept']) && $task['col_StatusID'] == 4){ // запуск в работу
                    $task->accept();
                    Tools::go();
                }
                elseif (!empty($_POST['acceptReason'])){//отказ
                    $task->decent($_POST['acceptReason']);
                    Tools::go();
                }

                if(empty($task['col_startFactLegend']))
                    $task['col_startFactLegend'] = '?';

                if(empty($task['col_startPlanLegend']))
                    $task['col_startPlanLegend'] = '?';

                if(empty($task['col_endFactLegend']))
                    $task['col_endFactLegend'] = '?';

                if(empty($task['col_taskDescLegend']))
                    $task['col_taskDescLegend'] = '<p>Описания к задаче почему-то нет...</p>';

                $status = Project::getStates();

                if($task['col_initID'] == router::getCurUser() || $task['col_respID'] == router::getCurUser()){
                    switch ($task['col_StatusID']){
                        case 1: //работа
                            if($task['col_respID'] == router::getCurUser()){
                               /* if(!empty($task['col_startPlan'])) //если
                                    unset($status[1],$status[2],$status[4],$status[5]);
                                else*/
                                    unset($status[1],$status[2],$status[4],$status[5]);

                                $status[0] = 'Выберите...';
                                $this->view
                                    ->set('stateList',html_::select($status,'stageChoose',0,'style="width:120px;" class="form-control inlineBlock" onchange="choseAction(this.value);"'))
                                    ->out('actionForm',$this->className);
                                $this->view->setFContainer('inTaskProperties',true);
                            }
                            break;
                        case 2: //отказ
                        case 3: //завершено
                            if($task['col_initID'] == router::getCurUser()){
                                $status = array();
                                $status[99] = 'Перезапустить';
                                $this->view
                                    ->set('stateList',html_::select($status,'stageChoose',0,'style="width:120px;" class="form-control inlineBlock" onchange="choseAction(this.value);"'))
                                    ->out('actionForm',$this->className);
                                $this->view->setFContainer('inTaskProperties',true);
                            }
                            break;
                        case 4: //принятие решения
                            if($task['col_respID'] == router::getCurUser()){
                                $this->view->out('acceptForm',$this->className);
                                $this->view->setFContainer('inTaskProperties',true);
                            }
                            break;
                        case 5: //план
                            $this->view->set('inTaskProperties','Ожидпние запуска');
                            break;
                    }
                }
                else{
                    $this->view->set('inTaskProperties','-/-');
                }

                //подсветка просрочки по окончанию
                if(strtotime($task['col_endPlan']) < time() && $task['col_StatusID'] == 1){
                    $this->view->set('curPlanEndStyle','danger');
                }


                self::actionShowComment($task['col_taskID']);

                $this->view
                    ->setFContainer('commentsList',true)
                    ->add_dict($task)
                    ->out('in',$this->className);
            }
        }
    }

    public function actionShowComment($task = null){

        if(!is_null($task))
            $_GET['id'] = $task;

        if(!empty($_GET['id'])){
            $list = TaskComments::getModels(['taskID'=>$_GET['id']]);
            if(!empty($list)){
                $ai = new \ArrayIterator($list);
                foreach ($ai as $item){
                    if(file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'imgs' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $item['col_UserID'] .'.png')){
                        $di = $item['col_UserID'];
                    }
                    else{
                        $di = 'default';
                    }

                    $this->view
                        ->set('curuserImg',$di)
                        ->add_dict($item)
                        ->out('comment',$this->className);
                }
            }
            else{
                $this->view->out('emptyComment',$this->className);
            }
        }
    }

    public function actionAddComment(){
        if(!empty($_POST['task']) && !empty($_POST['taskComment'])){
            $comment = trim(strip_tags(htmlspecialchars_decode($_POST['taskComment'],ENT_QUOTES)));
            if(strlen($comment)>0){
                try{
                    $task =TaskComments::Add([
                        'col_taskID'=>$_POST['task'],
                        'col_UserID'=>router::getCurUser(),
                        'col_text'=>$_POST['taskComment'],
                    ]);
                    echo json_encode([
                        'data'=>$task['col_dateLegend'],
                        'author' => $task['']
                    ]);
                }
                catch (ModException $e){
                    echo json_encode(['error'=>$e->getMessage()]);
                }
            };
        }
    }

    public function actionReStart()
    {
        if(!empty($_GET['id'])){
            $task = Task::getCurModel($_GET['id']);
            if(empty($task)){
                $this->view
                    ->set(['errTitle'=>'Ошибка','msg_desc'=>'Нет такой задачи'])
                    ->out('error');
            }
            else{
                if(empty($_POST)){
                    $users = User::getUserList();
                    $curUsers = $users;
                    $curUsers[0]='...';
                    $task['col_endPlan_'] = date_::intransDate( $task['col_endPlan']);
                    $this->view
                        ->add_dict($task)
                        ->set('respList',html_::select($users,'taskResp',$task['col_respID'],'class="form-control inlineBlock"'))
                        ->set('curatorList',html_::select($curUsers,'taskCurator',$task['col_curatorID'],'class="form-control inlineBlock"'))
                        ->out('reStart',$this->className);
                }
                else if(
                    !empty($_POST['taskResp'])
                    && !empty($_POST['finishTo'])
                    && !empty($_POST['finishToTime'])
                ){
                    $params = array(
                        'col_respID' => $_POST['taskResp'],
                        'col_endPlan' => $_POST['finishTo'].' '.$_POST['finishToTime'],
                        'col_StatusID' => 4,
                        'col_endFact' => 'NULL',
                        'col_curatorID' => empty($_POST['taskCurator']) ? 'NULL' : $_POST['taskCurator'],
                    );

                    if(!empty($_POST['taskCurator'])){
                        $params['col_curatorID'] = $_POST['taskCurator'];
                    }
                    $task->edit($params);
                    $newDate = date_::transDate($params['col_endPlan'],true);

                    $text = "<b style=\"color:red;\">Задача была перезапущена с {$task['col_endPlanLegendTD']} на $newDate. </b>";

                    if(!empty($_POST['newComment']))
                        $text.="<p>Комментарий:<br>{$_POST['newComment']}</p>";

                    $task->newComment(htmlspecialchars($text,ENT_QUOTES));
                }
            }
        }
    }
}