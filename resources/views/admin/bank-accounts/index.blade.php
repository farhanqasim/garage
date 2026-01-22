@extends('layouts.app')
@section('title', __('Bank Accounts List'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Bank Accounts</h2>
                </div>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary">
                    <i class="ti ti-circle-plus me-1"></i>Add Bank Account
                </a>
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
                <div class="d-flex gap-2 flex-wrap">
                    <!-- Filter by Account Type -->
                    <form method="GET" action="{{ route('admin.bank-accounts.index') }}" class="d-inline">
                        <select name="account_type" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="bank" {{ request('account_type') == 'bank' ? 'selected' : '' }}>Bank Accounts</option>
                            <option value="cash" {{ request('account_type') == 'cash' ? 'selected' : '' }}>Cash Accounts</option>
                        </select>
                    </form>

                    @if(request('account_type'))
                        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-sm btn-secondary">Clear Filter</a>
                    @endif
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table id="searchableTable" class="table table-hover table-center" style="min-width: 1000px;">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>Bank</th>
                                <th>Account Type</th>
                                <th>Account Title</th>
                                <th>Account Number</th>
                                <th>IBAN</th>
                                <th>Branch Code</th>
                                <th>Opening Balance</th>
                                <th>Current Balance</th>
                                <th>Primary</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bankAccounts as $index => $account)
                                <tr>
                                    <td>{{ $index + 1 + (($bankAccounts->currentPage() - 1) * $bankAccounts->perPage()) }}</td>
                                    <td><strong>{{ $account->bank->name }}</strong></td>
                                    <td>
                                        @if(($account->account_type ?? 'bank') == 'cash')
                                            <span class="badge badge-info">Cash</span>
                                        @else
                                            <span class="badge badge-primary">Bank</span>
                                        @endif
                                    </td>
                                    <td>{{ $account->account_title }}</td>
                                    <td>{{ $account->account_number }}</td>
                                    <td>{{ $account->iban ?? 'N/A' }}</td>
                                    <td>{{ $account->branch_code ?? 'N/A' }}</td>
                                    <td>
                                        <strong>{{ number_format($account->opening_balance ?? 0, 2) }} PKR</strong>
                                    </td>
                                    <td>
                                        <strong class="text-{{ $account->current_balance >= 0 ? 'success' : 'danger' }}">
                                            {{ number_format($account->current_balance ?? 0, 2) }} PKR
                                        </strong>
                                    </td>
                                    <td>
                                        @if($account->is_primary)
                                            <span class="badge badge-success">Primary</span>
                                        @else
                                            <span class="badge badge-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($account->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.bank-accounts.show', $account->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.bank-accounts.toggle-status', $account->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $account->status ? 'warning' : 'success' }}" title="{{ $account->status ? 'Deactivate' : 'Activate' }}">
                                                    <i class="ti ti-{{ $account->status ? 'toggle-left' : 'toggle-right' }}"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.bank-accounts.edit', $account->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.bank-accounts.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this bank account?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-4">
                                        <p class="text-muted">No bank accounts found. <a href="{{ route('admin.bank-accounts.create') }}">Create one</a></p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($bankAccounts->hasPages())
                <div class="card-footer p-3">
                    <div class="d-flex justify-content-center w-100" style="overflow-x: auto;">
                        {{ $bankAccounts->links('pagination::default') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Simple table search
        document.getElementById('tableSearch')?.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('searchableTable');
            const rows = table?.getElementsByTagName('tbody')[0]?.getElementsByTagName('tr') || [];

            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });
    </script>
@endsection
