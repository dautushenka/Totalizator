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

class admin_template
{
	var $line = "<div class=\"unterline\"></div>";
	var $calendar = 0;
	var $CountTD = array();
	var $CountTable = 0;
	var $ajax = FALSE;
	var $echo = TRUE;
	var $output = "";
	var $inclass = FALSE;
	var $head = TRUE;
	var $footer = TRUE;
	var $menu = '';
    var $year;
	
    function admin_template($year = null)
    {
        if (is_null($year))
        {
            $this->year = date('Y');
        }
        else
        {
            $this->year = $year;
        }
    }
    
	function header($header_text='', $head = false, $menu = false)
	{
		if ($this->head || $head)
			echoheader('', $header_text);
		if ($menu && $this->menu)
			return $this->show_return($this->menu);
	}
	
	function menu($action_array, $mod, $image_patch)
	{
		if (!is_array($action_array) || !count($action_array))
			return "";
		
		if ($this->menu)
			return $this->menu;
		else 
		{
			$this->inclass = TRUE;
			$this->OpenTable();
			$this->OTable();
			foreach ($action_array AS $name=>$value)
			{
				$td[] = "<a href=\"$mod&action=$value[action]\" title=\"$name\"><img src=\"$image_patch/$value[image]\" border=\"0\" /></a>";
			}
			$this->row($td, false);
			$this->CTable();
			$this->CloseTable();
			$this->inclass = FALSE;
			$this->menu = $this->output;
			$this->output = '';
		}
	}
	
	function stats($array)
	{
		if (!is_array($array) || !count($array))
			return "";
			
		$output = "";
		$this->inclass = TRUE;
		$this->OTable();

		foreach ($array as $desc=>$value)
		{
			$output .=<<<HTML
			<tr>
		        <td style="padding-top:2px;padding-bottom:2px;width:300px;">$desc</td>
		        <td>$value</td>
		    </tr>
HTML;
		}
		$this->show_return($output);
		$this->CTable();
		$this->inclass = FALSE;
		
		return $this->show_return();
	}
	
	function footer($footer = false)
	{
        $c_year = date('Y');
    
        if ($c_year != $this->year)
        {
            $this->year .= " - " . $c_year;
        }
    
		$output = <<<HTML
		<table width="100%">
		    <tr>
		        <td bgcolor="#EFEFEF" height="20" align="center" style="padding-right:10px;"><div class="navigation">Copyright © {$this->year} <a href="http://www.kaliostro.net" style="text-decoration:underline;color:green">kaliostro</a></div></td>
		    </tr>
		</table>
HTML;
		if ($this->footer || $footer)
		{
			$this->show_return($output);
			echofooter();
		}
		exit();
	}
	
	function OpenTable()
	{
		$output = <<<HTML
		<div style="padding-top:5px;padding-bottom:2px;">
		<table width="100%">
	    <tr>
	        <td width="4"><img src="engine/skins/images/tl_lo.gif" width="4" height="4" border="0"></td>
	        <td background="engine/skins/images/tl_oo.gif"><img src="engine/skins/images/tl_oo.gif" width="1" height="4" border="0"></td>
	        <td width="6"><img src="engine/skins/images/tl_ro.gif" width="6" height="4" border="0"></td>
	    </tr>
	    <tr>
	        <td background="engine/skins/images/tl_lb.gif"><img src="engine/skins/images/tl_lb.gif" width="4" height="1" border="0"></td>
	        <td style="padding:5px;" bgcolor="#FFFFFF">
HTML;
		$this->show_return($output);
	}
	
	function OpenSubtable($title='', $script="")
	{
		$output = <<<HTML
		<table width="100%" $script >
		    <tr>
		        <td bgcolor="#EFEFEF" height="29" style="padding-left:10px;"><div class="navigation">{$title}</div></td>
		    </tr>
		</table>
		<div class="unterline"></div>
		<table width="100%">
		<tr><td>
HTML;
		return $this->show_return($output);
	}
	
	function CloseSubtable($button=false)
	{
		if ($button) 
			$button = <<<HTML
<tr>
	<td style="padding-top:10px; padding-bottom:10px;padding-right:10px;"><input type="submit" class="buttons" value="$button"></td>
</tr>
HTML;
		$output = <<<HTML
			</td>
		</tr>
		$button
		</table>
HTML;
		return $this->show_return($output);
	}
	
	function CloseTable()
	{
		$output = <<<HTML
		</td>
	        <td background="engine/skins/images/tl_rb.gif"><img src="engine/skins/images/tl_rb.gif" width="6" height="1" border="0"></td>
	    </tr>
	    <tr>
	        <td><img src="engine/skins/images/tl_lu.gif" width="4" height="6" border="0"></td>
	        <td background="engine/skins/images/tl_ub.gif"><img src="engine/skins/images/tl_ub.gif" width="1" height="6" border="0"></td>
	        <td><img src="engine/skins/images/tl_ru.gif" width="6" height="6" border="0"></td>
	    </tr>
		</table>
		</div>
	
HTML;
		return $this->show_return($output);
	}
	
	function msg($title, $text, $back=false)
	{
		if ($back)
			$back = "<a href=\"$back\" >Вернуться назад</a>";
		$this->inclass = TRUE;
		if ($this->head)
			$this->header($title);
		$this->OpenTable();
		$this->OpenSubtable($title, "align=center");
		$this->OTable(array(), "style=\"text-align:center; padding:20px;\"", false);
		$this->row(array('height="100" align="center"' => $text . "<br />" . $back), false);
		$this->CTable();
		$this->CloseSubtable();
		$this->CloseTable();
		$this->inclass = FALSE;
		$this->show_return();
		$this->footer(true);
		exit();
	}
	
	function msg_yes_no($title, $text, $yes, $no='')
	{
		$this->inclass = TRUE;
		$this->OpenTable();
		$this->OpenSubtable($title);
		$this->OTable(array(), "style=\"text-align:center; padding:20px;\"", false);
		$this->OpenForm('', $yes);
		$this->row(array('height="100" align="center"' => $text."<br /><br /><input class=bbcodes type=submit value=\"Да\"> &nbsp; <input type=button class=bbcodes value=\"Нет\" onclick=\"javascript:document.location='$no'\">"), false);
		$this->CloseForm();
		$this->CTable();
		$this->CloseSubtable();
		$this->CloseTable();
		$this->inclass = FALSE;
		$this->show_return();
		$this->footer(true);
		exit();
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
      
        return $this->show_return($output);
	}
	
	function input($name, $value, $type = "text", $script = "")
	{
		if ($name == "")
			return false;
		$style = array( "text" => "edit",
						"submit" => "buttons",
						);
		if (isset($style[$type]))
			$style = " class=\"" . $style[$type] . "\" ";
		$output = "<input type=\"$type\" name=\"$name\" value=\"$value\"$style$script />";
		
		return $this->show_return($output);
	}
	
	function SettingRow($title="", $description="", $field="")
	{
		$output = "<tr>
		<td style=\"padding:4px\" class=\"option\">
	    <b>$title</b><br /><span class=small>$description</span>
	    <td width=394 align=middle >
	    $field
		</tr><tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=2></td></tr>";
		
		return $this->show_return($output);
	}
	
	function OpenForm($action = "", $hidden = array(), $script='')
	{
		$output = <<<HTML
		<form action="$action" method="POST" name="form" $script >
HTML;
		if (count($hidden))
		{
			foreach ($hidden as $name=>$value)
			{
				$output .= "<input type=\"hidden\" name=\"$name\" value=\"$value\"  />\n";
			}
		}
		
		return $this->show_return($output);
	}
	
	function CloseForm()
	{
		$output = "</form>";
		
		return $this->show_return($output);
	}
	
	function OTable($thead = array(), $script = "", $line = true)
	{
		$this->CountTable++;
		$output = "<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";
		if (count($thead))
		{
			$this->CountTD[$this->CountTable] = count($thead);
			$output .= "\n<tr>\n";
			foreach ($thead as $th)
			{
				$output .= "<td align=\"center\"><b>". $th . "</b></td>\n";
			}
			$output .= "</tr>\n";
			if ($line)
				$output .= "<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=\"{$this->CountTD[$this->CountTable]}\"></td></tr>";
		}
		
		return $this->show_return($output);
	}
	
	function row($td_array, $line=true, $split=false)
	{
		if (!count($td_array) && !$split)
			return false;
			
		if (!isset($this->CountTD[$this->CountTable]))
			$this->CountTD[$this->CountTable] = count($td_array);
			
		$output = "<tr>";
		if (!$split)
		{
			$i = 0;
			foreach ($td_array as $script=>$value)
			{
				if ($i >= $this->CountTD[$this->CountTable])
					break;
					
				if (is_numeric($script))
					$script = "align=\"center\"";
					
				$output .= "<td $script >".$value."</td>";
				$i++;
			}
		}
		else 
			$output .= "<td align=\"center\" colspan=\"{$this->CountTD[$this->CountTable]}\">". $td_array ."</td>";
		$output .= "</tr>";
		
		if ($line)
			$output .= "<tr><td background=\"engine/skins/images/mline.gif\" height=1 colspan=\"{$this->CountTD[$this->CountTable]}\"></td></tr>";
			
		return $this->show_return($output);
	}
	
	function CTable()
	{
		$output = "</table>";
		unset($this->CountTD[$this->CountTable--]);
		
		return $this->show_return($output);
	}
	
	function url($param, $url="")
	{
		if (count($param))
		{
			$i = 0;
			foreach ($param as $key=>$value)
			{
				if ($i != 0) $url .= "&";
				$url .= $key."=".$value;
				$i++;
			}
		}
		return $url;
		
		return $this->show_return($url);
	}
	
	function calendar($field)
	{
		global $lang;
		
		if (!$this->calendar)
		{
			$output = <<<HTML
				<link rel="stylesheet" type="text/css" media="all" href="engine/skins/calendar-blue.css" title="win2k-cold-1" />
				<script type="text/javascript" src="engine/skins/calendar.js"></script>
				<script type="text/javascript" src="engine/skins/calendar-en.js"></script>
				<script type="text/javascript" src="engine/skins/calendar-setup.js"></script>
HTML;
		}
		$output .= <<<HTML
			<img src="engine/skins/images/img.gif"  align="absmiddle" id="b_trigger_$this->calendar" style="cursor: pointer; border: 0" title="{$lang['edit_ecal']}"/>&nbsp;
			<script type="text/javascript">
			    Calendar.setup({
			        inputField     :    "$field",     // id of the input field
			        ifFormat       :    "%Y-%m-%d %H:%M",      // format of the input field
			        button         :    "b_trigger_$this->calendar",  // trigger for the calendar (button ID)
			        align          :    "Br",           // alignment
					timeFormat     :    "24",
					showsTime      :    true,
			        singleClick    :    true
			    });
			</script>
HTML;
		$this->calendar++;
		
		return $this->show_return($output);
	}
	
	function check_uncheck_all($formname, $master)
	{
		$output = <<<HTML
		<script language='JavaScript' type="text/javascript">
		<!--
		function ckeck_uncheck_all() {
		    var frm = document.$formname;
		    for (var i=0;i<frm.elements.length;i++) {
		        var elmnt = frm.elements[i];
		        if (elmnt.type=='checkbox') {
		            if(frm.$master.checked == true){ elmnt.checked=false; }
		            else{ elmnt.checked=true; }
		        }
		    }
		    if(frm.$master.checked == true){ frm.$master.checked = false; }
		    else{ frm.$master.checked = true; }
		}
		-->
		</script>
HTML;
		return $this->show_return($output);
	}
	
	function navigation($start, $per_page, $i, $all_count, $url = "")
	{
		if ($url == "")
			$url = "/?";
		else 
			$url .= "&";
			
		$npp_nav ="";

		if($start > 0)
		{
			$previous = $start - $per_page;
			$npp_nav .= "<a href=\"".$url."per_page=".$per_page."&start=".$previous."\">&lt;&lt; $lang[edit_prev]</a>";
		}
		
		if($all_count > $per_page)
		{
			$npp_nav .= " [ ";
		    $enpages_count = @ceil($all_count/$per_page);
		    $enpages_start_from = 0;
		    $enpages = "";
		    for($j=1;$j<=$enpages_count;$j++)
		    {
		    	if($enpages_start_from != $start)
		    	{
		    		$enpages .= "<a class=maintitle href=\"".$url."per_page=".$per_page."&start=".$enpages_start_from."\">$j</a> "; 
		    	}
				else
				{ 
					$enpages .= "<span class=navigation> $j </span>"; 
				}
		        $enpages_start_from += $per_page;
			}
			$npp_nav .= $enpages;
			$npp_nav .= " ] ";
		}
		
		if($all_count > $i)
		{
			$how_next = $all_count - $i;
		        if($how_next > $per_page){ $how_next = $per_page; }
		        $npp_nav .= "<a href=\"".$url."per_page=".$per_page."&start=".$how_next."\">$lang[edit_next] $how_next &gt;&gt;</a>";
		}
		
		return $this->show_return($npp_nav);
	}
	
	function show_return($echo = "")
	{
		if ($this->inclass)
			$this->output .= $echo;
		elseif ($this->echo)
		{
			echo $this->output.$echo;
			$this->output = '';
		}
		else 
		{
			$this->output = '';
			return $echo;
		}
			
		return false;
	}
}

$tpl = new admin_template(2008);

?>
