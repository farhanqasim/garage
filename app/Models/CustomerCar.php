<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'car_manufacturer_id',
        'car_model_id',
        'plate_number',
        'make',
        'model',
        'year',
        'last_service_current_km',
        'last_service_next_km',
        'last_service_next_date',
        'last_service_daily_run_km',
        'last_service_interval_days',
        'last_service_interval_months',
        'last_visit_date',
    ];

    protected $casts = [
        'last_service_next_date' => 'date',
        'last_visit_date' => 'date',
        'last_service_current_km' => 'decimal:2',
        'last_service_next_km' => 'decimal:2',
        'last_service_daily_run_km' => 'decimal:2',
        'last_service_interval_days' => 'decimal:2',
        'last_service_interval_months' => 'decimal:2',
    ];

    // Relationship with Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function carManufacturer()
    {
        return $this->belongsTo(CarManufacturer::class, 'car_manufacturer_id');
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }
}
