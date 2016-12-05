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
use mwce\DicBuilder;
use mwce\Exceptions\ModException;
use mwce\Logs;
use mwce\mwceAccessor;
use mwce\PluginController;
use mwce\Tools;


class AccessRouter extends mwceAccessor
{
    public function __construct(content $view, $conNum=0)
    {
        parent::__construct($view, $conNum);

        $this->plugins =$this->getPluginsList();
        $this->pages = $this->getModuleList();
    }

    /**
     * получение списка зарегистрированных модулей
     * @return array|mixed
     */
    protected function getModuleList(){

        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . '_dat' . DIRECTORY_SEPARATOR . curLang .'_pages.php';

        if(file_exists($path)){
            $pages = require $path;
            if(!empty($pages))
                return $pages;
        }


        $q = $this->db->query("SELECT 
  mm.*,
  (SELECT GROUP_CONCAT(tmg.col_gID SEPARATOR ',') FROM tbl_module_groups tmg WHERE tmg.col_modID = mm.col_modID) AS col_groups,
  (SELECT GROUP_CONCAT(tmr.col_roleID SEPARATOR ',') FROM tbl_module_roles tmr WHERE tmr.col_modID = mm.col_modID) AS col_roles
FROM 
   tbl_modules mm 
ORDER BY
   mm.col_title");

        $pages = array();
        $inFile = '';
        $dict = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . curLang . DIRECTORY_SEPARATOR . 'titles.php');

        while ($res = $q->fetch()){
            $pages[$res['col_moduleName']] = array(
                'title' => $res['col_title'],
                'path' => $res['col_path'],
                'cache' => $res['col_cache'],
                'isClass' => $res['col_isClass'],
                'groupAccess' => $res['col_groups'],
                'roleAccess' => $res['col_roles'],
                'titleLegend' => !empty($dict[$res['col_title']]) ? $dict[$res['col_title']] : '',
            );
            $inFile.="\t'{$res['col_moduleName']}' => ['title' => '{$res['col_title']}','path' => '{$res['col_path']}','cach' => {$res['col_cache']}, 'isClass' => {$res['col_isClass']}, 'groupAccess' => '{$res['col_groups']}', 'roleAccess'=>'{$res['col_roles']}','titleLegend' => '{$pages[$res['col_moduleName']]['titleLegend']}',],\r\n";
        }
        if(!empty($inFile)){
            file_put_contents($path,'<?php return array('.$inFile.');',LOCK_EX);
        }

        return $pages;
    }

    protected function getPluginsList(){
        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . '_dat' . DIRECTORY_SEPARATOR . curLang .'_plugins.php';

        if(file_exists($path)){
            $plugins = require $path;
            if(!empty($plugins))
                return $plugins;
        }


        $q = $this->db->query("SELECT
  tp.*,
  (SELECT GROUP_CONCAT(col_roleID SEPARATOR ',') FROM tbl_plugins_roles tpr WHERE tpr.col_pID = tp.col_pID) AS col_roles,
  (SELECT GROUP_CONCAT(col_gID SEPARATOR ',') FROM tbl_plugins_group tpg WHERE tpg.col_pID = tp.col_pID) AS col_groups
FROM
  tbl_plugins tp
ORDER BY tp.col_seq");

        $plugins = array();
        $inFile = '';

        while ($res = $q->fetch()){
            $plugins[$res['col_pluginName']] = array(
                'cache' => $res['col_cache'],
                'isClass' => $res['col_isClass'],
                'state' => $res['col_pluginState'],
                'groupAccess' => $res['col_groups'],
                'roleAccess' => $res['col_roles'],
                'seq' => $res['col_seq'],
            );
            $inFile.="\t'{$res['col_pluginName']}' => ['cache' => {$res['col_cache']}, 'isClass' => {$res['col_isClass']}, 'groupAccess' => '{$res['col_groups']}', 'roleAccess'=>'{$res['col_roles']}','state' => {$res['col_pluginState']},'seq' => {$res['col_seq']}],\r\n";
        }
        if(!empty($inFile)){
            file_put_contents($path,'<?php return array('.$inFile.');',LOCK_EX);
        }

        return $plugins;
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

            if (in_array($group, $access)
                || in_array($role, $roleAccess)
                || in_array(4, $access) //все
                || (in_array(3, $access) && $group != 2)  //пользователи (кроме гостей)
                || $err == 0)//если пользователю дозволен вход и нет проблем с allowedUsrs
            {
                if ($this->pages[$page]["isClass"] == '1') //если модуль является православным MVC
                {
                    $cPath = '\\build\\' . tbuild . '\\' . str_replace('/', '\\', $this->pages[$page]['path']);
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
                    $controller->genNonMVC(baseDir . DIRECTORY_SEPARATOR . 'build/' . tbuild . '/' . $this->pages[$page]['path'] . '/' . $page . '.php');
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

        if (is_array($this->plugins)) //если в бекграунде, то плагины не включаем.
        {
            $ai = new \ArrayIterator($this->plugins);

            foreach ($ai as $name => $param) {
                try {
                    if ($param["state"] == 1)//если плагин включен
                    {
                        $contoller_path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $name . '.php';

                        $cPath = 'build\\' . tbuild . '\\plugins\\' . $name;

                        if(!file_exists($contoller_path))
                        {
                            Logs::log(new ModException('Plugin controller "' . $cPath . '" not exists'));
                        }
                        else
                        {
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

                            if (empty($param["groupAccess"])) {
                                $paccess = [];
                            }
                            else {
                                $paccess = explode(",", $param["groupAccess"]);
                            }

                            if(!empty($param['roleAccess'])){
                                $rAccess = explode(',',$param['roleAccess']);
                            }
                            else{
                                $rAccess = [];
                            }

                            if (in_array($group, $paccess)
                                || in_array($role, $rAccess)
                                || in_array(4, $paccess)
                                || (in_array(3, $paccess) && $group != 2)
                                || $err == 0
                            ) //если есть доступ к плагинам показываем
                            {
                                if ($param["isClass"] == 1) //если это MVC плагин
                                {
                                    if (class_exists($cPath)) {
                                        $pcontoller = new $cPath($this->view, $this->plugins);
                                        $pcontoller->action('actionIndex');
                                        $pcontoller->parentOut();
                                    } else {
                                        Logs::log(3, 'class ' . $cPath . ' not found');
                                    }
                                } else {
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