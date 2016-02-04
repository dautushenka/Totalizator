<?php
/*
=====================================================
 Totalizator v1.0.0
-----------------------------------------------------
 http://kaliostro.net/
-----------------------------------------------------
 Copyright (c) 2007 kaliostro ICQ: 415-74-19
=====================================================
 Данный код защищен авторскими правами
=====================================================
*/

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

@include_once(ENGINE_DIR . "/data/totalizator_conf.php");
@include_once(ENGINE_DIR . "/totalizator/functions.php");

function PredictedMatche()
{
	global $config, $total_conf, $tpl, $db;
	
	if (!$total_conf['allow_cache'] || !($cache = dle_cache("PredictedMatche")))
	{
		$limit = (intval($total_conf['predicted_limit']))?intval($total_conf['predicted_limit']):10;
		$short_limit = (intval($total_conf['short_name']))?intval($total_conf['short_name']):0;
		
		$db->query("SELECT *, SUM(rates_right)/SUM(rates)*100 AS procent FROM " . PREFIX . "_matches WHERE calculate=1 AND rates!=0 GROUP BY matche_id ORDER BY procent DESC LIMIT 0, ".$limit);
		
		$tpl->load_template("totalizator/predicted_block.tpl");
		preg_match("'\[row\](.*?)\[/row\]'si", $tpl->copy_template, $matches);
		
		while ($row = $db->get_row())
		{
			$name = $short_name = $row['komanda1'] . " " . $row['points_1'] . " - " . $row['points_2'] . " " . $row['komanda2'];
			
			if ($short_limit)
			{
				if (strlen($name) > $short_limit);
					$short_name = substr($name, 0 ,$short_limit);
			}
			
			$replace = array(
							"{short_name}" => $short_name,
							"{name}" => $name,
							"{rates}" => $row['rates'],
							"{rates_right}" => $row['rates_right'],
							"{komanda1}" => $row['komanda1'],
							"{komanda2}" => $row['komanda2'],
							"{points_1}" => $row['points_1'],
							"{points_2}" => $row['points_2'],
							"{procent}" => round($row['procent']),
							"{date_matche}" => date("d-m-Y H:i", $row['date_matche']),
							"{matche_url}" => GetUrl("matche", $row['matche_id'], false, false),
							);
							
			$tpl->copy_template = strtr($tpl->copy_template, $replace);
			$tpl->copy_template = preg_replace("'\[row\](.*?)\[/row\]'si", "\\1\n".$matches[0], $tpl->copy_template);
		}
		$tpl->set_block("'\[row\](.*?)\[/row\]'si", "");
		$tpl->compile('PredictedMatche');
		$tpl->clear();
	 	$db->free();
	 	
	 	if ($total_conf['allow_cache'])
	 		create_cache('PredictedMatche', $tpl->result['PredictedMatche']);
	}
	else 
		$tpl->result['PredictedMatche'] = $cache;
}

function NotPredictedMatche()
{
	global $config, $total_conf, $tpl, $db;
	
	if (!$total_conf['allow_cache'] || !($cache = dle_cache("NotPredictedMatche")))
	{
		$limit = (intval($total_conf['predicted_limit']))?intval($total_conf['predicted_limit']):10;
		$short_limit = (intval($total_conf['short_name']))?intval($total_conf['short_name']):0;
		
		$db->query("SELECT *, SUM(rates_right)/SUM(rates)*100 AS procent FROM " . PREFIX . "_matches WHERE calculate=1 AND rates!=0 GROUP BY matche_id ORDER BY procent ASC LIMIT 0, ".$limit);
		
		$tpl->load_template("totalizator/predicted_block.tpl");
		preg_match("'\[row\](.*?)\[/row\]'si", $tpl->copy_template, $matches);
		
		while ($row = $db->get_row())
		{			
			$name = $short_name = $row['komanda1'] . " " . $row['points_1'] . " - " . $row['points_2'] . " " . $row['komanda2'];
			
			if ($short_limit)
			{
				if (strlen($name) > $short_limit);
					$short_name = substr($name, 0 ,$short_limit);
			}
			
			$replace = array(
							"{short_name}" => $short_name,
							"{name}" => $name,
							"{rates}" => $row['rates'],
							"{rates_right}" => $row['rates_right'],
							"{komanda1}" => $row['komanda1'],
							"{komanda2}" => $row['komanda2'],
							"{points_1}" => $row['points_1'],
							"{points_2}" => $row['points_2'],
							"{procent}" => round($row['procent']),
							"{date_matche}" => date("d-m-Y H:i", $row['date_matche']),
							"{matche_url}" => GetUrl("matche", $row['matche_id'], false, false),
							);
							
			$tpl->copy_template = strtr($tpl->copy_template, $replace);
			$tpl->copy_template = preg_replace("'\[row\](.*?)\[/row\]'si", "\\1\n".$matches[0], $tpl->copy_template);
		}
		$tpl->set_block("'\[row\](.*?)\[/row\]'si", "");
		$tpl->compile('NotPredictedMatche');
		$tpl->clear();
	 	$db->free();
	 	if ($total_conf['allow_cache'])
	 		create_cache('NotPredictedMatche', $tpl->result['NotPredictedMatche']);
	}
	else 
		$tpl->resilt['NotPredictedMatche'] = $cache;
}

function RatesUsers()
{
	global $config, $total_conf, $tpl, $db;
	
	if (!$total_conf['allow_cache'] || !($cache = dle_cache("RatesUsers")))
	{
		$limit = (intval($total_conf['user_limit']))?intval($total_conf['user_limit']):10;
		
		$db->query("SELECT name, points FROM " . PREFIX . "_users WHERE points!=0 AND points!='' ORDER BY points DESC LIMIT 0, ".$limit);
		
		$tpl->load_template("totalizator/rates_users.tpl");
		preg_match("'\[row\](.*?)\[/row\]'si", $tpl->copy_template, $matches);
		
		while ($row = $db->get_row())
		{
			$replace = array(
							"{name}" => $row['name'],
							"{points}" => $row['points'],
							"{profile_url}" => $config['http_home_url'].($config['allow_alt_url'] == "yes")?"user/$row[name]/":"?subaction=userinfo&user=".$row['name'],
							"{user_url}" => GetUrl("user", $row['name'],false,false),
							);
							
			$tpl->copy_template = strtr($tpl->copy_template, $replace);
			$tpl->copy_template = preg_replace("'\[row\](.*?)\[/row\]'si", "\\1\n".$matches[0], $tpl->copy_template);
		}
		$tpl->set_block("'\[row\](.*?)\[/row\]'si", "");
		$tpl->compile('RatesUsers');
		$tpl->clear();
	 	$db->free();
	 	if ($total_conf['allow_cache'])
	 		create_cache('RatesUsers', $tpl->result['RatesUsers']);
	}
	else 
		$tpl->resilt['RatesUsers'] = $cache;
}

if ($total_conf['allow_PredictedMatche'])
	PredictedMatche();
	
if ($total_conf['allow_NotPredictedMatche'])
	NotPredictedMatche();
	
if ($total_conf['allow_RatesUsers'])
	RatesUsers();

?>
