<?php
class DB 
{
	private static $instance = null;
	
	private $handler;
	private $query;
	private $result;
	private $count;
	private $error;
	
	
	private function __construct()
	{
		try 
		{
			$this->handler = new PDO('mysql:host=' . Config::get('db/host') . ';dbname=' . Config::get('db/name'), Config::get('db/username'), Config::get('db/password'));
			$this->handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOEXCEPTION $e)
		{
			die($e->getMessage());
		}
	}
	
	public static function getInstance()
	{
		if(!isset(self::$instance))
			self::$instance = new DB();
		return self::$instance;
	}
	
	private function query($sql, $params = array())
	{
		$this->error = false;
		
		if($this->query = $this->handler->prepare($sql))
		{
			$x = 1;
			if(count($params))
			{
			
				foreach($params as $param)
				{
					$this->query->bindValue($x, $param);
					$x++;
				}
			}
			
			if($this->query->execute())
			{
				$this->result = $this->query->fetchAll(PDO::FETCH_OBJ);
				$this->count = $this->query->rowCount();
			} else 
			{
				$this->error = true;
			}
		}
		
		return $this;
		
	}
	
	private function action($sql, $table, $condition, $subSql = null)
	{
		//conditions
		if(is_array($condition[0]))
		{
			$sql = "{$sql} FROM {$table} WHERE ";
			$values = array();
			$i = 1;
			
			foreach($condition as $cond)
			{
				if(count($cond) === 3)
				{
					$operators = array('=', '>', '<', '>=', '<=');
			 
					$field = $cond[0];
					$operator = $cond[1];
					$value = $cond[2];
			 
					if(in_array($operator, $operators))
					{ 
						$sql .= "BINARY {$field} {$operator} BINARY ?";
						
						if($i < count($condition))
						{
							$sql .= ' AND ';
						}
						$i++;
					 }
					 $values[] = $value;
				}
				if (isset($subSql))
					$sql .= " {$subSql}";
			}
			if(!$this->query($sql, $values, $subSql)->error())
			{
				return $this;
			}

		}
		return false;
	}
	
	public function insert($table, $params)
	{
		if(is_array($params))
		{
			$keys = array_keys($params);
			$values = null;
			$i = 1;
			
			foreach($params as $value)
			{
				$values .= '?';
				
				if($i < count($params))
				{
					$values .= ', ';
				}
				$i++;
			}
			$sql = "INSERT INTO {$table} (`". implode('`,`', $keys) ."`) VALUES ({$values})";
			
			if(!$this->query($sql, $params)->error())
			{
				return true;
			}
			return false;
		}
	}
	
	public function update($table, $params, $condition)
	{
		$set = '';
		$x = 1;
		
		foreach($params as $name => $value)
		{
			$set .= "{$name} = ?";
			if($x < count($params))
			{
				$set .= ', ';
			}
			$x++;
		}
		
		$sql = "UPDATE {$table} SET {$set} WHERE ";
		$values = array();
		
		//condition start here
		//mulitple conditions
		if(is_array($condition[0])) 
		{
			$i = 1;	
			foreach($condition as $cond)
			{
				if(count($cond) === 3)
				{
					$operators = array('=', '>', '<', '>=', '<=');
					$field = $cond[0];
					$operator = $cond[1];
					$value = $cond[2];
			 
					if(in_array($operator, $operators))
					{ 
						$sql .= "BINARY {$field} {$operator} BINARY ?";
						
						if($i < count($condition))
						{
							$sql .= ' AND ';
						}
						$i++;
					 }
					 $params[] = $value;
				}
			}
		}
		
		if(!$this->query($sql, $params)->error())
		{
			return true;
		}
		return false;
	}
	
	public function select($table, $what, $condition, $subSql = null)
	{
		$sql = 'SELECT ';
		
		if(is_array($what))
		{
			$x = 1;
			foreach($what as $value)
			{
				if($x === count($what))
				{
					$sql .= $value;
					break;
				}
				$sql .= $value . ', ';
				$x++;
			}
		} else 
		{
			$sql .= $what;
		}
		
		return $this->action($sql, $table, $condition, $subSql);
	}
	
	public function selectAll($table, $what, $subSql = null)
	{
		$sql = 'SELECT ';
		
		if(is_array($what))
		{
			$x = 1;
			foreach($what as $value)
			{
				if($x === count($what))
				{
					$sql .= $value;
					break;
				}
				$sql .= $value . ', ';
				$x++;
			}
		} else 
		{
			$sql .= $what;
		}
		
		$sql .= " FROM {$table}";
		
		if (isset($subSql))
			$sql .= " {$subSql}";
		
		if(!$this->query($sql, array())->error())
		{
			return $this;
		}
	}
	
	public function delete($table, $condition)
	{
		return $this->action('DELETE', $table, $condition);
	}
	
	public function result()
	{
		return $this->result;
	}
	
	public function first()
	{
		return $this->result()[0];
	}
	
	public function count()
	{
		return $this->count;
	}
	
	public function error()
	{
		return $this->error;
	}
	
}