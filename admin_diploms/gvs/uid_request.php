<?php 
ob_start();
session_start();

if(!$_SESSION['token']) 
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
ob_end_flush();

if(!in_array($_SESSION['username'], array('banderaz','MagDi'))) echo '<script type="text/javascript">document.location.href = "../../index.php";</script>';

$log = date('d.m.Y H:i:s') . " ## " . $_SESSION['USER_IP'] . " ## " . $_SESSION['username'] . " ## " . basename(__DIR__) . "/" . basename(__FILE__) . "\n";
file_put_contents ($_SESSION['LOGFILE'], $log, FILE_APPEND);

if (isset($_SESSION['z_data'])) $z_data = $_SESSION['z_data'];
else echo '<script type="text/javascript">document.location.href = "index.php";</script>';
?>
<html>
	<head>
		<?PHP //header("charset=utf-8");?>
		<meta charset="utf-8">
		<meta name="description" content=""> 
		<title></title>
		<link rel="stylesheet" type="text/css" href="../../css/style.css" />
	</head>
	<body>
	<div class="main" >
		<form enctype="multipart/form-data" action="" method="post" style="text-align: center;">
			<div><p style="text-align: center; margin: 0px;"><a href="http://geocaching.su" target="blank"><img src="../../media/gc.png" /></a></p></div>

			<div>
				Игрока с ником <b><?php echo trim($z_data[6]['E']); ?></b> нет в базе дипломов ГВС</br></br>
				Введите uid игрока:</br></br>
				<input type="text" name="userid">
				<input type="submit" value=" Пуск "><br /></br>
				<a href="https://geocaching.su/?pn=108" target="_blank"><span style="font-size: 14px;">страница поиска игрока</span></a>
			</div>
		</form>

		<table class="nav">
			<tr>
				<td class="nav1"><a href="../../index.php">К началу</a></td>
			</tr>
		</table>
	</div>
	</body>
</html>
<?php 
if (isset($_POST['userid']))
{
	$z_data[6]['uid'] = $_POST['userid'];
	if($z_data[6]['uid']<1)
	{
		echo '<script type="text/javascript">alert("Вы не ввели uid игрока");</script>';
		echo '<script type="text/javascript">window.top.location.href = "";</script>';
		die("error");
	}
	else
	{
		$url = 'https://geocaching.su/profile.php?uid='.$z_data[6]['uid'];
		include ('../../curlinit.php');
		$user_page = curl_exec($ch);

		preg_match_all('/<title>Профайл игрока:(?P<unick>.*?)<\/title>/u', $user_page, $user_profile);
		
/*echo "<pre>";
print_r($user_profile);
echo "</pre>";*/

		if(!$user_profile['unick'])
		{
			echo '<script type="text/javascript">alert("Игрока с uid=' . $z_data[6]['uid'] . ' не существует.");</script>';
			echo '<script type="text/javascript">window.top.location.href = "";</script>';
			die("error");
		}
		else 
		{
			$user_profile['unick'][0] = trim($user_profile['unick'][0]);
			if($user_profile['unick'][0] == $z_data[6]['E'])
			{
				$z_data[6]['stars'] = 0;
				$_SESSION['z_data'] = $z_data;
				/*echo '<script type="text/javascript">
						result = confirm("uid игрока:		' . $z_data[6]['uid'] . '\nНик по uid:		' . $user_profile['unick'][0] . '\nНик в заявке:	' . $z_data[6]['E'] . '\n\n внести игрока в базу дипломов ГВС?");
						if(result == true) {window.top.location.href = "add_user.php";}
						else {window.top.location.href = "index.php";}
					</script>';*/
					echo '<script type="text/javascript">
					result = confirm("uid игрока:		' . $z_data[6]['uid'] . '\nНик по uid:		' . $user_profile['unick'][0] . '\nНик в заявке:	' . $z_data[6]['E'] . '\n\n внести игрока в базу дипломов ГВС?");
					if(result !== true) {window.top.location.href = "index.php";}
					</script>';	

					require 'tables.php';
					require '../../Classes/loc.php';
					$link = mysqli_connect($a,$b,$c,$d);
					if (!$link)
					{
						printf("Невозможно подключиться к базе данных. Код ошибки: %s\n", mysqli_connect_error());
						exit;
					}
					$uid = mysqli_real_escape_string($link, (int)$z_data[6]['uid']);
					$uname = mysqli_real_escape_string($link, $z_data[6]['E']);
					$stars = mysqli_real_escape_string($link, (int)$z_data[6]['stars']);
					$uid_request = '';
					require_once ('add_user.php');
			}
			else 
			{
				echo '<script type="text/javascript">
						result = confirm("uid игрока:		' . $z_data[6]['uid'] . '\nНик по uid:		' . $user_profile['unick'][0] . '\nНик в заявке:	' . $z_data[6]['E'] . '\n\nНик по uid не совпадает с ником в заявке.\nПроверьте правильность введенного uid и/или правильность написания ника в заявке.\n\nОК - ввести uid снова\nОтмена - возврат на страницу загрузки заявки.");
						if(result == true) {window.top.location.href = "";}
						else {window.top.location.href = "index.php";}
					</script>';
			}
		}
	}	
}

?>