<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 17.04.2017
 *
 **/
namespace build\erp\reports\m;
use build\erp\inc\Project;
use build\erp\inc\traits\tExcell;
use mwce\db\Connect;
use mwce\Models\Model;
use mwce\Tools\Date;

class mProjectStageReport extends Model
{
    use tExcell;
    private static $states = null;

    public static function getModels($params = null)
    {
        $db = Connect::start();
        $filter = '';

        if(!empty($params['prName'])){
            $filter.= " AND tp.col_projectName like '%{$params['prName']}%'";
        }

        if(!empty($params['curManager'])){
            $filter.= " AND tp.col_founderID = ".$params['curManager'];
        }

        if(!empty($params['curResp'])){
            $filter.= " AND tps.col_respID = ".$params['curResp'];
        }

        if(!empty($params['curStage'])){
            $filter.= " AND tps.col_stageID = ".$params['curStage'];
        }

        if(!empty($params['prNum'])){
            $filter.= " AND tp.col_pnID = ".$params['prNum'];
        }

        if(!empty($params['dBegin'])){
            $filter.= " AND tps.col_dateStart BETWEEN '{$params['dBegin']} 00:00:00' AND '{$params['dBegin']} 23:59:59'";
        }

        if(!empty($params['dEndPlan'])){
            $filter.= " AND tps.col_dateEndPlan BETWEEN '{$params['dEndPlan']} 00:00:00' AND '{$params['dEndPlan']} 23:59:59'";
        }

        if(!empty($params['dEndFact'])){
            $filter.= " AND tps.col_dateEndFact BETWEEN '{$params['dEndFact']} 00:00:00' AND '{$params['dEndFact']} 23:59:59'";
        }

        return $db->query("SELECT
  tp.col_projectID,
  tp.col_pnID,
  tp.col_projectName,
  tp.col_founderID,
  f_getUserFIO(tp.col_founderID) as col_founder,
  thps.col_StageName,
  tps.col_statusID,
  tps.col_respID,
  f_getUserFIO(tps.col_respID) as col_resp,
  DATEDIFF(tps.col_dateEndPlan,COALESCE(tps.col_dateEndFact,NOW())) AS col_freeDays,
  tps.col_dateStart,
  tps.col_dateEndFact,
  tps.col_dateEndPlan
FROM
  tbl_project tp,
  tbl_project_stage tps,
  tbl_hb_project_stage thps
WHERE
  tps.col_projectID = tp.col_projectID
  AND tps.col_statusID !=5
  AND thps.col_StageID = tps.col_stageID
  $filter")->fetchAll(static::class);
    }

    public static function getCurModel($id)
    {

    }

    public function getExcel($params){

        $list = self::getModels($params);
        if(!empty($list)){
            $objPHPExcel = self::initExcel();
            $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray(self::getHeaderStyle());
            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
            $objPHPExcel->getSheet(0)->setCellValue('A1', "#");
            $objPHPExcel->getSheet(0)->setCellValue('B1', "Проект");
            $objPHPExcel->getSheet(0)->setCellValue('C1', "Менеджер");
            $objPHPExcel->getSheet(0)->setCellValue('D1', "Стадия");
            $objPHPExcel->getSheet(0)->setCellValue('E1', "Статус");
            $objPHPExcel->getSheet(0)->setCellValue('F1', "Ответственный");
            $objPHPExcel->getSheet(0)->setCellValue('G1', "Начало");
            $objPHPExcel->getSheet(0)->setCellValue('H1', "Плановое завершение");
            $objPHPExcel->getSheet(0)->setCellValue('I1', "Фактическое завершение");
            $objPHPExcel->getSheet(0)->setCellValue('J1', "Разница");

            $i = 2;
            $ai = new \ArrayIterator($list);

            $objPHPExcel->getActiveSheet()->getStyle('E:F')
                ->getNumberFormat()
                ->setFormatCode('#,##0.00 р\.');
            $objPHPExcel->getActiveSheet()->getStyle('H:I')
                ->getNumberFormat()
                ->setFormatCode('#,##0.00 р\.');

            \PHPExcel_Cell::setValueBinder( new \PHPExcel_Cell_AdvancedValueBinder() );


            foreach ($ai as $res)
            {

                if(empty($res["col_dateEndFact"]))
                    $res["col_dateEndFact"] = '';

                $objPHPExcel->getSheet(0)
                    ->setCellValue('A'.$i, $res["col_pnID"])
                    ->setCellValue('B'.$i, $res["col_projectName"])
                    ->setCellValue('C'.$i, $res["col_founder"])
                    ->setCellValue('D'.$i, $res["col_StageName"])
                    ->setCellValue('E'.$i, $res["col_statusIDLegend"])
                    ->setCellValue('F'.$i, $res["col_resp"])
                    ->setCellValue('G'.$i, $res["col_dateStart"])
                    ->setCellValue('H'.$i, $res["col_dateEndPlan"])
                    ->setCellValue('I'.$i, $res["col_dateEndFact"])
                    ->setCellValue('J'.$i, $res["col_freeDays"])
                ;

                $objPHPExcel->setActiveSheetIndex(0)->getStyle("A$i:J$i")->applyFromArray(self::plantCellStyle());
                $objPHPExcel->getSheet(0)->getStyle("G$i:I$i")->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
                $i++;
            }

            $objPHPExcel->getActiveSheet()->setAutoFilter('A1:J'.$i);
            self::outWriterDoc($objPHPExcel, 'Отчет о стадиях в проекте.xlsx');
        }
        else
            exit('no data');
    }

    protected function _adding($name, $value)
    {

        switch ($name){
            case 'col_dateStart':
            case 'col_dateEndFact':
            case 'col_dateEndPlan':
                parent::_adding($name.'Legend', Date::transDate($value,true));
                break;
            case 'col_statusID':

                if(is_null(self::$states))
                    self::$states = Project::getStates();
                if(!empty(self::$states[$value]))
                    parent::_adding($name.'Legend', self::$states[$value]);

                break;
        }
        parent::_adding($name, $value);
    }
}