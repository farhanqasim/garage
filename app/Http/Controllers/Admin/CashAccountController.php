<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\HasBranchAccess;

class CashAccountController extends Controller
{
    use HasBranchAccess;

    /**
     * Display a listing of all cash accounts
     */
    public function index()
    {
        $user = Auth::user();
        $query = CashAccount::with('user');

        // If admin, show all cash accounts. If branch user, show only their branch users' accounts
        if ($user->role !== 'admin') {
            $branchId = $this->getUserBranchId($user);
            
            if ($branchId) {
                // Filter cash accounts where user belongs to this branch
                $query->whereHas('user', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereHas('assignedBranches', function($subQ) use ($branchId) {
                          $subQ->where('branch_id', $branchId);
                      });
                });
            } else {
                // If no branch, show empty result
                $query->whereRaw('1 = 0');
            }
        }

        $cashAccounts = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.cash-accounts.index', compact('cashAccounts'));
    }

    /**
     * Show cash account details with transaction history
     */
    public function show($id)
    {
        $cashAccount = CashAccount::with(['user', 'user.cashTransactions' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(50);
        }])->findOrFail($id);

        return view('admin.cash-accounts.show', compact('cashAccount'));
    }

    /**
     * Show the form for creating a new cash account
     */
    public function create()
    {
        $user = Auth::user();
        
        // If admin, show all users. If branch owner, show only their branch users
        if ($user->role === 'admin') {
            $users = User::orderBy('name', 'asc')->get();
        } else {
            // Branch owner - get their branch users
            $branchId = $user->branch_id;
            if ($branchId) {
                $users = User::where(function($query) use ($branchId) {
                    $query->whereHas('assignedBranches', function($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    })
                    ->orWhere('branch_id', $branchId);
                })
                ->orderBy('name', 'asc')
                ->get();
            } else {
                // Check assigned branches
                $assignedBranch = $user->assignedBranches()->first();
                if ($assignedBranch) {
                    $users = User::where(function($query) use ($assignedBranch) {
                        $query->whereHas('assignedBranches', function($q) use ($assignedBranch) {
                            $q->where('branch_id', $assignedBranch->id);
                        })
                        ->orWhere('branch_id', $assignedBranch->id);
                    })
                    ->orderBy('name', 'asc')
                    ->get();
                } else {
                    $users = collect();
                }
            }
        }
        
        return view('admin.cash-accounts.create', compact('users'));
    }

    /**
     * Store a newly created cash account
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'opening_balance' => 'nullable|numeric|min:0',
        ]);
        
        // Check if user already has a cash account
        $existingAccount = CashAccount::where('user_id', $validated['user_id'])->first();
        if ($existingAccount) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This user already has a cash account.');
        }
        
        // If branch owner, ensure they can only create accounts for their branch users
        if ($user->role !== 'admin') {
            $userBranchId = $user->branch_id;
            if ($userBranchId) {
                $targetUser = User::findOrFail($validated['user_id']);
                $isBranchUser = $targetUser->branch_id == $userBranchId;
                $isAssignedUser = $targetUser->assignedBranches()->where('branch_id', $userBranchId)->exists();
                
                if (!$isBranchUser && !$isAssignedUser) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'You can only create cash accounts for users in your branch.');
                }
            } else {
                // Check assigned branches
                $assignedBranch = $user->assignedBranches()->first();
                if ($assignedBranch) {
                    $targetUser = User::findOrFail($validated['user_id']);
                    $isBranchUser = $targetUser->branch_id == $assignedBranch->id;
                    $isAssignedUser = $targetUser->assignedBranches()->where('branch_id', $assignedBranch->id)->exists();
                    
                    if (!$isBranchUser && !$isAssignedUser) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'You can only create cash accounts for users in your branch.');
                    }
                }
            }
        }
        
        try {
            $openingBalance = (float) ($validated['opening_balance'] ?? 0);
            
            CashAccount::create([
                'user_id' => $validated['user_id'],
                'balance' => $openingBalance,
            ]);
            
            return redirect()->route('admin.cash-accounts.index')
                ->with('success', 'Cash account created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating cash account: ' . $e->getMessage());
        }
    }

    /**
     * Display all cash transactions (complete history)
     */
    public function transactions(Request $request)
    {
        $query = \App\Models\CashTransaction::with(['user', 'relatedUser', 'branch']);

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by direction
        if ($request->has('direction') && $request->direction) {
            $query->where('direction', $request->direction);
        }

        // Filter by branch
        if ($request->has('branch_id') && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get users and branches for filters
        $users = \App\Models\User::orderBy('name')->get();
        $branches = \App\Models\Branch::orderBy('branch_name')->get();

        return view('admin.cash-transactions.index', compact('transactions', 'users', 'branches'));
    }
}
