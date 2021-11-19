<?php 

session_start();

if(!$_SESSION['token'])
{
	if (filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_CLIENT_IP'];
	elseif (filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif (filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_X_REAL_IP'];
	else $ip = $_SERVER['REMOTE_ADDR'];

	$_SESSION['USER_IP'] = $ip;
	$_SESSION['LOGFILE'] = __DIR__ . "/logs/" . basename(__DIR__) . ".log";
}

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

?>

<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="Geocaching tools. Конвертируем экспортированный из c:geo gpx файл, что бы в Locus Map прикреплялись путевые точки"> 
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<title>Geocaching tools - конвертирование gpx файла из c:geo для Locus Map</title>
</head>
<body>
	<div class="main" >
		<form enctype="multipart/form-data" action="" method="post" style="text-align: center;">
			<div style="text-align: center;">
				<a href="http://geocaching.su" target="blank"><img src="media/gc.png" /></a>
			</div>
			<div>
				<center><b>c:geo --> Locus Map</b></center>
			</div>
			<div>
				Выберите экспортированный из c:geo GPX файл<br /><br />
				<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
				<input type="file" name="uploadfile" ><br />
			</div>
			<div>
				<input type="checkbox" name="deleteorigins" value="1" class="large">   Удалить оригинальные координаты (если координаты тайника были перенесены в c:geo в другую точку)</br>
				<input type="checkbox" name="deletevisited" value="1" class="large">   Удалить посещенные точки (чтобы не мешались в Locus Map)
			</div>
			<div>
				<input type="submit" value="Пуск" >&nbsp;&nbsp;&nbsp;&nbsp;Нажмите, чтобы сформировать файл
			</div>
		</form>
		<table class="nav">
			<tr>
				<td class="nav1" style="width: 33%;"><a href="index.php">К началу</a></td>
				<td style="width: 33%; text-align:center;"><a href="locus2cgeo.php">locus2cgeo</a></td>
				<td class="nav2" style="width: 33%;"><a href="sssndstry.php">Выход</a></td>
			</tr>
		</table>
		<div class="descr">
			Сделайте экспорт нужных тайников из программы c:geo в формате gpx. Загрузите файл сюда. Полученный gpx-файл загрузите в Locus Maps.
			<br>
			<br>
			У c:geo и Locus Map разная структура gpx-файлов. Поэтому, при импорте в Locus Map экспортированного из c:geo файла, путевые точки тайника не цепляются к тайнику, а отображаются отдельными точками. Данный скрипт преобразовывает gpx-файл из c:geo в подходящий по структуре для Locus Map.
		</div>
	</div>
</body>
</html>

<?php
if (isset($_FILES['uploadfile']))
{
	$deleteorigins = $_POST['deleteorigins'];
	$deletevisited = $_POST['deletevisited'];
	
	$file_type = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);
	$file_name = pathinfo($_FILES['uploadfile']['name'], PATHINFO_BASENAME);


	if($file_type != "gpx") {
		echo '<script type="text/javascript">alert("Можно загружать только .GPX файл");</script>';
		echo '<script type="text/javascript">window.top.location.href = "";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	unset ($file_type);

	if($_FILES['uploadfile']['error'] > 0) {
		echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
		echo '<script type="text/javascript">window.top.location.href = "";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}

	$uploadfile = $_SESSION['TMPDIR']."/".$_SESSION['userid']."_cgeo2locus_";
	$num = rand(10,1000);
	while (file_exists($uploadfile.$num)) $num++;
	$uploadfile .= $num;
	move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfile);

	$cgeodata = file_get_contents($uploadfile);

	$patterns = array('/<groundspeak:/','/<\/groundspeak:/','/<gsak:/','/<\/gsak:/','/<cgeo:/','/<\/cgeo:/');
	$replacement = array('<groundspeak','</groundspeak','<gsak','</gsak','<cgeo','</cgeo');
	$cgeodata = preg_replace($patterns, $replacement, $cgeodata);

	$xmlstr = simplexml_load_string($cgeodata);

	for($n=0; $n<count($xmlstr->wpt); $n++)
	{
		if($xmlstr->wpt[$n]->groundspeakcache->groundspeakowner)
		{
			if(!$xmlstr->wpt[$n]->groundspeakcache->groundspeakowner->attributes())
			{
				$xmlstr->wpt[$n]->groundspeakcache->groundspeakowner = $xmlstr->wpt[$n]->groundspeakcache->groundspeakowner->addAttribute('id', $xmlstr->wpt[$n]->groundspeakcache->groundspeakowner);
				$xmlstr->wpt[$n]->groundspeakcache->groundspeakowner = $xmlstr->wpt[$n]->groundspeakcache->groundspeakplaced_by;
			}
		}
		else
		{
			if ($xmlstr->wpt[$n]->gsakwptExtension->gsakParent) 
			{
				if (($xmlstr->wpt[$n]['lat']=='0.0' and $xmlstr->wpt[$n]['lon']=='0.0') or ($deleteorigins == 1 and $xmlstr->wpt[$n]->type == 'Waypoint|Original Coordinates') or ($deletevisited == 1 and $xmlstr->wpt[$n]->cgeovisited == 'true'))
				{
					unset ($xmlstr->wpt[$n][0][0]);
					$n=$n-1;
				}
				else $xmlstr->wpt[$n]->name = $xmlstr->wpt[$n]->gsakwptExtension->gsakParent . ": " . $xmlstr->wpt[$n]->name;
			}
			
		}
	}

	$patterns = array('/<groundspeak/','/<\/groundspeak/','/<gsak/','/<\/gsak/','/<cgeo/','/<\/cgeo/');
	$replacement = array('<groundspeak:','</groundspeak:','<gsak:','</gsak:','<cgeo:','</cgeo:');
	$cgeodata = preg_replace($patterns, $replacement, $xmlstr->asXML());

	$file = $_SESSION['TMPDIR'] . mb_substr($file_name,0,-4) . "_cgeo2locus.gpx";

	file_put_contents($file, $cgeodata);

	$_SESSION['result_file'] = $file;
	
	if ( !(@unlink($uploadfile))) die('Ошибка при удалении временного файла');
	
	unset ($uploadfile);
	
	$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## скрипт отработал\n";
	file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

	echo '<script type="text/javascript">window.top.location.href = "download.php";</script>';
}
else exit();
?>