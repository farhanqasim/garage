<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pehle existing foreign keys drop karein
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'bank_id')) {
                $table->dropForeign(['bank_id']);
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            // Old columns drop karein
            $table->dropColumn(['order_id', 'payment_method', 'bank_id']);
            
            // Naye columns add karein
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->after('customer_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('payment_method_id')->after('supplier_id')->constrained('payment_methods')->onDelete('restrict');
            $table->foreignId('bank_account_id')->nullable()->after('payment_method_id')->constrained('bank_accounts')->onDelete('set null');
            $table->enum('direction', ['in', 'out'])->default('in')->after('bank_account_id');
            $table->date('payment_date')->after('direction');
            $table->text('notes')->nullable()->after('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['payment_method_id']);
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn(['customer_id', 'supplier_id', 'payment_method_id', 'bank_account_id', 'direction', 'payment_date', 'notes']);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->enum('payment_method', ['card', 'bank', 'wallet', 'cash'])->default('cash');
            $table->foreignId('bank_id')->nullable()->constrained('banks')->onDelete('set null');
        });
    }
};
