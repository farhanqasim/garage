<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        return $this->hasOne(Branch::class, 'user_id');
    }

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

}
