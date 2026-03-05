@extends('layouts.app')
@section('title', 'Scrap Report')
@section('content')
<div class="content">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <h2 class="fw-bold mb-1">Scrap Report</h2>
            <p class="text-muted mb-0 small">
                Scrap items with Total Weight and Total Scrap Value.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-arrow-left me-1"></i> Dashboard
            </a>
            <a href="{{ route('all.items.create.new') }}?type=scrap" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> Add Scrap Item
            </a>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3 me-3">
                        <i class="ti ti-scale text-primary" style="font-size: 1.75rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Weight</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($totalWeight, 2) }} <span class="fs-6 fw-normal text-muted">KG</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3 me-3">
                        <i class="ti ti-currency-rupee text-success" style="font-size: 1.75rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Scrap Value</div>
                        <div class="h4 mb-0 fw-bold">{{ number_format($totalScrapValue, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('items.scrap.report') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-1">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-filter me-1"></i> Apply
                    </button>
                    <a href="{{ route('items.scrap.report') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead class="thead-primary bg-light">
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th class="text-end">Weight (KG) / Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Total Price</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($item->product_item)
                                        {{ $item->product_item->name }}
                                    @else
                                        {{ $item->short_disc ?? $item->bar_code ?? '—' }}
                                    @endif
                                </td>
                                <td>{{ $item->category ? $item->category->name : '—' }}</td>
                                <td class="text-end">
                                    @if($item->category && $item->category->scrap_measurement === 'count')
                                        {{ number_format((float)($item->on_hand ?? 0), 0) }} <span class="text-muted">(Qty)</span>
                                    @else
                                        {{ number_format((float)($item->weight_for_delivery ?? 0), 2) }}
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format((float)($item->price_per_unit ?? 0), 2) }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float)($item->total_price ?? 0), 2) }}</td>
                                <td>
                                    <a href="{{ route('item.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No scrap items found. <a href="{{ route('all.items.create.new') }}?type=scrap">Add a scrap item</a>.
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
