<!-- Edit Job Modal {{ $job->id }} -->
<div class="modal fade" id="editJobModal{{ $job->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Job #{{ $job->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('car-wash.jobs.update', $job->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_name{{ $job->id }}" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="customer_name{{ $job->id }}" name="customer_name" value="{{ $job->customer_name }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="vehicle_no{{ $job->id }}" class="form-label">Vehicle Number</label>
                            <input type="text" class="form-control" id="vehicle_no{{ $job->id }}" name="vehicle_no" value="{{ $job->vehicle_no }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mobile{{ $job->id }}" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" id="mobile{{ $job->id }}" name="mobile" value="{{ $job->mobile }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="service_id{{ $job->id }}" class="form-label">Service</label>
                            <select class="form-select" id="service_id{{ $job->id }}" name="service_id">
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ $job->service_id == $service->id ? 'selected' : '' }}>
                                    {{ $service->label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="worker_id{{ $job->id }}" class="form-label">Worker</label>
                            <select class="form-select" id="worker_id{{ $job->id }}" name="worker_id">
                                <option value="">Select Worker</option>
                                @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ $job->worker_id == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price{{ $job->id }}" class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" id="price{{ $job->id }}" name="price" value="{{ $job->price }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Job</button>
                </div>
            </form>
        </div>
    </div>
</div>