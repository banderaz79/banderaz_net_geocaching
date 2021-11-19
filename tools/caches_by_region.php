<?php
$link = mysqli_connect('localhost','root','','diploms');
if (!$link) 
{
	printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
	exit;
}
$userid = 111161; // 29695-banderaz 111161-Xanthippe 127896-Белочка Чернобыльская
$regions = 47;

$regions_table = 'diploms_regions_const';
$temp_table = "diploms_".$userid;


	
$query = "SELECT * FROM $regions_table WHERE id = $regions";
$result = mysqli_query ($link, $query) or die("2Ошибка: " . mysqli_error($link));
$object = mysqli_fetch_object($result);
$reg_name = $object->name;

mysqli_free_result($result);


$query = "SELECT * FROM $temp_table WHERE region = '$reg_name' ORDER BY date DESC";
$result = mysqli_query ($link, $query) or die("4Ошибка: " . mysqli_error($link));
while ($arr = mysqli_fetch_assoc($result))
{
	$suite_caches_main[] = $arr;
}

echo "<br>";
$sc_count = count ($suite_caches_main);

echo "<b>$reg_name:</b>"."<br><br>";

//echo "<br>";
//echo "<table>";

for ($o=0; $o<$sc_count; $o++)
{
	//echo "<tr><td><a href=".'"http://www.geocaching.su/?pn=101&cid='.$suite_caches_main[$o][cid].'" target="blank">'.$suite_caches_main[$o][name]."</a></td><td>[".$suite_caches_main[$o][type]."/".$suite_caches_main[$o][cid]."]</td></tr>";
	echo "<a href=".'"http://www.geocaching.su/?pn=101&cid='.$suite_caches_main[$o][cid].'" target="blank">'.$suite_caches_main[$o][name]."</a> [".$suite_caches_main[$o][type]."/".$suite_caches_main[$o][cid]."]<br>";
}

//echo "</table>";

unset ($suite_caches_main);

mysqli_close($link);
?>