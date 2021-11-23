<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

//require_once('../../../../vendor/PhpOffice/autoload.php');
require_once('../../../../vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;


ob_start();
session_start();

if(!$_SESSION['token']) 
{
	$new_url = '../../index.php';
	header('Location: '.$new_url);
	if ($_SESSION['LOGFILE'])
	{
		$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## перенаправлен на главную index.php\n";
		file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
	}
	ob_end_flush();
	exit;
}
ob_end_flush();

if(!in_array($_SESSION['username'], array('banderaz','MagDi'))) echo '<script type="text/javascript">document.location.href = "../../../../index.php";</script>';

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

?>
<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="">
	<link rel="stylesheet" type="text/css" href="../../../css/style.css" />
	<title></title>
</head>
<body>
	<div class="main" >
		<form enctype="multipart/form-data" action="" method="post" style="text-align: center;">
			<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../../../media/gc.png" /></a></p></div>
			<div>
				Выберите файл<br /><br />
				<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
				<input type="file" name="uploadfile"><br />
			</div>
			<div>
				<input type="submit" value="Пуск">&nbsp;&nbsp;&nbsp;&nbsp;Пуск.
			</div>
		</form>
		<table class="nav">
			<tr>
				<td class="nav1"><a href="../../../index.php">К началу</a></td>
				<td class="nav2"></td>
			</tr>
		</table>
	</div>
</body>
</html>
<?php
if (isset($_FILES['uploadfile']))
{
	$file_type = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);
	$file_name = pathinfo($_FILES['uploadfile']['name'], PATHINFO_BASENAME);

    echo'<pre>';
    print_r($file_type);
    echo'</pre>';

    echo'<pre>';
    print_r($file_name);
    echo'</pre>';

    echo'<pre>';
    print_r($_FILES['uploadfile']['error']);
    echo'</pre>';
	
// Проверки
	if($file_name == "") {
		echo '<script type="text/javascript">alert("Вы не выбрали файл");</script>';
		//echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	elseif(!in_array($file_type,array('xls','xlsx'))) {
		echo '<script type="text/javascript">alert("Можно загружать только xlsфайл");</script>';
		//echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	elseif($_FILES['uploadfile']['error'] > 0) {
		echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
        //echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	else
	{
		$uploadfile = $_SESSION['TMPDIR']."/";
		$num = rand(10,1000);
		while (file_exists($uploadfile . $num)) $num++;
		$uploadfile .= $num;
		move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfile);

		//$fyletype = IOFactory::identify($uploadfile);
		//$reader = IOFactory::createReader($fyletype);
		//$spreadsheet = $reader->load($uploadfile);
		
		//$spreadsheet = IOFactory::load($uploadfile);
		
// Смотрим, можно ли загрузить файл
		try 
		{
			$spreadsheet = IOFactory::load($uploadfile);
		} 
		catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) 
		{
			//die('Error loading file: '.$e->getMessage());
			echo '<script type="text/javascript">alert("Ошибка загрузки файла: ' . $e->getMessage() . '\n\nК сожалению, файл не может быть загружен.\n\nЧтобы избавиться от проблемы, откройте файл в Excel и просто пересохраните его (Ctrl+S).\n\nЗатем заново загрузите его здесь.");</script>';
			//echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
			if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
			die("error");
		}


		class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter 
		{
			public function readCell($columnAddress, $row, $worksheetName = '') 
			{
				if (in_array($columnAddress, ['C','D','E'])) 
				{
					return true;
				}
                if (in_array($row, [2,3]) and in_array($columnAddress, ['A','B'])) 
				{
					return true;
				}
				return false;
			}
		}   
		
		$filetype = IOFactory::identify($uploadfile);
		$reader = IOFactory::createReader($filetype);
		//$reader->setReadDataOnly(true);
		//$reader->setLoadSheetsOnly(["Заявка","111", "Данные"]);
		$reader->setReadFilter( new MyReadFilter() );
		$spreadsheet = $reader->load($uploadfile);

// Смотрим, можно ли получить первый лист	
		try 
		{
			$z_data = $spreadsheet->getSheet(0)->toArray(null, true, true, true);
		} 
		catch (\PhpOffice\PhpSpreadsheet\Exception $e) 
		{
			echo '<script type="text/javascript">alert("Error loading file: ' . $e->getMessage() . '\n\nК сожалению, файл не может быть загружен.\n\nЧтобы избавиться от проблемы, откройте файл в Excel и просто пересохраните его (Ctrl+S).\n\nЗатем заново загрузите его здесь.");</script>';
			//echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
			if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
			die("error");
		}
		
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		
		$z_data = $spreadsheet->getSheet(0)->toArray(null, true, true, true);

        echo'<pre>';
        print_r($z_data);
        echo'</pre>';

    }
}


?>