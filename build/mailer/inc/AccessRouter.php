<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 25.11.2016
 *
 **/
namespace build\mailer\inc;

use mwce\Tools\Configs;
use mwce\Tools\Content;
use mwce\Exceptions\ModException;
use mwce\Tools\Logs;
use mwce\Routing\mwceAccessor;

class AccessRouter extends mwceAccessor
{
    public function __construct(Content $view, $conNum=0)
    {
        //если не настроены подключения к бд, автоматически копируем с erp билда

        $cfg = Configs::loadConnectionCfg();

        if(empty($cfg)){
            $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'erp' . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'connections.php';

            if(file_exists($path)){
                $newCfg = require $path;

                if(!empty($newCfg)){
                    self::writeCfg(Configs::currentBuild(),$newCfg,true);
                }
            }
        }

        parent::__construct($view, $conNum);

        $this->plugins = [];
        $this->pages = array(
            'mail'=>[
                'title' => 'mail',
                'path' => 'mail/mail',
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

    private function writeCfg($build, $array,$notOne = false)
    {
        $content = '<?php return [' . PHP_EOL;

        if(!$notOne)
        {
            $content .= '0=>[ ' . PHP_EOL;
            $content .= '"server"=>"' . $array['server'] . '", ' . PHP_EOL;
            $content .= '"db"=>"' . $array['db'] . '", ' . PHP_EOL;
            $content .= '"user"=>"' . $array['user'] . '", ' . PHP_EOL;
            $content .= '"password"=>"' . $array['password'] . '", ' . PHP_EOL;
            $content .= '"type"=>' . $array['type'] . ', ' . PHP_EOL;
            $content .= '],';
        }
        else
        {
            foreach ($array as $name=>$vals){
                if(is_numeric($name))
                    $content .= $name.'=>[ ' . PHP_EOL;
                else
                    $content .= '"'.$name.'"=>[ ' . PHP_EOL;

                $content .= '"server"=>"' . $vals['server'] . '", ' . PHP_EOL;
                $content .= '"db"=>"' . $vals['db'] . '", ' . PHP_EOL;
                $content .= '"user"=>"' . $vals['user'] . '", ' . PHP_EOL;
                $content .= '"password"=>"' . $vals['password'] . '", ' . PHP_EOL;
                $content .= '"type"=>' . $vals['type'] . ', ' . PHP_EOL;
                $content .= '],';
            }
        }


        $content .= '];';
        file_put_contents(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $build . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'connections.php', $content, LOCK_EX);

    }
}