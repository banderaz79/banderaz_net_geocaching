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
		padding: 0 5px 0 5px;
		}

		tr {
			height: 10pt;
		}

	</style>	
</head>
<body>

<?php

echo"<pre>";
print_r($_POST);
echo"</pre>";
echo"<pre>";
print_r($_SESSION);
echo"</pre>";

?>

</body>
</html>