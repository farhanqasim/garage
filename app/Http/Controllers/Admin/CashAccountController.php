<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\User;
use Illuminate\Http\Request;

class CashAccountController extends Controller
{
    /**
     * Display a listing of all cash accounts
     */
    public function index()
    {
        $cashAccounts = CashAccount::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

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
