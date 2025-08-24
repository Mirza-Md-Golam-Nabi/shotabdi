<?php
namespace App\Filament\Services;

use App\Models\Customer;

class CustomerService
{
    public function updateBalance(
        int $customer_id,
        int $balance,
        string $operation = 'add'
    ): void {
        $operation == 'subtract'
        ? Customer::where('id', $customer_id)->decrement('balance', $balance)
        : Customer::where('id', $customer_id)->increment('balance', $balance);
    }
}
