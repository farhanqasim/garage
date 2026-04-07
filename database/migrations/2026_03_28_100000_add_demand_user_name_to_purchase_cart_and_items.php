<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_cart') && ! Schema::hasColumn('purchase_cart', 'demand_user_name')) {
            Schema::table('purchase_cart', function (Blueprint $table) {
                $table->string('demand_user_name', 191)->nullable()->after('item_name');
            });
        }
        if (Schema::hasTable('purchase_items') && ! Schema::hasColumn('purchase_items', 'demand_user_name')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->string('demand_user_name', 191)->nullable()->after('warehouse_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_cart') && Schema::hasColumn('purchase_cart', 'demand_user_name')) {
            Schema::table('purchase_cart', function (Blueprint $table) {
                $table->dropColumn('demand_user_name');
            });
        }
        if (Schema::hasTable('purchase_items') && Schema::hasColumn('purchase_items', 'demand_user_name')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropColumn('demand_user_name');
            });
        }
    }
};
