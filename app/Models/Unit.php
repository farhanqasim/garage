<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'allow_decimal',
        'decimal_after_point_digit',
        'status'
    ];
    
    protected $casts = [
        'decimal_after_point_digit' => 'integer',
    ];

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function childUnits()
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    // Many-to-many relationship for multiple base units
    public function baseUnits()
    {
        return $this->belongsToMany(Unit::class, 'unit_base_units', 'unit_id', 'base_unit_id')
            ->withPivot('multiplier')
            ->withTimestamps()
            ->orderBy('unit_base_units.id', 'asc'); // Preserve insertion order
    }

    // Reverse relationship - units that have this unit as base
    public function unitsWithThisAsBase()
    {
        return $this->belongsToMany(Unit::class, 'unit_base_units', 'base_unit_id', 'unit_id')
            ->withPivot('multiplier')
            ->withTimestamps();
    }

    public function item_unit()
    {
        return $this->hasOne(Item::class);
    }

    // Direct relationship to UnitConversion model
    public function conversions()
    {
        return $this->hasMany(UnitConversion::class, 'unit_id');
    }

    /**
     * Get all conversions for this unit with base unit details
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConversionsWithDetails()
    {
        return $this->conversions()->with('baseUnit')->get();
    }

    /**
     * Add a new conversion to this unit
     *
     * @param int $baseUnitId
     * @param float $multiplier
     * @return UnitConversion
     */
    public function addConversion($baseUnitId, $multiplier)
    {
        return UnitConversion::create([
            'unit_id' => $this->id,
            'base_unit_id' => $baseUnitId,
            'multiplier' => $multiplier,
        ]);
    }

    /**
     * Get conversion count for this unit
     *
     * @return int
     */
    public function getConversionCountAttribute()
    {
        return $this->conversions()->count();
    }

}
