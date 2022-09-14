<?php
// IRISK handles everything revolving risk managment
class SellRisk implements IRisk
{
    private const RISKPERCENTAGE = 2.0;
    private const RISKREWARDRATIO = 1/1;

    private $_close,
            $_distToStopLoss,
            $_pip,
            $_valuePerPip,
			$_spread;

    public function __construct(STATE $state)
    {   
        // Use BID + spread candles for everything stop loss related, use BID candles for close price | Short position 

        $this->_close = $state->getClose('bid');
		$this->_spread = $state->getSpread();

        $this->_pip = Instrument::pip($state->getInstrument());
        $this->_valuePerPip = $this->_pip / $this->_close;

        $atr = function() use ($state) {$value = $state->getAtr(14, 'bid'); return end($value); };
        $ema = function() use ($state) {$value = $state->getEma(200, 'bid'); return end($value); };
        $swingHigh = $state->getSwingHigh('bid');

		// Selling is done on the bid chart, add spread when dealing with stop losses
		// As the position is bought back on the ask price
		
        if ($swingHigh - $ema() < 0 && $ema() - $swingHigh < $atr())
            $this->_distToStopLoss = $ema() + $atr() - $this->_close;
        else
            $this->_distToStopLoss = $swingHigh + $atr() - $this->_close;
    }

    public function getClose() : float
    {
        return $this->_close;
    }

    public function getStopLossTarget() : float
    {
        return ($this->_close + $this->_distToStopLoss) + $this->_spread;
    }

    public function getTakeProfitTarget() : float
    {
        return ($this->_close - ($this->_distToStopLoss * self::RISKREWARDRATIO)) + $this->_spread;
    }
	
	public function getPips() : int
	{
		return ceil($this->_distToStopLoss / $this->_pip);
	}

    public function getUnits(): float
    {
        // Position sizing = Amount to risk / (stop loss x value per pip), value per pip = 
        //$risk = Account::getAccountBalance() * (self::RISKPERCENTAGE / 100);

        /*
        $pips = ceil($this->_distToStopLoss / $this->_pip);
        
        
        echo 'Risk - ' . $risk . '</br>';
        echo 'Pips - ' . $pips . '</br>';
        echo 'Units - ' . ceil($risk / ($pips * $this->_valuePerPip)) . '<br>';
		
        $pctg = $this->_distToStopLoss / $this->_close;
		$leverage = (self::RISKPERCENTAGE / 100) / $pctg;
        echo 'Percentage - ' . round($pctg*100, 4) . "\n";
        echo 'Leverage to use - ' . $leverage . "\n";

        echo 'Units - ' . $leverage * Account::getAccountBalance() . "\n";

        die();
		*/
		
        return -1000;
    }
}