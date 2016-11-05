<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 03.11.2016
 *
 **/
namespace mwce;

interface iStartable
{
    /**
     * инициализация класса
     * @param null|array|mixed $params
     * @return mixed
     */
    public static function start($params = null);

    /**
     * список зарегистрированных страниц с доступами
     * @return array
     */
    public function getPages();

    /**
     * список зарегистрированных Плагинов
     * @return array
     */
    public function getPlugins();
}