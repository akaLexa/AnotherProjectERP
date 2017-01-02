<?php
/**
 * MuWebCloneEngine
 * Created by epmak
 * 24.12.2016
 *
 **/

namespace mwce\traits;


trait tInsert
{
    /**
     * возвращает сгененированный кусок SQL кода для простого интерта в базу данных
     * где $array является ассоциативным и ключ = название столбца
     * @param array $array
     * @return string
     */
    public function genInsert($array){
        $genQpice = '';
        if(!empty($array) && is_array($array)){

            $left = '';
            $right = '';

            foreach ($array as $id=>$value){
                if(!empty($left))
                    $left.=',';

                if(!empty($right))
                    $right.=',';

                if(strtolower(trim($value)) != 'null')
                    $value = "'$value'";

                $left.=" $id";
                $right.= " $value";
            }

            if(!empty($left) && !empty($right))
                $genQpice = "($left) VALUE ($right)";
        }

        return $genQpice;
    }

    /**
     * возвращает сгененированный кусок SQL кода для простого интерта в базу данных
     * где $array является ассоциативным и ключ = название столбца
     * @param array $array
     * @return string
     */
    public static function genInsertSt($array){
        $genQpice = '';
        if(!empty($array) && is_array($array)){

            $left = '';
            $right = '';

            foreach ($array as $id=>$value){
                if(!empty($left))
                    $left.=',';

                if(!empty($right))
                    $right.=',';

                if(strtolower(trim($value)) != 'null')
                    $value = "'$value'";

                $left.=" $id";
                $right.= " $value";
            }

            if(!empty($left) && !empty($right))
                $genQpice = "($left) VALUE ($right)";
        }

        return $genQpice;
    }
}