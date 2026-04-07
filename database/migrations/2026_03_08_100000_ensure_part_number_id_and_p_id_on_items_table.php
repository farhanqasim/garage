<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ensure items table has part_number_id and p_id for Part Number / Product Name so saves persist.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'part_number_id')) {
                $table->unsignedBigInteger('part_number_id')->nullable()->after('category_id');
            }
            if (! Schema::hasColumn('items', 'p_id')) {
                $table->unsignedBigInteger('p_id')->nullable()->after('part_number_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'part_number_id')) {
                $table->dropColumn('part_number_id');
            }
            if (Schema::hasColumn('items', 'p_id')) {
                $table->dropColumn('p_id');
            }
        });
    }
};
