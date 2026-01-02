<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    use HasFactory;
    protected $fillable = ['name','status'];

    public function item_lineitem()
    {
        return $this->hasOne(Item::class);
    }
}
