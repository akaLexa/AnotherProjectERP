<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 16.05.2017
 * настройка пользователя
 **/
namespace build\erp\user;
use build\erp\inc\eController;
use build\erp\inc\User;
use mwce\Tools\Configs;

class UserArea extends eController
{
    public function actionIndex()
    {
        $obj = User::getCurModel(Configs::userID());
        if(!empty($obj)) {

            if(empty($obj['col_deputyID']))
                $this->view->set('vizDepStyle','display:none');

            $this->view
                ->set('imgNum',
                    (file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'imgs' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . Configs::userID() . '.png') ? Configs::userID() : 'default'))
                ->add_dict($obj)
                ->out('main', $this->className);
        }

    }

    public function actionGetMain(){
        if(empty($_POST)){
            $this->view
                ->out('tabMain',$this->className);
        }
    }

    public function actionGetNotice(){
        echo 'notice';
    }

    public function actionGetMailer(){
        echo 'mail';
    }
}