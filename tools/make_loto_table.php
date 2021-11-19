<?php

$table = 'diploms_loto_const';
$uploadfile = "geoloto.csv";
echo $uploadfile."<br>";
$csv_file = fopen($uploadfile, "r");
$loto = array();
if ($csv_file !== FALSE) 
{
	while (($data = fgetcsv($csv_file, 0, ";")) !== FALSE) 
	{
		$loto[] = $data;
    }
    fclose($csv_file);

}
else
{	
	echo "Не открыть файл<br>";
}


//echo $loto[1][0];
print_r ($loto);


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
		$query ="DROP TABLE $table";
		$result = mysqli_query($link, $query) or die("Ошибка удаления таблицы: " . mysqli_error($link));
		echo "таблица удалена"."<br>";
}


	$query ="CREATE TABLE $table
		(
			id INT(2) PRIMARY KEY,
			numbers VARCHAR(200)
		)";
	$result = mysqli_query($link, $query) or die("Ошибка создания таблицы: " . mysqli_error($link));
	echo "таблица создана"."<br>";

$j = count($loto);
echo $j."<br>";
for($o=0; $o<$j; $o++)
		
	{
		echo $loto[$o][0]."<br>";
		echo $loto[$o][1]."<br><br>";
		$query = "INSERT INTO $table (id, numbers) VALUES ('".$loto[$o][0]."', '".$loto[$o][1]."')";
		$result = mysqli_query ($link, $query) or die("Ошибка: " . mysqli_error($link));
	}


?>
