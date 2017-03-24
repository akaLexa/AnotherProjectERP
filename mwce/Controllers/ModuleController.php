<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.04.2016
 *
 **/
namespace mwce\Controllers;

use mwce\db\Connect;
use mwce\Tools\Configs;
use mwce\Tools\Content;
use mwce\Tools\Logs;

class ModuleController extends Controller
{
    /**
     * массив со всеми страницами
     * @var array
     */
    protected $pages;

    /**
     * @var int  показывать ли полное окно или только кусок модуля
     */
    protected $showMain = 1;


    /**
     * Controller constructor.
     * @param Content $view
     * @param string $pages
     */
    public function __construct(\mwce\Tools\Content $view, $pages)
    {
        $this->view = $view;
        $this->pages = $pages;

        $build = Configs::currentBuild();

        if (!empty($_SESSION["whosconfig"]))
            $build = $_SESSION["whosconfig"];
        
        $this->className = basename(static::class);
        if($this->className == static::class)
        {
            $t = explode('\\',static::class);
            $this->className = end($t);
        }

        if (!empty($build))
            $this->configs = Configs::readCfg($this->className, $build); //подгружаем конфиги модуля сразу

        self::validate();
    }

    /**
     * метод по умолчанию
     * возвращает на экран название страницы
     */
    public function actionTitle()
    {
        $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $_SESSION['mwclang'] . DIRECTORY_SEPARATOR . 'titles.php';

        if (file_exists($path)) {
            $lang = include $path;
            if (!empty($lang[$this->pages[$this->className]['title']]))
                echo $lang[$this->pages[$this->className]['title']];
        }
    }

    /**
     * эмуляция не ооп работы модуля
     *
     * @param string $mpath где модуль
     */
    public function genNonMVC($mpath)
    {
        $modulename = basename($mpath, ".php");
        $this->view->showOnly(true);

        if (!empty($this->pages[$modulename]["title"])) // полезно для кеширования
        {
            $this->view->replace($this->pages[$modulename]["title"], "title");
        }

        if ($this->isCached(__FUNCTION__, $modulename)) //кешик
            return;

        try {
            $muuser = $this->model;
            $content = $this->view;
            $page = $this;
            $db = Connect::start();

            ob_start();
            require_once $mpath;
            $cnt = ob_get_contents();
            ob_end_clean();

            if (!empty($cnt))
                $this->view->setFromCache($cnt);
            $this->view->showOnly(false);
        }
        catch (\Exception $e) {
            echo $e->getMessage();
            Logs::log($e);
            $this->view->showOnly(false);
        }

        if ($this->cacheNeed($modulename)) //если нужен кеш
            $this->doCache($modulename . "_" . __FUNCTION__);
    }

    /**
     * узнать настройки данного модуля
     *
     * @return bool|array
     */
    public function getPProperties($name = NULL)
    {
        if (is_null($name))
            $name = $this->className;

        if (!empty($this->pages[$name]))
            return $this->pages[$name];
        return false;
    }

    /**
     * возвращает разницу времени создания файла и текущего
     *
     * @return int
     */
    protected function cacheDif($fname)
    {
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $this->view->cLAng() . "_$fname";

        if (file_exists($path)) {
            return time() - filemtime($path);
        }
        else
            return 0;
    }

    /**
     * удаляем файлик кеша
     * @param string $fname название файлика(функции)
     */
    protected function cacheDelete($fname)
    {
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $this->view->cLAng() . "_$fname";

        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Определяет, нужно ли кешировать файл или уже есть кешик
     * @param string|null $name название модуля
     * @return bool
     */
    protected function cacheNeed($name = null)
    {
        if (is_null($name))
            $name = $this->className;

        if ($this->pages[$name]["caching"] > 0)
            return true;
        return false;
    }

    /**
     * возвращает закешированный модуль иначе, пустую строку
     * @param string $fname название функции
     * @return string
     */
    protected function cacheGive($fname)
    {
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $this->view->cLAng() . "_$fname";

        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return "";
    }

    /**
     * пишем кеш
     *
     * @param string $fname
     * @param string $content
     */
    protected function cacheWrite($fname, $content)
    {
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $this->view->cLAng() . "_$fname";
        $h = fopen($path, "w");
        fwrite($h, $content);
        fclose($h);
    }

    /**
     * функция подхвата кешироваиия вернет true в случае, если есть актуальная копия в кеше
     *
     * @param string $fname - название экшена
     * @param string|null $name название модуля
     * @return bool
     */
    protected function isCached($fname, $name = null)
    {
        $prop = $this->getPProperties($name);

        if (!is_null($name))
            $fname = $name . "_" . $fname;

        if ($this->cacheNeed($name) && $this->cacheDif($fname) <= $prop["caching"]) //если модуль кешируется и кеш еще актуален, вместо работы модуля берем кеш
        {
            $cache = $this->cacheGive($fname);
            if (empty($cache))
                return false;

            $this->view->setFromCache($this->cacheGive($fname)); //суем в контейнер данные
            return true;
        }
        return false;
    }

    /**
     * пишем кеш для экшена
     *
     * @param string $fname - экшен
     */
    protected function doCache($fname)
    {
        $cache = $this->view->getContainer();

        if (!empty($cache))
            $this->cacheWrite($fname, $cache); //пишем кеш
    }

    /**
     * фиильтрация данных
     */
    protected function validate()
    {
        if (!$this->needValid) //если выставлен флаг, что не надо валидации, значит, не надо валидации :)
        {
            return;
        }

        if (empty($this->postField))
            self::clearPost();
        else
            self::customPostValid();

        if (empty($this->getField))
            self::clearGet();
        else
            self::customGetValid();
    }
}