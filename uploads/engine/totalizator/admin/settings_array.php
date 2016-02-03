<?php

$settings_array = array(
					array(
							"title" => 'Включить кэширование статистики и блоков?',
							"descr" => 'Основная страница пока не кэшируется',
							"setting"=> YesNo("allow_cache"),
							),
					array(
							"title" => 'Включить ЧПУ',
							"descr" => 'ЧПУ - человеко-понятные урл',
							"setting"=> YesNo("alt_url"),
							),
					array(
							"title" => 'Кол. на страницу',
							"descr" => 'Количество матчей/пользователей/ставок на строницу',
							"setting"=> $tpl->input("save_con[per_page]", $total_conf['per_page'], "text" ,"style=\"width:30px\""),
							),
					array(
							"title" => 'Минуты до начало',
							"descr" => 'Количество минут до матча, когда прекращаются ставки',
							"setting"=> $tpl->input("save_con[time]", $total_conf['time'], "text" ,"style=\"width:30px\""),
							),
					array(
							"title" => 'Просмотр ставок пользователей',
							"descr" => 'Разрешить просматривать ставки других пользователей',
							"setting"=> YesNo("allow_view_points"),
							),
					array(
							"title" => 'Показывать количество ставок для матча',
							"descr" => 'Выводит количество ставок для матча',
							"setting"=> YesNo("allow_rates"),
							),
					array(
							"title" => 'Показывать процент угадываний для матча',
							"descr" => 'Выводит процент для матча который вычесляется так: (угадали матч/всего ставок)*100',
							"setting"=> YesNo("allow_procent"),
							),
					array(
							"title" => 'Очки за угаданный счёт матча',
							"descr" => 'Полномтью угадал счёт',
							"setting"=> $tpl->input("save_con[point_3]", $total_conf['point_3'], "text" ,"style=\"width:30px\""),
							),
					array(
							"title" => 'Очки за угаданную разницу мячей',
							"descr" => 'Это значит что если команды сыграли 2-1 на самом деле, а юзер ставил на 3-2, то юзер получает это кол. очков',
							"setting"=> $tpl->input("save_con[point_2]", $total_conf['point_2'], "text" ,"style=\"width:30px\""),
							),
					array(
							"title" => 'Очки за угаданный исход матча',
							"descr" => 'Угаданный исход матча, это значит, что если команды сыграли 2-0 в пользу скажем Челси, а юзер ставил 4-0, но на Челси, то он получает это кол. очков, за то что предсказал победителя матча',
							"setting"=> $tpl->input("save_con[point_1]", $total_conf['point_1'], "text" ,"style=\"width:30px\""),
							),
					array(
							"title" => 'Очки за не угаданный исход матча',
							"descr" => 'Не угаданный исход матча, это значит, что если команды сыграли 2-0 в пользу скажем Челси, а юзер ставил 0-3, но на Челси, то он получает это кол. очков, за то что не правильно предсказал победителя матча',
							"setting"=> $tpl->input("save_con[point_0]", $total_conf['point_0'], "text" ,"style=\"width:30px\""),
							),
                    array(
							"title" => 'Максимальное количество очков при выборе счета',
							"descr" => 'Устанавливает максимальное количество очков',
							"setting"=> $tpl->input("save_con[game_points]", $total_conf['game_points'], "text" ,"style=\"width:30px\""),
							),
                    array(
							"title" => 'Разрешить пользователям редактировать свои ставки',
							"descr" => 'Даёт возможность пользователям изменить счет до того как начнется матч',
							"setting"=> YesNo("allow_edit"),
							),
                    array(
							"title" => 'Включить архив на сайте',
							"descr" => 'Даёт возможность пользователям просматривать матчи прошлых сезонов',
							"setting"=> YesNo("show_archive"),
							),
					array(
							"title" => 'Включить блок "Самый предсказуемый матч"',
							"descr" => 'это матчи, которые угадало больше всего человек за всю историю',
							"setting"=> YesNo("allow_PredictedMatche"),
							),
					array(
							"title" => 'Включить блок "Самый непредсказуемы матч"',
							"descr" => 'это матчи, которые угадали меньше всего человек за всю историю',
							"setting"=> YesNo("allow_NotPredictedMatche"),
							),
					array(
							"title" => 'Максимальная длина имени в блоках "Самый предсказуемый матч" и "Самый непредсказуемы матч"',
							"descr" => 'Длина текста который вставляется в тег {short_name}, 0 => идёт полное название',
							"setting"=> $tpl->input("save_con[short_name]", $total_conf['short_name'], "text" ,"style=\"width:30px\""),
							),
					array(
							"title" => 'Количество матчей в блоках "Самый предсказуемый матч" и "Самый непредсказуемы матч"',
							"descr" => ' 0 => 10 матчей',
							"setting"=> $tpl->input("save_con[predicted_limit]", $total_conf['predicted_limit'], "text" ,"style=\"width:30px\""),
							),
					array(
							"title" => 'Включить блок "Реконсмены"',
							"descr" => 'Выводит пользователей у которых само больше отчков',
							"setting"=> YesNo("allow_RatesUsers"),
							),
					array(
							"title" => 'Количество пользователей в блоке "Реконсмены"',
							"descr" => ' 0 => 10 пользователей',
							"setting"=> $tpl->input("save_con[user_limit]", $total_conf['user_limit'], "text" ,"style=\"width:30px\""),
							),
					);
                    
?>