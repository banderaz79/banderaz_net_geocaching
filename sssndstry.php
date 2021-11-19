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

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## произведен выход\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

$userid = $_SESSION['userid'];

/*require 'Classes/loc.php';
$link = mysqli_connect($a,$b,$c,$d);
if (!$link) 
{
	printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
	exit;
}
$query="SHOW TABLES LIKE '$user_caches_table'";
$result = mysqli_query($link, $query) or die('error query' . mysqli_error($link));
if (mysqli_num_rows($result))	
{	
		$query ="DROP TABLE $user_caches_table";
		$result = mysqli_query($link, $query) or die("Ошибка удаления таблицы: " . mysqli_error($link));
}*/

$_SESSION = array(); // или unset($_SESSION['name'])
session_destroy();

foreach($_COOKIE as $key => $value) setcookie($key, '', time() - 3600, '/');

header('Location: index.php');
?>