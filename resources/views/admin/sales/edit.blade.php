@extends('layouts.app')

@section('title', 'Edit Sale')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Edit Sale</h4>
            <h6>Update sale information</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('all_sales') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="editSaleForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->names[0] ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" id="branch_id" class="form-select" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $sale->branch_id == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sale Date <span class="text-danger">*</span></label>
                                <input type="date" name="sale_date" id="sale_date" class="form-control" value="{{ $sale->sale_date->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Reference</label>
                                <input type="text" name="reference" id="reference" class="form-control" value="{{ $sale->reference ?? '' }}" placeholder="Enter reference number">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="pending" {{ $sale->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ $sale->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Discount</label>
                                <input type="number" name="discount" id="discount" class="form-control" value="{{ $sale->discount ?? 0 }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Order Tax</label>
                                <input type="number" name="order_tax" id="order_tax" class="form-control" value="{{ $sale->order_tax ?? 0 }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Shipping</label>
                                <input type="number" name="shipping" id="shipping" class="form-control" value="{{ $sale->shipping ?? 0 }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>

                        <!-- Items Summary (Read-only) -->
                        <div class="mb-4">
                            <h5 class="mb-3">Sale Items</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>Rate</th>
                                            <th>Discount</th>
                                            <th>Tax %</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sale->saleItems as $item)
                                        <tr>
                                            <td>
                                                @php
                                                    $itemName = $item->item->short_disc ?? $item->item->pro_dis ?? $item->item->bar_code ?? 'N/A';
                                                    if ($item->item->partnumber_item) {
                                                        $itemName = $item->item->partnumber_item->name ?? $itemName;
                                                    }
                                                @endphp
                                                {{ $itemName }}
                                            </td>
                                            <td>{{ number_format($item->quantity, 2) }}</td>
                                            <td>{{ $item->unit ?? 'pcs' }}</td>
                                            <td>Rs {{ number_format($item->rate, 2) }}</td>
                                            <td>Rs {{ number_format($item->discount ?? 0, 2) }}</td>
                                            <td>{{ number_format($item->tax_percentage ?? 0, 2) }}%</td>
                                            <td>Rs {{ number_format($item->total, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Totals Summary -->
                        <div class="row mb-4">
                            <div class="col-md-6 offset-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">Subtotal</span>
                                    <span class="fw-bold">Rs {{ number_format($sale->subtotal, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Order Tax</span>
                                    <span>Rs {{ number_format($sale->order_tax ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Discount</span>
                                    <span class="text-success">- Rs {{ number_format($sale->discount ?? 0, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Shipping</span>
                                    <span>Rs {{ number_format($sale->shipping ?? 0, 2) }}</span>
                                </div>
                                <div class="bg-primary text-white p-3 rounded mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold fs-16">GRAND TOTAL</div>
                                            <div class="small">Total Amount</div>
                                        </div>
                                        <div class="fw-bold fs-24">Rs {{ number_format($sale->grand_total, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('all_sales') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Sale</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Recalculate grand total when discount, tax, or shipping changes
    $('#discount, #order_tax, #shipping').on('input', function() {
        calculateGrandTotal();
    });

    function calculateGrandTotal() {
        const subtotal = {{ $sale->subtotal }};
        const discount = parseFloat($('#discount').val()) || 0;
        const orderTax = parseFloat($('#order_tax').val()) || 0;
        const shipping = parseFloat($('#shipping').val()) || 0;
        
        const grandTotal = subtotal + orderTax - discount + shipping;
        
        // Update display (if you want to show it dynamically)
        // For now, the form will submit and server will recalculate
    }
});
</script>

@endsection
