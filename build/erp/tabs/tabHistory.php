<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.01.2017
 *
 **/
namespace build\erp\tabs;
use build\erp\inc\AprojectTabs;
use build\erp\tabs\m\mTabHistory;

class tabHistory extends AprojectTabs
{

    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params = null)
    {
        $list = mTabHistory::getModels(['col_projectID'=>$this->project['col_projectID']]);
        if(!empty($list)){
            $ai = new \ArrayIterator($list);
            $curStage = 0;

            foreach ($ai as $item) {

                $item['dateStart'] = empty($item['col_dateStart']) ? $item['col_dateStartPlanLegend'] : $item['col_dateStartLegend'];
                $item['dateEnd'] = empty($item['col_dateEndFact']) ? $item['col_dateEndPlanLegend'] : $item['col_dateEndFactLegend'];

                if(strtotime($item['dateEnd'])<time())
                    $this->view->set('oldDateRed','color:red;');
                else
                    $this->view->set('oldDateRed','');

                $this->view->add_dict($item);

                if($curStage != $item['col_pstageID'])
                {
                    if(empty($item['col_dateStartPlan']))
                        $this->view->set('isNotPlan','opacity: 0.4');
                    else
                        $this->view->set('isNotPlan','');

                    $curStage = $item['col_pstageID'];
                    $this->view->out('centerStage',$this->className);
                }



                if(!empty($item['col_taskName'])){

                    if(empty($item['col_taskStartPlan']))
                        $this->view->set('isNotPlan','opacity: 0.4');
                    else
                        $this->view->set('isNotPlan','');

                    if(!empty($item['col_nextID']))
                        $this->view->set('isfirstTask','display:none;');
                    else
                        $this->view->set('isfirstTask','');

                    if(empty($item['col_taskStartPlan'])){
                        $this->view
                            ->set('hintPref', ' не из плана!')
                            ->set('taskStyle','color:red;')
                            ->set('isfirstTask','display:none;')
                        ;
                    }
                    else{
                        $this->view
                            ->set('hintPref', '')
                            ->set('taskStyle','')
                        ;
                    }

                    if(strtotime($item['col_taskEnd'])<time())
                        $this->view->set('oldDateRed','color:red;');
                    else
                        $this->view->set('oldDateRed','');

                    $this->view->out('centerTask',$this->className);
                }
            }
            $this->view->setFContainer('historyProject',true);
        }
        $this->view->out('main',$this->className);
    }
}