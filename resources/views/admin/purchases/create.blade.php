@extends('layouts.app')

@section('title', 'Create Purchase')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Purchase</h4>
        </div>
        <div class="page-btn d-flex gap-2">
            <a href="{{ route('all_purchases') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- ACTIVE BRANCH Selector (Pill-shaped like Gemini design) -->
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill" style="border: 1px solid #0d6efd; background: #f8f9fa;">
                                <i class="ti ti-user me-2 text-muted"></i>
                                <span class="fw-bold me-2 text-uppercase" style="font-size: 12px;">ACTIVE BRANCH:</span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-primary p-0 text-decoration-none dropdown-toggle fw-bold" type="button" id="branchDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 14px;">
                                        <span id="selectedBranchName">{{ session('selected_branch_name', 'Select Branch') }}</span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="branchDropdown">
                                        @php
                                            $branches = \App\Models\Branch::where('status', 'active')->get();
                                            $currentBranchId = session('selected_branch_id');
                                        @endphp
                                        @foreach($branches as $branch)
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="selectPurchaseBranch({{ $branch->id }}, '{{ $branch->branch_name }}', '{{ $branch->branch_code ?? '' }}')">
                                                {{ $branch->branch_name }}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <input type="hidden" name="branch_id" id="purchaseBranchId" value="{{ session('selected_branch_id') }}" required>
                        </div>

                        <!-- Business Information Panel (Like Gemini Design) -->
                        <div class="mb-4 p-3 rounded" id="purchaseDocTypePanel" style="border: 1px solid #0d6efd; background: #f8f9fa;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded p-2 me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                        <i class="ti ti-file-invoice fs-20"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('home') }}" class="text-decoration-none text-dark d-inline-block" title="Home par jayein"><h4 class="mb-0 fw-bold">Barki Express</h4></a>
                                        <p class="mb-0 text-primary" style="font-size: 13px;">
                                            <i class="ti ti-phone me-1"></i>
                                            HELPLINE: <span id="helplineNumber">{{ setting_value('helpline', '+92-335-08-999-08') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 ms-auto flex-wrap">
                                    <div class="text-end">
                                        <div class="mb-1">
                                            @php
                                                $nextBillNum = str_pad((\App\Models\Purchase::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
                                                $nextPoNum = str_pad(\App\Models\Purchase::where('invoice_no', 'like', 'PO-%')->count(), 5, '0', STR_PAD_LEFT);
                                            @endphp
                                            <span class="text-primary fw-bold" style="font-size: 16px;" id="purchase-number" data-bill-number="{{ $nextBillNum }}" data-po-number="{{ $nextPoNum }}">Bill #{{ $nextBillNum }}</span>
                                        </div>
                                        <div style="font-size: 13px; color: #6c757d;">
                                            <span id="currentDateTime">{{ date('d/m/Y, H:i:s') }}</span>
                                        </div>
                                    </div>
                                    <!-- Toggle: Bill (off) / PO (on) - press = PO, press again = Bill -->
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <span class="small text-muted fw-semibold" id="purchaseDocLabelBill">Bill</span>
                                        <label class="mb-0 position-relative d-inline-block flex-shrink-0" style="width: 44px; height: 24px; flex-shrink: 0;">
                                            <input type="checkbox" class="form-check-input position-absolute top-0 start-0" id="purchaseOrderSwitch" value="1" style="width: 44px; height: 24px; cursor: pointer; opacity: 0; z-index: 2; margin: 0;" aria-label="Purchase Order On/Off">
                                            <span class="position-absolute top-0 start-0 rounded-pill bg-secondary" id="purchaseOrderTrack" style="width: 44px; height: 24px; transition: background 0.2s;"></span>
                                            <span class="position-absolute rounded-circle bg-white border shadow-sm" id="purchaseOrderThumb" style="width: 20px; height: 20px; top: 2px; left: 2px; transition: left 0.2s;"></span>
                                        </label>
                                        <span class="small text-muted fw-semibold" id="purchaseDocLabelPO">PO</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="is_purchase_order" id="isPurchaseOrderHidden" value="0">
                        
                        <!-- Hidden purchase date field -->
                        <input type="hidden" name="purchase_date" id="purchase_date" value="{{ date('Y-m-d') }}" required>

                        <!-- Supplier/Customer Information (Like Gemini Design) -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">SUPPLIER NAME</label>
                                <div class="input-group supplier-name-input-group">
                                    <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required style="border-radius: 6px 0 0 6px;">
                                        <option value="">Select vendor</option>
                                        @foreach($suppliers as $supplier)
                                            @php $products = is_array($supplier->business_detail ?? null) ? $supplier->business_detail : (json_decode($supplier->business_detail ?? '[]', true) ?? []); @endphp
                                            <option value="{{ $supplier->id }}" 
                                                    data-name="{{ $supplier->names[0] ?? '' }}" 
                                                    data-phone="{{ $supplier->phones[0] ?? '' }}"
                                                    data-company="{{ $supplier->company ?? '' }}"
                                                    data-address="{{ $supplier->address ?? '' }}"
                                                    data-area="{{ $supplier->area ?? '' }}"
                                                    data-products="{{ e(json_encode($products)) }}">
                                                {{ $supplier->company ?? '' }}@if($supplier->company ?? '') - @endif{{ $supplier->names[0] ?? 'N/A' }}@if(!empty($supplier->phones[0])) - {{ $supplier->phones[0] }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('suppliers.index') }}" id="supplier_edit_btn" class="btn btn-sm btn-outline-secondary supplier-edit-btn" style="border-radius: 0 6px 6px 0;" title="Edit vendor" target="_blank" data-base-url="{{ route('suppliers.index') }}"><i class="ti ti-edit"></i></a>
                                </div>
                                @error('supplier_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">MOBILE NUMBER</label>
                                <input type="hidden" id="supplier_mobile" name="supplier_mobile" value="">
                                <div class="input-group supplier-mobile-input-group">
                                    <select id="supplier_id_mobile" class="form-control" style="border-radius: 6px 0 0 6px;" title="Search mobile number">
                                        <option value="">Search mobile number</option>
                                        @foreach($suppliers as $supplier)
                                            @php $productsM = is_array($supplier->business_detail ?? null) ? $supplier->business_detail : (json_decode($supplier->business_detail ?? '[]', true) ?? []); @endphp
                                            <option value="{{ $supplier->id }}"
                                                    data-name="{{ $supplier->names[0] ?? '' }}"
                                                    data-phone="{{ $supplier->phones[0] ?? '' }}"
                                                    data-company="{{ $supplier->company ?? '' }}"
                                                    data-address="{{ $supplier->address ?? '' }}"
                                                    data-area="{{ $supplier->area ?? '' }}"
                                                    data-products="{{ e(json_encode($productsM)) }}">
                                                {{ $supplier->names[0] ?? 'N/A' }}@if($supplier->company) - {{ $supplier->company }}@endif @if(!empty($supplier->phones[0])) - {{ $supplier->phones[0] }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <a href="{{ route('suppliers.index') }}" id="supplier_edit_btn_mobile" class="btn btn-sm btn-outline-secondary supplier-edit-btn" style="border-radius: 0 6px 6px 0;" title="Edit vendor" target="_blank" data-base-url="{{ route('suppliers.index') }}"><i class="ti ti-edit"></i></a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">ADDRESS</label>
                                <input type="text" id="supplier_address" name="supplier_address" class="form-control" placeholder="Shop/House #" style="border-radius: 6px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">AREA</label>
                                <input type="text" id="supplier_area" name="supplier_area" class="form-control" placeholder="Location/City" style="border-radius: 6px;">
                            </div>
                        </div>

                        <!-- Reference (Optional) -->
                        <!-- Items Summary Section -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">ITEMS SUMMARY</h5>
                            <div id="items-summary-container" class="text-center py-5" style="background: #f8f9fa; border-radius: 8px; min-height: 200px;">
                                <div id="empty-items-state">
                                    <i class="ti ti-package fs-48 text-muted mb-3" style="display: block;"></i>
                                    <p class="text-muted mb-2">No items in cart</p>
                                    <p class="text-muted small mb-0" id="empty-state-hint">Select a branch first, then add items</p>
                                </div>
                                <div id="items-list" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                        <h6 class="mb-0 fw-bold text-muted small text-uppercase">Items</h6>
                                        <button type="button" class="btn btn-sm btn-primary purchase-print-all-labels" title="Sab items ke labels ek saath print"><i class="ti ti-printer me-1"></i> Print All</button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table mb-0 pehla-items-table" id="items-table">
                                            <thead>
                                                <tr class="pehla-items-thead">
                                                    <th class="pehla-th">WAREHOUSE</th>
                                                    <th class="pehla-th">ITEM</th>
                                                    <th class="pehla-th text-end">TOTAL</th>
                                                    <th class="pehla-th pehla-th-actions"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-tbody">
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="purchase-total-below" class="mt-3 p-3 border rounded bg-light fw-bold d-none" style="display: none;">
                                        <div id="purchase-total-below-row" class="d-flex justify-content-between align-items-center total-below-purchase" style="display: none;">
                                            <span>PURCHASE</span>
                                            <span><span id="subtotal-purchase-qty-below">0</span> QTY · <span id="subtotal-purchase-amount-below">Rs 0</span></span>
                                        </div>
                                        <div id="claim-totals-below-container"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden: gross/grand total kept for JS (Amount Summary block removed) -->
                        <span id="gross-amount" class="d-none">Rs 0</span>
                        <span id="previous-balance" class="d-none">Rs 0</span>
                        <span id="grand-total" class="d-none">Rs 0</span>

                        <!-- Barcode Scan (page-level: scan without opening modal first) -->
                        <div class="mb-3 p-3 rounded" style="background: #f0f9ff; border: 1px solid #bae6fd;">
                            <label class="form-label fw-bold mb-2 small text-uppercase text-primary" style="font-size: 11px;"><i class="ti ti-barcode me-1"></i> Barcode Scan</label>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <input type="text" id="purchase-page-barcode-input" class="form-control" placeholder="Scan barcode yahan (barcode scanner se scan karein)" autocomplete="off" style="max-width: 320px;">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="purchase-open-camera-scan" title="Camera se barcode scan karein"><i class="ti ti-camera me-1"></i> Camera</button>
                                <a href="#" class="btn btn-outline-secondary open-temporary-product-modal-btn" title="Add temporary product">
                                    <i class="ti ti-package me-1"></i> Temporary Products
                                </a>
                            </div>
                        </div>

                        <!-- Add Item, Claim Return, Return, Send Claim, Damage Product, Wrong Item -->
                        <div class="text-center mb-4 d-flex flex-wrap justify-content-center align-items-center gap-3">
                            <button type="button" class="btn btn-action-purchase btn-lg" id="add-new-item-btn">
                                <i class="ti ti-shopping-cart me-2"></i>PURCHASE ITEM
                            </button>
                            @hasanyrole('Super Admin|Admin|Manager')
                            <a href="#" class="btn btn-action-claim btn-lg" id="claim-receive-btn">
                                <i class="ti ti-truck-delivery me-2"></i>CLAIM RETURN
                            </a>
                            @endhasanyrole
                            <a href="#" class="btn btn-action-return btn-lg" id="return-btn">
                                <i class="ti ti-arrow-back-up me-2"></i>NEW RETURN
                            </a>
                        </div>

                        <!-- Supplier Payment Section (same design/colours as Sales) -->
                        <div class="row mb-4" id="payment-section">
                            <div class="col-12">
                                <div class="total-section">
                                    <div class="total-row">
                                        <p class="mb-0" style="font-size: 10px; font-weight: 700; text-transform: uppercase;">Total Items Amount</p>
                                        <p class="mb-0" style="font-size: 14px; font-weight: 700;">Rs <span id="payment-gross-amount">0</span></p>
                                    </div>
                                    <div class="discount-section">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="discount-label mb-0">Discount (Manual Edit)</p>
                                            <div class="d-flex align-items-center bg-white rounded-lg px-2 border" style="border-color: #bbf7d0 !important;">
                                                <span class="mr-2" style="font-size: 10px; font-weight: 900; color: #16a34a; background: #dcfce7; padding: 2px 6px; border-radius: 4px;">Rs</span>
                                                <input type="number" id="totalBillDiscount" value="0" min="0" step="0.01" class="bg-transparent text-right outline-none border-0" style="width: 64px; font-weight: 900; color: #16a34a; font-size: 14px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="net-payable">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="net-payable-label mb-0">Net Payable</p>
                                            <p class="net-payable-value mb-0">Rs <span id="net-payable-total">0</span></p>
                                        </div>
                                    </div>
                                    <div id="cash-paid-section" class="received-amount-section">
                                        <p class="received-amount-label mb-2" style="font-size: 9px; font-weight: 900; color: #6b7280; text-transform: uppercase; letter-spacing: 1px;">Cash Paid (Amount Rs)</p>
                                        <div id="purchaseCashPaidWrapper" class="space-y-2">
                                            <div class="payment-card border-blue-100 no-print">
                                                <div class="d-flex justify-content-between align-items-center gap-2">
                                                    <p class="mb-0" style="font-size: 10px; font-weight: 900; color: #374151; text-transform: uppercase;">Cash Entry</p>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <div class="d-flex align-items-center bg-white rounded-lg px-2 border" style="border-color: #e5e7eb !important;">
                                                            <span style="font-size: 10px; font-weight: 900; color: #9ca3af; text-transform: uppercase; margin-right: 6px;">Rs</span>
                                                            <input type="number" class="purchase-cash-input border-0 bg-transparent" value="0" min="0" step="0.01" style="width: 96px; font-weight: 900; text-align: right; outline: none; color: #1f2937; font-size: 14px;">
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-cash-row" title="Remove"><i class="ti ti-x"></i></button>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <label class="d-flex-1 cursor-pointer bg-white border border-dashed rounded-lg p-2 text-center block transition-all duration-300" style="border-color: #bfdbfe;">
                                                        <p class="status-text mb-0" style="font-size: 8px; font-weight: 900; color: #60a5fa; text-transform: uppercase;"><i class="ti ti-camera me-1"></i> Attach Photo</p>
                                                        <input type="file" accept="image/*" class="d-none purchase-cash-pic">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-2 no-print">
                                            <button type="button" class="btn btn-sm w-100" id="purchase-add-cash-row" style="background-color: #dbeafe; color: #2563eb; border: 1px dashed #93c5fd; border-radius: 12px; padding: 8px; font-size: 9px; font-weight: 900; text-transform: uppercase;">
                                                <i class="ti ti-plus me-1"></i> Add More Cash
                                            </button>
                                        </div>
                                    </div>
                                    <div id="bank-paid-section" class="space-y-1 pt-1 border-top" style="border-color: #e5e7eb; padding-top: 8px;">
                                        <p class="mb-2" style="font-size: 9px; font-weight: 900; color: #a855f7; text-transform: uppercase; letter-spacing: 1px; margin-left: 8px;">Bank Paid</p>
                                        <div id="purchaseBankPaidWrapper" class="space-y-2"></div>
                                        <div class="px-2 no-print mt-1">
                                            <button type="button" class="btn btn-sm w-100" id="purchase-add-bank-row" style="background-color: #f3e8ff; color: #9333ea; border: 1px dashed #c084fc; border-radius: 12px; padding: 8px; font-size: 9px; font-weight: 900; text-transform: uppercase;">
                                                <i class="ti ti-building-bank me-1"></i> Add Bank Payment
                                            </button>
                                        </div>
                                    </div>
                                    <div class="total-row" style="color: #ea580c;">
                                        <p class="mb-0" style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #ea580c;">Current Remaining</p>
                                        <p class="mb-0" style="font-size: 14px; font-weight: 700; color: #ea580c;">Rs <span id="purchase-current-remaining">0</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="discount" id="discount" value="0">
                        <input type="hidden" id="discount_type" value="fixed">
                        <input type="hidden" id="discount_percent" value="0">
                        <input type="hidden" name="payment_amount" id="payment_amount" value="0">
                        @php
                            $purchaseCashMethod = \App\Models\PaymentMethod::where('code','cash')->where('is_active',true)->first();
                            $purchaseBankMethod = \App\Models\PaymentMethod::where('code','bank_transfer')->where('is_active',true)->first() ?? \App\Models\PaymentMethod::where('requires_bank_account', true)->where('is_active', true)->first();
                        @endphp
                        <input type="hidden" name="payment_method_id" id="payment_method_id" value="{{ $purchaseCashMethod->id ?? '' }}">
                        <input type="hidden" id="purchase_cash_method_id" value="{{ $purchaseCashMethod->id ?? '' }}">
                        <input type="hidden" id="purchase_bank_method_id" value="{{ $purchaseBankMethod->id ?? '' }}">
                        <input type="hidden" name="bank_account_id" id="bank_account_id" value="">
                        <input type="hidden" name="payment_transaction_id" id="payment_transaction_id" value="">
                        <input type="hidden" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="payment_notes" id="payment_notes" value="">

                        <!-- Hidden fields for order tax, shipping -->
                        <input type="hidden" name="order_tax" id="order_tax" value="0">
                        <input type="hidden" name="shipping" id="shipping" value="0">
                        <input type="hidden" name="status" value="pending">

                        <!-- Submit Buttons -->
                        <input type="hidden" name="save_and_print" id="save_and_print" value="0">
                        <input type="hidden" name="save_and_send_pdf" id="save_and_send_pdf" value="0">
                        <input type="hidden" name="save_and_new" id="save_and_new" value="0">
                        <input type="hidden" name="print_format" id="print_format" value="a4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small fw-bold me-1">Print view:</span>
                                <div class="btn-group btn-group-sm" role="group" id="printFormatSwitch">
                                    <input type="radio" class="btn-check" name="print_format_radio" id="printFormatThermal" value="thermal" autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="printFormatThermal">Thermal</label>
                                    <input type="radio" class="btn-check" name="print_format_radio" id="printFormatA4" value="a4" checked autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="printFormatA4">A4</label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-info" id="btnSaveAndPrint" title="Save purchase and open print dialog">
                                    <i class="ti ti-printer me-1"></i> Save & Print
                                </button>
                                <button type="button" class="btn btn-primary" id="btnSendPdf" title="Save and send PDF link to supplier via WhatsApp">
                                    <i class="ti ti-send me-1"></i> Send PDF
                                </button>
                                <button type="button" class="btn btn-success" id="btnSaveNew" title="Save and stay on page to add another purchase">
                                    <i class="ti ti-check me-1"></i> Save & New
                                </button>
                                <a href="{{ route('all_purchases') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Supplier Modal (opened from "+ Add" in vendor dropdown) --}}
<div class="modal fade" id="loadFromPurchaseOrderModal" tabindex="-1" aria-labelledby="loadFromPurchaseOrderModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true" style="z-index: 10055;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" style="pointer-events: auto;">
        <div class="modal-content" style="pointer-events: auto;">
            <div class="modal-header">
                <h5 class="modal-title" id="loadFromPurchaseOrderModalLabel">Load from Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="pointer-events: auto;">
                <p class="text-muted small">Select one or more Purchase Orders to load items into this bill. Only lines with pending quantity will be added.</p>
                <div id="load-from-po-list"></div>
            </div>
            <div class="modal-footer" style="pointer-events: auto;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnLoadPOIntoBill">Load into Bill</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true" style="z-index: 10056;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" style="pointer-events: auto;">
        <div class="modal-content" style="pointer-events: auto;">
            <div class="modal-header" style="pointer-events: auto;">
                <h4 class="modal-title" id="addSupplierModalLabel">Add Supplier</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.suppliers.modals.create-supplier-form')
        </div>
    </div>
</div>

<!-- CLAIM RETURN Access List Modal (double-click on CLAIM RETURN button) -->
<div class="modal fade" id="claimReturnAccessModal" tabindex="-1" aria-labelledby="claimReturnAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header border-bottom" style="background: #f8f9fa;">
                <h5 class="modal-title" id="claimReturnAccessModalLabel">
                    <i class="ti ti-truck-delivery me-2"></i>CLAIM RETURN - Access List
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <p class="px-4 pt-3 mb-0 small text-muted">Manage which users can use CLAIM RETURN. Off = access revoked.</p>
                <div class="table-responsive px-4" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th>User</th>
                                <th>Roles</th>
                                <th class="text-center" style="width: 140px;">Access (On/Off)</th>
                            </tr>
                        </thead>
                        <tbody id="claimReturnAccessListBody">
                            <tr id="claimReturnAccessLoading">
                                <td colspan="3" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- YouTube-Style Search & Filter Modal for Purchase -->
<div class="modal fade" id="purchase-item-search-modal" tabindex="-1" aria-labelledby="purchaseItemSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header border-bottom" style="background: #f8f9fa;">
                <div class="page-title">
                    <h4 class="mb-0">Search & Filter Items</h4>
                    <small class="text-muted">Find items using advanced filters</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Search Bar -->
                <div class="p-4 border-bottom" style="background: #fff;">
                    <div class="position-relative">
                        <input type="text" id="purchase-item-search-input" class="form-control form-control-lg ps-5" 
                            placeholder="Search by barcode, part number, vehicle, model, year..." 
                            style="border-radius: 24px; border: 2px solid #e0e0e0;">
                        <i class="fas fa-search position-absolute" style="left: 20px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                        <button type="button" id="purchase-clear-search" class="btn btn-link position-absolute d-none" 
                            style="right: 10px; top: 50%; transform: translateY(-50%); padding: 0; color: #999;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Filter Chips (YouTube-style) -->
                <div class="px-4 py-3 border-bottom" style="background: #f8f9fa; overflow-x: auto;">
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <span class="text-muted small fw-bold me-2">Filters:</span>
                        <button type="button" class="btn btn-sm filter-chip" data-filter="in_stock" data-value="yes" style="border-radius: 16px; white-space: nowrap;">
                            <i class="fas fa-check-circle me-1"></i> In Stock
                        </button>
                        <button type="button" class="btn btn-sm filter-chip" data-filter="is_active" data-value="1" style="border-radius: 16px; white-space: nowrap;">
                            <i class="fas fa-toggle-on me-1"></i> Active
                        </button>
                        <button type="button" class="btn btn-sm" id="purchase-advanced-filters-toggle" style="border-radius: 16px; white-space: nowrap;">
                            <i class="fas fa-filter me-1"></i> More Filters
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger d-none" id="purchase-clear-all-filters" style="border-radius: 16px; white-space: nowrap;">
                            <i class="fas fa-times me-1"></i> Clear All
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters Panel (Collapsible) -->
                <div class="collapse" id="purchaseAdvancedFiltersPanel">
                    <div class="p-4 border-bottom" style="background: #fff;">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Category</label>
                                <select class="form-select form-select-sm" id="purchase-filter-category">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Manufacturer</label>
                                <select class="form-select form-select-sm" id="purchase-filter-manufacturer">
                                    <option value="">All Manufacturers</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Part Number</label>
                                <select class="form-select form-select-sm" id="purchase-filter-part-number">
                                    <option value="">All Part Numbers</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Technology</label>
                                <select class="form-select form-select-sm" id="purchase-filter-technology">
                                    <option value="">All Technologies</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Grade</label>
                                <select class="form-select form-select-sm" id="purchase-filter-grade">
                                    <option value="">All Grades</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Volt</label>
                                <select class="form-select form-select-sm" id="purchase-filter-volt">
                                    <option value="">All Volts</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">CCA</label>
                                <select class="form-select form-select-sm" id="purchase-filter-cca">
                                    <option value="">All CCAs</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Supplier</label>
                                <select class="form-select form-select-sm" id="purchase-filter-supplier">
                                    <option value="">All Suppliers</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Rack</label>
                                <select class="form-select form-select-sm" id="purchase-filter-rack">
                                    <option value="">All Racks</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Min Price</label>
                                <input type="number" class="form-control form-control-sm" id="purchase-filter-min-price" placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Max Price</label>
                                <input type="number" class="form-control form-control-sm" id="purchase-filter-max-price" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Info -->
                <div class="row g-2 px-4 py-3 border-bottom" style="background: #f8f9fa;">
                    <div class="col-6">
                        <div class="p-2 rounded" style="background-color: #f0fff4; border: 1px solid #d1fae5;">
                            <small class="text-success fw-bold d-block mb-1" style="font-size: 0.7rem;">WAREHOUSE</small>
                            <div class="fw-bold text-success" id="purchase-warehouse-stock">0 Units</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background-color: #fffaf0; border: 1px solid #feebc8;">
                            <small class="text-warning fw-bold d-block mb-1" style="font-size: 0.7rem; color: #c05621 !important;">SHOP</small>
                            <div class="fw-bold" style="color: #c05621;" id="purchase-shop-stock">0 Units</div>
                        </div>
                    </div>
                </div>

                <!-- Results Container -->
                <div class="p-4" style="max-height: 400px; overflow-y: auto;">
                    <div id="purchase-search-results-container">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                            <p>Start typing to search items or use filters above</p>
                        </div>
                    </div>
                    <div id="purchase-no-results" class="text-center text-muted py-5 d-none">
                        <i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.3;"></i>
                        <p>No items found. Try adjusting your search or filters.</p>
                        <button type="button" id="purchase-add-temporary-product-btn" class="btn btn-outline-primary mt-3">
                            <i class="ti ti-plus me-1"></i> Add Temporary Product
                        </button>
                    </div>
                    <div id="purchase-loading-results" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Searching...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-link text-primary p-0 open-temporary-product-modal-btn" title="Product list mein nahi mila? Temporary add karein">
                    <i class="ti ti-plus me-1"></i> Add Temporary Product
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Temporary Product Modal (high z-index so it stays on top and is clickable) -->
<div class="modal fade" id="add-temporary-product-modal" tabindex="-1" aria-labelledby="addTemporaryProductModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true" style="z-index: 10050;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content d-flex flex-column" style="max-height: 90vh;">
            <div class="modal-header flex-shrink-0">
                <h5 class="modal-title" id="addTemporaryProductModalLabel"><i class="ti ti-package me-2"></i>Add Temporary Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="temporary-product-form" class="d-flex flex-column flex-grow-1" style="min-height: 0;">
                @csrf
                <div class="modal-body flex-grow-1 overflow-auto" style="max-height: 60vh;">
                    <p class="text-muted small mb-3">Product not in the system? Add it temporarily. It will be added to your purchase list and you can convert it to a real product later.</p>
                    <div class="mb-3" id="tmp-product-name-block">
                        <label for="tmp_product_name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-2">Type below ya voice message attach karein (max 15 sec); jo boliye woh product name mein auto likh jayega.</p>
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <input type="text" class="form-control flex-grow-1" id="tmp_product_name" name="product_name" maxlength="255" placeholder="Enter product name" style="min-width: 180px;">
                            <button type="button" class="btn btn-outline-primary" id="tmp_voice_start_btn" title="Start voice recording">
                                <i class="ti ti-microphone me-1"></i><span class="tmp-voice-btn-text">Voice</span>
                            </button>
                        </div>
                        <input type="hidden" name="voice_path" id="tmp_voice_path" value="">
                        <input type="hidden" name="voice_transcript" id="tmp_voice_transcript" value="">
                        <div id="tmp_voice_panel" class="border rounded p-2 bg-light small d-none">
                            <div id="tmp_voice_status" class="mb-1"></div>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <span id="tmp_voice_timer" class="text-muted">0:00</span>
                                <button type="button" class="btn btn-sm btn-outline-danger d-none" id="tmp_voice_stop_btn">Stop</button>
                                <button type="button" class="btn btn-sm btn-outline-success d-none" id="tmp_voice_play_btn" title="Play recording"><i class="ti ti-player-play me-1"></i>Play</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary d-none" id="tmp_voice_delete_btn"><i class="ti ti-trash me-1"></i>Remove</button>
                                <button type="button" class="btn btn-sm btn-outline-primary d-none" id="tmp_voice_again_btn"><i class="ti ti-microphone me-1"></i>Record Again</button>
                            </div>
                            <audio id="tmp_voice_audio_el" class="mt-2 w-100" style="max-height: 40px; display: none;" controls preload="metadata"></audio>
                            <div id="tmp_voice_error" class="text-danger small mt-1"></div>
                        </div>
                        <div id="tmp_product_name_error" class="invalid-feedback d-block"></div>
                    </div>
                    <div class="mb-3">
                        <label for="tmp_cost_price" class="form-label fw-semibold">Cost Price (Rs) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="tmp_cost_price" name="cost_price" required min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label for="tmp_quantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="tmp_quantity" name="quantity" required min="0.01" step="0.01" placeholder="1">
                    </div>
                    <div class="mb-3">
                        <label for="tmp_image" class="form-label fw-semibold">Attach Photo <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="tmp_image" name="image" accept="image/*" capture="environment" required>
                        <div class="form-text">Click to choose a file from your device, or on mobile tap to take a photo with the camera. Required. Max 5MB. JPEG, PNG, JPG, GIF, WebP.</div>
                        <div id="tmp_image_error" class="invalid-feedback d-block"></div>
                    </div>
                    <div class="mb-3">
                        <label for="tmp_notes" class="form-label fw-semibold">Notes <span class="text-muted">(optional)</span></label>
                        <div class="d-flex flex-wrap gap-2 align-items-start mb-2">
                            <textarea class="form-control flex-grow-1" id="tmp_notes" name="notes" rows="3" maxlength="1000" placeholder="Type notes or use the mic to record — jo boliye woh yahan likh jayega, voice bhi save hogi." style="min-width: 200px;"></textarea>
                            <button type="button" class="btn btn-outline-primary flex-shrink-0" id="tmp_notes_voice_btn" title="Record notes by voice">
                                <i class="ti ti-microphone me-1"></i>Voice
                            </button>
                        </div>
                        <input type="hidden" name="notes_voice_path" id="tmp_notes_voice_path" value="">
                        <div id="tmp_notes_voice_panel" class="border rounded p-2 bg-light small d-none mt-1">
                            <span id="tmp_notes_voice_status" class="text-muted"></span>
                            <span id="tmp_notes_voice_timer" class="ms-2 fw-semibold">0:00 / 0:30</span>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2 d-none" id="tmp_notes_voice_stop_btn">Stop</button>
                            <div id="tmp_notes_voice_error" class="text-danger small mt-1"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-shrink-0 border-top bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <a href="{{ route('purchases.temporary.index') }}" class="btn btn-link btn-sm text-muted text-decoration-none order-first order-md-0">
                        <i class="ti ti-list me-1"></i>View Temporary Products List
                    </a>
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="tmp_product_submit_btn">
                            <i class="ti ti-device-floppy me-1"></i><span class="btn-text">Save &amp; Add to Purchase</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Item Modal - ITEM DETAIL BOX -->
<div class="modal fade" id="add-item-modal" tabindex="-1" aria-hidden="true" style="z-index: 9999 !important; pointer-events: auto !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="pointer-events: auto !important;">
        <div class="modal-content add-item-modal-content position-relative" style="border-radius: 12px; pointer-events: auto !important;">
            <div class="modal-header add-item-modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold modal-title--purchase" id="add-item-modal-title">
                    <i class="ti ti-shopping-cart me-2"></i>Add Item to Purchase
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body add-item-modal-body">
                <!-- Step 1: Find product -->
                <div class="add-item-section mb-4">
                    <h6 class="add-item-section-title mb-3"><span class="add-item-step-num">1</span> Find product</h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-barcode me-1"></i> Barcode</label>
                            <input type="text" id="barcode-scan-input" class="form-control form-control-sm" placeholder="Scan or type barcode..." autocomplete="off" style="border-radius: 8px;">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1"><i class="ti ti-search me-1"></i> Search by name</label>
                            <div class="position-relative">
                                <input type="text" id="item-search" class="form-control form-control-sm item-search-input" placeholder="Type to search item..." autocomplete="off" title="Type to search or edit product name" style="border-radius: 8px;">
                                <div id="item-search-results" class="position-absolute w-100 item-search-results-box" style="top: 100%; left: 0; z-index: 1050; max-height: 280px; overflow-y: auto; display: none; margin-top: 4px;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Product detail (when item selected) -->
                    <div id="selected-item-details-display" class="mt-3 d-none add-item-product-card rounded-3 p-3 d-flex align-items-center gap-3">
                        <div id="item-search-image-preview" class="d-none flex-shrink-0">
                            <img id="item-search-image" src="" alt="Item" class="rounded border" style="width: 56px; height: 56px; object-fit: cover; cursor: pointer;" title="Click to view full image">
                            <div id="item-search-stock" class="text-center mt-1 small fw-semibold"></div>
                            <div id="item-search-warehouse" class="text-center mt-0 d-none small text-muted"></div>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="small text-uppercase fw-semibold text-muted mb-1" style="font-size: 10px;">Selected product</div>
                            <div class="small mb-1" id="selected-item-details-line1"></div>
                            <div class="small text-primary fw-semibold mb-1" id="selected-item-details-line2" style="display: none;"></div>
                            <div class="text-primary small fw-semibold" id="selected-item-details-line3"></div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-shrink-0" id="item-edit-in-modal-btn" title="Edit item" style="display: none;">
                            <i class="ti ti-edit me-1"></i>Edit
                        </button>
                    </div>
                    <input type="hidden" id="selected-item-id">
                    <input type="hidden" id="selected-warehouse-id">
                    <input type="hidden" id="selected-warehouse-ids" value="">
                </div>

                <!-- Step 2: Select warehouse & quantity -->
                <div id="stock-status-section" class="add-item-section mb-4" style="display: none;">
                    <h6 class="add-item-section-title mb-2"><span class="add-item-step-num">2</span> <span id="stock-status-section-label">Select warehouse & quantity</span></h6>
                    <p class="small text-muted mb-2">Pick a warehouse and enter quantity. Tick to include in this purchase.</p>
                    <div id="stock-status-content" class="add-item-stock-box rounded-3 p-3">
                        <div id="stock-status-list">
                            <p class="text-muted mb-0 small text-center">Loading...</p>
                        </div>
                        <div id="stock-status-list-total" class="d-flex py-2 small fw-bold mt-2 border-top pt-2" style="border-color: #e9ecef !important; display: none;">Total — <span id="stock-status-list-total-text">0</span></div>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-link btn-sm p-0 text-muted" data-bs-toggle="collapse" data-bs-target="#stock-all-branches-collapse" aria-expanded="false">
                            <i class="ti ti-building-store me-1"></i>View stock in other branches
                        </button>
                        <div class="collapse mt-2" id="stock-all-branches-collapse">
                            <div id="stock-status-all-branches" class="add-item-stock-box rounded-3 p-2 small" style="max-height: 120px; overflow-y: auto;">
                                <p class="text-muted mb-0 text-center">—</p>
                            </div>
                        </div>
                    </div>
                    <span id="stock-status-retail-pct-badge" class="badge bg-primary opacity-90 small d-none mt-2" style="font-size: 0.7rem;">Retail <span id="stock-status-retail-pct-value"></span>% applies</span>
                </div>

                <input type="hidden" id="item-quantity" value="1">
                <input type="hidden" id="item-quantity-input" value="1">
                <select id="item-unit" class="d-none" aria-hidden="true">
                    <option value="">-</option>
                    @if(isset($units) && $units->count() > 0)
                        @foreach($units as $unit)
                            <option value="{{ $unit->name ?? $unit->short_name }}">{{ $unit->name ?? $unit->short_name }}</option>
                        @endforeach
                    @else
                        <option value="Unit">Unit</option>
                        <option value="Piece">Piece</option>
                        <option value="Box">Box</option>
                        <option value="Kg">Kg</option>
                        <option value="Liter">Liter</option>
                        <option value="Pack">Pack</option>
                        <option value="Set">Set</option>
                    @endif
                </select>

                <!-- Step 3: Rate & options -->
                <div class="add-item-section mb-3">
                    <h6 class="add-item-section-title mb-3"><span class="add-item-step-num">3</span> Rate & options</h6>
                    <div class="row g-3">
                        <div class="col-md-4" id="item-rate-column">
                            <label class="form-label small fw-semibold mb-1" id="item-rate-label">Purchase rate (Rs)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">Rs</span>
                                <input type="number" id="item-rate" class="form-control no-number-spinner border-start-0" value="0" step="1" min="0" placeholder="0" style="border-radius: 0 8px 8px 0;">
                            </div>
                        </div>
                        <div class="col-md-4" id="item-retail-price-column">
                            <label class="form-label small fw-semibold mb-1">Retail (Rs)</label>
                            <div class="input-group input-group-sm flex-wrap">
                                <span class="input-group-text bg-light">Rs</span>
                                <input type="number" id="item-retail-price" class="form-control form-control-sm no-number-spinner" value="" step="1" min="0" placeholder="—" readonly tabindex="-1" style="max-width: 80px;" title="Item retail price">
                                <input type="text" id="item-tax-percent" class="form-control form-control-sm" value="18%" style="max-width: 52px;" readonly>
                                <span class="input-group-text bg-success text-white border-0 px-2" id="item-retail-after-calc" style="min-width: 70px; font-weight: 700; font-size: 0.8rem;">—</span>
                                <select id="item-retail-percentage" class="form-select form-select-sm retail-pct-select" style="max-width: 80px;" title="Adjust retail %">
                                    <option value="" selected data-pct-type="zero">—</option>
                                    @for($p = -10; $p <= 10; $p++)
                                        <option value="{{ $p }}" data-pct-type="{{ $p < 0 ? 'minus' : ($p == 0 ? 'zero' : 'plus') }}">{{ $p >= 0 ? '+' : '' }}{{ $p }}%</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Warranty</label>
                            <div class="d-flex gap-1">
                                <select id="warranty-value" class="form-select form-select-sm" style="flex: 1; border-radius: 8px;">
                                    <option value="">—</option>
                                    @for($i = 1; $i <= 30; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                                <select id="warranty-unit" class="form-select form-select-sm" style="flex: 1; border-radius: 8px;">
                                    <option value="">—</option>
                                    <option value="Days">Days</option>
                                    <option value="Weeks">Weeks</option>
                                    <option value="Months">Months</option>
                                    <option value="Years">Years</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase history (compact) -->
                <div class="add-item-section add-item-section-sub mb-3" id="purchase-history-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-muted"><i class="ti ti-history me-1"></i>Last purchases</span>
                        <a href="javascript:void(0)" class="btn btn-sm btn-link p-0 text-success" id="hold-rate-link" style="display: none; font-size: 11px;">Apply last rate</a>
                    </div>
                    <div id="customer-history-content" class="add-item-stock-box rounded-3 p-2 small" style="min-height: 60px; max-height: 160px; overflow-y: auto;">
                        <p class="text-muted mb-0 text-center py-2">Select an item to see history</p>
                    </div>
                </div>

                <div id="additional-fields" style="display: none;">
                    <div class="mb-2">
                        <label class="form-label small mb-1">Discount</label>
                        <div class="input-group input-group-sm">
                            <input type="number" id="item-discount" class="form-control" value="0" step="0.01" min="0">
                            <select id="discount-type" class="form-select" style="max-width: 80px;">
                                <option value="amount">Rs</option>
                                <option value="percent">%</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-1">Tax %</label>
                        <input type="number" id="item-tax" class="form-control form-control-sm" value="0" step="0.01" min="0" max="100">
                    </div>
                </div>
            </div>
            <!-- Scroll up / down buttons inside modal -->
            <div class="add-item-modal-scroll-btns d-flex flex-column gap-1">
                <button type="button" class="btn btn-light btn-sm add-item-scroll-btn add-item-scroll-up" title="Scroll up" aria-label="Scroll up">
                    <i class="ti ti-chevron-up"></i>
                </button>
                <button type="button" class="btn btn-light btn-sm add-item-scroll-btn add-item-scroll-down" title="Scroll down" aria-label="Scroll down">
                    <i class="ti ti-chevron-down"></i>
                </button>
            </div>
            <div class="modal-footer add-item-modal-footer border-0 pt-0 flex-wrap gap-2">
                <div class="d-none">
                    <select id="item-save-warehouse" class="form-select form-select-sm">
                        <option value="">— Select warehouse —</option>
                    </select>
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="save-and-new-entry">Save & add another</button>
                <button type="button" class="btn btn-primary" id="confirm-entry">Add to purchase</button>
            </div>
        </div>
    </div>
</div>

<!-- Add New Item (Create) Modal - loads item create page in iframe -->
<div class="modal fade" id="add-new-item-modal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 95%; height: 90vh;">
        <div class="modal-content" style="height: 90vh; border-radius: 12px;">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold"><i class="ti ti-plus me-2"></i>Add New Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: calc(90vh - 120px);">
                <iframe id="add-new-item-iframe" src="about:blank" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Bulk edit retail price (purchase items table) -->
<div class="modal fade" id="purchase-bulk-retail-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="ti ti-tag me-2"></i>Bulk edit retail price</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Set retail price (Rs.) for <strong><span id="purchase-bulk-retail-modal-count">0</span></strong> selected item(s). This will be saved with the purchase.</p>
                <div class="mb-0">
                    <label for="purchase-bulk-retail-input" class="form-label">Retail Price (Rs.)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs</span>
                        <input type="number" step="1" min="0" class="form-control" id="purchase-bulk-retail-input" placeholder="e.g. 9650">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="purchase-bulk-retail-apply-btn">
                    <i class="ti ti-check me-1"></i>Apply to selected
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Camera Barcode Scanner Modal (mobile / when no physical scanner) -->
<div class="modal fade" id="camera-barcode-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 340px;">
        <div class="modal-content rounded-3">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold d-flex align-items-center">
                    <i class="ti ti-camera me-2"></i> Camera scanner
                </h6>
                <button type="button" class="btn-close" id="close-camera-scan-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div id="camera-barcode-reader" style="width: 100%; min-height: 240px; border-radius: 8px; overflow: hidden; background: #000;"></div>
                <p class="small text-muted mb-0 mt-2 text-center">Point camera at barcode</p>
            </div>
        </div>
    </div>
</div>

<!-- Item image full view modal (click thumbnail to open) -->
<div class="modal fade" id="item-image-view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="ti ti-photo me-2"></i>Item image</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <img id="item-image-view-full" src="" alt="Item" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<!-- 2×1 Label Print View modal (when popup is blocked, view shows here) -->
<div class="modal fade" id="label-print-view-modal" tabindex="-1" aria-hidden="true" aria-labelledby="labelPrintModalTitle" data-bs-backdrop="true" data-bs-keyboard="true" style="z-index: 10060;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content" style="pointer-events: auto;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="labelPrintModalTitle"><i class="ti ti-printer me-2"></i>2×1 Label Print (Thermal)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 bg-white border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted" id="label-print-count">0 labels</span>
                        <div id="label-print-qty-wrap" class="d-none d-flex align-items-center gap-2">
                            <label class="form-label small mb-0 text-muted">Quantity:</label>
                            <input type="number" id="label-print-qty-input" class="form-control form-control-sm no-number-spinner" min="1" max="500" value="1" style="width: 80px;" title="Labels ki tadad change karein">
                        </div>
                        <div class="form-check form-switch mb-0 ms-2">
                            <input class="form-check-input" type="checkbox" id="label-print-show-price" checked title="Label par price dikhayein">
                            <label class="form-check-label small text-muted" for="label-print-show-price">Show price</label>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="label-print-modal-print-btn"><i class="ti ti-printer me-1"></i>Print</button>
                </div>
                <div id="label-print-modal-content" class="p-4 bg-white" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* 2×1 Label Print View (modal + print) */
    #label-print-view-modal.show { z-index: 10060 !important; }
    #label-print-view-modal .modal-dialog,
    #label-print-view-modal .modal-content { pointer-events: auto !important; }
    /* Load from Purchase Order modal – ensure clickable when shown */
    #loadFromPurchaseOrderModal.show { z-index: 10055 !important; }
    #loadFromPurchaseOrderModal.show .modal-dialog,
    #loadFromPurchaseOrderModal.show .modal-content,
    #loadFromPurchaseOrderModal.show .modal-body,
    #loadFromPurchaseOrderModal.show .modal-footer { pointer-events: auto !important; }
    /* Add Supplier modal – prevent frozen state (same fix as Load PO modal) */
    #addSupplierModal.show { z-index: 10056 !important; }
    #addSupplierModal.show .modal-dialog,
    #addSupplierModal.show .modal-content,
    #addSupplierModal.show .modal-header,
    #addSupplierModal.show .modal-body,
    #addSupplierModal.show .modal-footer { pointer-events: auto !important; }
    .label-print-sheet { display: flex; flex-wrap: wrap; gap: 8px; background: #fff; padding: 16px; border-radius: 8px; }
    .label-print-item { width: 2in; height: 1in; padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; display: flex; flex-direction: column; justify-content: center; overflow: hidden; background: #fff; }
    .label-print-name { font-weight: bold; line-height: 1.2; margin-bottom: 2px; }
    .label-print-rate { color: #333; }
    #label-print-modal-content.label-print-hide-price .label-print-rate { display: none !important; }
    @media print {
        .label-print-sheet { gap: 0 !important; padding: 0.25in !important; box-shadow: none !important; }
        .label-print-item { border: none !important; border-right: 1px solid #ddd !important; border-bottom: 1px solid #ddd !important; }
        #label-print-view-modal .modal-header,
        #label-print-view-modal .d-flex.justify-content-between.px-3 { display: none !important; }
        #label-print-qty-wrap { display: none !important; }
        #label-print-view-modal .modal-body { padding: 0 !important; }
        #label-print-modal-content.label-print-hide-price .label-print-rate { display: none !important; }
    }

    /* Hide number input spinners (up/down arrows) */
    .no-number-spinner::-webkit-inner-spin-button,
    .no-number-spinner::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .no-number-spinner { -moz-appearance: textfield; }

    /* Retail percentage: minus options in red */
    /* Retail % select: 0% = black, + = blue, minus = red */
    #item-retail-percentage.retail-pct-zero { color: #000 !important; }
    #item-retail-percentage.retail-pct-plus { color: #0d6efd !important; }
    #item-retail-percentage.retail-pct-minus { color: #dc3545 !important; }
    #item-retail-percentage option[data-pct-type="zero"] { color: #000; }
    #item-retail-percentage option[data-pct-type="plus"] { color: #0d6efd; }
    #item-retail-percentage option[data-pct-type="minus"] { color: #dc3545; }

    /* Stylish Action Buttons */
    .btn-action-purchase, .btn-action-claim, .btn-action-return,
    .btn-action-send-claim, .btn-action-damage, .btn-action-wrong-item {
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .btn-action-purchase {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: #fff;
    }
    .btn-action-purchase:hover {
        background: linear-gradient(135deg, #0b5ed7 0%, #084298 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
    }
    .btn-action-claim {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        color: #fff;
    }
    .btn-action-claim:hover {
        background: linear-gradient(135deg, #157347 0%, #0f5132 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
    }
    .btn-action-send-claim {
        background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
        color: #fff;
    }
    .btn-action-send-claim:hover {
        background: linear-gradient(135deg, #5a32a3 0%, #4c2a8a 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(111, 66, 193, 0.4);
    }
    .btn-action-damage {
        background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
        color: #fff;
    }
    .btn-action-damage:hover {
        background: linear-gradient(135deg, #bb2d3b 0%, #942a32 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    }
    .btn-action-wrong-item {
        background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        color: #fff;
    }
    .btn-action-wrong-item:hover {
        background: linear-gradient(135deg, #5c636a 0%, #495057 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }
    .btn-action-return {
        background: linear-gradient(135deg, #fd7e14 0%, #dc6502 100%);
        color: #fff;
    }
    .btn-action-return:hover {
        background: linear-gradient(135deg, #e8590c 0%, #bf5200 100%);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(253, 126, 20, 0.4);
    }

    /* Purchase total below – colors match action buttons, line below each row */
    #purchase-total-below .total-below-purchase { color: #0d6efd !important; font-weight: 600; padding-bottom: 0.5rem; margin-bottom: 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.12); }
    #purchase-total-below #claim-totals-below-container .total-below-row { font-weight: 600; padding-bottom: 0.5rem; margin-bottom: 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.12); }
    #purchase-total-below #claim-totals-below-container .total-below-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    #purchase-total-below .total-below-return { color: #fd7e14 !important; }
    #purchase-total-below .total-below-send-claim { color: #6f42c1 !important; }
    #purchase-total-below .total-below-damage { color: #dc3545 !important; }
    #purchase-total-below .total-below-scrap { color: #6c757d !important; }

    /* Supplier Payment Section – same design/colours as Sales */
    #payment-section .total-section {
        padding-top: 16px;
        border-top: 2px dashed #e5e7eb;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 16px;
    }
    #payment-section .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
    }
    #payment-section .discount-section {
        background: #dcfce7;
        border-radius: 12px;
        border: 1px solid #bbf7d0;
        padding: 8px 16px;
        margin: 8px 0;
    }
    #payment-section .discount-label {
        font-size: 10px;
        font-weight: 900;
        color: #16a34a;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #payment-section .net-payable {
        background: #eff6ff;
        border-radius: 12px;
        border: 1px solid #bfdbfe;
        padding: 6px 16px;
        margin: 8px 0;
    }
    #payment-section .net-payable-label {
        font-size: 10px;
        font-weight: 900;
        color: #1e40af;
        text-transform: uppercase;
    }
    #payment-section .net-payable-value {
        font-size: 14px;
        font-weight: 900;
        color: #1e40af;
    }
    #payment-section .received-amount-section {
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 8px 16px;
        margin: 8px 0;
    }
    #payment-section .received-amount-label {
        font-size: 10px;
        font-weight: 900;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #payment-section .payment-card.border-blue-100 {
        background-color: #f8fafc;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: 10px;
        margin-top: 5px;
    }
    #payment-section .purchase-bank-row {
        background-color: #f8fafc;
        border: 1px solid #e9d5ff;
        border-radius: 12px;
        padding: 10px;
        margin-top: 5px;
    }

    /* Items summary: pehla design (WAREHOUSE | ITEM | TOTAL + Print/Barcode) */
    .pehla-items-table { border-collapse: collapse; background: #fff; }
    .pehla-items-table thead { border-bottom: 2px solid #dee2e6; }
    .pehla-items-thead .pehla-th {
        font-weight: 700; font-size: 0.75rem; color: #495057; text-transform: uppercase;
        padding: 10px 12px; background: transparent; border: none;
    }
    .pehla-items-table tbody tr.pehla-items-row {
        background-color: #f8f9fa; border-bottom: 1px solid #e9ecef;
    }
    .pehla-items-table tbody tr.pehla-items-row:hover { background-color: #f1f3f5; }
    .pehla-items-table .pehla-td-warehouse,
    .pehla-items-table .pehla-td-item,
    .pehla-items-table .pehla-td-total {
        padding: 10px 12px; vertical-align: middle; border: none; border-bottom: 1px solid #e9ecef;
    }
    .pehla-items-table .pehla-td-warehouse { border-right: none; }
    .pehla-items-table .pehla-td-item { border-right: none; }
    .pehla-items-table .pehla-td-total { border-right: none; }
    .pehla-th-actions, .pehla-td-actions { width: 1%; white-space: nowrap; padding: 8px !important; border: none !important; border-bottom: 1px solid #e9ecef !important; }
    .pehla-td-actions .purchase-row-verified-cb { cursor: pointer; }
    .pehla-td-actions .purchase-row-print-btn { cursor: pointer; }
    .pehla-td-actions .remove-item { cursor: pointer; }

    /* Legacy table row colors (subtle row tint) */
    #items-tbody tr.items-row-wh-0 { background-color: #e7f1ff; }
    #items-tbody tr.items-row-wh-1 { background-color: #fff3e0; }
    #items-tbody tr.items-row-wh-2 { background-color: #e8f5e9; }
    #items-tbody tr.items-row-wh-3 { background-color: #f3e5f5; }
    #items-tbody tr.items-row-wh-4 { background-color: #e0f7fa; }
    #items-tbody tr.items-row-wh-5 { background-color: #fff8e1; }
    #items-tbody tr.items-row-wh-0:hover,
    #items-tbody tr.items-row-wh-1:hover,
    #items-tbody tr.items-row-wh-2:hover,
    #items-tbody tr.items-row-wh-3:hover,
    #items-tbody tr.items-row-wh-4:hover,
    #items-tbody tr.items-row-wh-5:hover { filter: brightness(0.97); }

    /* Add Temporary Product modal: ensure on top and clickable (fixes frozen modal) */
    #add-temporary-product-modal { z-index: 10050 !important; pointer-events: auto !important; }
    #add-temporary-product-modal.show { z-index: 10050 !important; }
    #add-temporary-product-modal .modal-dialog,
    #add-temporary-product-modal .modal-content { pointer-events: auto !important; }
    #add-temporary-product-modal .modal-body { overflow-y: auto !important; -webkit-overflow-scrolling: touch; }
    #add-temporary-product-modal .modal-footer { display: flex !important; flex-shrink: 0 !important; }

    /* Modal title color matches button for current flow */
    #add-item-modal-title.modal-title--purchase { color: #0d6efd; }
    #add-item-modal-title.modal-title--return { color: #fd7e14; }
    #add-item-modal-title.modal-title--scrap { color: #6c757d; }
    #add-item-modal-title.modal-title--claim { color: #198754; }
    #add-item-modal-title.modal-title--claim_send { color: #6f42c1; }
    #add-item-modal-title.modal-title--damage { color: #dc3545; }

    /* Hide scrollbars inside add-item-modal (content still scrolls) */
    #add-item-modal .modal-content,
    #add-item-modal .modal-body,
    #add-item-modal #item-search-results,
    #add-item-modal #stock-status-content,
    #add-item-modal #stock-status-all-branches {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    /* Add-item modal: user-friendly sections */
    .add-item-modal-content .add-item-modal-header { padding: 1rem 1.25rem 0.5rem; }
    .add-item-modal-content .add-item-modal-body { padding: 0 1.25rem 1rem; max-height: 70vh; overflow-y: auto; overflow-x: hidden; -webkit-overflow-scrolling: touch; }
    .add-item-modal-content .add-item-modal-footer { padding: 0.5rem 1.25rem 1rem; }
    .add-item-section-title { font-size: 0.95rem; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 0.5rem; }
    .add-item-step-num { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #e0e7ff; color: #4f46e5; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; }
    .add-item-product-card { background: #f8fafc; border: 1px solid #e2e8f0; }
    .add-item-stock-box { background: #f8fafc; border: 1px solid #e2e8f0; max-height: 200px; overflow-y: auto; }
    .add-item-section-sub .add-item-section-title { font-size: 0.85rem; }
    #add-item-modal .item-search-input { border-radius: 8px; }

    #add-item-modal .modal-content::-webkit-scrollbar,
    #add-item-modal .modal-body::-webkit-scrollbar,
    #add-item-modal #item-search-results::-webkit-scrollbar,
    #add-item-modal #stock-status-content::-webkit-scrollbar,
    #add-item-modal #stock-status-all-branches::-webkit-scrollbar {
        display: none;
    }
    /* Add-item modal: show scrollbar so user can scroll down */
    #add-item-modal .add-item-modal-body::-webkit-scrollbar { width: 8px; display: block !important; }
    #add-item-modal .add-item-modal-body::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    #add-item-modal .add-item-modal-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    #add-item-modal .add-item-modal-body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Scroll up/down buttons position */
    .add-item-modal-scroll-btns {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        pointer-events: none;
    }
    .add-item-modal-scroll-btns .add-item-scroll-btn {
        pointer-events: auto;
        width: 32px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .add-item-modal-scroll-btns .add-item-scroll-btn:hover {
        background: #e2e8f0 !important;
        border-color: #cbd5e1;
    }
    .add-item-modal-scroll-btns .add-item-scroll-btn i { font-size: 1.1rem; }

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
    .item-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }
    .item-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .item-card.selected {
        border-color: #0d6efd;
        background: #e7f1ff;
    }
    /* Selected warehouse row: blue bar design (like reference image) */
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar {
        border-radius: 10px;
        padding: 10px 14px;
        border: none;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar.bg-primary {
        background-color: #0050FF !important;
        color: #fff !important;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar .stock-warehouse-bar-inner {
        width: 100%;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar .stock-bar-display-wrap {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar .stock-bar-check {
        font-weight: bold;
        font-size: 1rem;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar .stock-bar-display-text {
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.02em;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar .stock-bar-pill {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        background: #1A1A1A;
        color: #fff !important;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar:not(.bg-primary) .stock-bar-pill {
        background: #333;
        color: #fff !important;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar .stock-bar-qty-label {
        font-weight: 600;
        font-size: 14px;
        white-space: nowrap;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar .stock-bar-inputs {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar .stock-bar-input {
        width: 56px;
        min-width: 56px;
        text-align: center;
        padding: 6px 8px;
        border: none;
        border-radius: 12px;
        background: #fff;
        color: #1a1a1a;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar.bg-primary .stock-bar-input {
        color: #1a1a1a;
        background: #fff;
    }
    #stock-status-list .stock-warehouse-item.stock-warehouse-bar:not(.bg-primary) .stock-bar-input {
        background: #fff;
        color: #1a1a1a;
        border: 1px solid #dee2e6;
    }
    #stock-status-list .stock-warehouse-item.bg-primary .text-muted,
    #stock-status-list .stock-warehouse-item.bg-primary .text-end span {
        color: rgba(255,255,255,0.95) !important;
    }
    mark {
        background: #ffeb3b;
        padding: 2px 4px;
        border-radius: 3px;
    }
    
    /* ========== Premium Search Filter (unique, beautiful) ========== */
    .item-search-input {
        background: linear-gradient(135deg, #f8f9fc 0%, #f0f2f8 100%) !important;
        border: 2px solid rgba(102, 126, 234, 0.2) !important;
        border-radius: 12px !important;
        padding: 12px 16px !important;
        font-size: 0.95rem !important;
        transition: all 0.3s ease !important;
        text-transform: uppercase;
    }
    .item-search-input::placeholder {
        color: #8b9dc3;
        font-weight: 500;
    }
    .item-search-input:hover {
        border-color: rgba(102, 126, 234, 0.4) !important;
        background: linear-gradient(135deg, #fff 0%, #f8f9fc 100%) !important;
    }
    .item-search-input:focus {
        border-color: #667eea !important;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15) !important;
        background: #fff !important;
        outline: none !important;
    }
    
    .item-search-results-box {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(102, 126, 234, 0.15);
        border-radius: 14px;
        box-shadow: 0 12px 40px rgba(102, 126, 234, 0.12), 0 4px 12px rgba(0, 0, 0, 0.06);
        animation: searchResultsIn 0.25s ease-out;
    }
    
    @keyframes searchResultsIn {
        from {
            opacity: 0;
            transform: translateY(-12px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .item-search-result {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
    }
    .item-search-result:last-child {
        border-bottom: none !important;
    }
    .item-search-result:hover,
    .branch-search-result:hover,
    .warehouse-search-result:hover {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.06) 0%, rgba(118, 75, 162, 0.04) 100%) !important;
        transform: translateX(4px);
        transition: all 0.2s ease;
    }
    
    .branch-search-result {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.06) 100%) !important;
        border-radius: 10px;
        margin: 4px 8px;
        width: calc(100% - 16px);
    }
    .warehouse-search-result {
        background: linear-gradient(90deg, rgba(100, 116, 139, 0.06) 0%, rgba(100, 116, 139, 0.04) 100%) !important;
        border-radius: 10px;
        margin: 4px 8px;
        width: calc(100% - 16px);
    }
    
    #item-search-results .p-3 {
        padding: 14px 16px !important;
    }
    /* Stock block on search result card: image-style (Can badge + Liter + ML) */
    .item-search-result-stock .badge {
        border-radius: 8px;
        white-space: nowrap;
    }
    .item-search-result-stock .badge.bg-success {
        background-color: rgba(25, 135, 84, 0.12) !important;
        color: #198754 !important;
    }
    #item-search-results .fw-bold.text-dark.mb-1 {
        font-weight: 700 !important;
        color: #1e293b !important;
        letter-spacing: 0.01em;
    }
    #item-search-results .text-primary.mb-1 {
        color: #667eea !important;
        font-weight: 700;
    }
    
    /* ========== Purchase History Styling ========== */
    .purchase-history-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 12px;
    }
    
    .purchase-history-item {
        transition: all 0.2s ease;
        border-radius: 6px;
        padding: 8px !important;
        margin: 2px 0;
    }
    
    .purchase-history-item:hover {
        background: rgba(16, 185, 129, 0.1) !important;
        transform: translateX(4px);
    }
    
    .purchase-history-summary {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
        padding: 10px;
    }

    /* Supplier name: Select2 + Edit button in input-group */
    #purchaseForm .input-group:has(#supplier_id) .select2-container {
        flex: 1 1 auto;
        min-width: 0;
    }
    #purchaseForm .input-group:has(#supplier_id) .select2-container .selection .select2-selection {
        border-radius: 6px 0 0 6px;
        border-right-color: transparent;
    }
    #purchaseForm .input-group:has(#supplier_id) .select2-container--focus .selection .select2-selection,
    #purchaseForm .input-group:has(#supplier_id) .select2-container--open .selection .select2-selection {
        border-right-color: #86b7fe;
    }
    /* Edit vendor button: padding + icon centered */
    #supplier_edit_btn,
    #supplier_edit_btn_mobile,
    .supplier-edit-btn {
        padding: 6px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
    }
    #supplier_edit_btn.supplier-edit-selected,
    #supplier_edit_btn_mobile.supplier-edit-selected {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
    #supplier_edit_btn.supplier-edit-selected:hover,
    #supplier_edit_btn_mobile.supplier-edit-selected:hover {
        background: #0b5ed7;
        border-color: #0a58ca;
        color: #fff;
    }
    /* ITEM DETAILS modal: like battery item – no base price (removed from HTML); hide base-unit dropdown only, keep main qty dropdown */
    #add-item-modal .stock-warehouse-base-qty-input {
        display: none !important;
    }
    /* MOBILE NUMBER: input-group + Edit button (same as SUPPLIER NAME) */
    #purchaseForm .input-group:has(#supplier_id_mobile) .select2-container {
        flex: 1 1 auto;
        min-width: 0;
    }
    #purchaseForm .input-group:has(#supplier_id_mobile) .select2-container .selection .select2-selection {
        border-radius: 6px 0 0 6px;
        border-right-color: transparent;
    }
    #purchaseForm .input-group:has(#supplier_id_mobile) .select2-container--focus .selection .select2-selection,
    #purchaseForm .input-group:has(#supplier_id_mobile) .select2-container--open .selection .select2-selection {
        border-right-color: #86b7fe;
    }
    /* Ensure Select2 dropdown always shows search box (dropdown is rendered outside container) */
    .select2-dropdown .select2-search--dropdown,
    .select2-dropdown .select2-search.select2-search--dropdown {
        display: block !important;
        visibility: visible !important;
    }
    .select2-dropdown .select2-search__field {
        display: block !important;
        width: 100% !important;
        visibility: visible !important;
    }
    /* Keep dropdown + search above modals when open */
    .select2-container--open .select2-dropdown {
        z-index: 1060 !important;
    }
    /* Ensure add-item-modal is clickable (move to body via JS; high z-index) */
    #add-item-modal.modal,
    body.modal-open #add-item-modal.modal {
        z-index: 9999 !important;
        pointer-events: auto !important;
    }
    #add-item-modal .modal-dialog,
    #add-item-modal .modal-content,
    #add-item-modal .modal-body,
    #add-item-modal input,
    #add-item-modal select,
    #add-item-modal button {
        pointer-events: auto !important;
    }
    body.modal-open .modal-backdrop {
        z-index: 9998 !important;
    }

</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
$(document).ready(function() {
    let purchaseItems = [];
    var selectedItemCategoryName = 'Other'; // for grouping by type (battery, oil, filter, etc.)
    let itemCounter = 0;
    let editingRowId = null;
    let pendingEditItem = null;
    let itemBaseRetailPrice = null; // item's retail from API; restored when % is cleared
    let warehouseQtyFirstSelectDone = false; // clear other qty dropdowns only on first selection; after that each box keeps its value
    // Entry type: 'purchase' (default) or 'scrap' - same modal as Smart Invoice Scrap In
    let currentEntryType = 'purchase';
    let addItemModalTitleKey = ''; // for modal title when different from currentEntryType (e.g. wrong_item uses return for save)

    /* Move add-item-modal to body so it is clickable (fixes no-click when inside page-wrapper) */
    function moveAddItemModalToBody() {
        var $modal = $('#add-item-modal');
        if ($modal.length) {
            $modal.appendTo('body');
            $modal.css({ 'z-index': 9999, 'pointer-events': 'auto' });
            $modal.find('.modal-dialog, .modal-content, .modal-body').css('pointer-events', 'auto');
        }
    }
    moveAddItemModalToBody();
    window.addEventListener('load', moveAddItemModalToBody);

    // ---------- Persisted purchase cart (e-commerce style: cart survives refresh) ----------
    // Helper to clean item name: strip HTML, remove Lorem/dummy, preserve battery-type sequence (Company • 12V • CCA etc.)
    function cleanItemName(name, itemId) {
        if (!name) return 'Item #' + itemId;
        var plain = (typeof stripHtml === 'function' ? stripHtml(name) : String(name).replace(/<[^>]*>/g, '')).trim();
        if (!plain) return 'Item #' + itemId;
        const lower = plain.toLowerCase();
        if (lower.indexOf('lorem') !== -1 || lower.indexOf('dummy') !== -1 || lower.indexOf('simply') !== -1 || plain.length > 150) {
            return 'Item #' + itemId;
        }
        return plain.length > 80 ? plain.substring(0, 77) + '...' : plain;
    }
    
    // Use image URL as-is if absolute; otherwise same-origin relative path (leading slash)
    function normalizeItemImageUrl(url) {
        if (!url || typeof url !== 'string') return '';
        var u = url.trim();
        if (u.indexOf('http://') === 0 || u.indexOf('https://') === 0) return u;
        if (u.indexOf('/') === 0) return u;
        return '/' + u;
    }
    
    // Strip HTML tags so product name/display never show raw HTML or empty tags
    function stripHtml(html) {
        if (!html || typeof html !== 'string') return '';
        var div = document.createElement('div');
        div.innerHTML = html;
        return (div.textContent || div.innerText || '').trim();
    }
    
    function loadPurchaseCart() {
        const branchId = $('#purchaseBranchId').val();
        
        // Only load cart if branch is selected
        if (!branchId) {
            // Show empty state - no branch selected
            $('#items-tbody').empty();
            purchaseItems = [];
            $('#empty-items-state').show();
            $('#items-list').hide();
            $('#payment-amount-row').hide();
            calculateTotals();
            return;
        }
        
        $.ajax({
            url: '{{ route("purchases.cart.get") }}',
            method: 'GET',
            dataType: 'json',
            success: function(cart) {
                if (cart.items && cart.items.length > 0) {
                    $('#items-tbody').empty();
                    purchaseItems = [];
                    cart.items.forEach(function(it) {
                        let total = parseFloat(it.total) || 0;
                        // Return-type entries: amount as minus (backend may store as positive)
                        if (['scrap', 'claim_send', 'damage'].indexOf(it.entry_type) >= 0 && total > 0) {
                            total = -total;
                        }
                        const item = {
                            id: itemCounter++,
                            item_id: it.item_id,
                            name: cleanItemName(it.name, it.item_id),
                            warehouse_id: it.warehouse_id || null,
                            warehouse_name: it.warehouse_name || null,
                            quantity: parseFloat(it.quantity),
                            quantity_base: it.quantity_base != null ? parseFloat(it.quantity_base) : null,
                            base_unit: (it.base_unit || '').trim() || null,
                            unit: it.unit || 'Unit',
                            rate: parseFloat(it.rate),
                            retail_price: it.retail_price != null ? parseFloat(it.retail_price) : null,
                            retail_price_base: it.retail_price_base != null ? parseFloat(it.retail_price_base) : null,
                            retail_pct: it.retail_pct != null ? parseFloat(it.retail_pct) : null,
                            discount: parseFloat(it.discount) || 0,
                            tax_percentage: parseFloat(it.tax_percentage) || 0,
                            tax_amount: parseFloat(it.tax_amount) || 0,
                            total: total,
                            warranty: it.warranty || null,
                            entry_type: it.entry_type || 'purchase'
                        };
                        purchaseItems.push(item);
                    });
                    sortPurchaseItemsByEntryType();
                    purchaseItems.forEach(function(item) { addItemToTable(item); });
                    $('#empty-items-state').hide();
                    $('#items-list').show();
                    if (typeof updatePurchaseBulkRetailBar === 'function') updatePurchaseBulkRetailBar();
                    if (purchaseItems.length > 0) {
                        $('#payment-amount-row').show();
                    }
                    if (cart.branch_id) {
                        $('#purchaseBranchId').val(cart.branch_id);
                    }
                    if (cart.supplier_id) {
                        $('#supplier_id').val(cart.supplier_id);
                    }
                    calculateTotals();
                } else {
                    // No items in cart - show empty state
                    $('#items-tbody').empty();
                    purchaseItems = [];
                    $('#empty-items-state').show();
                    $('#items-list').hide();
                    $('#payment-amount-row').hide();
                    calculateTotals();
                }
            },
            error: function() {
                console.warn('Could not load purchase cart');
                // On error, show empty state
                $('#empty-items-state').show();
                $('#items-list').hide();
            }
        });
    }

    function syncCartToServer() {
        const branchId = $('#purchaseBranchId').val();
        const supplierId = $('#supplier_id').val();
        const items = purchaseItems.map(function(item) {
            return {
                item_id: item.item_id,
                name: item.name,
                warehouse_id: item.warehouse_id || null,
                quantity: item.quantity,
                unit: item.unit,
                rate: item.rate,
                retail_price: item.retail_price != null ? item.retail_price : null,
                retail_price_base: item.retail_price_base != null ? item.retail_price_base : null,
                retail_pct: item.retail_pct != null ? item.retail_pct : null,
                discount: item.discount,
                tax_percentage: item.tax_percentage,
                tax_amount: item.tax_amount,
                total: item.total,
                entry_type: item.entry_type || 'purchase'
            };
        });
        $.ajax({
            url: '{{ route("purchases.cart.update") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                branch_id: branchId || null,
                supplier_id: supplierId || null,
                items: items
            }),
            success: function() { /* cart saved */ },
            error: function(xhr) {
                console.warn('Could not save purchase cart', xhr);
            }
        });
    }

    // New Purchase: show empty items by default (do not load persisted cart on page load)
    $('#items-tbody').empty();
    purchaseItems = [];
    $('#empty-items-state').show();
    $('#items-list').hide();
    $('#payment-amount-row').hide();
    calculateTotals();
    // Update empty state hint based on branch selection
    const initialBranchId = $('#purchaseBranchId').val();
    if (initialBranchId) {
        $('#empty-state-hint').text('Click "PURCHASE ITEM" to add items to cart');
    } else {
        $('#empty-state-hint').text('Select a branch first, then add items');
    }

    // If redirected back after item edit (open_add_item=1), show add-item-modal after DOM/Bootstrap ready
    (function() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('open_add_item') === '1') {
            params.delete('open_add_item');
            var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '') + (window.location.hash || '');
            if (window.history && window.history.replaceState) window.history.replaceState({}, '', newUrl);
            currentEntryType = 'purchase';
            addItemModalTitleKey = 'purchase';
            var $modal = $('#add-item-modal');
            function showAddItemModal() {
                if ($modal.length) {
                    if (typeof $modal.modal === 'function') {
                        $modal.modal('show');
                    } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var bsModal = new bootstrap.Modal($modal[0]);
                        bsModal.show();
                    }
                }
            }
            setTimeout(showAddItemModal, 150);
        }
    })();

    // ========== YouTube-Style Search Modal Functionality ==========
    const purchaseSearchInput = $('#purchase-item-search-input');
    const purchaseClearSearchBtn = $('#purchase-clear-search');
    const purchaseSearchModal = $('#purchase-item-search-modal');
    const purchaseResultsContainer = $('#purchase-search-results-container');
    const purchaseNoResults = $('#purchase-no-results');
    const purchaseLoadingResults = $('#purchase-loading-results');
    const purchaseAdvancedFiltersToggle = $('#purchase-advanced-filters-toggle');
    const purchaseClearAllFiltersBtn = $('#purchase-clear-all-filters');
    
    // Filter state
    let purchaseActiveFilters = {};
    let purchaseFilterOptions = {};
    let purchaseSearchTimeout = null;
    
    // Initialize: Load filter options when modal opens
    purchaseSearchModal.on('show.bs.modal', function() {
        if (Object.keys(purchaseFilterOptions).length === 0) {
            loadPurchaseFilterOptions();
        }
        purchaseSearchInput.focus();
    });
    
    // Load filter options
    function loadPurchaseFilterOptions() {
        $.ajax({
            url: "{{ route('purchases.filter.options') }}",
            success: function(data) {
                purchaseFilterOptions = data;
                populatePurchaseFilterDropdowns(data);
            },
            error: function(xhr) {
                console.error('Error loading filter options:', xhr);
            }
        });
    }
    
    // Populate filter dropdowns
    function populatePurchaseFilterDropdowns(data) {
        if (data.categories) {
            data.categories.forEach(cat => {
                $('#purchase-filter-category').append(`<option value="${cat.id}">${cat.name}</option>`);
            });
        }
        if (data.manufacturers) {
            data.manufacturers.forEach(man => {
                $('#purchase-filter-manufacturer').append(`<option value="${man.id}">${man.name}</option>`);
            });
        }
        if (data.part_numbers) {
            data.part_numbers.forEach(pn => {
                $('#purchase-filter-part-number').append(`<option value="${pn.id}">${pn.name}</option>`);
            });
        }
        if (data.technologies) {
            data.technologies.forEach(tech => {
                $('#purchase-filter-technology').append(`<option value="${tech.id}">${tech.name}</option>`);
            });
        }
        if (data.grades) {
            data.grades.forEach(grade => {
                $('#purchase-filter-grade').append(`<option value="${grade.id}">${grade.name}</option>`);
            });
        }
        if (data.volts) {
            data.volts.forEach(volt => {
                $('#purchase-filter-volt').append(`<option value="${volt.id}">${volt.name}</option>`);
            });
        }
        if (data.ccas) {
            data.ccas.forEach(cca => {
                $('#purchase-filter-cca').append(`<option value="${cca.id}">${cca.name}</option>`);
            });
        }
        if (data.suppliers) {
            data.suppliers.forEach(supplier => {
                $('#purchase-filter-supplier').append(`<option value="${supplier}">${supplier}</option>`);
            });
        }
        if (data.racks) {
            data.racks.forEach(rack => {
                $('#purchase-filter-rack').append(`<option value="${rack}">${rack}</option>`);
            });
        }
    }
    
    // Live search with debounce
    purchaseSearchInput.on('input', function() {
        const query = $(this).val().trim();
        purchaseClearSearchBtn.toggleClass('d-none', !query);
        
        clearTimeout(purchaseSearchTimeout);
        purchaseSearchTimeout = setTimeout(function() {
            if (query.length >= 2 || Object.keys(purchaseActiveFilters).length > 0) {
                performPurchaseSearch();
            } else {
                purchaseResultsContainer.html(`
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                        <p>Start typing to search items or use filters above</p>
                    </div>
                `);
            }
        }, 500);
    });
    
    // Clear search
    purchaseClearSearchBtn.on('click', function() {
        purchaseSearchInput.val('');
        $(this).addClass('d-none');
        performPurchaseSearch();
    });
    
    // Filter chip clicks
    $('.filter-chip').on('click', function() {
        const filter = $(this).data('filter');
        const value = $(this).data('value');
        
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            delete purchaseActiveFilters[filter];
        } else {
            $('.filter-chip[data-filter="' + filter + '"]').removeClass('active');
            $(this).addClass('active');
            purchaseActiveFilters[filter] = value;
        }
        
        updatePurchaseClearAllButton();
        performPurchaseSearch();
    });
    
    // Advanced filter changes
    $('#purchase-filter-category, #purchase-filter-manufacturer, #purchase-filter-part-number, #purchase-filter-technology, #purchase-filter-grade, #purchase-filter-volt, #purchase-filter-cca, #purchase-filter-supplier, #purchase-filter-rack, #purchase-filter-min-price, #purchase-filter-max-price').on('change input', function() {
        const filterId = $(this).attr('id').replace('purchase-filter-', '').replace('-', '_');
        const value = $(this).val();
        
        if (value) {
            purchaseActiveFilters[filterId] = value;
        } else {
            delete purchaseActiveFilters[filterId];
        }
        
        updatePurchaseClearAllButton();
        performPurchaseSearch();
    });
    
    // Toggle advanced filters
    purchaseAdvancedFiltersToggle.on('click', function() {
        $('#purchaseAdvancedFiltersPanel').collapse('toggle');
    });
    
    // Clear all filters
    purchaseClearAllFiltersBtn.on('click', function() {
        purchaseActiveFilters = {};
        $('.filter-chip').removeClass('active');
        $('#purchase-filter-category, #purchase-filter-manufacturer, #purchase-filter-part-number, #purchase-filter-technology, #purchase-filter-grade, #purchase-filter-volt, #purchase-filter-cca, #purchase-filter-supplier, #purchase-filter-rack').val('');
        $('#purchase-filter-min-price, #purchase-filter-max-price').val('');
        purchaseSearchInput.val('');
        purchaseClearSearchBtn.addClass('d-none');
        updatePurchaseClearAllButton();
        performPurchaseSearch();
    });
    
    // Update clear all button visibility
    function updatePurchaseClearAllButton() {
        const hasFilters = Object.keys(purchaseActiveFilters).length > 0 || purchaseSearchInput.val().trim().length > 0;
        purchaseClearAllFiltersBtn.toggleClass('d-none', !hasFilters);
    }
    
    // Perform search
    function performPurchaseSearch() {
        const query = purchaseSearchInput.val().trim();
        
        // Build search params
        const params = {
            q: query,
            limit: 50
        };
        
        // Add active filters
        Object.keys(purchaseActiveFilters).forEach(key => {
            params[key] = purchaseActiveFilters[key];
        });
        
        // Show loading
        purchaseResultsContainer.hide();
        purchaseNoResults.hide();
        purchaseLoadingResults.show();
        
        // Get selected branch ID
        const branchId = $('#purchaseBranchId').val();
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first.'
            });
            return;
        }
        
        // Add branch_id to params
        params.branch_id = branchId;
        
        // Perform AJAX search
        $.ajax({
            url: "{{ route('purchases.items.ajax.search') }}",
            data: params,
            success: function(items) {
                purchaseLoadingResults.hide();
                
                if (items.length === 0) {
                    purchaseNoResults.show();
                    purchaseResultsContainer.hide();
                    return;
                }
                
                purchaseNoResults.hide();
                purchaseResultsContainer.show();
                
                let html = '';
                const searchTerm = query.toLowerCase();
                const regex = searchTerm ? new RegExp(searchTerm.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&'), 'gi') : null;
                
                items.forEach(item => {
                    const partNumber = item.partnumber_item?.name || 'N/A';
                    const manufacturer = item.vehical_item?.manutacturer_vehical?.name || '';
                    const model = item.vehical_item?.model_vehical?.name || '';
                    const yearFrom = item.vehical_item?.year_from || '';
                    const yearTo = item.vehical_item?.year_to || '';
                    const yearDisplay = yearFrom && yearTo ? `${yearFrom}-${yearTo}` : (yearFrom || yearTo || '');
                    const price = item.packing_purchase_rate || item.sale_price || 0;
                    const stock = item.on_hand || 0;
                    const barCode = item.bar_code || '';
                    const serialNumber = item.serial_number || '';
                    
                    // Highlight search term
                    let displayPartNumber = partNumber;
                    let displayManufacturer = manufacturer;
                    let displayModel = model;
                    let displayYear = yearDisplay;
                    
                    if (regex) {
                        displayPartNumber = partNumber.replace(regex, match => `<mark>${match}</mark>`);
                        displayManufacturer = manufacturer.replace(regex, match => `<mark>${match}</mark>`);
                        displayModel = model.replace(regex, match => `<mark>${match}</mark>`);
                        displayYear = yearDisplay.replace(regex, match => `<mark>${match}</mark>`);
                    }
                    
                    html += `
                        <div class="item-card" data-id="${item.id}" 
                             data-name="${partNumber.replace(/"/g, '&quot;')}"
                             data-price="${price}"
                             data-stock="${stock}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">${displayPartNumber}</h6>
                                    <div class="small text-muted mb-2">
                                        ${displayManufacturer ? displayManufacturer + ' ' : ''}${displayModel}${displayYear ? ' (' + displayYear + ')' : ''}
                                    </div>
                                    <div class="d-flex gap-3 small">
                                        ${barCode ? `<span><i class="fas fa-barcode me-1"></i>${barCode}</span>` : ''}
                                        ${serialNumber ? `<span><i class="fas fa-hashtag me-1"></i>${serialNumber}</span>` : ''}
                                        <span class="text-${stock > 0 ? 'success' : 'danger'}">
                                            <i class="fas fa-box me-1"></i>Stock: ${stock}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary mb-1">Rs ${parseFloat(price).toFixed(2)}</div>
                                    <button class="btn btn-sm btn-primary purchase-add-item-btn">
                                        <i class="fas fa-plus me-1"></i>Select
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                purchaseResultsContainer.html(html);
                
                // Update stock info
                updatePurchaseStockInfo(items);
            },
            error: function(xhr) {
                purchaseLoadingResults.hide();
                console.error('Search error:', xhr);
                purchaseResultsContainer.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading items. Please try again.
                    </div>
                `);
            }
        });
    }
    
    // Update stock info
    function updatePurchaseStockInfo(items) {
        let warehouseStock = 0;
        let shopStock = 0;
        
        items.forEach(item => {
            const stock = item.on_hand || 0;
            warehouseStock += stock * 0.7;
            shopStock += stock * 0.3;
        });
        
        $('#purchase-warehouse-stock').text(Math.round(warehouseStock) + ' Units');
        $('#purchase-shop-stock').text(Math.round(shopStock) + ' Units');
    }
    
    // Add item to purchase detail modal
    $(document).on('click', '.item-card, .purchase-add-item-btn', function(e) {
        e.stopPropagation();
        const card = $(this).closest('.item-card');
        const itemId = card.data('id');
        const itemName = card.data('name');
        const itemPrice = card.data('price');
        
        // Close search modal
        purchaseSearchModal.modal('hide');
        
        // Load item details and open detail modal
        loadItemDetails(itemId);
        addItemModalTitleKey = 'purchase';
        $('#add-item-modal').modal('show');
    });

    // Open Add Temporary Product modal (from no-results or from footer link)
    function openTemporaryProductModal() {
        purchaseSearchModal.modal('hide');
        var searchVal = purchaseSearchInput.val().trim();
        if (searchVal) {
            $('#tmp_product_name').val(searchVal);
        } else {
            $('#tmp_product_name').val('');
        }
        $('#tmp_cost_price').val('');
        $('#tmp_quantity').val('1');
        $('#tmp_notes').val('');
        $('#tmp_image').val('');
        $('#tmp_image').removeClass('is-invalid');
        $('#tmp_image_error').text('');
        $('#tmp_voice_path').val('');
        $('#tmp_voice_transcript').val('');
        $('#tmp_voice_panel').addClass('d-none');
        $('#tmp_product_name_error').text('');
        $('#tmp_product_name').removeClass('is-invalid');
        $('#tmp_voice_error').text('');
        $('#tmp_notes_voice_path').val('');
        $('#tmp_notes_voice_panel').addClass('d-none');
        $('#tmp_notes_voice_error').text('');
        $('#add-temporary-product-modal').modal('show');
    }
    $('#purchase-add-temporary-product-btn').on('click', openTemporaryProductModal);
    $(document).on('click', '.open-temporary-product-modal-btn', function(e) {
        e.preventDefault();
        openTemporaryProductModal();
    });

    // Voice recording for Product Name (Add Temporary Product modal)
    (function() {
        var MAX_DURATION_SEC = 15;
        var mediaRecorder = null;
        var stream = null;
        var chunks = [];
        var startTime = null;
        var timerInterval = null;
        var currentVoicePath = null;
        var lastPlayUrl = null;
        var speechRecognition = null;
        var speechTranscriptParts = [];

        function showVoicePanel() {
            $('#tmp_voice_panel').removeClass('d-none');
        }
        function setVoiceStatus(text) {
            $('#tmp_voice_status').text(text);
        }
        function setVoiceError(text) {
            $('#tmp_voice_error').text(text || '');
        }
        function updateTimer(sec) {
            var m = Math.floor(sec / 60);
            var s = sec % 60;
            $('#tmp_voice_timer').text(m + ':' + (s < 10 ? '0' : '') + s);
        }
        function stopRecording() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                try {
                    if (mediaRecorder.state === 'recording') {
                        mediaRecorder.requestData();
                    }
                    mediaRecorder.stop();
                } catch (e) {}
            }
            if (stream) {
                stream.getTracks().forEach(function(t) { t.stop(); });
                stream = null;
            }
            if (speechRecognition) {
                try {
                    speechRecognition.stop();
                } catch (e) {}
                speechRecognition = null;
            }
            $('#tmp_voice_stop_btn').addClass('d-none');
            $('#tmp_voice_start_btn').removeClass('d-none').prop('disabled', false);
        }
        function setRecordingUI() {
            $('#tmp_voice_start_btn').addClass('d-none');
            $('#tmp_voice_stop_btn').removeClass('d-none');
            $('#tmp_voice_play_btn').addClass('d-none');
            $('#tmp_voice_delete_btn').addClass('d-none');
            $('#tmp_voice_again_btn').addClass('d-none');
            var audioEl = document.getElementById('tmp_voice_audio_el');
            if (audioEl) {
                audioEl.src = '';
                audioEl.style.display = 'none';
            }
            setVoiceError('');
        }
        function setRecordedUI() {
            $('#tmp_voice_stop_btn').addClass('d-none');
            $('#tmp_voice_start_btn').removeClass('d-none').prop('disabled', false);
            $('#tmp_voice_play_btn').removeClass('d-none');
            $('#tmp_voice_delete_btn').removeClass('d-none');
            $('#tmp_voice_again_btn').removeClass('d-none');
            var audioEl = document.getElementById('tmp_voice_audio_el');
            if (audioEl && lastPlayUrl) {
                audioEl.src = lastPlayUrl;
                audioEl.style.display = 'block';
                audioEl.load();
            }
        }

        $('#tmp_voice_start_btn').on('click', function() {
            var btn = $(this);
            setVoiceError('');
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setVoiceError('Voice recording is not supported in this browser.');
                return;
            }
            btn.prop('disabled', true);
            setVoiceStatus('Requesting microphone...');
            showVoicePanel();
            setRecordingUI();
            updateTimer(0);
            chunks = [];
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function(s) {
                stream = s;
                setVoiceStatus('Recording... (max ' + MAX_DURATION_SEC + ' sec)');
                var recOptions = undefined;
                if (typeof MediaRecorder !== 'undefined' && MediaRecorder.isTypeSupported) {
                    if (MediaRecorder.isTypeSupported('audio/mp4')) {
                        recOptions = { mimeType: 'audio/mp4' };
                    } else if (MediaRecorder.isTypeSupported('audio/webm')) {
                        recOptions = { mimeType: 'audio/webm' };
                    }
                }
                try {
                    mediaRecorder = recOptions ? new MediaRecorder(s, recOptions) : new MediaRecorder(s);
                } catch (e1) {
                    try {
                        mediaRecorder = new MediaRecorder(s);
                    } catch (e2) {
                        setVoiceError('MediaRecorder not supported.');
                        stopRecording();
                        return;
                    }
                }
                speechTranscriptParts = [];
                var lastInterimTranscript = '';
                var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (SpeechRecognition) {
                    try {
                        speechRecognition = new SpeechRecognition();
                        speechRecognition.continuous = true;
                        speechRecognition.interimResults = true;
                        speechRecognition.lang = 'en-US';
                        speechRecognition.onresult = function(event) {
                            for (var i = event.resultIndex; i < event.results.length; i++) {
                                var t = event.results[i][0].transcript;
                                if (event.results[i].isFinal) {
                                    speechTranscriptParts.push(t);
                                    lastInterimTranscript = '';
                                } else {
                                    lastInterimTranscript = t;
                                }
                            }
                        };
                        speechRecognition.start();
                    } catch (e) {}
                }
                mediaRecorder.ondataavailable = function(e) {
                    if (e.data && e.data.size > 0) chunks.push(e.data);
                };
                mediaRecorder.onstop = function() {
                    if (speechRecognition) {
                        try { speechRecognition.stop(); } catch (e) {}
                        speechRecognition = null;
                    }
                    stopRecording();
                    if (chunks.length === 0) {
                        setVoiceError('No recording data.');
                        setRecordedUI();
                        return;
                    }
                    var recordedType = (mediaRecorder.mimeType || 'audio/webm').split(';')[0];
                    var blob = new Blob(chunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                    var ext = 'webm';
                    if (recordedType.indexOf('ogg') >= 0) ext = 'ogg';
                    else if (recordedType.indexOf('mp4') >= 0 || recordedType.indexOf('mpeg') >= 0) ext = 'mp4';
                    else if (recordedType.indexOf('wav') >= 0) ext = 'wav';
                    if (lastPlayUrl && lastPlayUrl.indexOf('blob:') === 0) {
                        try { URL.revokeObjectURL(lastPlayUrl); } catch (e) {}
                    }
                    lastPlayUrl = URL.createObjectURL(blob);
                    setVoiceStatus('Uploading...');
                    setVoiceError('');
                    var fd = new FormData();
                    var file = new File([blob], 'recording.' + ext, { type: blob.type });
                    fd.append('voice', file);
                    fd.append('_token', $('input[name="_token"]').val());
                    setRecordedUI();
                    setTimeout(function() {
                        var liveTranscript = speechTranscriptParts.length ? speechTranscriptParts.join(' ').trim() : '';
                        if (!liveTranscript && typeof lastInterimTranscript !== 'undefined' && lastInterimTranscript) {
                            liveTranscript = lastInterimTranscript.trim();
                        }
                        if (liveTranscript) {
                            $('#tmp_product_name').val(liveTranscript);
                            $('#tmp_voice_transcript').val(liveTranscript);
                        }
                        $.ajax({
                        url: '{{ route("purchases.voice.upload") }}',
                        method: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        success: function(res) {
                            if (res.success) {
                                currentVoicePath = res.voice_path || '';
                                if (res.voice_url && (!lastPlayUrl || lastPlayUrl.indexOf('blob:') !== 0)) {
                                    lastPlayUrl = res.voice_url;
                                }
                                var transcript = (res.transcript || '').trim();
                                if (!transcript && liveTranscript) {
                                    transcript = liveTranscript;
                                }
                                $('#tmp_voice_transcript').val(transcript);
                                $('#tmp_voice_path').val(currentVoicePath);
                                if (transcript) {
                                    $('#tmp_product_name').val(transcript);
                                } else {
                                    $('#tmp_product_name').attr('placeholder', 'Voice uploaded. Type product name if needed.');
                                }
                                setVoiceStatus(transcript ? 'Transcript filled. You can edit above.' : 'Voice saved. Type product name above.');
                            } else {
                                setVoiceError(res.message || 'Upload failed.');
                            }
                            setRecordedUI();
                        },
                        error: function(xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Upload failed.';
                            setVoiceError(msg);
                            setRecordedUI();
                        }
                    });
                    }, 120);
                };
                startTime = Date.now();
                mediaRecorder.start(200);
                timerInterval = setInterval(function() {
                    var elapsed = Math.floor((Date.now() - startTime) / 1000);
                    updateTimer(elapsed);
                    if (elapsed >= MAX_DURATION_SEC) {
                        stopRecording();
                    }
                }, 200);
            }).catch(function(err) {
                setVoiceError('Microphone access denied or not available.');
                btn.prop('disabled', false);
                $('#tmp_voice_stop_btn').addClass('d-none');
                $('#tmp_voice_start_btn').removeClass('d-none');
            });
        });

        $('#tmp_voice_stop_btn').on('click', function() {
            stopRecording();
        });

        $('#tmp_voice_play_btn').on('click', function() {
            if (!lastPlayUrl) return;
            var audioEl = document.getElementById('tmp_voice_audio_el');
            if (audioEl) {
                audioEl.src = lastPlayUrl;
                audioEl.onerror = function() {
                    setVoiceError('In-page playback not supported. Opening in new tab...');
                    if (lastPlayUrl) {
                        try {
                            window.open(lastPlayUrl, '_blank', 'noopener');
                        } catch (e) {
                            setVoiceError('Playback failed. Try Chrome or Firefox.');
                        }
                    }
                };
                audioEl.onended = function() {
                    setVoiceError('');
                };
                audioEl.load();
                var p = audioEl.play();
                if (p && p.catch) {
                    p.catch(function(e) {
                        setVoiceError('In-page playback failed. Opening in new tab...');
                        try {
                            window.open(lastPlayUrl, '_blank', 'noopener');
                        } catch (e2) {
                            setVoiceError('Playback failed: ' + (e.message || 'Try Chrome/Firefox'));
                        }
                    });
                }
            } else {
                try {
                    var audio = new Audio(lastPlayUrl);
                    audio.play().catch(function(e) {
                        setVoiceError('Playback failed.');
                    });
                } catch (e) {
                    setVoiceError('Playback not supported.');
                }
            }
        });

        $('#tmp_voice_delete_btn').on('click', function() {
            var path = $('#tmp_voice_path').val();
            if (path) {
                $.post('{{ route("purchases.voice.delete") }}', { path: path, _token: $('input[name="_token"]').val() }, function() {}).fail(function() {});
            }
            if (lastPlayUrl && lastPlayUrl.indexOf('blob:') === 0) {
                try { URL.revokeObjectURL(lastPlayUrl); } catch (e) {}
            }
            $('#tmp_voice_path').val('');
            $('#tmp_voice_transcript').val('');
            $('#tmp_product_name').val('').attr('placeholder', 'Enter product name');
            currentVoicePath = null;
            lastPlayUrl = null;
            setVoiceStatus('');
            var audioEl = document.getElementById('tmp_voice_audio_el');
            if (audioEl) { audioEl.src = ''; audioEl.style.display = 'none'; }
            $('#tmp_voice_play_btn').addClass('d-none');
            $('#tmp_voice_delete_btn').addClass('d-none');
            $('#tmp_voice_again_btn').addClass('d-none');
            $('#tmp_voice_start_btn').removeClass('d-none').prop('disabled', false);
        });

        $('#tmp_voice_again_btn').on('click', function() {
            var path = $('#tmp_voice_path').val();
            if (path) {
                $.post('{{ route("purchases.voice.delete") }}', { path: path, _token: $('input[name="_token"]').val() }, function() {}).fail(function() {});
            }
            if (lastPlayUrl && lastPlayUrl.indexOf('blob:') === 0) {
                try { URL.revokeObjectURL(lastPlayUrl); } catch (e) {}
            }
            $('#tmp_voice_path').val('');
            $('#tmp_voice_transcript').val('');
            $('#tmp_product_name').val('').attr('placeholder', 'Enter product name');
            currentVoicePath = null;
            lastPlayUrl = null;
            setVoiceStatus('');
            var audioEl = document.getElementById('tmp_voice_audio_el');
            if (audioEl) { audioEl.src = ''; audioEl.style.display = 'none'; }
            $('#tmp_voice_play_btn').addClass('d-none');
            $('#tmp_voice_delete_btn').addClass('d-none');
            $('#tmp_voice_start_btn').removeClass('d-none').prop('disabled', false);
        });
    })();

    // Notes voice recording (Add Temporary Product modal) — 30 sec max, transcript written to notes, voice saved
    (function() {
        var NOTES_MAX_SEC = 30;
        var notesMr = null, notesStream = null, notesChunks = [], notesStartTime = null, notesTimerId = null;
        var notesTranscriptParts = [], notesLastInterim = '';
        var NotesSpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        var notesRecognition = null;

        function notesStatus(t) { $('#tmp_notes_voice_status').text(t || ''); }
        function notesError(t) { $('#tmp_notes_voice_error').text(t || ''); }
        function notesTimer(sec) {
            var m = Math.floor(sec / 60), s = sec % 60;
            $('#tmp_notes_voice_timer').text(m + ':' + (s < 10 ? '0' : '') + s + ' / 0:30');
        }
        function notesStop() {
            if (notesTimerId) { clearInterval(notesTimerId); notesTimerId = null; }
            if (notesMr && notesMr.state !== 'inactive') { try { notesMr.requestData(); notesMr.stop(); } catch (e) {} }
            if (notesStream) { notesStream.getTracks().forEach(function(t) { t.stop(); }); notesStream = null; }
            if (notesRecognition) { try { notesRecognition.stop(); } catch (e) {} notesRecognition = null; }
            $('#tmp_notes_voice_stop_btn').addClass('d-none');
            $('#tmp_notes_voice_btn').prop('disabled', false);
        }

        $('#tmp_notes_voice_btn').on('click', function() {
            var btn = $(this);
            notesError('');
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                notesError('Voice recording not supported.');
                return;
            }
            btn.prop('disabled', true);
            $('#tmp_notes_voice_panel').removeClass('d-none');
            $('#tmp_notes_voice_stop_btn').removeClass('d-none');
            notesStatus('Recording...');
            notesTranscriptParts = [];
            notesLastInterim = '';
            notesChunks = [];
            notesTimer(0);
            navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
                notesStream = stream;
                try { notesMr = new MediaRecorder(stream); } catch (e) { notesMr = new MediaRecorder(stream); }
                if (NotesSpeechRecognition) {
                    try {
                        notesRecognition = new NotesSpeechRecognition();
                        notesRecognition.continuous = true;
                        notesRecognition.interimResults = true;
                        notesRecognition.lang = 'en-US';
                        notesRecognition.onresult = function(ev) {
                            for (var i = ev.resultIndex; i < ev.results.length; i++) {
                                var t = ev.results[i][0].transcript;
                                if (ev.results[i].isFinal) { notesTranscriptParts.push(t); notesLastInterim = ''; }
                                else { notesLastInterim = t; }
                            }
                        };
                        notesRecognition.start();
                    } catch (e) {}
                }
                notesMr.ondataavailable = function(e) { if (e.data && e.data.size > 0) notesChunks.push(e.data); };
                notesMr.onstop = function() {
                    notesStop();
                    setTimeout(function() {
                        var live = notesTranscriptParts.length ? notesTranscriptParts.join(' ').trim() : '';
                        if (!live && notesLastInterim) live = notesLastInterim.trim();
                        if (notesChunks.length === 0) {
                            notesError('No recording.');
                            return;
                        }
                        var blob = new Blob(notesChunks, { type: notesMr.mimeType || 'audio/webm' });
                        var ext = 'webm';
                        if ((notesMr.mimeType || '').indexOf('ogg') >= 0) ext = 'ogg';
                        else if ((notesMr.mimeType || '').indexOf('mp4') >= 0) ext = 'mp4';
                        var file = new File([blob], 'notes.' + ext, { type: blob.type });
                        var fd = new FormData();
                        fd.append('voice', file);
                        fd.append('_token', $('input[name="_token"]').val());
                        notesStatus('Uploading...');
                        $.ajax({
                            url: '{{ route("purchases.voice.upload") }}',
                            method: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            success: function(res) {
                                notesError('');
                                notesStatus('');
                                if (res.success && res.voice_path) {
                                    $('#tmp_notes_voice_path').val(res.voice_path);
                                    var transcript = (res.transcript || '').trim();
                                    if (!transcript && live) transcript = live;
                                    var $notes = $('#tmp_notes');
                                    if (transcript) {
                                        var cur = ($notes.val() || '').trim();
                                        $notes.val(cur ? cur + ' ' + transcript : transcript);
                                    }
                                    notesStatus('Voice saved. Text added to notes.');
                                } else {
                                    if (live) {
                                        $('#tmp_notes').val(function(i, v) { return (v || '').trim() ? v + ' ' + live : live; });
                                        notesStatus('Text added from voice.');
                                    }
                                }
                            },
                            error: function(xhr) {
                                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Upload failed.';
                                notesError(msg);
                                if (live) {
                                    $('#tmp_notes').val(function(i, v) { return (v || '').trim() ? v + ' ' + live : live; });
                                    notesStatus('Text added from voice.');
                                }
                            }
                        });
                    }, 120);
                };
                notesStartTime = Date.now();
                notesMr.start(200);
                notesTimerId = setInterval(function() {
                    var el = Math.floor((Date.now() - notesStartTime) / 1000);
                    notesTimer(el);
                    if (el >= NOTES_MAX_SEC) notesStop();
                }, 200);
            }).catch(function() {
                notesError('Microphone access denied.');
                btn.prop('disabled', false);
                $('#tmp_notes_voice_stop_btn').addClass('d-none');
            });
        });

        $('#tmp_notes_voice_stop_btn').on('click', function() { notesStop(); });
    })();

    // Temporary product form submit
    $(document).on('submit', '#temporary-product-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitBtn = $('#tmp_product_submit_btn');
        var $fileInput = $('#tmp_image')[0];
        if (!$fileInput || !$fileInput.files || !$fileInput.files.length) {
            $('#tmp_image').addClass('is-invalid');
            $('#tmp_image_error').text('Image is required for temporary products.');
            return;
        }
        $('#tmp_image').removeClass('is-invalid');
        $('#tmp_image_error').text('');
        var productName = ($('#tmp_product_name').val() || '').trim();
        var voicePath = ($('#tmp_voice_path').val() || '').trim();
        if (!productName && !voicePath) {
            $('#tmp_product_name_error').text('Please enter product name or attach a voice message.');
            $('#tmp_product_name').addClass('is-invalid');
            return;
        }
        $('#tmp_product_name_error').text('');
        $('#tmp_product_name').removeClass('is-invalid');
        var formData = new FormData($form[0]);
        $submitBtn.prop('disabled', true);
        $submitBtn.find('.btn-text').addClass('d-none');
        $submitBtn.find('.spinner-border').removeClass('d-none');
        $.ajax({
            url: '{{ route("purchases.temporary.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success && res.item) {
                    var it = res.item;
                    var qty = parseFloat($('#tmp_quantity').val()) || 1;
                    var rate = parseFloat(it.rate) || 0;
                    var discountAmount = 0;
                    var taxPct = 0;
                    var taxAmount = 0;
                    var total = qty * rate - discountAmount + taxAmount;
                    var newItem = {
                        id: itemCounter++,
                        item_id: it.id,
                        name: it.name,
                        warehouse_id: null,
                        warehouse_name: null,
                        quantity: qty,
                        quantity_base: null,
                        base_unit: null,
                        unit: it.unit || 'Unit',
                        rate: rate,
                        retail_price: null,
                        retail_price_base: null,
                        retail_pct: null,
                        discount: 0,
                        tax_percentage: taxPct,
                        tax_amount: taxAmount,
                        total: total,
                        warranty: null,
                        entry_type: 'purchase',
                        image: it.image || null
                    };
                    purchaseItems.push(newItem);
                    sortPurchaseItemsByEntryType();
                    $('#items-tbody').empty();
                    purchaseItems.forEach(function(item) { addItemToTable(item); });
                    $('#empty-items-state').hide();
                    $('#items-list').show();
                    if (purchaseItems.length > 0) $('#payment-amount-row').show();
                    updatePurchaseTableRetailColumnVisibility();
                    calculateTotals();
                    syncCartToServer();
                    if (typeof updatePurchaseBulkRetailBar === 'function') updatePurchaseBulkRetailBar();
                    $('#add-temporary-product-modal').modal('hide');
                    $form[0].reset();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Added', text: res.message || 'Temporary product added to purchase list.', timer: 2000, showConfirmButton: false });
                    } else {
                        alert(res.message || 'Temporary product added to purchase list.');
                    }
                } else {
                    alert(res.message || 'Failed to add temporary product.');
                }
            },
            error: function(xhr) {
                var msg = 'Failed to add temporary product.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errs = xhr.responseJSON.errors;
                    msg = (errs.image && (Array.isArray(errs.image) ? errs.image[0] : errs.image)) ||
                          (errs.product_name && (Array.isArray(errs.product_name) ? errs.product_name[0] : errs.product_name)) ||
                          (errs.cost_price && (Array.isArray(errs.cost_price) ? errs.cost_price[0] : errs.cost_price)) ||
                          (errs.quantity && (Array.isArray(errs.quantity) ? errs.quantity[0] : errs.quantity)) ||
                          msg;
                    if (errs.image) { $('#tmp_image').addClass('is-invalid'); $('#tmp_image_error').text(Array.isArray(errs.image) ? errs.image[0] : errs.image); }
                    else { $('#tmp_image').removeClass('is-invalid'); $('#tmp_image_error').text(''); }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                    $('#tmp_image_error').text('');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                } else {
                    alert(msg);
                }
            },
            complete: function() {
                $submitBtn.prop('disabled', false);
                $submitBtn.find('.btn-text').removeClass('d-none');
                $submitBtn.find('.spinner-border').addClass('d-none');
            }
        });
    });

    // When arriving from "Create Purchase & Add Temporary Product" link, open the modal
    (function() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('open_temporary') === '1') {
            setTimeout(function() {
                $('#tmp_product_name').val('');
                $('#tmp_cost_price').val('');
                $('#tmp_quantity').val('1');
                $('#tmp_notes').val('');
                $('#tmp_image').val('');
                $('#tmp_image').removeClass('is-invalid');
                $('#tmp_image_error').text('');
                var $modal = $('#add-temporary-product-modal');
                if ($modal.length) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var modalEl = $modal[0];
                        var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        bsModal.show();
                    } else {
                        $modal.modal('show');
                    }
                }
                if (window.history && window.history.replaceState) {
                    var cleanUrl = window.location.pathname + (window.location.hash || '');
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            }, 600);
        }
    })();

    // ========== End YouTube-Style Search Modal ==========

    function updateSupplierEditButtonStyle() {
        var hasSupplier = !!$('#supplier_id').val();
        $('#supplier_edit_btn, #supplier_edit_btn_mobile').toggleClass('supplier-edit-selected', hasSupplier);
    }
    // Supplier change handler - auto-fill phone when name is selected
    $('#supplier_id').on('change', function() {
        updateSupplierEditButtonStyle();
        const selected = $(this).find('option:selected');
        const name = selected.data('name') || '';
        const phone = selected.data('phone') || '';
        const address = selected.data('address') || '';
        const area = selected.data('area') || '';
        const supplierId = $(this).val();
        
        $('#supplier_mobile').val(phone);
        $('#supplier_address').val(address);
        $('#supplier_area').val(area);
        
        // Fetch supplier balance (previous balance we owe to supplier)
        if (supplierId) {
            $.ajax({
                url: '{{ route("purchases.suppliers.balance", ":id") }}'.replace(':id', supplierId),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const balance = parseFloat(response.balance) || 0;
                        $('#remaining_amount').text('Rs ' + Math.round(balance));
                        
                        // Update color based on balance
                        if (balance === 0) {
                            $('#remaining_amount').removeClass('text-warning text-danger').addClass('text-success');
                        } else if (balance > 0) {
                            // Positive balance means we owe money (red)
                            $('#remaining_amount').removeClass('text-success text-warning').addClass('text-danger');
                        } else {
                            // Negative balance means supplier owes us (yellow/warning)
                            $('#remaining_amount').removeClass('text-success text-danger').addClass('text-warning');
                        }
                    } else {
                        console.error('Failed to fetch supplier balance:', response.message);
                        $('#remaining_amount').text('Rs 0');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching supplier balance:', xhr);
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        console.error('Error message:', xhr.responseJSON.message);
                    }
                    $('#remaining_amount').text('Rs 0.00');
                }
            });
        } else {
            $('#remaining_amount').text('Rs 0.00');
        }

        // When creating a Bill (not PO), fetch Purchase Orders for this supplier and auto-open modal if any
        var isPOMode = $('#purchaseOrderSwitch').is(':checked');
        if (!isPOMode && supplierId) {
            $.ajax({
                url: '{{ route("purchases.suppliers.purchase-orders", ":id") }}'.replace(':id', supplierId),
                method: 'GET',
                success: function(res) {
                    if (res.success && res.purchase_orders && res.purchase_orders.length > 0) {
                        window.supplierPurchaseOrders = res.purchase_orders;
                        openLoadFromPOModal();
                    } else {
                        window.supplierPurchaseOrders = [];
                    }
                },
                error: function() {
                    window.supplierPurchaseOrders = [];
                }
            });
        } else {
            window.supplierPurchaseOrders = [];
        }
    });
    
    // Branch selection for purchase
    function selectPurchaseBranch(branchId, branchName, branchCode) {
        $('#selectedBranchName').text(branchName);
        $('#selectedBranchCode').remove();
        $('#purchaseBranchId').val(branchId);
        
        // Update session via AJAX
        $.ajax({
            url: '{{ route("branch.select.complete") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                branch_id: branchId
            },
            success: function() {
                // Load warehouse info for this branch
                loadBranchWarehouseInfo(branchId);
                
                // Update helpline from branch if available
                $.ajax({
                    url: '/branches/' + branchId,
                    method: 'GET',
                    success: function(branch) {
                        if (branch && branch.phone) {
                            $('#helplineNumber').text(branch.phone);
                        }
                    }
                });
                
                // Update empty state hint (branch is now selected)
                $('#empty-state-hint').text('Click "PURCHASE ITEM" to add items to cart');
                
                // Keep items summary empty by default (do not load persisted cart)
                $('#items-tbody').empty();
                purchaseItems = [];
                $('#empty-items-state').show();
                $('#items-list').hide();
                $('#payment-amount-row').hide();
                calculateTotals();
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to select branch. Please try again.'
                });
            }
        });
    }
    window.selectPurchaseBranch = selectPurchaseBranch;

    // Load warehouse info for selected branch
    function loadBranchWarehouseInfo(branchId) {
        $.ajax({
            url: '{{ route("warehouses.by.branch", ":id") }}'.replace(':id', branchId),
            method: 'GET',
            success: function(warehouse) {
                if (warehouse && !warehouse.error) {
                    $('#warehouseName').text(warehouse.warehouse_name + (warehouse.warehouse_code ? ' (' + warehouse.warehouse_code + ')' : ''));
                    $('#warehouseItemsCount').text(warehouse.items_count || 0);
                    $('#branchWarehouseInfo').show();
                } else {
                    $('#branchWarehouseInfo').hide();
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Warehouse',
                        text: 'This branch does not have a warehouse. Please create one first.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading warehouse:', xhr);
                $('#branchWarehouseInfo').hide();
            }
        });
    }

    // Update date/time display every second
    function updateDateTime() {
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        $('#currentDateTime').text(`${day}/${month}/${year}, ${hours}:${minutes}:${seconds}`);
    }
    
    // Load warehouse info on page load if branch is already selected
    $(document).ready(function() {
        // Searchable supplier dropdown (Select2) – SUPPLIER NAME (no results = "+ Add" button)
        if ($.fn.select2 && $('#supplier_id').length) {
            $('#supplier_id').select2({
                placeholder: 'Select vendor',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 1,
                escapeMarkup: function(markup) { return markup; },
                language: {
                    search: function() { return 'Search…'; },
                    noResults: function() {
                        var term = '';
                        var $open = $('.select2-container--open');
                        if ($open.length) {
                            term = $open.find('.select2-search__field').val() || '';
                        }
                        term = (term + '').trim();
                        var display = term ? ' &quot;' + (term.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')) + '&quot;' : '';
                        return '<div class="select2-add-vendor-wrap p-2 text-center"><button type="button" class="btn btn-primary btn-sm select2-add-vendor-btn" data-add-term="' + (term.replace(/"/g, '&quot;')) + '"><i class="ti ti-plus me-1"></i>Add' + display + '</button></div>';
                    }
                }
            });
            $('#supplier_id').on('select2:open', function() {
                setTimeout(function() {
                    var $search = $('.select2-dropdown .select2-search__field');
                    if (!$search.length) $search = $('.select2-container--open .select2-search__field');
                    if ($search.length) $search[0].focus();
                }, 100);
            });
        }
        $(document).on('click', '.select2-add-vendor-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var term = ($(this).data('add-term') || '').toString().trim();
            $('#supplier_id').select2('close');
            $('#supplier_id_mobile').select2('close');
            var addModal = document.getElementById('addSupplierModal');
            if (addModal) {
                $('#addSupplierModal').data('add-term', term);
                var modal = bootstrap.Modal.getOrCreateInstance(addModal);
                modal.show();
            } else {
                window.open('{{ route("suppliers.index") }}', '_blank');
            }
        });
        $('#addSupplierModal').on('shown.bs.modal', function() {
            var term = $('#addSupplierModal').data('add-term');
            if (term) {
                $('#addSupplierModal').find('input[name="company"]').val(term);
                $('#addSupplierModal').removeData('add-term');
            }
        });
        $(document).on('keydown', '.select2-search__field', function(e) {
            if (e.which !== 13) return;
            var $btn = $('.select2-add-vendor-btn:visible');
            if ($btn.length) {
                e.preventDefault();
                e.stopPropagation();
                $btn[0].click();
            }
        });
        function openSupplierEditInNewTab() {
            var id = $('#supplier_id').val();
            var base = $('#supplier_edit_btn').data('base-url') || '{{ route("suppliers.index") }}';
            var url = id ? (base + (base.indexOf('?') >= 0 ? '&' : '?') + 'edit=' + id) : base;
            window.open(url, '_blank');
        }
        $('#supplier_edit_btn').on('click', function(e) {
            e.preventDefault();
            openSupplierEditInNewTab();
        });
        $('#supplier_edit_btn_mobile').on('click', function(e) {
            e.preventDefault();
            openSupplierEditInNewTab();
        });
        // MOBILE NUMBER – same Select2 behaviour as SUPPLIER NAME (search, + Add button, focus, Enter)
        if ($.fn.select2 && $('#supplier_id_mobile').length) {
            $('#supplier_id_mobile').select2({
                placeholder: 'Search mobile number',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 1,
                escapeMarkup: function(markup) { return markup; },
                language: {
                    search: function() { return 'Search by mobile number…'; },
                    searching: function() { return 'Searching…'; },
                    noResults: function() {
                        var term = '';
                        var $open = $('.select2-container--open');
                        if ($open.length) {
                            term = $open.find('.select2-search__field').val() || '';
                        }
                        term = (term + '').trim();
                        var display = term ? ' &quot;' + (term.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')) + '&quot;' : '';
                        return '<div class="select2-add-vendor-wrap p-2 text-center"><button type="button" class="btn btn-primary btn-sm select2-add-vendor-btn" data-add-term="' + (term.replace(/"/g, '&quot;')) + '"><i class="ti ti-plus me-1"></i>Add' + display + '</button></div>';
                    }
                },
                matcher: function(params, data) {
                    var term = $.trim((params.term || '').toLowerCase()).replace(/\s+/g, '').replace(/-/g, '');
                    if (term === '') return data;
                    var phone = '';
                    if (data.element) {
                        phone = ($(data.element).data('phone') || '').toString();
                    } else if (data.id) {
                        var $opt = $('#supplier_id_mobile').find('option[value="' + data.id + '"]');
                        if ($opt.length) phone = $opt.data('phone') || '';
                    }
                    phone = phone.replace(/\s+/g, '').replace(/-/g, '').toLowerCase();
                    if (phone && phone.indexOf(term) !== -1) return data;
                    return null;
                },
                templateResult: function(data) {
                    if (!data.id) return data.text;
                    var phone = '';
                    if (data.element) {
                        phone = ($(data.element).data('phone') || '').toString().trim();
                    } else {
                        var $opt = $('#supplier_id_mobile').find('option[value="' + data.id + '"]');
                        if ($opt.length) phone = ($opt.data('phone') || '').toString().trim();
                    }
                    return phone || data.text;
                },
                templateSelection: function(data, container) {
                    var phone = '';
                    if (data.element) {
                        phone = ($(data.element).data('phone') || '').toString().trim();
                    } else if (data.id) {
                        var $opt = $('#supplier_id_mobile').find('option[value="' + data.id + '"]');
                        if ($opt.length) phone = ($opt.data('phone') || '').toString().trim();
                    }
                    return phone || data.text || 'Search mobile number';
                }
            });
            $('#supplier_id_mobile').on('select2:open', function() {
                setTimeout(function() {
                    var $search = $('.select2-dropdown .select2-search__field');
                    if (!$search.length) $search = $('.select2-container--open .select2-search__field');
                    if ($search.length) $search[0].focus();
                }, 100);
            });
        }
        // Keep both vendor dropdowns in sync: selecting from either updates the other and fills address/phone
        $('#supplier_id_mobile').on('change', function() {
            var val = $(this).val();
            if ($('#supplier_id').val() !== val) {
                $('#supplier_id').val(val).trigger('change');
            }
        });
        $('#supplier_id').on('change', function() {
            var val = $(this).val();
            if ($('#supplier_id_mobile').val() !== val) {
                $('#supplier_id_mobile').val(val).trigger('change');
            }
        });
        // Initial sync when page loads (e.g. cart restored)
        if ($('#supplier_id').val()) {
            $('#supplier_id_mobile').val($('#supplier_id').val()).trigger('change');
        }
        updateSupplierEditButtonStyle();

        const branchId = $('#purchaseBranchId').val();
        if (branchId) {
            loadBranchWarehouseInfo(branchId);
        }
        
        // Start updating date/time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Purchase / Purchase Order: sync hidden input, doc number label, backgrounds, and small on/off toggle
        function updateDocTypeFromSwitch() {
            const isPO = $('#purchaseOrderSwitch').is(':checked');
            const billNum = $('#purchase-number').data('bill-number') || '00001';
            const poNum = $('#purchase-number').data('po-number') || '00000';
            $('#isPurchaseOrderHidden').val(isPO ? '1' : '0');
            $('#purchase-number').text(isPO ? ('PO #' + poNum) : ('Bill #' + billNum));
            $('.page-title h4').text(isPO ? 'Create Purchase Order' : 'Create Purchase');
            // Small on/off toggle thumb and track
            const $thumb = $('#purchaseOrderThumb');
            const $track = $('#purchaseOrderTrack');
            if ($thumb.length) $thumb.css('left', isPO ? '22px' : '2px');
            if ($track.length) $track.css('background', isPO ? 'linear-gradient(135deg, #f59e0b, #d97706)' : '#6c757d');
            // Toggle labels: highlight active (Bill vs PO)
            const $lblBill = $('#purchaseDocLabelBill');
            const $lblPO = $('#purchaseDocLabelPO');
            if ($lblBill.length) $lblBill.removeClass('text-primary fw-bold').addClass(isPO ? 'text-muted fw-semibold' : 'text-primary fw-bold');
            if ($lblPO.length) $lblPO.removeClass('text-primary fw-bold').addClass(isPO ? 'text-primary fw-bold' : 'text-muted fw-semibold');
            // Panel (Barki Express box)
            const $panel = $('#purchaseDocTypePanel');
            if ($panel.length) {
                $panel.css(isPO ? { background: '#fef3c7', borderColor: '#f59e0b' } : { background: '#f8f9fa', borderColor: '#0d6efd' });
            }
            // Items table header (same style as panel when PO)
            const $thead = $('#purchaseItemsThead');
            if ($thead.length) {
                $thead.find('th').css(isPO ? { background: '#fef3c7', borderColor: '#f59e0b', color: '#92400e' } : { background: '', borderColor: '', color: '' });
            }
            // Grand Total panel (same style as Barki panel when PO)
            const $grandTotal = $('#purchaseGrandTotalPanel');
            if ($grandTotal.length) {
                $grandTotal.css(isPO ? { background: '#fef3c7', border: '1px solid #f59e0b', color: '#92400e' } : { background: '#0d6efd', border: '1px solid #0d6efd', color: '#fff' });
            }
            // Whole content area
            const $content = $('.content');
            if ($content.length) {
                $content.css('background', isPO ? '#fef9eb' : '');
            }
            // In PO mode: show only Total Items Amount; hide discount, net payable, cash, bank, current remaining
            const $paySection = $('#payment-section');
            if ($paySection.length) {
                $paySection.find('.total-section .discount-section').toggle(!isPO);
                $paySection.find('.total-section .net-payable').toggle(!isPO);
                $paySection.find('#cash-paid-section').toggle(!isPO);
                $paySection.find('#bank-paid-section').toggle(!isPO);
                $paySection.find('.total-section .total-row').last().toggle(!isPO);
            }
        }
        $('#purchaseOrderSwitch').on('change', updateDocTypeFromSwitch);
        updateDocTypeFromSwitch(); // init on load
    });

    // Load from Purchase Order: build modal content and open modal (called when supplier has POs)
    function openLoadFromPOModal() {
        var pos = window.supplierPurchaseOrders || [];
        if (pos.length === 0) return;
        var html = '<div class="table-responsive"><table class="table table-bordered table-hover mb-0">';
        html += '<thead><tr><th style="width:40px;">Select</th><th>PO Number</th><th>Date</th><th>Status</th><th>Items (Ordered / Received / Pending)</th></tr></thead><tbody>';
        pos.forEach(function(po, idx) {
            var statusClass = (po.po_status === 'completed') ? 'success' : (po.po_status === 'partial') ? 'warning' : 'secondary';
            html += '<tr><td class="align-middle"><input type="checkbox" class="form-check-input po-select-cb" data-po-index="' + idx + '"></td>';
            html += '<td class="align-middle">' + (po.invoice_no || '') + '</td>';
            html += '<td class="align-middle">' + (po.purchase_date || '') + '</td>';
            html += '<td class="align-middle"><span class="badge bg-' + statusClass + '">' + (po.po_status || 'draft') + '</span></td>';
            html += '<td class="align-middle"><table class="table table-sm mb-0"><thead><tr><th>Item</th><th>Ordered</th><th>Received</th><th>Pending</th></tr></thead><tbody>';
            (po.items || []).forEach(function(line) {
                var nameText = (line.item_name || 'Item #' + line.item_id).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var nameCell = '<span class="battery-type-sequence fw-bold">' + nameText + '</span>';
                if (line.pending_quantity > 0) {
                    html += '<tr><td class="align-middle">' + nameCell + '</td><td>' + line.ordered_quantity + '</td><td>' + line.received_quantity + '</td><td><strong>' + line.pending_quantity + '</strong></td></tr>';
                } else {
                    html += '<tr class="text-muted"><td class="align-middle">' + nameCell + '</td><td>' + line.ordered_quantity + '</td><td>' + line.received_quantity + '</td><td>0</td></tr>';
                }
            });
            html += '</tbody></table></td></tr>';
        });
        html += '</tbody></table></div>';
        $('#load-from-po-list').html(html);
        var modalEl = document.getElementById('loadFromPurchaseOrderModal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
            $('#loadFromPurchaseOrderModal').modal('show');
        }
    }

    // Load selected POs into bill
    $(document).on('click', '#btnLoadPOIntoBill', function() {
        var pos = window.supplierPurchaseOrders || [];
        var selected = [];
        $('#load-from-po-list .po-select-cb:checked').each(function() {
            var idx = parseInt($(this).data('po-index'), 10);
            if (!isNaN(idx) && pos[idx]) selected.push(pos[idx]);
        });
        if (selected.length === 0) {
            alert('Please select at least one Purchase Order.');
            return;
        }
        var branchId = $('#purchaseBranchId').val();
        if (!branchId) {
            alert('Please select a branch first.');
            return;
        }
        var linesToAdd = [];
        selected.forEach(function(po) {
            (po.items || []).forEach(function(line) {
                var pending = parseFloat(line.pending_quantity) || 0;
                if (pending <= 0) return;
                linesToAdd.push({
                    po_item_id: line.id,
                    item_id: line.item_id,
                    item_name: line.item_name || ('Item #' + line.item_id),
                    warehouse_id: line.warehouse_id || null,
                    warehouse_name: line.warehouse_name || null,
                    quantity: pending,
                    rate: parseFloat(line.rate) || 0,
                    discount: parseFloat(line.discount) || 0,
                    tax_percentage: parseFloat(line.tax_percentage) || 0,
                    unit: line.unit || 'Unit'
                });
            });
        });
        if (linesToAdd.length === 0) {
            alert('Selected PO(s) have no pending quantity.');
            return;
        }
        var defaultWh = { id: null, name: null };
        function applyLoadedPOItems() {
            linesToAdd.forEach(function(line) {
                var whId = line.warehouse_id || defaultWh.id;
                var whName = (line.warehouse_name && line.warehouse_name.trim()) ? line.warehouse_name.replace(/\s*\([^)]*\)\s*$/, '').trim() : (defaultWh.name || null);
                if (!whName && defaultWh.name) whName = defaultWh.name;
                var rate = line.rate || 0;
                var qty = line.quantity || 0;
                var disc = line.discount || 0;
                var taxPct = line.tax_percentage || 0;
                var subtotal = (qty * rate) - disc;
                var taxAmt = (subtotal * taxPct) / 100;
                var total = subtotal + taxAmt;
                var newItem = {
                    id: itemCounter++,
                    item_id: line.item_id,
                    warehouse_id: whId,
                    warehouse_name: whName,
                    name: line.item_name,
                    quantity: qty,
                    unit: line.unit || 'Unit',
                    rate: rate,
                    discount: disc,
                    tax_percentage: taxPct,
                    tax_amount: taxAmt,
                    total: total,
                    entry_type: 'purchase',
                    purchase_order_item_id: line.po_item_id
                };
                purchaseItems.push(newItem);
            });
            sortPurchaseItemsByEntryType();
            $('#items-tbody').empty();
            purchaseItems.forEach(function(item) { addItemToTable(item); });
            $('#empty-items-state').hide();
            $('#items-list').show();
            $('#payment-amount-row').show();
            calculateTotals();
            syncCartToServer();
            var modalEl = document.getElementById('loadFromPurchaseOrderModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getInstance(modalEl).hide();
            } else {
                $('#loadFromPurchaseOrderModal').modal('hide');
            }
        }
        var needBranchWarehouse = linesToAdd.some(function(line) { return !line.warehouse_id; });
        if (!needBranchWarehouse) {
            applyLoadedPOItems();
        } else {
            $.ajax({
                url: '{{ route("warehouses.by.branch", ":id") }}'.replace(':id', branchId),
                method: 'GET',
                success: function(warehouse) {
                    defaultWh = { id: (warehouse && warehouse.id) ? warehouse.id : null, name: (warehouse && warehouse.warehouse_name) ? (warehouse.warehouse_name + (warehouse.warehouse_code ? ' (' + warehouse.warehouse_code + ')' : '')) : null };
                    applyLoadedPOItems();
                },
                error: function() {
                    defaultWh = { id: null, name: null };
                    applyLoadedPOItems();
                }
            });
        }
    });

    // Handle "PURCHASE ITEM" button click - check branch first (event delegation so it works reliably)
    $(document).on('click', '#add-new-item-btn, #add-new-item-btn-above', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Branch Required',
                    text: 'Please select a branch first before adding items.',
                    confirmButtonText: 'OK'
                });
            } else {
                alert('Please select a branch first before adding items.');
            }
            return;
        }
        
        currentEntryType = 'purchase';
        addItemModalTitleKey = 'purchase';
        resetItemModal();
        $('#add-item-modal').modal('show');
    });

    // Enter key on purchase create page triggers PURCHASE ITEM (when focus not in input/textarea/select)
    $(document).on('keydown', function(e) {
        if (e.which !== 13) return;
        var tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
        e.preventDefault();
        $('#add-new-item-btn').trigger('click');
    });

    // Enter key on purchase create page triggers PURCHASE ITEM (when focus not in input/textarea/select)
    $(document).on('keydown', function(e) {
        if (e.which !== 13) return;
        var tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
        e.preventDefault();
        $('#add-new-item-btn').trigger('click');
    });

    // Page-level barcode scan: Enter opens add-item modal and runs barcode search
    $('#purchase-page-barcode-input').on('keydown', function(e) {
        if (e.which !== 13) return;
        e.preventDefault();
        var barcode = $(this).val().trim();
        if (!barcode) return;
        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Pehle branch select karein.' });
            } else { alert('Pehle branch select karein.'); }
            return;
        }
        window._pendingPageBarcode = barcode;
        $(this).val('');
        currentEntryType = 'purchase';
        addItemModalTitleKey = 'purchase';
        $('#add-item-modal').modal('show');
    });

    // Purchase page: Camera scan button opens camera barcode modal
    $('#purchase-open-camera-scan').on('click', function() {
        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Pehle branch select karein.' });
            } else { alert('Pehle branch select karein.'); }
            return;
        }
        if (typeof Html5Qrcode === 'undefined') {
            alert('Camera scanner library not loaded.');
            return;
        }
        $('#camera-barcode-reader').empty().css({ width: '100%', minHeight: '240px' });
        $('#camera-barcode-modal').modal('show');
    });

    // Open item create page in modal when "Add New Item" is clicked from search no-results
    $(document).on('click', '.btn-open-add-item-modal', function(e) {
        e.preventDefault();
        const url = $(this).data('create-url');
        if (url) {
            $('#add-new-item-iframe').attr('src', url);
            $('#add-item-modal').modal('hide');
            $('#add-new-item-modal').modal('show');
        }
    });
    $('#add-new-item-modal').on('hidden.bs.modal', function() {
        $('#add-new-item-iframe').attr('src', 'about:blank');
        $('#item-search').trigger('input');
    });

    // When item is updated inside iframe (add-new-item-modal), show add-item-modal so user can add it to purchase
    var pendingItemIdAfterUpdate = null;
    window.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'ITEM_UPDATED' && event.data.itemId) {
            $('#add-new-item-modal').modal('hide');
            pendingItemIdAfterUpdate = event.data.itemId;
            currentEntryType = 'purchase';
            addItemModalTitleKey = 'purchase';
            $('#add-item-modal').modal('show');
        }
    });

    // Handle "Scrap In" button - same modal as Add Item (like Smart Invoice Scrap In)
    // Handle "Claim Return" button - double-click (2 quick clicks) opens Access List; single-click opens CLAIM RETURN item modal
    var claimReceiveClickCount = 0;
    var claimReceiveClickTimer = null;
    $('#claim-receive-btn').on('click', function(e) {
        e.preventDefault();
        claimReceiveClickCount++;
        if (claimReceiveClickCount === 1) {
            claimReceiveClickTimer = setTimeout(function() {
                claimReceiveClickCount = 0;
                claimReceiveClickTimer = null;
                var branchId = $('#purchaseBranchId').val();
                if (!branchId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Branch Required',
                        text: 'Please select a branch first before adding claim items.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                var hasAccess = {{ (auth()->user()->claim_return_enabled ?? false) ? 'true' : 'false' }};
                if (!hasAccess) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Access Denied',
                        text: 'You do not have CLAIM RETURN access. Contact admin.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                currentEntryType = 'claim';
                addItemModalTitleKey = 'claim';
                $('#add-item-modal').modal('show');
            }, 350);
        } else if (claimReceiveClickCount >= 2) {
            clearTimeout(claimReceiveClickTimer);
            claimReceiveClickCount = 0;
            claimReceiveClickTimer = null;
            openClaimReturnAccessModal();
        }
    });

    function openClaimReturnAccessModal() {
        $('#claimReturnAccessModal').modal('show');
        $('#claimReturnAccessLoading').show();
        $('#claimReturnAccessListBody').find('tr:not(#claimReturnAccessLoading)').remove();
        $.get('{{ route("purchases.claim.return.access.list") }}')
            .done(function(data) {
                $('#claimReturnAccessLoading').hide();
                if (data && data.length) {
                    data.forEach(function(u) {
                        var rolesText = (u.roles && u.roles.length) ? u.roles.join(', ') : '-';
                        var isOn = u.has_access;
                        var onClass = isOn ? 'btn-success' : 'btn-outline-secondary';
                        var offClass = !isOn ? 'btn-danger' : 'btn-outline-secondary';
                        var row = '<tr data-user-id="' + u.id + '">' +
                            '<td><strong>' + (u.name || 'N/A') + '</strong>' +
                                (u.email ? '<br><small class="text-muted">' + u.email + '</small>' : '') + '</td>' +
                            '<td><span class="badge bg-secondary">' + rolesText + '</span></td>' +
                            '<td class="text-center">' +
                                '<div class="btn-group btn-group-sm" role="group">' +
                                    '<button type="button" class="btn btn-on ' + onClass + '" data-enable="1">On</button>' +
                                    '<button type="button" class="btn btn-off ' + offClass + '" data-enable="0">Off</button>' +
                                '</div></td></tr>';
                        $('#claimReturnAccessListBody').append(row);
                    });
                } else {
                    $('#claimReturnAccessListBody').append('<tr><td colspan="3" class="text-muted text-center py-3">No users found.</td></tr>');
                }
            })
            .fail(function() {
                $('#claimReturnAccessLoading').hide();
                $('#claimReturnAccessListBody').append('<tr><td colspan="3" class="text-danger text-center py-3">Failed to load.</td></tr>');
            });
    }

    $('#claimReturnAccessListBody').on('click', '.btn-on, .btn-off', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var $row = $btn.closest('tr');
        var userId = $row.data('user-id');
        if (!userId) return;
        var enable = parseInt($btn.attr('data-enable')) === 1;
        $.ajax({
            url: '{{ route("purchases.claim.return.access.toggle") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                user_id: parseInt(userId),
                enabled: enable ? 1 : 0
            },
            dataType: 'json'
        }).done(function(res) {
            if (res.success) {
                $row.find('.btn-on').removeClass('btn-success btn-outline-secondary').addClass(res.enabled ? 'btn-success' : 'btn-outline-secondary');
                $row.find('.btn-off').removeClass('btn-danger btn-outline-secondary').addClass(!res.enabled ? 'btn-danger' : 'btn-outline-secondary');
                if (parseInt(userId) === {{ auth()->check() ? auth()->id() : 0 }}) {
                    location.reload();
                }
            }
        }).fail(function() {
            if (typeof toastr !== 'undefined') toastr.error('Failed to update.');
        });
    });

    // Handle "Return" button - same modal as Add Item (like Smart Invoice Return)
    $('#return-btn').on('click', function(e) {
        e.preventDefault();
        const branchId = $('#purchaseBranchId').val();
        
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before adding return items.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        currentEntryType = 'return';
        addItemModalTitleKey = 'return';
        $('#add-item-modal').modal('show');
    });

    // Load branch warehouses into footer dropdown
    function loadItemSaveWarehouseDropdown() {
        const branchId = $('#purchaseBranchId').val();
        const $sel = $('#item-save-warehouse');
        $sel.find('option:not(:first)').remove();
        $sel.val('');
        $('#selected-warehouse-id').val('');
        $('#selected-warehouse-ids').val('');
        if (!branchId) return;
        $.ajax({
            url: '{{ url(route("warehouses.list.by.branch", ["branchId" => "__ID__"])) }}'.replace('__ID__', branchId),
            method: 'GET',
            success: function(warehouses) {
                if (Array.isArray(warehouses) && warehouses.length) {
                    warehouses.forEach(function(w) {
                        $sel.append($('<option></option>').val(w.id).text((w.warehouse_code || '') ? w.warehouse_name + ' (' + w.warehouse_code + ')' : w.warehouse_name));
                    });
                    $sel.val(warehouses[0].id);
                    $('#selected-warehouse-id').val(warehouses[0].id);
                }
            }
        });
    }

    $('#item-save-warehouse').on('change', function() {
        $('#selected-warehouse-id').val($(this).val() || '');
    });

    // Set modal title from current entry type so ITEM DETAILS / SEND CLAIM / RETURN etc. always show correctly
    function setAddItemModalTitle() {
        const type = (addItemModalTitleKey || currentEntryType || 'purchase').toString();
        const titles = {
            purchase: '<i class="ti ti-shopping-cart me-2"></i>PURCHASE ITEM',
            return: '<i class="ti ti-arrow-back-up me-2"></i>NEW RETURN',
            scrap: '<i class="ti ti-alert-triangle me-2"></i>SCRAP IN',
            claim: '<i class="ti ti-truck-delivery me-2"></i>CLAIM RETURN',
            claim_send: '<i class="ti ti-send me-2"></i>SEND CLAIM',
            damage: '<i class="ti ti-alert-triangle me-2"></i>DAMAGE PRODUCT'
        };
        $('#add-item-modal-title').html(titles[type] || titles.purchase)
            .removeClass('modal-title--purchase modal-title--return modal-title--scrap modal-title--claim modal-title--claim_send modal-title--damage')
            .addClass('modal-title--' + type);
        addItemModalTitleKey = '';
        // Rate label: "PURCHASE RATE" for purchase, "RATE" for return/claim/etc.
        var rateLabel = (type === 'purchase') ? '<i class="ti ti-shopping-cart me-1"></i>PURCHASE RATE' : 'RATE (Rs)';
        $('#item-rate-label').html(rateLabel);
        // Stock section label: context-specific (purchase = available stock, send claim = stock to send as claim)
        var stockLabels = {
            purchase: '<i class="ti ti-package me-2"></i>Available stock',
            return: '<i class="ti ti-arrow-back-up me-2"></i>Stock (select warehouse & qty to return)',
            scrap: '<i class="ti ti-alert-triangle me-2"></i>Stock (select warehouse & qty)',
            claim: '<i class="ti ti-truck-delivery me-2"></i>Claim received – select warehouse & qty',
            claim_send: '<i class="ti ti-send me-2"></i>Stock to send as claim',
            damage: '<i class="ti ti-alert-triangle me-2"></i>Stock (select for damage)'
        };
        $('#stock-status-section-label').html(stockLabels[type] || stockLabels.purchase);
    }

    // Update "After % & tax" display for retail price (retail * (1 + pct/100) * (1 + tax/100))
    function updateRetailAfterCalc() {
        var retail = parseFloat($('#item-retail-price').val()) || 0;
        var pct = parseFloat($('#item-retail-percentage').val()) || 0;
        var taxPct = parseFloat($('#item-tax-percent').val()) || 0;
        var adjusted = retail * (1 + pct / 100);
        var withTax = adjusted * (1 + taxPct / 100);
        var el = $('#item-retail-after-calc');
        if (retail <= 0) {
            el.text('Rs —');
        } else {
            var formatted = (Math.round(withTax * 100) / 100).toLocaleString('en-PK', { minimumFractionDigits: 2 });
            var lastDot = formatted.lastIndexOf('.');
            if (lastDot !== -1) {
                el.html('Rs ' + formatted.substring(0, lastDot + 1) + '<span class="opacity-75">' + formatted.substring(lastDot + 1) + '</span>');
            } else {
                el.text('Rs ' + formatted);
            }
        }
    }
    $('#item-retail-price, #item-retail-percentage, #item-tax-percent').on('input change', updateRetailAfterCalc);
    function updateRetailPctSelectColor() {
        var val = $('#item-retail-percentage').val();
        var pct = parseFloat(val) || 0;
        var $sel = $('#item-retail-percentage').removeClass('retail-pct-zero retail-pct-plus retail-pct-minus');
        if (val === '' || pct === 0) $sel.addClass('retail-pct-zero');
        else if (pct > 0) $sel.addClass('retail-pct-plus');
        else $sel.addClass('retail-pct-minus');
    }
    $('#item-retail-percentage').on('change', updateRetailPctSelectColor);
    updateRetailPctSelectColor();

    // When retail % selected: hide rate column; when empty (—): show rate. Edit: sirf tab rate clear karein jab row par pehle retail % tha aur user ne clear kiya (rate-only row par rate dikhti rehni chahiye).
    function updateRateColumnByRetailPct() {
        if (editingRowId !== null) {
            var pctVal = ($('#item-retail-percentage').val() || '').toString().trim();
            if (pctVal !== '' && pctVal !== '—') {
                $('#item-rate-column').hide();
            } else {
                $('#item-rate-column').show();
                var editItem = purchaseItems.find(function(i) { return i.id === editingRowId; });
                var rowHadPct = editItem && (editItem.retail_pct != null && editItem.retail_pct !== '' && String(editItem.retail_pct).trim() !== '');
                if (rowHadPct) {
                    $('#item-rate').val(''); // User ne percentage clear kiya = rate empty taake naya rate likh saken
                }
                // Rate-only row (rowHadPct false): rate mat chhedain, RS 12333 wahi dikhe
            }
            updateRetailAfterCalc();
            return;
        }
        var pctVal = ($('#item-retail-percentage').val() || '').toString().trim();
        if (pctVal !== '') {
            $('#item-rate-column').hide();
        } else {
            $('#item-rate-column').show();
            // Do not set #item-rate to '0' here — user may have already entered purchase rate (e.g. 12222); modal open already sets 0 for fresh add.
            var currentRetail = ($('#item-retail-price').val() || '').toString().trim();
            if (currentRetail === '' || currentRetail == null) {
                $('#item-retail-price').val(itemBaseRetailPrice != null ? Math.round(itemBaseRetailPrice) : '');
            }
            updateRetailAfterCalc();
        }
    }
    $('#item-retail-percentage').on('change', updateRateColumnByRetailPct);
    function updateStockStatusRetailPctBadge() {
        var pctVal = ($('#item-retail-percentage').val() || '').toString().trim();
        var $badge = $('#stock-status-retail-pct-badge');
        var $valSpan = $('#stock-status-retail-pct-value');
        if (pctVal !== '' && pctVal !== '—') {
            var num = parseFloat(pctVal);
            var sign = (num >= 0 ? '+' : '');
            $valSpan.text(sign + num);
            $badge.removeClass('d-none');
        } else {
            $badge.addClass('d-none');
        }
    }
    $('#item-retail-percentage').on('change', updateStockStatusRetailPctBadge);
    // Format tax %: ensure value shows with % inside input (e.g. 18%)
    $('#item-tax-percent').on('blur', function() {
        var v = $(this).val().toString().replace(/%/g, '').trim();
        var num = parseFloat(v);
        if (!isNaN(num) && v !== '') $(this).val(num + '%');
    });

    // Retail column: item select hone par hamesha dikhao; edit mode mein bhi row ke hisaab se
    function updateRetailColumnByRate() {
        var rateVal = ($('#item-rate').val() || '').toString().trim();
        var rate = parseFloat(rateVal);
        var hasRate = rateVal !== '' && !isNaN(rate) && rate > 0;
        var pctVal = ($('#item-retail-percentage').val() || '').toString().trim();
        var hasPctSelected = pctVal !== '' && pctVal !== '—';
        var hasSelectedItem = ($('#selected-item-id').val() || '').toString().trim() !== '';

        // Edit mode: agar user ne percentage empty kiya aur rate likha hai to retail hide (sirf rate dikhao)
        if (editingRowId !== null) {
            if (hasRate && !hasPctSelected) {
                $('#item-retail-price-column').hide();
                return;
            }
            var editItem = purchaseItems.find(function(i) { return i.id === editingRowId; });
            var rowHadPct = editItem && (editItem.retail_pct != null && editItem.retail_pct !== '' && String(editItem.retail_pct).trim() !== '');
            if (rowHadPct || hasPctSelected) {
                $('#item-retail-price-column').show();
            } else {
                $('#item-retail-price-column').hide();
            }
            return;
        }

        // Add mode: item select hone par retail hamesha dikhao (pehle rate hone par hide hota tha, ab nahi)
        if (hasSelectedItem) {
            $('#item-retail-price-column').show();
        } else {
            $('#item-retail-price-column').hide();
        }
    }
    $('#item-rate').on('input change keyup paste', function() {
        updateRetailColumnByRate();
    });
    $('#item-rate').on('keyup', function() {
        setTimeout(updateRetailColumnByRate, 0);
    });

    // Reset form when modal opens (skip full reset when opening for edit)
    $('#add-item-modal').on('show.bs.modal', function() {
        $('#add-item-modal').appendTo('body');
        setAddItemModalTitle();
        const branchId = $('#purchaseBranchId').val();
        if (editingRowId !== null) {
            $('#item-search-results').hide();
            loadItemSaveWarehouseDropdown();
            updateRetailColumnByRate(); // Set retail column visibility for edit (hide unless row has retail %)
            return;
        }
        $('#item-search').val('');
        $('#selected-item-id').val('');
        $('#selected-warehouse-id').val('');
        $('#selected-warehouse-ids').val('');
        $('#item-quantity').val('');
        $('#item-unit').val('');
        $('#item-rate').val('0');
        $('#item-retail-price').val('');
        $('#item-retail-percentage').val('');
        $('#item-tax-percent').val('18%');
        itemBaseRetailPrice = null;
        updateRetailPctSelectColor();
        updateRateColumnByRetailPct();
        updateRetailColumnByRate();
        $('#warranty-value').val('');
        $('#warranty-unit').val('');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to view history</p>');
        $('#item-search-results').hide();
        $('#item-edit-in-modal-btn').hide();
        $('#stock-status-section').hide();
        $('#stock-status-content').hide();
        $('#barcode-scan-input').val('');
        $('#item-search-image-preview').addClass('d-none');
        $('#item-search-image').attr('src', '');
        $('#item-search-stock').html('');
        $('#item-search-warehouse').text('');
        $('#selected-item-details-display').addClass('d-none');
        $('#selected-item-details-line1').html('');
        $('#selected-item-details-line2').html('').css('display', 'none');
        $('#selected-item-details-line3').html('');
        loadItemSaveWarehouseDropdown();
        updateRetailAfterCalc();
    });
        
    // Focus on search input when modal is fully shown; keep search editable, retail price never editable
    $('#add-item-modal').on('shown.bs.modal', function() {
        $('#item-search').prop('readonly', false).prop('disabled', false).attr('readonly', false);
        $('#item-retail-price').prop('readonly', true).attr('readonly', 'readonly');
        if (window._pendingPageBarcode) {
            var b = window._pendingPageBarcode;
            window._pendingPageBarcode = null;
            $('#barcode-scan-input').val('');
            runBarcodeSearch(b);
        }
        if (pendingItemIdAfterUpdate) {
            var itemId = pendingItemIdAfterUpdate;
            pendingItemIdAfterUpdate = null;
            $('#selected-item-id').val(itemId);
            $.get('{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId))
                .then(function(r) {
                    var name = (r.name || '').toString();
                    $('#item-search').val(typeof stripHtml === 'function' ? (stripHtml(name).trim() || name.replace(/<[^>]*>/g, '').trim() || 'Item #' + (r.id || itemId)) : (name.replace(/<[^>]*>/g, '').trim() || 'Item #' + (r.id || itemId)));
                    loadItemStockStatus(itemId);
                    $('#item-edit-in-modal-btn').show();
                })
                .catch(function() { $('#item-search').trigger('input'); });
        }
        setTimeout(function() {
            $('#item-search').focus();
        }, 100);
    });

    // Add-item modal: scroll up / down buttons
    $(document).on('click', '#add-item-modal .add-item-scroll-up', function() {
        var $body = $('#add-item-modal .add-item-modal-body');
        if ($body.length) $body.animate({ scrollTop: $body.scrollTop() - 200 }, 200);
    });
    $(document).on('click', '#add-item-modal .add-item-scroll-down', function() {
        var $body = $('#add-item-modal .add-item-modal-body');
        if ($body.length) $body.animate({ scrollTop: $body.scrollTop() + 200 }, 200);
    });
    
    // Shared: run barcode search and auto-select if single item (used by Enter key and camera scan)
    function runBarcodeSearch(barcode) {
        if (!barcode) return;
        $('#item-search').val(barcode);
        const branchId = $('#purchaseBranchId').val();
        const resultsDiv = $('#item-search-results');
        
        resultsDiv.html(`
            <div class="p-4 text-center">
                <div class="spinner-border text-primary mb-2" style="width: 2rem; height: 2rem; border-width: 0.2em;" role="status"></div>
                <p class="mb-0 text-muted fw-500">Searching by barcode...</p>
            </div>
        `).show();
        
        $.ajax({
            url: "{{ route('purchases.items.ajax.search') }}",
            method: 'GET',
            data: { q: barcode, branch_id: branchId, limit: 15 },
            success: function(results) {
                const itemResults = results.filter(function(r) { return r.type === 'item'; });
                if (itemResults.length === 1) {
                    const result = itemResults[0];
                    const item = result.item;
                    const itemId = item.id;
                    var rawItemName = (item.short_disc && item.short_disc.toLowerCase().indexOf('lorem') === -1) ? item.short_disc : ((item.pro_dis && item.pro_dis.toLowerCase().indexOf('lorem') === -1) ? item.pro_dis : (item.bar_code || (item.partnumber_item ? item.partnumber_item.name : '') || 'Item #' + item.id));
                    const itemName = (typeof stripHtml === 'function' ? stripHtml(rawItemName) : String(rawItemName).replace(/<[^>]*>/g, '')).trim() || rawItemName || ('Item #' + item.id);
                    const itemRate = item.packing_purchase_rate || item.total_price || 0;
                    const unit = (item.unit_item && (item.unit_item.name || item.unit_item.short_name)) ? (item.unit_item.name || item.unit_item.short_name) : 'Unit';
                    const warehouseId = result.warehouse_id || '';
                    
                    $('#item-search').val(itemName);
                    $('#selected-item-id').val(itemId);
                    $('#item-unit').val(unit);
                    $('#item-rate').val(Math.round(parseFloat(itemRate) || 0));
                    updateRetailColumnByRate();
                    $('#item-search-results').hide();
                    $('#barcode-scan-input').val('');
                    
                    $.ajax({
                        url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
                        method: 'GET',
                        success: function(response) {
                            itemBaseRetailPrice = (response.retail_price != null && response.retail_price !== '' && !isNaN(parseFloat(response.retail_price))) ? parseFloat(response.retail_price) : null;
                            if (editingRowId !== null) {
                                var editItem = purchaseItems.find(function(i) { return i.id === editingRowId; });
                                if (editItem) {
                                    $('#item-rate').val(editItem.rate != null ? Math.round(parseFloat(editItem.rate)) : 0);
                                    // Retail price field: always show item's linked retail from API; do not overwrite with row/calculated value
                                    $('#item-retail-price').val(itemBaseRetailPrice != null ? Math.round(itemBaseRetailPrice) : '');
                                    $('#item-retail-percentage').val((editItem.retail_pct != null && editItem.retail_pct !== '') ? String(editItem.retail_pct) : '');
                                    if (typeof updateRetailPctSelectColor === 'function') updateRetailPctSelectColor();
                                    if (typeof updateRateColumnByRetailPct === 'function') updateRateColumnByRetailPct();
                                    if (typeof updateRetailAfterCalc === 'function') updateRetailAfterCalc();
                                }
                            } else {
                                var purchaseRate = response.rate || response.total_price || itemRate;
                                $('#item-rate').val(Math.round(parseFloat(purchaseRate) || 0));
                                $('#item-rate-label').html('<i class="ti ti-shopping-cart me-1"></i>PURCHASE RATE').removeClass('text-primary');
                                updateRetailColumnByRate();
                                var currentRetail = ($('#item-retail-price').val() || '').toString().trim();
                                if (currentRetail === '' || currentRetail == null) {
                                    if (itemBaseRetailPrice != null) {
                                        $('#item-retail-price').val(Math.round(itemBaseRetailPrice));
                                    } else {
                                        $('#item-retail-price').val('');
                                    }
                                }
                                updateRetailAfterCalc();
                            }
                            if (response.unit) $('#item-unit').val(response.unit);
                            if (response.warehouse_id || warehouseId) {
                                const whId = response.warehouse_id || warehouseId;
                                $('#selected-warehouse-id').val(whId);
                                if ($('#item-save-warehouse option[value="' + whId + '"]').length) $('#item-save-warehouse').val(whId);
                            }
                            // Show item image if available (normalize to same-origin path)
                            if (response.image) {
                                $('#item-search-image').attr('src', normalizeItemImageUrl(response.image));
                                $('#item-search-image-preview').removeClass('d-none');
                            } else {
                                $('#item-search-image-preview').addClass('d-none');
                            }

                            // Stock will be set after loadItemStockStatus (correct per-warehouse value)
                            $('#item-search-stock').html('<span class="text-muted small">...</span>');
                            
                            loadItemStockStatus(itemId);
                            loadCustomerHistory(itemId);
                        },
                        error: function() {
                            loadItemStockStatus(itemId);
                            loadCustomerHistory(itemId);
                        }
                    });
                    $('#stock-status-section').show();
                } else {
                    $('#item-search').trigger('input');
                    $('#barcode-scan-input').val('');
                }
            },
            error: function() {
                resultsDiv.html('<div class="p-3 text-center text-danger"><i class="ti ti-alert-circle me-1"></i>Error. Try again.</div>').show();
                $('#barcode-scan-input').val('');
            }
        });
    }
    
    // Barcode scanner: on Enter run search and auto-select if single item
    $('#barcode-scan-input').on('keydown', function(e) {
        if (e.which !== 13) return;
        e.preventDefault();
        const barcode = $(this).val().trim();
        runBarcodeSearch(barcode);
    });
    
    // Barcode scanner: auto-search when barcode is entered (debounced for manual typing)
    let barcodeInputTimeout = null;
    $('#barcode-scan-input').on('input', function() {
        const barcode = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(barcodeInputTimeout);
        
        // If barcode is empty, clear search
        if (!barcode) {
            $('#item-search').val('');
            $('#item-search-results').hide();
            return;
        }
        
        // Wait 500ms after user stops typing, then search
        // This handles both barcode scanner (fast input) and manual typing
        barcodeInputTimeout = setTimeout(function() {
            if (barcode.length >= 3) { // Only search if at least 3 characters
                runBarcodeSearch(barcode);
            }
        }, 500);
    });
    
    // Enter in main search: if results visible, select first item; else run search
    $(document).on('keydown', '#item-search', function(e) {
        if (e.which !== 13) return;
        e.preventDefault();
        const $results = $('#item-search-results');
        const $first = $results.find('.item-search-result').first();
        if ($results.is(':visible') && $first.length) {
            $first[0].click();
        } else {
            $(this).trigger('input');
        }
    });
    
    // Camera barcode scanner (mobile / when no physical scanner)
    let cameraBarcodeScanner = null;
    
    function stopCameraScanner() {
        if (!cameraBarcodeScanner) return;
        if (cameraBarcodeScanner.isScanning !== true) {
            try { cameraBarcodeScanner.clear(); } catch (e) {}
            cameraBarcodeScanner = null;
            return;
        }
        cameraBarcodeScanner.stop().then(function() {
            try { cameraBarcodeScanner.clear(); } catch (e) {}
            cameraBarcodeScanner = null;
        }).catch(function() {
            try { if (cameraBarcodeScanner) cameraBarcodeScanner.clear(); } catch (e) {}
            cameraBarcodeScanner = null;
        });
    }
    
    $('#open-camera-scan-btn').on('click', function() {
        if (typeof Html5Qrcode === 'undefined') {
            alert('Camera scanner library not loaded. Check your connection.');
            return;
        }
        $('#camera-barcode-reader').empty().css({ width: '100%', minHeight: '240px' });
        $('#camera-barcode-modal').modal('show');
    });
    
    // Start camera only after modal is visible (so reader div has real dimensions)
    $('#camera-barcode-modal').on('shown.bs.modal', function() {
        if (cameraBarcodeScanner) return;
        var startScan = function() {
            if (cameraBarcodeScanner) return;
            var readerEl = document.getElementById('camera-barcode-reader');
            if (!readerEl || readerEl.offsetWidth < 100) return;
            cameraBarcodeScanner = new Html5Qrcode('camera-barcode-reader');
            var config = { fps: 12 };
            cameraBarcodeScanner.start(
                { facingMode: 'environment' },
                config,
                function(decodedText) {
                    if (!decodedText) return;
                    stopCameraScanner();
                    $('#camera-barcode-modal').modal('hide');
                    var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
                    if (branchId) {
                        window._pendingPageBarcode = decodedText;
                        currentEntryType = 'purchase';
                        addItemModalTitleKey = 'purchase';
                        $('#add-item-modal').modal('show');
                    } else {
                        runBarcodeSearch(decodedText);
                    }
                },
                function() {}
            ).catch(function(err) {
                cameraBarcodeScanner = null;
                $('#camera-barcode-modal').modal('hide');
                var msg = 'Camera access nahi mili. Browser settings se camera allow karein, ya upar wale "Barcode Scan" box mein barcode scanner se scan karein.';
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Camera band',
                        text: msg,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        $('#purchase-page-barcode-input').focus();
                    });
                } else {
                    alert(msg);
                    $('#purchase-page-barcode-input').focus();
                }
            });
        };
        setTimeout(startScan, 350);
    });
    
    $('#camera-barcode-modal').on('hidden.bs.modal', function() {
        stopCameraScanner();
        cameraBarcodeScanner = null;
    });
    
    // Product name search with dropdown - COMPREHENSIVE SEARCH
    let itemSearchTimeout = null;
    
    // Edit selected item: open item edit page in new tab
    $(document).on('click', '#item-edit-in-modal-btn', function() {
        var itemId = ($('#selected-item-id').val() || '').toString().trim();
        if (!itemId) return;
        var editUrl = '{{ url("item/edit") }}' + '/' + itemId;
        var returnTo = (window.location.pathname || '/purchases/create').replace(/\/+$/, '') || '/purchases/create';
        editUrl += (editUrl.indexOf('?') !== -1 ? '&' : '?') + 'return_to=' + encodeURIComponent(returnTo);
        window.open(editUrl, '_blank');
    });

    // Use event delegation to ensure it works even if modal is dynamically loaded
    $(document).on('input', '#item-search', function() {
        const query = $(this).val().trim();
        const branchId = $('#purchaseBranchId').val();
        const resultsDiv = $('#item-search-results');
        
        // Clear previous timeout
        clearTimeout(itemSearchTimeout);
        
        if (query.length < 2) {
            resultsDiv.hide();
            $('#selected-item-id').val('');
            $('#selected-warehouse-ids').val('');
            $('#item-edit-in-modal-btn').hide();
            // Hide image preview when search is cleared
            $('#item-search-image-preview').addClass('d-none');
            $('#item-search-image').attr('src', '');
            $('#item-search-stock').html('');
            $('#item-search-warehouse').text('');
            // Hide selected item details display when search is cleared
            $('#selected-item-details-display').addClass('d-none');
            $('#selected-item-details-line1').html('');
            $('#selected-item-details-line2').html('').css('display', 'none');
            $('#selected-item-details-line3').html('');
            return;
        }
        
        // Show loading state (premium style)
        resultsDiv.html(`
            <div class="p-4 text-center">
                <div class="spinner-border text-primary mb-2" style="width: 2rem; height: 2rem; border-width: 0.2em;" role="status"></div>
                <p class="mb-0 text-muted fw-500">Searching items...</p>
            </div>
        `).show();
        
        // Debounce search (300ms for smooth typing)
        itemSearchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('purchases.items.ajax.search') }}",
                method: 'GET',
                data: {
                    q: query,
                    branch_id: branchId,
                    limit: 15  // Show more results for better UX
                },
                success: function(results) {
                    const hasItemResults = results.some(function(r) { return r.type === 'item'; });
                    const noItemsHtml = `
                        <div class="p-4 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);">
                                <i class="ti ti-search-off fs-32" style="color: #667eea;"></i>
                            </div>
                            <p class="fw-600 text-dark mb-1">No items found</p>
                            <p class="text-muted small mb-2">Try: code + space + vehicle or keyword. e.g. 53495878 Toyota</p>
                            <a href="#" class="btn btn-primary btn-sm fw-bold btn-open-add-item-modal" data-create-url="{{ url(route('all.items.create')) }}" style="border-radius: 8px;">
                                <i class="ti ti-plus me-1"></i>Add New Item
                            </a>
                        </div>
                    `;
                    if (results.length === 0 || !hasItemResults) {
                        resultsDiv.html(noItemsHtml);
                    } else {
                        // Sort: branch first, then warehouse, then items; within items sort by name (alphabetically)
                        var sortedResults = results.slice();
                        sortedResults.sort(function(a, b) {
                            if (a.type !== b.type) {
                                var order = { 'branch': 0, 'warehouse': 1, 'item': 2 };
                                return (order[a.type] !== undefined ? order[a.type] : 2) - (order[b.type] !== undefined ? order[b.type] : 2);
                            }
                            if (a.type === 'item' && b.type === 'item' && a.item && b.item) {
                                var nameA = ((a.item.product_item && a.item.product_item.name) || a.item.short_disc || a.item.pro_dis || a.item.bar_code || '').toString().toLowerCase();
                                var nameB = ((b.item.product_item && b.item.product_item.name) || b.item.short_disc || b.item.pro_dis || b.item.bar_code || '').toString().toLowerCase();
                                return nameA.localeCompare(nameB, undefined, { sensitivity: 'base' });
                            }
                            return 0;
                        });
                        let html = '';
                        // Only render item results in dropdown (skip branch/warehouse)
                        sortedResults.forEach(function(result) {
                            if (result.type !== 'item') return;
                            if (result.type === 'branch') {
                                // Branch result
                                html += `
                                    <div class="p-2 border-bottom branch-search-result" 
                                         data-type="branch"
                                         data-id="${result.id}"
                                         style="background-color: #e7f3ff; cursor: pointer; transition: background 0.2s;">
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-building me-2 text-primary"></i>
                                            <div>
                                                <div class="fw-bold text-primary">${result.display}</div>
                                                <div class="small text-muted">Branch</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            } else if (result.type === 'warehouse') {
                                // Warehouse header
                                html += `
                                    <div class="p-2 border-bottom warehouse-search-result" 
                                         data-type="warehouse"
                                         data-id="${result.id}"
                                         style="background-color: #f0f9ff; cursor: pointer; transition: background 0.2s;">
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-archive me-2 text-info"></i>
                                            <div>
                                                <div class="fw-bold text-info">${result.display}</div>
                                                <div class="small text-muted">Warehouse${result.branch_name ? ' - ' + result.branch_name : ''}</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            } else if (result.type === 'item') {
                                // Item result - comprehensive display with all type-based details
                                const item = result.item;
                                const itemType = (item.type || '').toString().toLowerCase();
                                // API sends unit_display and liter_per_can for oil/can (e.g. "Can - 4 Liter")
                                const apiUnitDisplay = (result.unit_display != null && result.unit_display !== '') ? String(result.unit_display).trim() : '';
                                const apiLiterPerCan = (result.liter_per_can != null && result.liter_per_can !== '' && !isNaN(parseFloat(result.liter_per_can))) ? parseFloat(result.liter_per_can) : null;
                                const partNumber = item.partnumber_item ? item.partnumber_item.name : '';
                                const barCode = item.bar_code || '';
                                const shortDisc = (item.short_disc || '').trim();
                                const proDis = (item.pro_dis || '').trim();
                                
                                // Helper to check if text is dummy/invalid
                                const isDummy = function(t) {
                                    if (!t || t.length > 200) return true;
                                    const lower = t.toLowerCase().trim();
                                    return lower.indexOf('lorem') !== -1 || 
                                           lower.indexOf('dummy') !== -1 || 
                                           lower.indexOf('simply') !== -1 ||
                                           lower === 'sdfsdf' ||
                                           lower === 'test' ||
                                           /^[a-z]{5,}$/.test(lower) && lower.split('').every(c => c === lower[0]); // All same character repeated
                                };
                                
                                // Helper to highlight search terms in text (only the matching part, not whole word)
                                const highlightText = function(text, searchQuery) {
                                    if (!text || !searchQuery) return text;
                                    const searchTerms = searchQuery.trim().split(/\s+/).filter(t => t.length > 0);
                                    if (searchTerms.length === 0) return text;
                                    
                                    let highlighted = text;
                                    searchTerms.forEach(term => {
                                        if (term.length < 1) return; // Allow single character matching
                                        // Escape special regex characters and match case-insensitively
                                        const escapedTerm = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                                        const regex = new RegExp(`(${escapedTerm})`, 'gi');
                                        // Replace only the matching part, not the whole word
                                        highlighted = highlighted.replace(regex, '<mark style="background-color: #ffeb3b; padding: 2px 4px; border-radius: 3px; font-weight: 600;">$1</mark>');
                                    });
                                    return highlighted;
                                };
                                
                                // PRIMARY: Get product name (product_item.name first, then short_disc or pro_dis) - NOT code
                                let productName = '';
                                
                                // Priority 1: product_item.name (actual product name)
                                const productFromRelation = item.product_item ? item.product_item.name : '';
                                if (productFromRelation && !isDummy(productFromRelation)) {
                                    productName = productFromRelation.trim();
                                }
                                
                                // Priority 2: short_disc (if product_item.name not available)
                                if (!productName && !isDummy(shortDisc)) {
                                    productName = shortDisc;
                                }
                                
                                // Priority 3: pro_dis (if still no product name)
                                if (!productName && !isDummy(proDis)) {
                                    productName = proDis;
                                }
                                
                                // Priority 4: Part Number (if available, but NOT barcode)
                                if (!productName && partNumber) {
                                    productName = partNumber;
                                }
                                
                                // Last resort: Item ID (never use barcode as product name)
                                if (!productName) {
                                    productName = 'Item #' + item.id;
                                }
                                
                                // Truncate if too long
                                if (productName.length > 100) {
                                    productName = productName.substring(0, 97) + '...';
                                }
                                
                                // SECONDARY: Code line (barcode/part number)
                                let codeInfo = '';
                                if (barCode) {
                                    codeInfo = barCode;
                                    if (partNumber && partNumber !== barCode) {
                                        codeInfo += ' | ' + partNumber;
                                    }
                                } else if (partNumber) {
                                    codeInfo = partNumber;
                                }
                                
                                // Get all item details based on type
                                const manufacturer = item.vehical_item && item.vehical_item.manutacturer_vehical ? item.vehical_item.manutacturer_vehical.name : '';
                                const model = item.vehical_item && item.vehical_item.model_vehical ? item.vehical_item.model_vehical.name : '';
                                const category = item.category ? item.category.name : '';
                                const subcategory = item.subcategory ? item.subcategory.name : '';
                                const company = item.company_item ? item.company_item.name : '';
                                const product = item.product_item ? item.product_item.name : '';
                                const quality = item.quality_item ? item.quality_item.name : '';
                                const technology = item.technology_item ? item.technology_item.name : '';
                                const grade = (item.grade_item && item.grade_item.name) ? String(item.grade_item.name).trim() : '';
                                const volt = item.volt_item ? item.volt_item.name : '';
                                const cca = item.cca_item ? item.cca_item.name : '';
                                const group = item.group_item ? item.group_item.name : '';
                                const madeIn = item.made_in_item ? item.made_in_item.name : '';
                                const level = (item.level_item && item.level_item.name) ? String(item.level_item.name).trim() : '';
                                const mileageName = (item.mileage_item && item.mileage_item.name) ? String(item.mileage_item.name).trim() : '';
                                const batterySize = item.battery_size || '';
                                const plate = item.plate_item ? item.plate_item.name : '';
                                const amperes = item.amphors_item ? item.amphors_item.name : '';
                                const stock = (result.stock !== undefined && result.stock !== null) ? parseFloat(result.stock) : (item.on_hand || 0);
                                const rate = item.packing_purchase_rate || item.total_price || 0;
                                const unit = (item.unit_item && (item.unit_item.name || item.unit_item.short_name)) 
                                    ? (item.unit_item.name || item.unit_item.short_name) 
                                    : 'Unit';
                                
                                // Parse "Can - 3 Liter" from unit name for oil — is can mai kitne liter
                                let literPerCan = null;
                                const unitStr = (unit || '').toString();
                                const literMatch = unitStr.match(/(\d+(?:\.\d+)?)\s*(?:liter|ltr|L)\b/i) || unitStr.match(/\b(?:liter|ltr|L)\s*(\d+(?:\.\d+)?)/i);
                                if (literMatch) literPerCan = parseFloat(literMatch[1]);
                                else if (item.filling != null && item.filling !== '' && !isNaN(parseFloat(item.filling))) literPerCan = parseFloat(item.filling);
                                if (apiLiterPerCan != null && apiLiterPerCan > 0) literPerCan = apiLiterPerCan;
                                // For oil first line: prefer API unit_display; else "X Liter" from literPerCan; else unit. Remove "Can - " prefix so only "1 LITER" shows, not "CAN - 1 LITER".
                                let unitForFirstLine = (apiUnitDisplay !== '')
                                    ? apiUnitDisplay
                                    : ((literPerCan != null && literPerCan > 0)
                                        ? (Number.isInteger(literPerCan) ? literPerCan : literPerCan.toFixed(1)) + ' Liter'
                                        : ((unit && unit !== 'Unit' && !isDummy(unit)) ? unit : ''));
                                if (unitForFirstLine) unitForFirstLine = unitForFirstLine.replace(/^can\s*-\s*/i, '').trim();
                                
                                // Update product name: Priority 1 - product_item.name (actual product name)
                                if (product && !isDummy(product)) {
                                    productName = product;
                                }
                                
                                // Build first line: Product Name + Plates + Amperes + Company (battery or when any of these present)
                                let firstLineParts = [];
                                if (itemType === 'battery' || plate || amperes || company) {
                                    firstLineParts.push(productName);
                                    if (plate) firstLineParts.push(plate + 'PL');
                                    if (amperes) firstLineParts.push(amperes + 'AH');
                                    if (company) firstLineParts.push(company);
                                }
                                
                                // Build short details array for search display (includes vehicle)
                                // Show volt, CCA, group etc. only for battery type (not for oil)
                                let searchDetails = [];
                                
                                // Battery-style details: only for battery type so oil never shows 12V
                                if (itemType === 'battery') {
                                    if (group && !isDummy(group)) searchDetails.push(group);
                                    if (volt) searchDetails.push(volt + (volt.toString().indexOf('V') !== -1 ? '' : 'V'));
                                    if (cca) searchDetails.push(cca + (cca.toString().indexOf('CCA') !== -1 ? '' : 'CCA'));
                                    if (technology && !isDummy(technology)) searchDetails.push(technology);
                                    if (grade && !isDummy(grade)) searchDetails.push(grade);
                                    if (batterySize && !isDummy(batterySize)) searchDetails.push(batterySize);
                                    if (searchDetails.length === 0 && company) searchDetails.push(company);
                                }
                                // Common when not battery-style (skip category for oil — shown on second line only)
                                if (searchDetails.length === 0) {
                                    if (company) searchDetails.push(company);
                                    if (category && itemType !== 'oil') searchDetails.push(category);
                                }
                                // Type-specific for parts/filters/oil only when no details added yet
                                if (searchDetails.length === 0) {
                                    if (itemType === 'parts' || itemType === 'filters' || itemType === 'breakpad') {
                                        if (partNumber && !isDummy(partNumber)) searchDetails.push(partNumber);
                                        if (quality && !isDummy(quality)) searchDetails.push(quality);
                                        if (manufacturer && model) {
                                            searchDetails.push(manufacturer + ' ' + model);
                                        } else if (manufacturer) {
                                            searchDetails.push(manufacturer);
                                        }
                                    } else if (itemType === 'oil') {
                                        if (technology && !isDummy(technology)) searchDetails.push(technology);
                                        if (grade && !isDummy(grade)) searchDetails.push(grade);
                                    }
                                }
                                
                                // Vehicle info (ONLY for search display, NOT for input field) - separate for styling
                                let vehicleInfo = '';
                                if (itemType !== 'parts' && itemType !== 'filters' && itemType !== 'breakpad') {
                                    if (manufacturer && model) {
                                        vehicleInfo = manufacturer + ' ' + model;
                                    } else if (manufacturer) {
                                        vehicleInfo = manufacturer;
                                    }
                                }
                                
                                // Build details for input field (NO vehicle)
                                let inputDetails = [];
                                if (company) inputDetails.push(company);
                                if (category) inputDetails.push(category);
                                
                                // Type-specific details (NO vehicle)
                                if (itemType === 'battery') {
                                    if (group && !isDummy(group)) inputDetails.push(group);
                                    if (volt) inputDetails.push(volt + 'V');
                                    if (cca) inputDetails.push(cca + 'CCA');
                                    if (technology && !isDummy(technology)) inputDetails.push(technology);
                                    if (grade && !isDummy(grade)) inputDetails.push(grade);
                                    if (batterySize && !isDummy(batterySize)) inputDetails.push(batterySize);
                                    if (plate) inputDetails.push(plate + ' PL');
                                    if (amperes) inputDetails.push(amperes + ' AH');
                                } else if (itemType === 'parts' || itemType === 'filters' || itemType === 'breakpad') {
                                    if (partNumber && !isDummy(partNumber)) inputDetails.push(partNumber);
                                    if (quality && !isDummy(quality)) inputDetails.push(quality);
                                } else if (itemType === 'oil') {
                                    if (technology && !isDummy(technology)) inputDetails.push(technology);
                                    if (grade && !isDummy(grade)) inputDetails.push(grade);
                                }
                                
                                // Stock status color and icon
                                const stockValue = parseFloat(stock) || 0;
                                let stockColor = stockValue > 10 ? 'success' : (stockValue > 0 ? 'secondary' : 'danger');
                                let stockIcon = stockValue > 10 ? 'ti-check' : (stockValue > 0 ? '' : 'ti-x');
                                const stockDisplay = stockValue % 1 === 0 ? Math.round(stockValue) : stockValue.toFixed(2);
                                
                                // Order: Grade • Level • Company (category on second line). For oil, append unit (e.g. Can - 4 Liter).
                                const gradeLevelCompanyParts = [];
                                if (grade && !isDummy(grade)) gradeLevelCompanyParts.push(grade);
                                if (level && !isDummy(level)) gradeLevelCompanyParts.push(level);
                                if (company && !isDummy(company)) gradeLevelCompanyParts.push(company);
                                if (itemType === 'oil' && unitForFirstLine) gradeLevelCompanyParts.push(unitForFirstLine);
                                const gradeLevelCompanyLine = gradeLevelCompanyParts.length > 0 ? gradeLevelCompanyParts.join(' • ') : '';
                                
                                // Battery: first line = GL50 • 11PL • 38AH • AGS (product • plate • amperes • company)
                                let batteryFirstLine = '';
                                if (itemType === 'battery') {
                                    const bp = [];
                                    if (productName && !isDummy(productName)) bp.push(productName);
                                    if (plate) bp.push(plate + (String(plate).toUpperCase().indexOf('PL') !== -1 ? '' : 'PL'));
                                    if (amperes) bp.push(amperes + (String(amperes).toUpperCase().indexOf('AH') !== -1 ? '' : 'AH'));
                                    if (company && !isDummy(company)) bp.push(company);
                                    if (bp.length) batteryFirstLine = bp.join(' • ');
                                }
                                
                                // Build first line HTML: oil = Grade•Level•Company•Category; battery = Company•12V•380CCA; else product/detail line
                                let firstLineHtml = '';
                                let firstLineText = productName;
                                if (batteryFirstLine) {
                                    firstLineText = batteryFirstLine;
                                    const highlighted = highlightText(batteryFirstLine, query);
                                    firstLineHtml = '<div class="battery-type-sequence fw-bold mb-1 text-uppercase">' + highlighted + '</div>';
                                } else if (gradeLevelCompanyLine) {
                                    firstLineText = gradeLevelCompanyLine;
                                    // For oil with unit Can: show "4 LITER CAN" on card (e.g. 10W40 • X5 • ZIC • 4 LITER CAN)
                                    if (itemType === 'oil' && unit && (unit + '').toUpperCase() === 'CAN' && !/CAN\s*$/i.test(gradeLevelCompanyLine)) {
                                        firstLineText = gradeLevelCompanyLine + ' CAN';
                                    }
                                    const highlighted = highlightText(firstLineText, query);
                                    firstLineHtml = '<div class="fw-bold text-dark mb-1 text-uppercase">' + highlighted + '</div>';
                                    // Second line: category (e.g. petrol engine oil) then Mileage when available
                                    const secondLineParts = [];
                                    if (category && !isDummy(category)) secondLineParts.push(category);
                                    if (mileageName) secondLineParts.push('Mileage: ' + mileageName);
                                    const secondLineContent = secondLineParts.length > 0
                                        ? secondLineParts.join(' • ')
                                        : highlightText(productName, query);
                                    firstLineHtml += '<div class="small text-muted mt-0 text-uppercase">' + secondLineContent + '</div>';
                                } else if (firstLineParts.length > 0) {
                                    firstLineText = firstLineParts.join(' ');
                                    const highlightedFirstLine = highlightText(firstLineText, query);
                                    firstLineHtml = '<div class="fw-bold text-dark mb-1">' + highlightedFirstLine + '</div>';
                                } else {
                                    const highlightedProductName = highlightText(productName, query);
                                    firstLineHtml = '<div class="fw-bold text-dark mb-1">' + highlightedProductName + '</div>';
                                }
                                
                                // Oil / Can: is can mai kitne liter — show when unit is e.g. "Can - 3 Liter"
                                const literPerCanHtml = (literPerCan != null && literPerCan > 0) 
                                    ? ('<div class="small text-info mt-0">Can = ' + (Number.isInteger(literPerCan) ? literPerCan : literPerCan.toFixed(1)) + ' L</div>') 
                                    : '';
                                
                                // Stock display for card: Line1 = X Can, Line2 = X Liter, Line3 = X ML (when literPerCan)
                                let stockDisplayHtml = '';
                                if (literPerCan != null && literPerCan > 0) {
                                    var totalLiters = stockValue * literPerCan;
                                    var literPart = (Number.isInteger(totalLiters) ? totalLiters : totalLiters.toFixed(2)) + ' Liter';
                                    var mlPart = Math.round(totalLiters * 1000) + ' ML';
                                    stockDisplayHtml = '<div class="text-muted mt-1" style="font-size: 0.8rem;">' + literPart + '</div><div class="text-muted mt-0" style="font-size: 0.7rem;">' + mlPart + '</div>';
                                }
                                
                                // Build short details HTML for search display (includes vehicle) with highlighting
                                let detailsHtml = '';
                                if (searchDetails.length > 0) {
                                    const detailsText = searchDetails.join(' • ');
                                    const highlightedDetails = highlightText(detailsText, query);
                                    
                                    // Add vehicle info with different color if available
                                    if (vehicleInfo) {
                                        const highlightedVehicle = highlightText(vehicleInfo, query);
                                        detailsHtml = '<div class="small text-muted mt-1">' + highlightedDetails + ' • <span class="text-primary fw-semibold">' + highlightedVehicle + '</span></div>';
                                    } else {
                                        detailsHtml = '<div class="small text-muted mt-1">' + highlightedDetails + '</div>';
                                    }
                                } else if (vehicleInfo) {
                                    // Only vehicle info, no other details
                                    const highlightedVehicle = highlightText(vehicleInfo, query);
                                    detailsHtml = '<div class="small text-muted mt-1"><span class="text-primary fw-semibold">' + highlightedVehicle + '</span></div>';
                                }
                                
                                // Build display string for input: Product Name + Details (NO vehicle)
                                let displayString = productName;
                                if (inputDetails.length > 0) {
                                    displayString += ' ' + inputDetails.join(' ');
                                }
                                
                                // Build details strings for display below input
                                const detailsText = searchDetails.length > 0 ? searchDetails.join(' • ') : '';
                                const vehicleText = vehicleInfo || '';
                                const codeText = codeInfo || '';
                                
                                // For battery items, build line 1: company + volt (if available)
                                let line1Details = '';
                                if (itemType === 'battery') {
                                    const line1Parts = [];
                                    if (company) line1Parts.push(company);
                                    if (volt) line1Parts.push(volt + 'V');
                                    line1Details = line1Parts.join(' • ');
                                } else {
                                    line1Details = detailsText;
                                }
                                
                                // Highlight barcode
                                const highlightedCodeInfo = codeInfo ? highlightText(codeInfo, query) : '';
                                
                                // Get item image URL (same-origin path to avoid console load errors)
                                const itemImage = normalizeItemImageUrl(item.image || '');
                                
                                html += `
                                    <div class="p-3 border-bottom item-search-result" 
                                         data-type="item"
                                         data-id="${item.id}" 
                                         data-name="${productName.replace(/"/g, '&quot;')}"
                                         data-display="${displayString.replace(/"/g, '&quot;')}"
                                         data-first-line="${firstLineText.replace(/"/g, '&quot;')}"
                                         data-details="${detailsText.replace(/"/g, '&quot;')}"
                                         data-line1-details="${line1Details.replace(/"/g, '&quot;')}"
                                         data-vehicle="${vehicleText.replace(/"/g, '&quot;')}"
                                         data-code="${codeText.replace(/"/g, '&quot;')}"
                                         data-cca="${(itemType === 'battery' && cca) ? (cca + 'CCA') : ''}"
                                         data-rate="${rate}"
                                         data-unit="${unit}"
                                         data-warehouse-id="${result.warehouse_id || ''}"
                                         data-liter-per-can="${(literPerCan != null && literPerCan > 0) ? literPerCan : ''}"
                                         style="cursor: pointer; transition: all 0.2s ease; background: white;">
                                        <div class="d-flex justify-content-between align-items-start">
                                            ${itemImage ? `<div class="me-3" style="flex-shrink: 0;">
                                                <img src="${itemImage}" alt="${productName}" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                                            </div>` : ''}
                                            <div class="flex-grow-1 me-3">
                                                ${firstLineHtml}
                                                ${literPerCanHtml}
                                                ${codeInfo ? '<div class="text-primary small fw-semibold mt-1"><i class="ti ti-barcode me-1"></i>' + highlightedCodeInfo + '</div>' : ''}
                                            </div>
                                            <div class="text-end item-search-result-stock" style="min-width: 110px;">
                                                <div class="fw-bold text-primary mb-1">Rs ${parseFloat(rate).toFixed(2)}</div>
                                                <div class="small d-flex flex-column align-items-end gap-0">
                                                    <span class="badge bg-${stockColor} bg-opacity-10 text-${stockColor} d-inline-block px-2 py-1 rounded mb-1" style="font-size: 0.8rem; font-weight: 600;">
                                                        ${stockIcon ? '<i class="ti ' + stockIcon + ' me-1"></i>' : ''}${stockDisplay} ${unit}
                                                    </span>
                                                    ${stockDisplayHtml}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                        });
                        resultsDiv.html(html);
                    }
                    resultsDiv.show();
                },
                error: function(xhr) {
                    console.error('Search error:', xhr);
                    resultsDiv.html(`
                        <div class="p-3 text-center">
                            <i class="ti ti-alert-circle fs-32 text-danger mb-2" style="display: block;"></i>
                            <p class="text-danger mb-0">Error loading results. Please try again.</p>
                        </div>
                    `);
                    resultsDiv.show();
                }
            });
        }, 300);
    });
    
    // Select from search results (branch, warehouse, or item)
    $(document).on('click', '.branch-search-result, .warehouse-search-result, .item-search-result', function() {
        const resultType = $(this).data('type');
        const resultId = $(this).data('id');
        
        if (resultType === 'branch') {
            // Select branch and reload search
            selectPurchaseBranch(resultId, $(this).find('.fw-bold').text(), '');
            $('#item-search').val(''); // Clear search to show all items for this branch
            $('#item-search-results').hide();
            // Trigger search again after branch selection
            setTimeout(function() {
                $('#item-search').trigger('input');
            }, 500);
        } else if (resultType === 'warehouse') {
            // Filter by warehouse - reload search with warehouse filter
            const currentQuery = $('#item-search').val();
            $('#item-search').val(currentQuery + ' [Warehouse: ' + resultId + ']');
            $('#item-search-results').hide();
            // Could add warehouse filter here if needed
        } else if (resultType === 'item') {
            // Select item - load full details to get total_price and warehouse
            const itemId = resultId;
            const itemName = $(this).data('name');
            const itemFirstLine = $(this).data('first-line') || itemName; // Use first line text (black text from search result)
            const itemDisplay = $(this).data('display') || itemName; // Use display string (product name + details)
            const itemDetails = $(this).data('details') || ''; // All details
            const itemLine1Details = $(this).data('line1-details') || ''; // Line 1 details (company + volt for battery)
            const itemVehicle = $(this).data('vehicle') || ''; // Vehicle like "HONDA City"
            const itemCode = $(this).data('code') || ''; // Barcode/code like "6704861980"
            const itemCca = $(this).data('cca') || ''; // CCA like "380CCA"
            const itemRate = $(this).data('rate');
            const itemUnit = $(this).data('unit');
            const itemLiterPerCan = $(this).data('liter-per-can'); // e.g. 4 for "4 L per can"
            const warehouseId = $(this).closest('.item-search-result').data('warehouse-id') || '';
            
            // Set input value: Use first line text (the black text from search result)
            $('#item-search').val(itemFirstLine);
            $('#selected-item-id').val(itemId);
            $('#item-quantity').val('1');
            $('#item-unit').val(itemUnit || 'Unit');
            $('#item-search-results').hide();
            $('#item-edit-in-modal-btn').show();
            
            // Show item details below input — same format as dropdown: "KSNKDCNK • 12V • 390CCA • Changan Altis" (vehicle in primary)
            let line1 = '';
            let line2 = '';
            let line3 = '';
            
            // Line 1: Full details • vehicle (same as search result second line)
            if (itemDetails || itemVehicle) {
                const detailsPart = (itemDetails || '').trim();
                if (itemVehicle) {
                    line1 = detailsPart ? (detailsPart + ' • <span class="text-primary fw-semibold">' + itemVehicle + '</span>') : ('<span class="text-primary fw-semibold">' + itemVehicle + '</span>');
                } else {
                    line1 = detailsPart;
                }
            }
            
            // Line 2: X L per can (blue) — when item has liter per can
            if (itemLiterPerCan != null && itemLiterPerCan !== '' && !isNaN(parseFloat(itemLiterPerCan)) && parseFloat(itemLiterPerCan) > 0) {
                const val = parseFloat(itemLiterPerCan);
                line2 = (Number.isInteger(val) ? val : val.toFixed(1)) + ' L per can';
            }
            
            // Line 3: Barcode/Code (with icon)
            if (itemCode) {
                line3 = '<i class="ti ti-barcode me-1"></i>' + itemCode;
            }
            
            if (line1 || line2 || line3) {
                $('#selected-item-details-line1').html(line1 || '&nbsp;').toggleClass('battery-type-sequence fw-bold', !!(line1 && line1.indexOf(' • ') !== -1));
                if (line2) {
                    $('#selected-item-details-line2').html(line2).css('display', '');
                } else {
                    $('#selected-item-details-line2').html('').css('display', 'none');
                }
                $('#selected-item-details-line3').html(line3 || '&nbsp;');
                $('#selected-item-details-display').removeClass('d-none');
            } else {
                $('#selected-item-details-display').addClass('d-none');
            }
            
            // Load full item details to get total_price and warehouse
            $.ajax({
                url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
                method: 'GET',
                success: function(response) {
                    itemBaseRetailPrice = (response.retail_price != null && response.retail_price !== '' && !isNaN(parseFloat(response.retail_price))) ? parseFloat(response.retail_price) : null;
                    if (editingRowId !== null) {
                        var editItem = purchaseItems.find(function(i) { return i.id === editingRowId; });
                        if (editItem) {
                            $('#item-rate').val(editItem.rate != null ? Math.round(parseFloat(editItem.rate)) : 0);
                            // Retail price: always item's linked retail from API; do not change with row/calculated value
                            $('#item-retail-price').val(itemBaseRetailPrice != null ? Math.round(itemBaseRetailPrice) : '');
                            $('#item-retail-percentage').val((editItem.retail_pct != null && editItem.retail_pct !== '') ? String(editItem.retail_pct) : '');
                            if (typeof updateRetailPctSelectColor === 'function') updateRetailPctSelectColor();
                            if (typeof updateRateColumnByRetailPct === 'function') updateRateColumnByRetailPct();
                            updateRetailAfterCalc();
                        }
                    } else {
                        const purchaseRate = response.rate || response.total_price || itemRate || 0;
                        $('#item-rate').val(Math.round(parseFloat(purchaseRate) || 0));
                        $('#item-rate-label').html('<i class="ti ti-shopping-cart me-1"></i>PURCHASE RATE').removeClass('text-primary');
                        updateRetailColumnByRate();
                        var currentRetail = ($('#item-retail-price').val() || '').toString().trim();
                        if (currentRetail === '' || currentRetail == null) {
                            if (itemBaseRetailPrice != null) {
                                $('#item-retail-price').val(Math.round(itemBaseRetailPrice));
                            } else {
                                $('#item-retail-price').val('');
                            }
                        }
                        updateRetailAfterCalc();
                    }
                    if (editingRowId === null) $('#item-quantity').val('1');

                    // Auto-set unit from item's saved unit
                    if (response.unit) {
                        $('#item-unit').val(response.unit);
                    }

                    // Auto-select warehouse if available (from response or from search result)
                    const finalWarehouseId = response.warehouse_id || warehouseId;
                    if (finalWarehouseId) {
                        $('#selected-warehouse-id').val(finalWarehouseId);
                    }
                    
                    // Show item image if available (normalize to same-origin path)
                    if (response.image) {
                        $('#item-search-image').attr('src', normalizeItemImageUrl(response.image));
                        $('#item-search-image-preview').removeClass('d-none');
                    } else {
                        $('#item-search-image-preview').addClass('d-none');
                    }
                    
                    // Stock will be set after loadItemStockStatus (correct per-warehouse value)
                    $('#item-search-stock').html('<span class="text-muted small">...</span>');
                    
                    // Set warranty if available
                    if (response.warranty_value && response.warranty_unit) {
                        $('#warranty-value').val(response.warranty_value);
                        $('#warranty-unit').val(response.warranty_unit);
                    } else {
                        $('#warranty-value').val('');
                        $('#warranty-unit').val('');
                    }
                    
                    // Load stock status to show warehouse options and auto-select
                    loadItemStockStatus(itemId);
                    
                    // Load purchase history
                    loadCustomerHistory(itemId);
                },
                error: function() {
                    // Fallback to basic data if API fails
                    $('#item-rate').val(Math.round(parseFloat(itemRate || 0)));
                    updateRetailColumnByRate();
                    // Use unit from search result if available
                    if (itemUnit) {
                        $('#item-unit').val(itemUnit);
                    }
                    if (warehouseId) {
                        $('#selected-warehouse-id').val(warehouseId);
                    }
                    loadItemStockStatus(itemId);
                    loadCustomerHistory(itemId);
                }
            });
        }
    });
    
    // Load stock status for selected item
    function loadItemStockStatus(itemId) {
        $('#stock-status-section').show();
        $('#stock-status-content').show();
        $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">Loading stock status...</p>');
        
        var branchIdParam = ($('#purchaseBranchId').val() || '').toString();
        $.ajax({
            url: '{{ route("purchases.items.stock.status", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            data: branchIdParam ? { branch_id: branchIdParam } : {},
            success: function(stockData) {
                warehouseQtyFirstSelectDone = false;
                if (stockData.length === 0) {
                    $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">No stock found</p>');
                    $('#stock-status-all-branches').html('<p class="text-muted mb-0 text-center">No stock</p>');
                    if (typeof updateStockStatusListTotal === 'function') updateStockStatusListTotal();
                    return;
                }
                
                var selectedBranchId = ($('#purchaseBranchId').val() || '').toString();
                var branchTotals = [];
                var warehouseRows = []; // { warehouseDisplay, branchDisplay, quantity, unit } — only other branches
                var currentBranchDisplay = '';
                // Auto-select first warehouse row in the list if none selected (so first warehouse item is always selected)
                var initialSelectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
                if (initialSelectedIds.length === 0 && $('#selected-warehouse-id').val()) initialSelectedIds = [$('#selected-warehouse-id').val().toString()];
                if (initialSelectedIds.length === 0 && stockData.length > 0) {
                    for (var i = 0; i < stockData.length; i++) {
                        if (stockData[i].type === 'warehouse') {
                            initialSelectedIds = [stockData[i].id + ''];
                            $('#selected-warehouse-ids').val(initialSelectedIds.join(','));
                            $('#selected-warehouse-id').val(initialSelectedIds[0] || '');
                            break;
                        }
                    }
                }
                let html = '';
                var firstWarehouseRowDone = false;
                var seenWarehouseIds = {}; // Dedupe: each warehouse appears only once
                // Show only warehouses in current branch in the main list (stock-status-content)
                var showInMainList = function(stock) {
                    if (!selectedBranchId) return true;
                    if (stock.type === 'branch') return (stock.id + '') === selectedBranchId;
                    if (stock.type === 'warehouse') return (stock.branch_id + '') === selectedBranchId;
                    return true;
                };
                stockData.forEach(function(stock) {
                    if (stock.type === 'warehouse') {
                        var whId = (stock.id + '').toString();
                        if (seenWarehouseIds[whId]) return; // Skip duplicate warehouse
                        seenWarehouseIds[whId] = true;
                    }
                    if (stock.type === 'branch') {
                        if (selectedBranchId && (stock.id + '') !== selectedBranchId) {
                            branchTotals.push(stock);
                        }
                        currentBranchDisplay = stock.display || '';
                        // When showing current branch only, skip rendering the branch row so the list shows only warehouses (avoids "duplicate" look)
                        if (selectedBranchId && (stock.id + '') === selectedBranchId) {
                            return;
                        }
                    }
                    var unitLabel = (stock.unit || 'Unit').trim();
                    var qty = parseFloat(stock.quantity) || 0;
                    var qtyText = (Number.isInteger(qty) ? qty : qty.toFixed(2)) + ' ' + unitLabel;
                    // Frontend fallback: unit name "Can - 3 Liter" but API sent 1 — use 3 for Liter (6 can = 18 liter)
                    var multFromApi = stock.base_unit_multiplier != null ? Number(stock.base_unit_multiplier) : 0;
                    var hasLiter = (stock.base_unit || '').toString().toLowerCase().indexOf('liter') !== -1 || (unitLabel || '').toLowerCase().indexOf('liter') !== -1;
                    if (multFromApi <= 1 && unitLabel && (hasLiter || (unitLabel || '').match(/\d+\s*(?:liter|ltr|L)/i))) {
                        var match = unitLabel.match(/(\d+(?:\.\d+)?)\s*(?:liter|ltr|L)/i) || unitLabel.match(/(?:liter|ltr|L)\s*(\d+(?:\.\d+)?)/i);
                        if (match && parseFloat(match[1]) > 1) {
                            stock.base_unit_multiplier = parseFloat(match[1]);
                            stock.base_unit = stock.base_unit || 'Liter';
                        }
                    }
                    if (stock.type === 'branch') {
                        if (showInMainList(stock)) {
                            var branchBaseUnit = (stock.base_unit || '').trim();
                            var branchQtyHtml = '<span class="d-block fw-bold text-dark">0</span><span class="small text-muted d-block" style="font-size: 0.7rem;">' + (unitLabel || 'Unit') + '</span>';
                            if (branchBaseUnit) branchQtyHtml += '<span class="small text-muted d-block" style="font-size: 0.65rem;">' + branchBaseUnit + '</span>';
                            html += `
                            <div class="p-2 mb-1 border-bottom stock-branch-item" data-branch-id="${stock.id}" style="background-color: #fff;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold">${stock.display}</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="stock-branch-selected-qty text-end">${branchQtyHtml}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                        }
                    } else if (stock.type === 'warehouse') {
                        if (selectedBranchId && (stock.branch_id + '') !== selectedBranchId) {
                            warehouseRows.push({
                                warehouseDisplay: stock.display || '',
                                branchDisplay: currentBranchDisplay,
                                quantity: qty,
                                unit: unitLabel,
                                qtyText: qtyText
                            });
                        }
                        if (!showInMainList(stock)) return;
                        var selectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
                        if (selectedIds.length === 0 && $('#selected-warehouse-id').val()) selectedIds = [$('#selected-warehouse-id').val().toString()];
                        if (selectedIds.length === 0 && !firstWarehouseRowDone) {
                            firstWarehouseRowDone = true;
                            selectedIds = [stock.id + ''];
                            $('#selected-warehouse-ids').val(selectedIds.join(','));
                            $('#selected-warehouse-id').val(selectedIds[0] || '');
                        }
                        const isSelected = selectedIds.indexOf((stock.id + '')) !== -1;
                        var baseUnitLabel = (stock.base_unit || '').trim();
                        var multVal = stock.base_unit_multiplier != null ? Number(stock.base_unit_multiplier) : 0;
                        var isCanLiter = baseUnitLabel && multVal > 0 && (baseUnitLabel.toLowerCase().indexOf('liter') !== -1 || multVal >= 1);
                        var baseOpts = '';
                        if (isCanLiter) {
                            baseOpts = '<option value="">-</option>';
                            for (var bi = 0; bi <= 1000; bi++) {
                                var bv = bi * multVal;
                                baseOpts += '<option value="' + bv + '">' + (Number.isInteger(bv) ? bv : bv.toFixed(2)) + '</option>';
                            }
                        }
                        var mainQtyDisp = Number.isInteger(qty) ? qty : qty.toFixed(2);
                        var canLabel = (unitLabel || 'Unit').trim();
                        var literLabel = baseUnitLabel || 'Liter';
                        var pill1 = '';
                        var pill2 = '';
                        if (isCanLiter) {
                            pill1 = (multVal + ' L PER ' + (canLabel || 'CAN')).replace(/\s+/g, ' ').toUpperCase();
                            pill2 = (Number.isInteger(multVal) ? multVal : multVal.toFixed(1)) + ' Liter';
                        } else {
                            pill1 = (canLabel || 'Piece').toUpperCase();
                        }
                        html += `
                            <div class="p-2 mb-1 stock-warehouse-item stock-warehouse-bar ${isSelected ? 'bg-primary text-white' : ''}" 
                                 data-warehouse-id="${stock.id}"
                                 data-branch-id="${stock.branch_id}"
                                 data-display="${((stock.display || stock.name || '').replace(/\s*\([^)]*\)\s*$/, '').trim() || '').replace(/"/g, '&quot;')}"
                                 data-quantity="${qty}"
                                 data-unit="${(unitLabel || '').replace(/"/g, '&quot;')}"
                                 data-base-unit="${(stock.base_unit || '').replace(/"/g, '&quot;')}"
                                 data-base-unit-multiplier="${stock.base_unit_multiplier != null ? Number(stock.base_unit_multiplier) : ''}"
                                 data-qty-text="${(qtyText || '').replace(/"/g, '&quot;')}"
                                 style="cursor: pointer; transition: all 0.2s; ${isSelected ? '' : 'background-color: #e9ecef; color: #1a1a1a;'}">
                                <div class="stock-warehouse-bar-inner d-flex align-items-center flex-wrap gap-2">
                                    <div class="stock-bar-display-wrap">
                                        <span class="stock-bar-check">${isSelected ? '✓' : ''}</span>
                                        <span class="stock-bar-display-text">${((stock.display || stock.name || 'Warehouse').replace(/\s*\([^)]*\)\s*$/, '').trim() || 'Warehouse').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')}</span>
                                    </div>
                                    ${pill1 ? '<span class="stock-bar-pill">' + pill1.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>' : ''}
                                    ${pill2 ? '<span class="stock-bar-pill">' + pill2.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span>' : ''}
                                    <span class="stock-bar-qty-label">${qtyText || (mainQtyDisp + ' ' + canLabel)}</span>
                                    <div class="stock-bar-inputs">
                                        <select class="stock-bar-input stock-warehouse-qty-input" data-warehouse-id="${stock.id}" onclick="event.stopPropagation();" data-unit="${(unitLabel || 'Piece').replace(/"/g, '&quot;')}" title="Quantity">${(function(){ var opts = '<option value="">0</option>'; for (var i = 1; i <= 1000; i++) { opts += '<option value="'+i+'">'+i+'</option>'; } return opts; })()}</select>
                                        ${baseOpts ? '<select class="stock-bar-input stock-warehouse-base-qty-input" data-warehouse-id="' + stock.id + '" onclick="event.stopPropagation();" data-multiplier="' + multVal + '" title="' + (baseUnitLabel || '').replace(/"/g, '&quot;') + '">' + baseOpts + '</select>' : '<input type="text" class="stock-bar-input" readonly value="" style="opacity: 0.6;">'}
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });
                
                if (!html.trim() && selectedBranchId) {
                    $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">No warehouses with stock in this branch for this item.</p>');
                    if (typeof updateStockStatusListTotal === 'function') updateStockStatusListTotal();
                } else {
                    $('#stock-status-list').html(html);
                    var $list = $('#stock-status-list');
                    var $branchItems = $list.find('.stock-branch-item');
                    $branchItems.get().reverse().forEach(function(el) { $(el).prependTo($list); });
                    var $firstSel = $list.find('.stock-warehouse-item.bg-primary').first();
                    if ($firstSel.length) {
                        var $afterBranch = $list.find('.stock-branch-item').last();
                        if ($afterBranch.length) $firstSel.insertAfter($afterBranch); else $firstSel.prependTo($list);
                    }
                }
                // By default: warehouse qty dropdowns stay empty; sync #item-quantity only if user has selected a qty
                var $firstWhQty = $('#stock-status-list .stock-warehouse-qty-input').first();
                if ($firstWhQty.length) {
                    var qtyFromWarehouse = parseFloat($firstWhQty.val());
                    if (!isNaN(qtyFromWarehouse) && qtyFromWarehouse >= 1) {
                        qtyFromWarehouse = Math.max(1, Math.min(1000, Math.round(qtyFromWarehouse)));
                        $('#item-quantity').val(qtyFromWarehouse);
                        $('#item-quantity-input').val(qtyFromWarehouse).hide();
                    } else {
                        $('#item-quantity').val('');
                        $('#item-quantity-input').val('').hide();
                    }
                } else if (typeof syncQuantityToWarehouseInputs === 'function') {
                    syncQuantityToWarehouseInputs();
                }
                var $whItems = $('.stock-warehouse-item');
                if ($whItems.length) {
                    $('#stock-status-select-all-wrap').removeClass('d-none');
                    $('#stock-status-select-all').prop('checked', $whItems.length === $whItems.filter('.bg-primary').length);
                } else {
                    $('#stock-status-select-all-wrap').addClass('d-none');
                }
                if (typeof updateStockStatusListTotal === 'function') updateStockStatusListTotal();
                // Sync image-preview: show total stock of selected warehouses; if none selected use first warehouse
                var $checked = $('.stock-warehouse-item.bg-primary');
                if (!$checked.length) $checked = $('.stock-warehouse-item').first();
                if ($checked.length) {
                    var totalQ = 0, u = ($checked.first().data('unit') || 'Unit').trim(), baseU = ($checked.first().data('base-unit') || '').trim(), mult = parseFloat($checked.first().attr('data-base-unit-multiplier')) || 0;
                    $checked.each(function(){ totalQ += parseFloat($(this).data('quantity')) || 0; });
                    var qtyPart = (Number.isInteger(totalQ) ? totalQ : totalQ.toFixed(2));
                    var stockColorClass = totalQ > 10 ? 'success' : (totalQ > 0 ? 'warning' : 'danger');
                    var stockHtml = '<div class="text-end"><div class="small">';
                    stockHtml += '<span class="badge bg-' + stockColorClass + ' bg-opacity-10 text-' + stockColorClass + ' d-block">' + qtyPart + ' ' + (u || 'Unit') + '</span>';
                    if (baseU && mult > 0) {
                        var baseQty = totalQ * mult;
                        var baseQtyPart = (Number.isInteger(baseQty) ? baseQty : baseQty.toFixed(2));
                        stockHtml += '<div class="small text-muted mt-1">' + baseQtyPart + ' ' + baseU + '</div>';
                        if ((baseU || '').toLowerCase().indexOf('liter') !== -1) {
                            stockHtml += '<div class="small text-muted mt-0" style="font-size: 0.65rem;">' + Math.round(baseQty * 1000) + ' ML</div>';
                        }
                    }
                    stockHtml += '</div></div>';
                    $('#item-search-stock').html(stockHtml);
                    var names = []; $checked.each(function(){ names.push($(this).data('display')); });
                    $('#item-search-warehouse').text($checked.length > 1 ? names.length + ' warehouses' : (names[0] || ''));
                } else {
                    $('#item-search-stock').html('');
                }
                
                // All branches stock: branch lines (Name (id) qty · Rs) then Total below
                var allBranchesHtml = '';
                var initialTotalQty = 0;
                var initialTotalUnit = 'Piece';
                if (warehouseRows.length > 0) {
                    var byBranch = {};
                    warehouseRows.forEach(function(r) {
                        var key = r.branchDisplay || '';
                        var q = parseFloat(r.quantity) || 0;
                        var u = (r.unit || 'Piece').trim();
                        var whDisplay = (r.warehouseDisplay || 'Warehouse').replace(/</g, '&lt;');
                        var whQtyText = (Number.isInteger(q) ? q : q.toFixed(2)) + ' ' + u;
                        if (!byBranch[key]) byBranch[key] = { display: r.branchDisplay, qty: 0, unit: u, warehouses: [] };
                        byBranch[key].qty += q;
                        byBranch[key].warehouses.push(whDisplay + ' ' + whQtyText);
                        initialTotalQty += q;
                        if (u) initialTotalUnit = u;
                    });
                    Object.keys(byBranch).forEach(function(k) {
                        var b = byBranch[k];
                        var qt = (Number.isInteger(b.qty) ? b.qty : b.qty.toFixed(2)) + ' ' + (b.unit || 'Piece');
                        allBranchesHtml += '<div class="d-flex justify-content-between align-items-center py-1 border-bottom small" style="border-color: #dee2e6 !important;">';
                        allBranchesHtml += '<span class="fw-bold">' + (b.display || '—').replace(/</g, '&lt;') + '</span>';
                        allBranchesHtml += '<span class="text-muted">' + qt + '</span>';
                        allBranchesHtml += '</div>';
                        if (b.warehouses && b.warehouses.length > 0) {
                            allBranchesHtml += '<div class="d-flex justify-content-between align-items-center py-0 px-2 small" style="border-color: #dee2e6 !important; font-size: 0.8rem; color: #6c757d;">';
                            allBranchesHtml += '<span>' + b.warehouses.join(' · ') + '</span>';
                            allBranchesHtml += '</div>';
                        }
                    });
                } else if (branchTotals.length > 0) {
                    branchTotals.forEach(function(b) {
                        var u = (b.unit || 'Unit').trim();
                        var q = parseFloat(b.quantity) || 0;
                        initialTotalQty += q;
                        if (u) initialTotalUnit = u;
                        var qt = (Number.isInteger(q) ? q : q.toFixed(2)) + ' ' + u;
                        allBranchesHtml += '<div class="d-flex justify-content-between align-items-center py-1 border-bottom small" style="border-color: #dee2e6 !important;">';
                        allBranchesHtml += '<span class="fw-bold">' + (b.display || '—').replace(/</g, '&lt;') + ' (' + (b.id || '') + ')</span>';
                        allBranchesHtml += '<span class="text-muted">' + qt + '</span>';
                        allBranchesHtml += '</div>';
                    });
                }
                if (allBranchesHtml) {
                    var totalText = (Number.isInteger(initialTotalQty) ? initialTotalQty : initialTotalQty.toFixed(2)) + ' ' + initialTotalUnit;
                    allBranchesHtml += '<div class="d-flex py-1 small fw-bold mt-1" style="border-color: #dee2e6 !important;">Total — ' + totalText + '</div>';
                }
                $('#stock-status-all-branches').html(allBranchesHtml || '<p class="text-muted mb-0 text-center">' + (selectedBranchId ? 'No stock in other branches' : 'No branches') + '</p>');
                // Fetch all branches stock (no branch_id) to populate "All branches stock" — show all branches
                $.ajax({
                    url: '{{ route("purchases.items.stock.status", ":id") }}'.replace(':id', itemId),
                    method: 'GET',
                    data: {},
                    success: function(allStockData) {
                        var allHtml = '';
                        var curBranchDisplay = '';
                        var curBranchId = '';
                        var curBranchCode = '';
                        var totalQty = 0;
                        var totalUnit = 'Piece';
                        var branchRows = [];
                        if (allStockData && allStockData.length > 0) {
                            allStockData.forEach(function(s) {
                                var u = (s.unit || 'Unit').trim();
                                var q = parseFloat(s.quantity) || 0;
                                var rate = parseFloat(s.rate || s.price || s.sale_price) || 0;
                                if (s.type === 'branch') {
                                    curBranchDisplay = s.display || s.name || '';
                                    curBranchId = (s.id + '') || '';
                                    curBranchCode = (s.code + '') || '';
                                } else if (s.type === 'warehouse') {
                                    totalQty += q;
                                    if (u) totalUnit = u;
                                    var whDisplay = (s.display || s.name || 'Warehouse').replace(/</g, '&lt;');
                                    var whQty = (Number.isInteger(q) ? q : q.toFixed(2)) + ' ' + u;
                                    var existing = branchRows.find(function(r) { return r.branchId === curBranchId; });
                                    if (existing) {
                                        existing.qty += q;
                                        existing.value += q * rate;
                                        existing.warehouses.push({ display: whDisplay, qty: q, qtyText: whQty, unit: u });
                                    } else {
                                        branchRows.push({
                                            branchId: curBranchId,
                                            display: curBranchDisplay,
                                            code: curBranchCode,
                                            qty: q,
                                            unit: u,
                                            value: q * rate,
                                            warehouses: [{ display: whDisplay, qty: q, qtyText: whQty, unit: u }]
                                        });
                                    }
                                }
                            });
                            branchRows.forEach(function(b) {
                                var qt = (Number.isInteger(b.qty) ? b.qty : b.qty.toFixed(2)) + ' ' + (b.unit || totalUnit);
                                var rsPart = (b.value > 0) ? (' · Rs ' + Math.round(b.value)) : '';
                                var branchLabel = (b.display || '—').replace(/</g, '&lt;');
                                if (b.code && branchLabel.indexOf('(') === -1) branchLabel += ' (' + b.code + ')';
                                else if (b.branchId && branchLabel.indexOf('(') === -1) branchLabel += ' (' + b.branchId + ')';
                                allHtml += '<div class="d-flex justify-content-between align-items-center py-1 border-bottom small" style="border-color: #dee2e6 !important;">';
                                allHtml += '<span class="fw-bold">' + branchLabel + '</span>';
                                allHtml += '<span class="text-muted">' + qt + rsPart + '</span>';
                                allHtml += '</div>';
                                if (b.warehouses && b.warehouses.length > 0) {
                                    var whParts = b.warehouses.map(function(w) { return w.display + ' ' + w.qtyText; });
                                    allHtml += '<div class="d-flex justify-content-between align-items-center py-0 px-2 small" style="border-color: #dee2e6 !important; font-size: 0.8rem; color: #6c757d;">';
                                    allHtml += '<span>' + whParts.join(' · ') + '</span>';
                                    allHtml += '</div>';
                                }
                            });
                            var totalText = (Number.isInteger(totalQty) ? totalQty : totalQty.toFixed(2)) + ' ' + totalUnit;
                            allHtml += '<div class="d-flex py-1 small fw-bold mt-1" style="border-color: #dee2e6 !important;">Total — ' + totalText + '</div>';
                        }
                        $('#stock-status-all-branches').html(allHtml || '<p class="text-muted mb-0 text-center">No stock in other branches</p>');
                    },
                    error: function() {
                        $('#stock-status-all-branches').html('<p class="text-muted mb-0 text-center">Could not load all branches</p>');
                    }
                });
                // Apply pending edit (row click → edit): set warehouse, qty, rate, unit, discount, tax, warranty
                if (pendingEditItem) {
                    var pe = pendingEditItem;
                    var editQty = Math.max(1, Math.min(1000, Math.round(parseFloat(pe.quantity) || 0)));
                    if (pe.warehouse_id) {
                        var whIdStr = String(pe.warehouse_id);
                        var $row = $('#stock-status-list .stock-warehouse-item[data-warehouse-id="' + whIdStr + '"]');
                        if ($row.length) {
                            var selectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
                            if (selectedIds.indexOf(whIdStr) === -1) selectedIds.push(whIdStr);
                            $('#stock-status-list .stock-warehouse-item').each(function() {
                                var wid = ($(this).data('warehouse-id') || '').toString();
                                if (wid && selectedIds.indexOf(wid) === -1) selectedIds.push(wid);
                            });
                            $('#selected-warehouse-ids').val(selectedIds.join(','));
                            $('#selected-warehouse-id').val(pe.warehouse_id);
                            if ($('#item-save-warehouse option[value="' + whIdStr + '"]').length) $('#item-save-warehouse').val(pe.warehouse_id);
                            var $qtyInput = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + whIdStr + '"]');
                            var qtyToShow = (editQty <= 0 ? '' : String(editQty));
                            if ($qtyInput.length) {
                                $qtyInput.val(qtyToShow);
                                if ($qtyInput[0] && $qtyInput[0].value !== qtyToShow) { $qtyInput[0].value = qtyToShow; }
                            }
                            if (pe.quantities_by_warehouse && typeof pe.quantities_by_warehouse === 'object') {
                                for (var qwhId in pe.quantities_by_warehouse) {
                                    if (!pe.quantities_by_warehouse.hasOwnProperty(qwhId)) continue;
                                    var qq = pe.quantities_by_warehouse[qwhId];
                                    var qwhStr = String(qwhId);
                                    var $qSel = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + qwhStr + '"]');
                                    var qVal = (qq <= 0 ? '' : String(qq));
                                    if ($qSel.length) {
                                        $qSel.val(qVal);
                                        if ($qSel[0] && $qSel[0].value !== qVal) { $qSel[0].value = qVal; }
                                    }
                                }
                            }
                            $('#item-quantity').val(pe.quantity); $('#item-quantity-input').val(pe.quantity).hide();
                        } else {
                            // Edit warehouse not in list (e.g. 0 stock): add a row so selected qty shows when editing
                            var whDisplay = (pe.warehouse_name || '').trim() || ('Warehouse ' + pe.warehouse_id);
                            var $whSel = $('#item-save-warehouse');
                            if ($whSel.find('option[value="' + pe.warehouse_id + '"]').length) whDisplay = $whSel.find('option[value="' + pe.warehouse_id + '"]').text().trim() || whDisplay;
                            whDisplay = whDisplay.replace(/"/g, '&quot;');
                            var qtyOpts = '<option value="">0</option>';
                            for (var oi = 1; oi <= 1000; oi++) { qtyOpts += '<option value="' + oi + '">' + oi + '</option>'; }
                            var uLabel = (pe.unit || 'Piece').replace(/"/g, '&quot;');
                            var editRowHtml = '<div class="p-2 mb-1 stock-warehouse-item stock-warehouse-bar bg-primary text-white" data-warehouse-id="' + pe.warehouse_id + '" data-display="' + whDisplay + '" data-quantity="0" data-unit="' + uLabel + '" style="cursor: pointer;">';
                            editRowHtml += '<div class="stock-warehouse-bar-inner d-flex align-items-center flex-wrap gap-2">';
                            editRowHtml += '<div class="stock-bar-display-wrap"><span class="stock-bar-check">✓</span><span class="stock-bar-display-text">' + (whDisplay.replace(/&quot;/g, '"') || 'Warehouse').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') + '</span></div>';
                            editRowHtml += '<span class="stock-bar-pill">' + (uLabel || 'Piece').toUpperCase() + '</span>';
                            editRowHtml += '<span class="stock-bar-qty-label">0 ' + (pe.unit || 'Piece') + '</span>';
                            editRowHtml += '<div class="stock-bar-inputs">';
                            editRowHtml += '<select class="stock-bar-input stock-warehouse-qty-input" data-warehouse-id="' + pe.warehouse_id + '" onclick="event.stopPropagation();" data-unit="' + uLabel + '" title="Quantity">' + qtyOpts + '</select>';
                            editRowHtml += '<input type="text" class="stock-bar-input" readonly value="" style="opacity: 0.6;">';
                            editRowHtml += '</div></div></div>';
                            $('#stock-status-list').append(editRowHtml);
                            var $newQty = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + pe.warehouse_id + '"]');
                            if ($newQty.length) $newQty.val(editQty <= 0 ? '' : String(editQty));
                            if (pe.quantities_by_warehouse && typeof pe.quantities_by_warehouse === 'object') {
                                for (var qwhId in pe.quantities_by_warehouse) {
                                    if (!pe.quantities_by_warehouse.hasOwnProperty(qwhId)) continue;
                                    var qq = pe.quantities_by_warehouse[qwhId];
                                    var qwhStr = String(qwhId);
                                    var $qSel = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + qwhStr + '"]');
                                    var qVal = (qq <= 0 ? '' : String(qq));
                                    if ($qSel.length) { $qSel.val(qVal); if ($qSel[0] && $qSel[0].value !== qVal) { $qSel[0].value = qVal; } }
                                }
                            }
                            $('#selected-warehouse-id').val(pe.warehouse_id);
                            $('#selected-warehouse-ids').val(pe.warehouse_id);
                            if ($whSel.find('option[value="' + pe.warehouse_id + '"]').length) $whSel.val(pe.warehouse_id);
                            $('#item-quantity').val(pe.quantity); $('#item-quantity-input').val(pe.quantity).hide();
                        }
                    }
                    // Re-apply row's purchase rate so edit shows what was entered (manual or from retail %)
                    if (pe.rate != null) $('#item-rate').val(pe.rate);
                    // Retail price: always item's linked retail; do not overwrite with row value
                    if (itemBaseRetailPrice != null) $('#item-retail-price').val(Math.round(itemBaseRetailPrice));
                    else $('#item-retail-price').val('');
                    if (pe.retail_pct != null && pe.retail_pct !== '') $('#item-retail-percentage').val(String(pe.retail_pct));
                    else $('#item-retail-percentage').val('');
                    if (typeof updateRetailPctSelectColor === 'function') updateRetailPctSelectColor();
                    if (typeof updateRateColumnByRetailPct === 'function') updateRateColumnByRetailPct();
                    updateRetailAfterCalc();
                    updateRetailColumnByRate();
                    if (pe.unit != null) $('#item-unit').val(pe.unit);
                    if (pe.discount != null) { $('#discount-type').val('amount'); $('#item-discount').val(pe.discount); }
                    if (pe.tax_percentage != null) $('#item-tax').val(pe.tax_percentage);
                    if (pe.warranty != null) {
                        var w = (pe.warranty || '').toString().trim().split(/\s+/);
                        $('#warranty-value').val(w[0] || ''); $('#warranty-unit').val(w[1] || '');
                    }
                    if (pe.warehouse_id) {
                        var selectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
                        var wid = (pe.warehouse_id + '').toString();
                        if (wid && selectedIds.indexOf(wid) === -1) selectedIds.push(wid);
                        $('#selected-warehouse-ids').val(selectedIds.join(','));
                        if (typeof applyStockWarehouseSelectionFromIds === 'function') applyStockWarehouseSelectionFromIds();
                        var editQtyForSelect = Math.max(1, Math.min(1000, Math.round(parseFloat(pe.quantity) || 0)));
                        var qtyVal = editQtyForSelect <= 0 ? '' : String(editQtyForSelect);
                        function setQtySelectVal() {
                            var $sel = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + wid + '"]');
                            if (!$sel.length) return;
                            if ($sel.find('option[value="' + qtyVal + '"]').length || qtyVal === '') {
                                $sel.val(qtyVal);
                                var el = $sel[0];
                                if (el && el.value !== qtyVal) { el.value = qtyVal; }
                            }
                        }
                        setQtySelectVal();
                        setTimeout(setQtySelectVal, 0);
                        setTimeout(setQtySelectVal, 80);
                        if (pe.quantities_by_warehouse && typeof pe.quantities_by_warehouse === 'object') {
                            setTimeout(function() {
                                for (var qwhId in pe.quantities_by_warehouse) {
                                    if (!pe.quantities_by_warehouse.hasOwnProperty(qwhId)) continue;
                                    var qq = pe.quantities_by_warehouse[qwhId];
                                    var qwhStr = String(qwhId);
                                    var $qSel = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + qwhStr + '"]');
                                    var qVal = (qq <= 0 ? '' : String(qq));
                                    if ($qSel.length && ($qSel.find('option[value="' + qVal + '"]').length || qVal === '')) {
                                        $qSel.val(qVal);
                                        if ($qSel[0]) $qSel[0].value = qVal;
                                    }
                                }
                            }, 100);
                        }
                    }
                    pendingEditItem = null;
                }
                // Keep all warehouse rows blue and stable: add any row with qty to selectedIds; if none selected, select all
                var selectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
                $('#stock-status-list .stock-warehouse-qty-input').each(function() {
                    if (parseFloat($(this).val()) > 0) {
                        var wid = ($(this).data('warehouse-id') || '').toString();
                        if (wid && selectedIds.indexOf(wid) === -1) selectedIds.push(wid);
                    }
                });
                if (selectedIds.length === 0) {
                    $('#stock-status-list .stock-warehouse-item').each(function() {
                        var wid = ($(this).data('warehouse-id') || '').toString();
                        if (wid) selectedIds.push(wid);
                    });
                }
                $('#selected-warehouse-ids').val(selectedIds.join(','));
                if (selectedIds.length && !$('#selected-warehouse-id').val()) $('#selected-warehouse-id').val(selectedIds[0]);
                if (typeof applyStockWarehouseSelectionFromIds === 'function') applyStockWarehouseSelectionFromIds();
                if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
                if (typeof updateStockBranchSelectedQty === 'function') updateStockBranchSelectedQty();
                if (typeof updateStockStatusRetailPctBadge === 'function') updateStockStatusRetailPctBadge();
            },
            error: function() {
                $('#stock-status-list').html('<p class="text-danger mb-0 small text-center">Error loading stock status</p>');
                $('#stock-status-all-branches').html('<p class="text-muted mb-0 text-center">—</p>');
                if (typeof updateStockStatusListTotal === 'function') updateStockStatusListTotal();
            }
        });
    }
    
    var stockWarehouseUpdating = false; // prevent change handler when we set .prop('checked') programmatically
    // Toggle warehouse selection (multiple allowed). If row has quantity set, keep it selected so color stays stable.
    function toggleStockWarehouseRow($row) {
        var id = $row.data('warehouse-id') + '';
        var selectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
        var idx = selectedIds.indexOf(id);
        if (idx !== -1) {
            var qtyVal = ($row.find('.stock-warehouse-qty-input').val() || '').toString().trim();
            if (qtyVal && parseFloat(qtyVal) > 0) {
                return;
            }
            selectedIds.splice(idx, 1);
        } else {
            selectedIds.push(id);
        }
        $('#selected-warehouse-ids').val(selectedIds.join(','));
        var firstId = selectedIds[0] || '';
        $('#selected-warehouse-id').val(firstId);
        if (firstId && $('#item-save-warehouse option[value="' + firstId + '"]').length) $('#item-save-warehouse').val(firstId);
        
        stockWarehouseUpdating = true;
        $('.stock-warehouse-item').each(function() {
            var rid = $(this).data('warehouse-id') + '';
            var sel = selectedIds.indexOf(rid) !== -1;
            $(this).removeClass('bg-primary text-white bg-light').css('background-color', '');
            if (sel) $(this).addClass('bg-primary text-white'); else $(this).css('background-color', '#e9ecef');
            var $check = $(this).find('.stock-bar-check');
            if ($check.length) $check.html(sel ? '✓' : ''); else $(this).find('span:first').html(sel ? '✓' : '');
        });
        
        var $checked = $('.stock-warehouse-item.bg-primary');
        if ($checked.length) {
            var totalQ = 0, u = ($checked.first().data('unit') || 'Unit').trim(), baseU = ($checked.first().data('base-unit') || '').trim(), mult = parseFloat($checked.first().attr('data-base-unit-multiplier')) || 0;
            $checked.each(function(){ totalQ += parseFloat($(this).data('quantity')) || 0; });
            var qtyPart = (Number.isInteger(totalQ) ? totalQ : totalQ.toFixed(2));
            var stockColor = totalQ > 10 ? 'text-success' : (totalQ > 0 ? 'text-warning' : 'text-danger');
            var stockColorClass = totalQ > 10 ? 'success' : (totalQ > 0 ? 'warning' : 'danger');
            var stockHtml = '<div class="text-end"><div class="small">';
            stockHtml += '<span class="badge bg-' + stockColorClass + ' bg-opacity-10 text-' + stockColorClass + ' d-block">' + qtyPart + ' ' + (u || 'Unit') + '</span>';
            if (baseU && mult > 0) {
                var baseQty = totalQ * mult;
                var baseQtyPart = (Number.isInteger(baseQty) ? baseQty : baseQty.toFixed(2));
                stockHtml += '<div class="small text-muted mt-1">' + baseQtyPart + ' ' + baseU + '</div>';
                if ((baseU || '').toLowerCase().indexOf('liter') !== -1) {
                    stockHtml += '<div class="small text-muted mt-0" style="font-size: 0.65rem;">' + Math.round(baseQty * 1000) + ' ML</div>';
                }
            }
            stockHtml += '</div></div>';
            $('#item-search-stock').html(stockHtml);
            var names = []; $checked.each(function(){ names.push($(this).data('display')); });
            $('#item-search-warehouse').text($checked.length > 1 ? $checked.length + ' warehouses' : (names[0] || ''));
        } else {
            $('#item-search-stock').html('');
            $('#item-search-warehouse').text('');
        }
        var $all = $('.stock-warehouse-item');
        $('#stock-status-select-all').prop('checked', $all.length > 0 && $all.length === $('.stock-warehouse-item.bg-primary').length);
        var $list = $('#stock-status-list');
        $list.find('.stock-branch-item').get().reverse().forEach(function(el) { $(el).prependTo($list); });
        var $firstSel = $list.find('.stock-warehouse-item.bg-primary').first();
        if ($firstSel.length) {
            var $afterBranch = $list.find('.stock-branch-item').last();
            if ($afterBranch.length) $firstSel.insertAfter($afterBranch); else $firstSel.prependTo($list);
        }
        setTimeout(function() { stockWarehouseUpdating = false; if (typeof updateStockBranchSelectedQty === 'function') updateStockBranchSelectedQty(); if (typeof updateItemLineTotal === 'function') updateItemLineTotal(); }, 0);
    }
    
    $(document).on('change', '#stock-status-select-all', function() {
        var check = $(this).is(':checked');
        var $rows = $('.stock-warehouse-item');
        var selectedIds = [];
        if (check) {
            $rows.each(function() { selectedIds.push($(this).data('warehouse-id') + ''); });
        }
        $('#selected-warehouse-ids').val(selectedIds.join(','));
        $('#selected-warehouse-id').val(selectedIds[0] || '');
        stockWarehouseUpdating = true;
        $rows.each(function() {
            var rid = $(this).data('warehouse-id') + '';
            var sel = selectedIds.indexOf(rid) !== -1;
            $(this).removeClass('bg-primary text-white bg-light').css('background-color', '');
            if (sel) $(this).addClass('bg-primary text-white'); else $(this).css('background-color', '#e9ecef');
            var $check = $(this).find('.stock-bar-check');
            if ($check.length) $check.html(sel ? '✓' : ''); else $(this).find('span:first').html(sel ? '✓' : '');
        });
        var $checked = $('.stock-warehouse-item.bg-primary');
        if ($checked.length) {
            var totalQ = 0, u = ($checked.first().data('unit') || 'Unit').trim(), baseU = ($checked.first().data('base-unit') || '').trim(), mult = parseFloat($checked.first().attr('data-base-unit-multiplier')) || 0;
            $checked.each(function(){ totalQ += parseFloat($(this).data('quantity')) || 0; });
            var qtyPart = (Number.isInteger(totalQ) ? totalQ : totalQ.toFixed(2));
            var stockColorClass = totalQ > 10 ? 'success' : (totalQ > 0 ? 'warning' : 'danger');
            var stockHtml = '<div class="text-end"><div class="small">';
            stockHtml += '<span class="badge bg-' + stockColorClass + ' bg-opacity-10 text-' + stockColorClass + ' d-block">' + qtyPart + ' ' + (u || 'Unit') + '</span>';
            if (baseU && mult > 0) {
                var baseQty = totalQ * mult;
                var baseQtyPart = (Number.isInteger(baseQty) ? baseQty : baseQty.toFixed(2));
                stockHtml += '<div class="small text-muted mt-1">' + baseQtyPart + ' ' + baseU + '</div>';
                if ((baseU || '').toLowerCase().indexOf('liter') !== -1) {
                    stockHtml += '<div class="small text-muted mt-0" style="font-size: 0.65rem;">' + Math.round(baseQty * 1000) + ' ML</div>';
                }
            }
            stockHtml += '</div></div>';
            $('#item-search-stock').html(stockHtml);
            $('#item-search-warehouse').text($checked.length > 1 ? $checked.length + ' warehouses' : ($checked.first().data('display') || ''));
        } else {
            $('#item-search-stock').html('');
            $('#item-search-warehouse').text('');
        }
        var $list = $('#stock-status-list');
        $list.find('.stock-branch-item').get().reverse().forEach(function(el) { $(el).prependTo($list); });
        var $firstSel = $list.find('.stock-warehouse-item.bg-primary').first();
        if ($firstSel.length) {
            var $afterBranch = $list.find('.stock-branch-item').last();
            if ($afterBranch.length) $firstSel.insertAfter($afterBranch); else $firstSel.prependTo($list);
        }
        setTimeout(function() { stockWarehouseUpdating = false; if (typeof updateStockBranchSelectedQty === 'function') updateStockBranchSelectedQty(); if (typeof updateItemLineTotal === 'function') updateItemLineTotal(); }, 0);
    });
    
    $(document).on('click', '.stock-warehouse-item', function(e) {
        toggleStockWarehouseRow($(this));
    });

    // Click item thumbnail to view full image
    $(document).on('click', '#item-search-image', function() {
        var src = $(this).attr('src');
        if (src) {
            $('#item-image-view-full').attr('src', src);
            $('#item-image-view-modal').modal('show');
        }
    });

    // Click purchase table row item photo to view full image
    $(document).on('click', '#items-tbody .purchase-row-item-thumb', function(e) {
        e.stopPropagation();
        var src = $(this).data('full-src') || $(this).attr('src');
        if (src) {
            $('#item-image-view-full').attr('src', src);
            $('#item-image-view-modal').modal('show');
        }
    });
    
    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#item-search, #item-search-results').length) {
            $('#item-search-results').hide();
        }
    });

    // Item search (old method - kept for backward compatibility if needed)
    // Now using YouTube-style search modal above

    // Load item details
    function loadItemDetails(itemId) {
        $.ajax({
            url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(response) {
                $('#selected-item-id').val(response.id);
                $('#item-edit-in-modal-btn').show();
                if (editingRowId === null) {
                    var apiName = (response.name || '').toString();
                    var plainName = (typeof stripHtml === 'function' ? stripHtml(apiName) : apiName.replace(/<[^>]*>/g, '')).trim();
                    $('#item-search').val(plainName || apiName || ('Item #' + (response.id || '')));
                }
                if (editingRowId === null) $('#item-quantity').val('1');
                
                // Show selected item details — same format as dropdown: "Group • 12V • CCA • Vehicle" for battery; for oil no 12V
                var detailsArr = [];
                if (response.group_name) detailsArr.push(response.group_name);
                if (response.type !== 'oil' && response.volt) detailsArr.push(response.volt + (response.volt.toString().indexOf('V') !== -1 ? '' : 'V'));
                if (response.cca) detailsArr.push(response.cca + (response.cca.toString().indexOf('CCA') !== -1 ? '' : 'CCA'));
                var detailsPart = detailsArr.length ? detailsArr.join(' • ') : '';
                var vehiclePart = (response.vehicle_name || (response.manufacturer && response.model ? (response.manufacturer + ' ' + response.model) : '')) || '';
                var line1 = '';
                if (detailsPart && vehiclePart) line1 = detailsPart + ' • <span class="text-primary fw-semibold">' + vehiclePart + '</span>';
                else if (vehiclePart) line1 = '<span class="text-primary fw-semibold">' + vehiclePart + '</span>';
                else if (detailsPart) line1 = detailsPart;
                else line1 = (response.name || '').trim();
                var line3 = (response.bar_code || '') ? ('<i class="ti ti-barcode me-1"></i>' + (response.bar_code + '').replace(/</g, '&lt;')) : '';
                if (line1 || line3) {
                    $('#selected-item-details-line1').html(line1 || '&nbsp;');
                    $('#selected-item-details-line3').html(line3 || '&nbsp;');
                    $('#selected-item-details-display').removeClass('d-none');
                } else {
                    $('#selected-item-details-display').addClass('d-none');
                }
                // Store base retail from API for restoring when % is cleared
                itemBaseRetailPrice = (response.retail_price != null && response.retail_price !== '' && !isNaN(parseFloat(response.retail_price))) ? parseFloat(response.retail_price) : null;
                // Set purchase rate and retail price (when editing, keep row's values; else use API)
                if (editingRowId !== null) {
                    var editItem = purchaseItems.find(function(i) { return i.id === editingRowId; });
                    if (editItem) {
                        $('#item-rate').val(editItem.rate != null ? Math.round(parseFloat(editItem.rate)) : 0);
                        $('#item-rate-label').html('<i class="ti ti-shopping-cart me-1"></i>PURCHASE RATE').removeClass('text-primary');
                        updateRetailColumnByRate();
                        // Retail price: always item's linked retail from API; do not change with row/calculated value
                        $('#item-retail-price').val(itemBaseRetailPrice != null ? Math.round(itemBaseRetailPrice) : '');
                        $('#item-retail-percentage').val((editItem.retail_pct != null && editItem.retail_pct !== '') ? String(editItem.retail_pct) : '');
                        updateRetailPctSelectColor();
                        updateRateColumnByRetailPct();
                        updateRetailAfterCalc();
                        updateRetailColumnByRate();
                    }
                } else {
                    // Use item's purchase rate (rate), not total_price — so we never show retail in purchase rate field
                    const purchaseRate = response.rate || response.total_price || 0;
                    $('#item-rate').val(Math.round(parseFloat(purchaseRate) || 0));
                    $('#item-rate-label').html('<i class="ti ti-shopping-cart me-1"></i>PURCHASE RATE').removeClass('text-primary');
                    updateRetailColumnByRate();
                    var currentRetail = ($('#item-retail-price').val() || '').toString().trim();
                    if (currentRetail === '' || currentRetail == null) {
                        if (itemBaseRetailPrice != null) {
                            $('#item-retail-price').val(Math.round(itemBaseRetailPrice));
                        } else {
                            $('#item-retail-price').val('');
                        }
                    }
                    // Battery / kisi bhi item mein retail price fill ho to column hamesha dikhao
                    if (itemBaseRetailPrice != null && !isNaN(itemBaseRetailPrice)) {
                        $('#item-retail-price-column').show();
                    }
                    updateRetailAfterCalc();
                }
                
                // Set unit: when editing keep row's unit, else use item's unit
                if (editingRowId !== null) {
                    var editItemForUnit = purchaseItems.find(function(i) { return i.id === editingRowId; });
                    if (editItemForUnit && (editItemForUnit.unit != null && editItemForUnit.unit !== '')) {
                        $('#item-unit').val(editItemForUnit.unit);
                    } else {
                        $('#item-unit').val(response.unit || 'Unit');
                    }
                } else {
                    $('#item-unit').val(response.unit || 'Unit');
                }
                
                // Auto-select warehouse if available (when not editing, or when editing use row's warehouse from loadItemStockStatus)
                if (response.warehouse_id) {
                    $('#selected-warehouse-id').val(response.warehouse_id);
                    var $row = $('.stock-warehouse-item[data-warehouse-id="' + response.warehouse_id + '"]');
                    $row.siblings('.stock-warehouse-item').removeClass('bg-primary text-white').css('background-color', '#e9ecef');
                    $row.removeClass('bg-light').addClass('bg-primary text-white').css('background-color', '');
                    var $check = $row.find('.stock-bar-check');
                    if ($check.length) $check.html('✓'); else $row.find('span:first').html('✓');
                    if ($row.length) $('#item-search-warehouse').text($row.data('display') || '');
                }
                
                // Show item image if available (normalize to same-origin path)
                if (response.image) {
                    $('#item-search-image').attr('src', normalizeItemImageUrl(response.image));
                    $('#item-search-image-preview').removeClass('d-none');
                } else {
                    $('#item-search-image-preview').addClass('d-none');
                }
                
                // Stock will be set after loadItemStockStatus (correct per-warehouse value)
                $('#item-search-stock').html('<span class="text-muted small">...</span>');
                
                // Set warranty: when editing keep row's warranty (already set on row click), else use item's warranty
                if (editingRowId === null) {
                    if (response.warranty_value && response.warranty_unit) {
                        $('#warranty-value').val(response.warranty_value);
                        $('#warranty-unit').val(response.warranty_unit);
                    } else {
                        $('#warranty-value').val('');
                        $('#warranty-unit').val('');
                    }
                }
                
                // Load stock status to show warehouse options
                loadItemStockStatus(itemId);
                
                // Load customer history for this item
                loadCustomerHistory(itemId);
                
                // Edit mode: re-apply row's purchase rate, retail price and percentage after async callbacks so the same values as add are shown
                if (editingRowId !== null) {
                    var editItem = purchaseItems.find(function(i) { return i.id === editingRowId; });
                    if (editItem) {
                        var rateToShow = (editItem.rate != null || editItem.rate === 0) ? (Math.round(parseFloat(editItem.rate)) || 0) : null;
                        var retailToShow = null;
                        if (editItem.retail_price_base != null && !isNaN(parseFloat(editItem.retail_price_base))) {
                            retailToShow = Math.round(parseFloat(editItem.retail_price_base));
                        } else if (editItem.retail_pct != null && editItem.retail_price != null && !isNaN(parseFloat(editItem.retail_pct)) && !isNaN(parseFloat(editItem.retail_price))) {
                            var taxPct = parseFloat(String($('#item-tax-percent').val()).replace(/%/g, '')) || 18;
                            retailToShow = Math.round(parseFloat(editItem.retail_price) / ((1 + parseFloat(editItem.retail_pct) / 100) * (1 + taxPct / 100)));
                        }
                        var pctToShow = (editItem.retail_pct != null && editItem.retail_pct !== '') ? String(editItem.retail_pct) : '';
                        setTimeout(function() {
                            if (editingRowId !== null) {
                                if (rateToShow != null) $('#item-rate').val(rateToShow);
                                // Do not overwrite item-retail-price: it must always show item's linked retail
                                if (pctToShow !== undefined) $('#item-retail-percentage').val(pctToShow);
                                if (typeof updateRetailPctSelectColor === 'function') updateRetailPctSelectColor();
                                if (typeof updateRateColumnByRetailPct === 'function') updateRateColumnByRetailPct();
                                if (typeof updateRetailAfterCalc === 'function') updateRetailAfterCalc();
                                if (typeof updateRetailColumnByRate === 'function') updateRetailColumnByRate();
                                if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
                            }
                        }, 80);
                    }
                }
                
                $('#search-results').hide();
                if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
            }
        });
    }

    // Load purchase history for selected item (from database)
    let lastPurchaseRate = 0;
    function loadCustomerHistory(itemId) {
        $('#customer-history-content').html(`
            <div class="text-center py-2">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                <span class="text-muted small">Loading purchase history...</span>
            </div>
        `);
        $('#hold-rate-link').hide();
        
        $.ajax({
            url: '{{ route("purchases.items.purchase.history", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(data) {
                if (data.total_purchases === 0) {
                    $('#customer-history-content').html(`
                        <div class="text-center py-2">
                            <i class="ti ti-history-off text-muted fs-24 mb-1" style="display: block;"></i>
                            <p class="text-muted mb-0 small">No purchase history for this item</p>
                        </div>
                    `);
                    $('#hold-rate-link').hide();
                    return;
                }
                
                // Store last purchase rate for "Hold Rate" button
                lastPurchaseRate = data.last_purchase ? data.last_purchase.rate : 0;
                
                // Build history HTML
                let html = `
                    <div class="purchase-history-summary mb-2 pb-2" style="border-bottom: 1px solid #e0e0e0;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-dark">Total Purchases: ${data.total_purchases}</span>
                            <span class="badge bg-primary">${data.total_quantity} ${data.last_purchase ? data.last_purchase.unit : 'Units'}</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span><i class="ti ti-trending-down text-success me-1"></i>Min: Rs ${parseFloat(data.min_rate).toLocaleString()}</span>
                            <span><i class="ti ti-chart-line text-primary me-1"></i>Avg: Rs ${parseFloat(data.avg_rate).toLocaleString()}</span>
                            <span><i class="ti ti-trending-up text-danger me-1"></i>Max: Rs ${parseFloat(data.max_rate).toLocaleString()}</span>
                        </div>
                    </div>
                `;
                
                // Show last few purchases
                html += '<div class="purchase-history-list small">';
                data.history.slice(0, 5).forEach(function(purchase) {
                    const daysAgo = purchase.days_ago === 0 ? 'Today' : (purchase.days_ago === 1 ? '1 day ago' : purchase.days_ago + ' days ago');
                    const dateTime = purchase.created_at || (purchase.purchase_date || '');
                    html += `
                        <div class="d-flex justify-content-between align-items-center py-1 purchase-history-item" style="border-bottom: 1px dashed #eee; cursor: pointer;" data-rate="${purchase.rate}">
                            <div>
                                <div class="fw-500">${purchase.supplier_name} <span class="text-muted">(${purchase.quantity} ${purchase.unit})</span></div>
                                <div class="text-muted" style="font-size: 0.75rem;">${dateTime ? dateTime + ' · ' : ''}${daysAgo}</div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-primary">Rs ${parseFloat(purchase.rate).toLocaleString()}</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                if (data.history.length > 5) {
                    html += `<div class="text-center mt-2"><small class="text-muted">+ ${data.history.length - 5} more purchases</small></div>`;
                }
                
                $('#customer-history-content').html(html);
                $('#hold-rate-link').show();
            },
            error: function(xhr) {
                console.error('Error loading purchase history:', xhr);
                $('#customer-history-content').html(`
                    <div class="text-center py-2">
                        <i class="ti ti-alert-circle text-danger fs-24 mb-1" style="display: block;"></i>
                        <p class="text-danger mb-0 small">Error loading history</p>
                    </div>
                `);
            }
        });
    }
    
    // Click on history item to apply that rate
    $(document).on('click', '.purchase-history-item', function() {
        const rate = $(this).data('rate');
        if (rate) {
            $('#item-rate').val(Math.round(parseFloat(rate) || 0));
            updateRetailColumnByRate();
            // Visual feedback
            $(this).addClass('bg-success bg-opacity-10');
            setTimeout(() => $(this).removeClass('bg-success bg-opacity-10'), 500);
        }
    });

    // Hold rate to apply (uses last purchase rate)
    $('#hold-rate-link').on('click', function() {
        if (lastPurchaseRate > 0) {
            $('#item-rate').val(Math.round(parseFloat(lastPurchaseRate) || 0));
            updateRetailColumnByRate();
        }
    });

    // Suggest rate button
    $('#suggest-rate').on('click', function() {
        const itemId = $('#selected-item-id').val();
        if (itemId) {
            loadItemDetails(itemId);
        }
    });

    // Update line total shown next to SALE RATE (qty × rate − discount + tax). Branch header Rs uses sum of all warehouse qty dropdowns.
    function updateItemLineTotal() {
        var quantity = parseFloat($('#item-quantity').val()) || 0;
        if ($('#item-quantity-input').is(':visible') && $('#item-quantity-input').val()) quantity = parseFloat($('#item-quantity-input').val()) || 0;
        quantity = Math.max(0, quantity);
        var rate = parseFloat($('#item-rate').val()) || 0;
        var discount = parseFloat($('#item-discount').val()) || 0;
        var discountType = $('#discount-type').val() || 'amount';
        var taxPct = parseFloat($('#item-tax').val()) || 0;
        var discountAmount = discountType === 'percent' ? (quantity * rate * discount) / 100 : discount;
        var subtotal = (quantity * rate) - discountAmount;
        var taxAmount = (subtotal * taxPct) / 100;
        var total = subtotal + taxAmount;
        $('#item-line-total').text('Rs ' + (Math.round(total) || 0));
        // Branch header total: use sum of all warehouse qty dropdowns
        var branchQty = 0;
        $('#stock-status-list .stock-warehouse-qty-input').each(function() { branchQty += parseFloat($(this).val()) || 0; });
        branchQty = Math.max(0, branchQty);
        var branchDiscountAmount = discountType === 'percent' ? (branchQty * rate * discount) / 100 : discount;
        var branchSubtotal = (branchQty * rate) - branchDiscountAmount;
        var branchTaxAmount = (branchSubtotal * taxPct) / 100;
        var branchTotal = branchSubtotal + branchTaxAmount;
        $('#stock-status-list .stock-branch-total-rs').text('Rs ' + (Math.round(branchTotal) || 0));
    }

    $('#item-rate, #item-discount, #item-tax').on('input change', updateItemLineTotal);
    $('#discount-type').on('change', updateItemLineTotal);
    $('#item-quantity').on('change', updateItemLineTotal);

    // Purchase rate: clear "0" on focus so user can type directly; restore 0 on blur if left empty
    $('#item-rate').on('focus', function() {
        if ($(this).val() === '0' || $(this).val() === 0) $(this).val('');
    }).on('blur', function() {
        if ($(this).val() === '' || $(this).val() == null) $(this).val('0');
    });

    // Update branch header "selected total" (sum of all warehouse qty dropdowns in the list)
    function updateStockBranchSelectedQty() {
        var total = 0;
        var unit = 'Piece';
        var baseUnit = '';
        $('#stock-status-list .stock-warehouse-qty-input').each(function() {
            total += parseFloat($(this).val()) || 0;
            if (!unit && $(this).data('unit')) unit = $(this).data('unit') || 'Piece';
            if (unit === 'Piece' && $(this).data('unit')) unit = $(this).data('unit');
        });
        if ($('#stock-status-list .stock-warehouse-qty-input').length) {
            unit = ($('#stock-status-list .stock-warehouse-qty-input').first().data('unit') || 'Piece').trim();
            var $firstRow = $('#stock-status-list .stock-warehouse-item').first();
            if ($firstRow.length) baseUnit = ($firstRow.data('base-unit') || '').trim();
        }
        var qtyPart = (Number.isInteger(total) ? total : total.toFixed(2));
        var branchQtyHtml = '<span class="d-block fw-bold text-dark">' + qtyPart + '</span><span class="small text-muted d-block" style="font-size: 0.7rem;">' + (unit || 'Unit') + '</span>';
        if (baseUnit) branchQtyHtml += '<span class="small text-muted d-block" style="font-size: 0.65rem;">' + baseUnit + '</span>';
        $('#stock-status-list .stock-branch-selected-qty').html(branchQtyHtml);
        if (typeof updateStockStatusListTotal === 'function') updateStockStatusListTotal();
    }

    // Update total row below warehouse list (sum of available stock from each warehouse row)
    function updateStockStatusListTotal() {
        var total = 0;
        var unit = 'Piece';
        $('#stock-status-list .stock-warehouse-item').each(function() {
            total += parseFloat($(this).data('quantity')) || 0;
            if ($(this).data('unit')) unit = ($(this).data('unit') || 'Piece').trim();
        });
        var $first = $('#stock-status-list .stock-warehouse-item').first();
        if ($first.length) unit = ($first.data('unit') || 'Piece').trim();
        var text = (Number.isInteger(total) ? total : total.toFixed(2)) + ' ' + unit;
        var $totalRow = $('#stock-status-list-total');
        var $totalText = $('#stock-status-list-total-text');
        if ($('#stock-status-list .stock-warehouse-item').length) {
            $totalRow.show();
            $totalText.text(text);
        } else {
            $totalRow.hide();
        }
    }

    // Sync quantity from item-quantity to warehouse inputs (dropdown is 1-1000) and base unit dropdowns
    function syncQuantityToWarehouseInputs() {
        var qty = parseFloat($('#item-quantity').val()) || 0;
        if ($('#item-quantity-input').is(':visible') && $('#item-quantity-input').val()) {
            qty = parseFloat($('#item-quantity-input').val()) || 0;
        }
        qty = Math.max(1, Math.min(1000, Math.round(qty) || 1));
        $('.stock-warehouse-qty-input').val(qty);
        $('.stock-warehouse-item').each(function() {
            var $row = $(this);
            var mult = parseFloat($row.attr('data-base-unit-multiplier')) || 0;
            if (mult > 0) {
                var baseVal = qty * mult;
                var $base = $row.find('.stock-warehouse-base-qty-input');
                if ($base.length && $base.find('option[value="' + baseVal + '"]').length) $base.val(baseVal);
                else if ($base.length && $base.find('option[value="' + baseVal.toFixed(2) + '"]').length) $base.val(baseVal.toFixed(2));
            }
        });
    }

    // Quantity dropdown change - sync to warehouse inputs
    $('#item-quantity').on('change', function() {
        $('#item-quantity-input').hide();
        syncQuantityToWarehouseInputs();
    });

    // Use custom quantity input if provided - sync to warehouse inputs
    $('#item-quantity-input').on('input', function() {
        const customQty = $(this).val();
        if (customQty && customQty > 0) {
            $('#item-quantity').val(customQty);
        }
        syncQuantityToWarehouseInputs();
    });

    // Apply blue/gray from #selected-warehouse-ids so selected rows stay stable.
    function applyStockWarehouseSelectionFromIds() {
        var selectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
        $('#stock-status-list .stock-warehouse-item').each(function() {
            var $r = $(this);
            var rid = $r.data('warehouse-id') + '';
            var sel = selectedIds.indexOf(rid) !== -1;
            $r.removeClass('bg-primary text-white').css('background-color', '');
            if (sel) $r.addClass('bg-primary text-white'); else $r.css('background-color', '#f0f0f0');
            $r.find('span:first').html(sel ? '✓' : '');
            var $nameSpan = $r.find('.d-flex.align-items-center span:eq(1)');
            if ($nameSpan.length) { $nameSpan.toggleClass('text-white', sel).toggleClass('text-dark', !sel); }
            $r.find('.text-end').first().find('span').each(function() {
                if (sel) { $(this).css('color', 'rgba(255,255,255,0.95)').addClass('text-white'); }
                else { $(this).css('color', '#6c757d').removeClass('text-white'); }
            });
        });
    }

    // Highlight one warehouse row as selected (primary color + checkmark); clear selection from others.
    function setStockWarehouseRowSelected($row) {
        if (!$row || !$row.length) return;
        var whId = $row.data('warehouse-id') + '';
        $('#selected-warehouse-ids').val(whId);
        $('#selected-warehouse-id').val(whId);
        var $whSel = $('#item-save-warehouse');
        if ($whSel.find('option[value="' + whId + '"]').length) $whSel.val(whId);
        applyStockWarehouseSelectionFromIds();
    }

    // When stock warehouse qty changes: sync that value to #item-quantity; sync base unit dropdown; keep row in place (Display upar, baqi apni jaga).
    $(document).on('change', '.stock-warehouse-qty-input', function() {
        var $this = $(this);
        var whId = $this.data('warehouse-id');
        var val = ($this.val() || '').toString().trim();
        // Do not clear other warehouse qty inputs when one is changed — user can have 13 in one and 11 in another
        var $row = $this.closest('.stock-warehouse-item');
        var mult = parseFloat($row.attr('data-base-unit-multiplier')) || 0;
        if (mult > 0) {
            var mainQty = parseFloat($this.val()) || 0;
            var baseVal = mainQty * mult;
            var $baseSelect = $row.find('.stock-warehouse-base-qty-input');
            if ($baseSelect.length) {
                var baseStr = (Number.isInteger(baseVal) ? baseVal : baseVal.toFixed(2)).toString();
                if ($baseSelect.find('option[value="' + baseStr + '"]').length) $baseSelect.val(baseStr);
                else $baseSelect.val('');
            }
        }
        if (val && $row.length) {
            var wid = (whId || '').toString();
            var selectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
            if (wid && selectedIds.indexOf(wid) === -1) selectedIds.push(wid);
            $('#selected-warehouse-ids').val(selectedIds.join(','));
            $('#selected-warehouse-id').val(wid);
            var $whSel = $('#item-save-warehouse');
            if ($whSel.find('option[value="' + wid + '"]').length) $whSel.val(wid);
            applyStockWarehouseSelectionFromIds();
        }
        var saveWhId = ($('#item-save-warehouse').val() || '').toString();
        if (whId && (whId + '') === saveWhId) {
            var q = parseFloat($this.val()) || 1;
            q = Math.max(1, Math.min(1000, Math.round(q)));
            $('#item-quantity').val(q);
            $('#item-quantity-input').val(q).hide();
        }
        if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
        if (typeof updateStockBranchSelectedQty === 'function') updateStockBranchSelectedQty();
    });

    // When base unit qty dropdown changes: set main unit dropdown (qty = baseVal / multiplier) and sync
    $(document).on('change', '.stock-warehouse-base-qty-input', function() {
        var $this = $(this);
        var $row = $this.closest('.stock-warehouse-item');
        var mult = parseFloat($this.attr('data-multiplier')) || parseFloat($row.attr('data-base-unit-multiplier')) || 0;
        if (mult <= 0) return;
        var baseVal = parseFloat($this.val()) || 0;
        var mainQty = Math.round(baseVal / mult);
        mainQty = Math.max(0, Math.min(1000, mainQty));
        var $mainSelect = $row.find('.stock-warehouse-qty-input');
        if ($mainSelect.length) {
            if (mainQty === 0) $mainSelect.val(''); else $mainSelect.val(mainQty);
            $mainSelect.trigger('change');
        }
    });

    // When "Save to warehouse" changes: set #item-quantity from that warehouse's qty input
    $('#item-save-warehouse').on('change', function() {
        var whId = ($(this).val() || '').toString();
        if (!whId) return;
        var $qtyInput = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + whId + '"]');
        if ($qtyInput.length) {
            var q = parseFloat($qtyInput.val()) || 1;
            q = Math.max(1, Math.min(1000, Math.round(q)));
            $('#item-quantity').val(q);
            $('#item-quantity-input').val(q).hide();
        }
        if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
        if (typeof updateStockBranchSelectedQty === 'function') updateStockBranchSelectedQty();
    });

    // Shared: add current modal item to list; if closeAfterAdd then close modal, else reset form for next item. When editingRowId is set, update existing row.
    // When multiple warehouses have quantity selected, adds one cart line per warehouse.
    function submitItemEntry(closeAfterAdd) {
        const itemId = $('#selected-item-id').val();
        const unit = $('#item-unit').val();
        const rateInput = parseFloat($('#item-rate').val()) || 0;
        const pctVal = ($('#item-retail-percentage').val() || '').toString().trim();
        const hasRate = rateInput > 0;
        const hasPct = pctVal !== '';
        if (!itemId) {
            alert('Please select an item');
            return;
        }
        if (!hasRate && !hasPct) {
            alert('Please enter Purchase rate OR select a retail % (— to skip). At least one is required.');
            return;
        }
        var rate, retailPrice, retailPriceBase = null;
        var selectedRetailPct = null;
        if (hasPct) {
            var calcText = ($('#item-retail-after-calc').text() || '').replace(/\s+/g, ' ').trim();
            var parsed = parseFloat(calcText.replace(/^Rs\s*/, '').replace(/,/g, '')) || 0;
            if (parsed <= 0 || isNaN(parsed)) {
                alert('Enter Retail price (Rs.) first, then select a % to use the calculated amount.');
                return;
            }
            retailPriceBase = parseFloat($('#item-retail-price').val()) || null;
            rate = parsed;
            retailPrice = parsed;
            selectedRetailPct = parseFloat(pctVal);
        } else {
            rate = rateInput;
            var retailPriceRaw = $('#item-retail-price').val();
            retailPrice = (retailPriceRaw !== '' && retailPriceRaw != null && !isNaN(parseFloat(retailPriceRaw))) ? parseFloat(retailPriceRaw) : null;
        }
        const discount = parseFloat($('#item-discount').val()) || 0;
        const discountType = $('#discount-type').val();
        const taxPercentage = parseFloat($('#item-tax').val()) || 0;
        const rawItemName = $('#item-search').val();
        const itemName = cleanItemName(rawItemName, itemId);
        const warrantyValue = $('#warranty-value').val();
        const warrantyUnit = $('#warranty-unit').val();
        const $whSel = $('#item-save-warehouse');

        // Collect all warehouses that have a quantity selected (dropdown not empty)
        var warehouseLines = [];
        $('#stock-status-list .stock-warehouse-qty-input').each(function() {
            var whId = ($(this).data('warehouse-id') || '').toString();
            var qty = parseFloat($(this).val()) || 0;
            if (!whId || qty <= 0) return;
            var $row = $(this).closest('.stock-warehouse-item');
            var whName = ($row.data('display') || '').replace(/&quot;/g, '"');
            if (!whName && $whSel.find('option[value="' + whId + '"]').length) whName = $whSel.find('option[value="' + whId + '"]').text().trim();
            var baseQty = null, baseUnit = ($row.data('base-unit') || '').trim();
            var $baseInput = $row.find('.stock-warehouse-base-qty-input');
            if ($baseInput.length && $baseInput.val() !== '' && $baseInput.val() != null) baseQty = parseFloat($baseInput.val());
            warehouseLines.push({ warehouse_id: whId, warehouse_name: whName, quantity: qty, quantity_base: baseQty, base_unit: baseUnit });
        });

        // Agar warehouse list dikh rahi hai lekin kisi bhi warehouse se quantity select nahi hai to warn karo
        if (warehouseLines.length === 0 && currentEntryType !== 'scrap') {
            var hasWarehouseRows = $('#stock-status-list .stock-warehouse-item').length > 0;
            if (hasWarehouseRows) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({ icon: 'warning', title: 'Warehouse quantity required', text: 'Please select quantity from at least one warehouse (use the quantity dropdown next to warehouse).' });
                } else if (typeof toastr !== 'undefined') {
                    toastr.warning('Please select quantity from at least one warehouse.');
                } else {
                    alert('Please select quantity from at least one warehouse (use the quantity dropdown next to warehouse).');
                }
                return;
            }
        }

        // Fallback: single warehouse from Save to warehouse / selected-warehouse-id
        if (warehouseLines.length === 0 && currentEntryType !== 'scrap') {
            var warehouseId = ($whSel.val() || '').toString() || ($('#selected-warehouse-id').val() || '').toString();
            var quantity = parseFloat($('#item-quantity').val()) || 0;
            if ($('#item-quantity-input').is(':visible') && $('#item-quantity-input').val()) quantity = parseFloat($('#item-quantity-input').val()) || 0;
            if (!warehouseId || quantity <= 0) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({ icon: 'warning', title: 'Warehouse & quantity required', text: 'Please select at least one warehouse and enter quantity (use the quantity dropdown per warehouse).' });
                } else if (typeof toastr !== 'undefined') {
                    toastr.warning('Please select at least one warehouse and enter quantity.');
                } else {
                    alert('Please select at least one warehouse and enter quantity (use the quantity dropdown per warehouse).');
                }
                return;
            }
            var warehouseName = $whSel.find('option:selected').text().trim() || ($('.stock-warehouse-item[data-warehouse-id="' + warehouseId + '"]').first().data('display') || '').replace(/&quot;/g, '"');
            var baseQty = null, baseUnit = '';
            var $row = $('.stock-warehouse-item[data-warehouse-id="' + warehouseId + '"]').first();
            if ($row.length) {
                baseUnit = ($row.data('base-unit') || '').trim();
                var $baseInput = $row.find('.stock-warehouse-base-qty-input');
                if ($baseInput.length && $baseInput.val() !== '' && $baseInput.val() != null) baseQty = parseFloat($baseInput.val());
            }
            warehouseLines = [{ warehouse_id: warehouseId, warehouse_name: warehouseName, quantity: quantity, quantity_base: baseQty, base_unit: baseUnit }];
        }
        if (warehouseLines.length === 0 && currentEntryType === 'scrap') {
            var qty = parseFloat($('#item-quantity').val()) || 0;
            if (qty <= 0) { alert('Please enter quantity'); return; }
            warehouseLines = [{ warehouse_id: null, warehouse_name: null, quantity: qty }];
        }

        if (editingRowId !== null) {
            // Edit mode: apply new quantity (and rate, retail) from modal – confirm karte hi cart/inventory view mein nayi quantity dikhe
            var editedRow = purchaseItems.find(function(i) { return i.id === editingRowId; });
            if (!editedRow) { editingRowId = null; return; }
            var sameItemId = String(editedRow.item_id);
            // Preserve PO line link so backend can update received quantity when saving the bill
            var poItemId = (editedRow.purchase_order_item_id != null && editedRow.purchase_order_item_id !== '') ? editedRow.purchase_order_item_id : null;
            // Remove all rows of this item; add new rows from modal (warehouseLines) so quantity fooran update ho
            purchaseItems = purchaseItems.filter(function(it) { return String(it.item_id) !== sameItemId; });
            warehouseLines.forEach(function(wl, wlIndex) {
                var quantity = wl.quantity;
                var discountAmount = discountType === 'percent' ? (quantity * rate * discount) / 100 : discount;
                var subtotal = (quantity * rate) - discountAmount;
                var taxAmount = (subtotal * taxPercentage) / 100;
                var total = subtotal + taxAmount;
                if (['scrap', 'claim_send', 'damage'].indexOf(currentEntryType) >= 0) total = -Math.abs(total);
                var newItem = {
                    id: itemCounter++,
                    item_id: sameItemId,
                    name: itemName,
                    warehouse_id: wl.warehouse_id || null,
                    warehouse_name: wl.warehouse_name || null,
                    quantity: quantity,
                    quantity_base: wl.quantity_base != null ? wl.quantity_base : null,
                    base_unit: (wl.base_unit || '').trim() || null,
                    unit: unit,
                    rate: rate,
                    retail_price: retailPrice,
                    retail_price_base: retailPriceBase,
                    retail_pct: selectedRetailPct,
                    discount: discountAmount,
                    tax_percentage: taxPercentage,
                    tax_amount: taxAmount,
                    total: total,
                    warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : (editedRow.warranty || null),
                    entry_type: currentEntryType || editedRow.entry_type || 'purchase'
                };
                // Keep purchase_order_item_id on first line when editing a PO-loaded row (so received qty posts correctly)
                if (poItemId != null && wlIndex === 0) {
                    newItem.purchase_order_item_id = poItemId;
                }
                purchaseItems.push(newItem);
            });
            sortPurchaseItemsByEntryType();
            $('#items-tbody').empty();
            purchaseItems.forEach(function(item) { addItemToTable(item); });
            updatePurchaseTableRetailColumnVisibility();
            editingRowId = null;
            pendingEditItem = null;
            calculateTotals();
            syncCartToServer();
            if (typeof updatePurchaseBulkRetailBar === 'function') updatePurchaseBulkRetailBar();
            resetItemModal();
            $('#add-item-modal').modal('hide');
            return;
        }

        // Add one cart line per warehouse with selected quantity
        warehouseLines.forEach(function(wl) {
            var quantity = wl.quantity;
            var discountAmount = discountType === 'percent' ? (quantity * rate * discount) / 100 : discount;
            var subtotal = (quantity * rate) - discountAmount;
            var taxAmount = (subtotal * taxPercentage) / 100;
            var total = subtotal + taxAmount;
            if (['scrap', 'claim_send', 'damage'].indexOf(currentEntryType) >= 0) total = -Math.abs(total);
            var item = {
                id: itemCounter++,
                item_id: itemId,
                name: itemName,
                warehouse_id: wl.warehouse_id || null,
                warehouse_name: wl.warehouse_name || null,
                quantity: quantity,
                quantity_base: wl.quantity_base != null ? wl.quantity_base : null,
                base_unit: (wl.base_unit || '').trim() || null,
                unit: unit,
                rate: rate,
                retail_price: retailPrice,
                retail_price_base: retailPriceBase,
                retail_pct: selectedRetailPct,
                discount: discountAmount,
                tax_percentage: taxPercentage,
                tax_amount: taxAmount,
                total: total,
                warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : null,
                entry_type: currentEntryType || 'purchase'
            };
            purchaseItems.push(item);
        });

        sortPurchaseItemsByEntryType();
        $('#items-tbody').empty();
        purchaseItems.forEach(function(item) { addItemToTable(item); });
        updatePurchaseTableRetailColumnVisibility();
        calculateTotals();
        syncCartToServer();
        if (typeof updatePurchaseBulkRetailBar === 'function') updatePurchaseBulkRetailBar();
        if (purchaseItems.length > 0) {
            $('#payment-amount-row').show();
        }
        if (closeAfterAdd) {
            resetItemModal();
            $('#add-item-modal').modal('hide');
        } else {
            resetItemModal();
            setTimeout(function() { $('#item-search').focus(); }, 100);
        }
    }

    $('#confirm-entry').on('click', function() {
        submitItemEntry(true);
    });

    $('#save-and-new-entry').on('click', function() {
        submitItemEntry(false);
    });

    // Order: purchase first, then scrap, return, claim_send, damage
    function sortPurchaseItemsByEntryType() {
        purchaseItems.sort(function(a, b) {
            const order = { 'purchase': 0, 'claim': 1, 'scrap': 2, 'return': 3, 'claim_send': 4, 'damage': 5 };
            const aOrd = order[a.entry_type || 'purchase'] ?? 0;
            const bOrd = order[b.entry_type || 'purchase'] ?? 0;
            if (aOrd !== bOrd) return aOrd - bOrd;
            // Same entry type: sort by warehouse name (sequence-wise)
            const aWh = (a.warehouse_name || '').toString().toLowerCase();
            const bWh = (b.warehouse_name || '').toString().toLowerCase();
            return aWh.localeCompare(bWh);
        });
    }

    function addItemToTable(item) {
        $('#empty-items-state').hide();
        $('#items-list').show();
        
        // RETURN / SCRAP / CLAIM_SEND / DAMAGE: show total as minus with red styling
        const isReturn = ['return', 'scrap', 'claim_send', 'damage'].indexOf(item.entry_type) >= 0;
        const totalVal = parseFloat(item.total);
        const displayVal = isReturn ? -Math.abs(totalVal) : totalVal;
        const totalDisplay = 'Rs ' + Math.round(displayVal);
        const totalClass = displayVal < 0 ? ' text-danger fw-bold' : '';
        
        // Show battery-type sequence (or item name) in same order as set during purchase/sale; strip HTML so no empty <P><BR></P>
        var rawName = (item.name || '').trim();
        var plainName = (typeof stripHtml === 'function' ? stripHtml(rawName) : String(rawName).replace(/<[^>]*>/g, '')).trim();
        if (!plainName) plainName = 'Item #' + (item.item_id || '');
        const itemName = plainName.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        // Always use battery-type-sequence highlight for item name (same style everywhere: dark blue, bold)
        const nameRowClass = 'battery-type-sequence fw-bold purchase-row-item-name';
        const warehouseDisplay = (item.warehouse_name || '—').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const entryType = item.entry_type || 'purchase';
        let entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 mb-1 text-white fw-semibold text-center" style="background-color: #0d6efd; font-size: 0.75rem; min-width: 4em;">PURCHASE</span>';
        if (entryType === 'return') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 mb-1 text-dark fw-semibold text-center" style="background-color: #ffc107; font-size: 0.75rem; min-width: 4em;">RETURN</span>';
        else if (entryType === 'scrap') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 mb-1 text-dark fw-semibold bg-secondary" style="font-size: 0.75rem;">SCRAP</span>';
        else if (entryType === 'claim') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 mb-1 text-white fw-semibold text-center" style="background-color: #198754; font-size: 0.75rem;">CLAIM</span>';
        else if (entryType === 'claim_send') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 mb-1 text-white fw-semibold text-center" style="background-color: #6f42c1; font-size: 0.75rem;">SEND CLAIM</span>';
        else if (entryType === 'damage') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 mb-1 text-white fw-semibold text-center" style="background-color: #dc3545; font-size: 0.75rem;">DAMAGE</span>';
        const itemImageUrl = (item.image && typeof item.image === 'string') ? normalizeItemImageUrl(item.image) : '';
        const photoHtml = itemImageUrl
            ? `<img src="${itemImageUrl.replace(/"/g, '&quot;')}" alt="" class="rounded me-2 flex-shrink-0 purchase-row-item-thumb" style="width: 42px; height: 42px; object-fit: cover; cursor: pointer;" data-full-src="${itemImageUrl.replace(/"/g, '&quot;')}" title="Click to view full image" onerror="this.src='{{ asset('assets/img/icons/image.svg') }}'">`
            : `<span class="rounded me-2 d-inline-flex align-items-center justify-content-center bg-light flex-shrink-0 text-muted" style="width: 42px; height: 42px;"><i class="ti ti-photo" style="font-size: 1.2rem;"></i></span>`;
        const warehouseHtml = `<div class="d-flex align-items-center">${photoHtml}<div class="d-flex flex-column"><div>${entryTypeBadge}</div><div class="text-muted small mt-1" style="font-size: 0.8rem;">${warehouseDisplay}</div></div></div>`;
        const hasBaseQty = item.quantity_base != null && item.base_unit;
        const qtyDisplay = hasBaseQty ? (item.quantity + ' · ' + (Number.isInteger(item.quantity_base) ? item.quantity_base : parseFloat(item.quantity_base).toFixed(2))) : item.quantity;
        const unitDisplay = hasBaseQty ? ((item.unit || 'Can') + ' · ' + item.base_unit) : (item.unit || 'Unit');
        const qtyUnitRate = hasBaseQty ? (item.quantity + ' ' + (item.unit || 'Can') + ' · ' + item.quantity_base + ' ' + item.base_unit + ' · Rs ' + Math.round(parseFloat(item.rate))) : (item.quantity + ' ' + (item.unit || 'Unit') + ' · Rs ' + Math.round(parseFloat(item.rate)));
        const retailPctNum = (item.retail_pct != null && !isNaN(parseFloat(item.retail_pct))) ? parseFloat(item.retail_pct) : null;
        const retailPctDisplay = retailPctNum != null ? ((retailPctNum >= 0 ? '+' : '') + retailPctNum + '%') : '';
        const retailPctColorClass = retailPctNum != null ? (retailPctNum >= 0 ? 'text-primary' : 'text-danger') : '';
        const itemIdNum = (item.item_id != null && item.item_id !== '') ? parseInt(item.item_id, 10) : 0;
        const itemColorClass = 'items-row-wh-' + (isNaN(itemIdNum) ? 0 : (Math.abs(itemIdNum) % 6));
        const retailDisplay = (item.retail_price != null && item.retail_price !== '' && !isNaN(parseFloat(item.retail_price))) ? ('Rs ' + Math.round(parseFloat(item.retail_price))) : '—';
        const qtyNum = parseFloat(item.quantity) || 0;
        const rateNum = parseFloat(item.rate) || 0;
        const totalNum = parseFloat(item.total) || 0;
        const unitLabel = (item.unit || 'Unit').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const rateDisplay = 'Rs ' + (rateNum % 1 === 0 ? rateNum : parseFloat(rateNum).toFixed(2)).toLocaleString('en-PK', { minimumFractionDigits: 2 });
        const totalDisplayFormatted = (displayVal % 1 === 0 ? displayVal : parseFloat(displayVal).toFixed(2)).toLocaleString('en-PK', { minimumFractionDigits: 2 });
        const secondLine = (Number.isInteger(qtyNum) ? qtyNum : qtyNum.toFixed(2)) + ' ' + unitLabel + ' • RS ' + (rateNum % 1 === 0 ? Math.round(rateNum) : parseFloat(rateNum).toFixed(2));
        const poItemIdAttr = (item.purchase_order_item_id != null && item.purchase_order_item_id !== '' && parseInt(item.purchase_order_item_id, 10) > 0) ? (' data-purchase-order-item-id="' + parseInt(item.purchase_order_item_id, 10) + '"') : '';
        const row = `
            <tr class="purchase-item-row pehla-items-row ${itemColorClass}" data-item-id="${item.item_id}" data-row-id="${item.id}" data-entry-type="${item.entry_type || 'purchase'}" data-warehouse-id="${item.warehouse_id || ''}"${poItemIdAttr}>
                <td class="align-middle pehla-td-warehouse">${warehouseHtml}</td>
                <td class="align-middle pehla-td-item">
                    <div class="${nameRowClass}">${itemName}</div>
                    <div class="small text-muted purchase-row-qty-unit-line">${secondLine}</div>
                </td>
                <td class="align-middle text-end pehla-td-total purchase-row-total-display${totalClass}">RS ${totalDisplayFormatted}</td>
                <td class="align-middle pehla-td-actions purchase-row-print-qty-cell purchase-row-print-cell purchase-row-action-cell">
                    <input type="checkbox" class="purchase-row-verified-cb form-check-input d-inline-block me-1" ${(item.verified || item.verified === 1) ? 'checked' : ''} title="Display on bill" aria-label="Display">
                    <button type="button" class="btn btn-sm btn-outline-secondary border-0 p-1 me-1 purchase-row-print-btn" data-row-id="${item.id}" title="Print barcode / labels"><i class="ti ti-barcode small"></i></button>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item" data-row-id="${item.id}" title="Remove"><i class="ti ti-x"></i></button>
                </td>
            </tr>
        `;
        $('#items-tbody').append(row);
        updatePurchaseTableRetailColumnVisibility();
    }

    // Cart table: show Retail column only when at least one item has retail (retail_pct or retail_price)
    function updatePurchaseTableRetailColumnVisibility() {
        var hasAnyRetail = purchaseItems.some(function(it) {
            var pct = it.retail_pct != null && it.retail_pct !== '' && String(it.retail_pct).trim() !== '';
            var price = it.retail_price != null && it.retail_price !== '' && !isNaN(parseFloat(it.retail_price));
            return pct || price;
        });
        if (hasAnyRetail) {
            $('.purchase-table-retail-col').show();
        } else {
            $('.purchase-table-retail-col').hide();
        }
    }

// Remove item
    $(document).on('click', '.remove-item', function(e) {
        e.stopPropagation();
        const rowId = $(this).data('row-id');
        purchaseItems = purchaseItems.filter(item => item.id !== rowId);
        $(this).closest('.purchase-item-row').remove();

        if ($('#items-tbody .purchase-item-row').length === 0) {
            $('#empty-items-state').show();
            $('#items-list').hide();
            $('#payment-amount-row').hide();
        }

        calculateTotals();
        syncCartToServer();
        updatePurchaseBulkRetailBar();
        updatePurchaseTableRetailColumnVisibility();
    });

    // Inline edit qty/rate in table: update item and row display
    function updatePurchaseBarRowFromInputs($row) {
        var rowId = $row.data('row-id');
        var item = purchaseItems.find(function(i) { return i.id === rowId; });
        if (!item) return;
        var qty = parseFloat($row.find('.purchase-row-qty-input').val()) || 0;
        var rate = parseFloat($row.find('.purchase-row-rate-input').val()) || 0;
        item.quantity = qty;
        item.rate = rate;
        var discountAmount = parseFloat(item.discount) || 0;
        var taxPct = parseFloat(item.tax_percentage) || 0;
        var subtotal = (qty * rate) - discountAmount;
        var taxAmount = (subtotal * taxPct) / 100;
        var total = subtotal + taxAmount;
        if (['scrap', 'claim_send', 'damage'].indexOf(item.entry_type || '') >= 0) total = -Math.abs(total);
        item.total = total;
        var totalFormatted = (total % 1 === 0 ? total : parseFloat(total).toFixed(2)).toLocaleString('en-PK', { minimumFractionDigits: 2 });
        $row.find('.purchase-row-total-display').text('Rs ' + totalFormatted).toggleClass('text-danger fw-bold', total < 0);
        var unitLabel = (item.unit || 'Unit').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var qtyLabel = (Number.isInteger(qty) ? qty : qty.toFixed(2)) + ' ' + unitLabel;
        $row.find('.purchase-row-qty-unit-line').text(qtyLabel);
        calculateTotals();
        syncCartToServer();
    }
    $(document).on('input change', '#items-tbody .purchase-row-qty-input, #items-tbody .purchase-row-rate-input', function() {
        updatePurchaseBarRowFromInputs($(this).closest('.purchase-item-row'));
    });

    function updatePurchaseBulkRetailBar() {}

    // Print button par click → seedha label print modal kholo
    $(document).on('click', '#items-tbody .purchase-row-print-qty-cell button.purchase-row-print-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var rowId = $(this).data('row-id');
        var item = purchaseItems.find(function(i) { return i.id === rowId; });
        if (!item) return;
        var qty = Math.max(1, Math.min(500, Math.round(parseFloat(item.quantity)) || 1));
        var name = (item.name || 'Item #' + item.item_id).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        var salePrice = (item.retail_price != null && item.retail_price !== '' && !isNaN(parseFloat(item.retail_price))) ? parseFloat(item.retail_price) : (parseFloat(item.rate) || 0);
        var priceText = 'Rs ' + Math.round(salePrice);
        var labelsHtml = '';
        for (var i = 0; i < qty; i++) {
            labelsHtml += '<div class="label-print-item"><div class="label-print-name">' + name + '</div><div class="label-print-rate">' + priceText + '</div></div>';
        }
        $('#label-print-count').text(qty + ' label' + (qty !== 1 ? 's' : ''));
        var $content = $('#label-print-modal-content');
        $content.data('single-name', name).data('single-rate', priceText).html('<div class="label-print-sheet">' + labelsHtml + '</div>');
        $content.toggleClass('label-print-hide-price', !$('#label-print-show-price').is(':checked'));
        $('#label-print-qty-wrap').removeClass('d-none').addClass('d-flex');
        $('#label-print-qty-input').val(qty).attr('min', 1).attr('max', 500);
        var labelModalEl = document.getElementById('label-print-view-modal');
        if (labelModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(labelModalEl).show();
        } else {
            $('#label-print-view-modal').modal('show');
        }
    });

    // Per-row 2×1 label print: is row ki quantity ke mutabiq thermal sticker print (cell ya button press par)
    // View hamesha modal mein dikhata hain taake popup block hone par bhi view show ho
    $(document).on('click', '#items-tbody .purchase-row-print-cell', function(e) {
        e.stopPropagation();
        var rowId = $(this).closest('.purchase-item-row').data('row-id');
        var item = purchaseItems.find(function(i) { return i.id === rowId; });
        if (!item) return;
        var qty = Math.max(1, Math.min(500, Math.round(parseFloat(item.quantity)) || 1));
        var name = (item.name || 'Item #' + item.item_id).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        var salePrice = (item.retail_price != null && item.retail_price !== '' && !isNaN(parseFloat(item.retail_price))) ? parseFloat(item.retail_price) : (parseFloat(item.rate) || 0);
        var priceText = 'Rs ' + Math.round(salePrice);
        var labelsHtml = '';
        for (var i = 0; i < qty; i++) {
            labelsHtml += '<div class="label-print-item"><div class="label-print-name">' + name + '</div><div class="label-print-rate">' + priceText + '</div></div>';
        }
        $('#label-print-count').text(qty + ' label' + (qty !== 1 ? 's' : ''));
        var $content = $('#label-print-modal-content');
        $content.data('single-name', name).data('single-rate', priceText).html('<div class="label-print-sheet">' + labelsHtml + '</div>');
        $content.toggleClass('label-print-hide-price', !$('#label-print-show-price').is(':checked'));
        $('#label-print-qty-wrap').removeClass('d-none').addClass('d-flex');
        $('#label-print-qty-input').val(qty).attr('min', 1).attr('max', 500);
        var labelModalEl = document.getElementById('label-print-view-modal');
        if (labelModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(labelModalEl).show();
        } else {
            $('#label-print-view-modal').modal('show');
        }
    });
    $('#label-print-modal-print-btn').on('click', function() {
        window.print();
    });
    // Switch: Show price ON = price dikhe, OFF = price chhup
    $('#label-print-show-price').on('change', function() {
        var $content = $('#label-print-modal-content');
        if ($(this).is(':checked')) {
            $content.removeClass('label-print-hide-price');
        } else {
            $content.addClass('label-print-hide-price');
        }
    });
    // Modal mein quantity change: labels dobara banao (sirf single-row print ke liye)
    $(document).on('change input', '#label-print-qty-input', function() {
        var qty = parseInt($(this).val(), 10);
        if (isNaN(qty) || qty < 1) qty = 1;
        if (qty > 500) qty = 500;
        $(this).val(qty);
        var name = $('#label-print-modal-content').data('single-name');
        var rate = $('#label-print-modal-content').data('single-rate');
        if (!name || !rate) return;
        var labelsHtml = '';
        for (var i = 0; i < qty; i++) {
            labelsHtml += '<div class="label-print-item"><div class="label-print-name">' + name + '</div><div class="label-print-rate">' + rate + '</div></div>';
        }
        $('#label-print-count').text(qty + ' label' + (qty !== 1 ? 's' : ''));
        $('#label-print-modal-content').html('<div class="label-print-sheet">' + labelsHtml + '</div>');
        $('#label-print-modal-content').toggleClass('label-print-hide-price', !$('#label-print-show-price').is(':checked'));
    });

    // Print All: sab rows ke labels ek saath ek hi print view mein, ek baar print
    $(document).on('click', '.purchase-print-all-labels', function(e) {
        e.preventDefault();
        if (!purchaseItems || purchaseItems.length === 0) {
            toastr.warning('Pehle cart mein items add karein.');
            return;
        }
        var labelsHtml = '';
        var totalLabels = 0;
        $('#items-tbody .purchase-item-row').each(function() {
            var rowId = $(this).data('row-id');
            var item = purchaseItems.find(function(i) { return i.id === rowId; });
            if (!item) return;
            var qty = Math.max(1, Math.min(500, Math.round(parseFloat(item.quantity)) || 1));
            var name = (item.name || 'Item #' + item.item_id).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            var salePrice = (item.retail_price != null && item.retail_price !== '' && !isNaN(parseFloat(item.retail_price))) ? parseFloat(item.retail_price) : (parseFloat(item.rate) || 0);
            var priceText = 'Rs ' + Math.round(salePrice);
            for (var i = 0; i < qty; i++) {
                labelsHtml += '<div class="label-print-item"><div class="label-print-name">' + name + '</div><div class="label-print-rate">' + priceText + '</div></div>';
                totalLabels++;
            }
        });
        if (totalLabels === 0) {
            toastr.warning('Koi label nahi bani. Quantity check karein.');
            return;
        }
        $('#label-print-count').text(totalLabels + ' label' + (totalLabels !== 1 ? 's' : '') + ' (sab items)');
        $('#label-print-modal-content').removeData('single-name').removeData('single-rate').html('<div class="label-print-sheet">' + labelsHtml + '</div>');
        $('#label-print-modal-content').toggleClass('label-print-hide-price', !$('#label-print-show-price').is(':checked'));
        $('#label-print-qty-wrap').addClass('d-none').removeClass('d-flex');
        var labelModalEl = document.getElementById('label-print-view-modal');
        if (labelModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(labelModalEl).show();
        } else {
            $('#label-print-view-modal').modal('show');
        }
    });

    // Click row to edit (open add-item modal) — Print/Qty cell par click se modal NA kholen
    $(document).on('click', '#items-tbody .purchase-item-row', function(e) {
        if ($(e.target).closest('.remove-item').length) return;
        if ($(e.target).closest('.purchase-row-qty-input').length) return;
        if ($(e.target).closest('.purchase-row-rate-input').length) return;
        if ($(e.target).closest('.purchase-row-print-cell').length) return;
        if ($(e.target).closest('.purchase-row-print-qty-cell').length) return;
        if ($(e.target).closest('.purchase-row-action-cell').length) return;
        if ($(e.target).closest('.dropdown').length) return;
        var rowId = $(this).data('row-id');
        var item = purchaseItems.find(function(i) { return i.id === rowId; });
        if (!item) return;
        editingRowId = rowId;
        var quantitiesByWarehouse = {};
        purchaseItems.forEach(function(i) {
            if (String(i.item_id) === String(item.item_id) && (i.warehouse_id != null && i.warehouse_id !== '')) {
                var whId = String(i.warehouse_id);
                var q = parseFloat(i.quantity);
                if (!isNaN(q) && q > 0) quantitiesByWarehouse[whId] = Math.max(1, Math.min(1000, Math.round(q)));
            }
        });
        pendingEditItem = { warehouse_id: item.warehouse_id, warehouse_name: item.warehouse_name, quantity: item.quantity, rate: item.rate, retail_price: item.retail_price, retail_price_base: item.retail_price_base, retail_pct: item.retail_pct, unit: item.unit, discount: item.discount, tax_percentage: item.tax_percentage, warranty: item.warranty, quantities_by_warehouse: quantitiesByWarehouse };
        $('#selected-item-id').val(item.item_id);
        var editName = (item.name || '').toString();
        var editPlainName = (typeof stripHtml === 'function' ? stripHtml(editName) : editName.replace(/<[^>]*>/g, '')).trim();
        $('#item-search').val(editPlainName || editName || ('Item #' + (item.item_id || '')));
        $('#item-rate').val(item.rate != null ? item.rate : '0');
        var editRetail = null;
        var pctForEdit = item.retail_pct;
        if (item.retail_price_base != null && !isNaN(parseFloat(item.retail_price_base))) {
            editRetail = Math.round(parseFloat(item.retail_price_base));
        } else {
            if ((pctForEdit == null || pctForEdit === '') && item.retail_price != null) {
                var rowText = $(this).text();
                var pctMatch = rowText.match(/([+-]?\d+)%/);
                if (pctMatch) {
                    pctForEdit = parseInt(pctMatch[1], 10);
                    item.retail_pct = pctForEdit;
                }
            }
            if (pctForEdit != null && !isNaN(parseFloat(pctForEdit)) && item.retail_price != null && !isNaN(parseFloat(item.retail_price))) {
                var taxPct = parseFloat(String($('#item-tax-percent').val()).replace(/%/g, '')) || 18;
                var pct = parseFloat(pctForEdit);
                editRetail = Math.round(parseFloat(item.retail_price) / ((1 + pct / 100) * (1 + taxPct / 100)));
            }
        }
        // Retail price: do not set from row; loadItemDetails will set from item's linked retail (API)
        $('#item-retail-price').val('');
        $('#item-retail-percentage').val((pctForEdit != null && pctForEdit !== '') ? String(pctForEdit) : '');
        updateRetailPctSelectColor();
        updateRateColumnByRetailPct();
        updateRetailAfterCalc();
        updateRetailColumnByRate();
        $('#item-unit').val(item.unit || '');
        $('#item-discount').val(item.discount != null ? item.discount : '0');
        $('#discount-type').val('amount');
        $('#item-tax').val(item.tax_percentage != null ? item.tax_percentage : '0');
        $('#item-quantity').val(item.quantity != null ? item.quantity : '1');
        $('#item-quantity-input').val(item.quantity != null ? item.quantity : '1').hide();
        if (item.warranty) {
            var w = (item.warranty + '').trim().split(/\s+/);
            $('#warranty-value').val(w[0] || ''); $('#warranty-unit').val(w[1] || '');
        } else { $('#warranty-value').val(''); $('#warranty-unit').val(''); }
        currentEntryType = item.entry_type || 'purchase';
        addItemModalTitleKey = item.entry_type || 'purchase';
        $('#add-item-modal').modal('show');
        loadItemDetails(item.item_id);
    });

    function resetItemModal() {
        editingRowId = null;
        pendingEditItem = null;
        itemBaseRetailPrice = null;
        $('#selected-item-id').val('');
        $('#item-search').val('');
        $('#item-quantity').val('');
        $('#item-quantity-input').val('1').hide();
        $('#item-unit').val('');
        $('#item-rate').val('0');
        $('#item-retail-price').val('');
        $('#item-retail-percentage').val('');
        $('#item-tax-percent').val('18%');
        updateRetailPctSelectColor();
        updateRateColumnByRetailPct();
        updateRetailAfterCalc();
        updateRetailColumnByRate();
        $('#warranty-value').val('');
        $('#warranty-unit').val('');
        $('#item-discount').val('0');
        $('#discount-type').val('amount');
        $('#item-tax').val('0');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to see history</p>');
        $('#hold-rate-link').hide();
        if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
        $('#item-search-results').hide();
        $('#stock-status-section').hide();
        $('#stock-status-content').hide();
        $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">Select item to see stock</p>');
        $('#stock-status-list-total').hide();
        $('#selected-item-details-display').addClass('d-none');
        $('#selected-item-details-line1').html('');
        $('#selected-item-details-line2').html('').css('display', 'none');
        $('#selected-item-details-line3').html('');
        $('#item-edit-in-modal-btn').hide();
        $('#item-search-image-preview').addClass('d-none');
        $('#item-search-image').attr('src', '');
        $('#barcode-scan-input').val('');
        $('#item-search-stock').html('');
        $('#item-search-warehouse').text('');
        $('#selected-warehouse-id').val('');
        $('#selected-warehouse-ids').val('');
        $('#item-retail-price-column').hide();
    }

    function calculateTotals() {
        let itemTotal = 0;
        purchaseItems.forEach(function(item) {
            const t = parseFloat(item.total);
            const isReturn = ['return', 'scrap', 'claim_send', 'damage'].indexOf(item.entry_type) >= 0;
            itemTotal += isReturn ? -t : t;
        });

        const orderTax = parseFloat($('#order_tax').val()) || 0;
        const discountType = $('#discount_type').val();
        let discount = 0;
        
        if (discountType === 'percent') {
            const discountPercent = parseFloat($('#discount_percent').val()) || 0;
            const subtotalBeforeDiscount = itemTotal + orderTax;
            discount = (subtotalBeforeDiscount * discountPercent) / 100;
        } else {
            discount = parseFloat($('#discount').val()) || 0;
        }
        
        const shipping = parseFloat($('#shipping').val()) || 0;

        const grossTotal = itemTotal;
        const grandTotal = itemTotal + orderTax - discount + shipping;

        $('#gross-amount').text('Rs ' + Math.round(parseFloat(grossTotal)));
        $('#grand-total').text('Rs ' + Math.round(parseFloat(grandTotal)));
        $('#payment_grand_total_display').text('Rs ' + Math.round(parseFloat(grandTotal)));
        $('#total_after_discount').text('Rs ' + Math.round(parseFloat(grandTotal)));
        if ($('#payment-gross-amount').length) {
            $('#payment-gross-amount').text(Math.round(parseFloat(grossTotal)));
            $('#net-payable-total').text(Math.round(parseFloat(grandTotal)));
        }
        // Sum cash + bank and set payment_amount, then remaining
        updatePurchasePaymentFromInputs();
        // Set max payment amount to grand total (if negative e.g. all scrap, use 0)
        const grandTotalValue = Math.max(0, parseFloat(grandTotal));
        $('#payment_amount').attr('max', grandTotalValue);
        const currentPaymentAmount = parseFloat($('#payment_amount').val()) || 0;
        if (currentPaymentAmount > grandTotalValue) {
            $('#payment_amount').val(grandTotalValue);
        }
        // Update remaining amount
        updateRemainingAmount();
        // Subtotal row below table (total qty + total amount) and entry-type breakdown
        updateItemsSubtotal();
    }

    function updatePurchasePaymentFromInputs() {
        let cashTotal = 0;
        $('.purchase-cash-input').each(function() { cashTotal += parseFloat($(this).val()) || 0; });
        let bankTotal = 0;
        $('.purchase-bank-amt').each(function() { bankTotal += parseFloat($(this).val()) || 0; });
        const totalPaid = cashTotal + bankTotal;
        $('#payment_amount').val(totalPaid.toFixed(2));
        const netPayable = parseFloat($('#grand-total').text().replace('Rs ', '').replace(/,/g, '')) || 0;
        const remaining = Math.max(0, netPayable - totalPaid);
        $('#purchase-current-remaining').text(Math.round(remaining));
    }

    $(document).on('input change', '#totalBillDiscount', function() {
        $('#discount').val($(this).val() || 0);
        calculateTotals();
    });
    $('#purchase-add-cash-row').on('click', function() {
        var r = '<div class="border rounded p-2 mb-2" style="border-color:#bfdbfe;background:#f8fafc"><div class="d-flex justify-content-between align-items-center gap-2"><span class="small fw-bold text-uppercase">Cash Entry</span><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-cash-row"><i class="ti ti-x"></i></button><div class="d-flex align-items-center border rounded px-2 bg-white" style="width:140px"><span class="small text-muted me-1">Rs</span><input type="number" class="form-control border-0 purchase-cash-input" value="0" min="0" step="0.01" style="font-weight:600;text-align:right"></div></div><div class="mt-2"><label class="d-block border border-dashed rounded p-2 text-center small cursor-pointer" style="border-color:#bfdbfe"><i class="ti ti-camera me-1"></i> Attach Photo<input type="file" accept="image/*" class="d-none purchase-cash-pic"></label></div></div>';
        $('#purchaseCashPaidWrapper').append(r);
    });
    $(document).on('click', '.remove-purchase-cash-row', function() { $(this).closest('.border.rounded.p-2').remove(); updatePurchasePaymentFromInputs(); });
    $(document).on('input', '.purchase-cash-input', function() { updatePurchasePaymentFromInputs(); });

    window.purchaseBankAccounts = @json(\App\Models\BankAccount::where('status', true)->with('bank')->get()->map(function($a) { return ['id' => $a->id, 'label' => ($a->bank->name ?? 'N/A') . ' - ' . $a->account_title . ' (' . $a->account_number . ')']; }));
    $('#purchase-add-bank-row').on('click', function() {
        var opts = '<option value="">Select Bank</option>';
        (window.purchaseBankAccounts || []).forEach(function(b) { opts += '<option value="' + b.id + '">' + b.label + '</option>'; });
        var row = '<div class="border rounded p-2 mb-2 purchase-bank-row" style="border-color:#e9d5ff"><div class="d-flex justify-content-between align-items-center mb-2"><span class="small fw-bold text-uppercase" style="color:#9333ea">Bank Entry</span><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-bank-row"><i class="ti ti-x"></i></button></div><div class="mb-2"><select class="form-select form-select-sm purchase-bank-account">' + opts + '</select></div><div class="row g-2"><div class="col-6"><input type="text" class="form-control form-control-sm purchase-bank-ref" placeholder="Trans ID"></div><div class="col-6"><input type="number" class="form-control form-control-sm purchase-bank-amt" value="0" min="0" step="0.01" placeholder="Rs"></div></div></div>';
        $('#purchaseBankPaidWrapper').append(row);
    });
    $(document).on('click', '.remove-purchase-bank-row', function() { $(this).closest('.purchase-bank-row').remove(); updatePurchasePaymentFromInputs(); });
    $(document).on('input', '.purchase-bank-amt', function() { updatePurchasePaymentFromInputs(); });

    function updateItemsSubtotal() {
        if (!purchaseItems || purchaseItems.length === 0) {
            $('#purchase-total-below').addClass('d-none').hide();
            return;
        }
        const byType = { purchase: { qty: 0, amount: 0 }, return: { qty: 0, amount: 0 }, claim_send: { qty: 0, amount: 0 }, damage: { qty: 0, amount: 0 }, scrap: { qty: 0, amount: 0 } };
        const claimTypes = ['return', 'scrap', 'claim_send', 'damage'];
        purchaseItems.forEach(function(item) {
            const qty = parseFloat(item.quantity) || 0;
            const t = parseFloat(item.total) || 0;
            const et = item.entry_type || 'purchase';
            const key = byType.hasOwnProperty(et) ? et : 'purchase';
            const amt = claimTypes.indexOf(et) >= 0 ? -Math.abs(t) : t;
            byType[key].qty += qty;
            byType[key].amount += amt;
        });
        $('#purchase-total-below').removeClass('d-none').show();
        if (byType.purchase.qty > 0 || byType.purchase.amount !== 0) {
            $('#purchase-total-below-row').show();
            $('#subtotal-purchase-qty-below').text(byType.purchase.qty);
            $('#subtotal-purchase-amount-below').text('Rs ' + Math.round(byType.purchase.amount));
        } else {
            $('#purchase-total-below-row').hide();
        }
        const typeLabels = { return: 'RETURN', claim_send: 'SEND CLAIM', damage: 'DAMAGE', scrap: 'SCRAP' };
        const typeClasses = { return: 'total-below-return', claim_send: 'total-below-send-claim', damage: 'total-below-damage', scrap: 'total-below-scrap' };
        let claimHtml = '';
        ['return', 'claim_send', 'damage', 'scrap'].forEach(function(et) {
            if (byType[et].qty > 0 || byType[et].amount !== 0) {
                var cls = typeClasses[et] || '';
                claimHtml += '<div class="d-flex justify-content-between align-items-center mt-1 total-below-row ' + cls + '"><span>' + typeLabels[et] + '</span><span>' + byType[et].qty + ' QTY · Rs ' + Math.round(byType[et].amount) + '</span></div>';
            }
        });
        if (claimHtml) {
            $('#claim-totals-below-container').html(claimHtml).addClass('mt-2 pt-2 border-top border-secondary').show();
        } else {
            $('#claim-totals-below-container').html('').removeClass('mt-2 pt-2 border-top border-secondary').hide();
        }
    }
    
    // Update remaining amount display (for purchase calculation - not used for supplier balance display)
    function updateRemainingAmount() {
        // Note: remaining_amount now shows supplier balance, not purchase remaining
        // This function is kept for other calculations but doesn't update remaining_amount
        const grandTotal = parseFloat($('#grand-total').text().replace('Rs ', '').replace(/,/g, '')) || 0;
        const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
        const remaining = Math.max(0, grandTotal - paymentAmount);
        
        // Don't update remaining_amount here - it shows supplier balance instead
        // Remaining amount calculation is kept for internal use if needed
    }
    
    // Fill full amount button
    $('#fillFullAmount').on('click', function() {
        const grandTotal = parseFloat($('#grand-total').text().replace('Rs ', '').replace(/,/g, '')) || 0;
        $('#payment_amount').val(grandTotal.toFixed(2));
        updateRemainingAmount();
    });
    
    // Update remaining amount when payment amount changes (already handled above, removing duplicate)
    
    // Payment method change handler
    $('#payment_method_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const requiresBank = selectedOption.data('requires-bank') == '1';
        const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
        const methodCode = selectedOption.data('method-code') || '';
        const isCash = methodCode.toLowerCase() === 'cash';
        
        // Show/hide transaction ID field (required for non-cash methods)
        if (!isCash && $(this).val()) {
            $('#transaction_id_wrapper').show();
            $('#payment_transaction_id').prop('required', true);
        } else {
            $('#transaction_id_wrapper').hide();
            $('#payment_transaction_id').prop('required', false);
            $('#payment_transaction_id').val('');
        }
        
        if (requiresBank && paymentAmount > 0) {
            $('#bank_account_wrapper').show();
            $('#bank_account_id').prop('required', true);
        } else {
            $('#bank_account_wrapper').hide();
            $('#bank_account_id').prop('required', false);
            $('#bank_account_id').val('');
        }
    });
    
    // Trigger change on page load to set initial state (Cash is selected by default)
    $('#payment_method_id').trigger('change');
    
    // Payment photo preview
    $('#payment_photo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#payment_photo_preview_img').attr('src', e.target.result);
                    $('#payment_photo_preview').show();
                };
                reader.readAsDataURL(file);
            } else {
                alert('Please select an image file');
                $(this).val('');
                $('#payment_photo_preview').hide();
            }
        } else {
            $('#payment_photo_preview').hide();
        }
    });
    
    // Remove payment photo
    $('#remove_payment_photo').on('click', function() {
        $('#payment_photo').val('');
        $('#payment_photo_preview').hide();
        $('#payment_photo_preview_img').attr('src', '');
    });
    
    // Show/hide percentage input based on discount type
    $('#discount_type').on('change', function() {
        const discountType = $(this).val();
        if (discountType === 'percent') {
            $('#discount_percent_input').show();
            $('#discount').val(0).prop('disabled', true);
        } else {
            $('#discount_percent_input').hide();
            $('#discount').prop('disabled', false);
            $('#discount_percent').val(0);
        }
        calculateTotals();
    });
    
    // Recalculate totals when discount changes
    $('#discount').on('input change', function() {
        calculateTotals();
    });
    
    // Recalculate totals when discount percentage changes
    $('#discount_percent').on('input change', function() {
        calculateTotals();
    });
    
    // Initialize discount type on page load
    if ($('#discount_type').val() === 'percent') {
        $('#discount_percent_input').show();
        $('#discount').prop('disabled', true);
    } else {
        $('#discount_percent_input').hide();
        $('#discount').prop('disabled', false);
    }
    
    // Show/hide bank account based on payment amount
    $('#payment_amount').on('input change', function() {
        const paymentAmount = parseFloat($(this).val()) || 0;
        const selectedOption = $('#payment_method_id').find('option:selected');
        const requiresBank = selectedOption.data('requires-bank') == '1';
        
        if (paymentAmount > 0 && requiresBank) {
            $('#bank_account_wrapper').show();
            $('#bank_account_id').prop('required', true);
        } else if (paymentAmount === 0) {
            $('#bank_account_wrapper').hide();
            $('#bank_account_id').prop('required', false);
        }
        
        updateRemainingAmount();
    });
    
    // Set payment amount to grand total on load
    $(document).ready(function() {
        calculateTotals();
        updateRemainingAmount();
        
        // Fetch supplier balance if supplier is already selected
        const selectedSupplierId = $('#supplier_id').val();
        if (selectedSupplierId) {
            $('#supplier_id').trigger('change');
        }
    });

    $('#printFormatSwitch input[name="print_format_radio"]').on('change', function() {
        $('#print_format').val($(this).val());
    });

    $('#btnSaveAndPrint').on('click', function() {
        $('#save_and_print').val('1');
        $('#save_and_send_pdf').val('0');
        $('#print_format').val($('input[name="print_format_radio"]:checked').val() || 'a4');
        $('#purchaseForm').submit();
    });

    $('#btnSendPdf').on('click', function() {
        $('#save_and_send_pdf').val('1');
        $('#save_and_print').val('0');
        $('#purchaseForm').submit();
    });

    $(document).on('click', '#btnSaveNew', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#save_and_print').val('0');
        $('#save_and_send_pdf').val('0');
        $('#save_and_new').val('1');
        $('#purchaseForm').trigger('submit');
    });

    // Form submission
    $('#purchaseForm').on('submit', function(e) {
        e.preventDefault();

        if (purchaseItems.length === 0) {
            alert('Please add at least one item');
            return false;
        }
        
        var saveAndPrint = $('#save_and_print').val() === '1';
        var saveAndSendPdf = $('#save_and_send_pdf').val() === '1';
        var saveAndNew = $('#save_and_new').val() === '1';
        var grandTotal = parseFloat($('#grand-total').text().replace('Rs ', '').replace(/,/g, '')) || 0;
        
        // Payment validation: skip when Save & Print, Send PDF, or Save & New (allow save without payment)
        if (!saveAndPrint && !saveAndSendPdf && !saveAndNew) {
            const paymentMethod = $('#payment_method_id').val();
            const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
            if (paymentMethod && paymentAmount <= 0) {
                alert('Please enter a valid payment amount.');
                $('#payment_amount').focus();
                return false;
            }
            if (paymentAmount > grandTotal) {
                alert('Payment amount cannot exceed grand total (Rs ' + Math.round(grandTotal) + ')!');
                $('#payment_amount').focus();
                return false;
            }
            if (paymentMethod && paymentAmount > 0) {
                const selectedOption = $('#payment_method_id').find('option:selected');
                const requiresBank = selectedOption.data('requires-bank') == '1';
                if (requiresBank && !$('#bank_account_id').val()) {
                    alert('Please select a bank account for this payment method.');
                    $('#bank_account_id').focus();
                    return false;
                }
            }
        }

        // Prepare items data (include warehouse_id, entry_type, retail_price, purchase_order_item_id for PO received qty update)
        // Use quantity from table input when present so PO received qty is always what user entered
        const itemsData = purchaseItems.map(function(item, index) {
            var $row = $('#items-tbody .purchase-item-row').eq(index);
            var qtyFromInput = $row.find('.purchase-row-qty-input').val();
            var quantity = (qtyFromInput !== undefined && qtyFromInput !== '' && !isNaN(parseFloat(qtyFromInput)))
                ? parseFloat(qtyFromInput) : (parseFloat(item.quantity) || 0);
            var verified = $('#items-tbody').find('.purchase-row-verified-cb').eq(index).is(':checked');
            var payload = {
                item_id: item.item_id,
                warehouse_id: item.warehouse_id || '',
                quantity: quantity,
                unit: item.unit,
                rate: item.rate,
                retail_price: (item.retail_price != null && item.retail_price !== '') ? item.retail_price : '',
                discount: item.discount,
                tax_percentage: item.tax_percentage,
                entry_type: item.entry_type || 'purchase',
                verified: verified ? 1 : 0
            };
            var poItemId = parseInt(item.purchase_order_item_id, 10);
            if (isNaN(poItemId) || poItemId <= 0) {
                var fromRow = $row.data('purchase-order-item-id');
                if (fromRow != null && fromRow !== '') poItemId = parseInt(fromRow, 10);
            }
            if (!isNaN(poItemId) && poItemId > 0) {
                payload.purchase_order_item_id = poItemId;
            }
            return payload;
        });

        // Sync discount and payment totals from cash/bank rows
        $('#discount').val($('#totalBillDiscount').val() || 0);
        updatePurchasePaymentFromInputs();

        // Build payments array: cash rows + bank rows (for backend multiple payments)
        const cashMethodId = $('#purchase_cash_method_id').val();
        const bankMethodId = $('#purchase_bank_method_id').val();
        const payments = [];
        $('.purchase-cash-input').each(function() {
            const amt = parseFloat($(this).val()) || 0;
            if (amt > 0 && cashMethodId) payments.push({ payment_method_id: cashMethodId, amount: amt, bank_account_id: '', transaction_id: '' });
        });
        $('.purchase-bank-row').each(function() {
            const amt = parseFloat($(this).find('.purchase-bank-amt').val()) || 0;
            const bankId = $(this).find('.purchase-bank-account').val();
            const ref = $(this).find('.purchase-bank-ref').val() || '';
            if (amt > 0 && bankId && bankMethodId) payments.push({ payment_method_id: bankMethodId, amount: amt, bank_account_id: bankId, transaction_id: ref });
        });
        if (payments.length === 0 && parseFloat($('#payment_amount').val()) > 0) {
            payments.push({ payment_method_id: $('#payment_method_id').val(), amount: $('#payment_amount').val(), bank_account_id: $('#bank_account_id').val() || '', transaction_id: $('#payment_transaction_id').val() || '' });
        }

        // Add items to form
        const formData = new FormData(this);
        itemsData.forEach(function(item, index) {
            Object.keys(item).forEach(function(key) {
                formData.append(`items[${index}][${key}]`, item[key]);
            });
        });
        payments.forEach(function(p, i) {
            formData.append(`payments[${i}][payment_method_id]`, p.payment_method_id);
            formData.append(`payments[${i}][amount]`, p.amount);
            formData.append(`payments[${i}][bank_account_id]`, p.bank_account_id || '');
            formData.append(`payments[${i}][transaction_id]`, p.transaction_id || '');
        });

        // Submit form
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Purchase created successfully:', response);
                if (response.success) {
                    var saveAndPrint = $('#save_and_print').val() === '1';
                    var saveAndSendPdf = $('#save_and_send_pdf').val() === '1';
                    var saveAndNew = $('#save_and_new').val() === '1';
                    $('#save_and_print').val('0');
                    $('#save_and_send_pdf').val('0');
                    $('#save_and_new').val('0');
                    if (saveAndSendPdf && response.signed_pdf_url) {
                        var phone = ($('#supplier_id').find('option:selected').data('phone') || '').toString().replace(/\D/g, '');
                        if (phone.length === 10 && (phone.startsWith('3') || phone.startsWith('4'))) {
                            phone = '92' + phone;
                        } else if (phone.length > 0 && !phone.startsWith('92')) {
                            phone = '92' + phone.replace(/^0+/, '');
                        }
                        var msg = 'Invoice PDF: ' + response.signed_pdf_url;
                        var waUrl = 'https://wa.me/' + (phone || '') + (phone ? '?text=' + encodeURIComponent(msg) : '');
                        if (phone) {
                            window.open(waUrl, '_blank');
                            alert('Purchase saved! WhatsApp opened with PDF link. Send the message to the supplier. Invoice: ' + (response.invoice_no || ''));
                        } else {
                            alert('Purchase saved! Copy this link to send to supplier:\n' + response.signed_pdf_url);
                        }
                        /* Stay on create page */
                    } else if (saveAndPrint && response.purchase_id) {
                        var printFormat = ($('#print_format').val() || 'a4').toLowerCase();
                        var printUrl = '{{ url("purchases") }}/' + response.purchase_id + '?print=1&format=' + encodeURIComponent(printFormat);
                        window.open(printUrl, 'purchase_print', 'width=800,height=700,scrollbars=yes');
                        alert('Purchase saved! Print window opened. Invoice: ' + (response.invoice_no || ''));
                    } else if (saveAndNew) {
                        alert('Purchase created successfully! Invoice: ' + (response.invoice_no || ''));
                        window.location.href = '{{ route("purchases.create") }}';
                    } else {
                        alert('Purchase created successfully! Invoice: ' + (response.invoice_no || ''));
                        purchaseItems = [];
                        $('#items-tbody').empty();
                        $('#empty-items-state').show();
                        $('#items-list').hide();
                        $('#payment-amount-row').hide();
                        calculateTotals();
                        window.location.href = '{{ route("all_purchases") }}';
                    }
                } else {
                    alert(response.message || 'Purchase created but with warnings.');
                    window.location.href = '{{ route("all_purchases") }}';
                }
            },
            error: function(xhr) {
                console.error('Purchase creation error:', xhr);
                let errorMessage = 'Error saving purchase. Please try again.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('\n');
                    }
                } else if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
                
                alert(errorMessage);
            }
        });
    });

    // Initialize date picker
    if ($('#purchase_date').length) {
        $('#purchase_date').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true
        });
    }
});
</script>
@endpush
@endsection
