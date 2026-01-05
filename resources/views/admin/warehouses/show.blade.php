@extends('layouts.app')

@section('title', 'Warehouse Details')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>{{ $warehouse->warehouse_name }}</h4>
            <h6>Warehouse Code: {{ $warehouse->warehouse_code }} | Branch: {{ $warehouse->branch->branch_name ?? 'N/A' }}</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('warehouses.add-item', $warehouse->id) }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Item
            </a>
            <a href="{{ route('warehouses.low-stock', $warehouse->id) }}" class="btn btn-warning">
                <i class="ti ti-alert-triangle me-1"></i> Low Stock
            </a>
        </div>
    </div>

    <!-- Warehouse Info Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Items</h6>
                    <h3 class="mb-0">{{ $items->total() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Stock Value</h6>
                    <h3 class="mb-0">Rs {{ number_format($warehouse->total_stock_value, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Status</h6>
                    <h3 class="mb-0">
                        @if($warehouse->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Barcode</th>
                            <th>Quantity</th>
                            <th>Available</th>
                            <th>Reserved</th>
                            <th>Min Level</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $warehouseItem)
                        <tr class="{{ $warehouseItem->isLowStock() ? 'table-warning' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $warehouseItem->item->short_disc ?? $warehouseItem->item->pro_dis ?? 'N/A' }}</strong>
                            </td>
                            <td>
                                <small class="text-muted">{{ $warehouseItem->item->bar_code ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ number_format($warehouseItem->quantity, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success">{{ number_format($warehouseItem->available_quantity, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">{{ number_format($warehouseItem->reserved_quantity, 2) }}</span>
                            </td>
                            <td>
                                <small>{{ number_format($warehouseItem->min_stock_level, 2) }}</small>
                            </td>
                            <td>
                                <small>{{ $warehouseItem->location ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($warehouseItem->isLowStock())
                                    <span class="badge bg-danger">Low Stock</span>
                                @else
                                    <span class="badge bg-success">OK</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editStockModal{{ $warehouseItem->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form action="{{ route('warehouses.remove-item', [$warehouse->id, $warehouseItem->item_id]) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to remove this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Stock Modal -->
                        <div class="modal fade" id="editStockModal{{ $warehouseItem->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Stock</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('warehouses.update-stock', [$warehouse->id, $warehouseItem->item_id]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Item</label>
                                                <input type="text" class="form-control" 
                                                       value="{{ $warehouseItem->item->short_disc ?? $warehouseItem->item->pro_dis ?? 'N/A' }}" 
                                                       readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                                <input type="number" name="quantity" class="form-control" 
                                                       value="{{ $warehouseItem->quantity }}" 
                                                       step="0.01" min="0" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Min Stock Level</label>
                                                <input type="number" name="min_stock_level" class="form-control" 
                                                       value="{{ $warehouseItem->min_stock_level }}" 
                                                       step="0.01" min="0">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Max Stock Level</label>
                                                <input type="number" name="max_stock_level" class="form-control" 
                                                       value="{{ $warehouseItem->max_stock_level }}" 
                                                       step="0.01" min="0">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Location</label>
                                                <input type="text" name="location" class="form-control" 
                                                       value="{{ $warehouseItem->location }}" 
                                                       placeholder="Rack/Shelf">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Update Stock</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <p class="text-muted">No items in this warehouse.</p>
                                <a href="{{ route('warehouses.add-item', $warehouse->id) }}" class="btn btn-primary">
                                    <i class="ti ti-plus me-1"></i> Add First Item
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
            <div class="card-footer">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

