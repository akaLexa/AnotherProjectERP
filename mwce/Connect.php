<?php

namespace mwce;

use mwce\Exceptions\CfgException;
use mwce\Exceptions\DBException;


/**
 * Class Connect
 * @package mwce
 *
 * @method FetchRow
 * @method GetRow
 * @method GetArray
 * @method fetch
 * @method fetchAll
 */
class Connect
{

    //region типы подключений
    const ODBC = 1;
    const MYSQL = 2;
    const MSSQL = 3;
    const SQLSRV = 5;
    const INSTALL = 4;
    //endregion


    /**
     * @var array
     * список доступных подключений для выбора
     */
    static public $conList = array(
        self::ODBC => 'MS SQL PDO ODBC',
        self::MYSQL => 'PDO MySql',
        self::MSSQL => 'PDO MS SQL',
        self::SQLSRV => 'PDO SQLSRV (ms sql)',
    );

    /**
     * @var array пул подключений
     */
    static protected $pool = []; //пул подключений

    /**
     * @var int количество запросов
     */
    static public $queryCount = 0;

    /**
     * @var \PDO
     */
    protected $resId;

    /**
     * @var \PDO
     * последний запрос
     */
    protected $lastQh;

    /**
     * @var int
     * тип текущего подключения
     */
    protected $curConType;

    /**
     * @var array
     * массив с командами при подключении PDO
     */
    protected $commands = array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET names 'utf8'",
    );

    /**
     * точка входа singleton
     * @param int $conNum номер или название подключения
     * @return mixed|Connect
     */
    static public function start($conNum = 0)
    {
        if (defined('conNum') && $conNum == 0 && strtolower(trim($conNum)) !='sitebase')
            $conNum = conNum;

        if (!isset(self::$pool[$conNum])) {
            if ($conNum == -1) {
                self::$pool[$conNum] = new connect($conNum);
            }
            else {
                try {
                    self::$pool[$conNum] = new connect($conNum);
                }
                catch (\Exception $e) {
                    content::errorException($e);
                    die();
                }
            }
        }

        return self::$pool[$conNum];
    }

    public function __get($name)
    {
        switch ($name) {
            case 'type':
                return $this->curConType;
                break;
            case 'suf':
                if ($this->curConType != self::MYSQL) {
                    return 'dbo.';
                }
                return '';
            default:
                return false;
        }
    }

    private function __construct($conNum)
    {
        if ($conNum == -1) {
            $configs = array(
                -1 => [
                    'server' => $_SESSION['installServer'],
                    'db' => $_SESSION['installBuildDb'],
                    'user' => $_SESSION['installUsr'],
                    'password' => $_SESSION['installPwd'],
                    'type' => $_SESSION['installCt']
                ]
            );
        }
        else {

            $path = baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'connections.php';
            if (file_exists($path)) {
                $configs = require $path;
            }
            else {
                throw new CfgException($path . ' no such config file!');
            }

            if (empty($configs[$conNum]) && $conNum == 'siteBase') //если нет отдельно выделенного конфига под базу(с настройками) сайта, то переключаем в умолчание
                $conNum = 0;

            if (empty($configs[$conNum])) {
                throw new CfgException('config file corrupted');
            }

            if (empty($configs[$conNum]['type'])) {
                throw new CfgException('config file corrupted: connection type is empty');
            }
        }

        $this->curConType = $configs[$conNum]['type'];

        try {

            switch ($configs[$conNum]['type']) {
                case self::MSSQL:
                    $this->mssql($configs[$conNum]);
                    break;
                case self::MYSQL:
                    $this->mysql($configs[$conNum]);
                    break;
                case self::ODBC:
                    $this->odbc($configs[$conNum]);
                    break;
                case self::INSTALL:
                    return;
                case self::SQLSRV:
                    $this->sqlsrv($configs[$conNum]);
            }
        }
        catch (\PDOException $e) {
            throw new DBException($e->getMessage(), 1, $e);
        }
    }

    /**
     * @param array $params
     */
    private function odbc($params)
    {
        $this->resId = new \PDO('odbc:Driver={SQL Server};SERVER=' . $params['server'] . ';Database=' . $params['db'] . ';', $params['user'], $params['password']);
        $this->resId->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param array $params
     */
    private function mysql($params)
    {
        $this->resId = new \PDO('mysql:host=' . $params['server'] . ';dbname=' . $params['db'], $params['user'], $params['password'],$this->commands);
    }

    /**
     * @param array $params
     */
    private function mssql($params)
    {
        $this->resId = new \PDO('dblib:host=' . $params['server'] . ';dbname=' . $params['db'], $params['user'], $params['password']);
        $this->resId->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    private function sqlsrv($params)
    {
        $this->resId = new \PDO('sqlsrv:server=' . $params['server'] . ';Database=' . $params['db'], $params['user'], $params['password']);
        $this->resId->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * логи в таблицу mwc_logs
     *
     * @param string $msg - текст лога
     * @param string $file - файл, в котором ахтунг
     * @param int $errNo - номер ошибки
     * @param bool|true $isValid - экранировать ли текст лога?
     * @throws \Exception
     */
    public function SQLog($msg, $file = "1", $errNo = 0, $isValid = true)
    {

        if ($this->curConType == self::INSTALL) {
            Logs::textLog($errNo, $msg);
            return;
        }

        $this->closeCursor();
        
        if ($file == "1") {
            $file = basename(__FILE__, '.php');
        }

        if ($isValid == true) {
            $msg = htmlspecialchars($msg, ENT_QUOTES);
        }

        if (self::MSSQL == $this->curConType || self::ODBC == $this->curConType) {
            $dt = "GETDATE()";
            $suf = 'dbo.';
        }
        else {
            $dt = "NOW()";
            $suf = '';
        }

        $msg = str_replace('\\', '/', $msg);
        $file = str_replace('\\', '/', $file);
        self::$queryCount++;

        if(empty($msg))
            return;

        try {
            $this->resId->query("INSERT INTO mwce_settings.{$suf}mwc_logs(col_ErrNum,col_msg,col_mname,col_createTime,tbuild) VALUES($errNo,'$msg','{$file}',$dt,'" . Configs::currentBuild() . "')");
        }
        catch (\Exception $e) {
            Logs::textLog(1, $e->getMessage() . ' log text: ' . $msg);
        }
    }

    /**
     * функция возвращает последний insert id
     * @param string $tbname - название таблицы, куда была последняя вставка
     * @return int id
     */
    public function lastId($tbname = null)
    {
        try{
            return $this->resId->lastInsertId($tbname);
        }
        catch (\Exception $e){
            if ($this->curConType != self::MYSQL) // ms
            {
                if (!$tbname)
                    return NULL;

                $res = self::query("SELECT IDENT_CURRENT('{$tbname}') as lastid")->fetch();
            }
            else
            {
                $res = self::query("SELECT LAST_INSERT_ID()  as lastid")->fetch();
            }
            return $res["lastid"];
        }

    }

    /**
     * @param string $qtext
     * @param null|array $bind
     * @return bool|Connect|\PDO
     * @throws DBException
     */
    public function query($qtext, $bind = [])
    {

        self::$queryCount++;
        try{
            $this->lastQh = $this->resId->prepare($qtext);
            $this->lastQh->execute($bind);
        }
        catch (\Exception $e)
        {
            throw new DBException($e->getMessage().', log text: ' . $qtext);
        }

        
        return $this;
    }

    /**
     * принудительно осободить ресурсы для выполнения след. задания.
     * @return bool
     * @throws DBException
     */
    public function closeCursor()
    {
        try {
            if( isset($this->lastQh) && is_object($this->lastQh))
                $this->lastQh->closeCursor();
            return true;
        }
        catch (\Exception $e) {
            //throw new DBException($e->getMessage(),3,$e);
        }
        return false;
    }

    /**
     * @param string $qtext
     * @param array $bind
     * @return bool
     * @throws DBException
     */
    public function exec($qtext, $bind = [])
    {
        self::$queryCount++;
        try{
            $dbh = $this->resId->prepare($qtext);
            $res = $dbh->execute($bind);
        }
        catch (\Exception $e)
        {
            throw new DBException($e->getMessage().', log text: ' . $qtext);
        }
        
        return $res;
    }


    public function __call($name, $arguments)
    {
        $ars = '';
        $obj = $this->lastQh;

        if (is_object($obj)) {
            switch (strtolower($name)) {
                case 'getrows':
                case 'getarray':
                case 'fetchall':
                    try {
                        if (!empty($arguments[0])) {
                            $ars = $obj->fetchAll(\PDO::FETCH_CLASS, $arguments[0]);
                        }
                        else {
                            $ars = $obj->fetchAll(\PDO::FETCH_ASSOC);
                        }
                    }
                    catch (\Exception $e)
                    {
                        throw new DBException($e->getMessage(), 1, $e);
                    }
                    break;
                case 'fetch':
                case 'fetchrow':
                    try {
                        if (!empty($arguments[0])) {
                            $ars = $obj->fetchObject($arguments[0]);
                        }
                        else {
                            $ars = $obj->fetch(\PDO::FETCH_ASSOC);
                        }
                    }
                    catch (\Exception $e)
                    {
                        throw new DBException($e->getMessage(), 1, $e);
                    }
                    break;
                default:
                    break;
            }

            if (!empty($ars)) {
                return $ars;
            }

        }
        else {
            throw new DBException('Call to a member function ' . $name . '() on boolean');
        }

        return false;
    }


}
