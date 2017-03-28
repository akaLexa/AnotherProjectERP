<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 07.04.2016
 *
 **/

namespace mwce\Routing;

use mwce\Exceptions\CfgException;
use mwce\Exceptions\ModException;
use mwce\Tools\Configs;
use mwce\Tools\Content;
use mwce\Tools\Logs;
use mwce\Tools\Tools;
use mwce\traits\tSingleton;

class router
{
    /**
     * mwce/Traits
     */
    use tSingleton;

    /**
     * @var string
     * контроллер для вызова
     */
    protected static $curController;

    /**
     * @var string
     * метод, что нужно вызвать
     * у контроллера
     */
    protected static $curAction;

    /**
     * @var bool
     * идентификатор,
     * нужно ли запускать механизм плагинов
     */
    protected $isBg = false;

    /**
     * @var Content
     */
    protected $view;

    /**
     * @var string
     * контроллер по умолчанию
     */
    protected $defController;

    /**
     * @var mwceAccessor
     */
    protected static $accessor;

    /**
     * router constructor.
     */
    private function __construct()
    {
        try {
            session_start();

            $data = URLparser::Parse();
            $this->isBg = $data['isBg'];

            $tmp_ = require_once baseDir . '/configs/configs.php';

            if(!empty($data['build'])){
                $tmp_['defaultBuild'] = $data['build'];
            }

            Configs::addParams('globalCfg',$tmp_);

            //region проверка на основной билд и альтернативный (админка)
            if($data['type'] == 1){
                $a ='';
                $upA ='';
            }
            else{
                $a ='a';
                $upA ='A';
            }

            if (empty($_SESSION['mwc'.$a.'build'])) {
                Configs::addParams('currentBuild',Configs::globalCfg('defaultBuild'));
                $_SESSION['mwc'.$a.'build'] = Configs::globalCfg('default'.$upA.'Build');
            }
            else{
                Configs::addParams('currentBuild',$_SESSION['mwc'.$a.'build']);
            }

            if (empty($_SESSION['mwc'.$a.'Group'])) {
                Configs::addParams('cur'.$a.'Group',2);
                $_SESSION['mwc'.$a.'Group'] = 2;
            }
            else {
                Configs::addParams('cur'.$a.'Group',$_SESSION['mwc'.$a.'Group']);
            }

            if (!empty($_SESSION['mwc'.$a.'Role'])) {
                Configs::addParams('cur'.$a.'Role',$_SESSION['mwc'.$a.'Role']);
            }
            else {
                Configs::addParams('cur'.$a.'Role',0);
                $_SESSION['mwc'.$a.'Role'] = 0;
            }

            if (!empty($_SESSION['mwc'.$a.'uid'])) {
                Configs::addParams('userID',$_SESSION['mwc'.$a.'uid']);
            }
            else{
                Configs::addParams('userID',0);
                $_SESSION['mwc'.$a.'uid'] = 0;
            }

            //endregion

            Configs::addParams('buildCfg',Configs::readCfg('main', Configs::currentBuild()));

            if (!Configs::buildCfg()) {
                session_destroy();
                throw new CfgException ('Can\'t read build config: main.cfg in ' .  Configs::currentBuild());
            }

            if (empty($_SESSION['mwclang']) && !empty(Configs::buildCfg('dlang'))) {
                $_SESSION['mwclang'] = Configs::buildCfg('dlang');
            }

            $this->defController = '\\build\\' . Configs::currentBuild() . '\\inc\\' . Configs::buildCfg('defController');

            $this->view = new Content(Tools::getAddress(), Configs::buildCfg('theme'), Configs::buildCfg('dlang'));

            //region запуск роутинга доступов для плагинов и модулей

            if(file_exists(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR .  Configs::currentBuild() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'AccessRouter.php')){

                $ar = '\\build\\' . Configs::currentBuild() . '\\' . 'inc\\' . 'AccessRouter';

                self::$accessor = new $ar($this->view,Configs::globalCfg('defaultConNum')); //todo: прописать в конфиг билда загрузку по умолчанию?

                if(!(self::$accessor instanceof mwceAccessor)){
                    $e = new ModException('AccessRouter does not extends mwceAccessor!');
                    Logs::log($e);
                    Content::errorException($e);
                    die();
                }
            }
            else{

                $e = new ModException('AccessRouter does not exists!');
                Logs::log($e);
                Content::errorException($e);
                die();
            }

            //endregion

            if (empty($data['controller'])) {
                self::$curController = Configs::buildCfg('defpage');
            }
            else {
                self::$curController = $data['controller'];
            }

            if (empty($data['action'])) {
                self::$curAction = 'actionIndex';
            }
            else {
                self::$curAction = 'action' . $data['action'];
            }

        }
        catch (\Exception $e) {
            Logs::log($e);
            Content::errorException($e);
            die();
        }
    }

    /**
     * установть текущую страницу
     * @param string $controller
     */
    public static function setCurController($controller){
        self::$curController = $controller;
    }

    /**
     * установить текущее действие (функцию)
     * @param string $action
     */
    public static function setCurAction($action){
        self::$curAction = $action;
    }

    /**
     * запуск обработки моделей
     */
    public function startModules()
    {
        try {

            if (self::$curController == $this->defController) //если указан контроллер по умолчанию, то никаких действий не предпринимаем
            {
                return;
            }

            self::$accessor->renderPage(self::$curController,self::$curAction, Configs::curGroup(),Configs::curRole(),Configs::userID(),$this->defController);
        }
        catch (\Exception $e) {
            Logs::log($e);
            
            if (!empty(Configs::globalCfg('errorLevel')) && Configs::globalCfg('errorLevel') > 0) {
                $this->view->error($e);
            }
            else {
                $this->view->errortext('Something wrong with this module. Please, check logs!');
            }
        }
    }

    /**
     * запуск обработки плагинов
     */
    public function startPlugins()
    {
        if (!$this->isBg) //если в бекграунде, то плагины не включаем.
        {
           self::$accessor->renderPlugin(Configs::curGroup(),Configs::curRole(),Configs::userID());
        }
    }

    /**
     * вывод на экран
     */
    public function show()
    {
        /*
         * выводим на экран сгенеренные данные модулей в ключевое
         * слово "page" в шаблон "index.html" в папке "theme/тема/html/public"
         */
        if (!$this->isBg) {
            $this->view->global_out($this->view->defHtml, 'public','page');
        }
        else {
            echo $this->view->getContainer();
        }
    }

    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'isbg':
                return $this->isBg;
            default:
                return false;
        }
    }

    public function __isset($name){}

    public function __set($name, $value){}
}