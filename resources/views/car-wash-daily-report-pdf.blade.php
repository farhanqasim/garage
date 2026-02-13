<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Report - {{ $dateRaw }} - Elite Car Wash</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 16px; }
        h1 { font-size: 18px; margin: 0 0 4px 0; color: #1e40af; }
        .meta { font-size: 10px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #1e40af; color: #fff; font-size: 10px; }
        td { font-size: 10px; }
        .text-right { text-align: right; }
        .totals { margin-top: 16px; border: 2px solid #1e40af; padding: 12px; background: #f8fafc; }
        .totals table { width: 100%; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .label { font-weight: bold; }
        .cash-row { font-size: 12px; font-weight: bold; color: #1e40af; }
        .no-data { text-align: center; padding: 40px; color: #64748b; }
        .opening-row { background: #f1f5f9; font-weight: bold; }
        .expense-detail { font-size: 9px; background: #fffbf5; color: #92400e; padding: 4px 8px; vertical-align: top; }
        tfoot .footer-row { background: #1e40af; color: #fff; font-weight: bold; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Elite Car Wash – Daily Jobs Report</h1>
    <div class="meta">
        <strong>Date:</strong> {{ $date }} &nbsp;|&nbsp; <strong>Branch:</strong> {{ $branchName }}
    </div>

    @if(count($rows) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Vehicle</th>
                    <th class="text-right">Refreshment Expenses</th>
                    <th class="text-right">Credit</th>
                    <th class="text-right">Total</th>
                    <th>Worker</th>
                    <th>Bank</th>
                    <th class="text-right">Commission</th>
                    <th class="text-right">G.total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                {{-- Image jaisa: Refreshment Expenses (Debit), Credit, Total sab ek hi row mein --}}
                <tr class="{{ ($r['vehicle'] ?? '') === 'Opening' ? 'opening-row' : '' }}">
                    <td>{{ $r['dateTime'] ?? '-' }}</td>
                    <td>{{ $r['vehicle'] ?? '-' }}</td>
                    <td class="text-right">{{ ((float)($r['debit'] ?? 0)) > 0 ? 'Rs. ' . number_format((float)$r['debit'], 0) : '-' }}</td>
                    <td class="text-right">{{ ((float)($r['credit'] ?? 0)) > 0 ? 'Rs. ' . number_format((float)$r['credit'], 0) : '-' }}</td>
                    <td class="text-right">Rs. {{ number_format((float)($r['total'] ?? 0), 0) }}</td>
                    <td>{{ $r['worker'] ?? '-' }}</td>
                    <td>{{ $r['bankName'] ?? '-' }}</td>
                    <td class="text-right">{{ (isset($r['commission']) && $r['commission'] !== '-' && $r['commission'] !== null && (float)($r['commission'] ?? 0) > 0) ? 'Rs. ' . number_format((float)$r['commission'], 0) : '-' }}</td>
                    <td class="text-right">{{ (isset($r['gTotal']) && $r['gTotal'] !== null && $r['gTotal'] !== '') ? 'Rs. ' . number_format((float)$r['gTotal'], 0) : '-' }}</td>
                </tr>
                @if(($r['vehicle'] ?? '') !== 'Opening' && !empty($r['expenseItems'] ?? []))
                <tr>
                    <td colspan="9" class="expense-detail">
                        <strong>Expenses:</strong><br>
                        @foreach($r['expenseItems'] as $ei)
                            @if(is_array($ei) && isset($ei['name']))
                                • {{ $ei['name'] ?? 'N/A' }}: {{ (int)($ei['quantity'] ?? 0) }} × Rs.{{ number_format((float)($ei['price'] ?? 0), 0) }} = Rs.{{ number_format((float)($ei['total'] ?? (($ei['quantity'] ?? 0) * ($ei['price'] ?? 0))), 0) }}@if(!$loop->last)<br>@endif
                            @endif
                        @endforeach
                        <br><strong>Total: Rs. {{ number_format((float)($r['debit'] ?? 0), 0) }}</strong>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr class="footer-row">
                    <td>Total</td>
                    <td>-</td>
                    <td class="text-right">Rs. {{ number_format($totalDebit ?? 0, 0) }}</td>
                    <td class="text-right">Rs. {{ number_format($totalCredit ?? 0, 0) }}</td>
                    <td class="text-right">Rs. {{ number_format($cashOnHand ?? 0, 0) }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td class="text-right">Rs. {{ number_format($totalCommission ?? 0, 0) }}</td>
                    <td class="text-right">Rs. {{ number_format($sumGtotal ?? 0, 0) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td class="label">Total Vehicles</td>
                    <td class="text-right">{{ $totalVehicles ?? 0 }}</td>
                    <td class="label">Total Refreshment Expenses</td>
                    <td class="text-right">Rs. {{ number_format($totalDebit ?? 0, 0) }}</td>
                    <td class="label">Total Credit</td>
                    <td class="text-right">Rs. {{ number_format($totalCredit ?? 0, 0) }}</td>
                </tr>
                <tr>
                    <td class="label">{{ (isset($paymentFilter) && $paymentFilter === 'bank') ? 'Bank Total' : 'Cash on Hand' }}</td>
                    <td class="text-right cash-row">Rs. {{ number_format($cashOnHand ?? 0, 0) }}</td>
                    <td class="label">Total Workers</td>
                    <td class="text-right">{{ $totalWorkers ?? 0 }}</td>
                    <td class="label">Total Commission</td>
                    <td class="text-right">Rs. {{ number_format($totalCommission ?? 0, 0) }}</td>
                </tr>
            </table>
        </div>
    @else
        <div class="no-data">No completed jobs for this date.</div>
    @endif
</body>
</html>
