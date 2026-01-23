@extends('layouts.app')
@section('title', 'Cash Accounts (Wallets)')
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Cash Accounts (Wallets)</h2>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="d-flex justify-content-end mb-3">
                    <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search by user name or email...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="searchableTable" class="table table-hover table-center">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>User Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Balance</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cashAccounts as $index => $account)
                                <tr>
                                    <td>{{ $index + 1 + (($cashAccounts->currentPage() - 1) * $cashAccounts->perPage()) }}</td>
                                    <td>
                                        <strong>{{ $account->user->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $account->user->email ?? 'N/A' }}</td>
                                    <td>{{ $account->user->phone ?? 'N/A' }}</td>
                                    <td>
                                        <strong class="text-{{ $account->balance >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($account->balance, 2) }} PKR
                                        </strong>
                                    </td>
                                    <td>{{ $account->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a href="{{ route('admin.cash-accounts.show', $account->id) }}" 
                                               class="me-2 p-2" 
                                               title="View Details">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="ti ti-wallet-off fs-48 mb-3 d-block"></i>
                                        No cash accounts found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $cashAccounts->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
