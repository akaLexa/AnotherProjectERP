<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 11.12.2016
 * управление проектами
 **/
namespace build\erp\adm;
use build\erp\adm\m\mStages;
use build\erp\inc\eController;

class ProjectManager extends eController
{
    protected $getField = array(
        'id' => ['type'=>self::INT],
    );

    protected $postField = array(
        'stageName' => ['type'=>self::STR,'maxLength'=>200],
    );

    public function actionIndex()
    {
        $this->view
            ->out('main',$this->className);
    }

    //region стадии проекта
    public function actionGetStageForm(){
        self::actionGetStageList();
        $this->view
            ->setFContainer('prStageBody',true)
            ->out('projectStageForm',$this->className);
    }

    public function actionGetStageList(){
        $list = mStages::getModels([]);
        if(!empty($list)){
            foreach ($list as $item){
                $this->view
                    ->add_dict($item)
                    ->out('projectStageCenter',$this->className);
            }
        }
    }

    public function actionStageAdd(){
        if(empty($_POST)){
            $this->view->out('projectStageEditForm',$this->className);
        }
        elseif(!empty($_POST['stageName'])){
            mStages::Add($_POST['stageName']);
        }
    }

    public function actionStageEdit(){
        if(!empty($_GET['id'])){
            $stage = mStages::getCurModel($_GET['id']);
            if(!empty($stage)){
                if(empty($_POST)){
                    $this->view
                        ->add_dict($stage)
                        ->out('projectStageEditForm',$this->className);
                }
                elseif (!empty($_POST['stageName'])){
                    $stage->edit($_POST['stageName']);
                }
            }
        }
    }

    public function actionDeleteSage(){
        if(!empty($_GET['id'])){
            $stage = mStages::getCurModel($_GET['id']);
            $stage->delete();
        }
    }
    //endregion
}