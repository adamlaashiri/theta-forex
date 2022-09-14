<?php
interface IStrategy
{
    public function buySignal() : bool;
    public function sellSignal() : bool;
}