<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'is_purchase_order',
        'po_status',
        'supplier_id',
        'branch_id',
        'purchase_date',
        'reference',
        'status',
        'subtotal',
        'order_tax',
        'discount',
        'shipping',
        'grand_total',
        'description',
    ];

    protected $casts = [
        'is_purchase_order' => 'boolean',
        'purchase_date' => 'date',
        'subtotal' => 'decimal:2',
        'order_tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'purchase_payments')
                    ->withPivot('allocated_amount')
                    ->withTimestamps();
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('purchase_payments.allocated_amount');
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
