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


    public function item_unit()
    {
        return $this->hasOne(Item::class);
    }

}
