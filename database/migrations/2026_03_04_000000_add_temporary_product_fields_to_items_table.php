<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds support for temporary products in purchase flow (add when product not found).
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'is_temporary')) {
                $table->boolean('is_temporary')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('items', 'notes')) {
                $table->text('notes')->nullable()->after('is_temporary');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'is_temporary')) {
                $table->dropColumn('is_temporary');
            }
            if (Schema::hasColumn('items', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
