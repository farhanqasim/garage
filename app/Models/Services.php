<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'details'];

    protected $casts = [
        'details' => 'array',
    ];

    public function item_servies()
    {
        return $this->hasOne(Item::class);
    }
}
