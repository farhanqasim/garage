<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'branches';

    protected $fillable = [
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


    /**
     * Get all users assigned to this branch (many-to-many)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user', 'branch_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
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
