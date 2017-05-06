<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * Date: 30.04.2017
 * интерфейс, гарантрующий, что есть списки для настроек
 */

namespace build\erp\inc\interfaces;


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
     *   [0]=>['id' => 1,'item' => 'Позиция 1'],
     *   [1]=>['id' => 2,'item' => 'Позиция 2'],
     * ]
     * @return mixed
     */
    public static function getMultiSelectList();

}