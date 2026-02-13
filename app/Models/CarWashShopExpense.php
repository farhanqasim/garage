<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarWashShopExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'expense_date',
        'category',
        'amount',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
