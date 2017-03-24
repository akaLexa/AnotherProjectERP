<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 21.01.2017
 * работа с файлами
 **/
namespace  build\erp\main;

use build\erp\inc\eController;
use build\erp\inc\Files;
use build\erp\inc\Project;
use mwce\Tools\Configs;
use mwce\Exceptions\ModException;

class Docs extends eController
{
    protected $getField = array(
        'p' => ['type'=>self::INT], //проект
        'f' => ['type'=>self::INT], //папка
        'gr' => ['type'=>self::INT], //группа файлов
        'queue' =>['type'=>self::STR],
    );

    /**
     * загрузка в проект
     */
    public function actionProjectUpload(){
        if(!empty($_GET['p']) && !empty($_GET['gr']))
        {
            if(empty($_FILES)){
                $project = Project::getCurModel($_GET['p']);
                if(!empty($project)){
                    $this->view
                        ->add_dict($project)
                        ->set('groupNum',$_GET['gr'])
                        ->set('folderNum',!empty($_GET['f']) ? $_GET['f'] : 0)
                        ->out('tabDocs',$this->className);
                }
                else
                    throw new ModException('Неизвестный проект.');

            }
            else{
                try{
                    $file = Files::start();
                    $file->projectUpload($_GET['p'],$_GET['gr'],!empty($_GET['f']) ? $_GET['f'] : 0,Configs::userID());
                    echo json_encode(['success' => 1]);
                }
                catch (\Exception $e){
                    echo json_encode(['error' => $e->getMessage()]);
                }
            }
        }
    }

    public function actionProjectDownload(){
        if(!empty($_GET['f'])){
            $f = Files::start();
            $f->projectDownloadFile($_GET['f'],Configs::curRole());
        }
    }

    public function actionProjectFolderDownload(){
        if(!empty($_GET['f'])){
            $f = Files::start();
            $f->projectFolderDownload($_GET['f'],Configs::curRole());
        }
    }

    public function actionProjectFilesDownload(){
        if(!empty($_GET['queue'])){
            $f = Files::start();
            $f->projectFilesDownload($_GET['queue'],Configs::curRole());
        }
    }
}