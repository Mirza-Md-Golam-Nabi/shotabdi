<?php
namespace App\Models;

use App\Models\StockIn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class, 'product_id', 'id');
    }

}
