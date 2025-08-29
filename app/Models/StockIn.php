<?php
namespace App\Models;

use App\Enums\AvailableEnum;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIn extends Model
{
    use SoftDeletes;

    protected $fillable = ['date', 'customer_id', 'product_id', 'quantity', 'rate', 'amount', 'discount', 'is_available'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'is_available' => AvailableEnum::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
