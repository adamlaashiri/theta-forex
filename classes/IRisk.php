<?php
interface IRisk
{
    public function getClose() : float;
    public function getStopLossTarget() : float;
    public function getTakeProfitTarget() : float;
	public function getPips() : int;
    public function getUnits() : float;
}