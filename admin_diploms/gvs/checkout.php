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
	$uid = $diplom['uid'];
	$gid = $diplom['gid'];
	$zayavka = $_SESSION['zayavka'];
	$const = $_SESSION['const'];
}
else echo '<script type="text/javascript">document.location.href = "index.php";</script>';

if (isset($_SESSION['diplom']['gvs_foto'])) $diplom['gvs_foto'] = $gvs_foto = $_SESSION['diplom']['gvs_foto'];
elseif (isset($_POST['foto'])) $diplom['gvs_foto'] = $gvs_foto = $_POST['foto'];
else $diplom['gvs_foto'] = $gvs_foto = '/images/unknown.jpg';
$_SESSION['diplom'] = $diplom;
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
$query = sprintf("SELECT MAX(diplom_num) FROM $diploms_table WHERE `gid` =  %u", mysqli_real_escape_string($link, $gid));
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
if (mysqli_num_rows ($result) == 0)
{
	echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
	echo '<script type="text/javascript">document.location.href = "index.php";</script>';
	
}
else 
{
	$row = mysqli_fetch_assoc($result);
	$diplom['diplom_num'] = (int)$row['MAX(diplom_num)']+1;
	$num_str = $gid . '-' . str_pad($diplom['diplom_num'], 3, '0', STR_PAD_LEFT);
}

$stars = $diplom['stars'] = (int)$diplom['stars']+1;

$diplom_num = $diplom['diplom_num'];

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
		<div style='font-weight: bold; display: table-cell;'>" . $stars . "</div>
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
		<td '. $st_td . $h_width[0] . 'pt;">' . $num_str . '</td>
		<td '. $st_td . $h_width[1] . 'pt;">' . date("d.m.Y") . '</td>
		<td '. $st_td . $h_width[2] . 'pt;">' . $const[$gid]['gvs'] . '</td>
		<td '. $st_td . $h_width[3] . 'pt;">' . $const[$gid]['region'] . '</td>
		<td '. $st_td . $h_width[4] . 'pt;">' . $stars . '</td>
		';

/*echo"<pre>";
print_r($zayavka);
echo"</pre>";*/

$main_cache = null;

foreach ($zayavka as $key => $data)
{
	if ($data['punkt'] == 1 and $gid !== 11) $main_cache = $key;

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
	Игроку <b>' . $diplom['user'] . '</b> будет выдан (записан в базу) диплом <b>' . $const[$gid]['gvs'] . '</b> № <b>' . $num_str . '</b> от <b>' . date("d.m.Y") . '</b> и присвоена <b>' . $stars . '-я</b> звезда.
</p>
</br>
';

$caches_to_suit = array();
foreach ($zayavka as $key => $data)
{
	if (($data['punkt'] !== 1) or ($data['punkt'] == 1 and $gid == 11))
	{
		$used[] = $key;
		if($data['suitable'] !== True) 
		{
			$caches_to_suit[$key]['cname'] = $data['cname'];
			$caches_to_suit[$key]['cregion'] = $data['region'];
		}
	}
}
$diplom['used_caches'] = implode(',', $used);

if ($caches_to_suit)
{
	echo '<p>Следующие тайники будут добавлены в базу подходящих тайников:</p>
	<ul>';
	
	foreach ($caches_to_suit as $cid => $data)
	{
		echo '<li style="list-style-type: none;">' . $cid . ' ' . $data['cname'] . '</li>';
	}
	echo '</ul>';
} 


if (is_int($stars/10))
{
	$diplom['extra_diplom'] = $extra_diplom = $stars/10;
	
	$query = sprintf("SELECT MAX(extra_num) FROM $extra_table WHERE `extra_diplom` = %u", $stepen);
	$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
	if (mysqli_num_rows ($result) !== 0)
	{
		$row = mysqli_fetch_assoc($result);
		$diplom['extra_num'] = $row['MAX(extra_num)']+1;
		$extra_num = str_pad($diplom['extra_num'], 3, '0', STR_PAD_LEFT);
	}
	echo '
	</br>
	<p>
		Так же игроку <b>' . $diplom['user'] . '</b> будет выдан (записан в базу) дополнительный диплом <b>' . $diplom['extra_diplom'] . '-й</b> степени № <b>' . $extra_num . '</b> от <b>' . date("d.m.Y") . '</b>.
	</p>
	</br>
	';
}

echo '
	</br></br>
	<form enctype="multipart/form-data" action="" method="post" style="">	
		<div style="border: none; width: 100%;text-align: center;">
			<input name="go" type="hidden" value="1">
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

if ($_POST['go'])
{
	$diplom_ok = '';
	$diplom_err = '';
	$suit_err = "";
	$suit_ok = "";
	$extra_err = "";
	$extra_ok = "";

	$diplom_date = date("d.m.Y");
	
	///// Добавляем основной диплом в базу
	$values = array($diplom['uid'], $gid, $diplom_num, '"'.$diplom_date.'"', $stars, $main_cache, '"'.$diplom['used_caches'].'"', '"'.$diplom['gvs_foto'].'"');
	$str = implode(',',$values);

	include_once ('add_diplom.php');

	///// Добавляем подходящие тайники в базу
	if ($caches_to_suit)
	{
		foreach ($caches_to_suit as $cid => $data)
		{
			$values = array($cid, '"' . mysqli_real_escape_string($link, $caches_to_suit[$cid]['cname']) . '"', '"' . mysqli_real_escape_string($link, $caches_to_suit[$cid]['cregion']) . '"');
			$str = implode(',',$values);
			include ('add_suitable.php');
		}
	}

	///// Добавляем дополнительный диплом в базу
	if ($diplom['extra_diplom'])
	{
		$values = array((int)$uid, (int)$extra_diplom, (int)$extra_num, '"'.$diplom_date.'"','""');
		$str = implode(',',$values);
		include_once ('add_extra_diplom.php');
	}

	//// Обновляем количество звезд у игрока
	include_once ('update_user_stars.php');

	
	$message = $diplom_ok . $diplom_err . $extra_ok . $extra_err . $suit_ok . $suit_err;

	unset ($_SESSION['diplom']);
	unset ($_SESSION['zayavka']);
	echo '<script type="text/javascript">alert("' . $message . '");</script>';
	echo '<script type="text/javascript">window.top.location.href = "";</script>';
	exit;




////////////////////////////////////////////////////////////////////	
// ПЕРЕХОД НА СТРАНИЦУ ИГРОКА, ГДЕ БУДУТ ВЫВЕДЕНЫ ДИПЛОМЫ И ДАННЫЕ

}

?>

</body>
</html>