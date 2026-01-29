@extends('layouts.app')

@section('title', 'Worker Bank Accounts')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Worker Bank Accounts</h4>
            <h6>Workers linked to bank accounts (from Bank module) – separate history per account</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('car.wash.staff') }}" class="btn btn-outline-primary">
                <i class="ti ti-users me-1"></i> Staff
            </a>
            <a href="{{ route('car.wash.worker-cash-accounts') }}" class="btn btn-outline-success">
                <i class="ti ti-wallet me-1"></i> Worker Cash Accounts
            </a>
            <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-credit-card me-1"></i> Bank Accounts
            </a>
            <a href="{{ route('car-wash.payments.index') }}" class="btn btn-outline-info">
                <i class="ti ti-wallet me-1"></i> Car Wash Payments
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Workers whose bank account is set from <strong>Bank → Bank Accounts</strong> have a separate transaction history here. When you pay commission by bank to such a worker, the amount is credited to their linked account and appears below.
                    </p>
                    @if($workers->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            No workers have a linked bank account yet. Link a worker to a bank account from <a href="{{ route('car.wash.staff') }}">Staff</a> (Pay Commission → Bank → add/link worker bank) or add the account from <a href="{{ route('admin.bank-accounts.index') }}">Bank Accounts</a> and assign it to the worker.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Worker</th>
                                        <th>Bank Account (from Bank)</th>
                                        <th>Balance</th>
                                        <th>Last transactions</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workers as $item)
                                        @php $worker = $item['worker']; $account = $item['account']; $balance = $item['balance']; $transactions = $item['transactions']; @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $worker->name }}</strong>
                                                @if($worker->mobile)
                                                    <br><small class="text-muted">{{ $worker->mobile }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($account)
                                                    <span class="text-primary">{{ $account->bank->name ?? 'Bank' }}</span>
                                                    <br>
                                                    <small>{{ $account->account_title ?? '' }} – {{ $account->account_number ?? '' }}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-success">Rs {{ number_format($balance, 2) }}</strong>
                                            </td>
                                            <td>
                                                @if($transactions->isEmpty())
                                                    <small class="text-muted">No transactions yet</small>
                                                @else
                                                    <small>
                                                        @foreach($transactions->take(5) as $tx)
                                                            <span class="d-block">
                                                                {{ $tx->transaction_date->format('d M Y') }}
                                                                <span class="badge {{ $tx->type === 'credit' ? 'bg-success' : 'bg-danger' }}">{{ $tx->type }}</span>
                                                                Rs {{ number_format($tx->amount, 0) }}
                                                                – {{ Str::limit($tx->description, 30) }}
                                                            </span>
                                                        @endforeach
                                                        @if($transactions->count() > 5)
                                                            <span class="text-muted">+ {{ $transactions->count() - 5 }} more</span>
                                                        @endif
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($account)
                                                    <a href="{{ route('admin.bank-transactions.index', ['bank_account_id' => $account->id]) }}" class="btn btn-sm btn-outline-primary" title="Full history">
                                                        <i class="ti ti-history me-1"></i> Full history
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
