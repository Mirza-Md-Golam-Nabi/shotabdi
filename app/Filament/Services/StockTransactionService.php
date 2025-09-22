<?php
namespace App\Filament\Services;

use App\Enums\CashFlowEnum;
use App\Enums\ProductEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Transaction;

class StockTransactionService
{
    public function handleStockInTransactions(array $stock, string $date, int $stockInId, float $amount): void
    {
        $type = $stock['product_id'] == ProductEnum::Egg->value
            ? TransactionTypeEnum::Egg
            : TransactionTypeEnum::Feed;

        // Egg or Feed
        $this->createTransaction(
            $stock['customer_id'],
            $date,
            $amount,
            CashFlowEnum::Deposit,
            $type,
            stock_in_id: $stockInId
        );

        // Deposit
        if (! empty($stock['deposit'])) {
            $this->createTransaction(
                $stock['customer_id'],
                $date,
                $stock['deposit'],
                CashFlowEnum::Deposit,
                TransactionTypeEnum::Deposit,
            );
        }

        // Expense
        if (! empty($stock['cashback'])) {
            $this->createTransaction(
                $stock['customer_id'],
                $date,
                $stock['cashback'],
                CashFlowEnum::Expense,
                TransactionTypeEnum::Expense,
            );
        }
    }

    public function handleStockOutTransactions(array $stock, string $date, int $stockOutId, float $amount): void
    {
        $type = $stock['product_id'] == ProductEnum::Egg->value
            ? TransactionTypeEnum::Egg
            : TransactionTypeEnum::Feed;

        // Egg or Feed
        $this->createTransaction(
            $stock['customer_id'],
            $date,
            $amount,
            CashFlowEnum::Expense,
            $type,
            stock_out_id: $stockOutId
        );

        // Deposit
        if (! empty($stock['deposit'])) {
            $this->createTransaction(
                $stock['customer_id'],
                $date,
                $stock['deposit'],
                CashFlowEnum::Deposit,
                TransactionTypeEnum::Deposit,
            );
        }
    }

    protected function createTransaction(
        int $customer_id,
        string $date,
        float $amount,
        CashFlowEnum $cash_flow_id,
        TransactionTypeEnum $tran_type_id,
        ?int $stock_in_id = null,
        ?int $stock_out_id = null,
    ): void {
        $data = [
            'customer_id'  => $customer_id,
            'date'         => $date,
            'cash_flow_id' => $cash_flow_id,
            'tran_type_id' => $tran_type_id,
            'amount'       => $amount,
        ];

        if ($stock_in_id !== null) {
            $data['stock_in_id'] = $stock_in_id;
        }

        if ($stock_out_id !== null) {
            $data['stock_out_id'] = $stock_out_id;
        }

        Transaction::create($data);
    }
}
