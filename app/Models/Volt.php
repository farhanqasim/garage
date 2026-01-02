<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volt extends Model
{
    use HasFactory;
        protected $fillable = [
        'name',
        'status',
    ];

    public function item_volt()
    {
        return $this->hasOne(Volt::class);
    }
}
