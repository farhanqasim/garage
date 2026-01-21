<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'payment_method',
        'bank_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that made this payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bank associated with this payment (if payment_method is 'bank')
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
