<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatterySize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'height',
        'width',
        'length',
        'status',
    ];
}
