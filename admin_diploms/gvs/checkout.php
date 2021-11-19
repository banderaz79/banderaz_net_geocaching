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

if (isset($_SESSION['zayavka']) and isset($_SESSION['diplom']) and $_SESSION['const']) 
{
	$diplom = $_SESSION['diplom']; 
	$zayavka = $_SESSION['zayavka'];
	$const = $_SESSION['const'];
}
else echo '<script type="text/javascript">document.location.href = "index.php";</script>';
if (isset($_POST['foto'])) $gvs_foto = $_POST['foto'];
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

require '../../Classes/loc.php';
require 'tables.php';
$link = mysqli_connect($a,$b,$c,$d);
if (!$link) 
{
	printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
	exit;
}

//загружаем из базы таблицу с дипломами текущего ГВС
$query = sprintf("SELECT MAX(diplom_num) FROM $diploms_table WHERE `gid` =  %u", mysqli_real_escape_string($link, $diplom['gid']));
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
if (mysqli_num_rows ($result) == 0)
{
	echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
	echo '<script type="text/javascript">document.location.href = "index.php";</script>';
	
}
else 
{
	$row = mysqli_fetch_assoc($result);
	$diplom['num'] = str_pad($row['MAX(diplom_num)']+1, 3, '0', STR_PAD_LEFT);
}

$diplom['stars'] = $diplom['stars']+1;
/*echo"<pre>";
print_r($row['MAX(diplom_num)']);
echo"</pre>";*/

$headers = array('№','Дата выдачи','Диплом ГВС','Регион','Звезды','Использованные тайники','Фото');
$h_width = array('45','50','','','55','','152');
//$f_size =  array('','','','','','','');

echo '
<table>
	<tr>';

foreach ($headers as $key => $hd)
{
	echo '
		<th  style="width: ' . $h_width[$key] . 'pt;font-size: ' . $f_size[$key] . 'pt;">' . $hd . '</th>';
}
$st_td = 'rowspan="5" style="text-align: center;"';
echo '
	</tr>
	<tr>
		<td '. $st_td . '>' . $diplom['gid'] . '-' . $diplom['num'] . '</td>
		<td '. $st_td . '>' . date("d.m.Y") . '</td>
		<td '. $st_td . '>' . $const[$diplom['gid']]['gvs'] . '</td>
		<td '. $st_td . '>' . $const[$diplom['gid']]['region'] . '</td>
		<td '. $st_td . '>' . $diplom['stars'] . '</td>
		';
foreach ($zayavka as $key => $data)
{
	echo '
		<td><a href="https://geocaching.su/?pn=101&cid=' . $data['cid'] . '" target="_blank">' . $data['ctype'] . '/' . $data['cid'] . '</a>  ' . $data['cname'] . '</td>';
	if ($data['punkt'] == 1) echo '<td '. $st_td . '><a href="https://geocaching.su' . $gvs_foto . '" target="_blank"><img src="https://geocaching.su' . $gvs_foto . '" style="width: 200px; height: 200px; object-fit: cover; margin: 1px; border: solid 1px grey;" /></td>';
	echo '</tr>';
}
	
echo '

	</tr>
</table>';

echo'
</br></br>
<table class="nav" style="width:100%; margin: 0;">
			<tr>
				<td class="nav1" style="width: 50%;"><a href="index.php">Назад</a></td>
				<td class="nav2"><a href="../../index.php">К началу</a></td>
			</tr>
</table>
</br>';


echo"<pre>";
print_r($diplom);
echo"</pre>";

echo"<pre>";
print_r($zayavka);
echo"</pre>";

echo"<pre>";
print_r($gvs_foto);
echo"</pre>";

echo"<pre>";
print_r($const);
echo"</pre>";

?>

</body>
</html>