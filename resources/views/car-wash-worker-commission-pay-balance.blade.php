@extends('layouts.app')

@section('title', 'Commission / Pay / Total Balance')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Commission / Pay / Total Balance</h4>
            <h6>Detailed transaction history with running balance for all workers</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('car.wash.staff') }}" class="btn btn-outline-primary">
                <i class="ti ti-users me-1"></i> Staff
            </a>
            <a href="{{ route('car-wash.worker-cash-accounts') }}" class="btn btn-outline-info">
                <i class="ti ti-wallet me-1"></i> Worker Cash Accounts
            </a>
            <a href="{{ route('car-wash.payments.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-credit-card me-1"></i> Payments
            </a>
        </div>
    </div>

    <div class="row">
        @foreach($workers as $worker)
            @php
                $cashAccount = $worker->workerCashAccount;
                $transactions = $cashAccount 
                    ? \App\Models\WorkerCashTransaction::where('worker_id', $worker->id)
                        ->orderBy('created_at', 'asc')
                        ->get()
                    : collect();
                $runningBalance = $cashAccount ? (float) $cashAccount->total_earned : 0;
            @endphp
            
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="ti ti-user me-2"></i>{{ $worker->name }}
                            @if($worker->mobile)
                                <small class="ms-2">({{ $worker->mobile }})</small>
                            @endif
                        </h5>
                        @if($cashAccount)
                            <div class="mt-2">
                                <span class="badge bg-light text-dark me-2">
                                    Total Earned: Rs {{ number_format($cashAccount->total_earned ?? 0, 2) }}
                                </span>
                                <span class="badge bg-light text-dark me-2">
                                    Total Paid: Rs {{ number_format($cashAccount->total_paid ?? 0, 2) }}
                                </span>
                                <span class="badge bg-{{ ($cashAccount->balance ?? 0) > 0 ? 'warning' : 'success' }} text-dark">
                                    Balance: Rs {{ number_format($cashAccount->balance ?? 0, 2) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($transactions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Type</th>
                                            <th>Commission (Credit)</th>
                                            <th>Pay (Debit)</th>
                                            <th>Note</th>
                                            <th>Total Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // Calculate running balance: start from 0, add credits, subtract debits
                                            $currentBalance = 0;
                                        @endphp
                                        @foreach($transactions as $tx)
                                            @php
                                                if($tx->type === 'credit') {
                                                    $currentBalance += (float) $tx->amount;
                                                } else {
                                                    $currentBalance -= (float) $tx->amount;
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $tx->created_at->format('d/m/Y h:i A') }}</td>
                                                <td>
                                                    @if($tx->type === 'credit')
                                                        <span class="badge bg-success">Commission</span>
                                                    @else
                                                        <span class="badge bg-danger">Pay</span>
                                                    @endif
                                                </td>
                                                <td class="text-success fw-bold">
                                                    @if($tx->type === 'credit')
                                                        Rs {{ number_format($tx->amount, 2) }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="text-danger fw-bold">
                                                    @if($tx->type === 'debit')
                                                        Rs {{ number_format($tx->amount, 2) }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>{{ $tx->note ?? '—' }}</td>
                                                <td class="fw-bold {{ $currentBalance >= 0 ? 'text-primary' : 'text-danger' }}">
                                                    Rs {{ number_format($currentBalance, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="5" class="text-end">Final Balance:</th>
                                            <th class="{{ ($cashAccount->balance ?? 0) >= 0 ? 'text-primary' : 'text-danger' }}">
                                                Rs {{ number_format($cashAccount->balance ?? 0, 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="ti ti-info-circle me-2"></i>No transactions found for this worker.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
        
        @if($workers->count() === 0)
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="ti ti-alert-triangle me-2"></i>No workers found.
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
