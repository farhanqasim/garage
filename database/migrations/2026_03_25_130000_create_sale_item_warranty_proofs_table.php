<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_item_warranty_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('sale_items')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');

            $table->unsignedInteger('unit_no'); // 1..N within sale_item line
            $table->string('proof_code')->nullable();
            $table->string('proof_image')->nullable(); // stored path
            $table->timestamps();

            $table->unique(['sale_item_id', 'unit_no'], 'uniq_sale_item_unit_no');
            $table->index(['sale_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_item_warranty_proofs');
    }
};
