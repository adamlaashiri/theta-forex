<?php
require_once 'core/init.php';
set_time_limit(0);

//(A) RUN FROM COMMAND-LINE ONLY
if (php_sapi_name() != 'cli')
	die('run this script from command line...');

//(B) instantiate a task and register shutdown function
try
{
	$task = new Task(4); //theta experimental
	$task->init();
}
catch (Exception $e)
{
	echoAndExit($e->getMessage());
}

register_shutdown_function(function() use($task)
{
	if (isset($task))
		$task->terminated();
});

$cycle = 0;
while (true)
{	
	$task->commands();
	while($task->getStatus())
	{
		$cycle++;
		$task->commands();
		
		// Memory check
		if (memory_get_usage(true) >= get_memory_limit())
			die();
		
		// For debugging
		if ($cycle%1000 == 0)
			echo "\033[92mCycle({$cycle})... idle(" . $task->getCommands()['idle'] . " sec) memory usage - " . memory_get_usage_mb(true) . "MB \033[0m\n";
		
		try
		{
			// Controller ...
            $controller = new Controller($task);
            $controller->scan();
		}
		catch (Exception $e)
		{
			$task->log($e->getMessage());
			continue;
		}
		sleep($task->getCommands()['idle']);
	}
}
?>