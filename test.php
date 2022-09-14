<?php
require_once 'core/init.php';

$time_start = microtime(true);

// Network related test
$state = new MultiTimeframeTrendMacdReversed("USD_CAD");

//echo '<br><br>time--test--: ' . round(microtime(true) - $time_start, 3) . ' sec<br><br>';

// Functionality test

//$local = new DateTime(date('Y-m-d H:i:s', $state->getTimestamp()), new DateTimeZone('UTC'));
//$local->setTimezone(new DateTimeZone('Europe/Luxembourg'));
echo 'Buy signal ' . ($state->buySignal() ? 'TRUE' : 'FALSE') . PHP_EOL;
echo 'Sell signal ' . ($state->sellSignal() ? 'TRUE' : 'FALSE') . PHP_EOL;
//$state->setRiskHandler(new BuyRisk($state));
						
//echo 'SHT' . ' ENTRY in ' . $state->getInstrument() . ' (' . sprintf("%02d", 1) . ' pips) at ' . $state->getRiskHandler()->getClose() . ', ' . $local->format('d M y H:i') . PHP_EOL;

?>