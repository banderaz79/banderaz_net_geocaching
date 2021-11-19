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

unset ($_SESSION['user_fotos']);
unset ($_SESSION['zayavka']);
unset ($_SESSION['diplom']);
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
$problem = array();
$zayavka = array();
$diplom = array();

$diplom['user'] = $z_data[6]['E']; // ник игрока
$diplom['uid'] = (int)$z_data[6]['uid']; // uid игрока
$diplom['stars'] = (int)$z_data[6]['stars']; // количество звезд у игрока
if($z_data[7]['E']) $diplom['fio'] = $z_data[7]['E']; // ФИО
else $diplom['fio'] = 'имя не указано';
if($z_data[8]['E']) $diplom['email'] = $z_data[8]['E']; // мэйл игрока
else $diplom['email'] = 'адрес e-mail не указан';
$gvs = $z_data[10]['E']; // ГВС


require '../../cfl/dict.php';
require 'tables.php';
require '../../Classes/loc.php';
$link = mysqli_connect($a,$b,$c,$d);
if (!$link) 
{
	printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
	exit;
}

//загружаем из базы таблицу с константами гвс
$query = sprintf("SELECT * FROM $const_table");
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
if (mysqli_num_rows ($result) == 0)
{
	echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
	echo '<script type="text/javascript">document.location.href = "index.php";</script>';
	
}
else 
{
	$i=1;
	while($row = mysqli_fetch_assoc($result))
	{
		$const[$i++] = $row;
	}
}
foreach($const as $value)
{
	if($value['gvs'] == $gvs)
	{
		$diplom['gid'] = (int)$value['gid'];
	}
}

// проверяем, есть ли у игрока уже такой диплом
$diploms = array();
$query = sprintf("SELECT 1 FROM $diploms_table WHERE `uid` = %u  AND `gid` = %u", mysqli_real_escape_string($link, $diplom['uid']), mysqli_real_escape_string($link, $diplom['gid']));
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
if (mysqli_num_rows ($result) !== 0)
{
	//echo '<script type="text/javascript">alert("У игрока уже есть диплом ' . $const[$diplom['gid']]['gvs'] . '");</script>';
	//echo '<script type="text/javascript">document.location.href = "index.php";</script>';
	//exit;
	
	$diplom_exists = True;
}

// загружаем из базы тайники, уже использованные игроком в других дипломах
$used_caches = array();
$query = sprintf("SELECT `used_caches` FROM $diploms_table WHERE `uid` = %u", mysqli_real_escape_string($link, $diplom['uid']));
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
if (mysqli_num_rows ($result) !== 0)
{
	while($row = mysqli_fetch_assoc($result)) $used_caches[] = $row;
	foreach($used_caches as $key => $value) $used_caches[$key] = $value['used_caches'];
	$used_caches = implode(",", $used_caches);
	$used_caches = explode(",", $used_caches);
}

/*echo"<pre>";
print_r($used_caches);
echo"</pre>";*/

// загружаем из базы тайники, подходящие для условий диплома в данном регионе
$suitable = array();
$region = $const[$diplom['gid']]['region'];
$query = sprintf("SELECT `cid` FROM $suitable_table WHERE `сregion` ='%s'", mysqli_real_escape_string($link, "$region"));
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
if (mysqli_num_rows ($result) !== 0)
{
	while($row = mysqli_fetch_assoc($result)) 
	{
		$suitable[] = $row['cid'];
	}
}

// загружаем с сайта инфомацию по найденным и созданным тайникам игрока
for ($i=1; $i<3; $i++)
{
	$url = 'https://geocaching.su/site/popup/userstat.php?s='.$i.'&uid='.$diplom['uid'];
	include('../../curlinit.php');
	$caches_page = curl_exec($ch);

	if ($i==2) 
	{
		preg_match_all('/<tr><td>.*?alt=.(?P<ctype>\w\w)[\s\S]*?<a href=.*?pn=101&cid=(?P<cid>\d+).*?blank>(?P<cname>.*?)<\/a><\/b>.*?<b>(?P<cowner>.*?)<\/b>.*?, (?P<country>.*?)[,|\)](?P<region>.*?)[,|\)|<].*?найден (?P<cdate>\d\d\.\d\d\.\d\d\d\d)/', $caches_page, $found_caches_info);
	}
	else 
	{
		preg_match_all('/<tr><td>.*?alt=.(?P<ctype>\w\w)[\s\S]*?<a href=.*?pn=101.*?cid=(?P<cid>\d+).*?blank.*?>(?P<cname>.*?)<\/a><\/b>.*?создан (?P<cdate>\d\d\.\d\d\.\d\d\d\d), (?P<country>.*?)[,|<|\/](?P<region>.*?)(,|\(|<|\/|рейтинг)/u', $caches_page, $owned_caches_info); 
	}
}
/*echo"<pre>";
print_r($z_data);
echo"</pre>";*/
/*echo"<pre>";
print_r($owned_caches_info);
echo"</pre>";*/
/*echo"<pre>";
print_r($found_caches_info);
echo"</pre>";*/

// проверка
for($i=15; $i<20; $i++)
{
	// проверяем, является ли номер тайника числом
	if(!preg_match('/^\d{1,5}$/', $z_data[$i]['C']))
	{
		$o=$i-14;
		echo '<script type="text/javascript">alert("ID тайника №' . $o . ' в заявке не является числовым значением или не введено");</script>';
		echo '<script type="text/javascript">document.location.href = "index.php";</script>';
	}
	
	// проверка, является ли первый тайник в заявке тем тайником, который является обязательным для диплома
	if($i == 15)
	{
		if($const[$diplom['gid']]['cid'] !== $z_data[15]['C'])
		{
			$problem[$z_data[$i]['C']][] = 1;
		}
	}
	else
	{
		// проверка, не является ли тайник тайником другого ГВС
		foreach($const as $value)
		{
			if($value['cid'] == $z_data[$i]['C'])
			{
				$problem[$z_data[$i]['C']][] = 2;
			}
		}
		// проверка, не было ли уже этого тайника в других дипломах игрока
		if(in_array($z_data[$i]['C'], $used_caches)) $problem[$z_data[$i]['C']][] = 3;
	}
	if($i !== 15)
	{
		if(!in_array($z_data[$i]['C'], $suitable)) $problem[$z_data[$i]['C']][] = 4; // тайник не входит в список подходящих
		else $z_data[$i]['suitable'] = True;
	}
	if(in_array($z_data[$i]['C'], $owned_caches_info['cid']))
	{
		$num = array_keys($owned_caches_info['cid'], $z_data[$i]['C']);
		$temp = $owned_caches_info;
		$cfound = '(со)автор';
		$status_comment = 'autorremark';
		$comment = 'Авторский тайник'; 
	}
	elseif(in_array($z_data[$i]['C'], $found_caches_info['cid']))
	{
		$num = array_keys($found_caches_info['cid'], $z_data[$i]['C']);
		$temp = $found_caches_info;
		$cfound = 'да';
		
		$url = 'https://geocaching.su/?pn=101&cid=' . $z_data[$i]['C'];
		include('../../curlinit.php');
		$cache_page = curl_exec($ch);
		$regexp = '/images\/commenticon\/(?P<status>.*?)\..*profile\.php\?uid=' . $diplom['uid'] . '.*\n.*Review">(?P<comment>[\s\S]*?)<\/div>/';
		preg_match_all($regexp, $cache_page, $user_comments);

/*echo"<pre>";
print_r($user_comments);
echo"</pre>";*/
		
		$num_com = 0;
		if($user_comments['status']) 
		{
			if(count($user_comments['status']) > 1)
			{
				if(in_array('repair', $user_comments['status']))
				{
					$user_comments_num = array_keys($user_comments['status'], 'repair');
					$num_com = $user_comments_num[0];
				}
				if(in_array('success', $user_comments['status']))
				{
					$user_comments_num = array_keys($user_comments['status'], 'success');
					$num_com = $user_comments_num[0];
				}
			}
			$status_comment = $user_comments['status'][$num_com];
			if(!in_array($status_comment, array('repair','success','autorremark'))) $problem[$z_data[$i]['C']][] = 9; // тип комментария отличается от "взял", "восстановил" и "авторская заметка"
		}
		else
		{
			$status_comment = '';
		}
		
		if($user_comments['comment'][$num_com] and $user_comments['comment'][$num_com] !== '') $comment = $user_comments['comment'][$num_com];
		else 
		{
			$comment = '';
			$problem[$z_data[$i]['C']][] = 6; // отсутствует комментарий игрока
		}
	}
	else 
	{
		$problem[$z_data[$i]['C']][] = 5; //тайник отсутствует в списке взятых и созданных
		$problem[$z_data[$i]['C']][] = 9; // тип комментария отличается от "взял", "восстановил" и "авторская заметка"
		$problem[$z_data[$i]['C']][] = 6; // отсутствует комментарий игрока
		unset($cfound);
		unset($temp);
		unset($status_comment);
		unset($comment);
	
		require_once('../../getoldapi.php');
		$x_cache = getoldapi(2, $z_data[$i]['C'], '&istr="m"');
	}

	// проверяем, соответствует ли регион тайника региону ГВС
	if(trim($temp['region'][$num[0]]) !== $region) 
	{
		$problem[$z_data[$i]['C']][] = 7;
	}
	
	$cid_temp = $z_data[$i]['C'];
		
	if(array_key_exists($cid_temp, $zayavka)) 
	{
		$cid_temp = $z_data[$i]['C'] . '_D';
		$problem[$z_data[$i]['C']][] = 8; // тайник повторяется в заявке
	}
	$zayavka[$cid_temp]['punkt'] = $i-14;
	if($temp['ctype'][$num[0]]) $zayavka[$cid_temp]['ctype'] = $temp['ctype'][$num[0]];
	else
	{
		foreach($ctype as $k => $v) // тип тайника из dict.php
		{
			if(in_array($x_cache['type'], $v)) $zayavka[$cid_temp]['ctype'] = $v[0];
		}
	}
	$zayavka[$cid_temp]['cid'] = $z_data[$i]['C'];
	$zayavka[$cid_temp]['cname_z'] = $z_data[$i]['E'];
	if($temp['cname'][$num[0]]) $zayavka[$cid_temp]['cname'] =  $temp['cname'][$num[0]];
	else $zayavka[$cid_temp]['cname'] = $x_cache['name'];
	$zayavka[$cid_temp]['region'] =  $temp['region'][$num[0]];
	$zayavka[$cid_temp]['cdate'] =  $temp['cdate'][$num[0]];
	$zayavka[$cid_temp]['cfound'] = $cfound;
	$zayavka[$cid_temp]['status_comment'] =  $status_comment;
	$zayavka[$cid_temp]['comment'] =  $comment;
	$zayavka[$cid_temp]['suitable'] = $z_data[$i]['suitable'];
}

/*echo"<pre>";
print_r($zayavka);
echo"</pre>";*/

unset ($z_data);

foreach($problem as $cid => $data)
{
	$problem[$cid] = array_flip($data);
}

/*echo"<pre>";
print_r($problem);
echo"</pre>";*/


echo "<div style='padding:5px;'>
		<div style='width: 5%; float:left; text-align:right; padding:0 15px 0 0;'>ГВС:</div>
		<div style='font-weight: bold; display: table-cell; text-transform: uppercase;";
if($diplom_exists) echo "
		background: #FF4747;'>$gvs - ДИПЛОМ УЖЕ ЧИСЛИТСЯ ВЗЯТЫМ У ИГРОКА!!!</div>";
else echo "'>$gvs</div>";
echo"
		<div style='font-size: 8pt;  padding:0 0 0 10px; display: table-cell;'>дипломы по этому гвс</div>
		<div style='font-size: 8pt;  padding:0 0 0 10px; display: table-cell;'>подходящие тайники в базе</div>
	</div>
	<div style='padding:5px;'>
		<div style='width: 5%; float:left; text-align:right; padding:0 15px 0 0;'>регион:</div>
		<div style='display: table-cell;'>$region</div>
	</div>
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
		<div style='font-size: 10pt;  padding:0 0 0 10px; display: table-cell;'>(на момент рассмотрения этой заявки)</div>
	</div>
	<div style='padding:5px;'>
		<div style='width: 5%; float:left; text-align:right; padding:0 15px 0 0;'>имя:</div>
		<div style='font-weight: bold;'>" . $diplom['fio'] . "</div>
	</div>
	<div style='padding:5px;'>
		<div style='width: 5%; float:left; text-align:right; padding:0 15px 0 0;'>e-mail:</div>
		<div>" . $diplom['email'] . "</div>
	</div>
	";
echo"</br></br>";
echo"<i>&nbsp;&nbsp;Вся информация парсится по id тайников, указанных в заявке</i>";	
echo"</br></br>";

$headers = array('№','Тип','ID','Название из заявки','Название','Регион','Дата','Найден','','Комментарий');
$h_width = array(13,22,27,150,210,110,56,45,13,'');
$f_size = array('','','',9,'','','','','','');

echo '
	<table>
		<tr>';
foreach($headers as $key => $val)
{
	echo"	<th style='width:$h_width[$key]pt;font-size:$f_size[$key]pt;'>$val</th>";	
}
echo"	</tr>";

foreach($zayavka as $cid => $data)
{
	$__problem = array();
	if(array_key_exists($data['cid'], $problem))
	{
		$__problem = $problem[$data['cid']];
		foreach($__problem as $err => $nn)
		{
			if(in_array($err, array(8))) 
			{
				echo "
		<tr style='background: #FFCA95;'>";
				break;
			}
			if(in_array($err, array(1,2,3,7))) 
			{
				echo "
		<tr style='background: #FF9393;'>";
				break;
			}
		}
	}
	else 
	{
		echo"
		<tr style='background: #88DDA0;'>";
	}
	
	foreach($data as $key => $val)
	{
		echo"
			<td ";
		switch ($key) 
		{
			case 'cname_z':
				echo"style='font-size: 9pt;'>$val</a>";
				break;
			case 'cdate_z':
				echo"style='font-size: 9pt;'>$val</a>";
				break;
			case 'cid':

				if (!array_key_exists(4, $__problem) and $data['punkt'] !== 1) echo "style='background: #88DDA0;'>";
				else echo">";				
				echo'<a href="https://geocaching.su/?pn=101&cid=' . $val . '" target="_blank">' . $val . '</a>';
				break;
			case 'region':
				if (array_key_exists(7, $__problem)) echo "style='background: #FF4747;'>"; 
				else echo">";
				echo"$val";
				break;
			case 'cfound':
				if (array_key_exists(5, $__problem)) echo "style='background: #FF4747;'>"; 
				else echo">";
				echo"$val";
				break;
			case 'status_comment':
				if (array_key_exists(9, $__problem)) echo "style='background: #FF4747;'>"; 
				else echo">";
				echo"<img src='https://geocaching.su/images/commenticon/$val.png' alt='' style='width: 15pt'>";
				break;
			case 'comment':
				if (array_key_exists(6, $__problem)) echo "style='background: #FF4747;'>"; 
				else echo">";
				echo"$val";
				break;
			default:
				echo">$val";
				break;
		}
		echo"
			</td>";
	}
	echo"
		</tr>";
}
echo"
	</table>";

$first = reset($zayavka);

if ($first['cfound'] !== '(со)автор')
{
	$url = 'https://geocaching.su/showmemphotos.php?cid=' . $first['cid'];

	include('../../curlinit.php');
	$foto_page = curl_exec($ch);

	$reg = "/<tr><th>Фото от.+profile\.php\?uid=" . $diplom['uid'] . "[\S\s]+<\/td><\/tr>/U";
	preg_match_all($reg, $foto_page, $user_f);

	$reg = '/\/photos\/albums\/\d+\.jpg/';
	preg_match_all($reg, htmlentities($user_f[0][0]), $user_fotos); 
	
	if($user_fotos[0])
	{
		echo '<p>Фото из альбома тайника ГВС:</br>';
		foreach($user_fotos[0] as $foto)
		{
			echo '<a href="https://geocaching.su' . $foto . '" target="_blank"><img src="https://geocaching.su' . $foto . '" style="width: 200px; height: 200px; object-fit: cover; margin: 2px; border: solid 1px grey;"></a>';
		}
		echo '</p>';
	}
	else echo "<p style='background: #FF4747; color: white;'>&nbsp;&nbsp;В ФОТОАЛЬБОМЕ НЕТ ФОТОГРАФИЙ ИГРОКА!!!</p>";
}
else echo "<p style='background: #88DDA0;'>&nbsp;&nbsp;Авторский тайник, фото не нужно.</p>";

$errors = array(
1 => 'Тайник не является основным тайником по данному ГВС.',
2 => 'Тайник является основным тайником другого ГВС.',
3 => 'Тайник уже был использован в других дипломах.',
7 => 'Тайник не соответствует региону ГВС.',
8 => 'Тайник повторяется в заявке.',
5 => 'Тайник отсутствует в списке взятых и созданных тайников игрока.',
6 => 'Нет комментария.',
9 => 'Cтатус записи отличен от "найден" или "восстановлен".',
4 => 'Тайника пока нет в базе подходящих тайников для данного ГВС. Проверьте, подходит ли он под условия перед тем, как продолжить.' 
);
//echo "</br></br>";

$is_problem = False;

if (count($problem)>0)
{
	foreach($problem as $cid => $data)
	{
		echo "</br>";
		if($zayavka[$cid]['ctype']) $kod = $zayavka[$cid]['ctype'] . "/" . $cid;
		else $kod = $cid;
		echo '&nbsp;&nbsp;<b><a href="https://geocaching.su/?pn=101&cid=' . $cid . '" target="_blank">' . $kod . '</a> - ' . $zayavka[$cid]['cname'] . '</b>.</br>';	
		if(array_key_exists(4,$data)) echo "&nbsp;&nbsp;$errors[4]</br>";
		//else ($suitable[$cid]
		foreach($data as $err => $nn)
		{
			if($err !== 4) echo "&nbsp;&nbsp;$errors[$err]</br>";
			$is_problem = True;
		}
	}
}

if (!$diplom_exists or !$is_problem)
{
	if($user_fotos[0]) $_SESSION['user_fotos'] = $user_fotos[0];
	$_SESSION['zayavka'] = $zayavka;
	$_SESSION['diplom'] = $diplom;
	unset ($zayavka);
	echo '
	</br></br>
	<form enctype="multipart/form-data" action="checkout.php" method="post" style="">
		<div style="border: none; width: 100%;text-align: center;">
			<input type="submit" value="Нажмите, чтобы продолжить" style="font-size: 16pt;">
		</div>
	</form>';
}

echo'
</br></br>
<table class="nav" style="width:100%; margin: 0;">
			<tr>
				<td class="nav1" style="width: 50%;"><a href="index.php">Назад</a></td>
				<td class="nav2"><a href="../../index.php">К началу</a></td>
			</tr>
</table>
</br>';

if ($diplom_exists)
{
	echo '<script type="text/javascript">alert("У игрока уже есть диплом ' . $const[$diplom['gid']]['gvs'] . '");</script>';
	exit;
}
else
{
	echo '<script type="text/javascript">alert("Перед тем, как продолжить, убедитесь, что:\n\n- есть фото игрока на фоне стелы ГВС\n\n- тайники, которых нет в базе подходящих тайников для данного ГВС, подходят по условиям.");</script>';
}
?>

		

</body>
</html>