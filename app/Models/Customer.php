<?php
namespace App\Models;

use App\Enums\FarmerEnum;
use App\Models\StockIn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'mobile', 'address', 'balance', 'is_farmer'];

    protected $casts = [
        'is_farmer' => FarmerEnum::class,
    ];

    public function getIsFarmerBoolAttribute(): bool
    {
        return $this->is_farmer->value;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id', 'id');
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(StockIn::class, 'customer_id', 'id');
    }
}
