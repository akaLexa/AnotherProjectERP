<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.12.2016
 *
 **/
namespace build\erp\tabs;
use build\erp\inc\eController;
use build\erp\inc\iProjectTabs;
use build\erp\inc\Project;
use build\erp\inc\User;
use build\erp\tabs\m\mTabMain;
use mwce\html_;
use mwce\router;
use mwce\Tools;

class tabMain extends eController implements iProjectTabs
{
    protected $postField = array(
        'projectNane' => ['type'=>self::STR,'maxLength'=>200],
        'projectDesc' => ['type'=>self::STR],
        'curManager' => ['type'=>self::INT],
    );

    /**
     * настройки вкладки
     * @var array
     */
    protected $props;

    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params=null)
    {
        $project = Project::getCurModel($params);
        if(empty($project)){
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

            $this->view
                ->add_dict($project)
                ->set('mngrList',html_::select($users,'curManager',$project['col_founderID'],'class="form-control inlineBlock"'))
                ->out('main',$this->className);
        }
    }

    /**
     * настройки для модуля
     * @return array
     */
    public function getProperties()
    {
        if(!empty($this->props))
            return $this->props;

        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.tbuild.DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg'.DIRECTORY_SEPARATOR.$this->className.'.php';
        if(file_exists($path)) {
            $this->props = require $path;
            return $this->props;
        }
        else
            return [];
    }

    public function save(){

        if(!self::WriteAccess()){
            echo json_encode(['error'=>'У Вас нет доступа для записи!']);
        }
        else{
            $project = mTabMain::getCurModel($_GET['id']);

            if(empty($_POST['curManager']))
                return;

            if(empty($project)){
                echo json_encode(['error'=>'Запрашиваемый проект не найден!']);
            }
            else{
                $params = array();
                $params['col_projectName'] = !empty($_POST['projectNane']) ? "'{$_POST['projectNane']}'" : 'NULL';
                $params['col_Desc'] = !empty($_POST['projectDesc']) ? "'{$_POST['projectDesc']}'" : 'NULL';
                $params['col_founderID'] = $_POST['curManager'];
                $project->save($params);
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

        if(in_array(router::getUserGroup(),$group) || in_array(3,$group)
            || in_array(router::getUserRole(),$role)){
            return true;
        }

        return false;
    }

}