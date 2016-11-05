<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 16.11.2015
 * работа с датой
 **/
namespace mwce;
class date_
{
    /**
     * @param string $date
     * @param bool|false $type
     * @return bool|string
     * конвертация даты из бд в человекопонятную дату
     */
    public static function transDate($date= "0000-00-00",$type=false)
    {
        if (trim($date) == "0000-00-00" or $date==NULL or $date=="1970-01-01 00:00:00" or $date=="1970-01-01")
            return "-/-";
        if (!$type)

            return date("d-m-Y",strtotime($date));
        return date("d-m-Y H:i",strtotime($date));
    }

    /**
     * @param $date
     * @param bool|false $type
     * @return bool|string
     * конвертация даты в дату, пригодную для бд(смена формата даты))
     */
    public static function intransDate($date,$type=false)
    {
        if ($date == NULL)
            return "-/-";
        if (!$type)
            return date("Y-m-d",strtotime($date));
        return date("Y-m-d H:i:s",strtotime($date));
    }

    /**
     * @param datetime $a
     * @param datetime $b - вычитаемое
     * @param bool|false $type true - разница в днях, false - в часах
     * @return int
     * узнать разницу между датами
     */
    public static function dateDif($a,$b,$type=false)
    {
        $a = strtotime($a);
        $b = strtotime($b);
        if (!$type)
            $c = floor(($a - $b)/86400);
        else
            $c = floor(($a - $b)/3600);
        return (int)$c;
    }
}