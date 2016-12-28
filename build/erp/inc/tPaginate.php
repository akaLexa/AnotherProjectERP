<?php
/**
 * Date: 28.12.2016
 * Time: 12:50
 */

namespace build\erp\inc;


/**
 * Class tPaginate
 * @package build\erp\inc
 * функция для генерации пагинатора
 * следует запускать ДО того как генерируется контент в шаблон
 */
trait tPaginate
{
    /**
     * @param int $curPage текущая страница (с 1)
     * @param int $countPage кол-во страниц
     * @param int $round показывать отрезок
     * @return string html code
     */
    public function paginator($curPage,$countPage,$round){

        if($countPage <= 1)
            return '';

        $this->view->clearContainer();

        $begin = $curPage-$round;
        $end = $curPage+$round;

        if($begin < 1)
            $begin = 1;

        if($end >= $countPage)
            $end = $countPage;

        $pages = array();

        for ($i = $begin; $i<=$end;$i++){

            $pages[] = array(
                'pNum' => $i,
                'pNumLegend' => $i,
                'isActive' => $i == $curPage ? 'active' : '',
            );

        }

        if($end<$countPage){
            $pages[] = array(
                'pNum' => ($end + 1),
                'pNumLegend' => '...',
                'isActive' => '',
            );
        }


        $this->view
            ->loops('pageNumbers',$pages,'main','paginator')
            ->set('minCnt',1)
            ->set('maxCnt',$countPage);

        $return = $this->view->out('main','paginator',2);

        return $return;
    }

}