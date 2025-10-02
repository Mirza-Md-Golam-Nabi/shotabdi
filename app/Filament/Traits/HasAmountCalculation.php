<?php
namespace App\Filament\Traits;

use App\Enums\ProductEnum;

trait HasAmountCalculation
{
    protected static function stockInUpdateAmount($set, $get): void
    {
        $product_id = $get('product_id') ?? 0;
        $quantity   = (float) $get('quantity') ?? 0;
        $rate       = (float) $get('rate') ?? 0;
        $discount   = (float) $get('discount') ?? 0;
        $deposit    = (float) $get('deposit') ?? 0;
        $cashback   = (float) $get('cashback') ?? 0;
        $multiply   = $product_id == ProductEnum::Egg->value ? 30 : 1;
        $sub_total  = round($quantity * $multiply * $rate);

        $amount = $sub_total + $deposit - $discount - $cashback;
        $set('amount', $amount);
    }

    protected static function stockOutUpdateAmount($set, $get): void
    {
        $product_id = $get('product_id') ?? 0;
        $quantity   = (float) $get('quantity') ?? 0;
        $rate       = (float) $get('rate') ?? 0;
        $discount   = (float) $get('discount') ?? 0;
        $deposit    = (float) $get('deposit') ?? 0;
        $multiply   = $product_id == ProductEnum::Egg->value ? 30 : 1;
        $sub_total  = round($quantity * $multiply * $rate);

        $amount = $sub_total - $discount - $deposit;
        $set('amount', $amount);
    }
}
