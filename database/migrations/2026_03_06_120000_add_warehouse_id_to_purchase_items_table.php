<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Store warehouse per PO/purchase line so loading PO shows correct warehouse.
     */
    public function up(): void
    {
        if (Schema::hasColumn('purchase_items', 'warehouse_id')) {
            return;
        }
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('item_id')->constrained('warehouses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('purchase_items', 'warehouse_id')) {
            return;
        }
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
        });
    }
};
