<?php
namespace App\Models;

use App\Enums\CashFlowEnum;
use App\Enums\ProductEnum;
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

    public static function deleteByColumn(string $column_name, array $id): bool
    {
        return self::whereIn($column_name, $id)->delete() > 0;
    }

    public function getDetailAttribute(): string
    {
        if ($this->stock_in_id) {
            $product  = $this->stock_in->product;
            $factor   = $product->id == ProductEnum::Egg->value ? 30 : 1;
            $unit     = $product->id == ProductEnum::Egg->value ? 'খাঁচি' : 'বস্তা';
            $quantity = $this->stock_in->quantity / $factor;
            $rate     = number_format($this->stock_in->rate, 1);

            return "{$product->name} ({$quantity} {$unit}, {$rate} দর)";
        }

        if ($this->stock_out_id) {
            $product  = $this->stock_out->product;
            $factor   = $product->id == ProductEnum::Egg->value ? 30 : 1;
            $unit     = $product->id == ProductEnum::Egg->value ? 'খাঁচি' : 'বস্তা';
            $quantity = $this->stock_out->quantity / $factor;
            $rate     = number_format($this->stock_out->rate, 1);

            return "{$product->name} ({$quantity} {$unit}, {$rate} দর)";
        }

        if ($this->cash_flow_id === CashFlowEnum::Deposit) {
            return 'জমা';
        }

        if ($this->cash_flow_id === CashFlowEnum::Expense) {
            return 'খরচ';
        }

        return '';
    }

    public function effectOnBalance(Customer $customer): int
    {
        if ($this->stock_in_id && $this->cash_flow_id === CashFlowEnum::Deposit) {
            if ($this->stock_in?->product_id == ProductEnum::Egg->value) {
                return $customer->determineAmount($this->cash_flow_id, $this->amount);
                // deprecated
                // return $customer->isFarmer()
                //     ? $this->amount
                //     : -$this->amount;
            }
            return $customer->determineAmount($this->cash_flow_id, $this->amount);
            // deprecated
            // return -$this->amount;
        }

        if ($this->stock_out_id && $this->cash_flow_id === CashFlowEnum::Expense) {
            return $customer->determineAmount($this->cash_flow_id, $this->amount);
            // deprecated
            // return -$this->amount;
        }

        if (in_array($this->cash_flow_id, [CashFlowEnum::Deposit, CashFlowEnum::Expense])) {
            return $customer->determineAmount($this->cash_flow_id, $this->amount);
            // deprecated
            // if ($customer->isFarmer()) {
            //     return $this->cash_flow_id == CashFlowEnum::Deposit ? $this->amount : -$this->amount;
            // }

            // if ($customer->isNormal()) {
            //     return $this->cash_flow_id == CashFlowEnum::Deposit ? $this->amount : -$this->amount;
            // }

            // if ($customer->isEggSeller()) {
            //     return $this->cash_flow_id == CashFlowEnum::Deposit ? $this->amount : -$this->amount;
            // }

            // return $this->cash_flow_id == CashFlowEnum::Deposit ? -$this->amount : $this->amount;
        }

        return 0;
    }
}
