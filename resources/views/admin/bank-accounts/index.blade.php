@extends('layouts.app')
@section('title', __('Bank Accounts List'))
@section('content')
@include('admin.partials.vyapar-bank-style')
<div class="content vyapar-bank-page">
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h2><i class="ti ti-wallet"></i> Bank Accounts</h2>
        <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Add Bank Account
        </a>
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

    <div class="card vyapar-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <form method="GET" action="{{ route('admin.bank-accounts.index') }}" class="d-flex align-items-center gap-2">
                <select name="account_type" class="form-select form-select-sm" style="max-width: 180px;" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="bank" {{ request('account_type') == 'bank' ? 'selected' : '' }}>Bank Accounts</option>
                    <option value="cash" {{ request('account_type') == 'cash' ? 'selected' : '' }}>Cash Accounts</option>
                </select>
                @if(request('account_type'))
                    <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </form>
            <input type="text" id="tableSearch" class="form-control" placeholder="Search bank accounts..." style="max-width: 280px;">
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="searchableTable" class="table table-hover vyapar-table mb-0" style="min-width: 1000px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Bank</th>
                            <th>Account Type</th>
                            <th>Account Title</th>
                            <th>Account Number</th>
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
                                <td><strong>{{ $account->bank->name ?? '—' }}</strong></td>
                                <td>
                                    @if(($account->account_type ?? 'bank') == 'cash')
                                        <span class="badge badge-info">Cash</span>
                                    @else
                                        <span class="badge badge-primary">Bank</span>
                                    @endif
                                </td>
                                <td>{{ $account->account_title }}</td>
                                <td>{{ $account->account_number }}</td>
                                <td><strong>{{ number_format($account->opening_balance ?? 0, 2) }} PKR</strong></td>
                                <td>
                                    <strong class="text-{{ $account->current_balance >= 0 ? 'success' : 'danger' }}">
                                        {{ number_format($account->current_balance ?? 0, 2) }} PKR
                                    </strong>
                                </td>
                                <td>
                                    @if($account->is_primary)
                                        <span class="badge badge-success">Primary</span>
                                    @else
                                        <span class="badge badge-secondary">—</span>
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
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="{{ route('admin.bank-accounts.show', $account->id) }}" class="btn btn-sm btn-info" title="View">
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
                                <td colspan="10" class="empty-state">
                                    <p class="mb-1">No bank accounts found.</p>
                                    <a href="{{ route('admin.bank-accounts.create') }}">Add your first bank account</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bankAccounts->hasPages())
            <div class="card-footer p-3 border-top">
                {{ $bankAccounts->links('pagination::default') }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('tableSearch')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const table = document.getElementById('searchableTable');
    const rows = table?.getElementsByTagName('tbody')[0]?.getElementsByTagName('tr') || [];
    for (let row of rows) {
        if (row.querySelector('.empty-state')) continue;
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    }
});
</script>
@endpush
@endsection
