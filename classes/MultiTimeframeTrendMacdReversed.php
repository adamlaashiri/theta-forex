<?php
class MultiTimeframeTrendMacdReversed extends STATE implements IStrategy
{
    private $_multiTimeframeTrend = 0;

    public function __construct($instrument, $interval = 'M5')
    {
        try
        {
            parent::__construct($instrument, $interval);
            $this->_multiTimeframeTrend = $this->getMultiTimeframeTrend();
        }
        catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }
    }

    public function buySignal() : bool
    {
        // I'll test mid candles for technical analysis
        $candleSource = 'mid';

        $ema = function() use($candleSource) { $value = $this->getEma(200, $candleSource); return end($value); };
        $macd = $this->getMacd($candleSource);

        // Check for trend direction
        $upTrend = $this->getClose($candleSource) > $ema();

        // Check for macd signal crossover
        $l = count($macd);
        $macdSignal = 
            $macd[$l - 2]['macd'] < $macd[$l - 2]['signal']  &&
            $macd[$l - 1]['macd'] > $macd[$l - 1]['signal'] &&
            $macd[$l - 2]['macd'] < 0;

        if (!$upTrend || !$macdSignal || $this->_multiTimeframeTrend != 1)
            return false;

        $this->setRiskHandler(new SellRisk($this));
        return true;
    }

    public function sellSignal() : bool
    {
        // I'll test mid candles for technical analysis
        $candleSource = 'mid';

        $ema = function() use($candleSource) { $value = $this->getEma(200, $candleSource); return end($value); };
        $macd = $this->getMacd($candleSource);

        // Check for trend direction
        $downTrend = $this->getClose($candleSource) < $ema();

        // Check for macd signal crossunder
        $l = count($macd);
        $macdSignal = 
            $macd[$l - 2]['macd'] > $macd[$l - 2]['signal']  &&
            $macd[$l - 1]['macd'] < $macd[$l - 1]['signal'] &&
            $macd[$l - 2]['macd'] > 0;

        if (!$downTrend || !$macdSignal || $this->_multiTimeframeTrend != -1)
            return false;

        $this->setRiskHandler(new BuyRisk($this));
        return true;
    }

    
    private function getMultiTimeframeTrend() : int
    {
        $candleSource = 'mid';
        $emaLength = 200;

        try
        {
			$trend10Min = Persistence::getData($this->getInstrument() . '_trend10min', 600, function() use($candleSource, $emaLength) {
                $state = new STATE($this->getInstrument(), 'M10');
                $ema = $state->getEma($emaLength, $candleSource);
                return $state->getClose($candleSource) <=> end($ema);
            });

            $trend15Min = Persistence::getData($this->getInstrument() . '_trend15min', 900, function() use($candleSource, $emaLength) {
                $state = new STATE($this->getInstrument(), 'M15');
                $ema = $state->getEma($emaLength, $candleSource);
                return $state->getClose($candleSource) <=> end($ema);
            });

            $trend30Min = Persistence::getData($this->getInstrument() . '_trend30min', 1800, function() use($candleSource, $emaLength) {
                $state = new STATE($this->getInstrument(), 'M30');
                $ema = $state->getEma($emaLength, $candleSource);
                return $state->getClose($candleSource) <=> end($ema);
            });

            $trend1H = Persistence::getData($this->getInstrument() . '_trend1h', 3600, function() use($candleSource, $emaLength) {
                $state = new STATE($this->getInstrument(), 'H1');
                $ema = $state->getEma($emaLength, $candleSource);
                return $state->getClose($candleSource) <=> end($ema);
            });

            $trend2H = Persistence::getData($this->getInstrument() . '_trend2h', 7200, function() use($candleSource, $emaLength) {
                $state = new STATE($this->getInstrument(), 'H2');
                $ema = $state->getEma($emaLength, $candleSource);
                return $state->getClose($candleSource) <=> end($ema);
            });

        }
        catch(Exception $e)
        {
            throw new Exception($e->getMessage());
        }

        if ($trend10Min == 1 && $trend15Min == 1 && $trend30Min == 1 && $trend1H == 1 && $trend2H == 1)
        {
            return 1;
        }
        else if ($trend10Min == -1 && $trend15Min == -1 && $trend30Min == -1 && $trend1H == -1 && $trend2H == -1)
        {
            return -1;
        }
        
        return 0;
    }
}