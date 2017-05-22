<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 05.11.2016
 * модуль управления группами и ролями
 **/
namespace build\erp\adm;

use build\erp\adm\m\mMenuManager;
use build\erp\adm\m\mModules;
use build\erp\adm\m\mPlugin;
use build\erp\adm\m\mUser;
use build\erp\adm\m\mUserGroup;
use build\erp\adm\m\mUserRole;
use build\erp\inc\eController;
use build\erp\inc\Project;
use build\erp\inc\User;
use mwce\Tools\Configs;
use mwce\Tools\DicBuilder;
use mwce\Exceptions\ModException;
use mwce\Tools\html;
use build\erp\adm\m\mConfigurator;
use mwce\Tools\Tools;

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
        'titleList' => ['type'=>self::STR],
        'pagesList' => ['type'=>self::STR],
        'adrCnt' => ['type'=>self::STR],
        'linkadr' => ['type'=>self::STR],
        'pluginDesc' => ['type'=>self::STR],
        'AddressList' => ['type'=>self::STR],
        'newName' => ['type'=>self::STR,'maxLength'=>254],
        'pluginName' => ['type'=>self::STR,'maxLength'=>254],
        'seq' => ['type'=>self::INT],
        'mSeq' => ['type'=>self::INT],
        'isMVC' => ['type'=>self::INT],
        'cachSec' => ['type'=>self::INT],
        'stateList' => ['type'=>self::INT],

        'Usurname' => ['type'=>self::STR],
        'addUsurname' => ['type'=>self::STR],
        'addUlastname' => ['type'=>self::STR],
        'addUname' => ['type'=>self::STR],
        'addUlogin' => ['type'=>self::STR],
        'addUpwd' => ['type'=>self::STR],
        'uGroupList' => ['type'=>self::INT],
        'uRoleList' => ['type'=>self::INT],
        'uBlock' => ['type'=>self::INT],
        'uAddRoleList' => ['type'=>self::INT],
        'uBlockList' => ['type'=>self::INT],

        'dlang' => ['type'=>self::STR],
        'theme' => ['type'=>self::STR],
        'defpage' => ['type'=>self::STR],
        'defController' => ['type'=>self::STR],
        'defgrp' => ['type'=>self::INT],
        'defConNum' => ['type'=>self::INT],
        'defLogConNum' => ['type'=>self::INT],

        'cfgName' => ['type'=>self::STR],
        'cfgLegendName' => ['type'=>self::STR],
        'cfgDesc' => ['type'=>self::STR],

        'cfg_name_1' => ['type'=>self::STR],
        'cfg_legend_1' => ['type'=>self::STR],
        'cfg_type_1' => ['type'=>self::INT],
        'cfg_desc_1' => ['type'=>self::STR],


        'privatePhone' => ['type'=>self::STR],
        'workPhone' => ['type'=>self::STR],
        'privateMail' => ['type'=>self::STR],
        'workMail' => ['type'=>self::STR],
    );

    protected $getField = array(
        'id' => ['type' => self::INT],
        'cfgName' => ['type' => self::STR],
        'pName' => ['type' => self::STR],
    );

    private $state = array(
        0=>'Выключен',
        1=>'Включен',
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

                $ai = new \ArrayIterator($_POST);
                $roles = [];
                foreach ($ai as $pid=>$item) {
                    if(stripos($pid,'role_')!== false){
                        $roles[] = (int)$item;
                    }
                }

                $newGroup = mUserGroup::Add($_POST['GroupNameText']);
                if(!empty($roles)){
                    $newGroup->addRoles($roles);
                }
                echo json_encode(['success'=>1]);
            }
            catch (\Exception $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }
        }
    }

    public function actionGetGroupList(){
        $curGeoups = mUserGroup::getModels();
        $users = User::getUserList();
        $users[0] = 'Нет';
        if(!empty($curGeoups)){
            $ai = new \ArrayIterator($curGeoups);
            foreach ($ai as $item) {
                if(!empty($item['col_projectID']))
                    $this->view->set('actions',"<a href='".$this->view->getAdr()."page/inProject.html?id=".$item['col_projectID']."' target='_blank'>Перейти к проекту</a>");
                else
                    $this->view->set('actions',"<a href='#' onclick='addSpecialProject(".$item['col_gID'].");return false;'>Добавить проект</a>");

                $this->view
                    ->add_dict($item)
                    ->set('controlList',html::select($users,'cList_'.$item['col_gID'],!empty($item['col_founder'])?$item['col_founder']:0,'class="form-control inlineBlock" style="width:140px;" onchange="setGroupFounder('.$item['col_gID'].',this.value)"'))
                    ->out('groupCenter',$this->className);
            }
        }
    }

    /**
     * создать проект для отдела
     */
    public function actionSetSpecProject(){
        if(!empty($_GET['id'])){
            $obj = mUserGroup::getCurModel($_GET['id']);
            if(!empty($obj)){
                $newProject = Project::Add($obj['col_gName'], !empty($obj['col_founder']) ? $obj['col_founder'] : Configs::userID());
                $newProject->setField('col_gID',$obj['col_gID']);
            }
        }
    }

    public function  actionSetFounder(){
        if(!empty($_GET['id']) && isset($_POST['id'])){
            $obj = mUserGroup::getCurModel($_GET['id']);

            if(!empty($obj)){
                if(!empty($_POST['id']))
                    $obj->setField('col_founder',$_POST['id']);
                else
                    $obj->setField('col_founder','NULL');
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

                if($_GET['id'] != 2 && $_GET['id'] != 4) // у гостей и у группы "Все" не может быть ролей
                {
                    $list = mUserRole::getEditModels($_GET['id']);

                    if(!empty($list)){ //отображение доступных ролей для привязки
                        $ai = new \ArrayIterator($list);
                        if(!empty($info['col_roleList']))
                            $roles = explode(',',$info['col_roleList']);
                        else
                            $roles = [];

                        foreach ($ai as $item) {
                            if(in_array($item['col_roleID'],$roles))
                                $this->view->set('isChecked', 'checked');
                            else
                                $this->view->set('isChecked', '');

                            $this->view
                                ->add_dict($item)
                                ->out('roleList',$this->className);
                        }

                        $this->view->setFContainer('roleTblContent',true);
                    }
                }


                $this->view
                    ->add_dict($info)
                    ->out('EditGroupForm',$this->className);
            }
            else{
                try{
                    $ai = new \ArrayIterator($_POST);
                    $roles = [];
                    foreach ($ai as $pid=>$item) {
                        if(stripos($pid,'role_')!== false){
                            $roles[] = (int)$item;
                        }
                    }
                    if(empty($roles))
                        $roles = null;

                    $info->edit($_POST['GroupNameText'],$roles);

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

        $list = mUserRole::getModels(['notInGroup'=>true]);

        if(empty($list)){ //отображение доступных ролей для привязки
            $list[] = array('col_roleName'=>'','col_roleID'=>'');
        }

        $this->view
            ->loops('roleTblContent',$list,'AddGroupForm',$this->className)
            ->out('AddGroupForm',$this->className);
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
            if($_POST['id']<=2){
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
        $list = mModules::getAddressList();

        $this->view
            ->set('adrList',html::select($list,'AddressList',current($list),'class="form-control" style="width:200px; display:inline-block;" onchange="moduleFiltr();"'))
            ->out('ModulesForm',$this->className);
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

            $groups = mUserGroup::getModels();

            if (!empty($groups)) {
                foreach ($groups as $group) {
                    $this->view
                        ->add_dict($group)
                        ->out('uGroupList', $this->className);
                }
                $this->view->setFContainer('GroupsList', true);
            }

            $titles = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'titles.php');
            $titles['0'] = '...';

            $this->view
                ->set('titleList',html::select($titles,'titleList','0','class="form-control form-inline-element" style="width:200px;"'))
                ->out('AddModuleForm', $this->className);
        }
        else {
            $params = array();

            if(!empty($_POST['newTitle'])){
                $lng = new DicBuilder(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'titles.php');
                $params['titleList'] = $lng->add2Dic($_POST['newTitle'],'auto_title');
            }
            else{
                if(empty($_POST['titleList']))
                    return;
                else
                    $params['titleList'] = $_POST['titleList'];
            }

            if(empty($_POST['adrCnt']))
                return;
            else
                $params['adrCnt'] = $_POST['adrCnt'];

            $t = explode('/',$params['adrCnt']);
            $params['module'] = end($t);
            $params['isMVC'] = empty($_POST['isMVC']) ? 0 : 1;
            $params['cachSec'] = empty($_POST['cachSec']) ? 0 : $_POST['cachSec'];

            $pi = new \ArrayIterator($_POST);

            $roles = array();
            $groups = array();

            foreach ($pi as $id=>$item) {
                if(stripos($id,'role_') !== FALSE){
                    $roles[] = (int)$item;
                }

                if(stripos($id,'group_') !== FALSE){
                    $groups[] = (int)$item;
                }
            }

            try{

                $newModule = mModules::Add($params);
                $newModule->addGroupToModule($groups);
                $newModule->addRolesToModule($roles);

                echo json_encode(['success'=>1]);
            }
            catch (\Exception $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }

        }
    }

    public function actionEditModule(){
        if(!empty($_GET['id'])){
            $info = mModules::getCurModel($_GET['id']);

            if(empty($_POST))
            {
                $roles = mUserRole::getRoleList();

                $rls = explode(',',$info['col_roles']);//isCheck

                if (!empty($roles)) {
                    foreach ($roles as $id => $role) {
                        if(in_array($id,$rls)){
                            $this->view->set('isCheck','checked');
                        }
                        else
                            $this->view->set('isCheck','');

                        $this->view
                            ->set(['roleId' => $id, 'roleName' => $role])
                            ->out('uRolesList', $this->className);
                    }
                    $this->view->setFContainer('roleList', true);
                }

                $groups = mUserGroup::getModels();

                if (!empty($groups)) {

                    if(!empty($info['col_groups']))
                        $grps = explode(',',$info['col_groups']);
                    else
                        $grps = array();

                    foreach ($groups as $group) {
                        if(!in_array($group['col_gID'],$grps)){
                            $group['isCheck'] = '';
                        }
                        else{
                            $group['isCheck'] = 'checked';
                        }
                        $this->view
                            ->add_dict($group)
                            ->out('uGroupList', $this->className);
                    }
                    $this->view->setFContainer('GroupsList', true);
                }

                $titles = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'titles.php');
                $titles['0'] = '...';

                if((int)$info['col_isClass'] == 1)
                    $info['isChecked'] = "checked";
                else
                    $info['isChecked'] = "";

                $this->view
                    ->add_dict($info)
                    ->set('titleList',html::select($titles,'titleList',$info['col_title'],'class="form-control form-inline-element" style="width:200px;"'))
                    ->out('editModule', $this->className);
            }
            else{
                $params = array();

                if(!empty($_POST['newTitle'])){
                    $lng = new DicBuilder(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'titles.php');
                    $params['titleList'] = $lng->add2Dic($_POST['newTitle'],'auto_title');
                }
                else{
                    if(empty($_POST['titleList']))
                        return;
                    else
                        $params['titleList'] = $_POST['titleList'];
                }

                if(empty($_POST['adrCnt']))
                    return;
                else
                    $params['adrCnt'] = $_POST['adrCnt'];

                $t = explode('/',$params['adrCnt']);
                $params['module'] = end($t);

                $params['isMVC'] = empty($_POST['isMVC']) ? 0 : 1;
                $params['cachSec'] = empty($_POST['cachSec']) ? 0 : $_POST['cachSec'];

                $pi = new \ArrayIterator($_POST);
                $roles = array();
                $groups = array();

                foreach ($pi as $id=>$item) {
                    if(stripos($id,'role_') !== FALSE){
                        $roles[] = (int)$item;
                    }
                    if(stripos($id,'group_') !== FALSE){
                        $groups[] = (int)$item;
                    }
                }

                try{
                    $info->edit($params);
                    $info->addRolesToModule($roles);
                    $info->addGroupToModule($groups);

                    echo json_encode(['success'=>1]);
                }
                catch (\Exception $e){
                    echo json_encode(['error'=>$e->getMessage()]);
                }
            }
        }
    }

    public function actionDelModule()
    {
        if(!empty($_GET['id'])){
            $info = mModules::getCurModel($_GET['id']);
            $info->delete();
        }
    }

    public function actionGetModuleList(){
        $params = array();
        if(!empty($_POST['AddressList'])){
            $params['adr'] = $_POST['AddressList'];
        }
        $list = mModules::getModels($params);
        if(!empty($list)){
            $lang = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'titles.php');
            $ai = new \ArrayIterator($list);
            foreach ($ai as $item) {

                if(!empty($lang[$item['col_title']])){
                    $item['col_title'] = "<h5>{$lang[$item['col_title']]} <small style='color:red'>{$item['col_title']}</small></h5> ";
                }

                $this->view
                    ->add_dict($item)
                    ->out('moduleCenter',$this->className);
            }
        }
    }

    public function actionClearModuleCache(){
        mModules::RefreshCache();
    }
    //endregion

    //region вкладка "меню"
    public function actionGetMenu(){

        $_POST['menuList'] = 1;
        self::actionGetMenuList();
        $this->view
            ->setFContainer('menu_Body',true)
            ->set('mList',html::select(mMenuManager::getMenuList(),'menuList',0,'class="form-control" style="display:inline-block;width:200px;" onchange="mfilter();"'))
            ->out('MenuForm',$this->className);
    }

    public function actionGetMenuList(){
        $params = [];

        $params['menuId'] = !empty($_POST['menuList']) ? $_POST['menuList'] : 0;
        $list = mMenuManager::getModels($params);

        if(!empty($list)){

            $lang = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::buildCfg('dlang').DIRECTORY_SEPARATOR.'titles.php');
            foreach ($list as $item) {
                if(!empty($lang[$item['mtitle']])){
                    $item['mtitle'] = $lang[$item['mtitle']];
                }

                $this->view
                    ->add_dict($item)
                    ->out('menucenter',$this->className);
            }
        }
    }

    public function actionAddNewMenu(){
        if(!empty($_POST['newName'])){
            mMenuManager::addMenu($_POST['newName']);
        }
    }

    public function actionAddMenu(){
        if(!empty($_POST['newName'])){
            mMenuManager::addMenu($_POST['newName']);
        }
    }

    public function actionEditInMenu(){

        if(!empty($_GET["id"]))
        {
            $tmenu = $_GET["id"];

            $info  = mMenuManager::getCurentPos($tmenu);

            if(empty($info))
                return;

            if(empty($_POST)){
                $lpath = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::buildCfg('dlang').DIRECTORY_SEPARATOR.'titles.php';
                $lang = DicBuilder::getLang($lpath);
                asort($lang);

                $this->view
                    ->set("titlest",html::select($lang,"mtitel",$info['col_mtitle'],"class=\"form-control\" style='display:inline-block;width:200px;'"))
                    ->set("modullist",html::select($info->pageList(),"pagesList",$info['col_modul'],"onchange='getlink();' class=\"form-control\" style='display:inline-block;width:200px;'"))
                    ->add_dict($info)
                    ->out("menueditPos",$this->className);
            }
            else{

                if(empty($_POST["mtitel"]) || !empty($_POST['newtitle'])){
                    if(!empty($_POST['newtitle'])) {
                        $db = new DicBuilder(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::buildCfg('dlang').DIRECTORY_SEPARATOR.'titles.php');
                        $mtytle = $db->add2Dic($_POST['newtitle'],'auto_title');
                    }
                    elseif(empty($_POST["mtitel"])){
                        return;
                    }
                }
                else
                    $mtytle = $_POST["mtitel"];

                if(empty($_POST["linkadr"]))
                    $link = '';
                else
                    $link = $_POST["linkadr"];

                if(empty($_POST["pagesList"]))
                    $modul = '';
                else
                    $modul = $_POST["pagesList"];

                $info->editCurrentPos($mtytle,$link,$modul,empty($_POST['seq'])? 0 : $_POST['seq']);
            }
        }

    }

    public function actionAddInMenu(){

        if(!empty($_GET["id"]))
        {
            $tmenu = $_GET["id"];

            $info  = mMenuManager::getCurModel($tmenu);

            if(empty($info))
                return;

            if(empty($_POST)){
                $lpath = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::buildCfg('dlang').DIRECTORY_SEPARATOR.'titles.php';
                $lang = DicBuilder::getLang($lpath);
                asort($lang);
                $mlist = $info->pageList();
                asort($mlist);

                $this->view
                    ->set("titlest",html::select($lang,"mtitel",1,"class=\"form-control\" style='display:inline-block;width:200px;'"))
                    ->set("modullist",html::select($mlist,"pagesList",-1,"onchange='getlink();' class=\"form-control\" style='display:inline-block;width:200px;'"))
                    ->add_dict($info)
                    ->out("menuaddPos",$this->className);
            }
            else{

                if(empty($_POST["mtitel"]) || !empty($_POST['newtitle'])){
                    if(!empty($_POST['newtitle'])) {
                        $db = new DicBuilder(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::buildCfg('dlang').DIRECTORY_SEPARATOR.'titles.php');
                        $mtytle = $db->add2Dic($_POST['newtitle'],'auto_title');
                    }
                    elseif(empty($_POST["mtitel"])){
                        return;
                    }
                }
                else
                    $mtytle = $_POST["mtitel"];

                if(empty($_POST["linkadr"]))
                    $link = '';
                else
                    $link = $_POST["linkadr"];

                if(empty($_POST["pagesList"]))
                    $modul = '';
                else
                    $modul = $_POST["pagesList"];

                mMenuManager::addToMenu($mtytle,$tmenu,$link,$modul,empty($_POST['seq'])? 0 : $_POST['seq']);
            }
        }
    }

    public function actionDelMenu()
    {
        if(!empty($_GET['id'])){
            mMenuManager::delMenu($_GET['id']);
        }
    }

    public function actionDelPosMenu()
    {
        if(!empty($_GET['id'])){
            mMenuManager::delPosMenu($_GET['id']);
        }
    }

    public function actionClearMenuCache(){
        mMenuManager::RefreshCache();
    }

    /**
     * параметры доступа
     */
    public function actionGetMenuAccess(){
        if(!empty($_POST['menuType'])){
            $menu = new mMenuManager();
            $menuAcs = '';

            if(!empty($_GET['upd'])) {
                $ai = new \ArrayIterator($_POST);
                foreach ($ai as $id => $item) {
                    if (strpos($id, 'acs_') !== FALSE) {
                        if(!empty($menuAcs))
                            $menuAcs.=',';

                        $menuAcs.= substr($id, 4);
                    }
                }
                $old = Configs::readCfg('plugin_mainMenu',Configs::currentBuild());
                $old[$_POST['menuType']] = $menuAcs;
                Configs::writeCfg($old,'plugin_mainMenu',Configs::currentBuild());
            }
            else{
                $list = $menu->getAccessList($_POST['menuType']);
                $listF = [];

                if(!empty($list)){
                    foreach ($list['roles'] as $id=>$roleName) {
                        $tm = !empty($list['access'][$id]) ? ' checked ' : '';

                        $listF[]= array(
                            'rName' => $roleName,
                            'idA' => $id,
                            'ischecked' =>$tm,
                        );
                    }
                }

                $this->view
                    ->loops('accessForsm',$listF,'menuaForm',$this->className)
                    ->out('menuaForm',$this->className);
            }
        }
    }

    /**
     * узнать/сменить очережность меню
     */
    public function actionMenuSeq(){
        if(!empty($_GET['id'])){
            if(!isset($_POST['mSeq'])){
                $seq = mMenuManager::KnowMenuSequence($_GET['id']);
                echo json_encode(['seq'=>$seq]);
            }
            else{
                mMenuManager::SetMenuSequence($_GET['id'],$_POST['mSeq']);
            }

        }
    }

    //endregion

    //region "плагины"
    public function actionGetPlugins(){
        self::actionGetPluginList();
        $this->view
            ->setFContainer('pluginsBodyTable',true)
            ->set('unregisteredList', html::select(mPlugin::getNonRegPlugins(),'unregPl',0,'style="display:inline-block;width:250px;" class="form-control"'))
            ->out('PluginsForm',$this->className);
    }

    public function actionPluginAdd(){
        if(!empty($_POST['pluginName'])){
            try{
                mPlugin::Add($_POST['pluginName']);
                echo json_encode(['success'=>1]);
            }
            catch (ModException $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }
        }
    }
    
    public function actionGetPluginList(){
        $list = mPlugin::getModels();
        if(!empty($list)){

            $pluginsLegend = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'plugins.php');

            foreach ($list as $item) {

                if(!empty($pluginsLegend[$item['col_pluginName']])){
                    $item['langDesc'] = $pluginsLegend[$item['col_pluginName']];
                }
                else
                    $item['langDesc'] = '';

                $this->view
                    ->add_dict($item)
                    ->out('PluginsCenter',$this->className);
            }
        }
    }

    public function actionEditPlugin(){
        if(!empty($_GET['id'])){
            $plugin = mPlugin::getCurModel($_GET['id']);

            if(empty($_POST)){

                if($plugin['col_isClass'] == 1){
                    $plugin['isChecked'] = 'checked';
                }
                else{
                    $plugin['isChecked'] = '';
                }

                $roles = mUserRole::getRoleList();

                $ch_roles = explode(',',$plugin['col_roles']);
                $ch_groups = explode(',',$plugin['col_groups']);

                if (!empty($roles)) {
                    foreach ($roles as $id => $role) {
                        if(in_array($id,$ch_roles)){
                            $this->view->set('isCheck','checked');
                        }
                        else{
                            $this->view->set('isCheck','');
                        }

                        $this->view
                            ->set(['roleId' => $id, 'roleName' => $role])
                            ->out('uRolesList', $this->className);
                    }
                    $this->view->setFContainer('rolesList', true);
                }

                $groups = mUserGroup::getModels();

                if (!empty($groups)) {
                    foreach ($groups as $group) {

                        if(in_array($group['col_gID'],$ch_groups)){
                            $this->view->set('isCheck','checked');
                        }
                        else{
                            $this->view->set('isCheck','');
                        }

                        $this->view
                            ->add_dict($group)
                            ->out('uGroupList', $this->className);
                    }
                    $this->view->setFContainer('groupList', true);
                }

                $pluginsLegend = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'plugins.php');

                if(!empty($pluginsLegend[$plugin['col_pluginName']])){
                    $plugin['pluginDesc'] = $pluginsLegend[$plugin['col_pluginName']];
                }
                else{
                    $plugin['pluginDesc'] = '';
                }

                $cfg = Configs::readCfg('plugin_'.$plugin['col_pluginName'],Configs::currentBuild());
                if(!empty($cfg) && !empty($cfg['allowedUsrs'])){
                    $plugin['pluginCustomUsrs'] = $cfg['allowedUsrs'];
                }

                $this->view
                    ->add_dict($plugin)
                    ->set('stateList',html::select($this->state,'stateList',$plugin['col_pluginState'],' style="width:200px; display:inline-block;" class="form-control"'))
                    ->out('editPlugins',$this->className);
            }
            else{

                try{
                    $pi = new \ArrayIterator($_POST);

                    $roles = array();
                    $groups = array();

                    foreach ($pi as $id=>$item) {
                        if(stripos($id,'role_') !== FALSE){
                            $roles[] = (int)$item;
                        }
                        if(stripos($id,'group_') !== FALSE){
                            $groups[] = (int)$item;
                        }
                    }

                    if(empty($_POST['pluginName'])){
                        return;
                    }
                    else
                        $params['pluginName'] = $_POST['pluginName'];

                    $params['isClass'] = !empty($_POST['isClass']) ? 1 : 0;
                    $params['pluginCache'] = $_POST['pluginCache'];
                    $params['pluginSeq'] = $_POST['pluginSeq'];
                    $params['pluginState'] = $_POST['stateList'];

                    if(!empty($_POST['pluginDesc'])){
                        $db = new DicBuilder(baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'plugins.php');
                        $db->add2Dic($_POST['pluginDesc'],$params['pluginName'],true);
                    }

                    if(!empty(trim($_POST['pluginCustomUsrs']))){
                        $cfg = Configs::readCfg('plugin_'.$params['pluginName'],Configs::currentBuild());
                        $cfg['allowedUsrs'] = $_POST['pluginCustomUsrs'];
                        Configs::writeCfg($cfg,'plugin_'.$params['pluginName'],Configs::currentBuild());
                    }

                    $plugin->edit($params);
                    $plugin->addRoles($roles);
                    $plugin->addGroup($groups);

                    echo json_encode(['success'=>1]);
                }
                catch (ModException $e){
                    echo json_encode(['error'=>$e->getMessage()]);
                }
            }
        }
    }

    public function actionDelPlugin(){
        if(!empty($_GET['id'])){
            try{
                $plugin = mPlugin::getCurModel($_GET['id']);
                $plugin->delete();
                echo json_encode(['success'=>1]);
            }
            catch (\Exception $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }
        }
    }

    public function actionClearPluginCache(){
        mPlugin::RefreshCache();
    }
    //endregion

    //region пользователи

    public function actionGetUser(){
        $group = User::getGropList();
        $group[0] = '...';

        $role = User::getRoleList();
        $role[0] = '...';

        $_POST['uBlock'] =0;
        self::actionGetUserList();

        $this->view
            ->setFContainer('UserFormContent',true)
            ->set('groupList',html::select($group,'uGroupList',0,'style="width:100%;display:inline-block;" class="form-control"'))
            ->set('roleList',html::select($role,'uRoleList',0,'style="width:100%;display:inline-block;" class="form-control"'))
            ->out('UserForm',$this->className);
    }

    public function actionGetUserList(){

        $params = array();
        if(!empty($_POST['Usurname'])){
            $params['col_Sername'] = $_POST['Usurname'];
        }

        if(!empty($_POST['uGroupList'])){
            $params['col_gID'] = $_POST['uGroupList'];
        }

        if(!empty($_POST['uRoleList'])){
            $params['col_roleID'] = $_POST['uRoleList'];
        }

        if(isset($_POST['uBlock'])){
            $params['col_isBaned'] = $_POST['uBlock'];
        }

        $list = User::getModels($params);
        if(!empty($list)){
            foreach ($list as $item){
                $this->view
                    ->add_dict($item)
                    ->out('UserFormCenter',$this->className);
            }
        }
    }

    public function actionAddUser(){
        if(!empty($_POST)){
            try{
                $params = array();

                if(empty($_POST['addUsurname'])){
                    echo json_encode(['error'=>'Не указана фамилия']);
                    return;
                }
                else{
                    $params['surname'] = $_POST['addUsurname'];
                }

                if(empty($_POST['addUname'])){
                    echo json_encode(['error'=>'Не указано имя']);
                    return;
                }
                else{
                    $params['name'] = $_POST['addUname'];
                }

                if(empty($_POST['addUlogin'])){
                    echo json_encode(['error'=>'Не указан логин']);
                    return;
                }
                else{
                    $params['login'] = $_POST['addUlogin'];
                }

                if(empty($_POST['addUpwd'])){
                    echo json_encode(['error'=>'Не указан пароль']);
                    return;
                }
                else{
                    $params['pwd'] = $_POST['addUpwd'];
                }

                if(!empty($_POST['addUlastname'])){
                    $params['lastname'] = $_POST['addUlastname'];
                }
                else
                    $params['lastname'] = ' ';

                $params['role'] = $_POST['uAddRoleList'];
                $params['block'] = $_POST['uBlockList'];

                mUser::AddUser($params);
                echo json_encode(['success'=>1]);
            }
            catch (\Exception $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }
        }
        else{
            $role = User::getRoleList();

            $this->view
                ->set('roleList',html::select($role,'uAddRoleList',0,'style="width:250px;display:inline-block;" class="form-control"'))
                ->out('AddUser',$this->className);
        }
    }

    public function actionEditUser(){
        if(empty($_GET['id']))
            return;
        $info = mUser::getCurModel($_GET['id']);
        if(empty($info))
            return;

        if(!empty($_POST)){
            try{
                $params = array();

                if(empty($_POST['addUsurname'])){
                    echo json_encode(['error'=>'Не указана фамилия']);
                    return;
                }
                else{
                    $params['surname'] = $_POST['addUsurname'];
                }

                if(empty($_POST['addUname'])){
                    echo json_encode(['error'=>'Не указано имя']);
                    return;
                }
                else{
                    $params['name'] = $_POST['addUname'];
                }

                if(empty($_POST['addUlogin'])){
                    echo json_encode(['error'=>'Не указан логин']);
                    return;
                }
                else{
                    $params['login'] = $_POST['addUlogin'];
                }

                if(!empty($_POST['addUpwd'])){
                    $params['pwd'] = $_POST['addUpwd'];
                }

                if(!empty($_POST['addUlastname'])){
                    $params['lastname'] = $_POST['addUlastname'];
                }
                else
                    $params['lastname'] = ' ';

                $params['role'] = $_POST['uAddRoleList'];
                $params['block'] = $_POST['uBlockList'];

                $params['privatePhone'] = !empty($_POST['privatePhone']) ? "'{$_POST['privatePhone']}'" : 'NULL';
                $params['workPhone'] = !empty($_POST['workPhone']) ? "'{$_POST['workPhone']}'" : 'NULL';
                $params['privateMail'] = !empty($_POST['privateMail']) ? "'{$_POST['privateMail']}'" : 'NULL';
                $params['workMail'] = !empty($_POST['workMail']) ? "'{$_POST['workMail']}'" : 'NULL';

                $info->editUser($params);

                echo json_encode(['success'=>1]);
            }
            catch (\Exception $e){
                echo json_encode(['error'=>$e->getMessage()]);
            }
        }
        else{
            $role = User::getRoleList();

            $this->view
                ->set('roleList',html::select($role,'uAddRoleList',0,'style="width:250px;display:inline-block;" class="form-control"'))
                ->set('blockList',html::select([0=>'Нет',1=>'Да'],'uBlockList',$info['col_isBaned'],'style="width:250px;display:inline-block;" class="form-control"'))
                ->add_dict($info)
                ->out('EditUser',$this->className);
        }
    }
    //endregion

    //region Настройки
    public function actionGetConfigurator(){
        self::actionGetCfgList();

        $this->view
            ->setFContainer('bodyCfg',true)
            ->out('Configurator',$this->className);
    }

    /**
     * общий список конфигов
     */
    public function actionGetCfgList(){
        $list = mConfigurator::getModels();
        if(empty($list)){
            $this->view->out('emptyConfig',$this->className);
        }
        else{
            foreach ($list as $item){
                $this->view
                    ->add_dict($item)
                    ->out('centerConfig',$this->className);
            }
        }
    }

    /**
     * показать форму, для набива менюх
     */
    public function actionGetFormCfg(){
        if(!empty($_POST) && !empty($_POST['cfgName'])){
            echo json_encode(mConfigurator::addNewCfg($_POST['cfgName'],(!empty($_POST['cfgLegendName']) ? $_POST['cfgLegendName'] : null),(!empty($_POST['cfgDesc']) ? $_POST['cfgDesc'] : null)));
        }
        else{
            $this->view
                ->out('main','configAdder');
        }
    }

    /**
     * удалить конфиг
     */
    public function actionDelCfg(){
        if(!empty($_POST['cfgName'])){
            echo json_encode(mConfigurator::deleteCfg($_POST['cfgName']));
        }
    }

    /**
     * структура выборанного коифига. главная форма
     */
    public function actionCfgStruct(){
        if(!empty($_GET['cfgName'])){
            $cfg = mConfigurator::getCurModel($_GET['cfgName']);
            if(!empty($cfg)){
                $types = mConfigurator::$avaliableCfgTypeList;

                self::actionCfgStructList();

                $this->view
                    ->setFContainer('cfgStrctContent',true)
                    ->set('typeList',html::select($types,'cfg_type_1',4,['class'=>'form-control inlineBlock']))
                    ->out('structMain','configAdder');
            }
            else
                echo json_encode(['error'=>'Такого файла конфигурации нет']);
        }
    }

    /**
     * структура текущего конфига
     */
    public function actionCfgStructList(){
        if(!empty($_GET['cfgName'])){
            $cfg = mConfigurator::getCurModel($_GET['cfgName']);
            $params = $cfg->getParams();

            if(empty($params))
                $this->view->out('structEmpty','configAdder');
            else{
                foreach ($params as $item){
                    $key = array_keys($item)[0];

                    unset($item[$key]['typeData']);

                    $this->view
                        ->set($item[$key])
                        ->set('vlName',$key)
                        ->set('cfgName',$_GET['cfgName'])
                        ->out('structCenter','configAdder');
                }
            }
        }
    }

    /**
     * добавление структуры в конфиг
     */
    public function actionAddCfgStructParams(){
        if(!empty($_GET['cfgName']) && !empty($_POST['cfg_name_1'])&& !empty($_POST['cfg_type_1'])){
            $cfg = mConfigurator::getCurModel($_GET['cfgName']);
            if(empty($cfg)){
                echo json_encode(['error'=>'Не существующий конфиг']);
            }
            else{
                $cfg->addNewParameter([
                    'name' => $_POST['cfg_name_1'],
                    'legend' => (!empty($_POST['cfg_legend_1']) ? $_POST['cfg_legend_1'] : null),
                    'type' => $_POST['cfg_type_1'],
                    'desc' => (!empty($_POST['cfg_desc_1']) ? $_POST['cfg_desc_1'] : null),
                    'value' => 1
                ]);
                echo json_encode(['state'=>1]);
            }
        }
    }

    /**
     * удалить параметр из конфига
     */
    public function actionDelCfgStruct(){
        if(!empty($_GET['cfgName']) && !empty($_GET['pName'])){
            $cfg = mConfigurator::getCurModel($_GET['cfgName']);
            if(empty($cfg)){
                echo json_encode(['error'=>'Не существующий конфиг']);
            }
            else{
                $cfg->deleteParam($_GET['pName']);
                echo json_encode(['state'=>1]);
            }
        }
    }

    /**
     * форма редактирования/редактирование конфига
     */
    public function actionEditCfg(){
        if(!empty($_GET['cfgName'])){
            $cfg = mConfigurator::getCurModel($_GET['cfgName']);
            if(empty($cfg)){
                echo json_encode(['error'=>'Не существующий конфиг']);
            }
            else{
                if(empty($_POST)){
                    $params = $cfg->getParams();

                    if(empty($params))
                        $this->view->out('structEmpty','configAdder');
                    else{
                        $cfgContent = [];
                        $i = 0;
                        foreach ($params as $item){

                            $key = array_keys($item)[0];

                            $cfgContent[$i]= $item[$key];
                            $cfgContent[$i]['pID'] = $key;

                            switch ($cfgContent[$i]['typeNum']){
                                case 1:
                                case 2:
                                case 5:
                                case 6:
                                case 7:
                                case 8:
                                    $cfgContent[$i]['element'] = html::select($cfgContent[$i]['typeData'],$key,$cfgContent[$i]['value'],'class="form-control inlineBlock" style="width:300px;"');
                                    break;
                                case 11:
                                case 22:
                                case 55:
                                case 66:
                                case 77:
                                case 88:
                                    if(!empty($cfgContent[$i]['typeData'])){
                                        $selected = explode(',',$cfgContent[$i]['value']);
                                        $checked = [];
                                        $j=0;
                                        foreach ($cfgContent[$i]['typeData'] as $id_ => $vals){

                                            if(in_array($vals['id'],$selected)){
                                                $checked[$j]['isChecked'] = true;
                                            }

                                            if(!empty($vals['item'])){
                                                $checked[$j]['legend'] = $vals['item'];
                                            }

                                            $checked[$j]['value'] = $vals['id'];

                                            $checked[$j]['params'] = [
                                                'id' =>$key.'_'.$vals['id'],
                                                'name' =>$key.'_'.$vals['id'],
                                            ];
                                            $checked[$j]['span'] =[
                                                'style' => 'display:inline-block; width:150px;',
                                            ];

                                            $checked[$j]['label'] =[
                                                'style' => 'display:block; width:auto;font-weight:normal;',
                                            ];
                                            $j++;
                                        }

                                        $cfgContent[$i]['element'] = '<div style="height:120px; width:auto; overflow-y:auto;">'.html::checkBoxGroup($checked).'</div>';
                                    }
                                    break;

                                case 3:
                                    $cfgContent[$i]['element'] = html::select([0=>'Нет',1=>'Да'],$key,$cfgContent[$i]['value'],'class="form-control inlineBlock" style="width:300px;"');
                                    break;
                                case 4:
                                    $cfgContent[$i]['element'] = "<input type='text' name='$key' id ='$key' value='{$cfgContent[$i]['value']}' class='form-control inlineBlock' style='width:300px;'";
                                    break;
                            }

                            $i++;
                        }

                        if(!empty($cfgContent)){
                            $this->view
                                ->loops('cfgContent',$cfgContent,'cfgEditForm','configAdder')
                                ->out('cfgEditForm','configAdder');
                        }
                    }
                }
                else{
                    $params = [];
                    foreach ($_POST as $pID => $pVal){
                        $params[self::paramsControl($pID,self::STR)] = self::paramsControl($pVal,self::STR);
                    }

                    if(!empty($params)){
                        $cfg->setParams($params);
                    }
                }
            }
        }
    }
    //endregion
}
//TODO: разбить на классы