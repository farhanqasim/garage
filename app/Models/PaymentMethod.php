<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_urdu',
        'code',
        'description',
        'requires_bank_account',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requires_bank_account' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getDisplayNameAttribute()
    {
        return $this->name_urdu ?? $this->name;
    }
}
