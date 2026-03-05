<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Persist last selected branch so it restores after standby/session expiry.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_selected_branch_id')) {
                $table->unsignedBigInteger('last_selected_branch_id')->nullable()->after('branch_id');
                $table->foreign('last_selected_branch_id')->references('id')->on('branches')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_selected_branch_id')) {
                $table->dropForeign(['last_selected_branch_id']);
                $table->dropColumn('last_selected_branch_id');
            }
        });
    }
};
