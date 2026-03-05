<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

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
        'role',
        'status',
        'branch_id',
        'last_selected_branch_id',
        'user_id_card_front',
        'user_id_card_back',
        'father_id_card_front',
        'father_id_card_back',
        'current_location',
        'house_photo_front',
        'credit_limit',
        'attachments',
        'salary_per_day',
        'salary_per_month',
        'salary_percentage',
        'commission',
        'bank_account_id',
        'bank_name',
        'bank_account_title',
        'bank_account_number',
        'bank_iban',
        'pattern_lock',
        'fingerprint_data',
        'claim_return_enabled',
        'theme_settings',
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
        'credit_limit' => 'decimal:2',
        'attachments' => 'array',
        'claim_return_enabled' => 'boolean',
        'salary_per_day' => 'decimal:2',
        'salary_per_month' => 'decimal:2',
        'salary_percentage' => 'decimal:2',
        'theme_settings' => 'array',
    ];


    /**
     * Get all branches assigned to this user (many-to-many)
     */
    public function assignedBranches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user', 'user_id', 'branch_id')
            ->withPivot('role')
            ->withTimestamps();
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
     * Get the WebAuthn credentials for the user.
     */
    public function webauthnCredentials()
    {
        return $this->hasMany(WebAuthnCredential::class);
    }

    /**
     * Get the cash account for this user
     */
    public function cashAccount()
    {
        return $this->hasOne(CashAccount::class);
    }

    /**
     * Get all cash transactions for this user
     */
    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class);
    }

    /**
     * Get cash transfers sent by this user
     */
    public function sentCashTransfers()
    {
        return $this->hasMany(CashTransfer::class, 'from_user_id');
    }

    /**
     * Get cash transfers received by this user
     */
    public function receivedCashTransfers()
    {
        return $this->hasMany(CashTransfer::class, 'to_user_id');
    }

    /**
     * Get bank transfers for this user
     */
    public function bankTransfers()
    {
        return $this->hasMany(BankTransfer::class);
    }

    /**
     * Car wash worker cash account (when user role = worker)
     */
    public function workerCashAccount()
    {
        return $this->hasOne(WorkerCashAccount::class);
    }

    /**
     * Linked bank account (for worker commission payments)
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }


    public static function getpermissionGroups()
    {
        $permission_groups = DB::table('permissions')
            ->orderBy('id','asc')
            ->select('group_name as name')
            ->groupBy('group_name')
            ->get();
        return $permission_groups;
    }
    public static function getpermissionsByGroupName($group_name)
    {
        $permissions = DB::table('permissions')
            ->select('name', 'id')
            ->where('group_name', $group_name)
            ->get();
        return $permissions;
    }
    public static function roleHasPermissions($role, $permissions)
    {
        $hasPermission = true;
        foreach ($permissions as $permission) {
            if (!$role->hasPermissionTo($permission->name)) {
                $hasPermission = false;
                return $hasPermission;
            }
        }
        return $hasPermission;
    }
    public function hasPermissionThroughRole($permission) : bool
    {
        if(auth()->check()){

            $roles = auth()->user()->getRoleNames();
            if (!empty($roles)) {

                $role = Role::where('name',$roles[0])->first();
                if ($role->hasPermissionTo($permission)) {
                    return true;
                }
                return false;
            }
            return false;
        }
        return false;
    }

    /**
     * Check if user has any linked transactions (sales, purchases, payments, etc.).
     * If true, user should be deactivated instead of deleted.
     */
    public function hasTransactionHistory(): bool
    {
        $id = $this->id;

        $checks = [
            DB::table('sales')->where('user_id', $id)->exists(),
            DB::table('payments')->where('user_id', $id)->exists(),
            DB::table('purchase_cart')->where('user_id', $id)->exists(),
            DB::table('cash_transactions')->where(function ($q) use ($id) {
                $q->where('user_id', $id)->orWhere('related_user_id', $id);
            })->exists(),
            DB::table('cash_transfers')->where(function ($q) use ($id) {
                $q->where('from_user_id', $id)->orWhere('to_user_id', $id);
            })->exists(),
            DB::table('car_wash_jobs')->where(function ($q) use ($id) {
                $q->where('user_id', $id)->orWhere('worker_user_id', $id);
            })->exists(),
            DB::table('car_wash_payments')->where(function ($q) use ($id) {
                $q->where('created_by', $id)->orWhere('worker_user_id', $id);
            })->exists(),
            DB::table('worker_cash_transactions')->where('user_id', $id)->exists(),
            DB::table('bank_transfers')->where('user_id', $id)->exists(),
            DB::table('car_wash_attendances')->where(function ($q) use ($id) {
                $q->where('user_id', $id)->orWhere('attended_user_id', $id);
            })->exists(),
            DB::table('suppliers')->where('created_by', $id)->exists(),
            DB::table('bank_accounts')->where('user_id', $id)->exists(),
            DB::table('car_wash_shop_expenses')->where('user_id', $id)->exists(),
        ];

        return in_array(true, $checks, true);
    }

}
