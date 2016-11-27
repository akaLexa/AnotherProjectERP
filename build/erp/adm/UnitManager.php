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
use build\erp\adm\m\mUserGroup;
use build\erp\adm\m\mUserRole;
use build\erp\inc\eController;
use mwce\Configs;
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
        'titleList' => ['type'=>self::STR],
        'pagesList' => ['type'=>self::STR],
        'adrCnt' => ['type'=>self::STR],
        'linkadr' => ['type'=>self::STR],
        'pluginDesc' => ['type'=>self::STR],
        'newName' => ['type'=>self::STR,'maxLength'=>254],
        'pluginName' => ['type'=>self::STR,'maxLength'=>254],
        'seq' => ['type'=>self::INT],
        'mSeq' => ['type'=>self::INT],
        'isMVC' => ['type'=>self::INT],
        'cachSec' => ['type'=>self::INT],
        'stateList' => ['type'=>self::INT],
    );

    protected $getField = array(
        'id' => ['type' => self::INT],
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

                if($_GET['id'] != 2 && $_GET['id'] != 4) // у гостей и у группы "Все" не может быть ролей
                {
                    $list = mUserRole::getModels();

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

            $groups = mUserGroup::getModels();

            if (!empty($groups)) {
                foreach ($groups as $group) {
                    $this->view
                        ->add_dict($group)
                        ->out('uGroupList', $this->className);
                }
                $this->view->setFContainer('GroupsList', true);
            }

            $titles = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['mwclang'].DIRECTORY_SEPARATOR.'titles.php');
            $titles['0'] = '...';

            $this->view
                ->set('titleList',html_::select($titles,'titleList','0','class="form-control form-inline-element" style="width:200px;"'))
                ->out('AddModuleForm', $this->className);
        }
        else {
            $params = array();

            if(!empty($_POST['newTitle'])){
                $lng = new DicBuilder(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['mwclang'].DIRECTORY_SEPARATOR.'titles.php');
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

                $titles = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['mwclang'].DIRECTORY_SEPARATOR.'titles.php');
                $titles['0'] = '...';

                if((int)$info['col_isClass'] == 1)
                    $info['isChecked'] = "checked";
                else
                    $info['isChecked'] = "";

                $this->view
                    ->add_dict($info)
                    ->set('titleList',html_::select($titles,'titleList',$info['col_title'],'class="form-control form-inline-element" style="width:200px;"'))
                    ->out('editModule', $this->className);
            }
            else{
                $params = array();

                if(!empty($_POST['newTitle'])){
                    $lng = new DicBuilder(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['mwclang'].DIRECTORY_SEPARATOR.'titles.php');
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
        $list = mModules::getModels();
        if(!empty($list)){
            $lang = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.'erp'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.$_SESSION['mwclang'].DIRECTORY_SEPARATOR.'titles.php');
            $ai = new \ArrayIterator($list);
            foreach ($ai as $item) {

                if(!empty($lang[$item['col_title']])){
                    $item['col_title'] = "<h5>{$lang[$item['col_title']]} <small>{$item['col_title']}</small></h5> ";
                }

                $this->view
                    ->add_dict($item)
                    ->out('moduleCenter',$this->className);
            }
        }
    }
    //endregion

    //region вкладка "меню"
    public function actionGetMenu(){

        $this->view
            ->set('mList',html_::select(mMenuManager::getMenuList(),'menuList',0,'class="form-control" style="display:inline-block;width:200px;" onchange="mfilter();"'))
            ->out('MenuForm',$this->className);
    }
    //endregion

    //region "плагины"
    public function actionGetPlugins(){
        self::actionGetPluginList();
        $this->view
            ->setFContainer('pluginsBodyTable',true)
            ->set('unregisteredList', html_::select(mPlugin::getNonRegPlugins(),'unregPl',0,'style="display:inline-block;width:250px;" class="form-control"'))
            ->out('PluginsForm',$this->className);
    }

    public function actionPluginAdd(){
        if(!empty($_POST['pluginName'])){
            mPlugin::Add($_POST['pluginName']);
        }
    }
    
    public function actionGetPluginList(){
        $list = mPlugin::getModels();
        if(!empty($list)){

            $pluginsLegend = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION['mwclang'].DIRECTORY_SEPARATOR.'plugins.php');

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

                $pluginsLegend = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION['mwclang'].DIRECTORY_SEPARATOR.'plugins.php');

                if(!empty($pluginsLegend[$plugin['col_pluginName']])){
                    $plugin['pluginDesc'] = $pluginsLegend[$plugin['col_pluginName']];
                }
                else{
                    $plugin['pluginDesc'] = '';
                }

                $cfg = Configs::readCfg('plugin_'.$plugin['col_pluginName'],tbuild);
                if(!empty($cfg) && !empty($cfg['allowedUsrs'])){
                    $plugin['pluginCustomUsrs'] = $cfg['allowedUsrs'];
                }

                $this->view
                    ->add_dict($plugin)
                    ->set('stateList',html_::select($this->state,'stateList',$plugin['col_pluginState'],' style="width:200px; display:inline-block;" class="form-control"'))
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
                        echo '-> go';
                        $db = new DicBuilder(baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.$_SESSION['mwclang'].DIRECTORY_SEPARATOR.'plugins.php');
                        Tools::debug($db->add2Dic($_POST['pluginDesc'],$params['pluginName'],true));
                    }

                    if(!empty($_POST['pluginCustomUsrs'])){
                        $cfg = Configs::readCfg('plugin_'.$params['pluginName'],tbuild);
                        $cfg['allowedUsrs'] = $_POST['pluginCustomUsrs'];
                        Configs::writeCfg($cfg,'plugin_'.$params['pluginName'],tbuild);
                    }

                    $plugin->edit($params);
                    $plugin->addRoles($roles);
                    $plugin->addGroup($groups);

                    echo json_encode(['success'=>1]);
                }
                catch (\Exception $e){
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
    //endregion
}