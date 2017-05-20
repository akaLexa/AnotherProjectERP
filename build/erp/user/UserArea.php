<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 16.05.2017
 * настройка пользователя
 **/
namespace build\erp\user;
use build\erp\inc\eController;
use build\erp\inc\User;
use build\erp\user\m\mUserArea;
use mwce\Tools\Configs;
use mwce\Tools\html;
use mwce\Tools\Tools;

class UserArea extends eController
{
    protected $postField = array(
        'depUser' => ['type'=>self::INT],
    );

    public function actionIndex()
    {
        $obj = User::getCurModel(Configs::userID());
        if(!empty($obj)) {

            if(empty($_POST)){
                if(empty($obj['col_deputyID']))
                    $this->view->set('vizDepStyle','display:none');

                $this->view
                    ->set('imgNum',
                        (file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'imgs' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . Configs::userID() . '.png') ? Configs::userID() : 'default'))
                    ->add_dict($obj)
                    ->out('main', $this->className);
            }
            else if (!empty($_POST['ismainTab']) && !empty($_FILES['avatarsImg'])){
                try{
                    mUserArea::DownloadAvatar('avatarsImg',Configs::userID());
                    Tools::go();
                }
                catch (\Exception $e){
                    $this->view->error($e);
                }

            }

        }

    }

    public function actionGetMain(){
        $obj = User::getCurModel(Configs::userID());
        if(!empty($obj)) {
            if (empty($_POST)) {
                $userList = User::getUserList();
                unset($userList[Configs::userID()]);
                $userList[-1] = 'Не замещается';

                $this->view
                    ->set('userList',
                        html::select($userList, 'depUser', (!empty($obj['col_deputyID']) ? $obj['col_deputyID'] : -1), [
                            'class' => 'form-control inlineBlock',
                            'style' => 'width:300px;',
                            'onChange' => 'ChooseDep()',
                        ]))
                    ->out('tabMain', $this->className);
            } else if (!empty($_POST['depUser'])) {
                $obj->setDep($_POST['depUser']);
            }
        }
    }

    public function actionGetNotice(){
        echo 'Under construction!';
        //echo 'notice';
    }

    public function actionGetMailer(){
        echo 'Under construction!';
        //echo 'mail';
    }

    public function actionDelPhoto(){
        mUserArea::delPhoto(Configs::userID());
    }
}