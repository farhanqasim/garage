@extends('layouts.app')

@section('title', 'Create Payment - Sale #{{ $sale->reference ?? $sale->id }}')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Payment</h4>
            <h6>Add payment for sale #{{ $sale->reference ?? $sale->id }}</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('sales.payments', $sale->id) }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-body p-4">
                    <!-- Payment Summary -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-{{ $discount > 0 ? '3' : '4' }}">
                                <p class="mb-1"><strong>Grand Total:</strong></p>
                                <h5 class="mb-0">Rs {{ number_format($sale->grand_total, 2) }}</h5>
                            </div>
                            @if($discount > 0)
                            <div class="col-md-3">
                                <p class="mb-1"><strong>Discount:</strong></p>
                                <h5 class="mb-0 text-info">Rs {{ number_format($discount, 2) }}</h5>
                            </div>
                            @endif
                            <div class="col-md-{{ $discount > 0 ? '3' : '4' }}">
                                <p class="mb-1"><strong>Total Paid:</strong></p>
                                <h5 class="mb-0 text-success">Rs {{ number_format($totalPaid, 2) }}</h5>
                            </div>
                            <div class="col-md-{{ $discount > 0 ? '3' : '4' }}">
                                <p class="mb-1"><strong>Remaining:</strong></p>
                                <h5 class="mb-0 {{ $remaining > 0 ? 'text-warning' : 'text-success' }}">Rs {{ number_format($remaining, 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('sales.payments.store', $sale->id) }}" method="POST" id="createPaymentForm">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method_id" id="payment_method_id" class="form-select" required>
                                    <option value="">Select Payment Method</option>
                                    @php
                                        $cashMethod = \App\Models\PaymentMethod::where('code', 'cash')->where('is_active', true)->first();
                                        $bankMethod = \App\Models\PaymentMethod::where('code', 'bank_transfer')->where('is_active', true)->first();
                                        if (!$bankMethod) {
                                            $bankMethod = \App\Models\PaymentMethod::where('requires_bank_account', true)->where('is_active', true)->first();
                                        }
                                    @endphp
                                    @if($cashMethod)
                                        <option value="{{ $cashMethod->id }}" data-requires-bank="0" data-method-code="cash">Cash</option>
                                    @endif
                                    @if($bankMethod)
                                        <option value="{{ $bankMethod->id }}" data-requires-bank="1" data-method-code="bank">Bank</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" name="payment_amount" id="payment_amount" class="form-control" step="0.01" min="0.01" max="{{ $remaining }}" value="{{ $remaining > 0 ? $remaining : 0 }}" required>
                                <small class="text-muted">Maximum: Rs {{ number_format($remaining, 2) }}</small>
                            </div>
                        </div>

                        <div class="row mb-3" id="bank-account-row" style="display: none;">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Bank Account <span class="text-danger">*</span></label>
                                <select name="bank_account_id" id="bank_account_id" class="form-select">
                                    <option value="">Select Bank Account</option>
                                    @php
                                        $bankAccounts = \App\Models\BankAccount::where('status', true)->with('bank')->get();
                                    @endphp
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->bank->name ?? 'N/A' }} - {{ $account->account_title }} ({{ $account->account_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Transaction ID <span class="text-danger">*</span></label>
                                <input type="text" name="payment_transaction_id" id="payment_transaction_id" class="form-control" placeholder="Enter transaction reference">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="payment_notes" id="payment_notes" class="form-control" rows="2" placeholder="Additional notes (optional)"></textarea>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('sales.payments', $sale->id) }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Payment method change handler
    $('#payment_method_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const requiresBank = selectedOption.data('requires-bank') == '1';
        const methodCode = selectedOption.data('method-code') || '';
        const isBank = methodCode.toLowerCase() === 'bank' || requiresBank;
        
        if (isBank && $(this).val()) {
            $('#bank-account-row').show();
            $('#bank_account_id').prop('required', true);
            $('#payment_transaction_id').prop('required', true);
        } else {
            $('#bank-account-row').hide();
            $('#bank_account_id').prop('required', false);
            $('#payment_transaction_id').prop('required', false);
        }
    });

    // Validate payment amount
    $('#payment_amount').on('input', function() {
        const amount = parseFloat($(this).val()) || 0;
        const max = parseFloat($(this).attr('max')) || 0;
        
        if (amount > max) {
            $(this).val(max);
            alert('Payment amount cannot exceed remaining amount (Rs ' + max.toFixed(2) + ')');
        }
    });
});
</script>

@endsection
