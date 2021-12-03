<?php 

$query = "SELECT 1 FROM $diploms_table WHERE `gid` = $gid AND `diplom_num` = $diplom_num";
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
if (mysqli_num_rows ($result) == 0)
{
	$query = "INSERT INTO $diploms_table (`uid`, `gid`, `diplom_num`, `diplom_date`, `stars`, `main_cache`, `used_caches`, `gvs_foto`) VALUES ($str)";
	if (!mysqli_query($link, $query)) 
	{
		$diplom_err .= 'Ошибка: ' . mysqli_error($link) . '\n\n';
	}
	else 
	{
		$diplom_ok .= 'Диплом ' . $num_str . ' добавлен в базу.\n\n';
	}
}
else $diplom_err .= 'Ошибка: диплом ' . $num_str . ' уже есть в базе.\n\n'; 

?>