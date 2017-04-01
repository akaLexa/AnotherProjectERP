<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 01.04.2017
 *
 **/
namespace build\erp\adm;

use build\erp\adm\m\mLogs;
use build\erp\inc\eController;
use mwce\Tools\Configs;
use mwce\Tools\Date;
use mwce\Tools\DicBuilder;
use mwce\Tools\html;
use mwce\Tools\Tools;

class Logs extends eController
{
    protected $postField = array(
        'choseError' => ['type' => self::INT],
        'dFrom' => ['type' => self::DATE],
        'dTo' => ['type' => self::DATE],
    );

    public function actionIndex()
    {
        $errorList = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'errors.php');
        unset($errorList['errTitle']);

        $_ =[];
        foreach ($errorList as $key=>$value){
            $_[substr($key,3)] = $value;
        }

        $errorList = $_;
        $errorList[0] ='...';

        $this->view
            ->set('dFrom', Date::intransDate('last monday'))
            ->set('dTo', Date::intransDate('next sunday'))
            ->set('errorList',html::select($errorList,'choseError',0,'class="form-control inlineBlock" style="width:200px;" onchange="logFilter();"'))
            ->out('main',$this->className);
    }

    public function actionCenter(){
        if(!empty($_POST['dFrom']) && !empty($_POST['dTo'])){
            $list = mLogs::getModels([
                'dFrom' => $_POST['dFrom'],
                'dTo' => $_POST['dTo'],
                'choseError' => (!empty($_POST['choseError']) ? $_POST['choseError'] : 0)
            ]);
            if(!empty($list)){
                foreach ($list as $item) {
                    $this->view
                        ->add_dict($item)
                        ->out('center',$this->className);
                }
            }
            else{
                $this->view->out('empty',$this->className);
            }
        }
        else{
            $this->view->out('empty',$this->className);
        }
    }
}