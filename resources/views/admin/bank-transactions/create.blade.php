@extends('layouts.app')
@section('title', __('Create Bank Transaction'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h2 class="fw-bold">Create Bank Transaction</h2>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.bank-transactions.index') }}" class="btn btn-secondary">
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
                <form action="{{ route('admin.bank-transactions.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                                <select class="form-control @error('bank_account_id') is-invalid @enderror" 
                                        id="bank_account_id" 
                                        name="bank_account_id" 
                                        required>
                                    <option value="">Select Bank Account</option>
                                    @foreach($bankAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                            {{ $account->account_title }} - {{ $account->bank->name ?? 'N/A' }} ({{ $account->account_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('transaction_date') is-invalid @enderror" 
                                       id="transaction_date" 
                                       name="transaction_date" 
                                       value="{{ old('transaction_date', date('Y-m-d')) }}" 
                                       required>
                                @error('transaction_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="credit" {{ old('type') == 'credit' ? 'selected' : '' }}>Credit (Money In)</option>
                                    <option value="debit" {{ old('type') == 'debit' ? 'selected' : '' }}>Debit (Money Out)</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0.01"
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       value="{{ old('amount') }}" 
                                       required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="statement_reference" class="form-label">Statement Reference</label>
                                <input type="text" 
                                       class="form-control @error('statement_reference') is-invalid @enderror" 
                                       id="statement_reference" 
                                       name="statement_reference" 
                                       value="{{ old('statement_reference') }}"
                                       placeholder="Bank statement reference number">
                                @error('statement_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3"
                                          placeholder="Transaction description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Create Transaction
                        </button>
                        <a href="{{ route('admin.bank-transactions.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
