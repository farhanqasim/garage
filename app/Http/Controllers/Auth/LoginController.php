<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
     * Attempt to log the user in. Always use "remember" so login keeps for 1 day, 1 month, or until user logs out.
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            true // always remember — user jab tak khud logout na kare tab tak login rahega
        );
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
        // Get users with email for username dropdown (login)
        $users = User::whereNotNull('email')->where('email', '!=', '')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        
        return view('auth.login', compact('branches', 'users'));
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
            // Find user's branches (from branch_id or assigned branches)
            $ownerBranches = collect();
            if ($user->branch_id) {
                $branch = Branch::where('id', $user->branch_id)
                    ->where('status', 'active')
                    ->first();
                if ($branch) {
                    $ownerBranches = collect([$branch]);
                }
            }
            
            $assignedBranches = $user->assignedBranches()
                ->where('status', 'active')
                ->get();
            
            // Merge and get unique branches
            $allBranches = $ownerBranches->merge($assignedBranches)->unique('id');
            
            if ($allBranches->count() > 0) {
                // If only one branch, auto-select it
                if ($allBranches->count() === 1) {
                    $branch = $allBranches->first();
                    return response()->json([
                        'success' => true,
                        'user_role' => 'user',
                        'branch_required' => true,
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->branch_name,
                        'branch_code' => $branch->branch_code,
                        'message' => 'Branch found - Selection required'
                    ]);
                }
                
                // Multiple branches - return list
                return response()->json([
                    'success' => true,
                    'user_role' => 'user',
                    'branch_required' => true,
                    'multiple_branches' => true,
                    'branches' => $allBranches->map(function($b) {
                        return [
                            'id' => $b->id,
                            'name' => $b->branch_name,
                            'code' => $b->branch_code
                        ];
                    }),
                    'message' => 'Multiple branches found - Selection required'
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
        // For normal users (role = 'user'), branch is REQUIRED and redirect to employee home
        if ($user->role === 'user') {
            $branch = null;
            
            // If branch_id is provided in request, use it
            if ($request->has('branch_id') && $request->branch_id) {
                $branch = Branch::find($request->branch_id);
            }
            
            // If no branch selected but user has branch_id, auto-select that branch
            if (!$branch && $user->branch_id) {
                $branch = Branch::find($user->branch_id);
            }
            
            // If still no branch, check assigned branches
            if (!$branch) {
                $assignedBranch = $user->assignedBranches()->where('status', 'active')->first();
                if ($assignedBranch) {
                    $branch = $assignedBranch;
                }
            }
            
            // If branch found, verify access
            if ($branch) {
                if ($branch->status !== 'active') {
                    Auth::logout();
                    return redirect()->back()
                        ->withInput($request->only('email'))
                        ->withErrors(['branch_id' => 'Your assigned branch is inactive. Please contact administrator.']);
                }
                
                // Check if user is branch owner OR assigned to this branch
                $isOwner = $branch->user_id == $user->id;
                $isAssigned = $user->assignedBranches()->where('branch_id', $branch->id)->exists();
                
                if ($isOwner || $isAssigned || $user->branch_id == $branch->id) {
                    // Store selected branch in session
                    session([
                        'selected_branch_id' => $branch->id,
                        'selected_branch_name' => $branch->branch_name,
                        'selected_branch_code' => $branch->branch_code
                    ]);
                    
                    // User (employee) redirects to employee home
                    return redirect()->route('employee.home');
                }
            }
            
            // If no branch found or no access, require branch selection
            Auth::logout();
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['branch_id' => 'Branch selection is required. Please select your branch.']);
        }
        
        // For admin users, branch is OPTIONAL and redirect to admin dashboard
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
                    $user->update(['last_selected_branch_id' => $branch->id]);
                }
            } else {
                // Admin can login without branch - clear session
                session()->forget(['selected_branch_id', 'selected_branch_name', 'selected_branch_code']);
            }
            
            // Admin redirects to main dashboard (home)
            return redirect()->route('home');
        }
        
        // Any other role (e.g. employee) redirects to employee home
        return redirect()->route('employee.home');
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

    /**
     * Switch branch for admin users (can switch to any branch)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function switchBranch(Request $request)
    {
        // Check if this is an AJAX/JSON request - use multiple methods for reliability
        $acceptHeader = $request->header('Accept', '');
        $isAjax = $request->ajax() || 
                  $request->wantsJson() || 
                  $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                  (strpos($acceptHeader, 'application/json') !== false);
        
        // Force JSON response for AJAX requests
        if ($isAjax) {
            $request->headers->set('Accept', 'application/json');
        }
        
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not authenticated',
                    ], 401)->header('Content-Type', 'application/json');
                }
                return redirect()->route('login')->with('error', 'Please login first.');
            }

            $user = Auth::user();

            // STRICT CHECK: Only admin can switch branches - no exceptions
            if (!$user || $user->role !== 'admin') {
                Log::warning('Unauthorized branch switch attempt', [
                    'user_id' => $user ? $user->id : 'null',
                    'user_role' => $user ? $user->role : 'null',
                    'ip' => $request->ip(),
                ]);
                
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Only admin users can switch branches.',
                    ], 403)->header('Content-Type', 'application/json');
                }
                return redirect()->back()->with('error', 'Access denied. Only admin users can switch branches.');
            }

            // Get branch_id from request (can be null for "All Branches")
            $branchId = $request->input('branch_id');
            
            // If branch_id is null or empty, clear branch selection
            if (empty($branchId) || $branchId === 'null' || $branchId === null) {
                session()->forget(['selected_branch_id', 'selected_branch_name', 'selected_branch_code']);
                $user->update(['last_selected_branch_id' => null]);

                if ($isAjax) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Branch selection cleared (viewing all branches)',
                    ])->header('Content-Type', 'application/json');
                }

                return redirect()->back()->with('success', 'Branch selection cleared');
            }

            // Validate branch_id exists - use manual validation to avoid exception bubbling
            $validator = \Validator::make($request->all(), [
                'branch_id' => 'required|exists:branches,id',
            ]);
            
            if ($validator->fails()) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ], 422)->header('Content-Type', 'application/json');
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Find and switch to branch
            $branch = Branch::find($branchId);
            
            if (!$branch) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Branch not found',
                    ], 404)->header('Content-Type', 'application/json');
                }
                return redirect()->back()->with('error', 'Branch not found');
            }

            if ($branch->status !== 'active') {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Branch is not active',
                    ], 400)->header('Content-Type', 'application/json');
                }
                return redirect()->back()->with('error', 'Branch is not active');
            }

            // Store selected branch in session
            session([
                'selected_branch_id' => $branch->id,
                'selected_branch_name' => $branch->branch_name,
                'selected_branch_code' => $branch->branch_code,
            ]);
            $user->update(['last_selected_branch_id' => $branch->id]);

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => "Switched to {$branch->branch_name}",
                    'branch' => [
                        'id' => $branch->id,
                        'name' => $branch->branch_name,
                        'code' => $branch->branch_code,
                    ],
                ])->header('Content-Type', 'application/json');
            }

            return redirect()->back()->with('success', "Switched to {$branch->branch_name}");

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Branch switch validation error', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);
            
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422)->header('Content-Type', 'application/json');
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Branch switch error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'branch_id' => $request->input('branch_id'),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all(),
                'headers' => $request->headers->all(),
            ]);

            // Always return JSON for AJAX requests, even on exceptions
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage(),
                    'error_details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : null,
                ], 500)->header('Content-Type', 'application/json');
            }
            return redirect()->back()->with('error', 'An error occurred while switching branch');
        }
    }
    
    /**
     * Log the user out of the application.
     * Redirect to main login (which is now employee login).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Perform logout
        $this->guard()->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Redirect to main login (which is now employee login)
        return redirect()->route('login');
    }

    /**
     * Check if user has pattern lock set
     */
    public function getUserPatternStatus(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'has_pattern' => false,
                'pattern' => null,
                'message' => 'User not found'
            ]);
        }

        return response()->json([
            'has_pattern' => !empty($user->pattern_lock),
            'message' => $user->pattern_lock ? 'Pattern is set' : 'Pattern not set'
        ]);
    }

    /**
     * Check if user has fingerprint set
     */
    public function getUserFingerprintStatus(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'has_fingerprint' => false,
                'message' => 'User not found'
            ]);
        }

        return response()->json([
            'has_fingerprint' => !empty($user->fingerprint_data),
            'message' => $user->fingerprint_data ? 'Fingerprint is set' : 'Fingerprint not set'
        ]);
    }

    /**
     * Save pattern lock for authenticated user
     */
    public function savePattern(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        try {
            // Manual validation to avoid HTML error pages
            $pattern = $request->input('pattern');
            
            // Validate pattern format manually
            if ($pattern !== null && $pattern !== '') {
                // Check if pattern matches format (comma-separated numbers 0-8)
                if (!preg_match('/^[0-8](,[0-8])*$/', $pattern)) {
                    return $isAjax
                        ? response()->json(['success' => false, 'message' => 'Invalid pattern format.'])->header('Content-Type', 'application/json')
                        : redirect()->back()->withErrors(['pattern' => 'Invalid pattern format.']);
                }
                
                // Validate pattern has at least 3 dots
                $patternArray = explode(',', $pattern);
                if (count($patternArray) < 3) {
                    return $isAjax
                        ? response()->json(['success' => false, 'message' => 'Pattern must have at least 3 dots'])->header('Content-Type', 'application/json')
                        : redirect()->back()->withErrors(['pattern' => 'Pattern must have at least 3 dots']);
                }
                
                // Validate each dot is between 0-8
                foreach ($patternArray as $dot) {
                    $dotNum = intval(trim($dot));
                    if ($dotNum < 0 || $dotNum > 8) {
                        return $isAjax
                            ? response()->json(['success' => false, 'message' => 'Invalid pattern.'])->header('Content-Type', 'application/json')
                            : redirect()->back()->withErrors(['pattern' => 'Invalid pattern.']);
                    }
                }
            }

            $user = Auth::user();
            if (!$user) {
                return $isAjax
                    ? response()->json(['success' => false, 'message' => 'User not authenticated'], 401)->header('Content-Type', 'application/json')
                    : redirect()->route('login')->withErrors(['pattern' => 'Please login again.']);
            }
            
            // If pattern is empty string, clear it
            if (empty($pattern)) {
                $user->pattern_lock = null;
                $message = 'Pattern cleared successfully';
            } else {
                try {
                    $user->pattern_lock = encrypt($pattern);
                } catch (\Exception $e) {
                    Log::warning('Pattern encrypt failed, storing plain: ' . $e->getMessage());
                    $user->pattern_lock = $pattern; // Fallback if APP_KEY missing
                }
                $message = 'Pattern saved successfully! You can now use it to login.';
            }

            $user->save();

            return $isAjax
                ? response()->json(['success' => true, 'message' => $message])->header('Content-Type', 'application/json')
                : redirect()->back()->with('success', $message);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $isAjax
                ? response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422)->header('Content-Type', 'application/json')
                : redirect()->back()->withErrors($e->errors());
            
        } catch (\Exception $e) {
            Log::error('Pattern save error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $errMsg = 'An error occurred while saving pattern.';
            return $isAjax
                ? response()->json(['success' => false, 'message' => $errMsg], 500)->header('Content-Type', 'application/json')
                : redirect()->back()->withErrors(['pattern' => $errMsg]);
        }
    }

    /**
     * Save fingerprint data for authenticated user
     */
    public function saveFingerprint(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        try {
            $request->validate([
                'fingerprint_data' => 'nullable|string'
            ]);

            $user = Auth::user();
            if (!$user) {
                return $isAjax
                    ? response()->json(['success' => false, 'message' => 'User not authenticated'], 401)->header('Content-Type', 'application/json')
                    : redirect()->route('login')->withErrors(['fingerprint' => 'Please login again.']);
            }

            $fingerprintData = $request->input('fingerprint_data');

            // If fingerprint_data is empty string, clear it
            if (empty($fingerprintData)) {
                $user->fingerprint_data = null;
                $message = 'Fingerprint cleared successfully';
            } else {
                // Encrypt fingerprint data before storing
                $user->fingerprint_data = encrypt($fingerprintData);
                $message = 'Fingerprint saved successfully! You can now use it to login.';
            }

            $user->save();

            return $isAjax
                ? response()->json(['success' => true, 'message' => $message])->header('Content-Type', 'application/json')
                : redirect()->back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $isAjax
                ? response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422)->header('Content-Type', 'application/json')
                : redirect()->back()->withErrors($e->errors());

        } catch (\Exception $e) {
            Log::error('Fingerprint save error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $errMsg = 'An error occurred while saving fingerprint.';
            return $isAjax
                ? response()->json(['success' => false, 'message' => $errMsg], 500)->header('Content-Type', 'application/json')
                : redirect()->back()->withErrors(['fingerprint' => $errMsg]);
        }
    }

    /**
     * Verify pattern login
     */
    public function verifyPatternLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'pattern' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        // Decrypt stored pattern and verify
        if (empty($user->pattern_lock)) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['pattern' => 'Pattern not set for this user.']);
        }
        $storedPattern = null;
        try {
            $storedPattern = decrypt($user->pattern_lock);
        } catch (\Exception $e) {
            // Backward compatibility: encrypted value or legacy plain text
            $storedPattern = $user->pattern_lock;
        }
        if ($storedPattern !== $request->pattern) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['pattern' => 'Invalid pattern.']);
        }

        // Login the user
        Auth::login($user, true); // always remember — login keep until user logs out

        // Handle branch selection (same logic as regular login)
        if ($user->role === 'user' && $request->branch_id) {
            $branch = Branch::find($request->branch_id);
            if ($branch && $branch->status === 'active') {
                session([
                    'selected_branch_id' => $branch->id,
                    'selected_branch_name' => $branch->branch_name,
                    'selected_branch_code' => $branch->branch_code
                ]);
            }
        }

        // Redirect based on role: user → employee home, admin → main dashboard
        if ($user->role === 'admin') {
            return redirect()->route('home');
        }
        return redirect()->route('employee.home');
    }

    /**
     * Verify fingerprint login
     */
    public function verifyFingerprintLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'fingerprint_data' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        // Check if fingerprint is set
        if (empty($user->fingerprint_data)) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['fingerprint' => 'Fingerprint not set for this user.']);
        }

        // Decrypt and compare fingerprint data
        try {
            $storedFingerprint = decrypt($user->fingerprint_data);
            if ($storedFingerprint !== $request->fingerprint_data) {
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['fingerprint' => 'Invalid fingerprint.']);
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['fingerprint' => 'Fingerprint verification failed.']);
        }

        // Login the user
        Auth::login($user, true); // always remember — login keep until user logs out

        // Handle branch selection (same logic as regular login)
        if ($user->role === 'user' && $request->branch_id) {
            $branch = Branch::find($request->branch_id);
            if ($branch && $branch->status === 'active') {
                session([
                    'selected_branch_id' => $branch->id,
                    'selected_branch_name' => $branch->branch_name,
                    'selected_branch_code' => $branch->branch_code
                ]);
            }
        }

        // Redirect based on role: user → employee home, admin → main dashboard
        if ($user->role === 'admin') {
            return redirect()->route('home');
        }
        return redirect()->route('employee.home');
    }
}
