<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

//require_once('../../../vendor/PhpOffice/autoload.php');
require_once('../../../vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\IOFactory;

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

if(!in_array($_SESSION['username'], array('banderaz','MagDi'))) echo '<script type="text/javascript">document.location.href = "../../index.php";</script>';

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

unset ($_SESSION['z_data']);
?>
<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="Проверка заявок на диплом ГВС">
	<link rel="stylesheet" type="text/css" href="../../css/style.css" />
	<title>Проверка заявок на диплом ГВС</title>
</head>
<body>
	<div class="main" >
		<form enctype="multipart/form-data" action="" method="post" style="text-align: center;">
			<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../../media/gc.png" /></a></p></div>
			<div>
				Выберите заявку на диплом ГВС<br /><br />
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
				<input type="file" name="uploadfile"><br />
			</div>
			<div>
				<input type="submit" value="Пуск">&nbsp;&nbsp;&nbsp;&nbsp;Нажмите, чтобы проверить.
			</div>
		</form>
		<table class="nav">
			<tr>
				<td class="nav1"><a href="../../index.php">К началу</a></td>
				<td class="nav2"><a href="help.php" target="_blank">Помощь</a></td>
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
	
// Проверки
	if($file_name == "") {
		echo '<script type="text/javascript">alert("Вы не выбрали файл");</script>';
		echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	elseif(!in_array($file_type,array('xls','xlsx'))) {
		echo '<script type="text/javascript">alert("Можно загружать только xlsфайл");</script>';
		echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	elseif($_FILES['uploadfile']['error'] > 0) {
		echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
		echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	else
	{
		$uploadfile = $_SESSION['TMPDIR']."/"."gvs_";
		$num = rand(10,1000);
		while (file_exists($num)) $num++;
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
			echo '<script type="text/javascript">alert("Error loading file: ' . $e->getMessage() . '\n\nК сожалению, файл не может быть загружен.\n\nЧтобы избавиться от проблемы, откройте файл в Excel и просто пересохраните его (Ctrl+S).\n\nЗатем заново загрузите его здесь.");</script>';
			echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
			if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
			die("error");
		}


		class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter 
		{
			public function readCell($columnAddress, $row, $worksheetName = '') 
			{
				if (in_array($row, [6,7,8,10,15,16,17,18,19]) and in_array($columnAddress, ['C','E']) and $worksheetName == 'Заявка') 
				{
					return true;
				}
				if ($worksheetName == 'Данные')
				{
					return true;
				}
				return false;
			}
		}
		
		$filetype = IOFactory::identify($uploadfile);
		$reader = IOFactory::createReader($filetype);
		$reader->setReadDataOnly(true);
		$reader->setLoadSheetsOnly(["Заявка", "Данные"]);
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
			echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
			if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
			die("error");
		}
		
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		
		$z_data = $spreadsheet->getSheet(0)->toArray(null, true, true, true);
		
		
		
		foreach($z_data as $key => $value)
		{
			if(in_array($key,array(1,2,3,4,5,9,11,12,13,14,20,21))) unset($z_data[$key]);
			else
			{
				foreach($value as $k => $v)
				{
					if(!$v == '') $z_data[$key][$k] = trim($v);
					else unset($z_data[$key][$k]);
					//if(preg_match('/^\d{1,2}\/\d{1,2}\/\d{1,4}$/', $v)) $z_data[$key][$k] = date("d.m.Y", strtotime($v));
				}
				unset($z_data[$key]['B']);
				unset($z_data[$key]['D']);
			}
		}

		if($z_data[6]['E'])
		{
			require 'tables.php';
			require '../../Classes/loc.php';
			$link = mysqli_connect($a,$b,$c,$d);
			if (!$link) 
			{
				printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
				exit;
			}
			$uname = $z_data[6]['E'];
			$query = sprintf("SELECT * FROM $users_table WHERE `uname` LIKE '%s'", mysqli_real_escape_string($link, "$uname"));
			$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
			if (mysqli_num_rows ($result) == 0)
			{
				$_SESSION['z_data'] = $z_data;
				echo '<script type="text/javascript">document.location.href = "uid_request.php";</script>';
			}
			else
			{
				while($row = mysqli_fetch_assoc($result))
				{
					$user[] = $row;
					$z_data[6]['uid'] = $row['uid'];
					$z_data[6]['stars'] = $row['stars'];
				}
				
				unset($user);
				$_SESSION['z_data'] = $z_data;
				echo '<script type="text/javascript">document.location.href = "check.php";</script>';
			}
		}
		else
		{
			echo '<script type="text/javascript">alert("В заявке не указан ник игрока!");</script>';
			echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
			die("error");
		}
	}
}
?>
