<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_reminders', function (Blueprint $table) {
            $table->longText('task_image')->nullable()->after('task_audio');
        });
    }

    public function down(): void
    {
        Schema::table('task_reminders', function (Blueprint $table) {
            $table->dropColumn('task_image');
        });
    }
};
