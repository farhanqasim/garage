<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('series')) {
            return;
        }

        Schema::table('series', function (Blueprint $table) {
            if (Schema::hasColumn('series', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('series')) {
            return;
        }

        Schema::table('series', function (Blueprint $table) {
            if (! Schema::hasColumn('series', 'status')) {
                $table->string('status')->default('active');
            }
        });
    }
};
