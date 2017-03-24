<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * Date: 25.11.2016
 */

namespace mwce\Interfaces;


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
     * @param string $page название модуля страницы
     * @param string $acton
     * @param int $group группа
     * @param int $role роль
     * @param int $uid
     * @param string $defController
     * @return \Exception|void
     */
    public function renderPage($page,$acton,$group,$role,$uid,$defController);

    /**
     * @param int $group
     * @param int $role
     * @param int $uid
     */
    public function renderPlugin($group,$role,$uid);
}