<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Payment;
use Illuminate\Http\Request;

class BankTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = BankTransaction::with(['bankAccount', 'matchedPayment', 'reconciledBy']);

        if ($request->has('bank_account_id') && $request->bank_account_id) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->has('reconciled') && $request->reconciled !== '') {
            $query->where('reconciled', $request->reconciled);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $bankAccounts = BankAccount::where('status', true)->orderBy('account_title')->get();

        return view('admin.bank-transactions.index', compact('transactions', 'bankAccounts'));
    }

    public function create()
    {
        $bankAccounts = BankAccount::where('status', true)->orderBy('account_title')->get();

        return view('admin.bank-transactions.create', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit',
            'statement_reference' => 'nullable|string|max:255',
        ]);

        BankTransaction::create([
            'bank_account_id' => $validated['bank_account_id'],
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'statement_reference' => $validated['statement_reference'] ?? null,
        ]);

        return redirect()->route('admin.bank-transactions.index')
            ->with('success', 'Bank transaction created successfully!');
    }

    public function show(BankTransaction $bankTransaction)
    {
        $bankTransaction->load(['bankAccount', 'matchedPayment', 'reconciledBy']);

        return view('admin.bank-transactions.show', compact('bankTransaction'));
    }

    public function edit(BankTransaction $bankTransaction)
    {
        $bankAccounts = BankAccount::where('status', true)->orderBy('account_title')->get();
        $payments = Payment::where('status', 'paid')->orderBy('payment_date', 'desc')->get();

        return view('admin.bank-transactions.edit', compact('bankTransaction', 'bankAccounts', 'payments'));
    }

    public function update(Request $request, BankTransaction $bankTransaction)
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit',
            'statement_reference' => 'nullable|string|max:255',
            'matched_payment_id' => 'nullable|exists:payments,id',
        ]);

        $bankTransaction->update([
            'bank_account_id' => $validated['bank_account_id'],
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'] ?? null,
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'statement_reference' => $validated['statement_reference'] ?? null,
            'matched_payment_id' => $validated['matched_payment_id'] ?? null,
        ]);

        return redirect()->route('admin.bank-transactions.index')
            ->with('success', 'Bank transaction updated successfully!');
    }

    public function destroy(BankTransaction $bankTransaction)
    {
        $bankTransaction->delete();

        return redirect()->route('admin.bank-transactions.index')
            ->with('success', 'Bank transaction deleted successfully!');
    }

    public function reconcile(BankTransaction $bankTransaction)
    {
        $bankTransaction->update([
            'reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Transaction marked as tallied!');
    }

    public function unreconcile(BankTransaction $bankTransaction)
    {
        $bankTransaction->update([
            'reconciled' => false,
            'reconciled_at' => null,
            'reconciled_by' => null,
        ]);

        return redirect()->back()
            ->with('success', 'Transaction marked as untallied!');
    }
}
