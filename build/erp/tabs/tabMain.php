<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.12.2016
 *
 **/
namespace build\erp\tabs;
use build\erp\adm\m\mStages;
use build\erp\inc\AprojectTabs;
use build\erp\inc\eController;
use build\erp\inc\User;
use build\erp\tabs\m\mProjectPlan;
use build\erp\tabs\m\mTabMain;
use mwce\Tools\Configs;
use mwce\Tools\content;
use mwce\Tools\html;


class tabMain extends AprojectTabs
{
    /**
     * @var mTabMain
     * переопределил под текущий контент
     */
    protected $project;

    protected $postField = array(
        'projectNane' => ['type'=>self::STR,'maxLength'=>200],
        'projectDesc' => ['type'=>self::STR],
        'stageComment' => ['type'=>self::STR],
        'stageFailDesc' => ['type'=>self::STR],
        'curManager' => ['type'=>self::INT],
        'planState' => ['type'=>self::INT],
        'choosedGroup' => ['type'=>self::INT],
        'chosedStage' => ['type'=>self::INT],
        'chosedStageResp' => ['type'=>self::INT],
        'descState' => ['type'=>self::STR],
        'descStage' => ['type'=>self::STR],
        'stageDateTo' => ['type'=>self::DATE],
    );

    protected $getField = array(
        'type' => ['type'=>self::INT],
        'group' => ['type'=>self::INT],
        'desc' => ['type'=>self::STR],
    );

    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params=null)
    {
        if(empty($this->project)){
            $this->view
                ->set(['errTitle'=>'Ошибка','msg_desc'=>'Данные по выбранному проекту не найдены!'])
                ->out('error');
        }
        else{
            $users = User::getUserList();

            if(self::WriteAccess()){
                $this->view->emptyName('customVizStyle');
            }
            else{
                $this->view->set('customVizStyle','display:none');
            }

            if($this->project['col_statusID'] == 4 && $this->project['col_respID'] == Configs::userID()){
                $this->view->set('chooseStageStyle','');
            }
            else{
                $this->view->set('chooseStageStyle','display:none;');
            }

            $this->view
                ->add_dict($this->project)
                ->set('mngrList',html::select($users,'curManager',$this->project['col_founderID'],'class="form-control inlineBlock"'))
                ->out('main',$this->className);
        }
    }

    /**
     * принятие решения по пришедшей стадии
     */
    public function stageAction(){
        if(!empty($_GET['type'])){
            if($this->project['col_statusID'] == 4 && $this->project['col_respID'] == Configs::userID()){
                switch ($_GET['type']){
                    case 1:
                        $this->project->stageAgree();
                        echo json_encode(['success'=>1]);
                        break;
                    case 2:
                        if(!empty($_GET['desc'])){
                            $this->project->stageDisagree($_GET['desc']);
                            echo json_encode(['success'=>1]);
                        }
                        else{
                            echo json_encode(['error'=>'Не указана причина отказа']);
                            exit;
                        }
                        break;
                    default:
                        echo json_encode(['error'=>'Ошибка данных!']);
                        exit;
                        break;
                }
            }
            else
                echo json_encode(['error'=>'Только ответственный за стадию может принимать решение!']);
        }
    }

    public function save(){

        if(!self::WriteAccess()){
            echo json_encode(['error'=>'У Вас нет доступа для записи!']);
        }
        else{
            if(empty($_POST['curManager']))
                return;

            if(empty($this->project)){
                echo json_encode(['error'=>'Запрашиваемый проект не найден!']);
            }
            else{
                $params = array();
                $params['col_projectName'] = !empty($_POST['projectNane']) ? "'{$_POST['projectNane']}'" : 'NULL';
                $params['col_Desc'] = !empty($_POST['projectDesc']) ? "'{$_POST['projectDesc']}'" : 'NULL';
                $params['col_founderID'] = $_POST['curManager'];
                $this->project->save($params);
            }
        }
    }

    public function __construct(content $view, $pages, $project)
    {
        eController::__construct($view, $pages);
        $this->project = mTabMain::getCurModel($project);
        $this->configs = Configs::readCfg('project',Configs::currentBuild());
        $this->configs['endStagesID'] = explode(',',$this->configs['endStagesID']);
        $this->configs['activeStagesID'] = explode(',',$this->configs['activeStagesID']);
    }

    /**
     * Включение/отключение автоплана
     */
    public function switchPlan(){
        if(isset($_POST['planState']) && $this->project['col_ProjectPlanState'] != $_POST['planState']){
            if($this->project['col_ProjectPlanState'] == 1 && empty($_POST['descState'])){
                echo json_encode(['error'=>'Пожалуйста, укажите причину выключения авто-плана']);
            }
            else{
                $stageInfo = mProjectPlan::getCurModel($this->project['col_pstageID']);
                if(!empty($stageInfo)){
                    //просрочка по стадии, а если так, то нужно указать причину просрочки
                    if(
                        strtotime($stageInfo['col_dateEndPlan']) < time()
                        && empty($_POST['descStage'])
                        && !in_array($this->project['col_pstageID'],$this->configs['endStagesID'])){
                        echo json_encode(['stageIsLate'=>true]);
                    }
                    else{
                        if(!$this->project->switchPlanState(
                            $_POST['planState'],
                            !empty($_POST['descStage']) ? ' Причина просрочки:'.$_POST['descStage'] : 0,
                            !empty($_POST['descState']) ? $_POST['descState'] : ''
                        )){
                            echo json_encode(['error'=>'Не удалось запустить автоплан. Возможно, больше нет плановых стадий.']);
                        }
                        else
                            echo json_encode(['success'=>1]);
                    }
                }
                else{
                    echo json_encode(['error'=>'Ошибка при поиске стадии проекта, пожалуйста, оповестите администратора!']);
                }
            }
        }
    }

    /**
     * перевод проекта на след. стадию.
     */
    public function stageMove(){
        if(!empty($this->project)){


            $stageInfo = mProjectPlan::getCurModel($this->project['col_pstageID']);

            if($stageInfo['col_respID'] != Configs::userID()){
                echo json_encode(['error'=>'Только ответственный может сменить стадию.']);
                return;
            }

            if(!empty($_POST['goNextStage'])){
                if(strtotime($stageInfo['col_dateEndPlan']) < time() && empty($_POST['descStage'])){
                    echo json_encode(['error'=>'Не указана причина просрочки стадии']);
                    return;
                }
                elseif (strtotime($stageInfo['col_dateEndPlan']) < time())
                    $_POST['descStage'] = 'Причина просрочки: '.$_POST['descStage'];

                if($this->project->switchToNextPlanStage(empty($_POST['descStage'])? 0 : $_POST['descStage']))
                    echo json_encode(['success'=>1]);
                else
                    echo json_encode(['error'=>'Ошибка при попытке перевести стадию']);
            }
            else{
                if($this->project['col_ProjectPlanState'] == 1){//автоплан
                    $stnfo = $this->project->getNextStageID();
                    if(empty($stnfo)){
                        echo json_encode(['error'=>'В плане проекта больше нет стадий. Скорее всего, выполнение плана завершено.']);
                    }
                    else{
                        $this->view
                            ->set($stnfo)
                            ->out('infoNextStageForm',$this->className);
                    }
                }
                else{ //не по плану
                    if(empty($_POST)){
                        $groups = User::getGropList();
                        $groups[0] = '...';
                        //если нет просрочки стадии
                        if(strtotime($stageInfo['col_dateEndPlan']) > time() || in_array($this->project['col_pstageID'],$this->configs['endStagesID'])){
                            $this->view->set('customDisplayStyle','display: none;');
                        }

                        $this->view
                            ->set('groupList',html::select($groups,'choosedGroup',0,'class="form-control inlineBlock" style="width:230px;" onchange="getStagesByGroup(this.value)"'))
                            ->out('nextStageForm',$this->className);
                    }
                    else if(
                        !empty($_POST['chosedStage'])
                        && !empty($_POST['chosedStageResp'])
                        && !empty($_POST['stageDateTo'])
                    ){
                        //если просрочена стадия и нет обьяснения или дата окончания след. стадии выставлена неверно, нужно завернуть
                        if(
                            (strtotime($stageInfo['col_dateEndPlan']) < time() && empty($_POST['stageFailDesc']) && !in_array($this->project['col_pstageID'],$this->configs['endStagesID']))
                            || (strtotime($_POST['stageDateTo']) < time())
                        ){
                            return;
                        }

                        $this->project->sendStage(
                            !empty($_POST['stageComment']) ? "'{$_POST['stageComment']}'": 'NULL',
                            $_POST['chosedStageResp'],
                            $_POST['chosedStage'],
                            $_POST['stageDateTo'],
                            !empty($_POST['stageFailDesc']) ? $_POST['stageFailDesc']: ''
                        );
                        echo json_encode(['success'=>1]);
                    }
                }
            }
        }
    }

    /**
     * список стадий для группы
     */
    public function getStageList(){
        if(!empty($this->project) && !empty($_GET['group'])){
            $list = mStages::getStageListByGroup($_GET['group']);

            if(!empty($list)){
                $list[0] = '...';
                echo '<span style="display: inline-block; width: 220px;">Пожалуйста, выберите стадию: </span>'.html::select($list,'chosedStage',0,'class="form-control inlineBlock" style="width:230px;" onchange="getRespByStage(this.value)"');
            }
        }
    }

    /**
     * Ответственные за стадию
     */
    public function getRespList(){
        if(!empty($this->project) && !empty($_GET['stage'])){
            $list = User::getUserListByStage($_GET['stage']);

            if(!empty($list)){
                $list[0] = '...';
                echo '<span style="display: inline-block; width: 220px;">Пожалуйста, выберите ответственного: </span>'.html::select($list,'chosedStageResp',0,'class="form-control inlineBlock" style="width:230px;"');
            }
        }
    }

    /**
     * проверка на возможность изменения вкладки (из конфига)
     * @return bool
     */
    protected function WriteAccess(){

        $prop = self::getProperties();
        $role = explode(',',$prop['userAccessRW']);
        $group = explode(',',$prop['groupAccessRW']);

        if(in_array(Configs::curGroup(),$group) || in_array(3,$group)
            || in_array(Configs::curRole(),$role)){
            return true;
        }

        return false;
    }

}