<?php
// Contains all instruments traded and relevant info about them
class instrument
{
    public static $instruments = [
		// Euro based
        'EUR_USD',
        'EUR_GBP',
        'EUR_AUD',
        'EUR_CAD',
        'EUR_CHF',
        'EUR_NZD',
        'EUR_SEK',
		
		// Non euro based
		'USD_CAD',
		'USD_CHF',
		'GBP_USD',
		'NZD_USD',
		'AUD_USD',
		'AUD_CAD',
		'GBP_CHF'
		
		// Exotic currencies tend to have higher spreads
    ];

    private static $_twoDecimals = [
        'EUR_JPY',
        'EUR_HUF'
    ];

    static public function pip($instrument) : float
    {
        return (in_array($instrument, self::$_twoDecimals)) ? 0.01 : 0.0001;
    }
}