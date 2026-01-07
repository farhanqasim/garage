@extends('layouts.app')

@section('title', 'View Purchase')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Purchase Details</h4>
            <h6>View purchase order #{{ $purchase->invoice_no }}</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-primary me-2">
                <i class="ti ti-edit me-1"></i> Edit
            </a>
            <a href="{{ route('all_purchases') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">
                    <!-- Business Information Panel -->
                    <div class="mb-4 p-3 rounded" style="border: 1px solid #0d6efd; background: #f8f9fa;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded p-2 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ti ti-file-invoice fs-20"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 fw-bold">{{ setting_value('logo_text', 'MUBARAK TRADERS') }}</h4>
                                    <p class="mb-0 text-primary" style="font-size: 13px;">
                                        <i class="ti ti-phone me-1"></i>
                                        HELPLINE: <span>{{ setting_value('helpline', '+92-335-08-999-08') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="mb-1">
                                    <span class="text-primary fw-bold" style="font-size: 16px;">INV #{{ $purchase->invoice_no }}</span>
                                </div>
                                <div style="font-size: 13px; color: #6c757d;">
                                    {{ $purchase->purchase_date->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier/Customer Information -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">SUPPLIER NAME</label>
                            <div class="form-control" style="border-radius: 6px; min-height: 38px; display: flex; align-items: center;">
                                {{ $purchase->supplier->names[0] ?? 'N/A' }}
                                @if($purchase->supplier && $purchase->supplier->company)
                                    - {{ $purchase->supplier->company }}
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">BRANCH</label>
                            <div class="form-control" style="border-radius: 6px; min-height: 38px; display: flex; align-items: center;">
                                {{ $purchase->branch->branch_name ?? 'N/A' }}
                                @if($purchase->branch && $purchase->branch->branch_code)
                                    ({{ $purchase->branch->branch_code }})
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">MOBILE NUMBER</label>
                            <div class="form-control" style="border-radius: 6px; min-height: 38px; display: flex; align-items: center;">
                                {{ $purchase->supplier->phones[0] ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">REFERENCE</label>
                            <div class="form-control" style="border-radius: 6px; min-height: 38px; display: flex; align-items: center;">
                                {{ $purchase->reference ?? '-' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">STATUS</label>
                            <div>
                                <span class="badge 
                                    {{ $purchase->status == 'received' ? 'bg-success' : ($purchase->status == 'pending' ? 'bg-warning' : 'bg-info') }} 
                                    p-2 px-3 rounded-pill">
                                    {{ strtoupper($purchase->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">PURCHASE DATE</label>
                            <div class="form-control" style="border-radius: 6px; min-height: 38px; display: flex; align-items: center;">
                                {{ $purchase->purchase_date->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 fw-bold">Items ({{ $purchase->items->count() }})</h5>
                        </div>
                        
                        @if($purchase->items->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-uppercase" style="font-size: 11px; font-weight: 700;">#</th>
                                        <th class="text-uppercase" style="font-size: 11px; font-weight: 700;">Item Name</th>
                                        <th class="text-uppercase" style="font-size: 11px; font-weight: 700;">Quantity</th>
                                        <th class="text-uppercase" style="font-size: 11px; font-weight: 700;">Unit</th>
                                        <th class="text-uppercase" style="font-size: 11px; font-weight: 700;">Rate</th>
                                        <th class="text-uppercase" style="font-size: 11px; font-weight: 700;">Discount</th>
                                        <th class="text-uppercase" style="font-size: 11px; font-weight: 700;">Tax %</th>
                                        <th class="text-uppercase text-end" style="font-size: 11px; font-weight: 700;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->items as $index => $purchaseItem)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @php
                                                $item = $purchaseItem->item;
                                                $itemName = $item->short_disc ?? $item->pro_dis ?? $item->bar_code ?? 'N/A';
                                                if ($item->partnumber_item) {
                                                    $itemName = $item->partnumber_item->name ?? $itemName;
                                                }
                                                if ($item->category) {
                                                    $itemName .= ' - ' . $item->category->name;
                                                }
                                            @endphp
                                            <div class="fw-bold">{{ $itemName }}</div>
                                            @if($item->bar_code)
                                                <small class="text-muted">Barcode: {{ $item->bar_code }}</small>
                                            @endif
                                        </td>
                                        <td>{{ number_format($purchaseItem->quantity, 2) }}</td>
                                        <td>{{ $purchaseItem->unit ?? 'Unit' }}</td>
                                        <td>Rs {{ number_format($purchaseItem->rate, 2) }}</td>
                                        <td>Rs {{ number_format($purchaseItem->discount, 2) }}</td>
                                        <td>{{ number_format($purchaseItem->tax_percentage, 2) }}%</td>
                                        <td class="text-end fw-bold">Rs {{ number_format($purchaseItem->total_cost, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>No items found in this purchase.
                        </div>
                        @endif
                    </div>

                    <!-- Amount Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6 offset-md-6">
                            <div class="border rounded p-3" style="background: #f8f9fa;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-uppercase" style="font-size: 12px;">Subtotal</span>
                                    <span class="fw-bold">Rs {{ number_format($purchase->subtotal, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-uppercase" style="font-size: 12px;">Order Tax</span>
                                    <span class="fw-bold">Rs {{ number_format($purchase->order_tax, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-uppercase" style="font-size: 12px;">Discount</span>
                                    <span class="fw-bold text-danger">- Rs {{ number_format($purchase->discount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-uppercase" style="font-size: 12px;">Shipping</span>
                                    <span class="fw-bold">Rs {{ number_format($purchase->shipping, 2) }}</span>
                                </div>
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold fs-16 text-uppercase">Grand Total</div>
                                            <div class="small text-muted">Total Payable Amount</div>
                                        </div>
                                        <div class="fw-bold fs-24 text-primary">Rs {{ number_format($purchase->grand_total, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    @if($purchase->description)
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Description</label>
                        <div class="form-control" style="border-radius: 6px; min-height: 80px; display: flex; align-items: flex-start; padding: 12px;">
                            {{ $purchase->description }}
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('all_purchases') }}" class="btn btn-secondary">Back to List</a>
                        <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-primary">
                            <i class="ti ti-edit me-1"></i> Edit Purchase
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

