<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $purchase->invoice_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 15mm 20mm;
            background: #fff;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .company-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-img {
            max-width: 90px;
            max-height: 90px;
            object-fit: contain;
        }
        
        .company-info h1 {
            font-size: 28px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 8px;
        }
        
        .company-info p {
            font-size: 11px;
            color: #555;
            margin: 3px 0;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-info h2 {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        
        .invoice-info p {
            font-size: 12px;
            margin: 4px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #28a745;
            color: #fff;
        }
        
        .status-pending { background-color: #ffc107; color: #000; }
        .status-ordered { background-color: #17a2b8; color: #fff; }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .detail-section {
            flex: 1;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .detail-section h3 {
            font-size: 12px;
            font-weight: bold;
            color: #495057;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        
        .detail-section p {
            font-size: 11px;
            margin: 6px 0;
        }
        
        .detail-section strong {
            display: inline-block;
            width: 110px;
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            page-break-inside: auto;
        }
        
        .items-table th {
            background-color: #0d6efd;
            color: #fff;
            border: 1px solid #0d6efd;
            padding: 10px 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
        }
        
        .items-table td {
            border: 1px solid #dee2e6;
            padding: 10px 8px;
            font-size: 11px;
            vertical-align: top;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        
        .summary-section {
            page-break-inside: avoid;
        }
        
        .summary-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .summary-table td {
            padding: 10px 20px;
            font-size: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .summary-table td:first-child {
            font-weight: bold;
            text-align: left;
        }
        
        .summary-table td:last-child {
            text-align: right;
            font-weight: bold;
        }
        
        .summary-table .grand-total td {
            background-color: #0d6efd;
            color: #fff;
            font-size: 15px;
            font-weight: bold;
            padding: 15px 20px;
        }
        
        .description-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .description-section h3 {
            font-size: 12px;
            font-weight: bold;
            color: #495057;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        
        .description-section p {
            font-size: 11px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .invoice-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #0d6efd;
            text-align: center;
            font-size: 11px;
            color: #555;
            page-break-inside: avoid;
        }
        
        @page {
            margin: 10mm;
            size: A4 portrait;
        }
        
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-section">
                <img src="{{ setting_value('logo', asset('assets/img/logo.svg')) }}" alt="Logo" class="logo-img">
                <div class="company-info">
                    <h1>{{ setting_value('logo_text', 'Auto Shop') }}</h1>
                    <p><strong>Helpline:</strong> {{ $helpline }}</p>
                    @if($purchase->branch)
                        <p><strong>Branch:</strong> {{ $purchase->branch->branch_name }} 
                           @if($purchase->branch->branch_code) ({{ $purchase->branch->branch_code }}) @endif
                        </p>
                    @endif
                </div>
            </div>
            <div class="invoice-info">
                <h2>INVOICE #{{ $purchase->invoice_no }}</h2>
                <p><strong>Date:</strong> {{ $purchase->purchase_date->format('d/m/Y') }}</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-{{ $purchase->status }}">
                        {{ strtoupper($purchase->status) }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Details -->
        <div class="invoice-details">
            <div class="detail-section">
                <h3>Supplier Information</h3>
                <p><strong>Name:</strong> {{ $purchase->supplier->names[0] ?? 'N/A' }}</p>
                @if($purchase->supplier && $purchase->supplier->company)
                    <p><strong>Company:</strong> {{ $purchase->supplier->company }}</p>
                @endif
                @if($purchase->supplier && isset($purchase->supplier->phones[0]))
                    <p><strong>Phone:</strong> {{ $purchase->supplier->phones[0] }}</p>
                @endif
                @if($purchase->supplier && $purchase->supplier->address)
                    <p><strong>Address:</strong> {{ $purchase->supplier->address }}</p>
                @endif
                @if($purchase->supplier && $purchase->supplier->area)
                    <p><strong>Area:</strong> {{ $purchase->supplier->area }}</p>
                @endif
            </div>
            <div class="detail-section">
                <h3>Purchase Information</h3>
                <p><strong>Reference:</strong> {{ $purchase->reference ?? '-' }}</p>
                <p><strong>Purchase Date:</strong> {{ $purchase->purchase_date->format('d/m/Y') }}</p>
                <p><strong>Invoice #:</strong> {{ $purchase->invoice_no }}</p>
                @if($purchase->branch)
                    <p><strong>Branch:</strong> {{ $purchase->branch->branch_name }}
                       @if($purchase->branch->branch_code) ({{ $purchase->branch->branch_code }}) @endif
                    </p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th>Item Name</th>
                    <th style="width:8%;" class="text-center">Qty</th>
                    <th style="width:8%;" class="text-center">Unit</th>
                    <th style="width:12%;" class="text-right">Rate</th>
                    <th style="width:12%;" class="text-right">Discount</th>
                    <th style="width:8%;" class="text-center">Tax %</th>
                    <th style="width:12%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $index => $purchaseItem)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        @php
                            $item = $purchaseItem->item;
                            $itemName = $item->short_disc ?? $item->pro_dis ?? $item->bar_code ?? 'N/A';
                            if ($item->partnumber_item) {
                                $itemName = $item->partnumber_item->name ?? $itemName;
                            }
                            if ($item->category) {
                                $itemName .= ' - ' . $item->category->name;
                            }
                        @endphp
                        <strong>{{ $itemName }}</strong>
                        @if($item->bar_code)
                            <br><small>Barcode: {{ $item->bar_code }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($purchaseItem->quantity, 2) }}</td>
                    <td class="text-center">{{ $purchaseItem->unit ?? 'Unit' }}</td>
                    <td class="text-right">Rs {{ number_format($purchaseItem->rate, 2) }}</td>
                    <td class="text-right">Rs {{ number_format($purchaseItem->discount, 2) }}</td>
                    <td class="text-center">{{ number_format($purchaseItem->tax_percentage, 2) }}%</td>
                    <td class="text-right"><strong>Rs {{ number_format($purchaseItem->total_cost, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary-section">
            <table class="summary-table">
                <tr>
                    <td>Subtotal:</td>
                    <td>Rs {{ number_format($purchase->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Order Tax:</td>
                    <td>Rs {{ number_format($purchase->order_tax, 2) }}</td>
                </tr>
                <tr>
                    <td>Discount:</td>
                    <td>- Rs {{ number_format($purchase->discount, 2) }}</td>
                </tr>
                <tr>
                    <td>Shipping:</td>
                    <td>Rs {{ number_format($purchase->shipping, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td>GRAND TOTAL:</td>
                    <td>Rs {{ number_format($purchase->grand_total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Description -->
        @if($purchase->description)
        <div class="description-section">
            <h3>Description</h3>
            <p>{{ $purchase->description }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            <p><strong>Thank You for Your Business!</strong></p>
            <p>This is a computer generated invoice and does not require a signature.</p>
            <p>For any queries, please contact: {{ $helpline }}</p>
        </div>
    </div>
</body>
</html>