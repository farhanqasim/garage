<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Item Unit Conversion Model
 * 
 * This model represents the unit configuration for each item,
 * storing base unit and secondary units with conversion factors.
 */
class ItemUnitConversion extends Model
{
    use HasFactory;

    protected $table = 'item_unit_conversions';

    protected $fillable = [
        'item_id',
        'unit_id',
        'unit_role', // 'base' or 'secondary'
        'conversion_factor',
        'display_order',
        'is_active'
    ];

    protected $casts = [
        'conversion_factor' => 'decimal:8',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the item this conversion belongs to
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the unit for this conversion
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}

