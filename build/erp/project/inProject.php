<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.12.2016
 * модуль отображения страницы проекта
 **/
namespace build\erp\project;
use build\erp\inc\eController;
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
            Tools::debug($_GET);
        }
    }

}