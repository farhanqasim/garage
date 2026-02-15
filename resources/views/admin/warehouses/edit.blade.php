@extends('layouts.app')

@section('title', 'Edit Warehouse')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Edit Warehouse</h4>
            <h6>Update warehouse details</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('warehouses.update', $warehouse->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-control @error('branch_id') is-invalid @enderror" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $warehouse->branch_id) == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                    @if($branch->branch_code) ({{ $branch->branch_code }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                        <input type="text" name="warehouse_name" class="form-control @error('warehouse_name') is-invalid @enderror"
                               value="{{ old('warehouse_name', $warehouse->warehouse_name) }}" required>
                        @error('warehouse_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Warehouse Code</label>
                        <div class="input-group">
                            <input type="text" name="warehouse_code" id="warehouse_code" class="form-control @error('warehouse_code') is-invalid @enderror"
                                   value="{{ old('warehouse_code', $warehouse->warehouse_code) }}" placeholder="e.g. WH-001">
                            <button type="button" class="btn btn-outline-primary" id="warehouse-code-auto-btn" title="Generate next code">
                                Auto generate
                            </button>
                        </div>
                        @error('warehouse_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $warehouse->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $warehouse->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Manager Name</label>
                        <input type="text" name="manager_name" class="form-control"
                               value="{{ old('manager_name', $warehouse->manager_name) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="{{ old('phone', $warehouse->phone) }}" placeholder="03xxxxxxxxx">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $warehouse->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control"
                               value="{{ old('city', $warehouse->city) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control"
                               value="{{ old('state', $warehouse->state) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control"
                               value="{{ old('country', $warehouse->country ?? 'Pakistan') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"
                                  placeholder="Enter warehouse address">{{ old('address', $warehouse->address) }}</textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Optional notes about this warehouse">{{ old('notes', $warehouse->notes) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">Cancel</a>
                    <a href="{{ route('warehouses.show', $warehouse->id) }}" class="btn btn-outline-primary">View</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i> Update Warehouse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('warehouse-code-auto-btn');
    var input = document.getElementById('warehouse_code');
    if (btn && input) {
        btn.addEventListener('click', function() {
            btn.disabled = true;
            btn.textContent = '...';
            fetch('{{ route("warehouses.next-code") }}', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.code) input.value = data.code;
                btn.textContent = 'Auto generate';
            })
            .catch(function() {
                btn.textContent = 'Auto generate';
            })
            .finally(function() { btn.disabled = false; });
        });
    }
});
</script>
@endpush
