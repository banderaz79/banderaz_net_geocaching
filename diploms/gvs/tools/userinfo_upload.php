<?php
ob_start();
session_start();

if(!$_SESSION['token']) 
{
	$new_url = '../../../index.php';
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

?>

<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="gvs userinfo">
	<link rel="stylesheet" type="text/css" href="../../../css/style.css" />
	<title>gvs userinfo</title>
</head>
<body>
	<div class="main" >
		<form enctype="multipart/form-data" action="" method="post" style="text-align: center;">
			<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../../../media/gc.png" /></a></p></div>
			<div>
				<b>Загрузка информации по игроку для дипломной программы ГВС</b>
			</div>
			<div>
				Выберите файл с информацией по пользователю (сохраненный из Excel в формате CSV (разделители - запятые))<br /><br />
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
				<input type="file" name="uploadfile"><br />
			</div>
			<div>
				<input type="text" name="userid">  Введите uid игрока<br /><br />
				<a href="https://geocaching.su/?pn=108" target="_blank"><span style="font-size: 14px;">страница поиска игрока</span></a>
			</div>
			<div>
				<input type="submit" value="Пуск">&nbsp;&nbsp;&nbsp;&nbsp;Нажмите, чтобы загрузить файл.
			</div>
		</form>
		<table class="nav">
			<tr>
				<td class="nav1"><a href="../../index.php">К началу</a></td>
				<td class="nav2"><a href="../../sssndstry.php">Выход</a></td>
			</tr>
		</table>
	</div>
</body>
</html>