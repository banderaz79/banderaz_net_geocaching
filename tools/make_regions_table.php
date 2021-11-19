<?php

$table='diploms_regions_const';

$uploadfile = "regions.csv";
echo $uploadfile."<br>";
$csv_file = fopen($uploadfile, "r");
$regs = array();
if ($csv_file !== FALSE) 
{
	while (($data = fgetcsv($csv_file, 0, ";")) !== FALSE) 
	{
		$regs[] = $data;
    }
    fclose($csv_file);

}
else
{	
	echo "Не открыть файл<br>";
}


//echo $regs[1][0];
print_r ($regs);


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
			name VARCHAR(200),
			number INT(3),
			around VARCHAR(40),
			spisok VARCHAR(200),
			iskl VARCHAR(40),
			plus VARCHAR(40)
		)";
	$result = mysqli_query($link, $query) or die("Ошибка создания таблицы: " . mysqli_error($link));
	echo "таблица создана"."<br>";

$j = count($regs);
echo $j."<br>";
for($o=0; $o<$j; $o++)
		
	{

			echo $regs[$o][0]."<br>";
			echo $regs[$o][1]."<br>";
			echo $regs[$o][2]."<br>";
			echo $regs[$o][3]."<br>";
			echo $regs[$o][4]."<br>";
			echo $regs[$o][5]."<br>";
			echo $regs[$o][6]."<br><br>";
			
			$query = "INSERT INTO $table (id, name, number, around, spisok, iskl, plus) VALUES ('".$regs[$o][0]."', '".$regs[$o][1]."', '".$regs[$o][2]."', '".$regs[$o][3]."', '".$regs[$o][4]."', '".$regs[$o][5]."', '".$regs[$o][6]."')";
			$result = mysqli_query ($link, $query) or die("Ошибка: " . mysqli_error($link));
	}


?>
