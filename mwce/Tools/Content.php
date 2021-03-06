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
     * @var array
     * разделитель 0 - левый 1 - правый
     */
    private $separator = [];

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
     * подключенные скрипты css/js/ и т.д.
     */
    private $attScripts = [];

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
     * @var array
     * отрезки по тегам из общего шаблона
     */
    private $segments = [];
    /**
     * @var array
     * 0 => folder 1 => name
     */
    private $curTemplate = [];

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
     * @throws /Exception
     */
    public function __construct($adr, $theme, $lang, $separator = ['|','|'] )
    {
        $this->clang = $lang;
        $this->themName = $theme;
        $this->adr = $adr;
        $this->separator = $separator;

        $this->vars['baseVals'] = array(
            $this->separator[0] . 'site' . $this->separator[1] => $this->adr,
            $this->separator[0] . 'theme' . $this->separator[1] => $this->themName,
            $this->separator[0] . 'global_js' . $this->separator[1] => '',
            $this->separator[0] . 'global_css' . $this->separator[1] => '',
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
        if(file_exists($path))
            return file_get_contents($path);
        return '';
    }

    /**
     * анализ шаблона
     * @param string $tplName
     * @param string $folder
     * @return Content $this
     */
    public function parseTemplate($tplName,$folder){
        if(!empty($this->curModule)){
            $module = $this->curModule;
        }
        else{
            $module = 'commonStack';
        }

        $this->curTemplate = [$folder,$tplName];

        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $tplName . '.html';
        if(file_exists($path)){
            $tpl = self::gContent($path);
            preg_match_all("/(".preg_quote($this->separator[0]).")+(\/)?(\w){1,}(".preg_quote($this->separator[1]).")+/", $tpl, $matches);
            if(!empty($matches[0])){
                $tagAreas = [];
                $i =0;
                foreach ($matches[0] as $_id=>$keys){
                    $i++;
                    $j=0;
                    $keys = str_replace('|','',$keys);
                    foreach ($matches[0] as $__id => $subKeys){
                        $j++;

                        $subKeys = str_replace($this->separator,'',$subKeys);
                        if ($subKeys == '/'.$keys){
                            $tagAreas[] = $keys;
                            unset($matches[0][$__id],$matches[0][$_id]);
                            break;
                        }
                    }
                }

                if(!empty($tagAreas)){
                    foreach ($tagAreas as $tag){
                        preg_match("#". preg_quote($this->separator[0].$tag.$this->separator[1]).'(.+?)'.preg_quote($this->separator[0].'/'.$tag.$this->separator[1]).'#s', $tpl, $matches);

                        if(!empty($matches)){
                            $this->segments[$module][$tag] = $matches;
                        }
                    }
                }
            }
        }

        return $this;
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
        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $content . '.html';

        if (file_exists($path)) {
            $content = self::gContent($path);
        }
        else {
            return $this;
        }

        $this->_loop($tag,$data,$content);

        return $this;
    }

    private function _loop($tag,$data,$content){
        preg_match_all('#' . preg_quote($this->separator[0] . $tag . $this->separator[1]) . '(.+?)' . preg_quote($this->separator[0] . '/' . $tag . $this->separator[1]) . '#s', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $str = '';
            foreach ($data as $row) {

                $temp = array();
                foreach ($row as $key => $val) {
                    if (is_array($val) || is_object($val)) {
                        $this->_loop($key, $val, $match[1]);
                        continue;
                    }

                    $temp[$this->separator[0] . $key . $this->separator[1]] = $val;
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
     * @param string $tag
     * @param array $data
     * @return Content $this
     */
    public function loopInSegment($tag,$data){
        if(!empty($this->curTemplate)) {

            if(!empty($this->curModule)){
                $module = $this->curModule;
            }
            else{
                $module = 'commonStack';
            }

            if (!empty($this->segments[$module][$tag])) {
                $this->_loop($tag, $data, $this->segments[$module][$tag][0]);
            }
        }
        return $this;
    }

    /**
     * вывод на экран только сегмента под тегами $segment
     * @param string $segment
     */
    public function outSegment($segment){
        if(!empty($this->curTemplate)) {

            if(!empty($this->curModule)){
                $module = $this->curModule;
            }
            else{
                $module = 'commonStack';
            }
            if (!empty($this->segments[$module][$segment])) {
                $content = $this->segments[$module][$segment][0];
                if (!empty($this->curModule) && !empty($this->vars[$this->curModule]) && is_array($this->vars[$this->curModule])) {
                    $content = strtr($content, $this->vars[$this->curModule]);
                }

                $ars = [];
                $ai = new \ArrayIterator($this->vars);
                foreach ($ai as $id => $val) {
                    if (!is_array($val)) {
                        $ars[$id] = $val;
                    }
                }

                $content = strtr($content, $ars);
                $content = strtr($content, $this->vars['baseVals']);

                if (!empty($this->segments)) {
                    if (!empty($this->curModule)) {
                        $module = $this->curModule;
                    }
                    else {
                        $module = 'commonStack';
                    }

                    if (!empty($this->segments[$module])) {
                        $tags = array_keys($this->segments[$module]);

                        foreach ($tags as $tag) {
                            $content = str_replace($this->segments[$module][$tag][0], '', $content);
                            unset($this->segments[$module][$tag]);
                        }
                    }
                }

                $content = preg_replace("/[" . $this->separator[0] . "]+[A-Za-z0-9_]{1,25}[" . $this->separator[1] . "]+/", ' ', $content);

                $this->container .= $content;
            }
        }
    }

    /**
     * выставить имя текущего контейнера
     * @param string $name
     * @return Content
     * @throws \mwce\Exceptions\ContentException
     * @throws /Exception
     */
    public function setName($name)
    {
        if (in_array($name, $this->deniedArray, true)) {
            throw new ContentException(" you can't use $name for object name");
        }

        $this->curModule = $name;

        if(!empty($this->adedDic[$name])){
            unset($this->adedDic[$name]);
        }

        $this->add_dict($name);

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
        $id = $this->separator[0] . $id . $this->separator[1];

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
     * @param  array|string $file - название файла "словаря"
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
                    $this->vars[$this->curModule][$this->separator[0] . $d . $this->separator[1]] = $v;
                }
                else {
                    $this->vars[$this->separator[0] . $d . $this->separator[1]] = $v;
                }
            }

        }
        else {

            $lang = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $this->clang . DIRECTORY_SEPARATOR . $file . '.php');

            if(!empty($lang)){

                if (!empty($this->adedDic[$file])) // если словарь уже подключен, второй раз лопатить смысла нет
                    return $this;

                if (is_array($lang)) {

                    foreach ($lang as $d => $v) {
                        if (!empty($this->curModule)) {
                            $this->vars[$this->curModule][$this->separator[0] . $d . $this->separator[1]] = $v;
                        }
                        else {
                            $this->vars[$this->separator[0] . $d . $this->separator[1]] = $v;
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
    public function curLang()
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
                $this->vars[$this->curModule][$this->separator[0] . $name . $this->separator[1]] = $val;
            }
            else {
                $this->vars[$this->separator[0] . $name . $this->separator[1]] = $val;
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
            $this->vars[$this->curModule][$this->separator[0] . $name . $this->separator[1]] = '';
        }
        else {
            $this->vars[$this->separator[0] . $name . $this->separator[1]] = '';
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
            && !empty($this->vars[$this->curModule][$this->separator[0] . $what . $this->separator[1]])
        ) {
            $this->set($where, $this->vars[$this->curModule][$this->separator[0] . $what . $this->separator[1]]);
        }
        else if (!empty($this->vars[$this->separator[0] . $what . $this->separator[1]])) {
            $this->set($where, $this->vars[$this->separator[0] . $what . $this->separator[1]]);
        }

        return $this;
    }

    /**
     * Функция очищаент контенер
     */
    public function clearContainer()
    {
        $this->container = '';
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
     * @param string $folder - папка под группу файлов (обычно для модуля)
     * @return mixed|string
     */
    public function out($tpl, $folder = '')
    {
        if (empty($folder)) {
            $folder = 'public';
        }

        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $tpl . '.html';

        if (file_exists($path)) {

            $this->loadScripts('js' . DIRECTORY_SEPARATOR,$folder.'.'.$tpl.'.js');
            $this->loadScripts('html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR,$folder.'.'.$tpl.'.js');

            $this->loadScripts('css' . DIRECTORY_SEPARATOR,$folder.'.'.$tpl.'.css',2);
            $this->loadScripts('html' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR,$folder.'.'.$tpl.'.css',2);

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
            //endregion

            //region clean unused tags

            if(empty($this->segments)){
                $this->parseTemplate($tpl,$folder);
            }

            if(!empty($this->segments)) {
                if (!empty($this->curModule)) {
                    $module = $this->curModule;
                }
                else {
                    $module = 'commonStack';
                }

                if (!empty($this->segments[$module])) {
                    $tags = array_keys($this->segments[$module]);

                    foreach ($tags as $tag) {
                        $content = str_replace ( $this->segments[$module][$tag][0] , '', $content);
                        unset($this->segments[$module][$tag]);
                    }
                }
            }

            $content = preg_replace("/[".$this->separator[0]."]+[A-Za-z0-9_]{1,25}[".$this->separator[1]."]+/", ' ', $content);
            //endregion

            if ($this->notWrite == 0) //если собираем
            {
                $this->container .= $content;
            }
            else {
                echo $content;
            }

            return $content;
        }

        $this->errortext("file \"$path\" doesn't exists");

        return '';
    }

    /**
     * отображает на экране только массив, преобраозованный в JSON
     * @param array $data
     */
    public function showJSON($data){
        $this->container = json_encode($data);
    }

    /**
     * подключить скрипты
     * @param string $address адрес до директории со скриптом (от дирректории с темой)
     * @param string $name название скрипта
     * @param int $type 1 = js, 2 = css
     */
    public function loadScripts($address,$name,$type = 1){

        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $this->themName . DIRECTORY_SEPARATOR . $address . $name;
        if (file_exists($path)) {

            $script = strtr(trim(file_get_contents($path)), $this->vars['baseVals']);

            if (!empty($script)) {
                if (!empty($this->curModule)) {
                    $script = strtr($script, $this->vars[$this->curModule]);
                }

                if ($type == 1) {
                    $this->vars['baseVals'][$this->separator[0] . 'global_js' . $this->separator[1]] .= "\r\n/* injected script */\r\n" . $script;
                }
                else {
                    $this->vars['baseVals'][$this->separator[0] . 'global_css' . $this->separator[1]] .= "\r\n/* injected script */\r\n" . $script;
                }

                $this->attScripts[$name] = 1;
            }
        }
    }

    /**
     * глобальный вывод на экран
     *
     * @param string $args - зарезервированное слово, в которое сольется весь накомпленный контейнер
     * @param string $tpl - файл шаблона, в который все будет сливаться
     * @param string $folder - папка
     */
    public function global_out($tpl, $folder = '', $args = 'page')
    {
        $this->setFContainer($args); //суем из контенера в переменную
        $this->showOnly(true);
        $this->out($tpl, $folder);
    }

    /**
     * культурно показывает ошибки на экран
     *
     * @param string $msg - заглавие ошибки
     * @param string $descr - подробности ошибки
     */
    public static function showError($msg, $descr = ' ')
    {
        if (file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'error.html')) {
            $content = file_get_contents(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'error.html');
            $c = array( '|msg|' => $msg, '|msg_desc|' => $descr );
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