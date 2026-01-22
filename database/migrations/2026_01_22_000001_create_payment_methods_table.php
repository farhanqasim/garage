<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_urdu')->nullable();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('requires_bank_account')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Pakistan ke Payment Methods
        DB::table('payment_methods')->insert([
            ['name' => 'Cash', 'name_urdu' => 'نقد', 'code' => 'cash', 'description' => 'Cash payment', 'requires_bank_account' => false, 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bank Transfer', 'name_urdu' => 'بینک ٹرانسفر', 'code' => 'bank_transfer', 'description' => 'Bank to bank transfer', 'requires_bank_account' => true, 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NIFT', 'name_urdu' => 'نیفٹ', 'code' => 'nift', 'description' => 'National Institutional Facilitation Technologies', 'requires_bank_account' => true, 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IBFT', 'name_urdu' => 'آئی بی ایف ٹی', 'code' => 'ibft', 'description' => 'Inter Bank Fund Transfer', 'requires_bank_account' => true, 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cheque', 'name_urdu' => 'چیک', 'code' => 'cheque', 'description' => 'Cheque payment', 'requires_bank_account' => true, 'is_active' => true, 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Credit Card', 'name_urdu' => 'کریڈٹ کارڈ', 'code' => 'credit_card', 'description' => 'Credit card payment', 'requires_bank_account' => false, 'is_active' => true, 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Debit Card', 'name_urdu' => 'ڈیبٹ کارڈ', 'code' => 'debit_card', 'description' => 'Debit card payment', 'requires_bank_account' => false, 'is_active' => true, 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'JazzCash', 'name_urdu' => 'جازکیش', 'code' => 'jazzcash', 'description' => 'JazzCash mobile wallet', 'requires_bank_account' => false, 'is_active' => true, 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'EasyPaisa', 'name_urdu' => 'ایزی پیسہ', 'code' => 'easypaisa', 'description' => 'EasyPaisa mobile wallet', 'requires_bank_account' => false, 'is_active' => true, 'sort_order' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'UPaisa', 'name_urdu' => 'یو پیسہ', 'code' => 'upaisa', 'description' => 'UPaisa mobile wallet', 'requires_bank_account' => false, 'is_active' => true, 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
