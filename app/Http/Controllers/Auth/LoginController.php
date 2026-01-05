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
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Priority 1: If branch selected from login form
        if ($request->has('branch_id') && $request->branch_id) {
            $branch = Branch::find($request->branch_id);
            
            // Verify branch belongs to this user or user is admin
            if ($branch && $branch->status === 'active') {
                $canUseBranch = false;
                
                if ($user->role === 'admin') {
                    // Admin can use any branch
                    $canUseBranch = true;
                } elseif ($branch->user_id == $user->id) {
                    // Branch belongs to this user
                    $canUseBranch = true;
                }
                
                if ($canUseBranch) {
                    // Store selected branch in session
                    session([
                        'selected_branch_id' => $branch->id,
                        'selected_branch_name' => $branch->branch_name,
                        'selected_branch_code' => $branch->branch_code
                    ]);
                    return redirect()->intended($this->redirectPath());
                }
            }
        }
        
        // Priority 2: Auto-connect user's branch (if exists and active)
        $userBranch = Branch::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
        
        if ($userBranch) {
            // Automatically connect user's branch
            session([
                'selected_branch_id' => $userBranch->id,
                'selected_branch_name' => $userBranch->branch_name,
                'selected_branch_code' => $userBranch->branch_code
            ]);
        } else {
            // Priority 3: If user has branch_id in user table (legacy support)
            if (isset($user->branch_id) && $user->branch_id) {
                $defaultBranch = Branch::find($user->branch_id);
                if ($defaultBranch && $defaultBranch->status === 'active') {
                    session([
                        'selected_branch_id' => $defaultBranch->id,
                        'selected_branch_name' => $defaultBranch->branch_name,
                        'selected_branch_code' => $defaultBranch->branch_code
                    ]);
                } else {
                    // No valid branch found, clear session
                    session()->forget(['selected_branch_id', 'selected_branch_name', 'selected_branch_code']);
                }
            } else {
                // No branch found for user, clear session
                session()->forget(['selected_branch_id', 'selected_branch_name', 'selected_branch_code']);
            }
        }
        
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
