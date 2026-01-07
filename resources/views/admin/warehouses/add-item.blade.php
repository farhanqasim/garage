@extends('layouts.app')

@section('title', 'Add Item to Warehouse')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Add Item to Warehouse</h4>
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
            <form action="{{ route('warehouses.store-item', $warehouse->id) }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Item <span class="text-danger">*</span></label>
                        <select name="item_id" class="form-control @error('item_id') is-invalid @enderror" required>
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                @php
                                    $itemName = $item->short_disc ?? $item->pro_dis ?? '';
                                    if (empty($itemName) && $item->partnumber_item) {
                                        $itemName = $item->partnumber_item->name ?? '';
                                    }
                                    if (empty($itemName)) {
                                        $itemName = $item->bar_code ?? 'N/A';
                                    }
                                    if ($item->category) {
                                        $itemName .= ' - ' . $item->category->name;
                                    }
                                @endphp
                                <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $itemName }}
                                    @if($item->bar_code && $itemName != $item->bar_code) ({{ $item->bar_code }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('item_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                               value="{{ old('quantity', 0) }}" step="0.01" min="0" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Min Stock Level</label>
                        <input type="number" name="min_stock_level" class="form-control" 
                               value="{{ old('min_stock_level', 0) }}" step="0.01" min="0">
                        <small class="text-muted">Alert when stock goes below this level</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Max Stock Level</label>
                        <input type="number" name="max_stock_level" class="form-control" 
                               value="{{ old('max_stock_level') }}" step="0.01" min="0">
                        <small class="text-muted">Maximum stock capacity</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" 
                               value="{{ old('location') }}" 
                               placeholder="e.g., Rack A-1, Shelf 3">
                        <small class="text-muted">Rack/Shelf location in warehouse</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Optional notes about this item">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i> Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

