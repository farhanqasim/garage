<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'api_enabled',
        'status',
        'logo',
    ];

    protected $casts = [
        'api_enabled' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get all bank accounts for this bank
     */
    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }
}
