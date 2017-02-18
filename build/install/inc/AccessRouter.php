<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 25.11.2016
 *
 **/
namespace build\install\inc;

use mwce\Configs;
use mwce\content;
use mwce\Exceptions\ModException;
use mwce\Logs;
use mwce\mwceAccessor;


class AccessRouter extends mwceAccessor
{
    public function __construct(content $view, $conNum=0)
    {
        parent::__construct($view, $conNum);

        $this->plugins = [];
        $this->pages = array(
            'install'=>[
                'title' => 'install',
                'path' => 'pages/install',
                'cache' => 0,
                'isClass' => 1,
                'groupAccess' => 4,
            ]
        );
    }

    /**
     * @param string $page
     * @param string $acton
     * @param int $group
     * @param int $uid
     * @param string $defController
     * @return \Exception|void
     */
    public function renderPage($page, $acton, $group,$role,$uid,$defController)
    {

        if (!empty($this->pages[$page])) {

            if(!empty($this->pages[$page]["groupAccess"])){
                $access = explode(",", $this->pages[$page]["groupAccess"]);
            }
            else{
                $access = [];
            }

            if(!empty($this->pages[$page]["roleAccess"])){
                $roleAccess = explode(",", $this->pages[$page]["roleAccess"]);
            }
            else{
                $roleAccess = [];
            }

            //region проверка на пользователя (если есть)
            $ccfg = Configs::readCfg($page, Configs::currentBuild());

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

            if (in_array($group, $access)
                || in_array($role, $roleAccess)
                || in_array(4, $access) //все
                || (in_array(3, $access) && $group != 2)  //пользователи (кроме гостей)
                || $err == 0)//если пользователю дозволен вход и нет проблем с allowedUsrs
            {

                if ($this->pages[$page]["isClass"] == '1') //если модуль является православным MVC
                {

                    $cPath = '\\build\\' . Configs::currentBuild() . '\\' . str_replace('/', '\\', $this->pages[$page]['path']);

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
                    $controller->genNonMVC(baseDir . DIRECTORY_SEPARATOR . 'build/' . Configs::currentBuild() . '/' . $this->pages[$page]['path'] . '/' . $page . '.php');
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
     * @param int $role
     * @param int $uid
     */
    public function renderPlugin($group,$role,$uid){

    }
}