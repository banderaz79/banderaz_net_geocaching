<?php
$uid=29695;
$cid=17525;

$url = 'https://geocaching.su/showmemphotos.php?cid=' . $cid;
include('../../curlinit.php');
$photo_page = curl_exec($ch);
$regexp = '/<tr><th>Фото от.+profile\.php\?uid=42220.+\n(<a class="tip" href="\/photos\/albums\/(?P<photo>[\d]+?)\.jpg.+<\/a>\n<div.+<\/div>\n)+<\/td><\/tr>/';
preg_match_all($regexp, $photo_page, $user_photos);

echo '<pre>';
print_r($user_photos); 
echo '</pre>';


echo '<pre>';
print_r($photo_page); 
echo '</pre>';
?>