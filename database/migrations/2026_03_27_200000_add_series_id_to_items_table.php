<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'series_id')) {
                $table->foreignId('series_id')
                    ->nullable()
                    ->constrained('series')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'series_id')) {
                try {
                    $table->dropForeign(['series_id']);
                } catch (\Exception $e) {
                    // ignore
                }
                $table->dropColumn('series_id');
            }
        });
    }
};
