<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 20.05.2017
 *
 **/
namespace build\erp\user\m;
use mwce\Models\Model;
use mwce\Tools\Configs;
use mwce\Tools\DicBuilder;
use mwce\Tools\Tools;

class mUserArea extends Model
{

    public static function getModels($params = null)
    {

    }

    public static function getCurModel($id)
    {

    }

    public static function DownloadAvatar($fName,$userID){
        $filePath  = $_FILES[$fName]['tmp_name'];
        $errorCode = $_FILES[$fName]['error'];

        if ($errorCode !== UPLOAD_ERR_OK || !is_uploaded_file($filePath)) {
            $lng = DicBuilder::getLang(baseDir . DIRECTORY_SEPARATOR . 'build' . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . Configs::curLang() . DIRECTORY_SEPARATOR . 'file_errors.php');
            $error = !empty($lng[$errorCode]) ? $lng[$errorCode] : 'Неизвестная ошибка при загрузке файла';
            throw  new \Exception($error);
        }

        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = (string) finfo_file($fi, $filePath);


        if (strpos($mime, 'image') === false || strpos($mime, 'png') === false)
            throw new \Exception('Поддерживатся только *.png формат.');

        $image = getimagesize($filePath);

        $limitWidth  = 100;
        $limitHeight = 100;

        if ($image[1] > $limitHeight)
            throw new \Exception('Высота изображения не должна привышать '.$limitHeight.'px');
        if ($image[0] > $limitWidth)
            throw new \Exception('Ширина изображения не должна привышать '.$limitWidth.'px');

        if (!move_uploaded_file($filePath, baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'imgs' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $userID.'.png')) {
            throw new \Exception('При записи изображения на диск произошла ошибка.');
        }
    }

    public static function delPhoto($userID){
        $path = baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'imgs' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $userID.'.png';
        if(file_exists($path))
            unlink($path);
    }
}