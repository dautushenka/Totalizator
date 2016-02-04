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

if (!function_exists('array_map_recursive'))
{
	function array_map_recursive($function,$data)
	{
		foreach ($data as $i=>$item)
				$data[$i]=is_array($item) ? array_map_recursive($function,$item) : $function($item);
		return $data ;
	}
}

if (!function_exists('array_intersect_key'))
{
	function array_intersect_key($array1, $array2)
	{
		foreach ($array1 as $key=>$value)
		{
			if (!array_key_exists($key, $array2))
				unset($array1[$key]);
		}
		return $array1;
	}
}

function GetPoint($mpoints_1, $mpoints_2, $rpoints_1, $rpoints_2)
{
	global $total_conf;
	
	if ($mpoints_1 == "" || $mpoints_2 == "" || $rpoints_1 == "" || $rpoints_2 == "")
		return 0;
		
	if ($mpoints_1 == $rpoints_1 && $mpoints_2 == $rpoints_2)
		$point = $total_conf['point_3'];
	elseif (($rpoints_1 - $rpoints_2) == ($mpoints_1 - $mpoints_2))
		$point = $total_conf['point_2'];
	elseif (!($mpoints_1 > $mpoints_2 xor $rpoints_1 > $rpoints_2) && $mpoints_1 != $mpoints_2 && $rpoints_1 != $rpoints_2)
		$point = $total_conf['point_1'];
	else 
		$point = $total_conf['point_0'];
		
	return $point;
}

function selection($options=array(), $name = "", $selected = "", $script = "")
{
	if (!count($options) || $name == "") return false;
	$output =  "<select name=\"$name\" $script >\r\n";
	foreach($options as $value=>$description)
	{
		$output .= "<option value=\"$value\"";
        if($selected === $value){ $output .= " selected "; }
        $output .= ">$description</option>\n";
	}
    $output .= "</select>";
        
    return $output;
}

function GetUrl($name, $value, $page = false, $href=true, $archive = true)
{
	global $config, $total_conf, $PHP_SELF;
	
	switch ($name)
	{
		case "tournament":
			$url_alt = "tournament-".$value['id']."-".$value['alt_name'];
			$url = "tournament=".$value['id'];
			break;
		case "matche":
			$url_alt = "matche-".$value;
			$url = "matche=".$value;
			break;
		case "komanda":
			$url_alt = "komanda/".$value;
			$url = "komanda=".$value;
			break;
		case "user":
			$url_alt = "users/".urlencode($value);
			$url = "user=".urlencode($value);
			break;
		case "stats":
			$url_alt = "stats";
			$url = "stats=1";
	}
	if ($page && $url)
	{
		$url_alt .= "/page-".$page;
		$url .= "&cstart=".$page;
	}
	elseif ($page)
	{
		$url_alt = "page-" . $page;
		$url = "cstart=" . $page;
	}
    
    if (empty($url_alt))
    {
        $url_alt = "totalizator";
    }
    
    $url_alt .= ".html";
    
    if (AREA == 'USER' && isset($_GET['archive']) && $archive)
    {
        $url_alt .= "?archive";
        $url .= "archive";

        if ($type == 'user' && isset($_GET['tor']))
        {
            $url_alt .= '&tor';
            $url .= "&tor";
        }
    }
    else if ($type == 'user' && isset($_GET['tor']))
    {
        $url_alt .= '?tor';
        $url .= "tor";
    }

	if ($total_conf['alt_url'])
	{
		if ($href)
		{
            $output = "href=\"".$config['http_home_url'].$url_alt ."\"";
        }
		else 
		{
            $output = $config['http_home_url'].$url_alt;
        }
	}
	else 
	{
		if ($href)
			$output = "href=\"".$PHP_SELF."?do=totalizator&".$url."\"";
		else 
			$output = $PHP_SELF."?do=totalizator&".$url;
	}
	if ($config['ajax'] && $href)
    {
		$output = "onclick=\"DlePage('do=totalizator&$url'); return false;\" ".$output;
    }
		
	return $output;
}

function navigation($cstart, $per_page, $i, $count_all, $name, $value, $addition = '')
{
	global $view_template, $tpl;
	
	if (!isset($view_template))  $tpl->load_template('navigation.tpl');

	$no_prev = false; 
	$no_next = false;

    if(isset($cstart) and $cstart != "" and $cstart > 0){
        $prev = $cstart / $per_page;
        
        $tpl->set_block("'\[prev-link\](.*?)\[/prev-link\]'si", "<a ".GetUrl($name, $value, $prev). $addition . " >\\1</a>");

    }else{ $tpl->set_block("'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>"); $no_prev = TRUE; }

	if($per_page){

	$pages_count = @ceil($count_all/$per_page);
	$pages_start_from = 0;
	$pages = "";
	$pages_per_section = 3;
	if($pages_count > 10)
    {
            for($j = 1; $j <= $pages_per_section; $j++)
            {
               if($pages_start_from != $cstart)
               {
							$pages .= "<a ".GetUrl($name, $value, $j). $addition . " >$j</a> ";
                } else
                {
                     $pages .= " <span>$j</span> ";
                }

				$pages_start_from += $per_page;
             }

             if(((($cstart / $per_page) + 1) > 1) && ((($cstart / $per_page) + 1) < $pages_count))
             {
               $pages   .= ((($cstart / $per_page) + 1) > ($pages_per_section + 2)) ? '... ' : ' ';
               $page_min = ((($cstart / $per_page) + 1) > ($pages_per_section + 1)) ? ($cstart / $per_page) : ($pages_per_section + 1);
               $page_max = ((($cstart / $per_page) + 1) < ($pages_count - ($pages_per_section + 1))) ? (($cstart / $per_page) + 1) : $pages_count - ($pages_per_section + 1);

               $pages_start_from = ($page_min - 1) * $per_page;

                     for($j = $page_min; $j < $page_max + ($pages_per_section - 1); $j++)
                         {
                           if($pages_start_from != $cstart)
								$pages .= "<a ".GetUrl($name, $value, $j). $addition . " >$j</a> ";
                            else
                               $pages .= " <span>$j</span> ";

                            $pages_start_from += $per_page;

                          }

                           $pages .= ((($cstart / $per_page) + 1) < $pages_count - ($pages_per_section + 1)) ? '... ' : ' ';

                        }
                        else
                        {
                                $pages .= '... ';
                        }

                        $pages_start_from = ($pages_count - $pages_per_section) * $per_page;

                        for($j=($pages_count - ($pages_per_section - 1)); $j <= $pages_count; $j++)
                        {
                                if($pages_start_from != $cstart)
									$pages .= "<a ".GetUrl($name, $value, $j). $addition . ">$j</a> ";
                                else
                                    $pages .= " <span>$j</span> ";
                                $pages_start_from += $per_page;
                        }

                }
                else
                {
                        for($j=1;$j<=$pages_count;$j++)
                        {
                                if($pages_start_from != $cstart)
									$pages .= "<a ".GetUrl($name, $value, $j). $addition . " >$j</a> ";
                                else
	                                $pages .= " <span>$j</span> ";

                                $pages_start_from += $per_page;
                        }
                }
                $tpl->set('{pages}', $pages);
        }


    if($per_page < $count_all and $i < $count_all){
		$next_page = $i / $per_page + 1;

		 $tpl->set_block("'\[next-link\](.*?)\[/next-link\]'si", "<a ".GetUrl($name, $value, $next_page). $addition . ">\\1</a>");

    }else{ $tpl->set_block("'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>"); $no_next = TRUE;}

	if  (!$no_prev OR !$no_next){ $tpl->compile('content'); }

	$tpl->clear();
}

function YesNo($value)
{
	global $tpl, $total_conf;
	
	return $tpl->selection(array(0=>"Нет", 1=>"Да"), "save_con[$value]", intval($total_conf[$value]));
}

function save_conf($save_con, $array=false)
{
	global $handler, $find, $replace;
    	
    foreach($save_con as $name => $value)
    {
    	if (is_array($value))
    	{
    		fwrite($handler, "'{$name}' => array (\n\n"); save_conf($value, true);
    	}
    	else
    	{
    		$value = strtr($value, '"', "'");
   			fwrite($handler, "'{$name}' => \"".stripslashes($value)."\",\n\n");
    	}
    }
    if ($array) fwrite($handler, "),\n\n");
}

function profileInfo($row)
{
    global $tpl, $db;
    
    $tpl->set('{points}', $row['points']);
    
    $count = $db->super_query('SELECT COUNT(*) as count FROM ' . USERPREFIX . "_users WHERE points!=0 AND points>" . $row['points']);
    
    $tpl->set('{rank}', $count['count'] + 1);
}
?>
