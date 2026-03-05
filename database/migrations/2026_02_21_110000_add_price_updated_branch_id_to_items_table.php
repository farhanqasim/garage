<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'price_updated_branch_id')) {
                $table->unsignedBigInteger('price_updated_branch_id')->nullable()->after('updated_by');
                $table->foreign('price_updated_branch_id')->references('id')->on('branches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'price_updated_branch_id')) {
                $table->dropForeign(['price_updated_branch_id']);
            }
        });
    }
};
