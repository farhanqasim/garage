<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItemWarrantyCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'sale_item_id',
        'customer_id',
        'item_id',
        'warehouse_id',
        'sale_item_warranty_proof_id',
        'unit_no',
        'code',
        'code_norm',
        'is_final',
        'source',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'unit_no' => 'integer',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function proof()
    {
        return $this->belongsTo(SaleItemWarrantyProof::class, 'sale_item_warranty_proof_id');
    }
}
