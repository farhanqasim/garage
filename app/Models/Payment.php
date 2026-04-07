<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'supplier_id',
        'payment_method_id',
        'bank_account_id',
        'direction',
        'payment_date',
        'transaction_id',
        'transfer_receipt',
        'amount',
        'currency',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'sale_payments')
            ->withPivot('allocated_amount')
            ->withTimestamps();
    }

    public function purchases()
    {
        return $this->belongsToMany(Purchase::class, 'purchase_payments')
            ->withPivot('allocated_amount')
            ->withTimestamps();
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class, 'matched_payment_id');
    }

    public function scopeIncoming($query)
    {
        return $query->where('direction', 'in');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'out');
    }
}
