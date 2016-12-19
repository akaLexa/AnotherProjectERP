<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.12.2016
 *
 **/
namespace build\erp\tabs;
use build\erp\inc\eController;
use build\erp\inc\iProjectTabs;
use build\erp\inc\Project;

class tabProjectPlan extends eController implements iProjectTabs
{

    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params = null)
    {
        if(!empty($_GET['id'])){
            $project = Project::getCurModel($_GET['id']);

            if(empty($project)){
                $this->view
                    ->set(['errTitle'=>'Ошибка','msg_desc'=>'Данные по выбранному проекту не найдены!'])
                    ->out('error');
            }
            else{
                $this->view
                    ->add_dict($project)
                    ->out('main',$this->className);
            }
        }
        else{
            $this->view
                ->set(['errTitle'=>'Ошибка','msg_desc'=>'Данные по выбранному проекту не найдены!'])
                ->out('error');
        }

    }

    /**
     * настройки для модуля
     * @return array
     */
    public function getProperties()
    {

    }
}