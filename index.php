<?php
session_start();


$_SESSION['LOGFILE'] = __DIR__ . "/logs/" . basename(__DIR__) . ".log";
$_SESSION['TMPDIR'] = __DIR__ . "/TMP/";

if (filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_CLIENT_IP'];
elseif (filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
elseif (filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) $ip = $_SERVER['HTTP_X_REAL_IP'];
else $ip = $_SERVER['REMOTE_ADDR'];

$_SESSION['USER_IP'] = $ip;

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'? 'https' : 'http';
if($_SERVER["SERVER_PORT"] == 443)	$protocol = 'https';
elseif (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) $protocol = 'https';
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') $protocol = 'https';

$_SESSION['geo_cat'] = $protocol . "://";

$_SERVER['SERVER_NAME'] == basename(__DIR__) ? $_SESSION['geo_cat'] .= $_SERVER['SERVER_NAME'] . "/" : $_SESSION['geo_cat'] .= $_SERVER['SERVER_NAME'] . "/" . basename(__DIR__) . "/";


/*$_SESSION['userid'] = 127896; //29695-banderaz 111161-Xanthippe 127896-Белочка Чернобыльская
$_SESSION['username'] = 'test';
$_SESSION['caches_number']['NO'] = 'test';
$_SESSION['caches_number']['YES'] = 'test';
require_once 'main.php';*/

//$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## перенаправлен на oauth.php\n";
//file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

?>

<html>
<head>
	<meta charset="utf-8">
	<meta name="description" content="Полезные инструменты "> 
	<title>Полезные инструменты для геокешеров geocaching.su</title>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
	<div class="main" >
		<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="media/gc.png" /></a></p></div>
		<div><p style="text-align: center;">Приветствую<?php if($_SESSION['username']) echo ", <b>" . $_SESSION['username'] . "</b>"; ?>! Что интересует?</p></div>
		<div>
			<ul>
				<?php if($_SESSION['token']) echo '
				<li><a href="cfl/">Формирование gpx файла для Locus Map</a></li>
				<li><a href="diploms/onload.php">Геокешерские дипломы</a></li>';
				?>
				<li><a href="cgeo2locus.php">Конвертер gpx файла из c:geo для Locus Map</a></li>
				<li><a href="locus2cgeo.php">Конвертер gpx файла из Locus Map для c:geo</a></li>
				<?php if($_SESSION['token']) echo '
				<li><a href="cache_list/">Список взятых и созданных тайников по user id игрока</a></li>';
				if(in_array($_SESSION['username'], array('banderaz','MagDi'))) echo'
				<li><a href="admin_diploms/gvs/">Админка ГВС</a></li>';
				?>
			</ul>
		</div>
		<?php if(!$_SESSION['token']) echo '
		<form enctype="multipart/form-data" action="oauth.php" method="post" style="text-align: center;">
			<div>
				<input type="submit" value="Авторизоваться">&nbsp;&nbsp;&nbsp;&nbsp;Для использования дополнительных функций авторизуйтесь на сайте geocaching.su
			</div>
		</form>
		';
		
		else echo '
		<div>
			Вы авторизованы на сайте geocaching.su
		</div>'; ?>
		<table class="nav">
			<tr>
				<td></td>
				<td class="nav2"><a href="sssndstry.php">Выход</a></td>
			</tr>
		</table>
	</div>
</body>
</html>