<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * Date: 30.04.2017
 * интерфейс, гарантрующий, что есть списки для настроек
 */

namespace erp\inc\interfaces;


interface iConfigurable
{
    /**
     * массив для генерации выпадающего списка
     * [
     *  [1] => позиция 1
     *  [2] => позиция 2
     * ]
     * @return array
     */
    public static function getSelectList();

    /**
     * массив для генерации списка, где можно
     * выбрать несколько значений
     * [
     *   [0]=>[1,'Позиция 1'],
     *   [1]=>[2,'Позиция 2'],
     * ]
     * @return mixed
     */
    public static function getMultiSelectList();

}