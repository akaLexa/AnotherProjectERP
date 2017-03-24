<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 27.12.2016
 *
 **/
namespace build\erp\tabs\m;
use build\erp\inc\SpecWordParser;
use mwce\db\Connect;
use mwce\Tools\Date;
use mwce\Models\Model;

class mTabMessages extends Model
{

    public static function getModels($params = null)
    {
        $filter = '';
        if(!empty($params['isSys'])){
            $filter = 'tpm.col_system = 1';
        }
        else
            $filter = 'tpm.col_system = 0';

        if(!empty($params['projectID'])){
            $filter.= " AND tpm.col_projectID = {$params['projectID']}";
        }

        $db = Connect::start();
        return $db->query("SELECT 
  f_getUserFIO(tpm.col_AuthorID) AS col_Author,
  tpm.col_AuthorID,
  tpm.col_text,
  tpm.col_dateCreate
FROM 
  tbl_project_messages tpm
WHERE
$filter
order by tpm.col_dateCreate DESC")->fetchAll(static::class);
    }

    public static function getCurModel($id)
    {

    }

    /**
     * @param int $project
     * @param string $text
     * @param int $user
     * @param array $listeners - user ids
     */
    public static function addComment($project,$text,$user,$listeners){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_project_messages (col_AuthorID,col_text,col_projectID) VALUE($user,'$text',$project)");
        if(!empty($listeners) && is_array($listeners)){
            //$lid = $db->lastId('tbl_project_messages');
            $q = '';
            foreach ($listeners as $listener){
                if(!empty($q))
                    $q.=',';
                $q.="(11,$project,$listener, CONCAT('Проект [',f_GetProjectNum($project),'], написал [',f_getUserFIO($user),']: ',LEFT('$text',50),'...'))";
            }
            if(!empty($q)){
                $db->exec("INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUES $q");
            }
        }
    }

    /**
     * добавить запись на вкладку событий в проекте
     * @param int $project
     * @param string $text
     */
    public static function addEvent($project,$text){
        $db = Connect::start();
        $db->exec("INSERT INTO tbl_project_messages (col_AuthorID,col_text,col_projectID,col_system) VALUE(2,'$text',$project,1)");
    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_dateCreate':
                parent::_adding($name.'Legend', Date::transDate($value));
                parent::_adding($name.'LegendDT', Date::transDate($value,true));
                break;
            case 'col_text':
                $value = SpecWordParser::check($value);
                break;
        }
        parent::_adding($name, $value);
    }
}