<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.04.2017
 * отчет по задачам
 **/
namespace build\erp\reports;
use build\erp\inc\eController;
use build\erp\inc\Project;
use build\erp\inc\User;
use build\erp\reports\m\mTaskReport;
use mwce\Tools\html;
use mwce\Tools\Tools;


class TaskReport extends eController
{
    protected $postField = array(
        'inTname' => ['type'=>self::STR],
        'curStatus' => ['type'=>self::INT],
        'curRole' => ['type'=>self::INT],
        'curInit' => ['type'=>self::INT],
        'curResp' => ['type'=>self::INT],
        'dBegin' => ['type'=>self::DATE],
        'dEndPlan' => ['type'=>self::DATE],
        'dEndFact' => ['type'=>self::DATE],
    );

    protected $getField = array(
        'inTname' => ['type'=>self::STR],
        'curStatus' => ['type'=>self::INT],
        'curRole' => ['type'=>self::INT],
        'curInit' => ['type'=>self::INT],
        'curResp' => ['type'=>self::INT],
        'dBegin' => ['type'=>self::DATE],
        'dEndPlan' => ['type'=>self::DATE],
        'dEndFact' => ['type'=>self::DATE],
    );

    public function actionIndex()
    {
        $users = User::getUserList();
        $users[0] = '...';

        $sts = Project::getStates();
        unset($sts[5],$sts[4]);
        $sts[0] = '...';

        $roles = User::getGropList();
        $roles[0] = '...';

        $this->view
            ->set('dateBegin',date('Y-m-01'))
            ->set('dateEnd',date('Y-m-t'))
            ->set('roleList',html::select($roles,'curRole',0,'class="form-control inlineBlock" onchange="filterTR();"'))
            ->set('initList',html::select($users,'curInit',0,'class="form-control inlineBlock" onchange="filterTR();"'))
            ->set('respList',html::select($users,'curResp',0,'class="form-control inlineBlock" onchange="filterTR();"'))
            ->set('stList',html::select($sts,'curStatus',0,'class="form-control inlineBlock" onchange="filterTR();"'))
            ->out('main',$this->className);
    }

    public function actionGetList(){
        $list = mTaskReport::getModels($_POST);
        if(!empty($list)){
            foreach ($list as $item){
                $this->view
                    ->add_dict($item)
                    ->out('center',$this->className);
            }
        }
        else
            $this->view
                ->out('empty',$this->className);

    }

    public function actionExcel(){
        $obj = new mTaskReport();
        $obj->getExcel($_GET);
    }
}