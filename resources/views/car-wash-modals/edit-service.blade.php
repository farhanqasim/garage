<!-- Edit Service Modal {{ $service->id }} -->
<div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--success) 0%, #059669 100%);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('car-wash.services.update', $service->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="label{{ $service->id }}" class="form-label">Service Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="label{{ $service->id }}" name="label" value="{{ $service->label }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="base_price{{ $service->id }}" class="form-label">Base Price (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="base_price{{ $service->id }}" name="base_price" value="{{ $service->base_price }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="icon{{ $service->id }}" class="form-label">Icon</label>
                            <input type="text" class="form-control" id="icon{{ $service->id }}" name="icon" value="{{ $service->icon }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="color{{ $service->id }}" class="form-label">Color Class</label>
                            <input type="text" class="form-control" id="color{{ $service->id }}" name="color" value="{{ $service->color }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="color_value{{ $service->id }}" class="form-label">Color Value (Hex)</label>
                            <input type="text" class="form-control" id="color_value{{ $service->id }}" name="color_value" value="{{ $service->color_value }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>