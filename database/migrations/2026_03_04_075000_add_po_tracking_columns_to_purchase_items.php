<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. Adds PO tracking columns to purchase_items if missing.
     */
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'ordered_quantity')) {
                $table->decimal('ordered_quantity', 12, 2)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('purchase_items', 'received_quantity')) {
                $table->decimal('received_quantity', 12, 2)->nullable()->after('ordered_quantity');
            }
            if (!Schema::hasColumn('purchase_items', 'purchase_order_item_id')) {
                $table->unsignedBigInteger('purchase_order_item_id')->nullable()->after('purchase_id');
                $table->foreign('purchase_order_item_id')->references('id')->on('purchase_items')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'purchase_order_item_id')) {
                $table->dropForeign(['purchase_order_item_id']);
                $table->dropColumn('purchase_order_item_id');
            }
            if (Schema::hasColumn('purchase_items', 'received_quantity')) {
                $table->dropColumn('received_quantity');
            }
            if (Schema::hasColumn('purchase_items', 'ordered_quantity')) {
                $table->dropColumn('ordered_quantity');
            }
        });
    }
};
