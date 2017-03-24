<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 09.04.2016
 *
 **/
namespace mwce\Controllers;
use mwce\Tools\content;
use mwce\Tools\Date;
use mwce\Tools\Logs;
use mwce\Tools\Tools;

class Controller
{
    /**
     * @var Tools
     * инстанс класса модели
     */
    protected $model;

    /**
     * @var content
     * инстанс класса шаблонизатора
     */
    protected $view;

    /**
     * @var array
     * поля для валидации из POST - массива
     */
    protected $postField;

    /**
     * @var array
     * поля для валидации из GET - массива
     */
    protected $getField;

    /**
     * @var bool
     * проверять или нет пост и гет массивы
     */
    protected $needValid = true;

    /**
     * @var array
     * конфигурации к модулю, если есть
     */
    protected $configs = array();

    /**
     * @var bool
     * использование логов при ошибках в валидации
     */
    protected $useLogs = true;

    /**
     * @var string
     * название класса без неймспейсов
     */
    protected $className;

    //region определение констант типов для валидации
    const INT = "int";
    const FLOAT = "float";
    const STR = "str";
    const NOVALID = 'not';
    const BOOL = "bool";
    const _ARRAY = "array";
    const DATE = "date";
    const DATETIME = "datetime";
    //endregion

    /**
     * реальный вызов экшена
     * @param $action string
     */
    public function action($action)
    {
        $this->init();
        $this->$action();
        $this->callback();
    }

    /**
     * показывает ошибку по номеру
     * @param int $er номер ошибки
     */
    public function showError($er = 2)
    {
        $this->view->error($er);
    }

    /**
     * метод, что запускается срау после констуктора
     */
    public function init()
    {
    }

    /**
     * метод, запускается после экшена
     */
    public function callback()
    {
    }

    /**
     * метод по умолчанию
     */
    public function actionIndex()
    {
    }

    /**
     * показать ошибку по тексту
     * @param string $text
     */
    public function showErrorText($text)
    {
        $this->view->errortext($text);
    }

    /**
     * GET массив. валидация
     */
    protected function clearGet()
    {
        if (empty($_GET)) {
            return;
        }

        $ai = new \ArrayIterator($_GET);
        foreach ($ai as $id => $v) {

            if (empty(trim($_GET[$id]))) {
                unset($_GET[$id]);
                continue;
            }

            if (!empty($GLOBALS["get_" . $id . "_v"]) && $GLOBALS["get_" . $id . "_v"] == true) {
                continue;
            }

            $v = trim(htmlspecialchars($v, ENT_QUOTES));
            $v = preg_replace("/(\&lt\;br \/\&gt\;)|(\&lt\;br\&gt\;)/", ' <br /> ', $v);

            if ($_GET[$id] != $v && $this->useLogs) {
                Logs::log(7, "GET -> {$_GET[$id]} != {$v}");
            }
            $_GET[$id] = $v;
            $GLOBALS["get_" . $id . "_v"] = true;
        }
    }

    /**
     * POST массив. валидация
     */
    protected function clearPost()
    {
        if (empty($_POST)) {
            return;
        }
        $ai = new \ArrayIterator($_POST);
        foreach ($ai as $id => $v) {
            if (!empty($GLOBALS["post_" . $id . "_v"]) && $GLOBALS["post_" . $id . "_v"] == true) {
                continue;
            }

            if (empty(trim($_POST[$id]))) {
                unset($_POST[$id]);
                continue;
            }

            if (is_array($v))
                continue;

            $v = trim(htmlspecialchars(self::checkText($v), ENT_QUOTES));

            if (function_exists("get_magic_quotes_gpc")) {
                if (get_magic_quotes_gpc()) {
                    $v = stripslashes($v);
                }
                $v = str_replace('`', '&quot;', $v);
            }

            if (trim($_POST[$id]) != $v && $this->useLogs) {
                Logs::log(7, "POST -> {$_POST[$id]} != {$v}");
            }

            $_POST[$id] = $v;
            $GLOBALS["post_" . $id . "_v"] = true;
        }
    }

    /**
     * возврат исходного текста после htmlspecialchars
     * почти аналог htmspecialchars_decode
     * @param string $str
     * @return string
     * @deprecated
     */
    static protected function decode($str)
    {
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        $ret = strtr($str, $trans_tbl);
        return preg_replace("/scri/", '', $ret);
    }

    /**
     * снятие последствий htmlspecialchars для ссылок
     *
     * @param $link
     * @return string
     */
    static public function linkDec($link)
    {
        return str_replace("&amp;", "&", $link);
    }

    /**
     * проверка текста на сюрпризы со вложенными тегами
     *
     * @param string $text
     * @return string
     */
    static public function checkText($text)
    {
        return preg_replace("!<script[^>]*>|</script>|<(\s{0,})iframe(\s{0,})>|</(\s{0,})iframe(\s{0,})>!isU", '!removed bad word!', $text);
    }

    /**
     * фильтр пост массива, если не пустой $postField
     */
    protected function customPostValid()
    {
        if (!empty($_POST)) {
            $ai = new \ArrayIterator($_POST);
            foreach ($ai as $id => $val) {
                if (!empty($GLOBALS["post_" . $id . "_v"]) && $GLOBALS["post_" . $id . "_v"] == true) {
                    continue;
                }

                if (!empty($this->postField[$id])) {
                    $val = trim($val);

                    if ($val == '') {
                        unset($_POST[$id]);
                        continue;
                    }

                    if (!empty($this->postField[$id]["type"])) {
                        $type = $this->postField[$id]["type"];
                    }
                    else {
                        $type = gettype($val);
                    }

                    if (!empty($this->postField[$id]["maxLength"])) {
                        $val = substr($val, 0, (int)$this->postField[$id]["maxLength"]);
                    }

                    $val = self::paramsControl($val, $type);

                    if (function_exists("get_magic_quotes_gpc")) {
                        if (function_exists("stripslashes")) {
                            if (get_magic_quotes_gpc()) {
                                $val = stripslashes($val);
                            }
                        }

                        $val = str_replace('`', '&quot;', $val);
                    }

                    if ($_POST[$id] != $val && $this->useLogs) {
                        Logs::log(7, "-> POST[$id] = $val");
                    }

                    $_POST[$id] = $val;
                    $GLOBALS["post_" . $id . "_v"] = true;
                }
            }
        }
    }

    /**
     * фильтр пост массива, если не пустой $getField
     */
    protected function customGetValid()
    {
        if (!empty($_GET)) {
            $ai = new \ArrayIterator($_GET);
            foreach ($ai as $id => $val) {
                if (!empty($GLOBALS["get_" . $id . "_v"]) && $GLOBALS["get_" . $id . "_v"] == true) {
                    continue;
                }

                if ($val == '') {
                    unset($_GET[$id]);
                    continue;
                }

                if (!empty($this->getField[$id])) {
                    $val = trim($val);

                    if (!empty($this->getField[$id]["type"])) {
                        $type = $this->getField[$id]["type"];
                    }
                    else {
                        $type = gettype($val);
                    }

                    if (!empty($this->getField[$id]["maxLength"])) {
                        $val = substr($val, 0, (int)$this->getField[$id]["maxLength"]);
                    }

                    $val = self::paramsControl($val, $type);

                    if (function_exists("get_magic_quotes_gpc")) {
                        if (function_exists("stripslashes")) {
                            if (get_magic_quotes_gpc()) {
                                $val = stripslashes($val);
                            }
                        }

                        $val = str_replace('`', '&quot;', $val);
                    }

                    $val = preg_replace("/(\&lt\;br \/\&gt\;)|(\&lt\;br\&gt\;)/", ' <br /> ', $val);

                    if ($_GET[$id] != $val && $this->useLogs) {
                        Logs::log(7, "-> GET[$id] = $val");
                    }

                    $_GET[$id] = $val;
                    $GLOBALS["get_" . $id . "_v"] = true;
                }
            }
        }
    }

    /**
     * приведение типов по парамету
     * @param number|string $param
     * @param string $type
     * @return bool|float|int|string
     */
    protected function paramsControl($param, $type)
    {
        switch ($type) {
            case self::FLOAT:
            case "double":
            case "float":
                $param = trim($param);
                if (strlen($param) < 1) {
                    $param = NULL;
                }
                else {
                    $param = floatval($param);
                }
                break;
            case "int":
            case self::INT:
            case "integer":
                $param = trim($param);
                if (strlen($param) < 1) {
                    $param = NULL;
                }
                else {
                    $param = (int)$param;
                }
                break;
            case "str":
            case self::STR:
            case "string":
                $param = htmlspecialchars(self::checkText($param), ENT_QUOTES);
                break;
            case "bool":
            case self::BOOL:
            case "boolean":
                $param = (bool)$param;
                break;
            case "array":
            case self::_ARRAY:
                break;
            case "date":
            case self::DATE:
                $param = Date::intransDate($param);
                if ($param == '-/-') {
                    $param = '';
                }
                break;
            case "datetime":
            case self::DATETIME:
                $param = Date::intransDate($param, true);
                if ($param == '-/-') {
                    $param = '';
                }
                break;
            case self::NOVALID:
                /*nop*/
                break;
            default:
                $param = htmlspecialchars(self::checkText($param), ENT_QUOTES);
                break;
        }

        return $param;
    }


    /**
     * ловим не существующие методы
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        $this->actionIndex();
        if (trim(strtolower($name)) != 'actonIndex') {
            Logs::log(3, static::class . " hasn't action $name");
        }
    }
}