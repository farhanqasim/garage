@extends('layouts.app')
@section('title', __('Edit Bank'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h2 class="fw-bold">Edit Bank</h2>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.banks.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.banks.update', $bank->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $bank->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="short_name" class="form-label">Short Name</label>
                                <input type="text" 
                                       class="form-control @error('short_name') is-invalid @enderror" 
                                       id="short_name" 
                                       name="short_name" 
                                       value="{{ old('short_name', $bank->short_name) }}">
                                @error('short_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="api_enabled" 
                                           name="api_enabled" 
                                           value="1"
                                           {{ old('api_enabled', $bank->api_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="api_enabled">
                                        API Enabled
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="status" 
                                           name="status" 
                                           value="1"
                                           {{ old('status', $bank->status) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status">
                                        Active Status
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Update Bank
                        </button>
                        <a href="{{ route('admin.banks.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
