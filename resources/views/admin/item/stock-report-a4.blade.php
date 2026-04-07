@extends('layouts.app')
@section('title', 'Stock Report (A4 Print)')
@section('content')
@php
    $printMode = $print_mode ?? false;
@endphp
<div class="content stock-report-a4 {{ $printMode ? 'print-only-content' : '' }}">
    @if($printMode)
    <div class="print-preview-bar no-print d-flex align-items-center justify-content-between flex-wrap gap-2 p-3 mb-3 rounded shadow-sm" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: #fff;">
        <div class="d-flex align-items-center gap-2">
            <i class="ti ti-printer fs-24"></i>
            <div>
                <h6 class="mb-0 fw-bold">A4 Stock Report – Print Preview</h6>
                <p class="mb-0 small opacity-90">This layout is optimized for A4. Click <strong>Print</strong> to send to printer.</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('items.stock.report.a4', request()->query()) }}" class="btn btn-light btn-sm">Back to Report</a>
            <button type="button" class="btn btn-light btn-sm" onclick="window.print();">
                <i class="ti ti-printer me-1"></i>Print
            </button>
        </div>
    </div>
    @endif

    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3 {{ $printMode ? 'no-print' : '' }}">
        <div>
            <h2 class="fw-bold mb-1">Stock Report (A4)</h2>
            <p class="text-muted mb-0 small">
                Item name, category, company, branch, current quantity, min stock, low-stock highlight, purchase qty needed, canister details, and recommended vendors.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(!$printMode)
            <a href="{{ route('items.stock.report.a4', array_merge(request()->query(), ['print' => 1])) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-printer me-1"></i> Print View
            </a>
            <a href="{{ route('items.stock.report') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Stock Report
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">Dashboard</a>
            @endif
        </div>
    </div>

    <div class="card mb-3 no-print">
        <div class="card-header">
            <form method="GET" action="{{ route('items.stock.report.a4') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>
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
                            <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary me-2"><i class="ti ti-filter me-1"></i> Apply</button>
                    <a href="{{ route('items.stock.report.a4') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card stock-report-a4-card">
        <div class="card-body p-0">
            <div class="report-print-header d-none print-show">
                <h4 class="mb-0">Stock Report</h4>
                <p class="text-muted small mb-0">As at {{ now()->format('d/m/Y H:i') }} — Branch: {{ $filters['branch_id'] ? (optional($branches->firstWhere('id', $filters['branch_id']))->branch_name ?? 'All') : 'All' }} | Warehouse: {{ $filters['warehouse_id'] ? (optional($warehouses->firstWhere('id', $filters['warehouse_id']))->warehouse_name ?? 'All') : 'All' }}</p>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 stock-report-a4-table">
                    <thead>
                        <tr class="table-dark">
                            <th class="text-center" style="width: 2%;">#</th>
                            <th style="width: 14%;">Item Name</th>
                            <th style="width: 8%;">Category</th>
                            <th style="width: 8%;">Company</th>
                            <th style="width: 8%;">Branch</th>
                            <th style="width: 8%;">Warehouse</th>
                            <th class="text-end" style="width: 5%;">Current Qty</th>
                            <th class="text-end" style="width: 5%;">Min Required</th>
                            <th style="width: 12%;">Canister (L/can &amp; remaining)</th>
                            <th class="text-center" style="width: 5%;">Status</th>
                            <th class="text-end" style="width: 5%;">Qty to Purchase</th>
                            <th style="width: 20%;">Recommended Vendors (Name, Rate)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows ?? [] as $index => $r)
                            <tr class="{{ ($r['is_low_stock'] ?? false) ? 'table-warning' : '' }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $r['product_name'] }}</strong>
                                    @if(!empty($r['part_number']))
                                        <br><small class="text-muted">{{ $r['part_number'] }}</small>
                                    @endif
                                </td>
                                <td>{{ $r['category'] ?? '—' }}</td>
                                <td>{{ $r['company'] ?? '—' }}</td>
                                <td>{{ $r['branch'] ?? '—' }}</td>
                                <td>{{ $r['warehouse'] ?? '—' }}</td>
                                <td class="text-end fw-bold">{{ number_format($r['current_qty'], 2) }}</td>
                                <td class="text-end">{{ $r['min_stock'] !== null ? number_format($r['min_stock'], 2) : '—' }}</td>
                                <td class="small">
                                    @if(!empty($r['canister_detail']))
                                        {{ $r['canister_detail'] }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($r['is_low_stock'] ?? false)
                                        <span class="badge bg-danger">Low</span>
                                    @else
                                        <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(isset($r['qty_to_purchase']) && $r['qty_to_purchase'] > 0)
                                        <strong>{{ number_format($r['qty_to_purchase'], 2) }}</strong>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small">
                                    @forelse($r['vendors'] ?? [] as $v)
                                        <div>{{ $v['name'] ?? '—' }} — {{ number_format($v['rate'], 2) }}</div>
                                    @empty
                                        —
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">No stock found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(count($rows ?? []) > 0 && !$printMode)
    <p class="text-muted small mt-2 no-print">
        Generated on {{ now()->format('d/m/Y H:i') }}. Use <strong>Print View</strong> for A4-optimized printing.
    </p>
    @endif
</div>
@endsection

@push('styles')
<style>
    .stock-report-a4-table { font-size: 0.8rem; }
    .stock-report-a4-table th,
    .stock-report-a4-table td { padding: 0.4rem 0.5rem; vertical-align: middle; }
    .stock-report-a4-table tbody tr.table-warning { background-color: #fff3cd !important; }
    .report-print-header.print-show { display: none; }
    @media print {
        .report-print-header.print-show { display: block !important; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #dee2e6; }
        body * { visibility: hidden; }
        .content.stock-report-a4, .content.stock-report-a4 * { visibility: visible; }
        .content.stock-report-a4 {
            position: absolute; left: 0; top: 0; width: 100%;
            font-size: 9px !important;
            padding: 0 !important;
        }
        .print-preview-bar, .no-print { display: none !important; visibility: hidden !important; }
        .stock-report-a4-card { border: none !important; box-shadow: none !important; }
        .stock-report-a4-table { font-size: 8px !important; page-break-inside: auto; }
        .stock-report-a4-table tr { page-break-inside: avoid; page-break-after: auto; }
        .stock-report-a4-table th,
        .stock-report-a4-table td { padding: 2px 4px !important; }
        .stock-report-a4-table thead { display: table-header-group; }
        @page { size: A4; margin: 12mm; }
    }
</style>
@endpush
