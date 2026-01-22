<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;

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
}
