<?php
namespace App\Filament\Traits;

use App\Filament\Services\StockTransactionService;


trait HandlesTransactions
{
    public function saveStockInTransaction(array $stock, $amount, $stockIn)
    {
        app(StockTransactionService::class)
            ->handleStockInTransactions($stock, $this->date, $stockIn->id, $amount);
    }

    public function saveStockOutTransaction(array $stock, $amount, $stockIn)
    {
        app(StockTransactionService::class)
            ->handleStockOutTransactions($stock, $this->date, $stockIn->id, $amount);
    }
}
