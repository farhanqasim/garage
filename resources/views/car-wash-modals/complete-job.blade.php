<!-- Complete Job Modal {{ $job->id }} -->
<div class="modal fade" id="completeJobModal{{ $job->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--success) 0%, #059669 100%);">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Complete Job #{{ $job->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('car-wash.jobs.complete', $job->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer: <strong>{{ $job->customer_name }}</strong></label>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vehicle: <strong>{{ $job->vehicle_no }}</strong></label>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="inspection_notes{{ $job->id }}" class="form-label">Inspection Notes</label>
                            <textarea class="form-control" id="inspection_notes{{ $job->id }}" name="inspection_notes" rows="3"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="expense_amount{{ $job->id }}" class="form-label">Expense Amount (if any)</label>
                            <input type="number" step="0.01" class="form-control" id="expense_amount{{ $job->id }}" name="expense_amount" value="0">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="expense_description{{ $job->id }}" class="form-label">Expense Description</label>
                            <textarea class="form-control" id="expense_description{{ $job->id }}" name="expense_description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Job</button>
                </div>
            </form>
        </div>
    </div>
</div>