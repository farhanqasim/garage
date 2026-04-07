@extends('layouts.app')
@section('title', __('Bank Account Details'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h2 class="fw-bold">Bank Account Details</h2>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.banks.index', ['tab' => 'accounts']) }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Bank Accounts
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Bank:</strong>
                                <p class="text-muted">
                                    {{ $bankAccount->bank->name }}
                                    @if($bankAccount->bank->short_name)
                                        <small>({{ $bankAccount->bank->short_name }})</small>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Account Type:</strong>
                                <p class="text-muted">
                                    @if($bankAccount->account_type == 'cash')
                                        <span class="badge badge-info">Cash Account</span>
                                    @else
                                        <span class="badge badge-primary">Bank Account</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Account Title:</strong>
                                <p class="text-muted">{{ $bankAccount->account_title }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Account Number:</strong>
                                <p class="text-muted">{{ $bankAccount->account_number }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>IBAN:</strong>
                                <p class="text-muted">{{ $bankAccount->iban ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Branch Code:</strong>
                                <p class="text-muted">{{ $bankAccount->branch_code ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>IFSC Code:</strong>
                                <p class="text-muted">{{ $bankAccount->ifsc_code ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <p class="text-muted">
                                    @if($bankAccount->status)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                    @if($bankAccount->is_primary)
                                        <span class="badge badge-info ms-2">Primary</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Balance Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-2">Opening Balance</h6>
                                    <h4 class="mb-0">{{ number_format($bankAccount->opening_balance ?? 0, 0) }} PKR</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-2">Current Balance</h6>
                                    <h4 class="mb-0 text-{{ $bankAccount->current_balance >= 0 ? 'success' : 'danger' }}">
                                        {{ number_format($bankAccount->current_balance ?? 0, 0) }} PKR
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-2">Total Transactions</h6>
                                    <h4 class="mb-0">{{ $totalTransactions }}</h4>
                                    <small class="text-muted">{{ $reconciledTransactions }} Tallied</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Transactions</h5>
                        <a href="{{ route('admin.bank-transactions.index', ['bank_account_id' => $bankAccount->id]) }}" class="btn btn-sm btn-primary">
                            View All
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Tallied</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bankAccount->bankTransactions()->orderBy('transaction_date', 'desc')->limit(10)->get() as $transaction)
                                        <tr>
                                            <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                            <td>{{ $transaction->description ?? 'N/A' }}</td>
                                            <td>
                                                @if($transaction->type == 'credit')
                                                    <span class="badge badge-success">Credit</span>
                                                @else
                                                    <span class="badge badge-danger">Debit</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($transaction->amount, 2) }} PKR</td>
                                            <td>
                                                @if($transaction->reconciled)
                                                    <span class="badge badge-success">Yes</span>
                                                @else
                                                    <span class="badge badge-warning">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <p class="text-muted">No transactions found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.bank-accounts.edit', $bankAccount->id) }}" class="btn btn-primary">
                                <i class="ti ti-edit me-1"></i>Edit Account
                            </a>
                            <a href="{{ route('admin.bank-transactions.create') }}?bank_account_id={{ $bankAccount->id }}" class="btn btn-success">
                                <i class="ti ti-plus me-1"></i>Add Transaction
                            </a>
                            <a href="{{ route('admin.bank-transactions.index', ['bank_account_id' => $bankAccount->id]) }}" class="btn btn-info">
                                <i class="ti ti-list me-1"></i>View Transactions
                            </a>
                            <a href="{{ route('admin.payments.index', ['bank_account_id' => $bankAccount->id]) }}" class="btn btn-warning">
                                <i class="ti ti-wallet me-1"></i>View Payments
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Total Payments:</strong>
                            <p class="text-muted mb-0">{{ $totalPayments }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>Total Transactions:</strong>
                            <p class="text-muted mb-0">{{ $totalTransactions }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>Tallied Transactions:</strong>
                            <p class="text-muted mb-0">{{ $reconciledTransactions }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>Untallied Transactions:</strong>
                            <p class="text-muted mb-0">{{ $totalTransactions - $reconciledTransactions }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
