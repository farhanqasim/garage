<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
        public function index()
        {
            // $this->creat_permission();       //use to create permission

            $roles = Role::where('name','!=','Super Admin')->orderBy('name','asc')->get();
            // return $roles;
            // $roles = Role::orderBy('name','asc')->get();

            return view('admin.roles.index', compact('roles'));
        }

        public function create()
        {
            $all_permissions  = Permission::orderBy('id','asc')->get();
            $permission_groups = User::getpermissionGroups();
            return view('admin.roles.create', compact('all_permissions','permission_groups'));
        }

}
