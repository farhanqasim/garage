@extends('layouts.app')
@section('title', 'Cash Account Details')
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Cash Account Details</h2>
                </div>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.cash-accounts.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">User Name</label>
                            <p class="mb-0"><strong>{{ $cashAccount->user->name ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <p class="mb-0">{{ $cashAccount->user->email ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Phone</label>
                            <p class="mb-0">{{ $cashAccount->user->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Current Balance</label>
                            <p class="mb-0">
                                <strong class="fs-18 text-{{ $cashAccount->balance >= 0 ? 'success' : 'danger' }}">
                                    {{ number_format($cashAccount->balance, 2) }} PKR
                                </strong>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Account Created</label>
                            <p class="mb-0">{{ $cashAccount->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Transaction History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Direction</th>
                                        <th>Amount</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($cashAccount->user->cashTransactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($transaction->direction == 'credit')
                                                    <span class="badge badge-success">Credit</span>
                                                @else
                                                    <span class="badge badge-danger">Debit</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-{{ $transaction->direction == 'credit' ? 'success' : 'danger' }}">
                                                    {{ $transaction->direction == 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} PKR
                                                </strong>
                                            </td>
                                            <td>{{ $transaction->note ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No transactions found
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
