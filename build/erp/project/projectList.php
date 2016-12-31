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

    protected $postField = array(
        'projectNum' => ['type' => self::INT],
        'projectName' => ['type' => self::STR],
        'UserResponse' => ['type' => self::INT],
        'UserManager' => ['type' => self::INT],
        'curPage' => ['type' => self::INT],
        'startDate' => ['type' => self::DATE],
        'endDate' => ['type' => self::DATE],
    );

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

        $params['curPage'] =  empty(($_POST['curPage']) ? 1 : $_POST['curPage']);

        if(!empty($_POST['projectNum'])){
            $params['projectNum'] = $_POST['projectNum'];
        }

        if(!empty($_POST['projectName'])){
            $params['projectName'] = $_POST['projectName'];
        }

        if(!empty($_POST['UserResponse'])){
            $params['UserResponse'] = $_POST['UserResponse'];
        }

        if(!empty($_POST['UserManager'])){
            $params['UserManager'] = $_POST['UserManager'];
        }

        if(!empty($_POST['UserManager'])){
            $params['UserManager'] = $_POST['UserManager'];
        }

        if(!empty($_POST['startDate'])){
            $params['startDate'] = $_POST['startDate'];
        }

        if(!empty($_POST['endDate'])){
            $params['endDate'] = $_POST['endDate'];
        }

        $pageCnt = Project::getCountProject($params);

        $pageData = Tools::paginate($pageCnt,50,$params['curPage']);
        $params['pageFrom'] = $pageData['min'];
        $params['pageTo'] = $pageData['max'];

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