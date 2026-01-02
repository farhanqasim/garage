<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function all_users(){
      $users = User::where('role', 'user')
                     ->with('branches')
                     ->paginate(10);
                    //  return $users;
        return view('admin.users.index', compact('users'));
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
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->branch_id = $request->branch_id;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->hasFile('profile_img')) {
            // Delete old image if exists
            if ($user->profile_img && file_exists(public_path($user->profile_img))) {
                unlink(public_path($user->profile_img));
            }

            $user->profile_img = saveSingleFile($request->file('profile_img'), 'profile');

        }

        $user->save();

        return redirect()->back()->with('success', 'User updated successfully!');
    }


}
