<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_title',
        'account_number',
        'iban',
        'amount',
        'status',
        'requested_at',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that requested this transfer
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
