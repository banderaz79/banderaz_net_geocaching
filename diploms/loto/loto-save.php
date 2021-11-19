<?php 
ob_start();
session_start(); 

if(!$_SESSION['saveloto'] and !$_SESSION['cards']) 
{
	$new_url = 'index.php';
	header('Location: '.$new_url);
	ob_end_flush();
	exit;
}
ob_end_flush();


$savekarta = $_POST['savekarta'];
$saveloto = $_SESSION['saveloto'];
$card = explode(',',$_SESSION['cards'][$savekarta]);
$file = $_SESSION['userid'].'_geoloto.xlsx';

$types=array(
	'TR' => 'Традиционный',
	'MS' => 'Традиционный пошаговый',
	'VI' => 'Виртуальный',
	'MV' => 'Пошаговый виртуальный');

$chisla=array('Первое','Второе','Третье','Четвертое','Пятое','Шестое','Седьмое','Восьмое','Девятое','Десятое','Одиннадцатое','Двенадцатое','Тринадцатое','Четырнадцатое','Пятнадцатое');



require_once '../../Classes/PHPExcel.php';
$pExcel = new PHPExcel();

$pExcel->setActiveSheetIndex(0);
$aSheet = $pExcel->getActiveSheet();

$aSheet->getPageSetup()
       ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
$aSheet->getPageSetup()
       ->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
// Поля документа
$aSheet->getPageMargins()->setTop(1);
$aSheet->getPageMargins()->setRight(0.75);
$aSheet->getPageMargins()->setLeft(0.75);
$aSheet->getPageMargins()->setBottom(1);
// Название листа
$aSheet->setTitle('Заявка Гео-Лото');
// Настройки шрифта
$pExcel->getDefaultStyle()->getFont()->setName('Verdana');
$pExcel->getDefaultStyle()->getFont()->setSize(10);

$aSheet->getColumnDimension('A')->setWidth(1.875);
$aSheet->getColumnDimension('B')->setWidth(26);
$aSheet->getColumnDimension('C')->setWidth(50.75);
$aSheet->getColumnDimension('D')->setWidth(18.5);

$aSheet->getRowDimension('1:90')->setRowHeight(2.25);


//---СТИЛИ

$style_body = array(
	// рамки
    'borders'=>array(
        // внутренняя
        'allborders'=>array(
            'style'=>PHPExcel_Style_Border::BORDER_THIN,
            'color' => array(
                'rgb'=>'FFFFFF'
            )
        )
    )
);

$style_header = array(
    // Шрифт
    'font'=>array(
        'bold' => true,
        )
,
    // Выравнивание
    'alignment' => array(
        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
    ),
);

$style_acenter = array(
    // Выравнивание
    'alignment' => array(
        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
    ),
);

$style_ajustify = array(
    // Выравнивание
    'alignment' => array(
        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_JUSTIFY,
    ),
);

$style_aright = array(
    // Выравнивание
    'alignment' => array(
        'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_RIGHT,
    ),
);

$style_wrap = array(
    // рамки
    'borders'=>array(
        // внешняя рамка
        'outline' => array(
            'style'=>PHPExcel_Style_Border::BORDER_MEDIUM,
            'color' => array(
                'rgb'=>'000000'
            )
        ),
        // внутренняя
        'allborders'=>array(
            'style'=>PHPExcel_Style_Border::BORDER_THIN,
            'color' => array(
                'rgb'=>'000000'
            )
        )
    )
);

$style_hwrap = array(
    // рамки
    'borders'=>array(
        // внешняя рамка
        'outline' => array(
            'style'=>PHPExcel_Style_Border::BORDER_THIN,
            'color' => array(
                'rgb'=>'000000'
            )
        ),
        // внутренняя
        'allborders'=>array(
            'style'=>PHPExcel_Style_Border::BORDER_THIN,
            'color' => array(
                'rgb'=>'000000'
            )
        )
    )
);

$style_dwrap = array(
    // рамки
    'borders'=>array(
        // внешняя рамка
        'outline' => array(
            'style'=>PHPExcel_Style_Border::BORDER_DOUBLE,
            'color' => array(
                'rgb'=>'FF0000'
            )
        )
    )
);

$style_green = array(
    // заполнение цветом
    'fill' => array(
        'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
        'color'=>array(
            'rgb' => 'CCFFCC'
        )
    )
);

$style_blue = array(
    // заполнение цветом
    'fill' => array(
        'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
        'color'=>array(
            'rgb' => 'CCFFFF'
        )
    )
);


//СТИЛИ---

$aSheet->getStyle('A1:AS150')->applyFromArray($style_body);

$aSheet->mergeCells('B2:D2'); 
$aSheet->setCellValue('B2','ЗАЯВКА НА ДИПЛОМ «Гео-Лото»');
$aSheet->getStyle('B2')->applyFromArray($style_header);

$aSheet->setCellValue('B4','Дата составления заявки:');
$aSheet->mergeCells('C4:D4'); 
$date = date('d.m.Y');
$aSheet->setCellValue('C4',$date);
$aSheet->getStyle('C4')->getNumberFormat()
->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14);
$aSheet->setCellValue('B5','Ник в игре:');
$aSheet->mergeCells('C5:D5');
$aSheet->setCellValue('C5',$_SESSION['username']);
$aSheet->setCellValue('B6','Имя, фамилия:');
$aSheet->setCellValue('B7','e-mail:');
$aSheet->mergeCells('C6:D6');
$aSheet->mergeCells('C7:D7');
$aSheet->mergeCells('B8:D10');
$aSheet->setCellValue('B8','В соответствии с положением о дипломе «Гео-Лото» прошу выдать мне диплом за выполнение его условий в формате PSD, JPG, BMP,TIF (указать один) разрешением ______ dpi. Список посещенных/созданных мной тайников:');
$aSheet->getStyle('B4:D10')->applyFromArray($style_hwrap);
$aSheet->getStyle('B4:B7')->applyFromArray($style_green);
$aSheet->getStyle('C4:D7')->applyFromArray($style_acenter);
$aSheet->getStyle('B8:D10')->applyFromArray($style_ajustify);

$aSheet->mergeCells('B12:C12');
$aSheet->setCellValue('B12','Номер выбранной карточки гео-лото:');
$aSheet->setCellValue('D12',$savekarta);
$aSheet->getStyle('D12')->applyFromArray($style_acenter);
$aSheet->getStyle('D12')->applyFromArray($style_dwrap);
$aSheet->getStyle('B12:C12')->applyFromArray($style_wrap);
$aSheet->getStyle('B12:C12')->applyFromArray($style_aright);
$aSheet->getStyle('B12:C12')->applyFromArray($style_blue);

$aSheet->setCellValue('B14','Тип Тайника');
$aSheet->setCellValue('C14','Название тайника');
$aSheet->setCellValue('D14','Код');
$aSheet->getStyle('B14:D14')->applyFromArray($style_wrap);
$aSheet->getStyle('B14:D14')->applyFromArray($style_acenter);
$aSheet->getStyle('B14:D14')->applyFromArray($style_blue);

$cel=14;
for ($o=0; $o<15; $o++)
{
	$cel++;
	$aSheet->mergeCells('B'.$cel.':C'.$cel);
	$aSheet->setCellValue('B'.$cel,$chisla[$o].' число карточки:');
	$aSheet->setCellValue('D'.$cel,$card[$o]);
	$aSheet->getStyle('B'.$cel.':D'.($cel+4))->applyFromArray($style_wrap);
	$aSheet->getStyle('B'.$cel)->applyFromArray($style_green);
	$aSheet->getStyle('B'.$cel)->applyFromArray($style_aright);
	$aSheet->getStyle('D'.$cel)->applyFromArray($style_wrap);
	$aSheet->getStyle('D'.$cel)->applyFromArray($style_acenter);
	$aSheet->getStyle('D'.$cel)->applyFromArray($style_blue);
	foreach ($types as $t=>$r)
	{
		$cel++;
		$aSheet->setCellValue('B'.$cel, $types[$t]);
		$aSheet->getStyle('B'.$cel)->applyFromArray($style_blue);
		$aSheet->getStyle('B'.$cel)->applyFromArray($style_acenter);
		$aSheet->setCellValue('C'.$cel, $saveloto[$savekarta][$card[$o]][$t]["name"]);
		$aSheet->setCellValue('D'.$cel, $saveloto[$savekarta][$card[$o]][$t]["type"].'/'.$saveloto[$savekarta][$card[$o]][$t]["cid"]);
	}
	
}


$objWriter = PHPExcel_IOFactory::createWriter($pExcel, 'Excel2007');
$objWriter->save($file);

if (file_exists($file)) {
    if (ob_get_level()) {
      ob_end_clean();
    }
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename="' . basename($file) .'"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public'); 
    header('Content-Length: ' . filesize($file));
  }
readfile($file);

unlink ($file);

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## сохранена заявка ГЕОЛОТО\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
?>