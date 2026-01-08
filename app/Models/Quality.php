<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quality extends Model
{
    use HasFactory;
        protected $fillable = [
        'name',
        'status',
        'type',
    ];

    public function item_quality()
    {
        return $this->hasOne(Item::class);
    }
}
