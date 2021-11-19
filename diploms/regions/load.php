<?php
ob_start();
session_start(); 
if(!$_SESSION['token'] OR !$_SESSION['geo_cat']) {
	$new_url = '/geocaching/index.php';
	header('Location: '.$new_url);
	ob_end_flush();
	exit;
}
ob_end_flush();
?>
<head>
				<meta charset="utf-8">
				<link rel="stylesheet" type="text/css" href="/css/style.css" />
</head>
<?php
echo '<form enctype="multipart/form-data" action="insert.php" method="post" style="text-align: center;" class="main">
		
		<div>
		<p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="/media/gc.png" /></a></p>
		</div>		
		<div style="text-align:center;"><p><b><a href="http://www.geocaching.su/phorum/read.php?18,140237" target="blank">ДИПЛОМ "РЕГИОНЫ РОССИИ"</a></b></p></div>
		<table class="nav">
				<tr>
					<td class="nav1"><a href="javascript:history.back()" title="Вернуться на предыдущую страницу" >Назад</a></td>
					<td class="nav2"><a href="/main.php" title="Вернуться к выбору">К началу</a></td>
				</tr>
			</table>';

if($_FILES['uploadfile']['error'] > 0) 
	{
		echo '<div><p class="no">Не выбран файл</p></div>';
		exit;
	}

	//проверка файла по расширению, можно только xls или xlsx
	$file_type = pathinfo($_FILES['uploadfile']['name'], PATHINFO_EXTENSION);
	$file_name = pathinfo($_FILES['uploadfile']['name'], PATHINFO_BASENAME);

	if($file_type != "xls")
	{
		if($file_type != "xlsx")
		{
			echo '<div><p class="no"">Можно загрузить только файлы <b>xls</b> или <b>xlsx</b></p></div>';
			exit;
		}
	}
	
	
	//папка для загрузки, новое имя, путь к файлу
	$uploadfile = $_SESSION['userid'] . '_regions.'.$file_type;
	
	//загрузка файла
	move_uploaded_file($_FILES['uploadfile']['tmp_name'], $uploadfile);

// Подключаем библиотеку
require_once ($_SERVER['DOCUMENT_ROOT'].'/Classes/PHPExcel.php');
$pExcel = PHPExcel_IOFactory::load($uploadfile);

// Цикл по листам Excel-файла
foreach ($pExcel->getWorksheetIterator() as $worksheet) {
    // выгружаем данные из объекта в массив
    $tables[] = $worksheet->toArray();
}

foreach ($tables as $table)
{
	foreach ($table as $row=>$col)
	{
		if ($k=array_search('ЗАЯВКА НА ДИПЛОМ «Регионы России»',$col)) $d=1;	
		if ($k=array_search('Номер заявляемого региона:',$col))	$_SESSION['region']=$tables[0][$row][3];	
		if ($k=array_search('Код',$col)) $row_k=$row;
	}
}
if (!$d==1)
{
	echo '<div><p class="no">Эта заявка не относится к диплому «Регионы России»</p></div>';
	unlink ($uploadfile);
	exit;
}
$row_k++;
$n=0;
while($tables[0][$row_k][3])
{
	$caches[$n][reg]=$tables[0][$row_k][1];
	$caches[$n][name]=$tables[0][$row_k][2];
	$caches[$n][cid]=$tables[0][$row_k][3];
	$row_k++;
	$n++;
}
unlink ($uploadfile);

echo '<div><div style="text-align: center; border: none;"><input name="submit" type="submit" value="Загрузить и сохранить"></div>
		<table class="res">';
		
			$n=1;
			foreach ($caches as $k => $v)
			{
				echo '<tr class="yes"><td>'.$n.'</td><td>'.$v[reg].'</td><td>'.$v[name].'</td><td>'.$v[cid].'</td></tr>';
				$n++;
			}
			$_SESSION['caches']=$caches;
echo'		</table>
<div style="text-align: center; border: none;"><input name="submit" type="submit" value="Загрузить и сохранить"></div>
	</div>
<table class="nav">
				<tr>
					<td class="nav1"><a href="javascript:history.back()" title="Вернуться на предыдущую страницу" >Назад</a></td>
					<td class="nav2"><a href="/main.php">К началу</a></td>
				</tr>
			</table>
	
	</form>';


//echo $caches;

/*echo "<pre>";
print_r ($caches);
echo "</pre>";*/

/*echo "<pre>";
print_r ($tables);
echo "</pre>";*/

?>