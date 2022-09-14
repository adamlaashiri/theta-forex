<?php
// Technical analysis
class TA
{
	protected $_maxPeriod = 200;
	
	private function checkPeriod(int $period, string $method)
	{
		if ($period > $this->_maxPeriod)
			throw new Exception("Exceeded maximum period of {$this->_maxPeriod} in {$method}");
		
		if ($period < 1)
			throw new Exception("Period must be a minimum of 1 in {$method}");
	}
	
	//<summary>
	//Get the average true range
	//</summary>
	protected function atr(array $candles, int $dp, int $period)
	{
		$this->checkPeriod($period, __METHOD__);
		$atr = [];
		$l = count($candles);
		$pre = max(
			($candles[$l - $dp - 1]['h'] - $candles[$l - $dp - 1]['l']),
			abs($candles[$l - $dp - 1]['h'] - $candles[$l - $dp - 2]['c']),
			abs($candles[$l - $dp - 1]['l'] - $candles[$l - $dp - 2]['c']),
		);
		for ($i = $l - $dp; $i < $l; $i++)
		{
			$tr = max(
				($candles[$i]['h'] - $candles[$i]['l']),
				abs($candles[$i]['h'] - $candles[$i - 1]['c']),
				abs($candles[$i]['l'] - $candles[$i - 1]['c']),
			);
			$temp = ($pre * ($period - 1) + $tr) / $period;
			$atr[] = $temp;
			$pre = $temp;
		}
		return $atr;
	}
	
	//<summary>
	//calculates exponential moving average with given period
	//</summary>
	protected function sma(array $candles, int $dp, int $period)
	{
		$this->checkPeriod($period, __METHOD__);
		$sma = [];
		$l = count($candles);
		for ($i = $l - $dp + 1; $i < $l + 1; $i++)
		{
			$sum = 0;
			for ($j = $i - $period; $j < $i; $j++)
				$sum += $candles[$j]['c'];
			$sma[] = $sum / $period;
		}
		return $sma;
	}

	//<summary>
	//calculates simple moving average with given period
	//</summary>
	protected function ema(array $candles, int $dp, int $period)
	{
		$this->checkPeriod($period, __METHOD__);
		$ema = [];
		$l = count($candles);
		$sum = 0;
		for ($i = $l - $dp - $period ; $i < $l - $dp; $i++)
			$sum += $candles[$i]['c'];
		
		$pre = $sum / $period;
		$k = 2 / ($period + 1);
		for ($i = $l - $dp; $i < $l; $i++)
		{
			$curr = ($k * $candles[$i]['c']) + (1 - $k) * $pre;
			$ema[] = round($curr, 5);
			$pre = $curr;
		}
		return $ema;
	}

	//<summary>
	//calculates the macd with a 26 period 12 period ema
	// signal is calculated through the 9 period ema of macd 
	//</summary>
	protected function macd(array $candles, int $dp)
	{
		$signalPeriod = 9;
		$slowEma = $this->ema($candles, $dp + $signalPeriod, 26);
		$fastEma = $this->ema($candles, $dp + $signalPeriod, 12);
		$macd = [];
		$final = [];

		for ($i = 0; $i < $dp + $signalPeriod; $i++)
			$macd[] = $fastEma[$i] - $slowEma[$i];

		$signal = $this->ema(array_map(fn ($data) => ['c' => $data], $macd), $dp, $signalPeriod);		
		
		for ($i=0; $i < $dp; $i++)
			$final[] = ['macd' => $macd[$i + $signalPeriod], 'signal' => $signal[$i], 'hist' => $macd[$i + $signalPeriod] - $signal[$i]];
		
		return $final;
	}

	/*
	* Trading Rush Moving Average by Trading Rush - https://www.patreon.com/posts/trading-rush-be-48443915
	*/

	//<summary>
	//Calculates exponential moving average with given period, that disappears in a ranging market
	//</summary>
	protected function magicEma(array $candles, int $dp, int $period)
	{
		$ema = $this->ema($candles, $dp, $period);
		$magicEma = [];
		$l = count($candles);
		$m = 49;
		
		for($i = $l - $dp + 50; $i < $l; $i++)
		{
			$m++;
			// close > ema1 and close[5] > ema1[5] and close[10] > ema1[10] and close[15] > ema1[15] and close[20] > ema1[20] and close[30] > ema1[30] and close[40] > ema1[40] and close[50] > ema1[50]
			if ($candles[$i]['c'] > $ema[$m] &&
				$candles[$i - 5]['c'] > $ema[$m - 5] &&
				$candles[$i - 10]['c'] > $ema[$m - 10] &&
				$candles[$i - 15]['c'] > $ema[$m - 15] &&
				$candles[$i - 20]['c'] > $ema[$m - 20] &&
				$candles[$i - 30]['c'] > $ema[$m - 30] &&
				$candles[$i - 40]['c'] > $ema[$m - 40] &&
				$candles[$i - 50]['c'] > $ema[$m - 50])
			{
				$magicEma[] = $candles[$i]['c'] - $ema[$m];
			}
			// close < ema1 and close[5] < ema1[5] and close[10] < ema1[10] and close[15] < ema1[15] and close[20] < ema1[20] and close[30] < ema1[30] and close[40] < ema1[40] and close[50] < ema1[50]
			elseif ($candles[$i]['c'] < $ema[$m] &&
					$candles[$i - 5]['c'] < $ema[$m - 5] &&
					$candles[$i - 10]['c'] < $ema[$m - 10] &&
					$candles[$i - 15]['c'] < $ema[$m - 15] &&
					$candles[$i - 20]['c'] < $ema[$m - 20] &&
					$candles[$i - 30]['c'] < $ema[$m - 30] &&
					$candles[$i - 40]['c'] < $ema[$m - 40] &&
					$candles[$i - 50]['c'] < $ema[$m - 50])
			{
				$magicEma[] = $candles[$i]['c'] - $ema[$m];
			}
			else
			{
				$magicEma[] = null;
			}
		}
		/*
		echo count($magicEma) . '<br>';
		$counter = 0;
		$counter2 = 0;
		for ($i = 0; $i < count($magicEma); $i++)
		{
			if ($magicEma[$i] == null)
			{
				$counter++;
				$counter2 = 0;
				echo "{$counter}<br>";
			}
			elseif ($magicEma[$i] > 0)
			{
				$counter = 0;
				$counter2++;
				echo '<span style="color:green;">' . $counter2 . ' ' . $magicEma[$i] . '</span><br>';
			}
			elseif ($magicEma[$i] < 0)
			{
				$counter = 0;
				echo '<span style="color:red;">' . $magicEma[$i] . '</span><br>';
			}
		}
		*/
		return $magicEma;
		
	}

	//<summary>
	//Calculates swing low, local minimum
	//</summary>
	protected function swingLow(array $candles)
	{
		$l = count($candles);
		for ($i = $l - 2; $i > 0; $i--)
		{
			$pre = $candles[$i - 1]['l'];
			$curr = $candles[$i]['l'];
			$post = $candles[$i + 1]['l'];
			if ($pre > $curr && $post > $curr)
				return $curr;
		}
		return null;
	}

	//<summary>
	//Calculates swing high, local maximum
	//</summary>
	protected function swingHigh(array $candles)
	{
		$l = count($candles);
		for ($i = $l - 2; $i > 0; $i--)
		{
			$pre = $candles[$i - 1]['h'];
			$curr = $candles[$i]['h'];
			$post = $candles[$i + 1]['h'];

			if ($pre < $curr && $post < $curr)
				return $curr;
		}
		return null;
	}

}