<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'min_stock_level',
        'max_stock_level',
        'location',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'available_quantity' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
    ];

    /**
     * Get the warehouse that owns this item
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the item details
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Check if stock is low
     */
    public function isLowStock()
    {
        return $this->available_quantity <= $this->min_stock_level;
    }

    /**
     * Update available quantity automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($warehouseItem) {
            $warehouseItem->available_quantity = $warehouseItem->quantity - $warehouseItem->reserved_quantity;
        });
    }
}
