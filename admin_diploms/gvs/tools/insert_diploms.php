<?php
ob_start();
session_start();

if (isset($_SESSION['user'])) $user = $_SESSION['user'];
if (isset($_SESSION['diploms'])) $diploms = $_SESSION['diploms'];
else echo '<script type="text/javascript">document.location.href = "../../../index.php";</script>';

$user = $_SESSION['user'];
$diploms = $_SESSION['diploms'];

require '../tables.php';
require '../../../Classes/loc.php';
$link = mysqli_connect($a,$b,$c,$d);
if (!$link) 
{
    printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
    exit;
}

preg_match('/.+=(\d{1,7})/', $user['url'], $uid);
$uid = $uid['1'];
$query = sprintf("SELECT * FROM $users_table WHERE `uid` LIKE '%s'", mysqli_real_escape_string($link, $uid));
$result = mysqli_query ($link, $query) or die("0Ошибка: " . mysqli_error($link));

echo'<pre>';
print_r(mysqli_num_rows ($result));
echo'</pre>';

if (mysqli_num_rows ($result) == 0)
{
    $uname = $user['nik'];
    $stars = count($diploms);
    require_once ('../add_user.php');
}

foreach ($diploms as $key => $data)
{
    
}










?>