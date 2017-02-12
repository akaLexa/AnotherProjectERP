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
     * @var content
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

            $cfg_params['globalCfg'] = require_once baseDir . '/configs/configs.php';

            $data = $this->parseURL();

            if (empty($data) || empty($cfg_params['globalCfg']['defaultabuild']) || trim($data['type']) != 'control') //обычные страницы
            {
                if (empty($_SESSION['mwcbuild'])) {
                    $cfg_params['currentBuild'] = $cfg_params['globalCfg']['defaultbuild'];
                    $_SESSION['mwcbuild'] = $cfg_params['globalCfg']['defaultbuild'];
                }
                else{
                    $cfg_params['currentBuild'] = $_SESSION['mwcbuild'];
                }

                if (empty($_SESSION['mwcGroup'])) {
                    $cfg_params['curGroup'] = 2;
                    $_SESSION['mwcGroup'] = 2;
                }
                else {
                    $cfg_params['curGroup'] = $_SESSION['mwcGroup'];
                }

                if (!empty($_SESSION['mwcRole'])) {
                    $cfg_params['curRole'] = $_SESSION['mwcRole'];
                }
                else {
                    $cfg_params['curRole'] = 0;
                    $_SESSION['mwcRole'] = 0;
                }

                if (!empty($_SESSION['mwcuid'])) {
                    $cfg_params['userID'] = $_SESSION['mwcuid'];
                }
                else{
                    $cfg_params['userID'] = 0;
                    $_SESSION['mwcuid'] = 0;
                }

            }
            else //админка
            {
                if (empty($_SESSION['mwcabuild'])) {
                    $_SESSION['mwcabuild'] = $cfg_params['globalCfg']['defaultabuild'];
                    $cfg_params['currentBuild'] = $cfg_params['globalCfg']['defaultabuild'];
                }
                else{
                    $cfg_params['currentBuild'] = $_SESSION['mwcbuild'];
                }

                if (empty($_SESSION['mwcaGroup'])) {
                    $cfg_params['curGroup'] = 2;
                    $_SESSION['mwcaGroup'] = 2;
                }
                else {
                    $cfg_params['curGroup'] = $_SESSION['mwcaGroup'];
                }

                if (!empty($_SESSION['mwcaRole'])) {
                    $cfg_params['curRole'] = $_SESSION['mwcaRole'];
                }
                else {
                    $cfg_params['curRole'] = 0;
                    $_SESSION['mwcaRole'] = 0;
                }

                if (!empty($_SESSION['mwcauid'])) {
                    $cfg_params['userID'] = $_SESSION['mwcauid'];
                }
                else{
                    $cfg_params['userID'] = 0;
                    $_SESSION['mwcauid'] = 0;
                }
            }

            $cfg_params['buildCfg'] = Configs::readCfg('main', $cfg_params['currentBuild']);

            if (empty($cfg_params['buildCfg'])) {
                session_destroy();
                throw new CfgException ('Can\'t read build config: main.cfg in ' . $cfg_params['currentBuild']);
            }

            if (empty($_SESSION['mwclang'])) {
                $_SESSION['mwclang'] = $cfg_params['buildCfg']['dlang'];
            }

            Configs::initConfigs($cfg_params); //собираем все логи в 1 кучу

            define('curLang',Configs::buildCfg('dlang'));//todo: проверить, зачем они нужны и выпилить

            $this->defController = '\\build\\' . Configs::currentBuild() . '\\inc\\' . Configs::buildCfg('defController');

            $this->view = new content(Tools::getAddress(), Configs::buildCfg('theme'), Configs::buildCfg('dlang'));

            //region запуск роутинга доступов для плагинов и модулей

            if(file_exists(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR .  Configs::currentBuild() . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'AccessRouter.php')){

                $ar = '\\build\\' . Configs::currentBuild() . '\\' . 'inc\\' . 'AccessRouter';

                self::$accessor = new $ar($this->view,Configs::globalCfg('defaultConNum')); //todo: прописать в конфиг билда загрузку по умолчанию?

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
                self::$curController = Configs::buildCfg('defpage');
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
}