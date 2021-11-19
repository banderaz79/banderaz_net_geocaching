<?php 
ob_start();
session_start(); 

if(!$_SESSION['token'] OR !$_SESSION['geo_cat']) 
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

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
?>

<head>
				<meta charset="utf-8">
				<meta name="description" content="Составление геокешерских дипломов. Дипломная программа geocaching.su"> 
				<title>АЗБУКА ГЕОКЕШЕРА - Проверка геокешерского диплома</title>
				<link rel="stylesheet" type="text/css" href="../../css/style.css" />
</head>

<?php
if (!$_SESSION['user_caches']) echo '<script type="text/javascript">document.location.href = "../reload.php";</script>';

$userid = $_SESSION['userid']; //$userid = 134443; //29695-banderaz 111161-Xanthippe 127896-Белочка Чернобыльская //134443
$username = $_SESSION['username'];

$ae = range(chr(0xC0), chr(0xC5));
array_push($ae, chr(0xA8));
$jya = range(chr(0xC6), chr(0xDF));
$aya = array_merge($ae,$jya);
$abc = array();
foreach ($aya as $b) $abc[] = iconv('CP1251', 'UTF-8', $b);

$iskl = array("Ё","Й","Ы","Ь","Ъ");

$caches_f = $_SESSION['user_caches'];

foreach($abc as $a)
{
	$s=0;
    foreach($caches_f as $key => $val)
	{
		$l=0;
		$b1 = mb_substr($caches_f[$key]["name"], $l, 1);
		while (!preg_match("/[a-zA-Zа-яА-Я0-9]/ui",$b1)) 
		{
			$l++;
			$b1 = mb_substr($caches_f[$key]["name"], $l, 1);
		}
		
		$b2 = mb_strtoupper($b1);
	
		if($b2 === $a)
        {
            $all_caches[$a][$s]["name"] = $caches_f[$key]["name"];
			$all_caches[$a][$s]["type"] = $caches_f[$key]["type"];
			$all_caches[$a][$s]["cid"] = $key;
			$s++;
        }
		else
		{
			foreach ($iskl as $bukvy)
			{
				if($a == $bukvy)
				{
					$pos = stripos(mb_strtoupper($caches_f[$key]["name"]),$bukvy);
					if ($pos) 
					{
						$all_caches[$a][$s]["name"] = $caches_f[$key]["name"];
						$all_caches[$a][$s]["type"] = $caches_f[$key]["type"];
						$all_caches[$a][$s]["cid"] = $key;
						$s++;
					}
				}
			}
		}
    }
}

foreach($abc as $a)
{
	$c=count($all_caches[$a]);
	$min[$a] = $c;
}
asort($min);


$suite_caches = array();
$sss=array();
foreach ($min as $k=>$v) 
{
	$z=count ($all_caches[$k]);
	for($b=0; $b<$z; $b++)
	{
		foreach ($suite_caches as $kkk=>$vvv) $sss[$kkk]=$vvv["name"];
		if (!in_array($all_caches[$k][$b]["name"], $sss))	
		{
			$suite_caches[$k]["name"] = $all_caches[$k][$b]["name"];
			$suite_caches[$k]["type"] = $all_caches[$k][$b]["type"];
			$suite_caches[$k]["cid"] = $all_caches[$k][$b]["cid"];
			break;
		}
	}
	if ($k=="Ъ" and !$suite_caches[$k])
	{
		foreach($caches_f as $key => $val)
		{
			if ($caches_f[$key]['own'] == 'YES' and !in_array ($caches_f[$key]["name"], $sss))
			{
				$suite_caches[$k]["name"] = $caches_f[$key]["name"];
				$suite_caches[$k]["type"] = $caches_f[$key]["type"];
				$suite_caches[$k]["cid"] = $key;
				break;
			}
		}
	}
}
ksort($suite_caches);
$sc=count($suite_caches);

echo '<form action="abc-save.php" method="POST" class="main">
		<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../../media/gc.png" /></a></p></div>';

echo '<div style="text-align:center;"><p><b><a href="http://www.geocaching.su/phorum/read.php?18,213882" target="blank">ДИПЛОМ "АЗБУКА ГЕОКЕШЕРА"</a></b></p></div><div style="text-align:center;">';
if ($sc == 33) echo '<p class="yes"><b>'.$username."</b>, Вы можете получить диплом!";
elseif ($sc==32 and !$suite_caches["Ё"]) echo '<p><b>'.$username."</b>, у Вас не хватает тайника с буквой ".'"Ё"'.'<br>Но! Принимаются в зачет также те тайники, в названиях которых согласно правил русского языка должна стоять "Ё", а стоит "Е" (например, "Мертвая тишина"). Проверьте, и если такие тайники есть, внесите в заявку вручную.';
else echo '<p class="no"><b>'.$username.'</b>, у Вас не хватает тайников для получения диплома.';

echo "</div>";

echo '<table class="nav">
		<tr>
			<td class="nav1"><a href="../../index.php">К началу</a></td>
			<td class="nav2"><a href="../index.php">Выбор диплома</a></td>
		</tr>
	</table>';
echo '<div><table class="res">';
if ($sc == 33) echo '<div style="text-align: center; border: none;"><input type="submit" value="Скачать заявку"></div>';
elseif ($sc==32 and !$suite_caches["Ё"]) echo '<div style="text-align: center; border: none;"><input type="submit" value="Скачать заявку"></div>';
foreach($abc as $a)
{

	if ($suite_caches[$a])
	{
		echo '<tr class="yes"><td>'.$a.'</td><td><a href="http://www.geocaching.su/?pn=101&cid='.$suite_caches[$a]["cid"].'" target="blank">'.$suite_caches[$a]["name"];
		echo "</td><td>".$suite_caches[$a]["type"]."/".$suite_caches[$a]["cid"]."</td>";
	}
	else echo '<tr class="no"><td>'.$a.'</td><td></td><td></td>';	
}



echo "</tr></table>";

if ($sc == 33) 
{
	echo '<div style="text-align: center; border: none;"><input type="submit" value="Скачать заявку"></div>';
	$_SESSION['saveabc'] = $suite_caches;
	$_SESSION['abc'] = $abc;
}
elseif ($sc==32 and !$suite_caches["Ё"]) 
{
	echo '<div style="text-align: center; border: none;"><input type="submit" value="Скачать заявку"></div>';
	$_SESSION['saveabc'] = $suite_caches;
	$_SESSION['abc'] = $abc;
}
echo '</div><table class="nav">
		<tr>
			<td class="nav1"><a href="../../index.php">К началу</a></td>
			<td class="nav2"><a href="../index.php">Выбор диплома</a></td>
		</tr>
	</table>';

echo '</form>';

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## отработал скрипт АЗБУКА ГЕОКЕШЕРА\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

unset ($caches_f);
?>