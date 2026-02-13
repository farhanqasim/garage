<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Account – {{ $worker->name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.min.css') }}">
    <style>
        body { font-size: 14px; padding: 20px; }
        .print-only { display: block; }
        .no-print { margin-bottom: 15px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .table { font-size: 12px; }
        }
        .summary-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .summary-box strong { margin-right: 8px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn btn-primary" onclick="window.print();">
            <i class="ti ti-printer"></i> Print
        </button>
        <a href="{{ route('car.wash.worker-cash-accounts') }}" class="btn btn-outline-secondary ms-2">Back to Cash Accounts</a>
    </div>

    <div class="container-fluid">
        <h4 class="mb-1">Worker Cash Account – Transaction History</h4>
        <p class="text-muted mb-4">{{ $worker->name }}@if($worker->mobile) &nbsp;·&nbsp; {{ $worker->mobile }}@endif</p>

        @if($cashAccount)
            <div class="summary-box row mb-4">
                <div class="col-md-4"><strong>Total Earned:</strong> Rs {{ number_format($totalEarned, 2) }}</div>
                <div class="col-md-4"><strong>Total Paid:</strong> Rs {{ number_format($totalPaid, 2) }}</div>
                <div class="col-md-4"><strong>Balance / Pending:</strong> Rs {{ number_format($balance, 2) }}</div>
            </div>

            <h5 class="mb-3">All Transactions</h5>
            @if($transactions->isEmpty())
                <p class="text-muted">No transactions yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Amount (Rs)</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $index => $tx)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $tx->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $tx->type === 'credit' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($tx->type) }}</span>
                                    </td>
                                    <td>{{ number_format($tx->amount, 2) }}</td>
                                    <td>{{ $tx->note ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-muted mt-2 small">Printed on {{ now()->format('d M Y H:i') }}</p>
            @endif
        @else
            <p class="text-muted">Cash account not created for this worker.</p>
        @endif
    </div>

    <script>
        // Optional: auto-print when opened in new tab (user can cancel)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
