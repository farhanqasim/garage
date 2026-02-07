<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'plate_number',
        'make',
        'model',
        'year',
    ];

    // Relationship with Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
