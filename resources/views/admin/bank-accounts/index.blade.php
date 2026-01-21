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
                                <th>Bank</th>
                                <th>Account Title</th>
                                <th>Account Number</th>
                                <th>IBAN</th>
                                <th>Branch Code</th>
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
                                    <td>{{ $account->account_title }}</td>
                                    <td>{{ $account->account_number }}</td>
                                    <td>{{ $account->iban ?? 'N/A' }}</td>
                                    <td>{{ $account->branch_code ?? 'N/A' }}</td>
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
                                    <td colspan="9" class="text-center py-4">
                                        <p class="text-muted">No bank accounts found. <a href="{{ route('admin.bank-accounts.create') }}">Create one</a></p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($bankAccounts->hasPages())
                <div class="card-footer">
                    {{ $bankAccounts->links() }}
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
