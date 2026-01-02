<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartNumber extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
    ];

    public function item_part_number()
    {
        return $this->hasOne(Item::class);
    }

        public function part_number_vehical()
        {
            return $this->hasMany(VehicalType::class,'v_part_number_id');
        }
}
