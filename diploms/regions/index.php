<?php 
ob_start();
session_start(); 

if(!$_SESSION['token'] OR !$_SESSION['geo_cat']) 
{
	$new_url = '../../index.php';
	header('Location: '.$new_url);
	if ($_SESSION['LOGFILE'])
	{
		$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . " ## перенаправлен на главную index.php\n";
		file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
	}
	ob_end_flush();
	exit;
}

if(!$_SESSION['user_caches']) echo '<script type="text/javascript">document.location.href = "../reload.php";</script>';

ob_end_flush();

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);
?>

<head>
				<meta charset="utf-8">
				<meta name="description" content="Составление геокешерских дипломов. Дипломная программа geocaching.su"> 
				<title>РЕГИОНЫ РОССИИ - Проверка геокешерского диплома</title>
				<link rel="stylesheet" type="text/css" href="../../css/style.css" />
</head>

</style>
<body>
		
		<form enctype="multipart/form-data" action="regions.php" method="post" style="text-align: center;" class="main">
		<div>
		<p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../../media/gc.png" /></a></p>
		</div>		
		<div style="text-align:center;"><p><b><a href="http://www.geocaching.su/phorum/read.php?18,140237" target="blank">ДИПЛОМ "РЕГИОНЫ РОССИИ"</a></b></p></div>
		
			
			<?php 
			$userid = $_SESSION['userid'];

			require 'tables.php';			
			require '../../Classes/loc.php';
			$link = mysqli_connect($a,$b,$c,$d);
			if (!$link) 
			{
				printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
				exit;
			}
			
			$query="SHOW TABLES LIKE '$user_regions_used_caches_table'";
			$result = mysqli_query($link, $query) or die('error query' . mysqli_error($link));
			if (mysqli_num_rows($result))
			{
				$query = "SELECT * FROM $user_regions_used_caches_table";
				if($result = mysqli_query ($link, $query) or die("1Ошибка: " . mysqli_error($link)))
				{
					while ($arr = mysqli_fetch_array($result))
					{
						$usedregions[] = $arr['region'];
					}
				}
			}
			
						
			/*echo "<pre>";
			print_r ($usedregions);
			echo "</pre>";*/
			
			$query="SELECT id, spisok FROM $const_regions_table ORDER BY spisok";
			$result = mysqli_query($link, $query);
			if ($result) 
			{
				echo('<div>
					<p>Выберите регион:</p>
					<p><select size="1" name="region"></p>');
				while($object = mysqli_fetch_object($result))
				{
					echo '- '.$object->spisok;
					if($object->spisok) 
					{	
						if (in_array($object->id, $usedregions)) echo ("<option value = '$object->id' disabled> $object->spisok </option>");
						else echo "<option value = '$object->id' > $object->spisok </option>";
					}
				}
				echo ('					
					</select>
				</div>');
				

				mysqli_free_result($result);
			}
			mysqli_close($link);
			
			?>
			
			<div>
				<input type="submit" name="submit" value="Нажмите"> для проверки.
			</div>
			<div>
				Так же можете:
				<ol>
						<li><a href="">Загрузить</a> свои заявки, что бы сохранить уже задействованные в дипломе тайники в базе.</li>	
						<li><a href="">Удалить</a> сохраненные тайники по региону.</li>
						<li><a href="">Удалить</a> все сохраненные тайники.</li>
				</ol>
			</div>
			</div><table class="nav">
				<tr>
					<td class="nav1"><a href="../../index.php">К началу</a></td>
					<td class="nav2"><a href="../index.php">Выбор диплома</a></td>
				</tr>
			</table>
		</form>
</body>
</html>
