<?php 

$query = "UPDATE $users_table SET `stars` = $stars WHERE `uid` = $uid";
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));

?>