<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    protected $fillable = ['title'];

    protected $hidden = ['created_at', 'updated_at'];
}
