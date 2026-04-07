<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_item_warranty_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');

            $table->foreignId('sale_item_warranty_proof_id')
                ->nullable()
                ->constrained('sale_item_warranty_proofs')
                ->nullOnDelete();

            $table->unsignedInteger('unit_no')->nullable(); // 1..N within sale_item line

            $table->string('code'); // stored as provided (trimmed)
            $table->string('code_norm'); // normalized for lookup
            $table->boolean('is_final')->default(false); // final user-confirmed code
            $table->string('source')->default('unknown'); // final|scanned|ocr|legacy

            $table->timestamps();

            $table->index(['code_norm'], 'idx_warranty_code_norm');
            $table->index(['customer_id', 'sale_id']);
            $table->index(['sale_item_id', 'is_final']);
            $table->unique(['sale_item_id', 'code_norm'], 'uniq_sale_item_code_norm');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_warranty_codes');
    }
};
