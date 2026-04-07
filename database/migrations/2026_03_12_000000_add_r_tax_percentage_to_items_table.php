<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'r_tax_percentage')) {
                $table->decimal('r_tax_percentage', 5, 2)->default(0.05)->after('tax_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'r_tax_percentage')) {
                $table->dropColumn('r_tax_percentage');
            }
        });
    }
};
