<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scale extends Model
{
    use HasFactory;
    protected $fillable = ['name','status'];

    public function item_scale()
    {
        return $this->hasOne(Item::class);
    }
}
