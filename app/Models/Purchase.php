<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'supplier_id',
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
}
