<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'related_user_id',
        'amount',
        'direction',
        'type',
        'reference_id',
        'reference_table',
        'branch_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns this transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related user (for transfers)
     */
    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    /**
     * Get the branch associated with this transaction
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
