<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitConversion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'unit_base_units';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unit_id',
        'base_unit_id',
        'multiplier',
    ];

    /**
     * Get the unit that owns this conversion.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get the base unit for this conversion.
     */
    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Get the conversion rate (how many base units = 1 main unit).
     *
     * @return float
     */
    public function getConversionRateAttribute()
    {
        return $this->multiplier ?? 1;
    }

    /**
     * Convert a quantity from main unit to base unit.
     *
     * @param float $quantity
     * @return float
     */
    public function convertToBase($quantity)
    {
        return $quantity * $this->multiplier;
    }

    /**
     * Convert a quantity from base unit to main unit.
     *
     * @param float $quantity
     * @return float
     */
    public function convertFromBase($quantity)
    {
        return $quantity / $this->multiplier;
    }
}

