@extends('layouts.app')
@section('title', 'Edit Temporary Product')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4 class="fw-bold">Edit Temporary Product</h4>
        </div>
        <div class="page-btn">
            <a href="{{ route('purchases.temporary.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('purchases.temporary.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ old('product_name', $item->short_disc ?? $item->pro_dis) }}" required maxlength="255">
                            @error('product_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="cost_price" class="form-label">Cost Price (Rs) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', $item->packing_purchase_rate) }}" required min="0" step="0.01">
                            @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <select class="form-select" id="unit" name="unit">
                                <option value="">— Select —</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ (old('unit', $item->unit) == $u->id) ? 'selected' : '' }}>{{ $u->name }} ({{ $u->short_name ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes', $item->notes) }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image <span class="text-muted">(optional – leave blank to keep current)</span></label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        @if($item->image)
                            <label class="form-label">Current image</label>
                            <div class="mb-3">
                                <img src="{{ asset($item->image) }}" alt="" class="img-fluid rounded border" style="max-height: 200px; object-fit: contain;" onerror="this.src='{{ asset('assets/img/icons/image.svg') }}'">
                            </div>
                        @endif
                    </div>
                </div>
                <hr>
                <button type="submit" class="btn btn-primary">Update Temporary Product</button>
                <a href="{{ route('purchases.temporary.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
