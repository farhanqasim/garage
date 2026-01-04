<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'user_id',
        'invoice_number',
        'sale_date',
        'status',
        'subtotal',
        'order_tax',
        'order_tax_amount',
        'discount',
        'shipping',
        'grand_total',
        'payment_status',
        'paid_amount',
        'due_amount',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'order_tax' => 'decimal:2',
        'order_tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Generate invoice number
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV-';
        $lastSale = self::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastSale) {
            $lastNumber = intval(str_replace($prefix, '', $lastSale->invoice_number));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
