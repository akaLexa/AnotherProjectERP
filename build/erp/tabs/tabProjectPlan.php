<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.12.2016
 *
 **/
namespace build\erp\tabs;
use build\erp\inc\eController;
use build\erp\inc\iProjectTabs;
use build\erp\inc\Project;
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

                    if($project['col_ProjectPlanState']>0)
                        $this->view->set('isDisable',' DISABLED ');
                    else
                        $this->view->set('isDisable','');

                    foreach ($ai as $item) {

                        $item['dateStart'] = empty($item['col_dateStart']) ? $item['col_dateStartPlanLegend'] : $item['col_dateStartLegend'];
                        $item['dateEnd'] = empty($item['col_dateEndFact']) ? $item['col_dateEndPlanLegend'] : $item['col_dateEndFactLegend'];

                        $this->view
                            ->add_dict($item)
                            ->out('stageCenter',$this->className);
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

                    $users = User::getUserList();
                    $stages = Project::getStagesList();

                    $this->view
                        ->add_dict($project)
                        ->set('stageList',html_::select($stages,'tbStageList',0,'class="form-control inlineBlock"'))
                        ->set('userList',html_::select($users,'tbUserList',router::getCurUser(),'class="form-control inlineBlock"'))
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

                        if(empty($item['col_dateEndFact']))
                            $curSettings['dateStart'] = $item['col_dateEndPlan'];
                        else
                            $curSettings['dateStart'] = $item['col_dateEndFact'];
                    }

                    $users = User::getUserList();
                    $stages = Project::getStagesList();

                    $this->view
                        ->add_dict($project)
                        ->add_dict($stageInfo)
                        ->set('stageList',html_::select($stages,'tbStageList',$stageInfo['col_stageID'],'class="form-control inlineBlock"'))
                        ->set('userList',html_::select($users,'tbUserList',$stageInfo['col_respID'],'class="form-control inlineBlock"'))
                        ->set('curSettings', json_encode($curSettings))
                        ->out('editStageForm',$this->className);
                }
                else if(!empty($_POST['tbStageList']) && !empty($_POST['stageDur']) && !empty($_POST['tbUserList'])&& !empty($_POST['stageSeq'])){

                    $stageInfo->edit($_POST['tbStageList'],$_POST['stageDur'],$_POST['tbUserList'],$_POST['stageSeq']);
                }

            }
        }
    }
}