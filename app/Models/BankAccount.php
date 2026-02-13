<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_id',
        'branch_id',
        'user_id',
        'account_type',
        'account_title',
        'account_number',
        'iban',
        'branch_code',
        'ifsc_code',
        'opening_balance',
        'is_primary',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_primary' => 'boolean',
        'status' => 'boolean',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function getCurrentBalanceAttribute()
    {
        $openingBalance = $this->opening_balance ?? 0;
        
        // Include ALL transactions (both reconciled and unreconciled) for current balance
        // This ensures job payments and other transactions are immediately reflected in balance
        $credits = $this->bankTransactions()
            ->where('type', 'credit')
            ->sum('amount');
        
        $debits = $this->bankTransactions()
            ->where('type', 'debit')
            ->sum('amount');
        
        return $openingBalance + $credits - $debits;
    }

    public function scopeBankAccounts($query)
    {
        return $query->where('account_type', 'bank');
    }

    public function scopeCashAccounts($query)
    {
        return $query->where('account_type', 'cash');
    }
}
