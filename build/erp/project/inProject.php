<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.12.2016
 * модуль отображения страницы проекта
 **/
namespace build\erp\project;
use build\erp\inc\eController;
use build\erp\inc\iProjectTabs;
use build\erp\inc\Project;
use build\erp\inc\User;
use build\erp\project\m\m_inProject;
use build\erp\tabs\m\mProjectPlan;
use mwce\html_;
use mwce\router;
use mwce\Tools;

class inProject extends eController
{
    protected $getField = array(
        'id' => ['type'=>self::INT],
        'tab' => ['type'=>self::STR],
        'act' => ['type'=>self::STR],
        'dateStart' => ['type'=>self::DATE],
    );

    //как заглушка, чтобы не валидировала пост
    protected $postField = array(
        'someValue' => ['type'=>self::STR],
    );

    public function actionIndex()
    {
        if(empty($_GET['id'])){
            $this->view
                ->set(['errTitle'=>'Просто сообщение','msg_desc'=>'Тут ничего нет. Совсем ;('])
                ->out('error');
        }
        else{
            $project = Project::getCurModel($_GET['id']);
            if(empty($project)){
                $this->view
                    ->set(['errTitle'=>'Сообщение','msg_desc'=>'Такого проекта не существует'])
                    ->out('error');
            }
            else{

                $tabs = m_inProject::GetTabList(router::getUserGroup(),router::getUserRole());

                if(empty($tabs)) {
                    $tabs = array(
                        'customClass' => '',
                        'tabName' => '',
                        'tabIcon' => '',
                        'tabTitle' => '',
                    );
                    $this->view->set('defaultTab','');
                }
                else{
                    $this->view->set('defaultTab',current($tabs)['tabName']);
                }
                $this->view->set('title',$project['col_pnID'].':'.$project['col_projectName']);

                if(strtotime($project['col_dateEndPlan']) < time())
                    $this->view->set('customLState','infoBad');
                else
                    $this->view->set('customLState','infoGood');

                if($project['col_ProjectPlanState'] == 1){
                    $this->view
                        ->set('planStoped','')
                        ->set('planStarted','planStarted');
                }else{
                     $this->view
                        ->set('planStoped','planStopped')
                        ->set('planStarted','');
                }


                $this->view
                    ->add_dict($project)
                    ->loops('tabsList',$tabs,'main',$this->className)
                    ->out('main',$this->className);
            }

        }
    }

    /**
     * форма на табе по умолсанию
     */
    public function actionTabContent(){
        if(!empty($_GET['tab']) && !empty($_GET['id'])){

            $cPath = '\\build\\' . tbuild . '\\' . 'tabs\\' . $_GET['tab'];

            if(class_exists($cPath)){
                $tab = new $cPath($this->view, $this->pages);
                if($tab instanceof iProjectTabs){
                    $tab->In($_GET['id']);
                }
                else{
                    $this->view
                        ->set(['errTitle'=>'Ошибка','msg_desc'=>'Модуль "'.$_GET['tab'].'" не соответствует iProjectTabs'])
                        ->out('error');
                }

            }
            else{
                $this->view
                    ->set(['errTitle'=>'Ошибка','msg_desc'=>'Вкладка "'.$_GET['tab'].'" не найдена.'])
                    ->out('error');
            }

        }
    }

    public function actionExecAction(){
        if(!empty($_GET['id']) && !empty($_GET['tab']) && !empty($_GET['act'])){

            $cPath = '\\build\\' . tbuild . '\\' . 'tabs\\' . $_GET['tab'];

            if(class_exists($cPath)){
                $tab = new $cPath($this->view, $this->pages);
                if($tab instanceof iProjectTabs){
                    $action = $_GET['act'];
                    $tab->$action();
                }
                else{
                    $this->view
                        ->set(['errTitle'=>'Ошибка','msg_desc'=>'Модуль "'.$_GET['tab'].'" не соответствует iProjectTabs'])
                        ->out('error');
                }

            }
            else{
                $this->view
                    ->set(['errTitle'=>'Ошибка','msg_desc'=>'Вкладка "'.$_GET['tab'].'" не найдена.'])
                    ->out('error');
            }
        }
    }

    public function actionUserFromGroup(){
        if(!empty($_GET['id'])){
            $userList = User::getUserGropuList($_GET['id']);
            echo html_::select($userList,'tbUserList',0,'class="form-control inlineBlock"');
        }
    }

    /**
     * перестройка плана
     */
    public function actionRebuildPlan(){
        if(!empty($_GET['id']) && ! empty($_GET['dateStart'])){
            $project = Project::getCurModel($_GET['id']);
            if($project['col_ProjectPlanState']>0){
                echo json_encode(['error'=>'Пока план проекта запущен, изменения запрещены.']);
                return;
            }
            mProjectPlan::rebuildPlan($_GET['id'],$_GET['dateStart']);
        }
    }
}