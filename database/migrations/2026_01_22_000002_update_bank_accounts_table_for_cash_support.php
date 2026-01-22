<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->enum('account_type', ['bank', 'cash'])->default('bank')->after('bank_id');
            $table->decimal('opening_balance', 15, 2)->default(0)->after('branch_code');
            $table->string('ifsc_code')->nullable()->after('branch_code');
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'opening_balance', 'ifsc_code']);
        });
    }
};
