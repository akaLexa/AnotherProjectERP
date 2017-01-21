<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 08.01.2017
 *
 **/
namespace build\erp\inc;
use mwce\Configs;
use mwce\Connect;
use mwce\Exceptions\ModException;
use mwce\Tools;
use mwce\traits\singleton;

class Files
{
    use singleton;

    /**
     * @var string адрес хранения файлов
     */
    protected static $docPath;

    /**
     * @var Connect
     */
    protected $db;

    /**
     * @var array MIME
     */
    protected $mimes = array(
        "png" => "image/png",
        "jpeg" => "image/jpeg",
        "jpg" => "image/jpeg",
        "gif" => "image/gif",
        "mpeg" => "video/mpeg",
        "mp4" => "video/mp4",
        "ogg" => "audio/ogg",
        "mp3" => "audio/mpeg",
        "avi" => "video/avi",
        "pdf" => "application/pdf",
        "zip" => "application/zip",
        "doc" => "application/msword",
        "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "txt" => "text/plain"
    );


    /**
     * расшифровка ошибки по номеру
     * @param int $num
     * @return string
     */
    public static function getFileError($num){
        //todo: возможно, если будет мультиязык, нужно загнать ошибки в словари
        $downerrors = array(
            1 => "Размер принятого файла превысил максимально допустимый размер",
            2 => "Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме",
            3 => "Загружаемый файл был получен только частично",
            4 => "Файл не был загружен. Возможно, он не был выбран при загрузке.",
            5 => "?",
            6 => "Отсутствует временная папка",
            7 => "Не удалось записать файл на диск",
            8 => "PHP-расширение остановило загрузку файла",
            0 => "Неизвестная ошибка",
        );

        switch ($num){
            case 1: return $downerrors[1];
                break;
            case 2: return $downerrors[2];
                break;
            case 3: return $downerrors[3];
                break;
            case 4: return $downerrors[4];
                break;
            case 5: return $downerrors[5];
                break;
            case 6: return $downerrors[6];
                break;
            case 7: return $downerrors[7];
                break;
            case 8: return $downerrors[8];
                break;
            default:
                return $downerrors[0];
                break;
        }
    }


    protected function __construct($con = 0)
    {
        $cfg = Configs::readCfg('project', tbuild);
        if (!empty($cfg['documentsFolder']))
            self::$docPath = $cfg['documentsFolder'];
        else
            throw new ModException('Не указан адрес, где хранятся файлы проекта!');

        $this->db = Connect::start($con);
    }

    /**
     * экранирование названий
     * @param string $word
     * @return mixed
     */
    public static function filterName($word)
    {
        $word = preg_replace("/[\s]/", "_", $word);
        return preg_replace("/[,]/", '', $word);
    }

    /**
     * выгрузка файлов
     * @param string $name
     * @param string $destination
     * @throws ModException
     */
    protected function startDownload($name, $destination)
    {
        if (ob_get_level()) {
            ob_end_clean();
        }
        $name = self::filterName($name);
        if (file_exists($destination)) {
            $m = explode(".", $name);
            $exe = strtolower(end($m));

            if (isset($this->mimes[$exe])) {
                $appl = $this->mimes[$exe];
            }
            else {
                $appl = "application/octet-stream";
            }

            header('Content-Description: File Transfer');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Disposition: inline; filename=' . $name);
            header('Content-Type: ' . $appl);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($destination));
            readfile($destination);
            exit;
        }
        throw new ModException("Файла $name не существует! Адрес:$destination");
    }


    //region project Upload
    /**
     * @param int $project
     * @param int $group
     * @param int $folderID
     * @param int $upUser
     * @throws ModException
     */
    public function projectUpload($project,$group,$folderID,$upUser){
        if(!empty($_FILES['dFile'])){
            if(empty($folderID))
                $folderID = 'NULL';

            if (!is_dir(self::$docPath . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $project)) {
               mkdir(self::$docPath . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $project);
            } //если нет папки проекта - создаем

            if (!is_dir(self::$docPath . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $project . DIRECTORY_SEPARATOR . $group)) {
                mkdir(self::$docPath . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . $project . DIRECTORY_SEPARATOR . $group);
            } //если нет папки группы документов - создаем

            if(!empty($_FILES['dFile']['name'])){
                $cnt = count($_FILES['dFile']['name']);
                for($i=0;$i<$cnt;$i++){
                    if(!empty($_FILES['dFile']['error'][$i])){
                        throw new ModException(self::getFileError($_FILES['dFile']['error'][$i]));
                    }

                    $curFile = array();
                    if(!empty($_FILES['dFile']['name'][$i])){
                        $tmp = htmlspecialchars($_FILES['dFile']['name'][$i],ENT_QUOTES);
                        $curFile['name'] = substr($tmp,0,254);
                        $ex = explode('.',$tmp);
                        if(count($ex)>0)
                            $curFile['ext'] = "'".end($ex)."'";
                        else
                            $curFile['ext'] = 'NULL';
                        $curFile['size'] = round(($_FILES['dFile']['size'][$i]/1048576),2); //save mb size

                        $this->db->exec("INSERT INTO tbl_files (col_fName,col_ext,col_parentID,col_size,col_uploaderID,col_groupID,col_projectID) VALUES('{$curFile['name']}',{$curFile['ext']},$folderID,{$curFile['size']},$upUser,$group,$project)");
                        $lid = $this->db->lastId('tbl_files');
                        if(!move_uploaded_file($_FILES['dFile']['tmp_name'][$i],self::$docPath.DIRECTORY_SEPARATOR .'projects'.DIRECTORY_SEPARATOR.$project.DIRECTORY_SEPARATOR.$group.DIRECTORY_SEPARATOR.$lid)){
                            $this->db->exec('DELETE FROM tbl_files WHERE col_fID ='.$lid);
                            throw new ModException('Файл не был загружен из-за неизвестной ошибки!');
                        }
                    }
                    else{
                        continue;
                    }
                }
            }
            else
                throw new ModException('Нет выбранных файлов!');
        }
        else
            throw new ModException('Нет выбранных файлов!');
    }
    //endregion

    /**
     * ini_set('implicit_flush', true);
    // ini_set("upload_max_filesize", "600M");
    // ini_set("memory_limit", "656M");
    //ini_set("post_max_size", "300M");

    ob_implicit_flush(true);



     */
}