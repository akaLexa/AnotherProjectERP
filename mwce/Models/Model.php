<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 10.04.2016
 *
 **/

namespace mwce\Models;

use mwce\db\Connect;
use mwce\Exceptions\ModException;
use mwce\Interfaces\Imodel;

abstract class Model implements Imodel, \ArrayAccess, \Iterator
{
    /**
     * @var Connect
     */
    protected $db;

    /**
     * @var array
     * значения по перечню полей
     */
    protected $object;

    /**
     * @var int
     */
    protected $pos = 0;

    /**
     * @var array
     * перечень полей
     */
    protected $fields;

    /**
     * @var array кешированные данные со статических функций, где предусмотрено
     */
    protected static $sdata = array();

    /**
     * Model constructor.
     * @param int $con
     */
    public function __construct($con = 0)
    {
        $this->db = Connect::start($con);
        $this->init();
    }

    /**
     * функция инициализации,
     * запускается сразу после конструктора
     */
    protected function init()
    {
    }

    /**
     * запись лога в БД
     * @param string $msg
     * @param string $file
     * @param int $errNo
     * @param bool|true $isValid
     */
    public function toLog($msg, $file = "1", $errNo = 0, $isValid = true)
    {
        $this->db->SQLog($msg, $file, $errNo, $isValid);
    }

    /**
     * универальный метод добавления в модель данных
     * @param $name mixed
     * @param $value mixed
     */
    protected function _adding($name, $value)
    {
        if (!isset($this->object[$name]))
            $this->fields[] = $name;

        $this->object[$name] = $value;
    }

    //region magic

    public function __call($name, $arguments)
    {
        throw new ModException('undefuned method "' . $name . '" in '. basename (static::class));
    }

    public function __get($name)
    {

        if (isset($this->object[$name]))
            return $this->object[$name];
        return false;
    }

    public function __set($name, $value)
    {
       $this->_adding($name, $value);
    }

    public function __isset($name)
    {
        return isset($this->object[$name]);
    }


    //endregion

    //region ArrayAccess
    public function offsetExists($offset)
    {
        if (isset($this->object[$offset]))
            return true;
        return false;
    }

    public function offsetGet($offset)
    {
        if (isset($this->object[$offset]))
            return $this->object[$offset];
        return false;
    }

    public function offsetSet($offset, $value)
    {
        $this->_adding($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->object[$offset]);
    }
    //endregion

    //region Iterator

    public function current()
    {
        if (isset($this->fields[$this->pos]))
            return isset($this->object[$this->fields[$this->pos]])?$this->object[$this->fields[$this->pos]]:'';
        return '';
    }

    public function next()
    {
        ++$this->pos;
    }

    public function key()
    {
        return $this->fields[$this->pos];
    }


    public function valid()
    {
        if (isset($this->fields[$this->pos]))
            return isset($this->object[$this->fields[$this->pos]]) || is_null($this->object[$this->fields[$this->pos]])? true : false;

        return false;
    }

    public function rewind()
    {
        $this->pos = 0;
    }

    //endregion

    //region чистка кешей 
    public static function dellCurPageCache()
    {
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . $_SESSION["mwccfgread"] . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $_SESSION["mwclang"] . "_pages.php";
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public static function dellCurMenuCache($menuName)
    {
        $path = baseDir.DIRECTORY_SEPARATOR."build".DIRECTORY_SEPARATOR.$_SESSION["mwccfgread"].DIRECTORY_SEPARATOR."_dat".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR.$_SESSION["mwclang"]."_plugin_".$menuName;

        if (file_exists($path))
        {
            unlink($path);
        }
    }
    //endregion
}