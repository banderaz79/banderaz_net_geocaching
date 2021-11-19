<?php
ob_start();
session_start(); 
if(!$_SESSION['oauth_token']) 
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

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

//подготовка запроса на получение пары oauth_token и oauth_token_secret для доступа клиента  к ресурсам сервера
	
$nonce = md5(uniqid(rand(), true));
$time = time();

if($verifier = $_GET['oauth_verifier']) {

	$base_str = "GET&";
	$base_str .= urlencode($_SESSION['ACC_URL'])."&";
	$base_str .= urlencode("oauth_consumer_key=".$_SESSION['CONS_KEY']."&");
	$base_str .= urlencode("oauth_nonce=".$nonce."&");
	$base_str .= urlencode("oauth_signature_method=HMAC-SHA1&");
	$base_str .= urlencode("oauth_timestamp=".$time."&");
	$base_str .= urlencode("oauth_token=".$_SESSION['oauth_token']."&");
	$base_str .= urlencode("oauth_verifier=".$verifier."&");
	$base_str .= urlencode("oauth_version=1.0");

	$key = $_SESSION['CONS_SEC']."&".$_SESSION['oauth_token_secret'];
	$sign = base64_encode(hash_hmac("sha1", $base_str, $key, true));

	$url = $_SESSION['ACC_URL']."?";
	$url .= 'oauth_consumer_key='.$_SESSION['CONS_KEY'];
	$url .= '&oauth_nonce='.$nonce;
	$url .= '&oauth_signature='.urlencode($sign);
	$url .= '&oauth_signature_method=HMAC-SHA1';
	$url .= '&oauth_timestamp='.$time;
	$url .= '&oauth_token='.urlencode($_SESSION['oauth_token']);
	$url .= '&oauth_verifier='.urlencode($verifier);
	$url .= '&oauth_version=1.0';


	//запрос и получение пары oauth_token и oauth_token_secret для доступа клиента  к ресурсам сервера

	$response = file_get_contents($url);
	parse_str($response, $result);

	$_SESSION['token'] = $result['oauth_token'];
	$_SESSION['secret'] = $result['oauth_token_secret'];



	$check = 1;
	require 'getnewapi.php';
	$data=getnewapi("profile.php", "");

	$_SESSION['username']=$data['data']['name'];
	$_SESSION['userid']=$data['data']['id'];
	$_SESSION['foundcaches']=$data['data']['foundCaches'];
	$_SESSION['hiddencaches']=$data['data']['hiddenCaches'];

}


$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## перенаправлен на index.php\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';


?>

