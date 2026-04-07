<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Ledger Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-section table th,
        .info-section table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .info-section table th {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 40%;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .transactions-table th,
        .transactions-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .transactions-table th {
            background-color: #333;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .transactions-table td {
            text-align: center;
        }
        .transactions-table .text-end {
            text-align: right;
        }
        .opening-balance-row {
            background-color: #e3f2fd;
            font-weight: bold;
        }
        .footer-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-success {
            color: #28a745;
        }
        .text-primary {
            color: #007bff;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Supplier Ledger Report</h1>
        @if(!empty($date_from) || !empty($date_to))
        <p style="margin: 5px 0 0 0; font-size: 14px; color: #555;">Period: {{ $date_from ?? '—' }} to {{ $date_to ?? '—' }}</p>
        @endif
    </div>
    
    <div class="info-section">
        <table>
            <tr>
                <th>Supplier Name:</th>
                <td>{{ $supplier['name'] }}</td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>{{ $supplier['email'] }}</td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td>{{ $supplier['phone'] }}</td>
            </tr>
        </table>
        
        <table>
            <tr>
                <th>Opening Balance:</th>
                <td><strong>{{ number_format($opening_balance, 0) }}</strong></td>
            </tr>
            <tr>
                <th>Total Debit:</th>
                <td class="text-danger">{{ number_format($total_debit, 0) }}</td>
            </tr>
            <tr>
                <th>Total Credit:</th>
                <td class="text-success">{{ number_format($total_credit, 0) }}</td>
            </tr>
            <tr>
                <th>Ending Balance:</th>
                <td class="text-primary"><strong>{{ number_format($ending_balance, 0) }}</strong></td>
            </tr>
            <tr>
                <th>Balance Type:</th>
                <td>{{ $balance_type == 'pay' ? 'To Pay (We Owe Supplier)' : 'To Receive (Supplier Owes)' }}</td>
            </tr>
        </table>
    </div>
    
    <h3 style="margin-top: 20px; margin-bottom: 10px;">Transaction Details</h3>
    <table class="transactions-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Type</th>
                <th>Reference/Bill</th>
                <th>History Purchase Bill</th>
                <th>Branch</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            <!-- Opening Balance Row -->
            <tr class="opening-balance-row">
                <td colspan="6"><strong>Opening Balance</strong></td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end"><strong>{{ number_format($opening_balance, 0) }}</strong></td>
            </tr>
            
            @foreach($transactions as $trans)
            <tr>
                <td>{{ $trans['date'] }}</td>
                <td>{{ $trans['time'] }}</td>
                <td>{{ $trans['type'] }}</td>
                <td>{{ $trans['reference'] }}</td>
                <td>{{ $trans['description'] }}</td>
                <td>{{ $trans['branch'] }}</td>
                <td class="text-end">{{ number_format($trans['debit'], 0) }}</td>
                <td class="text-end">{{ number_format($trans['credit'], 0) }}</td>
                <td class="text-end"><strong>{{ number_format($trans['balance'], 0) }}</strong></td>
            </tr>
            @endforeach
            
            <!-- Footer Row -->
            <tr class="footer-row">
                <td colspan="6" class="text-end"><strong>Totals:</strong></td>
                <td class="text-end"><strong>{{ number_format($total_debit, 0) }}</strong></td>
                <td class="text-end"><strong>{{ number_format($total_credit, 0) }}</strong></td>
                <td class="text-end"><strong>{{ number_format($ending_balance, 0) }}</strong></td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Generated on: {{ $generated_at }}</p>
        <p>This is a computer-generated report.</p>
    </div>
</body>
</html>
