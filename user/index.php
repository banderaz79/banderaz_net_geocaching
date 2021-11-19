<?php 
require "../getoldapi.php";
//$data = getoldapiuser();
$url = 'https://geocaching.su/site/api.php?rtype=8';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIEFILE, $_SESSION['cookiefile']);
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
curl_close($ch);
print_r($data);

require '../getnewapi.php';
$data=getnewapi("profile.php", "");
print_r($data);

?>