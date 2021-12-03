<?php
/*ob_start();
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
ob_end_flush();*/

require "Classes/xmlstr_to_array.php";

function  oldapicurl($url)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['cookiefile']);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,30); 
	curl_setopt($ch,CURLOPT_USERAGENT,'Bot 1.0');
	$xml = curl_exec($ch);
	$data = xmlstr_to_array($xml);
	//$data = simplexml_load_string($xml);
	curl_close($ch);
	return $data;
}


function getoldapi($rtype, $cid, $istr)
{
	$url = 'https://geocaching.su/site/api.php?rtype='.$rtype.'&cid='.$cid.$istr;
	$data = oldapicurl($url);
	return $data;
}


function getoldapiuser()
{
	$url = 'https://geocaching.su/site/api.php?rtype=8';
	$data = oldapicurl($url);
	return $data;
}
//$array = json_decode(json_encode((array) simplexml_load_string("<response>{$xml}</response>")), true);

?>