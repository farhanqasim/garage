<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
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
                $branches = Branch::with(['users'])->orderBy('branch_name', 'asc')->paginate(10);

                return view('admin.branches.index', compact('branches', 'users'));
            }

            // ✅ If normal user - check assigned branches
            if ($user->role === 'user') {
                $branch = null;

                // Check assigned branches
                try {
                    $assignedBranch = $user->assignedBranches()->first();
                    if ($assignedBranch) {
                        $branch = Branch::with(['users'])->find($assignedBranch->id);
                    }
                } catch (\Exception $e) {
                    // If branch_user table doesn't exist, continue without assigned branches
                    Log::warning('Branch user relationship error: '.$e->getMessage());
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
            Log::error('BranchController all_branches error: '.$e->getMessage());

            return redirect()->route('home')->with('error', 'An error occurred. Please check if migrations are run.');
        }
    }

    /**
     * Get branch details as JSON (for AJAX requests, e.g. helpline phone)
     */
    public function show($id)
    {
        $branch = Branch::find($id);
        if (! $branch) {
            return response()->json(['error' => 'Branch not found'], 404);
        }
        $user = Auth::user();
        if ($user->role !== 'admin') {
            $isAssigned = $user->assignedBranches()->where('branch_id', $branch->id)->exists();
            if (! $isAssigned) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        return response()->json([
            'id' => $branch->id,
            'branch_name' => $branch->branch_name,
            'branch_code' => $branch->branch_code,
            'phone' => $branch->phone,
            'email' => $branch->email,
            'address' => $branch->address,
        ]);
    }

    public function store_branches(Request $request)
    {
        try {
            $user = Auth::user();

            // ✅ Validate input
            $request->validate([
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
            $branch = new Branch;
            $branch->branch_name = $request->branch_name;
            $branch->branch_code = $request->branch_code;
            $branch->manager_name = $request->manager_name ?? null;
            $branch->email = $request->email ?? null;
            $branch->phone = $request->phone ?? null;
            $branch->address = $request->address ?? null;
            $branch->city = $request->city ?? null;
            $branch->state = $request->state ?? null;
            $branch->country = $request->country ?? 'Pakistan';
            $branch->location = $request->location ?? null;
            $branch->status = 'inactive';

            if (! $branch->save()) {
                Log::error('Failed to save branch', ['request' => $request->all()]);

                return redirect()->route('all.branches')->with('error', 'Failed to save branch. Please try again.');
            }

            Log::info('Branch created successfully', ['branch_id' => $branch->id, 'branch_name' => $branch->branch_name]);

            // Auto-create warehouse for this branch
            try {
                $warehouseCode = 'WH-'.strtoupper(Str::random(6));
                Warehouse::create([
                    'branch_id' => $branch->id,
                    'warehouse_name' => $branch->branch_name.' Warehouse',
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
                Log::info('Warehouse created for branch', ['branch_id' => $branch->id]);
            } catch (\Exception $e) {
                Log::error('Failed to create warehouse for branch: '.$e->getMessage(), [
                    'branch_id' => $branch->id,
                    'error' => $e->getTraceAsString(),
                ]);
                // Continue even if warehouse creation fails
            }

            // Connect all admin users to this new branch (admin role gets access to already saved branches)
            try {
                $adminIds = User::where('role', 'admin')->pluck('id');
                foreach ($adminIds as $aid) {
                    $branch->users()->syncWithoutDetaching([$aid => ['role' => 'admin']]);
                }
                Log::info('Admins attached to branch', ['branch_id' => $branch->id, 'admin_count' => $adminIds->count()]);
            } catch (\Exception $e) {
                Log::warning('Could not attach admins to new branch: '.$e->getMessage(), [
                    'branch_id' => $branch->id,
                    'error' => $e->getTraceAsString(),
                ]);
                // Continue even if admin attachment fails
            }

            return redirect()->route('all.branches')->with('success', 'Branch and warehouse created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Branch validation failed', ['errors' => $e->errors(), 'request' => $request->all()]);

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput($request->all());
        } catch (\Exception $e) {
            Log::error('Branch creation error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return redirect()->route('all.branches')->with('error', 'Error creating branch: '.$e->getMessage());
        }
    }

    public function updatebranchStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        $model = Branch::findOrFail($id);

        // ✅ Optional: Authorization check
        $user = Auth::user();
        if ($user->role !== 'admin') {
            // Check if user is assigned to this branch
            $isAssigned = $user->assignedBranches()->where('branch_id', $model->id)->exists();
            if (! $isAssigned) {
                return redirect()->back()->with('error', 'Unauthorized access.');
            }
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

        // ✅ Validate input
        $request->validate([
            'branch_name' => 'required|string|max:255',
            'branch_code' => 'required|string|max:255|unique:branches,branch_code,'.$branch->id,
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
                'user_roles.*' => 'in:admin,manager,staff,worker,other',
            ]);

            $userIds = $request->user_ids;
            $userRoles = $request->user_roles ?? [];

            // Prepare user IDs and roles
            $filteredUserIds = [];
            $filteredRoles = [];

            foreach ($userIds as $index => $uid) {
                $filteredUserIds[] = $uid;
                $filteredRoles[] = $userRoles[$index] ?? 'staff';
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
            \Log::error('BranchController assignUsers error: '.$e->getMessage());

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
            Log::error('BranchController removeUser error: '.$e->getMessage());

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
