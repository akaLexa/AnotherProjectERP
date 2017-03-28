<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 07.04.2016
 *
 **/
namespace mwce\Tools;

class Tools
{
    /**
     * перенаправление на нужную встраницу. В случае пустого передаваемого параметра сделает перезагрузку страницы.
     * @param null|string $path адрес
     */
    public static function go ($path = NULL)
    {
        if ($path == NULL) {
            if (!empty($_SERVER["REQUEST_URI"]))
                $path = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            else
                $path = "http://" . $_SERVER["HTTP_HOST"];
        }
        header("Location: {$path}");
        die();
    }

    /**
     * @param $obj
     * функция позволяет увидеть содержимое всех переданных в нее параметров
     */
    public static function debug ($obj)
    {
        $numargs = func_num_args();
        if ($numargs > 1) {
            $arg_list = func_get_args();
            for ($i = 0; $i < $numargs; $i++) {
                print "<pre>";
                print_r($arg_list[$i]);
                print "</pre>";
            }
        } else {
            print "<pre>";
            print_r($obj);
            print "</pre>";
        }
    }

    public static function debugCmd ($obj)
    {
        $numargs = func_num_args();
        if ($numargs > 1) {
            $arg_list = func_get_args();
            for ($i = 0; $i < $numargs; $i++) {
                print chr(10);
                print_r($arg_list[$i]);
                print chr(10);
            }
        } else {
            print chr(10);
            print_r($obj);
            print chr(10);
        }
    }

    /**
     * @return string
     */
    public static function getAddress()
    {
        $list = explode("/",$_SERVER["PHP_SELF"]);
        array_pop($list);
        if(isset($_SERVER['HTTPS']))
            $gaddress ="https://".getenv("HTTP_HOST").implode("/",$list)."/";
        else
            $gaddress ="http://".getenv("HTTP_HOST").implode("/",$list)."/";
        
        return $gaddress;
    }
    
    static public function linkDec($link)
    {
        return str_replace("&amp;","&",$link);
    }

    public static function getAllBuilds($width = true)
    {
        $list = scandir("build");
        $ai = new \ArrayIterator($list);
        $sel = array();

        foreach ($ai as $id=>$v)
        {
            if($v!="." && $v!=".." && $v!=".htaccess")
            {
                $sel[$v] = $v;
            }

        }
        if($width)
            $sel[-1] = "...";

        return $sel;
    }

    /**
     * пагинатор, то есть возвращает значения для выборки,сколько страниц (min,max,count)
     * @param int $count поличество записей в общем
     * @param int $perpage сколько записей на страницу
     * @param int $curpage текущая страница
     * @return array min,max,count
     */
    public static function paginate($count,$perpage,$curpage=1)
    {
        $total = floor($count/$perpage); //сколько страниц
        $ost = $count % $perpage; //сколько страниц в остатке

        if ($ost>0)
            $total++; // если есть еще страницы

        $return["min"] = ($curpage-1)*$perpage;
        $return["max"] = $curpage*$perpage ;
        $return["count"] = $total;

        return $return;
    }

    /**
     * форматирует числа с отступами по английской манере
     * @param int $num число
     * @param int $nums кол-во символов после запятой
     * @return string
     */
    public static function number($num,$nums=2)
    {
        return number_format($num, $nums, ',', ' ');
    }

    /**
     * возвращает массив с месяцами, если
     * $isCurMonth = true, тогла в 0 ячейке будет номер текущего месяца
     * @param bool $isCurMonth
     * @return array
     */
    public static function getMonth($isCurMonth = true){
        $months = [
            1 => 'Январь',
            2 => 'Ферваль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь'
        ];

        if($isCurMonth){
            $m = date('n');
            if(!empty($months[$m]))
            {
                $months[0] = $m;
            }
        }

        return $months;
    }

    /**
     * возвращает массив с годаме от $from до $to, если
     * $isCur = true, то в $years[0] будет текущий год
     * @param bool $isCur
     * @param int $from
     * @param int $to
     * @return array
     */
    public static function getYear($isCur = true, $from = 2015, $to = 2020)
    {
        $years = [];

        for ($i = $from; $i <= $to; $i++) {
            $years[$i] = $i;
        }

        if ($isCur) {
            $m = date('Y');
            if (!empty($years[$m])) {
                $years[0] = $m;
            }
        }
        return $years;
    }
}