<?php
namespace App\Filament\Traits;

trait HasAmountCalculation
{
    protected static function stockInUpdateAmount($set, $get): void
    {
        $quantity = (float) $get('quantity') ?? 0;
        $rate     = (float) $get('rate') ?? 0;
        $discount = (float) $get('discount') ?? 0;
        $deposit  = (float) $get('deposit') ?? 0;
        $cashback = (float) $get('cashback') ?? 0;

        $amount = ($quantity * $rate) + $deposit - $discount - $cashback;
        $set('amount', $amount);
    }
}
