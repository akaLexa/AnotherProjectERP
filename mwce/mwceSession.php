<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 24.09.2016
 * класс-обертка для сессий с механизмом обновления оных
 * из базы данных. рассчитан на работу в режиме master-slave между сабдоменами,
 * где master - основной домен, а slave - все домены, что будут содержать
 * вспомогательные сервисы и по сессиям, определять, кто обращается к скрипту
 **/

namespace mwce;

class mwceSession
{
    /**
     * @var mwceSession
     */
    protected static $inst = null;

    /**
     * @var int 1/0 можно писать данные или  нет
     */
    public static $readOnly = 0;

    /**
     * @var string домен, вокруг которого настроены субдомены
     */
    protected static $domain = ''; //.domain.ru

    /**
     * @var null|int текущий id сесии
     */
    protected static $curSesID = null;

    /**
     * @var null|string идентификатор сессии из кука
     */
    protected static $cookSesID = null;

    /**
     * @var string название кука, где лежит ид сессии
     * у read-only саб-доменов (через запятую)
     */
    protected static $cookSlaveName ='';
    /**
     * @var int актуальный срок жизни куков
     */
    protected static $actialDate = 3600 * 24;

    /**
     * @var Connect
     */
    protected $db;

    /**
     * @var string соль для генерации ид сессии
     */
    protected $salt = '1234Rtst!@#';

    /**
     * @var string название кука, где лежит ид сессии
     */
    protected $cookName ='PHPSESSID';

    /**
     * @var array сохраняемые значания
     */
    protected $values =[];

    /**
     * @var string ip адресс клиента
     */
    protected $ip;

    /**
     * текущий домен
     * @return string
     */
    public static function getDomain(){
        return self::$domain;
    }

    /**
     * р/о демены
     * @return array
     */
    public static function getSlaveDomains(){
        return explode(',',self::$cookSlaveName);
    }

    /**
     * mwceSession constructor.
     */
    protected function __construct($sesidName = null)
    {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $this->ip = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        }
        else{
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        self::$actialDate = time()+3600*48; //срок жизни куков

        if(is_null($sesidName)) //название кука для идентификатора сессии
            $this->cookName ='PHPSESSID';
        else
            $this->cookName = $sesidName;

        if(!empty(self::$domain))
            session_set_cookie_params (self::$actialDate , '/', self::$domain);

        $this->db = Connect::start();

        if(empty($_COOKIE['mwc_sess_id']) || empty($_COOKIE['mwc_sess_time'])){
            self::getNewSessions();
        }
        else{
            self::$cookSesID = htmlspecialchars($_COOKIE['mwc_sess_id'],ENT_QUOTES);


            if(
                (time()-$_COOKIE['mwc_sess_time']) >= 0
                || empty($_COOKIE[$this->cookName])
			    || !empty($_COOKIE['mwc_need_refresh'])
			  ){

                if(isset($_COOKIE['mwc_sess_time'])){
                    unset($_COOKIE['mwc_sess_time']);
                    setcookie('mwc_sess_time', '', time()-1);
                }

				self::getSessions();

                if(empty($_COOKIE[$this->cookName])){
					setcookie('mwc_sess_ip', $this->ip , self::$actialDate ,'/', self::$domain);
					Tools::go();
				}

            }
        }
    }

    /**
     * singleton, точка входа
     * @return mwceSession
     */
    public static function start($sesidName = null)
    {
        if(is_null(self::$inst))
            self::$inst = new self($sesidName);
        return self::$inst;
    }

    /**
     * уничтожить данные о сессии из куков
     */
    public static function destroy(){

        if(!empty($_COOKIE['mwc_sess_id']))
            unset($_COOKIE['mwc_sess_id']);

        if(!empty($_COOKIE['mwc_sess_time']))
            unset($_COOKIE['mwc_sess_time']);

        setcookie('mwc_sess_id', '', time() -1,'/');
        setcookie('mwc_sess_time', '', time()-1,'/');

        if(!empty(self::$cookSlaveName)){
            $tm = explode(',',self::$cookSlaveName);
            foreach ($tm as $vas){

                if(!empty($_COOKIE[$vas]))
                    unset($_COOKIE[$vas]);

                setcookie($vas, '', time());
            }
        }

        self::$cookSesID = null;
        if(!is_null(self::$curSesID)){
            $db = Connect::start();
            $db->exec("DELETE FROM  mwce_settings.mwc_session_id WHERE col_sesID =".self::$curSesID);
        }

        header("Refresh:2");
    }

    /**
     * запущен механизм кеширования сессий или нет
     * @return bool
     */
    public static function isStarted(){
        return !is_null(self::$inst);
    }

    /**
     * создает новый идентификатор сессии
     * и проверяет, есть ли данные по нему
     */
    public function getNewSessions(){
        if (self::$readOnly  == 1)
            return;

        $idSes = '-'.md5($_SERVER['REMOTE_ADDR'] . '<||>' . self::$domain . '<||>' . $this->salt . time() . mt_rand(1,999)).'_';
        $this->db->exec("INSERT INTO mwce_settings.mwc_session_id (col_sesIdent,col_ip) VALUE('$idSes','{$this->ip}')");
        /**
         * механизм следует переделать, если время сервера базы данных и сервера сайта разные!
         */
        self::$curSesID = $this->db->lastId('mwc_session_id');
        self::$cookSesID = $idSes;

        //если есть поддомены, то убиваем их куки, для актуальности данных
        if(!empty(self::$domain)){
            setcookie('mwc_sess_id', $idSes, self::$actialDate,'/', self::$domain);

            if(!empty(self::$cookSlaveName)){
                $tm = explode(',',self::$cookSlaveName);
                foreach ($tm as $vas){

                    if(!empty($_COOKIE[$vas]))
                        unset($_COOKIE[$vas]);

                    setcookie($vas, '', time()-1, '/', self::$domain);
                }
            }
        }
        else{
            setcookie('mwc_sess_id', $idSes, self::$actialDate,'/' );
            if(!empty(self::$cookSlaveName)){
                $tm = explode(',',self::$cookSlaveName,'/');
                foreach ($tm as $vas){

                    if(!empty($_COOKIE[$vas]))
                        unset($_COOKIE[$vas]);

                    setcookie($vas, '', time()-1 ,'/');
                }
            }
        }

        if(!self::getSessions()){

            $_SESSION['mwc_sess_curID'] = self::$curSesID ;
            $inf = $this->db->query("SELECT col_create FROM mwce_settings.mwc_session_id WHERE col_sesIdent ='$idSes'")->fetch();

            //если сессия существует в базе
            if(!empty($inf['col_create'])){
                if(!empty(self::$domain))
                    setcookie('mwc_sess_time', self::$actialDate , self::$actialDate,'/', self::$domain);
                else
                    setcookie('mwc_sess_time', self::$actialDate , self::$actialDate, '/');

                $_COOKIE['mwc_sess_time'] = self::$actialDate;
            }
        }
    }

    /**
     * обновление данных по сесии
     * возватит false, если сессия новая и true, если есть данные
     * @return bool
     */
    public function getSessions(){

        $list = $this->db->query("SELECT 
 msi.col_create,
 msc.col_name,
 msc.col_value,
 msi.col_sesID
FROM 
 mwce_settings.mwc_session_id msi,
 mwce_settings.mwc_session_container msc
WHERE 
 msc.col_sesID = msi.col_sesID
 AND msi.col_sesIdent = '".self::$cookSesID."' ");//AND msi.col_ip='{$this->ip}'

        $timeFlag = false;

        while ($res = $list->fetch()){

            if(!$timeFlag){
                if(!empty(self::$domain)){
					setcookie('mwc_sess_time', self::$actialDate , self::$actialDate ,'/', self::$domain);
				}

                else
                    setcookie('mwc_sess_time', self::$actialDate , self::$actialDate );
                $timeFlag = true;
                self::$curSesID = $res['col_sesID'];
                $_SESSION['mwc_sess_curID'] = self::$curSesID ;
            }

            $_SESSION[$res['col_name']] = $res['col_value'];
        }


        if (!$timeFlag){
            if(empty($_COOKIE[$this->cookName])){
                self::destroy();
            }
            return false; //нет данных
        }

		if(!empty($_COOKIE['mwc_need_refresh'])){
			unset($_COOKIE['mwc_need_refresh']);
			 setcookie('mwc_need_refresh', '', time() -1,'/');
		}


        return true;
    }

    /**
     * набивка параметров сессии, что должны быть сохранены в базу
     * @param $id mixed|array либо массив, либо идентификатор
     * @param null|mixed $vall значение
     */
    public function set($id,$vall =null){
        if(!is_array($id) && is_null($vall))
            return;
        else if(is_array($id)){
            foreach ($id as $i_=>$val){
                self::set($i_,$val);
            }
        }
        else{
            $this->values[$id] = $vall;
        }
    }

    /**
     * сохранение в бд данных сессии
     * @param int $isClean флаг, чистить после сохранение данные или нет
     * @param int $setInSes флаг, сохранять ли в сессию данные
     */
    public function save($isClean = 1,$setInSes = 1){

        if(self::$readOnly == 1)
            return;

        if(empty(self::$cookSesID))
            self::getNewSessions();


        if(is_null(self::$curSesID)){
            self::getNewSessions();
        }

        if(is_null(self::$curSesID)){
            return;
        }

        $q = '';
        foreach ($this->values as $id=>$value){
            if(!empty($q))
                $q.=',';

            if(!empty($setInSes)){
                $_SESSION[$id] = $value;
            }
            $q.= "(".self::$curSesID.",'$id','$value')";
        }

        if(!empty($q)){
            $this->db->exec("INSERT INTO mwce_settings.mwc_session_container (col_sesID,col_name,col_value) VALUES $q");
        }

        if(!empty($isClean))
            $this->values = [];
    }
}