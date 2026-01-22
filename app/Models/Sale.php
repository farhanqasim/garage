<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'branch_id',
        'sale_date',
        'reference',
        'status',
        'subtotal',
        'order_tax',
        'discount',
        'shipping',
        'grand_total',
        'user_id',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'order_tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'sale_payments')
                    ->withPivot('allocated_amount')
                    ->withTimestamps();
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('sale_payments.allocated_amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->grand_total - $this->total_paid;
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->remaining_amount <= 0;
    }
}

