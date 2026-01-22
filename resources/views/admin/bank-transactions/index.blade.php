@extends('layouts.app')
@section('title', __('Bank Transactions List'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">Bank Transactions</h2>
                </div>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.bank-transactions.create') }}" class="btn btn-primary">
                    <i class="ti ti-circle-plus me-1"></i>Add Transaction
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="d-flex gap-2 flex-wrap">
                    <!-- Filter by Bank Account -->
                    <form method="GET" action="{{ route('admin.bank-transactions.index') }}" class="d-inline">
                        <input type="hidden" name="reconciled" value="{{ request('reconciled') }}">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <select name="bank_account_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Bank Accounts</option>
                            @foreach($bankAccounts as $account)
                                <option value="{{ $account->id }}" {{ request('bank_account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->account_title }} ({{ $account->bank->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <!-- Filter by Type -->
                    <form method="GET" action="{{ route('admin.bank-transactions.index') }}" class="d-inline">
                        <input type="hidden" name="bank_account_id" value="{{ request('bank_account_id') }}">
                        <input type="hidden" name="reconciled" value="{{ request('reconciled') }}">
                        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit</option>
                            <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit</option>
                        </select>
                    </form>

                    <!-- Filter by Reconciled Status -->
                    <form method="GET" action="{{ route('admin.bank-transactions.index') }}" class="d-inline">
                        <input type="hidden" name="bank_account_id" value="{{ request('bank_account_id') }}">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <select name="reconciled" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="1" {{ request('reconciled') == '1' ? 'selected' : '' }}>Reconciled</option>
                            <option value="0" {{ request('reconciled') == '0' ? 'selected' : '' }}>Unreconciled</option>
                        </select>
                    </form>

                    @if(request('bank_account_id') || request('type') || request('reconciled'))
                        <a href="{{ route('admin.bank-transactions.index') }}" class="btn btn-sm btn-secondary">Clear Filters</a>
                    @endif
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <input type="text" id="tableSearch" class="form-control w-100" placeholder="Search...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table id="searchableTable" class="table table-hover table-center" style="min-width: 1200px;">
                        <thead class="thead-primary">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Bank Account</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Statement Reference</th>
                                <th>Matched Payment</th>
                                <th>Reconciled</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $index => $transaction)
                                <tr>
                                    <td>{{ $index + 1 + (($transactions->currentPage() - 1) * $transactions->perPage()) }}</td>
                                    <td>
                                        <strong>{{ $transaction->transaction_date->format('Y-m-d') }}</strong>
                                        <br><small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $transaction->bankAccount->account_title }}</strong>
                                        <br><small class="text-muted">{{ $transaction->bankAccount->bank->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $transaction->description ?? 'N/A' }}</td>
                                    <td>
                                        @if($transaction->type == 'credit')
                                            <span class="badge badge-success">Credit</span>
                                        @else
                                            <span class="badge badge-danger">Debit</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ number_format($transaction->amount, 2) }} PKR</strong>
                                    </td>
                                    <td>
                                        @if($transaction->statement_reference)
                                            <code>{{ $transaction->statement_reference }}</code>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->matchedPayment)
                                            <a href="{{ route('admin.payments.show', $transaction->matchedPayment->id) }}" class="badge badge-info">
                                                Payment #{{ $transaction->matchedPayment->id }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->reconciled)
                                            <span class="badge badge-success">Yes</span>
                                            @if($transaction->reconciled_at)
                                                <br><small class="text-muted">{{ $transaction->reconciled_at->format('Y-m-d') }}</small>
                                            @endif
                                        @else
                                            <span class="badge badge-warning">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.bank-transactions.show', $transaction->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.bank-transactions.edit', $transaction->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            @if(!$transaction->reconciled)
                                                <form action="{{ route('admin.bank-transactions.reconcile', $transaction->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Mark as Reconciled" onclick="return confirm('Mark this transaction as reconciled?');">
                                                        <i class="ti ti-check"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.bank-transactions.unreconcile', $transaction->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Mark as Unreconciled" onclick="return confirm('Mark this transaction as unreconciled?');">
                                                        <i class="ti ti-x"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.bank-transactions.destroy', $transaction->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
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
                                    <td colspan="10" class="text-center py-4">
                                        <p class="text-muted">No bank transactions found. <a href="{{ route('admin.bank-transactions.create') }}">Create one</a></p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($transactions->hasPages())
                <div class="card-footer p-3">
                    <div class="d-flex justify-content-center w-100" style="overflow-x: auto;">
                        {{ $transactions->links('pagination::default') }}
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
