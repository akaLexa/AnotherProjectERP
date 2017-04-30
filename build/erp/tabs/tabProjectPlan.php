<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.12.2016
 *
 **/
namespace build\erp\tabs;
use build\erp\adm\m\mTaskTypes;
use build\erp\inc\AProjectTabs;
use build\erp\inc\PlanStage;
use build\erp\inc\Project;
use build\erp\inc\Task;
use build\erp\inc\User;
use build\erp\tabs\m\mProjectPlan;
use lib\RandomColor;
use mwce\Tools\Configs;
use mwce\Tools\html;


class tabProjectPlan extends AProjectTabs
{

    protected $props;

    protected $postField = array(
        'tbStageList' =>['type'=>self::INT],
        'stageDur' =>['type'=>self::INT],
        'tbUserList' =>['type'=>self::INT],
        'stageSeq' =>['type'=>self::INT],

        'TaskName' => ['type'=>self::STR,'maxLength'=>255],
        'taskDesc' => ['type'=>self::STR],
        'tName' => ['type'=>self::STR],
        'taskDur' => ['type'=>self::INT],
        'tbGroupList' => ['type'=>self::INT],
        'TaskRtype' => ['type'=>self::INT],
        'TaskRespID' => ['type'=>self::INT],
    );

    protected $getField = array(
        'stageID' => ['type'=>self::INT],
        'taskID' => ['type'=>self::INT],
        'pos' => ['type'=>self::INT],
    );

    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params = null)
    {
        if(!empty($this->project['col_projectID'])){

            if(empty($this->project)){
                $this->view
                    ->set(['errTitle'=>'Ошибка','msg_desc'=>'Данные по выбранному проекту не найдены!'])
                    ->out('error');
            }
            else{

                if($this->project['col_ProjectPlanState']>0)
                    $this->view->set('isDisable',' DISABLED ');
                else
                    $this->view->set('isDisable','');

                //region шаблоны
                $tp = PlanStage::getSavedList();
                $tp[0] = '...';
                $this->view->set('savedPlansList',html::select($tp,'planStageTheme',0,'class="form-control inlineBlock" style="width:300px;"'));

                //endregion

                $this->view
                    ->add_dict($this->project)
                    ->out('main',$this->className);
            }
        }
        else{
            $this->view
                ->set(['errTitle'=>'Ошибка','msg_desc'=>'Данные по выбранному проекту не найдены!'])
                ->out('error');
        }

    }

    public function AddTheme(){
        if(!empty($this->project['col_projectID']) && !empty($_POST['tName'])){
            try{
                PlanStage::Add($_POST['tName'],$this->project['col_projectID']);
                echo json_encode(['state'=>1]);
            }
            catch (\Exception $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }

        }
    }

    public function DelTheme(){
        if(!empty($this->project['col_projectID']) && !empty($_GET['pos'])){
            PlanStage::delPlan($_GET['pos']);
        }
    }

    public function addPlan(){
        if(!empty($this->project['col_projectID']) && !empty($_GET['pos'])){
            PlanStage::ExportToPlan($_GET['pos'],$this->project['col_projectID']);
        }
    }

    public function getList(){
        if(!empty($this->project['col_projectID'])){
            if(!empty($this->project)){
                $stageList = mProjectPlan::getModels($this->project);

                if(!empty($stageList)){
                    $ai = new \ArrayIterator($stageList);

                    $curStage = 0;

                    if($this->project['col_ProjectPlanState']>0)
                        $this->view->set('isDisable',' DISABLED ');
                    else
                        $this->view->set('isDisable','');

                    foreach ($ai as $item) {

                        $item['dateStart'] = empty($item['col_dateStart']) ? $item['col_dateStartPlanLegend'] : $item['col_dateStartLegend'];
                        $item['dateEnd'] = empty($item['col_dateEndFact']) ? $item['col_dateEndPlanLegend'] : $item['col_dateEndFactLegend'];

                        if(strtotime($item['dateEnd'])<time())
                            $this->view->set('oldDateRed','color:red;');
                        else
                            $this->view->set('oldDateRed','');

                        $this->view->add_dict($item);

                        if($curStage != $item['col_pstageID'])
                        {
                            if(empty($item['col_dateStartPlan']))
                                $this->view->set('isNotPlan','opacity: 0.4');
                            else
                                $this->view->set('isNotPlan','');

                            if($item['col_statusID'] !=5)
                                $this->view->set('isDisable',' DISABLED ');
                            else
                                $this->view->set('isDisable',' ');

                            $curStage = $item['col_pstageID'];
                            $this->view->out('stageCenter',$this->className);
                        }



                        if(!empty($item['col_taskName'])){

                            if(empty($item['col_taskStartPlan']))
                                $this->view->set('isNotPlan','opacity: 0.4');
                            else
                                $this->view->set('isNotPlan','');

                            if(!empty($item['col_nextID']))
                                $this->view->set('isfirstTask','display:none;');
                            else
                                $this->view->set('isfirstTask','');

                            if(empty($item['col_taskStartPlan'])){
                                $this->view
                                    ->set('hintPref', ' не из плана!')
                                    ->set('taskStyle','color:red;')
                                    ->set('isfirstTask','display:none;')
                                ;
                            }
                            else{
                                $this->view
                                    /*->emptyName('hintPref')
                                    ->emptyName('taskStyle')*/
                                    ->set('hintPref', '')
                                    ->set('taskStyle','')
                                ;
                            }

                            if(strtotime(date('Y-m-d',strtotime($item['col_taskEnd'])))<strtotime(date('Y-m-d',strtotime('NOW'))))
                                $this->view->set('oldDateRed','color:red;');
                            else
                                $this->view->set('oldDateRed','');

                            if($item['col_taskStatusID'] !=5)
                                $this->view->set('isDisable',' DISABLED ');
                            else
                                $this->view->set('isDisable',' ');

                            $this->view->out('taskCenter',$this->className);
                        }
                    }
                }
            }
        }
    }

    public function Gant(){
        if(!empty($this->project['col_projectID'])){
            $stageList = mProjectPlan::getModels($this->project);
            if(!empty($stageList)){
                $endDate = strtotime(date('Y-m-d'));
                $startDate = strtotime(date('Y-m-d'));

                foreach ($stageList as $id =>$data){
                    if(!empty($data['col_dateStartPlan']) && !empty($data['col_dateEndPlan']) ||
                        !empty($data['col_dateStart'])){

                        if (!empty($data['col_dateStart']))
                            $tmpStart = strtotime($data['col_dateStart']);
                        else if(!empty($data['col_dateStartPlan']))
                            $tmpStart = strtotime($data['col_dateStartPlan']);


                        if(empty($data['col_dateEndFact']))
                            $tmpEnd = strtotime($data['col_dateEndPlan']);
                        else
                            $tmpEnd = strtotime($data['col_dateEndFact']);
                    }


                    if(!empty($data['col_taskStartPlan']) && !empty($data['col_taskEnd'])){
                        $tmpStart = strtotime($data['col_taskStartPlan']);
                        $tmpEnd = strtotime($data['col_taskEnd']);
                    }

                    if(!empty($tmpStart) && !empty($tmpEnd)){
                        if($startDate > $tmpStart)
                            $startDate = $tmpStart;

                        if($endDate < $tmpEnd)
                            $endDate = $tmpEnd;
                    }
                }

                if($startDate != $endDate){
                    $startDate = (int)$startDate;
                    $endDate = (int)$endDate;

                    #region шапка
                    $_startDate = $startDate;
                    $i = 0;

                    while ($_startDate < $endDate){
                        $this->view
                            ->set('date',date('d.m',$_startDate))
                            ->out('gantHead',$this->className);

                        $_startDate += 86400;
                        $i++;
                    }
                    $i++;

                    $this->view
                        ->set('date',date('d.m',$_startDate))
                        ->out('gantHead',$this->className);

                    $this->view->setFContainer('days',true);
                    #endregion

                    #region центр
                    $curStage =0;
                    foreach ($stageList as $id =>$data){

                        if($curStage != $data['col_pstageID']){
                            $curStage = $data['col_pstageID'];

                            $end = !empty($data['col_dateEndFact']) ? ($data['col_dateEndFact']) : ($data['col_dateEndPlan']);
                            $end_ = explode(' ',$end);
                            $end = strtotime($end_[0]);

                            $start = !empty($data['col_dateStart']) ? ($data['col_dateStart']) : ($data['col_dateStartPlan']);
                            $start_ = explode(' ',$start);
                            $start = strtotime($start_[0]);

                            $_startDate = $startDate;
                            $duration = round(($end - $start)/86400) + 1;

                            $days = '';
                            if($_startDate<$start){
                                $n = round(($start - $_startDate)/86400)+1;
                                $days.= str_repeat('<td>&nbsp;</td>',$n);
                            }

                            $name = ($duration*8) >= strlen($data['col_StageName']) ? $data['col_StageName'] : '';

                            $days.= "<td style='background-color:".RandomColor::one().";vertical-align: middle;cursor: help;padding-top:5px;text-align: center;color:white;text-shadow: 1px 1px 2px black;' title='Стадия: {$data['col_StageName']}' colspan='$duration'><b class='glyphicon glyphicon-arrow-right' style='color:white;text-shadow: 1px 1px 2px black;'></b> $name</td>";

                            if($endDate>$end)
                            {
                                $n = round(($endDate - $end)/86400);
                                $days.= str_repeat('<td>&nbsp;</td>',$n);
                            }

                            $this->view
                                ->set('tdContent',$days)
                                ->out('gantCenter',$this->className)
                            ;
                        }

                        if (!empty($data['col_taskName'])){

                            $end = $data['col_taskEnd'];
                            $end_ = explode(' ',$end);
                            $end = strtotime($end_[0]);

                            $start = $data['col_taskStart'];
                            $start_ = explode(' ',$start);
                            $start = strtotime($start_[0]);


                            $duration = round(($end - $start)/86400) + 1;

                            $days = '';
                            if($startDate<$start){
                                $n = round(($start - $startDate)/86400)+1;
                                $days.= str_repeat('<td>&nbsp;</td>',$n);
                            }

                            $name = ($duration*5) >= strlen($data['col_taskName']) ? $data['col_taskName'] : '';
                            $days.= "<td style='background-color: ".RandomColor::one().";vertical-align: middle;cursor: help;padding-top:5px;text-align: center;color:white;text-shadow: 1px 1px 2px black;' title='Задача: {$data['col_taskName']}' colspan='$duration'><b class='glyphicon glyphicon-time' style='color:white;text-shadow: 1px 1px 2px black;'></b> $name</td>";

                            if($endDate>$end)
                            {
                                $n = round(($endDate - $end)/86400);
                                $days.= str_repeat('<td>&nbsp;</td>',$n);
                            }

                            $this->view
                                ->set('tdContent',$days)
                                ->out('gantCenter',$this->className)
                            ;
                        }
                    }
                    $this->view->setFContainer('content',true);

                    #endregion

                    $this->view
                        ->set('legend', date('d-m-Y',$startDate)." - ".date('d-m-Y',$endDate))
                        ->out('gantMain',$this->className);
                }
                else{
                    echo 'Мало данных';
                }
            }
        }
    }

    /**
     * добавление стадии проекта
     */
    public function add(){
        if(!empty($this->project['col_projectID'])){

            if($this->project['col_ProjectPlanState']>0){
                echo json_encode(['error'=>'Пока план проекта запущен, изменения запрещены.']);
                return;
            }
            if(!empty($this->project)){
                if(empty($_POST)){

                    $stageList = mProjectPlan::getModels($this->project);
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
                        ->add_dict($this->project)
                        ->set('stageList',html::select($stages,'tbStageList',0,'class="form-control inlineBlock"'))
                        ->set('userList',html::select($users,'tbGroupList',0,'class="form-control inlineBlock" onchange="genUserFromGroup(\'tdUserList\',this.value)"'))
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
        if(!empty($_GET['stageID'])){

            $stageInfo = mProjectPlan::getCurModel($_GET['stageID']);

            if($this->project['col_ProjectPlanState']>0){
                echo json_encode(['error'=>'Пока план проекта запущен, изменения запрещены.']);
                return;
            }
            if($stageInfo['col_statusID']!=5){
                echo json_encode(['error'=>'Редактировать можно стадии только со статусом "План".']);
                return;
            }

            if(!empty($stageInfo)){
                if(empty($_POST)){
                    $stageList = mProjectPlan::getModels($this->project);
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

                        if($item['col_pstageID'] == $_GET['stageID'])
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
                        ->add_dict($this->project)
                        ->add_dict($stageInfo)
                        ->set('stageList',html::select($stages,'tbStageList',$stageInfo['col_stageID'],'class="form-control inlineBlock"'))
                        ->set('userList',html::select($users,'tbUserList',$stageInfo['col_respID'],'class="form-control inlineBlock"'))
                        ->set('groupList',html::select($groups,'tbGroupList',0,'class="form-control inlineBlock" onchange="genUserFromGroup(\'tdUserList\',this.value)"'))
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
        if(!empty($_GET['stageID'])) {

            $stageInfo = mProjectPlan::getCurModel($_GET['stageID']);

            if($this->project['col_ProjectPlanState']>0){
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
                    ->set('genTypeTaskList',html::select($types,'hbTaskTypes','0',' class="form-control inlineBlock" style="width:300px;" onchange="document.querySelector(\'#_TaskName\').value=this.value"'))
                    ->set('groupList',html::select($users,'tbGroupList',0,'class="form-control inlineBlock" onchange="genUserFromGroup(\'tdUserList\',this.value)"'))
                    ->set('tRepsList',html::select(Task::$resps,'TaskRtype',0,'class="form-control inlineBlock"'))
                    ->set('tRepsTaskList',html::select(Task::getParentTasks($_GET['stageID']),'TaskRespID',0,'class="form-control inlineBlock" style="width:180px;"'))
                    ->out('addTaskForm',$this->className);
            }
            else if(!empty($_POST['TaskName']) && !empty($_POST['taskDur'])&& !empty($_POST['tbUserList'])){
                $totalDurs = Task::getSumDur($_GET['stageID']) + $_POST['taskDur'];

                if(empty($_POST['TaskRespID']))
                    $_POST['TaskRtype'] = 1;

                Task::Add([
                    'col_taskName' => "'{$_POST['TaskName']}'",
                    'col_StatusID' => 5,
                    'col_initID'=> Configs::userID(), // пока в плане, инициатор тот, кто составил план
                    'col_respID' => $_POST['tbUserList'],
                    'col_curatorID' =>'Null',
                    'col_pstageID'=> $_GET['stageID'],
                    'col_taskDesc' => !empty($_POST['taskDesc']) ? "'{$_POST['taskDesc']}'" : 'NULL',
                    'col_createDate' => 'NOW()',
                    'col_startPlan'=> "'{$stageInfo['col_dateStartPlan']}'",
                    'col_endPlan' => "DATE_ADD('{$stageInfo['col_dateStartPlan']}', interval $totalDurs DAY)",
                    'col_autoStart' =>'NULL',
                    'col_taskDur' => $_POST['taskDur'],
                    'col_fromPlan' => 1,
                    'col_nextID' => !empty($_POST['TaskRespID']) ? $_POST['TaskRespID'] : 'NULL',
                    'col_bonding' => !empty($_POST['TaskRtype']) ? $_POST['TaskRtype'] : 0,
                ]);
            }

        }
    }

    public function editStageTask(){
        if(!empty($_GET['taskID'])) {

            $curTask = Task::getCurModel($_GET['taskID']);
            if(empty($curTask)){
                echo json_encode(['error'=>'Задача не найдена!']);
                return;
            }

            if($curTask['col_StatusID']!=5){
                echo json_encode(['error'=>'Редактировать можно только задачу со статусом "План"']);
                return;
            }

            $stageInfo = mProjectPlan::getCurModel($curTask['col_pstageID']);


            if($this->project['col_ProjectPlanState']>0){
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

                $groups = User::getGropList();
                unset($groups[2],$groups[3]);
                $groups[0] = '..';

                $users = User::getUserList();

                $this->view
                    ->add_dict($curTask)
                    ->set('userList',html::select($users,'tbUserList',$curTask['col_respID'],'class="form-control inlineBlock"'))
                    ->set('tRepsTaskList',html::select(Task::getParentTasks($curTask['col_pstageID'],$_GET['taskID']),'TaskRespID',$curTask['col_nextID'],'class="form-control inlineBlock" style="width:180px;"'))
                    ->set('genTypeTaskList',html::select($types,'hbTaskTypes','0',' class="form-control inlineBlock" style="width:300px;" onchange="document.querySelector(\'#_TaskName\').value=this.value"'))
                    ->set('groupList',html::select($groups,'tbGroupList',0,'class="form-control inlineBlock" onchange="genUserFromGroup(\'tdUserList\',this.value)"'))
                    ->set('tRepsList',html::select(Task::$resps,'TaskRtype',$curTask['col_bonding'],'class="form-control inlineBlock"'))
                    ->out('editTaskForm',$this->className);
            }
            else if(!empty($_POST['TaskName']) && !empty($_POST['taskDur']) && !empty($_POST['tbUserList'])){

                if(empty($_POST['TaskRespID']))
                    $_POST['TaskRtype'] = 0;

                $curTask->edit([
                    'col_taskName' => $_POST['TaskName'],
                    'col_initID'=> Configs::userID(), // пока в плане, инициатор тот, кто составил план
                    'col_respID' => $_POST['tbUserList'],
                    'col_curatorID' =>'null',
                    'col_taskDesc' => !empty($_POST['taskDesc']) ? $_POST['taskDesc'] : 'NULL',
                    'col_taskDur' => $_POST['taskDur'],
                    'col_nextID' => !empty($_POST['TaskRespID']) ? $_POST['TaskRespID'] : 'NULL',
                    'col_bonding' => !empty($_POST['TaskRtype']) ? $_POST['TaskRtype'] : 0,
                ]);
            }
        }
    }

    public function deleteTask(){
        if(!empty($_GET['taskID'])){
            $curTask = Task::getCurModel($_GET['taskID']);
            if(empty($curTask)){
                echo json_encode(['error'=>'Задача не найдена!']);
                return;
            }

            if($curTask['col_StatusID']!=5){
                echo json_encode(['error'=>'Редактировать можно только задачу со статусом "План"']);
                return;
            }

            $stageInfo = mProjectPlan::getCurModel($curTask['col_pstageID']);

            if($this->project['col_ProjectPlanState']>0){
                echo json_encode(['error'=>'Пока план проекта запущен, изменения запрещены.']);
                return;
            }
            if($stageInfo['col_statusID']!=5){
                echo json_encode(['error'=>'Редактировать можно стадии только со статусом "План".']);
                return;
            }

            $curTask->delete();
        }
    }

    public function deleteStage(){
        if(!empty($_GET['stageID'])){
            $stageInfo = mProjectPlan::getCurModel($_GET['stageID']);

            if($this->project['col_ProjectPlanState']>0){
                echo json_encode(['error'=>'Пока план проекта запущен, изменения запрещены.']);
                return;
            }
            if($stageInfo['col_statusID']!=5){
                echo json_encode(['error'=>'Редактировать можно стадии только со статусом "План".']);
                return;
            }
            $stageInfo->delete();
        }
    }
}