<?php 

$query = "INSERT INTO $suitable_table (`cid`, `сname`, `сregion`) VALUES ($str)";
if (!mysqli_query($link, $query)) 
{
    if(preg_match('/Duplicate entry \'(\d{1,5})\'/', mysqli_error($link), $err))
    {
        $suit_err .= $err[1] . ' - тайник не добавлен, так как уже есть в базе подходящих тайников.\n' ;
    }
    else $suit_err .= 'Ошибка: ' . mysqli_error($link) . '\n\n';
    
}
else 
{
    $suit_ok .= $cid . ' - тайник добавлен в базу подходящих тайников.\n\n';
}
?>