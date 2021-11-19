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
		font-size: 14px;
		width:100%;
	   }
	   td, th {
		border: 1px solid grey;
		}
	</style>	
</head>
<body>
<?php 
if (isset($_FILES['uploadfile']) AND isset($_POST['userid']))
{
	$file_type = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);
	$file_name = pathinfo($_FILES['uploadfile']['name'], PATHINFO_BASENAME);
	$uid = $_POST['userid']; 
	
	if($uid<1)
	{
		echo '<script type="text/javascript">alert("Вы не ввели uid игрока");</script>';
		echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		die("error");
	}
	
// Проверки
	
	$url = 'https://geocaching.su/profile.php?uid='.$uid;
	include('../../curlinit.php');
	$user_page = curl_exec($ch);
	preg_match_all('/<title>Профайл игрока:(?P<unick>.*?)<\/title>/u', $user_page, $user_profile);
	
	if(!$user_profile['unick'])
	{
		echo '<script type="text/javascript">alert("Игрока с uid=' . $uid . ' не существует.");</script>';
		echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		die("error");
	}
	elseif($file_name == "") {
		echo '<script type="text/javascript">alert("Вы не выбрали .CSV файл");</script>';
		echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	elseif($file_type != "csv") {
		echo '<script type="text/javascript">alert("Можно загружать только .CSV файл");</script>';
		echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	elseif($_FILES['uploadfile']['error'] > 0) {
		echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
		echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
		die("error");
	}
	else
	{
// Данные из заявки в массив
		$uploadfile = $_SESSION['TMPDIR']."/".$_SESSION['userid']."_zd_";
		$num = rand(10,1000);
		while (file_exists($uploadfile.$num)) $num++;
		$uploadfile .= $num;
		move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfile);
		
		$csv = file($uploadfile);
		if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
	
		$punkt = array('1. Железная дорога', '2. Узкоколейная железная дорога', '3. Детская железная дорога', '4. Железнодорожный вокзал, станция, депо', '5. Музей железных дорог, железнодорожной техники', '6. Паровоз, тепловоз, электровоз', '7. Поезд, бронепоезд, состав, вагон', '8. Железнодорожный мост, туннель', '9. Трагедия на железной дороге', '10. Прочее (достопримечательности, имеющее непосредственное отношение к железной дороге)'); 
		
		$zd_caches = array();
		$npp = 1;
		
		$x = 0;
		$y = 0;
		$str = explode(";", $csv[$y+3]);
		$prov = iconv('windows-1251', 'UTF-8', $str[$x]);

		if($prov !== "Ник в Игре:") 
		{
			$x = 1;
			$y = 1;
		}

		$str = explode(";", $csv[$y+3]);
		$unick_z = iconv('windows-1251', 'UTF-8', $str[$x+2]);

		
		for($i=0; $i < count($csv); $i++)
		{

			if(!preg_match("/^;*\s$/",str_replace(' ', '', $csv[$i])))
			{
				$str = explode(";", iconv('windows-1251', 'UTF-8', $csv[$i]));
				if(in_array(trim($str[$x]), $punkt))
				{
					$zd_caches[trim($str[$x])] = array();
					$punkt_n = trim($str[$x]);
				}
				else
				{
					if(!trim($str[$x+1])) 
					{
						preg_match_all("/\[(.{5,8}.?)\]/", $str[$x+2], $temp_str);
/*echo'<pre>';
print_r($temp_str);
echo'</pre>';*/					
						$str[$x+1] = $temp_str[1][0];
					}
					$zd_caches[$punkt_n][$npp++] = array('$i+1' => $i+1, 'cid_z' => preg_replace("/\D/", "", trim($str[$x+1])), 'cname_z' => trim($str[$x+2]), 'cdate_z' => trim($str[$x+3]));
				}
			}
		}
		unset ($csv);

/*echo'<pre>';
print_r($zd_caches);
echo'</pre>';*/
		
// Парсим созданные и найденные тайники с сайта
		for ($i=1; $i<3; $i++)
		{
			$url = 'https://geocaching.su/site/popup/userstat.php?s='.$i.'&uid='.$uid;
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

// Обрабатываем
		$problem = array();
		$dbl=array();
		
		$zayavka = array();
		foreach($zd_caches as $zd_key => $zd_value)
		{
			if($zd_value)
			{
				foreach($zd_value as $cdata_z)
				{
					if(in_array($cdata_z['cid_z'], $owned_caches_info['cid']))	
					{
						$num = array_keys($owned_caches_info['cid'], $cdata_z['cid_z']);
						$temp = $owned_caches_info;
						$cfound = '(со)автор';
						$status_comment = 'autorremark';
						$comment = 'Авторский тайник';
					}
					elseif(in_array($cdata_z['cid_z'], $found_caches_info['cid']))
					{
						$num = array_keys($found_caches_info['cid'], $cdata_z['cid_z']);
						$temp = $found_caches_info;
						$cfound = 'да';
						
						$url = 'https://geocaching.su/?pn=101&cid=' . $cdata_z['cid_z'];
						include('../../curlinit.php');
						$cache_page = curl_exec($ch);
						$regexp = '/images\/commenticon\/(?P<status>.*?)\..*profile\.php\?uid=' . $uid . '.*\n.*Review">(?P<comment>[\s\S]*?)<\/div>/';
						preg_match_all($regexp, $cache_page, $user_comments);
						
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
						}
						else
						{
							$status_comment = '';
						}
						
						if($user_comments['comment'][$num_com]) $comment = $user_comments['comment'][$num_com];
						else $comment = '';
					}
					else 
					{
						unset($temp);
						unset($cfound);
						unset($status_comment);
						unset($comment);
					}
/*echo'<pre>';
print_r($cdata_z);
echo'</pre>';*/		
					
					
					
					if($cdata_z['cid_z']) $cid_temp = $cdata_z['cid_z'];
					else unset($cdata_z);
					if(array_key_exists($cid_temp, $zayavka)) 
					{
						$cid_temp = $cdata_z['cid_z'] . '_D';
						$zayavka[$cid_temp]['dbl'] = $cdata_z['cid_z'];
						$zayavka[$cdata_z['cid_z']]['dbl'] = $cdata_z['cid_z'];
						$dbl[$cdata_z['cid_z']]++;
					}
					$zayavka[$cid_temp]['punkt'] = $zd_key;
					$zayavka[$cid_temp]['ctype'] = $temp['ctype'][$num[0]];
					$zayavka[$cid_temp]['cname_z'] = $cdata_z['cname_z'];
					$zayavka[$cid_temp]['cdate_z'] = $cdata_z['cdate_z'];
					$zayavka[$cid_temp]['cfound'] = $cfound;
					$zayavka[$cid_temp]['cdate'] =  $temp['cdate'][$num[0]];
					$zayavka[$cid_temp]['cname'] =  $temp['cname'][$num[0]];
					$zayavka[$cid_temp]['status_comment'] =  $status_comment;
					$zayavka[$cid_temp]['comment'] =  $comment;
					$zayavka[$cid_temp]['$i+1'] =  $cdata_z['$i+1'];
				}
			}
			else $problem[2] = 1;
		}

/*echo'<pre>';
print_r($dbl);
echo'</pre>';*/

// Запоняем таблиу с данными

		$messages = array(
			1 => 'общее количество тайников меньше 37',
			2 => 'в каких-то пунктах нет тайников',
			3 => 'тайник не числится взятым/созданным',
			4 => 'нет записи в инет-блокноте',
			5 => 'статус записи отличен от "найден" или "восстановлен"',
			6 => 'тайник продублирован'	
			);
		
		$result = '';
		
		if($problem[2]) $result .= '<span style="color:#A60000;">' . $messages[2] . '</span></br>';
	
		echo'
		<table class="nav">
			<tr>
				<td class="nav1" style="width: 50%"><a href="../../index.php">К началу</a></td>
				<td class="nav2" style="width: 50%"><a href="index.php">Новая проверка</a></td>
			</tr>
		</table>';
		echo '</br></br><div style="width:100%; text-align:center; font-size: 110%;"><span style="font-size:25px;">' . $unick_z . '  /  ' . $uid . ' - ' . $user_profile['unick'][0] . '</span></div></br></br>';
		
		echo'
		<table>
			<tr>
				<th>N</th>
				<!--<th>Тип</th>
				<th>ID</th>-->
				<th style="min-width:250px;">Название из заявки</th>
				<th>Дата из заявки</th>
				<th>Взят</th>
				<th>Дата с сайта</th>
				<th style="width:250px">Название с сайта</th>
				<th></th>
				<th>Комментарий</th>
			</tr>';
			
		$npp = 1;
		

		foreach($punkt as $pu)
		{
			echo'
			<tr>
				<td colspan="10" style="background: #fff;"><b>' . $pu . '</b></td>
			</tr>';
			foreach($zayavka as $cid => $data)
			{
				if($data['punkt'] == $pu)
				{
					if(!$data['dbl']) echo '
			<tr>
				<td>' . $npp++ . '</td>';		
					else 
					{
						if($dbl[$data['dbl']] == 1) echo '<tr style="background:#FFB273;"><td>' . $npp++ . '</td>';
						else echo '<tr style="background:#FFB273;"><td></td>';
					}
					//echo '				<td>' . $data['ctype'] . '</td>';
					if($data['dbl'])
					{
						echo '
				<td><a href="https://geocaching.su/?pn=101&cid=' . $data['dbl'] . '" target=_blank>' . $data['ctype'] . '/' . $data['dbl'] . '</a> ' . $data['cname_z'] . '</td>';
						$problem[6] = 1;
						if($dbl[$data['dbl']] == 1) 
						{
							$result .= '<a href="https://geocaching.su/?pn=101&cid=' . $data['dbl'] . '" target=_blank>' . $data['ctype'] . '/' . $data['dbl'] . '</a> ' . $data['cname'] . '  -  <span style="color:#A60000;">' . $messages[6] . '</span></br>';					
							$dbl[$data['dbl']]++;
						}
					}
					else echo '<td><a href="https://geocaching.su/?pn=101&cid=' . $cid . '" target=_blank>' . $data['ctype'] . '/' . $cid . '</a> ' . $data['cname_z'] . '</td>';
					echo '
				<!--<td>' . $data['cname_z'] . '</td>-->
				<td>' . $data['cdate_z'] . '</td>';
					if($data['cfound']) echo'
				<td>' . $data['cfound'];
					else
					{
						echo '<td style="background:#FF7373;">';
						$problem[3] = 1;
						$result .= '<a href="https://geocaching.su/?pn=101&cid=' . $cid . '" target=_blank>' . $data['ctype'] . '/' . $cid . '</a> ' . $data['cname'] . '  -  <span style="color:#A60000;">' . $messages[3] . '</span></br>';	
					}
					echo '
				</td>
				<td>' . $data['cdate'] . '</td>
				<td>' . $data['cname'] . '</td>';
					if(!$data['status_comment']) echo '
				<td style="background:#FF7373;">';
					elseif(!in_array($data['status_comment'], array('success', 'repair', 'autorremark')))
					{
						echo '<td style="background:#FF7373;"><img src="https://geocaching.su/images/commenticon/' . $data['status_comment'] . '.png" style="width:20px;">';
						$problem[5] = 1;
						$result .= '<a href="https://geocaching.su/?pn=101&cid=' . $cid . '" target=_blank>' . $data['ctype'] . '/' . $cid . '</a> ' . $data['cname'] . '  -  <span style="color:#A60000;">' . $messages[5] . '</span></br>';
					}
					else echo '<td><img src="https://geocaching.su/images/commenticon/' . $data['status_comment'] . '.png" style="width:20px;">';
					echo '
				</td>';
					if($data['comment']) echo '
				<td>' . $data['comment'];
					else
					{
						echo '<td style="background:#FF7373;">' . $data['comment'];
						$problem[4] = 1;
						$result .= '<a href="https://geocaching.su/?pn=101&cid=' . $cid . '" target=_blank>' . $data['ctype'] . '/' . $cid . '</a> ' . $data['cname'] . '  -  <span style="color:#A60000;">' . $messages[4] . '</span></br>';
					}
					echo '
				</td>
			</tr>';
				}
				
			}
		}
		if($npp < 37)
		{
			$problem[1] = 1;
			$result = '<span style="color:#A60000;">' . $messages[1] . '</span></br>' . $result;
		}
		
		echo '
		</table>';
		
		echo '</br><span style="font-size:25px;">Отчет:</span>';
		if($result == '') $result = "всё ОК"; 
		echo '</br></br>' . $result . '</br></br>';
		
		echo'</br></br>
		<table class="nav">
			<tr>
				<td class="nav1" style="width: 50%"><a href="../../index.php">К началу</a></td>
				<td class="nav2" style="width: 50%"><a href="index.php">Новая проверка</a></td>
			</tr>
		</table>';
	}

	
	

	if($problem)
	{
		$message = 'ЕСТЬ ПРОБЛЕМЫ:\n\n\n';
		ksort($problem);
		foreach($problem as $k => $v)
		{
			$message .= $messages[$k] . '\n\n';
		}
		$message .= '\nСмотри таблицу и список внизу';
		
		echo '<script type="text/javascript">alert("' . $message . '");</script>';

		die("");
	}
	
}

?>

</body>
</html>