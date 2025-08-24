<?php
namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enums\CashFlowEnum;
use App\Enums\TransactionTypeEnum;
use App\Filament\Resources\TransactionResource;
use App\Filament\Services\CustomerService;
use App\Models\Transaction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected ?string $lastDate;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $this->lastDate = $data['date'];
        $first          = null;
        foreach ($data['transactions'] as $tran) {
            if (! empty($tran['deposit_amount'])) {
                $first = Transaction::create([
                    'customer_id'  => $tran['customer_id'],
                    'date'         => $data['date'],
                    'cash_flow_id' => CashFlowEnum::DEPOSIT,
                    'tran_type_id' => TransactionTypeEnum::DEPOSIT,
                    'amount'       => $tran['deposit_amount'],
                ]);

                $this->updateCustomerBalance($tran['customer_id'], $tran['deposit_amount'], 'subtract');
            }

            if (! empty($tran['expense_amount'])) {
                $first = Transaction::create([
                    'customer_id'  => $tran['customer_id'],
                    'date'         => $data['date'],
                    'cash_flow_id' => CashFlowEnum::EXPENSE,
                    'tran_type_id' => TransactionTypeEnum::EXPENSE,
                    'amount'       => $tran['expense_amount'],
                ]);

                $this->updateCustomerBalance($tran['customer_id'], $tran['expense_amount'], 'add');
            }
        }

        return $first;
    }

    private function updateCustomerBalance($customer_id, $balance, $operation = 'add')
    {
        (new CustomerService())->updateBalance($customer_id, $balance, $operation);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create', ['date' => $this->lastDate]);
    }
}
