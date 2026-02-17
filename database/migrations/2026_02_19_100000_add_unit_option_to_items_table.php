<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store full unit dropdown value (e.g. "12_8" for CAN 2 Liter) so edit restores exact option.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('items', 'unit_option')) {
            Schema::table('items', function (Blueprint $table) {
                $table->string('unit_option', 64)->nullable()->after('unit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('items', 'unit_option')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('unit_option');
            });
        }
    }
};
