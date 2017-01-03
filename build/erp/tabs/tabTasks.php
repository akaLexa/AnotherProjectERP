<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 02.01.2017
 *
 **/
namespace build\erp\tabs;
use build\erp\adm\m\mTaskTypes;
use build\erp\inc\AprojectTabs;
use build\erp\inc\Project;
use build\erp\inc\Task;
use build\erp\inc\User;
use build\erp\tabs\m\mProjectPlan;
use build\erp\tabs\m\mTabTasks;
use mwce\date_;
use mwce\Exceptions\ModException;
use mwce\html_;
use mwce\router;
use mwce\Tools;

class tabTasks extends AprojectTabs
{
    protected $getField = array(
        'id' => ['type'=>self::INT],
        'quenue' => ['type'=>self::INT],
        'group' => ['type'=>self::INT],
    );

    protected $postField = array(
        'taskName' => ['type'=>self::STR,'maxLength'=>255],
        'tbUserList1' => ['type'=>self::INT],
        'tbUserList2' => ['type'=>self::INT],
        'duration' => ['type'=>self::INT],
        'endDate' => ['type'=>self::DATE],
        'endTime' => ['type'=>self::STR,'maxLength'=>5],
        'taskDesc' => ['type'=>self::STR],

        'taskInit' => ['type'=>self::INT],
        'taskResp' => ['type'=>self::INT],
        'endPlan' => ['type'=>self::DATE],
        'dbegin' => ['type'=>self::DATE],
        'endFact' => ['type'=>self::DATE],
        'taskStatus' => ['type'=>self::INT],
        'ftaskName' => ['type'=>self::STR,'maxLength'=>255],
    );

    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params = null)
    {
        $users = User::getUserList();
        $users[0] = '...';

        $this->view
            ->set('stateList',html_::select(Project::getStates(),'taskStatus',1,'style="width:150px;" class="erpInput" onchange="filterTask();"'))
            ->set('initList',html_::select($users,'taskInit',0,'style="width:100%" class="erpInput" onchange="filterTask();"'))
            ->set('respList',html_::select($users,'taskResp',router::getCurUser(),'style="width:100%" class="erpInput" onchange="filterTask();"'))
            ->out('main',$this->className);
    }

    public function getList(){
        if(!empty($_GET['id'])){
            $params['projectID'] = $_GET['id'];
            if(!empty($_POST['ftaskName']))
                $params['taskName'] = $_POST['ftaskName'];

            if(!empty($_POST['taskStatus']))
                $params['taskStatus'] = $_POST['taskStatus'];
            else
                $params['taskStatus'] = 1;

            if(!empty($_POST['taskInit']))
                $params['taskInit'] = $_POST['taskInit'];

            if(!empty($_POST['taskResp']))
                $params['taskResp'] = $_POST['taskResp'];

            if(!empty($_POST['dbegin']))
                $params['dbegin'] = $_POST['dbegin'];

            if(!empty($_POST['endPlan']))
                $params['endPlan'] = $_POST['endPlan'];

            if(!empty($_POST['endFact']))
                $params['endFact'] = $_POST['endFact'];

            $list = Task::getModels($params);

            if(!empty($list)){
                $ai = new \ArrayIterator($list);

                foreach ($ai as $item) {

                    if($item['col_dayDifs'] <0)
                        $this->view->set('customLeft','color:red;');
                    else
                        $this->view->set('customLeft','');

                    $this->view
                        ->add_dict($item)
                        ->out('center', $this->className);
                }
            }
            else
                $this->view->out('centerEmpty',$this->className);


        }

    }

    public function add(){
        if(!empty($_POST)
            && !empty($_POST['taskName'])
            && !empty($_POST['tbUserList1'])
            && !empty($_POST['endDate'])
            && !empty($_POST['endTime'])
        ){
            $project = Project::getCurModel($_GET['id']);
            if(empty($project))
                return;

            $_POST['duration'] = !empty($_POST['duration']) ? $_POST['duration'] : 1;
            $params = array(
                'col_taskName' => $_POST['taskName'],
                'col_respID' => $_POST['tbUserList1'],
                'col_pstageID' => $project['col_pstageID'],
                'col_createDate' => date_::intransDate('now',true),
                'col_startFact' => date_::intransDate('now',true),
                'col_autoStart' => date_::intransDate('now + '.$_POST['duration'].' DAY',true),
                'col_endPlan' => $_POST['endDate'].' '.$_POST['endTime'],
                'col_taskDur' => $_POST['duration'],
                'col_initID' => router::getCurUser(),
                'col_StatusID' => 4,
            );

            if(!empty($_POST['tbUserList2'])){
                $params['col_curatorID'] = $_POST['tbUserList2'];
            }

            if(!empty($_POST['taskDesc'])){
                $params['col_taskDesc'] = $_POST['taskDesc'];
            }

            try{
                mTabTasks::Add($params);
                echo json_encode(['status'=>1]);
            }
            catch (ModException $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }

        }
        else{
            $types = mTaskTypes::getTypesList();
            $types[0] = '...';

            $groups = User::getGropList();
            $groups[0] = '...';

            $this->view
                ->set('groupList',html_::select($groups,'respGroup',0,'class="form-control inlineBlock" style="width: 150px;" onchange="genIn({element:\'tdResp\',address:\''.$this->view->getAdr().'page/inProject/ExecAction?tab=tabTasks&id='.$_GET['id'].'&act=GenUser&quenue=1&group=\'+this.value})"'))
                ->set('groupList1',html_::select($groups,'curatorGroup',0,'class="form-control inlineBlock" style="width: 150px;" onchange="genIn({element:\'tdResp1\',address:\''.$this->view->getAdr().'page/inProject/ExecAction?tab=tabTasks&id='.$_GET['id'].'&act=GenUser&quenue=2&group=\'+this.value})"'))
                ->set('typeTaskList',html_::select($types,'tTypes','0','class="form-control inlineBlock" style="width: 360px;" onchange="document.querySelector(\'#_taskName\').value = this.value"'))
                ->out('Add',$this->className);
        }
    }

    public function actionAdd(){
        if(!empty($_GET['id'])){
            self::add();
        }
    }

    public function GenUser(){
        if(!empty($_GET['quenue']) && !empty($_GET['group'])){
            $userList = User::getUserGropuList($_GET['group']);
            echo html_::select($userList,'tbUserList'.$_GET['quenue'],0,'class="form-control inlineBlock"');
        }

    }
}