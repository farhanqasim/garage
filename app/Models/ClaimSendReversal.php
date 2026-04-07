<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimSendReversal extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_item_id',
        'warehouse_id',
        'item_id',
        'quantity',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }
}
