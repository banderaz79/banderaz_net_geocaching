<?php 
$query = "SELECT 1 FROM $extra_table WHERE `uid` = $uid AND `extra_diplom` = $extra_diplom";
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));
if (mysqli_num_rows ($result) == 0)
{

	$query = "INSERT INTO $extra_table (`uid`, `extra_diplom`, `extra_num`, `extra_date`, `used_gvs`) VALUES ($str)";
	if (!mysqli_query($link, $query)) 
	{
		$extra_err .= 'Ошибка: ' . mysqli_error($link) . '\n\n';
	}
	else 
	{
		$extra_ok .= 'Дополнительный диплом ' . $extra_diplom . '-й степени №' . $extra_num . ' добавлен в базу.\n\n';
	}
}
else $extra_err .= 'Ошибка: диплом ' . $extra_diplom . '-й степени уже добавлен у игрока.\n\n';

?>