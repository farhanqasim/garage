<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
public function all_employees()
{
    $authUser = Auth::user();

    if ($authUser->role === 'admin') {
        $branches = Branch::all();
        $spatieRoles = Role::where('name', '!=', 'Super Admin')->orderBy('name')->get();
        $users = User::whereIn('role', ['employee', 'worker'])
                     ->with('branch', 'roles')
                     ->paginate(10);
        return view('admin.employee.index', compact('users','branches', 'spatieRoles'));
    }
    elseif ($authUser->role === 'user') {
        // show employees under the same branch as logged-in user
        $users = User::whereIn('role', ['employee', 'worker'])
                     ->where('branch_id', $authUser->branch_id)
                     ->with('branch')
                     ->get();
        return view('admin.employee.branchemployee', compact('users'));
    }
    else {
        // employee can see only their own record
        $users = User::where('id', $authUser->id)
                     ->with('branch')
                     ->get();
        return view('admin.employee.singleemployee', compact('users'));
    }
}

public function post_employees(Request $request)
{
  
    $validated = $request->validate([
        'name'       => 'required|string|max:255',
        'email'      => 'required|email|unique:users,email',
        'phone'      => 'nullable|array',
        'phone.*'    => 'nullable|string|max:50',
        'phone_name' => 'nullable|array',
        'phone_name.*' => 'nullable|string|max:100',
        'role'       => 'required|in:user,employee,customer,manager,salesman,purchaser,worker',
        'spatie_role' => 'nullable|string|exists:roles,name',
        'password'   => 'required|min:6',
        'profile_img'=> 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'branch_id'  => 'nullable|exists:branches,id',
        'branch_ids' => 'nullable|array',
        'branch_ids.*' => 'exists:branches,id',
        'salary_per_day' => 'nullable|numeric|min:0',
        'salary_per_month' => 'nullable|numeric|min:0',
        'salary_percentage' => 'nullable|numeric|min:0|max:100',
        'commission' => 'nullable|numeric|min:0|max:100',
    ]);

    try {
        DB::beginTransaction();

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $phones = is_array($request->phone) ? $request->phone : [];
        $names = is_array($request->phone_name) ? $request->phone_name : [];
        $pairs = [];
        for ($i = 0; $i < max(count($phones), count($names)); $i++) {
            $num = isset($phones[$i]) ? trim((string) $phones[$i]) : '';
            $name = isset($names[$i]) ? trim((string) $names[$i]) : '';
            if ($num !== '' || $name !== '') {
                $pairs[] = $name !== '' ? $name . '|' . $num : $num;
            }
        }
        $user->phone = !empty($pairs) ? implode(',', $pairs) : null;
        $user->role = $request->role;
        $user->status = 'inactive';
        $user->branch_id = $request->branch_id ?: null;
        $user->salary_per_day = $request->filled('salary_per_day') ? (float) $request->salary_per_day : null;
        $user->salary_per_month = $request->filled('salary_per_month') ? (float) $request->salary_per_month : null;
        $user->salary_percentage = $request->filled('salary_percentage') ? (float) $request->salary_percentage : null;
        $user->commission = $request->filled('commission') ? (float) $request->commission : null;
        if ($request->hasFile('profile_img')) {
            $user->profile_img = saveSingleFile($request->file('profile_img'), 'profile');
        }
        $user->password = Hash::make($request->password);
        $user->save();

        // Assign Spatie role (for permissions) if provided
        if ($request->filled('spatie_role')) {
            $user->assignRole($request->spatie_role);
        }

        // Multiple branch access: primary branch_id + all checked branch_ids[]
        $branchIds = array_values(array_unique(array_filter(
            array_merge(
                $request->branch_id ? [$request->branch_id] : [],
                is_array($request->branch_ids) ? $request->branch_ids : []
            )
        )));
        $pivot = collect($branchIds)->mapWithKeys(fn ($bid) => [$bid => ['role' => $user->role ?? 'staff']])->all();
        $user->assignedBranches()->sync($pivot);
        if (!$user->branch_id && !empty($branchIds)) {
            $user->update(['branch_id' => (int) $branchIds[0]]);
        }

        // For role=worker: create WorkerCashAccount so commission (job complete) can be credited and paid later
        if ($user->role === 'worker') {
            \App\Models\WorkerCashAccount::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'total_earned' => 0, 'total_paid' => 0]
            );
        }

        DB::commit();
        return redirect()->back()->with('success', 'User created successfully! Branch access configured.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('EmployeeController@post_employees save failed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()
            ->withInput($request->except('password'))
            ->with('error', 'User save failed: ' . $e->getMessage());
    }
}



    public function updateemployeesStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = User::findOrFail($id);
        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'Status updated successfully');
    }

}
