<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
public function all_employees()
{
    $authUser = Auth::user();

    if ($authUser->role === 'admin') {
        $branches = Branch::all();
        $users = User::where('role', 'employee')
                     ->with('branch')
                     ->paginate(10);
        return view('admin.employee.index', compact('users','branches'));
    }
    elseif ($authUser->role === 'user') {
        // show employees under the same branch as logged-in user
        $users = User::where('role', 'employee')
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
        'role'       => 'required|in:user,employee,customer,manager,salesman,purchaser',
        'password'   => 'required|min:6',
        'profile_img'=> 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'branch_id'  => 'nullable|exists:branches,id'
    ]);

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
    if ($request->hasFile('profile_img')) {
        $user->profile_img = saveSingleFile($request->file('profile_img'), 'profile');
    }
    $user->password = Hash::make($request->password);
    $user->save();

    // Attach user to selected branch in branch_user so they can select this branch at login
    if ($user->branch_id) {
        $user->assignedBranches()->syncWithoutDetaching([
            $user->branch_id => ['role' => $user->role ?? 'staff']
        ]);
    }

    return redirect()->back()->with('success', 'User created successfully! Branch access configured.');
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
