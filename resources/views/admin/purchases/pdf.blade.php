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
            line-height: 1.6;
            color: #333;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        
        .invoice-header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            page-break-inside: avoid;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-img {
            max-width: 80px;
            max-height: 80px;
            border-radius: 5px;
        }
        
        .company-info h1 {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        
        .company-info p {
            font-size: 11px;
            color: #555;
            margin: 0;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-info h2 {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        
        .invoice-info p {
            font-size: 11px;
            color: #555;
            margin: 2px 0;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .detail-section {
            width: 48%;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        
        .detail-section h3 {
            font-size: 11px;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px solid #d0d0d0;
            padding-bottom: 5px;
        }
        
        .detail-section p {
            font-size: 11px;
            margin: 5px 0;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        .detail-section strong {
            display: inline-block;
            min-width: 100px;
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: auto;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
        }
        
        .items-table td {
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 11px;
            word-wrap: break-word;
            word-break: break-word;
            vertical-align: top;
        }
        
        .items-table tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .items-table .item-name {
            min-width: 150px;
            max-width: 200px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .summary-section {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        
        .summary-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .summary-table td {
            padding: 8px 15px;
            font-size: 11px;
            border: 1px solid #ddd;
        }
        
        .summary-table td:first-child {
            font-weight: bold;
            text-align: right;
            background-color: #f0f0f0;
        }
        
        .summary-table td:last-child {
            text-align: right;
            font-weight: bold;
        }
        
        .summary-table .grand-total td {
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            font-size: 14px;
            font-weight: bold;
            padding: 12px 15px;
            background-color: #e9ecef;
            color: #0d6efd;
        }
        
        .invoice-footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #0d6efd;
            text-align: center;
            page-break-inside: avoid;
        }
        
        .invoice-footer p {
            font-size: 10px;
            color: #555;
            margin: 5px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #28a745;
            color: #fff;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        
        .status-ordered {
            background-color: #17a2b8;
            color: #fff;
        }
        
        .description-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .description-section h3 {
            font-size: 11px;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px solid #d0d0d0;
            padding-bottom: 5px;
        }
        
        .description-section p {
            font-size: 11px;
            word-wrap: break-word;
            word-break: break-word;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        @page {
            margin: 15mm;
            size: A4 portrait;
        }
        
        @media print {
            .invoice-container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="logo-section">
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

        <!-- Invoice Details -->
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
                    <th style="width: 5%;">#</th>
                    <th class="item-name">Item Name</th>
                    <th style="width: 8%;" class="text-center">Qty</th>
                    <th style="width: 8%;" class="text-center">Unit</th>
                    <th style="width: 10%;" class="text-right">Rate</th>
                    <th style="width: 10%;" class="text-right">Discount</th>
                    <th style="width: 8%;" class="text-center">Tax %</th>
                    <th style="width: 12%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $index => $purchaseItem)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="item-name">
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

        <!-- Summary Section -->
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

        <!-- Invoice Footer -->
        <div class="invoice-footer">
            <p><strong>Thank You for Your Business!</strong></p>
            <p>This is a computer generated invoice and does not require a signature.</p>
            <p>For any queries, please contact: {{ $helpline }}</p>
        </div>
    </div>
</body>
</html>