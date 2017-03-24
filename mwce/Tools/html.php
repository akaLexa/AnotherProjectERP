<?php
namespace mwce\Tools;
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 21.10.2015
 *
 **/
class html
{
    /**
     * @param array $args - массив с данными для заполнения элемента
     * @param mixed $chosen - какой элемент должен быть выбран по умолчанию
     * @param string $name - название и id элемента
     * @param array|string $others - любые html-атрибуты элемента.
     * либо в виде строки: "style='width:12px;' onchange='alert(123);'",
     * либо в виде ассоциативного массива: array("style"=>"width:12px;","onchange"=>"alert(123);")
     * @return string - html код элемента select
     */
    public static function select($args,$name,$chosen=-1,$others="")
    {
        if(is_array($others))
        {
            $htmAttr="";
            foreach ($others as $id => $val)
            {
                $htmAttr.=" $id=\"$val\"";
            }
        }
        else
            $htmAttr = $others;

        $text = "<select name=\"{$name}\" id=\"{$name}\" {$htmAttr}>";
        $wassel = 0;

        if(!empty($args) && is_array($args))
        {
            foreach ($args as $id=>$val)
            {
                $text.="<option value=\"$id\"";
                if ($chosen == $id && $wassel == 0)
                {
                    $text.=" SELECTED ";
                    $wassel=1;
                }
                $text.=">$val</option>";
            }
        }
        $text.="</select>";
        return $text;
    }

    /**
     *
     * @param array $collect:
     * [0] запись перед чекбоксом
     * [1] имя = id
     * [2] значение
     * [3] функции js
     * [4] нажат? (1/0)
     * [5] css класс
     * @return string сгенерированный html код
     */
    public static function checkbox($collect)
    {
        $return = "";
        foreach ($collect as $array)
        {
            if (isset($array[4]) && $array[4]>0)
                $array[4]="CHECKED";
            else
                $array[4]="";
            if(!isset($array[3]))
                $array[3] = "";
            if(!isset($array[5]))
                $array[5] = "";

            $return.= " $array[0] <input type='checkbox' name='$array[1]' id='$array[1]' value='$array[2]' $array[3]  $array[4] class='$array[5]'>";
        }

        return $return;
    }

    /**
     * генерация text
     * @param string $name
     * @param mixed $value
     * @param string $others
     * @return string
     */
    public static function inputText($name,$value,$others='')
    {
        return "<input type='text' name='$name' id='$name' value='$value' $others>";
    }

    /**
     * html элемент input
     * @param string $type text,tel,checkbox, etc тип
     * @param string $name имя/ид
     * @param int|mixed $value значение
     * @param string $other класс, стиль и все что не указано выше
     * @return string html
     */
    public static function input($type,$name,$value=0,$other =''){
        return '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.$value.'" '.$other.'>';
    }

    /**
     * группа чекбоксов
     * @param string $name название для каждого чекбокса(выведет $name_н, где н- кол-во элементов, начиная с 0)
     * @param array $values массив значений и надписей для чекбоксов [легенда , значение , bool checked]
     * @param string $styles стили для дива, в котором чекбоксы
     * @return string html
     */
    public static function checkGroup($name,$values,$styles){
        if(!is_array($values))
            return 'values must be an array["legend"=>value]';

        $ret = "<div $styles>";
        $i =0;
        foreach ($values as $item){
            $ret.=" <label class='mwceCustomCheckGroup'><span class='mwceCustomCheckGroupSpan'>{$item[0]}</span> <input type='checkbox' name='{$name}_{$i}' value='{$item[1]}'";
            if($item[2] == true){
                $ret.= ' checked ';
            }
            $ret.="> </label>";
            $i++;
        }

        return $ret.'</div>';
    }
}