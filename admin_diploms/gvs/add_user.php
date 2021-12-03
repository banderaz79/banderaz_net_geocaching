<?php
$query = sprintf("INSERT INTO $users_table (`uid`,`uname`,`stars`) VALUES (%u,'%s',%u)", $uid, "$uname", $stars);

if(mysqli_query ($link, $query))
{
	if (isset($uid_request)) echo '<script type="text/javascript">document.location.href = "check.php";</script>';
}
elseif(strpos(mysqli_error($link),'Duplicate entry') >= 0 AND isset($uid_request))
{
	echo '<script type="text/javascript">alert("Такой uid уже есть в базе.\nСвяжитесь с администратором.");</script>';
	echo '<script type="text/javascript">window.top.location.href = "index.php";</script>';
}
else die("0Ошибка: " . mysqli_error($link));

?>
