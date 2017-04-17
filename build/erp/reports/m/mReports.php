<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 16.04.2017
 *
 **/
namespace build\erp\reports\m;
use mwce\db\Connect;
use mwce\Models\Model;
use mwce\Tools\Configs;
use mwce\Tools\DicBuilder;

class mReports extends Model
{

    public static function getModels($params = null)
    {

    }

    public static function getCurModel($id)
    {

    }

    /**
     * список доступных отчетов для текущего пользователя
     * @return array
     */
    public static function getReportsList(){
        $db = Connect::start();
        $list = $db->query("SELECT 
  tm.col_moduleName,
  tm.col_title
FROM 
  tbl_modules tm 
      LEFT JOIN tbl_module_groups tmg ON tmg.col_modID = tm.col_modID
      LEFT JOIN tbl_module_roles tmr ON tm.col_modID = tmr.col_modID
WHERE 
  LOCATE('reports',tm.col_path) > 0
   AND tm.col_moduleName !='Reports'
   AND (tmg.col_gID IN (".Configs::curGroup().",3) OR tmr.col_roleID IN(".Configs::curRole().",2))
 GROUP BY tm.col_modID");

        $listLegend = [];
        $titles = DicBuilder::getLang("build".DIRECTORY_SEPARATOR.Configs::currentBuild().DIRECTORY_SEPARATOR."lang".DIRECTORY_SEPARATOR.Configs::buildCfg('dlang').DIRECTORY_SEPARATOR."titles.php");
        while ($res = $list->fetch()){
            $listLegend[$res['col_moduleName']] = !empty($titles[$res['col_title']]) ? $titles[$res['col_title']] : $res['col_title'];
        }

        return $listLegend;
    }
}