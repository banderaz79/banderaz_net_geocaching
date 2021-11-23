<?php 

$query = "INSERT INTO $extra_table (`uid`, `extra_diplom`, `extra_num`, `extra_date`, `used_gvs`) VALUES ($str)";
if (!mysqli_query($link, $query)) 
{
	$extra_err .= 'Ошибка: ' . mysqli_error($link) . '\n\n';
}
else 
{
	$extra_ok .= 'Дополнительный диплом ' . $extra . ' добавлен в базу.\n\n';
}

?>