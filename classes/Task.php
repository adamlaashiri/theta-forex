<?php
//errors will be handled through try and catch blocks, logging and restarting process
class Task
{
	private $handler,
			$taskid,
			$name,
			$status,
			$commands = null;
	
	public function __construct(int $taskid)
	{
		$this->handler = DB::getInstance();
		
		//check if task exist in db, otherwise throw exception
		$query = $this->handler->select('task', array('task_name'), [array('task_id', '=', $taskid)]);
		
		if (!$query->count())
			throw new Exception("Error: Task with id {$taskid} not found");
		
		$result = $query->first();
		$this->taskid = $taskid;
		$this->name = $result->task_name;
	}
	
	public function init()
	{
		try
		{
			$this->setPid(getmypid());
			//self::setRunningState($this->taskid, 1);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}
	
	private function setPid($pid)
	{
		if (is_int($pid))
		{
			if (!$this->handler->update('task', array('pid' => $pid), [array('task_id', '=', $this->taskid)]))
				throw new Exception("Error: there was a problem updating pid for task: {$this->name}");
		}
		else
		{
			throw new Exception("Error: not a valid process id for task: {$this->name}");
		}
	}
	
	public function commands()
	{
		$query = $this->handler->select('task', array('status', 'commands'), [array('task_id', '=', $this->taskid)]);
		if ($query->count())
		{
			$this->status = $query->first()->status;
			$this->commands = json_decode($query->first()->commands, true);
		}
	}
	
	public function terminated()
	{
		$message = "Task: {$this->name} has terminated with a peak memory usage of " . memory_get_peak_usage_mb(true) . ' MB';
		
		$error = error_get_last();
		if ($error !== null && $error['type'] === E_ERROR)
		{
			// fatal error has occured
			$this->log($message . ' and an error');
		}
		else
		{
			$this->log($message);
		}
		//self::setRunningState($this->taskid, 0);
	}
	
	public function log($message)
	{	
		if (strlen($message) <= 1024)
		$this->handler->insert('task_log', array('task_id' => $this->taskid, 'message' => $message));
	}
	
	public function clear()
	{
		$this->handler->delete('task_log', [array('task_id', '=', $this->taskid)]);
	}
	
	//getters
	public function getStatus() { return $this->status ? true : false; }
	
	public function getCommands() { return $this->commands; }
	
	
	
	
	//static methods
	public static function setRunningState(int $task_id, int $running)
	{
		if (isset($task_id) && isset($running))
		{
			$handler = DB::getInstance();
			if (!$handler->update('task_status', array('running' => $running), [array('task_id', '=', $task_id)]))
				throw new Exception("Error: there was a problem updating running state for task: {$task_id}");
		}
	}
}