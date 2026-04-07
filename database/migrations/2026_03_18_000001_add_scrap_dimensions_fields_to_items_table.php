<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Optional physical dimensions for scrap items (store as entered, with unit).
            $table->decimal('scrap_dim_width', 10, 2)->nullable()->after('weight_for_delivery');
            $table->decimal('scrap_dim_height', 10, 2)->nullable()->after('scrap_dim_width');
            $table->decimal('scrap_dim_length', 10, 2)->nullable()->after('scrap_dim_height');
            $table->decimal('scrap_dim_depth', 10, 2)->nullable()->after('scrap_dim_length');
            $table->string('scrap_dim_unit', 10)->nullable()->after('scrap_dim_depth');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'scrap_dim_width',
                'scrap_dim_height',
                'scrap_dim_length',
                'scrap_dim_depth',
                'scrap_dim_unit',
            ]);
        });
    }
};
