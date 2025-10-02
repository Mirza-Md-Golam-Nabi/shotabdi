<?php
namespace App\Models;

use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = ['date', 'from', 'to', 'amount', 'tran_id_from', 'tran_id_to'];

    protected $hidden = ['created_at', 'updated_at'];

    public function from_customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'from', 'id');
    }

    public function to_customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'to', 'id');
    }

    public function from_transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'tran_id_from', 'id');
    }

    public function to_transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'tran_id_to', 'id');
    }
}
