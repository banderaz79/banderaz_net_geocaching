<?php 

$query = "INSERT INTO $diploms_table (`uid`, `gid`, `diplom_num`, `diplom_date`, `stars`, `used_caches`, `gvs_foto`) VALUES ($str)";
if (!mysqli_query($link, $query)) 
{
	$diplom_err .= 'Ошибка: ' . mysqli_error($link) . '\n\n';
}
else 
{
	$diplom_ok .= 'Диплом ' . $diplom_num . ' добавлен в базу.\n\n';
}

?>