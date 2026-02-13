<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function all_users(){
      $users = User::whereIn('role', ['user', 'manager', 'salesman', 'purchaser', 'employee'])
                     ->with(['assignedBranches', 'roles'])
                     ->paginate(10);
      $branches = Branch::all();
      $spatieRoles = Role::where('name', '!=', 'Super Admin')->orderBy('name')->get();
        return view('admin.users.index', compact('users', 'branches', 'spatieRoles'));
    }


    public function deleteuser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // ✅ Delete profile image if exists
        if (!empty($user->profile_img) && file_exists(public_path($user->profile_img))) {
            unlink(public_path($user->profile_img));
        }

        // ✅ Delete user record
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function updateuser(Request $request, $id)
    {
        if (!function_exists('saveSingleFile')) {
            require base_path('app/Helper/helper.php');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|array',
            'phone.*' => 'nullable|string|max:50',
            'phone_name' => 'nullable|array',
            'phone_name.*' => 'nullable|string|max:100',
            'role' => 'required|string',
            'spatie_role' => 'nullable|string|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'user_id_card_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'user_id_card_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'father_id_card_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'father_id_card_back' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_location' => 'nullable|string',
            'house_photo_front' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'credit_limit' => 'nullable|numeric|min:0',
            'salary_per_day' => 'nullable|numeric|min:0',
            'salary_per_month' => 'nullable|numeric|min:0',
            'salary_percentage' => 'nullable|numeric|min:0|max:100',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx|max:5120',
        ]);

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
        $user->branch_id = $request->branch_id ?: null;
        $user->current_location = $request->filled('current_location') ? trim((string) $request->current_location) : null;
        $user->credit_limit = $request->filled('credit_limit') ? (float) $request->credit_limit : null;
        $user->salary_per_day = $request->filled('salary_per_day') ? (float) $request->salary_per_day : null;
        $user->salary_per_month = $request->filled('salary_per_month') ? (float) $request->salary_per_month : null;
        $user->salary_percentage = $request->filled('salary_percentage') ? (float) $request->salary_percentage : null;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->hasFile('profile_img')) {
            $oldPath = $user->profile_img ? public_path($user->profile_img) : null;
            if ($oldPath && is_string($user->profile_img) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->profile_img = saveSingleFile($request->file('profile_img'), 'profile');
            } catch (\Throwable $e) {
                \Log::warning('UserController: profile_img save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        if ($request->hasFile('user_id_card_front')) {
            $oldPath = $user->user_id_card_front ? public_path($user->user_id_card_front) : null;
            if ($oldPath && is_string($user->user_id_card_front) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->user_id_card_front = saveSingleFile($request->file('user_id_card_front'), 'user_id_cards');
            } catch (\Throwable $e) {
                \Log::warning('UserController: user_id_card_front save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }
        if ($request->hasFile('user_id_card_back')) {
            $oldPath = $user->user_id_card_back ? public_path($user->user_id_card_back) : null;
            if ($oldPath && is_string($user->user_id_card_back) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->user_id_card_back = saveSingleFile($request->file('user_id_card_back'), 'user_id_cards');
            } catch (\Throwable $e) {
                \Log::warning('UserController: user_id_card_back save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }
        if ($request->hasFile('father_id_card_front')) {
            $oldPath = $user->father_id_card_front ? public_path($user->father_id_card_front) : null;
            if ($oldPath && is_string($user->father_id_card_front) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->father_id_card_front = saveSingleFile($request->file('father_id_card_front'), 'father_id_cards');
            } catch (\Throwable $e) {
                \Log::warning('UserController: father_id_card_front save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }
        if ($request->hasFile('father_id_card_back')) {
            $oldPath = $user->father_id_card_back ? public_path($user->father_id_card_back) : null;
            if ($oldPath && is_string($user->father_id_card_back) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->father_id_card_back = saveSingleFile($request->file('father_id_card_back'), 'father_id_cards');
            } catch (\Throwable $e) {
                \Log::warning('UserController: father_id_card_back save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }
        if ($request->hasFile('house_photo_front')) {
            $oldPath = $user->house_photo_front ? public_path($user->house_photo_front) : null;
            if ($oldPath && is_string($user->house_photo_front) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                $user->house_photo_front = saveSingleFile($request->file('house_photo_front'), 'house_photos');
            } catch (\Throwable $e) {
                \Log::warning('UserController: house_photo_front save failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            }
        }

        if ($request->hasFile('attachments')) {
            $existing = is_array($user->attachments) ? $user->attachments : [];
            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    try {
                        $existing[] = saveSingleFile($file, 'user_attachments');
                    } catch (\Throwable $e) {
                        \Log::warning('UserController: attachment save failed', ['user_id' => $id, 'file' => $file->getClientOriginalName(), 'error' => $e->getMessage()]);
                    }
                }
            }
            $user->attachments = $existing;
        }

        $user->save();

        // Assign Spatie role (for permissions) - if spatie_role provided
        if ($request->filled('spatie_role')) {
            $user->syncRoles([$request->spatie_role]);
        } else {
            $user->syncRoles([]); // Remove all Spatie roles if none selected
        }

        // Keep branch_user in sync: if branch selected, ensure user is attached to that branch for login
        if ($user->branch_id) {
            $user->assignedBranches()->syncWithoutDetaching([
                $user->branch_id => ['role' => $user->role ?? 'staff']
            ]);
        }

        return redirect()->back()->with('success', 'User updated successfully!');
    }

    /**
     * Show all users for a specific branch
     */
    public function branchUsers($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        
        // Get all users for this branch (where branch_id matches)
        $users = User::whereIn('role', ['user', 'manager', 'salesman', 'purchaser'])
            ->where('branch_id', $branchId)
            ->with(['assignedBranches'])
            ->paginate(10);
        
        $branches = Branch::all();
        
        return view('admin.users.branch-users', compact('users', 'branches', 'branch'));
    }

}
