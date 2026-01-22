@extends('layouts.app')

@section('title', 'Create Payment - Car Wash')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Payment</h4>
            <h6>{{ ucfirst(str_replace('_', ' ', $paymentType)) }} Payment</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('car-wash.payments.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Available Cash Display -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <strong>Available Cash:</strong> 
                    <span class="fs-18 fw-bold text-success">Rs {{ number_format($availableCash['available_cash'], 2) }}</span>
                </div>
                <div>
                    <small class="text-muted">
                        Income: Rs {{ number_format($availableCash['total_income'], 2) }} | 
                        Expenses: Rs {{ number_format($availableCash['total_expenses'], 2) }} | 
                        Commission Paid: Rs {{ number_format($availableCash['total_commission_paid'], 2) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('car-wash.payments.store') }}" method="POST" id="paymentForm">
                        @csrf
                        
                        <input type="hidden" name="payment_type" value="{{ $paymentType }}">

                        @if($paymentType === 'commission')
                            <!-- Commission Payment Form -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Select Worker <span class="text-danger">*</span></label>
                                    <select name="worker_id" id="worker_id" class="form-select" required>
                                        <option value="">Select Worker</option>
                                        @foreach($workers as $worker)
                                            <option value="{{ $worker->id }}" 
                                                {{ $workerId == $worker->id ? 'selected' : '' }}
                                                data-commission="{{ $worker->commission }}">
                                                {{ $worker->name }} ({{ $worker->commission }}% Commission)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Pending Commission</label>
                                    <div class="input-group">
                                        <input type="text" id="pending_commission" class="form-control" value="Rs 0.00" readonly>
                                        <button type="button" class="btn btn-outline-primary" id="fillPendingCommission">
                                            <i class="ti ti-arrow-down"></i> Fill
                                        </button>
                                    </div>
                                    <small class="text-muted">Total pending commission for selected worker</small>
                                </div>
                            </div>
                        @endif

                        @if($paymentType === 'cash_transfer')
                            <!-- Cash Transfer Form -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">From Account <span class="text-danger">*</span></label>
                                    <select name="from_account_id" id="from_account_id" class="form-select" required>
                                        <option value="">Select Source Account</option>
                                        @foreach($bankAccounts as $account)
                                            <option value="{{ $account->id }}">
                                                {{ $account->bank->name ?? 'N/A' }} - {{ $account->account_title }} ({{ $account->account_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">To Account <span class="text-danger">*</span></label>
                                    <select name="to_account_id" id="to_account_id" class="form-select" required>
                                        <option value="">Select Destination Account</option>
                                        @foreach($bankAccounts as $account)
                                            <option value="{{ $account->id }}">
                                                {{ $account->bank->name ?? 'N/A' }} - {{ $account->account_title }} ({{ $account->account_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if($paymentType === 'bank_transfer')
                            <!-- Bank Transfer Form -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Bank Account <span class="text-danger">*</span></label>
                                    <select name="bank_account_id" id="bank_account_id" class="form-select" required>
                                        <option value="">Select Bank Account</option>
                                        @foreach($bankAccounts as $account)
                                            <option value="{{ $account->id }}">
                                                {{ $account->bank->name ?? 'N/A' }} - {{ $account->account_title }} ({{ $account->account_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Payment Method</label>
                                <select name="payment_method_id" id="payment_method_id" class="form-select">
                                    <option value="">Select Payment Method</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}">
                                            {{ $method->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Transaction ID / Reference</label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="Optional transaction reference">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes (optional)"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i> Create Payment
                                </button>
                                <a href="{{ route('car-wash.payments.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const paymentType = '{{ $paymentType }}';
    const availableCash = {{ $availableCash['available_cash'] }};

    // Commission payment handling
    if (paymentType === 'commission') {
        $('#worker_id').on('change', function() {
            const workerId = $(this).val();
            if (workerId) {
                // Fetch pending commission
                $.ajax({
                    url: '{{ route("car-wash.payments.pending-commission", ":id") }}'.replace(':id', workerId),
                    method: 'GET',
                    success: function(response) {
                        const pending = parseFloat(response.pending_commission) || 0;
                        $('#pending_commission').val('Rs ' + pending.toFixed(2));
                    },
                    error: function() {
                        $('#pending_commission').val('Rs 0.00');
                    }
                });
            } else {
                $('#pending_commission').val('Rs 0.00');
            }
        });

        // Fill pending commission
        $('#fillPendingCommission').on('click', function() {
            const pendingText = $('#pending_commission').val();
            const pending = parseFloat(pendingText.replace('Rs ', '').replace(/,/g, '')) || 0;
            if (pending > 0) {
                $('#amount').val(pending.toFixed(2));
            }
        });

        // Trigger change on load if worker is pre-selected
        @if($workerId)
            $('#worker_id').trigger('change');
        @endif
    }

    // Validate amount doesn't exceed available cash
    $('#paymentForm').on('submit', function(e) {
        const amount = parseFloat($('#amount').val()) || 0;
        
        if (paymentType !== 'commission' && amount > availableCash) {
            e.preventDefault();
            alert('Payment amount (Rs ' + amount.toFixed(2) + ') cannot exceed available cash (Rs ' + availableCash.toFixed(2) + ')!');
            $('#amount').focus();
            return false;
        }

        // Validate cash transfer accounts are different
        if (paymentType === 'cash_transfer') {
            const fromAccount = $('#from_account_id').val();
            const toAccount = $('#to_account_id').val();
            
            if (fromAccount === toAccount) {
                e.preventDefault();
                alert('Source and destination accounts cannot be the same!');
                return false;
            }
        }
    });
});
</script>
@endpush
