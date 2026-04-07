@extends('layouts.app')
@section('title', 'Item Stock Report')
@section('content')
@php
    $printMode = $print_mode ?? false;
@endphp
<div class="content {{ $printMode ? 'print-only-content' : '' }}">
    @if($printMode)
    <div class="print-preview-bar no-print d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-3 rounded shadow-sm" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff;">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-printer fs-24"></i>
            <div>
                <h6 class="mb-0 fw-bold">Print View – Item Stock Report</h6>
                <p class="mb-0 small opacity-90">This page will print as you see it. Click <strong>Print</strong> below to send to printer.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('items.stock.report', request()->query()) }}" class="btn btn-light btn-sm">Back to Report</a>
            <button type="button" class="btn btn-light btn-sm" onclick="window.print();">
                <i class="ti ti-printer me-1"></i>Print
            </button>
        </div>
    </div>
    @endif

    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3 {{ $printMode ? 'no-print' : '' }}">
        <div>
            <h2 class="fw-bold mb-1">Item Stock Report</h2>
            <p class="text-muted mb-0 small">
                Detailed stock in/out by item type, category, branch and user within a date range.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!$printMode)
            <a href="{{ route('items.stock.report', array_merge(request()->query(), ['print' => 1])) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-printer me-1"></i> Print View
            </a>
            <a href="{{ route('items.stock.report.a4', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-file-report me-1"></i> A4 Stock Report
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Dashboard
            </a>
            @endif
        </div>
    </div>

    <div class="card mb-3 no-print">
        <div class="card-header">
            <form method="GET" action="{{ route('items.stock.report') }}" class="row g-3 align-items-end" id="stock-report-filters">
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Date From</label>
                    <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Date To</label>
                    <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($filters['branch_id'] == $branch->id)>
                                {{ $branch->branch_name }} @if($branch->branch_code) ({{ $branch->branch_code }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Warehouse</label>
                    <select name="warehouse_id" class="form-select">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses ?? [] as $wh)
                            <option value="{{ $wh->id }}" @selected(($filters['warehouse_id'] ?? '') == $wh->id)>
                                {{ $wh->warehouse_name }} @if($wh->warehouse_code) ({{ $wh->warehouse_code }}) @endif
                                @if($wh->branch) — {{ $wh->branch->branch_name }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">User (Sales)</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected($filters['user_id'] == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Item Type</label>
                    <select name="type" class="form-select">
                        <option value="all" @selected(($filters['type'] ?? 'all') === 'all')>All</option>
                        <option value="parts" @selected(($filters['type'] ?? '') === 'parts')>Parts</option>
                        <option value="filters" @selected(($filters['type'] ?? '') === 'filters')>Filters</option>
                        <option value="breakpad" @selected(($filters['type'] ?? '') === 'breakpad')>Break Pad</option>
                        <option value="oil" @selected(($filters['type'] ?? '') === 'oil')>Oil</option>
                        <option value="battery" @selected(($filters['type'] ?? '') === 'battery')>Battery</option>
                        <option value="scrap" @selected(($filters['type'] ?? '') === 'scrap')>Scrap</option>
                        <option value="services" @selected(($filters['type'] ?? '') === 'services')>Services</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($filters['category_id'] == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('items.stock.report') }}" class="btn btn-outline-secondary">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Current Stock by Product &amp; Branch</h5>
            <span class="badge bg-secondary">{{ count($summaryWithDetails ?? []) }} groups</span>
        </div>
        <div class="card-body p-0">
            <p class="small text-muted px-3 pt-2 mb-0">Click a row to show or hide warehouse-wise stock.</p>
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0 stock-report-table" id="stock-report-table">
                    <colgroup>
                        <col class="col-toggle">
                        <col class="col-index">
                        <col class="col-product">
                        <col class="col-branch">
                        <col class="col-qty">
                        <col class="col-required-qty">
                        <col class="col-can">
                        <col class="col-liter">
                        <col class="col-ml">
                    </colgroup>
                    <thead class="thead-primary bg-light">
                        <tr>
                            <th class="text-center" aria-label="Toggle"></th>
                            <th class="text-center">#</th>
                            <th class="text-start">Product</th>
                            <th class="text-start">Branch with Warehouse</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Required Qty</th>
                            <th class="text-end">Can</th>
                            <th class="text-end">Liter</th>
                            <th class="text-end">ML</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryWithDetails ?? [] as $groupIndex => $group)
                            @php
                                $sr = $group['summary'];
                                $details = $group['details'];
                                $sortedDetails = collect($details)->sortBy(function($d) {
                                    $name = strtolower($d['warehouse'] ?? '');
                                    $isDisplay = (strpos($name, 'display') !== false) ? 0 : 1;
                                    return $isDisplay . $name;
                                })->values()->all();
                            @endphp
                            <tr class="stock-summary-row align-middle" role="button" tabindex="0"
                                data-item-id="{{ $sr['item_id'] }}" data-branch-id="{{ $sr['branch_id'] ?? '' }}"
                                data-details-count="{{ count($details) }}">
                                <td class="stock-toggle-cell text-center">
                                    @if(count($details) > 0)
                                        <i class="ti ti-chevron-down stock-chevron text-primary"></i>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $groupIndex + 1 }}</td>
                                <td class="text-start">
                                    @if(!empty($sr['oil_display']))
                                        @php
                                            $od = $sr['oil_display'];
                                            $oilHasAny = ($od['quality'] ?? '') !== '' || ($od['grade'] ?? '') !== '' || ($od['level'] ?? '') !== '' || ($od['company'] ?? '') !== '' || ($od['unit_volume'] ?? '') !== '' || ($od['unit_type'] ?? '') !== '' || ($od['bar_code'] ?? '') !== '';
                                        @endphp
                                        @if($oilHasAny)
                                        <div class="stock-report-oil-detail small">
                                            @if(($od['quality'] ?? '') !== '' || ($od['grade'] ?? '') !== '' || ($od['level'] ?? '') !== '')
                                                <div class="mb-0">
                                                    @if(($od['quality'] ?? '') !== '')<span class="bg-warning px-1 rounded">{{ $od['quality'] }}</span>@endif
                                                    @if(($od['quality'] ?? '') !== '' && (($od['grade'] ?? '') !== '' || ($od['level'] ?? '') !== '')) <span class="text-muted">•</span> @endif
                                                    @if(($od['grade'] ?? '') !== '')<span class="text-primary fw-medium">{{ $od['grade'] }}</span>@endif
                                                    @if(($od['grade'] ?? '') !== '' && ($od['level'] ?? '') !== '') <span class="text-muted">•</span> @endif
                                                    @if(($od['level'] ?? '') !== '')<span>{{ $od['level'] }}</span>@endif
                                                    @if(($od['quality'] ?? '') !== '' || ($od['grade'] ?? '') !== '' || ($od['level'] ?? '') !== '') <span class="text-muted">•</span> @endif
                                                </div>
                                            @endif
                                            @if(($od['company'] ?? '') !== '' || ($od['unit_volume'] ?? '') !== '')
                                                <div class="mb-0">{{ $od['company'] ?? '' }}{{ ($od['company'] ?? '') !== '' && ($od['unit_volume'] ?? '') !== '' ? ' • ' : '' }}{{ $od['unit_volume'] ?? '' }}{{ (($od['company'] ?? '') !== '' || ($od['unit_volume'] ?? '') !== '') ? ' •' : '' }}</div>
                                            @endif
                                            @if(($od['unit_type'] ?? '') !== '')
                                                <div class="mb-0 fw-medium">{{ $od['unit_type'] }}</div>
                                            @endif
                                            @if(($od['bar_code'] ?? '') !== '')
                                                <div class="mt-1"><span class="text-primary fw-medium">{{ $od['bar_code'] }}</span></div>
                                            @endif
                                        </div>
                                        @else
                                            {{ $sr['product_name'] ?? '—' }}
                                        @endif
                                    @elseif(!empty($sr['battery_display']))
                                        @php
                                            $bd = $sr['battery_display'];
                                            $hasAny = ($bd['product_name'] ?? '') !== '' || ($bd['group'] ?? '') !== '' || ($bd['plate'] ?? '') !== '' || ($bd['amp'] ?? '') !== '' || ($bd['company'] ?? '') !== '' || ($bd['volt'] ?? '') !== '' || ($bd['cca'] ?? '') !== '' || ($bd['bar_code'] ?? '') !== '';
                                        @endphp
                                        @if($hasAny)
                                            <div class="stock-report-battery-detail small">
                                                @if(($bd['product_name'] ?? '') !== '')
                                                    <div class="mb-0"><span class="battery-edit-val battery-edit-val-product fw-semibold">{{ $bd['product_name'] }}</span></div>
                                                @endif
                                                @if(($bd['group'] ?? '') !== '' || ($bd['plate'] ?? '') !== '')
                                                    <div class="mb-0">
                                                        @if(($bd['plate'] ?? '') !== '')<span>{{ $bd['plate'] }}</span>@endif
                                                        @if(($bd['plate'] ?? '') !== '') <span class="text-muted">•</span> @endif
                                                    </div>
                                                @endif
                                                @if(($bd['amp'] ?? '') !== '' || ($bd['company'] ?? '') !== '')
                                                    <div class="mb-0">{{ $bd['amp'] ?? '' }}{{ ($bd['amp'] ?? '') !== '' && ($bd['company'] ?? '') !== '' ? ' • ' : '' }}{{ $bd['company'] ?? '' }}</div>
                                                @endif
                                                @if(($bd['volt'] ?? '') !== '' || ($bd['cca'] ?? '') !== '')
                                                    <div class="mb-0 text-muted" style="font-size: 0.85em;">{{ $bd['volt'] ?? '' }}{{ ($bd['volt'] ?? '') !== '' && ($bd['cca'] ?? '') !== '' ? ' • ' : '' }}{{ $bd['cca'] ?? '' }}</div>
                                                @endif
                                                @if(($bd['bar_code'] ?? '') !== '')
                                                    <div class="mt-1"><span class="text-primary fw-medium">{{ $bd['bar_code'] }}</span></div>
                                                @endif
                                            </div>
                                        @else
                                            {{ $sr['product_name'] ?? '—' }}
                                        @endif
                                    @else
                                        {{ $sr['product_name'] ?? '—' }}
                                    @endif
                                </td>
                                <td class="text-start">
                                    <div>{{ $sr['branch'] }}</div>
                                    @if(count($details) > 0)
                                        <div class="text-muted small mt-1">
                                            @foreach($sortedDetails as $d)
                                                {{ $d['warehouse'] }} ({{ number_format($d['quantity'], 0) }})@if(!$loop->last) &nbsp; @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">
                                    @if(!empty($sr['oil_display']) && (($sr['total_can'] ?? 0) > 0 || ($sr['total_liter'] ?? 0) > 0 || ($sr['total_ml'] ?? 0) > 0))
                                        @if(!empty($sr['is_very_low_stock']))<span class="badge bg-danger me-1" title="Quantity is below Low Stock">Very Low Stock</span>@endif
                                        <div class="small @if(empty($sr['is_very_low_stock']))text-muted @endif mt-0 lh-sm">{{ $sr['total_can'] ?? 0 }} can · {{ $sr['total_liter'] ?? 0 }} L · {{ $sr['total_ml'] ?? 0 }} ml</div>
                                    @else
                                        {{ number_format($sr['total_quantity'], 0) }}@if(!empty($sr['is_very_low_stock'])) <span class="badge bg-danger ms-1" title="Quantity is below Low Stock">Very Low Stock</span>@endif
                                    @endif
                                </td>
                                <td class="text-end">{{ $sr['required_quantity'] !== null ? number_format($sr['required_quantity'], 0) : '—' }}</td>
                                <td class="text-end">@if(($sr['total_can'] ?? 0) > 0){{ $sr['total_can'] }}@else — @endif</td>
                                <td class="text-end">@if(($sr['total_liter'] ?? 0) > 0){{ $sr['total_liter'] }}@else — @endif</td>
                                <td class="text-end">@if(($sr['total_ml'] ?? 0) > 0){{ $sr['total_ml'] }}@else — @endif</td>
                            </tr>
                            @foreach($sortedDetails as $wr)
                                <tr class="stock-detail-row table-light" style="display: none;"
                                    data-item-id="{{ $sr['item_id'] }}" data-branch-id="{{ $sr['branch_id'] ?? '' }}">
                                    <td class="text-center"></td>
                                    <td class="text-center text-muted small">↳</td>
                                    <td class="text-start"></td>
                                    <td class="text-start ps-3 small">{{ $wr['warehouse'] }}@if(!empty($wr['warehouse_code'])) ({{ $wr['warehouse_code'] }})@endif</td>
                                    <td class="text-end">
                                        {{ number_format($wr['quantity'], 2) }}
                                        @php
                                            $hasOilBreakdown = (isset($wr['qty_can']) && $wr['qty_can'] !== null) || (isset($wr['qty_liter']) && $wr['qty_liter'] !== null) || (isset($wr['qty_ml']) && $wr['qty_ml'] !== null);
                                            $oilParts = array_filter([
                                                isset($wr['qty_can']) && $wr['qty_can'] !== null ? $wr['qty_can'] . ' can' : null,
                                                isset($wr['qty_liter']) && $wr['qty_liter'] !== null ? ($wr['qty_liter'] . ' L') : null,
                                                isset($wr['qty_ml']) && $wr['qty_ml'] !== null ? ($wr['qty_ml'] . ' ml') : null,
                                            ]);
                                        @endphp
                                        @if($hasOilBreakdown && count($oilParts) > 0)
                                            <div class="small text-muted mt-0 lh-sm">{{ implode(' · ', $oilParts) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">@php
                                        $avail = (float)($wr['available_quantity'] ?? $wr['quantity'] ?? 0);
                                        $maintain = $wr['item_m_stock'] ?? null;
                                        $req = ($maintain !== null && (float)$maintain > 0) ? max(0, (float)$maintain - $avail) : null;
                                        echo $req !== null ? number_format($req, 0) : '—';
                                    @endphp</td>
                                    <td class="text-end">@if(isset($wr['qty_can']) && $wr['qty_can'] !== null){{ $wr['qty_can'] }}@else — @endif</td>
                                    <td class="text-end">@if(isset($wr['qty_liter']) && $wr['qty_liter'] !== null){{ $wr['qty_liter'] }}@else — @endif</td>
                                    <td class="text-end">@if(isset($wr['qty_ml']) && $wr['qty_ml'] !== null){{ $wr['qty_ml'] }}@else — @endif</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No stock found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stock-report-battery-detail { line-height: 1.35; }
    .stock-report-battery-detail .battery-edit-val-product { font-weight: 700 !important; font-size: 1.1rem !important; }
    .stock-report-oil-detail { line-height: 1.35; }

    /* Stock report table: balanced columns, equal padding, alignment */
    .stock-report-table {
        width: 100%;
        table-layout: fixed;
        min-width: 640px;
    }
    .stock-report-table .col-toggle { width: 2.25rem; }
    .stock-report-table .col-index { width: 2.5rem; }
    .stock-report-table .col-product { width: 17%; }
    .stock-report-table .col-branch { width: 22%; }
    .stock-report-table .col-qty { width: 11%; }
    .stock-report-table .col-required-qty { width: 10%; }
    .stock-report-table .col-can { width: 11%; }
    .stock-report-table .col-liter { width: 10%; }
    .stock-report-table .col-ml { width: 10%; }

    .stock-report-table th,
    .stock-report-table td {
        padding: 0.5rem 0.75rem;
        vertical-align: middle;
    }
    .stock-report-table th {
        font-weight: 600;
        white-space: nowrap;
    }
    .stock-report-table td:nth-child(3),
    .stock-report-table td:nth-child(4) {
        overflow-wrap: break-word;
        word-break: break-word;
    }

    @media (max-width: 768px) {
        .stock-report-table { font-size: 0.9rem; }
        .stock-report-table th,
        .stock-report-table td { padding: 0.4rem 0.5rem; }
    }

    /* Print mode: when printing, show only report content */
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
            font-size: 10px !important;
        }
        .print-preview-bar,
        .no-print {
            display: none !important;
            visibility: hidden !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('stock-report-filters');
    if (form) {
        form.querySelectorAll('select, input[name="from"], input[name="to"]').forEach(function(el) {
            el.addEventListener('change', function() {
                form.submit();
            });
        });
    }

    var table = document.getElementById('stock-report-table');
    if (!table) return;
    table.querySelectorAll('.stock-summary-row').forEach(function(row) {
        row.addEventListener('click', function() {
            var itemId = this.getAttribute('data-item-id');
            var branchId = this.getAttribute('data-branch-id') || '';
            var chevron = this.querySelector('.stock-chevron');
            var details = table.querySelectorAll('.stock-detail-row[data-item-id="' + itemId + '"][data-branch-id="' + branchId + '"]');
            if (details.length === 0) return;
            var isHidden = details[0].style.display === 'none';
            details.forEach(function(tr) {
                tr.style.display = isHidden ? '' : 'none';
            });
            if (chevron) {
                chevron.classList.toggle('ti-chevron-down', isHidden);
                chevron.classList.toggle('ti-chevron-right', !isHidden);
            }
        });
    });
});
</script>
@endpush

