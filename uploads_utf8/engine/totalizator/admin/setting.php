<?php

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}
$hidden_array['subaction'] = "save";
$save_con = empty($_POST['save_con'])?array():$_POST['save_con'];

if ($subaction == "save" && $save_con)
{
	$save_con['version_id'] = $total_conf['version_id'];

	if ($config['version_id'] < 7.5)
	{
    	if($member_db[1] != 1){ $tpl->msg($lang['opt_denied'], $lang['opt_denied'], $PHP_SELF."?mod=totalizator"); }
	}
    else 
    {
    	if($member_id['user_group'] != 1){ $tpl->msg($lang['opt_denied'], $lang['opt_denied'], $PHP_SELF."?mod=totalizator"); }
    }
    
    clear_cache();
    $handler = fopen(ENGINE_DIR.'/data/totalizator_conf.php', "w");
    fwrite($handler, "<?PHP \n\n//Totalizator Configurations\n\n\$total_conf = array (\n\n");
    
    save_conf($save_con);
    fwrite($handler, ");\n\n?>");
    fclose($handler);
    $tpl->msg("Сохранение настроек", "Настройки успешно сохранены", $PHP_SELF."?mod=totalizator");
}

$tpl->echo = FALSE;

include_once ENGINE_DIR . "/totalizator/admin/settings_array.php";

$tpl->echo = TRUE;

$tpl->OpenTable();
$tpl->OpenSubtable("Настройки");
$tpl->OpenForm('', $hidden_array);
$tpl->OTable();
foreach ($settings_array as $setting)
{
	if ($setting['title'] && $setting['setting'])
		$tpl->SettingRow($setting['title'], $setting['descr'], $setting['setting']);
}
$tpl->CTable();
$tpl->CloseSubtable("Сохранить");
$tpl->CloseForm();
$tpl->CloseTable();
?>
