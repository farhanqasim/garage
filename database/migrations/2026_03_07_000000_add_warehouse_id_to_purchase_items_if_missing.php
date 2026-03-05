<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add warehouse_id to purchase_items if missing (avoids FK issues on strict DBs).
     */
    public function up(): void
    {
        if (Schema::hasColumn('purchase_items', 'warehouse_id')) {
            return;
        }
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('item_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('purchase_items', 'warehouse_id')) {
            return;
        }
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
    }
};
