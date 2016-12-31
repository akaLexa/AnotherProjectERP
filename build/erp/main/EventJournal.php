<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 31.12.2016
 * журнал событий
 **/
namespace build\erp\main;
use build\erp\inc\eController;

class EventJournal extends eController
{

    public function actionIndex()
    {
        $this->view
            ->out('main',$this->className);
    }

}