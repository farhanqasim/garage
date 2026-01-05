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
        Schema::create('warehouse_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('reserved_quantity', 15, 2)->default(0); // Reserved for orders
            $table->decimal('available_quantity', 15, 2)->default(0); // Available = quantity - reserved
            $table->decimal('min_stock_level', 15, 2)->default(0); // Minimum stock alert
            $table->decimal('max_stock_level', 15, 2)->nullable(); // Maximum stock level
            $table->string('location')->nullable(); // Rack/shelf location
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->unique(['warehouse_id', 'item_id']); // One record per item per warehouse
            $table->index('warehouse_id');
            $table->index('item_id');
            $table->index('available_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_items');
    }
};
