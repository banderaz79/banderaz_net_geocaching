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


//$loadimages = $_POST['loadimages'];
$loadnotes = $_POST['loadnotes'];

//print ($loadnotes);

$file_type = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);
$file_name = pathinfo($_FILES['uploadfile']['name'], PATHINFO_BASENAME);

if($file_type != "wpt") {
	echo '<script type="text/javascript">alert("Можно загружать только .WPT файл");</script>';
	echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
	//if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
	die("error");
}
unset ($file_type);

if($_FILES['uploadfile']['error'] > 0) {
	echo '<script type="text/javascript">alert("Что-то пошло не так...");</script>';
	echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
	//if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');
	die("error");
}

$uploadfile = $_SESSION['TMPDIR']."/".$_SESSION['userid']."_cfl_";
$num = rand(10,1000);
while (file_exists($uploadfile.$num)) $num++;
$uploadfile .= $num;
move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfile);


//извлекаем id тайников из файла
$wpt = file($uploadfile);
foreach ($wpt as $k => $v) {
	if (strpos($v,","))	{
		$i = explode (",", $v);
		if (!strpos($i[1],".")) $cache_ids[] = substr($i[1], 2);
	}
}

//$cache_ids = array(4673);

if(!is_array($cache_ids)) {
	echo '<script type="text/javascript">alert("В файле нет точек");</script>';
	echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
	die("error");
}

//if ( !(@unlink($uploadfile)) ) die('Ошибка при удалении временного файла');

unset ($wpt); unset ($i); unset ($uploadfile);

$check = 1;
require "../getnewapi.php";
require "../getoldapi.php";
require "dict.php";


header("Cache-control: private");
header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=\"" . mb_substr($file_name,0,-4) . "_cfl.gpx" . "\"");
header("Content-Description: Waypoints");

$time = date("Y-m-d\TH:i:s");


print <<<HTML
<?xml version="1.0" encoding="utf-8"?>
<gpx version="1.0" creator="Anton Shumilov aka banderaz http://banderaz.net/" xmlns="http://www.topografix.com/GPX/1/0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:locus="http://www.locusmap.eu" xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd http://www.groundspeak.com/cache/1/0 http://www.groundspeak.com/cache/1/0/cache.xsd">
	<name>Cache Listing Generated from Geocaching.su</name>
	<desc>This is a cache list generated from Geocaching.su</desc>
	<email>org@geocaching.ru</email>
	<url>https://geocaching.su</url>
	<urlname>Geocaching - Treasure Hunting</urlname>
	<time>{$time}</time>
	<keywords>cache, geocache</keywords>\r\n
HTML;

foreach ($cache_ids as $kk=>$vv)
{
	$new_api = getnewapi("geocache.php", $vv);
	
	if ($new_api['status']['code'] == "OK") {
		($loadnotes>20) ? $s = '&istr="fgn"' : $s = '&istr="fg"';
		$old_api = getoldapi(2, $vv, $s);
		
		$cacheid = $new_api['data']['id'];
		$cachetypeid = $new_api['data']['type'];
		$wptlat = $new_api['data']['latitude'];
		$wptlon = $new_api['data']['longitude'];
		$wpttime = $new_api['data']['dateHidden'];
		$wptname = (((array_key_exists($new_api['data']['type'], $ctype)) ? $ctype[$cachetypeid][0] : $ctype[1979][0]) . $cacheid);
		$cachename = htmlspecialchars($new_api['data']['name']);
		$wpttype = ((array_key_exists($new_api['data']['type'], $ctype)) ? $ctype[$cachetypeid][1] : $ctype[1979][1]);
		$cacheavail = (($new_api['data']['status2String'] == "Active") ? "True" : "False");
		$cachearch = (($new_api['data']['status']==2) ? "True" : "False");
		$cacheauthor = htmlspecialchars($new_api['data']['author']['name']);
		$cacheownerid = $new_api['data']['author']['id'];
		$cachesize = $new_api['data']['size'];
		$cacheattr = "";
		foreach($new_api['data']['attributes']as $k=>$v) $cacheattr .= '				<groundspeak:attribute id="' . $cattr[$v['id']][1] . '" inc="' . $cattr[$v['id']][2] . '">' . $cattr[$v['id']][0] . '</groundspeak:attribute>'."\r\n";
		$new_api['data']['status2String'] !== "Active" ? $cacheattr .= '				<groundspeak:attribute id="' . $cattr[$new_api['data']['status2String']][1] . '" inc="' . $cattr[$new_api['data']['status2String']][2] . '">' . $cattr[$new_api['data']['status2String']][0] . '</groundspeak:attribute>'."\r\n" : "False";
		$cachediff = $new_api['data']['difficulty'];
		$cachearea = $new_api['data']['area'];
		$cachefav = $new_api['data']['rating'];
		$typecolor = $ctype[$new_api['data']['type']][4];
		$cachevotes = ' по  ' . $new_api['data']['votes'] . ' голосам';
		$cacherec = $new_api['data']['recommendations'];
		$class=array();
		foreach(explode(",", $new_api['data']['class']) as $k) array_key_exists($k, $cclass) ? $class[] = $cclass[$k][0] : $class[] = $cclass[1979][0];
		$cc = implode(", ", $class);
		$cachedesc = $new_api['data']['description']['cache'];
		$cachetpart = $new_api['data']['description']['traditionalPart'];
		$cachevpart = $new_api['data']['description']['virtualPart'];
		$cacheareadesc = $new_api['data']['description']['area'];
		$cachecontainer = $new_api['data']['description']['container'];
		$cachefoto = (htmlspecialchars('<p style="color: green; font-size: 50%;"><b>ФОТО ТАЙНИКА ОТСУТСТВУЕТ</b></p>'));
		$cachefotos = "";
		$areafoto=htmlspecialchars('<p style="color: green; font-size: 50%;"><b>ФОТО МЕСТНОСТИ ОТСУТСТВУЕТ</b></p>');
		$areafotos = "";
		foreach ($new_api['data']['images'] as $k=>$v) {
			if (in_array("cachePhoto",$v)) {
				$cachefoto = (htmlspecialchars('<p style="color: green; font-size: 100%;"><b>ФОТО ТАЙНИКА</b></p>'));
				$cachefotos = (htmlspecialchars('<p font-size: 50%;"><a href="' . $v['url'] . '"><img width="100%" alt="Фото тайника отсутствует" src="' . $v['url'] . '"></a>'));
			}
			if(in_array("areaPhoto", $v)) {
				$areafoto=htmlspecialchars('<p style="color: green; font-size: 100%;"><b>ФОТО МЕСТНОСТИ</b></p>');
				$areafotos .= (htmlspecialchars('<p font-size: 50%;"><b>' . $v['description'] . '</b><br /><a href="' . $v['url'] . '"><img width="100%" alt="Фото местности отсутствует" src="' . $v['url'] . '"></a></p>'));
			}
		}
		$cachewpts = "";
		if($new_api['data']['waypoints'][0])	{
			foreach ($new_api['data']['waypoints'] as $k => $v) {
				$cachewpts .= ('	<wpt lat="' . $v['lat'] . '" lon="' . $v['lon'] . '">'."\r\n");
				$cachewpts .= ('		<name>' . $wptname . ': ' . htmlspecialchars($v['name']) . '</name>'."\r\n");
				$cachewpts .= ('		<desc>' . htmlspecialchars($v['name']) . '</desc>'."\r\n");
				$cachewpts .= ('		<cmt>' . htmlspecialchars($v['text']) . '</cmt>'."\r\n");
				$cachewpts .= ('		<sym>' . $wptypes[$v['type']][1] . '</sym>'."\r\n");
				$cachewpts .= ('		<type>Waypoint|' . $wptypes[$v['type']][1] . '</type>'."\r\n");
				$cachewpts .= ("	</wpt>\r\n");
			}
		}
	}
	
	else {
		$s = '&istr="abcdefghijklmnopqrstuvwxyz"';
		
		$old_api = getoldapi(2, $vv, $s);
		
		$cacheid = $old_api['id'];
		$wptlat = $old_api['lat'];
		$wptlon = $old_api['lng'];
		$wpttime = $old_api['date'];
		foreach ($ctype as $k=>$v) {
			if(array_search($old_api['type'],$v)) {
				$wptname = $ctype[$k][0] . $cacheid;
				$cachetypeid = $k;
			}
		}
		$cachename = htmlspecialchars($old_api['name']);
		$wpttype = $ctype[$cachetypeid][1];
		$cacheavail = "True";
		$cachearch = (($old_api['status'] == "На сайте") ? "False" : "True");
		$cacheauthor = htmlspecialchars($old_api['nick']);
		$cacheownerid = "";
		$cachesize = $old_api['size'];
		$cacheattr = "";
		$cachediff = $old_api['cache_value'];
		$cachearea = $old_api['area_value'];
		$cachefav = $old_api['rating'];
		$typecolor = $ctype[$cachetypeid][4];
		$cachevotes = '';
		$cacherec = $old_api['recom'];
		$cc = $old_api['cclass'];
		$cachedesc = $old_api['cdesc'];
		$cachetpart = $old_api['tpart'];
		$cachevpart = $old_api['vpart'];
		if(!is_array($old_api['images']['image'])) {
			stristr($old_api['images']['img'][0],"caches") ? $cachefoto = (htmlspecialchars('<p style="color: green; font-size: 100%;"><b>ФОТО ТАЙНИКА</b></p><a href="' . $old_api['images']['img'][0] . '"><img width="100%" alt="Фото тайника отсутствует" src="' . $caches_['images']['img'][0] . '"></a>')) : $cachefoto = (htmlspecialchars('<p style="color: green; font-size: 50%;"><b>ФОТО ТАЙНИКА ОТСУТСТВУЕТ</b></p>'));
		}
		stristr($old_api['images']['img'][0],"caches") ? $cachefoto = (htmlspecialchars('<p style="color: green; font-size: 100%;"><b>ФОТО ТАЙНИКА</b></p><a href="' . $old_api['images']['img'][0] . '"><img width="100%" alt="Фото тайника отсутствует" src="' . $caches_['images']['img'][0] . '"></a>')) : $cachefoto = (htmlspecialchars('<p style="color: green; font-size: 50%;"><b>ФОТО ТАЙНИКА ОТСУТСТВУЕТ</b></p>'));
		$cacheareadesc = $old_api['adesc'];
		$cachecontainer = $old_api['container'];
		$cachefoto = htmlspecialchars('<p style="color: green; font-size: 50%;"><b>ФОТО ТАЙНИКА ОТСУТСТВУЕТ</b></p>');
		$cachefotos = "";
		$areafoto=htmlspecialchars('<p style="color: green; font-size: 50%;"><b>ФОТО МЕСТНОСТИ ОТСУТСТВУЕТ</b></p>');
		$areafotos = "";
		if ($old_api['images']['img'] AND !is_array($old_api['images']['img'])) {
			if(stristr($old_api['images']['img'],"caches")) {
				$cachefoto = htmlspecialchars('<p style="color: green; font-size: 100%;"><b>ФОТО ТАЙНИКА</b></p>');
				$cachefotos = htmlspecialchars('<p><a href="' . $old_api['images']['img'] . '"><img width="100%" alt="Фото тайника отсутствует" src="' . $old_api['images']['img'] . '"></a></p>');
			}
			if(stristr($old_api['images']['img'],"areas")) {
				$areafoto=htmlspecialchars('<p style="color: green; font-size: 100%;"><b>ФОТО МЕСТНОСТИ</b></p>');
				$areafotos .= htmlspecialchars('<p><a href="' . $old_api['images']['img'] . '"><img width="100%" alt="Фото местности отсутствует" src="' . $old_api['images']['img'] . '"></a></p>');
			}
		}
		if($old_api['images']['img'] AND is_array($old_api['images']['img'])) {
			foreach ($old_api['images']['img'] as $k=>$v) {
				if(stristr($v,"caches")) {
					$cachefoto = htmlspecialchars('<p style="color: green; font-size: 100%;"><b>ФОТО ТАЙНИКА</b></p>');
					$cachefotos = htmlspecialchars('<p><a href="' . $v . '"><img width="100%" alt="Фото тайника отсутствует" src="' . $v . '"></a></p>');
				}
				if(stristr($v,"areas")) {
					$areafoto=htmlspecialchars('<p style="color: green; font-size: 100%;"><b>ФОТО МЕСТНОСТИ</b></p>');
					$areafotos .= htmlspecialchars('<p><a href="' . $v . '"><img width="100%" alt="Фото местности отсутствует" src="' . $v . '"></a></p>');
				}
			}
		}
		
		$cachewpts = "";
		if($old_api['waypoints'])	{
			if ($old_api['waypoints']['waypoint'][0]) {
				foreach ($old_api['waypoints']['waypoint'] as $k => $v) {
					$cachewpts .= ('	<wpt lat="' . $v['@attributes']['lat'] . '" lon="' . $v['@attributes']['lon'] . '">'."\r\n");
					$cachewpts .= ('		<name>' . $wptname . ': ' . htmlspecialchars($v['@attributes']['name']) . '</name>'."\r\n");
					$cachewpts .= ('		<desc>' . htmlspecialchars($v['@attributes']['name']) . '</desc>'."\r\n");
					$cachewpts .= ('		<cmt>' . htmlspecialchars($v['@content']) . '</cmt>'."\r\n");
					$cachewpts .= ('		<sym>' . $wptypes[$v['@attributes']['type']][1] . '</sym>'."\r\n");
					$cachewpts .= ('		<type>Waypoint|' . $wptypes[$v['@attributes']['type']][1] . '</type>'."\r\n");
					$cachewpts .= ("	</wpt>\r\n");
				}
			}
			else {
				$v = $old_api['waypoints']['waypoint'];
				$cachewpts .= ('	<wpt lat="' . $v['@attributes']['lat'] . '" lon="' . $v['@attributes']['lon'] . '">'."\r\n");
				$cachewpts .= ('		<name>' . $wptname . ': ' . htmlspecialchars($v['@attributes']['name']) . '</name>'."\r\n");
				$cachewpts .= ('		<desc>' . htmlspecialchars($v['@attributes']['name']) . '</desc>'."\r\n");
				$cachewpts .= ('		<cmt>' . htmlspecialchars($v['@content']) . '</cmt>'."\r\n");
				$cachewpts .= ('		<sym>' . $wptypes[$v['@attributes']['type']][1] . '</sym>'."\r\n");
				$cachewpts .= ('		<type>Waypoint|' . $wptypes[$v['@attributes']['type']][1] . '</type>'."\r\n");
				$cachewpts .= ("	</wpt>\r\n");
			}
		}
	}
	
/*	echo "<pre>";
	print_r ($new_api);
	echo "</pre>";
	echo "<pre>";
	print_r ($old_api);
	echo "</pre>";*/

	
	print ("	<wpt lat=\"" . $wptlat . "\" lon=\"" . $wptlon . "\">\r\n");
	print ("		<time>" . $wpttime . "T00:00:00</time>\r\n");
	print ("  		<name>" . $wptname . "</name>\r\n");
	print ("		<desc>" . $cachename . "</desc>\r\n");
	print ("		<url>https://geocaching.su/?pn=101&amp;cid=" . $cacheid . "</url>\r\n");
	print ("		<urlname>" . $cachename . "</urlname>\r\n");
	print ("		<sym>Geocache</sym>\r\n");
	print ("		<type>Geocache|" . $wpttype . "</type>\r\n");
	print ("		<groundspeak:cache id=\"" . $cacheid . "\" available=\"" . $cacheavail . "\" archived=\"" . $cachearch ."\" xmlns:groundspeak=\"http://www.groundspeak.com/cache/1/0/1\">\r\n");
	print ("			<groundspeak:name>" . $cachename . "</groundspeak:name>\r\n");
	print ("			<groundspeak:placed_by>" . $cacheauthor . "</groundspeak:placed_by>\r\n");
	///* РАЗОБРАТЬСЯ С PID И UID */
	print ("			<groundspeak:owner id=\"" . $cacheownerid . "\">" . $cacheauthor . "</groundspeak:owner>\r\n"); 
	print ("			<groundspeak:type>" . $wpttype . "</groundspeak:type>\r\n");
	print ("   			<groundspeak:container>" . ((array_key_exists($cachesize, $csize)) ? $csize[$cachesize][1] : "") . "</groundspeak:container>\r\n");
	print ("   			<groundspeak:attributes>\r\n");
	print ($cacheattr);
	print ("   			</groundspeak:attributes>\r\n");
	print ("			<groundspeak:difficulty>" . $cachediff . "</groundspeak:difficulty>\r\n");
	print ("			<groundspeak:terrain>" . $cachearea . "</groundspeak:terrain>\r\n");
	print ("			<groundspeak:favorite_points>" . $cachefav . "</groundspeak:favorite_points>\r\n");
	///*  КАК ДОБАВИТЬ СТРАНУ? */
	print ("			<groundspeak:country></groundspeak:country>\r\n"); 
	///*  КАК ДОБАВИТЬ ОБЛАСТЬ И ГОРОД? */
	print ("			<groundspeak:state></groundspeak:state>\r\n"); 
	print ("			<groundspeak:short_description html=\"True\">"); 
	print (htmlspecialchars('<p>Автор: <span style="color: darkblue;"><b>' . $cacheauthor . '</b></span><br />Соавтор(ы): <span style="color: darkblue;">' . (is_array($old_api['coautors']) ? $ccoautors=implode(",", $old_api['coautors']) : $ccoautors=$old_api['coautors']) . '</span><br />Компаньон(ы): <span style="color: dark_grey; font-size: 0.85em;">' . (is_array($old_api['company']) ? $ccompany=implode(",", $old_api['company']) : $ccompany=$old_api['company']) . '</span></p><p>Тип: <span style="color: ' . $typecolor . ';"><b>' . (array_key_exists($cachetypeid, $ctype) ? $ctype[$cachetypeid][3] : $ctype[1979][3]) . '</b></span><br />Класс: <span style="font-size: 0.85em;">' . $cc . '</span><br /></p><p>Размер: ' . ((array_key_exists($new_api['data']['size'], $csize)) ? $csize[$new_api['data']['size']][0] : "Неизвестно") . '</p><p>Сложность: ' . $cachediff . '<br />Местность: ' . $cachearea . '</p><p>Рейтинг: ' . $cachefav . $cachevotes . '<br />Рекомендован: ' . $cacherec . '</p>'));	
	print ("\r\n");
	print ("			</groundspeak:short_description>\r\n");
	///* ЗИМНИЙ ПЕРИОД? */
	print ("			<groundspeak:long_description html=\"True\">");
	
	if($cachearch=="True") print(htmlspecialchars('<img src="https://geocaching.su/images/ctypes/icons/arc.png"><span style="color: black; font-size: 120%;"><b>ТАЙНИК В АРХИВЕ</b></span><br>'));
	
	if (array_key_exists($new_api['data']['status2String'], $cattr)) print(htmlspecialchars('<img src="' . $cattr[$new_api['data']['status2String']][3] . '" ><span style="color: ' . $cattr[$new_api['data']['status2String']][4] . '; font-size: 120%;"><b>' . $cattr[$new_api['data']['status2String']][0] . '</b></span><br>'));
	
	print(htmlspecialchars('<p style="color: ' . $typecolor . '; font-size: 120%;"><b>' . $wptname . ' ' . ((array_key_exists($cachetypeid, $ctype)) ? $ctype[$cachetypeid][3] : $ctype[1979][3]) . '</b></p>'));
	$cachedesc ? print(htmlspecialchars('<p style="color: darkblue; font-size: 120%;"><b>ОПИСАНИЕ ТАЙНИКА</b></p>' . $cachedesc)) : null;
	$cachetpart ? print(htmlspecialchars('<p style="color: darkblue;"><b>Традиционная часть тайника</b></p>' . $cachetpart)) : null;
	$cachevpart ? print(htmlspecialchars('<p style="color: darkblue;"><b>Виртуальная часть тайника на зимний период</b></p>' . $cachevpart)) : null;
	print ($cachefoto);
	print ($cachefotos);
	$cacheareadesc ? print(htmlspecialchars('<p style="color: darkblue; font-size: 120%;"><b>ОПИСАНИЕ МЕСТНОСТИ</b></p>' . $cacheareadesc)) : null;
	$cachecontainer ? print(htmlspecialchars('<p style="color: darkblue;"><b>Содержимое тайника</b></p>' . $cachecontainer)) : null;
	print ($areafoto);
	print ($areafotos);
	print ("\r\n");
	print ("			</groundspeak:long_description>\r\n");
	print ("			<groundspeak:personal_note>" . htmlspecialchars($new_api['data']['personal_note']) . "</groundspeak:personal_note>\r\n");
	print ("			<groundspeak:logs>\r\n");
	
	if($loadnotes != 0) {
		if ($new_api['status']['code'] == "OK" AND $loadnotes > 0 AND $loadnotes < 21) {
			foreach ($new_api['data']['logs'] as $k=>$v) {
				print ("				<groundspeak:log>\r\n");
				print ("					<groundspeak:date>" . str_replace(" ","T",$v['date']) . "</groundspeak:date>\r\n");
				print ("					<groundspeak:type>" . $notetypes[$v['type']] . "</groundspeak:type>\r\n");
				print ("					<groundspeak:finder>" . htmlspecialchars($v['author']['name']) . "</groundspeak:finder>\r\n");
				print ("					<groundspeak:text>" . htmlspecialchars($v['text']) . "</groundspeak:text>\r\n");
				print ("				</groundspeak:log>\r\n");
				//if ($k == $loadnotes-1) break;
			}
		}
		else {
			if ($old_api['notes']) {
				if ($old_api['notes']['note']['@attributes']) {
					print ("				<groundspeak:log>\r\n");
					print ("					<groundspeak:date>" . str_replace(" ","T",$old_api['notes']['note']['@attributes']['date']) . "</groundspeak:date>\r\n");
					print ("					<groundspeak:type>" . $notetypes[$old_api['notes']['note']['@attributes']['status']] . "</groundspeak:type>\r\n");
					print ("					<groundspeak:finder>" . htmlspecialchars($old_api['notes']['note']['@attributes']['nick']) . "</groundspeak:finder>\r\n");
					print ("					<groundspeak:text>" . htmlspecialchars($old_api['notes']['note']['@content']) . "</groundspeak:text>\r\n");
					print ("				</groundspeak:log>\r\n");
				}
				else {
					foreach ($old_api['notes']['note'] as $k => $v) {
						print ("				<groundspeak:log>\r\n");
						print ("					<groundspeak:date>" . str_replace(" ","T",$v['@attributes']['date']) . "</groundspeak:date>\r\n");
						print ("					<groundspeak:type>" . $notetypes[$v['@attributes']['status']] . "</groundspeak:type>\r\n");
						print ("					<groundspeak:finder>" . htmlspecialchars($v['@attributes']['nick']) . "</groundspeak:finder>\r\n");
						print ("					<groundspeak:text>" . htmlspecialchars($v['@content']) . "</groundspeak:text>\r\n");
						print ("				</groundspeak:log>\r\n");
						if ($k == $loadnotes-1) break;
					}
				}
			}
		}
	}
	print ("			</groundspeak:logs>\r\n");
	print ("		</groundspeak:cache>\r\n");
	print ("	</wpt>\r\n");
	print ($cachewpts);
	
}

print ("</gpx>\r\n");


$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## скрипт отработал\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

?>