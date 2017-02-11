<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 03.12.2016
 * плагин авторизации
 **/
namespace build\erp\plugins;
use build\erp\plugins\m\mLogin;
use mwce\Configs;
use mwce\PluginController;
use mwce\router;
use mwce\Tools;

class Login extends PluginController
{
    protected $postField = array(
        'uLogin' => ['type' => self::STR],
        'uPassword' => ['type' => self::STR],
    );

    public function actionIndex()
    {
        if(empty($_SESSION['mwcuid']) || $_SESSION['mwcGroup'] == 2){

            if(!empty($_POST['uLogin']) && !empty($_POST['uPassword'])) {

                $user = mLogin::auth($_POST['uLogin'],$_POST['uPassword']);
                if(!empty($user) && $user['col_isBaned'] == 0){

                    $_SESSION['mwcuid'] = $user['col_uID'];
                    $_SESSION['mwcGroup'] = $user['col_gID'];
                    $_SESSION['mwcRole'] = $user['col_roleID'];
                    $_SESSION['mwcLogin'] = $user['col_login'];
                    $_SESSION['mwcName'] = $user['col_Name'];
                    $_SESSION['mwcSurname'] = $user['col_Sername'];
                    $_SESSION['mwcRoleName'] = $user['col_roleName'];

                    if(!empty(trim($user['col_Lastname']))){
                        $_SESSION['mwcLastname'] = $user['col_Lastname'];
                    }
                    if(!empty($user['col_gName'])){
                        $_SESSION['mwcGroupName'] = $user['col_gName'];
                    }

                    Tools::go();
                }

            }

            $cfg = Configs::readCfg('main', Configs::currentBuild());
            if (!empty($cfg['defpage'])) {
                router::setCurController($cfg['defpage']);
                router::setCurAction('actionErrorInLogin');
            }
            $this->view->out('Login', 'plugin_' . $this->className);

        }
        else{
            if(isset($_REQUEST['IwantLogOut'])){
                session_destroy();
                Tools::go();
            }
            $this->view->out('LoginIn','plugin_'.$this->className);
        }
    }

}