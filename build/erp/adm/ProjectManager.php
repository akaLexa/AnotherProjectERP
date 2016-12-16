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
use build\erp\inc\Project;
use build\erp\inc\User;
use build\erp\project\m\m_inProject;
use build\erp\project\m\m_TabsCfgs;
use mwce\Configs;
use mwce\DicBuilder;
use mwce\html_;
use mwce\Tools;


class ProjectManager extends eController
{
    protected $getField = array(
        'id' => ['type'=>self::INT],
        'tab' => ['type'=>self::STR],
    );

    protected $postField = array(
        'stageName' => ['type'=>self::STR,'maxLength'=>200],

        // настройки проекта
        'startStageID' => ['type'=>self::INT],
        'countDefStartDays' => ['type'=>self::INT],
        'endStagesID' => ['type'=>self::STR],

        //настройки вкладок проекта
        'TabChosen' => ['type'=>self::STR],
        'name' => ['type'=>self::STR],
        'title' => ['type'=>self::STR],
        'icon' => ['type'=>self::STR],
        'isActive' => ['type'=>self::INT],
        'num' => ['type'=>self::INT],
        'state' => ['type'=>self::INT],
    );

    /**
     * соответствие каждой настройки элементу
     * @var array
     */
    protected $types = array(
        'startStageID' =>['select','stages'],
        'endStagesID' =>['checkGroup','stagesGroup'],
        'countDefStartDays' =>['text','text'],
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

    //region настройки проекта

    public function actionGetProjectCfg(){

        $stages = Project::getStagesList();
        $stagesGroup = array();
        foreach ($stages as $num=>$name){
            $stagesGroup[] = array($name,$num);
        }

        $lang = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.curLang.DIRECTORY_SEPARATOR.'cfg_project.php');
        $cfg = Configs::readCfg('project',tbuild);

        foreach ($cfg as $cname=>$cval){

            if(!empty ($this->types[$cname])){
                switch ($this->types[$cname][0]){
                    case 'select':
                        $cval = html_::select(${$this->types[$cname][1]},$cname,$cval,'style="display:inline-block; width:300px;" class="form-control"');
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
                        $cval = html_::checkGroup($cname,$tm_ar," style='height:120px; width:auto; overflow-y:auto;' ");
                        break;
                    default:
                        $cval = html_::input('text',$cname,$cval,'style="display:inline-block; width:300px;" class="form-control"');
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
                if(empty ($this->types[$pId])){
                    $c = explode('_',$pId);
                    if(!empty ($this->types[$c[0]])){
                        $curID = $this->paramsControl($pValue,$c[0]);
                    }
                    else{
                        continue;
                    }
                }
                else{
                    $curID = $pId;
                }

                switch ($this->types[$curID][0]){
                    case 'select':
                        $cfg[$curID] = $this->paramsControl($pValue,self::STR);
                        break;
                    case 'checkGroup':
                        if(!empty($cfg[$curID]))
                            $cfg[$curID].=','.$this->paramsControl($pValue,self::STR);
                        else
                            $cfg[$curID] = $this->paramsControl($pValue,self::STR);
                        break;
                    default:
                        $cfg[$curID] = $this->paramsControl($pValue,self::STR);
                        break;
                }
            }

            //чтобы не потерять существующую конфигурацию в случае, если она не заполнена
            $oldCfg = Configs::readCfg('project',tbuild);
            foreach ($oldCfg as $id=>$item) {
                if(empty($cfg[$id])){
                    $cfg[$id] = '';
                }
            }

            Configs::writeCfg($cfg,'project',tbuild);
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
            ->set('tabsList',html_::select($tabs,'TabChosen',0,'class="form-control inlineBlock" onchange="showTabsCfg(this.value)"'))
            ->out('TabsManagementMain',$this->className);
    }

    public function actionTabCfg(){

        if(!empty($_POST['TabChosen'])){

            $boolVars = array(0=>'Нет',1=>'Да');

            $groups = array();
            $groups_ = User::getGropList();

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
                                    $item['value'] = html_::select(${$this->configTypes[$pName][1]},$pName,$item['value'],' class="form-control inlineBlock"');
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
                                    $item['value'] = html_::checkGroup($pName,$tmVal," style='height:120px; width:auto; overflow-y:auto;' ");
                                    break;
                                default:
                                    $item['value'] = html_::input('text',$pName,$item['value'],'class="form-control inlineBlock"');
                                    break;
                            }
                        }
                        else
                            $item['value'] = html_::input('text',$pName,$item['value'],'class="form-control inlineBlock"');


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
}