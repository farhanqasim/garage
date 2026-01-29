@extends('layouts.app')

@section('title', 'Worker Cash Accounts')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Worker Cash Accounts</h4>
            <h6>Workers with cash account – commission paid by cash is credited here (separate from bank)</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('car.wash.staff') }}" class="btn btn-outline-primary">
                <i class="ti ti-users me-1"></i> Staff
            </a>
            <a href="{{ route('car.wash.worker-bank-accounts') }}" class="btn btn-outline-info">
                <i class="ti ti-credit-card me-1"></i> Worker Bank Accounts
            </a>
            <a href="{{ route('car-wash.payments.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-wallet me-1"></i> Car Wash Payments
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Worker ka cash account alag add hota hai. Jab aap <strong>Pay Commission → Cash</strong> karte hain to amount worker ke cash account mein credit ho jata hai. Pehle worker ka cash account create karein (Staff page se).
                    </p>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Worker</th>
                                    <th>Cash Account</th>
                                    <th>Total Earned (Rs)</th>
                                    <th>Total Paid (Rs)</th>
                                    <th>Balance / Pending (Rs)</th>
                                    <th class="text-end">All Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workers as $item)
                                    @php $worker = $item['worker']; $cashAccount = $item['cash_account']; $balance = $item['balance']; $totalEarned = $item['total_earned'] ?? 0; $totalPaid = $item['total_paid'] ?? 0; $transactions = $item['transactions']; @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $worker->name }}</strong>
                                            @if($worker->mobile)
                                                <br><small class="text-muted">{{ $worker->mobile }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($cashAccount)
                                                <span class="badge bg-success">Cash account created</span>
                                            @else
                                                <span class="text-muted">— Not created</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($cashAccount)
                                                <strong>Rs {{ number_format($totalEarned, 2) }}</strong>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($cashAccount)
                                                <strong>Rs {{ number_format($totalPaid, 2) }}</strong>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($cashAccount)
                                                <strong class="text-success">Rs {{ number_format($balance, 2) }}</strong>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($cashAccount && $transactions->isNotEmpty())
                                                <a href="{{ route('car.wash.worker-cash-accounts.print', $worker) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="View all transactions">
                                                    <i class="ti ti-list me-1"></i> All Transactions
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
