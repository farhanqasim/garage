@extends('layouts.app')
@section('title', __('Bank Transaction Details'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h2 class="fw-bold">Bank Transaction Details</h2>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.bank-transactions.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Transactions
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Transaction Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Transaction ID:</strong>
                                <p class="text-muted">#{{ $bankTransaction->id }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Transaction Date:</strong>
                                <p class="text-muted">{{ $bankTransaction->transaction_date->format('Y-m-d') }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Bank Account:</strong>
                                <p class="text-muted">
                                    {{ $bankTransaction->bankAccount->account_title }}
                                    <br><small>{{ $bankTransaction->bankAccount->bank->name ?? 'N/A' }}</small>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Type:</strong>
                                <p class="text-muted">
                                    @if($bankTransaction->type == 'credit')
                                        <span class="badge badge-success">Credit (Money In)</span>
                                    @else
                                        <span class="badge badge-danger">Debit (Money Out)</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Amount:</strong>
                                <p class="text-muted">
                                    <strong class="fs-18">{{ number_format($bankTransaction->amount, 2) }} PKR</strong>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Statement Reference:</strong>
                                <p class="text-muted">
                                    @if($bankTransaction->statement_reference)
                                        <code>{{ $bankTransaction->statement_reference }}</code>
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($bankTransaction->description)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong>Description:</strong>
                                    <p class="text-muted">{{ $bankTransaction->description }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Reconciled Status:</strong>
                                <p class="text-muted">
                                    @if($bankTransaction->reconciled)
                                        <span class="badge badge-success">Yes</span>
                                        @if($bankTransaction->reconciled_at)
                                            <br><small>Reconciled on: {{ $bankTransaction->reconciled_at->format('Y-m-d H:i') }}</small>
                                        @endif
                                        @if($bankTransaction->reconciledBy)
                                            <br><small>By: {{ $bankTransaction->reconciledBy->name }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-warning">No</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Created At:</strong>
                                <p class="text-muted">{{ $bankTransaction->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Matched Payment</h5>
                    </div>
                    <div class="card-body">
                        @if($bankTransaction->matchedPayment)
                            <div class="mb-3">
                                <strong>Payment ID:</strong>
                                <p class="text-muted">
                                    <a href="{{ route('admin.payments.show', $bankTransaction->matchedPayment->id) }}" class="badge badge-info">
                                        Payment #{{ $bankTransaction->matchedPayment->id }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <strong>Amount:</strong>
                                <p class="text-muted">{{ number_format($bankTransaction->matchedPayment->amount, 2) }} {{ $bankTransaction->matchedPayment->currency }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Payment Date:</strong>
                                <p class="text-muted">{{ $bankTransaction->matchedPayment->payment_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Payment Method:</strong>
                                <p class="text-muted">{{ $bankTransaction->matchedPayment->paymentMethod->display_name ?? $bankTransaction->matchedPayment->paymentMethod->name }}</p>
                            </div>
                        @else
                            <p class="text-muted">No payment matched with this transaction.</p>
                        @endif
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.bank-transactions.edit', $bankTransaction->id) }}" class="btn btn-primary">
                                <i class="ti ti-edit me-1"></i>Edit Transaction
                            </a>
                            @if(!$bankTransaction->reconciled)
                                <form action="{{ route('admin.bank-transactions.reconcile', $bankTransaction->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Mark this transaction as reconciled?');">
                                        <i class="ti ti-check me-1"></i>Mark as Reconciled
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.bank-transactions.unreconcile', $bankTransaction->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Mark this transaction as unreconciled?');">
                                        <i class="ti ti-x me-1"></i>Mark as Unreconciled
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.bank-transactions.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
