<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerCashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'amount',
        'type',
        'reference_type',
        'reference_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function worker()
    {
        return $this->belongsTo(CarWashWorker::class, 'worker_id');
    }
}
