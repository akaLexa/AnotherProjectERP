<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.12.2016
 *
 **/
namespace build\erp\tabs;
use build\erp\adm\m\mTaskTypes;
use build\erp\inc\eController;
use build\erp\inc\iProjectTabs;
use build\erp\inc\Project;
use build\erp\inc\Task;
use build\erp\inc\User;
use build\erp\tabs\m\mProjectPlan;
use mwce\html_;
use mwce\router;
use mwce\Tools;

class tabProjectPlan extends eController implements iProjectTabs
{

    protected $props;

    protected $postField = array(
        'tbStageList' =>['type'=>self::INT],
        'stageDur' =>['type'=>self::INT],
        'tbUserList' =>['type'=>self::INT],
        'stageSeq' =>['type'=>self::INT],

        'TaskName' => ['type'=>self::STR,'maxLength'=>255],
        'taskDesc' => ['type'=>self::STR],
        'taskDur' => ['type'=>self::INT],
        'tbGroupList' => ['type'=>self::INT],
        'TaskRtype' => ['type'=>self::INT],
        'TaskRespID' => ['type'=>self::INT],
    );

    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params = null)
    {
        if(!empty($_GET['id'])){
            $project = Project::getCurModel($_GET['id']);

            if(empty($project)){
                $this->view
                    ->set(['errTitle'=>'Ошибка','msg_desc'=>'Данные по выбранному проекту не найдены!'])
                    ->out('error');
            }
            else{

                if($project['col_ProjectPlanState']>0)
                    $this->view->set('isDisable',' DISABLED ');
                else
                    $this->view->set('isDisable','');

                $this->view
                    ->add_dict($project)
                    ->out('main',$this->className);
            }
        }
        else{
            $this->view
                ->set(['errTitle'=>'Ошибка','msg_desc'=>'Данные по выбранному проекту не найдены!'])
                ->out('error');
        }

    }

    public function getList(){
        if(!empty($_GET['id'])){
            $project = Project::getCurModel($_GET['id']);
            if(!empty($project)){
                $stageList = mProjectPlan::getModels($project);

                if(!empty($stageList)){
                    $ai = new \ArrayIterator($stageList);

                    $curStage = 0;

                    if($project['col_ProjectPlanState']>0)
                        $this->view->set('isDisable',' DISABLED ');
                    else
                        $this->view->set('isDisable','');

                    foreach ($ai as $item) {

                        $item['dateStart'] = empty($item['col_dateStart']) ? $item['col_dateStartPlanLegend'] : $item['col_dateStartLegend'];
                        $item['dateEnd'] = empty($item['col_dateEndFact']) ? $item['col_dateEndPlanLegend'] : $item['col_dateEndFactLegend'];

                        $this->view->add_dict($item);
                        if($curStage != $item['col_pstageID'])
                        {
                            $curStage = $item['col_pstageID'];
                            $this->view->out('stageCenter',$this->className);
                        }

                        if(!empty($item['col_taskName'])){
                            $this->view->out('taskCenter',$this->className);
                        }
                    }
                }
                //Tools::debug($stageList);
            }
        }
    }

    /**
     * настройки для модуля
     * @return array
     */
    public function getProperties()
    {
        if(!empty($this->props))
            return $this->props;

        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg'.DIRECTORY_SEPARATOR.$this->className.'.php';
        if(file_exists($path)) {
            $this->props = require $path;
            return $this->props;
        }
        else
            return [];
    }

    /**
     * добавление стадии проекта
     */
    public function add(){
        if(!empty($_GET['id'])){
            $project = Project::getCurModel($_GET['id']);
            if($project['col_ProjectPlanState']>0){
                echo json_encode(['error'=>'Пока план проекта запущен, изменения запрещены.']);
                return;
            }
            if(!empty($project)){
                if(empty($_POST)){

                    $stageList = mProjectPlan::getModels($project);
                    $curSettings = array('nextSeq'=>2,'minSeq'=>2,'dateStart'=>date('Y-m-d'));

                    foreach ($stageList as $item) {
                        if($item['col_statusID']!=5 && $curSettings['minSeq']<$item['col_seq']){
                            $curSettings['minSeq'] = $item['col_seq'];
                        }

                        if($item['col_seq'] + 1 > $curSettings['nextSeq']){
                            $curSettings['nextSeq'] = $item['col_seq'] + 1;
                        }

                        if(empty($item['col_dateEndFact']))
                            $curSettings['dateStart'] = $item['col_dateEndPlan'];
                        else
                            $curSettings['dateStart'] = $item['col_dateEndFact'];
                    }

                    $users = User::getGropList();
                    unset($users[2],$users[3]);
                    $users[0] = '..';
                    $stages = Project::getStagesList();

                    $this->view
                        ->add_dict($project)
                        ->set('stageList',html_::select($stages,'tbStageList',0,'class="form-control inlineBlock"'))
                        ->set('userList',html_::select($users,'tbGroupList',0,'class="form-control inlineBlock" onchange="genUserFromGroup(\'tdUserList\',this.value)"'))
                        //->set('userList',html_::select($users,'tbUserList',router::getCurUser(),'class="form-control inlineBlock"'))
                        ->set('curSettings', json_encode($curSettings))
                        ->out('addStageForm',$this->className);
                }
                else if(!empty($_POST['tbStageList']) && !empty($_POST['stageDur']) && !empty($_POST['tbUserList'])){
                    mProjectPlan::AddPlanState($_GET['id'],$_POST['tbStageList'],$_POST['stageDur'],$_POST['tbUserList']);
                }

            }
        }
    }

    public function edit(){
        if(!empty($_GET['id'])){

            $stageInfo = mProjectPlan::getCurModel($_GET['id']);
            $project = Project::getCurModel($stageInfo['col_projectID']);

            if($project['col_ProjectPlanState']>0){
                echo json_encode(['error'=>'Пока план проекта запущен, изменения запрещены.']);
                return;
            }
            if($stageInfo['col_statusID']!=5){
                echo json_encode(['error'=>'Редактировать можно стадии только со статусом "План".']);
                return;
            }

            if(!empty($stageInfo)){
                if(empty($_POST)){
                    $stageList = mProjectPlan::getModels($project);
                    $curSettings = array('nextSeq'=>2,'minSeq'=>2,'dateStart'=>date('Y-m-d'));

                    foreach ($stageList as $item) {
                        if($item['col_statusID']!=5 && $curSettings['minSeq']<$item['col_seq']){
                            $curSettings['minSeq'] = $item['col_seq'];
                        }

                        if($item['col_seq'] + 1 > $curSettings['nextSeq']){
                            $curSettings['nextSeq'] = $item['col_seq'] + 1;
                        }
                    }

                    foreach ($stageList as $item) {

                        if($item['col_pstageID'] == $_GET['id'])
                            break;

                        if(empty($item['col_dateEndFact']))
                            $curSettings['dateStart'] = $item['col_dateEndPlan'];
                        else
                            $curSettings['dateStart'] = $item['col_dateEndFact'];
                    }


                    $users = User::getUserList();
                    $stages = Project::getStagesList();
                    $groups = User::getGropList();
                    unset($groups[2],$groups[3]);
                    $groups[0] = '..';


                    $this->view
                        ->add_dict($project)
                        ->add_dict($stageInfo)
                        ->set('stageList',html_::select($stages,'tbStageList',$stageInfo['col_stageID'],'class="form-control inlineBlock"'))
                        ->set('userList',html_::select($users,'tbUserList',$stageInfo['col_respID'],'class="form-control inlineBlock"'))
                        ->set('groupList',html_::select($groups,'tbGroupList',0,'class="form-control inlineBlock" onchange="genUserFromGroup(\'tdUserList\',this.value)"'))
                        ->set('curSettings', json_encode($curSettings))
                        ->out('editStageForm',$this->className);
                }
                else if(!empty($_POST['tbStageList']) && !empty($_POST['stageDur']) && !empty($_POST['tbUserList'])&& !empty($_POST['stageSeq'])){

                    $stageInfo->edit($_POST['tbStageList'],$_POST['stageDur'],$_POST['tbUserList'],$_POST['stageSeq']);
                }

            }
        }
    }

    public function addStageTask(){
        if(!empty($_GET['id'])) {

            $stageInfo = mProjectPlan::getCurModel($_GET['id']);
            $project = Project::getCurModel($stageInfo['col_projectID']);

            if($project['col_ProjectPlanState']>0){
                echo json_encode(['error'=>'Пока план проекта запущен, изменения запрещены.']);
                return;
            }
            if($stageInfo['col_statusID']!=5){
                echo json_encode(['error'=>'Редактировать можно стадии только со статусом "План".']);
                return;
            }

            if(empty($_POST)){
                $types = mTaskTypes::getTypesList();
                $types[0] = '...';

                $users = User::getGropList();
                unset($users[2],$users[3]);
                $users[0] = '..';


                $this->view
                    ->set('genTypeTaskList',html_::select($types,'hbTaskTypes','0',' class="form-control inlineBlock" style="width:300px;" onchange="document.querySelector(\'#_TaskName\').value=this.value"'))
                    ->set('groupList',html_::select($users,'tbGroupList',0,'class="form-control inlineBlock" onchange="genUserFromGroup(\'tdUserList\',this.value)"'))
                    ->set('tRepsList',html_::select(Task::$resps,'TaskRtype',0,'class="form-control inlineBlock" onchange="if(this.value > 0){ document.querySelector(\'#TaskRespID\').style.display=\'inline-block\'; } else { document.querySelector(\'#TaskRespID\').style.display=\'none\'; }"'))
                    ->set('tRepsTaskList',html_::select(Task::getParentTasks($_GET['id']),'TaskRespID',0,'class="form-control inlineBlock" style="width:180px; display:none;" onchange=""'))
                    ->out('addTaskForm',$this->className);
            }
            else if(!empty($_POST['TaskName']) && !empty($_POST['taskDur'])&& !empty($_POST['tbUserList'])){

                $sumDur = Task::getSumDur($_GET['id']) + $_POST['taskDur'];
                Task::Add([
                    'col_taskName' => "'{$_POST['TaskName']}'",
                    'col_StatusID' => 5,
                    'col_initID'=> router::getCurUser(), // пока в плане, инициатор тот, кто составил план
                    'col_respID' => $_POST['tbUserList'],
                    'col_curatorID' =>'Null',
                    'col_pstageID'=>$_GET['id'],
                    'col_taskDesc' => !empty($_POST['taskDesc']) ? "'{$_POST['taskDesc']}'" : 'NULL',
                    'col_createDate' => 'NOW()',
                    'col_startPlan'=> "'{$stageInfo['col_dateStartPlan']}'",
                    'col_endPlan' => "DATE_ADD('{$stageInfo['col_dateStartPlan']}', interval {$_POST['taskDur']} DAY)",
                    'col_autoStart' =>'NULL',
                    'col_taskDur' => $_POST['taskDur'],
                    'col_fromPlan' => 1,
                    'col_nextID' => !empty($_POST['TaskRespID']) ? $_POST['TaskRespID'] : 'NULL',
                    'col_bonding' => !empty($_POST['TaskRtype']) ? $_POST['TaskRtype'] : 0,
                ]);
            }

        }
    }
}