<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_name',
        'branch_code',
        'manager_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'status',
        'location',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

        public function item_branch()
    {
        return $this->hasOne(Item::class);
    }
}
