<?php

namespace build\erp\main;

use build\erp\inc\eController;

class MainPage extends eController
{
    public function actionIndex()
    {
        $this->view->out('mainpage',$this->className);
    }

    public function actionErrorInLogin(){
        $this->view->out('errorLogin',$this->className);
    }

}