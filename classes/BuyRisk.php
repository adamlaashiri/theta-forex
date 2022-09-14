<?php
// IRISK handles everything revolving risk managment
class BuyRisk implements IRisk
{
    private const RISKPERCENTAGE = 2.0;
    private const RISKREWARDRATIO = 1/1;

    private $_close,
            $_distToStopLoss,
            $_pip,
            $_valuePerPip;

    public function __construct(STATE $state)
    {   
        // Use BID candles for everything stop loss related, use ASK candles for close price | Long position 

        $this->_close = $state->getClose('ask');

        $this->_pip = Instrument::pip($state->getInstrument());
        $this->_valuePerPip = $this->_pip / $this->_close;

        $atr = function() use ($state) {$value = $state->getAtr(14, 'bid'); return end($value); };
        $ema = function() use ($state) {$value = $state->getEma(200, 'bid'); return end($value); };
        $swingLow = $state->getSwingLow('bid');

        if ($swingLow - $ema() > 0 && $swingLow - $ema() < $atr())
            $this->_distToStopLoss = $this->_close - $ema() + $atr();
        else
            $this->_distToStopLoss = $this->_close - $swingLow + $atr();
    }

    public function getClose() : float
    {
        return $this->_close;
    }

    public function getStopLossTarget() : float
    {
        return $this->_close - $this->_distToStopLoss;
    }

    public function getTakeProfitTarget() : float
    {
        return $this->_close + ($this->_distToStopLoss * self::RISKREWARDRATIO);
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
		
        return 1000;
    }
}