<?php
session_start();

$file = $_SESSION['result_file'];

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($file));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));

if ($fd = fopen($file, 'rb')) 
{
  while (!feof($fd)) print fread($fd, 1024);
  fclose($fd);
}

if ( !(@unlink($file))) die('Ошибка при удалении временного файла');

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## скрипт отработал\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

if(!$_SESSION['token'])
{
	unset ($_SESSION['USER_IP']);
	unset ($_SESSION['LOGFILE']);
}

sleep(5);
?>