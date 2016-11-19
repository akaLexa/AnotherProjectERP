<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 05.11.2016
 * модуль управления группами и ролями
 **/
namespace build\erp\adm;

use build\erp\adm\m\mMenuManager;
use build\erp\adm\m\mUserGroup;
use build\erp\adm\m\mUserRole;
use build\erp\inc\eController;
use mwce\DicBuilder;
use mwce\html_;
use mwce\Tools;

class UnitManager extends eController
{
    protected $postField = array(
        'id' => ['type'=>self::INT],
        'GroupNameText' => ['type'=>self::STR,'maxLength'=>250],
        'roleName' => ['type'=>self::STR,'maxLength'=>250],
        'menuList' => ['type'=>self::INT],
        'menuType' => ['type'=>self::STR],
        'mtitel' => ['type'=>self::STR],
        'newtitle' => ['type'=>self::STR],
        'pagesList' => ['type'=>self::STR],
        'linkadr' => ['type'=>self::STR],
        'newName' => ['type'=>self::STR,'maxLength'=>254],
        'seq' => ['type'=>self::INT],
        'mSeq' => ['type'=>self::INT],
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
            self::actionGetGroupList();

            $this->view
                ->setFContainer('groupTableBody',true)
                ->out('GroupForm',$this->className);
        }
        else{
            try{
                mUserGroup::Add($_POST['GroupNameText']);
                echo json_encode(['success'=>1]);
            }
            catch (\Exception $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }
        }
    }

    public function actionGetGroupList(){
        $curGeoups = mUserGroup::getModels();
        if(!empty($curGeoups)){
            $ai = new \ArrayIterator($curGeoups);
            foreach ($ai as $item) {
                $this->view
                    ->add_dict($item)
                    ->out('groupCenter',$this->className);
            }
        }
    }

    /**
     * редактирование группы
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

        $list = mUserRole::getModels();
        if(!empty($list)){
            $this->view->loops('roleTblContent',$list,'AddGroupForm',$this->className);
        }

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

    //region вкладка "Роли"

    public function actionGetRole(){
        self::actionGetRoleList();
        $this->view
            ->setFContainer('roleTblContent',true)
            ->out('RoleIndex',$this->className);
    }

    public function actionGetRoleList(){
        $list = mUserRole::getModels();
        if(!empty($list)){
            $ai = new \ArrayIterator($list);
            foreach ($ai as $item){
                $this->view
                    ->add_dict($item)
                    ->out('roleCenter',$this->className);
            }
        }
    }

    public function actionAddRole(){
        if(empty($_POST)){
            $this->view->out('AddRoleForm',$this->className);
        }
        else if(!empty($_POST['roleName'])){
            try{
                mUserRole::AddRole($_POST['roleName']);
                echo json_encode(['success'=>1]);
            }
            catch (\Exception $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }
        }
    }

    public function actionEditRole(){
        if(!empty($_GET['id'])){
            $info = mUserRole::getCurModel($_GET['id']);

            if(empty($info))
                return;

            if(empty($_POST)){
                $this->view
                    ->add_dict($info)
                    ->out('EditRoleForm',$this->className);
            }
            else{
                try{
                    $info->edit($_POST['roleName']);
                    echo json_encode(['success'=>1]);
                }
                catch (\Exception $e){
                    echo json_encode(['error'=>$e->getMessage()]);
                }
            }
        }
    }

    public function actionDelRole(){
        if(!empty($_POST['id'])){
            if($_POST['id']<=1){
                echo json_encode(['error'=>'Удалить основные роли нельзя!']);
            }
            else{

                try{
                    $obj = mUserRole::getCurModel($_POST['id']);
                    $obj->delete();
                    echo json_encode(['success'=>1]);
                }
                catch (\Exception $e){
                    echo json_encode(['error'=>$e->getMessage()]);
                }


            }
        }
    }

    //endregion


    //region вкладка "модули"
    public function actionGetModules(){
        $this->view->out('ModulesForm',$this->className);
    }

    public function actionAddModule(){
        if (empty($_POST)) {
            $roles = mUserRole::getRoleList();

            if (!empty($roles)) {
                foreach ($roles as $id => $role) {
                    $this->view
                        ->set(['roleId' => $id, 'roleName' => $role])
                        ->out('uRolesList', $this->className);
                }
                $this->view->setFContainer('roleList', true);
            }

            $titles = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['mwclang'].DIRECTORY_SEPARATOR.'titles.php');
            $titles[0] = '...';
            $this->view
                ->set('titleList',html_::select($titles,'titleList',0,'class="form-control form-inline-element" style="width:200px;"'))
                ->out('AddModuleForm', $this->className);
        } else {

        }
    }
    //endregion

    //region вкладка "меню"
    public function actionGetMenu(){

        $this->view
            ->set('mList',html_::select(mMenuManager::getMenuList(),'menuList',0,'class="form-control" style="display:inline-block;width:200px;" onchange="mfilter();"'))
            ->out('MenuForm',$this->className);
    }
    //edregion
}