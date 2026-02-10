@extends('layouts.app')
@section('title', __('Banks & Bank Accounts'))
@section('content')
@include('admin.partials.vyapar-bank-style')
<div class="content vyapar-bank-page">
    <ul class="nav nav-tabs mb-3 border-0" id="banksTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ !request('tab') || request('tab') == 'banks' ? 'active' : '' }}" id="banks-tab" href="{{ route('admin.banks.index') }}" role="tab">Banks</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request('tab') == 'accounts' ? 'active' : '' }}" id="accounts-tab" href="{{ route('admin.banks.index', ['tab' => 'accounts']) }}" role="tab">Bank Accounts</a>
        </li>
    </ul>

    <div class="tab-content" id="banksTabContent">
        {{-- Banks Tab --}}
        <div class="tab-pane fade {{ !request('tab') || request('tab') == 'banks' ? 'show active' : '' }}" id="banks-pane" role="tabpanel">
            <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <h2 class="mb-0"><i class="ti ti-building-bank"></i> Banks</h2>
                <a href="{{ route('admin.banks.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Bank
                </a>
            </div>

            @if(session('success') && (!session('success_tab') || session('success_tab') == 'banks'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card vyapar-card">
                <div class="card-header">
                    <input type="text" id="banksSearch" class="form-control" placeholder="Search banks...">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="banksTable" class="table table-hover vyapar-table mb-0" style="min-width: 600px;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Short Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($banks as $index => $bank)
                                    <tr>
                                        <td>{{ $index + 1 + (($banks->currentPage() - 1) * $banks->perPage()) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="flex-shrink-0">
                                                    @if(!empty($bank->logo))
                                                        <img src="{{ asset('assets/img/banks/' . $bank->logo) }}" alt="{{ $bank->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: contain; background: #f8fafc;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center rounded bg-light" style="width: 40px; height: 40px;">
                                                            <i class="ti ti-building-bank text-muted" style="font-size: 1.25rem;"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <strong>{{ $bank->name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $bank->short_name ?? '—' }}</td>
                                        <td>
                                            @if($bank->status)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <form action="{{ route('admin.banks.toggle-status', $bank->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="ti ti-{{ $bank->status ? 'toggle-left' : 'toggle-right' }} me-2"></i>{{ $bank->status ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('admin.banks.toggle-api', $bank->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="ti ti-api me-2"></i>API {{ $bank->api_enabled ? 'Disable' : 'Enable' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.banks.edit', $bank->id) }}"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('admin.banks.destroy', $bank->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this bank?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"><i class="ti ti-trash me-2"></i>Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-state">
                                            <p class="mb-1">No banks found.</p>
                                            <a href="{{ route('admin.banks.create') }}">Add your first bank</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($banks->hasPages())
                    <div class="card-footer p-3 border-top">
                        {{ $banks->appends(['tab' => request('tab')])->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Bank Accounts Tab --}}
        <div class="tab-pane fade {{ request('tab') == 'accounts' ? 'show active' : '' }}" id="accounts-pane" role="tabpanel">
            <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <h2 class="mb-0"><i class="ti ti-wallet"></i> Bank Accounts</h2>
                <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Bank Account
                </a>
            </div>

            @if(session('success') && session('success_tab') == 'accounts')
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
                    <form method="GET" action="{{ route('admin.banks.index') }}" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="tab" value="accounts">
                        <select name="account_type" class="form-select form-select-sm" style="max-width: 180px;" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="bank" {{ request('account_type') == 'bank' ? 'selected' : '' }}>Bank Accounts</option>
                            <option value="cash" {{ request('account_type') == 'cash' ? 'selected' : '' }}>Cash Accounts</option>
                        </select>
                        @if(request('account_type'))
                            <a href="{{ route('admin.banks.index', ['tab' => 'accounts']) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </form>
                    <input type="text" id="accountsSearch" class="form-control" placeholder="Search bank accounts..." style="max-width: 280px;">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="accountsTable" class="table table-hover vyapar-table mb-0" style="min-width: 1000px;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account</th>
                                    <th>Current Balance</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bankAccounts as $index => $account)
                                    <tr>
                                        <td>{{ $index + 1 + (($bankAccounts->currentPage() - 1) * $bankAccounts->perPage()) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                @php $bank = $account->bank; @endphp
                                                <div class="flex-shrink-0">
                                                    @if($bank && !empty($bank->logo))
                                                        <img src="{{ asset('assets/img/banks/' . $bank->logo) }}" alt="{{ $bank->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: contain; background: #f8fafc;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center rounded bg-light" style="width: 40px; height: 40px;">
                                                            <i class="ti ti-building-bank text-muted" style="font-size: 1.25rem;"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $bank->name ?? '—' }}</div>
                                                    <div>{{ $account->account_title }}</div>
                                                    <div class="text-muted small">{{ $account->account_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong class="text-{{ $account->current_balance >= 0 ? 'success' : 'danger' }}">
                                                {{ number_format($account->current_balance ?? 0, 0) }}
                                            </strong>
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
                                                <a href="{{ route('admin.bank-transactions.index', ['bank_account_id' => $account->id]) }}" class="btn btn-sm btn-info" title="View Transactions">
                                                    <i class="ti ti-receipt"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('admin.bank-accounts.show', $account->id) }}"><i class="ti ti-eye me-2"></i>View</a></li>
                                                    <li>
                                                        <form action="{{ route('admin.bank-accounts.toggle-status', $account->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="ti ti-{{ $account->status ? 'toggle-left' : 'toggle-right' }} me-2"></i>{{ $account->status ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.bank-accounts.edit', $account->id) }}"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('admin.bank-accounts.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this bank account?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"><i class="ti ti-trash me-2"></i>Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-state">
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
                        {{ $bankAccounts->appends(['tab' => 'accounts', 'account_type' => request('account_type')])->links('pagination::default') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('banksSearch')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#banksTable tbody tr');
    rows.forEach(function(row) {
        if (row.querySelector('.empty-state')) return;
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
document.getElementById('accountsSearch')?.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#accountsTable tbody tr');
    rows.forEach(function(row) {
        if (row.querySelector('.empty-state')) return;
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
