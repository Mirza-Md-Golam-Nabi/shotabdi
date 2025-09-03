<?php
namespace App\Models;

use App\Models\StockIn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class, 'product_id', 'id')
            ->withDefault([
                'quantity'  => 0,
                'available' => 0,
            ]);
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class, 'product_id', 'id');
    }

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class, 'product_id', 'id');
    }

    public static function selectOption(string $sort = 'asc'): Collection
    {
        return self::orderBy('name', $sort)->pluck('name', 'id');
    }

}
