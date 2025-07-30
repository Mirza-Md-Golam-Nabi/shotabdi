<?php

namespace App\Models;

use App\Enums\TransactionTypeEnum;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = ['customer_id', 'date', 'type', 'amount'];

    protected $casts = [
        'type' => TransactionTypeEnum::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
