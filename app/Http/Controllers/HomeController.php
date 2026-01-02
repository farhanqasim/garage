<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function userprofile($id){
      $user = User::find($id);
      return view('admin.pages.profile',compact('user'));
     }



public function userprofileupdate(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);
    $user = User::findOrFail($id);
    // Profile Image Handling
    if ($request->hasFile('profile_img')) {
        $user->profile_img = saveSingleFile($request->file('profile_img'), 'profile');
    }
    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->phone = $request->input('phone');
    // Password Update Logic
    if ($request->filled('new_password')) {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        $user->password = Hash::make($request->new_password);
    }
    // return $user;
    $user->save();
    return redirect()->back()->with('success', 'Profile updated successfully!');
}


// Route for verifying old password
public function verifyOldPassword(Request $request)
{
    $request->validate([
        'old_password' => 'required|string',
    ]);

    if (Hash::check($request->old_password, auth()->user()->password)) {
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false, 'message' => 'Old password is incorrect.']);
}
}
