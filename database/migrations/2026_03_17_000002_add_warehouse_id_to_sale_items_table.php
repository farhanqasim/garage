<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Store warehouse_id for claim lines so we can reverse claim_warehouse_items on sale delete/edit.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_items', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('item_id')->constrained('warehouses')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
            }
        });
    }
};
