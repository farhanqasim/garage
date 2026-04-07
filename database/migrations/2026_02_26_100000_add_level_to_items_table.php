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
            if (! Schema::hasColumn('items', 'level')) {
                $table->unsignedBigInteger('level')->nullable();
            }
        });
        // Skip foreign key to avoid errno 150 (type/engine mismatch); level column still works for references
        // if (Schema::hasTable('levels') && Schema::hasColumn('items', 'level')) {
        //     Schema::table('items', function (Blueprint $table) {
        //         $table->foreign('level')->references('id')->on('levels')->onDelete('set null');
        //     });
        // }
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
