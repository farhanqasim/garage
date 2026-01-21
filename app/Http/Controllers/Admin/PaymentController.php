<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'bank']);

        // Filter by payment method
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load(['user', 'bank']);
        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Mark payment as paid.
     */
    public function markAsPaid(Payment $payment)
    {
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Payment marked as paid successfully!');
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(Payment $payment)
    {
        $payment->update([
            'status' => 'failed',
        ]);

        return redirect()->back()
            ->with('success', 'Payment marked as failed successfully!');
    }

    /**
     * Mark payment as refunded.
     */
    public function markAsRefunded(Payment $payment)
    {
        $payment->update([
            'status' => 'refunded',
        ]);

        return redirect()->back()
            ->with('success', 'Payment marked as refunded successfully!');
    }
}
