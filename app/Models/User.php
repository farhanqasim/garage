<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_img',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function branches()
    {
        return $this->hasOne(Branch::class);
    }

public function user_items()
{
    return $this->hasMany(Item::class, 'user_id');
}

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get permission groups for organizing permissions in views
     */
    public static function getpermissionGroups()
    {
        return [
            'Dashboard' => [
                'view-dashboard',
                'view-reports',
                'view-analytics',
            ],
            'Items Management' => [
                'view-items',
                'create-items',
                'edit-items',
                'delete-items',
                'duplicate-items',
                'view-item-details',
                'export-items',
                'import-items',
            ],
            'Branches' => [
                'view-branches',
                'create-branches',
                'edit-branches',
                'delete-branches',
                'manage-branch-status',
                'view-all-branches',
            ],
            'Sales' => [
                'view-sales',
                'create-sales',
                'edit-sales',
                'delete-sales',
                'view-sales-reports',
                'process-sales-return',
            ],
            'Purchases' => [
                'view-purchases',
                'create-purchases',
                'edit-purchases',
                'delete-purchases',
                'view-purchase-reports',
            ],
            'Customers' => [
                'view-customers',
                'create-customers',
                'edit-customers',
                'delete-customers',
                'view-customer-details',
            ],
            'Suppliers' => [
                'view-suppliers',
                'create-suppliers',
                'edit-suppliers',
                'delete-suppliers',
                'view-supplier-details',
            ],
            'Users' => [
                'view-users',
                'create-users',
                'edit-users',
                'delete-users',
                'manage-user-roles',
            ],
            'Roles & Permissions' => [
                'view-roles',
                'create-roles',
                'edit-roles',
                'delete-roles',
                'assign-permissions',
            ],
            'POS' => [
                'access-pos',
                'process-pos-sales',
                'view-pos-reports',
            ],
            'Categories' => [
                'view-categories',
                'create-categories',
                'edit-categories',
                'delete-categories',
            ],
            'Settings' => [
                'view-settings',
                'edit-settings',
                'manage-system-settings',
            ],
        ];
    }

}
