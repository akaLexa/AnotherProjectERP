<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 22.12.2016
 * справочник типов задач
 **/
namespace build\erp\adm;
use build\erp\adm\m\mTaskTypes;
use build\erp\inc\eController;

class hbTaskTypes extends eController
{
    protected $postField = array(
        'hbTname' => ['type' => self::STR,'maxLength'=>250],
    );

    protected $getField = array(
        'id' => ['type' => self::INT],
    );

    public function actionIndex()
    {
        $this->view->out('main', $this->className);
    }

    public function actionGetList(){
        $list = mTaskTypes::getModels();
        if(!empty($list)){
            foreach ($list as $item) {
                $this->view
                    ->add_dict($item)
                    ->out('center',$this->className);
            }
        }
    }

    public function actionAdd(){
        if(empty($_POST)){
            $this->view->out('addForm',$this->className);
        }
        else if(!empty($_POST['hbTname'])){
            mTaskTypes::Add($_POST['hbTname']);
        }
    }

    public function actionEdit(){
        if(empty($_GET['id']))
            return;
        $obj = mTaskTypes::getCurModel($_GET['id']);

        if(empty($_POST)){
            $this->view
                ->add_dict($obj)
                ->out('editForm',$this->className);
        }
        else if(!empty($_POST['hbTname'])){
            $obj->edit($_POST['hbTname']);
        }
    }

    public function actionDel(){
        if(!empty($_GET['id'])){
            $obj = mTaskTypes::getCurModel($_GET['id']);
            $obj->delete();
        }
    }

}