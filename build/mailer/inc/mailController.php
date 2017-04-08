<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 08.04.2017
 *
 **/
namespace  build\mailer\inc;
use mwce\Controllers\ModuleController;

class mailController extends ModuleController
{
    public function __construct(\mwce\Tools\Content $view, $pages)
    {
        parent::__construct($view, $pages);

        $this->view
            ->setName($this->className)
            ->set('currentPage',$this->className);
    }
}