<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-roles')->only('index', 'show');
        $this->middleware('permission:create-roles')->only('create', 'store');
        $this->middleware('permission:edit-roles')->only('edit', 'update');
        $this->middleware('permission:delete-roles')->only('destroy');
    }

    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::where('name', '!=', 'Super Admin')
            ->orderBy('name', 'asc')
            ->get();
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $all_permissions = Permission::orderBy('name', 'asc')->get();
        $permission_groups = User::getpermissionGroups();
        
        return view('admin.roles.create', compact('all_permissions', 'permission_groups'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web',
            ]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Role created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        $role = Role::findOrFail($id);
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $all_permissions = Permission::orderBy('name', 'asc')->get();
        $permission_groups = User::getpermissionGroups();

        return view('admin.roles.show', compact('role', 'rolePermissions', 'all_permissions', 'permission_groups'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent editing Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot edit Super Admin role.');
        }

        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $all_permissions = Permission::orderBy('name', 'asc')->get();
        $permission_groups = User::getpermissionGroups();

        return view('admin.roles.edit', compact('role', 'rolePermissions', 'all_permissions', 'permission_groups'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Prevent editing Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot edit Super Admin role.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role->name = $request->name;
            $role->save();

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            return redirect()->route('roles.index')
                ->with('success', 'Role updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete Super Admin role.');
        }

        // Check if role is assigned to any users
        $usersWithRole = User::role($role->name)->count();
        if ($usersWithRole > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role. It is assigned to ' . $usersWithRole . ' user(s).');
        }

        try {
            $role->delete();
            return redirect()->route('roles.index')
                ->with('success', 'Role deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }

    /**
     * Create all permissions (Seeder method)
     * Run this once to create all permissions
     */
    public function createPermissions()
    {
        $permissionGroups = User::getpermissionGroups();
        
        try {
            DB::beginTransaction();

            foreach ($permissionGroups as $group => $permissions) {
                foreach ($permissions as $permission) {
                    Permission::firstOrCreate(
                        ['name' => $permission, 'guard_name' => 'web'],
                        ['name' => $permission, 'guard_name' => 'web']
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All permissions created successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create permissions: ' . $e->getMessage()
            ], 500);
        }
    }
}
