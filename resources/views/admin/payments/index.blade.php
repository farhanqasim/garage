@extends('layouts.app')
@section('title', __('Payments List'))
@section('content')
    <div class="content">
        <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="page-title">
                <h2 class="fw-bold">Payments</h2>
            </div>
            @canany(['view_cash_accounts', 'view_bank_transactions', 'view_bank_accounts'])
            <a href="{{ route('admin.reports.cash-ledger') }}" class="btn btn-success">
                <i class="ti ti-cash me-1"></i> Cash Ledger Report
            </a>
            @endcanany
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
                    <!-- Filter by Payment Method -->
                    <form method="GET" action="{{ route('admin.payments.index') }}" class="d-inline">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="direction" value="{{ request('direction') }}">
                        <input type="hidden" name="customer_id" value="{{ request('customer_id') }}">
                        <input type="hidden" name="supplier_id" value="{{ request('supplier_id') }}">
                        <select name="payment_method_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Payment Methods</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" {{ request('payment_method_id') == $method->id ? 'selected' : '' }}>
                                    {{ $method->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <!-- Filter by Direction -->
                    <form method="GET" action="{{ route('admin.payments.index') }}" class="d-inline">
                        <input type="hidden" name="payment_method_id" value="{{ request('payment_method_id') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="customer_id" value="{{ request('customer_id') }}">
                        <input type="hidden" name="supplier_id" value="{{ request('supplier_id') }}">
                        <select name="direction" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Directions</option>
                            <option value="in" {{ request('direction') == 'in' ? 'selected' : '' }}>Incoming (From Customers)</option>
                            <option value="out" {{ request('direction') == 'out' ? 'selected' : '' }}>Outgoing (To Suppliers)</option>
                        </select>
                    </form>

                    <!-- Filter by Status -->
                    <form method="GET" action="{{ route('admin.payments.index') }}" class="d-inline">
                        <input type="hidden" name="payment_method_id" value="{{ request('payment_method_id') }}">
                        <input type="hidden" name="direction" value="{{ request('direction') }}">
                        <input type="hidden" name="customer_id" value="{{ request('customer_id') }}">
                        <input type="hidden" name="supplier_id" value="{{ request('supplier_id') }}">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </form>

                    @if(request('payment_method_id') || request('status') || request('direction') || request('customer_id') || request('supplier_id'))
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-secondary">Clear Filters</a>
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
                                <th>Customer/Supplier</th>
                                <th>Payment Method</th>
                                <th>Bank Account</th>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Direction</th>
                                <th>Status</th>
                                <th>Paid At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $index => $payment)
                                <tr>
                                    <td>{{ $index + 1 + (($payments->currentPage() - 1) * $payments->perPage()) }}</td>
                                    <td>
                                        <strong>{{ $payment->payment_date->format('Y-m-d') }}</strong>
                                        <br><small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($payment->customer)
                                            <strong>Customer:</strong> {{ is_array($payment->customer->names) ? implode(', ', $payment->customer->names) : $payment->customer->names }}
                                        @elseif($payment->supplier)
                                            <strong>Supplier:</strong> {{ $payment->supplier->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $payment->paymentMethod->display_name ?? $payment->paymentMethod->name }}</span>
                                    </td>
                                    <td>
                                        @if($payment->bankAccount)
                                            {{ $payment->bankAccount->account_title }}
                                            <br><small class="text-muted">{{ $payment->bankAccount->bank->name ?? '' }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($payment->transaction_id)
                                            <code>{{ $payment->transaction_id }}</code>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</strong>
                                    </td>
                                    <td>
                                        @if($payment->direction == 'in')
                                            <span class="badge badge-success">IN</span>
                                        @else
                                            <span class="badge badge-danger">OUT</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'badge-warning',
                                                'paid' => 'badge-success',
                                                'failed' => 'badge-danger',
                                                'refunded' => 'badge-info'
                                            ];
                                            $statusColor = $statusColors[$payment->status] ?? 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $statusColor }}">{{ strtoupper($payment->status) }}</span>
                                    </td>
                                    <td>
                                        @if($payment->paid_at)
                                            {{ $payment->paid_at->format('Y-m-d H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            @if($payment->status === 'pending')
                                                <form action="{{ route('admin.payments.mark-paid', $payment->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Mark as Paid" onclick="return confirm('Mark this payment as paid?');">
                                                        <i class="ti ti-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.payments.mark-failed', $payment->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Mark as Failed" onclick="return confirm('Mark this payment as failed?');">
                                                        <i class="ti ti-x"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($payment->status === 'paid')
                                                <form action="{{ route('admin.payments.mark-refunded', $payment->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Mark as Refunded" onclick="return confirm('Mark this payment as refunded?');">
                                                        <i class="ti ti-refund"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
                                        <p class="text-muted">No payments found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($payments->hasPages())
                <div class="card-footer p-3">
                    <div class="d-flex justify-content-center w-100" style="overflow-x: auto;">
                        {{ $payments->links('pagination::default') }}
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
