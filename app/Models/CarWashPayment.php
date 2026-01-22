<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarWashPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'worker_id',
        'payment_type',
        'amount',
        'payment_method_id',
        'bank_account_id',
        'from_account_id',
        'to_account_id',
        'transaction_id',
        'payment_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function worker()
    {
        return $this->belongsTo(CarWashWorker::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(BankAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(BankAccount::class, 'to_account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCommission($query)
    {
        return $query->where('payment_type', 'commission');
    }

    public function scopeCashTransfer($query)
    {
        return $query->where('payment_type', 'cash_transfer');
    }

    public function scopeBankTransfer($query)
    {
        return $query->where('payment_type', 'bank_transfer');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
