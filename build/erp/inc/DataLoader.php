<?php

namespace build\erp\inc;

use mwce\iStartable;
use mwce\traits\singleton;

/**
 * Class DataLoader
 * @package build\erp\inc
 * генерация страниц
 */
class DataLoader implements iStartable
{

    use singleton;


    /**
     * список зарегистрированных страниц с доступами
     * @return array
     */
    public function getPages()
    {
        return array(
            'MainPage' => ['title' => 'title_1','ppath' => 'main', 'caching' => '0', "ison" => '1', "isClass" => '1', "groups" => '2'],
            'UnitManager' => ['title' => 'title_2','ppath' => 'adm', 'caching' => '0', "ison" => '1', "isClass" => '1', "groups" => '2'],
        );
    }

    /**
     * список зарегистрированных Плагинов
     * @return array
     */
    public function getPlugins()
    {
        return [];
    }
}