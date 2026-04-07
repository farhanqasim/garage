@extends('layouts.app')

@section('title', 'Edit Sale')

@section('content')
<style>
    .invoice-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        border-top: 8px solid #2563eb;
    }
    .modern-input,
    .modern-select {
        width: 100%;
        padding: 16px;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        font-weight: 800;
        font-size: 14px;
        color: #1f2937;
        transition: all 0.2s;
    }
    .modern-input:focus,
    .modern-select:focus {
        border-color: #2563eb;
        outline: none;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .modern-label {
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        color: #9ca3af;
        margin-left: 4px;
        margin-bottom: 6px;
        display: block;
        letter-spacing: 0.05em;
    }
    .invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 16px;
        margin-bottom: 16px;
        gap: 16px;
    }
    .invoice-header-left h1 {
        font-size: 28px;
        font-weight: 900;
        color: #1e3a8a;
        text-transform: uppercase;
        line-height: 1.1;
        letter-spacing: -0.01em;
    }
    .invoice-header-left .subtitle {
        font-size: 10px;
        color: #2563eb;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-top: 4px;
    }
    .invoice-header-left .helpline {
        font-size: 14px;
        color: #6b7280;
        font-weight: 700;
        margin-top: 8px;
    }
    .invoice-header-right {
        text-align: right;
    }
    .invoice-header-right .invoice-number {
        font-size: 18px;
        font-weight: 900;
        color: #2563eb;
    }
    .invoice-header-right .invoice-date {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        margin-top: 4px;
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Edit Sale</h4>
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
                        
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill" style="border: 1px solid #0d6efd; background: #f8f9fa;">
                                <i class="ti ti-user me-2 text-muted"></i>
                                <span class="fw-bold me-2 text-uppercase" style="font-size: 12px;">ACTIVE BRANCH:</span>
                                <span style="font-weight: 900; color: #1e3a8a;">
                                    {{ $sale->branch->branch_name ?? '—' }}
                                    @if(!empty($sale->branch->branch_code)) ({{ $sale->branch->branch_code }}) @endif
                                </span>
                            </div>
                        </div>

                        <div class="invoice-card p-5 mb-4">
                        <div class="invoice-header">
                            <div class="invoice-header-left">
                                <div class="subtitle">SOFT AUTO OIL & SPARE PARTS SPECIALIST</div>
                                <div class="helpline"><i class="ti ti-phone me-1"></i> HELPLINE: {{ setting_value('helpline', '+92-335-08-999-08') }}</div>
                            </div>
                            <div class="invoice-header-right">
                                <div class="invoice-number">INV #{{ str_pad((int) $sale->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="invoice-date">{{ date('d/m/Y, h:i:s A') }}</div>
                                <div class="d-flex align-items-center justify-content-end gap-2 mt-2" style="flex-wrap: wrap;">
                                    <div class="d-inline-block position-relative" style="vertical-align: middle;">
                                        <button type="button"
                                            class="custom-3step-switch switch-sale"
                                            style="position: relative; width: 80px; height: 30px; border-radius: 15px; cursor: default; transition: all 0.3s ease; margin-top: 0; border: none; padding: 0; outline: none; background: #2563eb;"
                                            aria-label="S/E/O toggle"
                                            disabled>
                                            <span class="switch-slider switch-position-0" style="position: absolute; width: 24px; height: 24px; background: white; border-radius: 50%; top: 3px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.2); pointer-events: none;"></span>
                                            <span class="switch-indicators" style="position: absolute; width: 100%; height: 100%; display: flex; justify-content: space-around; align-items: center; pointer-events: none; left: 0; top: 0;">
                                                <span style="font-size: 8px; color: rgba(255,255,255,0.5);">S</span>
                                                <span style="font-size: 8px; color: rgba(255,255,255,0.5);">E</span>
                                                <span style="font-size: 8px; color: rgba(255,255,255,0.5);">O</span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="modern-label">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="modern-select" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        @php
                                            $customerName = $customer->names[0] ?? 'N/A';
                                            $displayText = $customerName;
                                            $company = $customer->company ?? ($customer->company_name ?? null);
                                            if (!empty($company)) {
                                                $displayText .= ' - ' . $company;
                                            }
                                            $phone = !empty($customer->phones[0]) ? $customer->phones[0] : null;
                                            if (!empty($phone)) {
                                                $displayText .= ' - ' . $phone;
                                            }
                                        @endphp
                                        <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                                            {{ $displayText }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" id="branch_id" class="modern-select" required>
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
                                <label class="modern-label">Sale Date <span class="text-danger">*</span></label>
                                <input type="date" name="sale_date" id="sale_date" class="modern-input" value="{{ $sale->sale_date->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Reference</label>
                                <input type="text" name="reference" id="reference" class="modern-input" value="{{ $sale->reference ?? '' }}" placeholder="Enter reference number">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="modern-label">Status</label>
                                <select name="status" id="status" class="modern-select">
                                    <option value="pending" {{ $sale->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ $sale->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="modern-label">Discount</label>
                                <input type="number" name="discount" id="discount" class="modern-input" value="{{ $sale->discount ?? 0 }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="modern-label">Order Tax</label>
                                <input type="number" name="order_tax" id="order_tax" class="modern-input" value="{{ $sale->order_tax ?? 0 }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="modern-label">Shipping</label>
                                <input type="number" name="shipping" id="shipping" class="modern-input" value="{{ $sale->shipping ?? 0 }}" step="0.01" min="0" placeholder="0.00">
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
                        </div><!-- /invoice-card -->
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
