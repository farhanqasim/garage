@extends('layouts.app')

@section('title', (isset($editMode) && $editMode && isset($purchase) && $purchase->is_purchase_order) ? 'Edit Purchase Order' : (isset($editMode) && $editMode ? 'Edit Purchase' : ((($demandMode ?? false) && !(isset($editMode) && $editMode)) ? 'Create Purchase Order' : 'Create Purchase')))

@section('content')
<script>window.PURCHASE_IS_DEMAND_FLOW = @json((bool)($isDemandPurchaseFlow ?? false));</script>
<script>window.PURCHASE_DEMAND_USER_NAME = @json(auth()->check() ? trim((string) (auth()->user()->name ?? '')) : '');</script>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>{{ (isset($editMode) && $editMode && isset($purchase) && $purchase->is_purchase_order) ? 'Edit Purchase Order' : (isset($editMode) && $editMode ? 'Edit Purchase' : ((($demandMode ?? false) && !(isset($editMode) && $editMode)) ? 'Create Purchase Order' : 'Create Purchase')) }}</h4>
            @if(isset($editMode) && $editMode && isset($purchase))
                <h6 class="text-muted mb-0">{{ $purchase->is_purchase_order ? ('PO #' . preg_replace('/^PO-/', '', $purchase->invoice_no ?? '')) : ('Bill #' . $purchase->invoice_no) }}</h6>
            @endif
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
                    <form action="{{ (isset($editMode) && $editMode && isset($purchase)) ? route('purchases.update', $purchase->id) : route('purchases.store') }}" method="POST" id="purchaseForm" enctype="multipart/form-data">
                        @csrf
                        @if(isset($editMode) && $editMode && isset($purchase))
                            @method('PUT')
                        @endif
                        <input type="hidden" id="saved_purchase_id" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? $purchase->id : '' }}" aria-hidden="true">

                        <!-- Saved bill banner (shown after Save & Print; hidden by default and in edit mode) -->
                        <div id="purchase-saved-banner" class="alert alert-success alert-dismissible fade show mb-3 {{ (isset($editMode) && $editMode) ? 'd-none' : 'd-none' }}" role="alert">
                            <strong><i class="ti ti-check me-2"></i>Bill saved and printed.</strong> Invoice: <span id="purchase-saved-invoice-no"></span>. Bill is locked. Click <strong>Edit Bill</strong> below to make changes.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        <!-- ACTIVE BRANCH Selector (Pill-shaped like Gemini design) -->
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill" style="border: 1px solid #0d6efd; background: #f8f9fa;">
                                <i class="ti ti-user me-2 text-muted"></i>
                                <span class="fw-bold me-2 text-uppercase" style="font-size: 12px;">ACTIVE BRANCH:</span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-primary p-0 text-decoration-none dropdown-toggle fw-bold" type="button" id="branchDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 14px;">
                                        <span id="selectedBranchName">{{ (isset($editMode) && $editMode && isset($purchase) && $purchase->branch) ? $purchase->branch->branch_name : session('selected_branch_name', 'Select Branch') }}</span>
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
                            <input type="hidden" name="branch_id" id="purchaseBranchId" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? $purchase->branch_id : session('selected_branch_id') }}" required>
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
                                                $isEditPo = isset($editMode) && $editMode && isset($purchase) && $purchase->is_purchase_order;
                                                $demandNewPo = ($demandMode ?? false) && !(isset($editMode) && $editMode);
                                                $poUiActive = $isEditPo || $demandNewPo;
                                                if ($isEditPo) {
                                                    $displayPoNum = $purchase->invoice_no ? preg_replace('/^PO-/', '', $purchase->invoice_no) : str_pad((int) $purchase->id, 5, '0', STR_PAD_LEFT);
                                                    $displayBillNum = preg_replace('/^PUR-\d+-/', '', $purchase->invoice_no ?? '') ?: str_pad((int) $purchase->id, 5, '0', STR_PAD_LEFT);
                                                } else {
                                                    $nextBillNum = str_pad((\App\Models\Purchase::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
                                                    $nextPoNum = str_pad(\App\Models\Purchase::where('invoice_no', 'like', 'PO-%')->count() + 1, 5, '0', STR_PAD_LEFT);
                                                    $displayBillNum = $nextBillNum;
                                                    $displayPoNum = $nextPoNum;
                                                }
                                            @endphp
                                            <span class="text-primary fw-bold" style="font-size: 16px;" id="purchase-number" data-bill-number="{{ $displayBillNum }}" data-po-number="{{ $displayPoNum }}">{{ $poUiActive ? ('PO #' . $displayPoNum) : ('Bill #' . $displayBillNum) }}</span>
                                        </div>
                                        <div style="font-size: 13px; color: #6c757d;">
                                            <span id="currentDateTime">{{ date('d/m/Y, H:i:s') }}</span>
                                        </div>
                                    </div>
                                    <!-- Toggle: Bill (off) / PO (on) - press = PO, press again = Bill -->
                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        <span class="small text-muted fw-semibold" id="purchaseDocLabelBill">Bill</span>
                                        <label class="mb-0 position-relative d-inline-block flex-shrink-0" style="width: 44px; height: 24px; flex-shrink: 0;">
                                            <input type="checkbox" class="form-check-input position-absolute top-0 start-0" id="purchaseOrderSwitch" value="1" style="width: 44px; height: 24px; cursor: pointer; opacity: 0; z-index: 2; margin: 0;" aria-label="Purchase Order On/Off" {{ ($poUiActive ?? false) ? 'checked' : '' }}>
                                            <span class="position-absolute top-0 start-0 rounded-pill bg-secondary" id="purchaseOrderTrack" style="width: 44px; height: 24px; transition: background 0.2s;"></span>
                                            <span class="position-absolute rounded-circle bg-white border shadow-sm" id="purchaseOrderThumb" style="width: 20px; height: 20px; top: 2px; left: 2px; transition: left 0.2s;"></span>
                                        </label>
                                        <span class="small text-muted fw-semibold" id="purchaseDocLabelPO">PO</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="is_purchase_order" id="isPurchaseOrderHidden" value="{{ ($poUiActive ?? false) ? '1' : '0' }}">
                        
                        <!-- Hidden purchase date field -->
                        <input type="hidden" name="purchase_date" id="purchase_date" value="{{ (isset($editMode) && $editMode && isset($purchase) && isset($purchaseForJs) && !empty($purchaseForJs['purchase_date'])) ? $purchaseForJs['purchase_date'] : date('Y-m-d') }}" required>

                        <!-- Supplier/Customer Information (Like Gemini Design) -->
                        <div class="row mb-4">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">SUPPLIER NAME</label>
                                <div class="input-group supplier-name-input-group supplier-field-row">
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
                                                    data-products="{{ e(json_encode($products)) }}"
                                                    {{ ((isset($editMode) && $editMode && isset($purchase) && $purchase->supplier_id == $supplier->id) || (($demandMode ?? false) && isset($demandSupplierId) && (int) $demandSupplierId === (int) $supplier->id)) ? 'selected' : '' }}>
                                                {{ $supplier->company ?? '' }}@if($supplier->company ?? '') - @endif{{ $supplier->names[0] ?? 'N/A' }}@if(!empty($supplier->phones[0])) - {{ $supplier->phones[0] }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="supplier-actions-wrap d-flex flex-shrink-0 align-items-center gap-2">
                                        <a href="{{ route('suppliers.index') }}" id="supplier_edit_btn" class="btn btn-sm btn-outline-secondary supplier-edit-btn" style="border-radius: 0;" title="Edit vendor" data-base-url="{{ route('suppliers.index') }}"><i class="ti ti-edit"></i></a>
                                        <button type="button" id="supplier_ledger_btn" class="btn btn-sm btn-outline-primary" style="border-radius: 0 6px 6px 0; display: none;" title="Supplier Ledger Report"><i class="ti ti-file-text me-1"></i>Ledger</button>
                                        <span id="supplier-previous-balance-wrap" class="d-none d-flex align-items-center ms-2 ps-2 pe-2 py-1 rounded" style="background: #1e3a5f; border: 1px solid #1e3a5f;">
                                            <span id="supplier-balance-label" class="small me-1 fw-semibold text-white" style="font-size: 10px;">Balance:</span>
                                            <span id="remaining_amount" class="fw-bold text-white" style="font-size: 13px;">Rs 0</span>
                                        </span>
                                    </div>
                                </div>
                                @error('supplier_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                <input type="hidden" id="supplier_mobile" name="supplier_mobile" value="">
                            </div>
                        </div>

                        <!-- Reference (Optional) -->
                        <!-- Items Section (frozen with form when bill is saved) -->
                        <div id="purchase-items-section">
                        <!-- Items Summary Section -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">ITEMS SUMMARY</h5>
                            <div id="items-summary-container" class="py-5" style="background: #f8f9fa; border-radius: 8px; min-height: 200px;">
                                <div id="empty-items-state" class="text-center">
                                    <i class="ti ti-package fs-48 text-muted mb-3" style="display: block;"></i>
                                    <p class="text-muted mb-2">No items in cart</p>
                                    <p class="text-muted small mb-0" id="empty-state-hint">Select a branch first, then add items</p>
                                </div>
                                <div id="items-list" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                        <h6 class="mb-0 fw-bold text-muted small text-uppercase">Items</h6>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-primary" id="purchase-print-labels-btn" title="Sab items ke labels print"><i class="ti ti-printer me-1"></i> Print All</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table mb-0 pehla-items-table" id="items-table">
                                            <thead>
                                                <tr class="pehla-items-thead">
                                                    <th class="pehla-th">WAREHOUSE</th>
                                                    <th class="pehla-th text-center">ITEM</th>
                                                    <th class="pehla-th text-end">TOTAL</th>
                                                    <th class="pehla-th pehla-th-print-select text-center" style="width: 100px;">PRINT / SELECT</th>
                                                    <th class="pehla-th pehla-th-actions"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-tbody">
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="purchase-total-below" class="mt-3 p-3 border rounded bg-light fw-bold d-none" style="display: none;">
                                        <div id="purchase-total-below-row" class="d-flex justify-content-between align-items-center total-below-purchase" style="display: none;">
                                            <span class="total-below-purchase-label">PURCHASE</span>
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
                                <button type="button" class="btn btn-action-purchase btn-action-purchase-barcode-toolbar flex-shrink-0" id="add-new-item-btn-barcode-row" title="Add item to cart">
                                    <i class="ti ti-shopping-cart me-2"></i>PURCHASE ITEM
                                </button>
                            </div>
                        </div>

                        <!-- Claim Return, Return, Send Claim, Scrap (add item: Barcode row PURCHASE / DEMAND ITEM) -->
                        <div class="text-center mb-4 d-flex flex-wrap justify-content-center align-items-center gap-3">
                            <a href="#" class="btn btn-action-return btn-lg" id="return-btn">
                                <i class="ti ti-arrow-back-up me-2"></i>NEW RETURN
                            </a>
                            @unless($isDemandPurchaseFlow ?? false)
                            <button type="button"
                                    id="purchase-claim-history-btn"
                                    class="btn btn-action-send-claim btn-lg d-inline-flex flex-column align-items-center justify-content-center"
                                    title="Open claim history and management">
                                <span><i class="ti ti-history me-2"></i>CLAIM HISTORY</span>
                                <small class="mt-1" style="font-size: 0.68rem; font-weight: 700;">
                                    IN <span id="purchase-claim-history-count-in">0</span> |
                                    SENT <span id="purchase-claim-history-count-sent">0</span> |
                                    REV <span id="purchase-claim-history-count-reverse">0</span>
                                </small>
                            </button>
                            <a href="#" class="btn btn-action-damage btn-lg" id="scrap-send-btn">
                                <i class="ti ti-alert-triangle me-2"></i>SCRAP SEND
                            </a>
                            @endunless
                        </div>
                        </div>
                        <!-- /purchase-items-section -->

                        <!-- Supplier Payment Section (same design/colours as Sales) -->
                        <div class="row mb-4 payment-panel-totals" id="payment-section">
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
                                                <input type="number" id="totalBillDiscount" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? ($purchase->discount ?? 0) : 0 }}" min="0" step="0.01" class="bg-transparent text-right outline-none border-0" style="width: 64px; font-weight: 900; color: #16a34a; font-size: 14px;">
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $chargeRentToSupplierInitial = false;
                                        if (isset($editMode) && $editMode && isset($purchase)) {
                                            $chargeRentToSupplierInitial = \Illuminate\Support\Facades\Schema::hasColumn('purchases', 'charge_rent_to_supplier')
                                                ? (bool) $purchase->charge_rent_to_supplier
                                                : true;
                                        }
                                    @endphp
                                    <div class="rent-paid-section">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <p class="rent-paid-label mb-0">Rent Paid</p>
                                            <div class="d-flex align-items-center bg-white rounded-lg px-2 border" style="border-color: #fed7aa !important;">
                                                <span class="mr-2" style="font-size: 10px; font-weight: 900; color: #c2410c; background: #ffedd5; padding: 2px 6px; border-radius: 4px;">Rs</span>
                                                <input type="number" id="totalRentPaid" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? ($purchase->rent_paid ?? 0) : 0 }}" min="0" step="0.01" class="bg-transparent text-right outline-none border-0" style="width: 64px; font-weight: 900; color: #c2410c; font-size: 14px;">
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2 pt-2 border-top" style="border-color: rgba(254, 215, 170, 0.6) !important;">
                                            <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                                                <input class="form-check-input flex-shrink-0" type="checkbox" role="switch" id="chargeRentToSupplierToggle" {{ $chargeRentToSupplierInitial ? 'checked' : '' }} style="cursor: pointer;">
                                                <label class="form-check-label mb-0 small fw-semibold" for="chargeRentToSupplierToggle" style="cursor: pointer; font-size: 11px; color: #334155;">Charge rent to supplier</label>
                                            </div>
                                            <span id="rent-charge-to-supplier-hint" class="small text-muted text-end" style="font-size: 10px; max-width: 220px; line-height: 1.35;"></span>
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
                                            <div class="payment-card border-blue-100 no-print purchase-cash-entry-row">
                                                <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                                    <p class="mb-0" style="font-size: 10px; font-weight: 900; color: #374151; text-transform: uppercase;">Cash Entry</p>
                                                    <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end min-w-0 purchase-payment-amount-rail">
                                                        <div class="d-flex align-items-center purchase-cash-amount-wrap">
                                                            <span class="purchase-cash-prefix text-uppercase">Rs</span>
                                                            <input type="number" class="form-control purchase-cash-input border-0 bg-transparent shadow-none" value="0" min="0" step="0.01">
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-cash-row flex-shrink-0" title="Remove"><i class="ti ti-x"></i></button>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <label class="d-flex-1 cursor-pointer bg-white border border-dashed rounded-lg p-2 text-center block transition-all duration-300 purchase-cash-pic-label" style="border-color: #bfdbfe;">
                                                        <p class="status-text mb-0" style="font-size: 8px; font-weight: 900; color: #60a5fa; text-transform: uppercase;"><i class="ti ti-camera me-1"></i> Attach Photo</p>
                                                        <input type="file" accept="image/*" class="d-none purchase-cash-pic">
                                                    </label>
                                                    <div class="purchase-attach-preview mt-2 d-none"></div>
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
                                    <div id="purchase-remaining-or-advance-row" class="total-row" style="color: #ea580c;">
                                        <p id="purchase-remaining-label" class="mb-0" style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #ea580c;">Current Remaining</p>
                                        <p id="purchase-remaining-value" class="mb-0" style="font-size: 14px; font-weight: 700; color: #ea580c;">Rs <span id="purchase-current-remaining">0</span></p>
                                    </div>
                                    <div id="purchase-advance-notification" class="d-none rounded p-3 mt-2" style="background: #dcfce7; border: 1px solid #22c55e;">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="ti ti-wallet flex-shrink-0" style="font-size: 1.5rem; color: #16a34a;"></i>
                                                <div>
                                                    <p class="mb-0 fw-bold text-uppercase small lh-sm" style="font-size: 10px; letter-spacing: 0.5px; color: #166534;">Supplier Advance Available</p>
                                                    <p class="mb-0 small mt-1" style="font-size: 11px; color: #15803d; line-height: 1.35;">This amount is saved as advance with the supplier and will be adjusted in this bill.</p>
                                                </div>
                                            </div>
                                            <div class="fw-bold flex-shrink-0" style="font-size: 16px; color: #166534;">Rs <span id="purchase-advance-amount">0</span></div>
                                        </div>
                                    </div>
                                    <div id="purchase-payment-excess-warning" class="d-none small fw-bold mt-1 py-1 px-2 rounded" style="font-size: 11px; background: #fef2f2; color: #b91c1c;">Total payment exceeds amount due. Please reduce payment.</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="discount" id="discount" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? ($purchase->discount ?? 0) : 0 }}">
                        <input type="hidden" name="rent_paid" id="rent_paid" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? ($purchase->rent_paid ?? 0) : 0 }}">
                        <input type="hidden" name="charge_rent_to_supplier" id="charge_rent_to_supplier" value="{{ $chargeRentToSupplierInitial ? 1 : 0 }}">
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
                        <input type="hidden" name="order_tax" id="order_tax" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? ($purchase->order_tax ?? 0) : 0 }}">
                        <input type="hidden" name="shipping" id="shipping" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? ($purchase->shipping ?? 0) : 0 }}">
                        <input type="hidden" name="status" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? $purchase->status : 'pending' }}">
                        <input type="hidden" name="reference" id="reference" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? ($purchase->reference ?? '') : '' }}">
                        <input type="hidden" name="description" id="description" value="{{ (isset($editMode) && $editMode && isset($purchase)) ? ($purchase->description ?? '') : '' }}">

                        <!-- Submit Buttons -->
                        <input type="hidden" name="save_and_print" id="save_and_print" value="0">
                        <input type="hidden" name="save_and_send_pdf" id="save_and_send_pdf" value="0">
                        <input type="hidden" name="save_and_new" id="save_and_new" value="0">
                        <input type="hidden" name="print_format" id="print_format" value="a4">
                        <div id="purchase-form-actions-create" class="d-flex flex-wrap justify-content-end align-items-center gap-2 {{ (isset($editMode) && $editMode) ? 'd-none' : '' }}">
                            <div class="d-flex flex-wrap gap-2">
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
                        @php
                            $purchaseEditActionsPo = (isset($editMode) && $editMode && ($poUiActive ?? false));
                        @endphp
                        <div id="purchase-form-actions-edit" class="d-flex flex-wrap justify-content-end align-items-center gap-2 {{ (isset($editMode) && $editMode) ? '' : 'd-none' }}">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-info" id="btnUpdateAndPrint" title="{{ $purchaseEditActionsPo ? 'Update PO and open print dialog' : 'Update and open print dialog' }}">
                                    <i class="ti ti-printer me-1"></i> {{ $purchaseEditActionsPo ? 'Update PO & Print' : 'Update & Print' }}
                                </button>
                                <button type="submit" class="btn btn-success" id="btnUpdateBill" title="{{ $purchaseEditActionsPo ? 'Save purchase order changes' : 'Save bill changes' }}">
                                    <i class="ti ti-check me-1"></i> {{ $purchaseEditActionsPo ? 'Update PO' : 'Update Bill' }}
                                </button>
                                <a href="{{ route('all_purchases') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                        <div id="purchase-form-actions-saved" class="d-flex flex-wrap justify-content-end align-items-center gap-2 d-none">
                            <a href="#" id="btnEditBill" class="btn btn-primary">
                                <i class="ti ti-edit me-1"></i> Edit Bill
                            </a>
                            <a href="{{ route('all_purchases') }}" class="btn btn-secondary">Back to list</a>
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
                <div class="d-flex align-items-center gap-2 ms-auto me-3">
                    <span class="text-muted small me-2">Show:</span>
                    <div class="btn-group btn-group-sm" role="group" id="loadFromPOFilterSwitch">
                        <input type="radio" class="btn-check" name="loadFromPOFilter" id="loadFromPOFilterPending" value="pending" checked>
                        <label class="btn btn-outline-warning" for="loadFromPOFilterPending">Pending</label>
                        <input type="radio" class="btn-check" name="loadFromPOFilter" id="loadFromPOFilterComplete" value="complete">
                        <label class="btn btn-outline-success" for="loadFromPOFilterComplete">Complete</label>
                    </div>
                </div>
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
    <div class="modal-dialog modal-dialog-centered modal-xl add-supplier-modal-scroll" style="pointer-events: auto;">
        <div class="modal-content add-supplier-modal-content" style="pointer-events: auto;">
            <div class="modal-header" style="pointer-events: auto;">
                <h4 class="modal-title" id="addSupplierModalLabel">Add Supplier</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.suppliers.modals.create-supplier-form')
        </div>
    </div>
</div>

{{-- Edit supplier in iframe: parent stays on /purchases/create --}}
@can('update_supplier')
<div class="modal fade" id="editSupplierFromPurchaseModal" tabindex="-1" aria-labelledby="editSupplierFromPurchaseModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index: 10058;">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" style="max-width: min(1140px, calc(100vw - 1.5rem));">
        <div class="modal-content" style="min-height: 70vh;">
            <div class="modal-header py-2">
                <h5 class="modal-title mb-0" id="editSupplierFromPurchaseModalLabel">Edit supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="min-height: 55vh;">
                <iframe id="editSupplierFromPurchaseIframe" title="Edit supplier" class="w-100 border-0 d-block" style="height: 75vh; min-height: 480px;" src="about:blank" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>
@endcan

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

{{-- Scrap-only picker (SCRAP SEND): branch scrap stock, not claim / not non-scrap --}}
<div class="modal fade" id="purchaseScrapPickerModal" tabindex="-1" aria-labelledby="purchaseScrapPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header border-bottom" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                <div>
                    <h5 class="modal-title mb-0" id="purchaseScrapPickerModalLabel">
                        <i class="ti ti-alert-triangle me-2 text-secondary"></i>Select scrap items
                    </h5>
                    <small class="text-muted">Scrap-type items with scrap stock in this branch only (normal warehouse stock — not claim stock).</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom bg-white">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control border-start-0" id="purchaseScrapPickerSearch" placeholder="Search name, part number, barcode, brand…" autocomplete="off">
                    </div>
                </div>
                <div id="purchaseScrapPickerLoading" class="text-center py-5 text-muted d-none">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div> Loading scrap items…
                </div>
                <div id="purchaseScrapPickerEmpty" class="text-center py-5 text-muted d-none">
                    <i class="ti ti-package-off fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-1 fw-semibold text-dark purchase-scrap-empty-title">No scrap items available in this branch.</p>
                    <small class="text-muted purchase-scrap-empty-sub">No scrap-type stock in branch warehouses, or nothing matches your search.</small>
                </div>
                <div id="purchaseScrapPickerMeta" class="px-3 py-2 small text-muted border-bottom bg-light d-none"></div>
                <div class="table-responsive" style="max-height: min(60vh, 520px);">
                    <table class="table table-hover align-middle mb-0 purchase-scrap-picker-table d-none" id="purchaseScrapPickerTable">
                        <thead class="table-light sticky-top shadow-sm" style="z-index: 2;">
                            <tr>
                                <th style="width: 40px;" class="text-center" title="Select for bulk add">
                                    <input type="checkbox" class="form-check-input" id="purchaseScrapPickerSelectAll" title="Select all on this page">
                                </th>
                                <th style="width: 56px;" class="text-center">Image</th>
                                <th>Item</th>
                                <th class="text-end purchase-scrap-rate-cell">Scrap qty</th>
                                <th class="text-end purchase-scrap-rate-cell">Purchase rate</th>
                                <th class="text-center" style="width: 104px;">Qty</th>
                                <th class="text-end" style="width: 88px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseScrapPickerTableBody"></tbody>
                    </table>
                </div>
                <div id="purchaseScrapPickerLoadMoreWrap" class="p-3 border-top bg-white d-none text-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="purchaseScrapPickerLoadMore">
                        <i class="ti ti-chevron-down me-1"></i> Load more
                    </button>
                </div>
            </div>
            <div class="modal-footer border-top flex-wrap gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="purchaseScrapPickerAddSelected">
                    <i class="ti ti-shopping-cart-plus me-1"></i> Add selected
                </button>
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
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Attach photo(s) <span class="text-danger">*</span></label>
                        <p class="small text-muted mb-2">Add one or more images (max 20, 5MB each). Gallery can pick multiple files.</p>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <button type="button" class="btn btn-outline-primary" id="tmp_image_gallery_btn" title="Choose from gallery or files">
                                <i class="ti ti-photo me-1"></i>Gallery
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="tmp_image_camera_btn" title="Take a photo with camera">
                                <i class="ti ti-camera me-1"></i>Take Photo
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="tmp_images_clear_all_btn" title="Remove all photos">
                                <i class="ti ti-trash me-1"></i>Clear all
                            </button>
                        </div>
                        <input type="file" class="form-control" id="tmp_images_file_input" accept="image/*" multiple style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;overflow:hidden;">
                        <div id="tmp_camera_capture_panel" class="tmp-camera-panel border rounded p-3 bg-light mt-2 d-none">
                            <p class="small text-muted mb-2">Use your camera to take a photo (adds to your list)</p>
                            <div class="position-relative mb-2" style="max-width: 320px; margin: 0 auto;">
                                <video id="tmp_camera_video" autoplay playsinline muted style="width:100%; max-height: 240px; background: #000; border-radius: 6px;"></video>
                            </div>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <button type="button" class="btn btn-primary" id="tmp_camera_capture_btn"><i class="ti ti-camera me-1"></i>Capture photo</button>
                                <button type="button" class="btn btn-outline-secondary" id="tmp_camera_cancel_btn">Cancel</button>
                            </div>
                        </div>
                        <div id="tmp_images_preview_wrap" class="mt-2 d-none">
                            <span class="small text-muted d-block mb-1">Selected (<span id="tmp_images_count">0</span>):</span>
                            <div id="tmp_images_preview_list" class="d-flex flex-wrap gap-2 align-items-start"></div>
                        </div>
                        <div id="tmp_image_error" class="invalid-feedback d-block"></div>
                    </div>
                    <div class="mb-3" id="tmp-product-name-block">
                        <label for="tmp_product_name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
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
                        <input type="number" class="form-control" id="tmp_cost_price" name="cost_price" required min="0" step="0.01" value="0" placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label for="tmp_quantity" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <select class="form-select" id="tmp_quantity" name="quantity" required>
                            <option value="" selected disabled>Select quantity — required</option>
                            @for ($q = 1; $q <= 1000; $q++)
                                <option value="{{ $q }}">{{ $q }}</option>
                            @endfor
                        </select>
                        <div id="tmp_quantity_error" class="invalid-feedback d-block"></div>
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

<!-- Supplier Ledger Modal -->
<div class="modal fade" id="supplierLedgerModalPurchase" tabindex="-1" aria-labelledby="supplierLedgerModalPurchaseLabel" aria-hidden="true" style="z-index: 10052;">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="supplierLedgerModalPurchaseLabel"><i class="ti ti-file-text me-2"></i>Supplier Ledger Report</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="supplierLedgerModalPurchaseBody">
                <div class="ledger-date-range-bar mb-3 p-2 bg-light rounded d-flex flex-wrap align-items-center gap-2">
                    <span class="fw-semibold small">Date range:</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="ledger_last_week_btn" title="Load ledger for the last 7 days">Last Week</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="ledger_last_month_btn" title="Load ledger for the previous month">Last Month</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="ledger_last_tally_btn" title="From last tally date to today">Last Tally</button>
                    <label class="mb-0 small ms-1">From</label>
                    <input type="date" id="ledger_date_from" class="form-control form-control-sm" style="max-width: 140px;">
                    <label class="mb-0 small">To</label>
                    <input type="date" id="ledger_date_to" class="form-control form-control-sm" style="max-width: 140px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="ledger_clear_btn" title="Reset to default date range"><i class="ti ti-filter-off me-1"></i>Clear</button>
                </div>
                <div id="ledger-report-content">
                    <div class="text-center p-4 text-muted small">Select date range or use quick range. Report loads automatically.</div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="supplierLedgerPdfLink" class="btn btn-outline-primary btn-sm me-auto" target="_blank" style="display: none;"><i class="ti ti-download me-1"></i>Download PDF</a>
                <button type="button" class="btn btn-success" id="supplierLedgerWhatsAppBtn" style="display: none;"><i class="ti ti-brand-whatsapp me-1"></i>Send via WhatsApp</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Ledger Tally Modal: attach image to link this row with another ledger -->
<div class="modal fade" id="ledgerReconcileModal" tabindex="-1" aria-labelledby="ledgerReconcileModalLabel" aria-hidden="true" style="z-index: 10053;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ledgerReconcileModalLabel"><i class="ti ti-link me-2"></i>Tally Ledger Row</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Attach an image to link this ledger row with another ledger (e.g. supplier copy). After saving, this row will be highlighted with your name and date/time.</p>
                <input type="hidden" id="reconcile_purchase_id" value="">
                <input type="hidden" id="reconcile_payment_id" value="">
                <input type="hidden" id="reconcile_balance_at" value="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Image <span class="text-danger">*</span></label>
                    <input type="file" id="reconcile_image_input" class="form-control" accept="image/*">
                    <div class="form-text">JPEG, PNG, GIF or WebP, max 5MB</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="ledgerReconcileSaveBtn"><i class="ti ti-device-floppy me-1"></i>Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Claim In History Modal (only Claim In records) -->
<div class="modal fade" id="claim-stock-detail-modal" tabindex="-1" aria-labelledby="claim-stock-detail-modal-label" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true" style="pointer-events: auto; z-index: 10054;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="claim-stock-detail-modal-label"><i class="ti ti-package me-2"></i>Claim In History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="claim-stock-detail-loading" class="text-center py-4">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <p class="mb-0 text-muted small">Loading claim stock history...</p>
                </div>
                <div id="claim-stock-detail-content" class="d-none">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                        <div class="small text-muted"><span id="claim-stock-detail-scope"></span></div>
                        <div class="d-flex flex-wrap gap-3 small">
                            <div><strong>Total Claim Received:</strong> <span id="claim-stock-total-in">0</span></div>
                            <div><strong>Total Claim Sent:</strong> <span id="claim-stock-total-sent">0</span></div>
                            <div><strong>Current Claim Stock:</strong> <span id="claim-stock-current">0</span></div>
                        </div>
                    </div>
                    <!-- Date range filter removed (From / To / Clear) -->
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 120px;">Date/Time</th>
                                    <th>Item</th>
                                    <th style="min-width: 160px;">Warehouse/Branch</th>
                                    <th class="text-end" style="min-width: 90px;">Qty</th>
                                    <th style="min-width: 120px;">Type</th>
                                    <th style="min-width: 120px;">Ref</th>
                                            <th style="min-width: 170px;">
                                                <span id="claim-stock-select-all-label">SELECT</span>
                                                <input type="checkbox"
                                                       id="claim-stock-select-all-checkbox"
                                                       class="form-check-input ms-2"
                                                       style="margin-top: 0.15rem;"
                                                       title="Select all">
                                            </th>
                                </tr>
                            </thead>
                            <tbody id="claim-stock-detail-tbody"></tbody>
                        </table>
                    </div>
                    <p id="claim-stock-detail-empty" class="text-muted small text-center py-3 d-none">No claim in history found for the selected scope.</p>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end gap-2 w-100">
                <button type="button" class="btn btn-primary" id="purchase-claim-stock-detail-load-btn">Load</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Claim Send History Modal (only Claim Send / Claim Out records) -->
<div class="modal fade" id="claim-send-stock-detail-modal" tabindex="-1" aria-labelledby="claim-send-stock-detail-modal-label" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true" style="pointer-events: auto; z-index: 10055;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="claim-send-stock-detail-modal-label"><i class="ti ti-truck-delivery me-2"></i>Claim Send History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="claim-send-stock-detail-loading" class="text-center py-4">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <p class="mb-0 text-muted small">Loading claim send history...</p>
                </div>
                <div id="claim-send-stock-detail-content" class="d-none">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                        <div class="small text-muted"><span id="claim-send-stock-detail-scope"></span></div>
                        <div class="d-flex flex-wrap gap-3 small">
                            <div><strong>Total Claim Received:</strong> <span id="claim-send-stock-total-in">0</span></div>
                            <div><strong>Total Claim Sent:</strong> <span id="claim-send-stock-total-sent">0</span></div>
                            <div><strong>Current Claim Stock:</strong> <span id="claim-send-stock-current">0</span></div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 120px;">Date/Time</th>
                                    <th>Item</th>
                                    <th style="min-width: 160px;">Warehouse/Branch</th>
                                    <th class="text-end" style="min-width: 90px;">Qty</th>
                                    <th style="min-width: 120px;">Type</th>
                                    <th style="min-width: 120px;">Ref</th>
                                    <th style="min-width: 170px;">
                                        <span id="claim-send-stock-select-all-label">SELECT</span>
                                        <input type="checkbox"
                                               id="claim-send-stock-select-all-checkbox"
                                               class="form-check-input ms-2"
                                               style="margin-top: 0.15rem;"
                                               title="Select all">
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="claim-send-stock-detail-tbody"></tbody>
                        </table>
                    </div>
                    <p id="claim-send-stock-detail-empty" class="text-muted small text-center py-3 d-none">No claim send history found for the selected scope.</p>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end gap-2 w-100">
                <button type="button" class="btn btn-primary" id="purchase-claim-send-stock-detail-load-btn">Load</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Unified Claim History Modal -->
<div class="modal fade" id="purchase-claim-history-modal" tabindex="-1" aria-labelledby="purchase-claim-history-modal-label" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true" style="pointer-events: auto; z-index: 10056;">
    <style>
        /* Ensure action buttons are never blocked by table overlays */
        #purchase-claim-history-modal .purchase-claim-reverse-btn {
            pointer-events: auto !important;
            position: relative;
            z-index: 5;
        }
        #purchase-claim-history-modal .purchase-claim-history-table td,
        #purchase-claim-history-modal .purchase-claim-history-table th {
            position: relative;
        }
    </style>
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purchase-claim-history-modal-label"><i class="ti ti-history me-2"></i>Claim History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="small text-muted" id="purchase-claim-history-scope">Branch</div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" class="form-control form-control-sm" id="purchase-claim-send-note" placeholder="Status note for new claim send..." style="min-width: 260px;">
                        <button type="button" class="btn btn-sm btn-primary" id="purchase-claim-send-new-btn">
                            <i class="ti ti-send me-1"></i>Send New Claim
                        </button>
                    </div>
                    <ul class="nav nav-pills gap-2" id="purchase-claim-history-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-claim-tab="in" type="button">Claim In (<span id="purchase-claim-tab-count-in">0</span>)</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-claim-tab="sent" type="button">Claim Sent (<span id="purchase-claim-tab-count-sent">0</span>)</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-claim-tab="reverse" type="button">Claim Reverse (<span id="purchase-claim-tab-count-reverse">0</span>)</button>
                        </li>
                    </ul>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">From Date</label>
                        <input type="date" class="form-control form-control-sm" id="purchase-claim-filter-from">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">To Date</label>
                        <input type="date" class="form-control form-control-sm" id="purchase-claim-filter-to">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Claim ID / Ref</label>
                        <input type="text" class="form-control form-control-sm" id="purchase-claim-filter-id" placeholder="Invoice / Reference / Claim ID">
                    </div>
                    <div class="col-md-2 d-flex align-items-end flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" id="purchase-claim-filter-clear">Clear</button>
                        <div class="form-check mb-0 d-flex align-items-center" title="Select all visible rows">
                            <input class="form-check-input" type="checkbox" id="purchase-claim-history-select-all">
                            <label class="form-check-label small text-muted ms-1 mb-0" for="purchase-claim-history-select-all">All</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="small text-muted fw-semibold">Aging Report</span>
                        <span class="badge bg-success-subtle text-success-emphasis">Open: <span id="purchase-claim-aging-open-count">0</span></span>
                        <span class="badge bg-warning-subtle text-warning-emphasis">Near Due: <span id="purchase-claim-aging-near-count">0</span></span>
                        <span class="badge bg-danger-subtle text-danger-emphasis">Overdue: <span id="purchase-claim-aging-overdue-count">0</span></span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <label class="small text-muted mb-0">Alert After (days)</label>
                        <input type="number" min="1" max="365" step="1" id="purchase-claim-aging-threshold" class="form-control form-control-sm" style="width: 88px;" value="7">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" id="purchase-claim-overdue-only">
                            <label class="form-check-label small text-muted" for="purchase-claim-overdue-only">Overdue only</label>
                        </div>
                    </div>
                </div>
                <div class="row g-2 mb-3" id="purchase-claim-trend-summary">
                    <div class="col-md-3"><div class="p-2 rounded border bg-light small">Total In: <strong id="purchase-claim-trend-total-in">0</strong></div></div>
                    <div class="col-md-3"><div class="p-2 rounded border bg-light small">Total Sent: <strong id="purchase-claim-trend-total-sent">0</strong></div></div>
                    <div class="col-md-3"><div class="p-2 rounded border bg-light small">Reversed: <strong id="purchase-claim-trend-total-reversed">0</strong></div></div>
                    <div class="col-md-3"><div class="p-2 rounded border bg-light small">Pending Exposure: <strong id="purchase-claim-trend-exposure">0</strong></div></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover align-middle mb-0 purchase-claim-history-table">
                        <thead class="table-light">
                            <tr class="small text-uppercase text-muted">
                                <th style="min-width: 118px;">Date / time</th>
                                <th style="min-width: 140px;">Item</th>
                                <th style="min-width: 150px;">Warehouse / branch</th>
                                <th class="text-end" style="min-width: 72px;">Qty</th>
                                <th style="min-width: 128px;">Claim ref</th>
                                <th style="min-width: 88px;">Age</th>
                                <th style="min-width: 112px;">Alert</th>
                                <th style="min-width: 180px;">Status</th>
                                <th style="min-width: 200px;">Traceability</th>
                                <th style="min-width: 220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-claim-history-tbody"></tbody>
                    </table>
                </div>
                <p id="purchase-claim-history-empty" class="text-muted small text-center py-3 d-none">No records found for selected filters.</p>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <button type="button" class="btn btn-primary" id="purchase-claim-history-load-btn">
                    <i class="ti ti-download me-1"></i><span id="purchase-claim-history-load-btn-label">Load selected to purchase</span>
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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
                            <div class="input-group input-group-sm">
                                <input type="text" id="barcode-scan-input" class="form-control form-control-sm" placeholder="Scan or type barcode..." autocomplete="off" style="border-radius: 8px 0 0 8px;">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-item-modal-camera-btn" title="Camera se barcode scan karein" style="border-radius: 0 8px 8px 0;"><i class="ti ti-camera me-1"></i> Camera</button>
                            </div>
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
                            <div id="selected-item-quality-wrap" class="mt-1 mb-1 d-none"></div>
                            <div class="small text-primary fw-semibold mb-1" id="selected-item-details-line2" style="display: none;"></div>
                            <div class="text-primary small fw-semibold" id="selected-item-details-line3"></div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-shrink-0" id="item-edit-in-modal-btn" title="Edit item" style="display: none;">
                            <i class="ti ti-edit me-1"></i>Edit
                        </button>
                    </div>
                    <input type="hidden" id="selected-item-id">
                    <input type="hidden" id="selected-item-is-temporary" value="0">
                    <input type="hidden" id="selected-item-bar-code" value="">
                    <input type="hidden" id="selected-item-company" value="">
                    <input type="hidden" id="selected-item-image" value="">
                    <input type="hidden" id="selected-item-images-json" value="[]">
                    <input type="hidden" id="selected-item-voice-url" value="">
                    <input type="hidden" id="selected-item-master-sale-price" value="">
                    <input type="hidden" id="selected-item-category-name" value="">
                    <input type="hidden" id="selected-item-type" value="">
                    <input type="hidden" id="selected-item-quality-name" value="">
                    <input type="hidden" id="selected-item-part-number" value="">
                    <input type="hidden" id="selected-item-product-title" value="">
                    <input type="hidden" id="selected-item-product-type-label" value="">
                    <input type="hidden" id="selected-item-technology-name" value="">
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
                        <div id="stock-status-list-available-total" class="d-flex py-1 small text-muted mt-0 pt-0" style="display: none;">Available — <span id="stock-status-list-available-total-text">0</span></div>
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
                            <div id="item-per-liter-wrap" class="mt-2 d-none">
                                <label class="form-label small fw-semibold mb-1 text-muted">Per liter (Rs) <span class="fw-normal">— oil</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0">Rs</span>
                                    <input type="number" id="item-per-liter-rate" class="form-control no-number-spinner border-start-0" value="" step="0.01" min="0" placeholder="Auto" style="border-radius: 0 8px 8px 0;">
                                </div>
                                <small class="text-muted">Can rate ÷ liters per can; edit for liter-wise purchase</small>
                            </div>
                            <input type="hidden" id="selected-item-is-oil" value="">
                            <input type="hidden" id="selected-item-liter-per-can" value="">
                        </div>
                        @php $canEditRetailGstRtax = auth()->check() && auth()->user()->role === 'admin'; @endphp
                        <div class="col-md-4" id="item-retail-price-column" data-admin-only-edit="{{ $canEditRetailGstRtax ? '1' : '0' }}">
                            <label class="form-label fw-bold mb-1 add-item-retail-label">Retail price (Rs)</label>
                            <div class="add-item-retail-box rounded-3 border add-item-retail-box-compact">
                                <div class="add-item-retail-input-row">
                                    <span class="add-item-retail-rs">Rs</span>
                                    <input type="number" id="item-retail-price" class="form-control form-control-sm no-number-spinner" value="" step="1" min="0" placeholder="—" @if(!$canEditRetailGstRtax) readonly tabindex="-1" @endif title="{{ $canEditRetailGstRtax ? 'Item retail price (admin can edit)' : 'Item retail price (from item master)' }}">
                                    <select id="item-tax-percent" class="form-select form-select-sm add-item-gst-select" title="GST %" @if(!$canEditRetailGstRtax) disabled @endif>
                                        <option value="12">12%</option>
                                        <option value="13">13%</option>
                                        <option value="14">14%</option>
                                        <option value="15">15%</option>
                                        <option value="16">16%</option>
                                        <option value="18" selected>18%</option>
                                    </select>
                                    <span class="add-item-rtax-wrap">
                                        <span class="add-item-rtax-label">R.Tax</span>
                                        <input type="number" id="item-rtax-percent" class="form-control form-control-sm add-item-rtax-input" value="0.5" step="0.01" min="0" max="100" title="R.Tax % ({{ $canEditRetailGstRtax ? 'editable' : 'admin only' }})" aria-label="R.Tax %" @if(!$canEditRetailGstRtax) readonly @endif>
                                        <span class="add-item-rtax-pct">%</span>
                                    </span>
                                </div>
                                <div class="add-item-sell-row">
                                    <div class="add-item-sell-label-wrap">
                                        <span class="add-item-sell-label">Sell price</span>
                                        <span class="add-item-sell-hint">Base + GST + R.Tax</span>
                                    </div>
                                    <span class="add-item-sell-value" id="item-retail-after-calc">—</span>
                                </div>
                                <div class="add-item-pct-row">
                                    <label class="add-item-pct-label" for="item-retail-percentage">Adjust by %</label>
                                    <select id="item-retail-percentage" class="form-select form-select-sm retail-pct-select" title="Adjust sell price by percentage">
                                        <option value="" selected data-pct-type="zero">—</option>
                                        @for($p = -25; $p <= 25; $p++)
                                            <option value="{{ $p }}" data-pct-type="{{ $p < 0 ? 'minus' : ($p == 0 ? 'zero' : 'plus') }}">{{ $p }}%</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4" id="purchase-item-warranty-section" style="display: none;">
                            <label class="form-label small fw-semibold mb-1">Warranty</label>
                            <div class="d-flex flex-column gap-2">
                                <div>
                                    <label class="form-label small text-muted mb-0" style="font-size: 0.7rem;">Duration</label>
                                    <select id="warranty-value" class="form-select form-select-sm w-100" style="border-radius: 8px;" title="Number (e.g. 6)">
                                        <option value="">—</option>
                                        @for($i = 1; $i <= 30; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label small text-muted mb-0" style="font-size: 0.7rem;">Unit</label>
                                    <select id="warranty-unit" class="form-select form-select-sm w-100" style="border-radius: 8px;" title="Days, Weeks, Months or Years">
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

<!-- Item image full view modal (click thumbnail — all attached photos; voice optional) -->
<div class="modal fade" id="item-image-view-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true" style="z-index: 10060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="item-image-view-modal-title"><i class="ti ti-photo me-2"></i>Item photos</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div id="item-image-gallery-nav" class="d-flex align-items-center justify-content-between gap-2 mb-2 d-none">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="item-image-gallery-prev" aria-label="Previous">&larr; Prev</button>
                    <span id="item-image-gallery-counter" class="small text-muted"></span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="item-image-gallery-next" aria-label="Next">Next &rarr;</button>
                </div>
                <div class="text-center mb-2">
                    <img id="item-image-view-full" src="" alt="" class="img-fluid rounded shadow-sm mx-auto d-block" style="max-height: 65vh; object-fit: contain;">
                </div>
                <div id="item-image-gallery-thumbs" class="d-flex flex-wrap justify-content-center gap-2 pt-2 border-top d-none"></div>
                <div id="item-image-view-voice-wrap" class="border-top pt-3 mt-2 text-center d-none">
                    <span class="small text-muted d-block mb-1">Voice note</span>
                    <audio id="item-image-view-voice-audio" controls preload="metadata" class="w-100 mx-auto" style="max-width: 420px; height: 40px;" controlsList="nodownload"></audio>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Attach Photo/Receipt image preview modal (full size) -->
<div class="modal fade" id="purchase-attach-image-modal" tabindex="-1" aria-hidden="true" aria-labelledby="purchase-attach-image-modal-title" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="purchase-attach-image-modal-title"><i class="ti ti-photo me-2"></i>Preview</h6>
                <button type="button" class="btn-close purchase-attach-image-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <img id="purchase-attach-image-full" src="" alt="Preview" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain;">
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
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label small mb-0 text-muted">Print Size:</label>
                            <select id="label-print-size-select" class="form-select form-select-sm" style="width: 160px;">
                                <option value="">Loading…</option>
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="label-print-manage-sizes-btn">
                                <i class="ti ti-settings me-1"></i>Manage
                            </button>
                        </div>
                        <div id="label-print-qty-wrap" class="d-none d-flex align-items-center gap-2">
                            <label class="form-label small mb-0 text-muted">Quantity:</label>
                            <input type="number" id="label-print-qty-input" class="form-control form-control-sm no-number-spinner" min="1" max="500" value="1" style="width: 80px;" title="Labels ki tadad change karein">
                        </div>
                        <div class="form-check form-switch mb-0 ms-2">
                            <input class="form-check-input" type="checkbox" id="label-print-show-price" title="Label par price dikhayein">
                            <label class="form-check-label small text-muted" for="label-print-show-price">Show price</label>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                        <button type="button" class="btn btn-primary" id="label-print-modal-print-btn"><i class="ti ti-printer me-1"></i>Print</button>
                    </div>
                </div>
                <div id="label-print-modal-content" class="p-4 bg-white" style="min-height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

<style id="label-print-dynamic-style"></style>

<!-- Manage Label Print Sizes -->
<div class="modal fade" id="label-print-sizes-modal" tabindex="-1" aria-hidden="true" style="z-index: 10061;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="ti ti-ruler-2 me-2"></i>Manage Label Print Sizes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Size</th>
                                        <th>Unit</th>
                                        <th>Default</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="label-print-sizes-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="fw-bold">Add / Edit preset</div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="label-print-sizes-form-reset">Reset</button>
                            </div>
                            <input type="hidden" id="lp-preset-id" value="">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <label class="form-label small mb-1">Preset name</label>
                                    <input type="text" class="form-control form-control-sm" id="lp-name" placeholder="e.g. 2×1 Label">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Unit</label>
                                    <select class="form-select form-select-sm" id="lp-unit">
                                        <option value="in">inch</option>
                                        <option value="mm">mm</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Width</label>
                                    <input type="number" step="0.01" min="0.1" class="form-control form-control-sm" id="lp-width">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Height</label>
                                    <input type="number" step="0.01" min="0.1" class="form-control form-control-sm" id="lp-height">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" id="lp-default">
                                        <label class="form-check-label small" for="lp-default">Default</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Padding</label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="lp-padding" placeholder="0.08">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Barcode height</label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="lp-barcode-height" placeholder="0.35">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Font L1</label>
                                    <input type="number" step="1" min="6" class="form-control form-control-sm" id="lp-font-line1" placeholder="14">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Font L2</label>
                                    <input type="number" step="1" min="6" class="form-control form-control-sm" id="lp-font-line2" placeholder="12">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Font Rate</label>
                                    <input type="number" step="1" min="6" class="form-control form-control-sm" id="lp-font-rate" placeholder="11">
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-primary btn-sm" id="label-print-sizes-save">
                                    <i class="ti ti-device-floppy me-1"></i>Save preset
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="label-print-sizes-delete" disabled>
                                    <i class="ti ti-trash me-1"></i>Delete preset
                                </button>
                            </div>
                            <div class="small text-muted mt-2">Tip: Use inches for most thermal printers (2×1, 2×4).</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Camera barcode modal above add-item modal when opened from Add Item */
    #camera-barcode-modal.modal.show { z-index: 10000 !important; }
    #camera-barcode-modal .modal-dialog,
    #camera-barcode-modal .modal-content { pointer-events: auto !important; }

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

    /* Add Supplier modal – fixed height, scrollable body, fixed header/footer (only this modal) */
    #addSupplierModal .modal-dialog.add-supplier-modal-scroll {
        max-height: 85vh !important;
        display: flex !important;
        flex-direction: column !important;
        margin: 1rem auto !important;
    }
    #addSupplierModal .modal-content.add-supplier-modal-content {
        max-height: 85vh !important;
        display: flex !important;
        flex-direction: column !important;
    }
    #addSupplierModal .modal-header {
        flex-shrink: 0 !important;
    }
    #addSupplierModal .modal-footer {
        flex-shrink: 0 !important;
    }
    #addSupplierModal .modal-body {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scroll-behavior: smooth !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: thin !important;
        scrollbar-color: rgba(0, 0, 0, 0.25) transparent !important;
    }
    #addSupplierModal .modal-body::-webkit-scrollbar { width: 8px !important; }
    #addSupplierModal .modal-body::-webkit-scrollbar-track { background: transparent !important; border-radius: 4px !important; }
    #addSupplierModal .modal-body::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.2) !important; border-radius: 4px !important; }
    #addSupplierModal .modal-body::-webkit-scrollbar-thumb:hover { background: rgba(0, 0, 0, 0.35) !important; }
    #addSupplierModal .modal-body::-webkit-scrollbar-thumb:active { background: rgba(0, 0, 0, 0.45) !important; }

    /* Add Supplier modal – solid white background, no transparent/crystal/blur inside */
    #addSupplierModal .modal-content,
    #addSupplierModal .modal-content.add-supplier-modal-content {
        background-color: #ffffff !important;
        opacity: 1 !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    #addSupplierModal .modal-header,
    #addSupplierModal .modal-body,
    #addSupplierModal .modal-footer {
        background-color: #ffffff !important;
        opacity: 1 !important;
    }
    #addSupplierModal .modal-body .row,
    #addSupplierModal .modal-body [class*="col-"] {
        background-color: #ffffff !important;
    }
    #addSupplierModal .form-control,
    #addSupplierModal .form-select,
    #addSupplierModal .input-group {
        background-color: #ffffff !important;
    }

    /* Products / Business Detail (Add Supplier modal) */
    .business-detail-tag-container { position: relative; }
    .business-detail-suggestions {
        position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #dee2e6;
        border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); max-height: 250px; overflow-y: auto;
        z-index: 1000; margin-top: 2px; display: none;
    }
    .business-detail-suggestions.show { display: block; }
    .business-detail-suggestion-item { display: flex; align-items: center; padding: 0.75rem; cursor: pointer; border-bottom: 1px solid #f0f0f0; }
    .business-detail-suggestion-item:hover { background-color: #f8f9fa; }
    .business-detail-suggestion-item.selected { background-color: #e7f3ff; }
    .business-detail-suggestion-loading { padding: 0.5rem 0.75rem; text-align: center; color: #6c757d; font-size: 0.875rem; }
    .business-detail-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; min-height: 20px; }
    .business-detail-tag {
        display: inline-flex; align-items: center; padding: 0.375rem 0.75rem; background-color: #0d6efd; color: #fff;
        border-radius: 0.375rem; font-size: 0.875rem; gap: 0.5rem;
    }
    .business-detail-tag .tag-remove { cursor: pointer; font-weight: bold; opacity: 0.8; font-size: 16px; line-height: 1; }

    .label-print-sheet { display: flex; flex-wrap: wrap; gap: 8px; background: #fff; padding: 16px; border-radius: 8px; }
    .label-print-item {
        width: var(--lp-width, 2in);
        min-width: var(--lp-width, 2in);
        max-width: var(--lp-width, 2in);
        height: var(--lp-height, 1in);
        min-height: var(--lp-height, 1in);
        max-height: var(--lp-height, 1in);
        padding: var(--lp-padding, 0.08in);
        border: 1px solid #e5e7eb;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        font-size: 11px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        overflow: hidden;
        background: #fff;
        text-align: center;
        box-sizing: border-box;
    }
    .label-print-head {
        align-self: stretch; width: 100%; max-width: 100%;
        flex-shrink: 1;
        min-height: 0;
        overflow: hidden;
    }
    /* Top row: [ATLAS left] [Rs center] [A+ right] — same line as sample label */
    .label-print-line1-row--triple {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        align-items: start;
        gap: 4px;
        width: 100%;
        max-width: 100%;
        margin-bottom: 1px;
    }
    .label-print-line1-row--single {
        width: 100%;
        max-width: 100%;
        margin-bottom: 2px;
    }
    .label-print-line1-row--single .label-print-line1-text {
        display: block;
        text-align: left;
    }
    .label-print-line1-text {
        font-weight: 700; font-size: var(--lp-font-line1, 14px); line-height: 1.2;
        text-transform: uppercase; letter-spacing: 0.02em;
        justify-self: start;
        text-align: left;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .label-print-line1-center {
        justify-self: center;
        text-align: center;
        font-size: var(--lp-font-rate, 11px);
        font-weight: 600;
        line-height: 1.2;
        color: #333;
        white-space: nowrap;
        padding: 0 2px;
    }
    .label-print-line1-center--empty { padding: 0; min-width: 0; }
    .label-print-line1-qty {
        font-weight: 800; font-size: var(--lp-font-line1, 14px); line-height: 1;
        justify-self: end;
        text-align: right;
        white-space: nowrap;
        letter-spacing: 0.02em;
        transform: translateX(-3px);
    }
    .label-print-line1-qty--empty { visibility: hidden; pointer-events: none; }
    .label-print-line1-qty .lp-grade-a { font-weight: 800; }
    .label-print-line1-qty .lp-plus { font-size: 0.82em; vertical-align: 0.12em; margin-left: 0.02em; font-weight: 900; }
    .label-print-line1-quality { max-width: 100%; overflow: hidden; text-overflow: ellipsis; color: #f97316; font-weight: 800; }
    /* Quality badge (e.g. CHINA): compact, top-right — does not affect barcode */
    .label-print-line1-qty.label-print-line1-quality {
        transform: none;
        font-weight: 600;
        font-size: 10px;
        line-height: 1.2;
        letter-spacing: 0.03em;
        padding: 2px 6px;
        border-radius: 4px;
        color: #f97316;
        background: rgba(249, 115, 22, 0.08);
        justify-self: end;
        align-self: start;
    }
    .label-print-line1-text .label-print-line1-brand-stack {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        max-width: 100%;
        white-space: normal;
    }
    .label-print-line1-row--triple .label-print-line1-text:has(.label-print-line1-brand-stack) {
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
    }
    .label-print-line1-brand-stack .label-print-line1-co {
        font-size: 10px;
        font-weight: 500;
        color: #6b7280;
        line-height: 1.15;
        letter-spacing: 0.02em;
    }
    .label-print-line1-brand-stack .label-print-line1-cat {
        font-size: 9px;
        font-weight: 500;
        color: #6b7280;
        line-height: 1.1;
        opacity: 0.95;
    }
    .label-print-line2 {
        font-size: var(--lp-font-line2, 12px); line-height: 1.2; margin-top: 1px; margin-bottom: 0; color: #374151;
        font-weight: 700;
        flex-shrink: 1;
        min-height: 0;
        width: 100%; max-width: 100%; overflow: hidden; text-overflow: ellipsis;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; white-space: normal;
        text-align: center;
    }
    .label-print-line2--part-hero {
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        letter-spacing: 0.02em;
        line-height: 1.15;
        -webkit-line-clamp: 2;
    }
    .label-print-barcode-wrap {
        margin-top: auto;
        flex: 0 0 auto;
        flex-shrink: 0;
        min-width: 0;
        width: 100%;
        max-width: 100%;
        align-self: stretch;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
        gap: 2px;
        overflow: visible;
        min-height: 28px;
        padding-top: 0;
    }
    .label-print-barcode-clip {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: flex-end;
        min-height: 24px;
        flex: 0 0 auto;
    }
    /* Barcode SVG: intrinsic width/height come from JsBarcode only — do not set fixed height/width that rescale bars */
    .label-print-barcode-wrap .label-print-barcode {
        display: block;
        max-width: 100%;
        width: auto;
        height: auto;
        flex-shrink: 1;
        min-height: 0;
        min-width: 0;
        max-height: calc(100% - 11px);
        object-fit: contain;
        vertical-align: bottom;
    }
    .label-print-barcode-caption {
        font-size: 8px; font-weight: 600; line-height: 1.1; font-family: ui-monospace, 'Cascadia Mono', Consolas, monospace;
        color: #111; max-width: 100%; text-align: center; padding: 0 1px; flex-shrink: 0;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    #label-print-modal-content.label-print-hide-price .label-print-line1-center:not(.label-print-line1-center--empty) { display: none !important; }
    @media print {
        /* Keep browser page auto-sized; labels keep physical size via inch units */
        @page { size: auto; margin: 0; }
        html, body { margin: 0 !important; padding: 0 !important; }
        body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

        /* Print ONLY the label area (prevents blank pages / printing whole DOM) */
        body * { visibility: hidden !important; }
        #label-print-print-area, #label-print-print-area * { visibility: visible !important; }
        #label-print-print-area { position: absolute !important; top: 0 !important; left: 0 !important; width: 100% !important; height: auto !important; }

        .label-print-sheet {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            align-content: flex-start !important;
            justify-content: flex-start !important;
            width: auto !important;
        }
        .label-print-item {
            width: var(--lp-width, 2in) !important;
            min-width: var(--lp-width, 2in) !important;
            max-width: var(--lp-width, 2in) !important;
            height: var(--lp-height, 1in) !important;
            min-height: var(--lp-height, 1in) !important;
            max-height: var(--lp-height, 1in) !important;
            padding: var(--lp-padding, 0.08in) !important;
            border: none !important;
            margin: 0 !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .label-print-line1-text { font-weight: 700; }
        .label-print-line1-brand-stack .label-print-line1-co,
        .label-print-line1-brand-stack .label-print-line1-cat { font-weight: 500 !important; color: #6b7280 !important; }
        .label-print-line2 { font-weight: 700; }
        .label-print-line2--part-hero { font-weight: 600 !important; color: #111827 !important; }
        .label-print-line1-qty { font-weight: 800; transform: translateX(-3px); }
        .label-print-line1-qty.label-print-line1-quality { font-weight: 600 !important; transform: none !important; }
        .label-print-barcode-wrap { overflow: visible !important; }
        .label-print-barcode-clip { overflow: hidden !important; max-width: 100% !important; }
        #label-print-view-modal .modal-header,
        #label-print-view-modal .d-flex.justify-content-between.px-3 { display: none !important; }
        #label-print-qty-wrap { display: none !important; }
        #label-print-view-modal .modal-body { padding: 0 !important; }
        #label-print-modal-content.label-print-hide-price .label-print-line1-center:not(.label-print-line1-center--empty) { display: none !important; }
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
    /* Barcode row: same blue action style, compact pill next to Temporary Products */
    .btn-action-purchase-barcode-toolbar {
        padding: 8px 20px !important;
        font-size: 0.8125rem !important;
        font-weight: 700 !important;
        border-radius: 999px !important;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        box-shadow: 0 2px 12px rgba(13, 110, 253, 0.35);
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
    #purchase-total-below .total-below-purchase { color: #1e293b !important; font-weight: 600; padding-bottom: 0.5rem; margin-bottom: 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.12); }
    #purchase-total-below .total-below-purchase-label { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 0.375rem; background-color: #0d6efd; color: #fff !important; font-size: 0.75rem; font-weight: 600; min-width: 4em; text-align: center; letter-spacing: 0.02em; }
    #purchase-total-below #claim-totals-below-container .total-below-row { font-weight: 600; padding-bottom: 0.5rem; margin-bottom: 0.5rem; border-bottom: 1px solid rgba(0,0,0,0.12); }
    #purchase-total-below #claim-totals-below-container .total-below-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    #purchase-total-below .total-below-return { color: #fd7e14 !important; }
    #purchase-total-below .total-below-send-claim { color: #6f42c1 !important; }
    #purchase-total-below .total-below-damage { color: #dc3545 !important; }
    #purchase-total-below .total-below-scrap { color: #dc3545 !important; }
    .purchase-scrap-picker-table th { font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; white-space: nowrap; }
    .purchase-scrap-picker-table .purchase-scrap-rate-cell { min-width: 120px; }
    .purchase-scrap-picker-table .purchase-scrap-rate-main { display: block; font-weight: 800; font-size: 0.95rem; color: #0c4a6e; font-variant-numeric: tabular-nums; letter-spacing: -0.02em; }
    .purchase-scrap-picker-table .purchase-scrap-rate-sub { font-size: 10px; color: #64748b; margin-top: 2px; }
    .purchase-scrap-picker-table .purchase-scrap-qty-input { max-width: 92px; margin-left: auto; }
    .purchase-scrap-picker-table .purchase-scrap-item-meta { font-size: 11px; line-height: 1.45; }
    /* SCRAP SEND: same as #scrap-send-btn (.btn-action-damage) — scrap picker rows + bill line badges */
    .purchase-scrap-send-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #fff !important;
        border: none;
        border-radius: 10px;
        white-space: nowrap;
        background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
        box-shadow: 0 2px 10px rgba(220, 53, 69, 0.35);
    }
    .purchase-scrap-picker-table .purchase-scrap-send-badge {
        font-size: 0.65rem;
        padding: 0.35rem 0.65rem;
        line-height: 1.1;
    }
    #items-tbody .purchase-scrap-send-badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        min-width: 5.5em;
        line-height: 1.2;
    }
    .purchase-scrap-picker-table tbody tr:hover .purchase-scrap-send-badge,
    #items-tbody tr.purchase-item-row:hover .purchase-scrap-send-badge {
        background: linear-gradient(135deg, #bb2d3b 0%, #942a32 100%);
        box-shadow: 0 3px 12px rgba(220, 53, 69, 0.45);
    }
    .purchase-scrap-picker-table tbody tr { border-left: 3px solid transparent; }
    .purchase-scrap-picker-table tbody tr:hover { border-left-color: #94a3b8; background-color: #fafafa; }
    .purchase-scrap-picker-qty-hint { font-size: 10px; color: #b45309; min-height: 1rem; }
    @include('admin.partials.payment-panel-totals-styles')

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
    #items-tbody tr.purchase-item-row.scan-highlight {
        box-shadow: inset 0 0 0 2px #f59e0b;
        background-color: #fff3cd !important;
        border-left: 4px solid #f59e0b;
        animation: purchaseScanPulse 1.15s ease-in-out 2;
        position: relative;
        z-index: 1;
    }
    #items-tbody tr.purchase-item-row.scan-highlight .purchase-row-item-name,
    #items-tbody tr.purchase-item-row.scan-highlight .purchase-row-qty-unit-line {
        color: #7c2d12 !important;
    }
    #items-tbody tr.purchase-item-row.manual-selected-row {
        box-shadow: inset 0 0 0 2px #0d6efd;
        background-color: #e7f1ff !important;
        border-left: 4px solid #0d6efd;
    }
    @keyframes purchaseScanPulse {
        0% { filter: brightness(1); }
        50% { filter: brightness(1.16); }
        100% { filter: brightness(1); }
    }

    /* Item photo gallery modal + row voice: keep audio in table cell; modal above page chrome */
    #item-image-view-modal { z-index: 10060 !important; }
    #items-tbody .purchase-row-voice-wrap {
        position: relative;
        contain: layout;
        max-width: 100%;
    }
    #items-tbody .purchase-row-voice-wrap audio {
        display: block;
        margin-left: auto;
        margin-right: auto;
        max-width: min(100%, 280px);
    }

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
    /* Selected product line1: filters/parts/breakpad — brand+CAT left, part # center (hero), quality right */
    #add-item-modal #selected-item-details-line1.selected-product-line1--segments {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.65rem 0.75rem;
        width: 100%;
        font-size: 1.05rem;
        line-height: 1.35;
        letter-spacing: 0.03em;
    }
    #add-item-modal .selected-product-line1-brand {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.35rem;
        flex-shrink: 0;
        max-width: 38%;
    }
    #add-item-modal .selected-product-line1-brand .selected-product-seg--company {
        font-size: 1.05rem;
    }
    #add-item-modal .selected-product-line1-brand .selected-product-seg--category {
        font-size: 0.88rem;
        font-weight: 700;
        padding: 0.3rem 0.55rem;
    }
    #add-item-modal .selected-product-line1-part {
        flex: 1 1 0;
        min-width: 8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        align-self: center;
        text-align: center;
    }
    #add-item-modal .selected-product-line1-right {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: flex-end;
        flex-shrink: 0;
        text-align: right;
    }
    #add-item-modal .selected-product-seg {
        display: inline-block;
        font-weight: 700;
        font-size: 1.05rem;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        text-transform: uppercase;
        line-height: 1.2;
    }
    #add-item-modal .selected-product-seg--part {
        background: rgba(37, 99, 235, 0.14);
        color: #1d4ed8;
        border: 1px solid rgba(37, 99, 235, 0.28);
    }
    #add-item-modal .selected-product-seg--part.selected-product-seg--hero {
        font-size: 1.45rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        padding: 0.45rem 0.9rem;
        background: #e8eef7;
        color: #1e3a5f;
        border: 1px solid #c5d4e8;
    }
    #add-item-modal .selected-product-seg--quality {
        background: rgba(249, 115, 22, 0.16);
        color: #c2410c;
        border: 1px solid rgba(249, 115, 22, 0.35);
    }
    #add-item-modal .selected-product-seg--company {
        background: rgba(139, 92, 246, 0.14);
        color: #6d28d9;
        border: 1px solid rgba(139, 92, 246, 0.3);
    }
    #add-item-modal .selected-product-seg--category {
        background: rgba(16, 185, 129, 0.15);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.32);
    }
    .add-item-stock-box { background: #f8fafc; border: 1px solid #e2e8f0; max-height: 200px; overflow-y: auto; }
    .add-item-section-sub .add-item-section-title { font-size: 0.85rem; }
    #add-item-modal .item-search-input { border-radius: 8px; }

    /* Add-item modal: retail price box – auto-adjust & user-friendly */
    #add-item-modal .add-item-retail-label { color: #0d6efd; font-size: 0.9rem; margin-bottom: 0.35rem !important; }
    #add-item-modal .add-item-retail-box { background: #f0fdf4; border-color: #bbf7d0 !important; }
    #add-item-modal .add-item-retail-box-compact {
        padding: 0.5rem 0.6rem !important;
        max-width: 100%;
        width: 100%;
        box-sizing: border-box;
    }
    /* Row 1: Base + GST + R.Tax – flex, wraps on narrow */
    #add-item-modal .add-item-retail-input-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    #add-item-modal .add-item-retail-input-row .add-item-retail-rs {
        padding: 0.3rem 0.4rem;
        font-size: 0.8rem;
        font-weight: 600;
        background: #e5e7eb;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        flex-shrink: 0;
    }
    #add-item-modal .add-item-retail-input-row #item-retail-price {
        flex: 1 1 4rem;
        min-width: 4rem;
        max-width: 6.5rem;
        font-size: 0.9rem;
        font-weight: 600;
        padding: 0.3rem 0.4rem;
        border-radius: 6px;
        min-height: 32px;
    }
    #add-item-modal .add-item-retail-input-row .add-item-gst-select {
        width: auto;
        min-width: 4.5rem;
        font-size: 0.8rem;
        padding: 0.3rem 0.5rem;
        border-radius: 6px;
        min-height: 32px;
        flex-shrink: 0;
    }
    #add-item-modal .add-item-rtax-wrap {
        display: inline-flex;
        align-items: center;
        gap: 0.15rem;
        flex-shrink: 0;
    }
    #add-item-modal .add-item-rtax-label {
        font-size: 0.7rem;
        color: #6b7280;
        white-space: nowrap;
    }
    #add-item-modal .add-item-rtax-input {
        width: 3rem;
        font-size: 0.8rem;
        padding: 0.3rem 0.25rem;
        border-radius: 6px;
        min-height: 32px;
        text-align: center;
    }
    #add-item-modal .add-item-rtax-pct {
        font-size: 0.75rem;
        color: #6b7280;
    }
    /* Sell price row – clear result */
    #add-item-modal .add-item-sell-row {
        margin-top: 0.5rem;
        padding-top: 0.4rem;
        border-top: 1px solid rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    #add-item-modal .add-item-sell-label-wrap { display: flex; flex-direction: column; gap: 0; }
    #add-item-modal .add-item-sell-label { font-size: 0.85rem; font-weight: 600; color: #374151; }
    #add-item-modal .add-item-sell-hint { font-size: 0.65rem; color: #9ca3af; margin-top: 0.05rem; }
    #add-item-modal .add-item-sell-value {
        font-size: 1rem !important;
        font-weight: 700;
        padding: 0.35rem 0.6rem !important;
        border-radius: 8px;
        text-align: center;
        background: #059669 !important;
        color: #fff !important;
        border: none;
        min-width: 5rem;
    }
    #add-item-modal #item-retail-after-calc { font-size: 1rem !important; }
    /* Adjust by % row */
    #add-item-modal .add-item-pct-row { margin-top: 0.4rem; }
    #add-item-modal .add-item-pct-label {
        display: block;
        font-size: 0.7rem;
        color: #6b7280;
        margin-bottom: 0.15rem;
    }
    #add-item-modal .add-item-pct-row .retail-pct-select {
        font-size: 0.85rem;
        padding: 0.3rem 0.4rem;
        min-height: 32px;
        width: 100%;
        border-radius: 6px;
    }

    /* Add-item modal: mobile & PC improvements */
    @media (max-width: 767px) {
        #add-item-modal .modal-dialog { margin: 0.5rem; max-width: calc(100vw - 1rem); }
        #add-item-modal .add-item-section .row.g-3 > [class*="col-"] { margin-bottom: 0.5rem; }
        #add-item-modal #item-rate-column .input-group,
        #add-item-modal #item-retail-price-column .add-item-retail-box,
        #add-item-modal #item-retail-price-column .add-item-retail-input-row #item-retail-price,
        #add-item-modal #item-retail-price-column .add-item-gst-select,
        #add-item-modal #item-retail-price-column .add-item-rtax-input { min-height: 44px; }
        #add-item-modal .add-item-modal-footer .btn { min-height: 44px; padding: 0.5rem 0.75rem; }
        #add-item-modal #confirm-entry { min-height: 48px; font-size: 1rem; }
    }
    @media (min-width: 768px) {
        #add-item-modal .modal-dialog { max-width: 760px; }
        #add-item-modal #item-retail-after-calc { font-size: 1.15rem !important; }
    }

    #add-item-modal .modal-content::-webkit-scrollbar,
    #add-item-modal .modal-body:not(.add-item-modal-body)::-webkit-scrollbar,
    #add-item-modal #item-search-results::-webkit-scrollbar,
    #add-item-modal #stock-status-content::-webkit-scrollbar,
    #add-item-modal #stock-status-all-branches::-webkit-scrollbar {
        display: none;
    }
    /* Purchase "Add Item" modal: show native scrollbar thumb only when content is scrollable */
    #add-item-modal .add-item-modal-body::-webkit-scrollbar {
        display: none;
        width: 8px;
    }
    #add-item-modal .add-item-modal-body.has-drag-scroll-handle::-webkit-scrollbar {
        display: block !important;
        width: 8px;
    }
    /* Firefox fallback */
    #add-item-modal .add-item-modal-body {
        scrollbar-width: thin;
        scrollbar-color: transparent transparent;
    }
    #add-item-modal .add-item-modal-body.has-drag-scroll-handle {
        scrollbar-color: #cbd5e1 #f1f5f9;
    }
    .add-item-modal-content .add-item-modal-body { padding: 0 1.25rem 1rem; max-height: 70vh; overflow-y: auto; overflow-x: hidden; -webkit-overflow-scrolling: touch; }
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
    /* Prefer readable part numbers: allow dropdown to extend past narrow inputs */
    #item-search-results.item-search-results-box {
        width: max(100%, 420px);
        max-width: calc(100vw - 24px);
        box-sizing: border-box;
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
    /* Flex middle column: allow shrink; part # wraps instead of ellipsis */
    #item-search-results .item-search-result .flex-grow-1.me-3 {
        min-width: 0;
    }
    #item-search-results .item-search-part-number {
        font-size: 0.9rem !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        letter-spacing: 0.02em;
        line-height: 1.35;
        white-space: normal;
        overflow: visible;
        word-break: break-word;
        max-width: 100%;
    }
    #item-search-results .item-search-brand-part {
        font-weight: 700;
    }
    /* Quality — orange pill (search dropdown + selected product preview) */
    .product-quality-badge {
        display: inline-block;
        vertical-align: middle;
        background: rgba(249, 115, 22, 0.12);
        color: #f97316;
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
        line-height: 1.35;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .product-quality-badge mark {
        background-color: rgba(251, 146, 60, 0.45) !important;
        color: inherit !important;
        padding: 0 2px;
        border-radius: 2px;
        font-weight: 700;
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

    /* Saved/locked bill: form is read-only until Edit Bill is clicked */
    #purchaseForm.purchase-form-locked input:disabled,
    #purchaseForm.purchase-form-locked select:disabled,
    #purchaseForm.purchase-form-locked textarea:disabled {
        cursor: not-allowed;
        background-color: #f1f5f9;
    }

    /* Items section frozen when bill is saved */
    #purchase-items-section.purchase-items-section-locked {
        opacity: 0.85;
        position: relative;
    }
    #purchase-items-section.purchase-items-section-locked input:disabled,
    #purchase-items-section.purchase-items-section-locked button:disabled,
    #purchase-items-section.purchase-items-section-locked a.disabled {
        cursor: not-allowed;
        background-color: #f1f5f9 !important;
    }
    #purchase-items-section.purchase-items-section-locked .remove-item,
    #purchase-items-section.purchase-items-section-locked .purchase-row-qty-input,
    #purchase-items-section.purchase-items-section-locked .purchase-row-print-btn {
        pointer-events: none;
    }

    /* Attach Photo / Attach Receipt: highlight when file is selected */
    #purchaseForm .purchase-attach-has-file {
        border-color: #22c55e !important;
        background-color: rgba(34, 197, 94, 0.08) !important;
        color: #16a34a;
    }
    #purchaseForm .purchase-attach-has-file span,
    #purchaseForm .purchase-attach-has-file .status-text {
        color: #16a34a !important;
    }

    /* Supplier name: Select2 + Edit button in input-group */
    #purchaseForm .supplier-field-row.input-group {
        display: flex !important;
        align-items: stretch;
        flex-wrap: nowrap;
        gap: 0.25rem;
    }
    #purchaseForm .supplier-field-row .select2-container {
        flex: 1 1 0% !important;
        min-width: 0 !important;
        max-width: 100%;
    }
    #purchaseForm .supplier-field-row .supplier-actions-wrap {
        display: flex !important;
        align-items: stretch;
        flex-shrink: 0;
        gap: 0;
    }
    #purchaseForm .supplier-field-row .supplier-actions-wrap .btn,
    #purchaseForm .supplier-field-row .supplier-actions-wrap a.btn {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        min-height: 38px;
    }
    #purchaseForm .supplier-field-row .supplier-actions-wrap .btn:first-child,
    #purchaseForm .supplier-field-row .supplier-actions-wrap a.btn:first-child {
        border-radius: 0;
    }
    #purchaseForm .supplier-field-row .supplier-actions-wrap .btn:last-child,
    #purchaseForm .supplier-field-row .supplier-actions-wrap a.btn:last-child {
        border-radius: 0 6px 6px 0;
    }
    #purchaseForm .input-group:has(#supplier_id) .select2-container {
        flex: 1 1 auto;
        min-width: 0;
    }
    #purchaseForm .input-group:has(#supplier_id) .select2-container .selection .select2-selection {
        border-radius: 6px 0 0 6px;
        border-right-color: transparent;
        min-height: 38px;
        align-items: center;
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
        min-height: 38px;
        align-items: center;
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
    /* Raise backdrop only when add-item modal is open. A global rule breaks other modals (e.g. scrap picker)
       because their .modal stays at ~1055 while backdrop becomes 9998 — clicks hit the backdrop, UI looks frozen. */
    body.modal-open:has(#add-item-modal.show) .modal-backdrop {
        z-index: 9998 !important;
    }
    #purchaseScrapPickerModal.modal,
    body.modal-open #purchaseScrapPickerModal.modal.show {
        z-index: 10005 !important;
    }
    #purchaseScrapPickerModal .modal-dialog,
    #purchaseScrapPickerModal .modal-content,
    #purchaseScrapPickerModal .modal-header,
    #purchaseScrapPickerModal .modal-body,
    #purchaseScrapPickerModal .modal-footer,
    #purchaseScrapPickerModal button,
    #purchaseScrapPickerModal input,
    #purchaseScrapPickerModal .form-control {
        pointer-events: auto !important;
    }

    /* Attach image preview modal: ensure it closes (z-index + pointer-events) */
    #purchase-attach-image-modal.modal { z-index: 10070 !important; }
    #purchase-attach-image-modal.modal.show { z-index: 10070 !important; }
    #purchase-attach-image-modal .modal-dialog,
    #purchase-attach-image-modal .modal-content,
    #purchase-attach-image-modal .modal-header,
    #purchase-attach-image-modal .modal-body { pointer-events: auto !important; }

</style>
@endpush

@push('scripts')
@if(isset($editMode) && $editMode && isset($purchaseItemsForJs))
<script>
window.__purchaseEditData = {
    items: @json($purchaseItemsForJs),
    purchase: @json($purchaseForJs ?? []),
    payments: @json($purchasePaymentsForJs ?? [])
};
</script>
@endif
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
<script>
$(document).ready(function() {
    let purchaseItems = [];
    var selectedItemCategoryName = 'Other'; // for grouping by type (battery, oil, filter, etc.)
    let itemCounter = 0;
    let editingRowId = null;
    let pendingEditItem = null;
    let itemBaseRetailPrice = null; // item's retail from API; restored when % is cleared
    function getDemandUserNameForNewCartLine() {
        if (typeof window.PURCHASE_IS_DEMAND_FLOW === 'undefined' || !window.PURCHASE_IS_DEMAND_FLOW) return null;
        var n = (typeof window.PURCHASE_DEMAND_USER_NAME !== 'undefined' && window.PURCHASE_DEMAND_USER_NAME) ? String(window.PURCHASE_DEMAND_USER_NAME).trim() : '';
        return n || null;
    }
    function syncSelectedItemMasterSaleFromDetailsResponse(response) {
        if (!response) {
            $('#selected-item-master-sale-price').val('');
            return;
        }
        var candidates = [
            response.sale_price,
            response.total_sale_price,
            response.sale_price_per_base,
            response.retail_price
        ];
        for (var i = 0; i < candidates.length; i++) {
            var v = parseFloat(candidates[i]);
            if (!isNaN(v) && v > 0) {
                $('#selected-item-master-sale-price').val(String(v));
                return;
            }
        }
        $('#selected-item-master-sale-price').val('');
    }
    function syncSelectedItemCategoryFromDetailsResponse(response) {
        if (!response || !response.category_name) {
            $('#selected-item-category-name').val('');
            return;
        }
        var cn = String(response.category_name).trim();
        if (!cn || /^other$/i.test(cn)) {
            $('#selected-item-category-name').val('');
            return;
        }
        $('#selected-item-category-name').val(cn);
    }
    /** Item type + quality + technology for thermal label (battery: Technology instead of A+ on right; filters: quality). */
    function syncSelectedItemLabelMetaFromDetailsResponse(response) {
        if (!response) {
            $('#selected-item-type').val('');
            $('#selected-item-quality-name').val('');
            $('#selected-item-part-number').val('');
            $('#selected-item-product-title').val('');
            $('#selected-item-product-type-label').val('');
            $('#selected-item-technology-name').val('');
            return;
        }
        $('#selected-item-type').val((response.type || '').toString().trim().toLowerCase());
        var qn = (response.quality_name != null && response.quality_name !== '') ? String(response.quality_name).trim() : '';
        $('#selected-item-quality-name').val(qn);
        var pnMeta = (response.part_number != null && String(response.part_number).trim() !== '') ? String(response.part_number).trim() : '';
        $('#selected-item-part-number').val(pnMeta);
        var prodT = (response.product_title != null && String(response.product_title).trim() !== '') ? String(response.product_title).trim() : '';
        $('#selected-item-product-title').val(prodT);
        var ptl = (response.product_type_label != null && String(response.product_type_label).trim() !== '') ? String(response.product_type_label).trim() : '';
        if (!ptl && response.category_name != null && String(response.category_name).trim() !== '' && !/^other$/i.test(String(response.category_name).trim())) {
            ptl = String(response.category_name).trim();
        }
        $('#selected-item-product-type-label').val(ptl);
        var tn = (response.technology_name != null && response.technology_name !== '') ? String(response.technology_name).trim() : '';
        $('#selected-item-technology-name').val(tn);
    }
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
        var $previewModal = $('#purchase-attach-image-modal');
        if ($previewModal.length) {
            $previewModal.appendTo('body');
        }
    }
    function movePurchaseScrapPickerModalToBody() {
        var $m = $('#purchaseScrapPickerModal');
        if ($m.length) {
            $m.appendTo('body');
            $m.css('z-index', '10005');
        }
    }
    moveAddItemModalToBody();
    movePurchaseScrapPickerModalToBody();
    window.addEventListener('load', function() {
        moveAddItemModalToBody();
        movePurchaseScrapPickerModalToBody();
    });

    /** Show/hide Warranty section (Duration + Unit) — only for Battery-type products. */
    function togglePurchaseItemWarrantySection(show) {
        var $sec = $('#purchase-item-warranty-section');
        if (show) {
            $sec.show();
        } else {
            $sec.hide();
            $('#warranty-value').val('');
            $('#warranty-unit').val('');
        }
    }

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

    /**
     * Parts / filters / breakpad: [PART] - [PRODUCT TYPE] • [QUALITY] • [BRAND].
     * productType = category / API product_type_label; titleFallback = product name if type missing.
     */
    function formatPurchasePartLineDisplay(pn, productType, qn, comp, titleFallback) {
        var pnV = (pn || '').toString().trim();
        var typeV = (productType || '').toString().trim();
        var qnV = (qn || '').toString().trim();
        var compV = (comp || '').toString().trim();
        var fb = (titleFallback || '').toString().trim();
        if (!typeV && fb !== '') {
            if (!pnV || fb.toLowerCase() !== pnV.toLowerCase()) {
                typeV = fb;
            }
        }
        if (typeV && pnV && typeV.toLowerCase() === pnV.toLowerCase()) typeV = '';
        if (typeV && qnV && typeV.toLowerCase() === qnV.toLowerCase()) typeV = '';
        var segs = [];
        var head = '';
        if (pnV && typeV) head = pnV + ' - ' + typeV;
        else if (pnV) head = pnV;
        else if (typeV) head = typeV;
        if (head) segs.push(head);
        if (qnV) segs.push(qnV);
        if (compV) segs.push(compV);
        return segs.length ? segs.join(' • ') : '';
    }

    function purchaseTableRowDisplayName(item) {
        if (!item) return '';
        var t = (item.item_type || '').toString().toLowerCase();
        if (t === 'parts' || t === 'filters' || t === 'breakpad') {
            var typeLbl = (item.product_type_label || '').toString().trim();
            if (!typeLbl) typeLbl = (item.category_name || '').toString().trim();
            if (/^other$/i.test(typeLbl)) typeLbl = '';
            var formatted = formatPurchasePartLineDisplay(
                item.part_number,
                typeLbl,
                item.quality_name,
                item.company_name,
                item.product_title
            );
            if (formatted) return formatted;
        }
        var rawName = (item.name || '').trim();
        var plainName = (typeof stripHtml === 'function' ? stripHtml(rawName) : String(rawName).replace(/<[^>]*>/g, '')).trim();
        if (!plainName) plainName = 'Item #' + (item.item_id || '');
        var companyName = (item.company_name || '').toString().trim();
        if (companyName && plainName && plainName.toLowerCase().indexOf(companyName.toLowerCase()) === -1) {
            return companyName + ' • ' + plainName;
        }
        return plainName;
    }
    
    // Use image URL as-is if absolute; otherwise same-origin relative path (leading slash)
    function normalizeItemImageUrl(url) {
        if (!url || typeof url !== 'string') return '';
        var u = url.trim();
        if (u.indexOf('http://') === 0 || u.indexOf('https://') === 0) return u;
        if (u.indexOf('/') === 0) return u;
        return '/' + u;
    }

    /** Same asset as <img> onerror fallback — used to detect “no real product photo” (e.g. Item accessor default). */
    function purchaseRowDefaultPlaceholderUrl() {
        return '{{ asset('assets/img/icons/image.svg') }}';
    }

    function purchaseRowIsNoProductImageUrl(url) {
        if (!url || typeof url !== 'string') return true;
        var u = url.trim();
        if (!u) return true;
        var def = purchaseRowDefaultPlaceholderUrl();
        var stripQ = function(s) { return String(s).replace(/\?.*$/, ''); };
        if (stripQ(u) === stripQ(def)) return true;
        var lu = u.toLowerCase();
        if (lu.indexOf('assets/img/icons/image.svg') !== -1) return true;
        var nu = normalizeItemImageUrl(u);
        if (nu && stripQ(nu) === stripQ(def)) return true;
        return false;
    }

    /**
     * First usable product image for row thumbnail: image, photo, image_path, then images[0].
     * Skips empty values and default placeholder URLs so rows don’t show a fake “image” for every line.
     */
    function resolvePurchaseRowImageSource(item) {
        if (!item) return '';
        var list = [];
        if (item.image && typeof item.image === 'string') list.push(item.image);
        if (item.photo && typeof item.photo === 'string') list.push(item.photo);
        if (item.image_path && typeof item.image_path === 'string') list.push(item.image_path);
        if (item.images && Array.isArray(item.images) && item.images.length && typeof item.images[0] === 'string') {
            list.push(item.images[0]);
        }
        for (var i = 0; i < list.length; i++) {
            var c = list[i].trim();
            if (!c) continue;
            if (purchaseRowIsNoProductImageUrl(c)) continue;
            var n = normalizeItemImageUrl(c);
            if (!n || purchaseRowIsNoProductImageUrl(n)) continue;
            return c;
        }
        return '';
    }

    function parseSelectedItemImagesJson() {
        var raw = ($('#selected-item-images-json').val() || '').trim();
        if (!raw) return [];
        try {
            var a = JSON.parse(raw);
            return Array.isArray(a) ? a.filter(function(u) { return u && typeof u === 'string'; }) : [];
        } catch (e) { return []; }
    }

    function buildPurchaseItemGalleryUrls(item) {
        var urls = [];
        var seen = {};
        function pushU(u) {
            if (!u || typeof u !== 'string') return;
            var n = (typeof normalizeItemImageUrl === 'function' ? normalizeItemImageUrl(u) : u).trim();
            if (!n) return;
            if (!seen[n]) { seen[n] = true; urls.push(n); }
        }
        if (item && item.images && Array.isArray(item.images)) {
            item.images.forEach(function(u) { pushU(u); });
        }
        if (item && item.image) pushU(item.image);
        if (item && item.photo) pushU(item.photo);
        if (item && item.image_path) pushU(item.image_path);
        return urls;
    }

    var purchaseItemImageGalleryState = { urls: [], idx: 0 };

    function purchaseItemImageGalleryShowIndex(i) {
        var urls = purchaseItemImageGalleryState.urls || [];
        if (!urls.length) return;
        i = Math.max(0, Math.min(i, urls.length - 1));
        purchaseItemImageGalleryState.idx = i;
        $('#item-image-view-full').attr('src', urls[i] || '');
        $('#item-image-gallery-counter').text((i + 1) + ' / ' + urls.length);
        var multi = urls.length > 1;
        $('#item-image-gallery-nav').toggleClass('d-none', !multi);
        var $th = $('#item-image-gallery-thumbs');
        $th.empty();
        if (multi) {
            $th.removeClass('d-none');
            urls.forEach(function(u, ti) {
                var $img = $('<img alt="" class="img-thumbnail" style="width:64px;height:64px;object-fit:cover;cursor:pointer">').attr('src', u);
                if (ti === i) $img.addClass('border border-2 border-primary');
                $img.on('click', function() { purchaseItemImageGalleryShowIndex(ti); });
                $th.append($img);
            });
        } else {
            $th.addClass('d-none');
        }
    }

    function openPurchaseItemImageGallery(urls, voiceUrl) {
        if (!urls || !urls.length) return;
        try {
            document.querySelectorAll('#items-tbody audio.purchase-row-voice').forEach(function(a) { try { a.pause(); } catch (e) {} });
        } catch (e) {}
        purchaseItemImageGalleryState.urls = urls;
        purchaseItemImageGalleryShowIndex(0);
        var $vw = $('#item-image-view-voice-wrap');
        var $va = $('#item-image-view-voice-audio');
        if (voiceUrl && String(voiceUrl).trim()) {
            var vSrc = String(voiceUrl).trim();
            $va.attr('src', vSrc);
            if ($va[0]) { try { $va[0].load(); } catch (e) {} }
            $vw.removeClass('d-none');
        } else {
            try { if ($va[0]) $va[0].pause(); } catch (e) {}
            $va.removeAttr('src');
            $vw.addClass('d-none');
        }
        var modalEl = document.getElementById('item-image-view-modal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (typeof $('#item-image-view-modal').modal === 'function') {
            $('#item-image-view-modal').modal('show');
        }
    }

    function purchaseItemImageModalRaiseBackdropZ() {
        setTimeout(function() {
            var $b = $('.modal-backdrop').last();
            if ($b.length) $b.css('z-index', 10055);
        }, 0);
    }
    $('#item-image-view-modal').on('show.bs.modal shown.bs.modal', purchaseItemImageModalRaiseBackdropZ);
    $('#item-image-view-modal').on('hidden.bs.modal', function() {
        $('.modal-backdrop').last().css('z-index', '');
        var a = document.getElementById('item-image-view-voice-audio');
        if (a) {
            try { a.pause(); } catch (e) {}
            a.removeAttribute('src');
        }
        $('#item-image-view-voice-wrap').addClass('d-none');
    });

    $(document).on('click', '#item-image-gallery-prev', function() {
        var urls = purchaseItemImageGalleryState.urls || [];
        if (urls.length < 2) return;
        var next = (purchaseItemImageGalleryState.idx - 1 + urls.length) % urls.length;
        purchaseItemImageGalleryShowIndex(next);
    });
    $(document).on('click', '#item-image-gallery-next', function() {
        var urls = purchaseItemImageGalleryState.urls || [];
        if (urls.length < 2) return;
        var next = (purchaseItemImageGalleryState.idx + 1) % urls.length;
        purchaseItemImageGalleryShowIndex(next);
    });
    
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
                        var entryT = it.entry_type || 'purchase';
                        var cleanedFull = cleanItemName(it.name, it.item_id);
                        var rowDisplayName = cleanedFull;
                        var nameFullVal = null;
                        if (entryT === 'claim_send' || entryT === 'claim') {
                            nameFullVal = cleanedFull;
                            rowDisplayName = (typeof claimHistoryExtractBatterySequence === 'function' ? claimHistoryExtractBatterySequence(cleanedFull) : cleanedFull) || cleanedFull;
                        }
                        const item = {
                            id: itemCounter++,
                            item_id: it.item_id,
                            name: rowDisplayName,
                            name_full: nameFullVal,
                            warehouse_id: it.warehouse_id || null,
                            warehouse_name: it.warehouse_name || null,
                            quantity: parseFloat(it.quantity),
                            quantity_base: it.quantity_base != null ? parseFloat(it.quantity_base) : null,
                            base_unit: (it.base_unit || '').trim() || null,
                            unit: it.unit || 'Unit',
                            rate: parseFloat(it.rate),
                            sale_price: it.sale_price != null && it.sale_price !== '' && !isNaN(parseFloat(it.sale_price))
                                ? parseFloat(it.sale_price)
                                : null,
                            item_master_sale_price: it.item_master_sale_price != null && it.item_master_sale_price !== '' && !isNaN(parseFloat(it.item_master_sale_price))
                                ? parseFloat(it.item_master_sale_price)
                                : null,
                            item_master_retail_price: it.item_master_retail_price != null && it.item_master_retail_price !== '' && !isNaN(parseFloat(it.item_master_retail_price))
                                ? parseFloat(it.item_master_retail_price)
                                : null,
                            total_sale_price: it.total_sale_price != null && it.total_sale_price !== '' && !isNaN(parseFloat(it.total_sale_price))
                                ? parseFloat(it.total_sale_price)
                                : null,
                            sale_price_per_base: it.sale_price_per_base != null && it.sale_price_per_base !== '' && !isNaN(parseFloat(it.sale_price_per_base))
                                ? parseFloat(it.sale_price_per_base)
                                : null,
                            retail_price: it.retail_price != null ? parseFloat(it.retail_price) : null,
                            retail_price_base: it.retail_price_base != null ? parseFloat(it.retail_price_base) : null,
                            retail_pct: it.retail_pct != null ? parseFloat(it.retail_pct) : null,
                            category_name: (it.category_name != null && String(it.category_name).trim() !== '') ? String(it.category_name).trim() : null,
                            discount: parseFloat(it.discount) || 0,
                            tax_percentage: parseFloat(it.tax_percentage) || 0,
                            tax_amount: parseFloat(it.tax_amount) || 0,
                            total: total,
                            warranty: it.warranty || null,
                            entry_type: it.entry_type || 'purchase',
                            image: it.image || null,
                            image_path: it.image_path || null,
                            images: (it.images && Array.isArray(it.images) && it.images.length) ? it.images.slice() : null,
                            voice_url: (it.voice_url && String(it.voice_url).trim()) ? String(it.voice_url).trim() : null,
                            demand_user_name: (it.demand_user_name != null && String(it.demand_user_name).trim() !== '') ? String(it.demand_user_name).trim() : null
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
            var syncName = item.name;
            if ((String(item.entry_type || 'purchase') === 'claim_send' || String(item.entry_type || 'purchase') === 'claim') && item.name_full) {
                syncName = item.name_full;
            }
            return {
                item_id: item.item_id,
                name: syncName,
                warehouse_id: item.warehouse_id || null,
                quantity: item.quantity,
                unit: item.unit,
                rate: item.rate,
                sale_price: item.sale_price != null && item.sale_price !== '' ? item.sale_price : null,
                retail_price: item.retail_price != null ? item.retail_price : null,
                retail_price_base: item.retail_price_base != null ? item.retail_price_base : null,
                retail_pct: item.retail_pct != null ? item.retail_pct : null,
                discount: item.discount,
                tax_percentage: item.tax_percentage,
                tax_amount: item.tax_amount,
                total: item.total,
                entry_type: item.entry_type || 'purchase',
                demand_user_name: (item.demand_user_name != null && String(item.demand_user_name).trim() !== '') ? String(item.demand_user_name).trim() : null
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

    // New Purchase: show empty items by default; skip when loading edit mode data
    if (!window.__purchaseEditData || !window.__purchaseEditData.items || window.__purchaseEditData.items.length === 0) {
        $('#items-tbody').empty();
        purchaseItems = [];
        $('#empty-items-state').show();
        $('#items-list').hide();
        $('#payment-amount-row').hide();
        calculateTotals();
        const initialBranchId = $('#purchaseBranchId').val();
        if (initialBranchId) {
            $('#empty-state-hint').text($('#purchaseOrderSwitch').is(':checked') ? 'Click "DEMAND ITEM" to add items to cart' : 'Click "PURCHASE ITEM" to add items to cart');
        } else {
            $('#empty-state-hint').text('Select a branch first, then add items');
        }
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
        $('#tmp_cost_price').val('0');
        $('#tmp_quantity').val('').removeClass('is-invalid');
        $('#tmp_quantity_error').text('');
        $('#tmp_notes').val('');
        if (typeof window.tmpProductImagesReset === 'function') window.tmpProductImagesReset();
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
        if (window.isBillLocked) return;
        openTemporaryProductModal();
    });

    $(document).on('change', '#tmp_quantity', function() {
        $(this).removeClass('is-invalid');
        $('#tmp_quantity_error').text('');
    });

    // Add Temporary Product: multiple photos (in-memory list; submitted as images[])
    (function() {
        var TMP_MAX_IMAGES = 20;
        var tmpProductImageFiles = [];
        window.__tmpProductImageFilesRef = tmpProductImageFiles;
        var tmpProductImageObjectUrls = [];

        function tmpProductImagesRevokeUrls() {
            tmpProductImageObjectUrls.forEach(function(u) {
                try { URL.revokeObjectURL(u); } catch (e) {}
            });
            tmpProductImageObjectUrls = [];
        }

        window.tmpProductImagesReset = function() {
            tmpProductImageFiles.length = 0;
            tmpProductImagesRevokeUrls();
            var $inp = $('#tmp_images_file_input');
            if ($inp.length) $inp.val('');
            $inp.removeClass('is-invalid');
            $('#tmp_image_error').text('');
            $('#tmp_images_preview_list').empty();
            $('#tmp_images_preview_wrap').addClass('d-none');
            $('#tmp_images_count').text('0');
        };

        function tmpProductImagesValidateOne(file) {
            if (!file || !file.type || !file.type.match(/^image\//)) {
                return 'Please use image files (JPEG, PNG, GIF, WebP).';
            }
            if (file.size / (1024 * 1024) > 5) {
                return 'Each image must be 5MB or smaller.';
            }
            return null;
        }

        var tmpProductImagesRender = function() {
            tmpProductImagesRevokeUrls();
            var $list = $('#tmp_images_preview_list');
            if (!$list.length) return;
            $list.empty();
            tmpProductImageFiles.forEach(function(file, idx) {
                var url = URL.createObjectURL(file);
                tmpProductImageObjectUrls.push(url);
                var $wrap = $('<div class="d-inline-block position-relative me-1 mb-1 tmp-product-thumb-wrap"></div>');
                var $img = $('<img alt="" class="img-thumbnail" style="max-height:88px;max-width:88px;object-fit:cover;">').attr('src', url);
                var $btn = $('<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 p-0 border-0 rounded-circle" style="width:22px;height:22px;line-height:1;font-size:14px;" title="Remove">&times;</button>');
                $btn.on('click', function() {
                    tmpProductImageFiles.splice(idx, 1);
                    tmpProductImagesRender();
                    $('#tmp_image_error').text('');
                    $('#tmp_images_file_input').removeClass('is-invalid');
                });
                $wrap.append($img).append($btn);
                $list.append($wrap);
            });
            var n = tmpProductImageFiles.length;
            $('#tmp_images_count').text(String(n));
            $('#tmp_images_preview_wrap').toggleClass('d-none', n === 0);
        };

        window.tmpProductImagesAddFiles = function(fileList) {
            if (!fileList || !fileList.length) return;
            var err = '';
            for (var i = 0; i < fileList.length; i++) {
                if (tmpProductImageFiles.length >= TMP_MAX_IMAGES) {
                    err = 'Maximum ' + TMP_MAX_IMAGES + ' photos.';
                    break;
                }
                var one = tmpProductImagesValidateOne(fileList[i]);
                if (one) {
                    err = one;
                    break;
                }
                tmpProductImageFiles.push(fileList[i]);
            }
            if (err) {
                $('#tmp_image_error').text(err);
                $('#tmp_images_file_input').addClass('is-invalid');
            } else {
                $('#tmp_image_error').text('');
                $('#tmp_images_file_input').removeClass('is-invalid');
            }
            tmpProductImagesRender();
        };

        $(document).on('change', '#tmp_images_file_input', function() {
            var input = this;
            if (input.files && input.files.length) {
                window.tmpProductImagesAddFiles(input.files);
            }
            input.value = '';
        });

        $(document).on('click', '#tmp_images_clear_all_btn', function() {
            window.tmpProductImagesReset();
        });

        // Gallery: open file picker (multiple)
        $(document).on('click', '#tmp_image_gallery_btn', function() {
            var input = document.getElementById('tmp_images_file_input');
            if (!input) return;
            input.removeAttribute('capture');
            input.click();
        });

        // Camera: webcam or capture fallback
        var cameraStream = null;
        var $panel = $('#tmp_camera_capture_panel');
        var $video = $('#tmp_camera_video')[0];

        function stopCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(t) { t.stop(); });
                cameraStream = null;
            }
            if ($video) $video.srcObject = null;
            $panel.addClass('d-none');
        }

        $(document).on('click', '#tmp_image_camera_btn', function() {
            var input = document.getElementById('tmp_images_file_input');
            if (!input) return;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                input.setAttribute('capture', 'environment');
                input.click();
                return;
            }
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                .then(function(stream) {
                    cameraStream = stream;
                    if ($video) {
                        $video.srcObject = stream;
                        $video.onloadedmetadata = function() { $video.play().catch(function() {}); };
                    }
                    $panel.removeClass('d-none');
                })
                .catch(function() {
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
                        .then(function(stream) {
                            cameraStream = stream;
                            if ($video) {
                                $video.srcObject = stream;
                                $video.onloadedmetadata = function() { $video.play().catch(function() {}); };
                            }
                            $panel.removeClass('d-none');
                        })
                        .catch(function() {
                            input.setAttribute('capture', 'environment');
                            input.click();
                        });
                });
        });

        $(document).on('click', '#tmp_camera_cancel_btn', function() {
            stopCamera();
        });

        $(document).on('click', '#tmp_camera_capture_btn', function() {
            if (!$video || !$video.srcObject) return;
            var w = $video.videoWidth;
            var h = $video.videoHeight;
            if (!w || !h) return;
            var canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            var ctx = canvas.getContext('2d');
            ctx.drawImage($video, 0, 0);
            canvas.toBlob(function(blob) {
                if (!blob) return;
                var file = new File([blob], 'photo-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                window.tmpProductImagesAddFiles([file]);
                stopCamera();
            }, 'image/jpeg', 0.92);
        });

        $(document).on('hidden.bs.modal', '#add-temporary-product-modal', function() {
            stopCamera();
        });
    })();

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
        var files = (window.__tmpProductImageFilesRef && window.__tmpProductImageFilesRef.length)
            ? window.__tmpProductImageFilesRef
            : [];
        if (!files.length) {
            $('#tmp_images_file_input').addClass('is-invalid');
            $('#tmp_image_error').text('At least one photo is required for temporary products.');
            return;
        }
        $('#tmp_images_file_input').removeClass('is-invalid');
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
        var qtyRaw = ($('#tmp_quantity').val() || '').toString().trim();
        var qtyNum = parseFloat(qtyRaw, 10);
        if (!qtyRaw || isNaN(qtyNum) || qtyNum < 1 || qtyNum > 1000) {
            $('#tmp_quantity').addClass('is-invalid');
            $('#tmp_quantity_error').text('Please select a quantity from 1 to 1000.');
            return;
        }
        $('#tmp_quantity').removeClass('is-invalid');
        $('#tmp_quantity_error').text('');
        var formData = new FormData();
        formData.append('_token', $form.find('input[name="_token"]').val() || '');
        formData.append('product_name', ($('#tmp_product_name').val() || ''));
        formData.append('cost_price', ($('#tmp_cost_price').val() || '0'));
        formData.append('quantity', ($('#tmp_quantity').val() || ''));
        formData.append('notes', ($('#tmp_notes').val() || ''));
        formData.append('voice_path', ($('#tmp_voice_path').val() || ''));
        formData.append('voice_transcript', ($('#tmp_voice_transcript').val() || ''));
        formData.append('notes_voice_path', ($('#tmp_notes_voice_path').val() || ''));
        files.forEach(function(f) {
            formData.append('images[]', f);
        });
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
                    var qty = parseFloat(String($('#tmp_quantity').val() || '').trim(), 10);
                    if (!qty || qty < 1) qty = 1;
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
                        image: it.image || null,
                        image_path: it.image_path || null,
                        images: (it.images && Array.isArray(it.images) && it.images.length) ? it.images.slice() : (it.image ? [it.image] : null),
                        voice_url: (it.voice_url && String(it.voice_url).trim()) ? String(it.voice_url).trim() : null,
                        is_temporary: true
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
                    if (typeof window.tmpProductImagesReset === 'function') window.tmpProductImagesReset();
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
                    msg = (errs.images && (Array.isArray(errs.images) ? errs.images[0] : errs.images)) ||
                          (errs['images.0'] && (Array.isArray(errs['images.0']) ? errs['images.0'][0] : errs['images.0'])) ||
                          (errs.product_name && (Array.isArray(errs.product_name) ? errs.product_name[0] : errs.product_name)) ||
                          (errs.cost_price && (Array.isArray(errs.cost_price) ? errs.cost_price[0] : errs.cost_price)) ||
                          (errs.quantity && (Array.isArray(errs.quantity) ? errs.quantity[0] : errs.quantity)) ||
                          msg;
                    if (errs.images || errs['images.0']) {
                        $('#tmp_images_file_input').addClass('is-invalid');
                        var imgErr = errs.images ? (Array.isArray(errs.images) ? errs.images[0] : errs.images) : (errs['images.0'] ? (Array.isArray(errs['images.0']) ? errs['images.0'][0] : errs['images.0']) : '');
                        $('#tmp_image_error').text(imgErr || 'Invalid image(s).');
                    } else {
                        $('#tmp_images_file_input').removeClass('is-invalid');
                        $('#tmp_image_error').text('');
                    }
                    if (errs.quantity) {
                        $('#tmp_quantity').addClass('is-invalid');
                        $('#tmp_quantity_error').text(Array.isArray(errs.quantity) ? errs.quantity[0] : errs.quantity);
                    } else {
                        $('#tmp_quantity').removeClass('is-invalid');
                        $('#tmp_quantity_error').text('');
                    }
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
                $('#tmp_cost_price').val('0');
                $('#tmp_quantity').val('').removeClass('is-invalid');
                $('#tmp_quantity_error').text('');
                $('#tmp_notes').val('');
                if (typeof window.tmpProductImagesReset === 'function') window.tmpProductImagesReset();
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
        $('#supplier_ledger_btn').toggle(!!hasSupplier);
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
        
        // Fetch supplier balance: label = "Pay" (we owe) / "Advance" (supplier owes us) / "Balance" (settled)
        var $balanceWrap = $('#supplier-previous-balance-wrap');
        var $balanceLabel = $('#supplier-balance-label');
        var $balanceEl = $('#remaining_amount');
        if (supplierId) {
            $balanceWrap.removeClass('d-none');
            $balanceLabel.text('Balance:');
            $balanceEl.text('Rs 0 (loading…)').css('color', '#fff');
            $.ajax({
                url: '{{ route("purchases.suppliers.balance", ":id") }}'.replace(':id', supplierId),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const balance = parseFloat(response.balance) || 0;
                        var formatted = 'Rs ' + (balance < 0 ? '-' : '') + Math.abs(balance).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        $balanceEl.text(formatted);
                        if (balance > 0) {
                            $balanceLabel.text('Pay:');      // hum supplier ko denge
                            $balanceEl.css('color', '#fecaca');
                        } else if (balance < 0) {
                            $balanceLabel.text('Advance:');   // supplier se lena hai / advance hai
                            $balanceEl.css('color', '#fef08a');
                        } else {
                            $balanceLabel.text('Balance:');
                            $balanceEl.css('color', '#bbf7d0');
                        }
                    } else {
                        console.error('Failed to fetch supplier balance:', response.message);
                        $balanceLabel.text('Balance:');
                        $balanceEl.text('Rs 0.00').css('color', '#bbf7d0');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching supplier balance:', xhr);
                    $balanceLabel.text('Balance:');
                    $balanceEl.text('Rs 0.00').css('color', '#bbf7d0');
                }
            });
        } else {
            $balanceWrap.addClass('d-none');
            $balanceLabel.text('Balance:');
            $balanceEl.text('Rs 0.00');
        }

        // When creating a Bill (not PO), fetch Purchase Orders; open modal only if supplier has at least one PO with pending quantity
        var isPOMode = $('#purchaseOrderSwitch').is(':checked');
        if (!isPOMode && supplierId) {
            $.ajax({
                url: '{{ route("purchases.suppliers.purchase-orders", ":id") }}'.replace(':id', supplierId),
                method: 'GET',
                success: function(res) {
                    if (res.success && res.purchase_orders && res.purchase_orders.length > 0) {
                        window.supplierPurchaseOrders = res.purchase_orders;
                        var hasAnyWithPending = res.purchase_orders.some(function(po) {
                            return po.has_pending === true || (po.items || []).some(function(line) {
                                return (parseFloat(line.pending_quantity) || 0) > 0;
                            });
                        });
                        if (hasAnyWithPending) {
                            openLoadFromPOModal();
                        }
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
                $('#empty-state-hint').text($('#purchaseOrderSwitch').is(':checked') ? 'Click "DEMAND ITEM" to add items to cart' : 'Click "PURCHASE ITEM" to add items to cart');
                
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
                    $('#warehouseName').text(warehouse.warehouse_name);
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
        // Defer Select2 so page shows "Direct Adjustable" state first (placeholders visible, no dropdown open) and load stays fast
        function initSupplierSelect2() {
            // Searchable supplier dropdown (Select2) – SUPPLIER NAME (no results = "+ Add" button)
            if ($.fn.select2 && $('#supplier_id').length && !$('#supplier_id').hasClass('select2-hidden-accessible')) {
                $('#supplier_id').select2({
                    placeholder: 'Select vendor',
                    allowClear: true,
                    width: '100%',
                    minimumResultsForSearch: 0,
                    escapeMarkup: function(markup) { return markup; },
                    language: {
                        search: function() { return 'Search by name, phone or product…'; },
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
                        var term = (params.term || '').toLowerCase().trim();
                        if (term === '') return data;
                        var text = (data.text || '').toLowerCase();
                        if (text.indexOf(term) !== -1) return data;
                        var products = [];
                        var raw = null;
                        if (data.element) {
                            raw = $(data.element).attr('data-products') || $(data.element).data('products');
                        }
                        if ((!raw || (typeof raw === 'string' && !raw.length)) && data.id) {
                            var $opt = $('#supplier_id').find('option[value="' + data.id + '"]');
                            if ($opt.length) raw = $opt.attr('data-products') || $opt.data('products');
                        }
                        if (typeof raw === 'string') {
                            raw = raw.replace(/&quot;/g, '"');
                            try { products = JSON.parse(raw); } catch (e) { products = []; }
                        } else if (Array.isArray(raw)) { products = raw; }
                        for (var i = 0; i < products.length; i++) {
                            if (String(products[i]).toLowerCase().indexOf(term) !== -1) return data;
                        }
                        return null;
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
        }
        function initSupplierMobileSelect2() {
            if ($.fn.select2 && $('#supplier_id_mobile').length && !$('#supplier_id_mobile').hasClass('select2-hidden-accessible')) {
                $('#supplier_id_mobile').select2({
                    placeholder: 'Search mobile number',
                    allowClear: true,
                    width: '100%',
                    minimumResultsForSearch: 0,
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
                            phone = ($(data.element).data('phone') || $(data.element).attr('data-phone') || '').toString();
                        } else if (data.id) {
                            var $opt = $('#supplier_id_mobile').find('option[value="' + data.id + '"]');
                            if ($opt.length) phone = ($opt.data('phone') || $opt.attr('data-phone') || '').toString();
                        }
                        phone = phone.replace(/\s+/g, '').replace(/-/g, '').toLowerCase();
                        if (phone && phone.indexOf(term) !== -1) return data;
                        var text = (data.text || '').toLowerCase();
                        if (text.indexOf(term) !== -1) return data;
                        var products = [];
                        var raw = null;
                        if (data.element) raw = $(data.element).attr('data-products') || $(data.element).data('products');
                        if ((!raw || (typeof raw === 'string' && !raw.length)) && data.id) {
                            var $opt = $('#supplier_id_mobile').find('option[value="' + data.id + '"]');
                            if ($opt.length) raw = $opt.attr('data-products') || $opt.data('products');
                        }
                        if (typeof raw === 'string') {
                            raw = raw.replace(/&quot;/g, '"');
                            try { products = JSON.parse(raw); } catch (e) { products = []; }
                        } else if (Array.isArray(raw)) products = raw;
                        for (var i = 0; i < products.length; i++) {
                            if (String(products[i]).toLowerCase().indexOf(term) !== -1) return data;
                        }
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
        }
        // Run after first paint so "Select vendor" / "Search mobile number" show immediately (Direct Adjustable), then Select2 replaces without opening
        setTimeout(function() {
            // If returning from supplier edit: set native select value BEFORE Select2 init so Select2 picks it up
            var params = new URLSearchParams(window.location.search);
            var urlSupplierId = params.get('supplier_id');
            if (urlSupplierId) {
                urlSupplierId = String(urlSupplierId).trim();
                var sel = document.getElementById('supplier_id');
                if (sel) {
                    var opt = sel.querySelector('option[value="' + urlSupplierId + '"]') || sel.querySelector('option[value="' + parseInt(urlSupplierId, 10) + '"]');
                    if (opt) { sel.value = opt.value; }
                }
                var selMobile = document.getElementById('supplier_id_mobile');
                if (selMobile) {
                    var optM = selMobile.querySelector('option[value="' + urlSupplierId + '"]') || selMobile.querySelector('option[value="' + parseInt(urlSupplierId, 10) + '"]');
                    if (optM) { selMobile.value = optM.value; }
                }
            }
            initSupplierSelect2();
            initSupplierMobileSelect2();
            @if(!empty($demandSupplierId))
            (function() {
                var did = String({{ (int) $demandSupplierId }});
                var $sel = $('#supplier_id');
                if ($sel.length && $sel.find('option[value="' + did + '"]').length) {
                    $sel.val(did).trigger('change');
                }
                var $selM = $('#supplier_id_mobile');
                if ($selM.length && $selM.find('option[value="' + did + '"]').length) {
                    $selM.val(did).trigger('change');
                }
            })();
            @endif
            // After returning from supplier edit: ensure dropdown stays set (run again after Select2 in case first set was overwritten)
            function applySupplierFromUrl() {
                var params = new URLSearchParams(window.location.search);
                var urlSupplierId = params.get('supplier_id');
                if (!urlSupplierId) return;
                urlSupplierId = String(urlSupplierId).trim();
                var $sel = $('#supplier_id');
                var $selMobile = $('#supplier_id_mobile');
                if (!$sel.length) return;
                var $opt = $sel.find('option').filter(function() { return $(this).val() === urlSupplierId || $(this).val() === String(parseInt(urlSupplierId, 10)); });
                if (!$opt.length) $opt = $sel.find('option[value="' + urlSupplierId + '"]');
                if (!$opt.length) $opt = $sel.find('option[value="' + parseInt(urlSupplierId, 10) + '"]');
                if ($opt.length) {
                    var val = $opt.first().val();
                    $sel.val(val).trigger('change');
                    if ($selMobile.length) $selMobile.val(val).trigger('change');
                    if (typeof updateSupplierEditButtonStyle === 'function') updateSupplierEditButtonStyle();
                }
            }
            applySupplierFromUrl();
            setTimeout(applySupplierFromUrl, 200);
            setTimeout(applySupplierFromUrl, 500);
            setTimeout(applySupplierFromUrl, 1000);
        }, 150);

        /** Strip spaces/dashes/brackets etc.; normalize PK-style 92… to leading 0 local. */
        function sanitizePhoneInput(value) {
            var t = (value || '').toString().trim();
            if (!t) return '';
            var digits = t.replace(/\D/g, '');
            if (!digits) return t.replace(/[\s\-\(\)]/g, '').trim();
            if (digits.indexOf('92') === 0 && digits.length >= 12) {
                return '0' + digits.slice(2);
            }
            return digits;
        }

        /** True if value looks like a phone (digits-only after ignoring +, spaces, dashes, brackets); no letters. */
        function isPhoneLikeInput(value) {
            var t = (value || '').toString().trim();
            if (!t) return false;
            var letters = t.replace(/[0-9+\s\-\(\)\.]/g, '');
            if (/[A-Za-z]/.test(letters)) return false;
            var digits = t.replace(/\D/g, '');
            return digits.length >= 7 && digits.length <= 15;
        }

        /** Prefill Add Supplier modal from vendor search term (Create Purchase Select2). */
        function applyAddSupplierModalPrefillFromSearchTerm(term) {
            var raw = (term || '').toString().trim();
            if (!raw) return;
            var $modal = $('#addSupplierModal');
            if (!$modal.length) return;
            var $company = $modal.find('input[name="company"]');
            var $phone = $modal.find('input.phone-number-input').first();

            if (isPhoneLikeInput(raw)) {
                var sanitized = sanitizePhoneInput(raw);
                $company.val('').trigger('input');
                if ($phone.length) {
                    $phone.val(sanitized);
                }
                setTimeout(function() {
                    var $name = $modal.find('input.speech-input').first();
                    if ($name.length && document.activeElement !== $name[0]) {
                        $name.trigger('focus');
                    }
                }, 350);
            } else {
                $company.val((raw).toUpperCase()).trigger('input');
                if ($phone.length) {
                    $phone.val('');
                }
                setTimeout(function() {
                    var $name = $modal.find('input.speech-input').first();
                    if ($name.length && document.activeElement !== $name[0]) {
                        $name.trigger('focus');
                    }
                }, 350);
            }
        }

        $(document).on('click', '.select2-add-vendor-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var term = ($(this).data('add-term') || '').toString().trim();
            $('#supplier_id').select2('close');
            if ($('#supplier_id_mobile').length && $('#supplier_id_mobile').hasClass('select2-hidden-accessible')) $('#supplier_id_mobile').select2('close');
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
            if (term !== undefined && term !== null && String(term).trim() !== '') {
                applyAddSupplierModalPrefillFromSearchTerm(String(term).trim());
                $('#addSupplierModal').removeData('add-term');
            }
            if (typeof window.initBusinessDetailProducts === 'function') window.initBusinessDetailProducts();
        });

        // When on purchases/create: submit Add Supplier form via AJAX and refresh supplier dropdown so new supplier appears immediately
        $(document).on('submit', '#addSupplierModal #supplierForm', function(e) {
            if (!$('#purchaseForm').length || !$('#supplier_id').length) return;
            e.preventDefault();
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var formData = new FormData(this);
            var storeUrl = $form.attr('action');
            if (!storeUrl) return;
            $submitBtn.prop('disabled', true);
            $form.find('.spinner-border').removeClass('d-none');
            $.ajax({
                url: storeUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                success: function(res) {
                    if (res && res.supplier && res.supplier.id) {
                        var s = res.supplier;
                        var name = (s.names && s.names[0]) ? s.names[0] : 'N/A';
                        var phone = (s.phones && s.phones[0]) ? s.phones[0] : '';
                        var company = s.company || '';
                        var address = s.address || '';
                        var area = s.area || '';
                        var products = (s.business_detail && Array.isArray(s.business_detail)) ? s.business_detail : [];
                        var productsJson = JSON.stringify(products);
                        var label = company + (company ? ' - ' : '') + name + (phone ? ' - ' + phone : '');
                        var optHtml = '<option value="' + s.id + '" data-name="' + (name || '').replace(/"/g, '&quot;') + '" data-phone="' + (phone || '').replace(/"/g, '&quot;') + '" data-company="' + (company || '').replace(/"/g, '&quot;') + '" data-address="' + (address || '').replace(/"/g, '&quot;') + '" data-area="' + (area || '').replace(/"/g, '&quot;') + '" data-products="' + (productsJson || '[]').replace(/"/g, '&quot;') + '">' + (label || '').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</option>';
                        if ($('#supplier_id').find('option[value="' + s.id + '"]').length === 0) {
                            $('#supplier_id').append(optHtml);
                        }
                        if ($('#supplier_id_mobile').length && $('#supplier_id_mobile').find('option[value="' + s.id + '"]').length === 0) {
                            $('#supplier_id_mobile').append(optHtml);
                        }
                        $('#supplier_id').val(s.id).trigger('change');
                        if ($('#supplier_id_mobile').length) $('#supplier_id_mobile').val(s.id).trigger('change');
                        if (typeof window.refreshSupplierGroupDropdown === 'function') window.refreshSupplierGroupDropdown();
                        var modalEl = document.getElementById('addSupplierModal');
                        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        if (typeof toastr !== 'undefined') toastr.success(res.message || 'Supplier added.');
                        else alert(res.message || 'Supplier added.');
                    }
                },
                error: function(xhr) {
                    var msg = 'Failed to save supplier.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        else if (xhr.responseJSON.errors) {
                            var first = [];
                            $.each(xhr.responseJSON.errors, function(k, v) { first.push(Array.isArray(v) ? v[0] : v); });
                            if (first.length) msg = first.join(' ');
                        }
                    }
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                    else alert(msg);
                },
                complete: function() {
                    $submitBtn.prop('disabled', false);
                    $form.find('.spinner-border').addClass('d-none');
                }
            });
            return false;
        });

        (function() {
            var searchProductsUrl = @json(route('suppliers.products.search'));
            var storeProductUrl = @json(route('suppliers.products.store'));
            var businessDetailTags = [], suggestionTimeout = null, currentSuggestions = [], selectedSuggestionIndex = -1, initialized = false;
            var COMMON_PRODUCT_SUGGESTIONS = [
                'Rice', 'Chawal', 'Sugar', 'Cheeni', 'Wheat', 'Gandum', 'Flour', 'Maida', 'Cooking Oil', 'Tail', 'Ghee', 'Butter', 'Makhan',
                'Tea', 'Chai', 'Milk', 'Doodh', 'Bread', 'Roti', 'Eggs', 'Anday', 'Rice Flour', 'Rice Maida', 'Basmati', 'Sella', 'Broken Rice',
                'Pulses', 'Daal', 'Lentils', 'Chana', 'Chickpeas', 'Masoor', 'Moong', 'Mash', 'Lobia', 'Rajma', 'White Chana', 'Kala Chana',
                'Spices', 'Masala', 'Salt', 'Namak', 'Pepper', 'Kali Mirch', 'Cumin', 'Zeera', 'Turmeric', 'Haldi', 'Red Chilli', 'Lal Mirch',
                'Coriander', 'Dhania', 'Garam Masala', 'Cardamom', 'Elaichi', 'Cinnamon', 'Dalchini', 'Cloves', 'Laung', 'Bay Leaf', 'Tez Patta',
                'Soap', 'Sabun', 'Shampoo', 'Detergent', 'Surf', 'Bleach', 'Toothpaste', 'Colgate', 'Face Wash', 'Hand Wash', 'Dish Wash',
                'Biscuits', 'Biskut', 'Nimco', 'Chips', 'Kurkure', 'Juice', 'Cold Drink', 'Water', 'Pani', 'Mineral Water', 'Soft Drink',
                'Computer', 'Computers', 'Laptop', 'Laptops', 'Tablet', 'Tablets', 'Electronics', 'Mobile', 'Phone', 'Charger', 'Cable', 'Battery', 'Batteries',
                'Smartphone', 'iPhone', 'Android', 'Touchscreen', 'Screen Guard', 'Case', 'Cover', 'Earphones', 'Headphones', 'Speaker', 'Bluetooth',
                'Clothing', 'Kapray', 'Fabric', 'Cloth', 'Garments', 'Shoes', 'Jootay', 'Bags', 'Company', 'Companies', 'Shirt', 'T-Shirt', 'Pant', 'Trouser',
                'Cotton', 'Colour', 'Color', 'Copper', 'Coil', 'Cooler', 'Copy', 'Copies', 'Cosmetics', 'Containers', 'Curtains', 'Carpet', 'Blanket',
                'Furniture', 'Furnishing', 'Hardware', 'Iron', 'Steel', 'Cement', 'Paint', 'Rang', 'Door', 'Window', 'Almirah', 'Bed', 'Chair', 'Table',
                'Groceries', 'Kirana', 'General Store', 'Pharmacy', 'Dawai', 'Medicine', 'Medicines', 'Tablets', 'Syrup', 'Injection', 'Vitamins',
                'Stationery', 'Kaghaz', 'Pen', 'Notebook', 'Notebooks', 'Books', 'Kitabain', 'Pencil', 'Eraser', 'Sharpener', 'Scale', 'Ruler',
                'Vegetables', 'Sabzi', 'Fruits', 'Phal', 'Onion', 'Pyaz', 'Potato', 'Aloo', 'Tomato', 'Tamatar', 'Garlic', 'Lehsan', 'Ginger', 'Adrak',
                'Grocery', 'Rashan', 'Dry Fruit', 'Mewa', 'Dates', 'Khajoor', 'Honey', 'Shehad', 'Almonds', 'Badam', 'Cashew', 'Kaju', 'Raisins', 'Kishmish',
                'Cosmetics', 'Makeup', 'Beauty', 'Skin Care', 'Hair Oil', 'Perfume', 'Itr', 'Lipstick', 'Nail Polish', 'Foundation', 'Cream', 'Lotion',
                'Automotive', 'Car Parts', 'Spare Parts', 'Tires', 'Tyres', 'Engine Oil', 'Petrol', 'Diesel', 'CNG', 'Battery', 'Filter', 'Brake', 'Clutch',
                'Plastic', 'Packaging', 'Containers', 'Bottles', 'Polythene', 'Compressor', 'Components', 'PVC', 'Pipe', 'Sheet', 'Box',
                'Textile', 'Yarn', 'Thread', 'Dori', 'Button', 'Zipper', 'Zip', 'Construction', 'Contractor', 'Sand', 'Gravel', 'Bricks', 'Blocks',
                'Office', 'Software', 'Hardware', 'Monitor', 'Keyboard', 'Mouse', 'Printer', 'Scanner', 'Projector', 'Webcam', 'USB', 'Adapter',
                'Camera', 'Cameras', 'Accessories', 'Electrical', 'Cables', 'Switch', 'Socket', 'Bulbs', 'LED', 'Fan', 'AC', 'Air Conditioner',
                'Tools', 'Machinery', 'Equipment', 'Machines', 'Generator', 'Inverter', 'UPS', 'Solar', 'Welding', 'Drill', 'Grinder', 'Cutter',
                'Printing', 'Paper', 'Cartridge', 'Toner', 'Stapler', 'File', 'Folder', 'Envelope', 'Stamp', 'Ink', 'Glue', 'Tape',
                'Food', 'Beverages', 'Snacks', 'Noodles', 'Pasta', 'Sauce', 'Ketchup', 'Mayonnaise', 'Pickle', 'Achar', 'Jam', 'Jelly', 'Marmalade',
                'Cleaning', 'Mop', 'Bucket', 'Brush', 'Polish', 'Wax', 'Sanitizer', 'Tissue', 'Napkin', 'Soap', 'Scrubber', 'Dettol',
                'Baby', 'Diapers', 'Wipes', 'Powder', 'Oil', 'Lotion', 'Toys', 'School', 'Bag', 'Uniform', 'Dairy', 'Yogurt', 'Dahi', 'Cheese',
                'Jewelry', 'Jewellery', 'Gold', 'Silver', 'Rings', 'Earrings', 'Necklace', 'Bracelet', 'Watch', 'Wristwatch', 'Gift', 'Decoration',
                'Sports', 'Cricket', 'Bat', 'Ball', 'Football', 'Badminton', 'Racket', 'Cycle', 'Bicycle', 'Gym', 'Exercise', 'Fitness',
                'Agriculture', 'Seeds', 'Fertilizer', 'Pesticide', 'Insecticide', 'Tractor', 'Harvester', 'Spray', 'Farming', 'Crops',
                'Restaurant', 'Hotel', 'Catering', 'Bakery', 'Sweets', 'Mithai', 'Cake', 'Pastry', 'Ice Cream', 'Cold Storage',
                'Medical', 'Surgical', 'Gloves', 'Mask', 'Bandage', 'Thermometer', 'Stethoscope', 'Equipment', 'Hospital', 'Clinic',
                'Education', 'School', 'College', 'University', 'Coaching', 'Tuition', 'Books', 'Course', 'Training', 'Certification',
                'Real Estate', 'Property', 'Land', 'House', 'Flat', 'Apartment', 'Rent', 'Sale', 'Builder', 'Developer',
                'Insurance', 'Policy', 'Claim', 'Life Insurance', 'Health Insurance', 'Vehicle Insurance', 'Agent',
                'Banking', 'Finance', 'Loan', 'Credit', 'Investment', 'Savings', 'Account', 'ATM', 'Cash', 'Cheque',
                'Travel', 'Tour', 'Ticket', 'Hotel', 'Transport', 'Bus', 'Train', 'Airline', 'Taxi', 'Cab',
                'Photography', 'Photo', 'Video', 'Album', 'Frame', 'Print', 'Lens', 'Tripod', 'Studio',
                'Garden', 'Plants', 'Seeds', 'Fertilizer', 'Pot', 'Soil', 'Flowers', 'Tree', 'Nursery',
                'Pet', 'Dog', 'Cat', 'Food', 'Accessories', 'Veterinary', 'Medicine', 'Collar', 'Leash',
                'Security', 'CCTV', 'Camera', 'Lock', 'Key', 'Alarm', 'Guard', 'Safe', 'Fire Extinguisher',
                'Kitchen', 'Utensils', 'Stove', 'Gas', 'Cylinder', 'Cooker', 'Pressure Cooker', 'Mixer', 'Grinder', 'Knife',
                'Appliances', 'Refrigerator', 'Fridge', 'Washing Machine', 'Microwave', 'Oven', 'Toaster', 'Blender',
                'Interior', 'Design', 'Decoration', 'Lighting', 'Lamp', 'Chandelier', 'Wallpaper', 'Tiles', 'Marble',
                'Chemical', 'Acid', 'Solvent', 'Dye', 'Paint', 'Varnish', 'Adhesive', 'Resin', 'Laboratory',
                'Rubber', 'Tyre', 'Tube', 'Belt', 'Hose', 'Gasket', 'Seal', 'Mat', 'Footwear', 'Slippers',
                'Leather', 'Shoes', 'Belt', 'Wallet', 'Bag', 'Jacket', 'Gloves', 'Saddlery',
                'Glass', 'Mirror', 'Window', 'Door', 'Tiles', 'Bottle', 'Jar', 'Tumbler', 'Glasses',
                'Ceramic', 'Tiles', 'Sanitary', 'Bathroom', 'Toilet', 'Basin', 'Faucet', 'Pipe',
                'Timber', 'Wood', 'Plywood', 'Board', 'Laminate', 'Door', 'Window', 'Furniture',
                'Aluminium', 'Steel', 'Metal', 'Copper', 'Brass', 'Zinc', 'Iron', 'Sheet', 'Rod', 'Wire',
                'Fashion', 'Apparel', 'Designer', 'Boutique', 'Tailor', 'Stitching', 'Embroidery', 'Printing',
                'Optics', 'Lens', 'Glasses', 'Sunglasses', 'Microscope', 'Telescope', 'Binoculars',
                'Musical', 'Instrument', 'Guitar', 'Piano', 'Drum', 'Speaker', 'Amplifier', 'Microphone',
                'Art', 'Craft', 'Painting', 'Brush', 'Canvas', 'Colours', 'Sketch', 'Drawing', 'Frame'
            ];
            function getInstantSuggestions(query) {
                var q = (query || '').toLowerCase().trim();
                if (!q) return [];
                var list = COMMON_PRODUCT_SUGGESTIONS.filter(function(name) { return name.toLowerCase().indexOf(q) !== -1 && !isTagDuplicate(name); });
                list.sort(function(a, b) {
                    var aLower = a.toLowerCase();
                    var bLower = b.toLowerCase();
                    var aStarts = aLower.indexOf(q) === 0 ? 0 : 1;
                    var bStarts = bLower.indexOf(q) === 0 ? 0 : 1;
                    if (aStarts !== bStarts) return aStarts - bStarts;
                    return aLower.localeCompare(bLower);
                });
                return list.slice(0, 25).map(function(name) { return { name: name, addNew: false }; });
            }
            function isTagDuplicate(name) {
                if (!name) return false;
                var l = name.toLowerCase().trim();
                return businessDetailTags.some(function(t) { return t.toLowerCase().trim() === l; });
            }
            function addTag(name) {
                if (!name || isTagDuplicate(name)) return;
                businessDetailTags.push(name);
                renderTags();
                updateHiddenInput();
            }
            function removeTag(name) {
                businessDetailTags = businessDetailTags.filter(function(t) { return t !== name; });
                renderTags();
                updateHiddenInput();
            }
            function renderTags() {
                var el = document.getElementById('business_detail_tags');
                if (!el) return;
                if (businessDetailTags.length === 0) { el.innerHTML = ''; return; }
                el.innerHTML = businessDetailTags.map(function(tag) {
                    return '<span class="business-detail-tag">' + (tag || '').replace(/</g, '&lt;') + ' <span class="tag-remove" data-tag="' + (tag || '').replace(/"/g, '&quot;') + '" title="Remove">×</span></span>';
                }).join('');
                el.querySelectorAll('.tag-remove').forEach(function(btn) {
                    btn.addEventListener('click', function() { removeTag(this.getAttribute('data-tag')); });
                });
            }
            function updateHiddenInput() {
                var el = document.getElementById('business_detail');
                if (el) el.value = JSON.stringify(businessDetailTags);
            }
            function addNewProductAndTag(name, inputEl, suggestionsEl) {
                var trimmed = (name || '').trim();
                if (!trimmed) return;
                if (isTagDuplicate(trimmed)) { addTag(trimmed); if (inputEl) inputEl.value = ''; if (suggestionsEl) suggestionsEl.classList.remove('show'); return; }
                var form = document.getElementById('supplierForm');
                var token = form ? (form.querySelector('input[name="_token"]') || {}).value : '';
                if (inputEl) { inputEl.disabled = true; inputEl.placeholder = 'Saving product…'; }
                fetch(storeProductUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: 'name=' + encodeURIComponent(trimmed) + '&_token=' + encodeURIComponent(token)
                }).then(function(r) { return r.json(); }).then(function(data) {
                    var nameToAdd = (data && data.name) ? data.name : trimmed;
                    addTag(nameToAdd);
                    if (inputEl) { inputEl.value = ''; inputEl.placeholder = 'Type product name and press Enter'; inputEl.disabled = false; }
                    if (suggestionsEl) suggestionsEl.classList.remove('show');
                    selectedSuggestionIndex = -1;
                }).catch(function() {
                    addTag(trimmed);
                    if (inputEl) { inputEl.value = ''; inputEl.placeholder = 'Type product name and press Enter'; inputEl.disabled = false; }
                    if (suggestionsEl) suggestionsEl.classList.remove('show');
                });
            }
            function fetchProductSuggestions(query, suggestionsEl) {
                var queryTrim = (query || '').trim();
                if (!queryTrim) { suggestionsEl.classList.remove('show'); return; }
                var instantList = getInstantSuggestions(queryTrim);
                var exactInInstant = instantList.some(function(p) { return p.name.toLowerCase() === queryTrim.toLowerCase(); });
                var list = instantList.map(function(p) { return { name: p.name, addNew: false }; });
                if (!exactInInstant && !isTagDuplicate(queryTrim)) list.push({ name: queryTrim, addNew: true });
                currentSuggestions = list;
                displayProductSuggestions(list, suggestionsEl, queryTrim);
                suggestionsEl.classList.add('show');
                fetch(searchProductsUrl + '?q=' + encodeURIComponent(queryTrim)).then(function(r) { return r.json(); }).then(function(data) {
                    var products = (data && data.products) ? data.products : [];
                    var fromApi = products.filter(function(p) { return !isTagDuplicate(p.name); }).map(function(p) { return { name: p.name, addNew: false }; });
                    var seen = {};
                    list.forEach(function(i) { seen[i.name.toLowerCase()] = true; });
                    fromApi.forEach(function(p) {
                        if (!seen[p.name.toLowerCase()]) { list.push(p); seen[p.name.toLowerCase()] = true; }
                    });
                    var exactMatch = list.some(function(p) { return p.name.toLowerCase() === queryTrim.toLowerCase(); });
                    list = list.filter(function(i) { return !i.addNew; });
                    if (!exactMatch && !isTagDuplicate(queryTrim)) list.push({ name: queryTrim, addNew: true });
                    else if (list.length === 0 && !isTagDuplicate(queryTrim)) list.push({ name: queryTrim, addNew: true });
                    currentSuggestions = list;
                    displayProductSuggestions(list, suggestionsEl, queryTrim);
                }).catch(function() {
                    if (list.length === 0) {
                        var fallback = isTagDuplicate(queryTrim) ? [] : [{ name: queryTrim, addNew: true }];
                        currentSuggestions = fallback;
                        displayProductSuggestions(fallback, suggestionsEl, queryTrim);
                    }
                });
            }
            function displayProductSuggestions(suggestions, suggestionsEl, query) {
                if (!suggestions || suggestions.length === 0) { suggestionsEl.innerHTML = ''; suggestionsEl.classList.remove('show'); return; }
                var regex = new RegExp('(' + (query || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                function highlight(t) { return (t || '').replace(regex, '<span class="highlight">$1</span>'); }
                suggestionsEl.innerHTML = suggestions.map(function(item, i) {
                    var isAddNew = item.addNew === true;
                    var text = isAddNew ? ('Add new: ' + item.name) : item.name;
                    return '<div class="business-detail-suggestion-item' + (i === selectedSuggestionIndex ? ' selected' : '') + '" data-value="' + (item.name || '').replace(/"/g, '&quot;') + '" data-add-new="' + (isAddNew ? '1' : '0') + '">' + '<div class="business-detail-suggestion-text">' + highlight(text) + '</div>' + (isAddNew ? ' <span class="badge bg-primary ms-1">Add</span>' : '') + '</div>';
                }).join('');
                suggestionsEl.classList.add('show');
            }
            window.initBusinessDetailProducts = function() {
                var input = document.getElementById('business_detail_input');
                var suggestions = document.getElementById('business_detail_suggestions');
                var tagsContainer = document.getElementById('business_detail_tags');
                var hiddenInput = document.getElementById('business_detail');
                if (!input || !tagsContainer || !hiddenInput) return;
                if (initialized) return;
                initialized = true;
                if (hiddenInput.value) {
                    try {
                        var existing = JSON.parse(hiddenInput.value);
                        if (Array.isArray(existing)) { businessDetailTags = existing; renderTags(); }
                    } catch (e) {
                        var tags = hiddenInput.value.split(',').map(function(t) { return t.trim(); }).filter(Boolean);
                        if (tags.length) { businessDetailTags = tags; renderTags(); }
                    }
                }
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.keyCode === 13) {
                        e.preventDefault();
                        e.stopPropagation();
                        var value = (input.value || '').trim();
                        if (selectedSuggestionIndex >= 0 && currentSuggestions[selectedSuggestionIndex]) {
                            var sel = currentSuggestions[selectedSuggestionIndex];
                            if (sel.addNew) addNewProductAndTag(sel.name, input, suggestions);
                            else { addTag(sel.name); input.value = ''; suggestions.classList.remove('show'); selectedSuggestionIndex = -1; }
                        } else if (value) addNewProductAndTag(value, input, suggestions);
                        return false;
                    }
                });
                input.addEventListener('input', function() {
                    var query = (input.value || '').trim();
                    if (suggestionTimeout) clearTimeout(suggestionTimeout);
                    if (query.length < 1) { suggestions.classList.remove('show'); selectedSuggestionIndex = -1; return; }
                    suggestionTimeout = setTimeout(function() { fetchProductSuggestions(query, suggestions); }, 150);
                });
                document.addEventListener('click', function(e) {
                    if (suggestions && !input.contains(e.target) && !suggestions.contains(e.target)) { suggestions.classList.remove('show'); selectedSuggestionIndex = -1; }
                });
                suggestions.addEventListener('click', function(e) {
                    var item = e.target.closest('.business-detail-suggestion-item');
                    if (!item) return;
                    var value = item.getAttribute('data-value');
                    var isAddNew = item.getAttribute('data-add-new') === '1';
                    if (value != null) {
                        if (isAddNew) addNewProductAndTag(value, input, suggestions);
                        else { addTag(value); input.value = ''; suggestions.classList.remove('show'); selectedSuggestionIndex = -1; }
                    }
                });
            };
            if (document.getElementById('business_detail_input')) {
                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function() { setTimeout(window.initBusinessDetailProducts, 50); });
                else setTimeout(window.initBusinessDetailProducts, 50);
            }
        })();
        $(document).on('keydown', '.select2-search__field', function(e) {
            if (e.which !== 13) return;
            var $btn = $('.select2-add-vendor-btn:visible');
            if ($btn.length) {
                e.preventDefault();
                e.stopPropagation();
                $btn[0].click();
            }
        });
        function openSupplierEdit() {
            var id = $('#supplier_id').val();
            var base = $('#supplier_edit_btn').data('base-url') || '{{ route("suppliers.index") }}';
            var iframe = document.getElementById('editSupplierFromPurchaseIframe');
            var modalEl = document.getElementById('editSupplierFromPurchaseModal');
            if (id && iframe && modalEl && typeof bootstrap !== 'undefined') {
                var appBase = @json(rtrim(url('/'), '/'));
                iframe.src = appBase + '/suppliers/' + encodeURIComponent(id) + '/embed-edit';
                var m = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static' });
                m.show();
                return;
            }
            var sep = base.indexOf('?') >= 0 ? '&' : '?';
            if (id) {
                var returnPath = window.location.pathname + window.location.search;
                var returnUrl = encodeURIComponent(returnPath);
                window.location.href = base + sep + 'edit=' + encodeURIComponent(id) + '&return_url=' + returnUrl;
            } else {
                window.open(base, '_blank');
            }
        }
        $('#supplier_edit_btn').on('click', function(e) {
            e.preventDefault();
            openSupplierEdit();
        });
        $('#supplier_edit_btn_mobile').on('click', function(e) {
            e.preventDefault();
            openSupplierEdit();
        });
        var editSupplierPurchaseModalEl = document.getElementById('editSupplierFromPurchaseModal');
        if (editSupplierPurchaseModalEl) {
            editSupplierPurchaseModalEl.addEventListener('hidden.bs.modal', function() {
                var ifr = document.getElementById('editSupplierFromPurchaseIframe');
                if (ifr) ifr.src = 'about:blank';
            });
        }
        window.addEventListener('message', function(e) {
            if (!e.data || e.origin !== window.location.origin) return;
            if (e.data.type === 'supplierEditEmbedClose') {
                var mel = document.getElementById('editSupplierFromPurchaseModal');
                if (mel && typeof bootstrap !== 'undefined') {
                    var inst = bootstrap.Modal.getInstance(mel);
                    if (inst) inst.hide();
                }
                return;
            }
            if (e.data.type === 'supplierUpdatedFromPurchaseEmbed' && e.data.supplier) {
                var s = e.data.supplier;
                var sid = String(s.id);
                var $opt = $('#supplier_id').find('option[value="' + sid + '"]');
                if ($opt.length) {
                    var label = s.display || ((s.company || '') + (s.company ? ' - ' : '') + (s.name || 'N/A') + (s.phone ? ' - ' + s.phone : ''));
                    $opt.text(label);
                    $opt.attr('data-name', s.name || '');
                    $opt.attr('data-phone', s.phone || '');
                    $opt.attr('data-company', s.company || '');
                    $('#supplier_id').val(sid).trigger('change');
                    var $optM = $('#supplier_id_mobile').find('option[value="' + sid + '"]');
                    if ($optM.length) {
                        $optM.text(label);
                        $optM.attr('data-name', s.name || '');
                        $optM.attr('data-phone', s.phone || '');
                        $optM.attr('data-company', s.company || '');
                        $('#supplier_id_mobile').val(sid).trigger('change');
                    }
                }
                if (typeof updateSupplierEditButtonStyle === 'function') updateSupplierEditButtonStyle();
                var mel2 = document.getElementById('editSupplierFromPurchaseModal');
                if (mel2 && typeof bootstrap !== 'undefined') {
                    var inst2 = bootstrap.Modal.getInstance(mel2);
                    if (inst2) inst2.hide();
                }
                if (typeof toastr !== 'undefined' && e.data.message) toastr.success(e.data.message);
            }
        });
        // Initial sync of supplier_mobile when page loads (e.g. cart restored); supplier_id change handler above already sets it on change
        if ($('#supplier_id').val()) {
            var $opt = $('#supplier_id').find('option:selected');
            var phone = $opt.length ? ($opt.data('phone') || $opt.attr('data-phone') || '').toString().trim() : '';
            $('#supplier_mobile').val(phone || '');
        }
        updateSupplierEditButtonStyle();

        // Supplier Ledger button: open modal, set date range, fetch report, show PDF + WhatsApp
        function setDefaultLedgerDates() {
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            var todayStr = yyyy + '-' + mm + '-' + dd;
            $('#ledger_date_from').val(todayStr);
            $('#ledger_date_to').val(todayStr);
        }
        // Parse API date d/m/Y to Y-m-d
        function ledgerDateDmyToYmd(dmy) {
            if (!dmy || typeof dmy !== 'string') return null;
            var parts = dmy.trim().split('/');
            if (parts.length !== 3) return null;
            var d = parts[0].padStart(2, '0'), m = parts[1].padStart(2, '0'), y = parts[2];
            return y + '-' + m + '-' + d;
        }
        // Default range: from the transaction *before* the last one to today, so the last bill is included but the report doesn't start from the last bill only.
        function loadLedgerReportWithDefaultRange(supplierId) {
            var $content = $('#ledger-report-content');
            $content.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 mb-0 small text-muted">Loading...</p></div>');
            var ledgerUrl = '{{ url(route("suppliers.ledger", ["supplier" => "__ID__"])) }}'.replace('__ID__', supplierId);
            $.ajax({
                url: ledgerUrl,
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                success: function(data) {
                    var today = new Date();
                    var todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
                    if (data.success && data.transactions && data.transactions.length > 0) {
                        var tx = data.transactions;
                        // Use the date of the transaction *before* the last one (closest before last), so report includes last bill but doesn't start from last bill only
                        var fromTrans = tx.length >= 2 ? tx[tx.length - 2] : tx[tx.length - 1];
                        var fromStr = ledgerDateDmyToYmd(fromTrans.date);
                        if (fromStr) {
                            $('#ledger_date_from').val(fromStr);
                            $('#ledger_date_to').val(todayStr);
                        } else {
                            $('#ledger_date_from').val(todayStr);
                            $('#ledger_date_to').val(todayStr);
                        }
                    } else {
                        $('#ledger_date_from').val(todayStr);
                        $('#ledger_date_to').val(todayStr);
                    }
                    loadLedgerReport(supplierId);
                },
                error: function() {
                    setDefaultLedgerDates();
                    loadLedgerReport(supplierId);
                }
            });
        }
        function setLedgerDatesLastWeek() {
            var today = new Date();
            var from = new Date(today);
            from.setDate(today.getDate() - 6);
            $('#ledger_date_from').val(from.toISOString().slice(0, 10));
            $('#ledger_date_to').val(today.toISOString().slice(0, 10));
        }
        function setLedgerDatesLastMonth() {
            var today = new Date();
            var firstLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            var lastLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
            $('#ledger_date_from').val(firstLastMonth.toISOString().slice(0, 10));
            $('#ledger_date_to').val(lastLastMonth.toISOString().slice(0, 10));
        }
        function loadLedgerReport(supplierId) {
            var $body = $('#supplierLedgerModalPurchaseBody');
            var $content = $('#ledger-report-content');
            var $pdfLink = $('#supplierLedgerPdfLink');
            var $waBtn = $('#supplierLedgerWhatsAppBtn');
            var from = ($('#ledger_date_from').val() || '').trim();
            var to = ($('#ledger_date_to').val() || '').trim();
            if (!from || !to) {
                $content.html('<div class="alert alert-warning">Please set From and To dates.</div>');
                return;
            }
            $content.html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 mb-0 small text-muted">Loading ledger...</p></div>');
            var ledgerUrl = '{{ url(route("suppliers.ledger", ["supplier" => "__ID__"])) }}'.replace('__ID__', supplierId);
            ledgerUrl += (ledgerUrl.indexOf('?') >= 0 ? '&' : '?') + 'date_from=' + encodeURIComponent(from) + '&date_to=' + encodeURIComponent(to) + '&include_bill_details=1';
            $.ajax({
                url: ledgerUrl,
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                success: function(data) {
                    if (!data.success) {
                        $content.html('<div class="alert alert-danger">Failed to load ledger.</div>');
                        return;
                    }
                    $body.attr('data-supplier-id', supplierId);
                    $body.attr('data-supplier-phone', (data.supplier && data.supplier.phone) ? data.supplier.phone : '');
                    $body.attr('data-supplier-name', (data.supplier && data.supplier.name) ? data.supplier.name : '');
                    var html = '<div class="ledger-report-purchase">';
                    if (data.date_from && data.date_to) {
                        html += '<p class="small text-muted mb-2"><strong>Period:</strong> ' + (data.date_from || '').replace(/</g, '&lt;') + ' to ' + (data.date_to || '').replace(/</g, '&lt;') + '</p>';
                    }
                    html += '<div class="row mb-4">';
                    html += '<div class="col-md-6"><h6 class="mb-2">Supplier</h6><table class="table table-bordered table-sm"><tr><th width="38%">Name</th><td>' + (data.supplier && data.supplier.name ? String(data.supplier.name).replace(/</g, '&lt;') : '-') + '</td></tr><tr><th>Email</th><td>' + (data.supplier && data.supplier.email ? String(data.supplier.email).replace(/</g, '&lt;') : '-') + '</td></tr><tr><th>Phone</th><td>' + (data.supplier && data.supplier.phone ? String(data.supplier.phone).replace(/</g, '&lt;') : '-') + '</td></tr></table></div>';
                    html += '<div class="col-md-6"><h6 class="mb-2">Balance</h6><table class="table table-bordered table-sm"><tr><th width="38%">Opening</th><td class="fw-bold">' + (data.opening_balance || '0') + '</td></tr><tr><th>Total Debit</th><td class="text-danger">' + (data.total_debit || '0') + '</td></tr><tr><th>Total Credit</th><td class="text-success">' + (data.total_credit || '0') + '</td></tr><tr><th>Ending Balance</th><td class="fw-bold text-primary">' + (data.ending_balance || '0') + '</td></tr><tr><th>Type</th><td>' + (data.balance_type === 'pay' ? 'To Pay (We Owe Supplier)' : 'To Receive (Supplier Owes)') + '</td></tr></table></div>';
                    html += '</div>';
                    var hasAnyBills = data.bill_details && data.bill_details.length > 0;
                    html += '<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">';
                    html += '<h6 class="mb-0">Transaction Details</h6>';
                    if (hasAnyBills) {
                        html += '<button type="button" class="btn btn-sm btn-outline-primary" id="ledger_show_all_btn"><i class="ti ti-arrows-expand me-1"></i>Show All</button>';
                    }
                    html += '</div>';
                    html += '<div class="table-responsive"><table class="table table-bordered table-sm table-hover" id="ledger-transactions-table">';
                    html += '<thead class="table-light"><tr><th>Date</th><th>Time</th><th>Type</th><th>Reference</th><th>Branch</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th><th class="text-center" style="min-width:110px">Details</th><th class="text-center" style="min-width:90px">Tally</th></tr></thead><tbody>';
                    html += '<tr class="table-info"><td colspan="6"><strong>Opening Balance</strong></td><td class="text-end">-</td><td class="text-end">-</td><td class="text-end fw-bold">' + (data.opening_balance || '0') + '</td><td></td><td></td></tr>';
                    var recMap = {};
                    if (data.reconciliations && data.reconciliations.length) {
                        data.reconciliations.forEach(function(r) { recMap[r.key] = r; });
                    }
                    var billDetailsMap = {};
                    if (data.bill_details && data.bill_details.length) {
                        data.bill_details.forEach(function(b) { billDetailsMap[b.purchase_id] = b; });
                    }
                    if (data.transactions && data.transactions.length) {
                        data.transactions.forEach(function(t) {
                            var key = t.purchase_id ? ('purchase_' + t.purchase_id) : (t.payment_id ? ('payment_' + t.payment_id) : null);
                            var rec = key ? recMap[key] : null;
                            var billDetail = t.purchase_id ? billDetailsMap[t.purchase_id] : null;
                            var rowClass = rec ? ' table-warning ledger-row-tallied' : '';
                            if (billDetail) rowClass += ' has-detail';
                            html += '<tr class="' + rowClass + '" data-purchase-id="' + (t.purchase_id || '') + '" data-payment-id="' + (t.payment_id || '') + '" data-balance="' + (t.balance != null ? t.balance : '') + '">';
                            html += '<td>' + (t.date || '') + '</td><td>' + (t.time || '') + '</td><td><span class="badge bg-secondary">' + (t.type || '') + '</span></td><td>' + (t.reference || '').replace(/</g, '&lt;') + '</td><td>' + (t.branch || '').replace(/</g, '&lt;') + '</td>';
                            html += '<td class="text-end text-danger">' + Math.round(parseFloat(t.debit) || 0) + '</td><td class="text-end text-success">' + Math.round(parseFloat(t.credit) || 0) + '</td><td class="text-end fw-bold">' + Math.round(parseFloat(t.balance) || 0) + '</td>';
                            html += '<td class="text-center align-middle">';
                            if (billDetail && billDetail.items && billDetail.items.length) {
                                html += '<button type="button" class="btn btn-sm btn-outline-secondary ledger-row-show-btn" title="Show bill items">Show</button>';
                            } else {
                                html += '<span class="text-muted">-</span>';
                            }
                            html += '</td><td class="text-center align-middle">';
                            if (rec) {
                                html += '<span class="small d-block text-success">' + (rec.reconciled_by_name ? ('Tallied by: ' + String(rec.reconciled_by_name).replace(/</g, '&lt;')) : '') + '</span>';
                                html += '<span class="small d-block text-muted">' + (rec.reconciled_at || '').replace(/</g, '&lt;') + '</span>';
                                if (rec.image_url) html += '<a href="' + rec.image_url.replace(/"/g, '&quot;') + '" target="_blank" class="btn btn-sm btn-link p-0 small">Image</a>';
                            } else if (key && !billDetail) {
                                html += '<button type="button" class="btn btn-sm btn-outline-primary ledger-reconcile-btn" title="Tally"><i class="ti ti-link me-1"></i>Tally</button>';
                            } else if (key && billDetail) {
                                html += '<button type="button" class="btn btn-sm btn-outline-primary ledger-reconcile-btn" title="Tally"><i class="ti ti-link me-1"></i>Tally</button>';
                            } else {
                                html += '<span class="text-muted">-</span>';
                            }
                            html += '</td></tr>';
                            if (billDetail && billDetail.items && billDetail.items.length) {
                                html += '<tr class="ledger-detail-row" style="display:none;"><td colspan="10" class="p-2 bg-light border-start border-end border-bottom">';
                                html += '<div class="small fw-semibold text-muted mb-2">Bill items</div>';
                                html += '<table class="table table-sm table-bordered mb-0 bg-white" style="max-width:100%">';
                                html += '<thead><tr><th>Product</th><th class="text-end">Qty</th><th>Unit</th><th class="text-end">Rate</th><th class="text-end">Item Total</th></tr></thead><tbody>';
                                billDetail.items.forEach(function(item) {
                                    html += '<tr><td>' + String(item.product_name || 'N/A').replace(/</g, '&lt;') + '</td>';
                                    html += '<td class="text-end">' + (item.quantity != null ? Number(item.quantity) : '-') + '</td>';
                                    html += '<td>' + String(item.unit || '-').replace(/</g, '&lt;') + '</td>';
                                    html += '<td class="text-end">' + (item.rate != null ? Math.round(parseFloat(item.rate)) : '-') + '</td>';
                                    html += '<td class="text-end">' + (item.item_total != null ? Math.round(parseFloat(item.item_total)) : '-') + '</td></tr>';
                                });
                                html += '</tbody><tfoot class="table-light"><tr><td colspan="4" class="text-end fw-bold">Bill Total</td><td class="text-end fw-bold">' + Math.round(parseFloat(billDetail.grand_total) || 0) + '</td></tr></tfoot></table>';
                                html += '</td></tr>';
                            }
                        });
                    } else {
                        html += '<tr><td colspan="10" class="text-center text-muted">No transactions in this date range</td></tr>';
                    }
                    html += '</tbody><tfoot class="table-light"><tr><td colspan="6" class="text-end"><strong>Totals</strong></td><td class="text-end"><strong>' + (data.total_debit || '0') + '</strong></td><td class="text-end"><strong>' + (data.total_credit || '0') + '</strong></td><td class="text-end"><strong>' + (data.ending_balance || '0') + '</strong></td><td></td><td></td></tr></tfoot></table></div>';
                    html += '</div>';
                    $content.html(html);
                    var pdfUrl = '{{ url(route("suppliers.ledger.pdf", ["supplier" => "__ID__"])) }}'.replace('__ID__', supplierId);
                    pdfUrl += (pdfUrl.indexOf('?') >= 0 ? '&' : '?') + 'date_from=' + encodeURIComponent(from) + '&date_to=' + encodeURIComponent(to);
                    $pdfLink.attr('href', pdfUrl).show();
                    if (data.supplier && data.supplier.phone && String(data.supplier.phone).replace(/\D/g, '').length >= 10) {
                        $waBtn.show();
                    }
                },
                error: function() {
                    $content.html('<div class="alert alert-danger">Could not load ledger. Please try again.</div>');
                }
            });
        }
        $('#supplier_ledger_btn').on('click', function() {
            var supplierId = $('#supplier_id').val();
            if (!supplierId) return;
            var $modal = $('#supplierLedgerModalPurchase');
            var $body = $('#supplierLedgerModalPurchaseBody');
            var $pdfLink = $('#supplierLedgerPdfLink');
            var $waBtn = $('#supplierLedgerWhatsAppBtn');
            $body.attr('data-supplier-id', supplierId);
            $pdfLink.hide().attr('href', '#');
            $waBtn.hide();
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modal = bootstrap.Modal.getOrCreateInstance($modal[0]);
                modal.show();
            } else {
                $modal.modal('show');
            }
            loadLedgerReportWithDefaultRange(supplierId);
        });
        var ledgerDateChangeTimer;
        $('#ledger_date_from, #ledger_date_to').on('change', function() {
            var supplierId = $('#supplierLedgerModalPurchaseBody').attr('data-supplier-id');
            if (!supplierId) return;
            clearTimeout(ledgerDateChangeTimer);
            ledgerDateChangeTimer = setTimeout(function() { loadLedgerReport(supplierId); }, 200);
        });
        $('#ledger_clear_btn').on('click', function() {
            var supplierId = $('#supplierLedgerModalPurchaseBody').attr('data-supplier-id');
            if (!supplierId) return;
            loadLedgerReportWithDefaultRange(supplierId);
        });
        $(document).on('click', '#ledger_show_all_btn', function() {
            var $btn = $(this);
            var $rows = $('#ledger-transactions-table').find('tr.ledger-detail-row');
            if ($rows.length === 0) return;
            var allVisible = $rows.first().is(':visible');
            if (allVisible) {
                $rows.hide();
                $('#ledger-transactions-table').find('.ledger-row-show-btn').text('Show');
                $btn.html('<i class="ti ti-arrows-expand me-1"></i>Show All');
            } else {
                $rows.show();
                $('#ledger-transactions-table').find('.ledger-row-show-btn').text('Hide');
                $btn.html('<i class="ti ti-arrows-collapse me-1"></i>Hide All');
            }
        });
        $(document).on('click', '.ledger-row-show-btn', function() {
            var $btn = $(this);
            var $detailRow = $btn.closest('tr').next('tr.ledger-detail-row');
            if ($detailRow.length) {
                $detailRow.toggle();
                $btn.text($detailRow.is(':visible') ? 'Hide' : 'Show');
            }
        });
        $('#ledger_last_week_btn').on('click', function() {
            setLedgerDatesLastWeek();
            var supplierId = $('#supplierLedgerModalPurchaseBody').attr('data-supplier-id');
            if (supplierId) loadLedgerReport(supplierId);
        });
        $('#ledger_last_month_btn').on('click', function() {
            setLedgerDatesLastMonth();
            var supplierId = $('#supplierLedgerModalPurchaseBody').attr('data-supplier-id');
            if (supplierId) loadLedgerReport(supplierId);
        });
        $('#ledger_last_tally_btn').on('click', function() {
            var supplierId = $('#supplierLedgerModalPurchaseBody').attr('data-supplier-id');
            if (!supplierId) return;
            var $btn = $(this).prop('disabled', true);
            var todayStr = new Date().toISOString().slice(0, 10);
            var url = '{{ url(route("suppliers.last-tally-date", ["supplier" => "__ID__"])) }}'.replace('__ID__', String(supplierId).trim());
            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                data: { _: Date.now() },
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                success: function(data) {
                    var from = (data && data.last_tally_date) ? data.last_tally_date : todayStr;
                    $('#ledger_date_from').val(from);
                    $('#ledger_date_to').val(todayStr);
                    loadLedgerReport(supplierId);
                },
                error: function() {
                    $('#ledger_date_from').val(todayStr);
                    $('#ledger_date_to').val(todayStr);
                    loadLedgerReport(supplierId);
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        });

        function sendLedgerViaWhatsAppPurchase() {
            var $body = $('#supplierLedgerModalPurchaseBody');
            var supplierId = $body.attr('data-supplier-id');
            var phone = ($body.attr('data-supplier-phone') || '').toString().trim();
            var from = ($('#ledger_date_from').val() || '').trim();
            var to = ($('#ledger_date_to').val() || '').trim();
            var pdfUrl = '{{ url(route("suppliers.ledger.pdf", ["supplier" => "__ID__"])) }}'.replace('__ID__', supplierId);
            if (from && to) pdfUrl += (pdfUrl.indexOf('?') >= 0 ? '&' : '?') + 'date_from=' + encodeURIComponent(from) + '&date_to=' + encodeURIComponent(to);
            var cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.length < 10) {
                alert('Supplier phone number is invalid or missing. Cannot open WhatsApp.');
                return;
            }
            if (cleanPhone.length === 10 && !/^0/.test(phone)) cleanPhone = '92' + cleanPhone;
            else if (cleanPhone.length === 11 && /^0/.test(phone)) cleanPhone = '92' + cleanPhone.slice(1);
            else if (cleanPhone.length < 11) cleanPhone = '92' + cleanPhone;
            var message = encodeURIComponent('Hello,\n\nPlease find your Supplier Ledger Report attached.\n\nThank you.');
            var whatsappUrl = 'https://wa.me/' + cleanPhone + '?text=' + message;
            window.open(pdfUrl, '_blank');
            setTimeout(function() { window.open(whatsappUrl, '_blank'); }, 300);
        }
        $('#supplierLedgerWhatsAppBtn').on('click', function() {
            sendLedgerViaWhatsAppPurchase();
        });

        $(document).on('click', '.ledger-reconcile-btn', function() {
            var $row = $(this).closest('tr');
            var purchaseId = $row.attr('data-purchase-id') || '';
            var paymentId = $row.attr('data-payment-id') || '';
            var balance = $row.attr('data-balance') || '';
            $('#reconcile_purchase_id').val(purchaseId);
            $('#reconcile_payment_id').val(paymentId);
            $('#reconcile_balance_at').val(balance);
            $('#reconcile_image_input').val('');
            var $reconcileModal = $('#ledgerReconcileModal');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var modal = bootstrap.Modal.getOrCreateInstance($reconcileModal[0]);
                modal.show();
            } else {
                $reconcileModal.modal('show');
            }
        });

        $('#ledgerReconcileSaveBtn').on('click', function() {
            var supplierId = $('#supplierLedgerModalPurchaseBody').attr('data-supplier-id');
            if (!supplierId) { alert('Supplier not found.'); return; }
            var purchaseId = $('#reconcile_purchase_id').val();
            var paymentId = $('#reconcile_payment_id').val();
            if (!purchaseId && !paymentId) { alert('Row has no purchase or payment to tally.'); return; }
            var fileInput = document.getElementById('reconcile_image_input');
            if (!fileInput.files || !fileInput.files.length) { alert('Please attach an image.'); return; }
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            if (purchaseId) formData.append('purchase_id', purchaseId);
            if (paymentId) formData.append('payment_id', paymentId);
            formData.append('balance_at_reconcile', $('#reconcile_balance_at').val());
            formData.append('image', fileInput.files[0]);
            var url = '{{ url(route("suppliers.ledger.reconciliations.store", ["supplier" => "__ID__"])) }}'.replace('__ID__', supplierId);
            var $btn = $('#ledgerReconcileSaveBtn').prop('disabled', true);
            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                success: function(res) {
                    if (res.success) {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var modal = bootstrap.Modal.getInstance(document.getElementById('ledgerReconcileModal'));
                            if (modal) modal.hide();
                        } else {
                            $('#ledgerReconcileModal').modal('hide');
                        }
                        loadLedgerReport(supplierId);
                    } else {
                        alert(res.message || 'Failed to save tally.');
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.statusText || 'Request failed.');
                    alert(msg);
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        });

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
            if (!$('#saved_purchase_id').val()) {
                $('.page-title h4').text(isPO ? 'Create Purchase Order' : 'Create Purchase');
            }
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
                $paySection.find('.total-section .rent-paid-section').toggle(!isPO);
                $paySection.find('.total-section .net-payable').toggle(!isPO);
                $paySection.find('#cash-paid-section').toggle(!isPO);
                $paySection.find('#bank-paid-section').toggle(!isPO);
                $paySection.find('.total-section .total-row').last().toggle(!isPO);
            }
            // PO mode: "PURCHASE ITEM" -> "DEMAND ITEM"; hide "NEW RETURN"
            const purchaseItemHtml = isPO ? '<i class="ti ti-shopping-cart me-2"></i>DEMAND ITEM' : '<i class="ti ti-shopping-cart me-2"></i>PURCHASE ITEM';
            if ($('#add-new-item-btn-above').length) $('#add-new-item-btn-above').html(purchaseItemHtml);
            if ($('#add-new-item-btn-barcode-row').length) $('#add-new-item-btn-barcode-row').html(purchaseItemHtml);
            $('#return-btn').toggle(!isPO);
            // Empty state hint: use DEMAND ITEM or PURCHASE ITEM based on mode
            var hasBranch = $('#purchaseBranchId').val();
            var hintText = hasBranch ? (isPO ? 'Click "DEMAND ITEM" to add items to cart' : 'Click "PURCHASE ITEM" to add items to cart') : 'Select a branch first, then add items';
            $('#empty-state-hint').text(hintText);
            // Edit mode: primary actions follow Bill vs PO
            if ($('#saved_purchase_id').val()) {
                var $btnUpd = $('#btnUpdateBill');
                var $btnPrint = $('#btnUpdateAndPrint');
                if ($btnUpd.length) {
                    $btnUpd.html(isPO ? '<i class="ti ti-check me-1"></i> Update PO' : '<i class="ti ti-check me-1"></i> Update Bill');
                    $btnUpd.attr('title', isPO ? 'Save purchase order changes' : 'Save bill changes');
                }
                if ($btnPrint.length) {
                    $btnPrint.html(isPO ? '<i class="ti ti-printer me-1"></i> Update PO & Print' : '<i class="ti ti-printer me-1"></i> Update & Print');
                    $btnPrint.attr('title', isPO ? 'Update PO and open print dialog' : 'Update and open print dialog');
                }
            }
        }
        $('#purchaseOrderSwitch').on('change', updateDocTypeFromSwitch);
        updateDocTypeFromSwitch(); // init on load
    });

    // Load from Purchase Order: build modal content and open modal (called when supplier has POs)
    function renderLoadFromPOList(filter) {
        var pos = window.supplierPurchaseOrders || [];
        filter = filter || 'pending';
        var filtered = pos.filter(function(po) {
            var isComplete = (po.po_status || '').toLowerCase() === 'completed';
            return filter === 'complete' ? isComplete : !isComplete;
        });
        var html = '<div class="table-responsive"><table class="table table-bordered table-hover mb-0">';
        html += '<thead><tr><th style="width:40px;">Select</th><th>PO Number</th><th>Date</th><th>Status</th><th>Items (Ordered / Received / Pending)</th><th style="width:90px;">Action</th></tr></thead><tbody>';
        if (filtered.length === 0) {
            html += '<tr><td colspan="6" class="text-center text-muted py-4">No ' + (filter === 'complete' ? 'completed' : 'pending') + ' purchase orders.</td></tr>';
        } else {
            var purchasesEditUrlTemplate = '{{ route("purchases.edit", ["id" => 0]) }}';
            filtered.forEach(function(po, displayIdx) {
                var idx = pos.indexOf(po);
                var statusClass = (po.po_status === 'completed') ? 'success' : (po.po_status === 'partial') ? 'warning' : 'secondary';
                var poId = (po.id != null && po.id !== '') ? String(po.id) : '';
                var editUrl = poId ? purchasesEditUrlTemplate.replace('/0/edit', '/' + poId + '/edit') : '#';
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
                html += '</tbody></table></td>';
                html += '<td class="align-middle">';
                if (poId) {
                    html += '<a href="' + editUrl + '" class="btn btn-sm btn-outline-primary" title="Edit PO" target="_blank"><i class="ti ti-edit"></i> Edit</a>';
                } else {
                    html += '<span class="text-muted small">-</span>';
                }
                html += '</td></tr>';
            });
        }
        html += '</tbody></table></div>';
        $('#load-from-po-list').html(html);
    }

    function openLoadFromPOModal() {
        var pos = window.supplierPurchaseOrders || [];
        if (pos.length === 0) return;
        // Default: show Pending; reset switch to Pending
        $('#loadFromPOFilterPending').prop('checked', true);
        $('#loadFromPOFilterComplete').prop('checked', false);
        renderLoadFromPOList('pending');
        var modalEl = document.getElementById('loadFromPurchaseOrderModal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
            $('#loadFromPurchaseOrderModal').modal('show');
        }
    }

    // Load from PO modal: when user switches Pending / Complete, re-render list
    $(document).on('change', 'input[name="loadFromPOFilter"]', function() {
        var filter = $(this).val();
        if (filter === 'pending' || filter === 'complete') {
            renderLoadFromPOList(filter);
        }
    });

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
                    purchase_order_item_id: line.po_item_id,
                    is_temporary: false
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
                    defaultWh = { id: (warehouse && warehouse.id) ? warehouse.id : null, name: (warehouse && warehouse.warehouse_name) ? warehouse.warehouse_name : null };
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
    $(document).on('click', '#add-new-item-btn-above, #add-new-item-btn-barcode-row', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (window.isBillLocked) return;
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

    // Enter key on purchase create page opens add-item modal (when focus not in input/textarea/select)
    $(document).on('keydown', function(e) {
        if (e.which !== 13) return;
        var tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
        e.preventDefault();
        $('#add-new-item-btn-barcode-row').trigger('click');
    });

    function focusPurchaseBarcodeInput(force) {
        var $input = $('#purchase-page-barcode-input');
        if (!$input.length || $input.is(':disabled')) return;
        var isAddItemModalOpen = $('#add-item-modal').hasClass('show');
        var isCameraModalOpen = $('#camera-barcode-modal').hasClass('show');
        if (!force && (isAddItemModalOpen || isCameraModalOpen)) return;
        setTimeout(function() {
            try { $input.trigger('focus'); } catch (e) {}
        }, 30);
    }

    // Keep scanner input ready on refresh and after modal flows.
    setTimeout(function() { focusPurchaseBarcodeInput(true); }, 180);
    $(window).on('focus', function() { focusPurchaseBarcodeInput(false); });
    $('#add-item-modal').on('hidden.bs.modal', function() {
        // Prevent stale edit state when modal is closed (especially important after Reverse actions).
        try { resetItemModal(); } catch (e) {}
        focusPurchaseBarcodeInput(true);
    });
    $('#camera-barcode-modal').on('hidden.bs.modal', function() { focusPurchaseBarcodeInput(true); });

    // Page-level barcode scan: Enter directly quick-adds item in summary
    $('#purchase-page-barcode-input').on('keydown', function(e) {
        if (e.which !== 13) return;
        e.preventDefault();
        if (window.isBillLocked) return;
        var barcode = $(this).val().trim();
        if (!barcode) return;
        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Pehle branch select karein.' });
            } else { alert('Pehle branch select karein.'); }
            focusPurchaseBarcodeInput(true);
            return;
        }
        quickAddPurchaseItemByBarcode(barcode);
    });

    // Purchase page: Camera scan button opens camera barcode modal
    $('#purchase-open-camera-scan').on('click', function() {
        if (window.isBillLocked) return;
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
        if (window.isBillLocked) return;
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

    // CLAIM SEND button – open Add Item modal with entry_type = 'claim_send'
    $('#send-claim-btn').on('click', function(e) {
        e.preventDefault();
        if (window.isBillLocked) return;
        var branchId = $('#purchaseBranchId').val();
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before sending claim stock.',
                confirmButtonText: 'OK'
            });
            return;
        }
        currentEntryType = 'claim_send';
        addItemModalTitleKey = 'claim_send';
        $('#add-item-modal').modal('show');
    });

    // SCRAP SEND — open scrap-only picker (branch scrap stock; not claim / not non-scrap items)
    var purchaseScrapPickerCache = {};
    var purchaseScrapPickerSearchTimer = null;
    var purchaseScrapPickerState = { page: 1, hasMore: false, total: 0, loading: false, perPage: 50 };

    function purchaseScrapPickerEscapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function purchaseScrapPickerFormatAvailQty(n, unit) {
        var u = (unit && String(unit).trim()) ? String(unit).trim() : 'Unit';
        var num = parseFloat(n);
        if (isNaN(num)) num = 0;
        var numStr = (Math.abs(num % 1) < 1e-6 ? String(Math.round(num)) : num.toFixed(2));
        return numStr + ' ' + purchaseScrapPickerEscapeHtml(u);
    }

    function purchaseScrapPickerGetLoadedScrapItemIds() {
        var set = {};
        if (typeof purchaseItems === 'undefined' || !purchaseItems.length) return set;
        purchaseItems.forEach(function(it) {
            if ((it.entry_type || '') === 'scrap' && it.item_id != null && it.item_id !== '') {
                set[String(it.item_id)] = true;
            }
        });
        return set;
    }

    function purchaseScrapPickerFilterItemsNotInCart(items) {
        if (!items || !items.length) return [];
        var loaded = purchaseScrapPickerGetLoadedScrapItemIds();
        return items.filter(function(it) { return !loaded[String(it.id)]; });
    }

    function purchaseScrapPickerSetEmptyCopy(mode) {
        var $w = $('#purchaseScrapPickerEmpty');
        var $t = $w.find('.purchase-scrap-empty-title');
        var $s = $w.find('.purchase-scrap-empty-sub');
        if (mode === 'all_in_bill') {
            $t.text('No more scrap items available to load.');
            $s.text('All matching scrap items are already on this bill. Remove a line from the bill to add again from here.');
        } else {
            $t.text('No scrap items available in this branch.');
            $s.text('No scrap-type stock in branch warehouses, or nothing matches your search.');
        }
    }

    function purchaseScrapPickerSyncMetaBar() {
        var $m = $('#purchaseScrapPickerMeta');
        if (!$('#purchaseScrapPickerLoading').hasClass('d-none')) return;
        var shown = $('#purchaseScrapPickerTableBody tr').length;
        var t = purchaseScrapPickerState.total;
        var inBill = Object.keys(purchaseScrapPickerGetLoadedScrapItemIds()).length;
        if (shown === 0 && t === 0 && $('#purchaseScrapPickerEmpty').hasClass('d-none')) {
            $m.addClass('d-none').text('');
            return;
        }
        $m.removeClass('d-none');
        var parts = shown + ' row(s) to add';
        if (inBill > 0) parts += ' · ' + inBill + ' scrap item(s) already on bill (hidden here)';
        if (t > 0) parts += ' · ' + t + ' match search in branch';
        if (purchaseScrapPickerState.hasMore) parts += ' · load more for additional items';
        $m.text(parts);
    }

    function purchaseScrapPickerRemoveRowsByItemIds(itemIds) {
        if (!itemIds || !itemIds.length) return;
        itemIds.forEach(function(sid) {
            var id = String(sid);
            $('#purchaseScrapPickerTableBody tr[data-scrap-id="' + id + '"]').remove();
            delete purchaseScrapPickerCache[id];
        });
        $('#purchaseScrapPickerSelectAll').prop('checked', false);
        if ($('#purchaseScrapPickerTableBody tr').length === 0) {
            $('#purchaseScrapPickerTable').addClass('d-none');
            if (purchaseScrapPickerState.hasMore) {
                $('#purchaseScrapPickerEmpty').addClass('d-none');
                $('#purchaseScrapPickerLoadMoreWrap').removeClass('d-none');
                purchaseScrapPickerSyncMetaBar();
                var qMore = ($('#purchaseScrapPickerSearch').val() || '').trim();
                purchaseScrapPickerLoad(qMore, { append: true });
            } else {
                purchaseScrapPickerSetEmptyCopy('all_in_bill');
                $('#purchaseScrapPickerEmpty').removeClass('d-none');
                $('#purchaseScrapPickerLoadMoreWrap').addClass('d-none');
                purchaseScrapPickerSyncMetaBar();
            }
        } else {
            purchaseScrapPickerSyncMetaBar();
        }
    }

    function purchaseScrapPickerRenderRows(items, append) {
        var $tb = $('#purchaseScrapPickerTableBody');
        var $table = $('#purchaseScrapPickerTable');
        var $empty = $('#purchaseScrapPickerEmpty');
        var $loadWrap = $('#purchaseScrapPickerLoadMoreWrap');
        if (!append) {
            $tb.empty();
            purchaseScrapPickerCache = {};
            $('#purchaseScrapPickerSelectAll').prop('checked', false);
        }
        if (!items || !items.length) {
            if (!append) {
                $table.addClass('d-none');
                $empty.removeClass('d-none');
                $loadWrap.addClass('d-none');
            }
            return;
        }
        $empty.addClass('d-none');
        $table.removeClass('d-none');
        items.forEach(function(it) {
            purchaseScrapPickerCache[String(it.id)] = it;
            var img = it.image
                ? '<img src="' + purchaseScrapPickerEscapeHtml(it.image) + '" alt="" class="rounded border bg-light" style="width:48px;height:48px;object-fit:cover;" onerror="this.onerror=null;this.src=\'{{ asset('assets/img/icons/image.svg') }}\';">'
                : '<span class="d-inline-flex align-items-center justify-content-center bg-light border rounded text-muted" style="width:48px;height:48px;"><i class="ti ti-photo"></i></span>';
            var metaBits = [];
            if (it.part_number) metaBits.push('<div><span class="text-muted">Part #</span> ' + purchaseScrapPickerEscapeHtml(it.part_number) + '</div>');
            if (it.company_name) metaBits.push('<div><span class="text-muted">Brand</span> ' + purchaseScrapPickerEscapeHtml(it.company_name) + '</div>');
            if (it.bar_code) metaBits.push('<div><span class="text-muted">Barcode</span> <span class="badge bg-light text-dark border fw-normal">' + purchaseScrapPickerEscapeHtml(it.bar_code) + '</span></div>');
            var metaHtml = metaBits.length ? '<div class="purchase-scrap-item-meta mt-1">' + metaBits.join('') + '</div>' : '';
            var whAvail = parseFloat(it.scrap_qty_warehouse) || 0;
            var brAvail = parseFloat(it.scrap_qty_branch) || 0;
            var whNote = '';
            if (it.warehouse_name) {
                whNote += '<div class="small text-muted mt-1"><i class="ti ti-building-warehouse me-1"></i>' + purchaseScrapPickerEscapeHtml(it.warehouse_name) + '</div>';
            }
            if (brAvail > whAvail + 1e-6) {
                whNote += '<div class="small text-muted">Branch total: ' + purchaseScrapPickerFormatAvailQty(brAvail, it.unit) + '</div>';
            }
            var maxQ = Math.max(0.01, whAvail || 0.01);
            var defQ = maxQ;
            var rateNum = parseFloat(it.rate) || 0;
            var rateDisp = (Math.abs(rateNum % 1) < 1e-6 ? String(Math.round(rateNum)) : rateNum.toFixed(2));
            var unitEsc = purchaseScrapPickerEscapeHtml(it.unit || 'unit');
            var scrapBadge = '<span class="purchase-scrap-send-badge align-middle" role="status">SCRAP SEND</span>';
            var row = '<tr data-scrap-id="' + it.id + '">' +
                '<td class="text-center align-middle"><input type="checkbox" class="form-check-input purchase-scrap-row-cb" data-scrap-id="' + it.id + '"></td>' +
                '<td class="text-center align-middle">' + img + '</td>' +
                '<td class="align-middle"><div class="d-flex align-items-center flex-wrap gap-1"><span class="fw-semibold small">' + purchaseScrapPickerEscapeHtml(it.name) + '</span>' + scrapBadge + '</div>' + metaHtml + whNote + '</td>' +
                '<td class="text-end align-middle"><div class="fw-bold text-dark" style="font-variant-numeric:tabular-nums;">' + purchaseScrapPickerFormatAvailQty(whAvail, it.unit) + '</div><div class="small text-muted">Available</div></td>' +
                '<td class="text-end align-middle purchase-scrap-rate-cell"><span class="purchase-scrap-rate-main">Rs ' + rateDisp + '</span><span class="purchase-scrap-rate-sub d-block">per ' + unitEsc + '</span></td>' +
                '<td class="text-center align-middle">' +
                    '<input type="number" class="form-control form-control-sm purchase-scrap-qty-input" min="0.01" step="0.01" max="' + maxQ + '" value="' + defQ + '" data-scrap-id="' + it.id + '" data-max-scrap="' + maxQ + '" title="Max scrap for this line: ' + maxQ + '">' +
                    '<div class="purchase-scrap-picker-qty-hint text-start mt-1" data-scrap-id="' + it.id + '"></div></td>' +
                '<td class="text-end align-middle"><button type="button" class="btn btn-sm btn-outline-primary purchase-scrap-add-one" data-scrap-id="' + it.id + '">Add</button></td></tr>';
            $tb.append(row);
        });
        if (!append) $('#purchaseScrapPickerSelectAll').prop('checked', false);
    }

    function purchaseScrapPickerLoad(q, opts) {
        opts = opts || {};
        var append = !!opts.append;
        var branchId = $('#purchaseBranchId').val();
        if (!branchId) return;
        if (purchaseScrapPickerState.loading) return;
        if (append && !purchaseScrapPickerState.hasMore) return;

        purchaseScrapPickerState.loading = true;
        var reqPage = append ? (purchaseScrapPickerState.page + 1) : 1;

        if (!append) {
            $('#purchaseScrapPickerLoading').removeClass('d-none');
            $('#purchaseScrapPickerEmpty').addClass('d-none');
            $('#purchaseScrapPickerTable').addClass('d-none');
            $('#purchaseScrapPickerLoadMoreWrap').addClass('d-none');
        } else {
            $('#purchaseScrapPickerLoadMore').prop('disabled', true);
        }

        $.ajax({
            url: '{{ route("purchases.scrap.stock.items") }}',
            method: 'GET',
            data: { branch_id: branchId, q: q || '', page: reqPage, per_page: purchaseScrapPickerState.perPage },
            dataType: 'json'
        }).done(function(res) {
            $('#purchaseScrapPickerLoading').addClass('d-none');
            $('#purchaseScrapPickerLoadMore').prop('disabled', false);
            purchaseScrapPickerState.loading = false;
            var rawList = (res && res.success && res.items) ? res.items : [];
            var meta = (res && res.meta) ? res.meta : {};
            purchaseScrapPickerState.page = (meta.page != null) ? meta.page : reqPage;
            purchaseScrapPickerState.hasMore = (meta.has_more != null) ? !!meta.has_more : false;
            if (meta.total != null) {
                purchaseScrapPickerState.total = meta.total;
            } else if (!append) {
                purchaseScrapPickerState.total = rawList.length;
            }
            if (meta.per_page != null) purchaseScrapPickerState.perPage = meta.per_page;

            var list = purchaseScrapPickerFilterItemsNotInCart(rawList);
            if (!append) {
                if (rawList.length > 0 && list.length === 0) {
                    purchaseScrapPickerSetEmptyCopy('all_in_bill');
                } else {
                    purchaseScrapPickerSetEmptyCopy('branch_empty');
                }
            }

            purchaseScrapPickerRenderRows(list, append);
            if (append && list.length) $('#purchaseScrapPickerSelectAll').prop('checked', false);
            if (!list.length && !append) {
                $('#purchaseScrapPickerTable').addClass('d-none');
                $('#purchaseScrapPickerEmpty').removeClass('d-none');
                $('#purchaseScrapPickerLoadMoreWrap').addClass('d-none');
            } else if (list.length) {
                $('#purchaseScrapPickerEmpty').addClass('d-none');
                $('#purchaseScrapPickerLoadMoreWrap').toggleClass('d-none', !purchaseScrapPickerState.hasMore);
            } else if (append) {
                if (rawList.length === 0) {
                    purchaseScrapPickerState.hasMore = false;
                    $('#purchaseScrapPickerLoadMoreWrap').addClass('d-none');
                } else {
                    $('#purchaseScrapPickerLoadMoreWrap').toggleClass('d-none', !purchaseScrapPickerState.hasMore);
                }
            }
            purchaseScrapPickerSyncMetaBar();
        }).fail(function() {
            $('#purchaseScrapPickerLoading').addClass('d-none');
            $('#purchaseScrapPickerLoadMore').prop('disabled', false);
            purchaseScrapPickerState.loading = false;
            if (!append) {
                purchaseScrapPickerSetEmptyCopy('branch_empty');
                purchaseScrapPickerRenderRows([], false);
                if (typeof toastr !== 'undefined') toastr.error('Could not load scrap items.');
            } else if (typeof toastr !== 'undefined') toastr.error('Could not load more scrap items.');
        });
    }

    function purchaseScrapPickerRefreshList() {
        if (!$('#purchaseScrapPickerModal').hasClass('show')) return;
        purchaseScrapPickerLoad(($('#purchaseScrapPickerSearch').val() || '').trim(), { append: false });
    }

    function purchaseScrapPickerHideModal() {
        var el = document.getElementById('purchaseScrapPickerModal');
        if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var inst = bootstrap.Modal.getInstance(el);
            if (inst) inst.hide();
        } else {
            $('#purchaseScrapPickerModal').modal('hide');
        }
    }

    $(document).on('input change', '#purchaseScrapPickerTableBody .purchase-scrap-qty-input', function() {
        var $inp = $(this);
        var max = parseFloat($inp.data('max-scrap'));
        if (isNaN(max)) max = parseFloat($inp.attr('max'));
        var v = parseFloat($inp.val());
        var $hint = $inp.closest('td').find('.purchase-scrap-picker-qty-hint');
        if (isNaN(v) || v <= 0) {
            $hint.text('');
            $inp.removeClass('is-invalid');
            return;
        }
        if (!isNaN(max) && max > 0 && v > max + 1e-9) {
            $hint.text('Maximum scrap available for this line is ' + max + '.');
            $inp.addClass('is-invalid');
        } else {
            $hint.text('');
            $inp.removeClass('is-invalid');
        }
    });

    function purchaseScrapPickerValidateScrapIds(ids) {
        var taxPct = parseFloat(String($('#item-tax-percent').val()).replace(/%/g, '')) || 18;
        var errors = [];
        var payloads = [];
        ids.forEach(function(sid) {
            var meta = purchaseScrapPickerCache[String(sid)];
            if (!meta) {
                errors.push('A selected row is no longer in the list. Refresh the list and try again.');
                return;
            }
            var $inp = $('.purchase-scrap-qty-input[data-scrap-id="' + sid + '"]');
            var qty = parseFloat($inp.val());
            if (isNaN(qty) || qty <= 0) {
                errors.push((meta.name || 'Item') + ': enter a quantity greater than 0.');
                return;
            }
            var maxQ = parseFloat(meta.scrap_qty_warehouse);
            if (isNaN(maxQ) || maxQ <= 0) maxQ = 0;
            if (qty > maxQ + 1e-9) {
                errors.push((meta.name || 'Item') + ': quantity cannot exceed available scrap (' + maxQ + ' ' + (meta.unit || '') + ').');
                return;
            }
            payloads.push({ sid: sid, meta: meta, qty: qty, taxPct: taxPct });
        });
        return { ok: errors.length === 0, errors: errors, payloads: payloads };
    }

    function purchaseScrapPickerCommitScrapPayloads(payloads) {
        payloads.forEach(function(p) {
            var meta = p.meta;
            var qty = p.qty;
            var taxPct = p.taxPct;
            var rate = parseFloat(meta.rate) || 0;
            var discountAmount = 0;
            var subtotal = (qty * rate) - discountAmount;
            var taxAmount = (subtotal * taxPct) / 100;
            var total = -(Math.abs(subtotal + taxAmount));
            var line = {
                id: itemCounter++,
                item_id: meta.id,
                name: meta.name,
                company_name: meta.company_name || null,
                warehouse_id: meta.warehouse_id || null,
                warehouse_name: meta.warehouse_name || null,
                quantity: qty,
                unit: meta.unit || 'Unit',
                rate: rate,
                discount: discountAmount,
                tax_percentage: taxPct,
                tax_amount: taxAmount,
                total: total,
                entry_type: 'scrap',
                image: meta.image || null,
                bar_code: meta.bar_code || null,
                part_number: meta.part_number || null,
                item_type: 'scrap',
                demand_user_name: (typeof getDemandUserNameForNewCartLine === 'function') ? getDemandUserNameForNewCartLine() : null
            };
            purchaseItems.push(line);
        });
        sortPurchaseItemsByEntryType();
        $('#items-tbody').empty();
        purchaseItems.forEach(function(it) { addItemToTable(it); });
        if (typeof updatePurchaseTableRetailColumnVisibility === 'function') updatePurchaseTableRetailColumnVisibility();
        calculateTotals();
        syncCartToServer();
        if (typeof updatePurchaseBulkRetailBar === 'function') updatePurchaseBulkRetailBar();
        $('#empty-items-state').hide();
        $('#items-list').show();
        $('#payment-amount-row').show();
    }

    function addPurchaseScrapLinesFromPicker(ids) {
        if (!ids || !ids.length) return { added: 0, errors: [], addedIds: [] };
        var val = purchaseScrapPickerValidateScrapIds(ids);
        if (!val.ok) return { added: 0, errors: val.errors, addedIds: [] };
        purchaseScrapPickerCommitScrapPayloads(val.payloads);
        var addedIds = val.payloads.map(function(p) { return p.sid; });
        return { added: addedIds.length, errors: [], addedIds: addedIds };
    }

    $('#scrap-send-btn').on('click', function(e) {
        e.preventDefault();
        if (window.isBillLocked) return;
        var branchId = $('#purchaseBranchId').val();
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before opening scrap items.',
                confirmButtonText: 'OK'
            });
            return;
        }
        currentEntryType = 'scrap';
        addItemModalTitleKey = 'scrap';
        var el = document.getElementById('purchaseScrapPickerModal');
        if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else {
            $('#purchaseScrapPickerModal').modal('show');
        }
    });

    $('#purchaseScrapPickerModal').on('show.bs.modal', function() {
        movePurchaseScrapPickerModalToBody();
    });
    $('#purchaseScrapPickerModal').on('shown.bs.modal', function() {
        $('#purchaseScrapPickerSearch').val('');
        purchaseScrapPickerState = { page: 1, hasMore: false, total: 0, loading: false, perPage: purchaseScrapPickerState.perPage || 50 };
        purchaseScrapPickerLoad('', { append: false });
    });

    $('#purchaseScrapPickerSearch').on('input', function() {
        var v = $(this).val() || '';
        if (purchaseScrapPickerSearchTimer) clearTimeout(purchaseScrapPickerSearchTimer);
        purchaseScrapPickerSearchTimer = setTimeout(function() {
            purchaseScrapPickerLoad(v.trim(), { append: false });
        }, 320);
    });

    $('#purchaseScrapPickerSelectAll').on('change', function() {
        var on = $(this).is(':checked');
        $('#purchaseScrapPickerTableBody .purchase-scrap-row-cb').prop('checked', on);
    });

    $('#purchaseScrapPickerLoadMore').on('click', function() {
        purchaseScrapPickerLoad(($('#purchaseScrapPickerSearch').val() || '').trim(), { append: true });
    });

    $(document).on('click', '#purchaseScrapPickerTableBody .purchase-scrap-add-one', function() {
        var id = $(this).data('scrap-id');
        if (id == null || id === '') return;
        var res = addPurchaseScrapLinesFromPicker([id]);
        if (res.added > 0) {
            purchaseScrapPickerRemoveRowsByItemIds(res.addedIds);
            if (typeof toastr !== 'undefined') toastr.success('Scrap line added.');
        } else if (res.errors && res.errors.length) {
            var msg = res.errors[0];
            if (typeof toastr !== 'undefined') toastr.warning(msg);
            else alert(msg);
        }
    });

    $('#purchaseScrapPickerAddSelected').on('click', function() {
        var ids = [];
        var seenSel = {};
        $('#purchaseScrapPickerTableBody .purchase-scrap-row-cb:checked').each(function() {
            var id = $(this).data('scrap-id');
            if (id == null || id === '') return;
            var k = String(id);
            if (seenSel[k]) return;
            seenSel[k] = true;
            ids.push(id);
        });
        if (!ids.length) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'Nothing selected', text: 'Select one or more rows, or use Add on a single row.' });
            else alert('Select one or more scrap items.');
            return;
        }
        var res = addPurchaseScrapLinesFromPicker(ids);
        if (res.added === ids.length && (!res.errors || !res.errors.length)) {
            purchaseScrapPickerRemoveRowsByItemIds(res.addedIds);
            if (typeof toastr !== 'undefined') toastr.success(res.added + ' scrap line(s) added.');
            purchaseScrapPickerHideModal();
        } else if (res.added === 0 && res.errors && res.errors.length) {
            var msg = res.errors.length > 1 ? res.errors.slice(0, 5).join(' ') : res.errors[0];
            if (typeof toastr !== 'undefined') toastr.warning(msg);
            else alert(msg);
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

    // Handle "Return" button - same modal as Add Item (like Smart Invoice Return); open empty
    $('#return-btn').on('click', function(e) {
        e.preventDefault();
        if (window.isBillLocked) return;
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
        
        editingRowId = null;
        pendingEditItem = null;
        pendingItemIdAfterUpdate = null;
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
                        $sel.append($('<option></option>').val(w.id).text(w.warehouse_name));
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

    // PDF formula: Base = (Retail + round(Retail×GST%)) + round(that × R.Tax%). Amount = Base − (Retail × pct/100). R.Tax 0.05 treated as 0.5% for match.
    function updateRetailAfterCalc() {
        var retail = parseFloat($('#item-retail-price').val()) || 0;
        var pctVal = ($('#item-retail-percentage').val() || '').toString().trim();
        var pct = (pctVal === '' || pctVal === '—') ? NaN : parseFloat(pctVal);
        var taxPct = parseFloat(String($('#item-tax-percent').val()).replace(/%/g, '')) || 0;
        var rtaxPct = parseFloat($('#item-rtax-percent').val()) || 0.5;
        if (rtaxPct === 0.05) rtaxPct = 0.5;
        var priceAfterGst = retail + Math.round(retail * taxPct / 100);
        var rTaxAmt = Math.round(priceAfterGst * rtaxPct / 100);
        var baseAmount = priceAfterGst + rTaxAmt;
        var withTax;
        if (pctVal === '' || pctVal === '—' || (pct === 0 && pctVal === '0')) {
            withTax = baseAmount;
        } else {
            withTax = baseAmount - (retail * (parseFloat(pctVal) || 0) / 100);
        }
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
    $('#item-retail-price, #item-retail-percentage, #item-tax-percent, #item-rtax-percent').on('input change', function() {
        updateRetailAfterCalc();
        if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
    });
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
            if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
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
        if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
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
    // GST % is now a dropdown (12–16%); no blur formatting needed.

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
        updateOilPerLiterFromCanRate();
    });
    $('#item-rate').on('keyup', function() {
        setTimeout(function() { updateRetailColumnByRate(); updateOilPerLiterFromCanRate(); }, 0);
    });
    function updateOilPerLiterFromCanRate() {
        if (window._suppressOilRateSync) return;
        var isOil = ($('#selected-item-is-oil').val() || '').toString() === '1';
        var lpc = parseFloat($('#selected-item-liter-per-can').val()) || 0;
        if (!isOil || lpc <= 0) return;
        var canRate = parseFloat($('#item-rate').val()) || 0;
        window._suppressOilRateSync = true;
        $('#item-per-liter-rate').val(canRate > 0 ? (canRate / lpc).toFixed(2) : '');
        window._suppressOilRateSync = false;
    }
    function updateCanRateFromOilPerLiter() {
        if (window._suppressOilRateSync) return;
        var isOil = ($('#selected-item-is-oil').val() || '').toString() === '1';
        var lpc = parseFloat($('#selected-item-liter-per-can').val()) || 0;
        if (!isOil || lpc <= 0) return;
        var perLiter = parseFloat($('#item-per-liter-rate').val()) || 0;
        window._suppressOilRateSync = true;
        $('#item-rate').val(perLiter > 0 ? Math.round(perLiter * lpc) : 0);
        window._suppressOilRateSync = false;
        updateRetailColumnByRate();
    }
    $(document).on('input change', '#item-per-liter-rate', function() {
        updateCanRateFromOilPerLiter();
    });

    // Reset form when modal opens (skip full reset when opening for edit)
    $('#add-item-modal').on('show.bs.modal', function() {
        $('#add-item-modal').appendTo('body');
        setAddItemModalTitle();
        const branchId = $('#purchaseBranchId').val();

        // Return from Purchase Bill (View Bill → X): reopen modal with saved context (sessionStorage + ?reopen_add_item_modal=1)
        if (window._restorePurchaseModalFromBill) {
            window._restorePurchaseModalFromBill = false;
            var st = window._purchaseModalBillReturnState || null;
            window._purchaseModalBillReturnState = null;
            if (st && st.editingRowId != null && st.editingRowId !== '') {
                window._pendingRestorePurchaseRowClick = st.editingRowId;
                $('#add-item-modal').one('shown.bs.modal', function() {
                    var rid = window._pendingRestorePurchaseRowClick;
                    window._pendingRestorePurchaseRowClick = null;
                    if (rid != null) {
                        var $row = $('#items-tbody .purchase-item-row[data-row-id="' + rid + '"]');
                        if ($row.length) {
                            $row.trigger('click');
                        }
                    }
                });
                return;
            }
            if (st && st.selectedItemId) {
                editingRowId = null;
                $('#item-search').val(st.itemSearch || '');
                $('#selected-item-id').val(st.selectedItemId || '');
                $('#item-search-results').hide();
                loadItemSaveWarehouseDropdown();
                updateRetailColumnByRate();
                setTimeout(function() {
                    if (typeof loadItemDetails === 'function') loadItemDetails(st.selectedItemId);
                    if (typeof loadCustomerHistory === 'function') loadCustomerHistory(st.selectedItemId);
                }, 0);
                return;
            }
        }

        if (editingRowId !== null) {
            $('#item-search-results').hide();
            loadItemSaveWarehouseDropdown();
            updateRetailColumnByRate(); // Set retail column visibility for edit (hide unless row has retail %)
            return;
        }
        $('#item-search').val('');
        $('#selected-item-id').val('');
        $('#selected-item-is-temporary').val('0');
        $('#selected-item-bar-code').val('');
        $('#selected-item-company').val('');
        $('#selected-warehouse-id').val('');
        $('#selected-warehouse-ids').val('');
        $('#item-quantity').val('');
        $('#item-unit').val('');
        $('#item-rate').val('0');
        $('#item-retail-price').val('');
        $('#item-retail-percentage').val('');
        $('#selected-item-is-oil').val('');
        $('#selected-item-liter-per-can').val('');
        $('#item-per-liter-wrap').addClass('d-none');
        $('#item-per-liter-rate').val('');
        $('#item-tax-percent').val('18');
        $('#item-rtax-percent').val('0.5');
        itemBaseRetailPrice = null;
        $('#selected-item-master-sale-price').val('');
        $('#selected-item-category-name').val('');
        $('#selected-item-type').val('');
        $('#selected-item-quality-name').val('');
        $('#selected-item-part-number').val('');
        $('#selected-item-technology-name').val('');
        updateRetailPctSelectColor();
        updateRateColumnByRetailPct();
        updateRetailColumnByRate();
        togglePurchaseItemWarrantySection(false);
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to view history</p>');
        $('#item-search-results').hide();
        $('#item-edit-in-modal-btn').hide();
        $('#stock-status-section').hide();
        $('#stock-status-content').hide();
        $('#barcode-scan-input').val('');
        $('#item-search-image-preview').addClass('d-none');
        $('#item-search-image').attr('src', '');
        $('#selected-item-image').val('');
        $('#item-search-stock').html('');
        $('#item-search-warehouse').text('');
        $('#selected-item-details-display').addClass('d-none');
        $('#selected-item-details-line1').html('').removeClass('selected-product-line1--segments battery-type-sequence fw-bold text-uppercase').addClass('small');
        $('#selected-item-details-line2').html('').css('display', 'none');
        $('#selected-item-details-line3').html('');
        $('#selected-item-quality-wrap').html('').addClass('d-none');
        loadItemSaveWarehouseDropdown();
        updateRetailAfterCalc();
    });
        
    // Focus on search input when modal is fully shown; retail price editable only for admin
    $('#add-item-modal').on('shown.bs.modal', function() {
        $('#item-search').prop('readonly', false).prop('disabled', false).attr('readonly', false);
        var adminCanEditRetail = $('#item-retail-price-column').data('admin-only-edit') === '1' || $('#item-retail-price-column').data('adminOnlyEdit') === 1;
        if (adminCanEditRetail) {
            $('#item-retail-price').prop('readonly', false).removeAttr('readonly').prop('tabindex', 0);
        } else {
            $('#item-retail-price').prop('readonly', true).attr('readonly', 'readonly').prop('tabindex', -1);
        }
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
                    $('#selected-item-is-temporary').val(r.is_temporary ? '1' : '0');
                    $('#selected-item-bar-code').val(r.bar_code || '');
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

    // Purchase "Add Item" modal: ensure native drag-to-scroll thumb appears only when scrollable
    (function initPurchaseAddItemNativeScrollHandle() {
        var bodyEl = document.querySelector('#add-item-modal .add-item-modal-body');
        if (!bodyEl) return;

        var updateHandle = function() {
            // Scrollable only when scrollHeight exceeds clientHeight
            var isScrollable = bodyEl.scrollHeight > (bodyEl.clientHeight + 1);
            bodyEl.classList.toggle('has-drag-scroll-handle', !!isScrollable);
        };

        // Initial calculation when modal is opened (shown handler above runs, but this is extra safety)
        $('#add-item-modal').on('shown.bs.modal', function() {
            setTimeout(updateHandle, 50);
            setTimeout(updateHandle, 200);
        });

        // Keep in sync when user scrolls (in case content height changes after interaction)
        bodyEl.addEventListener('scroll', function() {
            updateHandle();
        }, { passive: true });

        // Dynamic content safety: observe DOM changes inside modal body
        var observer = new MutationObserver(function() {
            // Throttle using rAF to avoid excessive calls during rapid DOM updates
            if (typeof window.requestAnimationFrame === 'function') {
                requestAnimationFrame(updateHandle);
            } else {
                setTimeout(updateHandle, 0);
            }
        });
        observer.observe(bodyEl, { childList: true, subtree: true, characterData: true });

        // First run
        updateHandle();
    })();

    function buildPurchaseScanDisplayName(item) {
        if (!item || typeof item !== 'object') return '';
        const product = ((item.product_item && item.product_item.name) || item.short_disc || item.pro_dis || (item.partnumber_item ? item.partnumber_item.name : '') || '').toString().trim();
        const company = (item.company_item && item.company_item.name ? item.company_item.name : '').toString().trim();
        const plateRaw = (item.plate_item && item.plate_item.name ? item.plate_item.name : '').toString().trim();
        const ampRaw = (item.amphors_item && item.amphors_item.name ? item.amphors_item.name : '').toString().trim();
        const plate = plateRaw ? plateRaw.replace(/PL$/i, '') + 'PL' : '';
        const amp = ampRaw ? ampRaw.replace(/AH$/i, '') + 'AH' : '';
        const pieces = [product, plate, amp, company].filter(Boolean);
        if (pieces.length >= 3) return pieces.join(' • ');
        if (product) return product;
        if (item.partnumber_item && item.partnumber_item.name) return String(item.partnumber_item.name).trim();
        return item.id ? ('Item #' + item.id) : '';
    }

    function normalizeBarcodeValue(v) {
        return String(v || '')
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '')
            .replace(/[-_]/g, '');
    }

    function getBarcodeQueryCandidates(rawBarcode) {
        var raw = String(rawBarcode || '').trim();
        var normalized = normalizeBarcodeValue(raw);
        var digitsOnly = normalized.replace(/\D+/g, '');
        var out = [];
        if (raw) out.push(raw);
        if (normalized && out.indexOf(normalized) === -1) out.push(normalized);
        if (digitsOnly && digitsOnly.length >= 6 && out.indexOf(digitsOnly) === -1) out.push(digitsOnly);
        return out;
    }

    // Resolve exact barcode ambiguities from API (same barcode can come from multiple rows/warehouses).
    function resolveExactBarcodeScanResult(exactMatches, preferredWarehouseId) {
        if (!Array.isArray(exactMatches) || exactMatches.length === 0) return null;
        if (exactMatches.length === 1) return exactMatches[0];

        var prefWh = (preferredWarehouseId || '').toString().trim();

        // 1) Prefer currently selected warehouse
        if (prefWh) {
            var whRows = exactMatches.filter(function(r) {
                return String((r && r.warehouse_id) || '').trim() === prefWh;
            });
            if (whRows.length === 1) return whRows[0];
        }

        // 2) If all rows point to same item id, pick deterministic first (or selected warehouse row).
        var uniqItem = {};
        exactMatches.forEach(function(r) {
            var iid = String((((r || {}).item || {}).id) || '').trim();
            if (iid) uniqItem[iid] = true;
        });
        var itemIds = Object.keys(uniqItem);
        if (itemIds.length === 1) {
            if (prefWh) {
                var pickWh = exactMatches.find(function(r) {
                    return String((r && r.warehouse_id) || '').trim() === prefWh;
                });
                if (pickWh) return pickWh;
            }
            return exactMatches[0];
        }

        // 3) Prefer row already present in cart (barcode + warehouse)
        if (Array.isArray(purchaseItems) && purchaseItems.length > 0) {
            var fromCart = exactMatches.find(function(r) {
                var bar = String(((((r || {}).item || {}).bar_code) || '')).trim();
                var wh = String((r && r.warehouse_id) || '').trim();
                return purchaseItems.some(function(it) {
                    return String(it.entry_type || 'purchase') === 'purchase' &&
                           String(it.bar_code || '').trim() === bar &&
                           String(it.warehouse_id || '').trim() === wh;
                });
            });
            if (fromCart) return fromCart;
        }

        return null;
    }

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
        
        var barcodeCandidates = getBarcodeQueryCandidates(barcode);
        var searchWithCandidate = function(candidateIndex) {
            var qVal = barcodeCandidates[candidateIndex] || barcode;
            $.ajax({
                url: "{{ route('purchases.items.ajax.search') }}",
                method: 'GET',
                data: (function() {
                    var data = { q: qVal, branch_id: branchId, limit: 15 };
                        if (typeof currentEntryType !== 'undefined') {
                            if (currentEntryType === 'scrap') data.entry_type = 'scrap';
                            else if (currentEntryType === 'claim' || currentEntryType === 'claim_send') data.entry_type = 'claim';
                        }
                    return data;
                })(),
                success: function(results) {
                const itemResults = results.filter(function(r) { return r.type === 'item'; });
                if ((!itemResults || itemResults.length === 0) && candidateIndex + 1 < barcodeCandidates.length) {
                    searchWithCandidate(candidateIndex + 1);
                    return;
                }
                const normalizedBarcode = normalizeBarcodeValue(barcode);
                const exactBarcodeMatches = itemResults.filter(function(r) {
                    const code = normalizeBarcodeValue((((r || {}).item || {}).bar_code || '').toString());
                    return normalizedBarcode && code === normalizedBarcode;
                });
                // For scanner behavior, strict exact barcode match gets priority.
                const preferredWarehouseId = ($('#selected-warehouse-id').val() || '').toString().trim();
                const resolvedExact = resolveExactBarcodeScanResult(exactBarcodeMatches, preferredWarehouseId);
                const matchedResults = resolvedExact
                    ? [resolvedExact]
                    : (exactBarcodeMatches.length ? exactBarcodeMatches : (itemResults.length === 1 ? itemResults : []));
                if (matchedResults.length === 1) {
                    const shouldAutoAddFromPageScan = !!window._pendingPageBarcodeAutoAdd;
                    if (shouldAutoAddFromPageScan) window._pendingPageBarcodeAutoAdd = false;
                    const result = matchedResults[0];
                    const item = result.item;
                    const itemId = item.id;
                    var rawItemName = buildPurchaseScanDisplayName(item);
                    if (!rawItemName) {
                        rawItemName = (item.short_disc && item.short_disc.toLowerCase().indexOf('lorem') === -1) ? item.short_disc : ((item.pro_dis && item.pro_dis.toLowerCase().indexOf('lorem') === -1) ? item.pro_dis : ((item.partnumber_item ? item.partnumber_item.name : '') || ('Item #' + item.id)));
                    }
                    const itemName = (typeof stripHtml === 'function' ? stripHtml(rawItemName) : String(rawItemName).replace(/<[^>]*>/g, '')).trim() || rawItemName || ('Item #' + item.id);
                    const itemRate = item.packing_purchase_rate || item.total_price || 0;
                    const unit = (item.unit_item && (item.unit_item.name || item.unit_item.short_name)) ? (item.unit_item.name || item.unit_item.short_name) : 'Unit';
                    const warehouseId = result.warehouse_id || '';
                    
                    $('#item-search').val(itemName);
                    $('#selected-item-id').val(itemId);
                    $('#selected-item-is-temporary').val(item.is_temporary ? '1' : '0');
                    $('#item-unit').val(unit);
                    $('#item-rate').val(Math.round(parseFloat(itemRate) || 0));
                    updateRetailColumnByRate();
                    $('#item-search-results').hide();
                    $('#barcode-scan-input').val('');
                    
                    $.ajax({
                        url: (function() {
                            const baseUrl = '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId);
                            // IMPORTANT: In Claim Return / Claim Send, details must use claim stock (not normal stock).
                            if (typeof currentEntryType !== 'undefined' && (currentEntryType === 'claim' || currentEntryType === 'claim_send')) {
                                return baseUrl + '?entry_type=claim';
                            }
                            if (typeof currentEntryType !== 'undefined' && currentEntryType === 'scrap') {
                                return baseUrl + '?entry_type=scrap';
                            }
                            return baseUrl;
                        })(),
                        method: 'GET',
                        success: function(response) {
                            itemBaseRetailPrice = (response.retail_price != null && response.retail_price !== '' && !isNaN(parseFloat(response.retail_price))) ? parseFloat(response.retail_price) : null;
                            syncSelectedItemMasterSaleFromDetailsResponse(response);
                            syncSelectedItemCategoryFromDetailsResponse(response);
                            $('#selected-item-bar-code').val(response.bar_code || '');
                            if (response.tax_percentage != null && !isNaN(parseFloat(response.tax_percentage))) {
                                var gstVal = String(Math.round(parseFloat(response.tax_percentage)));
                                if ($('#item-tax-percent option[value="' + gstVal + '"]').length) $('#item-tax-percent').val(gstVal);
                            }
                            if (response.r_tax_percentage != null && response.r_tax_percentage !== '' && !isNaN(parseFloat(response.r_tax_percentage)))
                                $('#item-rtax-percent').val(parseFloat(response.r_tax_percentage));
                            if (response.amount_adjustment_pct != null && response.amount_adjustment_pct !== '') {
                                var adjVal = String(Math.round(parseFloat(response.amount_adjustment_pct)));
                                if ($('#item-retail-percentage option[value="' + adjVal + '"]').length) $('#item-retail-percentage').val(adjVal);
                                else $('#item-retail-percentage').val('');
                            } else {
                                $('#item-retail-percentage').val('');
                            }
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
                                $('#selected-item-image').val(response.image || '');
                                $('#item-search-image-preview').removeClass('d-none');
                            } else {
                                $('#item-search-image-preview').addClass('d-none');
                            }

                            // Stock will be set after loadItemStockStatus (correct per-warehouse value)
                            $('#item-search-stock').html('<span class="text-muted small">...</span>');
                            
                            loadItemStockStatus(itemId);
                            loadCustomerHistory(itemId);
                            if (shouldAutoAddFromPageScan) {
                                setTimeout(function() { $('#confirm-entry').trigger('click'); }, 120);
                            }
                        },
                        error: function() {
                            loadItemStockStatus(itemId);
                            loadCustomerHistory(itemId);
                            if (shouldAutoAddFromPageScan) {
                                setTimeout(function() { $('#confirm-entry').trigger('click'); }, 120);
                            }
                        }
                    });
                    $('#stock-status-section').show();
                } else {
                    window._pendingPageBarcodeAutoAdd = false;
                    if (itemResults.length > 0) {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({ icon: 'warning', title: 'Multiple items found', text: 'Barcode par multiple items mile. Please PURCHASE ITEM se select karein.' });
                        }
                        $('#item-search').trigger('input');
                    } else {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({ icon: 'warning', title: 'Item not found', text: 'Is barcode ka exact item nahi mila.' });
                        }
                    }
                    $('#barcode-scan-input').val('');
                }
            },
            error: function() {
                window._pendingPageBarcodeAutoAdd = false;
                resultsDiv.html('<div class="p-3 text-center text-danger"><i class="ti ti-alert-circle me-1"></i>Error. Try again.</div>').show();
                $('#barcode-scan-input').val('');
            }
            });
        };
        searchWithCandidate(0);
    }

    // Purchase page quick add: scan barcode -> direct add item in summary/cart
    var quickAddPurchaseBarcodeInFlight = false;
    function quickAddPurchaseItemByBarcode(barcode) {
        var code = (barcode || '').toString().trim();
        if (!code) return;
        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) return;
        if (quickAddPurchaseBarcodeInFlight) return;

        quickAddPurchaseBarcodeInFlight = true;
        $('#purchase-page-barcode-input').prop('disabled', true);

        var barcodeCandidates = getBarcodeQueryCandidates(code);
        var searchWithCandidate = function(candidateIndex) {
            var qVal = barcodeCandidates[candidateIndex] || code;
            $.ajax({
                url: "{{ route('purchases.items.ajax.search') }}",
                method: 'GET',
                data: { q: qVal, branch_id: branchId, limit: 20 },
                success: function(results) {
                var itemResults = (results || []).filter(function(r) { return r && r.type === 'item' && r.item; });
                if ((!itemResults || itemResults.length === 0) && candidateIndex + 1 < barcodeCandidates.length) {
                    searchWithCandidate(candidateIndex + 1);
                    return;
                }
                var normalizedCode = normalizeBarcodeValue(code);
                var exactMatches = itemResults.filter(function(r) {
                    var bar = normalizeBarcodeValue((r.item.bar_code || '') + '');
                    return bar && bar === normalizedCode;
                });
                var preferredWarehouseId = ($('#selected-warehouse-id').val() || '').toString().trim();
                var resolvedExact = resolveExactBarcodeScanResult(exactMatches, preferredWarehouseId);
                var matches = resolvedExact
                    ? [resolvedExact]
                    : (exactMatches.length ? exactMatches : (itemResults.length === 1 ? itemResults : []));

                if (matches.length !== 1) {
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire({
                            icon: 'warning',
                            title: itemResults.length === 0 ? 'Item not found' : 'Multiple items found',
                            text: itemResults.length === 0
                                ? 'Is barcode ka exact item nahi mila.'
                                : 'Barcode par multiple items mile. Please PURCHASE ITEM se select karein.'
                        });
                    }
                    return;
                }

                var result = matches[0];
                var item = result.item;
                var itemId = item.id;
                var rawName = buildPurchaseScanDisplayName(item);
                if (!rawName) {
                    rawName = (item.short_disc && item.short_disc.toLowerCase().indexOf('lorem') === -1)
                        ? item.short_disc
                        : ((item.pro_dis && item.pro_dis.toLowerCase().indexOf('lorem') === -1)
                            ? item.pro_dis
                            : ((item.partnumber_item ? item.partnumber_item.name : '') || ('Item #' + item.id)));
                }
                var itemName = (typeof stripHtml === 'function' ? stripHtml(rawName) : String(rawName).replace(/<[^>]*>/g, '')).trim() || rawName || ('Item #' + item.id);

                $.ajax({
                    url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
                    method: 'GET',
                    success: function(response) {
                        var qty = 1;
                        var rate = parseFloat(response.rate || response.total_price || item.packing_purchase_rate || 0) || 0;
                        var taxPct = parseFloat(response.tax_percentage || 0) || 0;
                        var subtotal = qty * rate;
                        var taxAmount = (subtotal * taxPct) / 100;
                        var total = subtotal + taxAmount;
                        var unit = response.unit || ((item.unit_item && (item.unit_item.name || item.unit_item.short_name)) ? (item.unit_item.name || item.unit_item.short_name) : 'Unit');
                        var selectedWhId = ($('#selected-warehouse-id').val() || '').toString().trim();
                        var selectedWhIds = ($('#selected-warehouse-ids').val() || '').toString().split(',').map(function(x){ return x.trim(); }).filter(Boolean);
                        if (!selectedWhId && selectedWhIds.length) selectedWhId = selectedWhIds[0];
                        var $selectedWhRow = $('#stock-status-list .stock-warehouse-item.bg-primary').first();
                        if (!$selectedWhRow.length) $selectedWhRow = $('#stock-status-list .stock-warehouse-item').first();
                        var rowWhId = ($selectedWhRow.attr('data-warehouse-id') || '').toString().trim();
                        var rowWhName = ($selectedWhRow.attr('data-display') || '').toString().trim();
                        var whId = response.warehouse_id || result.warehouse_id || selectedWhId || rowWhId || null;
                        var whName = result.warehouse_name || result.warehouse_display || null;
                        if (!whName && whId) {
                            var $matchWhRow = $('#stock-status-list .stock-warehouse-item[data-warehouse-id="' + whId + '"]').first();
                            whName = ($matchWhRow.attr('data-display') || '').toString().trim();
                        }
                        if (!whName) whName = rowWhName || (result.display || '');
                        // Ensure quick barcode add always binds a concrete warehouse, preferring "Display".
                        var finalizeQuickAdd = function(finalWhId, finalWhName) {
                            var whIdResolved = finalWhId || whId || null;
                            var whNameResolved = (finalWhName || whName || '').toString().trim();
                            if (!whNameResolved) {
                                // Scan flow default: always show Display warehouse label instead of dash.
                                whNameResolved = 'Display';
                            }
                            if (whIdResolved) {
                                $('#selected-warehouse-id').val(String(whIdResolved));
                                $('#selected-warehouse-ids').val(String(whIdResolved));
                            }

                            var retailPrice = (response.retail_price != null && response.retail_price !== '' && !isNaN(parseFloat(response.retail_price))) ? parseFloat(response.retail_price) : null;
                            var apiSaleRaw = (response.sale_price != null && response.sale_price !== '' && !isNaN(parseFloat(response.sale_price)))
                                ? parseFloat(response.sale_price)
                                : ((item.sale_price != null && item.sale_price !== '' && !isNaN(parseFloat(item.sale_price))) ? parseFloat(item.sale_price) : null);
                            var apiSalePos = (apiSaleRaw != null && apiSaleRaw > 0) ? apiSaleRaw : null;
                            var totalSalePos = (response.total_sale_price != null && response.total_sale_price !== '' && !isNaN(parseFloat(response.total_sale_price)) && parseFloat(response.total_sale_price) > 0)
                                ? parseFloat(response.total_sale_price) : null;
                            var spbPos = (response.sale_price_per_base != null && response.sale_price_per_base !== '' && !isNaN(parseFloat(response.sale_price_per_base)) && parseFloat(response.sale_price_per_base) > 0)
                                ? parseFloat(response.sale_price_per_base) : null;
                            var labelPrimarySale = apiSalePos || totalSalePos || spbPos || ((retailPrice != null && retailPrice > 0) ? retailPrice : null);
                            var itQ = (response.type || item.type || '').toString().toLowerCase();
                            var pnQ = (response.part_number != null && String(response.part_number).trim() !== '') ? String(response.part_number).trim() : '';
                            var qQ = (response.quality_name != null && String(response.quality_name).trim() !== '') ? String(response.quality_name).trim() : '';
                            var ptQ = (response.product_title != null && String(response.product_title).trim() !== '') ? String(response.product_title).trim() : '';
                            var typeLabQ = (response.product_type_label != null && String(response.product_type_label).trim() !== '') ? String(response.product_type_label).trim() : '';
                            if (!typeLabQ && response.category_name != null && String(response.category_name).trim() !== '' && !/^other$/i.test(String(response.category_name).trim())) {
                                typeLabQ = String(response.category_name).trim();
                            }
                            var compQ = (response.company_name != null && String(response.company_name).trim() !== '') ? String(response.company_name).trim() : ((item.company_item && item.company_item.name) ? String(item.company_item.name).trim() : '');
                            var quickDisplayName = itemName;
                            if (itQ === 'parts' || itQ === 'filters' || itQ === 'breakpad') {
                                var fmtQ = formatPurchasePartLineDisplay(pnQ, typeLabQ, qQ, compQ, ptQ);
                                if (fmtQ) quickDisplayName = fmtQ;
                            }
                            var newItem = {
                                id: itemCounter++,
                                item_id: itemId,
                                name: quickDisplayName,
                                company_name: compQ || null,
                                warehouse_id: whIdResolved,
                                warehouse_name: whNameResolved,
                                quantity: qty,
                                quantity_base: null,
                                base_unit: null,
                                unit: unit,
                                rate: rate,
                                sale_price: labelPrimarySale,
                                total_sale_price: totalSalePos,
                                sale_price_per_base: spbPos,
                                item_master_sale_price: labelPrimarySale,
                                item_master_retail_price: (retailPrice != null && retailPrice > 0) ? retailPrice : null,
                                retail_price: retailPrice,
                                retail_price_base: retailPrice,
                                retail_pct: null,
                                discount: 0,
                                tax_percentage: taxPct,
                                tax_amount: taxAmount,
                                total: total,
                                warranty: null,
                                entry_type: 'purchase',
                                image: (response.image || null),
                                images: (response.images && Array.isArray(response.images) && response.images.length) ? response.images.slice() : null,
                                is_temporary: !!(item.is_temporary),
                                bar_code: ((response.bar_code || item.bar_code || code) + '').trim() || null,
                                category_name: (function() {
                                    var c = (response.category_name != null) ? String(response.category_name).trim() : '';
                                    return (c && !/^other$/i.test(c)) ? c : null;
                                })(),
                                item_type: (response.type || item.type || '').toString().trim().toLowerCase() || null,
                                quality_name: (response.quality_name != null && String(response.quality_name).trim() !== '')
                                    ? String(response.quality_name).trim()
                                    : ((item.quality_item && item.quality_item.name) ? String(item.quality_item.name).trim() : null),
                                part_number: pnQ || null,
                                product_title: ptQ || null,
                                product_type_label: typeLabQ || null,
                                technology_name: (response.technology_name != null && String(response.technology_name).trim() !== '')
                                    ? String(response.technology_name).trim()
                                    : ((item.technology_item && item.technology_item.name) ? String(item.technology_item.name).trim() : null),
                                voice_url: (response.voice_url && String(response.voice_url).trim()) ? String(response.voice_url).trim() : null
                            };
                            if (item.liter_per_can != null && item.liter_per_can !== '' && !isNaN(parseFloat(item.liter_per_can)) && parseFloat(item.liter_per_can) > 0) {
                                newItem.liter_per_can = parseFloat(item.liter_per_can);
                                newItem.rate_per_liter = rate / parseFloat(item.liter_per_can);
                            }

                            // If same item is scanned again in same warehouse, increase quantity instead of adding a new row.
                            var existingIndex = purchaseItems.findIndex(function(it) {
                                return shouldMergePurchaseLines(it, newItem);
                            });

                            var scannedRowId = null;
                            if (existingIndex >= 0) {
                                var existing = purchaseItems[existingIndex];
                                scannedRowId = existing.id;
                                var prevQty = parseFloat(existing.quantity) || 0;
                                var addQty = parseFloat(newItem.quantity) || 0;
                                var mergedQty = prevQty + addQty;
                                existing.quantity = mergedQty;

                                var mergedRate = parseFloat(existing.rate) || parseFloat(newItem.rate) || 0;
                                var mergedDiscount = parseFloat(existing.discount) || 0;
                                var mergedTaxPct = parseFloat(existing.tax_percentage) || 0;
                                var mergedSubtotal = (mergedQty * mergedRate) - mergedDiscount;
                                var mergedTaxAmount = (mergedSubtotal * mergedTaxPct) / 100;
                                existing.tax_amount = mergedTaxAmount;
                                existing.total = mergedSubtotal + mergedTaxAmount;

                                // Keep warehouse label/display synced.
                                existing.warehouse_name = existing.warehouse_name || newItem.warehouse_name || '';
                                existing.bar_code = existing.bar_code || newItem.bar_code || null;
                                if (!existing.image && newItem.image) existing.image = newItem.image;
                                if (!existing.voice_url && newItem.voice_url) existing.voice_url = newItem.voice_url;
                                if ((!existing.images || !existing.images.length) && newItem.images && newItem.images.length) existing.images = newItem.images.slice();
                                if (newItem.item_type && (!existing.item_type || String(existing.item_type).trim() === '')) {
                                    existing.item_type = newItem.item_type;
                                }
                                if (newItem.quality_name && (!existing.quality_name || String(existing.quality_name).trim() === '')) {
                                    existing.quality_name = newItem.quality_name;
                                }
                                if (newItem.technology_name && (!existing.technology_name || String(existing.technology_name).trim() === '')) {
                                    existing.technology_name = newItem.technology_name;
                                }

                                // For can/liter items, keep base qty cumulative when available.
                                if (newItem.liter_per_can != null && !isNaN(parseFloat(newItem.liter_per_can))) {
                                    var lpc = parseFloat(newItem.liter_per_can) || 0;
                                    if (lpc > 0) {
                                        var prevBaseQty = parseFloat(existing.quantity_base) || 0;
                                        existing.quantity_base = prevBaseQty + (addQty * lpc);
                                        existing.base_unit = existing.base_unit || 'Liter';
                                        existing.liter_per_can = existing.liter_per_can || lpc;
                                        existing.rate_per_liter = mergedRate / lpc;
                                    }
                                }
                            } else {
                                purchaseItems.push(newItem);
                                scannedRowId = newItem.id;
                            }
                            sortPurchaseItemsByEntryType();
                            $('#items-tbody').empty();
                            purchaseItems.forEach(function(it) { addItemToTable(it); });
                            if (scannedRowId != null) highlightScannedPurchaseRow(scannedRowId);
                            updatePurchaseTableRetailColumnVisibility();
                            calculateTotals();
                            syncCartToServer();
                            if (typeof updatePurchaseBulkRetailBar === 'function') updatePurchaseBulkRetailBar();
                            if (purchaseItems.length > 0) $('#payment-amount-row').show();
                        };

                        if (!whId || !whName) {
                            $.ajax({
                                url: '{{ route("purchases.items.stock.status", ":id") }}'.replace(':id', itemId),
                                method: 'GET',
                                success: function(stockRows) {
                                    var rows = (stockRows || []).filter(function(s) { return s && s.type === 'warehouse'; });
                                    if (branchId) rows = rows.filter(function(s) { return String(s.branch_id || '') === String(branchId); });
                                    var displayRow = rows.find(function(s) { return ((s.display || s.name || '') + '').trim().toLowerCase() === 'display'; });
                                    var selectedRow = rows.find(function(s) { return String(s.id || '') === String(selectedWhId || rowWhId || ''); });
                                    var firstRow = rows[0] || null;
                                    var pick = displayRow || selectedRow || firstRow || null;
                                    finalizeQuickAdd(
                                        pick ? (pick.id || whId) : whId,
                                        pick ? ((pick.display || pick.name || whName || '') + '').trim() : whName
                                    );
                                },
                                error: function() {
                                    finalizeQuickAdd(whId, whName);
                                }
                            });
                        } else {
                            finalizeQuickAdd(whId, whName);
                        }
                    },
                    error: function() {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Item details load nahi ho saki. Dobara scan karein.' });
                        }
                    }
                });
            },
            error: function() {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Barcode search failed. Dobara try karein.' });
                }
            },
            complete: function() {
                quickAddPurchaseBarcodeInFlight = false;
                $('#purchase-page-barcode-input').prop('disabled', false).val('');
                focusPurchaseBarcodeInput(true);
            }
            });
        };
        searchWithCandidate(0);
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
    
    // Enter in main search: first item → else "No items found" → Add New Item → else confirm / re-search
    $(document).on('keydown', '#item-search', function(e) {
        if (e.which !== 13) return;
        e.preventDefault();
        const $results = $('#item-search-results');
        const $first = $results.find('.item-search-result').first();
        const $addNewBtn = $results.find('.btn-open-add-item-modal').first();
        const hasSelectedItem = (($('#selected-item-id').val() || '').toString().trim() !== '');
        if ($results.is(':visible') && $first.length) {
            $first[0].click();
        } else if ($results.is(':visible') && $addNewBtn.length) {
            $addNewBtn[0].click();
        } else if (hasSelectedItem) {
            $('#confirm-entry').trigger('click');
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
    
    // Add-item modal: Camera button next to barcode – open camera; on scan fill barcode and search
    $(document).on('click', '#add-item-modal-camera-btn', function() {
        if (typeof Html5Qrcode === 'undefined') {
            alert('Camera scanner library not loaded.');
            return;
        }
        window._cameraFromAddItemModal = true;
        $('#camera-barcode-reader').empty().css({ width: '100%', minHeight: '240px' });
        $('#camera-barcode-modal').modal('show');
    });
    
    // Start camera only after modal is visible
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
                    // Opened from Add Item modal: fill barcode input and run search (modal already open)
                    if (window._cameraFromAddItemModal) {
                        window._cameraFromAddItemModal = false;
                        $('#barcode-scan-input').val(decodedText);
                        runBarcodeSearch(decodedText);
                        return;
                    }
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

    // ===== Claim Stock Summary (Claim In badge) =====
    function loadPurchaseClaimStockSummary() {
        var branchId = ($('#purchaseBranchId').val() || '').toString();
        var $text = $('#purchase-claim-stock-summary-text');
        if (!$text.length) return;
        if (!branchId) {
            $text.text('—');
            return;
        }
        $.ajax({
            url: '{{ route("sales.claim.stock.summary") }}',
            method: 'GET',
            data: { branch_id: branchId },
            success: function(res) {
                var total = 0;
                if (res && typeof res.total_quantity !== 'undefined') total = parseFloat(res.total_quantity) || 0;
                var display = (res && res.display) ? String(res.display) : null;
                if (display) {
                    $text.text(display);
                } else {
                    $text.text((Number.isInteger(total) ? total : total.toFixed(2)) + ' Piece');
                }
            },
            error: function() {
                $text.text('—');
            }
        });
    }

    // Refresh on page load + whenever branch changes
    $(document).ready(function() { loadPurchaseClaimStockSummary(); });
    $(document).on('change', '#purchaseBranchId', function() { loadPurchaseClaimStockSummary(); });
    if (typeof window.selectPurchaseBranch === 'function') {
        var _oldSelectPurchaseBranch = window.selectPurchaseBranch;
        window.selectPurchaseBranch = function() {
            var r = _oldSelectPurchaseBranch.apply(this, arguments);
            setTimeout(loadPurchaseClaimStockSummary, 250);
            return r;
        };
    }

    // ===== Claim Send Summary (Claim Send badge) =====
    function loadPurchaseClaimSendStockSummary() {
        var branchId = ($('#purchaseBranchId').val() || '').toString();
        var $text = $('#purchase-claim-send-stock-summary-text');
        if (!$text.length) return;
        if (!branchId) {
            $text.text('—');
            return;
        }
        $.ajax({
            url: '{{ route("sales.claim.send.stock.summary") }}',
            method: 'GET',
            data: { branch_id: branchId },
            success: function(res) {
                var total = 0;
                if (res && typeof res.total_quantity !== 'undefined') total = parseFloat(res.total_quantity) || 0;
                var display = (res && res.display) ? String(res.display) : null;
                if (display) {
                    $text.text(display);
                } else {
                    $text.text((Number.isInteger(total) ? total : total.toFixed(2)) + ' Piece');
                }
            },
            error: function() {
                $text.text('—');
            }
        });
    }

    // Refresh on page load + whenever branch changes
    $(document).ready(function() { loadPurchaseClaimSendStockSummary(); });
    $(document).on('change', '#purchaseBranchId', function() { loadPurchaseClaimSendStockSummary(); });
    if (typeof window.selectPurchaseBranch === 'function') {
        var _oldSelectPurchaseBranch2 = window.selectPurchaseBranch;
        window.selectPurchaseBranch = function() {
            var r = _oldSelectPurchaseBranch2.apply(this, arguments);
            setTimeout(loadPurchaseClaimSendStockSummary, 250);
            return r;
        };
    }

    // Unified Claim History (In/Sent/Reverse)
    window._purchaseClaimReverseLogs = window._purchaseClaimReverseLogs || [];
    window._purchaseClaimInRecords = window._purchaseClaimInRecords || [];
    window._purchaseClaimSentRecords = window._purchaseClaimSentRecords || [];
    window._purchaseClaimActiveTab = 'in';
    window._purchaseClaimAgingAlertShownForOpen = false;
    window._purchaseClaimSendStatusNote = '';

    function formatClaimHistoryDateTime(ts) {
        var d = new Date((Number(ts) || Math.floor(Date.now() / 1000)) * 1000);
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        var h = String(d.getHours()).padStart(2, '0');
        var min = String(d.getMinutes()).padStart(2, '0');
        return { date: y + '-' + m + '-' + day, time: h + ':' + min, ts: Math.floor(d.getTime() / 1000) };
    }

    function updatePurchaseClaimHistoryCounts() {
        var inCount = (window._purchaseClaimInRecords || []).length;
        var sentCount = (window._purchaseClaimSentRecords || []).length;
        var revCount = (window._purchaseClaimReverseLogs || []).length;
        $('#purchase-claim-history-count-in, #purchase-claim-tab-count-in').text(inCount);
        $('#purchase-claim-history-count-sent, #purchase-claim-tab-count-sent').text(sentCount);
        $('#purchase-claim-history-count-reverse, #purchase-claim-tab-count-reverse').text(revCount);
    }

    function updatePurchaseClaimTrendSummary() {
        var inRows = window._purchaseClaimInRecords || [];
        var sentRows = window._purchaseClaimSentRecords || [];
        var revRows = window._purchaseClaimReverseLogs || [];
        var sumQty = function(rows) {
            var t = 0;
            (rows || []).forEach(function(r) {
                var q = parseFloat((r.display_quantity != null) ? r.display_quantity : (r.quantity || 0)) || 0;
                t += q;
            });
            return t;
        };
        var inTotal = sumQty(inRows);
        var sentTotal = sumQty(sentRows);
        var revTotal = sumQty(revRows);
        var exposure = Math.max(0, sentTotal - revTotal);
        $('#purchase-claim-trend-total-in').text(inTotal.toFixed(2).replace(/\.00$/, ''));
        $('#purchase-claim-trend-total-sent').text(sentTotal.toFixed(2).replace(/\.00$/, ''));
        $('#purchase-claim-trend-total-reversed').text(revTotal.toFixed(2).replace(/\.00$/, ''));
        $('#purchase-claim-trend-exposure').text(exposure.toFixed(2).replace(/\.00$/, ''));
    }

    // Claim history: apply current date filters to both table AND header counts/trend summary.
    function getPurchaseClaimFilterTs() {
        var fromDate = ($('#purchase-claim-filter-from').val() || '').trim();
        var toDate = ($('#purchase-claim-filter-to').val() || '').trim();
        var claimIdQ = ($('#purchase-claim-filter-id').val() || '').trim().toLowerCase();
        var thresholdDays = getClaimAgingThresholdDays();
        var overdueOnly = $('#purchase-claim-overdue-only').is(':checked');

        var fromTs = fromDate ? Math.floor(new Date(fromDate + 'T00:00:00').getTime() / 1000) : null;
        var toTs = toDate ? Math.floor(new Date(toDate + 'T23:59:59').getTime() / 1000) : null;
        return { fromTs: fromTs, toTs: toTs, claimIdQ: claimIdQ, thresholdDays: thresholdDays, overdueOnly: overdueOnly };
    }

    function getPurchaseClaimRowsForTab(tab) {
        if (tab === 'in') return (window._purchaseClaimInRecords || []).slice();
        if (tab === 'sent') return (window._purchaseClaimSentRecords || []).slice();
        return (window._purchaseClaimReverseLogs || []).slice(); // reverse
    }

    function filterPurchaseClaimRowsByCurrentFilters(rows, tab) {
        var f = getPurchaseClaimFilterTs();
        var fromTs = f.fromTs;
        var toTs = f.toTs;
        var claimIdQ = f.claimIdQ;
        // Note: "Overdue only" is meant for row-level aging buckets inside the active tab.
        // Header counts + trend totals should follow date range + claim-id filters only.

        rows = rows.filter(function(r) {
            var ts = Number(r.datetime_sort || r.reverse_ts || 0);
            if (fromTs != null && ts < fromTs) return false;
            if (toTs != null && ts > toTs) return false;
            if (!claimIdQ) return true;
            var bag = [
                r.claim_id, r.reference_no, r.invoice_no, r.sale_id, r.purchase_id,
                r.reverse_claim_id, r.original_sent_to
            ].map(function(v) { return String(v || '').toLowerCase(); }).join(' ');
            return bag.indexOf(claimIdQ) !== -1;
        });

        rows.sort(function(a, b) {
            return Number(b.datetime_sort || b.reverse_ts || 0) - Number(a.datetime_sort || a.reverse_ts || 0);
        });
        return rows;
    }

    function updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters() {
        if (!$('#purchase-claim-filter-from').length) return;
        var inRowsFiltered = filterPurchaseClaimRowsByCurrentFilters(getPurchaseClaimRowsForTab('in'), 'in');
        var sentRowsFiltered = filterPurchaseClaimRowsByCurrentFilters(getPurchaseClaimRowsForTab('sent'), 'sent');
        var revRowsFiltered = filterPurchaseClaimRowsByCurrentFilters(getPurchaseClaimRowsForTab('reverse'), 'reverse');

        $('#purchase-claim-history-count-in, #purchase-claim-tab-count-in').text(inRowsFiltered.length);
        $('#purchase-claim-history-count-sent, #purchase-claim-tab-count-sent').text(sentRowsFiltered.length);
        $('#purchase-claim-history-count-reverse, #purchase-claim-tab-count-reverse').text(revRowsFiltered.length);

        var sumQty = function(rows) {
            var t = 0;
            (rows || []).forEach(function(r) {
                var q = parseFloat((r.display_quantity != null) ? r.display_quantity : (r.quantity || 0)) || 0;
                t += q;
            });
            return t;
        };

        var inTotal = sumQty(inRowsFiltered);
        var sentTotal = sumQty(sentRowsFiltered);
        var revTotal = sumQty(revRowsFiltered);
        var exposure = Math.max(0, sentTotal - revTotal);

        $('#purchase-claim-trend-total-in').text(inTotal.toFixed(2).replace(/\.00$/, ''));
        $('#purchase-claim-trend-total-sent').text(sentTotal.toFixed(2).replace(/\.00$/, ''));
        $('#purchase-claim-trend-total-reversed').text(revTotal.toFixed(2).replace(/\.00$/, ''));
        $('#purchase-claim-trend-exposure').text(exposure.toFixed(2).replace(/\.00$/, ''));
    }

    function setPurchaseClaimHistoryFiltersToToday() {
        var today = new Date();
        var tYmd = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        $('#purchase-claim-filter-from').val(tYmd);
        $('#purchase-claim-filter-to').val(tYmd);
        $('#purchase-claim-filter-id').val('');
        $('#purchase-claim-overdue-only').prop('checked', false);
    }

    function getClaimAgingThresholdDays() {
        var v = parseInt(($('#purchase-claim-aging-threshold').val() || '7'), 10);
        if (isNaN(v) || v <= 0) v = 7;
        return v;
    }

    function getClaimAgeDays(ts) {
        var nowTs = Math.floor(Date.now() / 1000);
        var age = Math.floor((nowTs - (Number(ts) || nowTs)) / 86400);
        return age < 0 ? 0 : age;
    }

    function claimHistoryExtractBatterySequence(displayName) {
        var n = (displayName != null) ? String(displayName).trim() : '';
        if (!n) return n;
        if (!/[•·]/.test(n)) return n;
        var parts = n.split(/\s*[•·]\s*/);
        var first = (parts[0] || '').trim();
        if (!first) return n;
        return (first.split(/\s+/)[0] || first).trim();
    }

    /** Read image fields from claim modal row checkboxes (data-* set from API). */
    function parseClaimLoadCheckboxImageAttrs($cb) {
        var imgUrl = ($cb.attr('data-item-image-url') || '').toString().trim();
        var imagePath = ($cb.attr('data-item-image-path') || '').toString().trim();
        var imgsJson = ($cb.attr('data-item-images-json') || '').toString().trim();
        var imagesArr = null;
        if (imgsJson) {
            try {
                var parsed = JSON.parse(imgsJson);
                if (Array.isArray(parsed) && parsed.length) imagesArr = parsed;
            } catch (e) {}
        }
        return {
            image: imgUrl || null,
            image_path: imagePath || null,
            images: imagesArr
        };
    }

    // ===== Duplicate load prevention (Claim Send / Claim Return) =====
    window._purchaseClaimLoadedKeys = window._purchaseClaimLoadedKeys || {};
    function claimLoadedKeyForRecord(r) {
        var itemId = (r && r.item_id != null) ? String(r.item_id) : '';
        var whId = (r && r.warehouse_id != null) ? String(r.warehouse_id) : '';
        var ref = String((r && (r.claim_id || r.reference_no || r.invoice_no || r.reverse_claim_id || '')) || '').trim().toLowerCase();
        // Key must be stable across re-renders. Ref may be empty; still prevent duplicates per item+warehouse.
        return [itemId, whId, ref].join('|');
    }
    function hasLoadedClaimRecord(r, kind) {
        var k = (kind || 'claim_send') + '|' + claimLoadedKeyForRecord(r);
        return !!window._purchaseClaimLoadedKeys[k];
    }
    function markLoadedClaimRecord(r, kind) {
        var k = (kind || 'claim_send') + '|' + claimLoadedKeyForRecord(r);
        window._purchaseClaimLoadedKeys[k] = true;
    }
    function warnAlreadyLoaded() {
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({ icon: 'warning', title: 'Already loaded', text: 'This item is already loaded.' });
        } else {
            alert('This item is already loaded.');
        }
    }

    /** Push one claim_send line from Claim In / Claim Sent history row (same rules as detail modal Load). */
    function pushClaimSendLineFromClaimHistoryRecord(r) {
        var out = { ok: false, adjusted: false, unknownAvailability: false, duplicate: false, moved_qty: 0 };
        if (hasLoadedClaimRecord(r, 'claim_send')) {
            out.duplicate = true;
            return out;
        }
        var itemId = parseInt(String(r.item_id || '0'), 10);
        if (isNaN(itemId) || itemId <= 0) return out;
        var itemNameRaw = (r.item_name || '').toString().trim() || ('Item #' + itemId);
        var warehouseIdRaw = r.warehouse_id;
        var warehouseId = warehouseIdRaw != null && warehouseIdRaw !== '' ? parseInt(warehouseIdRaw, 10) : NaN;
        if (isNaN(warehouseId) || warehouseId <= 0) warehouseId = null;

        var qty = Math.abs(parseFloat((r.display_quantity != null) ? r.display_quantity : (r.quantity != null ? r.quantity : '0')) || 0);
        if (!qty || qty <= 0) return out;
        var availableQty = Math.abs(parseFloat(r.available_claim_qty != null ? r.available_claim_qty : '0') || 0);
        if (availableQty > 0 && qty > availableQty) {
            qty = availableQty;
            out.adjusted = true;
        }
        if (availableQty <= 0) {
            out.unknownAvailability = true;
            console.warn('Claim Send availability unresolved', {
                item_id: itemId,
                warehouse_id: warehouseId,
                stock_type: 'claim',
                available_stock: availableQty
            });
        }

        var warehouseName = (r.warehouse_name || r.branch_name || '').toString().trim() || null;
        var itemName = claimHistoryExtractBatterySequence(itemNameRaw) || itemNameRaw;
        var claimImg = {
            image: (r.image != null && typeof r.image === 'string' && r.image.trim()) ? r.image.trim() : null,
            image_path: (r.image_path != null && typeof r.image_path === 'string' && r.image_path.trim()) ? r.image_path.trim() : null,
            images: (r.images && Array.isArray(r.images) && r.images.length) ? r.images.slice() : null
        };

        var newItem = {
            id: itemCounter++,
            item_id: itemId,
            item_line: null,
            name: itemName,
            /** Full label from API (e.g. GL50 • 11PL • 38AH • AGS); `name` may be short for the table row. */
            name_full: itemNameRaw,
            warehouse_id: warehouseId,
            warehouse_name: warehouseName,
            available_claim_qty: (availableQty > 0 ? availableQty : null),
            quantity: qty,
            quantity_base: null,
            base_unit: null,
            unit: 'Unit',
            rate: 1,
            retail_price: null,
            retail_price_base: null,
            retail_pct: null,
            discount: 0,
            tax_percentage: 0,
            tax_amount: 0,
            total: qty,
            warranty: null,
            entry_type: 'claim_send',
            purchase_order_item_id: null,
            is_temporary: false,
            image: claimImg.image,
            image_path: claimImg.image_path,
            images: claimImg.images,
            verified: 0
        };
        purchaseItems.push(newItem);
        markLoadedClaimRecord(r, 'claim_send');
        out.moved_qty = qty;
        out.ok = true;
        return out;
    }

    // After sending a claim from Claim History (UI action), move it from "Claim In" to "Claim Sent" immediately.
    function purchaseClaimHistoryMoveInToSent(r, qtyMoved) {
        try {
            var qty = Math.abs(parseFloat(qtyMoved || 0) || 0);
            if (!qty) return;
            var now = formatClaimHistoryDateTime(Math.floor(Date.now() / 1000));
            var claimRef = (r && (r.claim_id || r.reference_no || r.invoice_no)) || '';
            claimRef = String(claimRef || '').trim();

            // Ensure the original in-row is effectively "consumed".
            if (r) {
                r.quantity = 0;
                r.display_quantity = 0;
            }

            // Add to Sent list (optimistic UI). Real backend will reflect on refresh.
            window._purchaseClaimSentRecords = window._purchaseClaimSentRecords || [];
            var sentRow = Object.assign({}, r, {
                quantity: qty,
                display_quantity: qty,
                date: now.date,
                time: now.time,
                datetime_sort: now.ts,
                status_note: 'Sent from claim stock (just now).' + (claimRef ? (' · Ref: ' + claimRef) : '')
            });
            window._purchaseClaimSentRecords.unshift(sentRow);

            // Remove from In list (qty becomes 0 and hidden).
            window._purchaseClaimInRecords = (window._purchaseClaimInRecords || []).filter(function(x) {
                if (!x) return false;
                // Prefer strict identity when available.
                if (x === r) return false;
                var sameItem = String(x.item_id || '') === String(r.item_id || '');
                var sameWh = String(x.warehouse_id || '') === String(r.warehouse_id || '');
                var aRef = String((x.claim_id || x.reference_no || x.invoice_no || '') || '').trim();
                var bRef = String((r.claim_id || r.reference_no || r.invoice_no || '') || '').trim();
                if (sameItem && sameWh && aRef && bRef && aRef.toLowerCase() === bRef.toLowerCase()) return false;
                return true;
            });

            updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
            if ($('#purchase-claim-history-modal').hasClass('show')) {
                // Make the move obvious: switch to "Claim Sent" and re-render.
                window._purchaseClaimActiveTab = 'sent';
                $('#purchase-claim-history-tabs .nav-link').removeClass('active');
                $('#purchase-claim-history-tabs .nav-link[data-claim-tab="sent"]').addClass('active');
                renderPurchaseClaimHistoryTab();
            }
        } catch (e) {
            console.warn('purchaseClaimHistoryMoveInToSent failed', e);
        }
    }

    // After re-sending from Claim Reverse, treat it like claim-stock source and move it to Sent.
    // If partial send, reduce reverse row quantity; if fully sent, remove it from reverse list.
    function purchaseClaimHistoryMoveReverseToSent(r, qtyMoved) {
        try {
            var qty = Math.abs(parseFloat(qtyMoved || 0) || 0);
            if (!qty) return;
            var now = formatClaimHistoryDateTime(Math.floor(Date.now() / 1000));
            var revRef = (r && (r.reverse_claim_id || r.claim_id || r.reference_no || r.invoice_no)) || '';
            revRef = String(revRef || '').trim();

            var remaining = Math.max(0, (parseFloat((r.display_quantity != null) ? r.display_quantity : (r.quantity || 0)) || 0) - qty);
            if (r) {
                r.quantity = remaining;
                r.display_quantity = remaining;
            }

            window._purchaseClaimSentRecords = window._purchaseClaimSentRecords || [];
            var sentRow = Object.assign({}, r, {
                quantity: qty,
                display_quantity: qty,
                date: now.date,
                time: now.time,
                datetime_sort: now.ts,
                status_note: 'Re-sent from reversed claim stock (just now).' + (revRef ? (' · Reverse Ref: ' + revRef) : '')
            });
            window._purchaseClaimSentRecords.unshift(sentRow);

            window._purchaseClaimReverseLogs = (window._purchaseClaimReverseLogs || []).filter(function(x) {
                if (!x) return false;
                if (x === r) return remaining > 0;
                var sameItem = String(x.item_id || '') === String(r.item_id || '');
                var sameWh = String(x.warehouse_id || '') === String(r.warehouse_id || '');
                var aRef = String((x.reverse_claim_id || x.claim_id || '') || '').trim().toLowerCase();
                var bRef = String((r.reverse_claim_id || r.claim_id || '') || '').trim().toLowerCase();
                if (sameItem && sameWh && aRef && bRef && aRef === bRef) return remaining > 0;
                return true;
            });

            updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
            if ($('#purchase-claim-history-modal').hasClass('show')) {
                window._purchaseClaimActiveTab = 'sent';
                $('#purchase-claim-history-tabs .nav-link').removeClass('active');
                $('#purchase-claim-history-tabs .nav-link[data-claim-tab="sent"]').addClass('active');
                renderPurchaseClaimHistoryTab();
            }
        } catch (e) {
            console.warn('purchaseClaimHistoryMoveReverseToSent failed', e);
        }
    }

    function claimHistoryRowEligibleForClaimSend(r) {
        var qtyNum = parseFloat((r.display_quantity != null) ? r.display_quantity : (r.quantity != null ? r.quantity : '0')) || 0;
        if (qtyNum <= 0) return false;
        var availRaw = r.available_claim_qty;
        var availNum = (availRaw != null && availRaw !== '') ? parseFloat(availRaw) : null;
        if (availNum != null && !isNaN(availNum) && availNum <= 0) return false;
        return true;
    }

    function updatePurchaseClaimHistoryLoadButtonLabel() {
        var tab = window._purchaseClaimActiveTab || 'in';
        var $btn = $('#purchase-claim-history-load-btn');
        var $lab = $('#purchase-claim-history-load-btn-label');
        if (!$lab.length) return;
        if (tab === 'sent') {
            $lab.text('Load selected to purchase');
            $btn.attr('title', 'Adds selected rows as claim-send lines. Only when claim stock is available for that item/warehouse — never uses normal inventory.');
        } else if (tab === 'reverse') {
            $lab.text('Load selected to purchase');
            $btn.attr('title', 'Adds selected reversed rows as outgoing claim-send lines from claim stock (same rules as Claim In).');
        } else {
            $lab.text('Load selected to purchase');
            $btn.attr('title', 'Adds selected claim-in rows as outgoing claim-send lines from claim stock.');
        }
    }

    function updatePurchaseClaimHistoryLoadButtonState() {
        var tab = window._purchaseClaimActiveTab || 'in';
        // Reverse tab is a functional claim-stock source (rejected items returned into claim stock).
        $('#purchase-claim-history-load-btn').prop('disabled', false);
        updatePurchaseClaimHistoryLoadButtonLabel();
    }

    /** Single-row add: Claim In = send to supplier from claim stock; Claim Sent = same load rules when claim stock exists (history row). */
    window.purchaseClaimHistorySendSingle = function(rowIdx) {
        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first.' });
            else alert('Please select a branch first.');
            return;
        }
        var tab = window._purchaseClaimActiveTab || 'in';
        if (tab !== 'in' && tab !== 'sent' && tab !== 'reverse') {
            if (typeof Swal !== 'undefined' && Swal.fire) Swal.fire({ icon: 'info', title: 'Claim History', text: 'Use Claim In, Claim Sent or Claim Reverse tab for this action.' });
            return;
        }
        var rows = window._purchaseClaimHistoryRenderedRows || [];
        var r = rows[rowIdx];
        if (!r) return;
        if (hasLoadedClaimRecord(r, 'claim_send')) {
            warnAlreadyLoaded();
            // Force re-render so button/checkbox reflect loaded state.
            if ($('#purchase-claim-history-modal').hasClass('show')) renderPurchaseClaimHistoryTab();
            return;
        }
        if (!claimHistoryRowEligibleForClaimSend(r)) {
            if (typeof Swal !== 'undefined' && Swal.fire) Swal.fire({ icon: 'warning', title: 'Not eligible', text: 'This claim row cannot be sent (no quantity or no available claim stock).' });
            return;
        }
        var res = pushClaimSendLineFromClaimHistoryRecord(r);
        if (res.duplicate) {
            warnAlreadyLoaded();
            if ($('#purchase-claim-history-modal').hasClass('show')) renderPurchaseClaimHistoryTab();
            return;
        }
        if (!res.ok) return;
        // Reflect immediately in Claim History tabs.
        if (tab === 'in') {
            purchaseClaimHistoryMoveInToSent(r, res.moved_qty);
        } else if (tab === 'reverse') {
            purchaseClaimHistoryMoveReverseToSent(r, res.moved_qty);
        }
        if (res.unknownAvailability || res.adjusted) {
            var msg = [];
            if (res.adjusted) msg.push('Quantity adjusted to available claim stock');
            if (res.unknownAvailability) msg.push('Loaded with live stock check on submit');
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'info', title: 'Claim Send Quantity', text: msg.join('. ') + '.' });
            }
        }
        sortPurchaseItemsByEntryType();
        $('#items-tbody').empty();
        purchaseItems.forEach(function(item) { addItemToTable(item); });
        $('#empty-items-state').hide();
        $('#items-list').show();
        $('#payment-amount-row').show();
        calculateTotals();
        syncCartToServer();
        updatePurchaseTableRetailColumnVisibility();
        updatePurchasePrintButton();
    };

    function renderPurchaseClaimHistoryTab() {
        var tab = window._purchaseClaimActiveTab || 'in';
        var rows = [];
        if (tab === 'in') rows = (window._purchaseClaimInRecords || []).slice();
        else if (tab === 'sent') rows = (window._purchaseClaimSentRecords || []).slice();
        else rows = (window._purchaseClaimReverseLogs || []).slice();

        var fromDate = ($('#purchase-claim-filter-from').val() || '').trim();
        var toDate = ($('#purchase-claim-filter-to').val() || '').trim();
        var claimIdQ = ($('#purchase-claim-filter-id').val() || '').trim().toLowerCase();
        var fromTs = fromDate ? Math.floor(new Date(fromDate + 'T00:00:00').getTime() / 1000) : null;
        var toTs = toDate ? Math.floor(new Date(toDate + 'T23:59:59').getTime() / 1000) : null;
        var thresholdDays = getClaimAgingThresholdDays();
        var overdueOnly = $('#purchase-claim-overdue-only').is(':checked');

        rows = rows.filter(function(r) {
            var ts = Number(r.datetime_sort || r.reverse_ts || 0);
            if (fromTs != null && ts < fromTs) return false;
            if (toTs != null && ts > toTs) return false;
            if (!claimIdQ) return true;
            var bag = [
                r.claim_id, r.reference_no, r.invoice_no, r.sale_id, r.purchase_id,
                r.reverse_claim_id, r.original_sent_to
            ].map(function(v) { return String(v || '').toLowerCase(); }).join(' ');
            return bag.indexOf(claimIdQ) !== -1;
        });
        if (overdueOnly) {
            rows = rows.filter(function(r) {
                var ts = Number(r.datetime_sort || r.reverse_ts || 0);
                var pending = (tab === 'in' || tab === 'sent');
                return pending && (getClaimAgeDays(ts) > thresholdDays);
            });
        }

        rows.sort(function(a, b) {
            return Number(b.datetime_sort || b.reverse_ts || 0) - Number(a.datetime_sort || a.reverse_ts || 0);
        });

        var safe = function(v) { return String(v || '').replace(/</g, '&lt;').replace(/>/g, '&gt;'); };
        var $tb = $('#purchase-claim-history-tbody');
        $tb.empty();
        $('#purchase-claim-history-select-all').prop('checked', false).prop('indeterminate', false);
        var pendingOpenCount = 0;
        var nearDueCount = 0;
        var overdueCount = 0;
        if (!rows.length) {
            window._purchaseClaimHistoryRenderedRows = [];
            $('#purchase-claim-aging-open-count').text(0);
            $('#purchase-claim-aging-near-count').text(0);
            $('#purchase-claim-aging-overdue-count').text(0);
            $('#purchase-claim-history-empty').removeClass('d-none');
            updatePurchaseClaimHistoryLoadButtonState();
            return;
        }
        $('#purchase-claim-history-empty').addClass('d-none');

        window._purchaseClaimHistoryRenderedRows = rows.slice();

        rows.forEach(function(r, idx) {
            var qtyRaw = (r.display_quantity != null) ? r.display_quantity : (r.quantity != null ? r.quantity : '');
            var qtyNum = parseFloat(qtyRaw);
            var qtyDisplay = (!isNaN(qtyNum) && qtyNum < 0) ? Math.abs(qtyNum) : qtyRaw;
            var claimId = r.claim_id || r.reference_no || r.invoice_no || r.reverse_claim_id || '';
            var itemLineExtra = (r.item_line || r.item_meta) ? ('<div class="small text-muted mt-1">' + safe([r.item_line, r.item_meta].filter(Boolean).join(' · ')) + '</div>') : '';
            var itemName = '<div class="fw-semibold">' + safe(r.item_name || '') + '</div>' + itemLineExtra;
            var traceability = '';
            var safeAttr = function(v) { return String(v || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); };
            var actionHtml = '<span class="text-muted small">—</span>';
            var statusNote = '';
            var ts = Number(r.datetime_sort || r.reverse_ts || 0);
            var ageDays = getClaimAgeDays(ts);
            // Aging buckets: only "Claim In" rows are treated as pending in claim stock. "Claim Sent" is historical dispatch, not open stock.
            var pendingInStock = (tab === 'in');
            if (pendingInStock) {
                pendingOpenCount++;
                if (ageDays > thresholdDays) overdueCount++;
                else if (ageDays >= Math.max(1, thresholdDays - 2)) nearDueCount++;
            }
            var ageBadgeClass = 'bg-success-subtle text-success-emphasis';
            var alertBadge = '<span class="badge bg-success-subtle text-success-emphasis">On track</span>';
            if (pendingInStock && ageDays > thresholdDays) {
                ageBadgeClass = 'bg-danger-subtle text-danger-emphasis';
                alertBadge = '<span class="badge bg-danger-subtle text-danger-emphasis">Overdue</span>';
            } else if (pendingInStock && ageDays >= Math.max(1, thresholdDays - 2)) {
                ageBadgeClass = 'bg-warning-subtle text-warning-emphasis';
                alertBadge = '<span class="badge bg-warning-subtle text-warning-emphasis">Near due</span>';
            } else if (tab === 'sent') {
                ageBadgeClass = 'bg-info-subtle text-info-emphasis';
                alertBadge = '<span class="badge bg-info-subtle text-info-emphasis" title="Already deducted from claim stock">Sent</span>';
            } else if (!pendingInStock) {
                ageBadgeClass = 'bg-secondary-subtle text-secondary-emphasis';
                alertBadge = '<span class="badge bg-secondary-subtle text-secondary-emphasis">Closed</span>';
            }

            if (tab === 'in') {
                statusNote = r.status_note || ('In claim stock — awaiting send to supplier (' + ageDays + 'd).');
                traceability = '<div class="small text-muted">Logged: ' + safe((r.date || '') + ' ' + (r.time || '')) + '</div>';
                var eligibleIn = claimHistoryRowEligibleForClaimSend(r);
                var alreadyLoadedIn = hasLoadedClaimRecord(r, 'claim_send');
                var cbDis = eligibleIn ? '' : ' disabled';
                if (alreadyLoadedIn) cbDis = ' disabled';
                var cbTitle = eligibleIn ? '' : ' title="No quantity or no claim stock available for this row"';
                if (alreadyLoadedIn) cbTitle = ' title="Already loaded"';
                var sendBtnClass = alreadyLoadedIn ? 'btn btn-sm btn-secondary' : 'btn btn-sm btn-primary';
                var sendBtnText = alreadyLoadedIn ? '<i class="ti ti-check me-1"></i>Loaded' : '<i class="ti ti-send me-1"></i>Send to supplier';
                actionHtml = '<div class="d-flex flex-column gap-2 align-items-stretch">'
                    + '<div class="d-flex flex-wrap align-items-center gap-2">'
                    + '<input type="checkbox" class="form-check-input purchase-claim-history-row-cb flex-shrink-0"'
                    + ' data-row-idx="' + idx + '"' + cbDis + cbTitle
                    + ' aria-label="Select row for load"'
                    + ' style="width: 1rem; height: 1rem; margin: 0;">'
                    + '<button type="button" class="' + sendBtnClass + '"'
                    + ((eligibleIn && !alreadyLoadedIn) ? '' : ' disabled')
                    + (alreadyLoadedIn ? ' title="Already loaded"' : (eligibleIn ? ' title="Send this line to supplier from claim stock"' : ' title="Not eligible"'))
                    + ' onclick="window.purchaseClaimHistorySendSingle(' + idx + ')"><i class="ti ti-send me-1"></i>Send to supplier</button>'
                    + '</div>'
                    + '<span class="small text-muted">Uses claim stock only.</span>'
                    + '</div>';
            } else if (tab === 'sent') {
                var sentTo = r.customer_name || r.supplier_name || r.party_name || 'N/A';
                var purchaseRef = (r.purchase_id != null && r.purchase_id !== '') ? ('<div class="small">Purchase: <strong>#' + safe(String(r.purchase_id)) + '</strong>'
                    + (r.invoice_no ? ' · Inv ' + safe(String(r.invoice_no)) : '') + '</div>') : '';
                statusNote = '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">Sent to supplier</span>'
                    + '<div class="small text-muted mt-1">' + safe((r.status_note || 'Outgoing from claim stock (claim send).').toString()) + '</div>';
                traceability = purchaseRef
                    + '<div class="small">Supplier: <strong>' + safe(sentTo) + '</strong></div>'
                    + '<div class="small text-muted">Sent at: ' + safe((r.date || '') + ' ' + (r.time || '')) + '</div>';
                var eligibleSent = claimHistoryRowEligibleForClaimSend(r);
                var alreadyLoadedSent = hasLoadedClaimRecord(r, 'claim_send');
                var cbDisSent = eligibleSent ? '' : ' disabled';
                if (alreadyLoadedSent) cbDisSent = ' disabled';
                var cbTitleSent = eligibleSent ? ' title="Select to add another claim-send line if claim stock exists"' : ' title="No claim stock available to send again — row is historical only"';
                if (alreadyLoadedSent) cbTitleSent = ' title="Already loaded"';
                var loadBtn = eligibleSent
                    ? (alreadyLoadedSent
                        ? '<button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Already loaded"><i class="ti ti-check me-1"></i>Loaded</button>'
                        : '<button type="button" class="btn btn-sm btn-outline-primary" title="Add as claim-send line when claim stock allows (same rules as Load)" onclick="window.purchaseClaimHistorySendSingle(' + idx + ')"><i class="ti ti-shopping-cart-plus me-1"></i>Load to purchase</button>')
                    : '<span class="small text-muted" title="This send is already completed. Load is only available when claim stock exists again for this item/warehouse."><i class="ti ti-lock me-1"></i>No claim stock</span>';
                actionHtml = '<div class="d-flex flex-column gap-2 align-items-stretch">'
                    + '<div class="d-flex flex-wrap align-items-center gap-2">'
                    + '<input type="checkbox" class="form-check-input purchase-claim-history-row-cb flex-shrink-0"'
                    + ' data-row-idx="' + idx + '"' + cbDisSent + cbTitleSent
                    + ' aria-label="Select for load to purchase"'
                    + ' style="width: 1rem; height: 1rem; margin: 0;">'
                    + loadBtn
                    + '</div>'
                    + '<button type="button" class="btn btn-sm btn-outline-danger align-self-start purchase-claim-reverse-btn" '
                    + 'onclick="window.reverseClaimSendRow(this)" '
                    + 'data-purchase-item-id="' + safeAttr(r.purchase_item_id || '') + '" '
                    + 'data-item-id="' + safeAttr(r.item_id) + '" '
                    + 'data-warehouse-id="' + safeAttr(r.warehouse_id) + '" '
                    + 'data-quantity="' + safeAttr(r.quantity) + '" '
                    + 'data-item-name="' + safeAttr(r.item_name) + '" '
                    + 'data-item-name-full="' + safeAttr((r.item_line || r.item_name)) + '" '
                    + 'data-item-image-url="' + safeAttr(r.image || '') + '" '
                    + 'data-item-image-path="' + safeAttr(r.image_path || '') + '" '
                    + 'data-item-images-json="' + safeAttr(JSON.stringify(Array.isArray(r.images) ? r.images : [])) + '" '
                    + 'data-warehouse-name="' + safeAttr(r.warehouse_name || r.branch_name || '') + '" '
                    + 'data-supplier-name="' + safeAttr(sentTo) + '" '
                    + 'data-sent-date="' + safeAttr(r.date || '') + '" '
                    + 'data-sent-time="' + safeAttr(r.time || '') + '" '
                    + 'data-claim-id="' + safeAttr(claimId) + '" '
                    + 'title="Move quantity back into claim stock (reverse send)">'
                    + '<i class="ti ti-arrow-back-up me-1"></i>Reverse to claim stock</button>'
                    + '</div>';
            } else {
                // Claim Reverse is a functional claim-stock source (rejected items returned to warehouse claim stock).
                statusNote = r.status_note || 'Reversed back into claim stock.';
                traceability = '<div class="small">Originally sent to: <strong>' + safe(r.original_sent_to || 'N/A') + '</strong></div>'
                    + '<div class="small text-muted">Sent: ' + safe(r.original_sent_at || '—') + '</div>'
                    + '<div class="small text-muted">Reversed: ' + safe(r.reversed_at || '—') + '</div>';
                var eligibleRev = claimHistoryRowEligibleForClaimSend(r);
                var alreadyLoadedRev = hasLoadedClaimRecord(r, 'claim_send');
                var cbDisRev = eligibleRev ? '' : ' disabled';
                if (alreadyLoadedRev) cbDisRev = ' disabled';
                var cbTitleRev = eligibleRev ? ' title="Select reversed claim to re-send from claim stock"' : ' title="No quantity or no claim stock available for this row"';
                if (alreadyLoadedRev) cbTitleRev = ' title="Already loaded"';
                actionHtml = '<div class="d-flex flex-column gap-2 align-items-stretch">'
                    + '<div class="d-flex flex-wrap align-items-center gap-2">'
                    + '<input type="checkbox" class="form-check-input purchase-claim-history-row-cb flex-shrink-0"'
                    + ' data-row-idx="' + idx + '"' + cbDisRev + cbTitleRev
                    + ' aria-label="Select row for load"'
                    + ' style="width: 1rem; height: 1rem; margin: 0;">'
                    + '<button type="button" class="' + (alreadyLoadedRev ? 'btn btn-sm btn-secondary' : 'btn btn-sm btn-primary') + '"'
                    + ((eligibleRev && !alreadyLoadedRev) ? '' : ' disabled')
                    + (alreadyLoadedRev ? ' title="Already loaded"' : (eligibleRev ? ' title="Send this reversed line to supplier from claim stock"' : ' title="Not eligible"'))
                    + ' onclick="window.purchaseClaimHistorySendSingle(' + idx + ')"><i class="ti ti-send me-1"></i>Send to supplier</button>'
                    + '</div>'
                    + '<span class="small text-muted">Uses claim stock only.</span>'
                    + '</div>';
            }

            var statusColHtml = (tab === 'sent')
                ? ('<td>' + statusNote + '</td>')
                : ('<td><span class="small">' + safe(statusNote) + '</span></td>');

            $tb.append('<tr class="' + (tab === 'sent' ? 'purchase-claim-sent-row' : '') + '">'
                + '<td>' + safe(r.date || '') + '<div class="small text-muted">' + safe(r.time || '') + '</div></td>'
                + '<td>' + itemName + '</td>'
                + '<td>' + safe(r.warehouse_name || r.branch_name || '') + '</td>'
                + '<td class="text-end fw-semibold">' + safe(String(qtyDisplay)) + '</td>'
                + '<td><span class="font-monospace small">' + safe(claimId) + '</span></td>'
                + '<td><span class="badge ' + ageBadgeClass + '">' + ageDays + 'd</span></td>'
                + '<td>' + alertBadge + '</td>'
                + statusColHtml
                + '<td>' + traceability + '</td>'
                + '<td>' + actionHtml + '</td>'
                + '</tr>');
        });
        $('#purchase-claim-aging-open-count').text(pendingOpenCount);
        $('#purchase-claim-aging-near-count').text(nearDueCount);
        $('#purchase-claim-aging-overdue-count').text(overdueCount);
        if (overdueCount > 0 && !window._purchaseClaimAgingAlertShownForOpen) {
            window._purchaseClaimAgingAlertShownForOpen = true;
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Claim Aging Alert',
                    text: overdueCount + ' pending claim(s) are overdue (>' + thresholdDays + ' days). Please prioritize action.'
                });
            }
        }
        // Keep header counts/trend aligned with the currently selected date range.
        updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
        updatePurchaseClaimHistoryLoadButtonState();
    }

    function openPurchaseClaimHistoryModal() {
        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first.' });
            else alert('Please select a branch first.');
            return;
        }
        var branchName = ($('#selectedBranchName').text() || '').trim();
        $('#purchase-claim-history-scope').text(branchName ? ('Branch: ' + branchName) : 'Branch');
        window._purchaseClaimAgingAlertShownForOpen = false;
        var savedThreshold = parseInt(localStorage.getItem('purchase_claim_aging_threshold_days') || '7', 10);
        if (isNaN(savedThreshold) || savedThreshold <= 0) savedThreshold = 7;
        $('#purchase-claim-aging-threshold').val(savedThreshold);
        // Default: show today's claims only.
        setPurchaseClaimHistoryFiltersToToday();
        $('#purchase-claim-history-modal').modal('show');
        $.when(
            $.ajax({ url: '{{ route("sales.claim.stock.detail") }}', method: 'GET', data: { branch_id: branchId } }),
            $.ajax({ url: '{{ route("sales.claim.send.stock.detail") }}', method: 'GET', data: { branch_id: branchId } }),
            $.ajax({ url: '{{ route("sales.claim.reverse.stock.detail") }}', method: 'GET', data: { branch_id: branchId } })
        ).done(function(inRes, sentRes, revRes) {
            var inData = inRes && inRes[0] ? inRes[0] : {};
            var sentData = sentRes && sentRes[0] ? sentRes[0] : {};
            var revData = revRes && revRes[0] ? revRes[0] : {};
            window._purchaseClaimInRecords = Array.isArray(inData.records) ? inData.records.map(function(r) {
                r.status_note = 'Received into claim stock.';
                r.claim_id = r.claim_id || r.reference_no || r.invoice_no || '';
                return r;
            }) : [];
            window._purchaseClaimSentRecords = Array.isArray(sentData.records) ? sentData.records.map(function(r) {
                var sup = (r.customer_name || r.supplier_name || '').toString().trim();
                r.status_note = r.status_note || ('Outgoing claim send from claim stock' + (sup ? ' · Supplier: ' + sup : '') + '.');
                r.claim_id = r.claim_id || r.reference_no || r.invoice_no || (r.purchase_id != null ? ('P#' + r.purchase_id) : '') || '';
                return r;
            }) : [];
            window._purchaseClaimReverseLogs = Array.isArray(revData.records) ? revData.records.map(function(r) {
                r.status_note = r.status_note || (r.reason ? ('Reversed to claim stock. Reason: ' + r.reason) : 'Reversed to claim stock.');
                r.reverse_claim_id = r.reverse_claim_id || r.claim_id || r.reference_no || r.invoice_no || (r.reversal_id != null ? ('REV#' + r.reversal_id) : '');
                return r;
            }) : [];

            // IMPORTANT RULE: items that exist in Claim Reverse must NOT appear in Claim In.
            // Filter Claim In by item_id|warehouse_id present in reverse list.
            (function() {
                var revKeys = new Set();
                (window._purchaseClaimReverseLogs || []).forEach(function(rv) {
                    if (!rv) return;
                    if (!rv.item_id || !rv.warehouse_id) return;
                    revKeys.add(String(rv.item_id) + '|' + String(rv.warehouse_id));
                });
                if (revKeys.size) {
                    window._purchaseClaimInRecords = (window._purchaseClaimInRecords || []).filter(function(r) {
                        if (!r) return false;
                        var k = String(r.item_id || '') + '|' + String(r.warehouse_id || '');
                        return !revKeys.has(k);
                    });
                }
            })();

            // Hide already-sent claims from "Claim In" (sent items must not appear as pending in-stock).
            // Match by (item_id + warehouse_id + claim reference).
            (function() {
                var sentKeys = new Set();
                var addKey = function(itemId, whId, ref) {
                    itemId = (itemId == null) ? '' : String(itemId);
                    whId = (whId == null) ? '' : String(whId);
                    ref = (ref || '').toString().trim().toLowerCase();
                    if (!itemId || !whId || !ref) return;
                    sentKeys.add(itemId + '|' + whId + '|' + ref);
                };
                (window._purchaseClaimSentRecords || []).forEach(function(r) {
                    addKey(r.item_id, r.warehouse_id, r.claim_id);
                    addKey(r.item_id, r.warehouse_id, r.reference_no);
                    addKey(r.item_id, r.warehouse_id, r.invoice_no);
                });

                window._purchaseClaimInRecords = (window._purchaseClaimInRecords || []).filter(function(r) {
                    var itemId = r.item_id;
                    var whId = r.warehouse_id;
                    var refs = [r.claim_id, r.reference_no, r.invoice_no].filter(Boolean);
                    for (var i = 0; i < refs.length; i++) {
                        var ref = String(refs[i] || '').trim().toLowerCase();
                        if (!ref) continue;
                        if (sentKeys.has(String(itemId) + '|' + String(whId) + '|' + ref)) return false;
                    }
                    return true;
                });
            })();

            updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
            window._purchaseClaimActiveTab = 'in';
            $('#purchase-claim-history-tabs .nav-link').removeClass('active');
            $('#purchase-claim-history-tabs .nav-link[data-claim-tab="in"]').addClass('active');
            renderPurchaseClaimHistoryTab();
        }).fail(function() {
            if (typeof Swal !== 'undefined' && Swal.fire) Swal.fire({ icon: 'error', title: 'Error', text: 'Unable to load claim history.' });
        });
    }

    $(document).on('click', '#purchase-claim-history-btn', function(e) { e.preventDefault(); openPurchaseClaimHistoryModal(); });
    $(document).on('click', '#purchase-claim-send-new-btn', function(e) {
        e.preventDefault();
        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first.' });
            else alert('Please select a branch first.');
            return;
        }
        window._purchaseClaimSendStatusNote = ($('#purchase-claim-send-note').val() || '').toString().trim();
        currentEntryType = 'claim_send';
        addItemModalTitleKey = 'claim_send';
        $('#purchase-claim-history-modal').modal('hide');
        $('#add-item-modal').modal('show');
        setTimeout(function() { try { $('#item-search').trigger('focus'); } catch (e) {} }, 180);
    });
    $(document).on('click', '#purchase-claim-history-tabs .nav-link', function() {
        $('#purchase-claim-history-tabs .nav-link').removeClass('active');
        $(this).addClass('active');
        window._purchaseClaimActiveTab = $(this).data('claim-tab') || 'in';
        renderPurchaseClaimHistoryTab();
    });
    $(document).on('change', '#purchase-claim-history-select-all', function() {
        var on = $(this).is(':checked');
        $('#purchase-claim-history-modal .purchase-claim-history-row-cb:not(:disabled)').prop('checked', on);
    });
    $(document).on('change', '#purchase-claim-history-modal .purchase-claim-history-row-cb', function() {
        var $enabled = $('#purchase-claim-history-modal .purchase-claim-history-row-cb:not(:disabled)');
        var $checked = $enabled.filter(':checked');
        var $all = $('#purchase-claim-history-select-all');
        if (!$enabled.length) {
            $all.prop('checked', false).prop('indeterminate', false);
            return;
        }
        if ($checked.length === 0) {
            $all.prop('checked', false).prop('indeterminate', false);
        } else if ($checked.length === $enabled.length) {
            $all.prop('checked', true).prop('indeterminate', false);
        } else {
            $all.prop('checked', false).prop('indeterminate', true);
        }
    });
    $(document).on('click', '#purchase-claim-history-load-btn', function(e) {
        e.preventDefault();
        var tab = window._purchaseClaimActiveTab || 'in';
        var $checked = $('#purchase-claim-history-modal .purchase-claim-history-row-cb:checked');
        if ($checked.length <= 0) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Select claims', text: 'Please select at least one claim.' });
            } else {
                alert('Please select at least one claim.');
            }
            return;
        }
        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first.' });
            else alert('Please select a branch first.');
            return;
        }
        var rows = window._purchaseClaimHistoryRenderedRows || [];
        var loadedAny = false;
        var adjustedRows = 0;
        var unknownAvailabilityRows = 0;
        $checked.each(function() {
            var idx = parseInt($(this).attr('data-row-idx'), 10);
            if (isNaN(idx)) return;
            var r = rows[idx];
            if (!r) return;
            if (!claimHistoryRowEligibleForClaimSend(r)) return;
            var res = pushClaimSendLineFromClaimHistoryRecord(r);
            if (res.ok) {
                loadedAny = true;
                if (res.adjusted) adjustedRows++;
                if (res.unknownAvailability) unknownAvailabilityRows++;
                if (tab === 'in') {
                    purchaseClaimHistoryMoveInToSent(r, res.moved_qty);
                } else if (tab === 'reverse') {
                    purchaseClaimHistoryMoveReverseToSent(r, res.moved_qty);
                }
            }
        });
        if (!loadedAny) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Nothing to load', text: 'No eligible claim rows selected (check quantity / availability).' });
            }
            return;
        }
        if (unknownAvailabilityRows > 0 || adjustedRows > 0) {
            var msg = [];
            if (adjustedRows > 0) msg.push(adjustedRows + ' row qty adjusted to available claim stock');
            if (unknownAvailabilityRows > 0) msg.push(unknownAvailabilityRows + ' row loaded with live stock check on submit');
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'info', title: 'Claim Send Quantity Updated', text: msg.join('. ') + '.' });
            } else {
                alert(msg.join('. ') + '.');
            }
        }
        sortPurchaseItemsByEntryType();
        $('#items-tbody').empty();
        purchaseItems.forEach(function(item) { addItemToTable(item); });
        $('#empty-items-state').hide();
        $('#items-list').show();
        $('#payment-amount-row').show();
        calculateTotals();
        syncCartToServer();
        updatePurchaseTableRetailColumnVisibility();
        updatePurchasePrintButton();
        $('#purchase-claim-history-modal .purchase-claim-history-row-cb').prop('checked', false);
        $('#purchase-claim-history-select-all').prop('checked', false).prop('indeterminate', false);
        // Keep the panel open so the user can see rows moving to "Claim Sent".
    });
    $(document).on('change input', '#purchase-claim-filter-from, #purchase-claim-filter-to, #purchase-claim-filter-id', function() {
        updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
        renderPurchaseClaimHistoryTab();
    });
    $(document).on('change input', '#purchase-claim-aging-threshold', function() {
        var t = getClaimAgingThresholdDays();
        $('#purchase-claim-aging-threshold').val(t);
        localStorage.setItem('purchase_claim_aging_threshold_days', String(t));
        updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
        renderPurchaseClaimHistoryTab();
    });
    $(document).on('change', '#purchase-claim-overdue-only', function() {
        updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
        renderPurchaseClaimHistoryTab();
    });
    $(document).on('click', '#purchase-claim-filter-clear', function() {
        setPurchaseClaimHistoryFiltersToToday();
        updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
        renderPurchaseClaimHistoryTab();
    });
    $(document).ready(function() {
        setPurchaseClaimHistoryFiltersToToday();
        updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
    });

    // Open detailed claim stock history modal (reuse same modal used elsewhere in purchase page)
    $(document).on('click', '#purchase-claim-stock-summary-badge', function(e) {
        e.preventDefault();
        var branchId = ($('#purchaseBranchId').val() || '').toString();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first before viewing claim stock history.' });
            } else {
                alert('Please select a branch first.');
            }
            return;
        }
        // If page doesn't have the detail modal for any reason, just do nothing.
        if (!$('#claim-stock-detail-modal').length) return;

        var branchName = ($('#selectedBranchName').text() || '').trim();
        var scopeLabel = branchName ? ('Branch: ' + branchName) : 'Branch';
        $('#claim-stock-detail-scope').html('<span class="badge text-white" style="background:#7c3aed; font-weight:800; letter-spacing:0.03em;">' + String(scopeLabel).replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</span>');
        $('#claim-stock-detail-loading').removeClass('d-none');
        $('#claim-stock-detail-content').addClass('d-none');
        $('#claim-stock-detail-tbody').empty();
        $('#claim-stock-detail-empty').addClass('d-none');
        $('#claim-stock-detail-modal').modal('show');

        $.ajax({
            url: '{{ route("sales.claim.stock.detail") }}',
            method: 'GET',
            data: { branch_id: branchId },
            success: function(res) {
                var totals = (res && res.totals) ? res.totals : {};
                var records = (res && res.records) ? res.records : [];
                // Keep originals for date filtering
                window._claimStockDetailAllRecords = Array.isArray(records) ? records.slice() : [];
                window._claimStockDetailTotals = totals || {};
                window._claimStockDetailCurrentStock = (totals && totals.current_claim_stock != null) ? totals.current_claim_stock : 0;

                function toYmdFromUnix(ts) {
                    var d = new Date((Number(ts) || 0) * 1000);
                    if (isNaN(d.getTime())) return '';
                    var m = String(d.getMonth() + 1).padStart(2, '0');
                    var day = String(d.getDate()).padStart(2, '0');
                    return d.getFullYear() + '-' + m + '-' + day;
                }
                function unixFromYmdStart(ymd) {
                    if (!ymd) return null;
                    var parts = ymd.split('-');
                    if (parts.length !== 3) return null;
                    var d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]), 0, 0, 0, 0);
                    return Math.floor(d.getTime() / 1000);
                }
                function unixFromYmdEnd(ymd) {
                    if (!ymd) return null;
                    var parts = ymd.split('-');
                    if (parts.length !== 3) return null;
                    var d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]), 23, 59, 59, 999);
                    return Math.floor(d.getTime() / 1000);
                }

                function renderClaimStockDetail() {
                    var all = window._claimStockDetailAllRecords || [];
                    $('#claim-stock-select-all-checkbox').prop('checked', false).prop('indeterminate', false);
                    $('#claim-stock-select-all-label').text('SELECT');
                    // Sort by datetime (latest first)
                    var sorted = all.slice().sort(function(a, b) {
                        var at = (a && a.datetime_sort != null) ? Number(a.datetime_sort) : 0;
                        var bt = (b && b.datetime_sort != null) ? Number(b.datetime_sort) : 0;
                        // Oldest first
                        return at - bt;
                    });

                    var fromYmd = ($('#claim-stock-date-from').val() || '').toString().trim();
                    var toYmd = ($('#claim-stock-date-to').val() || '').toString().trim();
                    var fromTs = unixFromYmdStart(fromYmd);
                    var toTs = unixFromYmdEnd(toYmd);

                    var filtered = sorted.filter(function(r) {
                        var ts = (r && r.datetime_sort != null) ? Number(r.datetime_sort) : 0;
                        if (fromTs != null && ts < fromTs) return false;
                        if (toTs != null && ts > toTs) return false;
                        return true;
                    });

                    // Purchase page requirement: show only CURRENT available claim stock rows
                    // (same quantity perspective as "CLAIM STOCK: X PIECE" badge).
                    var currentOnlyMap = {};
                    filtered.forEach(function(r) {
                        var itemId = (r && r.item_id != null) ? Number(r.item_id) : 0;
                        var whId = (r && r.warehouse_id != null) ? Number(r.warehouse_id) : 0;
                        var available = (r && r.available_claim_qty != null) ? Number(r.available_claim_qty) : NaN;
                        if (!itemId || !whId || isNaN(available) || available <= 0) return;
                        var k = itemId + ':' + whId;
                        // Keep latest row metadata for this item+warehouse, but display current available quantity.
                        currentOnlyMap[k] = Object.assign({}, r, { display_quantity: available });
                    });
                    var visibleRows = Object.keys(currentOnlyMap).map(function(k) { return currentOnlyMap[k]; });
                    visibleRows.sort(function(a, b) {
                        var at = (a && a.datetime_sort != null) ? Number(a.datetime_sort) : 0;
                        var bt = (b && b.datetime_sort != null) ? Number(b.datetime_sort) : 0;
                        return at - bt;
                    });

                    // Totals must stay accurate even though the backend table shows ONLY Claim In rows.
                    var totalsAll = window._claimStockDetailTotals || {};
                    $('#claim-stock-total-in').text((totalsAll.total_claim_in != null ? totalsAll.total_claim_in : 0));
                    $('#claim-stock-total-sent').text((totalsAll.total_claim_sent != null ? totalsAll.total_claim_sent : 0));
                    // Current stock stays branch current (not date-filtered)
                    $('#claim-stock-current').text(window._claimStockDetailCurrentStock ?? 0);

                    var hint = '';
                    if (fromYmd || toYmd) {
                        hint = 'Showing ' + visibleRows.length + ' current rows';
                    }
                    $('#claim-stock-date-range-hint').text(hint);

                    var $tbody = $('#claim-stock-detail-tbody');
                    $tbody.empty();
                    if (!visibleRows.length) {
                        $('#claim-stock-detail-empty').removeClass('d-none');
                        return;
                    }
                    $('#claim-stock-detail-empty').addClass('d-none');

                    visibleRows.forEach(function(r) {
                        var refText = (r.reference_no || r.invoice_no || '');
                        var rawName = (r.item_name || '').toString().trim();
                        var code = (r.item_code || '').toString().trim();
                        var line = (r.item_line || '').toString().trim();
                        var meta = (r.item_meta || '').toString().trim();
                        var salesEditBaseUrl = '{{ url("sales") }}';
                        var purchasesEditBaseUrl = '{{ url("purchases") }}';
                        var editBtnHtml = '';
                        var reverseBtnHtml = '';
                        if (r.sale_id) {
                            editBtnHtml = '<button type="button" class="btn btn-sm btn-outline-primary" ' +
                                'onclick="window.location=\'' + salesEditBaseUrl + '/' + r.sale_id + '/edit\'' + '">' +
                                'Edit</button>';
                        } else if (r.purchase_id) {
                            editBtnHtml = '<button type="button" class="btn btn-sm btn-outline-primary" ' +
                                'onclick="window.location=\'' + purchasesEditBaseUrl + '/' + r.purchase_id + '/edit\'' + '">' +
                                'Edit</button>';
                        }

                        var safeAttr = function(v) {
                            return String(v || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        };
                        var rowCheckboxHtml = '<input type="checkbox" class="form-check-input claim-stock-row-checkbox" ' +
                            'data-sale-id="' + ((r.sale_id != null) ? r.sale_id : '') + '" ' +
                            'data-purchase-id="' + ((r.purchase_id != null) ? r.purchase_id : '') + '" ' +
                            'data-item-id="' + ((r.item_id != null) ? r.item_id : '') + '" ' +
                            'data-warehouse-id="' + ((r.warehouse_id != null) ? r.warehouse_id : '') + '" ' +
                            'data-quantity="' + ((r.display_quantity != null) ? r.display_quantity : (r.quantity != null ? r.quantity : '')) + '" ' +
                            'data-available-quantity="' + ((r.available_claim_qty != null) ? r.available_claim_qty : '') + '" ' +
                            'data-item-name="' + safeAttr(r.item_name) + '" ' +
                            'data-warehouse-name="' + safeAttr(r.warehouse_name || r.branch_name || '') + '" ' +
                            'data-item-image-url="' + safeAttr(r.image || '') + '" ' +
                            'data-item-image-path="' + safeAttr(r.image_path || '') + '" ' +
                            'data-item-images-json="' + safeAttr(JSON.stringify(Array.isArray(r.images) ? r.images : [])) + '" ' +
                            'style="width: 1rem; height: 1rem; margin: 0;">';
                        var itemHtml = (function() {
                            var safe = function(s) { return String(s || '').replace(/</g, '&lt;').replace(/>/g, '&gt;'); };
                            var name = rawName;
                            if (name && code && name === code) name = '';
                            var title = '';
                            var subtitle = '';
                            if (name) {
                                if (name.indexOf(' • ') !== -1) {
                                    var parts = name.split(' • ').map(function(p){ return p.trim(); }).filter(Boolean);
                                    title = parts.shift() || '';
                                    subtitle = parts.join(' • ');
                                } else {
                                    title = name;
                                }
                            } else if (code) {
                                title = code;
                            } else {
                                title = '';
                            }
                            var html = '';
                            if (title) html += '<div class="fw-bold text-dark" style="line-height:1.1;">' + safe(title) + '</div>';
                            if (line) {
                                html += '<div class="small text-muted" style="line-height:1.1; margin-top:2px;">' + safe(line) + '</div>';
                                if (meta) html += '<div class="small text-muted" style="line-height:1.1; margin-top:2px;">' + safe(meta) + '</div>';
                            } else {
                                if (subtitle) html += '<div class="small text-muted" style="line-height:1.1; margin-top:2px;">' + safe(subtitle) + '</div>';
                            }
                            if (code && (code !== title)) {
                                html += '<div class="small" style="margin-top:4px; color:#f97316; font-weight:800; letter-spacing:0.02em;"><i class="ti ti-barcode me-1"></i>' + safe(code) + '</div>';
                            }
                            return html || '&nbsp;';
                        })();
                        $tbody.append(`
                            <tr>
                                <td>${r.date || ''}<div class="small text-muted">${r.time || ''}</div></td>
                                <td>${itemHtml}</td>
                                <td>${r.warehouse_name || r.branch_name || ''}</td>
                                <td class="text-end">${(r.display_quantity != null ? r.display_quantity : (r.quantity != null ? r.quantity : ''))}</td>
                                <td>${(function(){
                                    var t = (r.entry_type || '').toString();
                                    var label = (r.entry_type_label || r.entry_type || '').toString();
                                    var bg = '#6c757d';
                                    if (t === 'claim_send') bg = '#7c3aed';
                                    else if (t === 'claim_in' || t === 'claim') bg = '#198754';
                                    return '<span class="badge text-white" style="background-color: ' + bg + '; font-weight: 700; letter-spacing: 0.02em;">' + label + '</span>';
                                })()}</td>
                                <td>${refText}</td>
                                <td>
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        ${rowCheckboxHtml}
                                        ${editBtnHtml}
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                }

                // Date range filter UI removed: show all records
                // (Keep renderClaimStockDetail logic tolerant if inputs are missing.)

                renderClaimStockDetail();
                $('#claim-stock-detail-loading').addClass('d-none');
                $('#claim-stock-detail-content').removeClass('d-none');
            },
            error: function() {
                $('#claim-stock-detail-modal').modal('hide');
            }
        });
    });

    // Load button inside Claim In history modal footer
    $(document).on('click', '#purchase-claim-stock-detail-load-btn', function(e) {
        e.preventDefault();
        var $checked = $('#claim-stock-detail-modal .claim-stock-row-checkbox:checked');
        if ($checked.length <= 0) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Select Items', text: 'Please select at least one row to load.' });
            } else {
                alert('Please select at least one row to load.');
            }
            return;
        }

        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first.' });
            } else {
                alert('Please select a branch first.');
            }
            return;
        }

        var loadedAny = false;
        var adjustedRows = 0;
        var unknownAvailabilityRows = 0;
        function extractBatterySequence(displayName) {
            // For battery rows, we want only the sequence (e.g. "GL50") like UI shows.
            // Claim history item_name often looks like: "GL50 • 11PL • 38AH • AGS".
            var n = (displayName != null) ? String(displayName).trim() : '';
            if (!n) return n;
            if (!/[•·]/.test(n)) return n; // only split when bullet exists
            var parts = n.split(/\s*[•·]\s*/);
            var first = (parts[0] || '').trim();
            if (!first) return n;
            // If first segment has extra token like "GL50 0", keep only first word "GL50"
            return (first.split(/\s+/)[0] || first).trim();
        }
        $checked.each(function() {
            var $cb = $(this);
            var itemId = parseInt($cb.attr('data-item-id') || '0', 10);
            if (isNaN(itemId) || itemId <= 0) return;
            var itemNameRaw = ($cb.attr('data-item-name') || '').toString().trim() || ('Item #' + itemId);
            var warehouseIdRaw = $cb.attr('data-warehouse-id');
            var warehouseId = warehouseIdRaw != null && warehouseIdRaw !== '' ? parseInt(warehouseIdRaw, 10) : NaN;
            if (isNaN(warehouseId) || warehouseId <= 0) warehouseId = null;

            var qty = Math.abs(parseFloat($cb.attr('data-quantity') || '0') || 0);
            if (!qty || qty <= 0) return;
            var availableQty = Math.abs(parseFloat($cb.attr('data-available-quantity') || '0') || 0);
            if (availableQty > 0 && qty > availableQty) { qty = availableQty; adjustedRows++; }
            if (availableQty <= 0) {
                unknownAvailabilityRows++;
                console.warn('Claim Send availability unresolved', {
                    item_id: itemId,
                    warehouse_id: warehouseId,
                    stock_type: 'claim',
                    available_stock: availableQty
                });
            }

            var warehouseName = ($cb.attr('data-warehouse-name') || '').toString().trim() || null;
            var itemName = extractBatterySequence(itemNameRaw) || itemNameRaw;
            var claimCbImg = parseClaimLoadCheckboxImageAttrs($cb);

            var newItem = {
                id: itemCounter++,
                item_id: itemId,
                item_line: null,
                name: itemName,
                warehouse_id: warehouseId,
                warehouse_name: warehouseName,
                available_claim_qty: (availableQty > 0 ? availableQty : null),
                quantity: qty,
                quantity_base: null,
                base_unit: null,
                unit: 'Unit',
                rate: 1,
                retail_price: null,
                retail_price_base: null,
                retail_pct: null,
                discount: 0,
                tax_percentage: 0,
                tax_amount: 0,
                total: qty,
                warranty: null,
                // Loading from Claim In history on this page means "send claim to supplier".
                entry_type: 'claim_send',
                purchase_order_item_id: null,
                is_temporary: false,
                image: claimCbImg.image,
                image_path: claimCbImg.image_path,
                images: claimCbImg.images,
                name_full: itemNameRaw,
                verified: 0
            };

            purchaseItems.push(newItem);
            loadedAny = true;
        });

        if (!loadedAny) return;
        if (unknownAvailabilityRows > 0 || adjustedRows > 0) {
            var msg = [];
            if (adjustedRows > 0) msg.push(adjustedRows + ' row qty adjusted to available claim stock');
            if (unknownAvailabilityRows > 0) msg.push(unknownAvailabilityRows + ' row loaded with live stock check on submit');
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'info', title: 'Claim Send Quantity Updated', text: msg.join('. ') + '.' });
            } else {
                alert(msg.join('. ') + '.');
            }
        }

        sortPurchaseItemsByEntryType();
        $('#items-tbody').empty();
        purchaseItems.forEach(function(item) { addItemToTable(item); });
        $('#empty-items-state').hide();
        $('#items-list').show();
        $('#payment-amount-row').show();

        calculateTotals();
        syncCartToServer();
        updatePurchaseTableRetailColumnVisibility();
        updatePurchasePrintButton();

        // Prevent accidental double-loading same rows
        $('#claim-stock-detail-modal .claim-stock-row-checkbox').prop('checked', false);
        $('#claim-stock-select-all-checkbox').prop('checked', false).prop('indeterminate', false);

        // Close after successful load
        $('#claim-stock-detail-modal').modal('hide');
    });

    // Load button inside Claim Send history modal footer
    $(document).on('click', '#purchase-claim-send-stock-detail-load-btn', function(e) {
        e.preventDefault();
        var $checked = $('#claim-send-stock-detail-modal .claim-send-row-checkbox:checked');
        if ($checked.length <= 0) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Select Items', text: 'Please select at least one row to load.' });
            } else {
                alert('Please select at least one row to load.');
            }
            return;
        }

        var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first.' });
            } else {
                alert('Please select a branch first.');
            }
            return;
        }

        var loadedAny = false;
        var adjustedRows = 0;
        var unknownAvailabilityRows = 0;
        function extractBatterySequence(displayName) {
            var n = (displayName != null) ? String(displayName).trim() : '';
            if (!n) return n;
            if (!/[•·]/.test(n)) return n;
            var parts = n.split(/\s*[•·]\s*/);
            var first = (parts[0] || '').trim();
            if (!first) return n;
            return (first.split(/\s+/)[0] || first).trim();
        }
        $checked.each(function() {
            var $cb = $(this);
            var itemId = parseInt($cb.attr('data-item-id') || '0', 10);
            if (isNaN(itemId) || itemId <= 0) return;
            var itemNameRaw = ($cb.attr('data-item-name') || '').toString().trim() || ('Item #' + itemId);
            var warehouseIdRaw = $cb.attr('data-warehouse-id');
            var warehouseId = warehouseIdRaw != null && warehouseIdRaw !== '' ? parseInt(warehouseIdRaw, 10) : NaN;
            if (isNaN(warehouseId) || warehouseId <= 0) warehouseId = null;

            var qty = Math.abs(parseFloat($cb.attr('data-quantity') || '0') || 0);
            if (!qty || qty <= 0) return;
            var availableQty = Math.abs(parseFloat($cb.attr('data-available-quantity') || '0') || 0);
            if (availableQty > 0 && qty > availableQty) { qty = availableQty; adjustedRows++; }
            if (availableQty <= 0) {
                unknownAvailabilityRows++;
                console.warn('Claim Send availability unresolved', {
                    item_id: itemId,
                    warehouse_id: warehouseId,
                    stock_type: 'claim',
                    available_stock: availableQty
                });
            }

            var warehouseName = ($cb.attr('data-warehouse-name') || '').toString().trim() || null;
            var itemName = extractBatterySequence(itemNameRaw) || itemNameRaw;
            var sendCbImg = parseClaimLoadCheckboxImageAttrs($cb);

            var newItem = {
                id: itemCounter++,
                item_id: itemId,
                item_line: null,
                name: itemName,
                warehouse_id: warehouseId,
                warehouse_name: warehouseName,
                available_claim_qty: (availableQty > 0 ? availableQty : null),
                quantity: qty,
                quantity_base: null,
                base_unit: null,
                unit: 'Unit',
                rate: 1,
                retail_price: null,
                retail_price_base: null,
                retail_pct: null,
                discount: 0,
                tax_percentage: 0,
                tax_amount: 0,
                total: qty,
                warranty: null,
                // Loading from Claim Send history keeps the same outgoing flow.
                entry_type: 'claim_send',
                purchase_order_item_id: null,
                is_temporary: false,
                image: sendCbImg.image,
                image_path: sendCbImg.image_path,
                images: sendCbImg.images,
                name_full: itemNameRaw,
                verified: 0
            };

            purchaseItems.push(newItem);
            loadedAny = true;
        });

        if (!loadedAny) return;
        if (unknownAvailabilityRows > 0 || adjustedRows > 0) {
            var msg = [];
            if (adjustedRows > 0) msg.push(adjustedRows + ' row qty adjusted to available claim stock');
            if (unknownAvailabilityRows > 0) msg.push(unknownAvailabilityRows + ' row loaded with live stock check on submit');
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'info', title: 'Claim Send Quantity Updated', text: msg.join('. ') + '.' });
            } else {
                alert(msg.join('. ') + '.');
            }
        }

        sortPurchaseItemsByEntryType();
        $('#items-tbody').empty();
        purchaseItems.forEach(function(item) { addItemToTable(item); });
        $('#empty-items-state').hide();
        $('#items-list').show();
        $('#payment-amount-row').show();

        calculateTotals();
        syncCartToServer();
        updatePurchaseTableRetailColumnVisibility();
        updatePurchasePrintButton();

        // Prevent accidental double-loading same rows
        $('#claim-send-stock-detail-modal .claim-send-row-checkbox').prop('checked', false);
        $('#claim-send-stock-select-all-checkbox').prop('checked', false).prop('indeterminate', false);

        // Close after successful load
        $('#claim-send-stock-detail-modal').modal('hide');
    });

    // Reverse (Reject) for Claim Send history: adds back quantity as `entry_type = claim` (Claim In).
    window.applyReverseClaimSendFromAttrs = function(attrs) {
        var itemId = parseInt(String(attrs && attrs.item_id != null ? attrs.item_id : '0'), 10);
        if (isNaN(itemId) || itemId <= 0) return;

        var purchaseItemId = parseInt(String(attrs && attrs.purchase_item_id != null ? attrs.purchase_item_id : '0'), 10);
        if (isNaN(purchaseItemId) || purchaseItemId <= 0) {
            console.warn('Reverse requires purchase_item_id');
            return;
        }

        var qty = Math.abs(parseFloat((attrs && attrs.quantity != null) ? attrs.quantity : '0') || 0);
        if (!qty || qty <= 0) return;

        var warehouseIdRaw = attrs ? attrs.warehouse_id : null;
        var warehouseId = warehouseIdRaw != null && warehouseIdRaw !== '' ? parseInt(warehouseIdRaw, 10) : NaN;
        if (isNaN(warehouseId) || warehouseId <= 0) warehouseId = null;

        var itemName = (attrs && attrs.item_name != null) ? String(attrs.item_name).trim() : '';
        if (!itemName) itemName = 'Item #' + itemId;
        var itemNameFull = (attrs && attrs.item_name_full != null) ? String(attrs.item_name_full).trim() : '';
        if (!itemNameFull) itemNameFull = itemName;
        var warehouseNameRaw = (attrs && attrs.warehouse_name != null) ? String(attrs.warehouse_name).trim() : '';
        var supplierName = (attrs && attrs.supplier_name != null) ? String(attrs.supplier_name).trim() : '';
        var sentDate = (attrs && attrs.sent_date != null) ? String(attrs.sent_date).trim() : '';
        var sentTime = (attrs && attrs.sent_time != null) ? String(attrs.sent_time).trim() : '';
        var claimId = (attrs && attrs.claim_id != null) ? String(attrs.claim_id).trim() : '';

        // If warehouseName already contains Reject text, keep it; otherwise append.
        var rejectSuffix = supplierName ? (' (Reject: ' + supplierName + ')') : '';
        var warehouseName = warehouseNameRaw ? warehouseNameRaw + rejectSuffix : (supplierName ? ('Display (Reject: ' + supplierName + ')') : null);

        var doReverse = function(qtyToReverse, reasonText) {
            $.ajax({
                url: '{{ route("sales.claim.send.reverse") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    purchase_item_id: purchaseItemId,
                    quantity: qtyToReverse,
                    reason: reasonText || ''
                },
                success: function(resp) {
                    if (!resp || resp.success !== true) {
                        var msg = (resp && resp.message) ? resp.message : 'Reverse failed.';
                        if (typeof Swal !== 'undefined' && Swal.fire) Swal.fire({ icon: 'error', title: 'Reverse failed', text: msg });
                        else alert(msg);
                        return;
                    }

                    // 1) Update Claim Sent list: reduce qty or remove
                    var remainingAfter = (resp.data && resp.data.remaining_quantity != null) ? parseFloat(resp.data.remaining_quantity) : null;
                    window._purchaseClaimSentRecords = (window._purchaseClaimSentRecords || []).map(function(sr) {
                        if (!sr) return sr;
                        if (String(sr.purchase_item_id || '') !== String(purchaseItemId)) return sr;
                        var newRemain = (remainingAfter != null && !isNaN(remainingAfter)) ? remainingAfter : 0;
                        sr.quantity = -Math.abs(newRemain);
                        sr.display_quantity = Math.abs(newRemain);
                        sr.remaining_quantity = Math.abs(newRemain);
                        return sr;
                    }).filter(function(sr) {
                        if (!sr) return false;
                        if (String(sr.purchase_item_id || '') !== String(purchaseItemId)) return true;
                        var q = Math.abs(parseFloat(sr.display_quantity != null ? sr.display_quantity : sr.quantity) || 0);
                        return q > 0;
                    });

                    // 2) Add to Claim Reverse tab immediately
                    var now = formatClaimHistoryDateTime(Math.floor(Date.now() / 1000));
                    window._purchaseClaimReverseLogs = window._purchaseClaimReverseLogs || [];
                    window._purchaseClaimReverseLogs.unshift({
                        item_id: itemId,
                        item_name: itemName,
                        warehouse_id: warehouseId,
                        warehouse_name: warehouseNameRaw || '',
                        quantity: qtyToReverse,
                        display_quantity: qtyToReverse,
                        available_claim_qty: null,
                        reverse_claim_id: claimId || ('REV-' + now.ts),
                        original_sent_to: supplierName || 'N/A',
                        original_sent_at: ((sentDate + ' ' + sentTime).trim() || 'N/A'),
                        reversed_at: now.date + ' ' + now.time,
                        reverse_ts: now.ts,
                        datetime_sort: now.ts,
                        date: now.date,
                        time: now.time,
                        status_note: (reasonText ? ('Reversed to claim stock. Reason: ' + reasonText) : 'Reversed to claim stock.')
                    });

                    // RULE: reversed items must NOT appear in Claim In.
                    window._purchaseClaimInRecords = (window._purchaseClaimInRecords || []).filter(function(r) {
                        if (!r) return false;
                        return !(String(r.item_id || '') === String(itemId) && String(r.warehouse_id || '') === String(warehouseId));
                    });

                    // 3) Refresh counts + re-render in-place, switch to Reverse tab so user sees result
                    updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
                    if ($('#purchase-claim-history-modal').hasClass('show')) {
                        window._purchaseClaimActiveTab = 'reverse';
                        $('#purchase-claim-history-tabs .nav-link').removeClass('active');
                        $('#purchase-claim-history-tabs .nav-link[data-claim-tab="reverse"]').addClass('active');
                        renderPurchaseClaimHistoryTab();
                    }

                    // 4) Refresh badges (claim stock + claim send)
                    try { loadPurchaseClaimStockSummary(); } catch (e) {}
                    try { loadPurchaseClaimSendStockSummary(); } catch (e) {}

                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire({ icon: 'success', title: 'Reversed', text: 'Returned to claim stock successfully.' });
                    }
                },
                error: function(xhr) {
                    var msg = 'Reverse failed.';
                    try {
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    } catch (e) {}
                    if (typeof Swal !== 'undefined' && Swal.fire) Swal.fire({ icon: 'error', title: 'Reverse failed', text: msg });
                    else alert(msg);
                }
            });
        };

        // Ask qty + optional reason (defaults to full remaining)
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                title: 'Reverse to claim stock',
                html:
                    '<div class="text-start small text-muted mb-2">Item: <strong>' + String(itemName).replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</strong></div>' +
                    '<label class="form-label small mb-1">Quantity to reverse</label>' +
                    '<input id="swal-rev-qty" class="swal2-input" inputmode="decimal" value="' + String(qty) + '">' +
                    '<label class="form-label small mb-1 mt-2">Reason (optional)</label>' +
                    '<input id="swal-rev-reason" class="swal2-input" placeholder="e.g. supplier rejected" value="">',
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Reverse',
                preConfirm: function() {
                    var q = parseFloat($('#swal-rev-qty').val() || '0');
                    var reason = ($('#swal-rev-reason').val() || '').toString().trim();
                    if (!q || q <= 0) {
                        Swal.showValidationMessage('Please enter a valid quantity.');
                        return false;
                    }
                    if (q - qty > 0.000001) {
                        Swal.showValidationMessage('Cannot reverse more than remaining quantity.');
                        return false;
                    }
                    return { qty: q, reason: reason };
                }
            }).then(function(result) {
                if (!result.isConfirmed) return;
                doReverse(result.value.qty, result.value.reason);
            });
        } else {
            if (!confirm('Reverse this claim send back to claim stock?')) return;
            doReverse(qty, '');
        }
    };

    window.reverseClaimSendRow = function(btnEl) {
        var $btn = $(btnEl);
        window.applyReverseClaimSendFromAttrs({
            purchase_item_id: $btn.attr('data-purchase-item-id'),
            item_id: $btn.attr('data-item-id'),
            quantity: $btn.attr('data-quantity'),
            warehouse_id: $btn.attr('data-warehouse-id'),
            item_name: $btn.attr('data-item-name'),
            item_name_full: $btn.attr('data-item-name-full'),
            item_image_url: $btn.attr('data-item-image-url'),
            item_image_path: $btn.attr('data-item-image-path'),
            item_images_json: $btn.attr('data-item-images-json'),
            warehouse_name: $btn.attr('data-warehouse-name'),
            supplier_name: $btn.attr('data-supplier-name'),
            sent_date: $btn.attr('data-sent-date'),
            sent_time: $btn.attr('data-sent-time'),
            claim_id: $btn.attr('data-claim-id')
        });
    };

    // Fallback: delegated binding for dynamically-rendered reverse buttons.
    // (If inline onclick is blocked by overlays or re-render timing, this still works.)
    $(document).on('click', '#purchase-claim-history-modal .purchase-claim-reverse-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        try {
            window.reverseClaimSendRow(this);
        } catch (err) {
            console.warn('Reverse click handler failed', err);
        }
    });

    // Capture-phase safety net (helps when bubbling is prevented by other handlers).
    if (!window._purchaseClaimReverseCaptureBound) {
        window._purchaseClaimReverseCaptureBound = true;
        document.addEventListener('click', function(ev) {
            try {
                var btn = ev.target && ev.target.closest ? ev.target.closest('#purchase-claim-history-modal .purchase-claim-reverse-btn') : null;
                if (!btn) return;
                ev.preventDefault();
                ev.stopPropagation();
                window.reverseClaimSendRow(btn);
            } catch (e) {}
        }, true);
    }

    // Open detailed claim send history modal (only outgoing rows)
    $(document).on('click', '#purchase-claim-send-stock-summary-badge', function(e) {
        e.preventDefault();
        var branchId = ($('#purchaseBranchId').val() || '').toString();
        if (!branchId) {
            if (typeof Swal !== 'undefined' && Swal.fire) {
                Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first before viewing claim send history.' });
            } else {
                alert('Please select a branch first.');
            }
            return;
        }

        if (!$('#claim-send-stock-detail-modal').length) return;

        var branchName = ($('#selectedBranchName').text() || '').trim();
        var scopeLabel = branchName ? ('Branch: ' + branchName) : 'Branch';
        $('#claim-send-stock-detail-scope').html('<span class="badge text-white" style="background:#6f42c1; font-weight:800; letter-spacing:0.03em;">' + String(scopeLabel).replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</span>');

        $('#claim-send-stock-detail-loading').removeClass('d-none');
        $('#claim-send-stock-detail-content').addClass('d-none');
        $('#claim-send-stock-detail-tbody').empty();
        $('#claim-send-stock-detail-empty').addClass('d-none');
        $('#claim-send-stock-detail-modal').modal('show');

        $.ajax({
            url: '{{ route("sales.claim.send.stock.detail") }}',
            method: 'GET',
            data: { branch_id: branchId },
            success: function(res) {
                var totals = (res && res.totals) ? res.totals : {};
                var records = (res && res.records) ? res.records : [];

                window._claimSendStockDetailAllRecords = Array.isArray(records) ? records.slice() : [];
                window._claimSendStockDetailTotals = totals || {};
                window._claimSendStockDetailCurrentStock = (totals && totals.current_claim_stock != null) ? totals.current_claim_stock : 0;

                function renderClaimSendStockDetail() {
                    var all = window._claimSendStockDetailAllRecords || [];
                    $('#claim-send-stock-select-all-checkbox').prop('checked', false).prop('indeterminate', false);
                    $('#claim-send-stock-select-all-label').text('SELECT');
                    // Oldest first
                    var sorted = all.slice().sort(function(a, b) {
                        var at = (a && a.datetime_sort != null) ? Number(a.datetime_sort) : 0;
                        var bt = (b && b.datetime_sort != null) ? Number(b.datetime_sort) : 0;
                        return at - bt;
                    });

                    var totalsAll = window._claimSendStockDetailTotals || {};
                    $('#claim-send-stock-total-in').text((totalsAll.total_claim_in != null ? totalsAll.total_claim_in : 0));
                    $('#claim-send-stock-total-sent').text((totalsAll.total_claim_sent != null ? totalsAll.total_claim_sent : 0));
                    $('#claim-send-stock-current').text(window._claimSendStockDetailCurrentStock ?? 0);

                    var $tbody = $('#claim-send-stock-detail-tbody');
                    $tbody.empty();
                    if (!sorted.length) {
                        $('#claim-send-stock-detail-empty').removeClass('d-none');
                        return;
                    }
                    $('#claim-send-stock-detail-empty').addClass('d-none');

                    sorted.forEach(function(r) {
                        var refText = (r.reference_no || r.invoice_no || '');
                        var rawName = (r.item_name || '').toString().trim();
                        var code = (r.item_code || '').toString().trim();
                        var line = (r.item_line || '').toString().trim();
                        var meta = (r.item_meta || '').toString().trim();
                        var salesEditBaseUrl = '{{ url("sales") }}';
                        var purchasesEditBaseUrl = '{{ url("purchases") }}';
                        var editBtnHtml = '';
                        if (r.sale_id) {
                            editBtnHtml = '<button type="button" class="btn btn-sm btn-outline-primary" ' +
                                'onclick="window.location=\'' + salesEditBaseUrl + '/' + r.sale_id + '/edit\'' + '">' +
                                'Edit</button>';
                        } else if (r.purchase_id) {
                            editBtnHtml = '<button type="button" class="btn btn-sm btn-outline-primary" ' +
                                'onclick="window.location=\'' + purchasesEditBaseUrl + '/' + r.purchase_id + '/edit\'' + '">' +
                                'Edit</button>';
                        }

                        var safeAttr = function(v) {
                            return String(v || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        };
                        var rowCheckboxHtml = '<input type="checkbox" class="form-check-input claim-send-row-checkbox" ' +
                            'data-sale-id="' + ((r.sale_id != null) ? r.sale_id : '') + '" ' +
                            'data-purchase-id="' + ((r.purchase_id != null) ? r.purchase_id : '') + '" ' +
                            'data-item-id="' + ((r.item_id != null) ? r.item_id : '') + '" ' +
                            'data-warehouse-id="' + ((r.warehouse_id != null) ? r.warehouse_id : '') + '" ' +
                            'data-quantity="' + ((r.quantity != null) ? r.quantity : '') + '" ' +
                            'data-available-quantity="' + ((r.available_claim_qty != null) ? r.available_claim_qty : '') + '" ' +
                            'data-item-name="' + safeAttr(r.item_name) + '" ' +
                            'data-warehouse-name="' + safeAttr(r.warehouse_name || r.branch_name || '') + '" ' +
                            'data-item-image-url="' + safeAttr(r.image || '') + '" ' +
                            'data-item-image-path="' + safeAttr(r.image_path || '') + '" ' +
                            'data-item-images-json="' + safeAttr(JSON.stringify(Array.isArray(r.images) ? r.images : [])) + '" ' +
                            'style="width: 1rem; height: 1rem; margin: 0;">';

                        var escText = function(s) {
                            return String(s || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        };
                        var supplierName = (r.customer_name || '').toString().trim();
                        var rejectLineHtml = supplierName ? '<div class="small text-muted mt-1">Reject: ' + escText(supplierName) + '</div>' : '';

                        // Reverse: adds back claim stock by pushing entry_type="claim" into cart.
                        reverseBtnHtml = '<button type="button" class="btn btn-sm btn-outline-warning mt-1" ' +
                            'onclick="window.reverseClaimSendRow(this)" ' +
                            'data-item-id="' + safeAttr(r.item_id) + '" ' +
                            'data-warehouse-id="' + safeAttr(r.warehouse_id) + '" ' +
                            'data-quantity="' + safeAttr(r.quantity) + '" ' +
                            'data-item-name="' + safeAttr(r.item_name) + '" ' +
                            'data-item-name-full="' + safeAttr(line || rawName || r.item_name) + '" ' +
                            'data-warehouse-name="' + safeAttr(r.warehouse_name || r.branch_name || '') + '" ' +
                            'data-item-image-url="' + safeAttr(r.image || '') + '" ' +
                            'data-item-image-path="' + safeAttr(r.image_path || '') + '" ' +
                            'data-item-images-json="' + safeAttr(JSON.stringify(Array.isArray(r.images) ? r.images : [])) + '" ' +
                            'data-supplier-name="' + safeAttr(supplierName) + '" ' +
                            'title="Reverse reject and load Claim In">Reverse</button>';

                        var itemHtml = (function() {
                            var safe = function(s) { return String(s || '').replace(/</g, '&lt;').replace(/>/g, '&gt;'); };
                            var name = rawName;
                            if (name && code && name === code) name = '';
                            var title = '';
                            var subtitle = '';
                            if (name) {
                                if (name.indexOf(' • ') !== -1) {
                                    var parts = name.split(' • ').map(function(p){ return p.trim(); }).filter(Boolean);
                                    title = parts.shift() || '';
                                    subtitle = parts.join(' • ');
                                } else {
                                    title = name;
                                }
                            } else if (code) {
                                title = code;
                            } else {
                                title = '';
                            }
                            var html = '';
                            if (title) html += '<div class="fw-bold text-dark" style="line-height:1.1;">' + safe(title) + '</div>';
                            if (line) {
                                html += '<div class="small text-muted" style="line-height:1.1; margin-top:2px;">' + safe(line) + '</div>';
                                if (meta) html += '<div class="small text-muted" style="line-height:1.1; margin-top:2px;">' + safe(meta) + '</div>';
                            } else {
                                if (subtitle) html += '<div class="small text-muted" style="line-height:1.1; margin-top:2px;">' + safe(subtitle) + '</div>';
                            }
                            if (code && (code !== title)) {
                                html += '<div class="small" style="margin-top:4px; color:#f97316; font-weight:800; letter-spacing:0.02em;"><i class="ti ti-barcode me-1"></i>' + safe(code) + '</div>';
                            }
                            return html || '&nbsp;';
                        })();

                        $tbody.append(`
                            <tr>
                                <td>${r.date || ''}<div class="small text-muted">${r.time || ''}</div></td>
                                <td>${itemHtml}</td>
                                <td>${r.warehouse_name || r.branch_name || ''}</td>
                                <td class="text-end">${(r.quantity != null ? r.quantity : '')}</td>
                                <td>${(function(){
                                    var t = (r.entry_type || '').toString();
                                    var label = (r.entry_type_label || r.entry_type || '').toString();
                                    var bg = '#6c757d';
                                    if (t === 'claim_send') bg = '#7c3aed';
                                    else if (t === 'claim_in' || t === 'claim') bg = '#198754';
                                    return '<span class="badge text-white" style="background-color: ' + bg + '; font-weight: 700; letter-spacing: 0.02em;">' + label + '</span>';
                                })()}</td>
                                <td>${refText}${rejectLineHtml}</td>
                                <td>
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        ${rowCheckboxHtml}
                                        ${reverseBtnHtml}
                                        ${editBtnHtml}
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                }

                renderClaimSendStockDetail();
                $('#claim-send-stock-detail-loading').addClass('d-none');
                $('#claim-send-stock-detail-content').removeClass('d-none');
            },
            error: function() {
                $('#claim-send-stock-detail-modal').modal('hide');
            }
        });
    });

    // Row selection (Claim In / Claim Send)
    $(document).on('change', '#claim-stock-select-all-checkbox', function() {
        var checked = $(this).is(':checked');
        $('.claim-stock-row-checkbox').prop('checked', checked);
        $(this).prop('indeterminate', false);
        // Update header label
        var total = $('.claim-stock-row-checkbox').length;
        if (total > 0 && checked) $('#claim-stock-select-all-label').text('SELECT ALL');
        else $('#claim-stock-select-all-label').text('SELECT');
    });

    $(document).on('change', '.claim-stock-row-checkbox', function() {
        var $rows = $('.claim-stock-row-checkbox');
        var total = $rows.length;
        var checkedCount = $rows.filter(':checked').length;
        var $master = $('#claim-stock-select-all-checkbox');
        if (total <= 0) {
            $master.prop('checked', false).prop('indeterminate', false);
            return;
        }
        if (checkedCount === 0) {
            $master.prop('checked', false).prop('indeterminate', false);
        } else if (checkedCount === total) {
            $master.prop('checked', true).prop('indeterminate', false);
            $('#claim-stock-select-all-label').text('SELECT ALL');
        } else {
            $master.prop('checked', false).prop('indeterminate', true);
            $('#claim-stock-select-all-label').text('SELECT');
        }
        if (checkedCount !== total) $('#claim-stock-select-all-label').text('SELECT');
    });

    // Extra safety: some browsers/plugins may not trigger `change` reliably on programmatic re-renders.
    $(document).on('click', '#claim-stock-select-all-checkbox', function() {
        var checked = $(this).is(':checked');
        $('.claim-stock-row-checkbox').prop('checked', checked);
        $(this).prop('indeterminate', false);
        var total = $('.claim-stock-row-checkbox').length;
        if (total > 0 && checked) $('#claim-stock-select-all-label').text('SELECT ALL');
        else $('#claim-stock-select-all-label').text('SELECT');
    });

    $(document).on('click', '.claim-stock-row-checkbox', function() {
        var $rows = $('.claim-stock-row-checkbox');
        var total = $rows.length;
        var checkedCount = $rows.filter(':checked').length;
        var $master = $('#claim-stock-select-all-checkbox');
        if (total <= 0) {
            $master.prop('checked', false).prop('indeterminate', false);
            return;
        }
        if (checkedCount === 0) {
            $master.prop('checked', false).prop('indeterminate', false);
            $('#claim-stock-select-all-label').text('SELECT');
        } else if (checkedCount === total) {
            $master.prop('checked', true).prop('indeterminate', false);
            $('#claim-stock-select-all-label').text('SELECT ALL');
        } else {
            $master.prop('checked', false).prop('indeterminate', true);
            $('#claim-stock-select-all-label').text('SELECT');
        }
    });

    $(document).on('change', '#claim-send-stock-select-all-checkbox', function() {
        var checked = $(this).is(':checked');
        $('.claim-send-row-checkbox').prop('checked', checked);
        $(this).prop('indeterminate', false);
        var total = $('.claim-send-row-checkbox').length;
        if (total > 0 && checked) $('#claim-send-stock-select-all-label').text('SELECT ALL');
        else $('#claim-send-stock-select-all-label').text('SELECT');
    });

    $(document).on('change', '.claim-send-row-checkbox', function() {
        var $rows = $('.claim-send-row-checkbox');
        var total = $rows.length;
        var checkedCount = $rows.filter(':checked').length;
        var $master = $('#claim-send-stock-select-all-checkbox');
        if (total <= 0) {
            $master.prop('checked', false).prop('indeterminate', false);
            return;
        }
        if (checkedCount === 0) {
            $master.prop('checked', false).prop('indeterminate', false);
        } else if (checkedCount === total) {
            $master.prop('checked', true).prop('indeterminate', false);
            $('#claim-send-stock-select-all-label').text('SELECT ALL');
        } else {
            $master.prop('checked', false).prop('indeterminate', true);
            $('#claim-send-stock-select-all-label').text('SELECT');
        }
        if (checkedCount !== total) $('#claim-send-stock-select-all-label').text('SELECT');
    });

    $(document).on('click', '#claim-send-stock-select-all-checkbox', function() {
        var checked = $(this).is(':checked');
        $('.claim-send-row-checkbox').prop('checked', checked);
        $(this).prop('indeterminate', false);
        var total = $('.claim-send-row-checkbox').length;
        if (total > 0 && checked) $('#claim-send-stock-select-all-label').text('SELECT ALL');
        else $('#claim-send-stock-select-all-label').text('SELECT');
    });

    $(document).on('click', '.claim-send-row-checkbox', function() {
        var $rows = $('.claim-send-row-checkbox');
        var total = $rows.length;
        var checkedCount = $rows.filter(':checked').length;
        var $master = $('#claim-send-stock-select-all-checkbox');
        if (total <= 0) {
            $master.prop('checked', false).prop('indeterminate', false);
            return;
        }
        if (checkedCount === 0) {
            $master.prop('checked', false).prop('indeterminate', false);
            $('#claim-send-stock-select-all-label').text('SELECT');
        } else if (checkedCount === total) {
            $master.prop('checked', true).prop('indeterminate', false);
            $('#claim-send-stock-select-all-label').text('SELECT ALL');
        } else {
            $master.prop('checked', false).prop('indeterminate', true);
            $('#claim-send-stock-select-all-label').text('SELECT');
        }
    });

    // Keyboard: press Enter inside modals to trigger Load using current selection
    // (Helps when focus is on checkbox; avoids breaking typing in inputs.)
    $(document).on('keydown', '#claim-stock-detail-modal', function(e) {
        var key = e.key || e.keyCode;
        var isEnter = (key === 'Enter' || key === 13);
        if (!isEnter) return;
        // If focus is a non-checkbox input, ignore
        var $t = $(e.target);
        if ($t.is('input, textarea, select')) {
            if (!($t.is('input[type="checkbox"]'))) return;
        }
        setTimeout(function() {
            $('#purchase-claim-stock-detail-load-btn').trigger('click');
        }, 30);
    });

    $(document).on('keydown', '#claim-send-stock-detail-modal', function(e) {
        var key = e.key || e.keyCode;
        var isEnter = (key === 'Enter' || key === 13);
        if (!isEnter) return;
        var $t = $(e.target);
        if ($t.is('input, textarea, select')) {
            if (!($t.is('input[type="checkbox"]'))) return;
        }
        setTimeout(function() {
            $('#purchase-claim-send-stock-detail-load-btn').trigger('click');
        }, 30);
    });
    
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
            $('#selected-item-image').val('');
            $('#item-search-stock').html('');
            $('#item-search-warehouse').text('');
            // Hide selected item details display when search is cleared
            $('#selected-item-details-display').addClass('d-none');
            $('#selected-item-details-line1').html('').removeClass('selected-product-line1--segments battery-type-sequence fw-bold text-uppercase').addClass('small');
            $('#selected-item-details-line2').html('').css('display', 'none');
            $('#selected-item-details-line3').html('');
            $('#selected-item-quality-wrap').html('').addClass('d-none');
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
                data: (function() {
                    var data = { q: query, branch_id: branchId, limit: 15 };
                    if (typeof currentEntryType !== 'undefined') {
                        if (currentEntryType === 'scrap') data.entry_type = 'scrap';
                        else if (currentEntryType === 'claim' || currentEntryType === 'claim_send') data.entry_type = 'claim';
                    }
                    return data;
                })(),
                success: function(results) {
                    const hasItemResults = results.some(function(r) { return r.type === 'item'; });
                    const noItemsHtml = `
                        <div class="p-4 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);">
                                <i class="ti ti-search-off fs-32" style="color: #667eea;"></i>
                            </div>
                            <p class="fw-600 text-dark mb-1">No items found</p>
                            <p class="text-muted small mb-2">Try: code + space + vehicle or keyword. e.g. 53495878 Toyota</p>
                            <a href="#" class="btn btn-primary btn-sm fw-bold btn-open-add-item-modal" data-create-url="{{ url(route('all.items.create.new')) }}" style="border-radius: 8px;">
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
                                // Claim context for Purchase modal: Claim Return + Claim Send should always use claim stock.
                                const isClaimContext = (typeof currentEntryType !== 'undefined') && (currentEntryType === 'claim' || currentEntryType === 'claim_send');
                                // IMPORTANT: In claim flows (Claim Return / Claim Send), NEVER fall back to normal stock (on_hand).
                                // If API didn't provide claim stock, treat as 0.
                                const stock = (result.stock !== undefined && result.stock !== null)
                                    ? parseFloat(result.stock)
                                    : (isClaimContext ? 0 : (item.on_hand || 0));
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
                                        // Quality is shown on brand line (SUZUKI - China); omit here to avoid duplicate
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
                                
                                // In claim flows, match Claim In UX: show claim qty as "Piece" and remove ✓ icon.
                                const unitForStockBadge = (isClaimContext && !(literPerCan != null && literPerCan > 0)) ? 'Piece' : unit;
                                if (isClaimContext) {
                                    stockIcon = '';
                                    stockColor = stockValue > 0 ? 'secondary' : 'danger';
                                }
                                
                                // Order: Grade • Level • Company (category on second line). For oil, append unit (e.g. Can - 4 Liter).
                                const gradeLevelCompanyParts = [];
                                if (grade && !isDummy(grade)) gradeLevelCompanyParts.push(grade);
                                if (level && !isDummy(level)) gradeLevelCompanyParts.push(level);
                                if (company && !isDummy(company)) gradeLevelCompanyParts.push(company);
                                if (itemType === 'oil' && unitForFirstLine) gradeLevelCompanyParts.push(unitForFirstLine);
                                const gradeLevelCompanyLine = gradeLevelCompanyParts.length > 0 ? gradeLevelCompanyParts.join(' • ') : '';
                                var gradeLevelCompanyLineForText = '';
                                if (gradeLevelCompanyLine) {
                                    const textParts = [];
                                    if (grade && !isDummy(grade)) textParts.push(grade);
                                    if (level && !isDummy(level)) textParts.push(level);
                                    if (company && !isDummy(company)) {
                                        textParts.push(quality && !isDummy(quality) ? (company + ' - ' + quality) : company);
                                    }
                                    if (itemType === 'oil' && unitForFirstLine) textParts.push(unitForFirstLine);
                                    gradeLevelCompanyLineForText = textParts.join(' • ');
                                }
                                
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
                                } else if (itemType === 'filters' || itemType === 'parts' || itemType === 'breakpad') {
                                    var catForParts = (category && !isDummy(category) && !/^other$/i.test(String(category).trim())) ? String(category).trim() : '';
                                    var partsHeadLine = formatPurchasePartLineDisplay(partNumber, catForParts, quality, company, productName);
                                    firstLineText = partsHeadLine || productName;
                                    if (partsHeadLine) {
                                        firstLineHtml = '<div class="item-search-parts-headline mb-1 text-uppercase" style="font-weight:700;">' + highlightText(partsHeadLine, query) + '</div>';
                                    } else {
                                        firstLineHtml = '<div class="fw-bold text-dark mb-1 text-uppercase">' + highlightText(productName, query) + '</div>';
                                    }
                                    if (mileageName) {
                                        firstLineHtml += '<div class="small text-muted mt-0 text-uppercase">Mileage: ' + highlightText(mileageName, query) + '</div>';
                                    }
                                } else if (gradeLevelCompanyLine) {
                                    firstLineText = gradeLevelCompanyLineForText || gradeLevelCompanyLine;
                                    // For oil with unit Can: show "4 LITER CAN" on card (e.g. 10W40 • X5 • ZIC • 4 LITER CAN)
                                    if (itemType === 'oil' && unit && (unit + '').toUpperCase() === 'CAN' && !/CAN\s*$/i.test(gradeLevelCompanyLine)) {
                                        firstLineText = (gradeLevelCompanyLineForText || gradeLevelCompanyLine) + ' CAN';
                                    }
                                    // Build first line with grade/level (e.g. "G") in color; company + quality on one line
                                    var partQualityLayoutTypes = itemType === 'oil';
                                    var firstLinePartsHtml = gradeLevelCompanyParts.map(function(p) {
                                        var safe = String(p).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                                        if (level && p === level) return '<span class="bg-dark text-white fw-semibold px-1 rounded">' + safe + '</span>';
                                        if (company && !isDummy(company) && p === company) {
                                            var brandLine = '<span class="item-search-brand-part">' + highlightText(company, query) + '</span>';
                                            if (quality && !isDummy(quality)) {
                                                brandLine += ' <span class="item-search-brand-dash text-muted fw-normal"> - </span><span class="product-quality-badge">' + highlightText(quality, query) + '</span>';
                                            }
                                            return brandLine;
                                        }
                                        return highlightText(p, query);
                                    });
                                    if (itemType === 'oil' && unit && (unit + '').toUpperCase() === 'CAN' && !/CAN\s*$/i.test(gradeLevelCompanyLine)) {
                                        firstLinePartsHtml.push('CAN');
                                    }
                                    // Part # above grade/company line — oil + filters/parts/breakpad
                                    var partAboveLinePrefix = '';
                                    if (partQualityLayoutTypes && partNumber && !isDummy(partNumber)) {
                                        const pnTitle = String(partNumber).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
                                        partAboveLinePrefix = '<div class="item-search-part-number mb-1 text-uppercase" title="' + pnTitle + '">' + highlightText(partNumber, query) + '</div>';
                                    }
                                    firstLineHtml = partAboveLinePrefix + '<div class="fw-bold text-dark mb-1 text-uppercase">' + firstLinePartsHtml.join(' • ') + '</div>';
                                    // Second line: category (e.g. AIR FILTER / petrol engine oil) then Mileage when available
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
                                
                                // For battery: extra line under sequence — only show Volt, CCA, short_disc, Vehicles if added
                                let batteryExtraHtml = '';
                                if (itemType === 'battery') {
                                    const batteryExtraParts = [];
                                    if (volt) batteryExtraParts.push(volt + (String(volt).indexOf('V') !== -1 ? '' : 'V'));
                                    if (cca) batteryExtraParts.push(cca + (String(cca).indexOf('CCA') !== -1 ? '' : 'CCA'));
                                    if (shortDisc && !isDummy(shortDisc)) batteryExtraParts.push(shortDisc.replace(/</g, '&lt;').replace(/>/g, '&gt;'));
                                    if (vehicleInfo) batteryExtraParts.push('Vehicles: ' + vehicleInfo.replace(/</g, '&lt;').replace(/>/g, '&gt;'));
                                    if (batteryExtraParts.length > 0) {
                                        batteryExtraHtml = '<div class="small text-muted mt-1">' + batteryExtraParts.join(' • ') + '</div>';
                                    }
                                }
                                
                                // Get item image URL (same-origin path to avoid console load errors)
                                const itemImage = normalizeItemImageUrl(item.image || '');
                                const qualityDataAttr = (quality && !isDummy(quality)) ? String(quality).replace(/&/g, '&amp;').replace(/"/g, '&quot;') : '';
                                const partNumberDataAttr = (partNumber && !isDummy(partNumber)) ? String(partNumber).replace(/&/g, '&amp;').replace(/"/g, '&quot;') : '';
                                const companyDataAttr = (company && !isDummy(company)) ? String(company).replace(/&/g, '&amp;').replace(/"/g, '&quot;') : '';
                                const categoryDataAttr = (category && !isDummy(category)) ? String(category).replace(/&/g, '&amp;').replace(/"/g, '&quot;') : '';
                                
                                html += `
                                    <div class="p-3 border-bottom item-search-result" 
                                         data-type="item"
                                         data-is-temporary="${item.is_temporary ? '1' : '0'}"
                                         data-bar-code="${(barCode || '').replace(/"/g, '&quot;')}"
                                         data-item-type="${(itemType || '').replace(/"/g, '&quot;')}"
                                         data-part-number="${partNumberDataAttr}"
                                         data-company="${companyDataAttr}"
                                         data-quality="${qualityDataAttr}"
                                         data-category="${categoryDataAttr}"
                                         data-product-type-label="${categoryDataAttr}"
                                         data-id="${item.id}" 
                                         data-name="${productName.replace(/"/g, '&quot;')}"
                                         data-display="${displayString.replace(/"/g, '&quot;')}"
                                         data-first-line="${firstLineText.replace(/"/g, '&quot;')}"
                                         data-details="${detailsText.replace(/"/g, '&quot;')}"
                                         data-line1-details="${line1Details.replace(/"/g, '&quot;')}"
                                         data-vehicle="${vehicleText.replace(/"/g, '&quot;')}"
                                         data-code="${codeText.replace(/"/g, '&quot;')}"
                                         data-volt="${(itemType === 'battery' && volt) ? (String(volt).indexOf('V') !== -1 ? volt : volt + 'V') : ''}"
                                         data-cca="${(itemType === 'battery' && cca) ? (cca + (String(cca).indexOf('CCA') !== -1 ? '' : 'CCA')) : ''}"
                                         data-rate="${rate}"
                                         data-unit="${unit}"
                                         data-warehouse-id="${result.warehouse_id || ''}"
                                         data-liter-per-can="${(literPerCan != null && literPerCan > 0) ? literPerCan : ''}"
                                         data-oil-sequence="${(itemType === 'oil' ? firstLineText : '').replace(/"/g, '&quot;')}"
                                         data-oil-category="${(itemType === 'oil' ? (category || '') : '').replace(/"/g, '&quot;')}"
                                         data-oil-mileage="${(itemType === 'oil' ? (mileageName || '') : '').replace(/"/g, '&quot;')}"
                                         style="cursor: pointer; transition: all 0.2s ease; background: white;">
                                        <div class="d-flex justify-content-between align-items-start">
                                            ${itemImage ? `<div class="me-3" style="flex-shrink: 0;">
                                                <img src="${itemImage}" alt="${productName}" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                                            </div>` : ''}
                                            <div class="flex-grow-1 me-3">
                                                ${firstLineHtml}
                                                ${batteryExtraHtml}
                                                ${codeInfo ? '<div class="text-primary small fw-semibold mt-1"><i class="ti ti-barcode me-1"></i>' + highlightedCodeInfo + '</div>' : ''}
                                            </div>
                                            <div class="text-end item-search-result-stock" style="min-width: 110px;">
                                                <div class="fw-bold text-primary mb-1">Rs ${parseFloat(rate).toFixed(2)}</div>
                                                <div class="small d-flex flex-column align-items-end gap-0">
                                                    <span class="badge bg-${stockColor} bg-opacity-10 text-${stockColor} d-inline-block px-2 py-1 rounded" style="font-size: 0.8rem; font-weight: 600;">
                                                        ${stockIcon ? '<i class="ti ' + stockIcon + ' me-1"></i>' : ''}${isClaimContext ? 'Claim: ' : ''}${stockDisplay} ${unitForStockBadge}
                                                    </span>
                                                    ${stockDisplayHtml}
                                                    ${literPerCanHtml}
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
            const itemVolt = $(this).data('volt') || ''; // Volt like "12V"
            const itemRate = $(this).data('rate');
            const itemUnit = $(this).data('unit');
            const itemLiterPerCan = $(this).data('liter-per-can'); // e.g. 4 for "4 L per can"
            const warehouseId = $(this).closest('.item-search-result').data('warehouse-id') || '';
            const itemType = $(this).data('item-type') || '';
            const oilSequence = $(this).data('oil-sequence') || '';
            const oilCategory = $(this).data('oil-category') || '';
            const oilMileage = $(this).data('oil-mileage') || '';
            
            // Set input value and include company name if missing.
            var inputNameText = (itemFirstLine || itemName || '').toString().trim();
            $('#selected-item-id').val(itemId);
            $('#selected-item-is-temporary').val(($(this).data('is-temporary') === 1 || $(this).data('is-temporary') === '1') ? '1' : '0');
            $('#selected-item-bar-code').val(($(this).data('bar-code') || $(this).data('code') || '').toString().trim());
            var companyFromLine1 = '';
            if (itemLine1Details && String(itemLine1Details).indexOf('•') !== -1) {
                companyFromLine1 = String(itemLine1Details).split('•')[0].trim();
            }
            if (!companyFromLine1 && itemFirstLine && String(itemFirstLine).indexOf('•') !== -1) {
                var flParts = String(itemFirstLine).split('•').map(function(x){ return x.trim(); }).filter(Boolean);
                if (flParts.length > 1) companyFromLine1 = flParts[flParts.length - 1];
            }
            var pnAttr = ($(this).attr('data-part-number') || '').trim();
            $('#selected-item-part-number').val(pnAttr || '');
            var qAttr = ($(this).attr('data-quality') || '').trim();
            var coAttr = ($(this).attr('data-company') || '').trim();
            var productTitlePick = String(itemName || '').trim();
            $('#selected-item-product-title').val(productTitlePick);
            var catTypePick = ($(this).attr('data-product-type-label') || $(this).attr('data-category') || '').trim();
            if (/^other$/i.test(catTypePick)) catTypePick = '';
            $('#selected-item-product-type-label').val(catTypePick);
            var itLower = (itemType || '').toString().toLowerCase();
            var usePartBreakFilterInput = (itLower === 'filters' || itLower === 'parts' || itLower === 'breakpad');
            var useOilPartQualityInput = (itLower === 'oil');
            if (usePartBreakFilterInput) {
                var linePick = formatPurchasePartLineDisplay(pnAttr, catTypePick, qAttr, '', productTitlePick);
                if (linePick) inputNameText = linePick;
            } else if (useOilPartQualityInput) {
                var dotSegs = [];
                if (pnAttr) dotSegs.push(pnAttr);
                if (qAttr) dotSegs.push(qAttr);
                if (dotSegs.length) {
                    inputNameText = dotSegs.join(' . ');
                }
            } else if (companyFromLine1 && inputNameText && inputNameText.toLowerCase().indexOf(companyFromLine1.toLowerCase()) === -1) {
                inputNameText += ' • ' + companyFromLine1;
            }
            $('#item-search').val(inputNameText);
            $('#selected-item-company').val(coAttr || companyFromLine1 || '');
            $('#item-quantity').val('1');
            $('#item-unit').val(itemType === 'battery' ? 'Piece' : (itemUnit || 'Unit'));
            $('#item-search-results').hide();
            $('#item-edit-in-modal-btn').show();
            
            // Show item details below input — battery: Line1 = sequence (bold), Line2 = 12V • 380CCA (muted); oil: Line1 = sequence, Line2 = category • MILEAGE, Line3 = Can = X L (blue); else details • vehicle
            let line1 = '';
            let line2 = '';
            let line3 = '';
            
            const isBatteryStyle = (itemFirstLine && itemFirstLine.indexOf(' • ') !== -1 && (itemVolt || itemCca));
            const isOil = (itemType === 'oil');
            
            if (isBatteryStyle) {
                line1 = (itemFirstLine || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                const voltPart = itemVolt ? (String(itemVolt).indexOf('V') !== -1 ? itemVolt : itemVolt + 'V') : '';
                const ccaPart = itemCca ? (String(itemCca).indexOf('CCA') !== -1 ? itemCca : itemCca + 'CCA') : '';
                line2 = [voltPart, ccaPart].filter(Boolean).join(' • ');
            } else if (isOil) {
                line1 = (oilSequence || itemFirstLine || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                const line2Parts = [];
                if (oilCategory) line2Parts.push(oilCategory);
                if (oilMileage) line2Parts.push('MILEAGE: ' + oilMileage);
                line2 = line2Parts.join(' • ');
                if (itemLiterPerCan != null && itemLiterPerCan !== '' && !isNaN(parseFloat(itemLiterPerCan)) && parseFloat(itemLiterPerCan) > 0) {
                    const val = parseFloat(itemLiterPerCan);
                    line3 = 'Can = ' + (Number.isInteger(val) ? val : val.toFixed(1)) + ' L';
                }
            } else if (itemType === 'filters' || itemType === 'parts' || itemType === 'breakpad') {
                var catAttrPick = ($(this).attr('data-product-type-label') || $(this).attr('data-category') || '').trim();
                if (/^other$/i.test(catAttrPick)) catAttrPick = '';
                var modalPickLine = formatPurchasePartLineDisplay(pnAttr, catAttrPick, qAttr, coAttr, productTitlePick);
                line1 = modalPickLine
                    ? '<div class="selected-product-line1--segments fw-bold text-uppercase item-search-parts-headline" style="font-weight:700;">' + escapeHtml(modalPickLine.toUpperCase()) + '</div>'
                    : '';
                if (itemLiterPerCan != null && itemLiterPerCan !== '' && !isNaN(parseFloat(itemLiterPerCan)) && parseFloat(itemLiterPerCan) > 0) {
                    const val = parseFloat(itemLiterPerCan);
                    line2 = (Number.isInteger(val) ? val : val.toFixed(1)) + ' L per can';
                }
                if (itemCode) {
                    var safeCodePick = String(itemCode).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    line3 = '<i class="ti ti-barcode me-1"></i><span class="text-uppercase fw-semibold">' + safeCodePick.toUpperCase() + '</span>';
                }
            } else {
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
            }
            
            // Line 3: Barcode/Code (with icon) — only when not oil (oil uses line3 for "Can = X L"); filters/parts/breakpad set line3 above
            if (!isOil && itemCode && !line3) {
                var safeCodeLn = String(itemCode).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                line3 = '<i class="ti ti-barcode me-1"></i><span class="text-uppercase fw-semibold">' + safeCodeLn.toUpperCase() + '</span>';
            }
            
            var itemQualityAttr = ($(this).attr('data-quality') || '').trim();
            if (itemType === 'filters' || itemType === 'parts' || itemType === 'breakpad') {
                $('#selected-item-quality-wrap').html('').addClass('d-none');
            } else if (itemQualityAttr) {
                $('#selected-item-quality-wrap').html('<span class="product-quality-badge">' + escapeHtml(itemQualityAttr) + '</span>').removeClass('d-none');
            } else {
                $('#selected-item-quality-wrap').html('').addClass('d-none');
            }
            
            if (line1 || line2 || line3 || itemQualityAttr) {
                var isSegLine1Pick = (itemType === 'filters' || itemType === 'parts' || itemType === 'breakpad');
                $('#selected-item-details-line1').html(line1 || '&nbsp;')
                    .toggleClass('battery-type-sequence fw-bold text-uppercase', !!(isBatteryStyle || isOil))
                    .toggleClass('selected-product-line1--segments', !!isSegLine1Pick)
                    .toggleClass('small', !(isBatteryStyle || isOil || isSegLine1Pick));
                if (line2) {
                    $('#selected-item-details-line2').html(line2).css('display', '').removeClass('text-primary fw-semibold').addClass('text-muted small');
                } else {
                    $('#selected-item-details-line2').html('').css('display', 'none');
                }
                if (isOil && line3) {
                    $('#selected-item-details-line3').html(line3).css('display', '').addClass('text-primary');
                } else {
                    $('#selected-item-details-line3').html(line3 || '&nbsp;').css('display', line3 ? '' : 'none').removeClass('text-primary');
                }
                $('#selected-item-details-display').removeClass('d-none');
            } else {
                $('#selected-item-details-display').addClass('d-none');
            }
            // Warranty section: only for Battery-type products
            togglePurchaseItemWarrantySection(itemType === 'battery');
            
            // Load full item details to get total_price and warehouse
            $.ajax({
                url: (function() {
                    const baseUrl = '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId);
                    // IMPORTANT: In Claim Return / Claim Send, details must use claim stock (not normal stock).
                    if (typeof currentEntryType !== 'undefined' && (currentEntryType === 'claim' || currentEntryType === 'claim_send')) {
                        return baseUrl + '?entry_type=claim';
                    }
                    if (typeof currentEntryType !== 'undefined' && currentEntryType === 'scrap') {
                        return baseUrl + '?entry_type=scrap';
                    }
                    return baseUrl;
                })(),
                method: 'GET',
                success: function(response) {
                    itemBaseRetailPrice = (response.retail_price != null && response.retail_price !== '' && !isNaN(parseFloat(response.retail_price))) ? parseFloat(response.retail_price) : null;
                    syncSelectedItemMasterSaleFromDetailsResponse(response);
                    syncSelectedItemCategoryFromDetailsResponse(response);
                    syncSelectedItemLabelMetaFromDetailsResponse(response);
                    $('#selected-item-bar-code').val(response.bar_code || '');
                    if (response.tax_percentage != null && !isNaN(parseFloat(response.tax_percentage))) {
                        var gstVal = String(Math.round(parseFloat(response.tax_percentage)));
                        if ($('#item-tax-percent option[value="' + gstVal + '"]').length) $('#item-tax-percent').val(gstVal);
                    }
                    if (response.r_tax_percentage != null && response.r_tax_percentage !== '' && !isNaN(parseFloat(response.r_tax_percentage)))
                        $('#item-rtax-percent').val(parseFloat(response.r_tax_percentage));
                    if (response.amount_adjustment_pct != null && response.amount_adjustment_pct !== '') {
                        var adjVal = String(Math.round(parseFloat(response.amount_adjustment_pct)));
                        if ($('#item-retail-percentage option[value="' + adjVal + '"]').length) $('#item-retail-percentage').val(adjVal);
                        else $('#item-retail-percentage').val('');
                    } else {
                        $('#item-retail-percentage').val('');
                    }
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
                        $('#item-unit').val(((response.type || itemType || '') + '').toLowerCase() === 'battery' ? 'Piece' : response.unit);
                    }

                    // Search input: part . quality (no company)
                    if (editingRowId === null) {
                        var itApi = (response.type || itemType || '').toString().toLowerCase();
                        var pnApi = (response.part_number != null && String(response.part_number).trim() !== '') ? String(response.part_number).trim() : '';
                        var qApi = (response.quality_name != null && String(response.quality_name).trim() !== '') ? String(response.quality_name).trim() : '';
                        var ptApi = (response.product_title != null && String(response.product_title).trim() !== '') ? String(response.product_title).trim() : '';
                        if (ptApi) $('#selected-item-product-title').val(ptApi);
                        var typeLabApi = (response.product_type_label != null && String(response.product_type_label).trim() !== '') ? String(response.product_type_label).trim() : '';
                        if (!typeLabApi && response.category_name != null && String(response.category_name).trim() !== '' && !/^other$/i.test(String(response.category_name).trim())) {
                            typeLabApi = String(response.category_name).trim();
                        }
                        if (typeLabApi) $('#selected-item-product-type-label').val(typeLabApi);
                        if ((itApi === 'filters' || itApi === 'parts' || itApi === 'breakpad') && (pnApi || qApi || ptApi || typeLabApi)) {
                            var lineApi = formatPurchasePartLineDisplay(pnApi, typeLabApi, qApi, '', ptApi);
                            if (lineApi) $('#item-search').val(lineApi);
                        } else if (itApi === 'oil' && (pnApi || qApi)) {
                            var segsApi = [];
                            if (pnApi) segsApi.push(pnApi);
                            if (qApi) segsApi.push(qApi);
                            if (segsApi.length) $('#item-search').val(segsApi.join(' . '));
                        }
                    }

                    // Auto-select warehouse if available (from response or from search result)
                    const finalWarehouseId = response.warehouse_id || warehouseId;
                    if (finalWarehouseId) {
                        $('#selected-warehouse-id').val(finalWarehouseId);
                    }
                    
                    // Show item image if available (normalize to same-origin path)
                    if (response.image) {
                        $('#item-search-image').attr('src', normalizeItemImageUrl(response.image));
                        $('#selected-item-image').val(response.image || '');
                        $('#item-search-image-preview').removeClass('d-none');
                    } else {
                        $('#item-search-image-preview').addClass('d-none');
                        $('#selected-item-image').val('');
                    }
                    $('#selected-item-images-json').val(JSON.stringify((response.images && Array.isArray(response.images)) ? response.images : []));
                    
                    // Stock will be set after loadItemStockStatus (correct per-warehouse value)
                    $('#item-search-stock').html('<span class="text-muted small">...</span>');
                    
                    // Warranty section: only for Battery; bind value/unit from item master when available
                    var isBatteryFromApi = (response.type || '').toString().toLowerCase() === 'battery';
                    togglePurchaseItemWarrantySection(isBatteryFromApi);
                    if (isBatteryFromApi) {
                        if (response.warranty_value && response.warranty_unit) {
                            $('#warranty-value').val(response.warranty_value);
                            $('#warranty-unit').val(response.warranty_unit);
                        } else {
                            $('#warranty-value').val('');
                            $('#warranty-unit').val('');
                        }
                    }
                    
                    // Load stock status to show warehouse options and auto-select
                    loadItemStockStatus(itemId);
                    
                    // Load purchase history
                    loadCustomerHistory(itemId);
                },
                error: function() {
                    // Fallback to basic data if API fails
                    togglePurchaseItemWarrantySection(itemType === 'battery');
                    $('#item-rate').val(Math.round(parseFloat(itemRate || 0)));
                    updateRetailColumnByRate();
                    var qFallback = (item && item.quality_item && item.quality_item.name) ? String(item.quality_item.name).trim() : '';
                    $('#selected-item-type').val((itemType || '').toString().trim().toLowerCase());
                    $('#selected-item-quality-name').val(qFallback);
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
        var ctx = 'normal';
        if (currentEntryType === 'claim' || currentEntryType === 'claim_send') ctx = 'claim';
        else if (currentEntryType === 'scrap') ctx = 'scrap';
        console.debug('Claim stock modal load check', {
            item_id: itemId,
            barcode: ($('#selected-item-bar-code').val() || '').toString().trim(),
            warehouse_id: ($('#selected-warehouse-id').val() || '').toString().trim(),
            stock_type: ctx
        });
        $.ajax({
            url: '{{ route("purchases.items.stock.status", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            data: branchIdParam ? { branch_id: branchIdParam, context: ctx } : { context: ctx },
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
                if (initialSelectedIds.length > 0) {
                    $('#selected-warehouse-ids').val(initialSelectedIds.join(','));
                    if (!$('#selected-warehouse-id').val()) $('#selected-warehouse-id').val(initialSelectedIds[0] || '');
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
                        var qtyLabelDisplay = qtyText || (mainQtyDisp + ' ' + canLabel);
                        if (multVal > 0) {
                            var cartons = parseInt(stock.cartons, 10) || Math.floor(qty);
                            var looseLiters = parseFloat(stock.loose_liters) || 0;
                            var totalLFromCans = cartons * multVal;
                            if (totalLFromCans > 0 || looseLiters > 0) {
                                var totalPart = totalLFromCans > 0 ? (Number.isInteger(totalLFromCans) ? totalLFromCans : totalLFromCans.toFixed(2)) + ' L' : '';
                                var extraPart = '';
                                if (looseLiters > 0) {
                                    extraPart = looseLiters < 1 ? (Math.round(looseLiters * 1000) + ' ml') : (Number.isInteger(looseLiters) ? looseLiters : looseLiters.toFixed(2)) + ' L';
                                    if (totalPart) totalPart += ' + ' + extraPart; else totalPart = extraPart;
                                }
                                var suffix = (extraPart || !totalPart) ? '' : ' total';
                                if (totalPart) qtyLabelDisplay += ' (' + totalPart + suffix + ')';
                            }
                        }
                        var pill1 = '';
                        var pill2 = '';
                        if (isCanLiter) {
                            pill1 = (multVal + ' L PER ' + (canLabel || 'CAN')).replace(/\s+/g, ' ').toUpperCase();
                            // pill2 removed: was (N Liter) duplicate of unit info
                        } else {
                            pill1 = (canLabel || 'Piece').toUpperCase();
                        }
                        // Incoming modes (Claim In / Scrap In): allow receiving regardless of current on-hand.
                        // Outgoing modes: cap by available stock to avoid negative.
                        var isIncomingReceive = (typeof currentEntryType !== 'undefined' && ['claim', 'scrap', 'scrap_in'].includes(String(currentEntryType)));
                        var isPurchaseFlow = (typeof currentEntryType === 'undefined' || String(currentEntryType || 'purchase') === 'purchase');
                        var maxCans = isIncomingReceive ? 1000 : Math.max(0, Math.floor(qty));
                        // Purchase + incoming flows: always allow up to 1000 from dropdown.
                        // Outgoing flows keep existing stock-aware cap.
                        var maxUiCans = (isPurchaseFlow || isIncomingReceive) ? 1000 : Math.min(100, maxCans);
                        var qtySelectOpts = '<option value="0" selected>0</option>';
                        for (var n = 1; n <= maxUiCans; n++) {
                            // Keep options enabled; any stock-limit validation should happen on submit/backend.
                            qtySelectOpts += '<option value="' + n + '">' + n + '</option>';
                        }
                        var disableQtySelect = false;

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
                                    <span class="stock-bar-qty-label">${qtyLabelDisplay}</span>
                                    <div class="stock-bar-inputs">
                                        <select class="stock-bar-input stock-warehouse-qty-input" data-warehouse-id="${stock.id}" onclick="event.stopPropagation();" data-unit="${(unitLabel || 'Piece').replace(/"/g, '&quot;')}" title="Quantity">${qtySelectOpts}</select>
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
                    // Default qty = 1 (priority: selected DISPLAY warehouse), else first eligible row.
                    // Skip when editing a row — pendingEditItem will set warehouse qty only (e.g. Send Claim: Green-C only).
                    if (!pendingEditItem) {
                        var defaultQtySet = false;
                        var $displaySelectedRow = $('#stock-status-list .stock-warehouse-item.bg-primary').filter(function() {
                            return String($(this).attr('data-display') || '').trim().toLowerCase() === 'display';
                        }).first();
                        if ($displaySelectedRow.length) {
                            var $displaySel = $displaySelectedRow.find('select.stock-warehouse-qty-input');
                            if ($displaySel.length && $displaySel.find('option[value="1"]').length) {
                                $displaySel.val('1');
                                defaultQtySet = true;
                            }
                        }

                        if (!defaultQtySet) {
                            $('#stock-status-list .stock-warehouse-item').each(function() {
                                var $row = $(this);
                                var $sel = $row.find('select.stock-warehouse-qty-input');
                                if (!$sel.length) return;
                                var available = parseFloat($row.attr('data-quantity')) || 0;
                                var isClaimReceive = (typeof currentEntryType !== 'undefined' && String(currentEntryType) === 'claim');
                                if ((isClaimReceive || available >= 1) && $sel.find('option[value="1"]').length) {
                                    $sel.val('1');
                                    defaultQtySet = true;
                                    return false;
                                }
                            });
                        }
                    }
                }
                // Sync per-liter rate with "X L PER CAN" from stock list (so rate column uses same liters-per-can as pills)
                var lpcFromStock = null;
                stockData.forEach(function(stock) {
                    if (stock.type !== 'warehouse' || lpcFromStock != null) return;
                    var mult = stock.base_unit_multiplier != null ? Number(stock.base_unit_multiplier) : 0;
                    var baseU = (stock.base_unit || '').toString().toLowerCase();
                    var uLabel = (stock.unit || '').toString().toLowerCase();
                    if (mult > 0 && (baseU.indexOf('liter') !== -1 || baseU.indexOf('ltr') !== -1 || uLabel.indexOf('can') !== -1)) {
                        lpcFromStock = mult;
                    }
                });
                if (lpcFromStock != null && lpcFromStock > 0) {
                    $('#selected-item-is-oil').val('1');
                    $('#selected-item-liter-per-can').val(lpcFromStock);
                    $('#item-per-liter-wrap').removeClass('d-none');
                    var canRate = parseFloat($('#item-rate').val()) || 0;
                    var curPerLiter = ($('#item-per-liter-rate').val() || '').toString().trim();
                    if (curPerLiter === '' || isNaN(parseFloat(curPerLiter))) {
                        window._suppressOilRateSync = true;
                        $('#item-per-liter-rate').val(canRate > 0 ? (canRate / lpcFromStock).toFixed(2) : '');
                        window._suppressOilRateSync = false;
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
                    $('#item-search-stock').html('');
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
                    var peEntryType = (pe.entry_type || 'purchase').toString();
                    var isSingleWhClaimLike = ['claim_send', 'claim', 'scrap', 'damage', 'return'].indexOf(peEntryType) >= 0;
                    if (pe.warehouse_id) {
                        var whIdStr = String(pe.warehouse_id);
                        var $row = $('#stock-status-list .stock-warehouse-item[data-warehouse-id="' + whIdStr + '"]');
                        if ($row.length) {
                            // Only warehouses that actually have qty on this edit — do not select every row (fixes Send Claim: Green-C vs Display).
                            var selectedIds = [];
                            if (pe.quantities_by_warehouse && typeof pe.quantities_by_warehouse === 'object') {
                                for (var qk in pe.quantities_by_warehouse) {
                                    if (!pe.quantities_by_warehouse.hasOwnProperty(qk)) continue;
                                    if (parseFloat(pe.quantities_by_warehouse[qk]) > 0) selectedIds.push(String(qk));
                                }
                            }
                            if (selectedIds.indexOf(whIdStr) === -1 && editQty > 0) selectedIds.push(whIdStr);
                            if (!isSingleWhClaimLike) {
                                var prev = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
                                prev.forEach(function(pid) { if (pid && selectedIds.indexOf(pid) === -1) selectedIds.push(pid); });
                                $('#stock-status-list .stock-warehouse-item').each(function() {
                                    var wid = ($(this).data('warehouse-id') || '').toString();
                                    if (wid && selectedIds.indexOf(wid) === -1) selectedIds.push(wid);
                                });
                            }
                            $('#selected-warehouse-ids').val(selectedIds.join(','));
                            $('#selected-warehouse-id').val(selectedIds[0] || pe.warehouse_id);
                            if ($('#item-save-warehouse option[value="' + whIdStr + '"]').length) $('#item-save-warehouse').val(pe.warehouse_id);
                            // Zero out every warehouse qty first for claim-like single lines, then apply saved map only.
                            if (isSingleWhClaimLike) {
                                $('#stock-status-list .stock-warehouse-qty-input').each(function() {
                                    var $s = $(this);
                                    if ($s.find('option[value="0"]').length) $s.val('0');
                                    else if ($s.find('option[value=""]').length) $s.val('');
                                    else $s.val('0');
                                });
                            }
                            var $qtyInput = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + whIdStr + '"]');
                            var qtyToShow = (editQty <= 0 ? '0' : String(editQty));
                            if ($qtyInput.length) {
                                if ($qtyInput.find('option[value="' + qtyToShow + '"]').length) {
                                    $qtyInput.val(qtyToShow);
                                } else {
                                    $qtyInput.val(editQty <= 0 ? '0' : String(Math.min(editQty, 1000)));
                                }
                                if ($qtyInput[0] && $qtyInput[0].value !== $qtyInput.val()) { $qtyInput[0].value = $qtyInput.val(); }
                            }
                            if (pe.quantities_by_warehouse && typeof pe.quantities_by_warehouse === 'object') {
                                for (var qwhId in pe.quantities_by_warehouse) {
                                    if (!pe.quantities_by_warehouse.hasOwnProperty(qwhId)) continue;
                                    var qq = pe.quantities_by_warehouse[qwhId];
                                    var qwhStr = String(qwhId);
                                    var $qSel = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + qwhStr + '"]');
                                    var qVal = (parseFloat(qq) <= 0 ? '0' : String(Math.max(1, Math.min(1000, Math.round(parseFloat(qq))))));
                                    if ($qSel.length) {
                                        if ($qSel.find('option[value="' + qVal + '"]').length) $qSel.val(qVal);
                                        else $qSel.val(parseFloat(qq) > 0 ? qVal : '0');
                                        if ($qSel[0] && $qSel[0].value !== $qSel.val()) { $qSel[0].value = $qSel.val(); }
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
                // Keep warehouse selection in sync with qty dropdowns. When editing a row, do not fall back to "select all" warehouses.
                var selectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
                var editingPurchaseRowSync = (typeof editingRowId !== 'undefined' && editingRowId !== null);
                $('#stock-status-list .stock-warehouse-qty-input').each(function() {
                    var qv = parseFloat($(this).val());
                    if (!isNaN(qv) && qv > 0) {
                        var wid = ($(this).data('warehouse-id') || '').toString();
                        if (wid && selectedIds.indexOf(wid) === -1) selectedIds.push(wid);
                    }
                });
                if (selectedIds.length === 0 && !editingPurchaseRowSync) {
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
            $('#item-search-stock').html('');
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
            $('#item-search-stock').html('');
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

    // Click item thumbnail in add-item modal — all photos from API / selection
    $(document).on('click', '#item-search-image', function() {
        var fromJson = parseSelectedItemImagesJson();
        if (fromJson.length) {
            openPurchaseItemImageGallery(fromJson);
            return;
        }
        var src = $(this).attr('src');
        if (src) openPurchaseItemImageGallery([src]);
    });

    // Click purchase table row photo — gallery from purchaseItems + API (data-gallery on img is unreliable)
    $(document).on('click', '#items-tbody .purchase-row-item-thumb', function(e) {
        e.stopPropagation();
        var $img = $(this);
        var $row = $img.closest('tr.purchase-item-row');
        var rowId = $row.data('row-id');
        var item = (typeof purchaseItems !== 'undefined' && purchaseItems) ? purchaseItems.find(function(i) { return i.id === rowId; }) : null;
        var itemId = item && item.item_id != null ? item.item_id : ($row.data('item-id') || null);
        var voiceUrl = item && item.voice_url ? item.voice_url : null;

        function dedupeUrls(arr) {
            var seen = {};
            var out = [];
            (arr || []).forEach(function(u) {
                if (!u || typeof u !== 'string') return;
                var n = (typeof normalizeItemImageUrl === 'function' ? normalizeItemImageUrl(u) : u).trim();
                if (!n || seen[n]) return;
                seen[n] = true;
                out.push(n);
            });
            return out;
        }

        function finishOpen(urls, vUrl) {
            var u = dedupeUrls(urls);
            if (!u.length) {
                var src = $img.data('full-src') || $img.attr('src');
                if (src) u = [normalizeItemImageUrl(src)];
            }
            if (!u.length) return;
            var v = (vUrl !== undefined && vUrl !== null && String(vUrl).trim() !== '') ? vUrl : voiceUrl;
            openPurchaseItemImageGallery(u, v);
        }

        var urls = item ? buildPurchaseItemGalleryUrls(item) : [];
        // Always merge server gallery when we have item_id so the modal shows every stored photo (memory can be stale).
        if (itemId) {
            $.ajax({
                url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
                method: 'GET',
                dataType: 'json',
                data: (function() {
                    var d = {};
                    var bid = ($('#purchaseBranchId').val() || '').toString().trim();
                    if (bid) d.branch_id = bid;
                    if (typeof currentEntryType !== 'undefined') {
                        if (currentEntryType === 'claim' || currentEntryType === 'claim_send') d.entry_type = 'claim';
                        else if (currentEntryType === 'scrap') d.entry_type = 'scrap';
                    }
                    return d;
                })(),
                success: function(response) {
                    var apiUrls = [];
                    if (response.images && Array.isArray(response.images) && response.images.length) {
                        response.images.forEach(function(x) {
                            if (x && typeof x === 'string') apiUrls.push(x);
                        });
                    }
                    if (!apiUrls.length && response.image) apiUrls.push(response.image);
                    var merged = dedupeUrls((apiUrls.length ? apiUrls : []).concat(urls));
                    if (item && merged.length) {
                        item.images = merged.slice();
                        if (response.image) item.image = response.image;
                        if (response.voice_url) item.voice_url = response.voice_url;
                    }
                    var finalVoice = voiceUrl || response.voice_url || null;
                    finishOpen(merged.length ? merged : urls, finalVoice);
                },
                error: function() { finishOpen(urls); }
            });
            return;
        }
        finishOpen(urls);
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
            data: (function() {
                var data = {};
                var branchId = ($('#purchaseBranchId').val() || '').toString().trim();
                if (branchId) data.branch_id = branchId;
                if (typeof currentEntryType !== 'undefined') {
                    if (currentEntryType === 'claim' || currentEntryType === 'claim_send') data.entry_type = 'claim';
                    else if (currentEntryType === 'scrap') data.entry_type = 'scrap';
                }
                return data;
            })(),
            success: function(response) {
                $('#selected-item-id').val(response.id);
                $('#selected-item-is-temporary').val(response.is_temporary ? '1' : '0');
                $('#selected-item-bar-code').val(response.bar_code || '');
                if (response.voice_url) {
                    $('#selected-item-voice-url').val(String(response.voice_url).trim());
                } else {
                    $('#selected-item-voice-url').val('');
                }
                syncSelectedItemLabelMetaFromDetailsResponse(response);
                $('#item-edit-in-modal-btn').show();
                if (editingRowId === null) {
                    var apiName = (response.name || '').toString();
                    var plainName = (typeof stripHtml === 'function' ? stripHtml(apiName) : apiName.replace(/<[^>]*>/g, '')).trim();
                    var itLoad = (response.type || '').toString().toLowerCase();
                    var pnLoad = (response.part_number != null && String(response.part_number).trim() !== '') ? String(response.part_number).trim() : '';
                    var qLoad = (response.quality_name != null && String(response.quality_name).trim() !== '') ? String(response.quality_name).trim() : '';
                    var ptLoad = (response.product_title != null && String(response.product_title).trim() !== '') ? String(response.product_title).trim() : '';
                    if (ptLoad) $('#selected-item-product-title').val(ptLoad);
                    var typeLabLoad = (response.product_type_label != null && String(response.product_type_label).trim() !== '') ? String(response.product_type_label).trim() : '';
                    if (!typeLabLoad && response.category_name != null && String(response.category_name).trim() !== '' && !/^other$/i.test(String(response.category_name).trim())) {
                        typeLabLoad = String(response.category_name).trim();
                    }
                    if (typeLabLoad) $('#selected-item-product-type-label').val(typeLabLoad);
                    if ((itLoad === 'filters' || itLoad === 'parts' || itLoad === 'breakpad') && (pnLoad || qLoad || ptLoad || typeLabLoad)) {
                        var lineLoad = formatPurchasePartLineDisplay(pnLoad, typeLabLoad, qLoad, '', ptLoad);
                        if (lineLoad) $('#item-search').val(lineLoad);
                    } else if (itLoad === 'oil' && (pnLoad || qLoad)) {
                        var segsLoad = [];
                        if (pnLoad) segsLoad.push(pnLoad);
                        if (qLoad) segsLoad.push(qLoad);
                        if (segsLoad.length) $('#item-search').val(segsLoad.join(' . '));
                    } else {
                        $('#item-search').val(plainName || apiName || ('Item #' + (response.id || '')));
                    }
                }
                if (editingRowId === null) $('#item-quantity').val('1');
                
                // Show selected item details — battery: Line1 = sequence (bold), Line2 = 12V • 380CCA (muted); oil: Line1 = sequence, Line2 = category • MILEAGE, Line3 = Can = X L (blue); else details • vehicle
                var line1 = '';
                var line2 = '';
                var line3 = '';
                var isBattery = (response.type || '').toString().toLowerCase() === 'battery' && (response.volt || response.cca);
                var isOil = (response.type || '').toString().toLowerCase() === 'oil';
                if (isBattery) {
                    line1 = (response.battery_sequence || response.name || '').toString().replace(/</g, '&lt;').replace(/>/g, '&gt;').trim() || 'Item #' + (response.id || '');
                    var v = response.volt ? (response.volt.toString().indexOf('V') !== -1 ? response.volt : response.volt + 'V') : '';
                    var c = response.cca ? (response.cca.toString().indexOf('CCA') !== -1 ? response.cca : response.cca + 'CCA') : '';
                    line2 = [v, c].filter(Boolean).join(' • ');
                } else if (isOil) {
                    line1 = (response.oil_sequence || response.name || '').toString().replace(/</g, '&lt;').replace(/>/g, '&gt;').trim() || 'Item #' + (response.id || '');
                    var oilLine2Parts = [];
                    if (response.category_name) oilLine2Parts.push(response.category_name);
                    if (response.mileage_name) oilLine2Parts.push('MILEAGE: ' + response.mileage_name);
                    line2 = oilLine2Parts.join(' • ');
                    if (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can)) && parseFloat(response.liter_per_can) > 0) {
                        var lpc = parseFloat(response.liter_per_can);
                        line3 = 'Can = ' + (Number.isInteger(lpc) ? lpc : lpc.toFixed(1)) + ' L';
                    }
                } else if (['filters', 'parts', 'breakpad'].indexOf((response.type || '').toString().toLowerCase()) !== -1) {
                    var pnF = (response.part_number != null && String(response.part_number).trim() !== '') ? String(response.part_number).trim() : '';
                    var qF = (response.quality_name != null && String(response.quality_name).trim() !== '') ? String(response.quality_name).trim() : '';
                    var cF = (response.company_name != null && String(response.company_name).trim() !== '') ? String(response.company_name).trim() : '';
                    var typeLabF = (response.product_type_label != null && String(response.product_type_label).trim() !== '') ? String(response.product_type_label).trim() : '';
                    if (!typeLabF && response.category_name != null && String(response.category_name).trim() !== '' && !/^other$/i.test(String(response.category_name).trim())) {
                        typeLabF = String(response.category_name).trim();
                    }
                    var ptF = (response.product_title != null && String(response.product_title).trim() !== '') ? String(response.product_title).trim() : '';
                    var linePlainF = formatPurchasePartLineDisplay(pnF, typeLabF, qF, cF, ptF);
                    line1 = linePlainF ? ('<div class="selected-product-line1--segments fw-bold text-uppercase item-search-parts-headline" style="font-weight:700;">' + escapeHtml(linePlainF.toUpperCase()) + '</div>') : '';
                    if (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can)) && parseFloat(response.liter_per_can) > 0) {
                        var lpc = parseFloat(response.liter_per_can);
                        line2 = (Number.isInteger(lpc) ? lpc : lpc.toFixed(1)) + ' L per can';
                    }
                    var codeLine = '';
                    if (response.bar_code) {
                        codeLine = String(response.bar_code).trim();
                    }
                    if (codeLine) {
                        line3 = '<i class="ti ti-barcode me-1"></i><span class="text-uppercase fw-semibold">' + escapeHtml(codeLine.toUpperCase()) + '</span>';
                    }
                } else {
                    var detailsArr = [];
                    if (response.group_name) detailsArr.push(response.group_name);
                    if (response.type !== 'oil' && response.volt) detailsArr.push(response.volt + (response.volt.toString().indexOf('V') !== -1 ? '' : 'V'));
                    if (response.cca) detailsArr.push(response.cca + (response.cca.toString().indexOf('CCA') !== -1 ? '' : 'CCA'));
                    var detailsPart = detailsArr.length ? detailsArr.join(' • ') : '';
                    var vehiclePart = (response.vehicle_name || (response.manufacturer && response.model ? (response.manufacturer + ' ' + response.model) : '')) || '';
                    if (detailsPart && vehiclePart) line1 = detailsPart + ' • <span class="text-primary fw-semibold">' + vehiclePart + '</span>';
                    else if (vehiclePart) line1 = '<span class="text-primary fw-semibold">' + vehiclePart + '</span>';
                    else if (detailsPart) line1 = detailsPart;
                    else line1 = (response.name || '').trim();
                }
                if (!isOil && !line3) {
                    var codeLine2 = '';
                    if (response.bar_code) {
                        codeLine2 = String(response.bar_code).trim();
                    }
                    line3 = codeLine2 ? ('<i class="ti ti-barcode me-1"></i><span class="text-uppercase fw-semibold">' + escapeHtml(codeLine2.toUpperCase()) + '</span>') : '';
                }
                var qnPreview = (response.quality_name != null && String(response.quality_name).trim() !== '') ? String(response.quality_name).trim() : '';
                if (['filters', 'parts', 'breakpad'].indexOf((response.type || '').toString().toLowerCase()) !== -1) {
                    $('#selected-item-quality-wrap').html('').addClass('d-none');
                } else if (qnPreview) {
                    $('#selected-item-quality-wrap').html('<span class="product-quality-badge">' + escapeHtml(qnPreview) + '</span>').removeClass('d-none');
                } else {
                    $('#selected-item-quality-wrap').html('').addClass('d-none');
                }
                if (line1 || line2 || line3 || qnPreview) {
                    var isSegLine1Api = ['filters', 'parts', 'breakpad'].indexOf((response.type || '').toString().toLowerCase()) !== -1;
                    $('#selected-item-details-line1').html(line1 || '&nbsp;')
                        .toggleClass('battery-type-sequence fw-bold text-uppercase', !!(isBattery || isOil))
                        .toggleClass('selected-product-line1--segments', !!isSegLine1Api)
                        .toggleClass('small', !(isBattery || isOil || isSegLine1Api));
                    if (line2) {
                        $('#selected-item-details-line2').html(line2).css('display', '').removeClass('text-primary fw-semibold').addClass('text-muted small');
                    } else {
                        $('#selected-item-details-line2').html('').css('display', 'none');
                    }
                    if (isOil && line3) {
                        $('#selected-item-details-line3').html(line3).css('display', '').addClass('text-primary');
                    } else {
                        $('#selected-item-details-line3').html(line3 || '&nbsp;').css('display', line3 ? '' : 'none').removeClass('text-primary');
                    }
                    $('#selected-item-details-display').removeClass('d-none');
                } else {
                    $('#selected-item-details-display').addClass('d-none');
                }
                // Send Claim edit: search must match full product label (same as Selected Product card), not short table-only name (e.g. GL50).
                // If name_full was saved as short "GL50", prefer API response.name (full battery string with •). Never let short name_full override a full API label.
                if (editingRowId !== null && typeof currentEntryType !== 'undefined' && currentEntryType === 'claim_send') {
                    var eiClaim = purchaseItems.find(function(i) { return i.id === editingRowId; });
                    var stripClaim = function(s) {
                        if (!s) return '';
                        return (typeof stripHtml === 'function' ? stripHtml(String(s)) : String(s).replace(/<[^>]*>/g, '')).trim();
                    };
                    var apiN = stripClaim((response.name || '').toString());
                    var nf = (eiClaim && eiClaim.name_full) ? stripClaim(String(eiClaim.name_full)) : '';
                    var hasBullet = function(t) { return /[•·]/.test(t); };
                    var plClaim = '';
                    if (apiN && hasBullet(apiN)) {
                        plClaim = apiN;
                    } else if (nf && hasBullet(nf)) {
                        plClaim = nf;
                    } else if (apiN) {
                        plClaim = apiN;
                    } else if (nf) {
                        plClaim = nf;
                    } else {
                        plClaim = ($('#selected-item-details-line1').text() || '').replace(/\s+/g, ' ').trim();
                    }
                    if (plClaim) {
                        $('#item-search').val(plClaim);
                        if (eiClaim && (!eiClaim.name_full || (hasBullet(plClaim) && !hasBullet(stripClaim(String(eiClaim.name_full)))))) {
                            eiClaim.name_full = plClaim;
                        }
                    }
                }
                // Store base retail from API for restoring when % is cleared
                itemBaseRetailPrice = (response.retail_price != null && response.retail_price !== '' && !isNaN(parseFloat(response.retail_price))) ? parseFloat(response.retail_price) : null;
                syncSelectedItemMasterSaleFromDetailsResponse(response);
                syncSelectedItemCategoryFromDetailsResponse(response);
                // Set GST, R.Tax, Adjust by % from item so Sell price matches price list Amount at 0%
                if (response.tax_percentage != null && !isNaN(parseFloat(response.tax_percentage))) {
                    var gstVal = String(Math.round(parseFloat(response.tax_percentage)));
                    if ($('#item-tax-percent option[value="' + gstVal + '"]').length) $('#item-tax-percent').val(gstVal);
                }
                if (response.r_tax_percentage != null && response.r_tax_percentage !== '' && !isNaN(parseFloat(response.r_tax_percentage)))
                    $('#item-rtax-percent').val(parseFloat(response.r_tax_percentage));
                if (response.amount_adjustment_pct != null && response.amount_adjustment_pct !== '') {
                    var adjVal = String(Math.round(parseFloat(response.amount_adjustment_pct)));
                    if ($('#item-retail-percentage option[value="' + adjVal + '"]').length) $('#item-retail-percentage').val(adjVal);
                    else $('#item-retail-percentage').val('');
                } else {
                    $('#item-retail-percentage').val('');
                }
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
                
                // Set unit: when editing keep row's unit, else use item's unit.
                // But for claim/reverse flows the row unit may be a generic placeholder "Unit";
                // prefer API unit so battery shows "Piece".
                if (editingRowId !== null) {
                    var editItemForUnit = purchaseItems.find(function(i) { return i.id === editingRowId; });
                    var storedUnit = (editItemForUnit && editItemForUnit.unit != null) ? String(editItemForUnit.unit).trim() : '';
                    var storedUnitLower = storedUnit ? storedUnit.toLowerCase() : '';
                    if (storedUnit && storedUnitLower !== 'unit') {
                        $('#item-unit').val(storedUnit);
                    } else {
                        $('#item-unit').val(response.unit || 'Unit');
                    }
                } else {
                    $('#item-unit').val(response.unit || 'Unit');
                }
                
                // Oil only: show Per liter (Rs) below purchase rate; calculate from can rate ÷ liters per can; editable for liter-wise purchase
                var lpc = (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can)) && parseFloat(response.liter_per_can) > 0) ? parseFloat(response.liter_per_can) : null;
                if (isOil && lpc == null && (response.unit || '').toString().trim()) {
                    var unitStr = (response.unit || '').toString();
                    var lpcMatch = unitStr.match(/(\d+(?:\.\d+)?)\s*(?:liter|ltr|L)\b/i) || unitStr.match(/\b(?:liter|ltr|L)\s*(\d+(?:\.\d+)?)/i);
                    if (lpcMatch && parseFloat(lpcMatch[1]) > 0) lpc = parseFloat(lpcMatch[1]);
                }
                if (isOil && lpc != null && lpc > 0) {
                    $('#selected-item-is-oil').val('1');
                    $('#selected-item-liter-per-can').val(lpc);
                    $('#item-per-liter-wrap').removeClass('d-none');
                    var canRate = parseFloat($('#item-rate').val()) || 0;
                    if (editingRowId !== null) {
                        var editItemOil = purchaseItems.find(function(i) { return i.id === editingRowId; });
                        if (editItemOil && (editItemOil.rate_per_liter != null && editItemOil.rate_per_liter !== '' && !isNaN(parseFloat(editItemOil.rate_per_liter)))) {
                            $('#item-per-liter-rate').val(parseFloat(editItemOil.rate_per_liter));
                        } else {
                            $('#item-per-liter-rate').val(canRate > 0 ? (canRate / lpc).toFixed(2) : '');
                        }
                    } else {
                        $('#item-per-liter-rate').val(canRate > 0 ? (canRate / lpc).toFixed(2) : '');
                    }
                } else {
                    $('#selected-item-is-oil').val('');
                    $('#selected-item-liter-per-can').val('');
                    $('#item-per-liter-wrap').addClass('d-none');
                    $('#item-per-liter-rate').val('');
                }
                
                // Auto-select warehouse if available.
                // For claim/claim_send edit flows, keep existing selected warehouse from row to avoid source mismatch.
                var isClaimContextForWarehouse = (typeof currentEntryType !== 'undefined') && (currentEntryType === 'claim' || currentEntryType === 'claim_send');
                if (response.warehouse_id && !(editingRowId !== null && isClaimContextForWarehouse)) {
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
                    $('#selected-item-image').val(response.image || '');
                    $('#item-search-image-preview').removeClass('d-none');
                } else {
                    $('#item-search-image-preview').addClass('d-none');
                    $('#selected-item-image').val('');
                }
                $('#selected-item-images-json').val(JSON.stringify((response.images && Array.isArray(response.images)) ? response.images : []));
                
                // Stock will be set after loadItemStockStatus (correct per-warehouse value)
                $('#item-search-stock').html('<span class="text-muted small">...</span>');
                
                // Warranty section: only for Battery-type products; bind from item or row
                togglePurchaseItemWarrantySection(isBattery);
                if (isBattery) {
                    if (editingRowId !== null) {
                        // Row click already set warranty from row; keep it
                    } else if (response.warranty_value && response.warranty_unit) {
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
                            var pctNum = parseFloat(editItem.retail_pct);
                            var taxPct = parseFloat(String($('#item-tax-percent').val()).replace(/%/g, '')) || 18;
                            var rtaxPct = parseFloat($('#item-rtax-percent').val()) || 0.5;
                            var factor0 = (1 + taxPct / 100) * (1 + rtaxPct / 100);
                            if (pctNum === 0 || (editItem.retail_pct + '').trim() === '') {
                                retailToShow = Math.round(parseFloat(editItem.retail_price) / factor0);
                            } else {
                                retailToShow = Math.round(parseFloat(editItem.retail_price) / (factor0 - pctNum / 100));
                            }
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
    let purchaseHistoryCache = {}; // itemId -> ajax response (includes history[])
    let purchaseHistoryExpanded = {}; // itemId -> boolean
    let purchaseHistoryLoading = {}; // itemId -> boolean
    let pendingPurchaseHistoryExpand = {}; // itemId -> boolean (used when cache not loaded yet)

    // Used by "View Bill" buttons (each row opens its exact purchase bill)
    const purchaseShowUrlTemplate = '{{ route("purchases.show", ":id") }}';
    const PURCHASE_HISTORY_COLLAPSED_COUNT = 5;

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDaysAgo(daysAgo) {
        if (daysAgo === null || daysAgo === undefined || daysAgo === '') return '—';
        const n = parseInt(daysAgo, 10);
        if (isNaN(n)) return '—';
        if (n === 0) return 'Today';
        if (n === 1) return '1 day ago';
        return n + ' days ago';
    }

    function renderPurchaseHistory(itemId, data) {
        const expanded = purchaseHistoryExpanded[itemId] === true;
        const history = (data && Array.isArray(data.history)) ? data.history : [];

        // Keep UX: compact max-height initially, larger on expand.
        $('#customer-history-content').css('max-height', expanded ? '520px' : '160px');

        const isOil = ($('#selected-item-is-oil').val() || '').toString() === '1';
        const literPerCan = parseFloat($('#selected-item-liter-per-can').val()) || 0;
        const showOilPrices = isOil && literPerCan > 0;

        const summaryHtml = `
            <div class="purchase-history-summary mb-2 pb-2" style="border-bottom: 1px solid #e0e0e0;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-dark">Total Purchases: ${data.total_purchases}</span>
                    <span class="badge bg-primary">
                        ${data.total_quantity} ${data.last_purchase ? escapeHtml(data.last_purchase.unit) : 'Units'}
                    </span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span><i class="ti ti-trending-down text-success me-1"></i>Min: Rs ${parseFloat(data.min_rate).toLocaleString()}</span>
                    <span><i class="ti ti-chart-line text-primary me-1"></i>Avg: Rs ${parseFloat(data.avg_rate).toLocaleString()}</span>
                    <span><i class="ti ti-trending-up text-danger me-1"></i>Max: Rs ${parseFloat(data.max_rate).toLocaleString()}</span>
                </div>
            </div>
        `;

        const slice = expanded ? history : history.slice(0, PURCHASE_HISTORY_COLLAPSED_COUNT);

        let listHtml = `<div class="purchase-history-list small">`;
        slice.forEach(function(purchase) {
            const rateNum = parseFloat(purchase.rate) || 0;
            const totalCostNum = (purchase.total_cost === null || purchase.total_cost === undefined) ? null : parseFloat(purchase.total_cost);
            const totalCostText = (totalCostNum === null || isNaN(totalCostNum)) ? '—' : 'Rs ' + totalCostNum.toLocaleString();

            const daysAgoText = formatDaysAgo(purchase.days_ago);
            const dateTimeText = (purchase.created_at || purchase.purchase_date || '—');

            const branchName = (purchase.branch_name || '').toString().trim() || '—';
            const userName = (purchase.user_name || '').toString().trim() || '—';

            const supplierName = (purchase.supplier_name || '').toString().trim() || '—';
            const qtyText = (purchase.quantity != null && purchase.quantity !== '') ? String(purchase.quantity) : '0';
            const unitText = (purchase.unit || 'Unit');

            let priceHtml = '';
            if (showOilPrices) {
                const perLiterRate = (literPerCan > 0) ? (rateNum / literPerCan) : 0;
                const perLiterRounded = (Math.round(perLiterRate * 100) / 100);
                priceHtml =
                    `<div class="fw-bold text-primary">Rs ${rateNum.toLocaleString()} per can</div>` +
                    `<div class="text-secondary" style="font-size: 0.8rem;">Rs ${perLiterRounded.toLocaleString()} per liter</div>`;
            } else {
                priceHtml = `<div class="fw-bold text-primary">Rs ${rateNum.toLocaleString()}</div>`;
            }

            const invoiceNo = purchase.invoice_no ? purchase.invoice_no : 'N/A';
            const purchaseId = purchase.purchase_id;
            const viewDisabledAttr = purchaseId ? '' : 'disabled';
            const viewBtnHtml = `
                <button type="button" class="btn btn-sm btn-outline-primary purchase-history-view-bill"
                    ${viewDisabledAttr} data-purchase-id="${escapeHtml(purchaseId)}">
                    <i class="ti ti-eye me-1"></i>View Bill
                </button>
            `;

            listHtml += `
                <div class="purchase-history-item d-block" data-rate="${purchase.rate}" style="
                    border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; margin-bottom: 8px;
                    cursor: pointer; background: #fff;">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-semibold text-dark">
                                ${escapeHtml(supplierName)}
                                <span class="text-muted fw-normal">(${escapeHtml(qtyText)} ${escapeHtml(unitText)})</span>
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">
                                ${expanded ? ('Invoice: <span class="fw-medium">' + escapeHtml(invoiceNo) + '</span><br/>') : ''}
                                Date: ${escapeHtml(dateTimeText)}<br/>
                                ${expanded ? ('Branch: ' + escapeHtml(branchName) + '<br/>') : ('Branch: ' + escapeHtml(branchName) + '<br/>')}
                                User: ${escapeHtml(userName)}
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="text-muted small mb-1">${escapeHtml(daysAgoText)}</div>
                            ${viewBtnHtml}
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-end mt-2 gap-2">
                        <div class="text-start">
                            ${priceHtml}
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Total: ${escapeHtml(totalCostText)}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        listHtml += `</div>`;

        let toggleHtml = '';
        if (!expanded && history.length > PURCHASE_HISTORY_COLLAPSED_COUNT) {
            toggleHtml = `
                <div class="text-center mt-1">
                    <a href="javascript:void(0)" class="btn btn-sm btn-link p-0 purchase-history-more"
                        data-item-id="${escapeHtml(itemId)}" style="font-size: 12px;">
                        + ${history.length - PURCHASE_HISTORY_COLLAPSED_COUNT} more purchases
                    </a>
                </div>
            `;
        } else if (expanded && history.length > PURCHASE_HISTORY_COLLAPSED_COUNT) {
            toggleHtml = `
                <div class="text-center mt-1">
                    <a href="javascript:void(0)" class="btn btn-sm btn-link p-0 text-muted purchase-history-collapse"
                        data-item-id="${escapeHtml(itemId)}" style="font-size: 12px;">
                        Show less
                    </a>
                </div>
            `;
        }

        return summaryHtml + listHtml + toggleHtml;
    }

    function loadCustomerHistory(itemId) {
        $('#customer-history-content').html(`
            <div class="text-center py-2">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                <span class="text-muted small">Loading purchase history...</span>
            </div>
        `);
        $('#hold-rate-link').hide();

        // Reset UI state on each item load unless user explicitly expanded it.
        const shouldExpandOnLoad = pendingPurchaseHistoryExpand[itemId] === true;
        delete pendingPurchaseHistoryExpand[itemId];
        purchaseHistoryExpanded[itemId] = shouldExpandOnLoad;
        purchaseHistoryLoading[itemId] = true;

        $.ajax({
            url: '{{ route("purchases.items.purchase.history", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(data) {
                purchaseHistoryLoading[itemId] = false;
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

                // Cache and render (collapsed or expanded based on click).
                purchaseHistoryCache[itemId] = data;
                $('#customer-history-content').html(renderPurchaseHistory(itemId, data));
                $('#hold-rate-link').show();
            },
            error: function(xhr) {
                purchaseHistoryLoading[itemId] = false;
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
    
    // Expand / Collapse purchase history (render remaining entries without duplicate loading)
    $(document).on('click', '.purchase-history-more', function(e) {
        e.preventDefault();
        const itemId = $(this).data('item-id');
        if (!itemId) return;
        if (purchaseHistoryExpanded[itemId] === true) return;

        purchaseHistoryExpanded[itemId] = true;
        if (purchaseHistoryCache[itemId] && purchaseHistoryCache[itemId].history && purchaseHistoryCache[itemId].history.length) {
            $('#customer-history-content').html(renderPurchaseHistory(itemId, purchaseHistoryCache[itemId]));
            return;
        }

        // Not loaded yet: fetch via AJAX, then auto-expand once loaded.
        pendingPurchaseHistoryExpand[itemId] = true;
        if (!purchaseHistoryLoading[itemId]) loadCustomerHistory(itemId);
    });

    $(document).on('click', '.purchase-history-collapse', function(e) {
        e.preventDefault();
        const itemId = $(this).data('item-id');
        if (!itemId) return;
        purchaseHistoryExpanded[itemId] = false;

        if (purchaseHistoryCache[itemId] && purchaseHistoryCache[itemId].history && purchaseHistoryCache[itemId].history.length) {
            $('#customer-history-content').html(renderPurchaseHistory(itemId, purchaseHistoryCache[itemId]));
        } else {
            // If cache missing for some reason, just reload collapsed.
            pendingPurchaseHistoryExpand[itemId] = false;
            if (!purchaseHistoryLoading[itemId]) loadCustomerHistory(itemId);
        }
    });

    // View Bill per purchase entry
    function buildPurchaseReturnUrlWithReopenModal() {
        try {
            var u = new URL(window.location.href);
            u.searchParams.set('reopen_add_item_modal', '1');
            u.hash = '';
            return u.toString();
        } catch (err) {
            var base = String(window.location.href.split('#')[0] || '');
            var sep = base.indexOf('?') === -1 ? '?' : '&';
            return base + sep + 'reopen_add_item_modal=1';
        }
    }

    $(document).on('click', '.purchase-history-view-bill', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const purchaseId = $(this).data('purchase-id');
        if (!purchaseId) return;

        try {
            sessionStorage.setItem('purchaseModalBillReturn', JSON.stringify({
                selectedItemId: ($('#selected-item-id').val() || '').toString(),
                itemSearch: ($('#item-search').val() || '').toString(),
                editingRowId: (editingRowId !== null && editingRowId !== undefined) ? editingRowId : null
            }));
        } catch (err) { /* ignore */ }

        let url = purchaseShowUrlTemplate.replace(':id', purchaseId);
        const sep = url.indexOf('?') === -1 ? '?' : '&';
        const returnUrl = encodeURIComponent(buildPurchaseReturnUrlWithReopenModal());
        url += sep + 'return_url=' + returnUrl;
        window.open(url, '_blank');
    });

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
    // Must match submitItemEntry(): when "Adjust by %" is set, purchase rate comes from #item-retail-after-calc and GST from #item-tax-percent;
    // hidden #item-tax (additional-fields) is not used in that path — previously caused modal Rs 980 vs cart Rs 1156.
    function updateItemLineTotal() {
        var quantity = parseFloat($('#item-quantity').val()) || 0;
        if ($('#item-quantity-input').is(':visible') && $('#item-quantity-input').val()) quantity = parseFloat($('#item-quantity-input').val()) || 0;
        quantity = Math.max(0, quantity);
        var pctValLine = ($('#item-retail-percentage').val() || '').toString().trim();
        var hasPctLine = pctValLine !== '';
        var rate;
        if (hasPctLine) {
            var calcTextLine = ($('#item-retail-after-calc').text() || '').replace(/\s+/g, ' ').trim();
            rate = parseFloat(calcTextLine.replace(/^Rs\s*/, '').replace(/,/g, '')) || 0;
        } else {
            rate = parseFloat($('#item-rate').val()) || 0;
        }
        var discount = parseFloat($('#item-discount').val()) || 0;
        var discountType = $('#discount-type').val() || 'amount';
        var gstPctLine = parseFloat(String($('#item-tax-percent').val()).replace(/%/g, '')) || 18;
        var taxPct = hasPctLine ? gstPctLine : (parseFloat($('#item-tax').val()) || 0);
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

    // Update total row below warehouse list (selected sum) and available total row (stock sum)
    function updateStockStatusListTotal() {
        var selectedTotal = 0;
        var availableCans = 0;
        var availableLiters = 0;
        var unit = 'Piece';
        var hasLiters = false;
        $('#stock-status-list .stock-warehouse-item').each(function() {
            var $row = $(this);
            var $sel = $row.find('select.stock-warehouse-qty-input');
            if ($sel.length) {
                var v = $sel.val();
                selectedTotal += (v === '' || v === null || v === undefined) ? 0 : (parseFloat(v) || 0);
            }
            var qty = parseFloat($row.data('quantity')) || 0;
            availableCans += qty;
            var mult = parseFloat($row.data('base-unit-multiplier')) || 0;
            if (mult > 0) {
                availableLiters += qty * mult;
                hasLiters = true;
            }
            if ($row.data('unit')) unit = ($row.data('unit') || 'Piece').trim();
        });
        var $first = $('#stock-status-list .stock-warehouse-item').first();
        if ($first.length) unit = ($first.data('unit') || 'Piece').trim();
        var selectedText = (Number.isInteger(selectedTotal) ? selectedTotal : selectedTotal.toFixed(2)) + ' ' + unit;
        var $totalRow = $('#stock-status-list-total');
        var $totalText = $('#stock-status-list-total-text');
        var availableText = (Number.isInteger(availableCans) ? availableCans : availableCans.toFixed(2)) + ' ' + unit;
        if (hasLiters && availableLiters > 0) {
            availableText += ' (' + (Number.isInteger(availableLiters) ? availableLiters : availableLiters.toFixed(2)) + ' L total)';
        }
        var $availRow = $('#stock-status-list-available-total');
        var $availText = $('#stock-status-list-available-total-text');
        if ($('#stock-status-list .stock-warehouse-item').length) {
            $totalRow.show();
            $totalText.text(selectedText);
            $availRow.show();
            $availText.text(availableText);
        } else {
            $totalRow.hide();
            $availRow.hide();
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
        if (typeof updateStockStatusListTotal === 'function') updateStockStatusListTotal();
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
    function getWarehouseGroupKey(itemLike) {
        if (!itemLike || typeof itemLike !== 'object') return 'name:';
        var wid = (itemLike.warehouse_id != null ? String(itemLike.warehouse_id) : '').trim();
        if (wid !== '') return 'id:' + wid;
        var wname = (itemLike.warehouse_name != null ? String(itemLike.warehouse_name) : '').trim().toLowerCase();
        return 'name:' + wname;
    }

    function shouldMergePurchaseLines(existingItem, newItem) {
        if (!existingItem || !newItem) return false;
        if (String(existingItem.entry_type || 'purchase') !== 'purchase') return false;
        if (String(newItem.entry_type || 'purchase') !== 'purchase') return false;
        var whKeyExisting = getWarehouseGroupKey(existingItem);
        var whKeyIncoming = getWarehouseGroupKey(newItem);
        var whNameExisting = String(existingItem.warehouse_name || '').trim().toLowerCase();
        var whNameIncoming = String(newItem.warehouse_name || '').trim().toLowerCase();
        var sameWarehouse = (whKeyExisting === whKeyIncoming) ||
                            (whNameExisting !== '' && whNameIncoming !== '' && whNameExisting === whNameIncoming);
        if (!sameWarehouse) return false;

        // If backend identifies same item_id in same warehouse, always merge quantity.
        var sameItemId = String(existingItem.item_id || '') !== '' &&
                         String(existingItem.item_id || '') === String(newItem.item_id || '');
        if (sameItemId) return true;

        var existingBarcode = String(existingItem.bar_code || '').trim();
        var incomingBarcode = String(newItem.bar_code || '').trim();

        // Barcode scanner priority (fallback when item_id not matching): merge strictly by barcode.
        if (existingBarcode !== '' || incomingBarcode !== '') {
            if (existingBarcode === '' || incomingBarcode === '') return false;
            return existingBarcode === incomingBarcode;
        }

        // Final safe fallback for manual/scan hybrid rows: same visible product + unit + rate in same warehouse.
        var cleanName = function(v) {
            return String(v || '')
                .replace(/<[^>]*>/g, '')
                .replace(/\s+/g, ' ')
                .trim()
                .toLowerCase();
        };
        var sameName = cleanName(existingItem.name) !== '' && cleanName(existingItem.name) === cleanName(newItem.name);
        var sameUnit = String(existingItem.unit || '').trim().toLowerCase() === String(newItem.unit || '').trim().toLowerCase();
        var sameRate = Math.abs((parseFloat(existingItem.rate) || 0) - (parseFloat(newItem.rate) || 0)) < 0.0001;
        return sameName && sameUnit && sameRate;
    }

    function mergeOrPushPurchaseLine(newItem) {
        if (!newItem || String(newItem.entry_type || 'purchase') !== 'purchase') {
            purchaseItems.push(newItem);
            return newItem ? newItem.id : null;
        }

        var existingIndex = purchaseItems.findIndex(function(it) {
            return shouldMergePurchaseLines(it, newItem);
        });

        if (existingIndex < 0) {
            purchaseItems.push(newItem);
            return newItem.id;
        }

        var existing = purchaseItems[existingIndex];
        var prevQty = parseFloat(existing.quantity) || 0;
        var addQty = parseFloat(newItem.quantity) || 0;
        var mergedQty = prevQty + addQty;
        existing.quantity = mergedQty;

        var mergedRate = parseFloat(existing.rate) || parseFloat(newItem.rate) || 0;
        existing.rate = mergedRate;
        var mergedDiscount = parseFloat(existing.discount) || 0;
        var mergedTaxPct = parseFloat(existing.tax_percentage) || 0;
        var mergedSubtotal = (mergedQty * mergedRate) - mergedDiscount;
        var mergedTaxAmount = (mergedSubtotal * mergedTaxPct) / 100;
        existing.tax_amount = mergedTaxAmount;
        existing.total = mergedSubtotal + mergedTaxAmount;

        existing.warehouse_name = existing.warehouse_name || newItem.warehouse_name || '';
        existing.bar_code = existing.bar_code || newItem.bar_code || null;
        if (!existing.image && newItem.image) existing.image = newItem.image;
        if (!existing.voice_url && newItem.voice_url) existing.voice_url = newItem.voice_url;
        if ((!existing.images || !existing.images.length) && newItem.images && newItem.images.length) existing.images = newItem.images.slice();
        if (newItem.retail_price != null && existing.retail_price == null) existing.retail_price = newItem.retail_price;
        if (newItem.retail_price_base != null && existing.retail_price_base == null) existing.retail_price_base = newItem.retail_price_base;
        if (newItem.sale_price != null && existing.sale_price == null) existing.sale_price = newItem.sale_price;
        if (newItem.item_master_sale_price != null && existing.item_master_sale_price == null) existing.item_master_sale_price = newItem.item_master_sale_price;
        if (newItem.item_master_retail_price != null && existing.item_master_retail_price == null) existing.item_master_retail_price = newItem.item_master_retail_price;
        if (newItem.total_sale_price != null && existing.total_sale_price == null) existing.total_sale_price = newItem.total_sale_price;
        if (newItem.sale_price_per_base != null && existing.sale_price_per_base == null) existing.sale_price_per_base = newItem.sale_price_per_base;
        if (newItem.category_name != null && String(newItem.category_name).trim() !== '' && (existing.category_name == null || String(existing.category_name).trim() === '')) {
            existing.category_name = String(newItem.category_name).trim();
        }
        if (newItem.item_type && (!existing.item_type || String(existing.item_type).trim() === '')) {
            existing.item_type = String(newItem.item_type).trim().toLowerCase();
        }
        if (newItem.quality_name != null && String(newItem.quality_name).trim() !== '' && (!existing.quality_name || String(existing.quality_name).trim() === '')) {
            existing.quality_name = String(newItem.quality_name).trim();
        }
        if (newItem.technology_name != null && String(newItem.technology_name).trim() !== '' && (!existing.technology_name || String(existing.technology_name).trim() === '')) {
            existing.technology_name = String(newItem.technology_name).trim();
        }
        if (newItem.part_number != null && String(newItem.part_number).trim() !== '' && (!existing.part_number || String(existing.part_number).trim() === '')) {
            existing.part_number = String(newItem.part_number).trim();
        }
        if (newItem.product_title != null && String(newItem.product_title).trim() !== '' && (!existing.product_title || String(existing.product_title).trim() === '')) {
            existing.product_title = String(newItem.product_title).trim();
        }
        if (newItem.product_type_label != null && String(newItem.product_type_label).trim() !== '' && (!existing.product_type_label || String(existing.product_type_label).trim() === '')) {
            existing.product_type_label = String(newItem.product_type_label).trim();
        }
        if (newItem.demand_user_name != null && String(newItem.demand_user_name).trim() !== '' && (!existing.demand_user_name || String(existing.demand_user_name).trim() === '')) {
            existing.demand_user_name = String(newItem.demand_user_name).trim();
        }

        if (newItem.liter_per_can != null && !isNaN(parseFloat(newItem.liter_per_can))) {
            var lpc = parseFloat(newItem.liter_per_can) || 0;
            if (lpc > 0) {
                var prevBaseQty = parseFloat(existing.quantity_base) || 0;
                existing.quantity_base = prevBaseQty + (addQty * lpc);
                existing.base_unit = existing.base_unit || 'Liter';
                existing.liter_per_can = existing.liter_per_can || lpc;
                existing.rate_per_liter = mergedRate / lpc;
            }
        }
        if (!existing.company_name && newItem.company_name) existing.company_name = newItem.company_name;

        return existing.id;
    }

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
        var hidMasterSale = parseFloat(String($('#selected-item-master-sale-price').val() || '').trim());
        var masterSaleForLine = (!isNaN(hidMasterSale) && hidMasterSale > 0) ? hidMasterSale : null;
        var masterRetailForLine = (typeof itemBaseRetailPrice === 'number' && !isNaN(itemBaseRetailPrice) && itemBaseRetailPrice > 0) ? itemBaseRetailPrice : null;
        var categoryLineRaw = ($('#selected-item-category-name').val() || '').toString().trim();
        var categoryLineName = (categoryLineRaw && !/^other$/i.test(categoryLineRaw)) ? categoryLineRaw : null;
        const discount = parseFloat($('#item-discount').val()) || 0;
        const discountType = $('#discount-type').val();
        const gstPct = parseFloat(String($('#item-tax-percent').val()).replace(/%/g, '')) || 18;
        const taxPercentage = hasPct ? gstPct : (parseFloat($('#item-tax').val()) || 0);
        const rawItemName = $('#item-search').val();
        const selectedItemCompany = ($('#selected-item-company').val() || '').toString().trim();
        const submitItemType = ($('#selected-item-type').val() || '').toString().toLowerCase();
        let itemName;
        if (submitItemType === 'parts' || submitItemType === 'filters' || submitItemType === 'breakpad') {
            var ptnS = ($('#selected-item-part-number').val() || '').toString().trim();
            var pttS = ($('#selected-item-product-title').val() || '').toString().trim();
            var typeLabS = ($('#selected-item-product-type-label').val() || '').toString().trim();
            if (!typeLabS && categoryLineName) typeLabS = String(categoryLineName).trim();
            if (/^other$/i.test(typeLabS)) typeLabS = '';
            var qnS = ($('#selected-item-quality-name').val() || '').toString().trim();
            itemName = formatPurchasePartLineDisplay(ptnS, typeLabS, qnS, selectedItemCompany, pttS);
            if (!itemName) {
                itemName = cleanItemName(rawItemName, itemId);
                if (selectedItemCompany && itemName && itemName.toLowerCase().indexOf(selectedItemCompany.toLowerCase()) === -1) {
                    itemName = itemName + ' • ' + selectedItemCompany;
                }
            }
        } else {
            itemName = cleanItemName(rawItemName, itemId);
            if (selectedItemCompany && itemName && itemName.toLowerCase().indexOf(selectedItemCompany.toLowerCase()) === -1) {
                itemName = itemName + ' • ' + selectedItemCompany;
            }
        }
        const warrantyValue = $('#warranty-value').val();
        const warrantyUnit = $('#warranty-unit').val();
        const claimSendStatusNote = (window._purchaseClaimSendStatusNote || ($('#purchase-claim-send-note').val() || '')).toString().trim();
        var selectedVoiceUrl = ($('#selected-item-voice-url').val() || '').toString().trim() || null;
        var selectedImagesArr = parseSelectedItemImagesJson();
        var isOilItem = ($('#selected-item-is-oil').val() || '').toString() === '1';
        var literPerCan = parseFloat($('#selected-item-liter-per-can').val()) || 0;
        var ratePerLiter = null;
        if (isOilItem && literPerCan > 0) {
            var plVal = ($('#item-per-liter-rate').val() || '').toString().trim();
            ratePerLiter = (plVal !== '' && !isNaN(parseFloat(plVal))) ? parseFloat(plVal) : null;
        }
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

        // Fallback: single warehouse from Save to warehouse / selected-warehouse-id (check before showing error so user can use dropdown + main quantity)
        // Skip warehouse requirement for RETURN and SCRAP; only PURCHASE (and claim/damage etc.) require warehouse + quantity
        if (warehouseLines.length === 0 && currentEntryType !== 'scrap' && currentEntryType !== 'return') {
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
        if (warehouseLines.length === 0 && currentEntryType === 'return') {
            var qtyReturn = parseFloat($('#item-quantity').val()) || 0;
            if (qtyReturn <= 0) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({ icon: 'warning', title: 'Quantity required', text: 'Please enter return quantity.' });
                } else {
                    alert('Please enter return quantity.');
                }
                return;
            }
            warehouseLines = [{ warehouse_id: null, warehouse_name: null, quantity: qtyReturn }];
        }

        if (editingRowId !== null) {
            // Edit mode: apply new quantity (and rate, retail) from modal – confirm karte hi cart/inventory view mein nayi quantity dikhe
            var editedRow = purchaseItems.find(function(i) { return i.id === editingRowId; });
            if (!editedRow) { editingRowId = null; return; }
            var sameItemId = String(editedRow.item_id);
            var editedEntryType = String(editedRow.entry_type || 'purchase');
            // Preserve PO line link so backend can update received quantity when saving the bill
            var poItemId = (editedRow.purchase_order_item_id != null && editedRow.purchase_order_item_id !== '') ? editedRow.purchase_order_item_id : null;
            // Purchase: replace all PURCHASE lines for this item_id (multi-warehouse split). Never remove return/claim/scrap rows with same item_id.
            // Non-purchase: replace only the edited row (unique cart line id).
            if (editedEntryType === 'purchase') {
                purchaseItems = purchaseItems.filter(function(it) {
                    if (String(it.item_id) !== sameItemId) return true;
                    return String(it.entry_type || 'purchase') !== 'purchase';
                });
            } else {
                purchaseItems = purchaseItems.filter(function(it) { return it.id !== editingRowId; });
            }
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
                    company_name: (editedRow.company_name || selectedItemCompany || null),
                    warehouse_id: wl.warehouse_id || null,
                    warehouse_name: wl.warehouse_name || null,
                    quantity: quantity,
                    quantity_base: wl.quantity_base != null ? wl.quantity_base : null,
                    base_unit: (wl.base_unit || '').trim() || null,
                    unit: unit,
                    rate: rate,
                    sale_price: masterSaleForLine,
                    item_master_sale_price: masterSaleForLine,
                    item_master_retail_price: masterRetailForLine,
                    retail_price: retailPrice,
                    retail_price_base: retailPriceBase,
                    retail_pct: selectedRetailPct,
                    discount: discountAmount,
                    tax_percentage: taxPercentage,
                    tax_amount: taxAmount,
                    total: total,
                    warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : (editedRow.warranty || null),
                    entry_type: currentEntryType || editedRow.entry_type || 'purchase',
                    name_full: (currentEntryType || editedRow.entry_type || '') === 'claim_send' ? itemName : (editedRow.name_full || null),
                    image: editedRow.image || null,
                    images: (selectedImagesArr.length ? selectedImagesArr.slice() : (editedRow.images && editedRow.images.length ? editedRow.images.slice() : null)),
                    voice_url: selectedVoiceUrl || ((editedRow.voice_url && String(editedRow.voice_url).trim()) ? String(editedRow.voice_url).trim() : null),
                    is_temporary: !!(editedRow.is_temporary),
                    bar_code: (editedRow.bar_code != null && String(editedRow.bar_code).trim() !== '') ? String(editedRow.bar_code).trim() : null,
                    category_name: categoryLineName || (editedRow.category_name != null && String(editedRow.category_name).trim() !== '' ? String(editedRow.category_name).trim() : null),
                    item_type: ($('#selected-item-type').val() || '').toString().trim().toLowerCase() || (editedRow.item_type || null),
                    quality_name: ($('#selected-item-quality-name').val() || '').toString().trim() || (editedRow.quality_name || null),
                    part_number: ($('#selected-item-part-number').val() || '').toString().trim() || (editedRow.part_number != null ? String(editedRow.part_number).trim() : null),
                    product_title: ($('#selected-item-product-title').val() || '').toString().trim() || (editedRow.product_title != null ? String(editedRow.product_title).trim() : null),
                    product_type_label: ($('#selected-item-product-type-label').val() || '').toString().trim() || (editedRow.product_type_label != null ? String(editedRow.product_type_label).trim() : null),
                    technology_name: ($('#selected-item-technology-name').val() || '').toString().trim() || (editedRow.technology_name || null),
                    demand_user_name: (editedRow.demand_user_name != null && String(editedRow.demand_user_name).trim() !== '') ? String(editedRow.demand_user_name).trim() : getDemandUserNameForNewCartLine()
                };
                if ((currentEntryType || editedRow.entry_type || 'purchase') === 'claim_send' && claimSendStatusNote !== '') {
                    newItem.status_note = claimSendStatusNote;
                }
                if (isOilItem && literPerCan > 0) {
                    newItem.liter_per_can = literPerCan;
                    newItem.rate_per_liter = ratePerLiter != null ? ratePerLiter : (editedRow.rate_per_liter != null ? editedRow.rate_per_liter : null);
                }
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
                company_name: (selectedItemCompany || null),
                warehouse_id: wl.warehouse_id || null,
                warehouse_name: wl.warehouse_name || null,
                quantity: quantity,
                quantity_base: wl.quantity_base != null ? wl.quantity_base : null,
                base_unit: (wl.base_unit || '').trim() || null,
                unit: unit,
                rate: rate,
                sale_price: masterSaleForLine,
                item_master_sale_price: masterSaleForLine,
                item_master_retail_price: masterRetailForLine,
                retail_price: retailPrice,
                retail_price_base: retailPriceBase,
                retail_pct: selectedRetailPct,
                discount: discountAmount,
                tax_percentage: taxPercentage,
                tax_amount: taxAmount,
                total: total,
                warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : null,
                entry_type: currentEntryType || 'purchase',
                name_full: (currentEntryType === 'claim_send') ? itemName : undefined,
                image: (($('#selected-item-image').val() || '').trim()) || null,
                images: selectedImagesArr.length ? selectedImagesArr.slice() : null,
                voice_url: selectedVoiceUrl,
                is_temporary: ($('#selected-item-is-temporary').val() === '1' || $('#selected-item-is-temporary').val() === 'true'),
                bar_code: (($('#selected-item-bar-code').val() || '').toString().trim()) || null,
                category_name: categoryLineName,
                item_type: ($('#selected-item-type').val() || '').toString().trim().toLowerCase() || null,
                quality_name: ($('#selected-item-quality-name').val() || '').toString().trim() || null,
                part_number: ($('#selected-item-part-number').val() || '').toString().trim() || null,
                product_title: ($('#selected-item-product-title').val() || '').toString().trim() || null,
                product_type_label: ($('#selected-item-product-type-label').val() || '').toString().trim() || null,
                technology_name: ($('#selected-item-technology-name').val() || '').toString().trim() || null,
                demand_user_name: getDemandUserNameForNewCartLine()
            };
            if ((currentEntryType || 'purchase') === 'claim_send' && claimSendStatusNote !== '') {
                item.status_note = claimSendStatusNote;
            }
            if (isOilItem && literPerCan > 0) {
                item.liter_per_can = literPerCan;
                item.rate_per_liter = ratePerLiter;
            }
            mergeOrPushPurchaseLine(item);
            if ((currentEntryType || 'purchase') === 'claim_send') {
                var nowDt = formatClaimHistoryDateTime(Math.floor(Date.now() / 1000));
                window._purchaseClaimSentRecords = window._purchaseClaimSentRecords || [];
                window._purchaseClaimSentRecords.unshift({
                    item_id: itemId,
                    item_name: itemName,
                    warehouse_id: wl.warehouse_id || null,
                    warehouse_name: wl.warehouse_name || null,
                    quantity: quantity,
                    claim_id: 'CLM-SEND-' + nowDt.ts + '-' + String(itemId),
                    date: nowDt.date,
                    time: nowDt.time,
                    datetime_sort: nowDt.ts,
                    status_note: claimSendStatusNote || 'Sent from claim stock.',
                    customer_name: ($('#supplier_id option:selected').text() || '').toString().trim() || null,
                    entry_type: 'claim_send'
                });
            }
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
        if ((currentEntryType || 'purchase') === 'claim_send') {
            updatePurchaseClaimHistoryCountsAndTrendForCurrentFilters();
            if ($('#purchase-claim-history-modal').hasClass('show')) renderPurchaseClaimHistoryTab();
        }
        window._purchaseClaimSendStatusNote = '';
    }

    $('#confirm-entry').on('click', function() {
        submitItemEntry(true);
    });

    $('#save-and-new-entry').on('click', function() {
        submitItemEntry(false);
    });

    // Order: purchase first, then scrap, return, claim_send, damage
    function sortPurchaseItemsByEntryType() {
        const order = { 'purchase': 0, 'claim': 1, 'scrap': 2, 'return': 3, 'claim_send': 4, 'damage': 5 };
        const prevIndexByRowId = {};
        const warehouseOrder = {};
        let warehouseCursor = 0;

        // Capture current visual order BEFORE sorting, so scan updates don't reshuffle groups.
        purchaseItems.forEach(function(it, idx) {
            prevIndexByRowId[String(it.id)] = idx;
            var key = getWarehouseGroupKey(it);
            if (!Object.prototype.hasOwnProperty.call(warehouseOrder, key)) {
                warehouseOrder[key] = warehouseCursor++;
            }
        });

        purchaseItems.sort(function(a, b) {
            const aOrd = order[a.entry_type || 'purchase'] ?? 0;
            const bOrd = order[b.entry_type || 'purchase'] ?? 0;
            if (aOrd !== bOrd) return aOrd - bOrd;

            const aWhOrd = warehouseOrder[getWarehouseGroupKey(a)] ?? Number.MAX_SAFE_INTEGER;
            const bWhOrd = warehouseOrder[getWarehouseGroupKey(b)] ?? Number.MAX_SAFE_INTEGER;
            if (aWhOrd !== bWhOrd) return aWhOrd - bWhOrd;

            const aPrev = prevIndexByRowId[String(a.id)] ?? Number.MAX_SAFE_INTEGER;
            const bPrev = prevIndexByRowId[String(b.id)] ?? Number.MAX_SAFE_INTEGER;
            return aPrev - bPrev;
        });
    }

    function escapePurchaseSummaryText(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    /** Item summary second line: qty · unit · rate; when line GST applies, show % and amount so TOTAL column matches. */
    function buildPurchaseRowQtyRateText(item) {
        var qty = parseFloat(item.quantity) || 0;
        var rate = parseFloat(item.rate) || 0;
        var qtyStr = (Number.isInteger(qty) ? String(qty) : qty.toFixed(2));
        var rateStr = (rate % 1 === 0 ? String(Math.round(rate)) : parseFloat(rate).toFixed(2));
        var unitEsc = escapePurchaseSummaryText(item.unit || 'Unit');
        var packEsc = escapePurchaseSummaryText(item.unit || 'Can');
        var parts;
        if (item.quantity_base != null && item.base_unit) {
            var qb = item.quantity_base;
            var qbStr = Number.isInteger(qb) ? String(qb) : parseFloat(qb).toFixed(2);
            parts = qtyStr + ' ' + packEsc + ' · ' + qbStr + ' ' + escapePurchaseSummaryText(item.base_unit) + ' · RS ' + rateStr;
        } else {
            parts = qtyStr + ' ' + unitEsc + ' • RS ' + rateStr;
        }
        var taxPctR = parseFloat(item.tax_percentage) || 0;
        var taxAmtR = parseFloat(item.tax_amount) || 0;
        var etRow = (item.entry_type || '').toString();
        if (etRow !== 'scrap' && taxPctR > 0 && taxAmtR > 0) {
            var pctDisp = (Math.abs(taxPctR - Math.round(taxPctR)) < 1e-6 ? String(Math.round(taxPctR)) : String(taxPctR));
            var amtDisp = (Math.abs(taxAmtR - Math.round(taxAmtR)) < 0.01 ? String(Math.round(taxAmtR)) : taxAmtR.toFixed(2));
            parts += ' · GST ' + pctDisp + '% Rs ' + amtDisp;
        }
        return parts;
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
        
        // Show battery-type sequence (or item name); parts/breakpad/filters use part - product • quality • company
        const itemName = purchaseTableRowDisplayName(item).replace(/</g, '&lt;').replace(/>/g, '&gt;');
        // Always use battery-type-sequence highlight for item name (same style everywhere: dark blue, bold)
        const nameRowClass = 'battery-type-sequence fw-bold purchase-row-item-name';
        const warehouseDisplay = (item.warehouse_name || '—').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var demandUserLine = (item.demand_user_name != null && String(item.demand_user_name).trim() !== '') ? String(item.demand_user_name).trim() : '';
        var demandUserHtml = demandUserLine
            ? '<div class="text-muted purchase-row-demand-by" style="font-size:0.7rem;line-height:1.25;max-width:168px;" title="Demand by">' + demandUserLine.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') + '</div>'
            : '';
        const entryType = item.entry_type || 'purchase';
        /* Line-type badges: PURCHASE vs RETURN etc. — same layout (next to item name) for quick scanning. */
        let entryTypeBadge = '';
        if (entryType === 'return') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 text-dark fw-semibold text-center" style="background-color: #ffc107; font-size: 0.75rem; min-width: 4em;">RETURN</span>';
        else if (entryType === 'purchase') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 text-white fw-semibold text-center" style="background-color: #0d6efd; font-size: 0.75rem; min-width: 4em;">PURCHASE</span>';
        else if (entryType === 'scrap') entryTypeBadge = '<span class="d-inline-block text-center purchase-scrap-send-badge">SCRAP SEND</span>';
        else if (entryType === 'claim') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 text-white fw-semibold text-center" style="background-color: #198754; font-size: 0.75rem;">CLAIM</span>';
        else if (entryType === 'claim_send') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 text-white fw-semibold text-center" style="background-color: #6f42c1; font-size: 0.75rem;">SEND CLAIM</span>';
        else if (entryType === 'damage') entryTypeBadge = '<span class="d-inline-block rounded-2 px-2 py-1 text-white fw-semibold text-center" style="background-color: #dc3545; font-size: 0.75rem;">DAMAGE</span>';
        const entryBadgeHtml = entryTypeBadge ? '<div class="purchase-row-entry-badge-wrap">' + entryTypeBadge + '</div>' : '';
        var rawThumb = resolvePurchaseRowImageSource(item);
        const itemImageUrl = rawThumb ? normalizeItemImageUrl(rawThumb) : '';
        const photoHtml = itemImageUrl
            ? `<img src="${itemImageUrl.replace(/"/g, '&quot;')}" alt="" class="me-2 flex-shrink-0 purchase-row-item-thumb" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px; cursor: pointer;" data-full-src="${itemImageUrl.replace(/"/g, '&quot;')}" title="Click to view all photos" onerror="this.onerror=null;this.src='{{ asset('assets/img/icons/image.svg') }}'">`
            : `<span class="me-2 d-inline-flex align-items-center justify-content-center bg-light border flex-shrink-0 text-muted" style="width: 48px; height: 48px; border-radius: 8px;"><i class="ti ti-photo" style="font-size: 1.2rem;"></i></span>`;
        const warehouseHtml = `<div class="d-flex align-items-center gap-2">${photoHtml}<div class="d-flex flex-column min-w-0"><div class="text-muted small" style="font-size: 0.8rem;">${warehouseDisplay}</div>${demandUserHtml}</div></div>`;
        const retailPctNum = (item.retail_pct != null && !isNaN(parseFloat(item.retail_pct))) ? parseFloat(item.retail_pct) : null;
        const retailPctDisplay = retailPctNum != null ? (retailPctNum + '%') : '';
        const itemIdNum = (item.item_id != null && item.item_id !== '') ? parseInt(item.item_id, 10) : 0;
        const itemColorClass = 'items-row-wh-' + (isNaN(itemIdNum) ? 0 : (Math.abs(itemIdNum) % 6));
        const totalDisplayFormatted = Math.round(displayVal).toLocaleString('en-PK', { maximumFractionDigits: 0 });
        const retailBadgeStyle = retailPctNum != null
            ? (retailPctNum < 0
                ? 'background:#b91c1c;color:#fff;font-size:10px;'
                : 'background:#1e3a5f;color:#fff;font-size:10px;')
            : '';
        // For claim rows: hide GST display only, keep qty, rate and total.
        // We do this by zeroing tax inputs used only for rendering the GST suffix.
        var secondLine;
        if (entryType === 'claim') {
            var itemNoGst = Object.assign({}, item, { tax_percentage: 0, tax_amount: 0 });
            secondLine = buildPurchaseRowQtyRateText(itemNoGst);
        } else {
            secondLine = buildPurchaseRowQtyRateText(item);
            if (retailPctNum != null) {
                secondLine += ' • <span class="badge rounded-pill px-2 py-1" style="' + retailBadgeStyle + '">' + retailPctDisplay + '</span>';
            }
        }
        const poItemIdAttr = (item.purchase_order_item_id != null && item.purchase_order_item_id !== '' && parseInt(item.purchase_order_item_id, 10) > 0) ? (' data-purchase-order-item-id="' + parseInt(item.purchase_order_item_id, 10) + '"') : '';
        const isTemporary = !!(item.is_temporary);
        const isTemporaryAttr = isTemporary ? ' data-is-temporary="1"' : '';
        var voiceUrlRaw = (item.voice_url && typeof item.voice_url === 'string') ? item.voice_url.trim() : '';
        var voiceSrcAttr = voiceUrlRaw ? voiceUrlRaw.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '') : '';
        var voiceHtml = voiceSrcAttr
            ? '<div class="mt-1 d-flex justify-content-center purchase-row-voice-wrap"><audio controls preload="metadata" class="purchase-row-voice" style="max-width:min(100%,280px);height:32px;vertical-align:middle;"><source src="' + voiceSrcAttr + '"></audio></div>'
            : '';
        const isReturnRow = entryType === 'return';
        const printSelectTd = (isTemporary || isReturnRow)
            ? '<td class="align-middle text-center pehla-td-print-select"><span class="text-muted">—</span></td>'
            : '<td class="align-middle pehla-td-print-select purchase-row-print-cell text-center" style="white-space: nowrap;"><input type="checkbox" class="form-check-input purchase-row-verified-cb me-2" title="Select for label print" ' + (item.verified ? 'checked' : '') + '><button type="button" class="btn btn-sm btn-link p-0 purchase-row-print-btn" title="Print labels"><i class="ti ti-printer"></i></button></td>';
        const row = `
            <tr class="purchase-item-row pehla-items-row ${itemColorClass}" data-item-id="${item.item_id}" data-row-id="${item.id}" data-entry-type="${item.entry_type || 'purchase'}" data-warehouse-id="${item.warehouse_id || ''}"${poItemIdAttr}${isTemporaryAttr}>
                <td class="align-middle pehla-td-warehouse">${warehouseHtml}</td>
                <td class="align-middle pehla-td-item text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                        <div class="${nameRowClass} mb-0">${itemName}</div>
                        ${entryBadgeHtml}
                    </div>
                    <div class="small text-muted purchase-row-qty-unit-line text-center">${secondLine}</div>
                    ${voiceHtml}
                </td>
                <td class="align-middle text-end pehla-td-total purchase-row-total-display${totalClass}">RS ${totalDisplayFormatted}</td>
                ${printSelectTd}
                <td class="align-middle pehla-td-actions purchase-row-action-cell">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item" data-row-id="${item.id}" title="Remove"><i class="ti ti-x"></i></button>
                </td>
            </tr>
        `;
        $('#items-tbody').append(row);
        // Keep warehouse/image visible on every item row (no rowspan grouping).
        resetWarehouseCellsVisibility();
        updatePurchaseTableRetailColumnVisibility();
        updatePurchasePrintButton();
    }

    // Ensure warehouse cell is visible for every row.
    function resetWarehouseCellsVisibility() {
        var $rows = $('#items-tbody .purchase-item-row');
        if (!$rows.length) return;
        $rows.each(function() {
            var $cell = $(this).children('td.pehla-td-warehouse').first();
            if ($cell.length) {
                $cell.show().attr('rowspan', 1);
            }
        });
    }

    function highlightScannedPurchaseRow(rowId) {
        if (rowId == null || rowId === '') return;
        var $row = $('#items-tbody .purchase-item-row[data-row-id="' + rowId + '"]');
        if (!$row.length) return;
        $('#items-tbody .purchase-item-row.scan-highlight').removeClass('scan-highlight');
        $('#items-tbody .purchase-item-row.manual-selected-row').removeClass('manual-selected-row');
        $row.addClass('scan-highlight');
        try { $row[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
    }

    // Manual row selection clears scan-highlight and marks manually selected row.
    $(document).on('click', '#items-tbody .purchase-item-row', function() {
        var $row = $(this);
        $('#items-tbody .purchase-item-row.scan-highlight').removeClass('scan-highlight');
        $('#items-tbody .purchase-item-row.manual-selected-row').removeClass('manual-selected-row');
        $row.addClass('manual-selected-row');
    });

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

    // Checkbox: sirf select/deselect — modal na kholen, click row tak na jaye
    $(document).on('click', '#items-tbody .purchase-row-verified-cb', function(e) {
        e.stopPropagation();
    });
    $(document).on('change', '#items-tbody .purchase-row-verified-cb', function() {
        var rowId = $(this).closest('.purchase-item-row').data('row-id');
        var item = purchaseItems.find(function(i) { return i.id === rowId; });
        if (item) item.verified = this.checked ? 1 : 0;
    });

// Remove item
    $(document).on('click', '.remove-item', function(e) {
        e.stopPropagation();
        if (window.isBillLocked) return;
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
        updatePurchasePrintButton();
        if ($('#purchaseScrapPickerModal').hasClass('show')) {
            purchaseScrapPickerRefreshList();
        }
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
        item.tax_amount = taxAmount;
        var totalFormatted = Math.round(total).toLocaleString('en-PK', { maximumFractionDigits: 0 });
        $row.find('.purchase-row-total-display').text('Rs ' + totalFormatted).toggleClass('text-danger fw-bold', total < 0);
        var qtyRateText = buildPurchaseRowQtyRateText(item);
        var retailPctNum = (item.retail_pct != null && !isNaN(parseFloat(item.retail_pct))) ? parseFloat(item.retail_pct) : null;
        var retailPctHtml = '';
        if (retailPctNum != null) {
            var pctDisplay = retailPctNum + '%';
            var badgeStyle = retailPctNum < 0
                ? 'background:#b91c1c;color:#fff;font-size:10px;'
                : 'background:#1e3a5f;color:#fff;font-size:10px;';
            retailPctHtml = ' • <span class="badge rounded-pill px-2 py-1" style="' + badgeStyle + '">' + pctDisplay + '</span>';
        }
        $row.find('.purchase-row-qty-unit-line').html(qtyRateText + retailPctHtml);
        calculateTotals();
        syncCartToServer();
    }
    $(document).on('input change', '#items-tbody .purchase-row-qty-input, #items-tbody .purchase-row-rate-input', function() {
        if (window.isBillLocked) return;
        updatePurchaseBarRowFromInputs($(this).closest('.purchase-item-row'));
    });

    function updatePurchaseBulkRetailBar() {}

    // ===== LABEL PRINT: single source of truth =====
    // labelsToPrint = { preset, quantity, showPrice, labels:[{line1,line2,priceText,barcode,rowQty}] }
    window._labelsToPrint = null;

    function getItemLabelSalePrice(item) {
        if (!item || typeof item !== 'object') return 0;
        function pickNum(v) {
            if (v == null || v === '') return null;
            if (typeof v === 'string' && !String(v).trim()) return null;
            var n = parseFloat(v);
            return isNaN(n) ? null : n;
        }
        // Shelf price: first positive wins. (sale_price === 0 must not block retail / master fallbacks.)
        function pickPositive(v) {
            var n = pickNum(v);
            return (n != null && n > 0) ? n : null;
        }
        var p = pickPositive(item.sale_price)
            || pickPositive(item.total_sale_price)
            || pickPositive(item.sale_price_per_base)
            || pickPositive(item.retail_price)
            || pickPositive(item.retail_price_base)
            || pickPositive(item.item_master_sale_price)
            || pickPositive(item.item_master_retail_price);
        if (p != null) return p;
        // Never use item.rate — purchase/cost, not customer price.
        return 0;
    }

    function normalizeLabelLinesFromName(name) {
        var raw = (name || '').toString().replace(/<[^>]*>/g, '').trim();
        var parts = raw.split(/\s*[•·]\s*/).map(function(p) { return p.trim(); }).filter(Boolean);
        var line1 = '';
        var line2 = '';
        if (parts.length >= 2) {
            line1 = (parts[0] + ' . ' + parts[parts.length - 1]).toUpperCase().replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            if (parts.length > 2) {
                line2 = parts.slice(1, -1).join(' . ').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }
        } else if (parts.length === 1) {
            line1 = parts[0].replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        } else {
            line1 = raw.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') || '—';
        }
        return { line1: line1, line2: line2 };
    }

    /**
     * Build battery middle text in full format:
     * SERIES.PLATES.AH (e.g. GL50.11PL.38AH)
     * Returns '' when complete values are not available.
     */
    function buildBatteryCombinedLine(name) {
        var raw = (name || '').toString().replace(/<[^>]*>/g, '').trim();
        if (!raw) return '';
        var parts = raw.split(/\s*[•·]\s*/).map(function(p) { return p.trim(); }).filter(Boolean);
        if (parts.length < 3) return '';

        var seriesRaw = (parts[0] || '').toString().trim();
        var platesRaw = (parts[1] || '').toString().trim();
        var ahRaw = (parts[2] || '').toString().trim();
        if (!seriesRaw || !platesRaw || !ahRaw) return '';

        var series = seriesRaw.replace(/\s+/g, '');
        var plates = platesRaw.replace(/\s+/g, '').replace(/PL$/i, '');
        var ah = ahRaw.replace(/\s+/g, '').replace(/AH$/i, '');
        if (!series || !plates || !ah) return '';

        return (series + '.' + plates + 'PL.' + ah + 'AH')
            .toUpperCase()
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /** Print labels: show BATTERY TONIC instead of Distilled Water (name/category typo or wrong line). */
    function aliasDistilledWaterToBatteryTonic(s) {
        if (s == null || s === '') return s;
        return String(s)
            .replace(/\bdistilled\s+water\b/gi, 'BATTERY TONIC')
            .replace(/\bdistral\s+water\b/gi, 'BATTERY TONIC');
    }

    function aliasLabelPrintLine2(line2) {
        if (line2 == null || line2 === '') return line2;
        var s = aliasDistilledWaterToBatteryTonic(String(line2));
        s = s.replace(/BATTERY\s+TONIC\s*•\s*BATTERY\s+TONIC/gi, 'BATTERY TONIC');
        s = s.replace(/BATTERY\s+TONIC\s*\.\s*BATTERY\s+TONIC/gi, 'BATTERY TONIC');
        return s;
    }

    /** Right badge: literal grade A+ (not numeric qty like 1+) */
    function shouldShowLabelGradeAplus(rowQty) {
        if (rowQty == null || rowQty === '') return false;
        var n = parseFloat(rowQty);
        return !isNaN(n) && n > 0;
    }

    function buildLabelLine1RowHtml(line1, rowQty, priceText, showPrice, printOpts) {
        printOpts = printOpts || {};
        var line1Esc = (line1 || '—').toString();
        var itemType = (printOpts.itemType || '').toString().trim().toLowerCase();
        var qualityRaw = (printOpts.qualityName != null) ? String(printOpts.qualityName).trim() : '';
        var qualityEsc = qualityRaw.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        var techRaw = (printOpts.technologyName != null) ? String(printOpts.technologyName).trim() : '';
        var techEsc = techRaw.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        var showA = shouldShowLabelGradeAplus(rowQty);
        if (itemType === 'filters') {
            showA = false;
        }
        // Battery + Technology: show LED ACID etc. on the right instead of hardcoded A+
        if (itemType === 'battery' && techEsc) {
            showA = false;
        }
        var priceStr = (showPrice && priceText) ? String(priceText).replace(/&/g, '&amp;').replace(/</g, '&lt;') : '';
        var aplusHtml = showA
            ? '<span class="label-print-line1-qty"><span class="lp-grade-a">A</span><span class="lp-plus">+</span></span>'
            : '';
        var rightHtml;
        if (itemType === 'filters') {
            if (qualityEsc) {
                rightHtml = '<span class="label-print-line1-qty label-print-line1-quality">' + qualityEsc.toUpperCase() + '</span>';
            } else {
                rightHtml = '<span class="label-print-line1-qty label-print-line1-qty--empty" aria-hidden="true"></span>';
            }
        } else if (itemType === 'battery' && techEsc) {
            rightHtml = '<span class="label-print-line1-qty label-print-line1-quality label-print-line1-technology">' + techEsc.toUpperCase() + '</span>';
        } else {
            rightHtml = aplusHtml || '<span class="label-print-line1-qty label-print-line1-qty--empty" aria-hidden="true">A+</span>';
        }
        // Keep triple row when battery shows Technology on the right (showA is false but right column has text).
        if (!showA && itemType !== 'filters' && !priceStr && !(itemType === 'battery' && techEsc)) {
            return '<div class="label-print-line1-row label-print-line1-row--single"><span class="label-print-line1-text">' + line1Esc + '</span></div>';
        }
        var centerHtml = priceStr
            ? '<span class="label-print-line1-center">' + priceStr + '</span>'
            : '<span class="label-print-line1-center label-print-line1-center--empty" aria-hidden="true"></span>';
        if (itemType === 'filters' && !priceStr && !qualityEsc) {
            return '<div class="label-print-line1-row label-print-line1-row--single"><span class="label-print-line1-text">' + line1Esc + '</span></div>';
        }
        return '<div class="label-print-line1-row label-print-line1-row--triple">' +
            '<span class="label-print-line1-text">' + line1Esc + '</span>' +
            centerHtml +
            rightHtml +
            '</div>';
    }

    /** Shelf label: show category under/with name (e.g. ATLAS + BATTERY TONIC) when not already in the title. */
    function enrichLabelLinesWithCategory(lines, categoryName, nameRaw) {
        if (!lines) return lines;
        var cat = (categoryName || '').toString().replace(/<[^>]*>/g, '').trim();
        if (!cat || /^other$/i.test(cat)) return lines;
        var namePlain = ((nameRaw || '').toString().replace(/<[^>]*>/g, '')).trim().toLowerCase();
        if (namePlain.indexOf(cat.toLowerCase()) !== -1) return lines;
        var catU = cat.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').toUpperCase();
        if (lines.line2 && String(lines.line2).trim()) {
            lines.line2 = catU + ' • ' + lines.line2;
        } else {
            lines.line2 = catU;
        }
        return lines;
    }

    /** Top row left: company name (e.g. ATLAS), not product title (e.g. BATTERY TONIC on line1). */
    function applyCompanyNameToLabelLine1(lines, companyName) {
        if (!lines) return lines;
        var co = (companyName || '').toString().replace(/<[^>]*>/g, '').trim();
        if (!co) return lines;
        lines.line1 = co.toUpperCase().replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        return lines;
    }

    /** Filters/parts/breakpad shelf label: company on top, category under; part # goes on line2 (see buildLabelLinesForItem). */
    function applyCompanyCategoryStackToLabelLine1(lines, companyName, categoryName) {
        if (!lines) return lines;
        var co = (companyName || '').toString().replace(/<[^>]*>/g, '').trim();
        var cat = (categoryName || '').toString().replace(/<[^>]*>/g, '').trim();
        if (!co && !cat) return lines;
        if (cat && /^other$/i.test(cat)) cat = '';
        function esc(s) {
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        if (co && cat) {
            lines.line1 = '<span class="label-print-line1-brand-stack"><span class="label-print-line1-co">' + esc(co).toUpperCase() + '</span><span class="label-print-line1-cat">' + esc(cat).toUpperCase() + '</span></span>';
        } else if (co) {
            lines.line1 = esc(co).toUpperCase();
        } else {
            lines.line1 = esc(cat).toUpperCase();
        }
        return lines;
    }

    /** Single source for thermal label line1/line2 (modal preview + print). */
    function buildLabelLinesForItem(name, categoryName, companyName, itemType, partNumber) {
        var lines = normalizeLabelLinesFromName(name);
        var it = (itemType || '').toString().trim().toLowerCase();
        if (it === 'filters' || it === 'parts' || it === 'breakpad') {
            lines = applyCompanyCategoryStackToLabelLine1(lines, companyName, categoryName);
            var pn = (partNumber != null && String(partNumber).trim() !== '') ? String(partNumber).trim() : '';
            lines.line2 = pn
                ? pn.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').toUpperCase()
                : '';
        } else {
            lines = enrichLabelLinesWithCategory(lines, categoryName, name);
            lines = applyCompanyNameToLabelLine1(lines, companyName);
            if (it === 'battery') {
                var batteryCombined = buildBatteryCombinedLine(name);
                if (batteryCombined) lines.line2 = batteryCombined;
            }
        }
        return lines;
    }

    // Supports both new object signature and legacy positional args:
    // buildLabelPrintItemHtml({line1,line2,rowQty,priceText,barcode,showPrice}) — line1 row: name | Rs (center) | A+
    // buildLabelPrintItemHtml(name, priceText, barcodeVal)
    function buildLabelPrintItemHtml(labelOrName, legacyPriceText, legacyBarcodeVal) {
        var label = null;
        if (labelOrName && typeof labelOrName === 'object' && !Array.isArray(labelOrName)) {
            label = labelOrName;
        } else {
            var lines = normalizeLabelLinesFromName(labelOrName || '');
            label = {
                line1: lines.line1,
                line2: lines.line2,
                priceText: legacyPriceText || '',
                showPrice: true,
                barcode: legacyBarcodeVal
            };
        }

        var line1Raw = (label && label.line1) ? String(label.line1) : '—';
        var line1 = (line1Raw.indexOf('<') !== -1) ? line1Raw : aliasDistilledWaterToBatteryTonic(line1Raw);
        var line2 = (label && label.line2) ? aliasLabelPrintLine2(String(label.line2)) : '';
        var rowQty = (label && label.rowQty != null) ? label.rowQty : null;
        var priceText = (label && label.priceText) ? String(label.priceText) : '';
        var showPrice = !!(label && label.showPrice);
        var barcodeRaw = (label && label.barcode != null && label.barcode !== '') ? String(label.barcode) : '0';
        var barcodeAttr = barcodeRaw.replace(/"/g, '&quot;');
        var barcodeCaptionHtml = barcodeRaw.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var printOpts = {
            itemType: (label && label.itemType) ? label.itemType : '',
            qualityName: (label && label.qualityName != null) ? label.qualityName : '',
            technologyName: (label && label.technologyName != null) ? label.technologyName : ''
        };
        var itLb = (label && label.itemType) ? String(label.itemType).trim().toLowerCase() : '';
        var line2Class = 'label-print-line2';
        if (line2 && ['filters', 'parts', 'breakpad'].indexOf(itLb) !== -1) {
            line2Class += ' label-print-line2--part-hero';
        }
        var barcodeHtml = '<div class="label-print-barcode-wrap"><div class="label-print-barcode-clip"><svg class="label-print-barcode" data-barcode="' + barcodeAttr + '"></svg></div><div class="label-print-barcode-caption">' + barcodeCaptionHtml + '</div></div>';
        return '<div class="label label-print-item">' +
            '<div class="label-print-head">' +
            buildLabelLine1RowHtml(line1, rowQty, priceText, showPrice, printOpts) +
            '</div>' +
            (line2 ? '<div class="' + line2Class + '">' + line2 + '</div>' : '') +
            barcodeHtml +
            '</div>';
    }

    function getActiveLabelPreset() {
        return window._labelPrintPresetCurrent || { unit: 'in', width: 2, height: 1, padding: 0.08, barcode_height: 0.35, font: { line1: 14, line2: 12, rate: 11 } };
    }

    function buildLabelsToPrintFromSingle(name, priceText, barcodeVal, quantity, showPrice, categoryName, rowQty, companyName, itemType, qualityName, technologyName, partNumber) {
        var preset = getActiveLabelPreset();
        var q = parseInt(quantity, 10);
        if (isNaN(q) || q < 1) q = 1;
        if (q > 500) q = 500;
        var rq = rowQty;
        if (rq == null || rq === '') rq = quantity;
        var lines = buildLabelLinesForItem(name, categoryName, companyName, itemType, partNumber);
        var it = (itemType || '').toString().trim().toLowerCase();
        var qn = (qualityName != null) ? String(qualityName).trim() : '';
        var tn = (technologyName != null) ? String(technologyName).trim() : '';
        var labels = [];
        for (var i = 0; i < q; i++) {
            labels.push({
                line1: lines.line1,
                line2: lines.line2,
                rowQty: rq,
                priceText: (priceText || '').toString().replace(/</g, '&lt;'),
                showPrice: !!showPrice,
                barcode: barcodeVal,
                itemType: it,
                qualityName: qn,
                technologyName: tn
            });
        }
        return { preset: preset, quantity: q, showPrice: !!showPrice, labels: labels };
    }

    function renderLabelPreview(job) {
        if (!job || !job.labels) return;
        window._labelsToPrint = job;
        var labelsHtml = '';
        job.labels.forEach(function(l) { labelsHtml += buildLabelPrintItemHtml(l); });
        $('#label-print-count').text(job.quantity + ' label' + (job.quantity !== 1 ? 's' : ''));
        $('#label-print-modal-content')
            .html('<div id="label-print-print-area" class="print-root"><div class="label-print-sheet">' + labelsHtml + '</div></div>');
        // keep class for legacy CSS, but print content itself is driven by showPrice
        $('#label-print-modal-content').toggleClass('label-print-hide-price', !job.showPrice);
        renderLabelBarcodes();
        $('#label-print-qty-wrap').removeClass('d-none').addClass('d-flex');
        $('#label-print-qty-input').val(job.quantity).attr('min', 1).attr('max', 500);
    }
    function getLabelPrintInnerWidthPx(preset) {
        preset = preset || window._labelPrintPresetCurrent || { unit: 'in', width: 2, padding: 0.08 };
        var unit = (preset.unit || 'in').toString();
        var w = parseFloat(preset.width) || 2;
        var pad = parseFloat(preset.padding) || 0.08;
        var labelPx = unit === 'mm' ? w * 3.78 : w * 96;
        var padPx = unit === 'mm' ? pad * 3.78 : pad * 96;
        return Math.max(48, Math.round(labelPx - 2 * padPx - 10));
    }

    /**
     * Draw CODE128 to fill label inner width (chora = wider bars), then refine once with getBBox
     * so short codes use the preset size and long codes shrink before clip.
     */
    function renderPurchaseLabelBarcodeInBox(svgEl, code, innerWidthPx, heightPx) {
        if (typeof JsBarcode === 'undefined' || !svgEl) return;
        var s = String(code == null || code === '' ? '0' : code);
        var len = Math.max(1, s.length);
        var modEst = Math.max(30, len * 8 + 26);
        var barW = (innerWidthPx * 0.97) / modEst;
        barW = Math.min(3.05, Math.max(0.45, barW));
        barW = Math.round(barW * 100) / 100;
        function paint(w) {
            JsBarcode(svgEl, s, {
                format: 'CODE128',
                width: w,
                height: heightPx,
                displayValue: false,
                margin: 0
            });
        }
        paint(barW);
        try {
            var rendered = svgEl.getBBox ? svgEl.getBBox().width : 0;
            if (rendered <= 0) return;
            var hardMax = innerWidthPx * 0.99;
            var softMin = innerWidthPx * 0.88;
            if (rendered > innerWidthPx) {
                barW = Math.max(0.45, barW * (hardMax / rendered));
                barW = Math.round(barW * 100) / 100;
                paint(barW);
            } else if (rendered < softMin) {
                barW = Math.min(3.05, barW * Math.min(1.68, (innerWidthPx * 0.97) / rendered));
                barW = Math.round(barW * 100) / 100;
                paint(barW);
                rendered = svgEl.getBBox().width;
                if (rendered > innerWidthPx) {
                    barW = Math.max(0.45, barW * (hardMax / rendered));
                    barW = Math.round(barW * 100) / 100;
                    paint(barW);
                }
            }
        } catch (e) { /* keep first paint */ }
    }

    function renderLabelBarcodes() {
        if (typeof JsBarcode === 'undefined') return;
        // Apply selected preset barcode height (in pixels) if available
        var preset = window._labelPrintPresetCurrent || null;
        var cssHeight = 58;
        if (preset && preset.barcode_height != null) {
            var unit = (preset.unit || 'in').toString();
            var bh = parseFloat(preset.barcode_height) || 0;
            if (bh > 0) {
                // Rough conversion for barcode rendering height
                cssHeight = unit === 'mm' ? Math.round(bh * 3.78) : Math.round(bh * 96);
                cssHeight = Math.max(30, Math.min(240, cssHeight));
            }
        }
        // Reserve space for human-readable line so bars + caption fit inside preset label box
        var captionReservePx = 11;
        cssHeight = Math.max(22, cssHeight - captionReservePx);
        // Minimum bar height (scanability) — old presets at 0.35" were ~21px bars
        cssHeight = Math.max(cssHeight, 30);
        var innerW = getLabelPrintInnerWidthPx(preset);
        $('#label-print-modal-content .label-print-barcode').each(function() {
            var val = $(this).data('barcode');
            if (val === '' || val == null) val = '0';
            renderPurchaseLabelBarcodeInBox(this, val, innerW, cssHeight);
        });
    }

    // ===== Label print presets (dynamic sizes) =====
    window._labelPrintPresets = [];
    window._labelPrintDefaultPresetId = '';
    window._labelPrintPresetCurrent = null;

    function lpUnitValue(n, unit) {
        var v = parseFloat(n);
        if (isNaN(v)) v = 0;
        return String(v) + (unit === 'mm' ? 'mm' : 'in');
    }
    function applyLabelPrintPreset(preset) {
        if (!preset) return;
        window._labelPrintPresetCurrent = preset;
        var unit = (preset.unit || 'in').toString();
        var w = parseFloat(preset.width) || 2;
        var h = parseFloat(preset.height) || 1;
        var pad = parseFloat(preset.padding) || 0.08;
        var bh = parseFloat(preset.barcode_height) || 0.35;
        var f1 = (preset.font && preset.font.line1) ? parseInt(preset.font.line1, 10) : 14;
        var f2 = (preset.font && preset.font.line2) ? parseInt(preset.font.line2, 10) : 12;
        var fr = (preset.font && preset.font.rate) ? parseInt(preset.font.rate, 10) : 11;

        var root = document.getElementById('label-print-view-modal');
        if (root) {
            root.style.setProperty('--lp-width', lpUnitValue(w, unit));
            root.style.setProperty('--lp-height', lpUnitValue(h, unit));
            root.style.setProperty('--lp-padding', lpUnitValue(pad, unit));
            root.style.setProperty('--lp-barcode-height', lpUnitValue(bh, unit));
            root.style.setProperty('--lp-font-line1', String(f1) + 'px');
            root.style.setProperty('--lp-font-line2', String(f2) + 'px');
            root.style.setProperty('--lp-font-rate', String(fr) + 'px');
        }

        // Update print stylesheet
        var s = document.getElementById('label-print-dynamic-style');
        if (s) {
            s.textContent = "@media print{ @page{ size: " + lpUnitValue(w, unit) + " " + lpUnitValue(h, unit) + "; margin: 0; } html,body{ width:" + lpUnitValue(w, unit) + "; height:" + lpUnitValue(h, unit) + "; } }";
        }

        // Re-render barcodes for new height
        try { renderLabelBarcodes(); } catch (e) {}
    }

    function loadLabelPrintPresets() {
        return $.ajax({
            url: '{{ route("admin.label.print.presets.index") }}',
            method: 'GET',
        }).then(function(res) {
            window._labelPrintPresets = (res && res.presets) ? res.presets : [];
            window._labelPrintDefaultPresetId = (res && res.default_preset_id) ? res.default_preset_id : '';
            var $sel = $('#label-print-size-select');
            if (!$sel.length) return;
            $sel.empty();
            window._labelPrintPresets.forEach(function(p) {
                var unit = (p.unit || 'in');
                $sel.append('<option value="' + (p.id || '') + '">' + (p.name || 'Preset') + ' (' + (p.width || '') + '×' + (p.height || '') + unit + ')</option>');
            });
            var defaultId = window._labelPrintDefaultPresetId || (window._labelPrintPresets[0] ? window._labelPrintPresets[0].id : '');
            if (defaultId) $sel.val(defaultId);
            var preset = window._labelPrintPresets.find(function(x){ return x.id === $sel.val(); }) || window._labelPrintPresets[0];
            applyLabelPrintPreset(preset);
        }).catch(function() {
            $('#label-print-size-select').html('<option value="">2×1 (fallback)</option>');
            applyLabelPrintPreset({ id: 'fallback', name: '2×1', unit: 'in', width: 2, height: 1, padding: 0.08, barcode_height: 0.35, font: { line1: 14, line2: 12, rate: 11 } });
        });
    }

    $(document).on('change', '#label-print-size-select', function() {
        var id = $(this).val();
        var preset = window._labelPrintPresets.find(function(x){ return x.id === id; });
        if (preset) applyLabelPrintPreset(preset);
    });

    $('#label-print-view-modal').on('shown.bs.modal', function() {
        loadLabelPrintPresets();
    });

    // Manage modal open
    $(document).on('click', '#label-print-manage-sizes-btn', function() {
        var el = document.getElementById('label-print-sizes-modal');
        if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) bootstrap.Modal.getOrCreateInstance(el).show();
        else $('#label-print-sizes-modal').modal('show');
        renderLabelPrintPresetTable();
        resetLabelPrintPresetForm();
    });

    function renderLabelPrintPresetTable() {
        var $tb = $('#label-print-sizes-tbody');
        if (!$tb.length) return;
        $tb.empty();
        window._labelPrintPresets.forEach(function(p) {
            var isDef = (p.id === window._labelPrintDefaultPresetId);
            $tb.append(
                '<tr data-id="' + p.id + '">' +
                '<td class="fw-semibold">' + (p.name || '') + '</td>' +
                '<td>' + (p.width || '') + '×' + (p.height || '') + '</td>' +
                '<td>' + (p.unit || 'in') + '</td>' +
                '<td>' + (isDef ? '<span class="badge bg-primary">Default</span>' : '') + '</td>' +
                '<td class="text-end">' +
                '<button type="button" class="btn btn-sm btn-outline-primary lp-edit">Edit</button> ' +
                '</td>' +
                '</tr>'
            );
        });
    }

    function resetLabelPrintPresetForm() {
        $('#lp-preset-id').val('');
        $('#lp-name').val('');
        $('#lp-unit').val('in');
        $('#lp-width').val('');
        $('#lp-height').val('');
        $('#lp-default').prop('checked', false);
        $('#lp-padding').val('');
        $('#lp-barcode-height').val('');
        $('#lp-font-line1').val('');
        $('#lp-font-line2').val('');
        $('#lp-font-rate').val('');
        $('#label-print-sizes-delete').prop('disabled', true);
    }
    $(document).on('click', '#label-print-sizes-form-reset', resetLabelPrintPresetForm);

    $(document).on('click', '#label-print-sizes-tbody .lp-edit', function() {
        var id = $(this).closest('tr').data('id');
        var p = window._labelPrintPresets.find(function(x){ return x.id === id; });
        if (!p) return;
        $('#lp-preset-id').val(p.id || '');
        $('#lp-name').val(p.name || '');
        $('#lp-unit').val(p.unit || 'in');
        $('#lp-width').val(p.width || '');
        $('#lp-height').val(p.height || '');
        $('#lp-default').prop('checked', (p.id === window._labelPrintDefaultPresetId));
        $('#lp-padding').val(p.padding != null ? p.padding : '');
        $('#lp-barcode-height').val(p.barcode_height != null ? p.barcode_height : '');
        $('#lp-font-line1').val((p.font && p.font.line1) ? p.font.line1 : '');
        $('#lp-font-line2').val((p.font && p.font.line2) ? p.font.line2 : '');
        $('#lp-font-rate').val((p.font && p.font.rate) ? p.font.rate : '');
        $('#label-print-sizes-delete').prop('disabled', false);
    });

    $(document).on('click', '#label-print-sizes-delete', function() {
        var id = ($('#lp-preset-id').val() || '').toString().trim();
        if (!id) return;
        window._labelPrintPresets = window._labelPrintPresets.filter(function(p){ return p.id !== id; });
        if (window._labelPrintDefaultPresetId === id) {
            window._labelPrintDefaultPresetId = window._labelPrintPresets[0] ? window._labelPrintPresets[0].id : '';
        }
        renderLabelPrintPresetTable();
        resetLabelPrintPresetForm();
    });

    $(document).on('click', '#label-print-sizes-save', function() {
        var id = ($('#lp-preset-id').val() || '').toString().trim();
        var name = ($('#lp-name').val() || '').toString().trim();
        var unit = ($('#lp-unit').val() || 'in').toString();
        var width = parseFloat($('#lp-width').val()) || 0;
        var height = parseFloat($('#lp-height').val()) || 0;
        if (!name || width <= 0 || height <= 0) {
            if (typeof toastr !== 'undefined') toastr.error('Name/Width/Height required.');
            return;
        }
        if (!id) id = name.toLowerCase().replace(/\s+/g,'_').replace(/[^a-z0-9_]/g,'') + '_' + Math.random().toString(36).slice(2, 6);
        var preset = {
            id: id,
            name: name,
            unit: unit,
            width: width,
            height: height,
            padding: parseFloat($('#lp-padding').val()) || 0,
            barcode_height: parseFloat($('#lp-barcode-height').val()) || 0,
            font: {
                line1: parseInt($('#lp-font-line1').val(), 10) || 14,
                line2: parseInt($('#lp-font-line2').val(), 10) || 12,
                rate: parseInt($('#lp-font-rate').val(), 10) || 11
            },
            margin: { top: 0, right: 0, bottom: 0, left: 0 }
        };
        window._labelPrintPresets = window._labelPrintPresets.filter(function(p){ return p.id !== id; });
        window._labelPrintPresets.push(preset);
        if ($('#lp-default').is(':checked')) window._labelPrintDefaultPresetId = id;

        $.ajax({
            url: '{{ route("admin.label.print.presets.store") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                presets: window._labelPrintPresets,
                default_preset_id: window._labelPrintDefaultPresetId
            }
        }).done(function(res) {
            window._labelPrintPresets = res.presets || window._labelPrintPresets;
            window._labelPrintDefaultPresetId = res.default_preset_id || window._labelPrintDefaultPresetId;
            renderLabelPrintPresetTable();
            loadLabelPrintPresets();
            resetLabelPrintPresetForm();
            if (typeof toastr !== 'undefined') toastr.success('Saved.');
        }).fail(function() {
            if (typeof toastr !== 'undefined') toastr.error('Failed to save presets.');
        });
    });

    // Print button par click → seedha label print modal kholo
    $(document).on('click', '#items-tbody .purchase-row-print-qty-cell button.purchase-row-print-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (window.isBillLocked) return;
        var rowId = $(this).data('row-id');
        var item = purchaseItems.find(function(i) { return i.id === rowId; });
        if (!item) return;
        var qty = Math.max(1, Math.min(500, Math.round(parseFloat(item.quantity)) || 1));
        var name = (item.name || 'Item #' + item.item_id);
        var salePrice = getItemLabelSalePrice(item);
        var priceText = (salePrice > 0) ? ('Rs ' + Math.round(salePrice)) : '';
        var barcodeVal = item.bar_code || item.item_id || '';
        var showPrice = $('#label-print-show-price').is(':checked');
        var job = buildLabelsToPrintFromSingle(name, priceText, barcodeVal, qty, showPrice, item.category_name, item.quantity, item.company_name, item.item_type, item.quality_name, item.technology_name, item.part_number);
        var $content = $('#label-print-modal-content');
        $content.data('single-name', name).data('single-rate', priceText).data('single-item-id', barcodeVal)
            .data('single-category-name', item.category_name || '').data('single-row-qty', item.quantity)
            .data('single-company-name', item.company_name || '')
            .data('single-item-type', item.item_type || '').data('single-quality-name', item.quality_name || '')
            .data('single-part-number', item.part_number || '')
            .data('single-technology-name', item.technology_name || '');
        renderLabelPreview(job);
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
        if (window.isBillLocked) return;
        var rowId = $(this).closest('.purchase-item-row').data('row-id');
        var item = purchaseItems.find(function(i) { return i.id === rowId; });
        if (!item) return;
        if ((item.entry_type || 'purchase') === 'return') return;
        var qty = Math.max(1, Math.min(500, Math.round(parseFloat(item.quantity)) || 1));
        var name = (item.name || 'Item #' + item.item_id);
        var salePrice = getItemLabelSalePrice(item);
        var priceText = (salePrice > 0) ? ('Rs ' + Math.round(salePrice)) : '';
        var barcodeVal = item.bar_code || item.item_id || '';
        var showPrice2 = $('#label-print-show-price').is(':checked');
        var job2 = buildLabelsToPrintFromSingle(name, priceText, barcodeVal, qty, showPrice2, item.category_name, item.quantity, item.company_name, item.item_type, item.quality_name, item.technology_name, item.part_number);
        var $content2 = $('#label-print-modal-content');
        $content2.data('single-name', name).data('single-rate', priceText).data('single-item-id', barcodeVal)
            .data('single-category-name', item.category_name || '').data('single-row-qty', item.quantity)
            .data('single-company-name', item.company_name || '')
            .data('single-item-type', item.item_type || '').data('single-quality-name', item.quality_name || '')
            .data('single-part-number', item.part_number || '')
            .data('single-technology-name', item.technology_name || '');
        renderLabelPreview(job2);
        var labelModalEl = document.getElementById('label-print-view-modal');
        if (labelModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(labelModalEl).show();
        } else {
            $('#label-print-view-modal').modal('show');
        }
    });
    // Browser headers/footers are controlled by print dialog settings and must be disabled manually by the user.
    // Label print: isolated iframe + robust print triggering for Chrome
    window._labelPrintInProgress = false;
    $(document).off('click', '#label-print-modal-print-btn').on('click', '#label-print-modal-print-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (window._labelPrintInProgress) return false;

        var $btn = $(this);
        var oldDocTitle = document.title;
        try { document.title = ''; } catch (err) {}

        var job = window._labelsToPrint;
        if (!job || !Array.isArray(job.labels) || job.labels.length === 0) {
            try { document.title = oldDocTitle; } catch (err) {}
            if (typeof toastr !== 'undefined') toastr.warning('Print ke liye labels available nahi hain.');
            return false;
        }

        window._labelPrintInProgress = true;
        $btn.prop('disabled', true);

        // Determine selected preset (fallback 2x1 inch)
        var p = job.preset || window._labelPrintPresetCurrent || { unit: 'in', width: 2, height: 1, padding: 0.08, barcode_height: 0.35, font: { line1: 14, line2: 12, rate: 11 } };
        var unit = (p.unit || 'in').toString();
        var w = parseFloat(p.width) || 2;
        var h = parseFloat(p.height) || 1;
        var pad = parseFloat(p.padding) || 0;
        var bh = parseFloat(p.barcode_height) || 0.35;
        var f1 = (p.font && p.font.line1) ? parseInt(p.font.line1, 10) : 14;
        var f2 = (p.font && p.font.line2) ? parseInt(p.font.line2, 10) : 12;
        var fr = (p.font && p.font.rate) ? parseInt(p.font.rate, 10) : 11;
        var uv = function(n) { var v = parseFloat(n) || 0; return String(v) + (unit === 'mm' ? 'mm' : 'in'); };
        var pxH = unit === 'mm' ? Math.round((parseFloat(bh) || 0.35) * 3.78) : Math.round((parseFloat(bh) || 0.35) * 96);
        pxH = Math.max(30, Math.min(240, pxH));
        pxH = Math.max(22, pxH - 11);
        pxH = Math.max(pxH, 30);

        var iframe = document.getElementById('label-print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'label-print-iframe';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            iframe.style.opacity = '0';
            iframe.style.pointerEvents = 'none';
            document.body.appendChild(iframe);
        }

        var labelsHtml = '';
        job.labels.forEach(function(l) { labelsHtml += buildLabelPrintItemHtml(l); });
        var html = '<div id="label-print-print-area" class="print-root"><div class="label-print-sheet">' + labelsHtml + '</div></div>';

        var iframeHtml =
            '<!doctype html><html><head><meta charset="utf-8"><title></title><style>' +
            '@page{size:auto;margin:0;}' +
            'html,body{margin:0;padding:0;background:#fff;}' +
            'body{-webkit-print-color-adjust:exact;print-color-adjust:exact;color:#000;font-family:Arial,Helvetica,sans-serif;}' +
            '*{box-shadow:none !important;text-shadow:none !important;filter:none !important;box-sizing:border-box;}' +
            '.print-root{margin:0;padding:0;width:100%;}' +
            '.label-print-sheet{display:flex;flex-wrap:wrap;gap:0;padding:0;margin:0;align-content:flex-start;justify-content:flex-start;}' +
            '.label{width:' + uv(w) + ';min-width:' + uv(w) + ';max-width:' + uv(w) + ';height:' + uv(h) + ';min-height:' + uv(h) + ';max-height:' + uv(h) + ';margin:0;padding:' + uv(pad) + ';display:flex;flex-direction:column;justify-content:flex-start;align-items:center;overflow:hidden;box-sizing:border-box;break-inside:avoid;page-break-inside:avoid;}' +
            '.label-print-head{align-self:stretch;width:100%;max-width:100%;flex-shrink:1;min-height:0;overflow:hidden;}' +
            '.label-print-line1-row--triple{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);align-items:start;gap:4px;width:100%;max-width:100%;margin-bottom:1px;}' +
            '.label-print-line1-row--single{width:100%;max-width:100%;margin-bottom:2px;}' +
            '.label-print-line1-row--single .label-print-line1-text{display:block;text-align:left;}' +
            '.label-print-line1-text{font-weight:700;font-size:' + String(f1) + 'px;line-height:1.2;text-transform:uppercase;letter-spacing:.02em;justify-self:start;text-align:left;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}' +
            '.label-print-line1-center{justify-self:center;text-align:center;font-size:' + String(fr) + 'px;font-weight:600;line-height:1.2;color:#000;white-space:nowrap;padding:0 2px;}' +
            '.label-print-line1-center--empty{padding:0;min-width:0;}' +
            '.label-print-line1-qty{font-weight:800;font-size:' + String(f1) + 'px;line-height:1;justify-self:end;text-align:right;white-space:nowrap;letter-spacing:.02em;transform:translateX(-3px);}' +
            '.label-print-line1-qty--empty{visibility:hidden;pointer-events:none;}' +
            '.label-print-line1-qty .lp-grade-a{font-weight:800;}' +
            '.label-print-line1-qty .lp-plus{font-size:' + String(Math.max(10, Math.round(f1 * 0.82))) + 'px;vertical-align:0.12em;margin-left:0.02em;font-weight:900;}' +
            '.label-print-line1-qty.label-print-line1-quality{transform:none;font-weight:600;font-size:10px;line-height:1.2;letter-spacing:0.03em;padding:2px 6px;border-radius:4px;color:#f97316;background:rgba(249,115,22,.08);justify-self:end;align-self:start;max-width:100%;overflow:hidden;text-overflow:ellipsis;}' +
            '.label-print-line1-text .label-print-line1-brand-stack{display:flex;flex-direction:column;align-items:flex-start;gap:2px;max-width:100%;white-space:normal;}' +
            '.label-print-line1-row--triple .label-print-line1-text:has(.label-print-line1-brand-stack){white-space:normal;overflow:visible;text-overflow:clip;}' +
            '.label-print-line1-brand-stack .label-print-line1-co{font-size:10px;font-weight:500;color:#6b7280;line-height:1.15;letter-spacing:.02em;}' +
            '.label-print-line1-brand-stack .label-print-line1-cat{font-size:9px;font-weight:500;color:#6b7280;line-height:1.1;opacity:.95;}' +
            '.label-print-line2{font-size:' + String(f2) + 'px;font-weight:700;line-height:1.15;margin-top:1px;margin-bottom:0;color:#000;width:100%;max-width:100%;overflow:hidden;text-align:center;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;flex-shrink:1;min-height:0;}' +
            '.label-print-line2--part-hero{font-size:15px !important;font-weight:600 !important;color:#111827 !important;letter-spacing:.02em !important;line-height:1.15 !important;}' +
            '.label-print-barcode-wrap{flex:0 0 auto;flex-shrink:0;min-width:0;min-height:28px;max-width:100%;align-self:stretch;margin-top:auto;width:100%;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:2px;overflow:visible;padding-top:0;}' +
            '.label-print-barcode-clip{width:100%;max-width:100%;overflow:hidden;display:flex;justify-content:center;align-items:flex-end;min-height:24px;flex:0 0 auto;}' +
            '.label-print-barcode-wrap .label-print-barcode{display:block;max-width:100%;width:auto;height:auto;flex-shrink:1;min-width:0;min-height:0;max-height:calc(100% - 11px);object-fit:contain;vertical-align:bottom;text-rendering:geometricPrecision;shape-rendering:geometricPrecision;}' +
            '.label-print-barcode-caption{font-size:8px;font-weight:600;line-height:1.1;font-family:ui-monospace,Consolas,monospace;color:#000;max-width:100%;text-align:center;padding:0 1px;flex-shrink:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}' +
            '@media print{' +
                '@page{size:auto;margin:0;}' +
                'html,body{margin:0 !important;padding:0 !important;background:#fff !important;}' +
                '.print-root{margin:0 !important;padding:0 !important;width:100% !important;}' +
                '.label-print-sheet{display:flex !important;flex-wrap:wrap !important;gap:0 !important;padding:0 !important;margin:0 !important;}' +
                '.label{width:' + uv(w) + ' !important;min-width:' + uv(w) + ' !important;max-width:' + uv(w) + ' !important;height:' + uv(h) + ' !important;min-height:' + uv(h) + ' !important;max-height:' + uv(h) + ' !important;padding:' + uv(pad) + ' !important;margin:0 !important;box-sizing:border-box !important;overflow:hidden !important;break-inside:avoid !important;page-break-inside:avoid !important;}' +
            '}' +
            '</style></head><body>' + html + '</body></html>';

        var restorePrintState = function() {
            window._labelPrintInProgress = false;
            $btn.prop('disabled', false);
            try { document.title = oldDocTitle; } catch (err) {}
        };

        var invokePrint = function() {
            try {
                var doc = iframe.contentWindow.document;
                var barcodeEls = doc.querySelectorAll('.label-print-barcode');
                var unitP = (p.unit || 'in').toString();
                var pw = parseFloat(p.width) || 2;
                var padP = parseFloat(p.padding) || 0.08;
                var labelPxP = unitP === 'mm' ? pw * 3.78 : pw * 96;
                var padPxP = unitP === 'mm' ? padP * 3.78 : padP * 96;
                var innerWP = Math.max(48, Math.round(labelPxP - 2 * padPxP - 10));
                if (typeof JsBarcode !== 'undefined' && typeof renderPurchaseLabelBarcodeInBox === 'function') {
                    barcodeEls.forEach(function(el) {
                        var val = el.getAttribute('data-barcode');
                        if (val == null || val === '') val = '0';
                        renderPurchaseLabelBarcodeInBox(el, val, innerWP, pxH);
                    });
                }
            } catch (err) {}

            var done = false;
            var finish = function() { if (!done) { done = true; restorePrintState(); } };
            try { iframe.contentWindow.onafterprint = finish; } catch (err) {}

            // Best effort: trigger print when frame is focused and painted.
            setTimeout(function() {
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch (err) {
                    if (typeof toastr !== 'undefined') toastr.error('Print dialog open nahi hua. Browser pop-up/print settings check karein.');
                    else alert('Print dialog open nahi hua. Browser pop-up/print settings check karein.');
                    finish();
                    return;
                }
                // Fallback unlock in case onafterprint doesn't fire (Chrome edge cases).
                setTimeout(finish, 4000);
            }, 60);
        };

        try {
            var d = iframe.contentWindow.document;
            d.open();
            d.write(iframeHtml);
            d.close();
        } catch (err) {
            restorePrintState();
            if (typeof toastr !== 'undefined') toastr.error('Print frame create nahi ho saka.');
            else alert('Print frame create nahi ho saka.');
            return false;
        }

        // Wait for frame ready; then print.
        if (iframe.contentWindow.document.readyState === 'complete') {
            invokePrint();
        } else {
            iframe.onload = function() {
                iframe.onload = null;
                invokePrint();
            };
            setTimeout(function() {
                if (window._labelPrintInProgress) invokePrint();
            }, 350);
        }
        return false;
    });
    // Show price OFF by default every time the label print modal opens
    $('#label-print-view-modal').on('shown.bs.modal', function() {
        $('#label-print-show-price').prop('checked', false);
        $('#label-print-modal-content').addClass('label-print-hide-price');
    });
    // Switch: Show price ON = price dikhe, OFF = price chhup
    $('#label-print-show-price').on('change', function() {
        // Re-render from the same prepared job so modal + print stay in sync
        if (window._labelsToPrint) {
            window._labelsToPrint.showPrice = $(this).is(':checked');
            window._labelsToPrint.labels = (window._labelsToPrint.labels || []).map(function(l) {
                l.showPrice = window._labelsToPrint.showPrice;
                return l;
            });
            renderLabelPreview(window._labelsToPrint);
        } else {
            $('#label-print-modal-content').toggleClass('label-print-hide-price', !$(this).is(':checked'));
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
        var barcodeVal = $('#label-print-modal-content').data('single-item-id');
        var catName = $('#label-print-modal-content').data('single-category-name');
        var rowQty = $('#label-print-modal-content').data('single-row-qty');
        var coName = $('#label-print-modal-content').data('single-company-name');
        var itType = $('#label-print-modal-content').data('single-item-type');
        var qName = $('#label-print-modal-content').data('single-quality-name');
        var partNumData = $('#label-print-modal-content').data('single-part-number');
        var techName = $('#label-print-modal-content').data('single-technology-name');
        if (!name) return;
        var showPrice = $('#label-print-show-price').is(':checked');
        renderLabelPreview(buildLabelsToPrintFromSingle(name, rate, barcodeVal, qty, showPrice, catName, rowQty, coName, itType, qName, techName, partNumData));
    });

    // Single print button: jab koi selection ho to "Print selected" + QR icon; warna "Print All" + printer icon
    function updatePurchasePrintButton() {
        var $btn = $('#purchase-print-labels-btn');
        if (!$btn.length) return;
        var hasSelection = $('#items-tbody .purchase-row-verified-cb:checked').length > 0;
        if (hasSelection) {
            $btn.attr('title', 'Selected items ke labels print karein').html('<i class="ti ti-qrcode me-1"></i> Print selected');
        } else {
            $btn.attr('title', 'Sab items ke labels ek saath print').html('<i class="ti ti-printer me-1"></i> Print All');
        }
    }

    $(document).on('click', '#purchase-print-labels-btn', function(e) {
        e.preventDefault();
        if (window.isBillLocked) return;
        var $checked = $('#items-tbody .purchase-item-row').has('.purchase-row-verified-cb:checked');
        if ($checked.length > 0) {
            // Print selected
            var labels = [];
            var totalLabels = 0;
            $checked.each(function() {
                var rowId = $(this).data('row-id');
                var item = purchaseItems.find(function(i) { return i.id === rowId; });
                if (!item || (item.entry_type || 'purchase') === 'return') return;
                var qty = Math.max(1, Math.min(500, Math.round(parseFloat(item.quantity)) || 1));
                var name = (item.name || 'Item #' + item.item_id);
                var salePrice = getItemLabelSalePrice(item);
                var priceText = (salePrice > 0) ? ('Rs ' + Math.round(salePrice)) : '';
                var barcodeVal = item.bar_code || item.item_id || '';
                var lines = buildLabelLinesForItem(name, item.category_name, item.company_name, item.item_type, item.part_number);
                var showPrice = $('#label-print-show-price').is(':checked');
                for (var i = 0; i < qty; i++) {
                    labels.push({
                        line1: lines.line1,
                        line2: lines.line2,
                        rowQty: item.quantity,
                        priceText: priceText,
                        showPrice: showPrice,
                        barcode: barcodeVal,
                        itemType: (item.item_type || '').toString().trim().toLowerCase(),
                        qualityName: (item.quality_name != null) ? String(item.quality_name).trim() : '',
                        technologyName: (item.technology_name != null) ? String(item.technology_name).trim() : ''
                    });
                    totalLabels++;
                }
            });
            if (totalLabels === 0) return;
            var selectedJob = {
                preset: getActiveLabelPreset(),
                quantity: totalLabels,
                showPrice: $('#label-print-show-price').is(':checked'),
                labels: labels
            };
            renderLabelPreview(selectedJob);
            $('#label-print-count').text(totalLabels + ' label' + (totalLabels !== 1 ? 's' : '') + ' (selected)');
            $('#label-print-modal-content').removeData('single-name').removeData('single-rate').removeData('single-item-id')
                .removeData('single-category-name').removeData('single-row-qty').removeData('single-company-name')
                .removeData('single-item-type').removeData('single-quality-name').removeData('single-part-number').removeData('single-technology-name');
            $('#label-print-qty-wrap').addClass('d-none').removeClass('d-flex');
            var labelModalEl = document.getElementById('label-print-view-modal');
            if (labelModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(labelModalEl).show();
            } else {
                $('#label-print-view-modal').modal('show');
            }
        } else {
            // Print All
            if (!purchaseItems || purchaseItems.length === 0) {
                if (typeof toastr !== 'undefined') toastr.warning('Pehle cart mein items add karein.');
                else alert('Pehle cart mein items add karein.');
                return;
            }
            var labels = [];
            var totalLabels = 0;
            $('#items-tbody .purchase-item-row').each(function() {
                if ($(this).data('is-temporary') === 1) return;
                if ($(this).data('entry-type') === 'return') return;
                var rowId = $(this).data('row-id');
                var item = purchaseItems.find(function(i) { return i.id === rowId; });
                if (!item || (item.entry_type || 'purchase') === 'return') return;
                var qty = Math.max(1, Math.min(500, Math.round(parseFloat(item.quantity)) || 1));
                var name = (item.name || 'Item #' + item.item_id);
                var salePrice = getItemLabelSalePrice(item);
                var priceText = (salePrice > 0) ? ('Rs ' + Math.round(salePrice)) : '';
                var barcodeVal = item.bar_code || item.item_id || '';
                var lines = buildLabelLinesForItem(name, item.category_name, item.company_name, item.item_type, item.part_number);
                var showPrice = $('#label-print-show-price').is(':checked');
                for (var i = 0; i < qty; i++) {
                    labels.push({
                        line1: lines.line1,
                        line2: lines.line2,
                        rowQty: item.quantity,
                        priceText: priceText,
                        showPrice: showPrice,
                        barcode: barcodeVal,
                        itemType: (item.item_type || '').toString().trim().toLowerCase(),
                        qualityName: (item.quality_name != null) ? String(item.quality_name).trim() : '',
                        technologyName: (item.technology_name != null) ? String(item.technology_name).trim() : ''
                    });
                    totalLabels++;
                }
            });
            if (totalLabels === 0) {
                if (typeof toastr !== 'undefined') toastr.warning('Koi label nahi bani. Quantity check karein.');
                else alert('Koi label nahi bani. Quantity check karein.');
                return;
            }
            var allJob = {
                preset: getActiveLabelPreset(),
                quantity: totalLabels,
                showPrice: $('#label-print-show-price').is(':checked'),
                labels: labels
            };
            renderLabelPreview(allJob);
            $('#label-print-count').text(totalLabels + ' label' + (totalLabels !== 1 ? 's' : '') + ' (sab items)');
            $('#label-print-modal-content').removeData('single-name').removeData('single-rate').removeData('single-item-id')
                .removeData('single-category-name').removeData('single-row-qty').removeData('single-company-name')
                .removeData('single-item-type').removeData('single-quality-name').removeData('single-part-number').removeData('single-technology-name');
            $('#label-print-qty-wrap').addClass('d-none').removeClass('d-flex');
            var labelModalEl = document.getElementById('label-print-view-modal');
            if (labelModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(labelModalEl).show();
            } else {
                $('#label-print-view-modal').modal('show');
            }
        }
    });

    // Checkbox change / items load par print button label + icon update
    $(document).on('change', '#items-tbody .purchase-row-verified-cb', function() {
        updatePurchasePrintButton();
    });

    // Click row to edit (open add-item modal) — Checkbox / Print / Remove par click se modal NA kholen
    $(document).on('click', '#items-tbody .purchase-item-row', function(e) {
        if (window.isBillLocked) return;
        if ($(e.target).closest('.purchase-row-verified-cb').length) return;
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
        var editEntryType = (item.entry_type || 'purchase').toString();
        // Purchase: merge same item across warehouses for edit (purchase lines only — never mix return/claim rows).
        if (editEntryType === 'purchase') {
            purchaseItems.forEach(function(i) {
                if (String(i.item_id) !== String(item.item_id)) return;
                if (String(i.entry_type || 'purchase') !== 'purchase') return;
                if (i.warehouse_id == null || i.warehouse_id === '') return;
                var whId = String(i.warehouse_id);
                var q = parseFloat(i.quantity);
                if (!isNaN(q) && q > 0) quantitiesByWarehouse[whId] = Math.max(1, Math.min(1000, Math.round(q)));
            });
        } else {
            if (item.warehouse_id != null && item.warehouse_id !== '') {
                var qOne = parseFloat(item.quantity);
                if (!isNaN(qOne) && qOne > 0) {
                    quantitiesByWarehouse[String(item.warehouse_id)] = Math.max(1, Math.min(1000, Math.round(qOne)));
                }
            }
        }
        pendingEditItem = { warehouse_id: item.warehouse_id, warehouse_name: item.warehouse_name, quantity: item.quantity, rate: item.rate, retail_price: item.retail_price, retail_price_base: item.retail_price_base, retail_pct: item.retail_pct, unit: item.unit, discount: item.discount, tax_percentage: item.tax_percentage, warranty: item.warranty, quantities_by_warehouse: quantitiesByWarehouse, is_temporary: item.is_temporary, entry_type: editEntryType };
        $('#selected-item-id').val(item.item_id);
        $('#selected-item-is-temporary').val(item.is_temporary ? '1' : '0');
        $('#selected-item-bar-code').val(item.bar_code || '');
        $('#selected-item-image').val(item.image || '');
        $('#selected-item-images-json').val(JSON.stringify((item.images && Array.isArray(item.images)) ? item.images : []));
        if (item.image && String(item.image).trim()) {
            $('#item-search-image').attr('src', typeof normalizeItemImageUrl === 'function' ? normalizeItemImageUrl(item.image) : item.image);
            $('#item-search-image-preview').removeClass('d-none');
        } else {
            $('#item-search-image').attr('src', '');
            $('#item-search-image-preview').addClass('d-none');
        }
        var editName = ((editEntryType === 'claim_send' || editEntryType === 'claim') && item.name_full)
            ? String(item.name_full)
            : ((item.name || '').toString());
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
                var pct = parseFloat(pctForEdit);
                var taxPct = parseFloat(String($('#item-tax-percent').val()).replace(/%/g, '')) || 18;
                var rtaxPct = parseFloat($('#item-rtax-percent').val()) || 0.5;
                var factor0 = (1 + taxPct / 100) * (1 + rtaxPct / 100);
                if (pct === 0 || (pctForEdit + '').trim() === '') {
                    editRetail = Math.round(parseFloat(item.retail_price) / factor0);
                } else {
                    editRetail = Math.round(parseFloat(item.retail_price) / (factor0 - pct / 100));
                }
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
        var gstVal = parseFloat(item.tax_percentage);
        if ([12, 13, 14, 15, 16, 18].indexOf(gstVal) !== -1) $('#item-tax-percent').val(String(gstVal));
        else $('#item-tax-percent').val('18');
        $('#item-quantity').val(item.quantity != null ? item.quantity : '1');
        $('#item-quantity-input').val(item.quantity != null ? item.quantity : '1').hide();
        if (item.warranty) {
            var w = (item.warranty + '').trim().split(/\s+/);
            $('#warranty-value').val(w[0] || ''); $('#warranty-unit').val(w[1] || '');
        } else { $('#warranty-value').val(''); $('#warranty-unit').val(''); }
        currentEntryType = item.entry_type || 'purchase';
        addItemModalTitleKey = item.entry_type || 'purchase';
        // Pre-limit warehouse selection for single-warehouse claim / send / return rows so stock list does not auto-pick Display.
        if (['claim_send', 'claim', 'scrap', 'damage', 'return'].indexOf(editEntryType) >= 0) {
            if (item.warehouse_id != null && item.warehouse_id !== '') {
                $('#selected-warehouse-ids').val(String(item.warehouse_id));
                $('#selected-warehouse-id').val(String(item.warehouse_id));
            }
        }
        $('#add-item-modal').modal('show');
        loadItemDetails(item.item_id);
    });

    function resetItemModal() {
        editingRowId = null;
        pendingEditItem = null;
        itemBaseRetailPrice = null;
        $('#selected-item-master-sale-price').val('');
        $('#selected-item-category-name').val('');
        $('#selected-item-type').val('');
        $('#selected-item-quality-name').val('');
        $('#selected-item-part-number').val('');
        $('#selected-item-product-title').val('');
        $('#selected-item-product-type-label').val('');
        $('#selected-item-technology-name').val('');
        $('#selected-item-id').val('');
        $('#item-search').val('');
        $('#item-quantity').val('');
        $('#item-quantity-input').val('1').hide();
        $('#item-unit').val('');
        $('#item-rate').val('0');
        $('#item-retail-price').val('');
        $('#item-retail-percentage').val('');
        $('#selected-item-is-oil').val('');
        $('#selected-item-liter-per-can').val('');
        $('#item-per-liter-wrap').addClass('d-none');
        $('#item-per-liter-rate').val('');
        $('#item-tax-percent').val('18');
        $('#item-rtax-percent').val('0.5');
        updateRetailPctSelectColor();
        updateRateColumnByRetailPct();
        updateRetailAfterCalc();
        updateRetailColumnByRate();
        togglePurchaseItemWarrantySection(false);
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
        $('#stock-status-list-available-total').hide();
        $('#selected-item-details-display').addClass('d-none');
        $('#selected-item-details-line1').html('').removeClass('selected-product-line1--segments battery-type-sequence fw-bold text-uppercase').addClass('small');
        $('#selected-item-details-line2').html('').css('display', 'none');
        $('#selected-item-details-line3').html('');
        $('#selected-item-quality-wrap').html('').addClass('d-none');
        $('#item-edit-in-modal-btn').hide();
        $('#item-search-image-preview').addClass('d-none');
        $('#item-search-image').attr('src', '');
        $('#selected-item-image').val('');
        $('#selected-item-images-json').val('[]');
        $('#selected-item-voice-url').val('');
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
            discount = parseFloat($('#totalBillDiscount').val()) || parseFloat($('#discount').val()) || 0;
        }
        
        discount = Math.round(discount * 100) / 100;
        $('#discount').val(discount);
        if (discountType === 'percent') {
            $('#totalBillDiscount').val(discount);
        }

        let rentPaid = parseFloat($('#totalRentPaid').val());
        if (isNaN(rentPaid)) rentPaid = parseFloat($('#rent_paid').val()) || 0;
        rentPaid = Math.round(rentPaid * 100) / 100;
        if (rentPaid < 0) rentPaid = 0;
        $('#rent_paid').val(rentPaid);

        const shipping = parseFloat($('#shipping').val()) || 0;
        var chargeRentToSupplier = $('#chargeRentToSupplierToggle').length ? $('#chargeRentToSupplierToggle').is(':checked') : true;
        var rentAppliedToSupplierBill = (chargeRentToSupplier && rentPaid > 0) ? rentPaid : 0;

        const grossTotal = itemTotal;
        const grandTotal = itemTotal + orderTax - discount - rentAppliedToSupplierBill + shipping;

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
        // Set max payment amount to grand total + 1 (allow small rounding; if negative e.g. all scrap, use 0)
        const grandTotalValue = Math.max(0, parseFloat(grandTotal));
        const maxPaymentAllowed = grandTotalValue + 1;
        $('#payment_amount').attr('max', maxPaymentAllowed);
        const currentPaymentAmount = parseFloat($('#payment_amount').val()) || 0;
        if (currentPaymentAmount > maxPaymentAllowed) {
            $('#payment_amount').val(maxPaymentAllowed.toFixed(2));
        }
        // Update remaining amount
        updateRemainingAmount();
        // Subtotal row below table (total qty + total amount) and entry-type breakdown
        updateItemsSubtotal();
    }

    function updatePurchasePaymentFromInputs(sourceInput) {
        let cashTotal = 0;
        $('.purchase-cash-input').each(function() { cashTotal += parseFloat($(this).val()) || 0; });
        let bankTotal = 0;
        $('.purchase-bank-amt').each(function() { bankTotal += parseFloat($(this).val()) || 0; });
        let totalPaid = Math.round((cashTotal + bankTotal) * 100) / 100;
        const netPayable = parseFloat($('#grand-total').text().replace('Rs ', '').replace(/,/g, '')) || 0;
        $('#payment_amount').val(totalPaid.toFixed(2));
        let remaining = Math.round((netPayable - totalPaid) * 100) / 100;
        if (remaining >= 0 && remaining <= 1) remaining = 0;
        remaining = Math.max(0, remaining);
        $('#purchase-current-remaining').text(Math.round(remaining));
        var advanceAmount = totalPaid > netPayable ? Math.round((totalPaid - netPayable) * 100) / 100 : 0;
        var $remainingRow = $('#purchase-remaining-or-advance-row');
        var $advanceNotification = $('#purchase-advance-notification');
        var $advanceSpan = $('#purchase-advance-amount');
        var $warn = $('#purchase-payment-excess-warning');
        if (advanceAmount > 0) {
            $remainingRow.addClass('d-none');
            $advanceSpan.text(advanceAmount.toFixed(2));
            $advanceNotification.removeClass('d-none');
            $warn.addClass('d-none');
        } else {
            $remainingRow.removeClass('d-none');
            $advanceNotification.addClass('d-none');
            $warn.addClass('d-none');
        }
    }

    $(document).on('input change', '#totalBillDiscount', function() {
        $('#discount').val($(this).val() || 0);
        calculateTotals();
    });
    $(document).on('input change', '#totalRentPaid', function() {
        $('#rent_paid').val($(this).val() || 0);
        calculateTotals();
    });
    function updateRentChargeToSupplierHint() {
        if (!$('#chargeRentToSupplierToggle').length || !$('#rent-charge-to-supplier-hint').length) return;
        var on = $('#chargeRentToSupplierToggle').is(':checked');
        $('#rent-charge-to-supplier-hint').text(on
            ? 'Charged to supplier — included in net payable'
            : 'Not charged to supplier — internal record only');
    }
    $(document).on('change', '#chargeRentToSupplierToggle', function() {
        $('#charge_rent_to_supplier').val($(this).is(':checked') ? '1' : '0');
        updateRentChargeToSupplierHint();
        calculateTotals();
    });
    updateRentChargeToSupplierHint();
    // Focus: 0 clear karein taake likhne mein asaani ho; blur: khali ho to 0 set
    $('#totalBillDiscount').on('focus', function() {
        var v = parseFloat($(this).val());
        if (v === 0 || isNaN(v)) $(this).val('');
    }).on('blur', function() {
        var v = parseFloat($(this).val());
        if ($(this).val() === '' || isNaN(v) || v < 0) {
            $(this).val('0');
            $('#discount').val(0);
            calculateTotals();
        }
    });
    $('#totalRentPaid').on('focus', function() {
        var v = parseFloat($(this).val());
        if (v === 0 || isNaN(v)) $(this).val('');
    }).on('blur', function() {
        var v = parseFloat($(this).val());
        if ($(this).val() === '' || isNaN(v) || v < 0) {
            $(this).val('0');
            $('#rent_paid').val(0);
            calculateTotals();
        }
    });
    $(document).on('focusin', '#purchaseForm .purchase-cash-input', function() {
        var v = parseFloat($(this).val());
        if (v === 0 || isNaN(v)) $(this).val('');
    }).on('focusout', '#purchaseForm .purchase-cash-input', function() {
        var v = parseFloat($(this).val());
        if ($(this).val() === '' || isNaN(v) || v < 0) {
            $(this).val('0');
            updatePurchasePaymentFromInputs();
        }
    });
    $(document).on('focusin', '#purchaseForm .purchase-bank-amt', function() {
        var v = parseFloat($(this).val());
        if (v === 0 || isNaN(v)) $(this).val('');
    }).on('focusout', '#purchaseForm .purchase-bank-amt', function() {
        var v = parseFloat($(this).val());
        if ($(this).val() === '' || isNaN(v) || v < 0) {
            $(this).val('0');
            updatePurchasePaymentFromInputs();
        }
    });
    $('#purchase-add-cash-row').on('click', function() {
        var r = '<div class="border rounded p-2 mb-2 purchase-cash-entry-row" style="border-color:#bfdbfe;background:#f8fafc"><div class="d-flex justify-content-between align-items-center gap-2 flex-wrap"><span class="small fw-bold text-uppercase">Cash Entry</span><div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end min-w-0 purchase-payment-amount-rail"><div class="d-flex align-items-center border rounded-lg bg-white purchase-cash-amount-wrap" style="border-color:#e5e7eb !important;"><span class="purchase-cash-prefix text-uppercase">Rs</span><input type="number" class="form-control border-0 purchase-cash-input shadow-none" value="0" min="0" step="0.01"></div><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-cash-row flex-shrink-0"><i class="ti ti-x"></i></button></div></div><div class="mt-2"><label class="d-block border border-dashed rounded p-2 text-center small cursor-pointer purchase-cash-pic-label" style="border-color:#bfdbfe"><i class="ti ti-camera me-1"></i> Attach Photo<input type="file" accept="image/*" class="d-none purchase-cash-pic"></label><div class="purchase-attach-preview mt-2 d-none"></div></div></div>';
        $('#purchaseCashPaidWrapper').append(r);
    });
    $(document).on('click', '.remove-purchase-cash-row', function() {
        var $row = $(this).closest('.purchase-cash-entry-row');
        if (!$row.length) {
            $row = $(this).closest('.border.rounded.p-2');
        }
        $row.remove();
        updatePurchasePaymentFromInputs();
    });
    $(document).on('input', '.purchase-cash-input', function() { updatePurchasePaymentFromInputs(this); });

    window.purchaseBankAccounts = @json(\App\Models\BankAccount::where('status', true)->with('bank')->get()->map(function($a) { return ['id' => $a->id, 'label' => ($a->bank->name ?? 'N/A') . ' - ' . $a->account_title . ' (' . $a->account_number . ')']; }));
    $('#purchase-add-bank-row').on('click', function() {
        var opts = '<option value="">Select Bank</option>';
        (window.purchaseBankAccounts || []).forEach(function(b) { opts += '<option value="' + b.id + '">' + b.label + '</option>'; });
        var row = '<div class="border rounded p-2 mb-2 purchase-bank-row" style="border-color:#e9d5ff"><div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-1"><p class="mb-0" style="font-size: 10px; font-weight: 900; color: #9333ea; text-transform: uppercase;">Bank Entry</p><div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end min-w-0 purchase-payment-amount-rail"><div class="d-flex align-items-center border rounded-lg bg-white purchase-bank-amount-wrap" style="border-color:#e5e7eb !important;"><span class="purchase-bank-amount-prefix text-uppercase">Rs</span><input type="number" class="form-control border-0 purchase-bank-amt shadow-none" value="0" min="0" step="0.01" aria-label="Bank amount Rs"></div><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-bank-row flex-shrink-0" title="Remove"><i class="ti ti-x"></i></button></div></div><div class="purchase-bank-left-stack"><div class="purchase-bank-select-wrap"><select class="form-select form-select-sm purchase-bank-account">' + opts + '</select></div><div class="purchase-bank-trans-wrap"><label class="form-label small text-muted purchase-bank-trans-label" style="font-size: 0.7rem;">Trans ID</label><input type="text" class="form-control purchase-bank-ref" placeholder="Transaction ID"><small class="text-muted purchase-bank-trans-hint" style="font-size: 0.65rem;">Enter Transaction ID or attach transfer receipt.</small></div><div class="purchase-bank-receipt-wrap"><label class="d-block border border-dashed rounded text-center small cursor-pointer bg-white transition-all duration-300 purchase-bank-receipt-label" style="border-color:#e9d5ff"><span style="font-size: 8px; font-weight: 900; color: #9333ea; text-transform: uppercase;"><i class="ti ti-file-upload me-1"></i>Attach receipt (image or PDF)</span><input type="file" class="d-none purchase-bank-receipt" accept="image/*,.pdf" aria-label="Transfer receipt"></label><div class="purchase-attach-preview d-none"></div></div></div></div>';
        $('#purchaseBankPaidWrapper').append(row);
    });
    $(document).on('click', '.remove-purchase-bank-row', function() { $(this).closest('.purchase-bank-row').remove(); updatePurchasePaymentFromInputs(); });
    $(document).on('input', '.purchase-bank-amt', function() { updatePurchasePaymentFromInputs(this); });

    // Build preview for Attach Photo / Attach Receipt (image or PDF)
    function buildPurchaseAttachPreview(input) {
        var $input = $(input);
        var $label = $input.closest('label');
        var $preview = $label.next('.purchase-attach-preview');
        if (!$preview.length) return;
        var file = input.files && input.files[0];
        var oldUrl = $preview.data('object-url');
        if (oldUrl) {
            try { URL.revokeObjectURL(oldUrl); } catch (e) {}
            $preview.removeData('object-url');
        }
        $label.toggleClass('purchase-attach-has-file', !!file);
        if (!file) {
            $preview.addClass('d-none').empty();
            return;
        }
        var isPdf = (file.type || '').toLowerCase() === 'application/pdf' || (file.name || '').toLowerCase().endsWith('.pdf');
        var url = URL.createObjectURL(file);
        $preview.data('object-url', url);
        var name = file.name || 'File';
        var html = '';
        if (isPdf) {
            html = '<div class="d-flex align-items-center gap-2 p-2 rounded border bg-light flex-wrap">' +
                '<span class="text-danger"><i class="ti ti-file-type-pdf fs-24"></i></span>' +
                '<span class="small text-break flex-grow-1">' + name.replace(/</g, '&lt;') + '</span>' +
                '<div class="d-flex gap-1">' +
                '<button type="button" class="btn btn-sm btn-outline-primary purchase-attach-view-btn" title="View"><i class="ti ti-eye"></i> View</button>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary purchase-attach-replace-btn" title="Replace"><i class="ti ti-replace"></i> Replace</button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger purchase-attach-remove-btn" title="Remove"><i class="ti ti-trash"></i> Remove</button>' +
                '</div></div>';
        } else {
            html = '<div class="d-flex align-items-start gap-2 p-2 rounded border bg-light flex-wrap">' +
                '<a href="#" class="purchase-attach-view-btn d-block flex-shrink-0" style="max-width:80px;max-height:80px;"><img src="' + url + '" alt="Preview" class="img-thumbnail rounded" style="max-width:80px;max-height:80px;object-fit:cover;"></a>' +
                '<div class="flex-grow-1 small">' +
                '<div class="mb-1">' + name.replace(/</g, '&lt;') + '</div>' +
                '<div class="d-flex gap-1 flex-wrap">' +
                '<button type="button" class="btn btn-sm btn-outline-primary purchase-attach-view-btn"><i class="ti ti-eye me-1"></i>View</button>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary purchase-attach-replace-btn"><i class="ti ti-replace me-1"></i>Replace</button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger purchase-attach-remove-btn"><i class="ti ti-trash me-1"></i>Remove</button>' +
                '</div></div></div>';
        }
        $preview.html(html).removeClass('d-none');
    }

    // Show saved receipt (image or PDF) in preview when loading edit form
    function showExistingReceiptPreview($row, receiptUrl) {
        if (!receiptUrl || !$row.length) return;
        var $label = $row.find('.purchase-bank-receipt-label');
        var $preview = $row.find('.purchase-attach-preview');
        if (!$preview.length) return;
        $label.addClass('purchase-attach-has-file');
        $preview.data('existing-url', receiptUrl);
        var isPdf = /\.pdf$/i.test(receiptUrl);
        var name = receiptUrl.split('/').pop() || 'Receipt';
        var html;
        if (isPdf) {
            html = '<div class="d-flex align-items-center gap-2 p-2 rounded border bg-light flex-wrap">' +
                '<span class="text-danger"><i class="ti ti-file-type-pdf fs-24"></i></span>' +
                '<span class="small text-break flex-grow-1">' + name.replace(/</g, '&lt;') + ' (saved)</span>' +
                '<div class="d-flex gap-1">' +
                '<a href="' + receiptUrl.replace(/"/g, '&quot;') + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="View"><i class="ti ti-eye"></i> View</a>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary purchase-attach-replace-btn" title="Replace"><i class="ti ti-replace"></i> Replace</button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger purchase-attach-remove-btn" title="Remove"><i class="ti ti-trash"></i> Remove</button>' +
                '</div></div>';
        } else {
            html = '<div class="d-flex align-items-start gap-2 p-2 rounded border bg-light flex-wrap">' +
                '<a href="' + receiptUrl.replace(/"/g, '&quot;') + '" target="_blank" rel="noopener" class="d-block flex-shrink-0" style="max-width:80px;max-height:80px;"><img src="' + receiptUrl.replace(/"/g, '&quot;') + '" alt="Receipt" class="img-thumbnail rounded" style="max-width:80px;max-height:80px;object-fit:cover;"></a>' +
                '<div class="flex-grow-1 small">' +
                '<div class="mb-1">' + name.replace(/</g, '&lt;') + ' (saved)</div>' +
                '<div class="d-flex gap-1 flex-wrap">' +
                '<a href="' + receiptUrl.replace(/"/g, '&quot;') + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="ti ti-eye me-1"></i>View</a>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary purchase-attach-replace-btn"><i class="ti ti-replace me-1"></i>Replace</button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger purchase-attach-remove-btn"><i class="ti ti-trash me-1"></i>Remove</button>' +
                '</div></div></div>';
        }
        $preview.html(html).removeClass('d-none');
    }

    $(document).on('change', '.purchase-bank-receipt', function() {
        buildPurchaseAttachPreview(this);
    });
    $(document).on('change', '.purchase-cash-pic', function() {
        buildPurchaseAttachPreview(this);
    });

    $(document).on('click', '.purchase-attach-remove-btn', function(e) {
        e.preventDefault();
        var $preview = $(this).closest('.purchase-attach-preview');
        var $row = $preview.closest('.payment-card, .border.rounded.p-2, .purchase-bank-row');
        var $input = $row.find('.purchase-cash-pic, .purchase-bank-receipt')[0];
        var oldUrl = $preview.data('object-url');
        if (oldUrl) {
            try { URL.revokeObjectURL(oldUrl); } catch (err) {}
        }
        if ($input) {
            $input.value = '';
            $($input).closest('label').removeClass('purchase-attach-has-file');
        }
        $row.find('.purchase-bank-receipt-label, .purchase-cash-pic-label').removeClass('purchase-attach-has-file');
        $preview.addClass('d-none').empty().removeData('object-url').removeData('existing-url');
    });

    $(document).on('click', '.purchase-attach-replace-btn', function(e) {
        e.preventDefault();
        var $preview = $(this).closest('.purchase-attach-preview');
        var $row = $preview.closest('.payment-card, .border.rounded.p-2, .purchase-bank-row');
        var $input = $row.find('.purchase-cash-pic, .purchase-bank-receipt');
        if (!$input.length) return;
        var inputEl = $input[0];
        // Clear current value so programmatic click opens the file picker (browsers block click when input already has a file)
        inputEl.value = '';
        inputEl.click();
    });

    $(document).on('click', '.purchase-attach-view-btn', function(e) {
        e.preventDefault();
        var $preview = $(this).closest('.purchase-attach-preview');
        var url = $preview.data('object-url');
        if (!url) return;
        var $row = $preview.closest('.payment-card, .border.rounded.p-2, .purchase-bank-row');
        var $input = $row.find('.purchase-cash-pic, .purchase-bank-receipt')[0];
        var file = $input && $input.files && $input.files[0];
        var isPdf = file && ((file.type || '').toLowerCase() === 'application/pdf' || (file.name || '').toLowerCase().endsWith('.pdf'));
        if (isPdf) {
            window.open(url, '_blank', 'noopener');
        } else {
            $('#purchase-attach-image-full').attr('src', url);
            var modalEl = document.getElementById('purchase-attach-image-modal');
            if (!modalEl) return;
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    });

    // Ensure attach image preview modal closes: X button, backdrop, ESC
    (function() {
        var modalId = 'purchase-attach-image-modal';
        var modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        function hidePreviewModal() {
            var m = bootstrap.Modal.getInstance(modalEl);
            if (m) m.hide();
        }
        $(modalEl).on('click', '.purchase-attach-image-modal-close', function(e) {
            e.preventDefault();
            hidePreviewModal();
        });
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $(modalEl).hasClass('show')) {
                hidePreviewModal();
            }
        });
        $(modalEl).on('hidden.bs.modal', function() {
            $('#purchase-attach-image-full').attr('src', '');
        });
    })();

    function updateItemsSubtotal() {
        if (!purchaseItems || purchaseItems.length === 0) {
            $('#purchase-total-below').addClass('d-none').hide();
            return;
        }
        const byType = { purchase: { qty: 0, amount: 0 }, return: { qty: 0, amount: 0 }, claim: { qty: 0, amount: 0 }, claim_send: { qty: 0, amount: 0 }, damage: { qty: 0, amount: 0 }, scrap: { qty: 0, amount: 0 } };
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
        const typeLabels = { return: 'RETURN', claim: 'CLAIM', claim_send: 'SEND CLAIM', damage: 'DAMAGE', scrap: 'SCRAP SEND' };
        const typeClasses = { return: 'total-below-return', claim: 'total-below-return', claim_send: 'total-below-send-claim', damage: 'total-below-damage', scrap: 'total-below-scrap' };
        let claimHtml = '';
        ['return', 'claim', 'claim_send', 'damage', 'scrap'].forEach(function(et) {
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

    // Saved bill state: after Save & Print we freeze the form and full items section; show Edit Bill
    window.savedPurchaseId = null;
    window.isBillLocked = false;
    function freezeBillAfterSave(purchaseId, invoiceNo) {
        window.savedPurchaseId = purchaseId;
        window.isBillLocked = true;
        $('#saved_purchase_id').val(purchaseId);
        $('#purchase-saved-invoice-no').text(invoiceNo || ('#' + purchaseId));
        $('#purchase-saved-banner').removeClass('d-none');
        $('#purchaseForm').addClass('purchase-form-locked');
        $('#purchase-form-actions-create').addClass('d-none');
        $('#purchase-form-actions-saved').removeClass('d-none');
        var $form = $('#purchaseForm');
        $form.find('input:not(#saved_purchase_id), select, textarea').prop('disabled', true);
        $form.find('button[type="button"]').prop('disabled', true);
        $('#add-new-item-btn-above, #add-new-item-btn-barcode-row, #purchase-add-cash-row, #purchase-add-bank-row').prop('disabled', true);
        if (typeof $().select2 !== 'undefined') {
            if ($('#supplier_id').length && $('#supplier_id').data('select2')) $('#supplier_id').prop('disabled', true);
        }
        // Freeze full Items section: barcode, camera, temporary products, add/return buttons, item rows
        $('#purchase-items-section').addClass('purchase-items-section-locked');
        $('#purchase-page-barcode-input').prop('disabled', true);
        $('#purchase-open-camera-scan').prop('disabled', true);
        $('.open-temporary-product-modal-btn').prop('disabled', true).css('pointer-events', 'none');
        $('#claim-receive-btn').prop('disabled', true).css('pointer-events', 'none');
        $('#return-btn').css('pointer-events', 'none').addClass('disabled');
        $('#purchase-print-labels-btn').prop('disabled', true);
        $('#items-tbody').find('input, button, select, .remove-item').prop('disabled', true);
        $('#items-tbody').find('a').css('pointer-events', 'none').addClass('disabled');
    }

    $('#btnEditBill').on('click', function(e) {
        e.preventDefault();
        var id = $('#saved_purchase_id').val() || window.savedPurchaseId;
        if (!id) return;
        window.location.href = '{{ url("purchases") }}/' + id + '/edit';
    });

    $('#btnSaveAndPrint').on('click', function() {
        if ($('#saved_purchase_id').val()) return;
        $('#save_and_print').val('1');
        $('#save_and_send_pdf').val('0');
        $('#print_format').val('a4');
        $('#purchaseForm').submit();
    });

    $('#btnUpdateAndPrint').on('click', function(e) {
        e.preventDefault();
        $('#save_and_print').val('1');
        $('#save_and_send_pdf').val('0');
        $('#save_and_new').val('0');
        $('#print_format').val($('#print_format').val() || 'a4');
        $('#purchaseForm').trigger('submit');
    });

    // Ensure Update Bill / Update PO always triggers submit handler
    $('#btnUpdateBill').on('click', function(e) {
        e.preventDefault();
        $('#save_and_print').val('0');
        $('#save_and_send_pdf').val('0');
        $('#save_and_new').val('0');
        $('#purchaseForm').trigger('submit');
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

    // Form submission (create and edit mode)
    $('#purchaseForm').on('submit', function(e) {
        e.preventDefault();
        var isEditMode = !!$('#saved_purchase_id').val();

        if (purchaseItems.length === 0) {
            alert('Please add at least one item');
            return false;
        }

        // Claim Send flow validation: supplier is mandatory and qty must be valid.
        var hasClaimSendItems = purchaseItems.some(function(it) {
            return (it && (it.entry_type || '') === 'claim_send');
        });
        if (hasClaimSendItems) {
            var supplierId = ($('#supplier_id').val() || '').toString().trim();
            if (!supplierId) {
                alert('Please select supplier before sending claim stock.');
                $('#supplier_id').focus();
                return false;
            }
            console.debug('Claim stock submit check', {
                stock_type: 'claim',
                items: purchaseItems.filter(function(it) { return it && (it.entry_type || '') === 'claim_send'; }).map(function(it) {
                    return {
                        item_id: it.item_id,
                        barcode: it.bar_code || '',
                        warehouse_id: it.warehouse_id || '',
                        available_stock: (it.available_claim_qty != null ? it.available_claim_qty : null),
                        required_qty: it.quantity
                    };
                })
            });
            var invalidClaimSendQty = purchaseItems.some(function(it) {
                if (!it || (it.entry_type || '') !== 'claim_send') return false;
                var q = parseFloat(it.quantity);
                return isNaN(q) || q <= 0;
            });
            if (invalidClaimSendQty) {
                alert('Claim Send quantity must be greater than 0.');
                return false;
            }

            // Prevent backend "Insufficient CLAIM stock" by validating aggregated qty
            // per warehouse+item using latest available qty captured from claim history rows.
            var groupedSend = {};
            purchaseItems.forEach(function(it) {
                if (!it || (it.entry_type || '') !== 'claim_send') return;
                var wh = (it.warehouse_id != null && it.warehouse_id !== '') ? parseInt(it.warehouse_id, 10) : 0;
                var iid = (it.item_id != null && it.item_id !== '') ? parseInt(it.item_id, 10) : 0;
                if (!wh || !iid) return;
                var key = wh + ':' + iid;
                if (!groupedSend[key]) groupedSend[key] = { required: 0, available: null, itemName: (it.name || ('Item #' + iid)) };
                groupedSend[key].required += Math.abs(parseFloat(it.quantity) || 0);
                var av = parseFloat(it.available_claim_qty);
                if (!isNaN(av)) {
                    groupedSend[key].available = (groupedSend[key].available == null) ? av : Math.max(groupedSend[key].available, av);
                }
            });

            var availabilityErrors = [];
            Object.keys(groupedSend).forEach(function(key) {
                var g = groupedSend[key];
                if (g.available == null) return; // if unknown, let backend be source of truth
                if ((g.required - g.available) > 0.000001) {
                    availabilityErrors.push(
                        (g.itemName || 'Item') + ' (required: ' + g.required + ', available: ' + g.available + ')'
                    );
                }
            });
            if (availabilityErrors.length > 0) {
                alert('Claim send quantity exceeds available claim stock for:\n- ' + availabilityErrors.join('\n- '));
                return false;
            }
        }
        
        var saveAndPrint = $('#save_and_print').val() === '1';
        var saveAndSendPdf = $('#save_and_send_pdf').val() === '1';
        var saveAndNew = $('#save_and_new').val() === '1';
        var grandTotalRaw = parseFloat($('#grand-total').text().replace('Rs ', '').replace(/,/g, '')) || 0;
        var grandTotal = Math.round(grandTotalRaw * 100) / 100;
        
        // Payment validation: skip when Save & Print, Send PDF, or Save & New (allow save without payment)
        if (!saveAndPrint && !saveAndSendPdf && !saveAndNew) {
            // In edit mode, user may already have cash/bank rows filled,
            // while hidden #payment_amount may still be 0. So we only validate
            // #payment_amount when no cash/bank amounts are entered.
            const hasAnyCashEntered = $('.purchase-cash-input').toArray().some(function(el) {
                return (parseFloat($(el).val()) || 0) > 0;
            });
            const hasAnyBankEntered = $('.purchase-bank-amt').toArray().some(function(el) {
                return (parseFloat($(el).val()) || 0) > 0;
            });

            if (!hasAnyCashEntered && !hasAnyBankEntered) {
                // In edit mode, it's valid to save/update bill with 0 paid amount
                // (remaining due / advance flow handled by backend).
                if (!isEditMode) {
                    const paymentMethod = $('#payment_method_id').val();
                    const paymentAmountRaw = parseFloat($('#payment_amount').val()) || 0;
                    const paymentAmount = Math.round(paymentAmountRaw * 100) / 100;

                    if (paymentMethod && paymentAmount <= 0) {
                        alert('Please enter a valid payment amount.');
                        $('#payment_amount').focus();
                        return false;
                    }

                    var paymentTolerance = 1;
                    if (paymentAmount > grandTotal + paymentTolerance) {
                        alert('Total payment (Rs ' + paymentAmount.toFixed(2) + ') exceeds grand total (Rs ' + grandTotal.toFixed(2) + ') by more than Rs 1. Please adjust the payment amount.');
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
            }
        }

        // Prepare items data (include warehouse_id, entry_type, retail_price, purchase_order_item_id for PO received qty update)
        // Use quantity from table input when present so PO received qty is always what user entered
        const itemsData = purchaseItems.map(function(item, index) {
            var $row = $('#items-tbody .purchase-item-row').eq(index);
            var qtyFromInput = $row.find('.purchase-row-qty-input').val();
            var quantity = (qtyFromInput !== undefined && qtyFromInput !== '' && !isNaN(parseFloat(qtyFromInput)))
                ? parseFloat(qtyFromInput) : (parseFloat(item.quantity) || 0);
            var verified = $row.find('.purchase-row-verified-cb').is(':checked');
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
                verified: verified ? 1 : 0,
                demand_user_name: (item.demand_user_name != null && String(item.demand_user_name).trim() !== '') ? String(item.demand_user_name).trim() : ''
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
        $('#rent_paid').val($('#totalRentPaid').val() || 0);
        if ($('#chargeRentToSupplierToggle').length && $('#charge_rent_to_supplier').length) {
            $('#charge_rent_to_supplier').val($('#chargeRentToSupplierToggle').is(':checked') ? '1' : '0');
        }
        updatePurchasePaymentFromInputs();

        // Build payments array: cash rows + bank rows (same structure for both; backend uses payments_json)
        const cashMethodId = $('#purchase_cash_method_id').val();
        const bankMethodId = $('#purchase_bank_method_id').val();
        const payments = [];
        $('.purchase-cash-input').each(function() {
            const amt = parseFloat($(this).val()) || 0;
            var $row = $(this).closest('.payment-card, .border.rounded.p-2');
            var paymentId = ($row.length && $row.attr('data-payment-id')) ? $row.attr('data-payment-id') : '';
            if (amt > 0 && cashMethodId) payments.push({ payment_method_id: cashMethodId, amount: amt, bank_account_id: '', transaction_id: '', payment_id: paymentId || null });
        });
        // Validate bank entries: Select Bank is compulsory when amount is entered
        var bankSelectMissing = false;
        $('.purchase-bank-row').each(function() {
            const amt = parseFloat($(this).find('.purchase-bank-amt').val()) || 0;
            const bankId = $(this).find('.purchase-bank-account').val();
            if (amt > 0 && (!bankId || bankId === '')) {
                bankSelectMissing = true;
                return false;
            }
        });
        if (bankSelectMissing) {
            alert('Please select Bank / Account for each bank entry where amount is entered.');
            $('.purchase-bank-row').each(function() {
                const amt = parseFloat($(this).find('.purchase-bank-amt').val()) || 0;
                const bankId = $(this).find('.purchase-bank-account').val();
                if (amt > 0 && (!bankId || bankId === '')) {
                    $(this).find('.purchase-bank-account').focus();
                    return false;
                }
            });
            return false;
        }
        // Validate: each bank entry must have EITHER Transaction ID OR Attach receipt (compulsory one of the two)
        // Existing rows (with data-payment-id) already have saved receipt on server, so skip for them
        var bankRefOrReceiptMissing = false;
        $('.purchase-bank-row').each(function() {
            const amt = parseFloat($(this).find('.purchase-bank-amt').val()) || 0;
            const bankId = $(this).find('.purchase-bank-account').val();
            const ref = ($(this).find('.purchase-bank-ref').val() || '').trim();
            const receiptInput = $(this).find('.purchase-bank-receipt')[0];
            const hasReceipt = receiptInput && receiptInput.files && receiptInput.files.length > 0;
            var paymentId = $(this).attr('data-payment-id') || '';
            var isExistingRow = paymentId && paymentId.length > 0;
            if (amt > 0 && bankId && bankMethodId) {
                if (!isExistingRow && !ref && !hasReceipt) {
                    bankRefOrReceiptMissing = true;
                    return false;
                }
                payments.push({ payment_method_id: bankMethodId, amount: amt, bank_account_id: bankId, transaction_id: ref, payment_id: paymentId || null });
            }
        });
        if (bankRefOrReceiptMissing) {
            alert('For each bank entry, you must fill one of the following:\n\n• Enter Transaction ID\n• OR Attach receipt (image or PDF)\n\nPlease complete at least one for every bank row.');
            $('.purchase-bank-row').each(function() {
                const amt = parseFloat($(this).find('.purchase-bank-amt').val()) || 0;
                const bankId = $(this).find('.purchase-bank-account').val();
                const ref = $(this).find('.purchase-bank-ref').val();
                const receiptInput = $(this).find('.purchase-bank-receipt')[0];
                const hasReceipt = receiptInput && receiptInput.files && receiptInput.files.length > 0;
                var paymentId = $(this).attr('data-payment-id') || '';
                var isExistingRow = paymentId && paymentId.length > 0;
                if (amt > 0 && bankId && !isExistingRow && !(ref && ref.trim()) && !hasReceipt) {
                    $(this).find('.purchase-bank-ref').focus();
                    return false;
                }
            });
            return false;
        }
        if (payments.length === 0 && parseFloat($('#payment_amount').val()) > 0) {
            payments.push({ payment_method_id: $('#payment_method_id').val(), amount: $('#payment_amount').val(), bank_account_id: $('#bank_account_id').val() || '', transaction_id: $('#payment_transaction_id').val() || '', payment_id: null });
        }

        // Total payment can exceed amount due; excess is saved as advance
        var totalPaymentSum = 0;
        payments.forEach(function(p) { totalPaymentSum += parseFloat(p.amount) || 0; });
        totalPaymentSum = Math.round(totalPaymentSum * 100) / 100;

        var paymentsJson = JSON.stringify(payments);

        // Add items to form
        const formData = new FormData(this);
        if (isEditMode) {
            formData.append('_method', 'PUT');
        }
        formData.append('payments_json', paymentsJson);
        itemsData.forEach(function(item, index) {
            Object.keys(item).forEach(function(key) {
                formData.append(`items[${index}][${key}]`, item[key]);
            });
        });
        var bankRowIndex = 0;
        payments.forEach(function(p, i) {
            formData.append(`payments[${i}][payment_method_id]`, p.payment_method_id);
            formData.append(`payments[${i}][amount]`, p.amount);
            formData.append(`payments[${i}][bank_account_id]`, p.bank_account_id || '');
            formData.append(`payments[${i}][transaction_id]`, p.transaction_id || '');
            formData.append(`payments[${i}][payment_id]`, (p.payment_id != null && p.payment_id !== '') ? String(p.payment_id) : '');
            if (p.bank_account_id) {
                var $row = $('.purchase-bank-row').eq(bankRowIndex);
                var receiptInput = $row.find('.purchase-bank-receipt')[0];
                if (receiptInput && receiptInput.files && receiptInput.files.length > 0) {
                    formData.append(`payments[${i}][transfer_receipt]`, receiptInput.files[0]);
                }
                bankRowIndex++;
            }
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
                console.log(isEditMode ? 'Purchase updated successfully:' : 'Purchase created successfully:', response);
                if (response.success) {
                    var saveAndPrint = $('#save_and_print').val() === '1';
                    var saveAndSendPdf = $('#save_and_send_pdf').val() === '1';
                    var saveAndNew = $('#save_and_new').val() === '1';
                    $('#save_and_print').val('0');
                    $('#save_and_send_pdf').val('0');
                    $('#save_and_new').val('0');
                    var purchaseId = response.purchase_id || $('#saved_purchase_id').val();
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
                            alert((isEditMode ? 'Purchase updated! ' : 'Purchase saved! ') + 'WhatsApp opened with PDF link. Send the message to the supplier. Invoice: ' + (response.invoice_no || ''));
                        } else {
                            alert((isEditMode ? 'Purchase updated! ' : 'Purchase saved! ') + 'Copy this link to send to supplier:\n' + response.signed_pdf_url);
                        }
                    } else if (saveAndPrint && purchaseId) {
                        var printFormat = ($('#print_format').val() || 'a4').toLowerCase();
                        var printUrl = '{{ url("purchases") }}/' + purchaseId + '?print=1&format=' + encodeURIComponent(printFormat);
                        window.open(printUrl, 'purchase_print', 'width=800,height=700,scrollbars=yes');
                        if (typeof freezeBillAfterSave === 'function') freezeBillAfterSave(purchaseId, response.invoice_no || ('#' + purchaseId));
                        window.location.href = '{{ route("all_purchases") }}';
                    } else if (saveAndNew && !isEditMode) {
                        alert('Purchase created successfully! Invoice: ' + (response.invoice_no || ''));
                        window.location.href = '{{ route("purchases.create") }}';
                    } else {
                        alert(isEditMode ? 'Purchase updated successfully! Invoice: ' + (response.invoice_no || '') : 'Purchase created successfully! Invoice: ' + (response.invoice_no || ''));
                        purchaseItems = [];
                        $('#items-tbody').empty();
                        $('#empty-items-state').show();
                        $('#items-list').hide();
                        $('#payment-amount-row').hide();
                        calculateTotals();
                        window.location.href = '{{ route("all_purchases") }}';
                    }
                } else {
                    alert(response.message || (isEditMode ? 'Purchase update failed.' : 'Purchase created but with warnings.'));
                    window.location.href = '{{ route("all_purchases") }}';
                }
            },
            error: function(xhr) {
                console.error(isEditMode ? 'Purchase update error:' : 'Purchase creation error:', xhr);
                let errorMessage = isEditMode ? 'Error updating purchase. Please try again.' : 'Error saving purchase. Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) errorMessage = xhr.responseJSON.message;
                    else if (xhr.responseJSON.errors) errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseText) errorMessage = xhr.responseText;
                alert(errorMessage);
            }
        });
    });

    // Edit mode: load existing purchase items (and optionally payments) from __purchaseEditData
    if (window.__purchaseEditData && window.__purchaseEditData.items && window.__purchaseEditData.items.length > 0) {
        var data = window.__purchaseEditData;
        if (data.purchase && data.purchase.rent_paid != null && data.purchase.rent_paid !== '') {
            var rpEdit = parseFloat(data.purchase.rent_paid);
            if (!isNaN(rpEdit)) {
                $('#totalRentPaid').val(rpEdit);
                $('#rent_paid').val(rpEdit);
            }
        }
        if (data.purchase && $('#chargeRentToSupplierToggle').length && $('#charge_rent_to_supplier').length) {
            var cr = (typeof data.purchase.charge_rent_to_supplier === 'undefined') ? true : !!data.purchase.charge_rent_to_supplier;
            $('#chargeRentToSupplierToggle').prop('checked', cr);
            $('#charge_rent_to_supplier').val(cr ? '1' : '0');
            if (typeof updateRentChargeToSupplierHint === 'function') updateRentChargeToSupplierHint();
        }
        $('#items-tbody').empty();
        purchaseItems = [];
        var maxId = 0;
        data.items.forEach(function(it) {
            var et = it.entry_type || 'purchase';
            var nm = it.name || ('Item #' + it.item_id);
            var nameFullEdit = it.name_full || (et === 'claim_send' && nm && /[•·]/.test(String(nm)) ? String(nm).trim() : null);
            var item = {
                id: it.id,
                item_id: it.item_id,
                name: nm,
                name_full: nameFullEdit,
                warehouse_id: it.warehouse_id || null,
                warehouse_name: it.warehouse_name || null,
                quantity: parseFloat(it.quantity) || 0,
                unit: it.unit || 'Unit',
                rate: parseFloat(it.rate) || 0,
                discount: parseFloat(it.discount) || 0,
                tax_percentage: parseFloat(it.tax_percentage) || 0,
                tax_amount: parseFloat(it.tax_amount) || 0,
                total: parseFloat(it.total) || 0,
                sale_price: it.sale_price != null && it.sale_price !== '' && !isNaN(parseFloat(it.sale_price))
                    ? parseFloat(it.sale_price)
                    : null,
                item_master_sale_price: it.item_master_sale_price != null && it.item_master_sale_price !== '' && !isNaN(parseFloat(it.item_master_sale_price))
                    ? parseFloat(it.item_master_sale_price)
                    : null,
                item_master_retail_price: it.item_master_retail_price != null && it.item_master_retail_price !== '' && !isNaN(parseFloat(it.item_master_retail_price))
                    ? parseFloat(it.item_master_retail_price)
                    : null,
                total_sale_price: it.total_sale_price != null && it.total_sale_price !== '' && !isNaN(parseFloat(it.total_sale_price))
                    ? parseFloat(it.total_sale_price)
                    : null,
                sale_price_per_base: it.sale_price_per_base != null && it.sale_price_per_base !== '' && !isNaN(parseFloat(it.sale_price_per_base))
                    ? parseFloat(it.sale_price_per_base)
                    : null,
                retail_price: it.retail_price != null ? parseFloat(it.retail_price) : null,
                category_name: (it.category_name != null && String(it.category_name).trim() !== '') ? String(it.category_name).trim() : null,
                entry_type: it.entry_type || 'purchase',
                verified: it.verified ? 1 : 0,
                image: it.image || null,
                image_path: it.image_path || null,
                images: (it.images && Array.isArray(it.images) && it.images.length) ? it.images.slice() : null,
                voice_url: (it.voice_url && String(it.voice_url).trim()) ? String(it.voice_url).trim() : null
            };
            purchaseItems.push(item);
            maxId = Math.max(maxId, it.id);
        });
        itemCounter = maxId + 1;
        purchaseItems.forEach(function(item) { addItemToTable(item); });
        $('#empty-items-state').hide();
        $('#items-list').show();
        $('#payment-amount-row').show();
        calculateTotals();
        if (typeof updatePurchaseBulkRetailBar === 'function') updatePurchaseBulkRetailBar();
        if (typeof updatePurchasePrintButton === 'function') updatePurchasePrintButton();
        if (typeof updatePurchaseTableRetailColumnVisibility === 'function') updatePurchaseTableRetailColumnVisibility();
        if (data.purchase && data.purchase.supplier_mobile && $('#supplier_mobile').length) {
            $('#supplier_mobile').val(data.purchase.supplier_mobile);
        }
    }

    // Edit mode: load saved payment entries (cash + bank) from __purchaseEditData.payments
    if (window.__purchaseEditData && window.__purchaseEditData.payments && Array.isArray(window.__purchaseEditData.payments)) {
        var payments = window.__purchaseEditData.payments;
        var cashPayments = payments.filter(function(p) { return p.is_cash; });
        var bankPayments = payments.filter(function(p) { return !p.is_cash; });
        var cashWrapper = $('#purchaseCashPaidWrapper');
        var bankWrapper = $('#purchaseBankPaidWrapper');
        cashWrapper.empty();
        cashPayments.forEach(function(p) {
            var amt = (p.amount != null && p.amount !== '') ? parseFloat(p.amount) : 0;
            var safeAmt = isNaN(amt) ? 0 : amt;
            var row = '<div class="border rounded p-2 mb-2 purchase-cash-entry-row" style="border-color:#bfdbfe;background:#f8fafc"><div class="d-flex justify-content-between align-items-center gap-2 flex-wrap"><span class="small fw-bold text-uppercase">Cash Entry</span><div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end min-w-0 purchase-payment-amount-rail"><div class="d-flex align-items-center border rounded-lg bg-white purchase-cash-amount-wrap" style="border-color:#e5e7eb !important;"><span class="purchase-cash-prefix text-uppercase">Rs</span><input type="number" class="form-control border-0 purchase-cash-input shadow-none" value="' + safeAmt + '" min="0" step="0.01"></div><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-cash-row flex-shrink-0"><i class="ti ti-x"></i></button></div></div><div class="mt-2"><label class="d-block border border-dashed rounded p-2 text-center small cursor-pointer purchase-cash-pic-label" style="border-color:#bfdbfe"><i class="ti ti-camera me-1"></i> Attach Photo<input type="file" accept="image/*" class="d-none purchase-cash-pic"></label><div class="purchase-attach-preview mt-2 d-none"></div></div></div>';
            cashWrapper.append(row);
        });
        if (cashPayments.length === 0) {
            var defaultCash = '<div class="border rounded p-2 mb-2 purchase-cash-entry-row" style="border-color:#bfdbfe;background:#f8fafc"><div class="d-flex justify-content-between align-items-center gap-2 flex-wrap"><span class="small fw-bold text-uppercase">Cash Entry</span><div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end min-w-0 purchase-payment-amount-rail"><div class="d-flex align-items-center border rounded-lg bg-white purchase-cash-amount-wrap" style="border-color:#e5e7eb !important;"><span class="purchase-cash-prefix text-uppercase">Rs</span><input type="number" class="form-control border-0 purchase-cash-input shadow-none" value="0" min="0" step="0.01"></div><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-cash-row flex-shrink-0"><i class="ti ti-x"></i></button></div></div><div class="mt-2"><label class="d-block border border-dashed rounded p-2 text-center small cursor-pointer purchase-cash-pic-label" style="border-color:#bfdbfe"><i class="ti ti-camera me-1"></i> Attach Photo<input type="file" accept="image/*" class="d-none purchase-cash-pic"></label><div class="purchase-attach-preview mt-2 d-none"></div></div></div>';
            cashWrapper.append(defaultCash);
        }
        bankWrapper.empty();
        var bankOpts = '<option value="">Select Bank</option>';
        (window.purchaseBankAccounts || []).forEach(function(b) { bankOpts += '<option value="' + b.id + '">' + (b.label || '').replace(/"/g, '&quot;') + '</option>'; });
        bankPayments.forEach(function(p) {
            var amt = (p.amount != null && p.amount !== '') ? parseFloat(p.amount) : 0;
            var safeAmt = isNaN(amt) ? 0 : amt;
            var ref = (p.transaction_id || '').replace(/"/g, '&quot;').replace(/</g, '&lt;');
            var paymentIdAttr = (p.payment_id != null && p.payment_id !== '') ? ' data-payment-id="' + p.payment_id + '"' : '';
            var row = '<div class="border rounded p-2 mb-2 purchase-bank-row"' + paymentIdAttr + ' style="border-color:#e9d5ff"><div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-1"><p class="mb-0" style="font-size: 10px; font-weight: 900; color: #9333ea; text-transform: uppercase;">Bank Entry</p><div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end min-w-0 purchase-payment-amount-rail"><div class="d-flex align-items-center border rounded-lg bg-white purchase-bank-amount-wrap" style="border-color:#e5e7eb !important;"><span class="purchase-bank-amount-prefix text-uppercase">Rs</span><input type="number" class="form-control border-0 purchase-bank-amt shadow-none" value="' + safeAmt + '" min="0" step="0.01" aria-label="Bank amount Rs"></div><button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-bank-row flex-shrink-0" title="Remove"><i class="ti ti-x"></i></button></div></div><div class="purchase-bank-left-stack"><div class="purchase-bank-select-wrap"><select class="form-select form-select-sm purchase-bank-account">' + bankOpts + '</select></div><div class="purchase-bank-trans-wrap"><label class="form-label small text-muted purchase-bank-trans-label" style="font-size: 0.7rem;">Trans ID</label><input type="text" class="form-control purchase-bank-ref" placeholder="Transaction ID" value="' + ref + '"><small class="text-muted purchase-bank-trans-hint" style="font-size: 0.65rem;">Enter Transaction ID or attach transfer receipt.</small></div><div class="purchase-bank-receipt-wrap"><label class="d-block border border-dashed rounded text-center small cursor-pointer bg-white transition-all duration-300 purchase-bank-receipt-label" style="border-color:#e9d5ff"><span style="font-size: 8px; font-weight: 900; color: #9333ea; text-transform: uppercase;"><i class="ti ti-file-upload me-1"></i>Attach receipt (image or PDF)</span><input type="file" class="d-none purchase-bank-receipt" accept="image/*,.pdf" aria-label="Transfer receipt"></label><div class="purchase-attach-preview d-none"></div></div></div></div>';
            bankWrapper.append(row);
            var $newRow = bankWrapper.find('.purchase-bank-row').last();
            if (p.bank_account_id) $newRow.find('.purchase-bank-account').val(String(p.bank_account_id));
            if (p.transfer_receipt_url && typeof showExistingReceiptPreview === 'function') showExistingReceiptPreview($newRow, p.transfer_receipt_url);
        });
        if (typeof updatePurchasePaymentFromInputs === 'function') updatePurchasePaymentFromInputs();
    }

    // Initialize date picker
    if ($('#purchase_date').length) {
        $('#purchase_date').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true
        });
    }

    // After closing bill (X) with return_url: land on purchase page with ?reopen_add_item_modal=1 and reopen add-item modal + context
    (function initReopenAddItemModalFromBill() {
        try {
            var params = new URLSearchParams(window.location.search);
            if (params.get('reopen_add_item_modal') !== '1') return;
            params.delete('reopen_add_item_modal');
            var newSearch = params.toString();
            var newUrl = window.location.pathname + (newSearch ? ('?' + newSearch) : '') + window.location.hash;
            window.history.replaceState({}, '', newUrl);

            var raw = sessionStorage.getItem('purchaseModalBillReturn');
            var st = null;
            if (raw) {
                try {
                    st = JSON.parse(raw);
                    sessionStorage.removeItem('purchaseModalBillReturn');
                } catch (e2) {
                    st = null;
                }
            }

            window._restorePurchaseModalFromBill = true;
            window._purchaseModalBillReturnState = st;
            $('#add-item-modal').modal('show');
        } catch (e) { /* ignore */ }
    })();
});
</script>
@endpush
@endsection
