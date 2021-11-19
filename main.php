<?php
ob_start();
session_start();

if(!$_SESSION['token']) 
{
	$new_url = 'index.php';
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

//require 'getnewapi.php';
//$data=getnewapi("geocache.php", "8441");//18253 10604 17654 19834 8441 16449
?>

<html>
	<head>
		<meta charset="utf-8">
		<meta name="description" content="Полезные инструменты для геокешеров geocaching.su - дипломы и парсинг тайников"> 
		<title>Полезные инструменты для геокешеров geocaching.su</title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
	</head>
	<body>
	<div class="main" >
		<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="media/gc.png" /></a></p></div>

		<div><p style="text-align: center;">Приветствую, <b><?php echo $_SESSION['username']; ?></b>! Что интересует?</p>	</div>
		<div>
			<ol>
				<li><a href="cfl/">Формирование gpx файла для Locus Map</a></li>
				<li><a href="diploms/onload.php">Геокешерские дипломы</a></li>
				<li><a href="cgeo2locus.php">Конвертер gpx файла из c:geo для Locus Map</a></li>
				<li><a href="locus2cgeo.php">Конвертер gpx файла из Locus Map для c:geo</a></li>
				<li><a href="cache_list/">Список взятых и созданных тайников по user id игрока</a></li>
			</ol>
		</div>

		<table class="nav">
			<tr>
				<td></td>
				<td class="nav2"><a href="sssndstry.php">Выход</a></td>
			</tr>
		</table>
	</div>
	</body>
</html>