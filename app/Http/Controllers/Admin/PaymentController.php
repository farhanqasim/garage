<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'customer', 'supplier', 'paymentMethod', 'bankAccount']);

        if ($request->has('payment_method_id') && $request->payment_method_id) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->has('direction') && $request->direction) {
            $query->where('direction', $request->direction);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $payments = $query->orderBy('payment_date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(15);

        $paymentMethods = PaymentMethod::active()->get();
        $customers = Customer::orderBy('names', 'asc')->get();
        $suppliers = Supplier::orderBy('names', 'asc')->get();

        return view('admin.payments.index', compact('payments', 'paymentMethods', 'customers', 'suppliers'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'customer', 'supplier', 'paymentMethod', 'bankAccount', 'sales', 'purchases']);
        return view('admin.payments.show', compact('payment'));
    }

    public function markAsPaid(Payment $payment)
    {
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Payment marked as paid successfully!');
    }

    public function markAsFailed(Payment $payment)
    {
        $payment->update([
            'status' => 'failed',
        ]);

        return redirect()->back()
            ->with('success', 'Payment marked as failed successfully!');
    }

    public function markAsRefunded(Payment $payment)
    {
        $payment->update([
            'status' => 'refunded',
        ]);

        return redirect()->back()
            ->with('success', 'Payment marked as refunded successfully!');
    }
}
