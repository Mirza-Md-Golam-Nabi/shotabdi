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

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'cash_flow_id' => CashFlowEnum::class,
        'tran_type_id' => TransactionTypeEnum::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function stock_in(): BelongsTo
    {
        return $this->belongsTo(StockIn::class, 'stock_in_id', 'id');
    }

    public function stock_out(): BelongsTo
    {
        return $this->belongsTo(StockOut::class, 'stock_out_id', 'id');
    }

    public function getDetailAttribute(): string
    {
        if ($this->stock_in_id) {
            $product = $this->stock_in->product;
            $factor  = $product->id == 1 ? 30 : 1;
            $unit    = $product->id == 1 ? 'খাঁচি' : 'বস্তা';
            $quantity = $this->stock_in->quantity / $factor;
            $rate     = number_format($this->stock_in->rate);

            return "{$product->name} ({$quantity} {$unit}, {$rate} দর)";
        }

        if ($this->stock_out_id) {
            $product = $this->stock_out->product;
            $factor  = $product->id == 1 ? 30 : 1;
            $unit    = $product->id == 1 ? 'খাঁচি' : 'বস্তা';
            $quantity = $this->stock_out->quantity / $factor;
            $rate     = number_format($this->stock_out->rate);

            return "{$product->name} ({$quantity} {$unit}, {$rate} দর)";
        }

        if ($this->cash_flow_id === CashFlowEnum::DEPOSIT) {
            return 'জমা';
        }

        if ($this->cash_flow_id === CashFlowEnum::EXPENSE) {
            return 'খরচ';
        }

        return '';
    }

    public function effectOnBalance(Customer $customer): int
    {
        if ($this->stock_in_id && $this->cash_flow_id === CashFlowEnum::DEPOSIT) {
            if ($this->stock_in?->product_id == 1) {
                return $customer->isFarmer()
                    ? $this->amount
                    : -$this->amount;
            }
            return -$this->amount;
        }

        if ($this->stock_out_id && $this->cash_flow_id === CashFlowEnum::EXPENSE) {
            return -$this->amount;
        }

        if (in_array($this->cash_flow_id, [CashFlowEnum::DEPOSIT, CashFlowEnum::EXPENSE])) {
            return $this->amount;
        }

        return 0;
    }
}
