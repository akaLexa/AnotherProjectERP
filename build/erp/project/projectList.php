<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 05.12.2016
 * список проектов
 **/
namespace build\erp\project;
use build\erp\inc\eController;
use build\erp\inc\Project;
use build\erp\inc\User;
use mwce\html_;
use mwce\Tools;

class projectList extends eController
{
    public function actionIndex()
    {
        $usrs = User::getUserList();
        $usrs[0] = '...';
        $this->view
            ->set('userRespList',html_::select($usrs,'UserResponse',0,'class="erpSelect" style="width:100%;"'))
            ->set('userMngrList',html_::select($usrs,'UserManager',0,'class="erpSelect" style="width:100%;"'))
            ->out('main',$this->className);
    }

    public function actionGetProjects(){
        $list = Project::getModels();
        if(!empty($list)){
            $ai = new \ArrayIterator($list);
            foreach ($ai as $item) {

                if(strtotime($item['col_dateEndPlan'] < time()))
                    $this->view->set('isDeadLine','deadLine');
                else
                    $this->view->set('isDeadLine','');

                $this->view
                    ->add_dict($item)
                    ->out('center',$this->className);
            }
        }
    }
}