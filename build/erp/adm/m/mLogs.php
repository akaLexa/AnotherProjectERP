<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 01.04.2017
 *
 **/
namespace build\erp\adm\m;
use mwce\db\Connect;
use mwce\Models\Model;
use mwce\Tools\Configs;
use mwce\Tools\Date;
use mwce\Tools\DicBuilder;
use mwce\Tools\Tools;

class mLogs extends Model
{
    private static $errorList = [];

    public static function getModels($params = null)
    {
        $db = Connect::start();
        $filter = '';

        if(!empty($params['dFrom']) && !empty($params['dTo']))
            $filter = " col_createTime BETWEEN '{$params['dFrom']} 00:00:00' AND '{$params['dTo']} 23:59:59' ";

        if(!empty($params['choseError'])){
            if(!empty($filter))
                $filter .= ' AND ';
            $filter.= " col_ErrNum = ".$params['choseError'];
        }

        if(!empty($filter))
            $filter = 'Where '.$filter;

        return $db->query("SELECT * FROM mwce_logs $filter order BY col_mlID DESC")->fetchAll(static::class);
    }

    public static function getCurModel($id)
    {

    }

    protected function _adding($name, $value)
    {
        if(empty(self::$errorList)){
            self::$errorList = DicBuilder::getLang(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.Configs::curLang().DIRECTORY_SEPARATOR.'errors.php');
        }

        switch ($name){
            case 'col_ErrNum':
                if(!empty(self::$errorList['err'.$value]))
                    parent::_adding($name.'Legend', self::$errorList['err'.$value]);
                break;
            case 'col_createTime':
                parent::_adding($name.'Legend', Date::transDate($value,true));
                break;
        }

        parent::_adding($name, $value);
    }
}