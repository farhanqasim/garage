@extends('layouts.app')
@section('title', 'Item Price List')
@section('content')
<div class="content">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <h2 class="fw-bold mb-1">Item Price List</h2>
            <p class="text-muted mb-0 small">View all items with prices. Filter by category.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('all.items') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> All Items
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
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
                        <label for="category_id" class="form-label fw-bold mb-2">Filter by Category</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-filter me-1"></i> Apply
                        </button>
                    </div>
                    @if(request('category_id') || (request('type') && request('type') !== 'all'))
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('items.price.list') }}" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead class="thead-primary bg-light">
                        <tr>
                            <th class="text-nowrap">#</th>
                            <th>Product Name</th>
                            <th class="text-end">Retail Price (Rs.)</th>
                            <th class="text-end">Tax %</th>
                            <th class="text-nowrap">Last updated</th>
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
                                }
                            @endphp
                            <tr data-item-id="{{ $item->id }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-medium align-top">
                                    @if($isBattery)
                                        <div>{{ $item->company_item->name ?? '-' }}</div>
                                        <div class="fw-semibold">{{ $item->product_item->name ?? $item->partnumber_item->name ?? $itemName }}</div>
                                        <div class="small text-muted">{{ $voltDisplay }} {{ $plateDisplay }} {{ $ampDisplay }} {{ $ccaDisplay }}</div>
                                    @else
                                        <div class="d-block">{{ $itemName }}</div>
                                    @endif
                                </td>
                                <td class="text-end price-list-editable" data-field="retail_price" data-value="{{ $item->retail_price !== null && $item->retail_price !== '' ? (float)$item->retail_price : '' }}" title="Click to edit">{{ $item->retail_price !== null && $item->retail_price !== '' ? number_format((float)$item->retail_price, 2) : '-' }}</td>
                                <td class="text-end price-list-editable" data-field="tax_percentage" data-value="{{ isset($item->tax_percentage) ? (float)$item->tax_percentage : 0 }}" title="Click to edit">{{ isset($item->tax_percentage) && $item->tax_percentage !== '' ? number_format((float)$item->tax_percentage, 2) : '0' }}%</td>
                                <td class="small">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="text-muted">{{ $item->last_updated_at ? $item->last_updated_at->format('d/m/Y H:i') : '-' }}</div>
                                            <div class="mt-1">{{ $item->priceUpdatedBranch ? $item->priceUpdatedBranch->branch_name : ($item->updated_by_user && $item->updated_by_user->branch ? $item->updated_by_user->branch->branch_name : ($currentBranchName ?? '-')) }}</div>
                                            <div class="mt-1">{{ $item->updated_by_user ? $item->updated_by_user->name : '-' }}</div>
                                        </div>
                                        @canany(['update_items', 'update_parts', 'update_filters', 'update_break_pad', 'update_oil', 'update_battery', 'update_scrap', 'update_services'])
                                        <a href="{{ route('item.edit', $item->id) }}" class="btn btn-sm btn-outline-primary flex-shrink-0" title="Edit product">Edit</a>
                                        @endcanany
                                    </div>
                                </td>
                                @if(request('type', 'all') === 'all')
                                <td><span class="badge bg-light text-dark">{{ $item->category ? $item->category->name : '-' }}</span></td>
                                <td>{{ $item->unit_item ? $item->unit_item->name : ($item->unit ?? '-') }}</td>
                                <td class="text-end price-list-editable" data-field="total_price" data-value="{{ $item->total_price ?? '' }}" title="Click to edit">{{ number_format((float)($item->total_price ?? 0), 2) }}</td>
                                <td class="text-end price-list-editable" data-field="sale_price" data-value="{{ $item->sale_price ?? '' }}" title="Click to edit">{{ number_format((float)($item->sale_price ?? 0), 2) }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ request('type', 'all') !== 'all' ? 5 : 9 }}" class="text-center py-5 text-muted">
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var baseUrl = '{{ route("items.price.list.bulk.update") }}';
    var token = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function savePriceListCell(itemId, field, value, $cell) {
        var payload = { items: [{ id: itemId }], _token: token };
        payload.items[0][field] = value === '' ? null : (field === 'tax_percentage' ? parseFloat(value) : parseFloat(value));
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
                var display = value === '' || value === null ? (field === 'retail_price' || field === 'total_price' || field === 'sale_price' ? '-' : '0%') : (field === 'tax_percentage' ? parseFloat(value).toFixed(2) + '%' : parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                $cell.removeClass('editing').html(display).attr('data-value', value);
                if (res.updated_items && res.updated_items.length > 0) {
                    var row = $cell.closest('tr');
                    var info = res.updated_items[0];
                    var $nextTd = row.find('td').eq($cell.index() + 1);
                    if ($nextTd.length) {
                        $nextTd.html('<div class="text-muted">' + (info.last_updated_at || '-') + '</div><div class="mt-1">' + (info.branch_name || '-') + '</div><div class="mt-1">' + (info.user_name || '-') + '</div>');
                    }
                }
            } else {
                $cell.removeClass('editing').html($cell.attr('data-value') !== '' ? ($cell.attr('data-field') === 'tax_percentage' ? parseFloat($cell.attr('data-value')).toFixed(2) + '%' : parseFloat($cell.attr('data-value')).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')) : '-').attr('title', 'Click to edit');
                if (res.message) alert(res.message);
            }
        }).catch(function() {
            var prev = $cell.attr('data-value');
            $cell.removeClass('editing').html(prev !== '' ? ($cell.attr('data-field') === 'tax_percentage' ? parseFloat(prev).toFixed(2) + '%' : parseFloat(prev).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')) : '-').attr('title', 'Click to edit');
            alert('Failed to save.');
        });
    }

    $(document).on('click', '.price-list-editable', function() {
        var $cell = $(this);
        if ($cell.hasClass('editing')) return;
        var field = $cell.data('field');
        var value = $cell.data('value');
        var isPct = field === 'tax_percentage';
        var input = $('<input type="number" class="form-control form-control-sm text-end" style="width:80px;min-width:60px" step="' + (isPct ? '0.01' : '1') + '" min="0" ' + (isPct ? 'max="100"' : '') + '>');
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
                $cell.removeClass('editing').html(prev !== '' ? (isPct ? parseFloat(prev).toFixed(2) + '%' : parseFloat(prev).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')) : '-').attr('title', 'Click to edit');
                input.off('blur.pledit keydown.pledit');
            }
        });
    });
});
</script>
@endpush
@endsection
