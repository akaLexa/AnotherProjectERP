<?php

/**
 * MuWebCloneEngine
 * version: 1.6
 * by epmak
 * 25.08.2015
 * шаблонизатор
 **/

namespace mwce\Tools;

use mwce\Exceptions\ContentException;
use mwce\Models\Model;

/**
 * Class Content
 * шаблонизатор.
 */
class Content
{
    /**
     * @var array
     * массив значений на которые будем заменять
     */
    private $vars = array();

    /**
     * @var int
     * 1/0 показывать или нет пустые переменные
     */
    private $debug;

    /**
     * @var string
     * текущее название темы
     */
    private $themName;

    /**
     * @var string
     * текущий язык
     */
    private $clang;

    /**
     * @var string
     * текущий адрес сайта
     */
    private $adr;

    /**
     * @var string
     * разделитель
     */
    private $separator;

    /**
     * @var string
     * своеобразный буфер
     * для хранения сгенерированного html
     */
    private $container = "";

    /**
     * @var int
     */
    private $notWrite = 0;

    /**
     * @var array
     * список подключенных словарей
     */
    private $adedDic = array();

    /**
     * @var array
     * список подключеаемых css файлов
     */
    private $connectCss = array();

    /**
     * @var array
     * список подключеаемых js файлов
     */
    private $connectjs = array();

    /**
     * @var string
     * текущий модуль
     */
    private $curModule = "";

    /**
     * @var array
     * массивчек со словами,
     * которые нельзя занимать под объект
     */
    private $deniedArray = array(
        'baseVals',
        'global_js',
        'global_css',
        'site',
        'theme',
    );

    /**
     * @var string
     * отображаемая по умолчанию главная страница
     */
    public $defHtml = 'index';

    /**
     * @param string $adr - адресс сайта
     * @param string $theme - назщвание темы
     * @param string $lang - язык
     * @param string $separator - суффикс и преффикс показывающий признак, что слово ключевое
     * @param int $debug - 1 - режим дебага, пр икотором все не заполненные ключевые фразы видны
     * @throws /Exception
     */
    public function __construct($adr, $theme, $lang, $separator = "|", $debug = 0)
    {
        $this->debug = $debug;
        $this->clang = $lang;
        $this->themName = $theme;
        $this->adr = $adr;
        $this->separator = $separator;

        $this->vars['baseVals'] = array(
            $this->separator . 'site' . $this->separator => $this->adr,
            $this->separator . 'theme' . $this->separator => $this->themName,
            $this->separator . 'global_js' . $this->separator => "",
            $this->separator . 'global_css' . $this->separator => "",
        );

        $this->add_dict('site'); //если есть общий словарь, то подгружаем

        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.html';
        if (!file_exists($path)) {
            throw new ContentException("there is no theme \"{$this->themName}\" or " . $this->themName . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "public" . DIRECTORY_SEPARATOR . "index.html doesn't exists.");
        }
    }

    /**
     * контект из файла
     * @param string $path
     * @return string
     */
    static public function gContent($path)
    {
        return @file_get_contents($path);
    }

    /**
     * выставить имя текущего контейнера
     * @param string $name
     * @return Content
     * @throws /Exception
     */
    public function setName($name)
    {
        if (in_array($name, $this->deniedArray)) {
            throw new ContentException(" you can't use $name for object name");
        }

        $this->curModule = $name;

        if(!empty($this->adedDic[$name])){
            unset($this->adedDic[$name]);
        }


        self::add_dict($name);

        return $this;
    }

    /**
     * затереть текущий контроллер
     * @param int $clearAll совсем затереть или не очень
     * @return Content
     */
    public function emptyName($clearAll = 1)
    {
        if ($clearAll) {
            if (!empty($this->vars[$this->curModule]) && is_array($this->vars[$this->curModule])) {
                unset($this->vars[$this->curModule]);
            }
        }

        $this->curModule = "";

        return $this;
    }

    /**
     * узнать текущий словарь
     * @return string
     */
    public function knowName()
    {
        return $this->curModule;
    }

    /**
     * Вывод отдельного слова по идентификатору
     *
     * @param mixed $id идентификатор
     * @param string|null $cname название модуля
     * @return string
     */
    public function getVal($id, $cname = NULL)
    {
        $id = $this->separator . $id . $this->separator;

        if (is_null($cname) && empty($this->curModule)) {
            if(!empty($this->vars[$id]))
                return $this->vars[$id];
            return false;
        }
        else {
            if (!empty($this->curModule) && !empty($this->vars[$this->curModule][$id])) {
                return $this->vars[$this->curModule][$id];
            }

            else if(!empty($this->vars[$cname]) && !empty($this->vars[$cname][$id])) {
                return $this->vars[$cname][$id];
            }
        }
        return '';
    }

    /**
     * возвращает адрес сервера
     *
     * @return mixed
     */
    public function getAdr()
    {
        return $this->adr;
    }

    /**
     * Добавляет язык к контенту
     *
     * @param  string $file - название файла "словаря"
     * @param  bool $isJSON - json ворфмат или нет
     * @return Content
     */
    public function add_dict($file, $isJSON = false)
    {

        if (is_array($file) || $file instanceof Model) {

            if ($isJSON) {
                $file = json_decode($file, true);
            }


            foreach ($file as $d => $v) {

                if (!empty($this->curModule)) {
                    $this->vars[$this->curModule][$this->separator . $d . $this->separator] = $v;
                }
                else {
                    $this->vars[$this->separator . $d . $this->separator] = $v;
                }
            }

        }
        else {

            $lang = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR . $this->clang . DIRECTORY_SEPARATOR . $file . ".php");

            if(!empty($lang)){
                if($file == 'titles'){

                }
                if (!empty($this->adedDic[$file])) // если словарь уже подключен, второй раз лопатить смысла нет
                {
                    return $this;
                }

                if (is_array($lang)) {

                    foreach ($lang as $d => $v) {
                        if (!empty($this->curModule)) {
                            $this->vars[$this->curModule][$this->separator . $d . $this->separator] = $v;
                        }
                        else {
                            $this->vars[$this->separator . $d . $this->separator] = $v;
                        }
                    }
                    $this->adedDic[$file] = 1;
                }
            }
        }
        return $this;
    }

    /**
     * возвращает текущий язык
     *
     * @return string
     */
    public function cLAng()
    {
        return $this->clang;
    }

    /**
     * добаляет в словарь
     *
     * @param string|array $name - резервированное слово(без "|"), если массив, то ассоциативный кгде ключ -
     *     заресервированное слово, а значение, то, на что нужно слово заменить
     * @param mixed $val - значение зарезервированного слова
     * @param int $isJSON
     * @return  Content
     */
    public function set($name, $val = NULL, $isJSON = 0)
    {
        if (is_array($name)) {
            $this->add_dict($name, $isJSON);
        }
        else {
            if (!empty($this->curModule)) {
                $this->vars[$this->curModule][$this->separator . $name . $this->separator] = $val;
            }
            else {
                $this->vars[$this->separator . $name . $this->separator] = $val;
            }
        }

        return $this;
    }

    /**
     * создать пустое значение
     * @param string $name
     * @return Content $this
     */
    public function setEmpty($name){

        if (!empty($this->curModule)) {
            $this->vars[$this->curModule][$this->separator . $name . $this->separator] = '';
        }
        else {
            $this->vars[$this->separator . $name . $this->separator] = '';
        }
        return $this;
    }

    /**
     * заменяет название элемента в "словаре" (!в словаре должно присутствовать выражение $where)
     * @param string $what - что вставить
     * @param string $where - за место чего
     * @return Content
     */
    public function replace($what, $where)
    {
        if (!empty($this->curModule)
            && !empty($this->vars[$this->curModule][$this->separator . $what . $this->separator])
        ) {
            $this->set($where, $this->vars[$this->curModule][$this->separator . $what . $this->separator]);
        }
        else if (!empty($this->vars[$this->separator . $what . $this->separator])) {
            $this->set($where, $this->vars[$this->separator . $what . $this->separator]);
        }

        return $this;
    }

    /**
     * Функция очищаент контенер
     */
    public function clearContainer()
    {
        $this->container = "";
    }

    /**
     * возвращает информацию из конетенра
     *
     * @return string
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * позволяет включить или отключить режими забиси в буфер и выводить сразу на экран
     * @param bool|int $val
     */
    public function showOnly($val)
    {
        if ((int)$val > 0) {
            $this->notWrite = 1;
        }
        else {
            $this->notWrite = 0;
        }
    }

    /**
     * пишет в контейнер данные
     * @param string $value
     */
    public function setFromCache($value)
    {
        $this->container = $value;
    }

    /**
     * задает, в какую переменную будут помещены данные из контенера
     *
     * @param string $cname - название переменной
     * @param int $isClean - если >0 то после добавления в словарь данные из контерена будут удалены
     * @return Content
     */
    public function setFContainer($cname, $isClean = 0)
    {

        if (!empty($this->container)) {
            $this->set($cname, $this->container);
            if ((int)$isClean > 0) {
                $this->clearContainer();
            }
        }
        return $this;
    }

    /**
     * функция выводит на экран или возвращает строку с содержимым шаблона и скрипта
     * @param string $tpl - название шаблона
     * @param int $type -
     * 1 = данные собираются в контенер, иначе просто выводятся на экран?
     * 2 = возвращается в виде строки
     * @param string $folder - папка под группу файлов (обычно для модуля)
     * @param int $gentime , вывод времени генерации скрипта, если не 0
     * @return mixed|string
     */
    public function out($tpl, $folder = "", $type = 1, $gentime = 0)
    {
        if (empty($folder)) {
            $folder = "public";
        }

        $path = baseDir . DIRECTORY_SEPARATOR . "theme" . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $tpl . ".html";

        if (file_exists($path)) {
            $this->inputScripts($folder, $tpl); //подключаемые css, js скрипты

            if ($gentime != 0) {
                $this->vars[$this->separator . "gentime" . $this->separator] = round(microtime() - $gentime, 4);
            }

            //region collect_dictionary
            $content = self::gContent($path);

            if (!empty($this->curModule)
                && !empty($this->vars[$this->curModule])
                && is_array($this->vars[$this->curModule]))
            {

                $content = strtr($content, $this->vars[$this->curModule]);
            }

            $ars = [];
            $ai = new \ArrayIterator($this->vars);
            foreach ($ai as $id => $val) {
                if (!is_array($val))
                    $ars[$id] = $val;
            }

            $content = strtr($content, $ars);



            $content = strtr($content, $this->vars['baseVals']);

            if ($this->debug == 0) {
                $content = preg_replace("/[\{$this->separator}]+[A-Za-z0-9_]{1,25}[\{$this->separator}]+/", " ", $content);
            }

            //endregion

            if ($type == 1 && $this->notWrite == 0) //если собираем
            {
                $this->container .= $content;
            }
            else {
                if ($type != 2) {
                    echo $content;
                }
            }

            return $content;
        }
        else {
            $this->errortext("file \"$path\" doesn't exists");
        }
        return '';
    }

    /**
     * парсинг шабона между тегами {some_tag} string... {/some_tag}
     * thx to codeigniter
     * @param string $tag тег, вокруг которого пляски
     * @param array $data словарь
     * @param string $content адрес
     * @param string $folder папка, где искать шаблон
     * @return Content
     */
    public function loops($tag, $data, $content, $folder = 'public')
    {
        $path = baseDir . DIRECTORY_SEPARATOR . "theme" . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $content . ".html";

        if (file_exists($path)) {
            $content = self::gContent($path);
        }
        else {
            return $this;
        }

        preg_match_all('#' . preg_quote($this->separator . $tag . $this->separator) . '(.+?)' . preg_quote($this->separator . '/' . $tag . $this->separator) . '#s', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $str = '';
            foreach ($data as $row) {
                if (!is_array($row) && !is_object($row)) {
                    continue;
                }
                $temp = array();
                foreach ($row as $key => $val) {
                    if (is_array($val) || is_object($val)) {
                        $pair = $this->loops($key, $val, $match[1]);
                        if (!empty($pair) && is_array($pair)) {
                            $temp = array_merge($temp, $pair);
                        }

                        continue;
                    }

                    $temp[$this->separator . $key . $this->separator] = $val;
                }

                $str .= strtr($match[1], $temp);
            }


            if (!empty($this->curModule)) {
                $this->vars[$this->curModule][$match[0]] = $str;
            }
            else {
                $this->vars[$match[0]] = $str;
            }
        }

        return $this;
    }

    /**
     * добавляем подключаемые скрипты
     * @param string $folder папка
     * @param string $tpl название шаблона (без расширения)
     */
    public function inputScripts($folder, $tpl)
    {
        $jspath = baseDir . DIRECTORY_SEPARATOR . "theme" . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . $folder . "." . $tpl . ".js";
        if (file_exists($jspath)) {
            if (empty($this->connectjs[$folder . "." . $tpl . ".js"])) {

                $js = strtr(trim(@file_get_contents($jspath)), $this->vars['baseVals']);

                if(!empty($this->curModule))
                    $js = strtr($js, $this->vars[$this->curModule]);

                $this->vars['baseVals'][$this->separator . "global_js" . $this->separator] .= "\r\n/* imputed $tpl */\r\n" . $js;
                $this->connectjs[$folder . "." . $tpl . ".js"] = 1;
            }
        }

        $jspath1 = baseDir . DIRECTORY_SEPARATOR . "theme" . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $folder . "." . $tpl . ".js";
        if (file_exists($jspath1)) {
            if (empty($this->connectjs[$folder . "." . $tpl . ".js_"])) {

                $js = strtr(trim(@file_get_contents($jspath1)), $this->vars['baseVals']);

                if(!empty($this->curModule))
                    $js = strtr($js, $this->vars[$this->curModule]);

                $this->vars['baseVals'][$this->separator . "global_js" . $this->separator] .= "\r\n/* imputed $tpl */\r\n " . $js;
                $this->connectjs[$folder . "." . $tpl . ".js_"] = 1;
            }
        }

        $csspath = baseDir . DIRECTORY_SEPARATOR . "theme" . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . $folder . "." . $tpl . ".css";

        if (file_exists($csspath)) {
            if (empty($this->connectCss[$folder . "." . $tpl . ".css"])) {

                $this->vars['baseVals'][$this->separator . "global_css" . $this->separator] .= "\r\n{/*imputed $tpl*/}\r\n  " . trim(@file_get_contents($csspath));
                $this->connectCss[$folder . "." . $tpl . ".css"] = 1;
            }

        }

        $csspath = baseDir . DIRECTORY_SEPARATOR . "theme" . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $folder . "." . $tpl . ".css";

        if (file_exists($csspath)) {
            if (empty($this->connectCss[$folder . "." . $tpl . ".css_"])) {
                $this->vars['baseVals'][$this->separator . "global_css" . $this->separator] .= "\r\n{/*imputed $tpl*/}\r\n  " . trim(@file_get_contents($csspath));
                $this->connectCss[$folder . "." . $tpl . ".css_"] = 1;
            }

        }

    }

    /**
     * глобальный вывод на экран
     *
     * @param string $args - зарезервированное слово, в которое сольется весь накомпленный контейнер
     * @param string $tpl - файл шаблона, в который все будет сливаться
     * @param string $folder - папка
     * @param int $gentime - время microtime() для подсчета времени выполнения
     */
    public function global_out($tpl, $folder = "", $args = "page", $gentime = 0)
    {
        $this->setFContainer($args); //суем из контенера в переменную
        $this->out($tpl, $folder, 0, $gentime);
    }

    /**
     * культурно показывает ошибки на экран
     *
     * @param string $msg - заглавие ошибки
     * @param string $descr - подробности ошибки
     */
    public static function showError($msg, $descr = " ")
    {
        if (file_exists(baseDir . DIRECTORY_SEPARATOR . "theme" . DIRECTORY_SEPARATOR . "error.html")) {
            $content = file_get_contents(baseDir . DIRECTORY_SEPARATOR . "theme" . DIRECTORY_SEPARATOR . "error.html");
            $c = array( "|msg|" => $msg, "|msg_desc|" => $descr );
            foreach ($c as $key => $val) {
                $content = str_replace($key, $val, $content);
            }
            echo $content;
        }
        else {
            die($msg);
        }

    }

    /**
     * вывод ошибки по номеру на экран
     * @param \Exception | int $erNum номер ошибки
     */
    public function error($erNum)
    {
        $this->add_dict("errors");
        if($this->getVal('errTitle') === false)
            $this->set("title", "...");
        else
            $this->replace('errTitle','title');

        if (($erNum instanceof \Exception || $erNum instanceof \Throwable) && !empty(Configs::globalCfg('errorLevel')) && Configs::globalCfg('errorLevel') > 0) {
            $this->set('msg_desc', $this->getVal('err' . $erNum->getCode()).': '.$erNum->getMessage());
        }
        else {
            if($this->getVal('err'.$erNum) !== false)
                $this->replace("err" . $erNum, "msg_desc");
            else
                $this->set('msg_desc','Unknown error '.$erNum);
        }
        $this->out("error", "public");
    }

    /**
     * вывод ошибки с заданным текстом
     *
     * @param string $text
     */
    public function errortext($text)
    {
        $this->add_dict("errors")
            ->set("msg_desc", $text)
            ->replace('errTitle','title')
            ->out("error", "public");
    }

    /**
     * вывод на кран информации из эксепшена
     * @param \Exception $e
     * @param bool $isWriteLog писать ли лог?
     * @param string $sepatator резделитель по умолчанию для шаблонизатора
     */
    public static function errorException(\Exception $e, $sepatator = '|')
    {

        if (file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'error.html')) {
            $content = file_get_contents(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'error.html');

            if (!empty(Configs::globalCfg('errorLevel')) && Configs::globalCfg('errorLevel') > 0) {
                $c = array(
                    $sepatator . 'message' . $sepatator => $e->getMessage(),
                    $sepatator . 'line' . $sepatator => $e->getLine(),
                    $sepatator . 'code' . $sepatator => $e->getCode(),
                    $sepatator . 'file' . $sepatator => $e->getFile(),
                    $sepatator . 'trace' . $sepatator => $e->getTraceAsString()
                );
            }
            else {
                $c = array(
                    $sepatator . 'message' . $sepatator => 'Something went wrong. Maybe page not found or maybe you should contact with administrator...',
                    $sepatator . 'line' . $sepatator => '',
                    $sepatator . 'code' . $sepatator => '',
                    $sepatator . 'file' . $sepatator => '',
                    $sepatator . 'trace' . $sepatator => ''
                );
            }


            $c[$sepatator . 'site' . $sepatator] = '/';


            foreach ($c as $key => $val) {
                $content = str_replace($key, $val, $content);
            }
            echo $content;
        }
        else {
            die($e->getMessage());
        }
    }

    /**
     * @param string $msg
     * @param bool $showTime
     * @param string $timeFormat
     */
    public static function cmdMessage($msg,$showTime = false,$timeFormat = 'H:i:s d-m-Y'){

        print ($showTime ? '['.date($timeFormat).']' : '').' '.$msg.chr(10).chr(13);
    }

    //region magic
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'address':
            case 'adr':
                return $this->adr;
            case 'theme':
                return $this->themName;
            case 'dictionary':
                return $this->vars;
            case 'lang':
                return $this->clang;
            case 'idic':
                return $this->adedDic;
            default:
                return false;
        }
    }

    public function __set($name, $value){ }

    public function __isset($name)
    {
        switch (strtolower($name)) {
            case 'address':
            case 'adr':
                if (!empty($this->adr)) {
                    return true;
                }
                return false;
            case 'theme':
                if (!empty($this->themName)) {
                    return true;
                }
                return false;
            case 'lang':
                if (!empty($this->clang)) {
                    return true;
                }
                return false;
            default:
                return false;
        }
    }
    //endregion
}