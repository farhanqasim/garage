<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EngineCc extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
    ];

        public function vehical_engincc()
            {
                return $this->hasOne(VehicalType::class);
            }
}
