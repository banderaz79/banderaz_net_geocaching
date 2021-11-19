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

echo '	<html>
			<head>
				<?PHP //header("charset=utf-8");?>
				<meta charset="utf-8">
				<meta name="description" content="Составление геокешерских дипломов. Дипломная программа geocaching.su"> 
				<title>Геокешерские дипломы. Дипломная программа geocaching.su</title>
				<link rel="stylesheet" type="text/css" href="../css/style.css" />
			</head>
			<body>
			<div class="main" >
				<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../media/gc.png" /></a></p></div>

				<div><p>Игрок: <b>'.$_SESSION['username'].'</b><br><br>Найдено тайников: <b>'.$_SESSION['caches_number']['NO'].'</b><br>Создано тайников: <b>'.$_SESSION['caches_number']['YES'].'</b></p></div>
				<div>
					<ol>';
						if(in_array($_SESSION['username'], array('banderaz','MagDi'))) echo'
						<li><a href="zd/">Железные Дороги</a> - проверка заявки</li>';
						echo'
						<li><a href="azbuka/">Азбука геокешера</a> - не актуально, <a href="http://geodiplomas.araiguma.ru/" target="blank">жми сюда</a></li>	
						<li><a href="loto/">Геолото</a> - не актуально, <a href="http://geodiplomas.araiguma.ru/" target="blank">жми сюда</a></li>
						<li><a href="regions/">Регионы России</a> - не актуально, <a href="http://geodiplomas.araiguma.ru/" target="blank">жми сюда</a></li>
					</ol>
				</div>

				<table class="nav">
					<tr>
						<td class="nav1"><a href="../index.php">К началу</a></td>
						<td class="nav2"><a href="reload.php">Перезагрузить тайники</a></td>
					</tr>
				</table>
			</div>
			</body>
		</html>';
?>
