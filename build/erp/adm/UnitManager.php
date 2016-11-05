<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 05.11.2016
 * модуль управления группами и ролями
 **/
namespace build\erp\adm;

use build\erp\adm\m\mUserGroup;
use build\erp\inc\eController;
use mwce\Tools;

class UnitManager extends eController
{
    protected $postField = array(
        'id' => ['type'=>self::INT],
        'GroupNameText' => ['type'=>self::STR,'maxLength'=>250],
    );

    protected $getField = array(
        'id' => ['type' => self::INT],
    );

    public function actionIndex()
    {
        $this->view->out('main',$this->className);
    }

    //region вкладка "Группы"

    /**
     * общий список группы / добавление группы
     */
    public function actionGetGroup(){
        if(empty($_POST)){
            $curGeoups = mUserGroup::getModels();

            if(empty($curGeoups))
                $curGeoups = mUserGroup::getEmptyList();

            $this->view
                ->loops('groupTableBody',$curGeoups,'GroupForm',$this->className)
                ->out('GroupForm',$this->className);
        }
        else{
            try{
                mUserGroup::Add($_POST['GroupNameText']);
            }
            catch (\Exception $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }

            echo json_encode(['success'=>1]);
        }
    }

    /**
     * едактирование группы
     */
    public function actionEditGroup(){
        if(!empty($_GET['id'])){
            $info = mUserGroup::getCurModel($_GET['id']);

            if(empty($info))
                return;

            if(empty($_POST)){
                $this->view
                    ->add_dict($info)
                    ->out('EditGroupForm',$this->className);
            }
            else{
                try{
                    $info->edit($_POST['GroupNameText']);
                    echo json_encode(['success'=>1]);
                }
                catch (\Exception $e){
                    echo json_encode(['error'=>$e->getMessage()]);
                }
            }
        }
    }

    /**
     * форма добавления
     */
    public function actionAddGroup(){
        $this->view->out('AddGroupForm',$this->className);
    }

    /**
     * Удаление группы
     */
    public function actionDelGroup(){
        if(!empty($_POST['id'])){
            if($_POST['id']<=4){
                echo json_encode(['error'=>'Удалить основные группы нельзя!']);
            }
            else{

                try{
                    $obj = mUserGroup::getCurModel($_POST['id']);
                    $obj->DelGroup();
                    echo json_encode(['success'=>1]);
                }
                catch (\Exception $e){
                    echo json_encode(['error'=>$e->getMessage()]);
                }


            }
        }
    }

    //endregion
}