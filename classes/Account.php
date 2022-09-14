<?php
// Handles information about account, trades and market orders
class Account
{
    private static $_accountSummary = null;

    public static function updateAccountSummary()
    {
        $data = Oandav20::getInstance()->getAccountSummary();

        if (!$data)
            throw new Exception('Failed to update account summary for ' . __CLASS__);
        
        self::$_accountSummary = $data;
    }

    public static function getAccountBalance()
    {
        return isset(self::$_accountSummary) ? (float)self::$_accountSummary['account']['balance'] : false;
    }

    public static function getOpenTradeCount()
    {
        return isset(self::$_accountSummary) ? (int)self::$_accountSummary['account']['openTradeCount'] : false;
    }

    public static function getPendingOrderCount()
    {
        return isset(self::$_accountSummary) ? (int)self::$_accountSummary['account']['pendingOrderCount'] : false;
    }

    public static function getMarginAvailable()
    {
        return isset(self::$_accountSummary) ? (int)self::$_accountSummary['account']['marginAvailable'] : false;
    }
}