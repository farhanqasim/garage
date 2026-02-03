<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarWashShopExpense;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashShopExpenseController extends Controller
{
    use HasBranchAccess;

    /**
     * List shop expenses. Query: date (YYYY-MM-DD) OR from & to (YYYY-MM-DD) for range.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $from = $request->get('from');
        $to = $request->get('to');
        $date = $request->get('date', now()->format('Y-m-d'));

        $query = CarWashShopExpense::with('user')
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($from !== null && $to !== null && $from !== '' && $to !== '') {
            $query->whereBetween('expense_date', [$from, $to]);
        } else {
            $query->where('expense_date', $date);
        }

        // Filter by user_id if provided
        if ($request->has('user_id') && $request->user_id !== '' && $request->user_id !== null) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by category if provided
        if ($request->has('category') && $request->category !== '' && $request->category !== null) {
            $query->where('category', $request->category);
        }

        $this->applyBranchFilter($query, 'branch_id', $user);

        $expenses = $query->get()->map(function ($e) {
            return [
                'id' => $e->id,
                'expense_date' => $e->expense_date->format('Y-m-d'),
                'created_at' => $e->created_at ? $e->created_at->format('Y-m-d H:i') : null,
                'category' => $e->category,
                'amount' => (float) $e->amount,
                'notes' => $e->notes,
                'user_name' => $e->user ? $e->user->name : null,
            ];
        });

        $total = $expenses->sum(fn ($e) => $e['amount']);

        return response()->json([
            'success' => true,
            'expenses' => $expenses,
            'total' => round($total, 2),
        ]);
    }

    /**
     * Show all shop expenses page with filters
     */
    public function showAllExpenses(Request $request)
    {
        $user = Auth::user();
        
        // Get all users for filter (based on user role)
        $users = \App\Models\User::query();
        if ($user->role === 'branch_owner') {
            $users->where('branch_id', $user->branch_id);
        }
        $users = $users->orderBy('name')->get(['id', 'name']);
        
        // Get all categories for filter
        $categories = CarWashShopExpense::distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->sort()
            ->values();
        
        // Get branch name and user name for header
        $branchName = $user->branch ? $user->branch->name : 'All Branches';
        $userName = $user->name;
        
        return view('car-wash-all-shop-expenses', [
            'users' => $users,
            'categories' => $categories,
            'branchName' => $branchName,
            'userName' => $userName,
        ]);
    }

    /**
     * Store a new shop expense
     */
    public function store(Request $request)
    {
        $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|string|max:191',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $branchId = $this->getUserBranchId($user);

        $expense = CarWashShopExpense::create([
            'branch_id' => $branchId,
            'user_id' => $user->id,
            'expense_date' => $request->expense_date,
            'category' => $request->category,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shop expense added.',
            'expense' => [
                'id' => $expense->id,
                'expense_date' => $expense->expense_date->format('Y-m-d'),
                'category' => $expense->category,
                'amount' => (float) $expense->amount,
                'notes' => $expense->notes,
            ],
        ]);
    }
}
