<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 26.03.2017
 *
 **/
namespace mwce\Routing;

use mwce\Tools\Tools;

class URLparser
{
    protected static $inst = null;
    protected $parserData;

    /**
     * @return array
     */
    public static function Parse(){
        if(is_null(self::$inst)){
            self::$inst = new self();
        }

        return self::$inst->parserData;
    }

    protected function __construct()
    {
        $url = '';

        if(empty($_SERVER['argc'])){
            $url = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI']:'';
            $this->parserData['isCmd'] = false;
        }
        else if($_SERVER['argc'] > 0) {
            // вызов из командной строки
            $this->parserData['isCmd'] = true;

            if (!empty($_SERVER['argv'][1])) {
                $url = $_SERVER['argv'][1];
            } else {
                $url = '';
            }

            if ($_SERVER['argc'] > 2) {
                for ($i = 2; $i < $_SERVER['argc']; $i++) {
                    $data_ = explode("=", $_SERVER['argv'][$i]);
                    $_GET[trim($data_[0])] = trim($data_[1]);
                }
            }
        }

        $path = trim(parse_url($url, PHP_URL_PATH), '/');

        $list = explode("/", $_SERVER["PHP_SELF"]);
        unset($list[0]);
        array_pop($list);

        if (!empty($list)) {
            $toemp = implode("/", $list) . "/";
            $path = str_replace($toemp, '', $path);
        }
        $path_array = explode('/', $path);


        if (strripos($path, '.html') === false
            && strripos($path, '.php') === false
        ) //если запрос для бекграунда (ajax, наример)
        {
            $this->parserData['isBg'] = true;
        }
        else
            $this->parserData['isBg'] = false;

        //todo: проверить на переизбыточность условия
        if (!empty($path_array)) {
            $parsed = $path_array[0];
            $parsed = explode('.', $parsed);

            $this->parserData['type'] = strtolower($parsed[0]);
            if (empty($parsed['type']) ||  $parsed['type'] != 'control') {
                $this->parserData['type'] = 1;
            } else {
                $this->parserData['type'] = 2;
            }

            if (empty($path_array[$this->parserData['type']])) //нет выражения типа site.ru/page/controller
            {
                $this->parserData['controller'] = false;
            } else {
                $parsed = $path_array[$this->parserData['type']];
                $parsed = explode('.', $parsed);
                $this->parserData['controller'] = $parsed[0];
            }

            if (empty($path_array[$this->parserData['type'] + 1]))  //нет выражения типа site.ru/page/controller/action...
            {
                $this->parserData['action'] = false;
            } else {
                $parsed = $path_array[$this->parserData['type'] + 1];
                $parsed = explode('.', $parsed);
                $this->parserData['action'] = $parsed[0];
            }

            if(!$this->parserData['controller']){
                $this->parserData['isBg'] = false;
            }
        }
        else{
            $this->parserData['type'] = 1;
            $this->parserData['isBg'] = false;
        }
    }
}