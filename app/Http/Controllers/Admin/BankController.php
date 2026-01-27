<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Services\CashAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    /**
     * Display a listing of the banks.
     */
    public function index(Request $request)
    {
        // If request expects JSON (API call), return JSON
        if ($request->wantsJson() || $request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $banks = Bank::where('status', true)->orderBy('name', 'asc')->get();
            
            return response()->json([
                'success' => true,
                'banks' => $banks->map(function ($bank) {
                    return [
                        'id' => $bank->id,
                        'name' => $bank->name,
                        'short_name' => $bank->short_name,
                        'icon' => 'bank',
                        'type' => 'bank',
                        'balance' => 0, // You can add balance calculation here
                        'subtitle' => $bank->short_name ?? $bank->name,
                    ];
                })
            ]);
        }
        
        // Otherwise return HTML view
        $banks = Bank::orderBy('name', 'asc')->paginate(10);
        return view('admin.banks.index', compact('banks'));
    }

    /**
     * Show the form for creating a new bank.
     */
    public function create()
    {
        return view('admin.banks.create');
    }

    /**
     * Store a newly created bank in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'api_enabled' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ]);

        Bank::create([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'] ?? null,
            'api_enabled' => $validated['api_enabled'] ?? false,
            'status' => $validated['status'] ?? true,
        ]);

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank created successfully!');
    }

    /**
     * Show the form for editing the specified bank.
     */
    public function edit(Bank $bank)
    {
        return view('admin.banks.edit', compact('bank'));
    }

    /**
     * Update the specified bank in storage.
     */
    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'api_enabled' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ]);

        $bank->update([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'] ?? null,
            'api_enabled' => $validated['api_enabled'] ?? false,
            'status' => $validated['status'] ?? true,
        ]);

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank updated successfully!');
    }

    /**
     * Remove the specified bank from storage.
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank deleted successfully!');
    }

    /**
     * Toggle the status of the specified bank.
     */
    public function toggleStatus(Bank $bank)
    {
        $bank->update([
            'status' => !$bank->status,
        ]);

        return redirect()->back()
            ->with('success', 'Bank status updated successfully!');
    }

    /**
     * Store cash transfer to bank account
     */
    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $bankAccount = BankAccount::findOrFail($validated['bank_account_id']);
        $amount = (float) $validated['amount'];

        return DB::transaction(function () use ($user, $bankAccount, $amount, $validated) {
            try {
                // Get bank name safely
                $bankName = 'Bank';
                if ($bankAccount->bank) {
                    $bankName = $bankAccount->bank->name;
                }
                
                // Get branch ID safely
                $branchId = null;
                if (isset($user->branch_id)) {
                    $branchId = $user->branch_id;
                }
                
                // Get notes safely
                $notes = "Cash transfer to {$bankName} - {$bankAccount->account_title}";
                if (isset($validated['notes']) && !empty($validated['notes'])) {
                    $notes = $validated['notes'];
                }
                
                // Debit from user's cash account
                $cashAccountService = app(CashAccountService::class);
                $cashAccountService->debit(
                    $user->id,
                    $amount,
                    'bank_transfer',
                    $bankAccount->id,
                    'bank_accounts',
                    $branchId,
                    $notes
                );

                // Credit to bank account
                $transferNotes = 'Cash deposit';
                if (isset($validated['notes']) && !empty($validated['notes'])) {
                    $transferNotes = $validated['notes'];
                }
                    
                BankTransaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => now(),
                    'description' => "Cash Transfer from {$user->name} - {$transferNotes}",
                    'amount' => $amount,
                    'type' => 'credit',
                    'statement_reference' => 'CASH-TRANSFER-' . time(),
                    'reconciled' => false,
                ]);

                $message = "Rs." . number_format($amount, 2) . " transferred to {$bankName} successfully!";
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        });
    }
}
