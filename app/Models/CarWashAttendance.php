<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarWashAttendance extends Model
{
    protected $fillable = [
        'worker_id',
        'attended_user_id',
        'attendance_type',
        'branch_id',
        'user_id',
        'captured_photo',
        'lat',
        'lng',
        'accuracy',
        'address',
        'maps_link',
        'captured_at',
        'device_info',
        'is_mock_location_detected',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'device_info' => 'array',
        'is_mock_location_detected' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'accuracy' => 'decimal:2',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(CarWashWorker::class, 'worker_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Who is marked (when attendee is a User, not a Worker) */
    public function attendedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attended_user_id');
    }
}
