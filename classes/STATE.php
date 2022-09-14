<?php
// STATE is 10 points of market data for a given time, fundamental to all technical analysis and strategies
// STATE is abstracted and accustomed to any trading strategy really (throug inheritance)
class STATE extends TA
{
	// Meta
	private $_oanda,
			$_instrument,
			$_interval,
			$_buffer = 210,
			$_candles,
			$_bidCandles,
			$_askCandles;

	// _risk provides the means to polymorphically assign a risk handler object through composition
	private ?IRisk $_risk = null;
			
	public function __construct($instrument, $interval = 'M5')
	{
		$this->_oanda = Oandav20::getInstance();
		$this->_instrument = $instrument;
		$this->_interval = $interval;
		
		$data = $this->_oanda->getInstrumentCandles($instrument, array(
			'price' => 'MBA',
			'granularity' => $interval,
			'count' => ($this->_buffer + $this->_maxPeriod)
		));
		
		if (!$data)
			throw new Exception('Invalid candle data for ' . __CLASS__);
		
		// Data preparation for mid, bid and ask
		for ($i = 0; $i < ($this->_buffer + $this->_maxPeriod); $i++)
		{
			$this->_candles[] = ['t' => $data['candles'][$i]['time'], 'o' => $data['candles'][$i]['mid']['o'], 'h' => $data['candles'][$i]['mid']['h'], 'l' => $data['candles'][$i]['mid']['l'], 'c' => $data['candles'][$i]['mid']['c']];
			$this->_bidCandles[] = ['t' => $data['candles'][$i]['time'], 'o' => $data['candles'][$i]['bid']['o'], 'h' => $data['candles'][$i]['bid']['h'], 'l' => $data['candles'][$i]['bid']['l'], 'c' => $data['candles'][$i]['bid']['c']];
			$this->_askCandles[] = ['t' => $data['candles'][$i]['time'], 'o' => $data['candles'][$i]['ask']['o'], 'h' => $data['candles'][$i]['ask']['h'], 'l' => $data['candles'][$i]['ask']['l'], 'c' => $data['candles'][$i]['ask']['c']];
		}
	}

	// Execute and order
	public function executeOrder($strategyName = 'NULL')
	{
		if ($this->_risk == null)
			return false;

		$risk = $this->_risk;
		if ($res = Oandav20::getInstance()->createMarketOrder(
				$this->_instrument,
				$risk->getUnits(),
				$risk->getClose(),
				$risk->getTakeProfitTarget(),
				$risk->getStopLossTarget()
		))
		{
			$this->saveState($strategyName, $res);
			return true;
		}
		return false;
	}

	public function saveState($strategyName, $transactionId)
	{
		if ($this->_risk == null)
			return false;

		$db = DB::getInstance();
		$risk = $this->_risk;
		
		$type = $risk->getUnits() > 0 ? 'long' : 'short';

		$db->insert('state', array(
			'strategy_name' => $strategyName,
			'transaction_id'=> (int)$transactionId,
			'instrument' 	=> $this->_instrument,
			'granularity' 	=> $this->_interval,
			'type' 			=> $type,
			'entry' 		=> $risk->getClose(),
			'stop_loss' 	=> $risk->getStopLossTarget(),
			'take_profit' 	=> $risk->getTakeProfitTarget(),
			'spread' 		=> $this->getSpread(),
			'datetime' 		=> date('Y-m-d H:i:s', $this->getTimestamp())
		));
	}
	
	public function getTimestamp()
	{
		return end($this->_candles)['t'];
	}

	public function getInstrument()
	{
		return $this->_instrument;
	}

	public function getInterval()
	{
		return $this->_interval;
	}
	
	// Select between mid, bid and ask
	public function getCandleType($t)
	{
		return match ($t) {
			'mid' => $this->_candles,
			'bid' => $this->_bidCandles,
			'ask' => $this->_askCandles,
			default => $this->_candles
		};
	}

	public function getSpread()
	{
		return $this->getClose('ask') - $this->getClose($t = 'bid');
	}
	
	public function getClose($t = 'mid')
	{
		$candleSource = $this->getCandleType($t);
		return end($candleSource)['c'];
	}

	public function getSwingLow($t = 'mid')
	{
		return $this->swingLow($this->getCandleType($t));
	}

	public function getSwingHigh($t = 'mid')
	{
		return  $this->swingHigh($this->getCandleType($t));
	}
	
	public function getAtr($period, $t = 'mid')
	{
		return array_slice($this->atr($this->getCandleType($t), $this->_buffer, $period), -10);
	}
	
	public function getSma($period, $t = 'mid')
	{
		return array_slice($this->sma($this->getCandleType($t), $this->_buffer, $period), -10);
	}
	
	public function getEma($period, $t = 'mid')
	{
		return array_slice($this->ema($this->getCandleType($t), $this->_buffer, $period), -10);
	}
	
	public function getMacd($t = 'mid')
	{
		return array_slice($this->macd($this->getCandleType($t), $this->_buffer), -10);
	}

	public function getMagicEma($period, $t = 'mid')
	{
		return array_slice($this->magicEma($this->getCandleType($t), $this->_buffer, $period), -10);
	}

	public function getRiskHandler()
	{
		return $this->_risk;
	}

	public function setRiskHandler(IRisk $riskHandler)
	{
		$this->_risk = $riskHandler;
	}
}