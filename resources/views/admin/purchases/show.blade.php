@extends('layouts.app')

@section('title', 'Invoice Details - #{{ $purchase->invoice_no }}')

@section('content')
@php
    $printMode = $print_mode ?? false;
    $printFormat = $print_format ?? 'a4';
    $printViewClass = $printMode ? 'print-view print-' . $printFormat : '';
@endphp
<div class="content {{ $printMode ? 'print-only-content' : '' }}">
    @if($printMode)
    {{-- Print Preview bar: yeh page hi preview hai; Print dabao to dialog open hoga (Cursor mein dialog preview support nahi karta) --}}
    <div class="print-preview-bar no-print d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-3 rounded shadow-sm" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff;">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-printer fs-24"></i>
            <div>
                <strong>Print Preview</strong>
                <p class="mb-0 small opacity-90">Yeh page jo aap dekh rahe hain, wahi print hogi. Neeche <strong>Print</strong> dabayein.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-light btn-sm" onclick="window.print();">
                <i class="ti ti-printer me-1"></i>Print
            </button>
            <a href="{{ route('all_purchases') }}" class="btn btn-outline-light btn-sm">Back to Purchases</a>
        </div>
    </div>
    @endif
    @if(!$printMode)
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
    @endif

    <!-- Invoices -->
    @php
        $hasTemporaryItems = $purchase->items->contains(fn($i) => $i->item && $i->item->is_temporary);
    @endphp
    <div class="card {{ $printViewClass }} position-relative {{ $hasTemporaryItems ? 'has-temporary-items' : '' }}">
        @if($purchase->items->isNotEmpty())
        @php
            $stampDateTime = $all_verified ? $purchase->items->whereNotNull('verified_at')->max('verified_at') : null;
            if ($stampDateTime && !$stampDateTime instanceof \Carbon\Carbon) $stampDateTime = \Carbon\Carbon::parse($stampDateTime);
        @endphp
        <div id="purchase-verified-stamp" class="purchase-verified-stamp {{ $all_verified ? '' : 'd-none' }}" aria-hidden="true" data-stamp-datetime="{{ $stampDateTime ? $stampDateTime->format('d-M-Y h:i A') : '' }}">
            <span class="purchase-verified-stamp-text">VERIFIED</span>
            <span class="purchase-verified-stamp-datetime">{{ $stampDateTime ? $stampDateTime->format('d-M-Y h:i A') : '' }}</span>
        </div>
        @endif
        <div class="card-body">
            @php
                $logoUrl = setting_value('logo') ?: asset('assets/img/logo.svg');
            @endphp
            <div class="row justify-content-between align-items-center border-bottom mb-3">
                <div class="col-md-6">
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
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="text-end mb-3">
                        <h5 class="text-gray mb-1"><span class="text-primary">#{{ $purchase->invoice_no }}</span></h5>
                        <p class="mb-1 fw-medium">Created Date : <span class="text-dark">{{ $purchase->purchase_date->format('M d, Y') }}</span></p>
                        <p class="fw-medium">Due Date : <span class="text-dark">{{ $purchase->purchase_date->format('M d, Y') }}</span></p>
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
                <div style="flex: 0 0 150px; padding-left: 15px; border-left: 1px solid #dee2e6;">
                    <div class="mb-3">
                        <p class="text-title mb-2 fw-medium">Payment Status</p>
                        <span class="bg-success text-white fs-10 px-1 rounded">
                            <i class="ti ti-point-filled"></i>
                            {{ $purchase->status == 'received' ? 'Paid' : ($purchase->status == 'pending' ? 'Pending' : 'Ordered') }}
                        </span>
                        @if($purchase->items->isNotEmpty())
                        <p class="text-title mb-1 fw-medium mt-2">Verification</p>
                        <span id="purchase-verification-status" class="fs-10 px-1 rounded {{ $all_verified ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                            @if($all_verified)
                                <i class="ti ti-checks"></i> All Verified
                            @else
                                {{ $purchase->items->filter(fn($i) => $i->verified_by)->count() }}/{{ $purchase->items->count() }} Verified
                            @endif
                        </span>
                        @endif
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
                                <th class="text-end">Qty</th>
                                <th>Description</th>
                                <th class="text-end">Cost</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Verified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $purchaseItem)
                            @php
                                $isTemporaryItem = $purchaseItem->item && $purchaseItem->item->is_temporary;
                            @endphp
                            <tr class="{{ $isTemporaryItem ? 'purchase-item-temporary' : '' }}">
                                <td class="text-gray-9 fw-medium text-end">{{ number_format($purchaseItem->quantity, 0) }} {{ $purchaseItem->unit ?? 'pcs' }}</td>
                                <td class="item-description-cell">
                                    @php
                                        $item = $purchaseItem->item;
                                        $parts = [];
                                        $productName = trim(strip_tags((string)($item->short_disc ?? $item->pro_dis ?? '')));
                                        if ($productName === '' && $item->product_item && trim((string)($item->product_item->name ?? '')) !== '') {
                                            $productName = trim($item->product_item->name);
                                        }
                                        if ($productName === '' && $item->partnumber_item && trim((string)($item->partnumber_item->name ?? '')) !== '') {
                                            $productName = trim($item->partnumber_item->name);
                                        }
                                        if ($productName !== '') {
                                            $parts[] = $productName;
                                        }
                                        $plateName = $item->plate_item && trim((string)($item->plate_item->name ?? '')) !== '' ? trim($item->plate_item->name) : null;
                                        $amphorsName = $item->amphors_item && trim((string)($item->amphors_item->name ?? '')) !== '' ? trim($item->amphors_item->name) : null;
                                        $voltName = $item->volt_item && trim((string)($item->volt_item->name ?? '')) !== '' ? trim($item->volt_item->name) : null;
                                        if ($plateName) $parts[] = $plateName;
                                        if ($amphorsName) $parts[] = $amphorsName;
                                        if ($voltName) $parts[] = $voltName;
                                        $companyNamePart = null;
                                        if ($item->company_item && trim((string)($item->company_item->name ?? '')) !== '') {
                                            $companyNamePart = trim($item->company_item->name);
                                            $parts[] = $companyNamePart;
                                        } elseif (trim((string)($item->car_company ?? '')) !== '') {
                                            $companyNamePart = trim($item->car_company);
                                            $parts[] = $companyNamePart;
                                        }
                                        if ($item->vehical_item && $item->vehical_item->manutacturer_vehical) {
                                            $m = trim((string)($item->vehical_item->manutacturer_vehical->name ?? ''));
                                            if ($m !== '') $parts[] = $m;
                                        }
                                        if ($item->vehical_item && $item->vehical_item->model_vehical) {
                                            $m = trim((string)($item->vehical_item->model_vehical->name ?? ''));
                                            if ($m !== '') $parts[] = $m;
                                        }
                                        if ($item->category && trim((string)($item->category->name ?? '')) !== '') {
                                            $parts[] = trim($item->category->name);
                                        }
                                        $itemName = implode(' ', $parts);
                                        $itemName = trim(strip_tags($itemName));
                                        if ($itemName === '') {
                                            $itemName = $item->bar_code ?? 'Item #' . $item->id;
                                        }
                                        // Screenshot-style: top line = model (e.g. GL50-AGS), bottom line = specs (e.g. 12V-11PL-38AH)
                                        $modelLine = $productName !== '' ? $productName : '';
                                        if ($companyNamePart !== null && $companyNamePart !== '') {
                                            $modelLine = $modelLine !== '' ? $modelLine . '-' . $companyNamePart : $companyNamePart;
                                        }
                                        $specsParts = [];
                                        if ($voltName) $specsParts[] = $voltName . 'V';
                                        if ($plateName) $specsParts[] = $plateName . 'PL';
                                        if ($amphorsName) $specsParts[] = $amphorsName . 'AH';
                                        $specsLine = implode('-', $specsParts);
                                        $useScreenshotStyle = ($modelLine !== '' || $specsLine !== '');
                                    @endphp
                                    @if($useScreenshotStyle)
                                        <div class="item-description-block">
                                            @if($modelLine !== '')
                                                <div class="item-description-model">{{ $modelLine }}</div>
                                            @endif
                                            @if($modelLine !== '' && $specsLine !== '')
                                                <hr class="item-description-divider">
                                            @endif
                                            @if($specsLine !== '')
                                                <div class="item-description-specs">{{ $specsLine }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <h6>{{ $itemName }}</h6>
                                        @if($item->bar_code)
                                            <small class="text-muted">Barcode: {{ $item->bar_code }}</small>
                                        @endif
                                    @endif
                                    @if($isTemporaryItem)
                                        <div class="mt-2">
                                            <span class="badge bg-warning text-dark">Temporary item</span>
                                            <a href="{{ route('purchases.temporary.edit', $purchaseItem->item_id) }}" class="btn btn-sm btn-outline-primary ms-1" target="_blank">Convert or Edit</a>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-gray-9 fw-medium text-end">Rs {{ number_format($purchaseItem->rate, 0) }}</td>
                                <td class="text-gray-9 fw-medium text-end">Rs {{ number_format($purchaseItem->discount, 0) }}</td>
                                <td class="text-gray-9 fw-medium text-end">Rs {{ number_format($purchaseItem->total_cost, 0) }}</td>
                                <td class="text-center align-middle">
                                    <div class="purchase-view-verified-wrap d-inline-flex flex-column align-items-center border rounded px-2 py-1" style="border-color: #dee2e6;" data-verified-by-name="{{ $purchaseItem->verifiedBy ? $purchaseItem->verifiedBy->name : '' }}">
                                        <label class="d-inline-flex align-items-center gap-1 mb-0 w-100 justify-content-center" style="cursor: pointer;">
                                            <input type="checkbox" class="form-check-input purchase-view-verified-cb" data-item-id="{{ $purchaseItem->id }}" title="Verified - double check" {{ $purchaseItem->verified_by ? 'checked' : '' }}>
                                            <span class="purchase-view-verified-label text-muted small">Verified</span>
                                            <i class="ti ti-checks text-success purchase-view-verified-icon d-none" style="font-size: 1.1rem;"></i>
                                        </label>
                                    </div>
                                </td>
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
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($purchase->subtotal, 2) }}</p>
                        </div>
                        @if($purchase->discount > 0)
                        <div style="flex: 1; text-align: center; padding: 10px; border-right: 1px solid #dee2e6;">
                            <p class="mb-1">Discount 
                                @php
                                    $discountPercent = $purchase->subtotal > 0 ? ($purchase->discount / $purchase->subtotal) * 100 : 0;
                                @endphp
                                ({{ number_format($discountPercent, 2) }}%)
                            </p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($purchase->discount, 2) }}</p>
                        </div>
                        @else
                        <div style="flex: 1; text-align: center; padding: 10px; border-right: 1px solid #dee2e6;">
                            @if($purchase->order_tax > 0)
                            <p class="mb-1">VAT 
                                @php
                                    $taxPercent = $purchase->subtotal > 0 ? ($purchase->order_tax / $purchase->subtotal) * 100 : 0;
                                @endphp
                                ({{ number_format($taxPercent, 2) }}%)
                            </p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($purchase->order_tax, 2) }}</p>
                            @elseif($purchase->shipping > 0)
                            <p class="mb-1">Shipping</p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($purchase->shipping, 2) }}</p>
                            @else
                            <p class="mb-0">-</p>
                            @endif
                        </div>
                        @endif
                        <div style="flex: 1; text-align: center; padding: 10px;">
                            <p class="mb-1">Total Amount</p>
                            <p class="text-dark fw-medium mb-0" style="font-weight: 600;">Rs {{ number_format($purchase->grand_total, 2) }}</p>
                        </div>
                    </div>
                    @if($purchase->order_tax > 0 && $purchase->discount > 0)
                    <div class="d-flex justify-content-between align-items-center mb-2" style="display: flex; flex-wrap: nowrap;">
                        <div style="flex: 1; text-align: center; padding: 10px; border-right: 1px solid #dee2e6;">
                            <p class="mb-1">VAT 
                                @php
                                    $taxPercent = $purchase->subtotal > 0 ? ($purchase->order_tax / $purchase->subtotal) * 100 : 0;
                                @endphp
                                ({{ number_format($taxPercent, 2) }}%)
                            </p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($purchase->order_tax, 2) }}</p>
                        </div>
                        <div style="flex: 1; text-align: center; padding: 10px;">
                            @if($purchase->shipping > 0)
                            <p class="mb-1">Shipping</p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($purchase->shipping, 2) }}</p>
                            @else
                            <p class="mb-0">-</p>
                            @endif
                        </div>
                    </div>
                    @elseif($purchase->shipping > 0 && $purchase->discount == 0 && $purchase->order_tax == 0)
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <div style="text-align: center; padding: 10px;">
                            <p class="mb-1">Shipping</p>
                            <p class="text-dark fw-medium mb-0">Rs {{ number_format($purchase->shipping, 2) }}</p>
                        </div>
                    </div>
                    @endif
                    <p class="fs-12 text-center mt-2">
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
                <div class="col-md-5 position-relative">
                    <div class="text-end">
                        @if(setting_value('signature'))
                            <img src="{{ setting_value('signature') }}" class="img-fluid" alt="sign" style="max-height: 80px;">
                        @endif
                    </div>
                    <div class="text-end mb-3 authorized-signatory-block">
                        <h6 class="fs-14 fw-medium pe-3 text-dark">{{ setting_value('authorized_person', 'Authorized Signatory') }}</h6>
                        <p class="text-body-secondary mb-0">{{ setting_value('designation', 'Manager') }}</p>
                        @php $stampDt = $all_verified ? $purchase->items->whereNotNull('verified_at')->max('verified_at') : null; if ($stampDt && !$stampDt instanceof \Carbon\Carbon) $stampDt = \Carbon\Carbon::parse($stampDt); @endphp
                        <div id="purchase-authorized-stamp" class="purchase-authorized-stamp {{ (count($verified_user_names) > 0) ? '' : 'd-none' }}" data-datetime="{{ $stampDt ? $stampDt->format('d-M-Y h:i A') : '' }}">
                            <span class="purchase-authorized-stamp-label">Verified by:</span>
                            <span class="purchase-authorized-stamp-name">{{ implode(', ', $verified_user_names) }}</span>
                            <span class="purchase-authorized-stamp-dt">{{ $stampDt ? $stampDt->format('d-M-Y h:i A') : '' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <!-- /Invoices -->

    @if(!$printMode)
    <div class="d-flex justify-content-center align-items-center mb-4 no-print">
        <a href="javascript:void(0);" onclick="window.print()" class="btn btn-primary d-flex justify-content-center align-items-center me-2">
            <i class="ti ti-printer me-2"></i>Print Invoice
        </a>
        <a href="{{ route('purchases.pdf', $purchase->id) }}" class="btn btn-secondary d-flex justify-content-center align-items-center border me-2" target="_blank">
            <i class="ti ti-file-pdf me-2"></i>Download PDF
        </a>
        <a href="{{ route('purchases.convert.to.sale', $purchase->id) }}" class="btn btn-success d-flex justify-content-center align-items-center">
            <i class="ti ti-shopping-cart me-2"></i>Create Sale
        </a>
    </div>
    @endif
</div>
@if($printMode)
<script>
(function() {
    var format = '{{ $printFormat }}';
    if (format === 'thermal') {
        document.body.classList.add('print-thermal-page');
    }
    // Auto-print hata diya: pehle user ko preview dikhao, phir woh Print button dabaye
})();
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    var totalRows = document.querySelectorAll('.purchase-view-verified-cb').length;

    function updateVerifiedFooter() {
        var names = [];
        document.querySelectorAll('.purchase-view-verified-wrap').forEach(function(wrap) {
            var cb = wrap.querySelector('.purchase-view-verified-cb');
            var name = wrap.getAttribute('data-verified-by-name');
            if (cb && cb.checked && name && name.trim() && names.indexOf(name) === -1) names.push(name);
        });
        var checked = document.querySelectorAll('.purchase-view-verified-cb:checked');
        var count = checked ? checked.length : 0;
        var totalRows = document.querySelectorAll('.purchase-view-verified-cb').length;
        var statusEl = document.getElementById('purchase-verification-status');
        if (statusEl) {
            if (count === totalRows && totalRows > 0) {
                statusEl.className = 'fs-10 px-1 rounded bg-success text-white';
                statusEl.innerHTML = '<i class="ti ti-checks"></i> All Verified';
            } else {
                statusEl.className = 'fs-10 px-1 rounded bg-secondary text-white';
                statusEl.textContent = count + '/' + totalRows + ' Verified';
            }
        }
        var stampEl = document.getElementById('purchase-verified-stamp');
        if (stampEl) {
            if (count === totalRows && totalRows > 0) {
                stampEl.classList.remove('d-none');
                var dtEl = stampEl.querySelector('.purchase-verified-stamp-datetime');
                if (dtEl && !dtEl.textContent.trim()) {
                    var d = new Date();
                    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    var dateStr = ('0' + d.getDate()).slice(-2) + '-' + months[d.getMonth()] + '-' + d.getFullYear();
                    var h = d.getHours(), m = d.getMinutes(), am = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    var timeStr = ('0' + h).slice(-2) + ':' + ('0' + m).slice(-2) + ' ' + am;
                    dtEl.textContent = dateStr + ' ' + timeStr;
                }
            } else stampEl.classList.add('d-none');
        }
        var authStampEl = document.getElementById('purchase-authorized-stamp');
        if (authStampEl) {
            if (names.length > 0) {
                authStampEl.classList.remove('d-none');
                var authNameEl = authStampEl.querySelector('.purchase-authorized-stamp-name');
                if (authNameEl) authNameEl.textContent = names.join(', ');
                var authDtEl = authStampEl.querySelector('.purchase-authorized-stamp-dt');
                if (authDtEl && !authDtEl.textContent.trim()) {
                    var d = new Date();
                    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    var dateStr = ('0' + d.getDate()).slice(-2) + '-' + months[d.getMonth()] + '-' + d.getFullYear();
                    var h = d.getHours(), m = d.getMinutes(), am = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    var timeStr = ('0' + h).slice(-2) + ':' + ('0' + m).slice(-2) + ' ' + am;
                    authDtEl.textContent = dateStr + ' ' + timeStr;
                }
            } else authStampEl.classList.add('d-none');
        }
        var listEl = document.getElementById('purchase-verified-by-list');
        if (listEl) listEl.textContent = names.length ? names.join(', ') : '—';
    }

    document.querySelectorAll('.purchase-view-verified-cb').forEach(function(cb) {
        function updateWrap() {
            var wrap = cb.closest('.purchase-view-verified-wrap');
            var label = wrap ? wrap.querySelector('.purchase-view-verified-label') : null;
            var icon = wrap ? wrap.querySelector('.purchase-view-verified-icon') : null;
            if (cb.checked) {
                if (label) label.classList.add('d-none');
                if (icon) icon.classList.remove('d-none');
                if (wrap) { wrap.classList.add('border-success', 'bg-success', 'bg-opacity-10'); wrap.classList.remove('border'); }
            } else {
                if (label) label.classList.remove('d-none');
                if (icon) icon.classList.add('d-none');
                if (wrap) { wrap.classList.remove('border-success', 'bg-success', 'bg-opacity-10'); wrap.classList.add('border'); }
            }
        }
        cb.addEventListener('change', function() {
            updateWrap();
            var itemId = cb.getAttribute('data-item-id');
            var verified = cb.checked;
            var wrap = cb.closest('.purchase-view-verified-wrap');
            fetch('{{ url("purchases/items") }}/' + itemId + '/verify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ verified: verified })
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.success && wrap) {
                    if (verified && data.verified_by_name) wrap.setAttribute('data-verified-by-name', data.verified_by_name);
                    else wrap.removeAttribute('data-verified-by-name');
                }
                updateVerifiedFooter();
            }).catch(function() { cb.checked = !verified; updateWrap(); });
        });
        updateWrap();
    });
    updateVerifiedFooter();
});
</script>

@push('styles')
<style>
    /* Temporary items - blinking until resolved (no blink in print) */
    .has-temporary-items {
        animation: blink-border 1.5s ease-in-out infinite;
    }
    .purchase-item-temporary {
        animation: blink-bg 1.5s ease-in-out infinite;
    }
    @keyframes blink-border {
        0%, 100% { border-color: #ffc107; box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.4); }
        50% { border-color: #fd7e14; box-shadow: 0 0 12px 3px rgba(253, 126, 20, 0.35); }
    }
    @keyframes blink-bg {
        0%, 100% { background-color: rgba(255, 193, 7, 0.2); }
        50% { background-color: rgba(253, 126, 20, 0.3); }
    }
    /* Job Description cell - screenshot style */
    .item-description-cell .item-description-block {
        text-align: center;
        padding: 4px 0;
    }
    .item-description-cell .item-description-model {
        font-size: 1rem;
        font-weight: 700;
        color: #000;
        margin-bottom: 2px;
        line-height: 1.2;
    }
    .item-description-cell .item-description-divider {
        margin: 4px 0;
        border: 0;
        border-top: 1px solid #dee2e6;
        width: 100%;
    }
    .item-description-cell .item-description-specs {
        font-size: 0.875rem;
        font-weight: 700;
        color: #000;
        line-height: 1.2;
    }
    /* Verified stamp overlay - watermark style (perlay / faint) */
    .purchase-verified-stamp {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-18deg);
        padding: 24px 48px;
        border-radius: 8px;
        pointer-events: none;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: transparent;
        border: 3px solid rgba(25, 135, 84, 0.25);
        box-shadow: none;
    }
    .purchase-verified-stamp-text {
        font-size: 3.5rem;
        font-weight: 800;
        letter-spacing: 0.2em;
        color: rgba(25, 135, 84, 0.18);
        line-height: 1;
        text-transform: uppercase;
    }
    .purchase-verified-stamp-datetime {
        font-size: 0.9rem;
        font-weight: 600;
        color: rgba(25, 135, 84, 0.2);
        letter-spacing: 0.04em;
    }
    .card-body {
        position: relative;
        z-index: 2;
    }
    .purchase-verified-by-label { font-size: 0.75rem; }
    .authorized-signatory-block,
    .authorized-signatory-block p,
    .authorized-signatory-block .purchase-verified-by-label { color: #495057 !important; }
    /* Small stamp in Authorized Signatory block - stylish, advanced */
    .purchase-authorized-stamp {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-top: -8px;
        margin-right: 72px;
        padding: 12px 20px 10px;
        border: 2px solid #198754;
        border-radius: 8px;
        transform: rotate(-5deg);
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.06) 0%, rgba(25, 135, 84, 0.02) 100%);
        pointer-events: none;
        box-shadow: 0 2px 12px rgba(25, 135, 84, 0.15), inset 0 1px 0 rgba(255,255,255,0.4);
        position: relative;
    }
    .purchase-authorized-stamp::before {
        content: '';
        position: absolute;
        inset: 3px;
        border: 1px solid rgba(25, 135, 84, 0.25);
        border-radius: 6px;
        pointer-events: none;
    }
    .purchase-authorized-stamp-text {
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: 0.2em;
        color: #198754;
        line-height: 1;
        text-transform: uppercase;
        text-shadow: 0 1px 2px rgba(0,0,0,0.08);
    }
    .purchase-authorized-stamp-dt {
        font-size: 0.6rem;
        font-weight: 600;
        color: rgba(25, 135, 84, 0.75);
        margin-top: 4px;
        line-height: 1.2;
    }
    .purchase-authorized-stamp-label {
        font-size: 0.9rem;
        font-weight: 800;
        color: rgba(25, 135, 84, 0.9);
        margin-top: 6px;
        letter-spacing: 0.08em;
        text-decoration: none;
        border: none;
        box-shadow: none;
    }
    .purchase-authorized-stamp-name {
        font-size: 0.6rem;
        font-weight: 600;
        color: #0d6e42;
        margin-top: 2px;
        line-height: 1.2;
    }
    /* Print mode: hide layout when opened for print (e.g. from Save & Print) */
    .print-only-content .main-wrapper > .page-wrapper > *:not(.content),
    .print-only-content .sidebar,
    .print-only-content .header {
        display: none !important;
    }
    .print-only-content .content {
        padding: 10px !important;
    }

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
        .d-flex.justify-content-center,
        .no-print {
            display: none !important;
        }
        /* Hide Verified column when printing */
        .table thead th:last-child,
        .table tbody td:last-child {
            display: none !important;
        }
        .print-preview-bar {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
        }
        .has-temporary-items,
        .purchase-item-temporary {
            animation: none !important;
        }
        .card-body {
            padding: 10px !important;
        }
        .card-body img[alt="logo"] {
            width: 80px !important;
            height: auto !important;
        }
        .row.justify-content-between { margin-bottom: 8px !important; }
        .row.justify-content-between h5 { font-size: 14px !important; margin-bottom: 2px !important; }
        .row.justify-content-between p { font-size: 10px !important; margin-bottom: 2px !important; }
        .row.border-bottom { margin-bottom: 8px !important; padding-bottom: 8px !important; }
        .row.border-bottom h4 { font-size: 13px !important; margin-bottom: 2px !important; }
        .row.border-bottom p { font-size: 10px !important; margin-bottom: 2px !important; }
        .table { font-size: 10px !important; margin-bottom: 8px !important; }
        .table th, .table td { padding: 4px 8px !important; font-size: 10px !important; }
        .table h6 { font-size: 10px !important; margin-bottom: 2px !important; }
        .table small { font-size: 8px !important; }
        .item-description-model { font-size: 9px !important; }
        .item-description-specs { font-size: 8px !important; }
        .row.border-bottom.mb-3:last-of-type { margin-bottom: 8px !important; }
        .row.border-bottom.mb-3 p { font-size: 10px !important; margin-bottom: 2px !important; }
        .row.border-bottom.mb-3 h5 { font-size: 12px !important; margin-bottom: 2px !important; }
        .row.align-items-center { margin-bottom: 8px !important; }
        .row.align-items-center h6 { font-size: 11px !important; margin-bottom: 2px !important; }
        .row.align-items-center p { font-size: 9px !important; margin-bottom: 2px !important; }
        .row.align-items-center img { max-height: 50px !important; }
        .text-center { margin-top: 8px !important; }
        .text-center img { width: 80px !important; }
        .text-center p { font-size: 9px !important; margin-bottom: 2px !important; }
        .mb-3 { margin-bottom: 8px !important; }
        .mb-2 { margin-bottom: 4px !important; }
        .mb-1 { margin-bottom: 2px !important; }
        .card-body { max-height: none; overflow: visible !important; }

        /* A4 print */
        .print-a4 .card-body,
        .card-body {
            max-width: 100%;
        }
        @page {
            margin: 8mm !important;
            size: A4;
        }
    }

    /* Thermal (80mm) print layout */
    @media print {
        .print-thermal.card,
        .card.print-thermal {
            max-width: 80mm !important;
            width: 80mm !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        .print-thermal .card-body {
            padding: 4px 6px !important;
            font-size: 9px !important;
        }
        .print-thermal .card-body img[alt="logo"] {
            width: 50px !important;
        }
        .print-thermal .row.justify-content-between h5 { font-size: 10px !important; }
        .print-thermal .row.justify-content-between p { font-size: 8px !important; }
        .print-thermal .row.border-bottom h4 { font-size: 9px !important; }
        .print-thermal .row.border-bottom p { font-size: 8px !important; }
        .print-thermal .table { font-size: 8px !important; }
        .print-thermal .table th,
        .print-thermal .table td { padding: 2px 4px !important; font-size: 8px !important; }
        .print-thermal .table h6 { font-size: 8px !important; }
        .print-thermal .table small { font-size: 6px !important; }
        .print-thermal .item-description-model { font-size: 7px !important; }
        .print-thermal .item-description-specs { font-size: 6px !important; }
        .print-thermal .row.border-bottom.mb-3 p,
        .print-thermal .row.border-bottom.mb-3 h5 { font-size: 8px !important; }
        .print-thermal .row.align-items-center h6,
        .print-thermal .row.align-items-center p { font-size: 7px !important; }
        .print-thermal .text-center p { font-size: 7px !important; }
        .print-thermal .text-center img { width: 50px !important; }
        body.print-thermal-page .content {
            max-width: 80mm !important;
            margin: 0 auto !important;
        }
        body.print-thermal-page {
            margin: 0 !important;
            padding: 0 !important;
            width: 80mm !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
    }
</style>
@if(isset($print_format) && $print_format === 'thermal')
<style>
    @media print {
        @page {
            size: 80mm auto;
            margin: 3mm;
        }
    }
</style>
@endif
@endpush

@endsection
