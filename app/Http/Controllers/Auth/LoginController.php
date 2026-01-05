<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
    
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function showLoginForm()
    {
        // Get all active branches for the dropdown
        $branches = Branch::where('status', 'active')->orderBy('branch_name', 'asc')->get();
        
        return view('auth.login', compact('branches'));
    }
    
    /**
     * Get user's branch by email (AJAX)
     * Used in login form to auto-select branch
     */
    public function getUserBranchByEmail(Request $request)
    {
        $email = $request->input('email');
        
        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 400);
        }
        
        // Find user by email
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'branch_id' => null,
                'user_role' => null
            ]);
        }
        
        // If user is admin, branch is optional
        if ($user->role === 'admin') {
            $allBranches = Branch::where('status', 'active')
                ->orderBy('branch_name', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'is_admin' => true,
                'user_role' => 'admin',
                'branch_required' => false,
                'branches' => $allBranches->map(function($b) {
                    return [
                        'id' => $b->id,
                        'name' => $b->branch_name,
                        'code' => $b->branch_code
                    ];
                }),
                'message' => 'Admin user - Branch is optional'
            ]);
        }
        
        // For normal users, branch is REQUIRED
        if ($user->role === 'user') {
            // Find user's branch
            $branch = Branch::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
            
            if ($branch) {
                return response()->json([
                    'success' => true,
                    'user_role' => 'user',
                    'branch_required' => true,
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->branch_name,
                    'branch_code' => $branch->branch_code,
                    'message' => 'Branch found - Selection required'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'user_role' => 'user',
                    'branch_required' => true,
                    'branch_id' => null,
                    'message' => 'No active branch found. Please contact administrator.'
                ]);
            }
        }
        
        return response()->json([
            'success' => false,
            'branch_id' => null,
            'message' => 'Unknown user role'
        ]);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // For normal users, branch is REQUIRED
        if ($user->role === 'user') {
            // Check if branch was selected
            if (!$request->has('branch_id') || !$request->branch_id) {
                Auth::logout();
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['branch_id' => 'Branch selection is required for user login.']);
            }
            
            // Verify branch belongs to this user
            $branch = Branch::find($request->branch_id);
            
            if (!$branch || $branch->user_id != $user->id || $branch->status !== 'active') {
                Auth::logout();
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['branch_id' => 'Invalid branch selected. Please select your branch.']);
            }
            
            // Store selected branch in session
            session([
                'selected_branch_id' => $branch->id,
                'selected_branch_name' => $branch->branch_name,
                'selected_branch_code' => $branch->branch_code
            ]);
            
            return redirect()->intended($this->redirectPath());
        }
        
        // For admin users, branch is OPTIONAL
        if ($user->role === 'admin') {
            // If branch selected from login form
            if ($request->has('branch_id') && $request->branch_id) {
                $branch = Branch::find($request->branch_id);
                
                if ($branch && $branch->status === 'active') {
                    // Admin can use any branch
                    session([
                        'selected_branch_id' => $branch->id,
                        'selected_branch_name' => $branch->branch_name,
                        'selected_branch_code' => $branch->branch_code
                    ]);
                }
            } else {
                // Admin can login without branch - clear session
                session()->forget(['selected_branch_id', 'selected_branch_name', 'selected_branch_code']);
            }
            
            return redirect()->intended($this->redirectPath());
        }
        
        // Default: redirect to home
        return redirect()->intended($this->redirectPath());
    }
    
    /**
     * Handle branch selection after login
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeBranchSelection(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }
        
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);
        
        // Get selected branch
        $branch = Branch::findOrFail($request->branch_id);
        
        // Verify branch is in pending branches (security check)
        $pendingBranches = collect(session('pending_branches', []));
        $branchIds = $pendingBranches->pluck('id')->toArray();
        
        if (!in_array($branch->id, $branchIds)) {
            return redirect()->route('branch.select')->with('error', 'Invalid branch selected.');
        }
        
        // Store selected branch in session
        session([
            'selected_branch_id' => $branch->id,
            'selected_branch_name' => $branch->branch_name
        ]);
        
        // Clear pending branches from session
        session()->forget('pending_branches');
        
        return redirect()->intended($this->redirectPath());
    }
}
