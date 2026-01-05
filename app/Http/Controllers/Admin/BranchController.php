<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-branches')->only('all_branches');
        $this->middleware('permission:create-branches')->only('store_branches');
        $this->middleware('permission:edit-branches')->only('update_branches');
        $this->middleware('permission:delete-branches')->only('delete_branch');
        $this->middleware('permission:manage-branch-status')->only('updatebranchStatus');
    }

    public function all_branches()
    {
        $user = Auth::user();

        // ✅ Prepare users only if admin
        $users = [];
        if ($user->role === 'admin') {
            $users = User::where('role', 'user')->get();
        }

        // ✅ If admin → show all branches
        if ($user->role === 'admin') {
            $branches = Branch::with('user')->paginate(10);
            return view('admin.branches.index', compact('branches', 'users'));
        }

        // ✅ If normal user
        if ($user->role === 'user') {
            $branch = Branch::with('user')->where('user_id', $user->id)->first();

            if ($branch) {
                // User already added a branch → show only their branch
                return view('admin.branches.single', compact('branch', 'users'));
            } else {
                // No branch found → show add form in single view
                return view('admin.branches.single', compact('users'));
            }
        }

        // 🚫 Unauthorized access
        abort(403, 'Unauthorized access.');
    }

public function store_branches(Request $request)
{
    $user = Auth::user();
    $user_id = $request->user_id;


    // ✅ Check if this user already has a branch
    $existing = Branch::where('user_id', $user_id)->first();

    if ($existing) {
        // If the selected user already has a branch — block all except admin trying for new users
        return redirect()->route('all.branches')->with('error', 'This user already has a branch. Only new users can have a branch added.');
    }

    // ✅ Validate input
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'branch_name' => 'required|string|max:255',
        'branch_code' => 'required|string|max:255|unique:branches,branch_code',
        'manager_name' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
        'state' => 'nullable|string|max:255',
        'country' => 'nullable|string|max:255',
        'location' => 'nullable|string|max:255',
    ]);

    // ✅ Create new branch
    $branch = new Branch();
    $branch->user_id = $user_id;
    $branch->branch_name = $request->branch_name;
    $branch->branch_code = $request->branch_code;
    $branch->manager_name = $request->manager_name;
    $branch->email = $request->email;
    $branch->phone = $request->phone;
    $branch->address = $request->address;
    $branch->city = $request->city;
    $branch->state = $request->state;
    $branch->country = $request->country ?? 'Pakistan';
    $branch->location = $request->location;
    $branch->status = 'inactive';
    $branch->save();

    return redirect()->route('all.branches')->with('success', 'Branch created successfully for new user!');
}


    public function updatebranchStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Branch::findOrFail($id);

        // ✅ Optional: Authorization check
        $user = Auth::user();
        if ($user->role !== 'admin' && $model->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $model->status = $request->status;
        $model->save();

        return redirect()->back()->with('success', 'Status updated successfully');
    }

    public function update_branches(Request $request, $id)
    {
        $user = Auth::user();

        // ✅ Find the branch to update
        $branch = Branch::findOrFail($id);

        // ✅ Authorization: restrict normal users from updating other users' branches
        if ($user->role === 'user' && $branch->user_id !== $user->id) {
            return redirect()->route('all.branches')->with('error', 'Unauthorized access.');
        }

        // ✅ Handle user_id update only for admins
        $user_id = $branch->user_id;
        if ($user->role === 'admin' && $request->user_id) {
            // Check if new user already has a branch
            $existing = Branch::where('user_id', $request->user_id)->where('id', '!=', $id)->first();
            if ($existing) {
                return redirect()->route('all.branches')->with('error', 'The selected user already has a branch.');
            }
            $user_id = $request->user_id;
        }

        // ✅ Validate input
        $request->validate([
            'user_id' => 'nullable|exists:users,id', // Only for admins
            'branch_name' => 'required|string|max:255',
            'branch_code' => 'required|string|max:255|unique:branches,branch_code,' . $branch->id,
            'manager_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        // ✅ Update fields
        $branch->user_id = $user_id;
        $branch->branch_name = $request->branch_name;
        $branch->branch_code = $request->branch_code;
        $branch->manager_name = $request->manager_name;
        $branch->email = $request->email;
        $branch->phone = $request->phone;
        $branch->address = $request->address;
        $branch->city = $request->city;
        $branch->state = $request->state;
        $branch->country = $request->country ?? 'Pakistan';
        $branch->location = $request->location;
        // Keep existing status
        $branch->save();

        // ✅ Redirect with success
        return redirect()->route('all.branches')->with('success', 'Branch updated successfully!');
    }



    public function delete_branch($id)
    {
        $user = Auth::user();
        // ✅ Find the branch to delete
        $branch = Branch::findOrFail($id);
        // ✅ Authorization: restrict normal users from deleting other users' branches
        if ($user->role === 'user' && $branch->user_id !== $user->id) {
            return redirect()->route('all.branches')->with('error', 'Unauthorized access.');
        }
        // ✅ Delete the branch
        $branch->delete();
        // ✅ Redirect with success
        return redirect()->route('all.branches')->with('success', 'Branch deleted successfully!');
    }


}