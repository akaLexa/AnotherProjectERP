<?php

namespace build\erp\inc\traits;


trait tExcell
{
    /**
     * инициализация экселя
     * @return \PHPExcel
     */
    protected function initExcel()
    {
        require baseDir . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'PHPExcel' . DIRECTORY_SEPARATOR . 'PHPExcel.php';
        set_time_limit(0);

        define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator("erp")
            ->setLastModifiedBy("erp")
            ->setTitle("erp")
            ->setSubject("erp")
            ->setDescription("erp")
            ->setKeywords("erp")
            ->setCategory("erp");
        \PHPExcel_Cell::setValueBinder( new \PHPExcel_Cell_AdvancedValueBinder() );

        return $objPHPExcel;
    }

    /**
     * генерация с последующей выгрузкой файла
     * @param \PHPExcel $exObj
     * @param string $name - название файла
     * @param string $path - куда сохранять
     * @throws \PHPExcel_Reader_Exception
     */
    protected static function outWriterDoc(\PHPExcel $exObj, $name, $path = 'php://output')
    {
        //Logs::log(?, 'Пользователь mwcuid:' . $_SESSION['mwcuid'] . ' экспортирует ' . basename($name));
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . basename($name) . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($exObj, 'Excel2007');
        $objWriter->save($path);
        exit;
    }

    //region стили
    /**
     * стиль
     * @return array
     */
    protected static function getHeaderStyle()
    {
        return array(
            'borders' =>
                array(
                    'allborders' =>
                        array(
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => '000000'),
                        )
                ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('argb' => 'C0C0C0'),
            ),
            'alignment' => array(
                'vertical' =>\PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'horizontal' =>\PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'font'=>array(
                'bold' => true,
                'color' => array('argb' => 'FFFFFF'),
            ),
        );
    }

    /**
     * стиль
     * @return array
     */
    protected static function plantCellStyle()
    {
        return array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                )
            ),
            'alignment' => array(
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
            'font' => array(
                'bold' => false
            )
        );
    }


    protected static function borderBottomStyle()
    {
        return array(
            'borders' => array(
                'bottom' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array( 'argb' => '000000' ),
                )
            )
        );
    }

    //endregion
}
