<?php
namespace App\Filament\Traits;

use App\Enums\CashFlowEnum;
use App\Enums\TransactionTypeEnum;
use App\Filament\Services\TransactionService;
use App\Models\BankTransaction;
use App\Models\Transaction;

trait HandlesTransactions
{
    public function saveStockInTransaction(array $stock, $amount, $stockIn)
    {
        app(TransactionService::class)
            ->handleStockInTransactions($stock, $this->date, $stockIn->id, $amount);
    }

    public function saveStockOutTransaction(array $stock, $amount, $stockOut)
    {
        app(TransactionService::class)
            ->handleStockOutTransactions($stock, $this->date, $stockOut->id, $amount);
    }

    public function saveTransaction(array $data, CashFlowEnum $flow, TransactionTypeEnum $type): Transaction
    {
        return app(TransactionService::class)
            ->createTransaction(
                $data['customer_id'],
                $data['date'],
                $data['amount'],
                $flow,
                $type
            );
    }

    public function bankTransactionFrom(array $data): BankTransaction
    {
        return app(TransactionService::class)
            ->bankTransaction(
                $data['date'],
                $data['amount'],
                from: $data['customer_id'],
                tran_id_from: $data['tran_id_from']
            );
    }

    public function bankTransactionTo(BankTransaction $transaction, int $customer_id, int $tran_id_to): BankTransaction
    {
        $transaction->to         = $customer_id;
        $transaction->tran_id_to = $tran_id_to;
        $transaction->save();

        return $transaction;
    }
}
