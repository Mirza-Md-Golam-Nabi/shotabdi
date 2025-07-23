<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'date', 'type', 'amount'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
