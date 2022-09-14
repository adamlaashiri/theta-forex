<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//configuration Theta
$GLOBALS['config'] = array (

	//database
	'db' => array('host' => '127.0.0.1', 'name' => '<DATABASE-NAME>', 'username' => '<DATABASE-USERNAME>', 'password' => '<DATABASE-PASSWORD>'),
	
	//api keys
	'oanda' => array('token' => '<YOUR-OANDA-TOKEN>', 'demo_account_number' => '<YOUR-DEMO-ACOOUNT-ID>', 'live_account_number' => '<YOUR-LIVE-ACOOUNT-ID>')
);
spl_autoload_register(function($class)
{
	require_once './classes/' . $class . '.php';
});
require_once('./functions/functions.php');
?>