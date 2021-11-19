<?php
ob_start();
session_start();

unset ($_SESSION['loadcaches']);
$new_url = 'onload.php';
header('Location: '.$new_url);
ob_end_flush();

?>