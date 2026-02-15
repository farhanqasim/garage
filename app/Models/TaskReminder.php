<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskReminder extends Model
{
    protected $table = 'task_reminders';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'task_audio',
        'task_image',
        'branch_id',
        'assignee',
        'priority',
        'status',
        'responses',
    ];

    protected $casts = [
        'responses' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
