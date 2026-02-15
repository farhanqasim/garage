<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('assignee')->nullable();
            $table->string('priority')->default('Normal'); // Low, Normal, High, Critical
            $table->string('status')->default('Pending');  // Pending, In-Progress, Completed
            $table->json('responses')->nullable(); // [{ user_id, user_name, text, attachment_type, attachment_value, created_at }]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_reminders');
    }
};
