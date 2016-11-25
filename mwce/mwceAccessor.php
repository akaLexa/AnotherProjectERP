<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 25.11.2016
 *
 **/
namespace mwce;

abstract class mwceAccessor implements ImwceAccessor
{
    /**
     * @var array
     */
    protected $pages = [];

    /**
     * @var array
     */
    protected $plugins = [];

    /**
     * @var content
     */
    protected $view;

    /**
     * @var Connect
     */
    protected $db;

    /**
     * mwceAccessor constructor.
     * @param content $view
     * @param int $conNum
     */
    public function __construct(content $view,$conNum = 0)
    {
        $this->db = Connect::start($conNum);
        $this->view = $view;
    }

    /**
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * @param string $page
     * @return bool|array
     */
    public function getCurPage($page)
    {
        if(!empty($this->pages[$page])){
            return $this->pages[$page];
        }

        return false;
    }

    /**
     * @param string $plugin
     * @return bool|array
     */
    public function getCurPlugin($plugin)
    {
        if(!empty($this->plugins[$plugin])){
            return $this->plugins[$plugin];
        }

        return false;
    }
}