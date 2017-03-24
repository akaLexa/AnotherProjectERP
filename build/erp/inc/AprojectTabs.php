<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 27.12.2016
 * абстрактрый класс для описания общего
 * во вкладках проекта
 **/
namespace  build\erp\inc;

use mwce\Tools\Configs;

abstract class AprojectTabs extends eController implements iProjectTabs
{
    /**
     * настройки вкладки
     * @var array
     */
    protected $props;
    /**
     * @var Project
     */
    protected $project;

    /**
     * настройки для модуля
     * @return array
     */
    public function getProperties()
    {
        if(!empty($this->props))
            return $this->props;

        $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'tabs'.DIRECTORY_SEPARATOR.'cfg'.DIRECTORY_SEPARATOR.$this->className.'.php';
        if(file_exists($path)) {
            $this->props = require $path;
            return $this->props;
        }
        else
            return [];
    }

    /**
     * default action
     */
    public function Index(){

    }

    /**
     * AprojectTabs constructor.
     * @param \mwce\Tools\Content $view
     * @param string $pages
     * @param int $project
     */
    public function __construct(\mwce\Tools\Content $view, $pages, $project)
    {
        parent::__construct($view, $pages);
        $this->project = Project::getCurModel($project);
    }
}