<?php
namespace App\Filament\Traits;

use App\Filament\Services\StockTransactionService;


trait HandlesTransactions
{
    public function saveTransaction(array $stock, $amount, $stockIn)
    {
        app(StockTransactionService::class)
            ->handleProductTransactions($stock, $this->date, $stockIn->id, $amount);
    }
}
