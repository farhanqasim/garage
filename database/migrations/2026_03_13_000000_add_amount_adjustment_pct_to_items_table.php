<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'amount_adjustment_pct')) {
                $table->decimal('amount_adjustment_pct', 5, 2)->nullable()->after('r_tax_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'amount_adjustment_pct')) {
                $table->dropColumn('amount_adjustment_pct');
            }
        });
    }
};
