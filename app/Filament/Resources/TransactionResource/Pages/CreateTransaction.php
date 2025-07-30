<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use App\Models\Transaction;
use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TransactionResource;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected ?string $lastDate;

    protected function handleRecordCreation(array $data): Model
    {
        $this->lastDate = $data['date'];
        $first = null;
        foreach ($data['transactions'] as $tran) {
            if (!empty($tran['deposit_amount'])) {
                $first = Transaction::create([
                    'customer_id' => $tran['customer_id'],
                    'date' => $data['date'],
                    'type' => TransactionTypeEnum::DEPOSIT,
                    'amount' => $tran['deposit_amount']
                ]);
            }

            if (!empty($tran['expense_amount'])) {
                $first = Transaction::create([
                    'customer_id' => $tran['customer_id'],
                    'date' => $data['date'],
                    'type' => TransactionTypeEnum::EXPENSE,
                    'amount' => $tran['expense_amount']
                ]);
            }
        }

        return $first;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('create', ['date' => $this->lastDate]);
    }
}
