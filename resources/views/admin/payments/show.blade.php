@extends('layouts.app')
@section('title', __('Payment Details'))
@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h2 class="fw-bold">Payment Details</h2>
            </div>
            <div class="page-btn">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Payments
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Payment ID:</strong>
                                <p class="text-muted">#{{ $payment->id }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <p>
                                    @php
                                        $statusColors = [
                                            'pending' => 'badge-warning',
                                            'paid' => 'badge-success',
                                            'failed' => 'badge-danger',
                                            'refunded' => 'badge-info'
                                        ];
                                        $statusColor = $statusColors[$payment->status] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge {{ $statusColor }}">{{ strtoupper($payment->status) }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>User:</strong>
                                <p>
                                    {{ $payment->user->name ?? 'N/A' }}
                                    @if($payment->user->email)
                                        <br><small class="text-muted">{{ $payment->user->email }}</small>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Order ID:</strong>
                                <p>
                                    @if($payment->order_id)
                                        <span class="badge badge-info">#{{ $payment->order_id }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Payment Method:</strong>
                                <p>
                                    @php
                                        $methodColors = [
                                            'card' => 'badge-primary',
                                            'bank' => 'badge-success',
                                            'wallet' => 'badge-warning',
                                            'cash' => 'badge-secondary'
                                        ];
                                        $color = $methodColors[$payment->payment_method] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge {{ $color }}">{{ strtoupper($payment->payment_method) }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Bank:</strong>
                                <p>
                                    @if($payment->bank)
                                        {{ $payment->bank->name }}
                                        @if($payment->bank->short_name)
                                            <small class="text-muted">({{ $payment->bank->short_name }})</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Transaction ID:</strong>
                                <p>
                                    @if($payment->transaction_id)
                                        <code>{{ $payment->transaction_id }}</code>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Amount:</strong>
                                <p class="h5 text-primary">
                                    {{ number_format($payment->amount, 2) }} {{ $payment->currency }}
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Created At:</strong>
                                <p class="text-muted">{{ $payment->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Paid At:</strong>
                                <p class="text-muted">
                                    @if($payment->paid_at)
                                        {{ $payment->paid_at->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">Not paid yet</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        @if($payment->status === 'pending')
                            <form action="{{ route('admin.payments.mark-paid', $payment->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Mark this payment as paid?');">
                                    <i class="ti ti-check me-1"></i>Mark as Paid
                                </button>
                            </form>
                            <form action="{{ route('admin.payments.mark-failed', $payment->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Mark this payment as failed?');">
                                    <i class="ti ti-x me-1"></i>Mark as Failed
                                </button>
                            </form>
                        @endif

                        @if($payment->status === 'paid')
                            <form action="{{ route('admin.payments.mark-refunded', $payment->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Mark this payment as refunded?');">
                                    <i class="ti ti-refund me-1"></i>Mark as Refunded
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary w-100">
                            <i class="ti ti-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
