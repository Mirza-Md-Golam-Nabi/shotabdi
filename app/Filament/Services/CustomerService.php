<?php
namespace App\Filament\Services;

use App\Enums\BankTransactionEnum;
use App\Enums\CashFlowEnum;
use App\Enums\TransactionTypeEnum;
use App\Filament\Traits\HandlesTransactions;
use App\Models\Customer;

class CustomerService
{
    use HandlesTransactions;

    public function updateBalance(
        int $customer_id,
        int $balance,
        string $operation = 'add'
    ): void {
        $operation == 'subtract'
            ? Customer::where('id', $customer_id)->decrement('balance', $balance)
            : Customer::where('id', $customer_id)->increment('balance', $balance);
    }

    public function bankTransaction(array $data): array
    {
        $return = [];
        $amount = $data['amount'];

        if (! empty($data['from_bank'])) {
            $customer_id = $data['from_bank'];

            $cash_flow = CashFlowEnum::Expense;
            $tran_type = TransactionTypeEnum::BankTransaction;

            if ($data['type'] == BankTransactionEnum::Self->value) {
                $isDeposit = $data['cash_flow_id'] == CashFlowEnum::Deposit->value;

                [$cash_flow, $tran_type] = $isDeposit
                    ? [$cash_flow->reverse(), TransactionTypeEnum::Deposit]
                    : [$cash_flow, TransactionTypeEnum::Expense];
            }

            $payload = [
                'customer_id' => $customer_id,
                'amount'      => $amount,
                'date'        => $data['date'],
            ];

            $from_tran = $this->saveTransaction(
                $payload,
                $cash_flow,
                $tran_type
            );

            $customer  = Customer::find($customer_id);
            $operation = $customer?->transactionOperation($cash_flow);
            $customer?->updateBalance($amount, $operation);

            $return['from']         = $customer_id;
            $return['tran_id_from'] = $from_tran->id;

        }

        if (! empty($data['from_customer'])) {
            $customer_id = $data['from_customer'];

            $cash_flow = CashFlowEnum::Deposit;

            $payload = [
                'customer_id' => $customer_id,
                'amount'      => $amount,
                'date'        => $data['date'],
            ];

            $from_tran = $this->saveTransaction(
                $payload,
                $cash_flow,
                TransactionTypeEnum::BankTransaction
            );

            $customer  = Customer::find($customer_id);
            $operation = $customer?->transactionOperation($cash_flow);
            $customer?->updateBalance($amount, $operation);

            $return['from']         = $customer_id;
            $return['tran_id_from'] = $from_tran->id;
        }

        if (! empty($data['to_customer'])) {
            $customer_id = $data['to_customer'];

            $cash_flow = CashFlowEnum::Expense;

            $payload = [
                'customer_id' => $customer_id,
                'amount'      => $amount,
                'date'        => $data['date'],
            ];

            $to_tran = $this->saveTransaction(
                $payload,
                $cash_flow,
                TransactionTypeEnum::BankTransaction
            );

            $customer  = Customer::find($customer_id);
            $operation = $customer?->transactionOperation($cash_flow);
            $customer?->updateBalance($amount, $operation);

            $return['to']         = $customer_id;
            $return['tran_id_to'] = $to_tran->id;

        }

        if (! empty($data['to_bank'])) {
            $customer_id = $data['to_bank'];

            $payload = [
                'customer_id' => $customer_id,
                'amount'      => $amount,
                'date'        => $data['date'],
            ];

            $cash_flow = CashFlowEnum::Deposit;

            $to_tran = $this->saveTransaction(
                $payload,
                $cash_flow,
                TransactionTypeEnum::BankTransaction
            );

            $customer  = Customer::find($customer_id);
            $operation = $customer?->transactionOperation($cash_flow);
            $customer?->updateBalance($amount, $operation);

            $return['to']         = $customer_id;
            $return['tran_id_to'] = $to_tran->id;

        }

        return $return;
    }
}
