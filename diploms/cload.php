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

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

if($_SESSION['loadcaches'] == 1) 
{
	$new_url = 'index.php';
	header('Location: '.$new_url);
	ob_end_flush();
	exit;
}

$userid = $_SESSION['userid'];//29695; //29695-banderaz 111161-Xanthippe 127896-Белочка Чернобыльская //134443; 30073

//тайники по uid
if (!$_SESSION['user_caches'])
{
	$user_caches = array();
	$caches_number = array();

	for ($i=1; $i<3; $i++)
	{
		
		$url = 'https://geocaching.su/site/popup/userstat.php?s='.$i.'&uid='.$userid;

		include ('../curlinit.php');
		$caches_page = curl_exec($ch);
		//$caches_page = iconv("cp1251", "utf-8", $caches_page);

		if ($i==2) 
		{
			preg_match_all('/<tr><td>.*?alt=.(?P<type>\w\w)[\s\S]*?<a href=.*?pn=101&cid=(?P<cid>\d+).*?blank>(?P<name>.*?)<\/a><\/b>.*?<b>(?P<cowner>.*?)<\/b>.*?, (?P<country>.*?)[,|\)](?P<region>.*?)[,|\)|<].*?найден (?P<cdate>\d\d\.\d\d\.\d\d\d\d)/', $caches_page, $found_caches_info);
			$caches_number['NO'] = count($found_caches_info["type"]);

			for($w=0;$w<$caches_number['NO'];$w++)
			{	
				$user_caches[$found_caches_info["cid"][$w]]['type'] = trim($found_caches_info["type"][$w]);
				$user_caches[$found_caches_info["cid"][$w]]['name'] = trim($found_caches_info["name"][$w]);
				$user_caches[$found_caches_info["cid"][$w]]['country'] = trim($found_caches_info["country"][$w]);
				$user_caches[$found_caches_info["cid"][$w]]['region'] = trim($found_caches_info["region"][$w]);
				$user_caches[$found_caches_info["cid"][$w]]['date'] = date('Y-m-d',strtotime($found_caches_info['cdate'][$w]));
				$user_caches[$found_caches_info["cid"][$w]]['own'] = "NO";
			}
		}
		else 
		{
			preg_match_all('/<tr><td>.*?alt=.(?P<type>\w\w)[\s\S]*?<a href=.*?pn=101.*?cid=(?P<cid>\d+).*?blank.*?>(?P<name>.*?)<\/a><\/b>.*?создан (?P<cdate>\d\d\.\d\d\.\d\d\d\d), (?P<country>.*?)[,|<|\/](?P<region>.*?)(,|\(|<|\/|рейтинг)/u', $caches_page, $owned_caches_info);
			$caches_number['YES']=count($owned_caches_info["type"]);
			for($w=0;$w<$caches_number['YES'];$w++)
			{	
				$user_caches[$owned_caches_info["cid"][$w]]['type'] = trim($owned_caches_info["type"][$w]);
				$user_caches[$owned_caches_info["cid"][$w]]['name'] = trim($owned_caches_info["name"][$w]);
				$user_caches[$owned_caches_info["cid"][$w]]['country'] = trim(str_replace('(соавтор)','',$owned_caches_info['country'][$w]));
				$user_caches[$owned_caches_info["cid"][$w]]['region'] = trim($owned_caches_info["region"][$w]);
				$user_caches[$owned_caches_info["cid"][$w]]['date'] = date('Y-m-d',strtotime($owned_caches_info['cdate'][$w]));
				$user_caches[$owned_caches_info["cid"][$w]]['own'] = "YES";
			}
		}
	}

	$_SESSION['caches_number']=$caches_number;
	$_SESSION['user_caches']=$user_caches;
	

	unset ($owned_caches_info);
	unset ($found_caches_info);
}

$_SESSION['loadcaches'] = 1;
$new_url = 'index.php';
header('Location: '.$new_url);
$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## загружен список тайников\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
ob_end_flush();
//echo '<script type="text/javascript">document.location.href = "index.php";</script>';
?>