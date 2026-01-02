<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarManufacturer extends Model
{
    use HasFactory;
        protected $fillable = ['name','status'];

        public function vehical_manufacture()
        {
            return $this->hasOne(VehicalType::class);
        }
}
