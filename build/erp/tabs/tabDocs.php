<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.01.2017
 *
 **/
namespace build\erp\tabs;
use build\erp\inc\AprojectTabs;
use build\erp\tabs\m\mDocs;
use mwce\html_;
use mwce\router;

class tabDocs extends AprojectTabs
{

    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params = null)
    {
        $docs = mDocs::getDocGroups(router::getUserRole());
        $docs[0] ='Все';
        $this->view
            ->set('fileGroupList',html_::select($docs,'curChosenDg',0,'class="form-control inlineBlock"'))
            ->out('main',$this->className);
    }
}