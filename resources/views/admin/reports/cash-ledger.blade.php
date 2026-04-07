@extends('layouts.app')
@section('title', 'Cash Ledger Report')

@php
    $typeBadgeClass = function ($code) {
        return match ($code) {
            'sale_cash' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
            'purchase_cash' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'cash_payment' => 'bg-primary-subtle text-primary-emphasis border border-primary-subtle',
            'cash_account_in' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
            'cash_account_out' => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
            'wallet' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
            default => 'bg-light text-dark border',
        };
    };
@endphp

@section('content')
<div class="content cash-ledger-report-page">
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h2 class="mb-1">Cash Ledger Report</h2>
            <p class="text-muted mb-0 small">Physical cash payments, cash-on-hand account movements, and internal wallet entries.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 no-print">
            <a href="{{ route('admin.reports.cash-ledger', request()->query()) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-refresh me-1"></i>Refresh</a>
            <a href="{{ route('admin.reports.cash-ledger', array_merge(request()->except('page'), ['preset' => 'this_month'])) }}" class="btn btn-outline-secondary btn-sm">Clear filters</a>
            <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()"><i class="ti ti-printer me-1"></i>Print</button>
            <div class="btn-group">
                <button type="button" class="btn btn-dark btn-sm" id="cash-ledger-thermal-print-btn"><i class="ti ti-receipt-2 me-1"></i>Thermal Print</button>
                <button type="button" class="btn btn-dark btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle thermal options</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button type="button" class="dropdown-item js-cash-ledger-thermal" data-paper="auto">Auto (saved setting)</button></li>
                    <li><button type="button" class="dropdown-item js-cash-ledger-thermal" data-paper="80">Thermal 80mm</button></li>
                    <li><button type="button" class="dropdown-item js-cash-ledger-thermal" data-paper="58">Thermal 58mm</button></li>
                </ul>
            </div>
            <a href="{{ route('admin.reports.cash-ledger.export-pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm"><i class="ti ti-file-type-pdf me-1"></i>PDF</a>
            <a href="{{ route('admin.reports.cash-ledger.export-excel', request()->query()) }}" class="btn btn-outline-success btn-sm"><i class="ti ti-file-spreadsheet me-1"></i>Excel</a>
        </div>
    </div>

    <form method="get" action="{{ route('admin.reports.cash-ledger') }}" id="cash-ledger-filters" class="card border-0 shadow-sm mb-3 no-print">
        <div class="card-body">
            <input type="hidden" name="preset" id="preset-field" value="{{ $preset }}">
            <div class="row g-2 align-items-end">
                <div class="col-12">
                    <label class="form-label small text-muted mb-1">Quick range</label>
                    <div class="btn-group flex-wrap" role="group">
                        <a href="{{ route('admin.reports.cash-ledger', array_merge(request()->except(['date_from','date_to','page']), ['preset' => 'today'])) }}" class="btn btn-sm btn-outline-secondary {{ $preset === 'today' ? 'active' : '' }}">Today</a>
                        <a href="{{ route('admin.reports.cash-ledger', array_merge(request()->except(['date_from','date_to','page']), ['preset' => 'yesterday'])) }}" class="btn btn-sm btn-outline-secondary {{ $preset === 'yesterday' ? 'active' : '' }}">Yesterday</a>
                        <a href="{{ route('admin.reports.cash-ledger', array_merge(request()->except(['date_from','date_to','page']), ['preset' => 'this_week'])) }}" class="btn btn-sm btn-outline-secondary {{ $preset === 'this_week' ? 'active' : '' }}">This week</a>
                        <a href="{{ route('admin.reports.cash-ledger', array_merge(request()->except(['date_from','date_to','page']), ['preset' => 'this_month'])) }}" class="btn btn-sm btn-outline-secondary {{ $preset === 'this_month' ? 'active' : '' }}">This month</a>
                        <a href="{{ route('admin.reports.cash-ledger', array_merge(request()->except(['date_from','date_to','page']), ['preset' => 'last_month'])) }}" class="btn btn-sm btn-outline-secondary {{ $preset === 'last_month' ? 'active' : '' }}">Last month</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (string)($filters['branch_id'] ?? '') === (string)$b->id ? 'selected' : '' }}>{{ $b->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (string)($filters['user_id'] ?? '') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All types</option>
                        @foreach($typeLabels as $code => $label)
                            <option value="{{ $code }}" {{ ($filters['type'] ?? '') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search (ref / notes)</label>
                    <input type="text" name="q" class="form-control form-control-sm" value="{{ $filters['q'] ?? '' }}" placeholder="Reference, remarks…">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Party (customer / supplier)</label>
                    <input type="text" name="party" class="form-control form-control-sm" value="{{ $filters['party'] ?? '' }}" placeholder="Name…">
                </div>
                <div class="col-md-4 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-filter me-1"></i>Apply</button>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Opening cash balance</div>
                    <div class="fs-5 fw-semibold">Rs {{ number_format($summary['opening_balance'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
                <div class="card-body py-3">
                    <div class="text-muted small">Total cash in</div>
                    <div class="fs-5 fw-semibold text-success">Rs {{ number_format($summary['total_cash_in'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-3">
                <div class="card-body py-3">
                    <div class="text-muted small">Total cash out</div>
                    <div class="fs-5 fw-semibold text-danger">Rs {{ number_format($summary['total_cash_out'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">Net cash flow</div>
                    <div class="fs-5 fw-semibold {{ $summary['net_cash_flow'] >= 0 ? 'text-success' : 'text-danger' }}">Rs {{ number_format($summary['net_cash_flow'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body py-3">
                    <div class="text-muted small">Closing cash balance</div>
                    <div class="fs-5 fw-bold {{ $summary['closing_balance'] < 0 ? 'text-danger' : 'text-dark' }}">Rs {{ number_format($summary['closing_balance'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive cash-ledger-table-wrap">
                <table class="table table-hover table-striped align-middle mb-0 cash-ledger-table">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Voucher / Ref</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Party</th>
                            <th>Created by</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-end">Running bal.</th>
                            <th>Branch</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr class="cash-ledger-row cursor-pointer"
                                data-source="{{ $row['source'] }}"
                                data-id="{{ $row['source_id'] }}"
                                style="cursor:pointer;">
                                <td class="text-nowrap">{{ $row['date'] }}</td>
                                <td class="text-nowrap small">{{ $row['time'] }}</td>
                                <td><code class="small">{{ $row['voucher_ref'] }}</code></td>
                                <td>
                                    <span class="badge rounded-pill {{ $typeBadgeClass($row['transaction_type'] ?? '') }}">{{ $row['transaction_type_label'] }}</span>
                                </td>
                                <td class="small">{{ \Illuminate\Support\Str::limit($row['description'] ?? '—', 80) }}</td>
                                <td class="small">{{ $row['party'] }}</td>
                                <td class="small">{{ $row['created_by'] }}</td>
                                <td class="text-end text-success fw-medium">{{ $row['debit'] > 0 ? 'Rs '.number_format($row['debit'], 2) : '—' }}</td>
                                <td class="text-end text-danger fw-medium">{{ $row['credit'] > 0 ? 'Rs '.number_format($row['credit'], 2) : '—' }}</td>
                                <td class="text-end fw-semibold {{ ($row['running_balance'] ?? 0) < 0 ? 'text-danger' : '' }}">Rs {{ number_format($row['running_balance'] ?? 0, 2) }}</td>
                                <td class="small">{{ $row['branch'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">No records found for this range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rows->hasPages())
            <div class="card-footer no-print">
                {{ $rows->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="cashLedgerDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cashLedgerDetailTitle">Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cashLedgerDetailBody">
                <div class="text-center text-muted py-4">Loading…</div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, .sidebar, .header, #sidebar, .btn, .pagination { display: none !important; }
    .cash-ledger-report-page { padding: 0 !important; }
    .cash-ledger-table thead { display: table-header-group; }
    .cash-ledger-table-wrap { overflow: visible !important; }
}
.cash-ledger-report-page {
    position: relative;
    z-index: 1;
    max-width: 100%;
    overflow: visible;
}
/* Defensive: some global themes inject oversized decorative pseudo-elements on .content */
.content.cash-ledger-report-page::before,
.content.cash-ledger-report-page::after,
.cash-ledger-report-page::before,
.cash-ledger-report-page::after {
    content: none !important;
    display: none !important;
}
/* Defensive: prevent any icon-font rule from scaling to viewport size on this page */
.cash-ledger-report-page i,
.cash-ledger-report-page [class^="ti-"],
.cash-ledger-report-page [class*=" ti-"],
.cash-ledger-report-page .ti {
    position: static !important;
    transform: none !important;
    max-width: 100%;
    max-height: 100%;
}
.cash-ledger-report-page .btn i,
.cash-ledger-report-page .badge i {
    font-size: 1em !important;
    line-height: 1 !important;
}
.cash-ledger-table thead th { position: sticky; top: 0; z-index: 2; box-shadow: 0 1px 0 rgba(0,0,0,.08); }
.cash-ledger-table-wrap { max-height: min(70vh, 900px); overflow: auto; }
.cash-ledger-row:hover { background: rgba(13,110,253,.06) !important; }
/* Fix oversized default pagination SVG icons on this page */
.cash-ledger-report-page .card-footer nav svg {
    width: 16px !important;
    height: 16px !important;
    min-width: 16px !important;
    min-height: 16px !important;
    max-width: 16px !important;
    max-height: 16px !important;
    display: inline-block !important;
}
</style>

<script>
document.querySelectorAll('.cash-ledger-row').forEach(function (row) {
    row.addEventListener('click', function () {
        var source = this.getAttribute('data-source');
        var id = this.getAttribute('data-id');
        var modal = new bootstrap.Modal(document.getElementById('cashLedgerDetailModal'));
        var body = document.getElementById('cashLedgerDetailBody');
        var title = document.getElementById('cashLedgerDetailTitle');
        body.innerHTML = '<div class="text-center text-muted py-4">Loading…</div>';
        title.textContent = 'Transaction';
        modal.show();
        fetch('{{ url('/admin/reports/cash-ledger/row') }}/' + encodeURIComponent(source) + '/' + encodeURIComponent(id), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); }).then(function (j) {
            if (!j.ok) {
                body.innerHTML = '<div class="alert alert-warning mb-0">Could not load details.</div>';
                return;
            }
            var d = j.data;
            title.textContent = d.title || 'Transaction';
            var html = '';
            if (d.meta) {
                html += '<dl class="row mb-3 small">';
                for (var k in d.meta) {
                    html += '<dt class="col-5 text-muted">' + k + '</dt><dd class="col-7">' + d.meta[k] + '</dd>';
                }
                html += '</dl>';
            }
            if (d.notes) {
                html += '<div class="mb-3"><strong>Notes</strong><p class="small mb-0">' + (d.notes.replace(/</g, '&lt;')) + '</p></div>';
            }
            if (d.links && d.links.length) {
                html += '<div class="d-flex flex-wrap gap-2">';
                d.links.forEach(function (l) {
                    html += '<a href="' + l.url + '" class="btn btn-sm btn-primary" target="_blank" rel="noopener">' + l.label + '</a>';
                });
                html += '</div>';
            }
            body.innerHTML = html || '<p class="text-muted mb-0">No extra details.</p>';
        }).catch(function () {
            body.innerHTML = '<div class="alert alert-danger mb-0">Request failed.</div>';
        });
    });
});

(function () {
    var thermalBaseUrl = @json(route('admin.reports.cash-ledger.thermal-print'));
    var queryString = @json(request()->getQueryString() ?? '');

    function getSavedPaper() {
        try {
            var raw = localStorage.getItem('thermal_print_settings');
            var settings = raw ? JSON.parse(raw) : {};
            var p = String(settings.paperSize || '80');
            return (p === '58' || p === '80') ? p : '80';
        } catch (e) {
            return '80';
        }
    }

    function openThermalPrint(paperPref) {
        var paper = (paperPref === '58' || paperPref === '80') ? paperPref : getSavedPaper();
        var qs = queryString ? ('?' + queryString + '&paper=' + encodeURIComponent(paper)) : ('?paper=' + encodeURIComponent(paper));
        window.open(thermalBaseUrl + qs, '_blank', 'noopener');
    }

    var defaultBtn = document.getElementById('cash-ledger-thermal-print-btn');
    if (defaultBtn) {
        defaultBtn.addEventListener('click', function () { openThermalPrint('auto'); });
    }
    document.querySelectorAll('.js-cash-ledger-thermal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openThermalPrint(this.getAttribute('data-paper') || 'auto');
        });
    });
})();
</script>
@endsection
