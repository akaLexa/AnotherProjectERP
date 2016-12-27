<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 27.12.2016
 *
 **/
namespace build\erp\tabs;
use build\erp\inc\AprojectTabs;
use build\erp\tabs\m\mTabMessages;

class tabEvents extends AprojectTabs
{

    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params = null)
    {
        self::getList();
        $this->view
            ->setFContainer('messageTabContent',true)
            ->out('main',$this->className);
    }

    public function getList(){
        if(!empty($_GET['id'])){
            $list = mTabMessages::getModels(['projectID'=>$_GET['id'],'isSys'=>1]);
            if(!empty($list)){
                foreach ($list as $item) {
                    $this->view
                        ->add_dict($item)
                        ->set('curuserImg',2)
                        ->out('message',$this->className);
                }

            }
        }
    }
}