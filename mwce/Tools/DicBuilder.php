<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 12.04.2016
 * v 0.1
 **/
namespace mwce\Tools;
/**
 * Class DicBuilder
 * @package mwce
 *
 * помогает строить/перестраивать языковые словари
 */
class DicBuilder
{
    /**
     * @var null|string
     * полный адрес до файла словаря, включая расширение(если есть)
     */
    private $location;

    /**
     * DicBuilder constructor.
     * @param string|null $location
     */
    public function __construct($location = null)
    {
        $this->location = $location;
        if(!file_exists($location) && !is_null($location))
            self::writeThis('<?php');
    }

    /**
     * @param array $array
     * @param null|string $location
     */
    public function buildDic($array, $location = null)
    {

        if (!is_null($location)){
            $this->location = $location;
        }

        $content = '<?php return ['.PHP_EOL;
        $ai = new \ArrayIterator($array);

        foreach ($ai as $id => $value) {
            $content .= '\'' . $id . '\' => \'' . $value . '\','.PHP_EOL;
        }

        $content .= '];';

        $this->writeThis($content);
    }

    /**
     * запись словаря
     * @param string $content
     * @throws \Exception
     */
    private function writeThis($content)
    {
        if(!is_null($this->location))
            file_put_contents($this->location, $content, LOCK_EX);
        else
            throw new \Exception('location parameter is empty!');
    }

    /**
     * @param string $path
     * @return array|mixed
     */
    public static function getLang($path){

        if(file_exists($path))
            $l = include $path;
        else
            return array();

        if(!is_array($l))
            return array();

        return $l;
    }

    /**
     * Запись в словарь данных
     * @param string $value данные
     * @param string $preffix часть названия ключа массива
     * @param bool $isIterate обновлять или дописывать вконце 1,2...н
     * @return bool|string ключ от добавленного элеента
     */
    public function add2Dic($value,$preffix='auto_lang',$isIterate = false)
    {
        if(!file_exists($this->location))
            return false;

        $container = include $this->location;

        if(empty($container) || !is_array($container)){
            $container = [];
        }

        $i=0;

        if (empty($container) || empty($container[$preffix]) || $isIterate) {
            $container[$preffix] = $value;
            self::buildDic($container);
            return $preffix;
        }

        while (isset($container[$preffix . $i])) {
            $i++;
        }

        $container[$preffix . $i] = $value;
        self::buildDic($container);
        return $preffix . $i;

    }

    /**
     * удалить
     * @param string|mixed $id
     * @return bool
     */
    public function delFromDic($id){
        if(!file_exists($this->location))
            return false;

        $container = include $this->location;

        if(!empty($container[$id])){
            unset($container[$id]);
            self::buildDic($container);
            return true;
        }
        return false;
    }
}