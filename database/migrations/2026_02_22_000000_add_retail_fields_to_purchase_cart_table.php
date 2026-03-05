<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            $table->decimal('retail_price', 15, 2)->nullable()->after('rate');
            $table->decimal('retail_price_base', 15, 2)->nullable()->after('retail_price');
            $table->decimal('retail_pct', 5, 2)->nullable()->after('retail_price_base');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_cart', function (Blueprint $table) {
            $table->dropColumn(['retail_price', 'retail_price_base', 'retail_pct']);
        });
    }
};
