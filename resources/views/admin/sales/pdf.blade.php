<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Invoice #{{ $sale->reference ?? $sale->id }}</title>

    <link rel="stylesheet" href="{{ public_path('assets/css/style.css') }}">

    <style>
        @page {
            margin: 8mm;
            size: A4;
        }
        
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            line-height: 1.3;
            font-size: 10px;
        }
        
        .content { 
            padding: 5px; 
        }
        
        .card { 
            background: #fff;
            margin: 0;
        }
        
        .card-body { 
            padding: 10px; 
        }
        
        /* Compact header */
        .row.justify-content-between {
            margin-bottom: 5px !important;
        }
        
        .row.justify-content-between img {
            width: 70px !important;
            height: auto !important;
        }
        
        .row.justify-content-between h5 {
            font-size: 12px !important;
            margin: 2px 0 !important;
        }
        
        .row.justify-content-between p {
            font-size: 9px !important;
            margin: 1px 0 !important;
        }
        
        /* Compact from/to section */
        .row.border-bottom {
            margin-bottom: 5px !important;
            padding-bottom: 5px !important;
        }
        
        .row.border-bottom h4 {
            font-size: 11px !important;
            margin: 2px 0 !important;
        }
        
        .row.border-bottom p {
            font-size: 9px !important;
            margin: 1px 0 !important;
        }
        
        .row.border-bottom .small {
            font-size: 8px !important;
        }
        
        /* Compact table */
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5px !important;
            font-size: 9px !important;
        }
        
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 3px 5px !important;
            font-size: 9px !important;
        }
        
        .table thead { 
            background: #f8f9fa; 
        }
        
        .table h6 {
            font-size: 9px !important;
            margin: 1px 0 !important;
        }
        
        .table small {
            font-size: 7px !important;
        }
        
        /* Compact totals */
        .row.border-bottom.mb-3 {
            margin-bottom: 5px !important;
        }
        
        .row.border-bottom.mb-3 p {
            font-size: 9px !important;
            margin: 1px 0 !important;
        }
        
        .row.border-bottom.mb-3 h5 {
            font-size: 11px !important;
            margin: 2px 0 !important;
        }
        
        /* Compact terms and signature */
        .row.align-items-center {
            margin-bottom: 5px !important;
        }
        
        .row.align-items-center h6 {
            font-size: 10px !important;
            margin: 2px 0 !important;
        }
        
        .row.align-items-center p {
            font-size: 8px !important;
            margin: 1px 0 !important;
        }
        
        .row.align-items-center img {
            max-height: 40px !important;
        }
        
        /* Compact footer */
        .text-center {
            margin-top: 5px !important;
        }
        
        .text-center img {
            width: 70px !important;
        }
        
        .text-center p {
            font-size: 8px !important;
            margin: 1px 0 !important;
        }
        
        /* Utility classes */
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-medium { font-weight: 500; }
        .fw-semibold { font-weight: 600; }
        .text-dark { color: #212529; }
        .text-primary { color: #0d6efd; }
        .bg-success { background: #28a745; }
        .text-white { color: #fff; }
        .fs-10 { font-size: 9px !important; }
        .fs-12 { font-size: 9px !important; }
        .fs-14 { font-size: 10px !important; }
        h4, h5, h6 { margin: 0; }
        p { margin: 2px 0 !important; }
        .border-bottom { border-bottom: 1px solid #dee2e6; }
        .mb-3 { margin-bottom: 5px !important; }
        .mb-2 { margin-bottom: 3px !important; }
        .mb-1 { margin-bottom: 2px !important; }
        .pe-3 { padding-right: 0.5rem; }
        .mt-3 { margin-top: 5px !important; }
        
        /* Prevent page breaks */
        .card-body {
            page-break-inside: avoid;
        }
        
        .row {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
<div class="content">
    <div class="card">
        <div class="card-body">
            <!-- Header -->
            <div class="row justify-content-between align-items-center border-bottom mb-3">
                <div class="col-md-6">
                    <div class="mb-2">
                        @if($logoData)
                            <img src="{{ $logoData }}" width="130" class="img-fluid" alt="logo">
                        @else
                            <img src="{{ $logoUrl }}" width="130" class="img-fluid" alt="logo">
                        @endif
                    </div>
                    @if($address || $city || $state || $zip || $country)
                        <p>
                            @if($address) {{ $address }}, @endif
                            @if($city) {{ $city }}, @endif
                            @if($state) {{ $state }}, @endif
                            @if($zip) {{ $zip }} @endif
                            @if($country) {{ $country }} @endif
                        </p>
                    @else
                        <p>{{ $helpline }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="text-end mb-3">
                        <h5 class="mb-1">Invoice No <span class="text-primary">#{{ $sale->reference ?? 'SALE-' . $sale->id }}</span></h5>
                        <p class="mb-1 fw-medium">Sale Date: <span class="text-dark">{{ $sale->sale_date->format('M d, Y') }}</span></p>
                        <p class="fw-medium">Status: <span class="text-dark">{{ ucfirst($sale->status) }}</span></p>
                    </div>
                </div>
            </div>

            <!-- From / To / Status -->
            <table class="border-bottom mb-3" style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
                <tr>
                    <td style="width: 40%; padding-right: 10px; vertical-align: top;">
                        <p class="text-dark mb-2 fw-semibold">From</p>
                        <h4 class="mb-1">{{ $companyName }}</h4>
                        @if($address || $city || $state || $zip || $country)
                            <p class="mb-1">
                                @if($address) {{ $address }}, @endif
                                @if($city) {{ $city }}, @endif
                                @if($state) {{ $state }}, @endif
                                @if($zip) {{ $zip }} @endif
                                @if($country) {{ $country }} @endif
                            </p>
                        @endif
                        <p class="mb-1">Email: <span class="text-dark">{{ setting_value('email', '') }}</span></p>
                        <p>Phone: <span class="text-dark">{{ $helpline }}</span></p>
                    </td>
                    <td style="width: 40%; padding-left: 10px; padding-right: 10px; border-left: 1px solid #dee2e6; vertical-align: top;">
                        <p class="text-dark mb-2 fw-semibold">To</p>
                        <h4 class="mb-1">{{ $sale->customer->names[0] ?? 'N/A' }}</h4>
                        @if($sale->customer && isset($sale->customer->phones[0]))
                            <p class="mb-1">Phone: <span class="text-dark">{{ $sale->customer->phones[0] }}</span></p>
                        @endif
                        @if($sale->customer && $sale->customer->email)
                            <p class="mb-1">Email: <span class="text-dark">{{ $sale->customer->email }}</span></p>
                        @endif
                        @if($sale->customer && $sale->customer->address)
                            <p class="mb-1">{{ $sale->customer->address }}</p>
                        @endif
                    </td>
                    <td style="width: 20%; padding-left: 10px; border-left: 1px solid #dee2e6; vertical-align: top;">
                        <p class="mb-2 fw-medium">Payment Status</p>
                        @php
                            $totalPaid = $sale->total_paid ?? 0;
                            $paymentStatus = $totalPaid > 0 ? 'Paid' : 'Pending';
                            $statusClass = $totalPaid > 0 ? 'bg-success' : 'bg-warning';
                        @endphp
                        <span class="{{ $statusClass }} text-white fs-10 px-1 rounded">
                            {{ $paymentStatus }}
                        </span>
                        @if($sale->branch)
                            <div class="mt-3">
                                <p class="mb-1 small"><strong>Branch:</strong></p>
                                <p class="mb-0 small">{{ $sale->branch->branch_name }}</p>
                                @if($sale->branch->branch_code)
                                    <p class="mb-0 small">({{ $sale->branch->branch_code }})</p>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            </table>

            <!-- Items Table -->
            <div>
                <p class="fw-medium">Invoice For: <span class="text-dark fw-medium">{{ $sale->reference ? 'Reference: ' . $sale->reference : 'Sale Invoice' }}</span></p>
                <div class="table-responsive mb-3">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>Item Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Tax</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleItems as $saleItem)
                            <tr>
                                <td>
                                    @php
                                        $item = $saleItem->item;
                                        $itemName = $item->short_disc ?? $item->pro_dis ?? $item->bar_code ?? 'N/A';
                                        if ($item->partnumber_item) {
                                            $itemName = $item->partnumber_item->name ?? $itemName;
                                        }
                                        if ($item->category) {
                                            $itemName .= ' - ' . $item->category->name;
                                        }
                                    @endphp
                                    <h6>{{ $itemName }}</h6>
                                    @if($item->bar_code)
                                        <small class="text-muted">Barcode: {{ $item->bar_code }}</small>
                                    @endif
                                    @if($saleItem->warranty)
                                        <small class="text-muted">Warranty: {{ $saleItem->warranty }}</small>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($saleItem->quantity, 2) }} {{ $saleItem->unit ?? 'pcs' }}</td>
                                <td class="text-end">Rs {{ number_format($saleItem->rate, 2) }}</td>
                                <td class="text-end">Rs {{ number_format($saleItem->discount ?? 0, 2) }}</td>
                                <td class="text-end">{{ number_format($saleItem->tax_percentage ?? 0, 2) }}%</td>
                                <td class="text-end">Rs {{ number_format($saleItem->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px; border-bottom: 1px solid #dee2e6;">
                <tr>
                    <td style="width: 33%; padding: 5px; text-align: center; border-right: 1px solid #dee2e6; vertical-align: top;">
                        <p class="mb-1" style="font-size: 9px;">Sub Total</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 10px;">Rs {{ number_format($sale->subtotal, 2) }}</p>
                    </td>
                    @if($sale->discount > 0)
                    <td style="width: 33%; padding: 5px; text-align: center; border-right: 1px solid #dee2e6; vertical-align: top;">
                        <p class="mb-1" style="font-size: 9px;">Discount ({{ number_format(($sale->subtotal > 0 ? ($sale->discount / $sale->subtotal) * 100 : 0), 2) }}%)</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 10px;">Rs {{ number_format($sale->discount, 2) }}</p>
                    </td>
                    @else
                    <td style="width: 33%; padding: 5px; text-align: center; border-right: 1px solid #dee2e6; vertical-align: top;">
                        @if($sale->order_tax > 0)
                        <p class="mb-1" style="font-size: 9px;">VAT ({{ number_format(($sale->subtotal > 0 ? ($sale->order_tax / $sale->subtotal) * 100 : 0), 2) }}%)</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 10px;">Rs {{ number_format($sale->order_tax, 2) }}</p>
                        @elseif($sale->shipping > 0)
                        <p class="mb-1" style="font-size: 9px;">Shipping</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 10px;">Rs {{ number_format($sale->shipping, 2) }}</p>
                        @else
                        <p class="mb-0" style="font-size: 9px;">-</p>
                        @endif
                    </td>
                    @endif
                    <td style="width: 34%; padding: 5px; text-align: center; vertical-align: top;">
                        <p class="mb-1" style="font-size: 9px;">Total Amount</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 11px; font-weight: 600;">Rs {{ number_format($sale->grand_total, 2) }}</p>
                    </td>
                </tr>
            </table>
            @if($sale->order_tax > 0 && $sale->discount > 0)
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
                <tr>
                    <td style="width: 50%; padding: 5px; text-align: center; border-right: 1px solid #dee2e6; vertical-align: top;">
                        <p class="mb-1" style="font-size: 9px;">VAT ({{ number_format(($sale->subtotal > 0 ? ($sale->order_tax / $sale->subtotal) * 100 : 0), 2) }}%)</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 10px;">Rs {{ number_format($sale->order_tax, 2) }}</p>
                    </td>
                    <td style="width: 50%; padding: 5px; text-align: center; vertical-align: top;">
                        @if($sale->shipping > 0)
                        <p class="mb-1" style="font-size: 9px;">Shipping</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 10px;">Rs {{ number_format($sale->shipping, 2) }}</p>
                        @else
                        <p class="mb-0" style="font-size: 9px;">-</p>
                        @endif
                    </td>
                </tr>
            </table>
            @elseif($sale->shipping > 0 && $sale->discount == 0 && $sale->order_tax == 0)
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
                <tr>
                    <td style="width: 100%; padding: 5px; text-align: center; vertical-align: top;">
                        <p class="mb-1" style="font-size: 9px;">Shipping</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 10px;">Rs {{ number_format($sale->shipping, 2) }}</p>
                    </td>
                </tr>
            </table>
            @endif
            
            <!-- Payment Summary -->
            @php
                $totalPaid = $sale->total_paid ?? 0;
                $due = $sale->grand_total - $totalPaid;
            @endphp
            @if($totalPaid > 0 || $due > 0)
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px; border-top: 1px solid #dee2e6;">
                <tr>
                    <td style="width: 50%; padding: 5px; text-align: center; border-right: 1px solid #dee2e6; vertical-align: top;">
                        <p class="mb-1" style="font-size: 9px;">Total Paid</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 10px;">Rs {{ number_format($totalPaid, 2) }}</p>
                    </td>
                    <td style="width: 50%; padding: 5px; text-align: center; vertical-align: top;">
                        <p class="mb-1" style="font-size: 9px;">Due Amount</p>
                        <p class="text-dark fw-medium mb-0" style="font-size: 10px;">Rs {{ number_format($due, 2) }}</p>
                    </td>
                </tr>
            </table>
            @endif
            
            <p class="fs-12" style="text-align: center; margin-top: 5px;">
                Amount in Words: <span class="text-dark">
                    @php
                        $amount = (int)$sale->grand_total;
                        $paise = (int)(($sale->grand_total - $amount) * 100);
                        $words = ucwords(numberToWords($amount)) . ' Rupees';
                        if ($paise > 0) $words .= ' and ' . ucwords(numberToWords($paise)) . ' Paise';
                        $words .= ' Only';
                    @endphp
                    {{ $words }}
                </span>
            </p>

            <!-- Terms & Signature -->
            <div class="row align-items-center border-bottom mb-3">
                <div class="col-md-7">
                    <div class="mb-3">
                        <h6 class="mb-1">Notes</h6>
                        <p>Thank you for your business!</p>
                        <p>Please quote invoice number when making payment.</p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="text-end mb-3">
                        <h6 class="fs-14 fw-medium pe-3">{{ setting_value('authorized_person', 'Authorized Signatory') }}</h6>
                        <p>{{ setting_value('designation', 'Manager') }}</p>
                    </div>
                </div>
            </div>

            <!-- Footer Bank Details -->
            <div class="text-center">
                <div class="mb-3">
                    @if($logoData)
                        <img src="{{ $logoData }}" width="130" class="img-fluid" alt="logo">
                    @else
                        <img src="{{ $logoUrl }}" width="130" class="img-fluid" alt="logo">
                    @endif
                </div>
                <p class="text-dark mb-1">Payment Made Via bank transfer / Cheque in the name of {{ $companyName }}</p>
                @php
                    $bankName = setting_value('bank_name', '');
                    $accountNumber = setting_value('account_number', '');
                    $ifsc = setting_value('ifsc_code', '');
                @endphp
                @if($bankName || $accountNumber || $ifsc)
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    @if($bankName)<p class="fs-12 mb-0">Bank Name: <span class="text-dark">{{ $bankName }}</span></p>@endif
                    @if($accountNumber)<p class="fs-12 mb-0">Account Number: <span class="text-dark">{{ $accountNumber }}</span></p>@endif
                    @if($ifsc)<p class="fs-12 mb-0">IFSC: <span class="text-dark">{{ $ifsc }}</span></p>@endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</body>
</html>
