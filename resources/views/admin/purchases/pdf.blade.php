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
            background: #fff;
        }
        
        .content {
            width: 100%;
            margin: 0 auto;
        }
        
        .card {
            width: 100%;
            margin: 0 auto;
            background: #fff;
            border: none;
            box-shadow: none;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -15px;
            margin-right: -15px;
        }
        
        .col-md-2, .col-md-5, .col-md-6, .col-md-7 {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .col-md-2 {
            width: 16.666667%;
        }
        
        .col-md-5 {
            width: 41.666667%;
        }
        
        .col-md-6 {
            width: 50%;
        }
        
        .col-md-7 {
            width: 58.333333%;
        }
        
        .ms-auto {
            margin-left: auto;
        }
        
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mt-3 { margin-top: 1rem; }
        
        .border-bottom {
            border-bottom: 1px solid #dee2e6;
        }
        
        .text-end {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-dark {
            color: #212529;
        }
        
        .text-primary {
            color: #0d6efd;
        }
        
        .text-gray {
            color: #6c757d;
        }
        
        .text-gray-9 {
            color: #495057;
        }
        
        .text-muted {
            color: #6c757d;
        }
        
        .fw-medium {
            font-weight: 500;
        }
        
        .fw-semibold {
            font-weight: 600;
        }
        
        .fs-10 {
            font-size: 10px;
        }
        
        .fs-12 {
            font-size: 12px;
        }
        
        .fs-14 {
            font-size: 14px;
        }
        
        .img-fluid {
            max-width: 100%;
            height: auto;
        }
        
        h4 {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        h5 {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        h6 {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        p {
            margin-bottom: 0.5rem;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
        }
        
        .table thead th.text-end {
            text-align: right;
        }
        
        .table tbody td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 11px;
            word-wrap: break-word;
            word-break: break-word;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .d-flex {
            display: flex;
        }
        
        .justify-content-between {
            justify-content: space-between;
        }
        
        .justify-content-center {
            justify-content: center;
        }
        
        .align-items-center {
            align-items: center;
        }
        
        .pe-3 {
            padding-right: 1rem;
        }
        
        .me-2 {
            margin-right: 0.5rem;
        }
        
        .me-3 {
            margin-right: 1rem;
        }
        
        .bg-success {
            background-color: #28a745;
        }
        
        .text-white {
            color: #fff;
        }
        
        .rounded {
            border-radius: 0.25rem;
        }
        
        .px-1 {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        
        .small {
            font-size: 0.875rem;
        }
        
        .text-title {
            font-weight: 500;
            color: #333;
        }
        
        @page {
            margin: 15mm;
            size: A4 portrait;
        }
    </style>
</head>
<body>
<div class="content">
    <!-- Invoices -->
    <div class="card">
        <div class="card-body">
            <div class="row justify-content-between align-items-center border-bottom mb-3">
                <div class="col-md-6">
                    <div class="mb-2">
                        @php
                            $logoUrl = setting_value('logo');
                            if (!$logoUrl) {
                                $logoUrl = asset('assets/img/logo.svg');
                            }
                        @endphp
                        @if($logoData)
                            <img src="{{ $logoData }}" width="130" class="img-fluid" alt="logo">
                        @else
                            <img src="{{ $logoUrl }}" width="130" class="img-fluid" alt="logo">
                        @endif
                    </div>
                    @php
                        $address = setting_value('address', '');
                        $city = setting_value('city', '');
                        $state = setting_value('state', '');
                        $zip = setting_value('zip', '');
                        $country = setting_value('country', '');
                    @endphp
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
                        <h5 class="text-gray mb-1">Invoice No <span class="text-primary">#{{ $purchase->invoice_no }}</span></h5>
                        <p class="mb-1 fw-medium">Created Date : <span class="text-dark">{{ $purchase->purchase_date->format('M d, Y') }}</span></p>
                        <p class="fw-medium">Due Date : <span class="text-dark">{{ $purchase->purchase_date->format('M d, Y') }}</span></p>
                    </div>
                </div>
            </div>
            
            <div class="row border-bottom mb-3">
                <div class="col-md-5">
                    <p class="text-dark mb-2 fw-semibold">From</p>
                    <div>
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
                        <p class="mb-1">Email : <span class="text-dark">{{ setting_value('email', '') }}</span></p>
                        <p>Phone : <span class="text-dark">{{ $helpline }}</span></p>
                    </div>
                </div>
                <div class="col-md-5">
                    <p class="text-dark mb-2 fw-semibold">To</p>
                    <div>
                        <h4 class="mb-1">{{ $purchase->supplier->names[0] ?? 'N/A' }}</h4>
                        @if($purchase->supplier && $purchase->supplier->company)
                            <p class="mb-1">{{ $purchase->supplier->company }}</p>
                        @endif
                        @if($purchase->supplier && $purchase->supplier->address)
                            <p class="mb-1">{{ $purchase->supplier->address }}</p>
                        @endif
                        @if($purchase->supplier && $purchase->supplier->area)
                            <p class="mb-1">{{ $purchase->supplier->area }}</p>
                        @endif
                        @if($purchase->supplier && isset($purchase->supplier->phones[0]))
                            <p class="mb-1">Email : <span class="text-dark">{{ $purchase->supplier->email ?? '-' }}</span></p>
                            <p>Phone : <span class="text-dark">{{ $purchase->supplier->phones[0] }}</span></p>
                        @endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <p class="text-title mb-2 fw-medium">Payment Status</p>
                        <span class="bg-success text-white fs-10 px-1 rounded">
                            {{ $purchase->status == 'received' ? 'Paid' : ($purchase->status == 'pending' ? 'Pending' : 'Ordered') }}
                        </span>
                        @if($purchase->branch)
                            <div class="mt-3">
                                <p class="mb-1 small"><strong>Branch:</strong></p>
                                <p class="mb-0 small">{{ $purchase->branch->branch_name }}</p>
                                @if($purchase->branch->branch_code)
                                    <p class="mb-0 small">({{ $purchase->branch->branch_code }})</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div>
                <p class="fw-medium">Invoice For : <span class="text-dark fw-medium">{{ $purchase->reference ? 'Reference: ' . $purchase->reference : 'Purchase Order' }}</span></p>
                <div class="table-responsive mb-3">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>Job Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Cost</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $purchaseItem)
                            <tr>
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
                                    <h6>{{ $itemName }}</h6>
                                    @if($item->bar_code)
                                        <small class="text-muted">Barcode: {{ $item->bar_code }}</small>
                                    @endif
                                </td>
                                <td class="text-gray-9 fw-medium text-end">{{ number_format($purchaseItem->quantity, 2) }} {{ $purchaseItem->unit ?? 'pcs' }}</td>
                                <td class="text-gray-9 fw-medium text-end">Rs {{ number_format($purchaseItem->rate, 2) }}</td>
                                <td class="text-gray-9 fw-medium text-end">Rs {{ number_format($purchaseItem->discount, 2) }}</td>
                                <td class="text-gray-9 fw-medium text-end">Rs {{ number_format($purchaseItem->total_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="row border-bottom mb-3">
                <div class="col-md-5 ms-auto mb-3">
                    <div class="d-flex justify-content-between align-items-center border-bottom mb-2 pe-3">
                        <p class="mb-0">Sub Total</p>
                        <p class="text-dark fw-medium mb-2">Rs {{ number_format($purchase->subtotal, 2) }}</p>
                    </div>
                    @if($purchase->discount > 0)
                    <div class="d-flex justify-content-between align-items-center border-bottom mb-2 pe-3">
                        <p class="mb-0">Discount 
                            @php
                                $discountPercent = $purchase->subtotal > 0 ? ($purchase->discount / $purchase->subtotal) * 100 : 0;
                            @endphp
                            ({{ number_format($discountPercent, 2) }}%)
                        </p>
                        <p class="text-dark fw-medium mb-2">Rs {{ number_format($purchase->discount, 2) }}</p>
                    </div>
                    @endif
                    @if($purchase->order_tax > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2 pe-3">
                        <p class="mb-0">VAT 
                            @php
                                $taxPercent = $purchase->subtotal > 0 ? ($purchase->order_tax / $purchase->subtotal) * 100 : 0;
                            @endphp
                            ({{ number_format($taxPercent, 2) }}%)
                        </p>
                        <p class="text-dark fw-medium mb-2">Rs {{ number_format($purchase->order_tax, 2) }}</p>
                    </div>
                    @endif
                    @if($purchase->shipping > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2 pe-3">
                        <p class="mb-0">Shipping</p>
                        <p class="text-dark fw-medium mb-2">Rs {{ number_format($purchase->shipping, 2) }}</p>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-center mb-2 pe-3">
                        <h5>Total Amount</h5>
                        <h5>Rs {{ number_format($purchase->grand_total, 2) }}</h5>
                    </div>
                    <p class="fs-12">
                        Amount in Words : <span class="text-dark">
                            @php
                                $amount = (int)$purchase->grand_total;
                                $paise = (int)(($purchase->grand_total - $amount) * 100);
                                $words = ucwords(numberToWords($amount)) . ' Rupees';
                                if ($paise > 0) {
                                    $words .= ' and ' . ucwords(numberToWords($paise)) . ' Paise';
                                }
                                $words .= ' Only';
                            @endphp
                            {{ $words }}
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="row align-items-center border-bottom mb-3">
                <div class="col-md-7">
                    <div>
                        @if($purchase->description)
                        <div class="mb-3">
                            <h6 class="mb-1">Terms and Conditions</h6>
                            <p>{{ $purchase->description }}</p>
                        </div>
                        @endif
                        <div class="mb-3">
                            <h6 class="mb-1">Notes</h6>
                            <p>Please pay within 15 days from the date of invoice, overdue interest @ 14% will be charged on delayed payments.</p>
                            <p>Please quote invoice number when remitting funds.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="text-end">
                        @php
                            $signatureUrl = setting_value('signature');
                            if ($signatureUrl) {
                                $signaturePath = str_replace(url('/'), public_path(), $signatureUrl);
                                if (!file_exists($signaturePath)) {
                                    $signaturePath = public_path(str_replace(url('/') . '/', '', $signatureUrl));
                                }
                                if (file_exists($signaturePath)) {
                                    $signatureContent = file_get_contents($signaturePath);
                                    $signatureExtension = strtolower(pathinfo($signaturePath, PATHINFO_EXTENSION));
                                    $signatureMime = $signatureExtension == 'png' ? 'image/png' : ($signatureExtension == 'jpg' || $signatureExtension == 'jpeg' ? 'image/jpeg' : 'image/svg+xml');
                                    $signatureData = 'data:' . $signatureMime . ';base64,' . base64_encode($signatureContent);
                                } else {
                                    $signatureData = $signatureUrl;
                                }
                            } else {
                                $signatureData = null;
                            }
                        @endphp
                        @if($signatureData)
                            <img src="{{ $signatureData }}" class="img-fluid" alt="sign" style="max-height: 80px;">
                        @endif
                    </div>
                    <div class="text-end mb-3">
                        <h6 class="fs-14 fw-medium pe-3">{{ setting_value('authorized_person', 'Authorized Signatory') }}</h6>
                        <p>{{ setting_value('designation', 'Manager') }}</p>
                    </div>
                </div>
            </div>
            
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
                <div class="d-flex justify-content-center align-items-center">
                    @if($bankName)
                        <p class="fs-12 mb-0 me-3">Bank Name : <span class="text-dark">{{ $bankName }}</span></p>
                    @endif
                    @if($accountNumber)
                        <p class="fs-12 mb-0 me-3">Account Number : <span class="text-dark">{{ $accountNumber }}</span></p>
                    @endif
                    @if($ifsc)
                        <p class="fs-12">IFSC : <span class="text-dark">{{ $ifsc }}</span></p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    <!-- /Invoices -->
</div>
</body>
</html>
