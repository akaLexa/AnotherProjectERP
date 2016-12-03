<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 07.04.2016
 *
 **/
namespace mwce;


use mwce\Exceptions\CfgException;
use mwce\Exceptions\ModException;
use mwce\traits\singleton;

class router
{
    /**
     * mwce/traits
     */
    use singleton;

    /**
     * @var array|bool
     * глобальная настройка для понимания,
     * какой бид сейчас по умолчанию должен быть запущен
     */
    protected static $globalCfg;

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
     * @var array|bool
     * главный конфиг текущего билда
     */
    protected $buildCfg;

    /**
     * @var content
     */
    protected $view;

    /**
     * @var int
     * id пользователя
     */
    protected $curUserId = 0;

    /**
     * @var int
     * группа, по умолчанию - гости
     */
    protected $userGroup = 2;

    /**
     * @var int
     * роль пользователя
     */
    protected $userRole = 0;

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
     * узнает контрллер и экшен (если есть)
     * @return array|mixed
     */
    protected function parseURL()
    {

        if(empty($_SERVER['argc'])){
            $url = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI']:'';
        }
        else {
            if ($_SERVER['argc'] > 0) {

                if (!empty($_SERVER['argv'][1])) {
                    $url = $_SERVER['argv'][1];
                }
                else {
                    $url = '';
                }

                if($_SERVER['argc']>2){
                    for ($i=2;$i<$_SERVER['argc'];$i++){
                        $data_ = explode("=",$_SERVER['argv'][$i]);
                        $_GET[trim($data_[0])] = trim($data_[1]);
                    }
                }
            }
            else {
                $url = '';
            }
        }

        $path = trim(parse_url($url, PHP_URL_PATH), '/');

        $list = explode("/", $_SERVER["PHP_SELF"]);
        unset($list[0]);
        array_pop($list);

        if (!empty($list)) {
            $toemp = implode("/", $list) . "/";
            $path = str_replace($toemp, '', $path);
        }


        if (strripos($path, '.html') === false && strripos($path, '.php') === false) //если запрос аяксом
        {
            $this->isBg = true;
        }

        $path_array = explode('/', $path);

        if (empty($path_array)) //нет данных в строке
        {
            return false;
        }

        $parsed['type'] = $path_array[0];
        $parsed['type'] = explode('.', $parsed['type']);
        $parsed['type'] = strtolower($parsed['type'][0]);

        if ($parsed['type'] != 'index' && $parsed['type'] != 'control') {
            $parsed['type'] = '';
            if (empty($path_array[1])) //нету выражения типа site.ru/page/controller
            {
                $parsed['controller'] = false;
            }
            else {
                $parsed['controller'] = $path_array[1];
                $parsed['controller'] = explode('.', $parsed['controller']);
                $parsed['controller'] = $parsed['controller'][0];
            }

            if (empty($path_array[2]))  //нету выражения типа site.ru/page/controller/action
            {
                $parsed['action'] = false;
            }
            else {
                $parsed['action'] = $path_array[2];
                $parsed['action'] = explode('.', $parsed['action']);
                $parsed['action'] = $parsed['action'][0];
            }
        }
        else {
            if (empty($path_array[2])) //нету выражения типа site.ru/page/controller
            {
                $parsed['controller'] = false;
            }
            else {
                $parsed['controller'] = $path_array[2];
                $parsed['controller'] = explode('.', $parsed['controller']);
                $parsed['controller'] = $parsed['controller'][0];
            }

            if (empty($path_array[3]))  //нету выражения типа site.ru/page/controller/action
            {
                $parsed['action'] = false;
            }
            else {
                $parsed['action'] = $path_array[3];
                $parsed['action'] = explode('.', $parsed['action']);
                $parsed['action'] = $parsed['action'][0];
            }
        }

        return $parsed;
    }

    /**
     * router constructor.
     */
    private function __construct()
    {
        try {
            session_start();
            self::$globalCfg = require_once baseDir . '/configs/configs.php';

            $data = $this->parseURL();

            if (empty($data) || empty(self::$globalCfg['defaultabuild']) || trim($data['type']) != 'control') //обычные страницы
            {
                if (empty($_SESSION['mwcbuild'])) {
                    define('tbuild', self::$globalCfg['defaultbuild']);
                    $_SESSION['mwcbuild'] = self::$globalCfg['defaultbuild'];
                }
                else{
                    define('tbuild', $_SESSION['mwcbuild']);
                }

                if (empty($_SESSION['mwcGroup'])) {
                    $_SESSION['mwcGroup'] = $this->userGroup;
                }
                else {
                    $this->userGroup = $_SESSION['mwcGroup'];
                }

                if (empty($_SESSION['mwcRole'])) {
                    $_SESSION['mwcRole'] = $this->userRole;
                }
                else {
                    $this->userRole = $_SESSION['mwcRole'];
                }



                if (!empty($_SESSION['mwcuid'])) {
                    $this->curUserId = $_SESSION['mwcuid'];
                }
            }
            else //админка
            {
                if (empty($_SESSION['mwcabuild'])) {
                    define('tbuild', self::$globalCfg['defaultabuild']);
                    $_SESSION['mwcabuild'] = self::$globalCfg['defaultabuild'];
                }
                else{
                    define('tbuild', $_SESSION['mwcabuild']);
                }

                if (empty($_SESSION['mwcaGroup'])) {
                    $_SESSION['mwcaGroup'] = $this->userGroup;
                }
                else {
                    $this->userGroup = $_SESSION['mwcaGroup'];
                }

                if (empty($_SESSION['mwcaRole'])) {
                    $_SESSION['mwcaRole'] = $this->userRole;
                }
                else {
                    $this->userRole = $_SESSION['mwcaRole'];
                }

                if (!empty($_SESSION['mwcauid'])) {
                    $this->curUserId = $_SESSION['mwcauid'];
                }
            }

            define('conNum', self::$globalCfg['defaultConNum']);  //set default connection num from builds pool
            define('errorLevel', self::$globalCfg['errorLevel']); //set default errors show level

            $this->buildCfg = Configs::readCfg('main', tbuild);

            if (empty($this->buildCfg)) {
                session_destroy();
                throw new CfgException ('Can\'t read build config: main.cfg in ' . tbuild);
            }

            if (empty($_SESSION['mwclang'])) {
                $_SESSION['mwclang'] = $this->buildCfg['dlang'];
            }

            define('curLang',$_SESSION['mwclang']);

            $this->defController = '\\build\\' . tbuild . '\\' . 'inc\\' . $this->buildCfg['defController'];

            $this->view = new content(Tools::getAddress(), $this->buildCfg["theme"], curLang);

            //region запуск роутинга доступов для плагинов и модулей

            if(file_exists(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'AccessRouter.php')){

                $ar = '\\build\\' . tbuild . '\\' . 'inc\\' . 'AccessRouter';

                self::$accessor = new $ar($this->view,self::$globalCfg['defaultConNum']);

                if(!(self::$accessor instanceof mwceAccessor)){
                    $e = new ModException('AccessRouter does not extends mwceAccessor!');
                    Logs::log($e);
                    content::errorException($e);
                    die();
                }
            }
            else{

                $e = new ModException('AccessRouter does not exists!');
                Logs::log($e);
                content::errorException($e);
                die();
            }

            //endregion

            if (empty($data['controller'])) {
                self::$curController = $this->buildCfg['defpage'];
                $this->isBg = false;
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
            content::errorException($e);            
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

            if (self::$curController == $this->buildCfg['defController']) //если указан контроллер по умолчанию, то никаких действий не предпринимаем
            {
                return;
            }

            self::$accessor->renderPage(self::$curController,self::$curAction,$this->userGroup,$this->userRole,$this->curUserId,$this->defController);
        }
        catch (\Exception $e) {
            Logs::log($e);
            
            if (defined('errorLevel') && errorLevel > 0) {
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
           self::$accessor->renderPlugin($this->userGroup,$this->userRole,$this->curUserId);
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
}