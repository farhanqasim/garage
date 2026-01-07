@extends('layouts.app')

@section('title', 'Edit Purchase')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Edit Purchase</h4>
            <h6>Update purchase order</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('all_purchases') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('purchases.update', $purchase->id) }}" method="POST" id="purchaseForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- ACTIVE BRANCH Selector -->
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill" style="border: 1px solid #0d6efd; background: #f8f9fa;">
                                <i class="ti ti-user me-2 text-muted"></i>
                                <span class="fw-bold me-2 text-uppercase" style="font-size: 12px;">ACTIVE BRANCH:</span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-primary p-0 text-decoration-none dropdown-toggle fw-bold" type="button" id="branchDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 14px;">
                                        <span id="selectedBranchName">{{ $purchase->branch->branch_name ?? 'Select Branch' }}</span>
                                        @if($purchase->branch && $purchase->branch->branch_code)
                                            <span id="selectedBranchCode"> ({{ $purchase->branch->branch_code }})</span>
                                        @endif
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="branchDropdown">
                                        @foreach($branches as $branch)
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="selectPurchaseBranch({{ $branch->id }}, '{{ $branch->branch_name }}', '{{ $branch->branch_code ?? '' }}')">
                                                {{ $branch->branch_name }} 
                                                @if($branch->branch_code) ({{ $branch->branch_code }}) @endif
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <input type="hidden" name="branch_id" id="purchaseBranchId" value="{{ $purchase->branch_id }}" required>
                        </div>

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
                                            HELPLINE: <span id="helplineNumber">{{ setting_value('helpline', '+92-335-08-999-08') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="mb-1">
                                        <span class="text-primary fw-bold" style="font-size: 16px;" id="purchase-number">INV #{{ $purchase->invoice_no }}</span>
                                    </div>
                                    <div style="font-size: 13px; color: #6c757d;">
                                        <span id="currentDateTime">{{ $purchase->purchase_date->format('d/m/Y, H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden purchase date field -->
                        <input type="hidden" name="purchase_date" id="purchase_date" value="{{ $purchase->purchase_date->format('Y-m-d') }}" required>

                        <!-- Supplier/Customer Information -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">CUSTOMER NAME</label>
                                <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required style="border-radius: 6px;">
                                    <option value="">Party Name</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                                data-name="{{ $supplier->names[0] ?? '' }}" 
                                                data-phone="{{ $supplier->phones[0] ?? '' }}"
                                                data-company="{{ $supplier->company ?? '' }}"
                                                data-address="{{ $supplier->address ?? '' }}"
                                                data-area="{{ $supplier->area ?? '' }}"
                                                {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->names[0] ?? 'N/A' }} @if($supplier->company) - {{ $supplier->company }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">MOBILE NUMBER</label>
                                <input type="text" id="supplier_mobile" name="supplier_mobile" class="form-control" placeholder="03xx..." style="border-radius: 6px;" value="{{ $purchase->supplier->phones[0] ?? '' }}">
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">ADDRESS</label>
                                <input type="text" id="supplier_address" name="supplier_address" class="form-control" placeholder="Shop/House #" style="border-radius: 6px;" value="{{ $purchase->supplier->address ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">AREA</label>
                                <input type="text" id="supplier_area" name="supplier_area" class="form-control" placeholder="Location/City" style="border-radius: 6px;" value="{{ $purchase->supplier->area ?? '' }}">
                            </div>
                        </div>

                        <!-- Reference -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">REFERENCE</label>
                                <input type="text" name="reference" id="reference" class="form-control" placeholder="Enter reference number" style="border-radius: 6px;" value="{{ $purchase->reference }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">STATUS</label>
                                <select name="status" id="status" class="form-control" style="border-radius: 6px;">
                                    <option value="pending" {{ $purchase->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="ordered" {{ $purchase->status == 'ordered' ? 'selected' : '' }}>Ordered</option>
                                    <option value="received" {{ $purchase->status == 'received' ? 'selected' : '' }}>Received</option>
                                </select>
                            </div>
                        </div>

                        <!-- Items Summary Section -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">ITEMS SUMMARY</h5>
                            <div id="items-summary-container" class="text-center py-5" style="background: #f8f9fa; border-radius: 8px; min-height: 200px;">
                                @if($purchase->items->count() > 0)
                                <div id="items-list">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Qty</th>
                                                    <th>Unit</th>
                                                    <th>Rate</th>
                                                    <th>Discount</th>
                                                    <th>Tax %</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-tbody">
                                                @foreach($purchase->items as $purchaseItem)
                                                <tr data-item-id="{{ $purchaseItem->item_id }}" data-row-id="{{ $purchaseItem->id }}">
                                                    <td>{{ $purchaseItem->item->name ?? $purchaseItem->item->bar_code }}</td>
                                                    <td>{{ $purchaseItem->quantity }}</td>
                                                    <td>{{ $purchaseItem->unit }}</td>
                                                    <td>Rs {{ number_format($purchaseItem->rate, 2) }}</td>
                                                    <td>Rs {{ number_format($purchaseItem->discount, 2) }}</td>
                                                    <td>{{ number_format($purchaseItem->tax_percentage, 2) }}%</td>
                                                    <td>Rs {{ number_format($purchaseItem->total_cost, 2) }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger remove-item" data-row-id="{{ $purchaseItem->id }}">
                                                            <i class="ti ti-x"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="empty-items-state" style="display: none;">
                                    <i class="ti ti-package fs-48 text-muted mb-3" style="display: block;"></i>
                                    <p class="text-muted mb-0">ABHI KOI ITEM NAHI HAI</p>
                                </div>
                                @else
                                <div id="empty-items-state">
                                    <i class="ti ti-package fs-48 text-muted mb-3" style="display: block;"></i>
                                    <p class="text-muted mb-0">ABHI KOI ITEM NAHI HAI</p>
                                </div>
                                <div id="items-list" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Qty</th>
                                                    <th>Unit</th>
                                                    <th>Rate</th>
                                                    <th>Discount</th>
                                                    <th>Tax %</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-tbody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Amount Summary -->
                        <div class="row mb-4">
                            <div class="col-md-6 offset-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">GROSS AMOUNT</span>
                                    <span class="fw-bold" id="gross-amount">Rs {{ number_format($purchase->subtotal, 2) }}</span>
                                </div>
                                <div class="bg-primary text-white p-3 rounded mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold fs-16">GRAND TOTAL</div>
                                            <div class="small">Total Payable Amount</div>
                                        </div>
                                        <div class="fw-bold fs-24" id="grand-total">Rs {{ number_format($purchase->grand_total, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="order_tax" id="order_tax" value="{{ $purchase->order_tax }}">
                        <input type="hidden" name="discount" id="discount" value="{{ $purchase->discount }}">
                        <input type="hidden" name="shipping" id="shipping" value="{{ $purchase->shipping }}">

                        <!-- Add Item Button -->
                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-primary btn-lg" id="add-new-item-btn">
                                <i class="ti ti-plus me-2"></i>ADD NEW ITEM
                            </button>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('all_purchases') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check me-1"></i> Update Purchase
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include the same modals and scripts from create view -->
@include('admin.purchases.modals.item-search-modal')
@include('admin.purchases.modals.add-item-modal')

@push('styles')
<style>
    .filter-chip {
        background: #fff;
        border: 1px solid #ddd;
        color: #333;
        transition: all 0.2s;
    }
    .filter-chip:hover {
        background: #f0f0f0;
        border-color: #999;
    }
    .filter-chip.active {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Load existing items into purchaseItems array
    let purchaseItems = [];
    let itemCounter = 0;

    @foreach($purchase->items as $purchaseItem)
    purchaseItems.push({
        id: itemCounter++,
        item_id: {{ $purchaseItem->item_id }},
        name: '{{ addslashes($purchaseItem->item->name ?? $purchaseItem->item->bar_code) }}',
        quantity: {{ $purchaseItem->quantity }},
        unit: '{{ $purchaseItem->unit }}',
        rate: {{ $purchaseItem->rate }},
        discount: {{ $purchaseItem->discount }},
        tax_percentage: {{ $purchaseItem->tax_percentage }},
        tax_amount: {{ $purchaseItem->tax_amount }},
        total: {{ $purchaseItem->total_cost }}
    });
    @endforeach

    // Same JavaScript functions as create view - copy from create.blade.php
    // Supplier change handler
    $('#supplier_id').on('change', function() {
        const selected = $(this).find('option:selected');
        const name = selected.data('name') || '';
        const phone = selected.data('phone') || '';
        const address = selected.data('address') || '';
        const area = selected.data('area') || '';
        
        $('#supplier_mobile').val(phone);
        $('#supplier_address').val(address);
        $('#supplier_area').val(area);
    });

    // Branch selection
    function selectPurchaseBranch(branchId, branchName, branchCode) {
        $('#selectedBranchName').text(branchName);
        if (branchCode) {
            if ($('#selectedBranchCode').length) {
                $('#selectedBranchCode').text(' (' + branchCode + ')');
            } else {
                $('#selectedBranchName').after('<span id="selectedBranchCode"> (' + branchCode + ')</span>');
            }
        } else {
            $('#selectedBranchCode').remove();
        }
        $('#purchaseBranchId').val(branchId);
    }
    window.selectPurchaseBranch = selectPurchaseBranch;

    // Calculate totals function
    function calculateTotals() {
        let itemTotal = 0;
        purchaseItems.forEach(function(item) {
            itemTotal += parseFloat(item.total);
        });

        const orderTax = parseFloat($('#order_tax').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        const shipping = parseFloat($('#shipping').val()) || 0;

        const grossTotal = itemTotal;
        const grandTotal = itemTotal + orderTax - discount + shipping;

        $('#gross-amount').text('Rs ' + parseFloat(grossTotal).toFixed(2));
        $('#grand-total').text('Rs ' + parseFloat(grandTotal).toFixed(2));
    }

    // Remove item
    $(document).on('click', '.remove-item', function() {
        const rowId = $(this).data('row-id');
        purchaseItems = purchaseItems.filter(item => item.id != rowId);
        $(this).closest('tr').remove();
        
        if ($('#items-tbody tr').length === 0) {
            $('#empty-items-state').show();
            $('#items-list').hide();
        }
        
        calculateTotals();
    });

    // Form submission - same as create but with PUT method
    $('#purchaseForm').on('submit', function(e) {
        e.preventDefault();

        if (purchaseItems.length === 0) {
            alert('Please add at least one item');
            return;
        }

        const itemsData = purchaseItems.map(function(item) {
            return {
                item_id: item.item_id,
                quantity: item.quantity,
                unit: item.unit,
                rate: item.rate,
                discount: item.discount,
                tax_percentage: item.tax_percentage
            };
        });

        const formData = new FormData(this);
        itemsData.forEach(function(item, index) {
            Object.keys(item).forEach(function(key) {
                formData.append(`items[${index}][${key}]`, item[key]);
            });
        });

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(response) {
                window.location.href = '{{ route("all_purchases") }}';
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = '';
                    Object.values(xhr.responseJSON.errors).forEach(function(error) {
                        errors += error[0] + '\n';
                    });
                    alert(errors);
                } else {
                    alert('Error updating purchase. Please try again.');
                }
            }
        });
    });

    // Add item modal and other functions - copy rest from create.blade.php
    // Note: You should copy the complete JavaScript from create.blade.php for full functionality
});
</script>
@endpush
@endsection

