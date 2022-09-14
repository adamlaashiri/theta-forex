<?php
// Searches and controlls the flow of new trades in the markets
class Controller
{
    private $_task;
    
    public function __construct(Task $task)
    {
		// We can manage the task assigned through dependency injection
        try
        {
            $this->_task = $task;
            Account::updateAccountSummary();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }
    
    // Scan the markets for a valid entries
    public function scan()
    {
        if (Account::getOpenTradeCount() > 0 || Account::getPendingOrderCount() > 0)
            return;

        foreach (Instrument::$instruments as $instrument)
        {
            try
            {
                $state = new MultiTimeframeTrendMacd($instrument);
                if ($state->buySignal() || $state->sellSignal())
                {
                    if ($state->executeOrder(get_class($state)))
                    {	
						// Position direction
						$position = $state->getRiskHandler()->getUnits() > 0 ? 'LNG' : 'SHT';
						
						// DateTime to local time
						$local = new DateTime(date('Y-m-d H:i:s', $state->getTimestamp()), new DateTimeZone('UTC'));
						$local->setTimezone(new DateTimeZone('Europe/Luxembourg'));
						
						echo $position . ' ENTRY in ' . $state->getInstrument() . ' (' . sprintf("%02d", $state->getRiskHandler()->getPips()) . ' pips) at ' . $state->getRiskHandler()->getClose() . ', ' . $local->format('d M y H:i') . PHP_EOL;
						sleep(60); // Sleep for some time to give the order some time to fill
                        return;
                    }
                }
            }
            catch(Exception $e)
            {   
                // All exceptions will be logged and loop continued
				$this->_task->log($e->getMessage());
				continue;
            }
			sleep(1);
        }
    }
}