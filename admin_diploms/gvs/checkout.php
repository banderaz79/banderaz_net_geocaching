<?php 
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

if(!in_array($_SESSION['username'], array('banderaz','MagDi'))) echo '<script type="text/javascript">document.location.href = "../../index.php";</script>';

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

if (isset($_SESSION['zayavka']) and isset($_SESSION['diplom'])) 
{
	$diplom = $_SESSION['diplom']; 
	$zayavka = $_SESSION['zayavka'];
}
else echo '<script type="text/javascript">document.location.href = "index.php";</script>';
if (isset($_SESSION['user_fotos'])) $user_fotos = $_SESSION['user_fotos'];
?>

<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="../../css/style.css" />
	<style type="text/css">
	   table {
		border: 1px solid grey;
		font-size: 11pt;
		
	   }
	   td, th {
		border: 1px solid grey;
		padding: 5px;
		}

	</style>	
</head>
<body>

<?php

// Проверка   
echo"<pre>";
print_r($diplom);
echo"</pre>";

echo"<pre>";
print_r($zayavka);
echo"</pre>";

echo"<pre>";
print_r($user_fotos);
echo"</pre>";

?>

</body>
</html>