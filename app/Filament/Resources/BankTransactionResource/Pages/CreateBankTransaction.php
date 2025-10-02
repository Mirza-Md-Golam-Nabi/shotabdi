<?php
namespace App\Filament\Resources\BankTransactionResource\Pages;

use App\Enums\BankTransactionEnum;
use App\Filament\Resources\BankTransactionResource;
use App\Filament\Services\CustomerService;
use App\Filament\Traits\HandlesTransactions;
use App\Models\Transaction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBankTransaction extends CreateRecord
{
    use HandlesTransactions;

    protected static string $resource = BankTransactionResource::class;

    protected static bool $canCreateAnother = false;

    protected ?string $date;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->date = $data['date'];

        $tran = (new CustomerService())->bankTransaction($data);

        return $data + $tran;
    }

    public function handleRecordCreation(array $data): Model
    {
        if ($data['type'] != BankTransactionEnum::Self->value) {
            return static::getModel()::create($data);
        }

        return Transaction::orderBy('id', 'desc')->first();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create', ['date' => $this->date]);
    }
}
