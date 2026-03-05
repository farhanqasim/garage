<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseCart extends Model
{
    protected $table = 'purchase_cart';

    protected $fillable = [
        'user_id',
        'branch_id',
        'supplier_id',
        'item_id',
        'entry_type',
        'item_name',
        'warehouse_id',
        'quantity',
        'unit',
        'rate',
        'retail_price',
        'retail_price_base',
        'retail_pct',
        'discount',
        'tax_percentage',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'retail_price_base' => 'decimal:2',
        'retail_pct' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
