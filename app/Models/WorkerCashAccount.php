<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkerCashAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'worker_id',
        'user_id',
        'balance',
        'total_earned',
        'total_paid',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    public function worker()
    {
        return $this->belongsTo(CarWashWorker::class, 'worker_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(WorkerCashTransaction::class, 'worker_id', 'worker_id');
    }
}
