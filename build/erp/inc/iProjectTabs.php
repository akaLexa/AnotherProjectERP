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
     * @param null $params
     * @return void
     */
    public function In($params=null);

    /**
     * настройки для модуля
     * @return array
     */
    public function getProperties();

}