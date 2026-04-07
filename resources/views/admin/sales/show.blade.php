@extends('layouts.app')

@section('title', 'Sale Details - #{{ $sale->reference ?? $sale->id }}')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h4>Sale Details</h4>
            </div>
        </div>
        <ul class="table-top-head">
            <li>
                <a href="{{ route('sales.download.pdf', $sale->id) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf" target="_blank">
                    <img src="{{ asset('assets/img/icons/pdf.svg') }}" alt="img">
                </a>
            </li>
            <li>
                <a href="{{ route('sales.print', ['id' => $sale->id, 'return' => 'show']) }}" target="_blank" rel="noopener" data-bs-toggle="tooltip" data-bs-placement="top" title="Thermal print" onclick="if(this.dataset.opened){event.preventDefault();return false;}this.dataset.opened='1';setTimeout(function(el){el.dataset.opened='';},6000,this);">
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
            <a href="{{ route('all_sales') }}" class="btn btn-primary">
                <i data-feather="arrow-left" class="me-2"></i>Back to Sales
            </a>
        </div>
    </div>

    <!-- Sale Invoice -->
    <div class="card">
        <div class="card-body">
                @php
                    $logoUrl = setting_value('logo');
                    if (!$logoUrl) {
                        $logoUrl = asset('assets/img/logo.svg');
                    }
                    $companyName = setting_value('logo_text', 'MUBARAK TRADERS');
                    $helpline = setting_value('helpline', '+92-335-08-999-08');
                    $address = setting_value('address', '');
                    $city = setting_value('city', '');
                    $state = setting_value('state', '');
                    $zip = setting_value('zip', '');
                    $country = setting_value('country', '');
                    $totalPaid = $sale->total_paid ?? 0;
                    $discount = $sale->discount ?? 0;
                    // If discount is given and no payment, treat discount as payment
                    if ($discount > 0 && $totalPaid == 0) {
                        $due = max(0, $sale->grand_total - $discount);
                    } else {
                        $due = max(0, $sale->grand_total - $totalPaid);
                    }
                @endphp
            
            <div class="row justify-content-between align-items-center border-bottom mb-3">
                <div class="col-md-6">
                    <div class="mb-2">
                        <img src="{{ $logoUrl }}" width="130" class="img-fluid" alt="logo">
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
                        <h5 class="text-gray mb-1">Invoice No <span class="text-primary">#{{ $sale->reference ?? 'SALE-' . $sale->id }}</span></h5>
                        <p class="mb-1 fw-medium">Sale Date : <span class="text-dark">{{ $sale->sale_date->format('M d, Y') }}</span></p>
                        <p class="fw-medium">Status : <span class="text-dark">{{ ucfirst($sale->status) }}</span></p>
                    </div>
                </div>
            </div>
            
            <div class="row border-bottom mb-3" style="display: flex; flex-wrap: nowrap;">
                <div style="flex: 1; padding-right: 15px;">
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
                <div style="flex: 1; padding-left: 15px; padding-right: 15px; border-left: 1px solid #dee2e6;">
                    <p class="text-dark mb-2 fw-semibold">To</p>
                    <div>
                        <h4 class="mb-1">{{ $sale->customer->names[0] ?? 'N/A' }}</h4>
                        @if($sale->customer && isset($sale->customer->phones[0]))
                            <p class="mb-1">Phone : <span class="text-dark">{{ $sale->customer->phones[0] }}</span></p>
                        @endif
                        @if($sale->customer && $sale->customer->email)
                            <p class="mb-1">Email : <span class="text-dark">{{ $sale->customer->email }}</span></p>
                        @endif
                        @if($sale->customer && $sale->customer->address)
                            <p class="mb-1">{{ $sale->customer->address }}</p>
                        @endif
                    </div>
                </div>
                <div style="flex: 0 0 150px; padding-left: 15px; border-left: 1px solid #dee2e6;">
                    <div class="mb-3">
                        <p class="text-title mb-2 fw-medium">Payment Status</p>
                        @if($totalPaid > 0)
                            <span class="bg-success text-white fs-10 px-1 rounded">
                                <i class="ti ti-point-filled"></i> Paid
                            </span>
                        @else
                            <span class="bg-warning text-white fs-10 px-1 rounded">
                                <i class="ti ti-point-filled"></i> Pending
                            </span>
                        @endif
                        @if($sale->branch)
                            <div class="mt-3">
                                <p class="mb-1 small"><strong>Branch:</strong></p>
                                <p class="mb-0 small">{{ $sale->branch->branch_name }}</p>
                                @if($sale->branch->branch_code)
                                    <p class="mb-0 small">({{ $sale->branch->branch_code }})</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div>
                <p class="fw-medium">Invoice For : <span class="text-dark fw-medium">{{ $sale->reference ? 'Reference: ' . $sale->reference : 'Sale Invoice' }}</span></p>
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
                                        $entryType = (string) ($saleItem->entry_type ?? '');
                                        $isTemporary = $entryType === 'temporary';
                                        $isPlaceholder = $entryType === 'placeholder';
                                        $itemName = $item->short_disc ?? $item->pro_dis ?? $item->bar_code ?? 'N/A';
                                        if ($item->partnumber_item) {
                                            $itemName = $item->partnumber_item->name ?? $itemName;
                                        }
                                        if ($item->category) {
                                            $itemName .= ' - ' . $item->category->name;
                                        }
                                        if ($isTemporary) {
                                            $itemName = $saleItem->temporary_item_name ?: ($saleItem->voice_transcript ?: 'Temporary item');
                                        } elseif ($isPlaceholder) {
                                            $itemName = $saleItem->line_note ?: 'Placeholder line';
                                        }
                                    @endphp
                                    @if($isTemporary)
                                        <span class="badge bg-warning text-dark me-1">TEMPORARY</span>
                                    @elseif($isPlaceholder)
                                        <span class="badge bg-info text-dark me-1">PLACEHOLDER</span>
                                    @endif
                                    <h6>{{ $itemName }}</h6>
                                    @if($isTemporary && $saleItem->temporary_quality)
                                        <small class="text-muted d-block">Quality: {{ $saleItem->temporary_quality }}</small>
                                    @endif
                                    @if($isTemporary && $saleItem->line_note)
                                        <small class="text-muted d-block">Note: {{ $saleItem->line_note }}</small>
                                    @endif
                                    @if($item->bar_code && ! $isTemporary && ! $isPlaceholder)
                                        <small class="text-muted">Barcode: {{ $item->bar_code }}</small>
                                    @endif
                                    @if($saleItem->warranty)
                                        <small class="text-muted">Warranty: {{ $saleItem->warranty }}</small>
                                    @endif
                                    @if($isTemporary)
                                        <div class="mt-2">
                                            <a href="{{ route('all.items.create.new') }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Create inventory item</a>
                                            <span class="small text-muted ms-1">— add the real product, then adjust this sale if needed.</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-gray-9 fw-medium text-end">{{ number_format($saleItem->quantity, 2) }} {{ $saleItem->unit ?? 'pcs' }}</td>
                                <td class="text-gray-9 fw-medium text-end">Rs {{ number_format($saleItem->rate, 2) }}</td>
                                <td class="text-gray-9 fw-medium text-end">Rs {{ number_format($saleItem->discount ?? 0, 2) }}</td>
                                <td class="text-gray-9 fw-medium text-end">{{ number_format($saleItem->tax_percentage ?? 0, 2) }}%</td>
                                <td class="text-gray-9 fw-medium text-end">Rs {{ number_format($saleItem->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="row border-bottom mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center border-bottom mb-2" style="display: flex; flex-wrap: nowrap;">
                        <div style="flex: 1; text-align: center; padding: 10px; border-right: 1px solid #dee2e6;">
                            <p class="mb-1">Sub Total</p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($sale->subtotal, 2) }}</p>
                        </div>
                        @if($sale->discount > 0)
                        <div style="flex: 1; text-align: center; padding: 10px; border-right: 1px solid #dee2e6;">
                            <p class="mb-1">Discount 
                                @php
                                    $discountPercent = $sale->subtotal > 0 ? ($sale->discount / $sale->subtotal) * 100 : 0;
                                @endphp
                                ({{ number_format($discountPercent, 2) }}%)
                            </p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($sale->discount, 2) }}</p>
                        </div>
                        @else
                        <div style="flex: 1; text-align: center; padding: 10px; border-right: 1px solid #dee2e6;">
                            @if($sale->order_tax > 0)
                            <p class="mb-1">VAT 
                                @php
                                    $taxPercent = $sale->subtotal > 0 ? ($sale->order_tax / $sale->subtotal) * 100 : 0;
                                @endphp
                                ({{ number_format($taxPercent, 2) }}%)
                            </p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($sale->order_tax, 2) }}</p>
                            @elseif($sale->shipping > 0)
                            <p class="mb-1">Shipping</p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($sale->shipping, 2) }}</p>
                            @else
                            <p class="mb-0">-</p>
                            @endif
                        </div>
                        @endif
                        <div style="flex: 1; text-align: center; padding: 10px;">
                            <p class="mb-1">Total Amount</p>
                            <p class="text-dark fw-medium mb-0" style="font-weight: 600;">Rs {{ number_format($sale->grand_total, 2) }}</p>
                        </div>
                    </div>
                    @if($sale->order_tax > 0 && $sale->discount > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2" style="display: flex; flex-wrap: nowrap;">
                        <div style="flex: 1; text-align: center; padding: 10px; border-right: 1px solid #dee2e6;">
                            <p class="mb-1">VAT 
                                @php
                                    $taxPercent = $sale->subtotal > 0 ? ($sale->order_tax / $sale->subtotal) * 100 : 0;
                                @endphp
                                ({{ number_format($taxPercent, 2) }}%)
                            </p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($sale->order_tax, 2) }}</p>
                        </div>
                        <div style="flex: 1; text-align: center; padding: 10px;">
                            @if($sale->shipping > 0)
                            <p class="mb-1">Shipping</p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($sale->shipping, 2) }}</p>
                            @else
                            <p class="mb-0">-</p>
                            @endif
                        </div>
                    </div>
                    @elseif($sale->shipping > 0 && $sale->discount == 0 && $sale->order_tax == 0)
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <div style="text-align: center; padding: 10px;">
                            <p class="mb-1">Shipping</p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($sale->shipping, 2) }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Payment Summary -->
                    @if($totalPaid > 0 || $discount > 0 || $due > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2" style="display: flex; flex-wrap: nowrap; border-top: 1px solid #dee2e6; padding-top: 10px;">
                        @if($discount > 0)
                        <div style="flex: 1; text-align: center; padding: 10px; border-right: 1px solid #dee2e6;">
                            <p class="mb-1">Discount</p>
                            <p class="text-success fw-medium mb-0">Rs {{ number_format($discount, 2) }}</p>
                        </div>
                        @endif
                        @if($totalPaid > 0)
                        <div style="flex: 1; text-align: center; padding: 10px; {{ $discount > 0 ? 'border-right: 1px solid #dee2e6;' : '' }}">
                            <p class="mb-1">Total Paid</p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($totalPaid, 2) }}</p>
                        </div>
                        @endif
                        <div style="flex: 1; text-align: center; padding: 10px;">
                            <p class="mb-1">Due Amount</p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($due, 2) }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <p class="fs-12 text-center mt-2">
                        Amount in Words : <span class="text-dark">
                            @php
                                $amount = (int)$sale->grand_total;
                                $paise = (int)(($sale->grand_total - $amount) * 100);
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
                        <div class="mb-3">
                            <h6 class="mb-1">Notes</h6>
                            <p>Thank you for your business!</p>
                            <p>Please quote invoice number when making payment.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
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
    <!-- /Sale Invoice -->

    <div class="d-flex justify-content-center align-items-center mb-4 flex-wrap gap-2">
        <a href="{{ route('sales.print', ['id' => $sale->id, 'return' => 'show']) }}" target="_blank" rel="noopener" class="btn btn-primary d-flex justify-content-center align-items-center" onclick="if(this.dataset.opened){event.preventDefault();return false;}this.dataset.opened='1';setTimeout(function(el){el.dataset.opened='';},6000,this);">
            <i class="ti ti-printer me-2"></i>Print Invoice
        </a>
        <a href="{{ route('sales.download.pdf', $sale->id) }}" class="btn btn-secondary d-flex justify-content-center align-items-center border me-2" target="_blank">
            <i class="ti ti-file-pdf me-2"></i>Download PDF
        </a>
        <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-info d-flex justify-content-center align-items-center me-2">
            <i class="ti ti-edit me-2"></i>Edit Sale
        </a>
        <a href="{{ route('sales.payments', $sale->id) }}" class="btn btn-success d-flex justify-content-center align-items-center">
            <i class="ti ti-dollar-sign me-2"></i>View Payments
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
            font-size: 11px !important;
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
            margin: 0 !important;
        }
        
        .card-body {
            padding: 10px !important;
        }
    }
</style>
@endpush

@endsection
