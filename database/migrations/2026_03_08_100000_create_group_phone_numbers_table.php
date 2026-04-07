<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('country_code', 10)->nullable();
            $table->string('phone_number', 50);
            $table->string('company_name', 255)->nullable();
            $table->boolean('is_frozen')->default(false);
            $table->timestamps();
        });

        Schema::table('group_phone_numbers', function (Blueprint $table) {
            $table->index('group_id');
            $table->index('is_frozen');
            $table->unique(['group_id', 'phone_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_phone_numbers');
    }
};
