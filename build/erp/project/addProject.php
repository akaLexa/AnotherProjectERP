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
            $isSerial = !empty($_POST['isSerial']) && !empty($_POST['projectNum']) ? $_POST['projectNum'] : 0;
            $result = Project::Add($_POST['projectName'],$_SESSION['mwcuid'],$isSerial);
            echo 'Тут должен быть редирект на проект '.$result['col_projectID'];
        }
        else{
            $this->view
                ->set(['errTitle'=>'Внимание, ошибка!','msg_desc'=>'Произошла непредвиденная ошибка! Если данное сообщение повторяется, пожалуйста, обратитесь к администратору.'])
                ->out('error');
        }
    }
    //todo: не забыть добавить опцию проверки на серийный проект и заполнение формы!

}