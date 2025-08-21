<?php
namespace App\Models;

use App\Enums\CashFlowEnum;
use App\Enums\TransactionTypeEnum;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = ['customer_id', 'date', 'cash_flow_id', 'tran_type_id', 'amount', 'stock_in_id', 'stock_out_id'];

    protected $casts = [
        'cash_flow_id' => CashFlowEnum::class,
        'tran_type_id' => TransactionTypeEnum::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
