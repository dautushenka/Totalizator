<?php
/*
=====================================================
 Totalizator v1.0.0
-----------------------------------------------------
 http://kaliostro.net/
-----------------------------------------------------
 Copyright (c) 2007 kaliostro ICQ: 415-74-19
=====================================================
 Е о®»йЎЄп¤ иЎ№йєҐрЁѓ®р±ЄЁтЃ гЎ¬иЌЉ=====================================================
*/

if(!defined('DATALIFEENGINE'))
{
  die("Hacking attempt!");
}

define('AREA', 'USER');

include(ROOT_DIR . "/language/".$config['langs']."/totalizator.lng");
include_once(ENGINE_DIR . "/data/totalizator_conf.php");
include_once(ENGINE_DIR . "/totalizator/functions.php");
include(ENGINE_DIR . "/totalizator/totalizator.class.php");
include(ENGINE_DIR."/totalizator/version.php");

$total = new Totalizator($_TIME, $db);
$tournament = (intval($_REQUEST['tournament']))?intval($_REQUEST['tournament']):0;
$matche = (intval($_REQUEST['matche']))?intval($_REQUEST['matche']):0;
$komanda = ($_REQUEST['komanda'])?$_REQUEST['komanda']:0;
$action = ($_REQUEST['action'])?$_REQUEST['action']:'';
$edit = isset($_POST['edit'])?true:false;
$per_page = (intval($total_conf['per_page']))?intval($total_conf['per_page']):50;
$start = (intval($_REQUEST['cstart']))?intval($_REQUEST['cstart']):0;
if ($start)
{
	$start = $start - 1;
	$start = $start * $per_page;
}
$j = $start;
$stats = (intval($_REQUEST['stats']))?1:0;
$user_id = (intval($member_id['user_id']))?$member_id['user_id']:0;
$user = ($_REQUEST['user'])?$_REQUEST['user']:0;
$where = array();
$save = 0;
$name = '';
$uvalue = '';
$points_array['no'] = '';
$addition = '';

if (empty($total_conf['game_points']))
{
    $total_conf['game_points'] =  15;
}
for ($i=0; $i<=$total_conf['game_points']; $i++)
{
	$points_array[$i] = $i;
}
$title = $lang_total['title_main'];
$desc = $lang_total['desc_main'];



if (count($_POST['matches']) && $user_id && !$edit)
{
    if (isset($_POST['update']))
    {
        $total->UpdateRates($_POST['matches'], $user_id);
    }
    else
    {
        $total->CreatRate($_POST['matches'], $user_id);
    }
    @header('Location:' . $_SERVER['REQUEST_URI']);
}
if ($user)
{
	$id = $total->db->super_query("SELECT user_id FROM " . PREFIX . "_users WHERE name='".$total->db->safesql($user) . "'");
    if (isset($_GET['tor']))
    {
        $array = $total->GetRateByUserTor($id['user_id'], $start, $per_page, array("date_rate"=> "DESC"));
        $count_all = $total->GetCount(array("user_id"=>$id['user_id']), "rates_by_user_tor");
    }
    else
    {
        $array = $total->GetRate(array("user_id"=>$id['user_id']), $start, $per_page, array("date_rate"=> "DESC"));
        $count_all = $total->GetCount(array("user_id"=>$id['user_id']), "rates");
    }
	$name = "user"; $uvalue = $user;
}
elseif ($matche)
{
	$array = $total->GetRate(array("matche_id"=>$matche), $start, $per_page, array("date_rate"=> "DESC"));
	$name = "matche"; $uvalue = $matche;
	$count_all = $total->GetCount(array("matche_id"=>$matche), "rates");
}
elseif ($komanda)
{
	$komanda = $total->db->safesql(urldecode($komanda));
	$array = $total->GetMatcheUsers("(komanda1='$komanda' OR komanda2='$komanda')", $start, $per_page, array("calculate" => "ASC", "date_matche"=>"DESC"), $user_id);
	$name = "komanda"; $uvalue = $komanda;
	$count_all = $total->GetCount("(komanda1='$komanda' OR komanda2='$komanda')", "matches");
}
elseif ($stats)
{
    $name = "stats"; $uvalue = 1;

	if (!$total_conf['allow_cache'] || !($array = @unserialize(dle_cache('totalizator_stats_'.$start))))
	{
		$total->db->query("SELECT *, name AS username FROM " . PREFIX . "_users WHERE points!=0 ORDER BY points DESC LIMIT $start, $per_page");
		while ($row = $total->db->get_row())
		{
			$array[$row['user_id']] = $row;
		}
		
		if ($total_conf['allow_cache'])
			create_cache("totalizator_stats_".$start , @serialize($array));
	}
	if (!$total_conf['allow_cache'] || !($count_all = intval(dle_cache("totalizator_stats_count"))))
	{
		$count = $total->db->super_query("SELECT COUNT(*) AS count FROM " . PREFIX . "_users WHERE points!=0");
		$count_all = $count['count'];
		
		if ($total_conf['allow_cache'])
			create_cache("totalizator_stats_count" , $count_all);
	}
}
else 
{
	if ($tournament)
	{
		$where['tournament_id'] = $tournament;
	}

	switch ($action)
	{
		case "new_matche":
			$where['calculate'] = 0;
			$title = $lang_total['new_matche'];
			$desc = $lang_total['new_matche_desc'];
			break;
			
		case "old_matche":
			$where['calculate'] = 1;
			$title = $lang_total['old_matche'];
			$desc = $lang_total['old_matche_desc'];
			break;
	}

	$array = $total->GetMatcheUsers($where, $start, $per_page, array("calculate" => "ASC", "date_matche"=>"DESC"), $user_id);
	$count_all = $total->GetCount($where, "matches");
}

if (!$array)
	msgbox("Матчи", "Матчи или ставки не найдены");
else 
{
    if ($total_conf['show_archive'])
    {
        $tpl->load_template('totalizator/archive.tpl');
        
        $tpl->set('{link}', GetUrl('', '', false, false, false));
        
        if ($total_conf['alt_url'])
        {
            $tpl->set('{link_archive}', GetUrl('', '', false, false, false) . "?archive");
        }
        else
        {
            $tpl->set('{link_archive}', GetUrl('', '', false, false, false) . "&archive");
        }

        $tpl->compile('content');
    }

	$tpl->load_template("totalizator/totalizator.tpl");
	
	if ($matche || $stats || $user)
	{
		$tpl->set_block('#\[matches\](.*?)\[/matches\]#si', "");
		$tpl->set_block('#\[rates\](.*?)\[/rates\]#si', "\\1");
		$tpl->set_block("#\[user_page\](.*?)\[/user_page\]#si", "");
        
		foreach ($array as $value)
		{
			$j++;
			preg_match("'\[rates_row\](.*?)\[/rates_row\]'si", $tpl->copy_template, $matches);
			$point = ($total_conf['allow_view_points'] || $value['calculate'])?$value['rpoints_1'] ." - " . $value['rpoints_2']:$lang_total['wait_result']; 
			if ($matche || $user)
			{
                if ($user && isset($_GET['tor']))
                {
                    $point = $value['name'];
                    $points = $value['points'];
                }
                else
                {
                    if ($value['calculate'])
                    {
                        $points = "+".GetPoint($value['mpoints_1'] ,$value['mpoints_2'], $value['rpoints_1'], $value['rpoints_2']);
                        
                        if ($user)
                        {
                            $point = $value['komanda1'] . " " . $value['rpoints_1'] . " - " . $value['rpoints_2'] . " " . $value['komanda2'];
                        }
                    }
                    else
                    {
                        $points = $lang_total['wait_result']; 
                        
                        if ($user)
                        {
                            $point = $value['komanda1'] . " " . (($total_conf['allow_view_points'])?$value['rpoints_1'] ." - " . $value['rpoints_2']:'') . " " . $value['komanda2'];
                        }
                    }
                }   
                
			}
			else
			{
                $points = $value['points'];
            }

			$replace = array(
							"{user}" => "<a ".GetUrl("user", $value['username'])." >".$value['username']."</a>",
							"{point}" => $point,
							"{points}" => $points,
							"{i}" => $j
							);
			$tpl->copy_template = strtr($tpl->copy_template, $replace);
			$tpl->copy_template = preg_replace("'\[rates_row\](.*?)\[/rates_row\]'si", "\\1\n".$matches[0], $tpl->copy_template);
		}
		if ($matche)
		{
			$tpl->set("{desc}", $lang_total['desc_matche']);
			$tpl->set("{title}", $value['komanda1'] . " " . $value['mpoints_1'] . " - " . $value['mpoints_2'] . " " . $value['komanda2']);
			$tpl->set_block("#\[rate\](.*?)\[/rate\]#si", "\\1");
			$tpl->set_block("#\[user\](.*?)\[/user\]#si", "\\1");
		}
		elseif ($user)
		{
			$tpl->set("{desc}", $lang_total['desc_user']); 
			$tpl->set("{title}", $user); 
			$tpl->set("{matche_link}", GetUrl("user", $user, false, false)); 
            
            if ($total_conf['alt_url'])
            {
                $tpl->set("{tor_link}", GetUrl("user", $user, false, false) . (isset($_GET['archive'])?'&':'?') . 'tor'); 
            }
            else
            {
                $tpl->set("{tor_link}", GetUrl("user", $user, false, false) . '&tor'); 
            }
            
			$tpl->set_block("#\[rate\](.*?)\[/rate\]#si", "\\1");
			$tpl->set_block("#\[user_page\](.*?)\[/user_page\]#si", "\\1");
			$tpl->set_block("#\[user\](.*?)\[/user\]#si", "");
            
            if (isset($_GET['tor']))
            {
                $addition = (isset($_GET['archive'])?'&':'?') . "tor";
            }
		}
		else
		{
			$tpl->set("{desc}", $lang_total['desc_stats']); 
			$tpl->set("{title}", $lang_total['title_stats']); 
			$tpl->set_block("#\[rate\](.*?)\[/rate\]#si", "");
			$tpl->set_block("#\[user\](.*?)\[/user\]#si", "\\1");
		}
		
		$tpl->set_block("'\[rates_row\](.*?)\[/rates_row\]'si", "");
	}
	else
	{	
        $tpl->set_block("#\[edit\](.*?)\[/edit\]#si", "");
        $tpl->set_block("#\[save\](.*?)\[/save\]#si", "");
            
		preg_match("'\[mat_row\](.*?)\[/mat_row\]'si", $tpl->copy_template, $matches);
		
        $e = false;
		foreach ($array as $value)
		{
			$j++;
			if ((($_TIME + intval($total_conf['time'])*60) < $value['date_matche']) && ($value['user_id'] == "" || $total_conf['allow_edit'] && $edit) && $user_id)
			{
                if ($value['user_id'])
                {
                    $points = selection($points_array, "matches[$value[matche_id]][points_1]", (int)$value['rpoints_1'])." - " . selection($points_array, "matches[$value[matche_id]][points_2]", (int)$value['rpoints_2']);
                }
                else
                {
                    $points = selection($points_array, "matches[$value[matche_id]][points_1]")." - " . selection($points_array, "matches[$value[matche_id]][points_2]");
                }
				
				$save++;
			}
			elseif ($value['calculate'])
				$points = $value['mpoints_1'] . " - " . $value['mpoints_2'];
			elseif (!$user_id)
				$points = "<a href=\"{$config['http_home_url']}?do=register\" >{$lang_total['registration']}</a>";
			else
            {
				$points = $lang_total['wait_result'];
                if (($_TIME + intval($total_conf['time'])*60) < $value['date_matche'])
                {
                    $e = true;
                }
            }
			
			$replace = array(
							"{date}" => "<a ".GetUrl("matche", $value['matche_id'])." />".date("d-m-Y H:i", $value['date_matche'])."</a>",
							"{tournament}" => "<a ".GetUrl("tournament", array("id"=>$value['tournament_id'], "alt_name" => $value['alt_name']))." >".$value['name']."</a>",
							"{komanda1}" => "<a ".GetUrl("komanda", urlencode($value['komanda1']))." >".$value['komanda1']."</a>",
							"{komanda2}" => "<a ".GetUrl("komanda", urlencode($value['komanda2']))." >".$value['komanda2']."</a>",
							"{points}" => $points,
							"{rates}" => $value['rates'],
							"{procent}" => ($value['calculate'] && $value['rates'])?round($value['rates_right']/$value['rates']*100)."%":$lang_total['wait_result'], 
							);
                            
            if ($total_conf['allow_rates'])
                $tpl->copy_template = preg_replace("#\[rate\](.*?)\[/rate\]#si", "\\1", $tpl->copy_template);
            else 
                $tpl->copy_template = preg_replace("#\[rate\](.*?)\[/rate\]#si", "", $tpl->copy_template);
                
            if ($total_conf['allow_procent'])
                $tpl->copy_template = preg_replace("#\[procent\](.*?)\[/procent\]#si", "\\1", $tpl->copy_template);
            else 
                $tpl->copy_template = preg_replace("#\[procent\](.*?)\[/procent\]#si", "", $tpl->copy_template);
                
            if ($tournaments)
                $tpl->copy_template = preg_replace("#\[tournament\](.*?)\[/tournament\]#si", "\\1", $tpl->copy_template);
            else 
                $tpl->copy_template = preg_replace("#\[tournament\](.*?)\[/tournament\]#si", "", $tpl->copy_template);
                            
			$tpl->copy_template = strtr($tpl->copy_template, $replace);
			$tpl->copy_template = preg_replace("'\[mat_row\](.*?)\[/mat_row\]'si", "\\1\n".$matches[0], $tpl->copy_template);
		}
		if ($save || $total_conf['allow_edit'])
		{
            if ($edit)
            {
                $tpl->copy_template .= '<input type="hidden" name="update" value="1" />';
            }
        
			$tpl->copy_template = "<form action=\"\" method=\"POST\" >".$tpl->copy_template . "</form>";
            
            if ($save)
			{
                $tpl->set_block("#\[save\](.*?)\[/save\]#si", "\\1");
            }
            
            if (!$edit && $e && $total_conf['allow_edit'])
            {
                $tpl->set_block("#\[edit\](.*?)\[/edit\]#si", "\\1");
            }
		}
        
		if ($komanda)
		{
			$tpl->set("{title}", $komanda);
			$tpl->set("{desc}", $lang_total['desc_komanda']);
		}
		elseif ($tournament)
		{
			$tpl->set("{desc}", $value['description']);
			$name = "tournament"; $uvalue = array("id" => $tournament, "alt_name" => $value['alt_name']);
			$tpl->set("{title}", $value['name']);
		}
		else 
		{
			$tpl->set("{title}", $title);
			$tpl->set("{desc}", $desc);
		}
        
		
		
		$tpl->set_block("#\[mat_row\](.*?)\[/mat_row\]#si", "");
		$tpl->set_block('#\[matches\](.*?)\[/matches\]#si', "\\1");
		$tpl->set_block('#\[rates\](.*?)\[/rates\]#si', "");
	}
	
    if (isset($_GET['archive']))
    {
        $tpl->data['{title}'] .= " (PЦ€В©";
    }
    
	$tpl->compile("content");
}

navigation($start, $per_page, $j, $count_all, $name, $uvalue, $addition);
?>
