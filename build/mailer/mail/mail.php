<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 08.04.2017
 *
 **/
namespace build\mailer\mail;

use build\mailer\inc\mailController;
use mwce\Tools\Logs;
use mwce\Tools\Tools;


class mail extends mailController
{
    private $host;
    private $mailBox;
    private $mailPWD;
    private $port;
    private $isSMTPAuth;
    private $domenTo;


    public function actionIndex()
    {
        if(!empty($_GET['address']))
            $adr = $_GET['address'];
        else
            $adr = '#';
        if(!empty($_GET['host']))
            $this->host = $_GET['host'];
        else
        {
            Tools::debugCmd('host e-mail server is empty!');
            exit;
        }
        if(!empty($_GET['port']))
            $this->port = $_GET['port'];
        else
            $this->port = 25;

        if(!empty($_GET['isSMTPAuth'])){
            $this->isSMTPAuth = true;
        }
        else
            $this->isSMTPAuth = false;

        if(!empty($_GET['mailBox'])){
            $this->mailBox = $_GET['mailBox'];
        }
        else{
            Tools::debugCmd('mailBox is empty!');
            exit;
        }

        if(!empty($_GET['mailPWD'])){
            $this->mailPWD = $_GET['mailPWD'];
        }
        else{
            Tools::debugCmd('mailPWD is empty!');
            exit;
        }

        if(!empty($_GET['domainSend'])){
            $this->domenTo = $_GET['domainSend'];
        }
        else{
            Tools::debugCmd('domainSend is empty!');
            exit;
        }

        $list = lettersList::getModels([
            /*'start' => date("d-m-Y H:i:00",strtotime('last sunday')),
            'end' => date("d-m-Y H:i:59",strtotime('first monday'))*/
            'start' => '2017-03-01 00:00:00',
            'end' => '2017-05-01 00:00:00'
        ]);

        if(!empty($list)){
            $curUser = 0;
            $curType = '';
            $tmp = [];
            $toUpdate = [];
            $login = '';

            foreach ($list as $item){

                if(empty($curUser)){

                    $curType = $item['col_etName'];
                    $curUser = $item['col_userID'];
                    $login = $item['col_login'];
                }
                else if(!empty($curUser) && $curUser != $item['col_userID']){
                    if(!empty($tmp)){
                        $this->view
                            ->loops('contentLetter',$tmp,'main',$this->className)
                            ->set('curType',$curType)
                            ->out('main',$this->className);
                        $letterContent = $this->view->getContainer();
                        $this->view->clearContainer();
                        self::mail($login.'@'.$this->domenTo,$letterContent,'Категория - '.$curType);
                        $letterContent = '';
                    }

                    $curUser = $item['col_userID'];
                    $curType = $item['col_etName'];

                    $tmp = [];
                    $login = $item['col_login'];
                }
                else if($curType != $item['col_etName']){


                    if(!empty($tmp)){
                        $this->view
                            ->loops('contentLetter',$tmp,'main',$this->className)
                            ->set('curType',$curType)
                            ->out('main',$this->className);
                        $letterContent = $this->view->getContainer();
                        $this->view->clearContainer();
                        self::mail($login.'@'.$this->domenTo,$letterContent,'Категория - '.$curType);
                        $letterContent = '';
                    }

                    $tmp = [];
                    $curType = $item['col_etName'];
                }

                $item['address'] = $adr;
                $item['link'] = lettersList::Getlink($item['col_etID'],$item['col_object']);
                $tmp[] = $item;
                $toUpdate[] = $item['col_evID'];
            }

            if(!empty($tmp)){
                $this->view
                    ->loops('contentLetter',$tmp,'main',$this->className)
                    ->set('curType',$curType)
                    ->out('main',$this->className);

                $letterContent = $this->view->getContainer();
                $this->view->clearContainer();
                self::mail($login.'@'.$this->domenTo,$letterContent,'Категория - '.$curType);
            }

            /*if(!empty($toUpdate))
                lettersList::updateEvents($toUpdate);*/
        }
    }

    protected function mail($mailTo,$body,$title){
        require_once baseDir . '/lib/PHPMailer/PHPMailerAutoload.php';

        $mail = new \PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';
        $mail->Host = $this->host;
        $mail->Port = $this->port;
        $mail->SMTPAuth = $this->isSMTPAuth;
        $mail->Username = $this->mailBox;
        $mail->Password = $this->mailPWD;
        $mail->setFrom($this->mailBox, 'Собщения из журнала событий');
        $mail->addAddress($mailTo);
        $mail->Subject = $title;
        $mail->msgHTML($body);
        //$mail->AltBody = 'Собщения из журнала событий';
        $mail->CharSet = "Utf-8";

        if (!$mail->send()) {
            Logs::textLog(9,$mail->ErrorInfo);
        }
        else {
            Tools::debugCmd("$mailTo -> Message send");
        }
    }
}