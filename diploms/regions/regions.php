<?php 
ob_start();
session_start(); 

if(!$_SESSION['token'] OR !$_SESSION['geo_cat'] OR !$_POST['region']) 
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

if (!$_SESSION['user_caches']) echo '<script type="text/javascript">document.location.href = "../reload.php";</script>';

ob_end_flush();

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

?>

<head>
				<meta charset="utf-8">
				<meta name="description" content="Составление геокешерских дипломов. Дипломная программа geocaching.su"> 
				<title>РЕГИОНЫ РОССИИ - Проверка геокешерского диплома</title>
				<link rel="stylesheet" type="text/css" href="../../css/style.css" />
</head>
<?php
$userid = $_SESSION['userid'];
$username = $_SESSION['username'];
$o = 0;
foreach($_SESSION['user_caches'] as $kk => $vv)
{
	$caches_f[$o] = $vv;
	$caches_f[$o]['cid'] = $kk;
	$o++;
}
if ($_POST['region']) $region = $_POST['region'];
else echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';

require 'tables.php';
require '../../Classes/loc.php';
$link = mysqli_connect($a,$b,$c,$d);
if (!$link) 
{
	printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
	exit;
}

//уже использованные cid заливаем в массив
$usedcaches = array();

$query="SHOW TABLES LIKE '$user_regions_used_caches_table'";
$result = mysqli_query($link, $query) or die('error query' . mysqli_error($link));
if (mysqli_num_rows($result))
{
	$query = "SELECT 1 FROM $user_regions_used_caches_table LIMIT 1";
	$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
	if (mysqli_num_rows ($result) !== 0)
	{
		//echo "есть использованные тайники";
		$query = "SELECT * FROM $user_regions_used_caches_table";
		if($result = mysqli_query ($link, $query) or die("1Ошибка: " . mysqli_error($link)))
		{
			while ($arr = mysqli_fetch_array($result))
			{
				$usedcaches[] = $arr['cid'];
			}
		}
	}
}

$query = "SELECT * FROM $const_regions_table WHERE id = $region";
$result = mysqli_query ($link, $query) or die("2Ошибка: " . mysqli_error($link));
$object = mysqli_fetch_object($result);
$reg_name = $object->name;
$reg_number = $object->number;
if ($object->around) $reg_around = $object->around;
if ($object->iskl) $reg_iskl = $object->iskl;
if ($object->plus) $reg_plus = $object->plus;
mysqli_free_result($result);

if ($reg_around) 
{
	$reg_around = explode(',', $reg_around);
	$oblnum=count((array)$reg_around);
	
	$oblasti = array();
	for($o=0; $o<$oblnum; $o++)
	{
		$query = "SELECT name,plus FROM $const_regions_table WHERE id = '$reg_around[$o]'";
		if($result = mysqli_query ($link, $query) or die("3Ошибка: " . mysqli_error($link)))
		{
			while ($object = mysqli_fetch_object($result))
			{
				$oblasti[$o]["name"] = $object->name;
				$r_plus = $object->plus;
				if ($r_plus) 
				{
					$que = "SELECT name FROM $const_regions_table WHERE id IN ($r_plus)";
					if($res = mysqli_query ($link, $que) or die("3Ошибка: " . mysqli_error($link)))
					{
						while ($obj = mysqli_fetch_object($res))
						{
							$oblasti[$o]["plus"][] = $obj->name;
						}	
					}
					
				}
			}
		}
		mysqli_free_result($result);
	}
}
	
foreach($caches_f as $cid => $val)
{
	if(!in_array($val['cid'], $usedcaches) AND ($val['region'] === $reg_name)) $suite_caches_main[] = $val;
}

if ($reg_plus)
{
	$reg_plus = explode(',', $reg_plus);
	$c_plus = count((array)$reg_plus);
	
	$plus = array();
	for($o=0; $o<$c_plus; $o++)
	{
		$query = "SELECT * FROM $const_regions_table WHERE id = '$reg_plus[$o]'";
		if($result = mysqli_query ($link, $query) or die("3Ошибка: " . mysqli_error($link)))
		{
			while ($object = mysqli_fetch_object($result))
			{
				$plus[] = $object->name;
			}
		}
		mysqli_free_result($result);
	}
	$plus =  implode("','",$plus);	

	foreach($caches_f as $key => $val)
	{
		if(!in_array($val['cid'], $usedcaches) AND in_array($val['region'], array($reg_name, $plus))) $suite_caches_main[] = $val;
	}
}

$sc_count = count((array)$suite_caches_main);

echo '<form action="regions-save.php" method="POST" class="main">
		<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../../media/gc.png" /></a></p></div>';

echo '<div style="text-align:center;"><p><b><a href="http://www.geocaching.su/phorum/read.php?18,140237" target="blank">ДИПЛОМ "РЕГИОНЫ РОССИИ"</a></b></p></div>';
echo '<table class="nav">
		<tr>
			<td class="nav1"><a href="index.php">Выбор региона</a></td>
			<td class="nav2"><a href="../index.php">Выбор диплома</a></td>
		</tr>
	</table>';
echo '<div style="text-align:center;"><b>'.mb_strtoupper($reg_name, 'UTF-8').'</b><br><br>';

echo '<table>';
echo "<tr><td><b>Регион</b></td><td><b>Нужно</b></td><td><b>Есть</b></td></td></tr>";

$diplom=0;
$is=0;
if ($sc_count>=$reg_number) 
{
	echo '<tr class="yes"><td>'.$reg_name.'</td><td>'.$reg_number.'</td><td>'.$sc_count.'</td></tr>';
	$diplom++;
	$suite_caches_main = array_slice($suite_caches_main, 0, $reg_number);
}
else echo '<tr class="no"><td>'.$reg_name.'</td><td>'.$reg_number.'</td><td>'.$sc_count.'</td></tr>';


for($o=0; $o<$oblnum; $o++)
{
	foreach($caches_f as $key => $val)
	{
		if(($val['region'] == $oblasti[$o]["name"]) AND (!in_array($val['cid'], $usedcaches))) $suite_caches_obl[$o][] = $val;
	}

	if ($oblasti[$o]["plus"])
	{
		foreach ($oblasti[$o]["plus"] as $k => $v)
		{
			if(($val['region'] == $v) AND (!in_array($val['cid'], $usedcaches))) $suite_caches_obl[$o][] = $val;
		}
	}
	
	$sc_count = count((array)$suite_caches_obl[$o]);

	if ($sc_count>=1) 
	{
		echo '<tr class="yes"><td>'.$oblasti[$o]["name"].'</td><td>1</td><td>'.$sc_count.'</td></tr>';
		$diplom++;
		$suite_caches_obl[$o] = array_slice($suite_caches_obl[$o], 0, 1);
	}
	else echo '<tr class="no"><td>'.$oblasti[$o]["name"].'</td><td>1</td><td>'.$sc_count.'</td></tr>';
}

if ($reg_iskl)	
{	
	echo '<tr><td colspan="3" style="text-align:justify;">и один тайник <b>на выбор</b> из перечисленных ниже регионов (в зачет будет взят тайник из региона с наибольшим количеством тайников):</td></tr>';
	$iskl = array();
	$reg_iskl = implode ("','", explode(',', $reg_iskl));
	$query = "SELECT name FROM $const_regions_table WHERE id IN ('$reg_iskl')";
	if($result = mysqli_query ($link, $query) or die("33Ошибка: " . mysqli_error($link)))
	while ($arr = mysqli_fetch_assoc($result)) $iskl[] = $arr;
	
	foreach ($iskl as $v)
	{
		foreach ($oblasti[$o]["plus"] as $k => $v)
		{
			if(($val['region'] == $v["name"]) AND (!in_array($val['cid'], $usedcaches))) $suite_caches_iskl[$v["name"]][] = $val;
		}
		
		if($suite_caches_iskl) arsort($suite_caches_iskl);
		$s_count[$v["name"]] = count((array)$suite_caches_iskl[$v["name"]]);
		arsort($s_count);
	}
	foreach ($s_count as $k => $v)
	{
		if ($v>=1) 
		{
			echo '<tr class="yes"><td>'.$k.'</td><td>1</td><td>'.$v.'</td></tr>';
			$is=1;
		}
		else echo '<tr class="no"><td>'.$k.'</td><td>1</td><td>'.$v.'</td></tr>';
	}
	$diplom=$diplom+$is;
}
echo "</table>";
echo "</div>";
echo '<div  style="text-align:center;">';

if ($diplom == $oblnum+$is+1)
{
	echo '<p class="yes"><b>'.$username.'</b>, Вы можете получить диплом!</p>Нажмите на кнопку "Сохранить", что бы <b>сохранить использованные тайники в базе</b> и скачать заявку.';
	if ($oblnum) for ($o=0; $o<$oblnum; $o++) $suite_caches_main = array_merge($suite_caches_main, $suite_caches_obl[$o]);
	if ($suite_caches_iskl) array_push($suite_caches_main, $suite_caches_iskl[key($suite_caches_iskl)][0]);
	$_SESSION['saveregions'] = $suite_caches_main;
	$_SESSION['region'] = $region;
	$_SESSION['reg_name'] = $reg_name;
}
else echo '<p class="no"><b>'.$username.'</b>, у Вас не хватает тайников для получения диплома.</p>';
echo '</div>';
echo '<div>';

if ($diplom == $oblnum+$is+1) echo '<div style="text-align: center; border: none;"><input type="submit" value="Скачать заявку и сохранить тайники в базе"></div>';

echo '<table class="res">';

for ($o=0; $o<$reg_number; $o++)
{
	if(!$suite_caches_main[$o]["cid"]) echo '<tr class="no"><td>'.$reg_name.'</td><td></td><td></td></tr>';
	else echo '<tr class="yes"><td>'.$reg_name.'</td><td ><a href='.'"http://www.geocaching.su/?pn=101&cid='.$suite_caches_main[$o]["cid"].'" target="blank">'.$suite_caches_main[$o]["name"].'</a></td><td >'.$suite_caches_main[$o]["type"]."/".$suite_caches_main[$o]["cid"]."</td></tr>";
}

for($o=0; $o<$oblnum; $o++)
{
	if (!$suite_caches_obl[$o][0]["cid"]) echo '<tr class="no"><td>'.$oblasti[$o]["name"].'</td><td></td><td></td></tr>';
	else echo '<tr class="yes"><td >'.$oblasti[$o]["name"].'</td><td ><a href="http://www.geocaching.su/?pn=101&cid='.$suite_caches_obl[$o][0]["cid"].'" target="blank">'.$suite_caches_obl[$o][0]["name"].'</a></td><td >'.$suite_caches_obl[$o][0]["type"]."/".$suite_caches_obl[$o][0]["cid"]."</td></tr>";
}

if ($reg_iskl and $is==1) echo '<tr class="yes"><td>'.key($suite_caches_iskl)."</td><td><a href=".'"http://www.geocaching.su/?pn=101&cid='.$suite_caches_iskl[key($suite_caches_iskl)][0]["cid"].'" target="blank">'.$suite_caches_iskl[key($suite_caches_iskl)][0]["name"]."</a></td><td>".$suite_caches_iskl[key($suite_caches_iskl)][0]["type"]."/".$suite_caches_iskl[key($suite_caches_iskl)][0]["cid"]."</td></tr>";
elseif ($reg_iskl and $is<>1)	echo '<tr class="no"><td>&nbsp;</td><td></td><td></td></tr>';

echo "</table>";

if ($diplom == $oblnum+$is+1) echo '<div style="text-align: center; border: none;"><input type="submit" value="Скачать заявку и сохранить тайники в базе"></div>';
echo '</div>';
echo '<table class="nav">
		<tr>
			<td class="nav1"><a href="index.php">Выбор региона</a></td>
			<td class="nav2"><a href="../index.php">Выбор диплома</a></td>
		</tr>
	</table>
				
</form>';

unset ($suite_caches_main);

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## отработал скрипт РЕГИОНЫ РОССИИ\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

mysqli_close($link);
?>