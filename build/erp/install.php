<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * 18.02.2017
 * настройки для скрипта установки
 **/
return array(
    'availableConnections' => [
        \mwce\Connect::MYSQL
    ],//список доступных для билда подключений
    'writeFolders' =>[
        '_dat','configs'
    ],//список дирректорий, с доступом на запись
    'description'=>'Альфа-версия web-системы управления проектами', //описание билда
    'needAdmin' => [
      /*  'login'=>'admin',
        'pwd'=>'admin'*/
    ], //нужно отдельно заводить администратора системы (оставить пустым в противном случае)
);
