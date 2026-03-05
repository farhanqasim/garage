<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'level')) {
                $table->unsignedBigInteger('level')->nullable();
            }
        });
        // Add foreign key only if levels table exists and column was added
        if (Schema::hasTable('levels') && Schema::hasColumn('items', 'level')) {
            Schema::table('items', function (Blueprint $table) {
                try {
                    $table->foreign('level')->references('id')->on('levels')->onDelete('set null');
                } catch (\Exception $e) {
                    // Ignore if foreign already exists or column type doesn't match
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'level')) {
                try {
                    $table->dropForeign(['level']);
                } catch (\Exception $e) {
                    // Ignore if no foreign
                }
                $table->dropColumn('level');
            }
        });
    }
};
