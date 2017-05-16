<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 17.04.2017
 *
 **/
namespace build\erp\reports;
use build\erp\inc\eController;
use build\erp\inc\Project;
use build\erp\inc\User;
use build\erp\reports\m\mProjectStageReport;
use mwce\Tools\html;

class ProjectStageReport extends eController
{

    protected $postField = array(
        'prName' => ['type'=>self::STR],
        'prNum' => ['type'=>self::INT],
        'curManager' => ['type'=>self::INT],
        'curStage' => ['type'=>self::INT],
        'curResp' => ['type'=>self::INT],
        'dBegin' => ['type'=>self::DATE],
        'dEndPlan' => ['type'=>self::DATE],
        'dEndFact' => ['type'=>self::DATE],
    );

    protected $getField = array(
        'prName' => ['type'=>self::STR],
        'prNum' => ['type'=>self::INT],
        'curManager' => ['type'=>self::INT],
        'curStage' => ['type'=>self::INT],
        'curResp' => ['type'=>self::INT],
        'dBegin' => ['type'=>self::DATE],
        'dEndPlan' => ['type'=>self::DATE],
        'dEndFact' => ['type'=>self::DATE],
    );

    public function actionIndex()
    {
        $stages = Project::getStagesList();
        $stages[0] = '...';
        $users = User::getUserList();
        $users[0] = '...';
        $this->view
            ->set('dateBegin',date('Y-m-01'))
            ->set('dateEnd',date('Y-m-t'))
            ->set('stList',html::select($stages,'curStage',0,'class="form-control inlineBlock" onchange="filterPSR();"'))
            ->set('mList',html::select($users,'curManager',0,'class="form-control inlineBlock" onchange="filterPSR();"'))
            ->set('respList',html::select($users,'curResp',0,'class="form-control inlineBlock" onchange="filterPSR();"'))
            ->out('main',$this->className);
    }

    public function actionGetList(){

        $list = mProjectStageReport::getModels($_POST);
        if(!empty($list)){
            foreach ($list as $item){
                $this->view
                    ->add_dict($item)
                    ->out('center',$this->className);
            }
        }
        else
            $this->view->out('empty',$this->className);
    }

    public function actionExcel(){
        $obj = new mProjectStageReport();
        $obj->getExcel($_GET);
    }

}