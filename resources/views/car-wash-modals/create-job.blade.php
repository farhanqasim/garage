<!-- Create Job Modal -->
<div class="modal fade" id="createJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('car-wash.jobs.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="vehicle_no" class="form-label">Vehicle Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vehicle_no" name="vehicle_no" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" id="mobile" name="mobile">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="service_id" class="form-label">Service <span class="text-danger">*</span></label>
                            <select class="form-select" id="service_id" name="service_id" required onchange="updateServicePrice(this)">
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}" data-name="{{ $service->label }}" data-price="{{ $service->base_price }}">
                                    {{ $service->label }} - Rs. {{ number_format($service->base_price, 2) }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="service_name" name="service_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="worker_id" class="form-label">Worker</label>
                            <select class="form-select" id="worker_id" name="worker_id" onchange="updateWorkerName(this)">
                                <option value="">Select Worker</option>
                                @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" data-name="{{ $worker->name }}">
                                    {{ $worker->name }}
                                </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="worker_name" name="worker_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Job</button>
                </div>
            </form>
        </div>
    </div>
</div>