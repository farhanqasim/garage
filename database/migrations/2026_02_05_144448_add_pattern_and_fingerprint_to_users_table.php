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
        Schema::table('users', function (Blueprint $table) {
            $table->string('pattern_lock')->nullable()->after('password'); // Store pattern as comma-separated numbers (e.g., "0,1,2")
            $table->text('fingerprint_data')->nullable()->after('pattern_lock'); // Store fingerprint/biometric data (encrypted)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pattern_lock', 'fingerprint_data']);
        });
    }
};
