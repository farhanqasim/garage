<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'purchase_order_item_id',
        'item_id',
        'warehouse_id',
        'quantity',
        'ordered_quantity',
        'received_quantity',
        'unit',
        'rate',
        'discount',
        'tax_percentage',
        'tax_amount',
        'unit_cost',
        'total_cost',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'ordered_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class);
    }
}
