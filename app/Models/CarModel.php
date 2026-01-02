<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    use HasFactory;
        protected $fillable = ['name','status'];

            public function vehical_model()
            {
                return $this->hasOne(VehicalType::class);
            }
}
