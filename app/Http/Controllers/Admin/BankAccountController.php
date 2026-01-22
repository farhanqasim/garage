<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the bank accounts.
     */
    public function index(Request $request)
    {
        $query = BankAccount::with('bank');

        // Filter by account type
        if ($request->has('account_type') && $request->account_type) {
            $query->where('account_type', $request->account_type);
        }

        $bankAccounts = $query->orderBy('bank_id', 'asc')
            ->orderBy('is_primary', 'desc')
            ->orderBy('account_title', 'asc')
            ->paginate(10);
        
        return view('admin.bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Show the form for creating a new bank account.
     */
    public function create()
    {
        $banks = Bank::where('status', true)->orderBy('name', 'asc')->get();
        return view('admin.bank-accounts.create', compact('banks'));
    }

    /**
     * Store a newly created bank account in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'account_type' => 'required|in:bank,cash',
            'account_title' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'iban' => 'nullable|string|max:255',
            'branch_code' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_primary' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // If this account is marked as primary, unmark other primary accounts for the same bank
            if ($validated['is_primary'] ?? false) {
                BankAccount::where('bank_id', $validated['bank_id'])
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            BankAccount::create([
                'bank_id' => $validated['bank_id'],
                'account_type' => $validated['account_type'],
                'account_title' => $validated['account_title'],
                'account_number' => $validated['account_number'],
                'iban' => $validated['iban'] ?? null,
                'branch_code' => $validated['branch_code'] ?? null,
                'ifsc_code' => $validated['ifsc_code'] ?? null,
                'opening_balance' => $validated['opening_balance'] ?? 0,
                'is_primary' => $validated['is_primary'] ?? false,
                'status' => $validated['status'] ?? true,
            ]);

            DB::commit();

            return redirect()->route('admin.bank-accounts.index')
                ->with('success', 'Bank account created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating bank account: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified bank account.
     */
    public function show(BankAccount $bankAccount)
    {
        $bankAccount->load(['bank', 'payments.paymentMethod', 'bankTransactions.matchedPayment']);
        
        // Calculate summary
        $totalPayments = $bankAccount->payments()->count();
        $totalTransactions = $bankAccount->bankTransactions()->count();
        $reconciledTransactions = $bankAccount->bankTransactions()->where('reconciled', true)->count();
        
        return view('admin.bank-accounts.show', compact('bankAccount', 'totalPayments', 'totalTransactions', 'reconciledTransactions'));
    }

    /**
     * Show the form for editing the specified bank account.
     */
    public function edit(BankAccount $bankAccount)
    {
        $banks = Bank::where('status', true)->orderBy('name', 'asc')->get();
        return view('admin.bank-accounts.edit', compact('bankAccount', 'banks'));
    }

    /**
     * Update the specified bank account in storage.
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'account_type' => 'required|in:bank,cash',
            'account_title' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'iban' => 'nullable|string|max:255',
            'branch_code' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric|min:0',
            'is_primary' => 'nullable|boolean',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // If this account is marked as primary, unmark other primary accounts for the same bank
            if ($validated['is_primary'] ?? false) {
                BankAccount::where('bank_id', $validated['bank_id'])
                    ->where('id', '!=', $bankAccount->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }

            $bankAccount->update([
                'bank_id' => $validated['bank_id'],
                'account_type' => $validated['account_type'],
                'account_title' => $validated['account_title'],
                'account_number' => $validated['account_number'],
                'iban' => $validated['iban'] ?? null,
                'branch_code' => $validated['branch_code'] ?? null,
                'ifsc_code' => $validated['ifsc_code'] ?? null,
                'opening_balance' => $validated['opening_balance'] ?? 0,
                'is_primary' => $validated['is_primary'] ?? false,
                'status' => $validated['status'] ?? true,
            ]);

            DB::commit();

            return redirect()->route('admin.bank-accounts.index')
                ->with('success', 'Bank account updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating bank account: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified bank account from storage.
     */
    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', 'Bank account deleted successfully!');
    }

    /**
     * Toggle the status of the specified bank account.
     */
    public function toggleStatus(BankAccount $bankAccount)
    {
        $bankAccount->update([
            'status' => !$bankAccount->status,
        ]);

        return redirect()->back()
            ->with('success', 'Bank account status updated successfully!');
    }
}
