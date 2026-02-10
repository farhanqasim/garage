@extends('layouts.app')
@section('title', __('Create Bank'))
@section('content')
@include('admin.partials.vyapar-bank-style')
<div class="content vyapar-bank-page">
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h2><i class="ti ti-building-bank"></i> Add Bank</h2>
        <a href="{{ route('admin.banks.index') }}" class="btn btn-primary">
            <i class="ti ti-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card vyapar-form-card">
        <div class="card-header">Bank Details</div>
        <div class="card-body">
                <form action="{{ route('admin.banks.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
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
                                       value="{{ old('short_name') }}">
                                @error('short_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="logo" class="form-label">Bank Logo (optional)</label>
                                <input type="file" 
                                       class="form-control @error('logo') is-invalid @enderror" 
                                       id="logo" 
                                       name="logo" 
                                       accept="image/*">
                                <small class="form-text text-muted">Upload bank logo. Shown in bank accounts list.</small>
                                @error('logo')
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
                                           {{ old('api_enabled') ? 'checked' : '' }}>
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
                                           {{ old('status', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status">
                                        Active Status
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Create Bank
                        </button>
                        <a href="{{ route('admin.banks.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
        </div>
    </div>
</div>
@endsection
