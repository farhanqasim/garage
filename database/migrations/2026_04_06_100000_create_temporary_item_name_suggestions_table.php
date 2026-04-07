<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temporary_item_name_suggestions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->string('display_name', 500);
            $table->string('normalized_name', 500);
            $table->decimal('last_rate', 14, 2)->nullable();
            $table->string('last_quality', 255)->nullable();
            $table->unsignedInteger('use_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'normalized_name'], 'temp_item_names_branch_norm_unique');
            $table->index(['branch_id', 'normalized_name'], 'temp_item_names_branch_norm_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temporary_item_name_suggestions');
    }
};
