<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function all_branches()
    {
        try {
            $user = Auth::user();

            // ✅ Prepare users only if admin
            $users = [];
            if ($user->role === 'admin') {
                $users = User::where('role', 'user')->get();
            }

            // ✅ If admin → show all branches with assigned users
            if ($user->role === 'admin') {
                $branches = Branch::with(['user', 'users'])->paginate(10);
                return view('admin.branches.index', compact('branches', 'users'));
            }

            // ✅ If normal user - check owner OR assigned branches
            if ($user->role === 'user') {
                $branch = Branch::with(['user', 'users'])->where('user_id', $user->id)->first();
                
                // If no branch as owner, check assigned branches
                if (!$branch) {
                    try {
                        $assignedBranch = $user->assignedBranches()->first();
                        if ($assignedBranch) {
                            $branch = Branch::with(['user', 'users'])->find($assignedBranch->id);
                        }
                    } catch (\Exception $e) {
                        // If branch_user table doesn't exist, continue without assigned branches
                        Log::warning('Branch user relationship error: ' . $e->getMessage());
                    }
                }

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
        } catch (\Exception $e) {
            Log::error('BranchController all_branches error: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'An error occurred. Please check if migrations are run.');
        }
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

    // Auto-create warehouse for this branch
    $warehouseCode = 'WH-' . strtoupper(Str::random(6));
    Warehouse::create([
        'branch_id' => $branch->id,
        'warehouse_name' => $branch->branch_name . ' Warehouse',
        'warehouse_code' => $warehouseCode,
        'address' => $branch->address,
        'city' => $branch->city,
        'state' => $branch->state,
        'country' => $branch->country,
        'phone' => $branch->phone,
        'email' => $branch->email,
        'manager_name' => $branch->manager_name,
        'status' => 'active',
    ]);

    return redirect()->route('all.branches')->with('success', 'Branch and warehouse created successfully for new user!');
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

        // Handle assigning multiple users to branch with roles (for admins)
        if ($user->role === 'admin' && $request->has('assigned_user_ids')) {
            try {
                $assignedUserIds = $request->assigned_user_ids ?? [];
                $assignedUserRoles = $request->assigned_user_roles ?? [];
                
                // Remove branch owner from assigned users list (they're already the owner)
                $filteredUserIds = [];
                $filteredRoles = [];
                foreach ($assignedUserIds as $index => $uid) {
                    if ($uid != $branch->user_id) {
                        $filteredUserIds[] = $uid;
                        $filteredRoles[] = $assignedUserRoles[$index] ?? 'staff';
                    }
                }
                
                // Prepare sync data with roles
                $syncData = [];
                foreach ($filteredUserIds as $index => $userId) {
                    $syncData[$userId] = ['role' => $filteredRoles[$index] ?? 'staff'];
                }
                
                // Sync assigned users with roles
                $branch->users()->sync($syncData);
            } catch (\Exception $e) {
                Log::error('Error syncing branch users: ' . $e->getMessage());
                // Continue without failing the update
            }
        }

        // ✅ Redirect with success
        return redirect()->route('all.branches')->with('success', 'Branch updated successfully!');
    }

    /**
     * Assign users to a branch with roles
     */
    public function assignUsers(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            // Only admins can assign users
            if ($user->role !== 'admin') {
                return redirect()->back()->with('error', 'Unauthorized access.');
            }

            $branch = Branch::findOrFail($id);
            
            $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
                'user_roles' => 'nullable|array',
                'user_roles.*' => 'in:manager,staff,worker,other'
            ]);

            $userIds = $request->user_ids;
            $userRoles = $request->user_roles ?? [];
            
            // Remove branch owner from list (they're already the owner)
            $filteredUserIds = [];
            $filteredRoles = [];
            
            foreach ($userIds as $index => $uid) {
                if ($uid != $branch->user_id) {
                    $filteredUserIds[] = $uid;
                    $filteredRoles[] = $userRoles[$index] ?? 'staff';
                }
            }

            // Prepare sync data with roles
            $syncData = [];
            foreach ($filteredUserIds as $index => $userId) {
                $syncData[$userId] = ['role' => $filteredRoles[$index] ?? 'staff'];
            }

            // Sync assigned users with roles
            $branch->users()->sync($syncData);

            return redirect()->back()->with('success', 'Users assigned to branch successfully!');
        } catch (\Exception $e) {
            \Log::error('BranchController assignUsers error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error assigning users. Please check if branch_user table exists.');
        }
    }

    /**
     * Remove a user from a branch
     */
    public function removeUser($branchId, $userId)
    {
        try {
            $user = Auth::user();
            
            // Only admins can remove users
            if ($user->role !== 'admin') {
                return redirect()->back()->with('error', 'Unauthorized access.');
            }

            $branch = Branch::findOrFail($branchId);
            $branch->users()->detach($userId);

            return redirect()->back()->with('success', 'User removed from branch successfully!');
        } catch (\Exception $e) {
            Log::error('BranchController removeUser error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error removing user. Please check if branch_user table exists.');
        }
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