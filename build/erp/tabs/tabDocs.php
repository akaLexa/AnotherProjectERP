<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 15.01.2017
 *
 **/
namespace build\erp\tabs;
use build\erp\inc\AProjectTabs;
use mwce\Tools\Configs;
use mwce\Tools\html;


class tabDocs extends AProjectTabs
{
    protected $postField = array(
        'curChosenDg'=>['type'=>self::INT],
        'chosenFolder'=>['type'=>self::INT],
        'newFname'=>['type'=>self::STR,'maxLength'=>254],
        'queue' =>['type'=>self::STR],
    );

    protected $getField = array(
        'folder' => ['type'=>self::INT],
        'file' => ['type'=>self::INT],
    );


    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params = null)
    {
        $docs = \build\erp\main\m\mDocs::getDocGroups(Configs::curRole());
        $docs[0] ='Все';
        $this->view
            ->set('fileGroupList',html::select($docs,'curChosenDg',0,'class="form-control inlineBlock" onchange="filterDocs();"'))
            ->out('main',$this->className);
    }

    public function getFiles(){

        $params = array(
            'role' => Configs::curRole()
        );

        if(!empty($_POST['curChosenDg']))
            $params['group'] = $_POST['curChosenDg'];
        if(!empty($_POST['chosenFolder'])){
            $params['subId'] = $_POST['chosenFolder'];
        }

        $files = \build\erp\main\m\mDocs::getModels($params);
        if(!empty($files)){
            $isShow = false;
            $curGroup ='';
            foreach ($files as $file){
                if (empty($params['group']) && (empty($curGroup) || $curGroup != $file['col_docGroupName'])) {
                    $curGroup = $file['col_docGroupName'];
                    $this->view
                        ->set('groupName', $file['col_docGroupName'])
                        ->out('centerGroup', $this->className);
                }

                if(!empty($params['subId']) && !$isShow){
                    $this->view
                        ->set('col_parentLegend',\build\erp\main\m\mDocs::getUpperParent($params['subId']))
                        ->set('col_groupID',$params['group'])
                        ->out('centerFolderOut',$this->className);
                    $isShow = true;
                }

                $this->view->add_dict($file);

                if ($file['col_access']>1){
                    $this->view->set('customVisDelDoc','');
                }
                else{
                    $this->view->set('customVisDelDoc','display:none;');
                }

                if($file['col_isFolder'] == 1){
                    $this->view->out('centerFolder',$this->className);
                }
                else{
                    $this->view->out('center',$this->className);
                }

            }
        }
        else{
            if(!empty($params['subId'])){

                $this->view
                    ->set('col_parentLegend',\build\erp\main\m\mDocs::getUpperParent($params['subId']))
                    ->set('col_groupID',$params['group'])
                    ->out('centerFolderOut',$this->className);

            }
            $this->view->out('centerEmpty',$this->className);
        }

    }

    public function addFolder(){
        if(!empty($_POST['newFname']) && !empty($_POST['curChosenDg'])){
            echo json_encode(['folder'=>
                \build\erp\main\m\mDocs::addFolder($_POST['newFname'],
                    !empty($_POST['chosenFolder'])? $_POST['chosenFolder'] : 0,
                    Configs::userID(),
                    $_POST['curChosenDg'],
                    $this->project['col_projectID']
                    )]);
        }
    }

    public function delFolder(){
        if(!empty($_GET['folder'])){
            \build\erp\main\m\mDocs::delFolder($_GET['folder'],Configs::curRole(),Configs::userID());
        }
    }
    public function delFile(){
        if(!empty($_GET['file'])){
            \build\erp\main\m\mDocs::delFolder($_GET['file'],Configs::curRole(),Configs::userID());
        }
    }
    public function delFiles(){
        if(!empty($_POST['queue'])){
            \build\erp\main\m\mDocs::delFiles($_POST['queue'],Configs::curRole(),Configs::userID());
        }
    }

}