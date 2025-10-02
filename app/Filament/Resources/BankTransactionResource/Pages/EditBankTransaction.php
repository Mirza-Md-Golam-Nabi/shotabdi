<?php
namespace App\Filament\Resources\BankTransactionResource\Pages;

use App\Enums\BankTransactionEnum;
use App\Enums\CashFlowEnum;
use App\Filament\Resources\BankTransactionResource;
use App\Filament\Services\CustomerService;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBankTransaction extends EditRecord
{
    protected static string $resource = BankTransactionResource::class;

    public ?string $date = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $rec        = $this->record;
        $this->date = $data['date'];

        if ($rec->from_customer->isBank()) {
            $type        = BankTransactionEnum::BankToCustomer;
            $from_bank   = $data['from'];
            $to_customer = $data['to'];

        } else {
            $type          = BankTransactionEnum::CustomerToBank;
            $from_customer = $data['from'];
            $to_bank       = $data['to'];

        }

        $data['type']          = $type->value;
        $data['from_bank']     = $from_bank ?? null;
        $data['to_customer']   = $to_customer ?? null;
        $data['from_customer'] = $from_customer ?? null;
        $data['to_bank']       = $to_bank ?? null;
        $data['cash_flow_id']  = $cash_flow_id ?? null;

        $data = collect($data)
            ->filter()
            ->toArray();

        return $data;
    }

    public function handleRecordUpdate(Model $record, array $data): Model
    {
        $this->transactionRollback($record);
        $tran = (new CustomerService())->bankTransaction($data);

        $data = $data + $tran;

        $record->update($data);

        return $record;
    }

    protected function transactionRollback(Model $record)
    {
        $rec           = $record; // bank_transactions table data
        $amount        = $rec->amount;
        $from_customer = $rec->from_customer;
        $to_customer   = $rec->to_customer;

        if ($from_customer->isBank()) {
            $from_cash_flow = CashFlowEnum::Expense;
            $to_cash_flow   = CashFlowEnum::Expense;

        } else {
            $from_cash_flow = CashFlowEnum::Deposit;
            $to_cash_flow   = CashFlowEnum::Deposit;

        }

        $from_operation = $from_customer?->transactionOperation($from_cash_flow, true);
        $to_operation   = $to_customer?->transactionOperation($to_cash_flow, true);

        $from_customer?->updateBalance($amount, $from_operation);
        $to_customer?->updateBalance($amount, $to_operation);

        Transaction::deleteByColumn('id', [$rec->tran_id_from, $rec->tran_id_to]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(false),
        ];
    }
}
