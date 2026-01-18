<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarWashExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'branch_id',
        'expense_items',
        'total_amount',
    ];

    protected $casts = [
        'expense_items' => 'array',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the job that owns the expense
     */
    public function job()
    {
        return $this->belongsTo(CarWashJob::class);
    }

    /**
     * Get the branch that owns the expense
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
