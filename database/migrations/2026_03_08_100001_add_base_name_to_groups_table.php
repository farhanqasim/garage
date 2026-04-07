<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('base_name', 255)->nullable()->after('name');
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
        \DB::table('groups')->whereNull('base_name')->update(['base_name' => \DB::raw('name')]);
        Schema::table('groups', function (Blueprint $table) {
            $table->index('base_name');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['base_name']);
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->unique('name');
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('base_name');
        });
    }
};
