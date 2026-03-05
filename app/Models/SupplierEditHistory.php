<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierEditHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'edited_by',
        'branch_id',
        'changes',
        'notes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y') : '';
    }

    public function getFormattedTimeAttribute()
    {
        return $this->created_at ? $this->created_at->format('H:i:s') : '';
    }
}
