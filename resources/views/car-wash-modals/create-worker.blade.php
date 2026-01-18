<!-- Create Worker Modal -->
<div class="modal fade" id="createWorkerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--info) 0%, #0891b2 100%);">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('car-wash.workers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Worker Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" id="mobile" name="mobile">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="father_name" class="form-label">Father Name</label>
                            <input type="text" class="form-control" id="father_name" name="father_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="father_mobile" class="form-label">Father Mobile Number</label>
                            <input type="text" class="form-control" id="father_mobile" name="father_mobile">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="location" class="form-label">Location / Home Address</label>
                            <textarea class="form-control" id="location" name="location" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="commission" class="form-label">Commission (%)</label>
                            <input type="number" class="form-control" id="commission" name="commission" value="0" min="0" max="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_card_front" class="form-label">ID Card Front</label>
                            <input type="file" class="form-control" id="id_card_front" name="id_card_front" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_card_back" class="form-label">ID Card Back</label>
                            <input type="file" class="form-control" id="id_card_back" name="id_card_back" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="father_card_front" class="form-label">Father Card Front</label>
                            <input type="file" class="form-control" id="father_card_front" name="father_card_front" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="father_card_back" class="form-label">Father Card Back</label>
                            <input type="file" class="form-control" id="father_card_back" name="father_card_back" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Create Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>