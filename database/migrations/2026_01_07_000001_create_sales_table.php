<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('invoice_number')->unique();
            $table->date('sale_date');
            $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');
            
            // Financial fields
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('order_tax', 15, 2)->default(0);
            $table->decimal('order_tax_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('shipping', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            
            // Payment
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('paid');
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('due_amount', 15, 2)->default(0);
            
            // Additional info
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

