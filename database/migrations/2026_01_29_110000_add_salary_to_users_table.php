<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('salary_per_day', 15, 2)->nullable()->after('attachments');
            $table->decimal('salary_per_month', 15, 2)->nullable()->after('salary_per_day');
            $table->decimal('salary_percentage', 8, 2)->nullable()->after('salary_per_month');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['salary_per_day', 'salary_per_month', 'salary_percentage']);
        });
    }
};
