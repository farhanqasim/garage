<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarWashJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'service_id',
        'worker_id',
        'customer_name',
        'vehicle_no',
        'mobile',
        'service_name',
        'price',
        'additional_prices',
        'worker_name',
        'status',
        'start_time',
        'end_time',
        'duration_seconds',
        'notes',
        'payment_method',
        'bank_id',
        'bank_account_id',
    ];

    protected $casts = [
        'additional_prices' => 'array',
        'price' => 'decimal:2',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    /**
     * Get the branch that owns this job
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the service for this job
     */
    public function service()
    {
        return $this->belongsTo(CarWashService::class, 'service_id');
    }

    /**
     * Get the worker assigned to this job
     */
    public function worker()
    {
        return $this->belongsTo(CarWashWorker::class, 'worker_id');
    }

    /**
     * Scope for active jobs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed jobs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for today's jobs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get the inspection for this job
     */
    public function inspection()
    {
        return $this->hasOne(CarWashInspection::class, 'job_id');
    }

    /**
     * Get the expense for this job
     */
    public function expense()
    {
        return $this->hasOne(CarWashExpense::class, 'job_id');
    }

    /**
     * Get the user who created this job
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bank (when commission transferred to bank)
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Get the bank account (when payment transferred to specific account)
     */
    public function bankAccount()
    {
        return $this->belongsTo(\App\Models\BankAccount::class);
    }
}
