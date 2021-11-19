<?php 
ob_start();
session_start();

if(!$_SESSION['token']) 
{
	$new_url = '../index.php';
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

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

echo '
<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="Caches For Locus. Парсим информацию о тайниках с geocaching.su. Импорт точек тайников в формате gpx с сайта geocaching.su"> 
	<link rel="stylesheet" type="text/css" href="../css/style.css" />
	<title>Caches For Locus - импорт тайников с geocaching.su</title>
</head>
<body>
	<div class="main" >
		<form enctype="multipart/form-data" action="cfl.php" method="post" style="text-align: center;">
			<div style="text-align: center;">
			<a href="http://locusmap.eu" target="blank"><img src="../media/locus.png" /></a><a href="http://geocaching.su" target="blank"><img src="../media/gc.png" /></a>
			<!--<img src="../media/cfl.png" style="width: 46%;" />-->
			</div>
			<div>
				Выберите загруженный с сайта geocaching.su WPT файл<br /><br />
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
				<input type="file" name="uploadfile"><br />
			</div>
			<!--<div>
				Выберите варианты загрузки фотографий тайника и местности<br /><br />
				<select size="1" name="loadimages">
					<option value="0">Без фото</option>
					<option value="1">Только фото тайника онлайн</option>
					<option value="2">Фото тайника и местности онлайн</option>
					<option value="3">Только фото тайника загрузка</option>
					<option value="4">Фото тайника и местности загрузка</option>
				</select>
			</div>-->
			<div>
				Выберите количество загружаемых записей интернет-блокнота тайника<br /><br />
				<select size="1" name="loadnotes">
					<option value="0">Не загружать записи блокнота</option>
					<option value="10">10 последних</option>
					<option value="20">20 последних</option>
					<option value="30">30 последних</option>
					<option value="40">40 последних</option>
					<option value="50">50 последних</option>
					<option value="10000">Загрузить все записи</option>
				</select>
			</div>
			<!--<div>
				<input type="checkbox" name="deletetypes" value="1"> Удалить типы тайников
			</div>-->
			<div>
				<input type="submit" value="Пуск">&nbsp;&nbsp;&nbsp;&nbsp;Нажмите, чтобы сформировать файл
			</div>
		</form>
		<table class="nav">
			<tr>
				<td class="nav1"><a href="../index.php">К началу</a></td>
				<td class="nav2"><a href="../sssndstry.php">Выход</a></td>
			</tr>
		</table>
		<div class="descr">
			Сделайте экспорт нужных тайников с сайта geocaching.su в виде wpt-файла. Загрузите файл сюда. Полученный gpx-файл импортируйте в Locus Maps или другую программу или устройство.
			<br>
			<br>
			<span style="font-weight: bold">Скрипт писался тогда, когда не было нормального экспорта gpx с сайта geocaching.su. Сейчас, возможно, он не слишком актуален, но я старался сделать итоговый файл максимально удобным для работы в Locus Maps.</span>
		</div>
	</div>
</body>
</html>';
?>