<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'item_id',
        'warehouse_id',
        'entry_type',
        'quantity',
        'unit',
        'rate',
        'discount',
        'tax_percentage',
        'tax_amount',
        'total',
        'warranty',
        'line_note',
        'line_image',
        'temporary_item_name',
        'temporary_quality',
        'voice_transcript',
        'voice_data',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class);
    }

    public function warrantyProofs()
    {
        return $this->hasMany(SaleItemWarrantyProof::class);
    }

    public function warrantyCodes()
    {
        return $this->hasMany(SaleItemWarrantyCode::class);
    }
}
