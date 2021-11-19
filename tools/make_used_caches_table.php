<?php
$userid=29695; //29695 111161
$table="diploms_gvs_".$userid."_usedcaches";
$uploadfile = "gvs_caches.csv";
echo $uploadfile."<br>";
$csv_file = fopen($uploadfile, "r");
$caches = array();
if ($csv_file !== FALSE) 
{
	while (($data = fgetcsv($csv_file, 0, ";")) !== FALSE) 
	{
		$caches[] = $data;
    }
    fclose($csv_file);

}
else
{	
	echo "Не открыть файл<br>";
}


//echo $caches[1][0];
print_r ($caches);
echo "<br>";


$link = mysqli_connect('localhost','root','','diploms');
if (!$link) 
{
	printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
	exit;
}

$query="SHOW TABLES LIKE '$table'";
$result = mysqli_query($link, $query) or die('error query' . mysqli_error($link));
if (mysqli_num_rows($result))	
{	
		$query ="DROP TABLE `$table`";
		$result = mysqli_query($link, $query) or die("Ошибка удаления таблицы: " . mysqli_error($link));
		echo "таблица удалена"."<br>";
}


	$query ="CREATE TABLE `$table`
		(
			id INT(2) PRIMARY KEY
		)";
	$result = mysqli_query($link, $query) or die("Ошибка создания таблицы: " . mysqli_error($link));
	echo "таблица создана"."<br>";

$j = count($caches);
echo $j."<br>";
for($o=0; $o<$j; $o++)
		
	{
		$query = "INSERT INTO `$table` (id) VALUES ('".$caches[$o][0]."')";
		$result = mysqli_query ($link, $query) or die("Ошибка: " . mysqli_error($link));
	}


?>
