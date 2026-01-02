{{-- resources/views/admin/branches/single.blade.php --}}
@extends('layouts.app')
@section('title')
{{ isset($branch) ? 'My Branch' : 'Add New Branch' }}
@endsection

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h2 class="fw-bold">{{ isset($branch) ? 'My Branch' : 'Add New Branch' }}</h2>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            @if(isset($branch) && $branch)
                {{-- View and Edit Mode --}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="ti ti-building me-2"></i>Branch Details</h5>
                    <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#edit-branch-modal">
                        <i data-feather="edit" class="feather-edit"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-card p-3 bg-light rounded">
                                <h6 class="fw-bold text-primary mb-3"><i class="ti ti-user me-2"></i>Basic Information</h6>
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th class="text-muted">Branch Name:</th>
                                        <td><strong>{{ $branch->branch_name }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Branch Code:</th>
                                        <td><strong>{{ $branch->branch_code }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Manager Name:</th>
                                        <td>{{ $branch->manager_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Email:</th>
                                        <td>{{ $branch->email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Phone:</th>
                                        <td>{{ $branch->phone ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card p-3 bg-light rounded">
                                <h6 class="fw-bold text-primary mb-3"><i class="ti ti-map-pin me-2"></i>Location Information</h6>
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th class="text-muted">Address:</th>
                                        <td>{{ $branch->address ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">City:</th>
                                        <td>{{ $branch->city ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">State:</th>
                                        <td>{{ $branch->state ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Country:</th>
                                        <td>{{ $branch->country ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Location:</th>
                                        <td>{{ $branch->location ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted">Status:</th>
                                        <td>
                                            @if ($branch->status === 'active')
                                                <span class="badge badge-success"><i class="ti ti-check me-1"></i>Active</span>
                                            @else
                                                <span class="badge badge-warning"><i class="ti ti-alert-circle me-1"></i>Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Edit Branch Modal --}}
                <div class="modal fade" id="edit-branch-modal">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div class="page-title">
                                    <h4>Edit Branch</h4>
                                </div>
                                <button type="button" class="close bg-danger text-white fs-16"
                                    data-bs-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{ route('branch.update', $branch->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="user_id" class="col-form-label fw-bold">Assigned User <span class="text-danger">*</span>:</label>
                                                <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required disabled>
                                                    <option value="{{ auth()->user()->id }}" selected>
                                                        {{ auth()->user()->name }} ({{ auth()->user()->email }})
                                                    </option>
                                                </select>
                                                @error('user_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="branch_name" class="col-form-label fw-bold">Branch Name <span class="text-danger">*</span>:</label>
                                                <input type="text" class="form-control @error('branch_name') is-invalid @enderror" name="branch_name" id="branch_name" value="{{ old('branch_name', $branch->branch_name) }}" required>
                                                @error('branch_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="branch_code" class="col-form-label fw-bold">Branch Code <span class="text-danger">*</span>:</label>
                                                <input type="text" class="form-control @error('branch_code') is-invalid @enderror" name="branch_code" id="branch_code" value="{{ old('branch_code', $branch->branch_code) }}" required>
                                                @error('branch_code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="manager_name" class="col-form-label fw-bold">Manager Name:</label>
                                                <input type="text" class="form-control @error('manager_name') is-invalid @enderror" name="manager_name" id="manager_name" value="{{ old('manager_name', $branch->manager_name) }}">
                                                @error('manager_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="email" class="col-form-label fw-bold">Email:</label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $branch->email) }}">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="phone" class="col-form-label fw-bold">Phone:</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone', $branch->phone) }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="address" class="col-form-label fw-bold">Address:</label>
                                                <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" id="address" value="{{ old('address', $branch->address) }}">
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="city" class="col-form-label fw-bold">City:</label>
                                                <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" value="{{ old('city', $branch->city) }}">
                                                @error('city')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="state" class="col-form-label fw-bold">State:</label>
                                                <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" id="state" value="{{ old('state', $branch->state) }}">
                                                @error('state')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="country" class="col-form-label fw-bold">Country:</label>
                                                <input type="text" class="form-control @error('country') is-invalid @enderror" name="country" id="country" value="{{ old('country', $branch->country ?? 'Pakistan') }}">
                                                @error('country')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="location" class="col-form-label fw-bold">Location:</label>
                                                <input type="text" class="form-control @error('location') is-invalid @enderror" name="location" id="location" value="{{ old('location', $branch->location) }}">
                                                @error('location')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update Branch</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                {{-- Add Mode --}}
                <div class="card-header">
                    <h5 class="mb-0"><i class="ti ti-building me-2"></i>Add Your Branch</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('branch.store') }}" method="post">
                        @csrf
                        <div class="row g-3">
                            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="col-form-label fw-bold">Assigned User <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control" value="{{ auth()->user()->name }} ({{ auth()->user()->email }})" disabled>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_name" class="col-form-label fw-bold">Branch Name <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_name') is-invalid @enderror" name="branch_name" id="branch_name" value="{{ old('branch_name') }}" required>
                                    @error('branch_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="branch_code" class="col-form-label fw-bold">Branch Code <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('branch_code') is-invalid @enderror" name="branch_code" id="branch_code" value="{{ old('branch_code') }}" required>
                                    @error('branch_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="manager_name" class="col-form-label fw-bold">Manager Name:</label>
                                    <input type="text" class="form-control @error('manager_name') is-invalid @enderror" name="manager_name" id="manager_name" value="{{ old('manager_name') }}">
                                    @error('manager_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email" class="col-form-label fw-bold">Email:</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="phone" class="col-form-label fw-bold">Phone:</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address" class="col-form-label fw-bold">Address:</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" id="address" value="{{ old('address') }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city" class="col-form-label fw-bold">City:</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state" class="col-form-label fw-bold">State:</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" name="state" id="state" value="{{ old('state') }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country" class="col-form-label fw-bold">Country:</label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" name="country" id="country" value="{{ old('country', 'Pakistan') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location" class="col-form-label fw-bold">Location:</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" name="location" id="location" value="{{ old('location') }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Branch</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection