<?php 
ob_start();
session_start(); 

if(!$_SESSION['geo_cat']) 
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
$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
if($_SESSION['token']) 
{
	$new_url = 'index.php';
	header('Location: '.$new_url);
	$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## перенаправлен на main.php\n";
	file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
	ob_end_flush();
	exit;
}
ob_end_flush();


//начальные параметры авторизации
$_SESSION['REQ_URL'] = 'https://geocaching.su/api/oauth/request_token.php';
$_SERVER['SERVER_NAME'] == "banderaz.net" ? $_SESSION['AUTH_URL'] = 'https://banderaz.net/api/oauth/authorize.php' : $_SESSION['AUTH_URL'] = 'https://geocaching.su/api/oauth/authorize.php';
$_SESSION['ACC_URL'] = 'https://geocaching.su/api/oauth/access_token.php';
$_SESSION['API_URL'] = 'https://geocaching.su/api/';
$_SESSION['BACK_URL'] = $_SESSION['geo_cat'] . 'oauth_back.php'; 
$_SESSION['CONS_KEY'] = 'fba252bc1ff4174b607b0ee4981022c005836daca';
$_SESSION['CONS_SEC'] = 'f44b5b77bf4cf15daadff4823968f27f';

//$_SESSION['LOG'] .= "1. Получение токена на авторизацию. Данные.<br>Date: ".$today."<br><br>Consumer_key: ".$_SESSION['CONS_KEY']."<br>Consumer_secret: ".$_SESSION['CONS_SEC']."<br>";

//составление первоначального запроса
$time = time();
$nonce = md5(uniqid(rand(), true));

$base_str = "GET&";
$base_str .= urlencode($_SESSION['REQ_URL'])."&";
$base_str .= urlencode("oauth_callback=".urlencode($_SESSION['BACK_URL'])."&");
$base_str .= urlencode("oauth_consumer_key=".$_SESSION['CONS_KEY']."&");
$base_str .= urlencode("oauth_nonce=".$nonce."&");
$base_str .= urlencode("oauth_signature_method=HMAC-SHA1&");
$base_str .= urlencode("oauth_timestamp=".$time."&");
$base_str .= urlencode("oauth_version=1.0");

$key = $_SESSION['CONS_SEC']."&";
$sign = base64_encode(hash_hmac("sha1", $base_str, $key, true));

$url = $_SESSION['REQ_URL']."?";
$url .= 'oauth_callback='.urlencode($_SESSION['BACK_URL']);
$url .= '&oauth_consumer_key='.$_SESSION['CONS_KEY'];
$url .= '&oauth_nonce='.$nonce;
$url .= '&oauth_signature='.urlencode($sign);
$url .= '&oauth_signature_method=HMAC-SHA1';
$url .= '&oauth_timestamp='.$time;
$url .= '&oauth_version=1.0';

//$_SESSION['LOG'] .= "Timestamp: ".$time."<br>Nonce: ".$nonce."<br>Base_string: ".$base_str."<br>Key: ".$key."<br>Signature: ".$sign."<br>URL: ".$url."<br>";

//запрос и получение ключей от сервера
$response = file_get_contents($url);
parse_str($response, $result);

$_SESSION['oauth_token'] = $result['oauth_token'];
$_SESSION['oauth_token_secret'] = $result['oauth_token_secret'];
$_SESSION['res1'] = $result;

//$_SESSION['LOG'] .= "Результат:<br>oauth_token: ".$_SESSION['oauth_token']."<br>oauth_token_secret: ".$_SESSION['oauth_token_secret']."<br><br>";

//перенаправление пользователя на авторизацию
$url = $_SESSION['AUTH_URL'];
$url .= '?oauth_token='.$_SESSION['oauth_token'];

//$_SESSION['LOG'] .= "2. Авторизация<br>Auth_url: ".$url;

//после авторизации перенаправляется на oauth_back.php

$ifr = 1;
if (strpos($_SERVER['HTTP_USER_AGENT'], "Chrome") !== FALSE)
{
	$browsers = array("YaBrowser", "OPR");
	$ifr = 0;
	foreach ($browsers as $browser)
	{
		if(strpos($_SERVER['HTTP_USER_AGENT'], $browser) !== FALSE) $ifr ++; 
	}
}
if($ifr == 0)
{
	$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## перенаправлен на страницу логина на сайте ## " . $_SERVER['HTTP_USER_AGENT'] . "\n";
	echo '<script type="text/javascript">window.top.location.href = "' . $url . '";</script>';
}
else
{
	echo '
	<html>
		<head>
			<meta charset="utf-8">
			<meta name="description" content="Полезные инструменты "> 
			<title>Полезные инструменты для геокешеров geocaching.su</title>
			<link rel="stylesheet" type="text/css" href="css/style.css" />
		</head>
		<body>
		<div class="main" >
			<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="media/gc.png" /></a></p></div>
			<div>
				Авторизуйтесь на сайте geocaching.su
			</div>
			<div>
				<center><br><iframe style="border: none;" src="' . $url . '" scrolling="no"></iframe></center>
			</div>
			<div>
				Для авторизации используется API геокешинга, пароли и логины не сохраняются на данном сервере.
			</div>
			<table class="nav">
				<tr>
					<td></td>
					<td class="nav2"><a href="" target="blank">Помощь</a></td>
				</tr>
			</table>
		</div>
		</body>
	</html>
	';
	$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## логин через iframe ## " . $_SERVER['HTTP_USER_AGENT'] . "\n";
}
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
?>


