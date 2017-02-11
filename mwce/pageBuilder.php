<?php

/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 08.04.2016
 *
 **/
namespace mwce;
use mwce\traits\singleton;

/**
 * Class pageBuilder
 * @package mwce
 *
 * отвечает за генерацию списка зарегистрированных
 * плагинов и страниц
 */
class pageBuilder implements iStartable
{
    /**
     * @var Connect
     */
    protected $db;

    /**
     * @var string
     */
    protected $lang;

    use singleton;

    protected function __construct($lang)
    {
        $this->lang = $lang;
        $this->checkBase();
    }

    /**
     * генерирует спислк страниц с доступами
     */
    private function buildPage()
    {
        $inf = '';
        /*
        if ($this->db->type == Connect::MSSQL || $this->db->type == Connect::ODBC)//если ms sql 2012++
            $q = $this->db->query("SELECT
id,
pname,
ptitle,
ppath,
caching,
ison,
isClass,
left(groups,len(groups)-1) as groups
FROM 
(
SELECT mp.*, (
SELECT CONCAT(ma.goupId ,',') as 'data()'
FROM mwce_settings.dbo.mwc_access AS ma 
WHERE ma.pageID = mp.id 
for xml path('')
) as groups
FROM 
mwce_settings.dbo.mwc_pages mp 
WHERE 
mp.tbuild = '" . tbuild . "')t");
        else
            $q = $this->db->query("SELECT *, (SELECT GROUP_CONCAT(goupId) FROM mwce_settings.mwc_access WHERE pageId = id) AS groups FROM mwce_settings.mwc_pages  WHERE tbuild = '" . tbuild . "'");

        $inf = '';


        while ($res = $q->FetchRow()) {

            $inf .= '"' . $res["pname"] . '"=>["title"=> "' . $res["ptitle"] . '","ppath"=>"' . str_replace('/', '\\', $res["ppath"]) . '","caching"=>"' . $res["caching"] . '","ison"=>"' . $res["ison"] . '","isClass"=>"' . $res["isClass"] . '",';

            if ($this->db->type == Connect::MSSQL || $this->db->type == Connect::ODBC)//если ms sql ...да, страшный костыль...
            {
                $inf .= '"groups" => "' . str_replace(' ', '', $res["groups"]) . '", ';
            }
            else {
                $inf .= '"groups" => "' . $res["groups"] . '", ';
            }
            $inf .= "],\r\n";

        }
*/
        if ($this->db->type == Connect::MSSQL || $this->db->type == Connect::ODBC || $this->db->type == Connect::SQLSRV){
            $pages = $this->db->query("SELECT * FROM mwce_settings.dbo.mwc_pages  WHERE tbuild = '" . Configs::currentBuild() . "'")->fetchAll();

            if(!empty($pages)){
                foreach ($pages as $res) {
                    $inf .= '"' . $res["pname"] . '"=>["title"=> "' . $res["ptitle"] . '","ppath"=>"' . str_replace('/', '\\', $res["ppath"]) . '","caching"=>"' . $res["caching"] . '","ison"=>"' . $res["ison"] . '","isClass"=>"' . $res["isClass"] . '",';

                    $q = $this->db->query("SELECT goupId FROM mwce_settings.dbo.mwc_access WHERE pageId = " . $res["id"])->fetchAll();
                    $res["groups"] = '';
                    if (is_array($q)) {
                        foreach ($q as $vals) {
                            if ($res["groups"] != '')
                                $res["groups"] .= ',';

                            $res["groups"] .= $vals['goupId'];
                        }
                    }

                    $inf .= '"groups" => "' . $res["groups"] . '", ';
                    $inf .= "],\r\n";
                }
            }
        }
        else
        {
            $q = $this->db->query("SELECT *, (SELECT GROUP_CONCAT(goupId) FROM mwce_settings.mwc_access WHERE pageId = id) AS groups FROM mwce_settings.mwc_pages  WHERE tbuild = '" . Configs::currentBuild() . "'");
            while ($res = $q->FetchRow()) {

                $inf .= '"' . $res["pname"] . '"=>["title"=> "' . $res["ptitle"] . '","ppath"=>"' . str_replace('/', '\\', $res["ppath"]) . '","caching"=>"' . $res["caching"] . '","ison"=>"' . $res["ison"] . '","isClass"=>"' . $res["isClass"] . '",';
                $inf .= '"groups" => "' . $res["groups"] . '", ';
                $inf .= "],\r\n";

            }
        }

        if (!empty($inf))
            $this->writef(baseDir.DIRECTORY_SEPARATOR. "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_pages.php", '<?php return array(' . $inf . ');');

    }

    /**
     * генерирует список плагинов с доступами
     */
    private function buildPlugin()
    {
        $inf = '';
        /*if ($this->db->type == Connect::MSSQL || $this->db->type == Connect::ODBC)//если ms sql
            $q = $this->db->query("SELECT
pid,
pname,
seq,
pcache,
pstate,
isClass,
left(groups,len(groups)-1) as groups
FROM 
(
SELECT mp.*, (
SELECT CONCAT(ma.col_groupID ,',') as 'data()'
FROM mwce_settings.dbo.mwc_pluginsaccess AS ma 
WHERE ma.col_pluginID = mp.pid 
for xml path('')
) as groups
FROM 
mwce_settings.dbo.mwc_plugins mp 
WHERE 
mp.tbuild = '" . tbuild . "')t");
        else
            $q = $this->db->query("SELECT *, (SELECT GROUP_CONCAT(col_groupID) FROM mwce_settings.mwc_pluginsaccess WHERE col_pluginID = pid) AS groups FROM mwce_settings.mwc_plugins WHERE tbuild = '" . tbuild . "'");



        while ($res = $q->FetchRow()) {

            $res["pstate"] = !empty($res["pstate"]) ? 1 : 0;
            $inf .= '"' . $res["pname"] . '" => ["pstate" => "' . $res["pstate"] . '", "pcache"=> "' . $res["pcache"] . '","isClass"=> "' . $res["isClass"] . '",';

            if ($this->db->type == Connect::MSSQL || $this->db->type == Connect::ODBC)//если ms sql ...да, страшный костыль...
                $inf .= '"groups" => "' . str_replace(' ', '', $res["groups"]) . '", ';
            else
                $inf .= '"groups" => "' . $res["groups"] . '", ';

            $inf .= "],\r\n";
        }*/

        if ($this->db->type == Connect::MSSQL || $this->db->type == Connect::ODBC || $this->db->type == Connect::SQLSRV){
            $plugins = $this->db->query("SELECT * FROM mwce_settings.dbo.mwc_plugins  WHERE tbuild = '" . Configs::currentBuild() . "'")->fetchAll();
            if(!empty($plugins)){
                foreach ($plugins as $res)
                {
                    $res["pstate"] = !empty($res["pstate"]) ? 1 : 0;
                    $inf .= '"' . $res["pname"] . '" => ["pstate" => "' . $res["pstate"] . '", "pcache"=> "' . $res["pcache"] . '","isClass"=> "' . $res["isClass"] . '",';

                    $q = $this->db->query("SELECT col_groupID FROM mwce_settings.dbo.mwc_pluginsaccess WHERE col_pluginID = ".$res["pid"])->fetchAll();
                    $res["groups"]='';
                    if(is_array($q))
                    {
                        foreach ($q as $vals)
                        {
                            if($res["groups"]!='')
                                $res["groups"].=',';

                            $res["groups"].= $vals['col_groupID'];
                        }
                    }

                    $inf .= '"groups" => "' . $res["groups"] . '", ';
                    $inf .= "],\r\n";
                }
            }
        }
        else
        {
            $q = $this->db->query("SELECT *, (SELECT GROUP_CONCAT(col_groupID) FROM mwce_settings.mwc_pluginsaccess WHERE col_pluginID = pid) AS groups FROM mwce_settings.mwc_plugins WHERE tbuild = '" . Configs::currentBuild() . "'");
            while ($res = $q->fetch()) {

                $res["pstate"] = !empty($res["pstate"]) ? 1 : 0;
                $inf .= '"' . $res["pname"] . '" => ["pstate" => "' . $res["pstate"] . '", "pcache"=> "' . $res["pcache"] . '","isClass"=> "' . $res["isClass"] . '",';
                $inf .= '"groups" => "' . $res["groups"] . '", ';
                $inf .= "],\r\n";
            }
        }

        $this->writef(baseDir.DIRECTORY_SEPARATOR."build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_plugins.php", '<?php return array(' . $inf . ');');

    }

    /**
     * принудительное обновление баз доступа к модулям
     *
     * @param int $type 0 - модули и плагины, 1- только модули, 2- только плагины
     */
    public function refresh($type = 0)
    {
        if ($type == 0) {
            unlink("build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_pages.php");
            unlink("build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_plugins.php");
        }
        elseif ($type == 1)
            unlink("build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_pages.php");
        else
            unlink("build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_plugins.php");

        $this->checkBase();
    }

    /**
     * запись файла
     *
     * @param string $fname полый адрес до хранения файла
     * @param string $content что писать
     */
    public function writef($fname, $content)
    {
        try{
            $fh = fopen($fname, "w");
            fwrite($fh, $content);
            fclose($fh);
        }
        catch (\Exception $e){
            Logs::log($e);
        }

    }

    /**
     * проверка файлов на наличие
     */
    public function checkBase()
    {
        if (!file_exists(baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_pages.php")) {
            if (is_null($this->db))
                $this->db = Connect::start('siteBase');
            $this->buildPage();
        }

        if (!file_exists(baseDir . DIRECTORY_SEPARATOR ."build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_plugins.php")) {
            if (is_null($this->db))
                $this->db = Connect::start('siteBase');
            $this->buildPlugin();
        }
    }

    /**
     * список зарегистрированных страниц с доступами
     * @return array
     */
    public function getPages()
    {
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_pages.php";
        if (file_exists($path)) {
            return include $path;
        }

        return [];
    }

    /**
     * список зарегистрированных Плагинов
     * @return array
     */
    public function getPlugins()
    {
        $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_plugins.php";
        if (file_exists($path)) {
            return include $path;
        }
        return [];
    }

    //region magic
    public function __get($name)
    {
        switch ($name) {
            case 'pages':
                $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_pages.php";
                if (file_exists($path)) {
                    return include $path;
                }
                break;
            case 'plugins':
                $path = baseDir . DIRECTORY_SEPARATOR . "build" . DIRECTORY_SEPARATOR . Configs::currentBuild() . DIRECTORY_SEPARATOR . "_dat" . DIRECTORY_SEPARATOR . $this->lang . "_plugins.php";
                if (file_exists($path)) {
                    return include $path;
                }
                break;
        }

        return false;
    }

    public function __set($name, $value)
    {

    }

    public function __isset($name)
    {

    }
    //endregion
}