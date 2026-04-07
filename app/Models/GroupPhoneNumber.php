<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupPhoneNumber extends Model
{
    protected $fillable = [
        'group_id',
        'supplier_id',
        'country_code',
        'phone_number',
        'company_name',
        'is_frozen',
    ];

    protected $casts = [
        'is_frozen' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /** Numbers included in export and messaging (not frozen) */
    public function scopeActive($query)
    {
        return $query->where('is_frozen', false);
    }
}
