<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarWashInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'branch_id',
        'inspection_items',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'inspection_items' => 'array',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the job that owns the inspection
     */
    public function job()
    {
        return $this->belongsTo(CarWashJob::class);
    }

    /**
     * Get the branch that owns the inspection
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
