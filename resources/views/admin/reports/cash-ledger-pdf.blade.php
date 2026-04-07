<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Ledger Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 8px; }
        .muted { color: #666; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: bold; }
        .num { text-align: right; white-space: nowrap; }
        .summary { margin-bottom: 12px; }
        .summary td { border: 1px solid #ddd; padding: 6px; }
    </style>
</head>
<body>
    <h1>Cash Ledger Report</h1>
    <p class="muted">Period: {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }} &nbsp;|&nbsp; Generated {{ $generatedAt->format('d M Y H:i') }}</p>

    <table class="summary" style="width: auto;">
        <tr>
            <td><strong>Opening</strong></td><td class="num">Rs {{ number_format($summary['opening_balance'], 2) }}</td>
            <td><strong>Cash in</strong></td><td class="num">Rs {{ number_format($summary['total_cash_in'], 2) }}</td>
            <td><strong>Cash out</strong></td><td class="num">Rs {{ number_format($summary['total_cash_out'], 2) }}</td>
            <td><strong>Net</strong></td><td class="num">Rs {{ number_format($summary['net_cash_flow'], 2) }}</td>
            <td><strong>Closing</strong></td><td class="num">Rs {{ number_format($summary['closing_balance'], 2) }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Ref</th>
                <th>Type</th>
                <th>Description</th>
                <th>Party</th>
                <th>By</th>
                <th class="num">Debit</th>
                <th class="num">Credit</th>
                <th class="num">Running</th>
                <th>Branch</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['time'] }}</td>
                    <td>{{ $row['voucher_ref'] }}</td>
                    <td>{{ $row['transaction_type_label'] }}</td>
                    <td>{{ $row['description'] }}</td>
                    <td>{{ $row['party'] }}</td>
                    <td>{{ $row['created_by'] }}</td>
                    <td class="num">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                    <td class="num">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                    <td class="num">{{ number_format($row['running_balance'] ?? 0, 2) }}</td>
                    <td>{{ $row['branch'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
