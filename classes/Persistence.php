<?php
// Provides access to persistent data that gets reassigned once for every interval
class Persistence
{
    // interval is in seconds
    public static function getData(string $name, int $interval, callable $data)
    {
        // Check if data exists in db
        $query = DB::getInstance()->select('persistent_data', ['data', 'datetime'], [['name', '=', $name]]);
        
        if ($query->count())
        {
            $result = $query->first();
            // Check if interval of data exceeded given interval
            $seconds = time() - strtotime($result->datetime);
            
            if ($seconds < $interval)
            {
                return json_decode($result->data);
            }
        }
		
		//echo $name . ' is being updated..' . PHP_EOL;

        // Check that data is callable
        if (!is_callable($data))
            return false;
        
        $fraction = (time() % $interval) / $interval;
        $time = time() - floor($interval * $fraction);

        if ($query->count())
        {
            // Update database with new data
            DB::getInstance()->update(
                'persistent_data',
                [
                    'data' => json_encode($data()),
                    'datetime' => date('Y-m-d H:i:s', $time)
                ],
                [['name', '=', $name]]
            );
        }
        else
        {
            // Persist data to database
            DB::getInstance()->insert(
                'persistent_data',
                [
                    'name' => $name,
                    'data' => json_encode($data()),
                    'datetime' => date('Y-m-d H:i:s', $time)
                ]
            );
        }

        return $data();
    }
}