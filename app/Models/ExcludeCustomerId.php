<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcludeCustomerId extends Model
{
    protected $fillable = [
        'customer_id',
        'reason',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
