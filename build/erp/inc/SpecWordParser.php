<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.03.2017
 * парсер завезервированных слов в {} в эвентах, нотисах и т.п.
 **/
namespace build\erp\inc;

class SpecWordParser
{
    private static $data = array();

    public static function check($text){
        preg_match_all("/({)+([a-zA-z]+[:]+[0-9]+){1,}(})+/",$text,$returns);
        if(!empty($returns) &&!empty($returns[0]) && !empty($returns[2])){
            foreach ($returns[2] as $nums => $return) {
                $elements = explode(':',$return);
                if(!empty($elements))
                    $text = preg_replace('/'.$returns[0][$nums].'/',self::getData($elements[0],$elements[1]),$text);
            }
        }

        return $text;
    }

    /**
     * @param string $type
     * @param int $num
     * @return string
     */
    private static function getData($type,$num){
        switch (strtolower($type)){
            case 'userid':
                if(!empty(self::$data[$type][$num]))
                    return self::$data[$type][$num];
                else{
                    $list = User::getUserList(true);
                    self::$data[$type] = $list;
                    if(!empty($list[$num]))
                        return self::$data[$type][$num];
                    return 'Unknown user '.$num;
                }
            default:
                return 'unknown type: '.$type;
        }
    }
}