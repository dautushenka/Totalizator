<?PHP

require dirname(__FILE__) . "/engine/totalizator/InstallUpdate.php";

require_once ENGINE_DIR.'/totalizator/version.php';
require(ENGINE_DIR . "/totalizator/functions.php");
require(ENGINE_DIR . "/totalizator/template.admin.class.php");

$version = VERSION;
define('MODULE_NAME', 'Totalizator (Тотализатор)');
$licence = /*licadm*/'.'/*/licadm*/;
define('CONFIG_VARNAME', 'total_conf');
define('CONFIG_FILE', 'totalizator_conf.php');
define('REQUIRED_DLE', 5.3);
define('REQUIRED_PHP', 5.0);
define('REQUIRED_MYSQL', 4.1);
define('YEAR', 2008);
$image_patch = "engine/totalizator/images/install";
$important_files = array();

$text_main = <<<HTML
<b>Основные возможности:</b>
- Добавление турниров 
- Добавление матчей 
- Ставки пользователей на матчи 
- Начисление определённого количества очков в зависимости от исхода матча и ставки пользователя 
- Возможность использовать AJAX для перехода м/д страницами 
- Возможность использование ЧПУ 
- Возможность использования кэширования 
- Выборка по турнирам, командам, пользователю 
- 3 Блока "Самый непредсказуемы матч", "Самый предсказуемый матч", "Самые активные". 
- Архив турниров
HTML;
$text_main = nl2br($text_main);

if ($_POST['type'] == "update")
{
	$obj = new install_update(MODULE_NAME, $version, array(), $licence, $db, $image_patch);
	$obj->year = YEAR;
	require(ENGINE_DIR . "/data/" . CONFIG_FILE);
	$module_config = ${CONFIG_VARNAME};
	
	switch ($module_config['version_id'])
	{
		case VERSION:
			$obj->Finish("<div style=\"text-align:center;font-size:150%;\">Вы используете актуальную версию скрипта. Обновление не требуется</div>");
			break;
			
		case '1.0.5':
		case '1.0.0':
	       $to_version = VERSION;
           $obj->steps_array = array(
                                    "ChangeLog",
                                    "Проверка хостинга",
                                    "Работа с базой данных",
                                    "Завершение обновления"
                                    );
                                    $ChangeLog = <<<TEXT
<b>Обновление до версии $to_version</b>

[+] - Архив турниров 
[+] - Администрирование БД 
[+] - Очки пользователей по турнирам 
[+] - Редактирование ставок 
[+] - Нумерация в статистике 
[+] - Очки и место в профиле
[fix] - Сортировка ставок 

TEXT;
                                    $ChangeLog = nl2br($ChangeLog);
                                    $important_files = array(
                        './install.php',
                        './engine/data/' . CONFIG_FILE,
                                    );
                                    
                                    $table_schema[] = "ALTER TABLE `" . PREFIX . "_tournaments` add column `archive` TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL";
                                    $table_schema[] = "UPDATE `" . PREFIX . "_rates` SET date_rate=" . time();
                                    
                                    $module_config = array_merge($module_config, array('game_points' => "15",
                                                                                       'allow_edit' => "0",
                                                                                       'show_archive' => "0"
                                                                                        ));
                                    
                                    
                                    $finish_text = <<<HTML
<div style="text-align:center;">Обновление модуля до версии $to_version прошло успешно.</div>
HTML;
                                    switch (intval($_POST['step']))
                                    {
                                        case 0:
                                            $obj->Main($ChangeLog, 'Начать обновление');
                                            break;

                                        case 1:
                                            $obj->CheckHost($important_files, REQUIRED_DLE, REQUIRED_PHP, REQUIRED_MYSQL);
                                            break;
                                            
                                        case 2:
                                            $obj->Database($table_schema);
                                            break;
                                                
                                        case 3:
                                            $obj->ChangeVersion(CONFIG_FILE, CONFIG_VARNAME, $module_config, array(), $to_version);
                                            $obj->Finish($finish_text, $to_version);
                                            break;
                                    }
		    break;
			
		default:
			$text = <<<TEXT
<b>Не известная версия модуля. Переустановите модуль.</b>
TEXT;
			$obj->OtherPage($text);
			break;
	}
}
else 
{
	$title = array(
					"Описание модуля",
                    "Лицензионное соглашение",
                    "Проверка хостинга",
                    "Создание файла настроек",
                    "Работа с базой данных",
                    "Завершение установки"
				);
				
	$obj = new install_update(MODULE_NAME, $version, $title, $licence, $db, $image_patch);
	$obj->year = YEAR;

	switch ($_POST['step'])
	{
	    case 1:
	        $module_name = MODULE_NAME;
	        $head_licence = <<<HTML
Пожалуйста внимательно прочитайте и примите пользовательское соглашение по использованию модуля "$module_name".
HTML;

	        $text_licence = <<<HTML
Покупатель имеет право:</b><ul><li>Изменять дизайн и структуру программного продукта в соответствии с нуждами своего сайта.</li><br /><li>Производить и распространять инструкции по созданным Вами модификациям шаблонов и языковых файлов, если в них будет иметься указание на оригинального разработчика программного продукта до Ваших модификаций.</li><br /><li>Переносить программный продукт на другой сайт после обязательного уведомления меня об этом, а также полного удаления скрипта с предыдущего сайта.</li><br /></ul><br /><b>Покупатель не имеет право:</b><br /><ul><li>Передавать права на использование интеграции третьим лицам, кроме случаев, перечисленных выше в нашем соглашении.</li><br /><li>Изменять структуру программных кодов, функции программы или создавать родственные продукты, базирующиеся на нашем программном коде</li><br /><li>Использовать более одной копии модуля <b>$module_name</b> по одной лицензии</li><br /><li>Рекламировать, продавать или публиковать на своем сайте пиратские копии модуля</li><br /><li>Распространять или содействовать распространению нелицензионных копий модуля <b>$module_name</b></li><br /></ul>
HTML;
	        
			$obj->Licence($head_licence, $text_licence);
			
			
		case 2:
		    $important_files = array(
						'./install.php',
                        './engine/data/'
						);
						
			$obj->CheckHost($important_files, REQUIRED_DLE, REQUIRED_PHP, REQUIRED_MYSQL);
			
        case 3:
            $total_conf = array(
                            'allow_cache' => "0",
                            'alt_url' => "1",
                            'per_page' => "50",
                            'time' => "10",
                            'allow_view_points' => "0",
                            'allow_rates' => "1",
                            'allow_procent' => "0",
                            'point_3' => "3",
                            'point_2' => "2",
                            'point_1' => "1",
                            'point_0' => "0",
                            'game_points' => "15",
                            'allow_edit' => "0",
                            'show_archive' => "0",
                            'allow_PredictedMatche' => "0",
                            'allow_NotPredictedMatche' => "0",
                            'predicted_limit' => "40",
                            'short_name' => "10",
                            'allow_RatesUsers' => "0",
                            'user_limit' => "10",
                            );
        
            $tpl->echo = FALSE;
            
            include_once ENGINE_DIR . "/totalizator/admin/settings_array.php";
        	
			$obj->Settings($settings_array, $total_conf, CONFIG_VARNAME, CONFIG_FILE);
			
        case 4:
		    $table_schema[PREFIX . "_tournaments"] = "CREATE TABLE `" . PREFIX . "_tournaments` (
                                                   `tournament_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                  `name` VARCHAR(255) DEFAULT NULL,
                                                  `alt_name` VARCHAR(255) DEFAULT NULL,
                                                  `description` TEXT,
                                                  `archive` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
                                                  PRIMARY KEY (`tournament_id`)
             ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
		    
		    $table_schema[PREFIX . "_matches"] = "CREATE TABLE `" . PREFIX . "_matches` (
                   `matche_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `tournament_id` INT(10) UNSIGNED DEFAULT NULL,
                  `date_matche` INT(10) UNSIGNED DEFAULT NULL,
                  `komanda1` VARCHAR(200) DEFAULT '',
                  `komanda2` VARCHAR(200) DEFAULT '',
                  `points_1` SMALLINT(5) UNSIGNED DEFAULT NULL,
                  `points_2` SMALLINT(5) UNSIGNED DEFAULT NULL,
                  `rates` SMALLINT(5) UNSIGNED DEFAULT '0',
                  `rates_right` SMALLINT(5) UNSIGNED DEFAULT '0',
                  `calculate` TINYINT(1) DEFAULT '0',
                  PRIMARY KEY (`matche_id`),
                  KEY `tournament_id` (`tournament_id`)
             ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
             
		    $table_schema[PREFIX . "_rates"] = "CREATE TABLE `" . PREFIX . "_rates` (
                   `matche_id` INT(10) UNSIGNED NOT NULL,
                  `user_id` INT(10) UNSIGNED NOT NULL,
                  `points_1` SMALLINT(5) UNSIGNED DEFAULT NULL,
                  `points_2` SMALLINT(5) UNSIGNED DEFAULT NULL,
                  `date_rate` INT(10) UNSIGNED DEFAULT NULL,
                  PRIMARY KEY (`matche_id`,`user_id`)
             ) ENGINE=MyISAM /*!40101 DEFAULT CHARACTER SET " . COLLATE . " COLLATE " . COLLATE . "_general_ci */";
		    
            $table_schema[PREFIX . "_users"] = "ALTER TABLE `" . PREFIX . "_users` ADD `points` int(5) unsigned NOT NULL default 0";
            
        	if ($config['version_id'] >= 8.2)
        	{
        	    $table_schema[] = "INSERT IGNORE `" . PREFIX . "_admin_sections` (allow_groups, name, icon, title, descr) VALUES ('all', 'totalizator', 'totalizator.jpg', 'Totalizator', 'Турниры, матчи, ставки')";
        	}
        	
			$obj->Database($table_schema);
			
		case 5:
		    $text_finish = <<<TEXT
	<div style="font-size:120%;text-align:center">Благодарим вас за покупку модуля. Надеемся что работа с ним доставит Вам только удовольствие!!! Все возникшие вопросы вы можете найти в документации или задать их на форуме поддержки <a href="http://forum.kaliostro.net/" >http://forum.kaliostro.net/</a> . </div>
TEXT;
			$obj->Finish($text_finish);
			break;
			
		default:
			if (file_exists(ENGINE_DIR.'/data/'.CONFIG_FILE) && empty($_POST['type']))
			{
				require(ENGINE_DIR . "/data/" . CONFIG_FILE);
				$config = ${CONFIG_VARNAME};
				$obj->steps_array = array();
				$obj->steps_array[] = "Описание модуля";
				
				switch ($config['version_id'])
				{
					case "1.0.5":
					case "1.0.0":
						$obj->steps_array[] = '2.0.0';

					default:
						$obj->steps_array[] = "Завершение обновления";
				}
				$obj->SetType("update", "Начать обновление");
				$obj->Main($text_main, "Начать обновление");
			}
			else 
			{
				$obj->SetType("install");
				$obj->Main($text_main, "Начать установку");
			}
			
			break;
	}
}

?>
