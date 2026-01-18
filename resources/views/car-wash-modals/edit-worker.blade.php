<!-- Edit Worker Modal {{ $worker->id }} -->
<div class="modal fade" id="editWorkerModal{{ $worker->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--info) 0%, #0891b2 100%);">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('car-wash.workers.update', $worker->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name{{ $worker->id }}" class="form-label">Worker Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name{{ $worker->id }}" name="name" value="{{ $worker->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mobile{{ $worker->id }}" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" id="mobile{{ $worker->id }}" name="mobile" value="{{ $worker->mobile }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="father_name{{ $worker->id }}" class="form-label">Father Name</label>
                            <input type="text" class="form-control" id="father_name{{ $worker->id }}" name="father_name" value="{{ $worker->father_name }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="father_mobile{{ $worker->id }}" class="form-label">Father Mobile Number</label>
                            <input type="text" class="form-control" id="father_mobile{{ $worker->id }}" name="father_mobile" value="{{ $worker->father_mobile }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="location{{ $worker->id }}" class="form-label">Location / Home Address</label>
                            <textarea class="form-control" id="location{{ $worker->id }}" name="location" rows="2">{{ $worker->location }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="commission{{ $worker->id }}" class="form-label">Commission (%)</label>
                            <input type="number" class="form-control" id="commission{{ $worker->id }}" name="commission" value="{{ $worker->commission }}" min="0" max="100">
                        </div>
                        @if($worker->id_card_front)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current ID Card Front</label>
                            <img src="{{ asset($worker->id_card_front) }}" alt="ID Front" class="img-thumbnail" style="max-width: 100px;">
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label for="id_card_front{{ $worker->id }}" class="form-label">Update ID Card Front</label>
                            <input type="file" class="form-control" id="id_card_front{{ $worker->id }}" name="id_card_front" accept="image/*">
                        </div>
                        @if($worker->id_card_back)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current ID Card Back</label>
                            <img src="{{ asset($worker->id_card_back) }}" alt="ID Back" class="img-thumbnail" style="max-width: 100px;">
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label for="id_card_back{{ $worker->id }}" class="form-label">Update ID Card Back</label>
                            <input type="file" class="form-control" id="id_card_back{{ $worker->id }}" name="id_card_back" accept="image/*">
                        </div>
                        @if($worker->father_card_front)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current Father Card Front</label>
                            <img src="{{ asset($worker->father_card_front) }}" alt="Father Card Front" class="img-thumbnail" style="max-width: 100px;">
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label for="father_card_front{{ $worker->id }}" class="form-label">Update Father Card Front</label>
                            <input type="file" class="form-control" id="father_card_front{{ $worker->id }}" name="father_card_front" accept="image/*">
                        </div>
                        @if($worker->father_card_back)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current Father Card Back</label>
                            <img src="{{ asset($worker->father_card_back) }}" alt="Father Card Back" class="img-thumbnail" style="max-width: 100px;">
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label for="father_card_back{{ $worker->id }}" class="form-label">Update Father Card Back</label>
                            <input type="file" class="form-control" id="father_card_back{{ $worker->id }}" name="father_card_back" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Update Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>