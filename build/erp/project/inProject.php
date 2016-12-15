<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.12.2016
 * модуль отображения страницы проекта
 **/
namespace build\erp\project;
use build\erp\inc\eController;
use build\erp\inc\Project;
use mwce\Tools;

class inProject extends eController
{
    protected $getField = array(
        'id' => ['type'=>self::INT],
    );

    public function actionIndex()
    {
        if(empty($_GET['id'])){
            $this->view
                ->set(['errTitle'=>'Просто сообщение','msg_desc'=>'Тут ничего нет. Совсем ;('])
                ->out('error');
        }
        else{
            $project = Project::getCurModel($_GET['id']);
            if(empty($project)){
                $this->view
                    ->set(['errTitle'=>'Сообщение','msg_desc'=>'Такого проекта не существует'])
                    ->out('error');
            }
            else{
                $this->view->set('title',$project['col_pnID'].':'.$project['col_projectName']);
              //  Tools::debug($project);
                $this->view
                    ->add_dict($project)
                    ->out('main',$this->className);
            }

        }
    }

}