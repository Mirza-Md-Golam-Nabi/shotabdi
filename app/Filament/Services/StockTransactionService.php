<?php
namespace App\Filament\Services;

use App\Enums\CashFlowEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;

class StockTransactionService
{
    public function handleStockInTransactions(array $stock, string $date, int $stockInId, float $amount): void
    {
        $type = $stock['product_id'] == 1 ? TransactionTypeEnum::EGG : TransactionTypeEnum::FEED;

        // Egg or Feed
        $this->createTransaction(
            $stock['customer_id'],
            $date,
            $stockInId,
            $amount,
            CashFlowEnum::DEPOSIT,
            $type
        );

        // Deposit
        if (! empty($stock['deposit'])) {
            $this->createTransaction(
                $stock['customer_id'],
                $date,
                $stockInId,
                $stock['deposit'],
                CashFlowEnum::DEPOSIT,
                TransactionTypeEnum::DEPOSIT
            );
        }

        // Cashback
        if (! empty($stock['cashback'])) {
            $this->createTransaction(
                $stock['customer_id'],
                $date,
                $stockInId,
                $stock['cashback'],
                CashFlowEnum::EXPENSE,
                TransactionTypeEnum::CASHBACK
            );
        }
    }

    public function handleStockOutTransactions(array $stock, string $date, int $stockOutId, float $amount): void
    {
        $type = $stock['product_id'] == 1 ? TransactionTypeEnum::EGG : TransactionTypeEnum::FEED;

        // Egg or Feed
        $this->createTransaction(
            $stock['customer_id'],
            $date,
            $stockOutId,
            $amount,
            CashFlowEnum::EXPENSE,
            $type
        );

        // Deposit
        if (! empty($stock['deposit'])) {
            $this->createTransaction(
                $stock['customer_id'],
                $date,
                $stockOutId,
                $stock['deposit'],
                CashFlowEnum::DEPOSIT,
                TransactionTypeEnum::DEPOSIT
            );
        }
    }

    protected function createTransaction(
        int $customer_id,
        string $date,
        int $stock_in_id,
        float $amount,
        CashFlowEnum $cash_flow_id,
        TransactionTypeEnum $tran_type_id
    ): void {
        Transaction::create([
            'customer_id'  => $customer_id,
            'date'         => $date,
            'stock_in_id'  => $stock_in_id,
            'cash_flow_id' => $cash_flow_id,
            'tran_type_id' => $tran_type_id,
            'amount'       => $amount,
        ]);
    }
}
