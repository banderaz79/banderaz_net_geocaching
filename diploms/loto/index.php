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
if(!$_SESSION['user_caches']) echo '<script type="text/javascript">document.location.href = "../reload.php";</script>';

ob_end_flush();

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
?>

<head>
		<meta charset="utf-8">
		<meta name="description" content="Составление геокешерских дипломов. Дипломная программа geocaching.su"> 
		<title>ГЕОЛОТО - Проверка геокешерского диплома</title>
		<link rel="stylesheet" type="text/css" href="../../css/style.css" />
		<script type="text/javascript">  function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}
</script>
<script type="text/javascript">
function enable_btn()
{
document.getElementById('submit_btn1').disabled=false;
document.getElementById('submit_btn2').disabled=false;
}
</script>
</head>
<style>
body{
    font-family: Georgia, serif;
}

/* скрытый блок */
.terms {
    display:none;
}
/* кликабельный текст */
.tt {
    cursor: pointer;
} 

</style>
<?php
$userid = $_SESSION['userid'];

$o = 0;
foreach($_SESSION['user_caches'] as $kk => $vv)
{
	$caches_f[$o] = $vv;
	$caches_f[$o]['cid'] = $kk;
	$o++;
}

$types=array(
	'TR' => 'Традиционный',
	'MS' => 'Традиционный пошаговый',
	'VI' => 'Виртуальный',
	'MV' => 'Пошаговый виртуальный');
	
foreach ($types as $t=>$r)
{
	$o = 0;
	foreach($caches_f as $k => $v)
	{
		if($t == $v['type']) 
		{
			$caches[$t][$o]['type'] = $v['type'];
			$caches[$t][$o]['cid'] = $v['cid'];
			$caches[$t][$o]['name'] = $v['name'];
			$o++;
		}
	}
}

$csv = file('const_loto.csv');
for($i=0; $i < count($csv); $i++)
{
	$cards[$i+1]=trim($csv[$i]);
}

$card_=array();
for($i=1;$i<25;$i++)
{
	$score[$i]=60;
	$card=explode(',', $cards[$i]);
	foreach ($types as $t=>$r)
	{
		foreach ($card as $c)
		{
			$c = trim($c);
			for ($o=0; $o<count($caches[$t]); $o++)
			{
				if (preg_match('/'.$c.'/', $caches[$t][$o]['cid']))
				{
					$card_[$i][$t][$c][] = $caches[$t][$o]['cid'];
				}
			}
			$count_[$i][$t][$c] = count((array)$card_[$i][$t][$c]);
			asort($count_[$i][$t]);
		}
	}
}



unset($caches);


for($i=1;$i<25;$i++)
{
	$suite[$i][0][0] = 0;
	foreach ($types as $t=>$r)
	{
		foreach ($count_[$i][$t] as $c=>$v)
		{
			for($o=0;$o<count((array)$card_[$i][$t][$c]);$o++)
			{
				foreach ($suite[$i] as $nnn=>$mmm) $sss[] = $mmm[$t];
				if(!in_array($card_[$i][$t][$c][$o], $sss))
				{	
					$suite[$i][$c][$t] = $card_[$i][$t][$c][$o];
					//echo "карточка".$i.' число '.$c.' тип '.$t.' '.$card_[$i][$t][$c][$o]." добавлена</br>";
					break; 
				}
				//else echo $card_[$i][$t][$c][$o]." уже есть</br>";
				unset($sss);
			}
			if (!$suite[$i][$c][$t]) $score[$i]--;
		}
	}
	ksort($suite[$i]);
}

foreach ($types as $t=>$r)
{
	$o = 0;
	foreach($caches_f as $k => $v)
	{
		if($t == $v['type'])
		{
			$caches[$t][$v['cid']]['type'] = $v['type'];
			$caches[$t][$v['cid']]['cid'] = $v['cid'];
			$caches[$t][$v['cid']]['name'] = $v['name'];
			$o++;
		}
	}
}

arsort($score);

$chisla=array('Первое','Второе','Третье','Четвертое','Пятое','Шестое','Седьмое','Восьмое','Девятое','Десятое','Одиннадцатое','Двенадцатое','Тринадцатое','Четырнадцатое','Пятнадцатое');

echo '<form action="loto-save.php" method="POST" class="main">
		<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../../media/gc.png" /></a></p></div>';

echo '<div style="text-align:center;"><p><b><a href="http://www.geocaching.su/phorum/read.php?18,244452" target="blank">ДИПЛОМ "ГЕОЛОТО"</a></b></p></div><div style="text-align:center;">';
if (in_array(60, $score)) echo '<p class="yes"><b>'.$_SESSION['username'].'</b>, Вы можете получить диплом!</p><p>Отметьте нужную заявку и нажмите "Скачать"</p>';
else echo '<p class="no"><b>'.$_SESSION['username'].'</b>, у Вас пока нет полностью закрытых карточек.</p>';

echo '</div>';
echo '<table class="nav">
		<tr>
			<td class="nav1"><a href="../../index.php">К началу</a></td>
			<td class="nav2"><a href="../index.php">Выбор диплома</a></td>
		</tr>
	</table>';

echo '</div><div style="text-align:center;">';
if (in_array(60, $score)) echo '<div style="text-align: center; border: none;"><input id="submit_btn1" type="submit" value="Скачать заявку" disabled></div>';

foreach($score as $k=>$v)
{
	if($v==60) $ss++;
	$card=explode(',', $cards[$k]);	
	echo '
	
	<table>
		<tr>
			<td style="text-align: center; vertical-align: middle; width: 10%">';
	if($v==60) 
	{	
		echo '<input type="radio" value="'.$k.'" name="savekarta" onclick="enable_btn()">';
	}
	echo'	</td>
	
			<td style="background: ';
	if($v==60) echo ' #CCFFCC;';
	else if($v==59) echo '#FFFF99;';
	else echo '#FFbbbb;';
	echo ' width: 1000px; padding: 1%;">
				<span class="tt" onclick="view(\''.$k.'\'); return false"><b>Карточка №'.$k.' - '.$v.' из 60</b></span>
			</td>
			
		</tr>
	</table>
	
    <span id="'.$k.'" class="terms">';
	echo '<table>
			<tr>
				<td style="padding: 5px; background: #CCFFFF; text-align: center;">Тип Тайника</td><td style="text-align:center; padding: 5px;  background: #CCFFFF;">Название Тайника</td><td style="text-align:center; padding: 5px; background: #CCFFFF;"><b>Код</b></td>
			</tr>
			';
	for ($o=0; $o<15; $o++)
	{
		echo '
			<tr>
				<td style="padding: 5px; background: #CCFFCC;">&nbsp;</td><td style="text-align:right; padding: 5px;  background: #CCFFCC;">'.$chisla[$o].' число карточки:</td><td style="text-align:center; padding: 5px; background: #CCFFFF;"><b>'.$card[$o].'</b></td>
			</tr>';
			foreach ($types as $t=>$r)
			{
	
				if($caches[$t][$suite[$k][$card[$o]][$t]]["name"]) 
				{	
					echo '<tr><td style="text-align:center; padding: 5px;">'.$r.'</td><td style="padding: 5px;  width: 450px;"><a href="http://www.geocaching.su/?pn=101&cid='.$caches[$t][$suite[$k][$card[$o]][$t]]["cid"].'" target="blank">'.$caches[$t][$suite[$k][$card[$o]][$t]]["name"].'</td><td style="padding: 5px">'.$caches[$t][$suite[$k][$card[$o]][$t]]["type"].'/'.$caches[$t][$suite[$k][$card[$o]][$t]]["cid"].'</td></tr>';
					if($v==60) 
					{
						$saveloto[$k][$card[$o]][$t]["name"]=$caches[$t][$suite[$k][$card[$o]][$t]]["name"];
						$saveloto[$k][$card[$o]][$t]["type"]=$caches[$t][$suite[$k][$card[$o]][$t]]["type"];
						$saveloto[$k][$card[$o]][$t]["cid"]=$caches[$t][$suite[$k][$card[$o]][$t]]["cid"];
					}
				}
				else echo '<tr style="background: #FFbbbb;"><td style="padding: 5px">'.$r.'</td><td></td><td></td></tr>';
			
			}
	}
	echo '</table><br>
    </span>

	';
}
if ($ss > 0) 
{
	echo '<div style="text-align: center; border: none;"><input id="submit_btn2" type="submit" value="Скачать заявку" disabled></div>';
	$_SESSION['saveloto']=$saveloto;
	$_SESSION['cards']=$cards;
}

echo '</div>';

echo '<table class="nav">
		<tr>
			<td class="nav1"><a href="../../index.php">К началу</a></td>
			<td class="nav2"><a href="../index.php">Выбор диплома</a></td>
		</tr>
	</table>';
echo '</form>';

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## отработал скрипт ГЕОЛОТО\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

unset($caches_f);
unset ($caches);
unset ($suite);
unset ($card);
unset ($types);
unset ($result);

?>