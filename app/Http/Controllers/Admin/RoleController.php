<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::where('name', '!=', 'Super Admin')->orderBy('name', 'asc')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $all_permissions = Permission::orderBy('id', 'asc')->get();
        $permission_groups = $this->getPermissionGroups();
        return view('admin.roles.create', compact('all_permissions', 'permission_groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array|min:1',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $all_permissions = Permission::orderBy('id', 'asc')->get();
        $permission_groups = $this->getPermissionGroups();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'all_permissions', 'permission_groups', 'rolePermissions'));
    }

    protected function getPermissionGroups(): array
    {
        $groups = [];
        foreach (User::getpermissionGroups() as $g) {
            $groupName = $g->name ?? null;
            if (!$groupName) continue;
            $perms = User::getpermissionsByGroupName($groupName);
            $groups[$groupName] = $perms;
        }
        return $groups;
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
        ]);

        $role->name = $request->name;
        $role->save();

        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->back()->with('success', 'Role deleted successfully');
    }

    public function edit_access_role(string $id)
    {
        return redirect()->route('all.users', ['edit' => $id]);
    }

    public function update_access_role(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        DB::beginTransaction();
        try {
            $user->syncRoles([$request->role]);
            $user->save();
            DB::commit();
            return redirect()->route('all.users')->with('success', 'User role updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function admin_delete_customer(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        DB::beginTransaction();
        try {
            $user->roles()->detach();
            $user->delete();
            DB::commit();
            return redirect()->route('all.users')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
