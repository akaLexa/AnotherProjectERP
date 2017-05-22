<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 22.05.2017
 * справочник работников
 **/
namespace build\erp\user;
use build\erp\inc\eController;
use build\erp\inc\UserGroupList;
use build\erp\inc\UserRoleList;
use build\erp\user\m\mUserHB;
use mwce\Tools\html;


class UserHB extends eController
{
    protected $postField = array(
        'curGroupList'=>['type'=>self::INT],
        'curRoleList'=>['type'=>self::INT],
    );

    public function __construct(\mwce\Tools\Content $view, $pages)
    {
        parent::__construct($view, $pages);
        $this->configs['allowMailGrp'] = (!empty($this->configs['allowMailGrp']) ? explode(',',$this->configs['allowMailGrp']) : []);
        $this->configs['allowPhoneGrp'] = (!empty($this->configs['allowMailGrp']) ? explode(',',$this->configs['allowPhoneGrp']) : []);
    }

    public function actionIndex()
    {
        $groups = UserGroupList::getSelectList();
        $groups[0] = '...';
        unset($groups[4],$groups[2]);

        $roles = UserRoleList::getSelectList();
        $roles[0] = '...';
        unset($roles[2]);

        $this->view
            ->set('groupList',html::select($groups,'curGroupList',0,['class'=>'form-control inlineBlock','style'=>'width:100%;','onChange'=>'filter();']))
            ->set('roleList',html::select($roles,'curRoleList',0,['class'=>'form-control inlineBlock','style'=>'width:100%;','onChange'=>'filter();']))
            ->out('main',$this->className);
    }

    public function actionGetList(){
        $params = [];

        if(!empty($_POST)){
            foreach ($_POST as $id=>$value){
                if(!empty($value))
                    $params[$id] = $value;
            }
        }

        $list = mUserHB::getModels($params);
        if(!empty($list)){
            foreach ($list as $item) {
                $this->view
                    ->add_dict($item)
                    ->out('center',$this->className);
            }
        }
        else{
            $this->view->out('centerEmpty',$this->className);
        }
    }
}