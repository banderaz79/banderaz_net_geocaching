<?php 

session_start();

if(!$_SESSION['token'])
{
	if (filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_CLIENT_IP'];
	elseif (filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif (filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_X_REAL_IP'];
	else $ip = $_SERVER['REMOTE_ADDR'];

	$_SESSION['USER_IP'] = $ip;
	//$_SESSION['LOGFILE'] = dirname(__DIR__) . '/logs/' . basename(dirname(__DIR__)) . ".log" ;
	$_SESSION['LOGFILE'] = __DIR__ . "/logs/" . basename(__DIR__) . ".log";
}

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

?>

<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="Geocaching tools. Конвертируем экспортированный из locus Map gpx файл, что бы в c:geo прикреплялись путевые точки"> 
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<title>Geocaching tools - конвертирование gpx файла из Locus Map для с:geo</title>
</head>
<body>
	<div class="main" >
		<form enctype="multipart/form-data" action="" method="post" style="text-align: center;">
			<div style="text-align: center;">
				<a href="http://geocaching.su" target="blank"><img src="media/gc.png" /></a>
			</div>
			<div>
				<center><b>Locus Map --> c:geo</b></center>
			</div>
			<div>
				Выберите экспортированный из Locus Map GPX файл<br /><br />
				<input type="hidden" name="MAX_FILE_SIZE" value="20000000">
				<input type="file" name="uploadfile"><br />
			</div>
			<div>
				<input type="submit" value="Пуск">&nbsp;&nbsp;&nbsp;&nbsp;Нажмите, чтобы сформировать файл
			</div>
		</form>
		<table class="nav">
			<tr>
				<td class="nav1" style="width: 33%;"><a href="index.php">К началу</a></td>
				<td style="width: 33%; text-align:center;"><a href="cgeo2locus.php">cgeo2locus</a></td>
				<td class="nav2" style="width: 33%;"><a href="sssndstry.php">Выход</a></td>
			</tr>
		</table>
		<div class="descr">
			Сделайте экспорт нужных тайников из программы Locus Map в формате gpx. Загрузите файл сюда. Полученный gpx-файл загрузите в c:geo.
			<br>
			<br>
			У c:geo и Locus Maps разная структура gpx-файлов. Поэтому, при импорте в c:geo экспортированного из Locus Map файла, путевые точки тайника не цепляются к тайнику совсем. Данный скрипт преобразовывает gpx-файл из Locus Map в подходящий по структуре для c:geo.
		</div>
	</div>
</body>
</html>

<?php 

if (!isset($_FILES['uploadfile'])){}
else 
{
	$file_type = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);
	$file_name = pathinfo($_FILES['uploadfile']['name'], PATHINFO_BASENAME);

	if($file_type != "gpx") 
	{
		echo '<script type="text/javascript">alert("Можно загружать только .GPX файл");</script>';
		echo '<script type="text/javascript">window.top.location.href = "";</script>';
		//if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	unset ($file_type);

	if($_FILES['uploadfile']['error'] > 0) 
	{
		echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
		echo '<script type="text/javascript">window.top.location.href = "";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	
	$uploadfile = $_SESSION['TMPDIR'].$_SESSION['userid']."_locus2cgeo_";
	$num = rand(10,1000);
	while (file_exists($uploadfile.$num)) $num++;
	$uploadfile .= $num;
	move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfile);

	$locusdata = file_get_contents($uploadfile);

	if(!strpos($locusdata,'creator="Locus Map'))
	{
		echo '<script type="text/javascript">alert("Этот файл выгружен не из Locus Map!");</script>';
		echo '<script type="text/javascript">window.top.location.href = "";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}

	$patterns = array('/<groundspeak:/','/<\/groundspeak:/');
	$replacement = array('<groundspeak','</groundspeak');
	$locusdata = preg_replace($patterns, $replacement, $locusdata);

	$xmlstr = simplexml_load_string($locusdata);

	foreach($xmlstr->wpt as $wpt)
	{
		if(strpos($wpt->type,'Geocache') !== FALSE) $parentname = $wpt->name;
		if(strpos($wpt->type,'Waypoint') !== FALSE)
		{
			$gsak = $wpt->addChild('gsakwptExtension');
			$gsak->addChild('gsakParent', $parentname );
		}
	}

	$patterns = array('/<groundspeak/','/<\/groundspeak/','/<gsak/','/<\/gsak/');
	$replacement = array('<groundspeak:','</groundspeak:','<gsak:','</gsak:');
	$locusdata = preg_replace($patterns, $replacement, $xmlstr->asXML());

	$newgpx = '<gpx version="1.0" creator="c:geo - http://www.cgeo.org/" xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd http://www.groundspeak.com/cache/1/0/1 http://www.groundspeak.com/cache/1/0/1/cache.xsd http://www.gsak.net/xmlv1/6 http://www.gsak.net/xmlv1/6/gsak.xsd" xmlns="http://www.topografix.com/GPX/1/0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:groundspeak="http://www.groundspeak.com/cache/1/0/1" xmlns:gsak="http://www.gsak.net/xmlv1/6" xmlns:cgeo="http://www.cgeo.org/wptext/1/0">';

	$locusdata = preg_replace('/<gpx.*">/sU', $newgpx, $locusdata);
	$locusdata = preg_replace('/<link.*link>/sU','', $locusdata);
	$locusdata = rtrim(preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $locusdata)); //удаление пустых строк в тексте

	$file = $_SESSION['TMPDIR'] . mb_substr($file_name,0,-4) . "_locus2cgeo.gpx";

	file_put_contents($file, $locusdata);

	$_SESSION['result_file'] = $file;
	
	if ( !(@unlink($uploadfile))) die('Ошибка при удалении временного файла');

	unset ($uploadfile);

	$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## скрипт отработал\n";
	file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
	
	if(!$_SESSION['token'])
	{
		unset ($_SESSION['USER_IP']);
		unset ($_SESSION['LOGFILE']);
	} 
	
	echo '<script type="text/javascript">window.top.location.href = "download.php";</script>';
}
?>
