<?php
ob_start();
session_start();

if(!$_SESSION['token']) 
{
	$new_url = '../index.php';
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

echo '	<html>
			<head>
				<?PHP //header("charset=utf-8");?>
				<meta charset="utf-8">
				<meta name="description" content="Список взятых и созданных тайников по user id игрока"> 
				<title>Список взятых и созданных тайников по user id игрока</title>
				<link rel="stylesheet" type="text/css" href="../css/style.css" />
			</head>
			<body>
			<div class="main" >
				<form enctype="multipart/form-data" action="" method="post" style="text-align: center;">
					<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../media/gc.png" /></a></p></div>

					<div>
						Введите id игрока</br>
						<input type="text" name="userid">
						<input type="submit"><br />
					</div>
				</form>

				<table class="nav">
					<tr>
						<td class="nav1"><a href="../index.php">К началу</a></td>
					</tr>
				</table>
			</div>
			</body>
		</html>';
		
if (isset($_POST['userid']))
{
	$uid = $_POST['userid'];
	
	if($uid<1)
	{
		$uid = $_SESSION['userid'];
	}
	
	//проверка, есть ли такой пользователь
	
	$url = 'https://geocaching.su/profile.php?uid='.$uid;

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['cookiefile']);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,30); 
	curl_setopt($ch,CURLOPT_USERAGENT,'Bot 1.0');
	curl_setopt($ch, CURLOPT_REFERER,'https://geocaching.su');
	$user_page = curl_exec($ch);

	preg_match_all('/<title>Профайл игрока:(?P<unick>.*?)<\/title>/u', $user_page, $user_profile);
	
echo "<pre>";
print_r($user_profile);
echo "</pre>";

	if(!$user_profile['unick'])
	{
		echo '<script type="text/javascript">alert("Игрока с uid=' . $uid . ' не существует.");</script>';
		echo '<script type="text/javascript">window.top.location.href = "";</script>';
		die("error");
	}
	else 
	{
	//тайники по uid
		for ($i=1; $i<3; $i++)
		{
			
			$url = 'https://geocaching.su/site/popup/userstat.php?s='.$i.'&uid='.$uid;

			$ch = curl_init($url);
			//curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['cookiefile']);
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,30); 
			curl_setopt($ch,CURLOPT_USERAGENT,'Bot 1.0');
			//curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17');
			curl_setopt($ch, CURLOPT_REFERER,'https://geocaching.su');
			$caches_page = curl_exec($ch);
			//$caches_page = iconv("cp1251", "utf-8", $caches_page);
			

			if ($i==2) 
			{
				//preg_match_all('/<tr><td>.*?alt=.(?P<ctype>\w\w)[\s\S]*?<a href=.*?pn=101&cid=(?P<cid>\d+).*?blank>(?P<cname>.*?)<\/a><\/b>.*?<\/b>.*?, (?P<country>.*?)[,|\)](?P<region>.*?)[,|\)|<].*?найден (?P<cdate>\d\d\.\d\d\.\d\d\d\d)/', $caches_page, $found_caches_info);
				preg_match_all('/<tr><td>.*?alt=.(?P<ctype>\w\w)[\s\S]*?<a href=.*?pn=101&cid=(?P<cid>\d+).*?blank>(?P<cname>.*?)<\/a><\/b>.*?<b>(?P<cowner>.*?)<\/b>.*?, (?P<country>.*?)[,|\)](?P<region>.*?)[,|\)|<].*?найден (?P<cdate>\d\d\.\d\d\.\d\d\d\d)/', $caches_page, $found_caches_info);
			}
			else 
			{
				preg_match_all('/<tr><td>.*?alt=.(?P<ctype>\w\w)[\s\S]*?<a href=.*?pn=101.*?cid=(?P<cid>\d+).*?blank.*?>(?P<cname>.*?)<\/a><\/b>.*?создан (?P<cdate>\d\d\.\d\d\.\d\d\d\d), (?P<country>.*?)[,|<|\/](?P<region>.*?)(,|\(|<|\/|рейтинг)/u', $caches_page, $owned_caches_info); 
			}
		}
		if(empty($found_caches_info['ctype']) AND empty($owned_caches_info['ctype']))
		{
			echo '<script type="text/javascript">alert("У игрока ' . $user_profile['unick'][0] . ' (uid ' . $uid . ') нет найденных или созданных тайников.");</script>';
			echo '<script type="text/javascript">window.top.location.href = "";</script>';
			die("error");
		}
		else
		{
			echo'<br /><br />
				<form enctype="multipart/form-data" action="" method="post" style="text-align: center;">
					<div style="width: 100%; text-align: center; border: none;">
						<input type="submit" value="Скачать в xls"><br />
					</div>';
			
			$table_head = array("Тип","ID","Название","Автор","Дата","Страна","Область");
			$field_name = array('ctype','cid','cname','cowner','cdate','country','region');
			$col_num = count($table_head);
			$owned_caches_num = count($owned_caches_info['ctype']);
			$found_caches_num = count($found_caches_info['ctype']);
			
			echo'</br>';
			echo'<table style="border: 2px solid grey; border-collapse: separate; font-size: 11pt; background: #A5ACB2;">
					<tr style="text-align: center;">
						<td colspan="'. $col_num .'" style="text-align: center; padding: 20px; font-size: 20px;">Игрок <b>' . $user_profile['unick'][0] . '</b>  (uid <span style=""><b>' . $uid . '</b>)</span></td>
					</tr>
					<tr style="text-align: center;">';
			foreach($table_head as $th) echo'<th style="border: 1px solid grey; padding: 10px;background-color: #C8DDCE;">' . $th . '</th>';
			echo'	</tr>';
			
			if(!empty($owned_caches_info['ctype']))
			{
				echo'<tr>
							<td colspan="'. $col_num .'" style="text-align: center; text-transform: uppercase; font-weight: bold;">Созданные тайники:   ' . $owned_caches_num . '</td>
						<tr>';
				for($cnum = 0; $cnum < $owned_caches_num; $cnum++)
				{
					echo'<tr>';
					foreach($field_name as $fn)
					{
						if($fn == 'country') $owned_caches_info[$fn][$cnum] = str_replace('(соавтор)','',$owned_caches_info[$fn][$cnum]);  
						if($fn == 'cid') $owned_caches_info[$fn][$cnum] = '<a href="https://geocaching.su/?pn=101&cid=' . $owned_caches_info[$fn][$cnum] . '" target="_blank">' . $owned_caches_info[$fn][$cnum] . '</a>';
						echo'<td style="border: 1px solid grey; padding: 5px 10px 5px 10px; background-color: #C8DDCE;">' . trim($owned_caches_info[$fn][$cnum]) . '</td>';
					}
					echo'</tr>';
				}
			}
			
			if(!empty($found_caches_info['ctype']))
			{
				echo'	<tr>
							<td colspan="'. $col_num .'" style="text-align: center; text-transform: uppercase; font-weight: bold;">Найденные тайники:   ' . $found_caches_num . '</td>
						<tr>';
				for($cnum = 0; $cnum < $found_caches_num; $cnum++)
				{
					echo'<tr>';
					foreach($field_name as $fn)
					{
						if($fn == 'cid') $found_caches_info[$fn][$cnum] = '<a href="https://geocaching.su/?pn=101&cid=' . $found_caches_info[$fn][$cnum] . '" target="_blank">' . $found_caches_info[$fn][$cnum] . '</a>';
						echo'<td style="border: 1px solid grey; padding: 5px 10px 5px 10px;background-color: #C8DDCE;">' . trim($found_caches_info[$fn][$cnum]) . '</td>';
					}
					echo'</tr>';
				}
			}
			echo'</table>';
			echo'<br />
					<div style="width: 100%; text-align: center; border: none;">
						<input type="submit" value="Скачать в xls"><br />
					</div>
				</form>';
		}
	}
/*echo'</br></br></br></br></br>';
echo "<pre>";
print_r($owned_caches_info);
echo "</pre>";*/
/*echo "<pre>";
var_dump($owned_caches_info);
echo "</pre>";*/

unset ($found_caches_info);
unset ($owned_caches_info);
}
?>
