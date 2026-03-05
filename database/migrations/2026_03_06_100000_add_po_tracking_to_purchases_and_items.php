<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'po_status')) {
                $table->string('po_status', 20)->nullable()->after('is_purchase_order'); // draft, partial, completed
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'ordered_quantity')) {
                $table->decimal('ordered_quantity', 12, 2)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('purchase_items', 'received_quantity')) {
                $table->decimal('received_quantity', 12, 2)->nullable()->after('ordered_quantity');
            }
            if (!Schema::hasColumn('purchase_items', 'purchase_order_item_id')) {
                $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_items')->nullOnDelete()->after('purchase_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('po_status');
        });
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropColumn(['ordered_quantity', 'received_quantity', 'purchase_order_item_id']);
        });
    }
};
