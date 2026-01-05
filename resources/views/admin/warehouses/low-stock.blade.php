@extends('layouts.app')

@section('title', 'Low Stock Items')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Low Stock Items</h4>
            <h6>{{ $warehouse->warehouse_name }}</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($lowStockItems->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Barcode</th>
                            <th>Available Qty</th>
                            <th>Min Level</th>
                            <th>Deficit</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $index => $warehouseItem)
                        <tr class="table-warning">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $warehouseItem->item->short_disc ?? $warehouseItem->item->pro_dis ?? 'N/A' }}</strong>
                            </td>
                            <td>
                                <small class="text-muted">{{ $warehouseItem->item->bar_code ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-danger">{{ number_format($warehouseItem->available_quantity, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">{{ number_format($warehouseItem->min_stock_level, 2) }}</span>
                            </td>
                            <td>
                                @php
                                    $deficit = $warehouseItem->min_stock_level - $warehouseItem->available_quantity;
                                @endphp
                                <span class="badge bg-danger">{{ number_format($deficit, 2) }}</span>
                            </td>
                            <td>
                                <small>{{ $warehouseItem->location ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="ti ti-check-circle fs-48 text-success mb-3"></i>
                <h5>All items are well stocked!</h5>
                <p class="text-muted">No low stock items found in this warehouse.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

