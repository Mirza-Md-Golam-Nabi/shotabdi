<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $fillable = ['date', 'customer_id', 'product_id', 'quantity', 'rate', 'discount', 'amount'];


}
