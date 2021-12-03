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

if (isset($_SESSION['z_data'])) $z_data = $_SESSION['z_data'];
else echo '<script type="text/javascript">document.location.href = "index.php";</script>';
?>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="../../css/style.css" />
</head>
<body></body>
</html>
<?php
/*echo"<pre>";
print_r($z_data);
echo"</pre>";*/

require 'tables.php';
require '../../Classes/loc.php';
$link = mysqli_connect($a,$b,$c,$d);
if (!$link)
{
	printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
	exit;
}

$uid = mysqli_real_escape_string($link, (int)$z_data[6]['uid']);
$uname = mysqli_real_escape_string($link, $z_data[6]['E']);
$stars = mysqli_real_escape_string($link, (int)$z_data[6]['stars']);
$query = sprintf("INSERT INTO $users_table (`uid`,`uname`,`stars`) VALUES (%u,'%s',%u)", $uid, "$uname", $stars);

if(mysqli_query ($link, $query))
{
	echo '<script type="text/javascript">document.location.href = "check.php";</script>';
	//include_once('check.php');
}
elseif(strpos(mysqli_error($link),'Duplicate entry') >= 0)
{
	echo '<script type="text/javascript">alert("Такой uid уже есть в базе.\nСвяжитесь с администратором.");</script>';
	echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
}
else die("0Ошибка: " . mysqli_error($link));

?>
