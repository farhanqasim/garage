@extends('layouts.app')

@section('title', 'Car Wash Payments')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Car Wash Payments</h4>
            <h6>Manage payments, commissions, and transfers</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('car-wash.payments.create', ['type' => 'commission']) }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Pay Commission
            </a>
            <a href="{{ route('car-wash.payments.create', ['type' => 'cash_transfer']) }}" class="btn btn-success">
                <i class="ti ti-transfer me-1"></i> Cash Transfer
            </a>
            <a href="{{ route('car-wash.payments.create', ['type' => 'bank_transfer']) }}" class="btn btn-info">
                <i class="ti ti-building-bank me-1"></i> Bank Transfer
            </a>
        </div>
    </div>

    <!-- Available Cash Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="ti ti-wallet me-2"></i>Available Cash Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded p-3 me-3">
                                    <i class="ti ti-arrow-down fs-20"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted">Total Income</p>
                                    <h4 class="mb-0">Rs {{ number_format($availableCash['total_income'], 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger text-white rounded p-3 me-3">
                                    <i class="ti ti-arrow-up fs-20"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted">Total Expenses</p>
                                    <h4 class="mb-0">Rs {{ number_format($availableCash['total_expenses'], 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning text-white rounded p-3 me-3">
                                    <i class="ti ti-users fs-20"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted">Commission Paid</p>
                                    <h4 class="mb-0">Rs {{ number_format($availableCash['total_commission_paid'], 0) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success text-white rounded p-3 me-3">
                                    <i class="ti ti-wallet fs-20"></i>
                                </div>
                                <div>
                                    <p class="mb-0 text-muted">Available Cash</p>
                                    <h4 class="mb-0 fw-bold">Rs {{ number_format($availableCash['available_cash'], 0) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('car-wash.payments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Payment Type</label>
                    <select name="payment_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="commission" {{ request('payment_type') == 'commission' ? 'selected' : '' }}>Commission</option>
                        <option value="cash_transfer" {{ request('payment_type') == 'cash_transfer' ? 'selected' : '' }}>Cash Transfer</option>
                        <option value="bank_transfer" {{ request('payment_type') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="expense" {{ request('payment_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Worker</label>
                    <select name="worker_id" class="form-select">
                        <option value="">All Workers</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}" {{ request('worker_id') == $worker->id ? 'selected' : '' }}>
                                {{ $worker->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('car-wash.payments.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Worker/Description</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>
                                <span class="badge 
                                    @if($payment->payment_type == 'commission') bg-primary
                                    @elseif($payment->payment_type == 'cash_transfer') bg-success
                                    @elseif($payment->payment_type == 'bank_transfer') bg-info
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                                </span>
                            </td>
                            <td>
                                @if($payment->worker)
                                    <strong>{{ $payment->worker->name }}</strong>
                                @elseif($payment->payment_type == 'cash_transfer')
                                    <small>{{ $payment->fromAccount->bank->name ?? 'N/A' }} → {{ $payment->toAccount->bank->name ?? 'N/A' }}</small>
                                @elseif($payment->notes)
                                    <small>{{ Str::limit($payment->notes, 50) }}</small>
                                @else
                                    <small>N/A</small>
                                @endif
                            </td>
                            <td><strong>Rs {{ number_format($payment->amount, 0) }}</strong></td>
                            <td>{{ $payment->paymentMethod->display_name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge 
                                    @if($payment->status == 'completed') bg-success
                                    @elseif($payment->status == 'pending') bg-warning
                                    @else bg-danger
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>
                                @if($payment->transaction_id)
                                    <small class="text-muted">{{ $payment->transaction_id }}</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No payments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
