<?php
namespace App\Models;

use App\Enums\FeedDisburseEnum;
use Illuminate\Database\Eloquent\Model;

class FeedDisburse extends Model
{
    protected $fillable = [
        'stock_out_id',
        'customer_id',
        'product_id',
        'previous_date',
        'next_date',
        'status',
    ];

    protected $casts = [
        'status' => FeedDisburseEnum::class,
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
