<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 25.11.2016
 *
 **/
namespace build\erp\inc;

use mwce\Configs;
use mwce\content;
use mwce\Exceptions\ModException;
use mwce\Logs;
use mwce\mwceAccessor;
use mwce\PluginController;


class AccessRouter extends mwceAccessor
{
    public function __construct(content $view, $conNum=0)
    {
        parent::__construct($view, $conNum);

        $this->pages = array(
            'MainPage' => ['title' => 'title_1','ppath' => 'main', 'caching' => '0', "ison" => '1', "isClass" => '1', "groups" => '2'],
            'UnitManager' => ['title' => 'title_2','ppath' => 'adm', 'caching' => '0', "ison" => '1', "isClass" => '1', "groups" => '2'],
        );

        $this->plugins = array();
    }

    /**
     * @param string $page
     * @param string $acton
     * @param int $group
     * @param int $uid
     * @param string $defController
     * @return \Exception|void
     */
    public function renderPage($page, $acton, $group,$uid,$defController)
    {

        if (!empty($this->pages[$page]) && $this->pages[$page]["ison"] == '1') {


            $access = explode(",", $this->pages[$page]["groups"]);

            //region проверка на пользователя (если есть)
            $ccfg = Configs::readCfg($page, tbuild);

            if (!empty($ccfg["allowedUsrs"])) {
                $usrs = explode(",", $ccfg["allowedUsrs"]);
                if (!in_array($uid, $usrs)) {
                    $err = 2;
                }
                else {
                    $err = 0;
                }
            }
            else {
                $err = 2;
            }
            //endregion

            if (in_array($group, $access) || in_array(4, $access) || (in_array(5, $access) && $group != 2) || $err == 0)//если пользователю дозволен вход и нет проблем с allowedUsrs
            {
                if ($this->pages[$page]["isClass"] == '1') //если модуль является православным MVC
                {
                    $cPath = '\\build\\' . tbuild . '\\' . $this->pages[$page]['ppath'] . '\\' . $page;
                    if (class_exists($cPath)) {

                        $controller = new $cPath($this->view, $this->pages);
                        $controller->action($acton);
                    }
                    else {
                        $controller = new $defController ($this->view, $this->pages);
                        $exp = new ModException('Module ' . $cPath . ' not exists or path is wrong');
                        $controller->showError($exp);
                        Logs::log($exp);
                    }
                }
                else {
                    $controller = new $defController ($this->view, $this->pages);
                    $controller->genNonMVC(baseDir . DIRECTORY_SEPARATOR . 'build/' . tbuild . '/' . str_replace('\\', '/', $this->pages[$page]['ppath']) . '/' . $page . '.php');
                }
            }
            else {
                $this->view->error(5);
            }
        }
        else {
            $this->view->error(5);
            Logs::log(new ModException('Controller ' . $page . ' wasn\'t register or terned off'));
        }
    }

    /**
     * @param int $group
     * @param int $uid
     */
    public function renderPlugin($group,$uid){

        if (is_array($this->plugins)) //если в бекграунде, то плагины не включаем.
        {
            $ai = new \ArrayIterator($this->plugins);

            foreach ($ai as $name => $param) {
                try {
                    if ($param["pstate"] == '1')//если плагин включен
                    {
                        $contoller_path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . $name . ".php";

                        $cPath = 'build\\' . tbuild . '\\plugins\\' . $name;

                        //region проверка на пользователя (если есть)
                        $ccfg = Configs::readCfg("plugin_" . $name, tbuild);
                        if (!empty($ccfg["allowedUsrs"])) {
                            $usrs = explode(",", $ccfg["allowedUsrs"]); //доступ по id для определенных пользователей

                            if (!in_array($uid, $usrs)) {
                                $err = 2;
                            }
                            else {
                                $err = 0;
                            }
                        }
                        else {
                            $err = 2;
                        }
                        //endregion

                        if(!file_exists($contoller_path))
                        {
                            Logs::log(new ModException('Plugin controller "' . $cPath . '" not exists'));
                        }
                        else if (!empty($param["groups"]) || $err == 0)
                        {
                            if (empty($param["groups"])) {
                                $paccess = array();
                            }
                            else {
                                $paccess = explode(",", $param["groups"]);
                            }


                            if ($param["isClass"] == '1') //если это MVC плагин
                            {

                                if (in_array($group, $paccess) || in_array(4, $paccess) || (in_array(5, $paccess) && $group != 2) || $err == 0) //если есть доступ к плагинам показываем
                                {
                                    if (class_exists($cPath)) {
                                        $pcontoller = new $cPath($this->view, $this->plugins);
                                        $pcontoller->action('actionIndex');
                                        $pcontoller->parentOut();
                                    }
                                    else {
                                        Logs::log(3, 'class ' . $cPath . ' not found');
                                    }
                                }

                            }
                            else {
                                if (in_array($group, $paccess) || in_array(4, $paccess) || (in_array(5, $paccess) && $group != 2)) //если есть доступ к плагинам показываем
                                {
                                    $pcontoller = new PluginController($this->view, $this->plugins);
                                    $pcontoller->genNonMVC($contoller_path);
                                    $pcontoller->parentOut($name);
                                }
                            }
                        }
                    }
                }
                catch (\Exception $e) {
                    $this->view->error($e);
                    $this->view->setFContainer("plugin_$name", true);
                    Logs::log($e);
                }
            }
        }
    }
}