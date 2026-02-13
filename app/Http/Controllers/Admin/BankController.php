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
use Illuminate\Support\Facades\Log;

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
        
        // Otherwise return HTML view - combined Banks + Bank Accounts (Vyapar style)
        $banks = Bank::orderBy('name', 'asc')->paginate(10, ['*'], 'banks_page');
        $bankAccountsQuery = BankAccount::with('bank')->where('user_id', Auth::id());
        if ($request->has('account_type') && $request->account_type) {
            $bankAccountsQuery->where('account_type', $request->account_type);
        }
        $bankAccounts = $bankAccountsQuery->orderBy('bank_id', 'asc')
            ->orderBy('is_primary', 'desc')
            ->orderBy('account_title', 'asc')
            ->paginate(10, ['*'], 'accounts_page');
        return view('admin.banks.index', compact('banks', 'bankAccounts'));
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
            'logo' => 'nullable|file|image|max:2048',
        ]);

        $bank = Bank::create([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'] ?? null,
            'api_enabled' => $validated['api_enabled'] ?? false,
            'status' => $validated['status'] ?? true,
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $filename = strtolower(preg_replace('/[^a-z0-9]/i', '', $bank->short_name ?? (string) $bank->id)) . '_' . time() . '.' . $ext;
            $dir = 'assets/img/banks';
            $file->move(public_path($dir), $filename);
            $bank->update(['logo' => $filename]);
        }

        return redirect()->route('admin.banks.index')->with('success', 'Bank created successfully!')->with('success_tab', 'banks');
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
            'logo' => 'nullable|file|image|max:2048',
        ]);

        $bank->update([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'] ?? null,
            'api_enabled' => $validated['api_enabled'] ?? false,
            'status' => $validated['status'] ?? true,
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $filename = strtolower(preg_replace('/[^a-z0-9]/i', '', $bank->short_name ?? (string) $bank->id)) . '_' . time() . '.' . $ext;
            $dir = 'assets/img/banks';
            $file->move(public_path($dir), $filename);
            $bank->update(['logo' => $filename]);
        }

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank updated successfully!')
            ->with('success_tab', 'banks');
    }

    /**
     * Remove the specified bank from storage.
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank deleted successfully!')
            ->with('success_tab', 'banks');
    }

    /**
     * Toggle the status of the specified bank.
     */
    public function toggleStatus(Bank $bank)
    {
        $bank->update([
            'status' => !$bank->status,
        ]);

        return redirect()->route('admin.banks.index')
            ->with('success', 'Bank status updated successfully!')
            ->with('success_tab', 'banks');
    }

    /**
     * Toggle API enabled status.
     */
    public function toggleApiEnabled(Bank $bank)
    {
        $bank->update([
            'api_enabled' => !$bank->api_enabled,
        ]);

        return redirect()->route('admin.banks.index')
            ->with('success', 'API enabled status updated successfully!')
            ->with('success_tab', 'banks');
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

        // Prevent transfer to own account
        if ($bankAccount->user_id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot transfer to your own account. Please select a different bank account.',
            ], 400);
        }

        return DB::transaction(function () use ($user, $bankAccount, $amount, $validated) {
            try {
                // Get sender's account (try bank type first, then cash type)
                $senderBankAccount = BankAccount::where('user_id', $user->id)
                    ->where('account_type', 'bank')
                    ->where(function ($q) {
                        $q->where('status', true)->orWhereNull('status');
                    })
                    ->first();
                
                if (!$senderBankAccount) {
                    $senderBankAccount = BankAccount::where('user_id', $user->id)
                        ->where('account_type', 'cash')
                        ->where(function ($q) {
                            $q->where('status', true)->orWhereNull('status');
                        })
                        ->first();
                }
                
                if (!$senderBankAccount) {
                    throw new \Exception("You don't have a bank or cash account linked. Please add a bank account in Admin first.");
                }
                
                // Check sender's bank account balance
                $senderBalance = $senderBankAccount->current_balance;
                if ($senderBalance < $amount) {
                    throw new \Exception("Insufficient bank balance. Available: Rs." . number_format($senderBalance, 2) . ", Required: Rs." . number_format($amount, 2));
                }
                
                // Get bank name safely
                $bankName = 'Bank';
                if ($bankAccount->bank) {
                    $bankName = $bankAccount->bank->name;
                }
                
                $senderBankName = 'Bank';
                if ($senderBankAccount->bank) {
                    $senderBankName = $senderBankAccount->bank->name;
                }
                
                // Get notes safely
                $transferNotes = 'Bank transfer';
                if (isset($validated['notes']) && !empty($validated['notes'])) {
                    $transferNotes = $validated['notes'];
                }
                
                // Debit from sender's bank account
                BankTransaction::create([
                    'bank_account_id' => $senderBankAccount->id,
                    'transaction_date' => now(),
                    'description' => "Transfer to {$bankName} - {$bankAccount->account_title} - {$transferNotes}",
                    'amount' => $amount,
                    'type' => 'debit',
                    'statement_reference' => 'BANK-TRANSFER-' . time(),
                    'reconciled' => true, // Auto-reconcile for transfers
                ]);

                // Credit to recipient's bank account
                BankTransaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'transaction_date' => now(),
                    'description' => "Transfer from {$senderBankName} - {$senderBankAccount->account_title} ({$user->name}) - {$transferNotes}",
                    'amount' => $amount,
                    'type' => 'credit',
                    'statement_reference' => 'BANK-TRANSFER-' . time(),
                    'reconciled' => true, // Auto-reconcile for transfers
                ]);

                $message = "Rs." . number_format($amount, 2) . " transferred from your bank account to {$bankName} successfully!";
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                \Log::error('Bank transfer error: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'bank_account_id' => $bankAccount->id,
                    'amount' => $amount,
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => $e->getMessage(),
                ], 500);
            }
        });
    }
}
