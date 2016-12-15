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
use mwce\Configs;
use mwce\content;
use mwce\DicBuilder;
use mwce\html_;
use mwce\Tools;


class ProjectManager extends eController
{
    protected $getField = array(
        'id' => ['type'=>self::INT],
    );

    protected $postField = array(
        'stageName' => ['type'=>self::STR,'maxLength'=>200],

        // настройки проекта
        'startStageID' => ['type'=>self::INT],
        'endStagesID' => ['type'=>self::STR],
    );

    /**
     * соответствие каждой настройки элементу
     * @var array
     */
    protected $types = array(
        'startStageID' =>['select','stages'],
        'endStagesID' =>['checkGroup','stagesGroup'],
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
                        $cval = html_::checkGroup('endStagesID',$tm_ar," style='height:120px; width:auto; overflow-y:auto;' ");
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
                        $curID = $c[0];
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
                        $cfg[$curID] = $pValue;
                        break;
                    case 'checkGroup':
                        if(!empty($cfg[$curID]))
                            $cfg[$curID].=','.$pValue;
                        else
                            $cfg[$curID] = $pValue;
                        break;
                    default:
                        $cfg[$curID] = $pValue;
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
}