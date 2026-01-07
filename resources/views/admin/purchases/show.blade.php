@extends('layouts.app')

@section('title', 'Invoice Details - #{{ $purchase->invoice_no }}')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Invoice Details</h4>
            </div>
        </div>
        <ul class="table-top-head">
            <li>
                <a href="{{ route('purchases.pdf', $purchase->id) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf" target="_blank">
                    <img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img">
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" onclick="window.print()" data-bs-toggle="tooltip" data-bs-placement="top" title="Print">
                    <i data-feather="printer" class="feather-rotate-ccw"></i>
                </a>
            </li>
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header">
                    <i class="ti ti-chevron-up"></i>
                </a>
            </li>
        </ul>
        <div class="page-btn">
            <a href="{{ route('all_purchases') }}" class="btn btn-primary">
                <i data-feather="arrow-left" class="me-2"></i>Back to Invoices
            </a>
        </div>
    </div>

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
                        <img src="{{ $logoUrl }}" width="130" class="img-fluid" alt="logo">
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
                        <p>{{ setting_value('helpline', '+92-335-08-999-08') }}</p>
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
                            <i class="ti ti-point-filled"></i>
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
                        @if(setting_value('signature'))
                            <img src="{{ setting_value('signature') }}" class="img-fluid" alt="sign" style="max-height: 80px;">
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
                    <img src="{{ $logoUrl }}" width="130" class="img-fluid" alt="logo">
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

    <div class="d-flex justify-content-center align-items-center mb-4">
        <a href="javascript:void(0);" onclick="window.print()" class="btn btn-primary d-flex justify-content-center align-items-center me-2">
            <i class="ti ti-printer me-2"></i>Print Invoice
        </a>
        <a href="{{ route('purchases.pdf', $purchase->id) }}" class="btn btn-secondary d-flex justify-content-center align-items-center border" target="_blank">
            <i class="ti ti-file-pdf me-2"></i>Download PDF
        </a>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        
        .content, .content * {
            visibility: visible;
        }
        
        .content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        
        .page-header,
        .page-btn,
        .btn,
        .table-top-head,
        .d-flex.justify-content-center {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .card-body {
            padding: 20px !important;
        }
        
        @page {
            margin: 15mm;
            size: A4;
        }
    }
</style>
@endpush

@endsection
