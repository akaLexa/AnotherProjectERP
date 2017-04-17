<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 16.04.2017
 * главная страница отчетов
 **/
namespace build\erp\reports;
use build\erp\inc\eController;
use build\erp\reports\m\mReports;
use mwce\Tools\html;
use mwce\Tools\Tools;

class Reports extends eController
{
    public function actionIndex()
    {
        $rList = mReports::getReportsList();
        $rList[0] = '...';

        $this->view
            ->set('rList', html::select($rList,'curReport',0,'class="form-control inlineBlock" style="width:400px;"'))
            ->out('main',$this->className);
    }
}