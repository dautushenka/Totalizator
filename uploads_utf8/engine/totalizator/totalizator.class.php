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

define('LIC_DOMAIN', /*lic*/'.'/*/lic*/);

if (
    !preg_match("#" . LIC_DOMAIN . "#i", $_SERVER['HTTP_HOST']) &&
    !preg_match('#localhost#i', $_SERVER['HTTP_HOST']) &&
    strpos($_SERVER['HTTP_HOST'], $_SERVER['SERVER_ADDR']) === false
    )
{
    @header("Content-type: text/html; charset=" . $config['charset']);
    echo "Вы используете не лицензионную версию модуля Totalizator.<br/>";
    echo "За информацией обращайтесь на форум <a href='http://forum.kaliostro.net/'>http://forum.kaliostro.net/</a><br/>";
    echo "You are not using licensed version of the module Totalizator.<br/>";
    echo "For information, visit the forum <a href='http://forum.kaliostro.net/'>http://forum.kaliostro.net/</a>";
    exit(); 
}

class Totalizator
{
	protected $tournaments_fields = array( "tournament_id"=> 'int',
                                            "name" 			=> 'string',
                                            "alt_name" 		=> 'string', 
                                            "description" 	=> 'string');
	protected $matches_fields = array( 	"matche_id"		=> 'int',
                                        "tournament_id" => 'int',
                                        "date_matche" 	=> 'int',
                                        "komanda1" 		=> 'string',
                                        "komanda2" 		=> 'string',
                                        "points_1" 		=> 'int',
                                        "points_2" 		=> 'int',
                                        "rates" 		=> 'int',
                                        "rates_right" 	=> 'int',
                                        "calculate" 	=> 'int'
									);
	protected $rates_fields = array(	"user_id" 		=> "int",
                                        "matche_id" 	=> 'int',
                                        "points_1" 		=> 'int',
                                        "points_2" 		=> 'int',
                                        "date_rate" 	=> 'int'
									);
	protected $array_type = array();
	protected $IdNameTournament = array();
	public $db;
	protected $time;
    
    protected $_archive;
	
	public function __construct($time, &$db)
	{
		$this->time = $time;
		$this->db =& $db;
        
        $this->_archive = isset($_GET['archive'])?true:false;
	}
	
	public function GetIdNameTournament()
	{
		if (count($this->IdNameTournament))
			return $this->IdNameTournament;
		
		$this->db->query("SELECT tournament_id, name FROM ". PREFIX . "_tournaments WHERE archive=0");
		while ($row = $this->db->get_row())
		{
			$this->IdNameTournament[$row['tournament_id']] = $row['name'];
		}
		
		return $this->IdNameTournament;
	}
	
	public function GetTournament($where = '', $start = 0, $limit = 0, $order='')
	{
		$this->array_type = $this->tournaments_fields;
		
		if (intval($limit) == 1)
			return $this->db->super_query("SELECT * FROM " . PREFIX . "_tournaments".$this->GetWhere($where).$this->GetLimit($start, $limit));
		else 
		{
			$this->db->query("SELECT * FROM " . PREFIX . "_tournaments".$this->GetWhere($where).$this->GetOrder($order).$this->GetLimit($start, $limit));
			while ($row = $this->db->get_row())
			{
				$tournaments[$row['tournament_id']] = $row;
			}
			return $tournaments;
		}
	}
	
	public function CreatTournament($name, $alt_name, $description = "")
	{
		if ($name == "" || $alt_name == "")
			return false;
		$name = $this->doescape($name, "string");
		$alt_name = $this->doescape($alt_name, "string");
		$description = $this->doescape($description, "string");
		$this->db->query("INSERT INTO " . PREFIX . "_tournaments (name, alt_name, description) VALUES ('$name', '$alt_name', '$description')");
		return $this->db->insert_id();
	}
	
	public function UpdateTournament($array_set, $where)
	{
		$this->array_type = $this->tournaments_fields;
		
		if ($set = $this->GetSet($array_set))
		{
			$this->db->query("UPDATE " . PREFIX . "_tournaments SET ".$set.$this->GetWhere($where));
			return $this->affected();
		}
		else 
			return false;
	}
	
	public function DeleteTournament($where)
	{	
		if ($where['tournament_id'])
		{
			$this->DeleteMatche(array("tournament_id" => $where['tournament_id']));
		}
		else 
		{
			if ($tournaments = $this->GetTournament($where))
			{
				foreach ($tournaments as $tournament)
				{
					$id[] = $tournament['tournament_id'];
				}
				if ($id)
					$this->DeleteMatche("tournament_id IN ('".implode("', '", $id)."')");
			}
		}
		
		$this->array_type = $this->tournaments_fields;
		$this->db->query("DELETE FROM " . PREFIX . "_tournaments".$this->GetWhere($where));
	
		return true;
	}
	
	public function GetMatches($where = '', $start = 0, $limit = 0, $order='')
	{
		$this->array_type = $this->matches_fields;
        $this->array_type['archive'] = 'int';
		
		if (intval($limit) == 1)
			return $this->db->super_query("SELECT * FROM " . PREFIX . "_matches AS m
											LEFT JOIN " . PREFIX ."_tournaments AS t
											ON t.tournament_id=m.tournament_id "
											.$this->GetWhere($where, "m").$this->GetLimit($start, $limit));
		else 
		{
			$this->db->query("SELECT * FROM " . PREFIX . "_matches AS m
											LEFT JOIN " . PREFIX ."_tournaments AS t
											ON t.tournament_id=m.tournament_id "
											.$this->GetWhere($where, "m").$this->GetOrder($order).$this->GetLimit($start, $limit));
			while ($row = $this->db->get_row())
			{
				$matches[$row['matche_id']] = $row;
			}
			return $matches;
		}
	}
	
	public function GetMatcheUsers($where = '', $start = 0, $limit = 0, $order='', $user_id)
	{
		$this->array_type = $this->matches_fields;
		$user_id = intval($user_id);
        
		if (empty($user_id))
		{
            $user_id = 0;
        }
        
		if (intval($limit) == 1)
        {
			return $this->db->super_query("SELECT m.*, t.*, r.user_id, r.points_1 AS rpoints_1, r.points_2 AS rpoints_2, m.points_1 AS mpoints_1, m.points_2 AS mpoints_2 FROM " . PREFIX . "_matches AS m
											LEFT JOIN " . PREFIX ."_tournaments AS t
											ON t.tournament_id=m.tournament_id 
											LEFT OUTER JOIN" .PREFIX . "_rates AS r 
											ON r.matche_id=m.matche_id AND r.user_id='$user_id' "
											.$this->GetWhere($where, "m").$this->GetLimit($start, $limit));
        }
		else 
		{
			$this->db->query("SELECT m.*, t.*, r.user_id, r.points_1 AS rpoints_1, r.points_2 AS rpoints_2, m.points_1 AS mpoints_1, m.points_2 AS mpoints_2 FROM " . PREFIX . "_matches AS m
											LEFT JOIN " . PREFIX ."_tournaments AS t
											ON t.tournament_id=m.tournament_id 
											LEFT JOIN " .PREFIX . "_rates AS r 
											ON r.matche_id=m.matche_id AND r.user_id='$user_id' "
											.$this->GetWhere($where, "m").$this->GetOrder($order).$this->GetLimit($start, $limit));
			while ($row = $this->db->get_row())
			{
				$matches[$row['matche_id']] = $row;
			}
			
			return $matches;
		}
	}
	
	public function CreatMatche($date, $tournament_id, $komanda1, $komanda2)
	{
		$this->array_type = $this->matches_fields;
		
		$value = array(
						"tournament_id" => $tournament_id,
						"date_matche" => $date,
						"komanda1" => $komanda1,
						"komanda2" => $komanda2,
						);
						
		$this->escape($value);
		
		foreach ($value as $val)
		{
			if (!$val)
				return false;
		}
		if ($value)
			$this->db->query("INSERT INTO " . PREFIX . "_matches (".implode(", ", array_keys($value)) .") VALUES ('".implode("', '", $value) ."')");
		
		return $this->db->insert_id();
	}
	
	public function UpdateMatche($array_set, $where)
	{
		$this->array_type = $this->matches_fields;
		
		if ($set = $this->GetSet($array_set))
		{
			$this->db->query("UPDATE " . PREFIX . "_matches SET ".$set.$this->GetWhere($where));
			return true;
		}
		else 
			return false;
	}
	
	public function DeleteMatche($where)
	{
		if ($where['matche_id'])
		{
			$this->DeleteRate(array("matche_id"=>$where['matche_id']));
		}
		else 
		{
			if ($matches = $this->GetMatches($where))
			{
				foreach ($matches as $mat)
				{
					$id[] = $mat['matche_id'];
				}
				if ($id)
					$this->DeleteRate("matche_id IN ('" . implode("', '" , $id) ."')");
			}
		}
		
		$this->array_type = $this->matches_fields;
		$result = $this->db->query("DELETE FROM " . PREFIX . "_matches".$this->GetWhere($where));
		
		return $result;
	}
	
	public function GetRate($where = '', $start = 0, $limit = 0, $order='')
	{
		$this->array_type = $this->rates_fields;
		
		if (intval($limit) == 1)
        {
			return $this->db->super_query("SELECT *, u.name AS username, t.name AS tname, r.points_1 AS rpoints_1, r.points_2 AS rpoints_2, m.points_1 AS mpoints_1, m.points_2 AS mpoints_2 FROM " . PREFIX . "_rates AS r
										LEFT JOIN ". PREFIX . "_matches AS m
										ON m.matche_id=r.matche_id
										LEFT JOIN " . PREFIX . "_tournaments AS t
										ON t.tournament_id=m.tournament_id 
										LEFT OUTER JOIN " . PREFIX . "_users AS u
										ON u.user_id=r.user_id ".
										$this->GetWhere($where, "r").$this->GetLimit($start, $limit));
        }
		else 
		{
			$this->db->query("SELECT *, u.name AS username, t.name AS tname, r.points_1 AS rpoints_1, r.points_2 AS rpoints_2, m.points_1 AS mpoints_1, m.points_2 AS mpoints_2 FROM " . PREFIX . "_rates AS r
										LEFT JOIN ". PREFIX . "_matches AS m
										ON m.matche_id=r.matche_id
										LEFT JOIN " . PREFIX . "_tournaments AS t
										ON t.tournament_id=m.tournament_id 
										LEFT OUTER JOIN " . PREFIX . "_users AS u
										ON u.user_id=r.user_id ".
										$this->GetWhere($where, "r").$this->GetOrder($order).$this->GetLimit($start, $limit));
			while ($row = $this->db->get_row())
			{
				$rates[] = $row;
			}
		
			return $rates;
		}
	}
	
	public function CreatRate($insert, $user_id)
	{
        global $total_conf;
    
		$this->array_type = $this->rates_fields;
		
		foreach ($insert as $matche_id=>$val)
		{
            $matche_id = intval($matche_id);
            
            $m = $this->db->super_query("SELECT * FROM " . PREFIX . "_matches WHERE matche_id=" . $matche_id);
            
            if (!$m || ($this->time + intval($total_conf['time'])*60) >= $m['date_matche']) {
                continue;
            }
        
			if ($user_id && $val['points_1'] != "no" && $val['points_2'] != "no" && $matche_id)
			{
				$value = array(
								"matche_id" => (int)$matche_id,
								"user_id" => (int)$user_id,
								"points_1" => (int)$val['points_1'],
								"points_2" => (int)$val['points_2'],
								"date_rate" => $this->time
								);
								
				$this->escape($value);

				$matches[] = (int)$value['matche_id'];
				$values[] = "('".implode("', '" , $value)."')";
			}
		}
		if ($value && $matches)
		{
			$this->db->query("INSERT INTO " . PREFIX . "_rates (".implode(", ", array_keys($value)).") VALUES " . implode(", ", $values));
			$this->db->query("UPDATE " . PREFIX . "_matches SET rates=rates+1 WHERE matche_id IN ('" . implode("', '", $matches) . "') AND date_matche > " . $this->time);
		}
		
		return true;
	}
    
    public function UpdateRates($rates, $user_id)
    {
        global $total_conf;
    
        $this->array_type = $this->rates_fields;
        
        foreach($rates as $matche_id=>$val)
        {
            $matche_id = intval($matche_id);
            
            $m = $this->db->super_query("SELECT * FROM " . PREFIX . "_matches WHERE matche_id=" . $matche_id);
            
            if (!$m || ($this->time + intval($total_conf['time'])*60) >= $m['date_matche']) {
                continue;
            }
        
            if ($user_id && $val['points_1'] != "no" && $val['points_2'] != "no" && $matche_id)
            {
                $value = array(
								"matche_id" => $matche_id,
								"user_id" => $user_id,
								"points_1" => $val['points_1'],
								"points_2" => $val['points_2'],
								"date_rate" => $this->time
								);
								
				$this->escape($value);
                
                if ($this->db->super_query('SELECT * FROM ' . PREFIX . "_rates WHERE user_id=$user_id && matche_id=" . $value['matche_id']))
                {
                    $this->db->query('UPDATE ' . PREFIX . "_rates SET points_1={$value['points_1']}, points_2={$value['points_2']} WHERE user_id=$user_id AND matche_id={$value['matche_id']}");
                }
                else
                {
                    $this->db->query("INSERT INTO " . PREFIX . "_rates (".implode(", ", array_keys($value)).") VALUES (" . implode(", ", $value) . ")");
                    $this->db->query("UPDATE " . PREFIX . "_matches SET rates=rates+1 WHERE matche_id=" . $value['matche_id']);
                }
            }
        }
    }
	
	public function DeleteRate($where)
	{
		$this->array_type = $this->rates_fields;
		$result = $this->db->query("DELETE FROM " . PREFIX . "_rates".$this->GetWhere($where));
		return $result;
	}
	
	public function GetCount($where, $type)
	{
       
		switch ($type)
		{
			case "tournaments":
				$count = $this->db->super_query("SELECT COUNT(*) AS count FROM " . PREFIX . "_tournaments" . $this->GetWhere($where));
				break;
				
			case "matches":
				$count = $this->db->super_query("SELECT COUNT(*) AS count FROM " . PREFIX . "_matches AS m
                                                 LEFT JOIN " . PREFIX . "_tournaments AS t
                                                 ON t.tournament_id=m.tournament_id
                                                 " . $this->GetWhere($where, 'm'));
				break;
				
			case "rates":
				$count = $this->db->super_query("SELECT COUNT(*) AS count FROM " . PREFIX . "_rates AS r
                                                 LEFT JOIN " . PREFIX . "_matches AS m
                                                 ON m.matche_id=r.matche_id  
                                                 LEFT JOIN " . PREFIX . "_tournaments AS t
                                                 ON t.tournament_id=m.tournament_id
                                                 " . $this->GetWhere($where, 'r'));
				break;
                
            case 'rates_by_user_tor':
                $res = $this->db->query("SELECT t.* FROM " . PREFIX . "_rates AS r
                                                 LEFT JOIN " . PREFIX . "_matches AS m
                                                 ON m.matche_id=r.matche_id  
                                                 LEFT JOIN " . PREFIX . "_tournaments AS t
                                                 ON t.tournament_id=m.tournament_id
                                                 " . $this->GetWhere($where, 'r') . " GROUP BY m.tournament_id");
                                                 
                $count['count'] = $this->db->num_rows($res);
                break;
				
			default:
				return false;
				break;
		}
		return $count['count'];
	}
    
    public function GetRateByUserTor($user_id, $start, $limit, $order)
    {
        $this->array_type = $this->rates_fields;
    
        $where = array('user_id' => $user_id);
    
        $this->db->query("SELECT t.* FROM " . PREFIX . "_rates AS r
										LEFT JOIN ". PREFIX . "_matches AS m
										ON m.matche_id=r.matche_id
										LEFT JOIN " . PREFIX . "_tournaments AS t
										ON t.tournament_id=m.tournament_id".
										$this->GetWhere($where, "r"). " GROUP BY m.tournament_id" . $this->GetOrder($order).$this->GetLimit($start, $limit));

        $tids = array(); $tnames = array();
        while($row = $this->db->get_row())
        {
            $tids[] = $row['tournament_id'];
            $tnames[$row['tournament_id']] = $row['name'];
        }
        
        if ($tids)
        {
            $this->db->query('SELECT m.tournament_id, r.points_1 AS rpoints_1, r.points_2 AS rpoints_2, m.points_1 AS mpoints_1, m.points_2 AS mpoints_2 FROM ' . PREFIX . '_rates AS r
                              LEFT JOIN ' . PREFIX . '_matches AS m
                              ON m.matche_id=r.matche_id
                              WHERE m.tournament_id IN (' . implode(",", $tids) . ') AND r.user_id=' . $user_id);
            
            $rates = array();
            while ($row = $this->db->get_row())
            {
                if(empty($rates[$row['tournament_id']]))
                {
                    $rates[$row['tournament_id']] = array('name' => $tnames[$row['tournament_id']], 'points' => 0);
                }
            
                $rates[$row['tournament_id']]['points'] += GetPoint($row['mpoints_1'] ,$row['mpoints_2'], $row['rpoints_1'], $row['rpoints_2']);
            }
        
            return $rates;
        }
    }
    
    public function ClearDB()
    {
        $this->db->query('TRUNCATE ' . PREFIX . "_tournaments");
        $this->db->query('TRUNCATE ' . PREFIX . "_matches");
        $this->db->query('TRUNCATE ' . PREFIX . "_rates");
        
        return $this;
    }
    
    public function clearPoints()
    {
        $this->db->query('UPDATE ' . USERPREFIX . "_users SET points=0");
        
        return $this;
    }
    
    public function NewSeason()
    {
        $this->db->query('UPDATE ' . PREFIX . "_tournaments SET archive=1");
        
        return $this;
    }
    
    public function reCalculate()
    {
        $this->clearPoints();
        
        $users = array();
        foreach($this->GetIdNameTournament() as $t_id => $name)
        {
            foreach($this->GetMatches(array("tournament_id" => $t_id, 'calculate' => 1)) as $m_id => $matche)
            {
                if ($rates = $this->GetRate(array("matche_id" => $m_id)))
				{
					foreach ($rates as $rate)
					{	
						$point = intval(GetPoint($matche['points_1'], $matche['points_2'], $rate['rpoints_1'], $rate['rpoints_2']));
					
						if (empty($users[$rate['user_id']]))
						{
                            $users[$rate['user_id']] = 0;
						}
                        
                        $users[$rate['user_id']] += $point;
					}
				}
                
            }
        }
        
        foreach($users as $id => $points)
        {
            $this->db->query("UPDATE " . PREFIX . "_users SET points={$points} WHERE user_id=" . $id);
        }
        
        clear_cache();
        
    }
	
	protected function affected()
	{
		if ($this->db->mysql_extend == 'MySQLi')
		{
            return mysqli_affected_rows($this->db->db_id);
        }
        else
        {
            return mysql_affected_rows($this->db->db_id);
        }
        
	}
	
	protected function GetWhere($where, $prefix=false)
	{
		if ($prefix)
        {
            $prefix .= ".";
        }
        
        $where_new = array();
        
		if (is_array($where) && count($where))
		{
			$this->escape($where);
			
			foreach ($where as $colum=>$value)
			{
				$where_new[] = $prefix.$colum . "='$value'";
			}
		}
		elseif (!is_array($where) && $where != "")
        {
            $where_new[] = $where;
        }
         
        if (AREA == 'USER')
        {
            if ($this->_archive)
            {
                $where_new[] = 'archive=1';
            }
            else
            {
                $where_new[] = 'archive=0';
            }
        }
        
        if (count($where_new))
        {
            return " WHERE " . implode(" AND ", $where_new);
        }
		else 
        {
			return '';
        }
	}
	
	protected function GetOrder($order)
	{
		if (is_array($order) && count($order))
		{
			$order_out = "";
			foreach ($order as $ord=>$value)
			{
				if ($order_out != "") $order_out .= ", ";
				
				if (in_array($ord, array_keys($this->array_type)))
					$order_out .= $ord." ".$value;
			}
			if ($order_out != '')
				return " ORDER BY ".$order_out;
		}
		elseif (!is_array($order) && $order != "")
		{
			if (!in_array($order, array_keys($this->array_type)))
				return '';
			return " ORDER BY " . $order;
		}
		else 
			return "";
	}
    
    protected function _checkArchive(&$where)
    {
        if (AREA == 'USER')
        {
            if ($this->_archive)
            {
                $where['archive'] = 1;
            }
            else
            {
                $where['archive'] = 0;
            }
        }
        
        return $this;
    }
	
	protected function GetLimit($start = 0, $limit = 0)
	{
		if (($limit = intval($limit)) > 0)
		{
			if (intval($start) == "")
				$start = 0;
			else 
				$start = intval($start);
				
			$limits = " LIMIT $start, $limit";
		}
		else 
			$limits = "";
			
		return $limits;
	}
	
	protected function GetSet($array)
	{
		$set = array();
		$this->escape($array);
		if (count($array))
		{
			foreach ($array as $colum=>$value)
			{
				$set[] = $colum . "='".$value."'";
			}
			return implode(", ", $set) ." ";
		}
		return false;
	}
	
	protected function escape(&$array, $array_type = false)
	{
		if (!$array_type || !is_array($array_type))
			$array_type = $this->array_type;
			
		$array = array_intersect_key($array, $array_type);

		foreach ($array_type as $colum=>$type)
		{
			if (is_array($type))
			{
				if (!in_array($array[$colum], $type))
					unset($array[$colum]);
				else 
					$array[$colum] = $this->doescape($array[$colum], "string");
			}
			else
			{
				if (isset($array[$colum]))
					$array[$colum] = $this->doescape($array[$colum], $type);
			}
		}
	}
	
	protected function doescape($data, $type = "int")
	{
		if (is_array($data))
		{
			switch ($type)
			{
				case "int":
					foreach ($data as $key=>$value)
					{
						$data[$key] = intval($value);
					}
					break;

				case "string":
					foreach ($data as $key=>$value)
					{
						$data[$key] = $this->db->safesql($value);
					}
					break;

				case "float":
					foreach ($data as $key=>$value)
					{
						$data[$key] = floatval($value);
					}
					break;

				default:
					foreach ($data as $key=>$value)
					{
						$data[$key] = intval($value);
					}
			}
		}
		else
		{
			switch ($type)
			{
				case "int":
					$data = intval($data);
					break;

				case "string":
					$data = $this->db->safesql($data);
					break;

				case "float":
					$data = floatval($data);
					break;

				default:
					$data = intval($data);
			}
		}
		return $data;
	}
}

?>
