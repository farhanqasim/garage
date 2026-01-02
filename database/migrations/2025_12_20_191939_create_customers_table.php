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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('profile_img')->nullable();
            $table->json('names');
            $table->json('phones')->nullable();
            $table->string('company')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('carnumber')->nullable();
            $table->string('group_id')->nullable();
            $table->string('password');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->date('as_of_date')->nullable();
            $table->enum('balance_type', ['receive', 'pay'])->default('receive');
            $table->enum('credit_limit_type', ['custom', 'no_limit'])->default('no_limit');
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->string('voice_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
