@extends('layouts.app')
@section('title', __('Create Bank Account'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h2 class="fw-bold">Create Bank Account</h2>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.bank-accounts.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_id" class="form-label">Bank <span class="text-danger">*</span></label>
                                <select class="form-control searchable-select @error('bank_id') is-invalid @enderror" 
                                        id="bank_id" 
                                        name="bank_id" 
                                        required>
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->name }} @if($bank->short_name)({{ $bank->short_name }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('account_type') is-invalid @enderror" 
                                        id="account_type" 
                                        name="account_type" 
                                        required>
                                    <option value="bank" {{ old('account_type', 'bank') == 'bank' ? 'selected' : '' }}>Current Account</option>
                                    <option value="cash" {{ old('account_type') == 'cash' ? 'selected' : '' }}>Saving Account</option>
                                </select>
                                @error('account_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_title" class="form-label">Account Title <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('account_title') is-invalid @enderror" 
                                       id="account_title" 
                                       name="account_title" 
                                       value="{{ old('account_title') }}" 
                                       required>
                                @error('account_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('account_number') is-invalid @enderror" 
                                       id="account_number" 
                                       name="account_number" 
                                       value="{{ old('account_number') }}" 
                                       required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="iban" class="form-label">IBAN</label>
                                <input type="text" 
                                       class="form-control @error('iban') is-invalid @enderror" 
                                       id="iban" 
                                       name="iban" 
                                       value="{{ old('iban') }}">
                                @error('iban')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="branch_code" class="form-label">Branch Code</label>
                                <input type="text" 
                                       class="form-control @error('branch_code') is-invalid @enderror" 
                                       id="branch_code" 
                                       name="branch_code" 
                                       value="{{ old('branch_code') }}">
                                @error('branch_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" 
                                       class="form-control @error('ifsc_code') is-invalid @enderror" 
                                       id="ifsc_code" 
                                       name="ifsc_code" 
                                       value="{{ old('ifsc_code') }}">
                                @error('ifsc_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="opening_balance" class="form-label">Opening Balance</label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0"
                                       class="form-control @error('opening_balance') is-invalid @enderror" 
                                       id="opening_balance" 
                                       name="opening_balance" 
                                       value="{{ old('opening_balance', 0) }}">
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_primary" 
                                           name="is_primary" 
                                           value="1"
                                           {{ old('is_primary') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_primary">
                                        Mark as Primary Account
                                    </label>
                                    <small class="form-text text-muted d-block">Only one account per bank can be primary</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
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
                            <i class="ti ti-check me-1"></i>Create Bank Account
                        </button>
                        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
