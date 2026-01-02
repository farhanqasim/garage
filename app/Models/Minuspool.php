<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minuspool extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
    ];

    public function item_minuspool()
    {
        return $this->hasOne(Item::class);
    }
}
