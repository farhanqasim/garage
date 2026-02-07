@extends('layouts.app')

@section('title', 'Sale Payments - #{{ $sale->reference ?? $sale->id }}')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Sale Payments</h4>
            <h6>View and manage payments for sale #{{ $sale->reference ?? $sale->id }}</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-secondary me-2">
                <i class="ti ti-arrow-left me-1"></i> Back to Sale
            </a>
            <a href="{{ route('sales.payments.create', $sale->id) }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Create Payment
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <!-- Payment Summary Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 rounded" style="background: #f8f9fa;">
                                <p class="mb-1 text-muted">Grand Total</p>
                                <h4 class="mb-0 fw-bold">Rs {{ number_format($sale->grand_total, 2) }}</h4>
                            </div>
                        </div>
                        @if($discount > 0)
                        <div class="col-md-2">
                            <div class="text-center p-3 rounded" style="background: #d1ecf1;">
                                <p class="mb-1 text-muted">Discount</p>
                                <h4 class="mb-0 fw-bold text-info">Rs {{ number_format($discount, 2) }}</h4>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-{{ $discount > 0 ? '2' : '3' }}">
                            <div class="text-center p-3 rounded" style="background: #d4edda;">
                                <p class="mb-1 text-muted">Total Paid</p>
                                <h4 class="mb-0 fw-bold text-success">Rs {{ number_format($totalPaid, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-{{ $discount > 0 ? '2' : '3' }}">
                            <div class="text-center p-3 rounded" style="background: {{ $due > 0 ? '#fff3cd' : '#d4edda' }};">
                                <p class="mb-1 text-muted">Due Amount</p>
                                <h4 class="mb-0 fw-bold {{ $due > 0 ? 'text-warning' : 'text-success' }}">Rs {{ number_format($due, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-{{ $discount > 0 ? '3' : '3' }}">
                            <div class="text-center p-3 rounded" style="background: #f8f9fa;">
                                <p class="mb-1 text-muted">Payment Status</p>
                                @if($discount > 0 && $totalPaid == 0 && $discount >= $sale->grand_total)
                                    <span class="badge badge-success fs-14">Paid</span>
                                @elseif($totalPaid >= $sale->grand_total)
                                    <span class="badge badge-success fs-14">Paid</span>
                                @elseif($totalPaid > 0)
                                    <span class="badge badge-info fs-14">Partial</span>
                                @else
                                    <span class="badge badge-warning fs-14">Pending</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments List -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Transaction ID</th>
                                    <th>Bank Account</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $index => $payment)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $payment->paymentMethod->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="fw-bold">Rs {{ number_format($payment->pivot->allocated_amount, 2) }}</td>
                                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                    <td>
                                        @if($payment->bankAccount)
                                            {{ $payment->bankAccount->bank->name ?? 'N/A' }} - {{ $payment->bankAccount->account_number }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $payment->notes ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('sales.download.pdf', $sale->id) }}" class="btn btn-sm btn-secondary" target="_blank" title="Download Invoice">
                                            <i class="ti ti-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <p class="text-muted mb-0">No payments found for this sale.</p>
                                        <a href="{{ route('sales.payments.create', $sale->id) }}" class="btn btn-primary mt-2">
                                            <i class="ti ti-plus me-1"></i> Create First Payment
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
