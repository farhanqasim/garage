@extends('layouts.app')
@section('title', __('Edit Bank Transaction'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h2 class="fw-bold">Edit Bank Transaction</h2>
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
                <form action="{{ route('admin.bank-transactions.update', $bankTransaction->id) }}" method="POST">
                    @csrf
                    @method('PUT')

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
                                        <option value="{{ $account->id }}" {{ old('bank_account_id', $bankTransaction->bank_account_id) == $account->id ? 'selected' : '' }}>
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
                                       value="{{ old('transaction_date', $bankTransaction->transaction_date->format('Y-m-d')) }}" 
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
                                    <option value="credit" {{ old('type', $bankTransaction->type) == 'credit' ? 'selected' : '' }}>Credit (Money In)</option>
                                    <option value="debit" {{ old('type', $bankTransaction->type) == 'debit' ? 'selected' : '' }}>Debit (Money Out)</option>
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
                                       value="{{ old('amount', $bankTransaction->amount) }}" 
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
                                       value="{{ old('statement_reference', $bankTransaction->statement_reference) }}"
                                       placeholder="Bank statement reference number">
                                @error('statement_reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="matched_payment_id" class="form-label">Match with Payment</label>
                                <select class="form-control @error('matched_payment_id') is-invalid @enderror" 
                                        id="matched_payment_id" 
                                        name="matched_payment_id">
                                    <option value="">No Payment</option>
                                    @foreach($payments as $payment)
                                        <option value="{{ $payment->id }}" {{ old('matched_payment_id', $bankTransaction->matched_payment_id) == $payment->id ? 'selected' : '' }}>
                                            Payment #{{ $payment->id }} - {{ number_format($payment->amount, 2) }} PKR ({{ $payment->payment_date->format('Y-m-d') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('matched_payment_id')
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
                                          placeholder="Transaction description">{{ old('description', $bankTransaction->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    @if($bankTransaction->reconciled)
                        <div class="alert alert-info">
                            <strong>Reconciled:</strong> This transaction was reconciled on {{ $bankTransaction->reconciled_at->format('Y-m-d H:i') }} 
                            @if($bankTransaction->reconciledBy)
                                by {{ $bankTransaction->reconciledBy->name }}
                            @endif
                        </div>
                    @endif

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Update Transaction
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
