<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.12.2016
 * добавить проект
 **/
namespace build\erp\project;
use build\erp\inc\eController;
use build\erp\inc\Project;
use mwce\Tools\Content;
use mwce\Tools\Tools;


class addProject extends eController
{
    protected $postField = array(
        'projectName' => ['type'=>self::STR,'maxLength'=>200],
        'isSerial' => ['type'=>self::INT],
        'projectNum' => ['type'=>self::INT],
    );

    public function actionIndex()
    {
        if(empty($_POST)){
            $this->view->out('main',$this->className);
        }
        else if(!empty($_POST['projectName'])){

            $result = Project::Add($_POST['projectName'],$_SESSION['mwcuid'],!empty($_POST['projectNum']) ? $_POST['projectNum'] : 0);
            if(!empty($result)){
                Tools::go($this->view->getAdr() . 'page/inProject.html?id='.$result['col_projectID']);
            }
            else{
                Content::showError('Создание проекта завершилось ошибкой','Если данная ошибка повторится, пожалуйста, оповестите об этом администратора системы');
            }
        }
        else{
            $this->view
                ->set(['errTitle'=>'Внимание, ошибка!','msg_desc'=>'Произошла непредвиденная ошибка! Если данное сообщение повторяется, пожалуйста, обратитесь к администратору.'])
                ->out('error');
        }
    }

    public function actionCheckProjectNum(){
        if(!empty($_POST['projectNum'])){
            $project = Project::getModels([
                'projectNum'=>$_POST['projectNum'],
                'pageFrom'=> 0,
                'pageTo'=> 1,
            ]);

            if(!empty($project)){
                echo json_encode(['name'=>$project[0]['col_projectName']]);
            }
            else{
                echo json_encode(['error'=>'Такого проекта не существует']);
            }
        }
    }
}