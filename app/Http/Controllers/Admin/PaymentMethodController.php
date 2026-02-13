<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('sort_order')->orderBy('name')->paginate(15);
        return view('admin.payment-methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('admin.payment-methods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_urdu' => 'nullable|string|max:255',
            'code' => 'required|string|max:255|unique:payment_methods,code',
            'description' => 'nullable|string',
            'requires_bank_account' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        PaymentMethod::create([
            'name' => $validated['name'],
            'name_urdu' => $validated['name_urdu'] ?? null,
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'requires_bank_account' => $validated['requires_bank_account'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method created successfully!');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_urdu' => 'nullable|string|max:255',
            'code' => 'required|string|max:255|unique:payment_methods,code,' . $paymentMethod->id,
            'description' => 'nullable|string',
            'requires_bank_account' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $paymentMethod->update([
            'name' => $validated['name'],
            'name_urdu' => $validated['name_urdu'] ?? null,
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'requires_bank_account' => $validated['requires_bank_account'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method updated successfully!');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method deleted successfully!');
    }
}
