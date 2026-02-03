<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarWashService extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'label',
        'base_price',
        'additional_prices',
        'icon',
        'color',
        'color_value',
        'is_default',
        'status',
        'sort_order',
        'inspection_compulsory',
        'is_per_foot',
    ];

    protected $casts = [
        'additional_prices' => 'array',
        'base_price' => 'decimal:2',
        'is_default' => 'boolean',
        'status' => 'boolean',
        'inspection_compulsory' => 'boolean',
        'is_per_foot' => 'boolean',
    ];

    /**
     * Get the branch that owns this service
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
