<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CashTransaction;
use App\Models\CashTransfer;
use App\Models\BankTransaction;
use App\Models\BankAccount;
use App\Http\Controllers\Traits\HasBranchAccess;

class CarWashTransactionHistoryController extends Controller
{
    use HasBranchAccess;

    /**
     * Show transaction history page
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get branch users for filter
        $users = \App\Models\User::query();
        if ($user->role === 'branch_owner' || ($user->role !== 'admin' && $user->branch_id)) {
            $users->where('branch_id', $user->branch_id);
        }
        $users = $users->orderBy('name')->get(['id', 'name']);

        // Get branch name and user name for header
        $branchName = $user->branch ? $user->branch->name : 'All Branches';
        $userName = $user->name;

        return view('car-wash-transaction-history', [
            'users' => $users,
            'branchName' => $branchName,
            'userName' => $userName,
        ]);
    }

    /**
     * Get transaction history data (API endpoint)
     */
    public function getTransactions(Request $request)
    {
        $user = Auth::user();

        $from = $request->get('from');
        $to = $request->get('to');
        $userId = $request->get('user_id');
        $transactionType = $request->get('type'); // 'cash_transfer', 'bank_transfer', or 'all'

        $transactions = [];

        // Get Cash Transfers
        if (!$transactionType || $transactionType === 'all' || $transactionType === 'cash_transfer') {
            $cashTransfersQuery = CashTransfer::with(['fromUser', 'toUser'])
                ->orderBy('created_at', 'desc');

            if ($from && $to) {
                $cashTransfersQuery->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            }

            if ($userId) {
                $cashTransfersQuery->where(function($q) use ($userId) {
                    $q->where('from_user_id', $userId)
                      ->orWhere('to_user_id', $userId);
                });
            }

            // Apply branch filter
            if ($user->role === 'branch_owner' || ($user->role !== 'admin' && $user->branch_id)) {
                $cashTransfersQuery->where('branch_id', $user->branch_id);
            }

            $cashTransfers = $cashTransfersQuery->get();

            foreach ($cashTransfers as $transfer) {
                $transactions[] = [
                    'id' => 'cash_' . $transfer->id,
                    'type' => 'cash_transfer',
                    'date' => $transfer->created_at->format('Y-m-d H:i:s'),
                    'from_user' => $transfer->fromUser ? $transfer->fromUser->name : 'Unknown',
                    'to_user' => $transfer->toUser ? $transfer->toUser->name : 'Unknown',
                    'amount' => (float) $transfer->amount,
                    'status' => $transfer->status,
                    'note' => $transfer->note,
                    'direction' => null, // Cash transfers don't have direction
                ];
            }
        }

        // Get Cash Transactions (bank transfers, cash transfers, and shop expenses)
        if (!$transactionType || $transactionType === 'all' || $transactionType === 'bank_transfer' || $transactionType === 'shop_expense') {
            $cashTransactionsQuery = CashTransaction::with(['user', 'relatedUser'])
                ->whereIn('type', ['bank_transfer', 'cash_transfer', 'shop_expense'])
                ->orderBy('created_at', 'desc');

            if ($from && $to) {
                $cashTransactionsQuery->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            }

            if ($userId) {
                $cashTransactionsQuery->where(function($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhere('related_user_id', $userId);
                });
            }

            // Apply branch filter
            if ($user->role === 'branch_owner' || ($user->role !== 'admin' && $user->branch_id)) {
                $cashTransactionsQuery->where('branch_id', $user->branch_id);
            }

            $cashTransactions = $cashTransactionsQuery->get();

            foreach ($cashTransactions as $transaction) {
                if ($transaction->type === 'bank_transfer') {
                    $transactions[] = [
                        'id' => 'bank_tx_' . $transaction->id,
                        'type' => 'bank_transfer',
                        'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                        'from_user' => $transaction->user ? $transaction->user->name : 'Unknown',
                        'to_user' => 'Bank Account',
                        'amount' => (float) $transaction->amount,
                        'direction' => $transaction->direction, // 'credit' or 'debit'
                        'note' => $transaction->note,
                    ];
                } elseif ($transaction->type === 'shop_expense') {
                    // Get shop expense details if available
                    $shopExpense = null;
                    if ($transaction->reference_id && $transaction->reference_table === 'car_wash_shop_expenses') {
                        $shopExpense = \App\Models\CarWashShopExpense::find($transaction->reference_id);
                    }
                    $transactions[] = [
                        'id' => 'shop_exp_' . $transaction->id,
                        'type' => 'shop_expense',
                        'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                        'from_user' => $transaction->user ? $transaction->user->name : 'Unknown',
                        'to_user' => 'Shop Expense',
                        'amount' => (float) $transaction->amount,
                        'direction' => $transaction->direction, // 'debit'
                        'note' => $transaction->note,
                        'category' => $shopExpense ? $shopExpense->category : null,
                        'expense_date' => $shopExpense && $shopExpense->expense_date ? $shopExpense->expense_date->format('Y-m-d') : null,
                    ];
                }
            }
        }

        // Get Bank Transactions (bank to bank transfers)
        if (!$transactionType || $transactionType === 'all' || $transactionType === 'bank_transfer') {
            $bankTransactionsQuery = BankTransaction::with(['bankAccount.user'])
                ->whereIn('type', ['credit', 'debit'])
                ->orderBy('created_at', 'desc');

            if ($from && $to) {
                $bankTransactionsQuery->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            }

            // Get bank accounts for the branch
            $bankAccountsQuery = BankAccount::query();
            if ($user->role === 'branch_owner' || ($user->role !== 'admin' && $user->branch_id)) {
                $bankAccountsQuery->where('branch_id', $user->branch_id);
            }
            if ($userId) {
                $bankAccountsQuery->where('user_id', $userId);
            }
            $bankAccountIds = $bankAccountsQuery->pluck('id');

            if ($bankAccountIds->isNotEmpty()) {
                $bankTransactionsQuery->whereIn('bank_account_id', $bankAccountIds);
            } else {
                $bankTransactionsQuery->whereRaw('1 = 0'); // No results
            }

            $bankTransactions = $bankTransactionsQuery->get();

            foreach ($bankTransactions as $transaction) {
                $bankAccount = $transaction->bankAccount;
                $accountUser = $bankAccount && $bankAccount->user ? $bankAccount->user->name : 'Unknown';
                
                $transactions[] = [
                    'id' => 'bank_' . $transaction->id,
                    'type' => 'bank_to_bank',
                    'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                    'from_user' => $transaction->type === 'debit' ? $accountUser : 'Bank Account',
                    'to_user' => $transaction->type === 'credit' ? $accountUser : 'Bank Account',
                    'amount' => (float) $transaction->amount,
                    'direction' => $transaction->type, // 'credit' or 'debit'
                    'description' => $transaction->description,
                ];
            }
        }

        // Sort by date descending
        usort($transactions, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
            'total' => count($transactions),
        ]);
    }
}
