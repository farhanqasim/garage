<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'account_title',
        'account_number',
        'iban',
        'branch_code',
        'is_primary',
        'status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the bank that owns this account
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
