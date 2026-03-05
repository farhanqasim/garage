<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'tax_percentage')) {
                $table->decimal('tax_percentage', 5, 2)->default(0)->after('retail_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'tax_percentage')) {
                $table->dropColumn('tax_percentage');
            }
        });
    }
};
