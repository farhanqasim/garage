<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Store which warehouse each cart item will be saved to when purchase is saved.
     */
    public function up(): void
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('item_id')->constrained('warehouses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
        });
    }
};
