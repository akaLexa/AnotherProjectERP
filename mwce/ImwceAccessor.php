<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * Date: 25.11.2016
 */

namespace mwce;


interface ImwceAccessor
{
    /**
     * список с данными модулей
     * @return array|bool
     */
    public function getPages();

    /**
     * список с данными плагинов
     * @return array|bool
     */
    public function getPlugins();

    /**
     * данные модуля по названию
     * @param string $page
     * @return array|bool
     */
    public function getCurPage($page);

    /**
     * данные плагины по названию
     * @param string $plugin
     * @return array|bool
     */
    public function getCurPlugin($plugin);


    /**
     * @param string $page
     * @param string $acton
     * @param int $group
     * @param int $uid
     * @param string $defController
     * @return \Exception|void
     */
    public function renderPage($page,$acton,$group,$uid,$defController);

    /**
     * @param int $group
     * @param int $uid
     */
    public function renderPlugin($group,$uid);
}