<?php
//useful functions
function memory_get_peak_usage_mb($real_usage = false)
{
	return round(memory_get_peak_usage($real_usage) / 1048576, 2, PHP_ROUND_HALF_UP);
}

function memory_get_usage_mb($real_usage = false)
{
	return round(memory_get_usage($real_usage) / 1048576, 2, PHP_ROUND_HALF_UP);
}

function get_memory_limit()
{
   $limit = ini_get('memory_limit');
   $unit = strtolower(mb_substr($limit, -1 ));
   $bytes = intval(mb_substr($limit, 0, -1), 10);
   
   switch ($unit)
   {
      case 'k':
         $bytes *= 1024;
         break 1;  
      case 'm':
         $bytes *= 1048576;
         break 1;
      case 'g':
         $bytes *= 1073741824;
         break 1;
      default:
         break 1;
   }
   return $bytes;
}

// Check if process with id is running
function pidExists($pid)
{
    exec('TASKLIST /NH /FO "CSV" /FI "PID eq '.$pid.'"', $outputA );
    $outputB = explode( '","', $outputA[0] );
    return isset($outputB[1])?true:false;
}

// For console applications
function echoAndExit(string $message, int $sleep = 10)
{
	echo $message;
	sleep($sleep);
	die();
}