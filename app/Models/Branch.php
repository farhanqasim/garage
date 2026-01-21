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

    /**
     * Get all users assigned to this branch (many-to-many)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user')->withPivot('role')->withTimestamps();
    }

    public function item_branch()
    {
        return $this->hasOne(Item::class);
    }

    /**
     * Get the warehouse for this branch
     */
    public function warehouse()
    {
        return $this->hasOne(Warehouse::class);
    }
}
