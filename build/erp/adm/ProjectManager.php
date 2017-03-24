<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 11.12.2016
 * управление проектами
 **/
namespace build\erp\adm;
use build\erp\adm\m\mDocumentGroups;
use build\erp\adm\m\mStages;
use build\erp\adm\m\mUserRole;
use build\erp\inc\eController;
use build\erp\inc\Project;
use build\erp\inc\User;
use build\erp\project\m\m_inProject;
use build\erp\project\m\m_TabsCfgs;
use mwce\Tools\Configs;
use mwce\Tools\DicBuilder;
use mwce\Exceptions\ModException;
use mwce\Tools\html;


class ProjectManager extends eController
{
    protected $getField = array(
        'id' => ['type'=>self::INT],
        'tab' => ['type'=>self::STR],
        'curGrp' => ['type'=>self::STR],
    );

    protected $postField = array(
        'stageName' => ['type'=>self::STR,'maxLength'=>200],

        // настройки проекта
        'startStageID' => ['type'=>self::INT],
        'countDefStartDays' => ['type'=>self::INT],
        'documentsFolder' => ['type'=>self::STR],
        'endStagesID' => ['type'=>self::STR],
        'activeStagesID' => ['type'=>self::STR],

        //настройки вкладок проекта
        'TabChosen' => ['type'=>self::STR],
        'name' => ['type'=>self::STR],
        'title' => ['type'=>self::STR],
        'icon' => ['type'=>self::STR],
        'isActive' => ['type'=>self::INT],
        'num' => ['type'=>self::INT],
        'state' => ['type'=>self::INT],

        'dgName' => ['type'=>self::STR],
    );

    /**
     * соответствие каждой настройки элементу
     * @var array
     */
    protected $types = array(
        'startStageID' =>['select','stages'],
        'endStagesID' =>['checkGroup','stagesGroup'],
        'activeStagesID' =>['checkGroup','stagesGroup'],
        'countDefStartDays' =>['text','text'],
        'documentsFolder' =>['text','text'],
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

    /**
     * редактирование доступов
     */
    public function actionStageAccessEdit(){
        if(!empty($_GET['id'])){
            $stage = mStages::getCurModel($_GET['id']);

            if(empty($_GET['curGrp'])){
                $grps = User::getGropList();
                $grps[0] = '...';
                $this->view
                    ->set('groupList',html::select($grps,'curGrp',0,'class="form-control inlineBlock" style="width:280px;" onchange="editStageAccessRole('.$_GET['id'].',this.value)"'))
                    ->out('projectStageEditAccessForm',$this->className);
            }
            else if(!empty($_GET['curGrp'])){
                $checked = array();

                if(!empty($_POST)){
                    $ai = new \ArrayIterator($_POST);

                    foreach ($ai as $pId=>$pVal){
                        if(stripos(trim($pId),'role_') !== false){
                            $checked[] = (int)$pVal;
                        }
                        else
                            echo $pId;
                    }
                }

                $stage->checkRoleAccess($_GET['curGrp'],$checked);
            }
        }
    }

    /**
     * пользователи к выбранной группе на стадии
     */
    public function actionGetStageRespUsers(){
        if(!empty($_GET['id']) && !empty($_GET['curGrp'])){
            $stage = mStages::getCurModel($_GET['id']);
            $stage->checkGroupAccess($_GET['curGrp']);

            $checkedRoles = $stage->getAccessedUsers($_GET['curGrp']);
            $roles = User::getRoleList($_GET['curGrp']);

            if(!empty($roles)){
                foreach ($roles as $rid=>$rval){
                    if(in_array($rid,$checkedRoles)){
                        $this->view->set('checked','checked');
                    }
                    else{
                        $this->view->set('checked','');
                    }

                    $this->view
                        ->set(['groupName'=>$rval,'roleID'=>$rid])
                        ->out('roleList',$this->className);
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

    //region настройки проекта

    public function actionGetProjectCfg(){

        $stages = Project::getStagesList();
        $stagesGroup = array();
        foreach ($stages as $num=>$name){
            $stagesGroup[] = array($name,$num);
        }

        $lang = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.curLang.DIRECTORY_SEPARATOR.'cfg_project.php');
        $cfg = Configs::readCfg('project',Configs::currentBuild());

        foreach ($cfg as $cname=>$cval){

            if(!empty ($this->types[$cname])){
                switch ($this->types[$cname][0]){
                    case 'select':
                        $cval = html::select(${$this->types[$cname][1]},$cname,$cval,'style="display:inline-block; width:300px;" class="form-control"');
                        break;
                    case 'checkGroup':
                        $curStages = explode(',',$cval);
                        $tm_ar = ${$this->types[$cname][1]};
                        $ai = new \ArrayIterator($tm_ar);

                        foreach ($ai as $id=>$item) {
                            if(in_array($item[1],$curStages))
                                $tm_ar[$id][2] = true;
                            else
                                $tm_ar[$id][2] = false;
                        }
                        $cval = html::checkGroup($cname,$tm_ar," style='height:120px; width:auto; overflow-y:auto;' ");
                        break;
                    default:
                        $cval = html::input('text',$cname,$cval,'style="display:inline-block; width:300px;" class="form-control"');
                        break;
                }
            }
            else{
                $cval = html_::input('text',$cname,$cval,'style="display:inline-block; width:300px;" class="form-control"');
            }

            $this->view
                ->add_dict([
                    'cfgName' => $cname,
                    'cfgLegend' => !empty($lang[$cname]) ? $lang[$cname] : '',
                    'cfgElement' => $cval,
                ])->out('ProjectCfgCenter',$this->className);
        }

        $this->view
            ->setFContainer('configProjectBody',true)
            ->out('ProjectCfgForm',$this->className);
    }

    public function actionSaveProjecrCfg(){
        if(!empty($_POST)){
            $cfg = [];

            $pa = new \ArrayIterator($_POST);
            foreach ($pa as $pId => $pValue) {
                $c = explode('_',$pId);

                if(empty ($this->types[$c[0]])){

                    if(isset($this->types[$c[0]])){
                        $curID = $this->paramsControl($pValue,$this->types[$c[0]][1]);
                    }
                    else{
                        continue;
                    }
                }
                else{
                    $curID = $c[0];
                }

                if(!empty($this->types[$curID]) && !empty($this->types[$curID][0])) {
                    switch ($this->types[$curID][0]) {
                        case 'select':
                            $cfg[$curID] = $this->paramsControl($pValue, self::STR);
                            break;
                        case 'checkGroup':
                            if (!empty($cfg[$curID])) {
                                $cfg[$curID] .= ',' . $this->paramsControl($pValue, self::STR);
                            } else {
                                $cfg[$curID] = $this->paramsControl($pValue, self::STR);
                            }

                            break;
                        default:
                            $cfg[$curID] = $this->paramsControl($pValue, self::STR);
                            break;
                    }
                }
            }

            //чтобы не потерять существующую конфигурацию в случае, если она не заполнена
            $oldCfg = Configs::readCfg('project',Configs::currentBuild());
            foreach ($oldCfg as $id=>$item) {
                if(empty($cfg[$id])){
                    $cfg[$id] = '';
                }
            }

            Configs::writeCfg($cfg,'project',Configs::currentBuild());
        }
    }

    //endregion

    //region Видимость вкладок в проекте

    protected $configTypes = array(
        'name' => ['text','text'],
        'title' => ['text','text'],
        'icon' =>['text','text'],
        'groupAccessR' => ['checkGroup','groups'],
        'userAccessR' => ['checkGroup','roles'],
        'groupAccessRW' => ['checkGroup','groups'],
        'userAccessRW' => ['checkGroup','roles'],
        'isActive' => ['select','boolVars'],
        'num' => ['text','text'],
        'state'=>['select','boolVars'],
    );

    public function actionTabsManagement(){
        $tabs = m_inProject::getAllTabs();

        $this->view
            ->set('tabsList',html::select($tabs,'TabChosen',0,'class="form-control inlineBlock" onchange="showTabsCfg(this.value)"'))
            ->out('TabsManagementMain',$this->className);
    }

    public function actionTabCfg(){

        if(!empty($_POST['TabChosen'])){

            $boolVars = array(0=>'Нет',1=>'Да');

            $groups = array();
            $groups_ = User::getGropList(false);

            foreach ($groups_ as $num=>$group){
                $groups[] = array($group,$num);
            }

            $roles = array();
            $roles_ = User::getRoleList();
            foreach ($roles_ as $num=>$role){
                $roles[] = array($role,$num);
            }


            $curTab = m_TabsCfgs::getCurModel($_POST['TabChosen']);
            if(!empty($curTab)){

                if(!empty($curTab)){
                    foreach ($curTab as $pName => $item) {
                        if(!empty($this->configTypes[$pName]))
                        {
                            switch ($this->configTypes[$pName][0]){
                                case 'select':
                                    $item['value'] = html::select(${$this->configTypes[$pName][1]},$pName,$item['value'],' class="form-control inlineBlock"');
                                    break;
                                case 'checkGroup':
                                    $curVal = explode(',',$item['value']);
                                    $tmVal = ${$this->configTypes[$pName][1]};
                                    $ai = new \ArrayIterator($tmVal);
                                    foreach ($ai as $id=>$item_) {
                                        if(in_array($item_[1],$curVal)){
                                            $tmVal[$id][2] = true;
                                        }
                                        else{
                                            $tmVal[$id][2] = false;
                                        }

                                    }

                                    $item['value'] = html::checkGroup($pName,$tmVal," style='height:120px; width:auto; overflow-y:auto;' ");
                                    break;
                                default:
                                    $item['value'] = html::input('text',$pName,$item['value'],'class="form-control inlineBlock"');
                                    break;
                            }
                        }
                        else
                            $item['value'] = html::input('text',$pName,$item['value'],'class="form-control inlineBlock"');


                        $this->view
                            ->add_dict($item)
                            ->out('TabsManagementCenter',$this->className);
                    }
                }

            }
        }
    }

    public function actionSaveTabCfg(){
        if(!empty($_POST) && !empty($_GET['tab'])){
            $curTab = m_TabsCfgs::getCurModel($_GET['tab']);
            if(empty($curTab))
                return;

            $cfg = [];

            $pa = new \ArrayIterator($_POST);
            foreach ($pa as $pId => $pValue) {
                if (empty ($this->configTypes[$pId])) {
                    $c = explode('_', $pId);
                    if (!empty ($this->configTypes[$c[0]])) {
                        $curID = $c[0];
                    } else {
                        continue;
                    }
                } else {
                    $curID = $pId;
                }

                switch ($this->configTypes[$curID][0]) {
                    case 'select':
                        $cfg[$curID] = $this->paramsControl($pValue,self::STR);
                        break;
                    case 'checkGroup':
                        if (!empty($cfg[$curID])) {
                            $cfg[$curID] .= ',' . $this->paramsControl($pValue,self::STR);
                        } else {
                            $cfg[$curID] = $this->paramsControl($pValue,self::STR);
                        }
                        break;
                    default:
                        $cfg[$curID] = $this->paramsControl($pValue,self::STR);
                        break;
                }
            }

            $curTab->save($cfg);

        }
    }
    //endregion

    //region документы и доступ к ним
    public function actionProjectDocuments(){
        if(empty($_POST)){
            self::actionGetDocGroups();
            $this->view
                ->setFContainer('docGroupLists',true)
                ->out('ProjectDocsMain',$this->className);
        }
    }

    public function actionGetDocGroups(){
        $list = mDocumentGroups::getModels();
        if(!empty($list)){
            $ai = new \ArrayIterator($list);
            foreach ($ai as $item){
                $this->view
                    ->add_dict($item)
                    ->out('ProjectDocsCenter',$this->className);
            }
        }
    }

    public function actionDelDocGroup(){
        if(!empty($_GET['id'])){
            $group = mDocumentGroups::getCurModel($_GET['id']);
            $group->delete();
        }
    }

    public function actionAddDocGroup(){
        if(!empty($_POST['dgName'])){
            mDocumentGroups::Add($_POST['dgName']);
        }
        else{
            $this->view
                ->out('ProjectDocsAdd',$this->className);
        }
    }

    public function actionEditDocGroup(){
        if(!empty($_GET['id'])){
            $curGroup = mDocumentGroups::getCurModel($_GET['id']);
            if(empty($curGroup))
                throw new ModException('Группа документов не существует!');

            if(empty($_POST)){
                $roles = mUserRole::getRoleList();
                if(!empty($roles)){
                    $accesses = mDocumentGroups::getRolesAccess();
                    $list = array(0=>'Нет доступа','Только чтение','Полный доступ');

                    $r1 = array();
                    foreach ($roles as $id=>$roleName){
                        $r1[] = array(
                            'roleName' => $roleName,
                            'roleId' => $id,
                            'accsList' => html_::select(
                                $list,
                                'roleAccss_'.$id,
                                (!empty($accesses[$id]) && $accesses[$id][0] == $_GET['id']) ? $accesses[$id][1] : 0,
                                'class="form-control inlineBlock" style="width:130px;"'
                            ),
                        );
                    }

                    $this->view
                        ->loops('groupsAccesses',$r1,'ProjectDocsEdit',$this->className)
                        ->add_dict($curGroup)
                        ->out('ProjectDocsEdit',$this->className);
                }
            }
            else{
                if(!empty($_POST['dgName'])){
                    if($curGroup['col_docGroupName'] != $_POST['dgName']){
                        $curGroup->edit($_POST['dgName']);
                    }

                    $acs = array();
                    $ai = new \ArrayIterator($_POST);
                    foreach ($ai as $pId=>$pVal){
                        if(stristr($pId,'roleAccss_') != false){
                            $id_ = explode("_",$pId);
                            $acs[(int)$id_[1]] = (int)$pVal;
                        }
                    }

                    if(!empty($acs)){
                        $curGroup->editAccess($acs);
                    }
                }
            }
        }
    }
    //endregion
}