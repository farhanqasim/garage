<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Ledger Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; color: #333; }
        .info-section { margin-bottom: 20px; }
        .info-section table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-section table th, .info-section table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .info-section table th { background-color: #f5f5f5; font-weight: bold; width: 40%; }
        .transactions-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .transactions-table th, .transactions-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .transactions-table th { background-color: #333; color: white; font-weight: bold; text-align: center; }
        .transactions-table td { text-align: center; }
        .transactions-table .text-end { text-align: right; }
        .opening-balance-row { background-color: #e3f2fd; font-weight: bold; }
        .footer-row { background-color: #f5f5f5; font-weight: bold; }
        .text-danger { color: #dc3545; }
        .text-success { color: #28a745; }
        .ending-balance-cell { color: #0d6efd; font-weight: 700; font-size: 1.1rem; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        .period-row { margin-top: 8px; font-size: 14px; color: #555; }
        .date-range-form { margin-bottom: 20px; padding: 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
        .date-range-form label { margin: 0 4px 0 0; font-weight: 600; }
        .date-range-form input[type="date"] { padding: 6px 8px; border: 1px solid #ced4da; border-radius: 4px; }
        .date-range-form button { padding: 6px 14px; background: #0d6efd; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; }
        .date-range-form button:hover { background: #0b5ed7; }
        .date-range-form .btn-link { padding: 6px 12px; background: #e9ecef; color: #495057; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 12px; font-weight: 500; }
        .date-range-form .btn-link:hover { background: #dee2e6; color: #212529; }
        .date-range-form .btn-clear { background: #6c757d; color: #fff; }
        .date-range-form .btn-clear:hover { background: #5a6268; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Customer Ledger Report</h1>
        <p class="period-row">
            @if($show_all ?? false)
                All dates
            @else
                Period: {{ $date_from_display }} to {{ $date_to_display }}
            @endif
        </p>
    </div>

    <form method="get" action="{{ request()->url() }}" id="dateRangeForm" class="date-range-form">
        <label for="date_from">From:</label>
        <input type="date" id="date_from" name="date_from" value="{{ $date_from ?? '' }}">
        <label for="date_to">To:</label>
        <input type="date" id="date_to" name="date_to" value="{{ $date_to ?? '' }}">
        @php
            $baseUrl = request()->url();
            $lastWeekFrom = now()->subDays(6)->format('Y-m-d');
            $lastWeekTo = now()->format('Y-m-d');
            $lastMonthStart = now()->subMonth()->startOfMonth()->format('Y-m-d');
            $lastMonthEnd = now()->subMonth()->endOfMonth()->format('Y-m-d');
        @endphp
        <a href="{{ $baseUrl }}?date_from={{ $lastWeekFrom }}&date_to={{ $lastWeekTo }}" class="btn-link">Last Week</a>
        <a href="{{ $baseUrl }}?date_from={{ $lastMonthStart }}&date_to={{ $lastMonthEnd }}" class="btn-link">Last Month</a>
        <a href="{{ $baseUrl }}?all=1" class="btn-link btn-clear">Clear</a>
    </form>

    <script>
        document.getElementById('dateRangeForm').addEventListener('change', function(e) {
            if (e.target.matches('input[name="date_from"], input[name="date_to"]')) {
                var from = document.getElementById('date_from').value;
                var to = document.getElementById('date_to').value;
                if (from && to) {
                    window.location.href = '{{ request()->url() }}?date_from=' + encodeURIComponent(from) + '&date_to=' + encodeURIComponent(to);
                }
            }
        });
    </script>

    <div class="info-section">
        <table>
            <tr><th>Customer Name:</th><td>{{ $customer['name'] }}</td></tr>
            <tr><th>Email:</th><td>{{ $customer['email'] }}</td></tr>
            <tr><th>Phone:</th><td>{{ $customer['phone'] }}</td></tr>
        </table>
        <table>
            <tr><th>Opening Balance:</th><td><strong>{{ number_format($opening_balance, 0, '.', '') }}</strong></td></tr>
            <tr><th>Total Debit:</th><td class="text-danger">{{ number_format($total_debit, 0, '.', '') }}</td></tr>
            <tr><th>Total Credit:</th><td class="text-success">{{ number_format($total_credit, 0, '.', '') }}</td></tr>
            <tr><th>Ending Balance:</th><td class="ending-balance-cell">{{ number_format($ending_balance, 0, '.', '') }}</td></tr>
            <tr><th>Balance Type:</th><td>{{ $balance_type === 'receive' ? 'To Receive (Customer Owes)' : 'To Pay (We Owe Customer)' }}</td></tr>
        </table>
    </div>

    <h3 style="margin-top: 20px; margin-bottom: 10px;">Transaction Details</h3>
    <table class="transactions-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Type</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Branch</th>
                <th>User</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr class="opening-balance-row">
                <td colspan="7"><strong>Opening Balance</strong></td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end"><strong>{{ number_format($opening_balance, 0, '.', '') }}</strong></td>
            </tr>
            @foreach($transactions as $trans)
            <tr>
                <td>{{ $trans['date'] }}</td>
                <td>{{ $trans['time'] }}</td>
                <td>{{ $trans['type'] }}</td>
                <td>{{ $trans['reference'] }}</td>
                <td>{{ $trans['description'] }}</td>
                <td>{{ $trans['branch'] }}</td>
                <td>{{ $trans['user'] }}</td>
                <td class="text-end">{{ number_format($trans['debit'], 0, '.', '') }}</td>
                <td class="text-end">{{ number_format($trans['credit'], 0, '.', '') }}</td>
                <td class="text-end"><strong>{{ number_format($trans['balance'], 0, '.', '') }}</strong></td>
            </tr>
            @endforeach
            <tr class="footer-row">
                <td colspan="7" class="text-end"><strong>Totals:</strong></td>
                <td class="text-end"><strong>{{ number_format($total_debit, 0, '.', '') }}</strong></td>
                <td class="text-end"><strong>{{ number_format($total_credit, 0, '.', '') }}</strong></td>
                <td class="text-end ending-balance-cell"><strong>{{ number_format($ending_balance, 0, '.', '') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: {{ $generated_at }}</p>
    </div>
</body>
</html>
