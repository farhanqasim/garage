@extends('layouts.app')
@section('title', 'Cash Transactions History')
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Cash Transactions History</h2>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.cash-transactions.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})@if($user->role ?? null) - {{ ucwords(str_replace('_', ' ', $user->role)) }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="job_payment" {{ request('type') == 'job_payment' ? 'selected' : '' }}>Job Payment</option>
                            <option value="cash_transfer" {{ request('type') == 'cash_transfer' ? 'selected' : '' }}>Cash Transfer</option>
                            <option value="bank_transfer" {{ request('type') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="commission" {{ request('type') == 'commission' ? 'selected' : '' }}>Commission</option>
                            <option value="admin_adjustment" {{ request('type') == 'admin_adjustment' ? 'selected' : '' }}>Admin Adjustment</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Direction</label>
                        <select name="direction" class="form-select">
                            <option value="">All</option>
                            <option value="credit" {{ request('direction') == 'credit' ? 'selected' : '' }}>Credit</option>
                            <option value="debit" {{ request('direction') == 'debit' ? 'selected' : '' }}>Debit</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
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
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="{{ route('admin.cash-transactions.index') }}" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="d-flex justify-content-end mb-3">
                    <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="searchableTable" class="table table-hover table-center">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>User</th>
                                <th>Related User</th>
                                <th>Type</th>
                                <th>Direction</th>
                                <th>Amount</th>
                                <th>Branch</th>
                                <th>Note</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $index => $transaction)
                                <tr>
                                    <td>{{ $index + 1 + (($transactions->currentPage() - 1) * $transactions->perPage()) }}</td>
                                    <td>{{ $transaction->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <strong>{{ $transaction->user->name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $transaction->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($transaction->relatedUser)
                                            {{ $transaction->relatedUser->name }}
                                            <br><small class="text-muted">{{ $transaction->relatedUser->email }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
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
                                    <td>
                                        {{ $transaction->branch->branch_name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($transaction->note ?? 'N/A', 50) }}</small>
                                    </td>
                                    <td>
                                        @if($transaction->reference_id && $transaction->reference_table)
                                            <small class="text-muted">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->reference_table)) }} #{{ $transaction->reference_id }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="ti ti-file-off fs-48 mb-3 d-block"></i>
                                        No transactions found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
