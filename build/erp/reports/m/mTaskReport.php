<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 19.04.2017
 *
 **/
namespace build\erp\reports\m;
use build\erp\inc\traits\tExcell;
use mwce\db\Connect;
use mwce\Models\Model;
use mwce\Tools\Date;

class mTaskReport extends Model
{

    use tExcell;

    public static function getModels($params = null)
    {
        $db = Connect::start();
        $filter = '';

        if(!empty($params['inTname']))
            $filter.=" AND tt.col_taskName like '%{$params['inTname']}%'";

        if(!empty($params['curStatus'])){
            $filter.=" AND tt.col_StatusID = ".$params['curStatus'];
        }

        if(!empty($params['curInit'])){
            $filter.=" AND tt.col_initID = ".$params['curInit'];
        }

        if(!empty($params['curResp'])){
            $filter.=" AND tt.col_respID = ".$params['curResp'];
        }

        if(!empty($params['curRole'])){
            $filter.=" AND tu.col_roleID = ".$params['curRole'];
        }

        if(!empty($params['dBegin'])){
            $filter.=" AND tt.col_startFact BETWEEN '{$params['dBegin']} 00:00:00' AND '{$params['dBegin']} 23:59:59' ";
        }

        if(!empty($params['dEndPlan'])){
            $filter.=" AND tt.col_endPlan BETWEEN '{$params['dEndPlan']} 00:00:00' AND '{$params['dEndPlan']} 23:59:59' ";
        }

        if(!empty($params['dEndFact'])){
            $filter.=" AND tt.col_endFact BETWEEN '{$params['dEndFact']} 00:00:00' AND '{$params['dEndFact']} 23:59:59' ";
        }

        return $db->query("SELECT
  tt.col_taskID,
  tt.col_initID,
  tt.col_respID,
  f_getUserFIO(tt.col_initID) as col_init,
  f_getUserFIO(tt.col_respID) as col_resp,
  tt.col_taskName,
  tt.col_startFact,
  tt.col_endPlan,
  tt.col_endFact,
  tt.col_StatusID,
  sts.col_StatusName,
  DATEDIFF(tt.col_startFact,COALESCE(tt.col_endFact,NOW())) AS col_freeDate,
  tu.col_roleID
FROM
  tbl_tasks tt,
  tbl_hb_status sts,
  tbl_user tu
WHERE
  tt.col_StatusID != 5
  AND sts.col_StatusID = tt.col_StatusID
  AND tu.col_uID = tt.col_initID
  $filter")->fetchAll(static::class);
    }

    public static function getCurModel($id)
    {

    }

    protected function _adding($name, $value)
    {
        switch ($name){
            case 'col_startFact':
            case 'col_endPlan':
            case 'col_endFact':
            parent::_adding($name.'Legend', Date::transDate($value));
        }
        parent::_adding($name, $value);
    }

    public function getExcel($params){

        $list = self::getModels($params);
        if(!empty($list)){
            $objPHPExcel = self::initExcel();
            $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(self::getHeaderStyle());
            $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
            $objPHPExcel->getSheet(0)->setCellValue('A1', "Задача	");
            $objPHPExcel->getSheet(0)->setCellValue('B1', "Состояние");
            $objPHPExcel->getSheet(0)->setCellValue('C1', "Инициатор");
            $objPHPExcel->getSheet(0)->setCellValue('D1', "Ответственный");
            $objPHPExcel->getSheet(0)->setCellValue('E1', "Начало");
            $objPHPExcel->getSheet(0)->setCellValue('F1', "Плановое завршение");
            $objPHPExcel->getSheet(0)->setCellValue('G1', "Фактическое завершение");
            $objPHPExcel->getSheet(0)->setCellValue('H1', "Разница");

            $i = 2;
            $ai = new \ArrayIterator($list);

            \PHPExcel_Cell::setValueBinder( new \PHPExcel_Cell_AdvancedValueBinder() );


            foreach ($ai as $res)
            {

                if(empty($res["col_endFact"]))
                    $res["col_endFact"] = '';

                $objPHPExcel->getSheet(0)
                    ->setCellValue('A'.$i, $res["col_taskName"])
                    ->setCellValue('B'.$i, $res["col_StatusName"])
                    ->setCellValue('C'.$i, $res["col_init"])
                    ->setCellValue('D'.$i, $res["col_resp"])
                    ->setCellValue('E'.$i, $res["col_startFact"])
                    ->setCellValue('F'.$i, $res["col_endPlan"])
                    ->setCellValue('G'.$i, $res["col_endFact"])
                    ->setCellValue('H'.$i, $res["col_freeDate"])
                ;

                $objPHPExcel->setActiveSheetIndex(0)->getStyle("A$i:H$i")->applyFromArray(self::plantCellStyle());
                $objPHPExcel->getSheet(0)->getStyle("E$i:G$i")->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
                $i++;
            }

            $objPHPExcel->getActiveSheet()->setAutoFilter('A1:H'.$i);
            self::outWriterDoc($objPHPExcel, 'Отчет о задачах.xlsx');
        }
        else
            exit('no data');
    }
}