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
        'phone'      => 'nullable|string|max:20',
        'role'       => 'required|in:user,employee,customer',
        'password'   => 'required|min:6',
        'profile_img'=> 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'branch_id'  => 'nullable|exists:branches,id'
    ]);

    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->role = $request->role;
    $user->status = 'inactive';
    if ($request->hasFile('profile_img')) {
        $user->profile_img = saveSingleFile($request->file('profile_img'), 'profile');
    }
    // Only save branch_id if employee or customer
    if (in_array($request->role, ['employee', 'customer'])) {
        $user->branch_id = $request->branch_id;
    }
    $user->password = Hash::make($request->password);
    $user->save();

    return redirect()->back()->with('success', 'Employee added successfully!');
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
