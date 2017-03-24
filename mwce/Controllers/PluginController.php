<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 11.04.2016
 *
 **/

namespace mwce\Controllers;

use mwce\db\Connect;
use mwce\Tools\Configs;

class PluginController extends Controller
{
    /**
     * @var int
     */
    private $tick;
    /**
     * @var array
     */
    protected $plugins;
    /**
     * @var int показывать ли полное окно или только кусок модуля
     * (если кому-то приспичит аяксить мплагины, то это будет очень полезное свойство)
     */
    protected $showAll = 1;

    public function __construct(\mwce\Tools\content $view, $plugins)
    {
        $this->view = $view;
        $this->className = basename(static::class);
        if($this->className == static::class)
        {
            $t = explode('\\',static::class);
            $this->className = end($t);
        }

        $this->view
            ->add_dict("plugin_" . $this->className)
            ->add_dict("admin");

        $this->configs = Configs::readCfg('plugin_'.$this->className, Configs::currentBuild()); //подгружаем конфиги модуля сразу);

        $this->tick = microtime(); //для проверки времени генерации
        $this->plugins = $plugins;

        self::validate();
    }


    /**
     * отдает шаблонизатору сгенерированный контент
     * @param null|string $name
     */
    public function parentOut($name = null)
    {
        /*
         * суем данные с плагина в переменную, с его именем
         */
        if (is_null($name)) {
            $name = $this->className;
        }

        if ($this->showAll == 1)
            $this->view->setFContainer("plugin_" . $name, 1);
        else {
            echo $this->view->getContainer();
            die();
        }
    }

    /**
     * функция, что вызывается после вызова основного экшена
     */
    public function callback()
    {
        $this->view->emptyName();
    }

    /**
     * эмуляция не ооп работы модуля
     *
     * @param string $mpath где модуль
     */
    public function genNonMVC($mpath)
    {
        if ($this->isCached(__FUNCTION__, basename($mpath, ".php"))) //кешик
            return;

        try {
            $this->view->showOnly(true);
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
            $this->view->showOnly(false);
        }

        if ($this->cacheNeed(basename($mpath, ".php"))) //если нужен кеш
            $this->doCache(basename($mpath, ".php") . "_" . __FUNCTION__);

        if ($this->showAll != 1) {
            echo $this->view->getContainer();
            die();
        }
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

        if (!empty($this->plugins[$name]))
            return $this->plugins[$name];
        return false;
    }

    /**
     * возвращает разницу ремени создания файла и текущего
     *
     * @return int
     */
    protected function cacheDif($fname)
    {
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $this->view->cLAng() . "_plugin_$fname";

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
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $this->view->cLAng() . "_plugin_" . $this->className . "_$fname";

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

        if ($this->plugins[$name]["cache"] > 0)
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
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $this->view->cLAng() . "_plugin_$fname";

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
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . $this->view->cLAng() . "_plugin_$fname";
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

        if ($this->cacheDif($fname) <= $prop["cache"]) //если модуль кешируется и кеш еще актуален, вместо работы модуля берем кеш
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

        if (empty($cache))
            $cache = " ";

        $this->cacheWrite($fname, $cache); //пишем кеш
    }

    /**
     * фиильтрация данных
     */
    protected function validate()
    {
        if (!empty($this->postField))
            self::customPostValid();

        if (!empty($this->getField))
            self::customGetValid();
    }
}