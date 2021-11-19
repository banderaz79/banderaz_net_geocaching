<?php
ob_start();
session_start();

if(!$_SESSION['token']) 
{
	$new_url = 'index.php';
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


function getnewapi($target, $tid)
{
	$nonce = md5(uniqid(rand(), true));
	$time = time();

	$base_str = "GET&".urlencode($_SESSION['API_URL'].$target)."&".urlencode("id=".$tid).urlencode("&oauth_consumer_key=".$_SESSION['CONS_KEY']).urlencode("&oauth_nonce=".$nonce).urlencode("&oauth_signature_method=HMAC-SHA1").urlencode("&oauth_timestamp=".$time).urlencode("&oauth_token=".$_SESSION['token']).urlencode("&oauth_version=1.0");

	$key = $_SESSION['CONS_SEC']."&".$_SESSION['secret'];
	$sign = base64_encode(hash_hmac("sha1", $base_str, $key, true));

	$url = $_SESSION['API_URL'].$target.'?id='.$tid.'&oauth_consumer_key='.$_SESSION['CONS_KEY'].'&oauth_nonce='.$nonce.'&oauth_signature='.urlencode($sign).'&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$time.'&oauth_token='.urlencode($_SESSION['token']).'&oauth_version=1.0';
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['cookiefile']);
	$json = curl_exec($ch);
	$data = json_decode($json, true);
	curl_close($ch);
	
	return $data;
}
?>