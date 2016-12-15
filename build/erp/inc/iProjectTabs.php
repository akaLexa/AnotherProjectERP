<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.12.2016
 * интерфейс для вкладок
 **/

namespace build\erp\inc;

/**
 * Interface iProjectTabs
 * @package build\erp\inc
 * определяет основное поведение для вкладок в проекте
 */
interface iProjectTabs
{
    /**
     * главный вид по умолчанию
     * @return void
     */
    public function In();

    /**
     * настройки для модуля
     * @return array
     */
    public function getProperties();

    /**
     * выполнение каких-либо функций
     * @param string $action название метода
     * @param array $params параметры
     * @return mixed
     */
    public function exec($action,$params);
}