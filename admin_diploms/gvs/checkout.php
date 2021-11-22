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
if (isset($_POST['foto'])) $diplom['gvs_foto'] = $gvs_foto = $_POST['foto'];
else $diplom['gvs_foto'] = $gvs_foto = '/images/unknown.jpg';
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
	$diplom['num'] = $row['MAX(diplom_num)']+1;
	$num = str_pad($diplom['num'], 3, '0', STR_PAD_LEFT);
}

$diplom['stars'] = $diplom['stars']+1;
/*echo"<pre>";
print_r($row['MAX(diplom_num)']);
echo"</pre>";*/

echo "
	<div style='padding:5px;'>
		<div style='width: 5%; float:left; text-align:right; padding:0 15px 0 0;'>игрок:</div>
		<div style='font-weight: bold; display: table-cell;'>" . $diplom['user'] . "</div>
		<div style='font-size: 8pt;  padding:0 0 0 10px; display: table-cell;'><a href='https://geocaching.su/profile.php?uid=" . $diplom['uid'] . "' target='_blank'>профиль игрока</a></div>
		<div style='font-size: 8pt;  padding:0 0 0 10px; display: table-cell;'>дипломы игрока</div>
	</div>
	<div style='padding:5px;'>
		<div style='width: 5%; float:left; text-align:right; padding:0 15px 0 0;'>uid:</div>
		<div>" . $diplom['uid'] . "</div>
	</div>
	<div style='padding:5px;'>
		<div style='width: 5%; float:left; text-align:right; padding:0 15px 0 0;'>звезды:</div>
		<div style='font-weight: bold; display: table-cell;'>" . $diplom['stars'] . "</div>
		<div style='font-size: 10pt;  padding:0 0 0 10px; display: table-cell;'>(с учетом данного диплома)</div>
	</div>
	<div style='padding:5px;'>
		<div style='width: 5%; float:left; text-align:right; padding:0 15px 0 0;'>имя:</div>
		<div style='font-weight: bold;'>" . $diplom['fio'] . "</div>
	</div>
	<div style='padding:5px;'>
		<div style='width: 5%; float:left; text-align:right; padding:0 15px 0 0;'>e-mail:</div>
		<div><a href='mailto:" . $diplom['email'] . "?subject=Re: Диплом ГВС " . $gvs . "'>" . $diplom['email'] . "</a></div>
	</div>
	";
echo"</br></br>";

$headers = array('№','Дата выдачи','Диплом ГВС','Регион','Звезды','Использованные тайники','Фото');
$h_width = array('50','55','150','180','55','','80');
//$f_size =  array('','','','','','','');

echo '
<table  style="">
	<tr style="">';

foreach ($headers as $key => $hd)
{
	echo '
		<th  style="width: ' . $h_width[$key] . 'pt;">' . $hd . '</th>';
}
echo '
	</tr>
</table>';

$st_td = 'rowspan="5" style="text-align: center; width: ';

echo '
<table>
	<tr  style="">
		<td '. $st_td . $h_width[0] . 'pt;">' . $diplom['gid'] . '-' . $num . '</td>
		<td '. $st_td . $h_width[1] . 'pt;">' . date("d.m.Y") . '</td>
		<td '. $st_td . $h_width[2] . 'pt;">' . $const[$diplom['gid']]['gvs'] . '</td>
		<td '. $st_td . $h_width[3] . 'pt;">' . $const[$diplom['gid']]['region'] . '</td>
		<td '. $st_td . $h_width[4] . 'pt;">' . $diplom['stars'] . '</td>
		';
foreach ($zayavka as $key => $data)
{
	echo '
		<td style="width: ' . $h_width[5] . 'pt; ' . $border . '">
			<table style="border: none;">
				<tr style="">
					<td style="border: none; width: 35pt;">
						<a href="https://geocaching.su/?pn=101&cid=' . $data['cid'] . '" target="_blank">' . $data['cid'] . '</a>
					</td>
					<td style="border: none;">
						  ' . $data['cname'] . '
					</td>
				</tr>
			</table>';

	if ($data['punkt'] == 1) echo '<td '. $st_td . $h_width[6] . 'pt;"><a href="https://geocaching.su' . $gvs_foto . '" target="_blank"><img src="https://geocaching.su' . $gvs_foto . '" style="width: 100px; height: 100px; object-fit: cover; margin: 1px; border: solid 1px grey;" /></td>';
	echo '</tr>';
}
	
echo '

	</tr>
</table>';

echo '
</br>
<p>
	Игроку <b>' . $diplom['user'] . '</b> будет выдан (записан в базу) диплом <b>' . $const[$diplom['gid']]['gvs'] . '</b> № <b>' . $diplom['gid'] . '-' . $num . '</b> от <b>' . date("d.m.Y") . '</b> и присвоена <b>' . $diplom['stars'] . '-я</b> звезда.
</p>
</br>
';

$caches_to_suit = array();
foreach ($zayavka as $key => $data)
{
	if ($data['punkt'] !== 1)
	{
		$used[] = $key;
		if($data['suitable'] !== True) 
		{
			$caches_to_suit[$key]['cname'] = $data['cname'];
			$caches_to_suit[$key]['region'] = $data['region'];
		}
	}
}
$diplom['used'] = implode(',', $used);

if ($caches_to_suit)
{
	echo '<p>Следующие тайники будут добавлены в базу подходящих тайников:</p>
	<ul>';
	
	foreach ($caches_to_suit as $cid => $data)
	{
		echo '<li style="list-style-type: none;">' . $cid . ' ' . $data['cname'] . '</li>';
	}
	echo '</ul>';
	$suit = 1;
} 

//$extra = $diplom['stars']/10;
if (is_int($diplom['stars']/10))
{

	$diploms = array();
	$query = sprintf("SELECT MAX(diplom_num) FROM $extra_table WHERE `uid` = %u  AND `extra_diplom` = %u", mysqli_real_escape_string($link, $diplom['uid']), mysqli_real_escape_string($link, $extra));
	$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
	if (mysqli_num_rows ($result) !== 0)
	{
		$row = mysqli_fetch_assoc($result);
		$diplom['extra'] = $row['MAX(diplom_num)']+1;
		$extra = str_pad($diplom['extra'], 3, '0', STR_PAD_LEFT);
	}
	echo '
	</br>
	<p>
		Так же игроку <b>' . $diplom['user'] . '</b> будет выдан (записан в базу) дополнительный диплом <b>' . $diplom['stars']/10 . '-й</b> степени № <b>' . $extra . '</b> от <b>' . date("d.m.Y") . '</b>.
	</p>
	</br>
	';
}

$_SESSION['diplom'] = $diplom;
unset ($_SESSION['zayavka']);

echo '
	</br></br>
	<form enctype="multipart/form-data" action="add_diplom.php" method="post" style="">	
		<div style="border: none; width: 100%;text-align: center;">
			<input id="submit_btn" type="submit" value="Записать в базу" style="font-size: 16pt;">
		</div>
	</form>';

echo'
</br></br>
<table class="nav" style="width:100%; margin: 0;">
			<tr>
				<td class="nav1" style="width: 50%; padding: 5px;"><a href="index.php">Назад</a></td>
				<td class="nav2" style="width: 50%; padding: 5px;"><a href="../../index.php">К началу</a></td>
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
print_r($caches_to_suit);
echo"</pre>";

?>

</body>
</html>