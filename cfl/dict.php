<?php
ob_start();
session_start();

if(!$_SESSION['token']) 
{
	$new_url = '../index.php';
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

$ctype = Array (
	"1" => array("TR","Traditional Cache","Traditional","Традиционный","green"),
	"2" => array("MS","Multi-cache","TraditionalMultistep","Пошаговый традиционный","green"),
	"3" => array("VI","Virtual Cache","Virtual","Виртуальный","orange"),
	"4" => array("EV","Event Cache","Event","Встреча","yellow"),
	//"7" => array("MV","Unknown Cache","VirtualMultistep","Пошаговый виртуальный","orange"),
	"7" => array("MV","Virtual Cache","VirtualMultistep","Пошаговый виртуальный","orange"),
	"8" => array("CT","Mega-Event Cache","Contest","Конкурс","yellow"),
	//"9" => array("LT","Wherigo Cache","Logical","Логический","blue"),
	//"10" => array("LV","Cache In Trash Out Event","LogicalVirtual", "Логический виртуальный","blue"),
	"9" => array("LT","Unknown Cache","Logical","Логический","blue"),
	"10" => array("LV","Unknown Cache","LogicalVirtual", "Логический виртуальный","blue"),
	"1979" => array("SU","Unknown cache type from SU","Unknown cache type from SU", "Неизвестный тип тайника","grey")
);

$csize = Array (
	"1" => array("Неизвестно", ""),
	"2" => array("Микро", "Micro"),
	"3" => array("Маленький", "Small"),
	"4" => array("Нормальный", "Regular"),
	"5" => array("Другой", "Other")
);

$statusString = array(
	"1" => array("Active","На сайте"),
	"2" => array("Archived","В архиве"),
	"6" => array("Premoderation","На премодерации"),
	"7" => array("","")
);

//stus2String => available * информация на русском * id атрибута на com (иконка) * зачеркнута иконка или нет (0-да, 1-нет) * title для иконки	

$cclass = Array (
	"0" => array("Другое"),
	"1" => array("Природный"),
	"2" => array("Архитектурный"),
	"3" => array("Техноген"),
	"4" => array("Археологические памятники"),
	"5" => array("Замечательные люди"),
	"6" => array("Исторический"),
	"7" => array("Фортификация"),
	"8" => array("Музей/экскурсия"),
	"9" => array("Прогулка"),
	"10" => array("Логический"),
	"11" => array("Экстремальный"),
	"1979" => array("Класс не определен")
);

$cattr = Array (
	"20" => array("Крутой подъем",10,1,"https://geocaching.su/members/images/attrib/climbing.png"),
	"21" => array("Переправа вброд",11,1,"https://geocaching.su/members/images/attrib/wading.png"),
	"22" => array("Заболоченная местность",11,1,"https://geocaching.su/members/images/attrib/swamp1.png"),
	"23" => array("Лесные завалы, бурелом",39,1,"https://geocaching.su/members/images/attrib/windbreak.png"),
	"24" => array("Нет воды для питья",27,0,"https://geocaching.su/members/images/attrib/no_water.png"),
	"25" => array("Обвалы/лавины",21,1,"https://geocaching.su/members/images/attrib/cliff.png"),
	"26" => array("Недалеко стреляют охотники",22,1,"https://geocaching.su/members/images/attrib/hunting.png"),
	"27" => array("Ядовитые растения",17,1,"https://geocaching.su/members/images/attrib/poisonoak.png"),
	"28" => array("Густой или колючий кустарник",39,1,"https://geocaching.su/members/images/attrib/thorn.png"),
	"29" => array("Змеи",18,1,"https://geocaching.su/members/images/attrib/snakes.png"),
	"30" => array("Клещи",19,1,"https://geocaching.su/members/images/attrib/ticks.png"),
	"31" => array("Заброшенные шахты",20,1,"https://geocaching.su/members/images/attrib/mine.png"),
	"8" => array("Хорошее место для большого привала или пикника",30,1,"https://geocaching.su/members/images/attrib/picnic.png"),
	"9" => array("Возможна интересная экскурсия",8,1,"https://geocaching.su/members/images/attrib/scenic.png"),
	"10" => array("Как минимум одна ночевка на природе",16,1,"https://geocaching.su/members/images/attrib/camping.png"),
	"11" => array("Нет общественного транспорта",26,0,"https://geocaching.su/members/images/attrib/no_bus.png"),
	"12" => array("Проблемы с общественным транспортом",26,0,"https://geocaching.su/members/images/attrib/doubt_bus.png"),
	"13" => array("На машине не проехать",35,0,"https://geocaching.su/members/images/attrib/no_way.png"),
	"14" => array("Есть оборудованная парковка",25,1,"https://geocaching.su/members/images/attrib/parking.png"),
	"15" => array("Плата за вход или парковку",2,1,"https://geocaching.su/members/images/attrib/fee.png"),
	"16" => array("Оборудовано для инвалидов",24,1,"https://geocaching.su/members/images/attrib/invalid.png"),
	"17" => array("С собакой нельзя",1,0,"https://geocaching.su/members/images/attrib/dogs.png"),
	"18" => array("Не жечь костры",38,0,"https://geocaching.su/members/images/attrib/campfires.png"),
	"19" => array("Часы посещения ограничены",13,0,"https://geocaching.su/members/images/attrib/watch.png"),
	"38" => array("Кротель",61,1,"https://geocaching.su/members/images/attrib/krotel.png"),
	"1" => array("Лучше пешком",9,1,"https://geocaching.su/members/images/attrib/hiking.png"),
	"2" => array("Лучше на велосипеде",32,1,"https://geocaching.su/members/images/attrib/bicycle.png"),
	"3" => array("Лучше на внедорожнике",35,1,"https://geocaching.su/members/images/attrib/jeeps.png"),
	"4" => array("Лучше на лыжах",50,1,"https://geocaching.su/members/images/attrib/ski.png"),
	"37" => array("Лучше на байдарке",4,1,"https://geocaching.su/members/images/attrib/kayak.png"),
	"5" => array("Рекомендуется для посещения с детьми",6,1,"https://geocaching.su/members/images/attrib/kids.png"),
	"6" => array("Рекомендуется для посещения ночью",14,1,"https://geocaching.su/members/images/attrib/night.png"),
	"7" => array("Зимой недоступен",15,0,"https://geocaching.su/members/images/attrib/winter.png"),
	"36" => array("Людное место, требуется особая осторожность при поиске",40,1,"https://geocaching.su/members/images/attrib/spy.png"),
	"32" => array("Требуется горное снаряжение",3,1,"https://geocaching.su/members/images/attrib/rappelling.png"),
	"33" => array("Требуется водное снаряжение",4,1,"https://geocaching.su/members/images/attrib/boat.png"),
	"34" => array("Требуется подводное снаряжение",5,1,"https://geocaching.su/members/images/attrib/scuba.png"),
	"35" => array("Требуется спелеоснаряжение",3,1,"https://geocaching.su/members/images/attrib/speleo.png"),
	"Doubtful" => array("Есть сомнения в сохранности или доступности тайника",42,1,"https://geocaching.su/images/ctypes/icons/blue.png","blue"),
	"Inactive" => array("Основатель тайника и/или администрация сайта считают, что тайник временно не действует",42,1,"https://geocaching.su/images/ctypes/icons/red.png","red")
);

$notetypes = array(
	"1" => "Found it", //found
	"2" => "Didn't find it", //notFound
	"3" => "Write note", //comment
	"4" => "Write note", //visitedNotTriedToFind
	"5" => "Owner Maintenance", //repaired
	"6" => "Write note", //authorChek
);

$wptypes = Array(
	"1" => array("Парковка", "Parking Area"),
	"2" => array("Шаг тайника", "Physical Stage"),
	"3" => array("Вопрос тайника", "Virtual Stage"),
	"4" => array("Начало тропы", "Trailhead"),
	"5" => array("Финальная точка", "Final Location"),
	"6" => array("Заметка", "Reference Point")
);
?>