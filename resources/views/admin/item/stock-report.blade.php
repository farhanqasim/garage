@extends('layouts.app')
@section('title', 'Item Stock Report')
@section('content')
<div class="content">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <h2 class="fw-bold mb-1">Item Stock Report</h2>
            <p class="text-muted mb-0 small">
                Detailed stock in/out by item type, category, branch and user within a date range.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <form method="GET" action="{{ route('items.stock.report') }}" class="row g-3 align-items-end">
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
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="ti ti-filter me-1"></i> Apply
                    </button>
                    <a href="{{ route('items.stock.report') }}" class="btn btn-outline-secondary flex-fill">
                        Clear
                    </a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead class="thead-primary bg-light">
                        <tr>
                            <th>#</th>
                            <th>Item Type</th>
                            <th>Product Name</th>
                            <th>Part Number</th>
                            <th>Category</th>
                            <th>Company</th>
                            <th>Branch</th>
                            <th>Purchase From</th>
                            <th>Purchase Date &amp; Time</th>
                            <th>Sale To</th>
                            <th>Sale Date &amp; Time</th>
                            <th class="text-end">Stock In</th>
                            <th class="text-end">Stock Out</th>
                            <th class="text-end">Net Movement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row['item_type'] }}</td>
                                <td>{{ $row['product_name'] }}</td>
                                <td>{{ $row['part_number'] }}</td>
                                <td>{{ $row['category'] }}</td>
                                <td>{{ $row['company'] }}</td>
                                <td>{{ $row['branch'] }}</td>
                                <td>{{ $row['last_purchase_from'] ?? '-' }}</td>
                                <td>
                                    @if(!empty($row['last_purchase_at']))
                                        {{ $row['last_purchase_at']->format('d/m/Y h:i A') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $row['last_sale_to'] ?? '-' }}</td>
                                <td>
                                    @if(!empty($row['last_sale_at']))
                                        {{ $row['last_sale_at']->format('d/m/Y h:i A') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($row['stock_in'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($row['stock_out'], 2) }}</td>
                                <td class="text-end fw-bold {{ $row['net_movement'] < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($row['net_movement'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center text-muted py-4">
                                    No stock movements found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Item Transactions (with Running Balance)</h5>
            <span class="badge bg-secondary">{{ count($transactions ?? []) }} entries</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead class="thead-primary bg-light">
                        <tr>
                            <th>#</th>
                            <th>Item Type</th>
                            <th>Product Name</th>
                            <th>Part Number</th>
                            <th>Category</th>
                            <th>Company</th>
                            <th>Branch</th>
                            <th>Transaction</th>
                            <th>Party</th>
                            <th>Date &amp; Time</th>
                            <th class="text-end">Stock In</th>
                            <th class="text-end">Stock Out</th>
                            <th class="text-end">Balance After</th>
                            <th class="text-end">Bal Can</th>
                            <th class="text-end">Bal Liter</th>
                            <th class="text-end">Bal ML</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions ?? [] as $index => $tx)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $tx['item_type'] }}</td>
                                <td>{{ $tx['product_name'] }}</td>
                                <td>{{ $tx['part_number'] }}</td>
                                <td>{{ $tx['category'] }}</td>
                                <td>{{ $tx['company'] }}</td>
                                <td>{{ $tx['branch'] }}</td>
                                <td>{{ $tx['type'] }}</td>
                                <td>{{ $tx['party'] ?? '-' }}</td>
                                <td>
                                    @if(!empty($tx['occurred_at']))
                                        {{ $tx['occurred_at']->format('d/m/Y h:i A') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($tx['qty_in'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($tx['qty_out'], 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($tx['balance_after'], 2) }}</td>
                                <td class="text-end">
                                    @if(isset($tx['balance_can']))
                                        {{ $tx['balance_can'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(isset($tx['balance_liter']))
                                        {{ $tx['balance_liter'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(isset($tx['balance_ml']))
                                        {{ $tx['balance_ml'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted py-4">
                                    No transaction entries found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Current Stock by Warehouse</h5>
            <span class="badge bg-secondary">{{ count($warehouseRows ?? []) }} rows</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead class="thead-primary bg-light">
                        <tr>
                            <th>#</th>
                            <th>Item Type</th>
                            <th>Product Name</th>
                            <th>Part Number</th>
                            <th>Category</th>
                            <th>Company</th>
                            <th>Branch</th>
                            <th>Warehouse</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Can</th>
                            <th class="text-end">Liter</th>
                            <th class="text-end">ML</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouseRows ?? [] as $index => $wr)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $wr['item_type'] }}</td>
                                <td>{{ $wr['product_name'] }}</td>
                                <td>{{ $wr['part_number'] }}</td>
                                <td>{{ $wr['category'] }}</td>
                                <td>{{ $wr['company'] }}</td>
                                <td>{{ $wr['branch'] }}</td>
                                <td>{{ $wr['warehouse'] }} @if(!empty($wr['warehouse_code'])) ({{ $wr['warehouse_code'] }}) @endif</td>
                                <td class="text-end fw-bold">{{ number_format($wr['quantity'], 2) }}</td>
                                <td class="text-end">
                                    @if(isset($wr['qty_can']))
                                        {{ $wr['qty_can'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(isset($wr['qty_liter']))
                                        {{ $wr['qty_liter'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(isset($wr['qty_ml']))
                                        {{ $wr['qty_ml'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    No warehouse stock found for the selected filters.
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

