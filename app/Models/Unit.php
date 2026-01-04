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
        'define_base_unit',
        'base_unit_multiplier',
        'base_unit_id',
        'status'
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
            ->withTimestamps();
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

}
