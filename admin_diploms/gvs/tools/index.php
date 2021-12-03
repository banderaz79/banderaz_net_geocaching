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
				if ((in_array($columnAddress, ['D','E']) and $row !== 1) or (in_array($row, [2,3]) and in_array($columnAddress, ['A','B']))) 
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

		if (trim($z_data[2]['A']) !== '') $user['nik'] = trim($z_data[2]['A']);
		else $user['nik'] = trim($z_data[3]['A']);
		if (trim($z_data[2]['B']) !== '') $user['url'] = trim($z_data[2]['B']);
		else $user['url'] = trim($z_data[3]['B']);
		
		unset($z_data[2]['A']);
		unset($z_data[2]['B']);


		foreach ($z_data as $key => $value)
		{
			foreach ($value as $k => $v)
			{
				if (!$v) unset ($z_data[$key][$k]);
				else $z_data[$key][$k] = trim($z_data[$key][$k]);
			}
			if (!$z_data[$key]) unset ($z_data[$key]);
		}


		require '../tables.php';
		require '../../../Classes/loc.php';
		$link = mysqli_connect($a,$b,$c,$d);
		if (!$link) 
		{
			printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
			exit;
		}
		

		//загружаем из базы таблицу с константами гвс
		$query = sprintf("SELECT * FROM $const_table");
		$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
		if (mysqli_num_rows ($result) == 0)
		{
			echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
			echo '<script type="text/javascript">document.location.href = "index.php";</script>';
			
		}
		else 
		{
			$i=1;
			while($row = mysqli_fetch_assoc($result))
			{
				$const[$i++] = $row;
			}
			$_SESSION['const'] = $const;
		}
		$const = $_SESSION['const'];

		foreach ($_SESSION['const'] as $key => $value)
		{
			$gvs_list[$key] = $value['gvs'];
		}

		$n = 0;
		foreach ($z_data as $key => $value)
		{
			preg_match('/^(.+).[A-Z]{2}\/(\d{1,5})/', $value['E'], $cid);

			if($cid)
			{
				if (isset($value['D']) and in_array($value['D'], $gvs_list))
				{
					$n++;
					$diploms[$n]['gid'] = array_search($value['D'], $gvs_list);
					$diploms[$n]['gvs'] = $value['D'];
					$diploms[$n]['region'] = $const[$diploms[$n]['gid']]['region'];
					$diploms[$n]['num'] = ltrim(substr($z_data[$key+1]['D'],3), '0');
					$diploms[$n]['date'] = date("d.m.Y", strtotime($z_data[$key+2]['D']));
					$diploms[$n]['stars'] = substr($z_data[$key+3]['D'], 0, -13);

					if ($value['D'] == 'Полярный')
					{
						require_once('../../../getoldapi.php');
						$cache = getoldapi(2, $cid[2], '');
						$diploms[$n]['used_caches'][$cid[2]] = $cache['name'];
					}
					else 
					{
						$diploms[$n]['main_cid'] = $cid[2];
						$diploms[$n]['main_cache'] = $cid[1];
					}
				}
				else
				{
					//if(isset($cid[2])) 
					//{
						require_once('../../../getoldapi.php');
						$cache = getoldapi(2, $cid[2], '');
						$diploms[$n]['used_caches'][$cid[2]] = $cache['name'];
					//}
				}
			}
			else 
			{
				echo '<script type="text/javascript">alert("Похоже, в ячейках нет id тайников");</script>';
				echo '<script type="text/javascript">document.location.href = "index.php";</script>';
				exit;
			}
			
		}
		unset ($z_data);
	}
	
	$_SESSION['user'] = $user;
	$_SESSION['diploms'] = $diploms;

echo'<pre>';
print_r($user);
echo'</pre>';
	
echo'<pre>';
print_r($diploms);
echo'</pre>';

	echo '
	<form enctype="multipart/form-data" action="insert_diploms.php" method="post" style="">	
		<div style="border: none; width: 100%;text-align: center;">
			<input name="go" type="hidden" value="1">
			<input id="submit_btn" type="submit" value="Записать в базу" style="font-size: 16pt;">
		</div>
	</form>';

}



?>