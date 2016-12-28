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
use build\erp\inc\tPaginate;
use build\erp\inc\User;
use mwce\html_;
use mwce\Tools;

class projectList extends eController
{

    use tPaginate;

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

        $pageCnt = Project::getCountProject();

        $pageData = Tools::paginate($pageCnt,50,1);

        $params = array(
            'pageFrom' => $pageData['min'],
            'pageTo' => $pageData['max'],
            'curPage' => 1,
        );

        $paginatorHTML = self::paginator($params['curPage'],$pageData['count'],5);

        $list = Project::getModels($params);

        if(!empty($list)){
            $ai = new \ArrayIterator($list);
            foreach ($ai as $item) {

                if(strtotime($item['col_dateEndPlan']) < time())
                    $this->view->set('isDeadLine','deadLine');
                else
                    $this->view->set('isDeadLine','');


                if($item['col_ProjectPlanState'] == 1){
                    $this->view->set('knowProjectPlan','glyphicon glyphicon-play planStarted');
                }
                else{
                    $this->view->set('knowProjectPlan','glyphicon glyphicon-stop planStopped');
                }

                $this->view
                    ->add_dict($item)
                    ->out('center',$this->className);
            }

            if($pageData['count'] >1){
                $this->view
                    ->set('curPaginator',$paginatorHTML)
                    ->out('paginator',$this->className);
            }
        }


    }
}