@extends('layouts.app')
@section('title', __('Create Cash Account'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h2 class="fw-bold">Create Cash Account</h2>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.cash-accounts.index') }}" class="btn btn-secondary">
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
                <form action="{{ route('admin.cash-accounts.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id" class="form-label">User <span class="text-danger">*</span></label>
                                <select class="form-control searchable-select @error('user_id') is-invalid @enderror" 
                                        id="user_id" 
                                        name="user_id" 
                                        required>
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} @if($user->email)({{ $user->email }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Select the user for whom you want to create a cash account.</small>
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
                                <small class="form-text text-muted">Initial balance for this cash account (default: 0).</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Create Cash Account
                        </button>
                        <a href="{{ route('admin.cash-accounts.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
