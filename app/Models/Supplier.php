<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_img',
        'visiting_doc',
        'multiple_images',
        'voice_note',
        'names',
        'phones',
        'emails',
        'company',
        'email',
        'address',
        'area',
        'business_detail',
        'carnumber',
        'group_id',
        'password',
        'opening_balance',
        'as_of_date',
        'balance_type',
        'credit_limit_type',
        'credit_limit',
        'created_by',
        'branch_id',
        'is_temporary',
    ];

    protected $casts = [
        'names' => 'array',
        'phones' => 'array',
        'emails' => 'array',
        'business_detail' => 'array',
        'multiple_images' => 'array',
        'opening_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    protected $hidden = [
        'password',
    ];

    // Always return array for names
    public function getNamesAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : (json_decode($value, true) ?? []);
    }

    public function getBusinessDetailAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return [];
        }

        return is_array($value) ? $value : (json_decode($value, true) ?? []);
    }

    // Always return array for phones
    public function getPhonesAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : (json_decode($value, true) ?? []);
    }

    // Safe accessor for multiple_images
    public function getMultipleImagesAttribute($value)
    {
        if (is_null($value) || empty($value)) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (is_string($decoded) && json_decode($decoded, true) !== null) {
            return json_decode($decoded, true);
        }

        return [];
    }

    // Format date for display (DD/MM/YYYY)
    public function getAsOfDateFormattedAttribute()
    {
        if ($this->as_of_date) {
            try {
                return Carbon::parse($this->as_of_date)->format('d/m/Y');
            } catch (\Exception $e) {
                return $this->as_of_date;
            }
        }

        return null;
    }

    // Relationship with User who created the supplier
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship with Branch where supplier was created
    public function createdByBranch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Relationship with Edit History
    public function editHistory()
    {
        return $this->hasMany(SupplierEditHistory::class)->orderBy('created_at', 'desc');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
