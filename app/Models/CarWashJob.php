<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\CustomerCar;
use App\Models\User;

class CarWashJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'service_id',
        'worker_id',
        'worker_user_id',
        'customer_id',
        'customer_car_id',
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
        'voice_note',
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
     * Get the customer (from customers table – linked by vehicle/customer_cars).
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the customer's vehicle (customer_cars) for this job.
     */
    public function customerCar()
    {
        return $this->belongsTo(CustomerCar::class, 'customer_car_id');
    }

    /**
     * Resolve customer name for display (from customer relation or stored value).
     */
    public function getCustomerNameAttribute(): ?string
    {
        if ($this->relationLoaded('customer') && $this->customer) {
            $names = $this->customer->names ?? [];
            if (is_array($names) && count($names) > 0) {
                return $names[0] ?? null;
            }
        }
        return $this->attributes['customer_name'] ?? null;
    }

    /**
     * Resolve vehicle number for display (from customer_car relation or stored value).
     */
    public function getVehicleNoAttribute(): ?string
    {
        if ($this->relationLoaded('customerCar') && $this->customerCar) {
            return $this->customerCar->plate_number;
        }
        return $this->attributes['vehicle_no'] ?? null;
    }

    /**
     * Resolve mobile for display (from customer relation or stored value).
     */
    public function getMobileAttribute(): ?string
    {
        if ($this->relationLoaded('customer') && $this->customer) {
            $phones = $this->customer->phones ?? [];
            if (is_array($phones) && count($phones) > 0) {
                return $phones[0] ?? null;
            }
        }
        return $this->attributes['mobile'] ?? null;
    }

    /**
     * Get the service for this job
     */
    public function service()
    {
        return $this->belongsTo(CarWashService::class, 'service_id');
    }

    /**
     * Get the worker assigned to this job (legacy: car_wash_workers table)
     */
    public function worker()
    {
        return $this->belongsTo(CarWashWorker::class, 'worker_id');
    }

    /**
     * Get the worker user assigned to this job (users table role=worker)
     */
    public function workerUser()
    {
        return $this->belongsTo(User::class, 'worker_user_id');
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
