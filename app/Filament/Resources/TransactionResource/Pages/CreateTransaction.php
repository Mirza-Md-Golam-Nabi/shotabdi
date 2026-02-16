<?php
namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enums\CashFlowEnum;
use App\Enums\TransactionTypeEnum;
use App\Filament\Resources\TransactionResource;
use App\Filament\Traits\HandlesTransactions;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTransaction extends CreateRecord
{
    use HandlesTransactions;

    protected static string $resource = TransactionResource::class;

    protected ?string $lastDate;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        session([
            'last_customer_id' => $data['transactions'][0]['customer_id'] ?? null,
        ]);
        $this->lastDate = $data['date'];
        $first          = null;

        foreach ($data['transactions'] as $tran) {
            $customer_id = $tran['customer_id'];
            $date        = $data['date'];

            $customer = Customer::find($customer_id);

            if (! empty($tran['deposit_amount'])) {
                $amount = $tran['deposit_amount'];

                /**
                 * If the customer is a bank,
                 * the cash flow value will be reversed:
                 * - Deposit becomes Expense
                 * - Expense becomes Deposit
                 *
                 * This is because for bank customers, the meaning of deposit and expense
                 * is opposite compared to regular customers.
                 */

                $cash_flow = $customer?->isBank()
                    ? CashFlowEnum::Deposit->reverse()
                    : CashFlowEnum::Deposit;

                $tran_type = $customer?->isBank()
                    ? TransactionTypeEnum::Deposit->reverse()
                    : TransactionTypeEnum::Deposit;

                $data = [
                    'customer_id' => $customer_id,
                    'date'        => $date,
                    'amount'      => $amount,
                ];

                $first = $this->saveTransaction(
                    $data,
                    $cash_flow,
                    $tran_type
                );

                $operation = $customer?->transactionOperation($cash_flow);
                $customer->updateBalance($amount, $operation);
            }

            if (! empty($tran['expense_amount'])) {
                $amount = $tran['expense_amount'];

                /**
                 * If the customer is a bank,
                 * the cash flow value will be reversed:
                 * - Deposit becomes Expense
                 * - Expense becomes Deposit
                 *
                 * This is because for bank customers, the meaning of deposit and expense
                 * is opposite compared to regular customers.
                 */

                $cash_flow = $customer?->isBank()
                    ? CashFlowEnum::Expense->reverse()
                    : CashFlowEnum::Expense;

                $tran_type = $customer->isBank()
                    ? TransactionTypeEnum::Expense->reverse()
                    : TransactionTypeEnum::Expense;

                $data = [
                    'customer_id' => $customer_id,
                    'date'        => $date,
                    'amount'      => $amount,
                ];

                $first = $this->saveTransaction(
                    $data,
                    $cash_flow,
                    $tran_type
                );

                $operation = $customer?->transactionOperation($cash_flow);
                $customer?->updateBalance($amount, $operation);
            }
        }

        return $first;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create', ['date' => $this->lastDate]);
    }
}
