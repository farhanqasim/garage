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
            background: #fff;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 15mm 20mm;
            background: #fff;
        }
        
        /* Header Section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .logo-section {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        
        .logo-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 5px;
        }
        
        .company-details {
            flex: 1;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        
        .company-address {
            font-size: 10px;
            color: #555;
            line-height: 1.6;
        }
        
        .invoice-title-section {
            text-align: center;
            margin: 20px 0;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #0d6efd;
            letter-spacing: 2px;
        }
        
        .invoice-number-section {
            text-align: right;
        }
        
        .invoice-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            color: #0d6efd;
        }
        
        /* Billing and Shipping Section */
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .bill-to, .ship-to {
            width: 48%;
            background-color: #f9f9f9;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #0d6efd;
        }
        
        .address-info {
            font-size: 10px;
            color: #333;
            line-height: 1.8;
        }
        
        .address-info p {
            margin: 3px 0;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        /* Invoice Details Bar */
        .invoice-details-bar {
            background-color: #0d6efd;
            color: #fff;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        
        .detail-item {
            flex: 1;
            text-align: center;
        }
        
        .detail-label {
            font-size: 9px;
            text-transform: uppercase;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 11px;
            font-weight: bold;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        .items-table thead {
            background-color: #0d6efd;
            color: #fff;
        }
        
        .items-table th {
            padding: 12px 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            border: none;
        }
        
        .items-table th.text-center {
            text-align: center;
        }
        
        .items-table th.text-right {
            text-align: right;
        }
        
        .items-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            page-break-inside: avoid;
        }
        
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .items-table td {
            padding: 12px 10px;
            font-size: 10px;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        .items-table td.text-center {
            text-align: center;
        }
        
        .items-table td.text-right {
            text-align: right;
        }
        
        .item-number {
            width: 5%;
            text-align: center;
        }
        
        .item-description {
            width: 45%;
        }
        
        .item-description strong {
            display: block;
            margin-bottom: 4px;
            color: #333;
            font-size: 11px;
        }
        
        .item-description small {
            font-size: 9px;
            color: #666;
            display: block;
            margin-top: 3px;
        }
        
        .item-qty {
            width: 12%;
            text-align: center;
        }
        
        .item-rate {
            width: 12%;
            text-align: right;
        }
        
        .item-amount {
            width: 12%;
            text-align: right;
            font-weight: bold;
        }
        
        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .thank-you {
            width: 45%;
            padding-top: 10px;
        }
        
        .thank-you-text {
            font-size: 11px;
            color: #666;
            font-style: italic;
        }
        
        .totals-box {
            width: 45%;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 11px;
        }
        
        .total-row:last-child {
            margin-bottom: 0;
        }
        
        .total-label {
            color: #666;
            font-weight: normal;
        }
        
        .total-value {
            color: #333;
            font-weight: bold;
        }
        
        .tax-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 11px;
        }
        
        .tax-label {
            color: #666;
        }
        
        .tax-value {
            color: #333;
        }
        
        .grand-total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            margin-top: 10px;
            border-top: 2px solid #0d6efd;
            border-bottom: 2px solid #0d6efd;
        }
        
        .grand-total-label {
            font-size: 13px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
        }
        
        .grand-total-value {
            font-size: 16px;
            font-weight: bold;
            color: #0d6efd;
        }
        
        .balance-due-box {
            background-color: #0d6efd;
            color: #fff;
            padding: 12px 15px;
            border-radius: 5px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .balance-due-label {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .balance-due-value {
            font-size: 14px;
            font-weight: bold;
        }
        
        /* Terms & Conditions */
        .terms-section {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #0d6efd;
            page-break-inside: avoid;
        }
        
        .terms-title {
            font-size: 11px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .terms-text {
            font-size: 10px;
            color: #555;
            line-height: 1.6;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-received {
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
        
        @page {
            margin: 0;
            size: A4 portrait;
        }
        
        @media print {
            .invoice-container {
                padding: 10mm 15mm;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="invoice-header">
            <div class="logo-section">
                @if($logoData)
                    <img src="{{ $logoData }}" alt="Logo" class="logo-img">
                @endif
                <div class="company-details">
                    <div class="company-name">{{ $companyName }}</div>
                    <div class="company-address">
                        @php
                            $address = setting_value('address', '');
                            $city = setting_value('city', '');
                            $state = setting_value('state', '');
                            $zip = setting_value('zip', '');
                            $country = setting_value('country', '');
                        @endphp
                        @if($address)
                            <p>{{ $address }}</p>
                        @endif
                        @if($city || $state || $zip)
                            <p>
                                @if($city) {{ $city }} @endif
                                @if($state) {{ $state }} @endif
                                @if($zip) {{ $zip }} @endif
                            </p>
                        @endif
                        @if($country)
                            <p>{{ $country }}</p>
                        @endif
                        <p><strong>Helpline:</strong> {{ $helpline }}</p>
                    </div>
                </div>
            </div>
            <div class="invoice-number-section">
                <div class="invoice-label">Invoice#</div>
                <div class="invoice-number">{{ $purchase->invoice_no }}</div>
            </div>
        </div>
        
        <!-- Invoice Title -->
        <div class="invoice-title-section">
            <div class="invoice-title">INVOICE</div>
        </div>
        
        <!-- Billing and Shipping Section -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="address-info">
                    <p><strong>{{ $purchase->supplier->names[0] ?? 'N/A' }}</strong></p>
                    @if($purchase->supplier && $purchase->supplier->company)
                        <p>{{ $purchase->supplier->company }}</p>
                    @endif
                    @if($purchase->supplier && $purchase->supplier->address)
                        <p>{{ $purchase->supplier->address }}</p>
                    @endif
                    @if($purchase->supplier && $purchase->supplier->area)
                        <p>{{ $purchase->supplier->area }}</p>
                    @endif
                    @if($purchase->supplier && isset($purchase->supplier->phones[0]))
                        <p><strong>Phone:</strong> {{ $purchase->supplier->phones[0] }}</p>
                    @endif
                </div>
            </div>
            <div class="ship-to">
                <div class="section-title">Ship To</div>
                <div class="address-info">
                    @if($purchase->branch)
                        <p><strong>{{ $purchase->branch->branch_name }}</strong></p>
                        @if($purchase->branch->branch_code)
                            <p>Code: {{ $purchase->branch->branch_code }}</p>
                        @endif
                        @if($purchase->branch->address)
                            <p>{{ $purchase->branch->address }}</p>
                        @endif
                    @else
                        <p><strong>Same as Bill To</strong></p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Invoice Details Bar -->
        <div class="invoice-details-bar">
            <div class="detail-item">
                <div class="detail-label">Invoice Date</div>
                <div class="detail-value">{{ $purchase->purchase_date->format('d M Y') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Terms</div>
                <div class="detail-value">Due on Receipt</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Due Date</div>
                <div class="detail-value">{{ $purchase->purchase_date->format('d M Y') }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="status-badge status-{{ $purchase->status }}">
                        {{ strtoupper($purchase->status) }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="item-number">#</th>
                    <th class="item-description">Item & Description</th>
                    <th class="item-qty text-center">Qty</th>
                    <th class="item-rate text-right">Rate</th>
                    <th class="item-amount text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $index => $purchaseItem)
                <tr>
                    <td class="item-number text-center">{{ $index + 1 }}</td>
                    <td class="item-description">
                        @php
                            $item = $purchaseItem->item;
                            $itemName = $item->short_disc ?? $item->pro_dis ?? $item->bar_code ?? 'N/A';
                            if ($item->partnumber_item) {
                                $itemName = $item->partnumber_item->name ?? $itemName;
                            }
                            $description = '';
                            if ($item->category) {
                                $description .= $item->category->name;
                            }
                            if ($item->vehical_item && $item->vehical_item->manutacturer_vehical) {
                                if ($description) $description .= ', ';
                                $description .= $item->vehical_item->manutacturer_vehical->name;
                            }
                            if ($item->vehical_item && $item->vehical_item->model_vehical) {
                                if ($description) $description .= ' ';
                                $description .= $item->vehical_item->model_vehical->name;
                            }
                        @endphp
                        <strong>{{ $itemName }}</strong>
                        @if($description)
                            <small>{{ $description }}</small>
                        @endif
                        @if($item->bar_code)
                            <small>Barcode: {{ $item->bar_code }}</small>
                        @endif
                        @if($purchaseItem->unit && $purchaseItem->unit != 'Unit')
                            <small>Unit: {{ $purchaseItem->unit }}</small>
                        @endif
                    </td>
                    <td class="item-qty text-center">{{ number_format($purchaseItem->quantity, 2) }} {{ $purchaseItem->unit ?? 'pcs' }}</td>
                    <td class="item-rate text-right">Rs {{ number_format($purchaseItem->rate, 2) }}</td>
                    <td class="item-amount text-right">Rs {{ number_format($purchaseItem->total_cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Summary Section -->
        <div class="summary-section">
            <div class="thank-you">
                <p class="thank-you-text">Thanks for your business.</p>
                @if($purchase->description)
                    <div style="margin-top: 15px;">
                        <div style="font-size: 10px; font-weight: bold; color: #666; margin-bottom: 5px;">Note:</div>
                        <div style="font-size: 10px; color: #555; word-wrap: break-word; word-break: break-word;">{{ $purchase->description }}</div>
                    </div>
                @endif
            </div>
            <div class="totals-box">
                <div class="total-row">
                    <span class="total-label">Sub Total</span>
                    <span class="total-value">Rs {{ number_format($purchase->subtotal, 2) }}</span>
                </div>
                @if($purchase->order_tax > 0)
                <div class="tax-row">
                    <span class="tax-label">Tax Rate</span>
                    <span class="tax-value">
                        @php
                            $taxRate = $purchase->subtotal > 0 ? ($purchase->order_tax / $purchase->subtotal) * 100 : 0;
                        @endphp
                        {{ number_format($taxRate, 2) }}%
                    </span>
                </div>
                <div class="total-row">
                    <span class="total-label">Tax Amount</span>
                    <span class="total-value">Rs {{ number_format($purchase->order_tax, 2) }}</span>
                </div>
                @endif
                @if($purchase->discount > 0)
                <div class="total-row">
                    <span class="total-label">Discount</span>
                    <span class="total-value">- Rs {{ number_format($purchase->discount, 2) }}</span>
                </div>
                @endif
                @if($purchase->shipping > 0)
                <div class="total-row">
                    <span class="total-label">Shipping</span>
                    <span class="total-value">Rs {{ number_format($purchase->shipping, 2) }}</span>
                </div>
                @endif
                <div class="grand-total-row">
                    <span class="grand-total-label">Total</span>
                    <span class="grand-total-value">Rs {{ number_format($purchase->grand_total, 2) }}</span>
                </div>
                <div class="balance-due-box">
                    <span class="balance-due-label">Balance Due</span>
                    <span class="balance-due-value">Rs {{ number_format($purchase->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
        
        <!-- Terms & Conditions -->
        <div class="terms-section">
            <div class="terms-title">Terms & Conditions</div>
            <div class="terms-text">
                Full payment is due upon receipt of this invoice. Late payments may incur additional charges or interest as per the applicable laws. 
                @if($purchase->reference)
                    Reference: {{ $purchase->reference }}
                @endif
            </div>
        </div>
    </div>
</body>
</html>
