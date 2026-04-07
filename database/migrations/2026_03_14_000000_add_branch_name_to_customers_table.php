<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'branch_name')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('branch_name', 255)->nullable()->after('branch_id');
            });
        }

        // Backfill: set branch_name from branches for existing customers
        if (Schema::hasColumn('customers', 'branch_id') && Schema::hasTable('branches')) {
            DB::table('customers')
                ->whereNotNull('branch_id')
                ->whereNull('branch_name')
                ->orderBy('id')
                ->chunk(100, function ($customers) {
                    foreach ($customers as $c) {
                        $name = DB::table('branches')->where('id', $c->branch_id)->value('branch_name');
                        if ($name !== null) {
                            DB::table('customers')->where('id', $c->id)->update(['branch_name' => $name]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'branch_name')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('branch_name');
            });
        }
    }
};
