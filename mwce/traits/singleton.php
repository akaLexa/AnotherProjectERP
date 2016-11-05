<?php

namespace mwce\traits;

trait singleton
{
    /**
     * @var self instance
     */
    protected static $inst = null;

    /**
     * точка входа
     * @param null|mixed $params
     * @return self|singleton
     */
    public static function start($params = null)
    {
        if(is_null(self::$inst))
            self::$inst = new self($params);
        return self::$inst;
    }
}