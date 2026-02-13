<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('claim_return_enabled')->default(0);
        });

        // Default ON for Super Admin, Admin, Manager
        $allowedRoles = ['Super Admin', 'Admin', 'Manager'];
        foreach ($allowedRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                User::whereHas('roles', fn($q) => $q->where('roles.id', $role->id))
                    ->update(['claim_return_enabled' => 1]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('claim_return_enabled');
        });
    }
};
