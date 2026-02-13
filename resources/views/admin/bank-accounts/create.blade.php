@extends('layouts.app')
@section('title', __('Create Bank Account'))
@section('content')
@include('admin.partials.vyapar-bank-style')
<div class="content vyapar-bank-page">
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <h2><i class="ti ti-wallet"></i> Add Bank Account</h2>
        <a href="{{ route('admin.banks.index', ['tab' => 'accounts']) }}" class="btn btn-primary">
            <i class="ti ti-arrow-left me-1"></i>Back
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card vyapar-form-card">
        <div class="card-header">Bank Account Details</div>
        <div class="card-body">
                <form action="{{ route('admin.bank-accounts.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                                @if($selectedBranchId)
                                    {{-- Branch owner: show readonly select + hidden input to ensure value is submitted --}}
                                    <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                                    <select class="form-control @error('branch_id') is-invalid @enderror" 
                                            id="branch_id" 
                                            disabled
                                            style="background-color: #e9ecef; cursor: not-allowed;">
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ ($selectedBranchId == $branch->id) ? 'selected' : '' }}>
                                                {{ $branch->branch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    {{-- Admin: normal select --}}
                                    <select class="form-control @error('branch_id') is-invalid @enderror" 
                                            id="branch_id" 
                                            name="branch_id" 
                                            required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->branch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id" class="form-label">User (Optional)</label>
                                <select class="form-control @error('user_id') is-invalid @enderror" 
                                        id="user_id" 
                                        name="user_id">
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} @if($user->email)({{ $user->email }})@endif @if($user->role ?? null) - {{ ucwords(str_replace('_', ' ', $user->role)) }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_logo" class="form-label">Bank logo (optional)</label>
                                <input type="file" 
                                       class="form-control @error('bank_logo') is-invalid @enderror" 
                                       id="bank_logo" 
                                       name="bank_logo" 
                                       accept="image/*">
                                <small class="form-text text-muted">Upload logo for the selected bank. Used in dropdown.</small>
                                @error('bank_logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_id" class="form-label">Bank <span class="text-danger">*</span></label>
                                <select class="form-control searchable-select @error('bank_id') is-invalid @enderror" 
                                        id="bank_id" 
                                        name="bank_id" 
                                        required>
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }} data-logo="{{ $bank->logo_url ?? '' }}">
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
                        <a href="{{ route('admin.banks.index', ['tab' => 'accounts']) }}" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const branchSelect = document.getElementById('branch_id');
            const userSelect = document.getElementById('user_id');
            const isBranchOwner = {{ $selectedBranchId ? 'true' : 'false' }};
            
            // If admin, load users when branch changes
            if (!isBranchOwner && branchSelect) {
                branchSelect.addEventListener('change', function() {
                    const branchId = this.value;
                    userSelect.innerHTML = '<option value="">Loading...</option>';
                    userSelect.disabled = true;
                    
                    if (branchId) {
                        fetch(`{{ route('admin.bank-accounts.branch-users', ['branchId' => '__BRANCH_ID__']) }}`.replace('__BRANCH_ID__', branchId), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            userSelect.innerHTML = '<option value="">Select User</option>';
                            if (data.success && data.users) {
                                data.users.forEach(function(user) {
                                    const option = document.createElement('option');
                                    option.value = user.id;
                                    const roleLabel = user.role ? ' - ' + user.role.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '';
                                    option.textContent = user.name + (user.email ? ' (' + user.email + ')' : '') + roleLabel;
                                    userSelect.appendChild(option);
                                });
                            }
                            userSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Error loading users:', error);
                            userSelect.innerHTML = '<option value="">Error loading users</option>';
                            userSelect.disabled = false;
                        });
                    } else {
                        userSelect.innerHTML = '<option value="">Select User</option>';
                        userSelect.disabled = false;
                    }
                });
            }
        });
</script>
@endpush
@endsection
