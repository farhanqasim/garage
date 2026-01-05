<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'warehouse_name',
        'warehouse_code',
        'address',
        'city',
        'state',
        'country',
        'phone',
        'email',
        'manager_name',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the branch that owns the warehouse
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get all items in this warehouse
     */
    public function items()
    {
        return $this->hasMany(WarehouseItem::class);
    }

    /**
     * Get total items count in warehouse
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    /**
     * Get total stock value
     */
    public function getTotalStockValueAttribute()
    {
        return $this->items()
            ->join('items', 'warehouse_items.item_id', '=', 'items.id')
            ->selectRaw('SUM(warehouse_items.quantity * COALESCE(items.packing_purchase_rate, 0)) as total')
            ->value('total') ?? 0;
    }
}
