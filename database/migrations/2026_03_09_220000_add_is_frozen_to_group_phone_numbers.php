<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('group_phone_numbers', 'is_frozen')) {
            Schema::table('group_phone_numbers', function (Blueprint $table) {
                $table->boolean('is_frozen')->default(false)->after('company_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('group_phone_numbers', 'is_frozen')) {
            Schema::table('group_phone_numbers', function (Blueprint $table) {
                $table->dropColumn('is_frozen');
            });
        }
    }
};
