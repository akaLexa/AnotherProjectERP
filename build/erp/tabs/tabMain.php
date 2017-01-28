<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.12.2016
 *
 **/
namespace build\erp\tabs;
use build\erp\inc\AprojectTabs;
use build\erp\inc\Project;
use build\erp\inc\User;
use build\erp\tabs\m\mTabMain;
use mwce\html_;
use mwce\router;


class tabMain extends AprojectTabs
{
    protected $postField = array(
        'projectNane' => ['type'=>self::STR,'maxLength'=>200],
        'projectDesc' => ['type'=>self::STR],
        'curManager' => ['type'=>self::INT],
    );



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