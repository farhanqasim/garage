@extends('layouts.app')
@section('title', 'Item Price List')
@push('styles')
<style>
    /* Print view: hide nav, filters, buttons; show only title and table */
    .price-list-int { font-weight: bold; }
    .price-list-decimal { color: #6c757d; font-weight: inherit; }
    .price-list-battery-edit { cursor: pointer; }
    .battery-edit-val { color: #212529; font-weight: bold; }
    .battery-edit-val-product { font-size: 1.75em; font-weight: 700; background-color: #000; color: #fff; padding: 2px 6px; display: inline-block; }
    .price-list-battery-edit:hover .battery-edit-val { text-decoration: underline; }
    .price-list-pct-dropdown.price-list-pct-negative,
    #priceListBulkPctHeader.price-list-pct-negative { color: #dc3545 !important; }
    .price-list-pct-dropdown option,
    #priceListBulkPctHeader option { color: #212529; }
    .price-list-pct-dropdown option.negative-pct,
    #priceListBulkPctHeader option.negative-pct { color: #dc3545; }
    @media print {
        body * { visibility: hidden; }
        .price-list-print-area,
        .price-list-print-area * { visibility: visible; }
        .price-list-print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .price-list-no-print { display: none !important; }
        .price-list-print-only { display: block !important; }
        .price-list-print-header { margin-bottom: 1rem; }
        .price-list-print-area table { width: 100%; }
        .price-list-print-area thead th { border-bottom: 2px solid #333; }
        .price-list-print-area tbody tr { border-bottom: 1px solid #ddd; }
    }
</style>
@endpush
@section('content')
<div class="content">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3 price-list-no-print">
        <div>
            <h2 class="fw-bold mb-1">Item Price List</h2>
            <p class="text-muted mb-0 small">View all items with prices. Filter by category.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('all.items') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> All Items
            </a>
            <button type="button" class="btn btn-success btn-sm" id="priceListWhatsAppBtn" title="Share via WhatsApp">
                <i class="ti ti-brand-whatsapp me-1"></i> WhatsApp
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="priceListPrintBtn" title="Print">
                <i class="ti ti-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <div class="card price-list-print-area">
        <div class="card-header price-list-no-print">
            <form method="GET" action="{{ route('items.price.list') }}" id="priceListFilterForm">
                <div class="row mb-4 g-3">
                    <div class="col-md-3">
                        <label for="typeFilter" class="form-label fw-bold mb-2">Filter by Type:</label>
                        <select name="type" id="typeFilter" class="form-select">
                            <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>All Items</option>
                            @canany(['view_items', 'view_parts'])<option value="parts" {{ request('type') === 'parts' ? 'selected' : '' }}>Parts</option>@endcanany
                            @canany(['view_items', 'view_filters'])<option value="filters" {{ request('type') === 'filters' ? 'selected' : '' }}>Filters</option>@endcanany
                            @canany(['view_items', 'view_break_pad'])<option value="breakpad" {{ request('type') === 'breakpad' ? 'selected' : '' }}>Break Pad</option>@endcanany
                            @canany(['view_items', 'view_oil'])<option value="oil" {{ request('type') === 'oil' ? 'selected' : '' }}>Oil</option>@endcanany
                            @canany(['view_items', 'view_battery'])<option value="battery" {{ request('type') === 'battery' ? 'selected' : '' }}>Battery</option>@endcanany
                            @canany(['view_items', 'view_scrap'])<option value="scrap" {{ request('type') === 'scrap' ? 'selected' : '' }}>Scrap</option>@endcanany
                            @canany(['view_items', 'view_services'])<option value="services" {{ request('type') === 'services' ? 'selected' : '' }}>Services</option>@endcanany
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="company_id" class="form-label fw-bold mb-2">Filter by Company</label>
                        <select name="company_id" id="company_id" class="form-select">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        @if($categories->isEmpty())
                            <label for="technology_id" class="form-label fw-bold mb-2">Filter by Technology</label>
                            <select name="technology_id" id="technology_id" class="form-select">
                                <option value="">All Technologies</option>
                                @foreach($technologies as $tech)
                                    <option value="{{ $tech->id }}" {{ request('technology_id') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <label for="category_id" class="form-label fw-bold mb-2">Filter by Category</label>
                            <select name="category_id" id="category_id" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    @if(request('category_id') || request('company_id') || (request('type') && request('type') !== 'all'))
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('items.price.list') }}" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
        @php
            $filtersApplied = request('category_id') || request('company_id') || (request('type') && request('type') !== 'all') || request('technology_id');
        @endphp
        <div class="card-body p-0">
            <div class="d-none mb-3 price-list-print-only">
                <h3 class="fw-bold mb-1">Item Price List</h3>
                <p class="text-muted small mb-0">{{ $currentBranchName ?? 'All branches' }} — {{ now()->format('d M Y H:i') }}</p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead class="thead-primary bg-light">
                        <tr>
                            <th class="text-nowrap">#</th>
                            <th class="text-start">Product Name</th>
                            <th class="text-start">GST / R.TAX</th>
                            <th class="text-end">Retail Price (Rs.)</th>
                            <th class="text-end">
                                <span class="d-block">Amount at 0%</span>
                                @if($filtersApplied)
                                <span class="small fw-normal mt-1 d-inline-block">Bulk: </span>
                                <select class="form-select form-select-sm d-inline-block price-list-no-print" id="priceListBulkPctHeader" style="width: auto; min-width: 5rem;" title="Apply % to all filtered items">
                                    <option value="">—</option>
                                    @for($p = -25; $p <= 25; $p++)
                                        <option value="{{ $p }}" class="{{ $p < 0 ? 'negative-pct' : '' }}">{{ $p }}%</option>
                                    @endfor
                                </select>
                                @endif
                            </th>
                            <th class="text-start">Last updated</th>
                            <th class="text-center">Action</th>
                            @if(request('type', 'all') === 'all')
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-end">Cost (Rs.)</th>
                            <th class="text-end">Sale Price (Rs.)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            @php
                                $rawName = $item->short_disc ?? $item->pro_dis ?? '';
                                $itemName = trim(strip_tags((string) $rawName));
                                if ($itemName === '' && $item->partnumber_item) {
                                    $itemName = $item->partnumber_item->name ?? $item->bar_code ?? 'Please Select';
                                }
                                $itemName = $itemName === '' ? ($item->bar_code ?? 'Please Select') : $itemName;
                                $partNo = $item->partnumber_item ? ($item->partnumber_item->name ?? '') : '';
                                $barcode = $item->bar_code ?? '';
                                $catName = $item->category ? $item->category->name : '';
                                $unitName = $item->unit_item ? ($item->unit_item->name ?? $item->unit) : ($item->unit ?? '');
                                $isBattery = ($item->type ?? '') === 'battery';
                                if ($isBattery) {
                                    $v = $item->volt_item ? trim((string)($item->volt_item->name ?? '')) : '';
                                    $voltDisplay = $v !== '' ? (preg_match('/\d*\s*V$/i', $v) ? $v : $v . 'V') : '-';
                                    $p = $item->plate_item ? trim((string)($item->plate_item->name ?? '')) : '';
                                    $plateDisplay = $p !== '' ? (preg_match('/\d*\s*PL$/i', $p) ? $p : $p . 'PL') : '-';
                                    $a = $item->amphors_item ? trim((string)($item->amphors_item->name ?? '')) : '';
                                    $ampDisplay = $a !== '' ? (preg_match('/\d*\s*AH$/i', $a) ? $a : $a . 'AH') : '-';
                                    $c = $item->cca_item ? trim((string)($item->cca_item->name ?? '')) : '';
                                    $ccaDisplay = $c !== '' ? (preg_match('/\d*\s*CCA$/i', $c) ? $c : $c . 'CCA') : '-';
                                    $line2 = implode('.', array_filter([$voltDisplay !== '-' ? $voltDisplay : '', $plateDisplay !== '-' ? $plateDisplay : '', $ampDisplay !== '-' ? $ampDisplay : ''], function($x) { return $x !== ''; }));
                                    if ($line2 === '') $line2 = '-';
                                }
                            @endphp
                            <tr data-item-id="{{ $item->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-medium align-top text-start">
                                    @if($isBattery)
                                        <div class="price-list-battery-fields small">
                                            <div class="mb-1 price-list-battery-edit" data-field="p_id" data-label="Product:" data-value="{{ $item->p_id ?? '' }}" data-display="{{ $item->product_item ? trim((string)($item->product_item->name ?? '')) : '' }}" title="Click to edit"><span class="battery-edit-val battery-edit-val-product">{{ $item->product_item ? $item->product_item->name : '—' }}</span></div>
                                            <div class="mb-1 price-list-battery-edit" data-field="company_id" data-label="Company:" data-value="{{ $item->company_id ?? '' }}" data-display="{{ $item->company_item ? trim((string)($item->company_item->name ?? '')) : '' }}" title="Click to edit"><span class="text-muted">Company:</span> <span class="battery-edit-val">{{ $item->company_item ? $item->company_item->name : '—' }}</span></div>
                                            <div class="mb-1 price-list-battery-edit" data-field="plat_id" data-label="Plates:" data-value="{{ $item->plat_id ?? '' }}" data-display="{{ isset($plateDisplay) && $plateDisplay !== '-' ? $plateDisplay : '' }}" title="Click to edit"><span class="text-muted">Plates:</span> <span class="battery-edit-val">{{ isset($plateDisplay) && $plateDisplay !== '-' ? $plateDisplay : '—' }}</span></div>
                                            <div class="mb-1 price-list-battery-edit" data-field="amphors" data-label="Ampere (AH):" data-value="{{ $item->amphors ?? '' }}" data-display="{{ isset($ampDisplay) && $ampDisplay !== '-' ? $ampDisplay : '' }}" title="Click to edit"><span class="text-muted">Ampere (AH):</span> <span class="battery-edit-val">{{ isset($ampDisplay) && $ampDisplay !== '-' ? $ampDisplay : '—' }}</span></div>
                                            <div class="mb-1 price-list-battery-edit" data-field="volt" data-label="Voltage:" data-value="{{ $item->volt ?? '' }}" data-display="{{ isset($voltDisplay) ? $voltDisplay : '' }}" title="Click to edit"><span class="text-muted">Voltage:</span> <span class="battery-edit-val">{{ isset($voltDisplay) && $voltDisplay !== '-' ? $voltDisplay : '—' }}</span></div>
                                            <div class="mb-1 price-list-battery-edit" data-field="cca" data-label="CCA:" data-value="{{ $item->cca ?? '' }}" data-display="{{ isset($ccaDisplay) && $ccaDisplay !== '-' ? $ccaDisplay : '' }}" title="Click to edit"><span class="text-muted">CCA:</span> <span class="battery-edit-val">{{ isset($ccaDisplay) && $ccaDisplay !== '-' ? $ccaDisplay : '—' }}</span></div>
                                            <div class="mb-1 price-list-battery-edit" data-field="gorup" data-label="Group:" data-value="{{ $item->gorup ?? '' }}" data-display="{{ $item->group_item ? trim((string)($item->group_item->name ?? '')) : '' }}" title="Click to edit"><span class="text-muted">Group:</span> <span class="battery-edit-val">{{ $item->group_item ? $item->group_item->name : '—' }}</span></div>
                                        </div>
                                    @else
                                        <div class="d-block">{{ $itemName }}</div>
                                    @endif
                                </td>
                                <td class="text-start align-top" title="Click to edit">
                                    <div class="price-list-editable d-block" data-field="tax_percentage" data-value="{{ (isset($item->tax_percentage) && $item->tax_percentage !== '' && (float)$item->tax_percentage > 0) ? (float)$item->tax_percentage : 18 }}"><span class="text-muted small">GST :</span> <span class="price-list-int">{{ (isset($item->tax_percentage) && $item->tax_percentage !== '' && (float)$item->tax_percentage > 0) ? (int)round((float)$item->tax_percentage) : 18 }}</span>%</div>
                                    @php $rTaxVal = isset($item->r_tax_percentage) && $item->r_tax_percentage !== '' && (float)$item->r_tax_percentage >= 0 ? (float)$item->r_tax_percentage : 0.05; @endphp
                                    <div class="price-list-editable d-block mt-1" data-field="r_tax_percentage" data-value="{{ $rTaxVal }}"><span class="text-muted small">R.TAX :</span> <span class="price-list-int">{{ number_format($rTaxVal, 2) }}</span>%</div>
                                </td>
                                <td class="text-end price-list-editable align-top" data-field="retail_price" data-value="{{ $item->retail_price !== null && $item->retail_price !== '' ? (float)$item->retail_price : '' }}" title="Click to edit">
                                    @php
                                        $rp = $item->retail_price;
                                        if ($rp !== null && $rp !== '' && (float)$rp > 0) {
                                            $s = number_format((float)$rp, 2);
                                            $dp = strrpos($s, '.');
                                            echo $dp !== false ? '<span class="price-list-int">' . substr($s, 0, $dp) . '</span><span class="price-list-decimal">' . substr($s, $dp) . '</span>' : $s;
                                        } else {
                                            echo '-';
                                        }
                                    @endphp
                                </td>
                                @php
                                    $retail = $item->retail_price !== null && $item->retail_price !== '' ? (float)$item->retail_price : 0;
                                    $gstPct = (isset($item->tax_percentage) && $item->tax_percentage !== '' && (float)$item->tax_percentage > 0) ? (float)$item->tax_percentage : 18;
                                    $rTaxPct = isset($item->r_tax_percentage) && $item->r_tax_percentage !== '' && (float)$item->r_tax_percentage >= 0 ? (float)$item->r_tax_percentage : 0.05;
                                    if ($rTaxPct == 0.05) $rTaxPct = 0.5;
                                    $priceAfterGst = $retail > 0 ? $retail + round($retail * $gstPct / 100) : 0;
                                    $rTaxAmt = $priceAfterGst > 0 ? round($priceAfterGst * $rTaxPct / 100) : 0;
                                    $baseAmount = $priceAfterGst + $rTaxAmt;
                                    $adjPct = isset($item->amount_adjustment_pct) && $item->amount_adjustment_pct !== '' && $item->amount_adjustment_pct !== null ? (float)$item->amount_adjustment_pct : 0;
                                    $amountAt0 = $baseAmount > 0 && $adjPct != 0 ? $baseAmount - ($retail * $adjPct/100) : $baseAmount;
                                @endphp
                                <td class="text-end align-top price-list-amount-at-0" data-field="amount_at_0" data-amount-adjustment-pct="{{ $adjPct }}">
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <span class="price-list-amount-value">
                                            @if($amountAt0 > 0)
                                                @php
                                                    $s = number_format($amountAt0, 2);
                                                    $dp = strrpos($s, '.');
                                                    echo $dp !== false ? '<span class="price-list-int">' . substr($s, 0, $dp) . '</span><span class="price-list-decimal">' . substr($s, $dp) . '</span>' : $s;
                                                @endphp
                                            @else
                                                -
                                            @endif
                                        </span>
                                        @if($filtersApplied)
                                        <select class="form-select form-select-sm price-list-pct-dropdown price-list-no-print {{ $adjPct < 0 ? 'price-list-pct-negative' : '' }}" style="width: auto; min-width: 4.5rem;" title="Apply % to this row and all below (retail unchanged)">
                                            @for($p = -25; $p <= 25; $p++)
                                                <option value="{{ $p }}" class="{{ $p < 0 ? 'negative-pct' : '' }}" {{ (float)$p === (float)$adjPct ? 'selected' : '' }}>{{ $p }}%</option>
                                            @endfor
                                        </select>
                                        @endif
                                    </div>
                                </td>
                                <td class="small price-list-last-updated text-start align-top">
                                    <div class="text-muted">{{ $item->last_updated_at ? $item->last_updated_at->format('d/m/Y H:i') : '-' }}</div>
                                    <div class="mt-1">{{ $item->priceUpdatedBranch ? $item->priceUpdatedBranch->branch_name : ($item->updated_by_user && $item->updated_by_user->branch ? $item->updated_by_user->branch->branch_name : ($currentBranchName ?? '-')) }}</div>
                                    <div class="mt-1">{{ $item->updated_by_user ? $item->updated_by_user->name : '-' }}</div>
                                </td>
                                <td class="text-center align-top">
                                    @canany(['update_items', 'update_parts', 'update_filters', 'update_break_pad', 'update_oil', 'update_battery', 'update_scrap', 'update_services'])
                                    <a href="{{ route('item.edit', $item->id) }}" class="btn btn-sm btn-outline-primary price-list-no-print" title="Edit product">Edit</a>
                                    @endcanany
                                </td>
                                @if(request('type', 'all') === 'all')
                                <td><span class="badge bg-light text-dark">{{ $item->category ? $item->category->name : '-' }}</span></td>
                                <td>{{ $item->unit_item ? $item->unit_item->name : ($item->unit ?? '-') }}</td>
                                <td class="text-end price-list-editable" data-field="total_price" data-value="{{ $item->total_price ?? '' }}" title="Click to edit">
                                    @php
                                        $tp = $item->total_price ?? 0;
                                        $s = number_format((float)$tp, 2);
                                        $dp = strrpos($s, '.');
                                        echo $dp !== false ? '<span class="price-list-int">' . substr($s, 0, $dp) . '</span><span class="price-list-decimal">' . substr($s, $dp) . '</span>' : $s;
                                    @endphp
                                </td>
                                <td class="text-end price-list-editable" data-field="sale_price" data-value="{{ $item->sale_price ?? '' }}" title="Click to edit">
                                    @php
                                        $sp = $item->sale_price ?? 0;
                                        $s = number_format((float)$sp, 2);
                                        $dp = strrpos($s, '.');
                                        echo $dp !== false ? '<span class="price-list-int">' . substr($s, 0, $dp) . '</span><span class="price-list-decimal">' . substr($s, $dp) . '</span>' : $s;
                                    @endphp
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ request('type', 'all') !== 'all' ? 7 : 11 }}" class="text-center py-5 text-muted">
                                    <i class="ti ti-package fs-1 d-block mb-2"></i>
                                    No items found. @if(request('category_id') || (request('type') && request('type') !== 'all')) Try changing the type or category filter. @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Share Modal -->
<div class="modal fade price-list-no-print" id="priceListWhatsAppModal" tabindex="-1" aria-labelledby="priceListWhatsAppModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="priceListWhatsAppModalLabel">
                    <i class="ti ti-brand-whatsapp me-2"></i>Share Price List via WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="priceListWhatsAppForm">
                    <div class="mb-3">
                        <label for="priceListWaPhone" class="form-label">Mobile number with country code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">+</span>
                            <input type="text" class="form-control" id="priceListWaPhone" name="phone" placeholder="923001234567" required maxlength="15">
                        </div>
                        <small class="text-muted">e.g. 923001234567 (no + sign)</small>
                    </div>
                    <div id="priceListWaSuccess" class="alert alert-success d-none">
                        <strong>WhatsApp opened.</strong> Download the PDF below and attach it in the chat, then press Send.
                        <div class="mt-2">
                            <a id="priceListWaPdfLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary">Download PDF</a>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="priceListWaSendBtn">
                    <i class="ti ti-send me-1"></i> Generate PDF & Open WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var platesOptions = @json(isset($plates) ? $plates : []);
    var amphorsOptions = @json(isset($amphors) ? $amphors : []);
    var groupsOptions = @json(isset($groups) ? $groups : []);
    var companiesOptions = @json(isset($companies) ? $companies : []);
    var productsOptions = @json(isset($products) ? $products : []);
    var partnumbersOptions = @json(isset($partnumbers) ? $partnumbers : []);
    var voltsOptions = @json(isset($volts) ? $volts : []);
    var ccasOptions = @json(isset($ccas) ? $ccas : []);
    // Print button: open print dialog
    var printBtn = document.getElementById('priceListPrintBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() { window.print(); });
    }
    // WhatsApp button: open modal to enter number and send PDF
    var whatsappBtn = document.getElementById('priceListWhatsAppBtn');
    var waModal = document.getElementById('priceListWhatsAppModal');
    if (whatsappBtn && waModal) {
        whatsappBtn.addEventListener('click', function() {
            document.getElementById('priceListWaSuccess').classList.add('d-none');
            document.getElementById('priceListWaPdfLink').href = '#';
            document.getElementById('priceListWaPhone').value = '';
            var modal = new bootstrap.Modal(waModal);
            modal.show();
        });
    }
    var waSendBtn = document.getElementById('priceListWaSendBtn');
    if (waSendBtn) {
        waSendBtn.addEventListener('click', function() {
            var phoneInput = document.getElementById('priceListWaPhone');
            var phone = (phoneInput && phoneInput.value) ? String(phoneInput.value).replace(/\D/g, '') : '';
            if (!phone || phone.length < 10) {
                alert('Please enter a valid mobile number with country code.');
                return;
            }
            var btn = waSendBtn;
            var origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader me-1"></i> Generating PDF...';
            var params = new URLSearchParams(window.location.search);
            var payload = {
                _token: token,
                phone_number: phone,
                type: params.get('type') || '',
                category_id: params.get('category_id') || '',
                company_id: params.get('company_id') || '',
                technology_id: params.get('technology_id') || ''
            };
            fetch('{{ route("items.price.list.whatsapp.pdf") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(payload)
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (res.success) {
                    var waUrl = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(res.message);
                    window.open(waUrl, '_blank', 'noopener,noreferrer');
                    var successEl = document.getElementById('priceListWaSuccess');
                    var linkEl = document.getElementById('priceListWaPdfLink');
                    if (res.pdf_url) {
                        linkEl.href = res.pdf_url;
                        linkEl.textContent = 'Download PDF';
                        successEl.classList.remove('d-none');
                    }
                } else {
                    alert(res.message || 'Failed to generate PDF.');
                }
            }).catch(function() {
                alert('Request failed. Please try again.');
            }).finally(function() {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            });
        });
    }
    // Auto-submit filter form when any filter changes (Apply button removed)
    var filterForm = document.getElementById('priceListFilterForm');
    if (filterForm) {
        [document.getElementById('typeFilter'),
         document.getElementById('category_id'),
         document.getElementById('company_id'),
         document.getElementById('technology_id')].forEach(function(el) {
            if (el) el.addEventListener('change', function() { filterForm.submit(); });
        });
    }

    var baseUrl = '{{ route("items.price.list.bulk.update") }}';
    var token = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function formatPriceWithDecimal(numStr) {
        if (!numStr || numStr === '-') return '-';
        var parts = String(numStr).split('.');
        var intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        var decPart = parts.length > 1 ? '.' + parts[1] : '.00';
        return '<span class="price-list-int">' + intPart + '</span><span class="price-list-decimal">' + decPart + '</span>';
    }
    function formatPctNoDecimal(value) {
        var v = parseFloat(value);
        if (isNaN(v)) return '0%';
        return '<span class="price-list-int">' + Math.round(v) + '</span>%';
    }

    function setPctDropdownRed($sel) {
        if (!$sel || !$sel.length) return;
        var v = $sel.val();
        var n = v !== '' && v !== null ? parseFloat(v) : 0;
        if (n < 0) $sel.addClass('price-list-pct-negative'); else $sel.removeClass('price-list-pct-negative');
    }
    function updateRowAmountValue($row) {
        var retailCell = $row.find('[data-field="retail_price"]');
        var gstCell = $row.find('[data-field="tax_percentage"]');
        var rTaxCell = $row.find('[data-field="r_tax_percentage"]');
        var retail = parseFloat(retailCell.attr('data-value')) || 0;
        var gst = parseFloat(gstCell.attr('data-value')) || 0;
        var rTax = rTaxCell.length ? (parseFloat(rTaxCell.attr('data-value')) || 0.05) : 0.05;
        if (rTax === 0.05) rTax = 0.5;
        var priceAfterGst = retail > 0 ? retail + Math.round(retail * gst / 100) : 0;
        var rTaxAmt = priceAfterGst > 0 ? Math.round(priceAfterGst * rTax / 100) : 0;
        var baseAmount = priceAfterGst + rTaxAmt;
        var $pctSel = $row.find('.price-list-pct-dropdown');
        var pctVal = $pctSel.length ? $pctSel.val() : '';
        var adjPct = pctVal !== '' && pctVal !== null ? parseFloat(pctVal) : 0;
        var amountAt0 = baseAmount > 0 && adjPct !== 0 ? baseAmount - (retail * adjPct/100) : baseAmount;
        $row.find('.price-list-amount-value').html(amountAt0 > 0 ? formatPriceWithDecimal(amountAt0.toFixed(2)) : '-');
        $row.attr('data-amount-adjustment-pct', adjPct);
        setPctDropdownRed($pctSel);
    }

    function applyPercentToRows(rows, pct) {
        if (!rows || rows.length === 0) return;
        var pctNum = parseFloat(pct);
        if (isNaN(pctNum)) return;
        var items = [];
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var $row = $(row);
            var itemId = $row.attr('data-item-id');
            if (!itemId) continue;
            items.push({ id: parseInt(itemId, 10), amount_adjustment_pct: pctNum });
        }
        if (items.length === 0) return;
        fetch(baseUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ items: items, _token: token })
        }).then(function(r) { return r.json(); }).then(function(res) {
            if (res.success) {
                for (var j = 0; j < items.length; j++) {
                    var it = items[j];
                    var $row = $('.price-list-print-area tbody tr[data-item-id="' + it.id + '"]');
                    if ($row.length) {
                        var $sel = $row.find('.price-list-pct-dropdown');
                        $sel.val(it.amount_adjustment_pct);
                        setPctDropdownRed($sel);
                        $row.attr('data-amount-adjustment-pct', it.amount_adjustment_pct);
                        updateRowAmountValue($row);
                    }
                }
                if (res.updated_items && res.updated_items.length) {
                    for (var k = 0; k < res.updated_items.length; k++) {
                        var info = res.updated_items[k];
                        var $r = $('.price-list-print-area tbody tr[data-item-id="' + info.id + '"]');
                        var $target = $r.find('.price-list-last-updated');
                        if ($target.length && info.last_updated_at) {
                            $target.html('<div class="text-muted">' + (info.last_updated_at || '-') + '</div><div class="mt-1">' + (info.branch_name || '-') + '</div><div class="mt-1">' + (info.user_name || '-') + '</div>');
                        }
                    }
                }
                alert((res.updated != null ? res.updated : items.length) + ' item(s) updated. Retail price unchanged; percentage saved.');
            } else {
                alert(res.message || 'Update failed.');
            }
        }).catch(function() { alert('Request failed.'); });
    }

    var bulkPctHeader = document.getElementById('priceListBulkPctHeader');
    if (bulkPctHeader) {
        bulkPctHeader.addEventListener('change', function() {
            var pct = this.value;
            if (pct === '') return;
            setPctDropdownRed($(this));
            var rows = document.querySelectorAll('.price-list-print-area tbody tr[data-item-id]');
            applyPercentToRows(Array.prototype.slice.call(rows), parseFloat(pct));
        });
    }
    $(document).on('change', '.price-list-pct-dropdown', function() {
        if (this.id === 'priceListBulkPctHeader') return;
        var pct = $(this).val();
        if (pct === '' || pct === null) return;
        var bulkHeader = document.getElementById('priceListBulkPctHeader');
        if (bulkHeader) bulkHeader.value = '';
        setPctDropdownRed($(this));
        var $row = $(this).closest('tr');
        var $rows = $row.add($row.nextAll('tr[data-item-id]'));
        applyPercentToRows($rows.toArray(), parseFloat(pct));
    });

    function savePriceListCell(itemId, field, value, $cell, displayText) {
        var payload = { items: [{ id: itemId }], _token: token };
        payload.items[0][field] = value === '' || value === null ? null : (field === 'tax_percentage' || field === 'r_tax_percentage' ? parseFloat(value) : (field === 'retail_price' || field === 'total_price' || field === 'sale_price' ? parseFloat(value) : value));
        fetch(baseUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(function(r) { return r.json(); }).then(function(res) {
            if (res.success) {
                var display;
                if (field === 'plat_id' || field === 'amphors') {
                    display = displayText !== undefined && displayText !== '' ? displayText : '-';
                    $cell.removeClass('editing').html(display).attr('data-value', value === '' || value === null ? '' : value).attr('data-display', displayText !== undefined ? displayText : '-');
                } else if (field === 'tax_percentage') {
                    display = value === '' || value === null ? '0%' : formatPctNoDecimal(value);
                    $cell.removeClass('editing').html('<span class="text-muted small">GST :</span> ' + display).attr('data-value', value);
                } else if (field === 'r_tax_percentage') {
                    var rTax = value === '' || value === null ? 0.05 : parseFloat(value);
                    if (isNaN(rTax)) rTax = 0.05;
                    $cell.removeClass('editing').html('<span class="text-muted small">R.TAX :</span> <span class="price-list-int">' + rTax.toFixed(2) + '</span>%').attr('data-value', rTax);
                } else {
                    display = value === '' || value === null ? '-' : formatPriceWithDecimal(parseFloat(value).toFixed(2));
                    $cell.removeClass('editing').html(display).attr('data-value', value);
                }
                if (field === 'retail_price' || field === 'tax_percentage' || field === 'r_tax_percentage') {
                    var row = $cell.closest('tr');
                    updateRowAmountValue(row);
                }
                if (res.updated_items && res.updated_items.length > 0) {
                    var row = $cell.closest('tr');
                    var info = res.updated_items[0];
                    var $target = row.find('.price-list-last-updated');
                    if ($target.length) {
                        $target.html('<div class="text-muted">' + (info.last_updated_at || '-') + '</div><div class="mt-1">' + (info.branch_name || '-') + '</div><div class="mt-1">' + (info.user_name || '-') + '</div>');
                    }
                }
            } else {
                var prevDisplay = $cell.attr('data-display');
                var prevVal = $cell.attr('data-value');
                if (field === 'plat_id' || field === 'amphors') {
                    $cell.removeClass('editing').html(prevDisplay ? '<span>' + (prevDisplay === '—' ? '<span class="text-muted">—</span>' : prevDisplay) + '</span>' : '—').attr('data-value', prevVal || '').attr('title', 'Click to edit');
                } else if (field === 'tax_percentage') {
                    $cell.removeClass('editing').html('<span class="text-muted small">GST :</span> ' + (prevVal !== '' ? formatPctNoDecimal(prevVal) : '18%')).attr('title', 'Click to edit');
                } else if (field === 'r_tax_percentage') {
                    var rTax = prevVal !== '' && prevVal !== null ? parseFloat(prevVal) : 0.05;
                    if (isNaN(rTax)) rTax = 0.05;
                    $cell.removeClass('editing').html('<span class="text-muted small">R.TAX :</span> <span class="price-list-int">' + rTax.toFixed(2) + '</span>%').attr('data-value', rTax).attr('title', 'Click to edit');
                } else {
                    $cell.removeClass('editing').html(prevVal !== '' && prevVal !== null ? formatPriceWithDecimal(parseFloat(prevVal).toFixed(2)) : '-').attr('title', 'Click to edit');
                }
                if (res.message) alert(res.message);
            }
        }).catch(function() {
            var field = $cell.attr('data-field');
            var prevDisplay = $cell.attr('data-display');
            var prevVal = $cell.attr('data-value');
            if (field === 'plat_id' || field === 'amphors') {
                $cell.removeClass('editing').html(prevDisplay ? '<span>' + (prevDisplay === '—' ? '<span class="text-muted">—</span>' : prevDisplay) + '</span>' : '—').attr('title', 'Click to edit');
            } else if (field === 'tax_percentage') {
                $cell.removeClass('editing').html('<span class="text-muted small">GST :</span> ' + (prevVal !== '' ? formatPctNoDecimal(prevVal) : '18%')).attr('title', 'Click to edit');
            } else if (field === 'r_tax_percentage') {
                var rTax = prevVal !== '' && prevVal !== null ? parseFloat(prevVal) : 0.05;
                if (isNaN(rTax)) rTax = 0.05;
                $cell.removeClass('editing').html('<span class="text-muted small">R.TAX :</span> <span class="price-list-int">' + rTax.toFixed(2) + '</span>%').attr('data-value', rTax).attr('title', 'Click to edit');
            } else {
                $cell.removeClass('editing').html(prevVal !== '' && prevVal !== null ? formatPriceWithDecimal(parseFloat(prevVal).toFixed(2)) : '-').attr('title', 'Click to edit');
            }
            alert('Failed to save.');
        });
    }

    $(document).on('click', '.price-list-editable', function() {
        var $cell = $(this);
        if ($cell.hasClass('editing')) return;
        if ($cell.hasClass('price-list-editable-select')) return;
        var field = $cell.data('field');
        var value = $cell.data('value');
        var isPct = field === 'tax_percentage' || field === 'r_tax_percentage';
        var input = $('<input type="number" class="form-control form-control-sm text-end" style="width:80px;min-width:60px" step="0.01" min="0" max="100">');
        input.val(value === '' || value === null ? '' : value);
        $cell.addClass('editing').html('').append(input);
        input.focus().select();
        function commit() {
            var v = input.val();
            var itemId = $cell.closest('tr').data('item-id');
            if (!itemId) return;
            input.off('blur.pledit keydown.pledit');
            savePriceListCell(itemId, field, v, $cell);
        }
        input.on('blur.pledit', function() { commit(); });
        input.on('keydown.pledit', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); commit(); }
            if (e.key === 'Escape') {
                e.preventDefault();
                var prev = $cell.attr('data-value');
                var isPct = $cell.attr('data-field') === 'tax_percentage';
                var isRTax = $cell.attr('data-field') === 'r_tax_percentage';
                var restore;
                if (isRTax) {
                    var rTax = prev === '' || prev === null ? 0.05 : parseFloat(prev);
                    if (isNaN(rTax)) rTax = 0.05;
                    restore = '<span class="text-muted small">R.TAX :</span> <span class="price-list-int">' + rTax.toFixed(2) + '</span>%';
                } else {
                    restore = prev === '' || prev === null ? (isPct ? '<span class="text-muted small">GST :</span> 18%' : '-') : (isPct ? '<span class="text-muted small">GST :</span> ' + formatPctNoDecimal(prev) : formatPriceWithDecimal(parseFloat(prev).toFixed(2)));
                }
                $cell.removeClass('editing').html(restore).attr('title', 'Click to edit');
                input.off('blur.pledit keydown.pledit');
            }
        });
    });

    $(document).on('click', '.price-list-editable-select', function(e) {
        var $cell = $(this);
        if ($cell.hasClass('editing')) return;
        var field = $cell.data('field');
        var value = ($cell.attr('data-value') || '').toString();
        var options = field === 'plat_id' ? platesOptions : amphorsOptions;
        var $select = $('<select class="form-select form-select-sm" style="min-width:90px">');
        $select.append($('<option value="">—</option>'));
        options.forEach(function(opt) {
            var v = (opt.id || opt).toString();
            var label = (opt.name || opt).toString();
            $select.append($('<option value="' + v + '">' + label.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</option>'));
        });
        if (value) $select.val(value);
        $cell.addClass('editing').html('').append($select);
        $select.focus();
        function commit() {
            var v = $select.val();
            var itemId = $cell.closest('tr').data('item-id');
            if (!itemId) return;
            var displayText = v ? ($select.find('option:selected').text() || '') : '';
            $select.off('change.pledit blur.pledit keydown.pledit');
            savePriceListCell(itemId, field, v || null, $cell, displayText);
        }
        $select.on('change.pledit', function() { commit(); });
        $select.on('blur.pledit', function() { commit(); });
        $select.on('keydown.pledit', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                var prev = $cell.attr('data-display') || $cell.attr('data-value') || '—';
                $cell.removeClass('editing').html('<span>' + (prev === '—' ? '<span class="text-muted">—</span>' : prev) + '</span>').attr('title', 'Click to edit');
                $select.off('change.pledit blur.pledit keydown.pledit');
            }
        });
    });

    var batteryFieldToNameKey = { gorup: 'gorup_name', company_id: 'company_name', p_id: 'p_name', volt: 'volt_name', plat_id: 'plat_name', amphors: 'amphors_name', cca: 'cca_name' };

    function saveBatteryField(itemId, field, nameKey, typedValue, $div) {
        var payload = { items: [{ id: itemId }], _token: token };
        payload.items[0][nameKey] = typedValue === '' || typedValue === null ? '' : String(typedValue).trim();
        var label = $div.data('label') || '';
        var field = $div.attr('data-field');
        var display = (typedValue !== undefined && typedValue !== null && String(typedValue).trim() !== '' ? String(typedValue).trim() : '—');
        fetch(baseUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        }).then(function(r) { return r.json(); }).then(function(res) {
            $div.removeClass('editing');
            $div.attr('data-display', display);
            if (field === 'p_id') {
                $div.html('<span class="battery-edit-val battery-edit-val-product">' + display + '</span>').attr('title', 'Click to edit');
            } else {
                $div.html('<span class="text-muted">' + label + '</span> <span class="battery-edit-val">' + display + '</span>').attr('title', 'Click to edit');
            }
        }).catch(function() {
            $div.removeClass('editing');
            var prev = $div.attr('data-display') || '—';
            if (field === 'p_id') {
                $div.html('<span class="battery-edit-val battery-edit-val-product">' + prev + '</span>').attr('title', 'Click to edit');
            } else {
                $div.html('<span class="text-muted">' + label + '</span> <span class="battery-edit-val">' + prev + '</span>').attr('title', 'Click to edit');
            }
        });
    }

    $(document).on('click', '.price-list-battery-edit', function(e) {
        var $div = $(this);
        if ($div.hasClass('editing')) return;
        e.stopPropagation();
        var field = $div.data('field');
        var nameKey = batteryFieldToNameKey[field];
        if (!nameKey) return;
        var currentVal = ($div.attr('data-display') || '').toString();
        if (currentVal === '—') currentVal = '';
        var label = $div.data('label') || '';
        var $input = $('<input type="text" class="form-control form-control-sm" style="min-width:120px" placeholder="—">');
        $input.val(currentVal);
        $div.addClass('editing').html('').append($input);
        $input.focus().select();
        function commit() {
            var typed = $input.val();
            var itemId = $div.closest('tr').data('item-id');
            if (!itemId) return;
            $input.off('blur.bedit keydown.bedit');
            saveBatteryField(itemId, field, nameKey, typed, $div);
        }
        $input.on('blur.bedit', function() { commit(); });
        $input.on('keydown.bedit', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); commit(); }
            if (e.key === 'Escape') {
                e.preventDefault();
                var prev = $div.attr('data-display') || '—';
                if (field === 'p_id') {
                    $div.removeClass('editing').html('<span class="battery-edit-val battery-edit-val-product">' + prev + '</span>').attr('title', 'Click to edit');
                } else {
                    $div.removeClass('editing').html('<span class="text-muted">' + label + '</span> <span class="battery-edit-val">' + prev + '</span>').attr('title', 'Click to edit');
                }
                $input.off('blur.bedit keydown.bedit');
            }
        });
    });
});
</script>
@endpush
@endsection
