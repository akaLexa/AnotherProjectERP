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
    public static $curController;

    /**
     * @var string
     * метод, что нужно вызвать
     * у контроллера
     */
    public static $curAction;

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
     * @var bool|array
     * набор страниц, что зарегистрированы
     */
    protected $pages;

    /**
     * @var bool|array
     * набор зарегистрированнных
     * плагинов
     */
    protected $plugins;

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
     * @var string
     * контроллер по умолчанию
     */
    protected $defController;

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
                    self::CustomSessions(!empty($data['controller'])? $data['controller'] : null);
                    $_SESSION['mwcbuild'] = self::$globalCfg['defaultbuild'];
                }
                else{
                    define('tbuild', $_SESSION['mwcbuild']);
                    self::CustomSessions(!empty($data['controller'])? $data['controller'] : null);
                }

                if (empty($_SESSION['mwcpoints'])) {
                    $_SESSION['mwcpoints'] = $this->userGroup;
                }
                else {
                    $this->userGroup = $_SESSION['mwcpoints'];
                }

                if (!empty($_SESSION['mwcuid'])) {
                    $this->curUserId = $_SESSION['mwcuid'];
                }
            }
            else //админка
            {
                if (empty($_SESSION['mwcabuild'])) {
                    define('tbuild', self::$globalCfg['defaultabuild']);
                    self::CustomSessions(!empty($data['controller'])? $data['controller'] : null);
                    $_SESSION['mwcabuild'] = self::$globalCfg['defaultabuild'];
                }
                else{
                    define('tbuild', $_SESSION['mwcabuild']);
                    self::CustomSessions(!empty($data['controller'])? $data['controller'] : null);
                }

                if (empty($_SESSION['mwcapoints'])) {
                    $_SESSION['mwcapoints'] = $this->userGroup;
                }
                else {
                    $this->userGroup = $_SESSION['mwcapoints'];
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



            $this->defController = '\\build\\' . tbuild . '\\' . 'inc\\' . $this->buildCfg['defController'];

            $this->view = new content(Tools::getAddress(), $this->buildCfg["theme"], $_SESSION["mwclang"]);

            if(!empty(self::$globalCfg['ExecClass'])){
                //coz php <7
                eval('$pb = '.self::$globalCfg['ExecClass'].'::start("'.$_SESSION['mwclang'].'");');
            }
            else{
                $pb = pageBuilder::start($_SESSION['mwclang']);
            }

            $this->pages = $pb->getPages();
            $this->plugins = $pb->getPlugins();


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
     * подключение сессий с кешированием
     */
    private function CustomSessions($defController = null){
        if(!empty(self::$globalCfg['useDbSessions']) && self::$globalCfg['useDbSessions'] == 1){
            if(!empty(self::$globalCfg['isDbSessionsMaster']) && self::$globalCfg['isDbSessionsMaster'] == 1) //если это мастер домен
                mwceSession::start(self::$globalCfg['CookieSesName']); //db session cache
            elseif (!empty($_COOKIE['mwc_sess_id']) && !empty($_COOKIE['mwc_sess_time'])) //если это read-only домен
            {
                mwceSession::$readOnly = 1;
                mwceSession::start(self::$globalCfg['CookieSesName']); //db session cache

                if(file_exists(baseDir . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'execSlave.php')){
                    require_once baseDir . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'execSlave.php';
                }
            }

        }
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

            if (!empty($this->pages[self::$curController]) && $this->pages[self::$curController]["ison"] == '1') {

                $access = explode(",", $this->pages[self::$curController]["groups"]);

                //region проверка на пользователя (если есть)
                $ccfg = Configs::readCfg(self::$curController, tbuild);

                if (!empty($ccfg["allowedUsrs"])) {
                    $usrs = explode(",", $ccfg["allowedUsrs"]);
                    if (!in_array($this->curUserId, $usrs)) {
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

                if (in_array($this->userGroup, $access) || in_array(4, $access) || (in_array(5, $access) && $this->userGroup != 2) || $err == 0)//если пользователю дозволен вход и нет проблем с allowedUsrs
                {
                    if ($this->pages[self::$curController]["isClass"] == '1') //если модуль является православным MVC
                    {
                        $cPath = '\\build\\' . tbuild . '\\' . $this->pages[self::$curController]['ppath'] . '\\' . self::$curController;
                        if (class_exists($cPath)) {
                            $controller = new $cPath($this->view, $this->pages);
                            $controller->action(self::$curAction);
                        }
                        else {
                            $controller = new $this->defController($this->view, $this->pages);
                            $exp = new ModException('Module ' . $cPath . ' not exists or path is wrong');
                            $controller->showError($exp);
                            Logs::log($exp);
                        }
                    }
                    else {
                        $controller = new $this->defController ($this->view, $this->pages);
                        $controller->genNonMVC(baseDir . DIRECTORY_SEPARATOR . 'build/' . tbuild . '/' . str_replace('\\', '/', $this->pages[self::$curController]['ppath']) . '/' . self::$curController . '.php');
                    }
                }
                else {
                    $this->view->error(5);
                }
            }
            else {
                $this->view->error(5);
                Logs::log(new ModException('Controller ' . self::$curController . ' wasn\'t register or terned off'));
            }
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
        if (is_array($this->plugins) && !$this->isBg) //если в бекграунде, то плагины не включаем.
        {
            $ai = new \ArrayIterator($this->plugins);

            foreach ($ai as $name => $param) {
                try {
                    if ($param["pstate"] == '1')//если плагин включен
                    {
                        $contoller_path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . tbuild . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . $name . ".php";

                        $cPath = 'build\\' . tbuild . '\\plugins\\controller\\' . $name;

                        //region проверка на пользователя (если есть)
                        $ccfg = Configs::readCfg("plugin_" . $name, tbuild);
                        if (!empty($ccfg["allowedUsrs"])) {
                            $usrs = explode(",", $ccfg["allowedUsrs"]); //доступ по id для определенных пользователей

                            if (!in_array($this->curUserId, $usrs)) {
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

                                if (in_array($this->userGroup, $paccess) || in_array(4, $paccess) || (in_array(5, $paccess) && $this->userGroup != 2) || $err == 0) //если есть доступ к плагинам показываем
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
                                if (in_array($this->userGroup, $paccess) || in_array(4, $paccess) || (in_array(5, $paccess) && $this->userGroup != 2)) //если есть доступ к плагинам показываем
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