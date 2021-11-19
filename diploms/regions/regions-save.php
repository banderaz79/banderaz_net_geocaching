<?php 
ob_start();
session_start(); 

if(!$_SESSION['saveregions'] and !$_SESSION['region']) 
{
	$new_url = 'index.php';
	header('Location: '.$new_url);
	ob_end_flush();
	exit;
}
ob_end_flush();

$used_caches_table = "diploms_regions_".$_SESSION['userid']."_usedcaches";
$saveregions = $_SESSION['saveregions'];
$file = $_SESSION['userid'].'_region_'.$_SESSION['region'].'.xlsx';
$userid = $_SESSION['userid'];

require '../../Classes/loc.php';
$link = mysqli_connect($a,$b,$c,$d);
if (!$link) 
{
	printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
	exit;
}

$query="CREATE TABLE IF NOT EXISTS $user_regions_used_caches_table
					(
						`region` INT,
						`cid` INT
					)";
$result = mysqli_query($link, $query) or die('1Ошибка:' . mysqli_error($link));
$usedregions = array();
$query = "SELECT * FROM $user_regions_used_caches_table";
if($result = mysqli_query ($link, $query) or die("12Ошибка: " . mysqli_error($link)))
{
	while ($arr = mysqli_fetch_array($result))
	{
		$usedregions[] = $arr['region'];
	}
}
/*echo "<pre>";
print_r($usedregions);
echo "</pre>";*/
if(!in_array($_SESSION['region'], $usedregions))
{
foreach($saveregions as $v)
{
	$query = "INSERT INTO $user_regions_used_caches_table (region, cid) VALUES ('".$_SESSION['region']."','".$v["cid"]."')";
	$result = mysqli_query ($link, $query) or die("Ошибка: " . mysqli_error($link));
}
}


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
$aSheet->setTitle('Регионы России');
// Настройки шрифта
$pExcel->getDefaultStyle()->getFont()->setName('Verdana');
$pExcel->getDefaultStyle()->getFont()->setSize(10);

//пиксели делить на 8
$aSheet->getColumnDimension('A')->setWidth(3.75);
$aSheet->getColumnDimension('B')->setWidth(26.5);
$aSheet->getColumnDimension('C')->setWidth(46.125);
$aSheet->getColumnDimension('D')->setWidth(9.625);

$aSheet->getRowDimension('1:90')->setRowHeight(18);
$aSheet->getRowDimension('2')->setRowHeight(50);


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
        'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_BOTTOM,
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
$aSheet->setCellValue('B2','ЗАЯВКА НА ДИПЛОМ «Регионы России»');
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
$aSheet->mergeCells('C6:D6');
$aSheet->setCellValue('B7','e-mail:');
$aSheet->mergeCells('C7:D7');
$aSheet->setCellValue('B8','Номер заявляемого региона:');
$aSheet->setCellValue('C8',$_SESSION['reg_name']);
$aSheet->getStyle('C8')->applyFromArray($style_blue);
$aSheet->setCellValue('D8',$_SESSION['region']);
$aSheet->mergeCells('B9:D11');
$aSheet->setCellValue('B9','В соответствии с положением о дипломе «Гео-Лото» прошу выдать мне диплом за выполнение его условий в формате PSD, JPG, BMP,TIF (указать один) разрешением ______ dpi. Список посещенных/созданных мной тайников:');
$aSheet->getStyle('B4:D11')->applyFromArray($style_hwrap);
$aSheet->getStyle('B4:B8')->applyFromArray($style_green);
$aSheet->getStyle('C4:D8')->applyFromArray($style_acenter);
$aSheet->getStyle('B9:D11')->applyFromArray($style_ajustify);
$aSheet->getStyle('D8')->applyFromArray($style_acenter);
$aSheet->getStyle('D8')->applyFromArray($style_dwrap);

$aSheet->setCellValue('B13','Регион');
$aSheet->setCellValue('C13','Название тайника');
$aSheet->setCellValue('D13','Код');
$aSheet->getStyle('B13:D13')->applyFromArray($style_wrap);
$aSheet->getStyle('B13:D13')->applyFromArray($style_acenter);
$aSheet->getStyle('B13:D13')->applyFromArray($style_blue);

$cel=14;
foreach ($_SESSION['saveregions'] as $v)
{
	$aSheet->setCellValue('B'.$cel,$v["region"]);
	$aSheet->getStyle('B'.$cel)->applyFromArray($style_blue);
	$aSheet->setCellValue('C'.$cel,$v["name"]);
	$aSheet->setCellValue('D'.$cel,$v["type"].'/'.$v["cid"]);
	$aSheet->getStyle('B'.$cel.':D'.$cel)->applyFromArray($style_hwrap);
	$aSheet->getStyle('C'.$cel.':D'.$cel)->applyFromArray($style_acenter);
	$cel++;
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



$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## сохранена заявка РЕГИОНЫ\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);



mysqli_close($link);
?>