@extends('layouts.app')

@section('title', 'Create Purchase')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Purchase</h4>
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
                                        @if(session('selected_branch_code'))
                                            <span id="selectedBranchCode"> ({{ session('selected_branch_code') }})</span>
                                        @endif
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
                                                @if($branch->branch_code) ({{ $branch->branch_code }}) @endif
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
                                        <h4 class="mb-0 fw-bold">Barki Express</h4>
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
                                <select name="supplier_id" id="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required style="border-radius: 6px;">
                                    <option value="">Select vendor</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                                data-name="{{ $supplier->names[0] ?? '' }}" 
                                                data-phone="{{ $supplier->phones[0] ?? '' }}"
                                                data-company="{{ $supplier->company ?? '' }}"
                                                data-address="{{ $supplier->address ?? '' }}"
                                                data-area="{{ $supplier->area ?? '' }}">
                                            {{ $supplier->names[0] ?? 'N/A' }}@if($supplier->company) - {{ $supplier->company }}@endif @if(!empty($supplier->phones[0])) - {{ $supplier->phones[0] }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3 position-relative">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">MOBILE NUMBER</label>
                                <input type="hidden" id="supplier_mobile" name="supplier_mobile" value="">
                                <div id="supplier_mobile_select2_trigger" class="supplier-mobile-select2-trigger position-relative" role="combobox" aria-haspopup="listbox" aria-expanded="false" title="Select vendor">
                                    <span id="supplier_mobile_display" class="supplier-mobile-selection-rendered placeholder" role="textbox" aria-readonly="true" title="Select vendor">SELECT VENDOR</span>
                                    <span class="supplier-mobile-select2-arrow" aria-hidden="true"><i class="ti ti-chevron-down"></i></span>
                                </div>
                                <div id="supplier_mobile_dropdown" class="supplier-mobile-select2-dropdown" style="display: none;">
                                    <div class="select2-search select2-search--dropdown">
                                        <input type="text" id="supplier_mobile_search" class="select2-search__field" placeholder="Search by number or name..." autocomplete="off" role="searchbox">
                                    </div>
                                    <div id="supplier_mobile_results" class="select2-results select2-results__options" style="max-height: 200px; overflow-y: auto;"></div>
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
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead id="purchaseItemsThead">
                                                <tr>
                                                    <th>Warehouse</th>
                                                    <th>Item</th>
                                                    <th class="d-none d-md-table-cell">Qty</th>
                                                    <th class="d-none d-md-table-cell">Unit</th>
                                                    <th class="d-none d-md-table-cell">Rate</th>
                                                    <th class="d-none d-md-table-cell">Discount</th>
                                                    <th class="d-none d-md-table-cell">Tax %</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-tbody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Summary -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">GROSS AMOUNT</span>
                                    <span class="fw-bold" id="gross-amount">Rs 0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">PREVIOUS BALANCE</span>
                                    <span class="fw-bold" id="previous-balance">Rs 0</span>
                                </div>
                                <div class="bg-primary text-white p-3 rounded mb-3" id="purchaseGrandTotalPanel">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold fs-16">GRAND TOTAL</div>
                                            <div class="small">Total Payable Amount</div>
                                        </div>
                                        <div class="fw-bold fs-24" id="grand-total">Rs 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Item, Scrap In, Claim Receive & Return -->
                        <div class="text-center mb-4 d-flex flex-wrap justify-content-center align-items-center gap-3">
                            <button type="button" class="btn btn-action-purchase btn-lg" id="add-new-item-btn">
                                <i class="ti ti-plus me-2"></i>PURCHASE ITEM
                            </button>
                            @hasanyrole('Super Admin|Admin|Manager')
                            <a href="#" class="btn btn-action-claim btn-lg" id="claim-receive-btn">
                                <i class="ti ti-truck-delivery me-2"></i>CLAIM RETURN
                            </a>
                            @endhasanyrole
                            <a href="#" class="btn btn-action-return btn-lg" id="return-btn">
                                <i class="ti ti-arrow-back-up me-2"></i>RETURN
                            </a>
                        </div>

                        <!-- Hidden fields for order tax, shipping -->
                        <input type="hidden" name="order_tax" id="order_tax" value="0">
                        <input type="hidden" name="shipping" id="shipping" value="0">
                        <input type="hidden" name="status" value="pending">

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('all_purchases') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check me-1"></i> Save & New
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
                    </div>
                    <div id="purchase-loading-results" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Searching...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal - ITEM DETAIL BOX -->
<div class="modal fade" id="add-item-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title fw-bold" id="add-item-modal-title">
                    <i class="ti ti-shopping-cart me-2"></i>ITEM DETAILS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Product Name (Searchable/Selectable) - Premium search -->
                <div class="mb-3" id="item-search-wrapper">
                    <label class="form-label fw-bold mb-2 d-flex align-items-center">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                            <i class="ti ti-search" style="font-size: 14px;"></i>
                        </span>
                        PRODUCT NAME
                    </label>
                    <div class="d-flex align-items-start gap-2">
                        <div class="position-relative flex-grow-1">
                        <input type="text" id="item-search" class="form-control item-search-input" placeholder="e.g. 53495878 Toyota — code, space, then vehicle or keyword" autocomplete="off" title="Type to search or edit product name">
                        <i class="ti ti-search position-absolute item-search-icon" style="right: 16px; top: 50%; transform: translateY(-50%); font-size: 18px; pointer-events: none;"></i>
                        <!-- Search Results Dropdown -->
                        <div id="item-search-results" class="position-absolute w-100 item-search-results-box" style="top: 100%; left: 0; z-index: 1050; max-height: 320px; overflow-y: auto; display: none; margin-top: 8px;">
                            </div>
                        <!-- Product detail (below search input when item selected) -->
                        <div id="selected-item-details-display" class="mt-2 d-none rounded border px-2 py-2" style="font-size: 0.85rem; background: linear-gradient(135deg, #f8f9fc 0%, #f0f2f8 100%);">
                            <div class="small text-uppercase fw-semibold text-secondary mb-1" style="font-size: 10px;">Product detail</div>
                            <div class="small text-muted mt-1 mb-1" id="selected-item-details-line1"></div>
                            <div class="text-primary small fw-semibold mt-1" id="selected-item-details-line3"></div>
                        </div>
                        </div>
                        <button type="button" class="btn btn-primary align-self-center" id="item-edit-in-modal-btn" title="Edit selected item" style="display: none; white-space: nowrap;">
                            <i class="ti ti-edit"></i> Edit
                        </button>
                        <!-- Item Image Preview -->
                        <div id="item-search-image-preview" class="d-none" style="flex-shrink: 0;">
                            <img id="item-search-image" src="" alt="Item Image" class="rounded border shadow-sm" style="width: 52px; height: 52px; object-fit: cover; cursor: pointer;" title="Click to view full image">
                            <div id="item-search-stock" class="text-center mt-1" style="font-size: 0.75rem; font-weight: 600;"></div>
                            <div id="item-search-warehouse" class="text-center mt-0 d-none" style="font-size: 0.65rem; color: #6c757d;"></div>
                        </div>
                    </div>
                    <input type="hidden" id="selected-item-id">
                    <input type="hidden" id="selected-warehouse-id">
                    <input type="hidden" id="selected-warehouse-ids" value="">
                </div>
                
                <!-- STOCK STATUS Section (Shows when item is selected) -->
                <div id="stock-status-section" class="mb-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0">
                            <i class="ti ti-settings me-2"></i>STOCK STATUS
                        </label>
                    </div>
                    <div id="stock-status-content" class="border rounded p-2" style="background-color: #f8f9fa; max-height: 200px; overflow-y: auto;">
                        <div id="stock-status-list">
                            <p class="text-muted mb-0 small text-center">Loading stock status...</p>
                        </div>
                        <div id="stock-status-list-total" class="d-flex py-1 small fw-bold mt-1 border-top pt-2" style="border-color: #dee2e6 !important; display: none;">Total — <span id="stock-status-list-total-text">0 Piece</span></div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label fw-bold mb-1 small text-uppercase" style="font-size: 11px; color: #6c757d;">All branches stock</label>
                        <div id="stock-status-all-branches" class="border rounded p-2 small" style="background-color: #f8f9fa; max-height: 120px; overflow-y: auto;">
                            <p class="text-muted mb-0 text-center">—</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quantity: taken from stock warehouse row; hidden for fallback -->
                <input type="hidden" id="item-quantity" value="1">
                <input type="hidden" id="item-quantity-input" value="1">
                <!-- Unit: hidden (value set from item/API, used on submit) -->
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

                <!-- Sale Rate and Warranty Row -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">SALE RATE</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rs</span>
                            <input type="number" id="item-rate" class="form-control" value="0" step="1" min="0" placeholder="0" style="background-color: #f8f9fa; border-radius: 8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">WARRANTY</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <select id="warranty-value" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
                                    <option value="">-</option>
                                    @for($i = 1; $i <= 30; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <select id="warranty-unit" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
                                    <option value="">-</option>
                                    <option value="Days">Days</option>
                                    <option value="Weeks">Weeks</option>
                                    <option value="Months">Months</option>
                                    <option value="Years">Years</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase History Section -->
                <div class="mb-3" id="purchase-history-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0 d-flex align-items-center">
                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff;">
                                <i class="ti ti-history" style="font-size: 12px;"></i>
                            </span>
                            PURCHASE HISTORY
                        </label>
                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-success" id="hold-rate-link" style="display: none; font-size: 11px;">
                            <i class="ti ti-check me-1"></i>Apply Last Rate
                        </a>
                    </div>
                    <div id="customer-history-content" class="p-3 purchase-history-box" style="min-height: 80px; max-height: 200px; overflow-y: auto;">
                        <p class="text-muted mb-0 small text-center">Select item to view purchase history</p>
                    </div>
                </div>

                <!-- Additional Fields (Hidden by default, can be shown if needed) -->
                <div id="additional-fields" style="display: none;">
                    <!-- Discount -->
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-2">DISCOUNT</label>
                        <div class="input-group">
                            <input type="number" id="item-discount" class="form-control" value="0" step="0.01" min="0" style="background-color: #f8f9fa; border-radius: 8px;">
                            <select id="discount-type" class="form-control" style="max-width: 100px; background-color: #f8f9fa; border-radius: 8px;">
                                <option value="amount">Rs</option>
                                <option value="percent">%</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tax -->
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-2">TAX %</label>
                        <input type="number" id="item-tax" class="form-control" value="0" step="0.01" min="0" max="100" style="background-color: #f8f9fa; border-radius: 8px;">
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0 pt-2 d-flex flex-wrap justify-content-end align-items-center gap-2">
                <div class="d-none">
                    <select id="item-save-warehouse" class="form-select form-select-sm">
                        <option value="">— Select warehouse —</option>
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 10px 24px;">Cancel</button>
                    <button type="button" class="btn btn-outline-primary fw-bold" id="save-and-new-entry" style="border-radius: 8px; padding: 10px 24px;">SAVE & ADD NEW</button>
                    <button type="button" class="btn btn-primary fw-bold" id="confirm-entry" style="background-color: #0d6efd; border-radius: 8px; padding: 10px 30px;">CONFIRM SELECTION</button>
                </div>
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

@push('styles')
<style>
    /* Stylish Action Buttons */
    .btn-action-purchase, .btn-action-claim, .btn-action-return {
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
    /* Selected warehouse row: always show primary blue (override inline styles) */
    #stock-status-list .stock-warehouse-item.bg-primary {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
    #stock-status-list .stock-warehouse-item.bg-primary .text-muted {
        color: rgba(255,255,255,0.9) !important;
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
        padding: 12px 44px 12px 16px !important;
        font-size: 0.95rem !important;
        transition: all 0.3s ease !important;
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
    .item-search-icon {
        color: #667eea !important;
        opacity: 0.85;
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

    /* Mobile vendor trigger – same look as Select2 (select2-supplier_id-container) */
    .supplier-mobile-select2-trigger {
        box-sizing: border-box;
        cursor: pointer;
        display: block;
        min-height: 38px;
        user-select: none;
        background-color: #fff;
        border: 1px solid #aaa;
        border-radius: 4px;
        width: 100%;
        padding: 0;
        margin-top: 2px;
    }
    .supplier-mobile-select2-trigger .supplier-mobile-selection-rendered {
        display: block;
        padding-left: 8px;
        padding-right: 28px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 36px;
        color: #444;
        font-size: 15px;
        font-weight: bold;
        text-transform: uppercase !important;
    }
    .supplier-mobile-select2-trigger .supplier-mobile-selection-rendered.placeholder {
        color: #999;
    }
    .supplier-mobile-select2-trigger .supplier-mobile-select2-arrow {
        height: 36px;
        position: absolute;
        top: 1px;
        right: 1px;
        width: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        color: #888;
    }
    .supplier-mobile-select2-trigger .supplier-mobile-select2-arrow i {
        font-size: 18px;
    }

    /* Mobile vendor dropdown – same look as Select2 dropdown + search */
    .supplier-mobile-select2-dropdown {
        background-color: #fff;
        border: 1px solid #aaa;
        border-radius: 4px;
        box-sizing: border-box;
        position: absolute;
        width: 100%;
        margin-top: 2px;
        z-index: 1051;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .supplier-mobile-select2-dropdown .select2-search--dropdown {
        display: block;
        padding: 4px;
        border-bottom: 1px solid #eee;
    }
    .supplier-mobile-select2-dropdown .select2-search__field {
        padding: 6px 8px;
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #aaa;
        border-radius: 4px;
        font-size: 14px;
    }
    .supplier-mobile-select2-dropdown .select2-search__field:focus {
        outline: none;
        border-color: #5897fb;
    }
    .supplier-mobile-select2-dropdown .select2-results__options {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .supplier-mobile-select2-dropdown .supplier-mobile-pick,
    .supplier-mobile-select2-dropdown .list-group-item {
        padding: 8px 12px;
        cursor: pointer;
        user-select: none;
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        background: #fff;
        width: 100%;
        text-align: left;
    }
    .supplier-mobile-select2-dropdown .supplier-mobile-pick:hover,
    .supplier-mobile-select2-dropdown .list-group-item.list-group-item-action:hover {
        background-color: #5897fb;
        color: #fff;
    }
    .supplier-mobile-select2-dropdown .list-group-item-secondary,
    .supplier-mobile-select2-dropdown .list-group-item-danger {
        font-weight: normal;
        text-transform: none;
        color: #999;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
$(document).ready(function() {
    let purchaseItems = [];
    let itemCounter = 0;
    let editingRowId = null;
    let pendingEditItem = null;
    let warehouseQtyFirstSelectDone = false; // clear other qty dropdowns only on first selection; after that each box keeps its value
    // Entry type: 'purchase' (default) or 'scrap' - same modal as Smart Invoice Scrap In
    let currentEntryType = 'purchase';

    // ---------- Persisted purchase cart (e-commerce style: cart survives refresh) ----------
    // Helper to clean item name (remove Lorem Ipsum or dummy text)
    function cleanItemName(name, itemId) {
        if (!name) return 'Item #' + itemId;
        const lower = name.toLowerCase();
        if (lower.indexOf('lorem') !== -1 || lower.indexOf('dummy') !== -1 || lower.indexOf('simply') !== -1 || name.length > 150) {
            return 'Item #' + itemId;
        }
        return name.length > 80 ? name.substring(0, 77) + '...' : name;
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
            $('#payment-section').hide(); $('#payment-amount-row').hide();
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
                        // SCRAP amount should be minus (backend may store as positive)
                        if ((it.entry_type === 'scrap') && total > 0) {
                            total = -total;
                        }
                        const item = {
                            id: itemCounter++,
                            item_id: it.item_id,
                            name: cleanItemName(it.name, it.item_id),
                            warehouse_id: it.warehouse_id || null,
                            warehouse_name: it.warehouse_name || null,
                            quantity: parseFloat(it.quantity),
                            unit: it.unit || 'Unit',
                            rate: parseFloat(it.rate),
                            discount: parseFloat(it.discount) || 0,
                            tax_percentage: parseFloat(it.tax_percentage) || 0,
                            tax_amount: parseFloat(it.tax_amount) || 0,
                            total: total,
                            warranty: it.warranty || null,
                            entry_type: it.entry_type || 'purchase'
                        };
                        purchaseItems.push(item);
                        addItemToTable(item);
                    });
                    $('#empty-items-state').hide();
                    $('#items-list').show();
                    if (purchaseItems.length > 0) {
                        $('#payment-section').show(); $('#payment-amount-row').show();
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
                    $('#payment-section').hide(); $('#payment-amount-row').hide();
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
    $('#payment-section').hide();
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
            $('#add-item-modal-title').html('<i class="ti ti-shopping-cart me-2"></i>ITEM DETAILS');
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
        
        // Open detail modal
        $('#add-item-modal').modal('show');
    });
    // ========== End YouTube-Style Search Modal ==========

    // Supplier change handler - auto-fill phone when name is selected
    $('#supplier_id').on('change', function() {
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
    });
    
    // Mobile field: Select2-style trigger + dropdown with search (like supplier_id)
    let supplierPhoneTimeout = null;
    var supplierMobileDropdownOpen = false;

    function openSupplierMobileDropdown() {
        supplierMobileDropdownOpen = true;
        $('#supplier_mobile_dropdown').show();
        $('#supplier_mobile_search').val('').focus();
        $('#supplier_mobile_results').empty().append('<div class="list-group-item list-group-item-secondary small">Type to search by number or name</div>');
        $('#supplier_mobile_select2_trigger').attr('aria-expanded', 'true');
    }
    function closeSupplierMobileDropdown() {
        supplierMobileDropdownOpen = false;
        $('#supplier_mobile_dropdown').hide();
        $('#supplier_mobile_select2_trigger').attr('aria-expanded', 'false');
    }
    function setSupplierMobileDisplay(phone, label) {
        $('#supplier_mobile').val(phone || '');
        var $disp = $('#supplier_mobile_display');
        if (phone && label) {
            $disp.text(label).removeClass('placeholder').attr('title', label);
        } else {
            $disp.text('SELECT VENDOR').addClass('placeholder').attr('title', 'Select vendor');
        }
    }

    $('#supplier_mobile_select2_trigger').on('click', function(e) {
        e.preventDefault();
        $(this).attr('aria-expanded', supplierMobileDropdownOpen ? 'false' : 'true');
        if (supplierMobileDropdownOpen) {
            closeSupplierMobileDropdown();
        } else {
            openSupplierMobileDropdown();
        }
    });
    $('#supplier_mobile_search').on('input', function() {
        const q = $(this).val().trim();
        const $results = $('#supplier_mobile_results');
        clearTimeout(supplierPhoneTimeout);
        if (q.length < 2) {
            $results.empty().append('<div class="list-group-item list-group-item-secondary small">Type at least 2 characters</div>');
            return;
        }
        supplierPhoneTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route("purchases.suppliers.search.phone") }}',
                method: 'GET',
                data: { phone: q },
                success: function(suppliers) {
                    $results.empty();
                    if (suppliers.length === 0) {
                        $results.append('<div class="list-group-item list-group-item-secondary small">No vendor found</div>');
                    } else {
                        suppliers.forEach(function(s) {
                            var label = (s.name || '') + (s.company ? ' - ' + s.company : '') + (s.phone ? ' · ' + s.phone : '');
                            $results.append('<a href="javascript:void(0)" class="list-group-item list-group-item-action supplier-mobile-pick" data-id="' + s.id + '" data-phone="' + (s.phone || '') + '" data-address="' + (s.address || '') + '" data-area="' + (s.area || '') + '" data-label="' + (label || s.phone || '').replace(/"/g, '&quot;') + '">' + (label || s.phone || 'Vendor #' + s.id) + '</a>');
                        });
                    }
                },
                error: function() {
                    $results.empty().append('<div class="list-group-item list-group-item-danger small">Search failed</div>');
                }
            });
        }, 300);
    });
    $(document).on('click', '.supplier-mobile-pick', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var phone = $(this).data('phone');
        var address = $(this).data('address');
        var area = $(this).data('area');
        var label = $(this).data('label') || (phone + '');
        $('#supplier_id').val(id);
        $('#supplier_mobile').val(phone);
        setSupplierMobileDisplay(phone, label);
        if (address) $('#supplier_address').val(address);
        if (area) $('#supplier_area').val(area);
        closeSupplierMobileDropdown();
        $('#supplier_id').trigger('change');
    });
    $(document).on('click', function(e) {
        if (supplierMobileDropdownOpen && !$(e.target).closest('#supplier_mobile_select2_trigger, #supplier_mobile_dropdown').length) {
            closeSupplierMobileDropdown();
        }
    });

    // When supplier is selected from supplier_id dropdown, sync mobile display
    $('#supplier_id').on('change', function() {
        var opt = $(this).find('option:selected');
        if (opt.length && opt.val()) {
            var phone = opt.data('phone') || '';
            var name = opt.data('name') || '';
            var company = opt.data('company') || '';
            var label = (name + (company ? ' - ' + company : '') + (phone ? ' · ' + phone : '')).trim() || phone;
            setSupplierMobileDisplay(phone, label);
        } else {
            setSupplierMobileDisplay('', null);
        }
    });
    // Initial sync on load (e.g. when restored from cart)
    if ($('#supplier_id').val()) {
        $('#supplier_id').trigger('change');
    }

    // Branch selection for purchase
    function selectPurchaseBranch(branchId, branchName, branchCode) {
        // Update UI immediately
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
                $('#payment-section').hide();
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
        // Searchable supplier dropdown (Select2)
        if ($.fn.select2 && $('#supplier_id').length) {
            $('#supplier_id').select2({
                placeholder: 'Select vendor',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0
            });
        }

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
        }
        $('#purchaseOrderSwitch').on('change', updateDocTypeFromSwitch);
        updateDocTypeFromSwitch(); // init on load
    });

    // Handle "PURCHASE ITEM" button click - check branch first (event delegation so it works reliably)
    $(document).on('click', '#add-new-item-btn', function(e) {
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
        $('#add-item-modal-title').html('<i class="ti ti-shopping-cart me-2"></i>ITEM DETAILS');
        $('#add-item-modal').modal('show');
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
            $('#add-item-modal-title').html('<i class="ti ti-shopping-cart me-2"></i>ITEM DETAILS');
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
                $('#add-item-modal-title').html('<i class="ti ti-truck-delivery me-2"></i>CLAIM RETURN');
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
        $('#add-item-modal-title').html('<i class="ti ti-arrow-back-up me-2"></i>RETURN');
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

    // Reset form when modal opens (skip full reset when opening for edit)
    $('#add-item-modal').on('show.bs.modal', function() {
        const branchId = $('#purchaseBranchId').val();
        if (editingRowId !== null) {
            $('#item-search-results').hide();
            loadItemSaveWarehouseDropdown();
            return;
        }
        $('#item-search').val('');
        $('#selected-item-id').val('');
        $('#selected-warehouse-id').val('');
        $('#selected-warehouse-ids').val('');
        $('#item-quantity').val('');
        $('#item-unit').val('');
        $('#item-rate').val('0');
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
        $('#selected-item-details-line3').html('');
        loadItemSaveWarehouseDropdown();
    });
        
    // Focus on search input when modal is fully shown; keep input always editable
    $('#add-item-modal').on('shown.bs.modal', function() {
        $('#item-search').prop('readonly', false).prop('disabled', false).attr('readonly', false);
        if (pendingItemIdAfterUpdate) {
            var itemId = pendingItemIdAfterUpdate;
            pendingItemIdAfterUpdate = null;
            $('#selected-item-id').val(itemId);
            $.get('{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId))
                .then(function(r) {
                    $('#item-search').val(r.name || '');
                    loadItemStockStatus(itemId);
                    $('#item-edit-in-modal-btn').show();
                })
                .catch(function() { $('#item-search').trigger('input'); });
        }
        setTimeout(function() {
            $('#item-search').focus();
        }, 100);
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
                    const itemName = (item.short_disc && item.short_disc.toLowerCase().indexOf('lorem') === -1) ? item.short_disc : ((item.pro_dis && item.pro_dis.toLowerCase().indexOf('lorem') === -1) ? item.pro_dis : (item.bar_code || (item.partnumber_item ? item.partnumber_item.name : '') || 'Item #' + item.id));
                    const itemRate = item.packing_purchase_rate || item.total_price || 0;
                    const unit = (item.unit_item && (item.unit_item.name || item.unit_item.short_name)) ? (item.unit_item.name || item.unit_item.short_name) : 'Unit';
                    const warehouseId = result.warehouse_id || '';
                    
                    $('#item-search').val(itemName);
                    $('#selected-item-id').val(itemId);
                    $('#item-unit').val(unit);
                    $('#item-rate').val(Math.round(parseFloat(itemRate) || 0));
                    $('#item-search-results').hide();
                    $('#barcode-scan-input').val('');
                    
                    $.ajax({
                        url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
                        method: 'GET',
                        success: function(response) {
                            $('#item-rate').val(Math.round(parseFloat(response.total_price || response.rate || itemRate) || 0));
                            if (response.unit) $('#item-unit').val(response.unit);
                            if (response.warehouse_id || warehouseId) {
                                const whId = response.warehouse_id || warehouseId;
                                $('#selected-warehouse-id').val(whId);
                                if ($('#item-save-warehouse option[value="' + whId + '"]').length) $('#item-save-warehouse').val(whId);
                            }
                            
                            // Show item image if available
                            if (response.image) {
                                $('#item-search-image').attr('src', response.image);
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
                    runBarcodeSearch(decodedText);
                },
                function() {}
            ).catch(function(err) {
                cameraBarcodeScanner = null;
                $('#camera-barcode-modal').modal('hide');
                alert('Camera access failed. Allow camera permission or use barcode input.');
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
                                const itemType = item.type || '';
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
                                const grade = item.grade_item ? item.grade_item.name : '';
                                const volt = item.volt_item ? item.volt_item.name : '';
                                const cca = item.cca_item ? item.cca_item.name : '';
                                const group = item.group_item ? item.group_item.name : '';
                                const madeIn = item.made_in_item ? item.made_in_item.name : '';
                                const level = item.level_item ? item.level_item.name : '';
                                const batterySize = item.battery_size || '';
                                const plate = item.plate_item ? item.plate_item.name : '';
                                const amperes = item.amphors_item ? item.amphors_item.name : '';
                                const stock = (result.stock !== undefined && result.stock !== null) ? parseFloat(result.stock) : (item.on_hand || 0);
                                const rate = item.packing_purchase_rate || item.total_price || 0;
                                const unit = (item.unit_item && (item.unit_item.name || item.unit_item.short_name)) 
                                    ? (item.unit_item.name || item.unit_item.short_name) 
                                    : 'Unit';
                                
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
                                // Show volt, CCA, group etc. whenever present (battery or battery-like items)
                                let searchDetails = [];
                                
                                // Battery-style details: show for battery type OR when item has volt/cca/group (so "AGS"-only items get full line)
                                if (itemType === 'battery' || group || volt || cca) {
                                    if (group && !isDummy(group)) searchDetails.push(group);
                                    if (volt) searchDetails.push(volt + (volt.toString().indexOf('V') !== -1 ? '' : 'V'));
                                    if (cca) searchDetails.push(cca + (cca.toString().indexOf('CCA') !== -1 ? '' : 'CCA'));
                                    if (technology && !isDummy(technology)) searchDetails.push(technology);
                                    if (grade && !isDummy(grade)) searchDetails.push(grade);
                                    if (batterySize && !isDummy(batterySize)) searchDetails.push(batterySize);
                                    if (searchDetails.length === 0 && company) searchDetails.push(company);
                                }
                                // Common when not battery-style
                                if (searchDetails.length === 0) {
                                    if (company) searchDetails.push(company);
                                    if (category) searchDetails.push(category);
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
                                
                                // Build first line HTML: show Product + PL + AH + Company when we have those parts
                                let firstLineHtml = '';
                                let firstLineText = productName; // Default to product name
                                if (firstLineParts.length > 0) {
                                    firstLineText = firstLineParts.join(' ');
                                    const highlightedFirstLine = highlightText(firstLineText, query);
                                    firstLineHtml = '<div class="fw-bold text-dark mb-1">' + highlightedFirstLine + '</div>';
                                } else {
                                    const highlightedProductName = highlightText(productName, query);
                                    firstLineHtml = '<div class="fw-bold text-dark mb-1">' + highlightedProductName + '</div>';
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
                                
                                // Get item image URL
                                const itemImage = item.image || '';
                                
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
                                         style="cursor: pointer; transition: all 0.2s ease; background: white;">
                                        <div class="d-flex justify-content-between align-items-start">
                                            ${itemImage ? `<div class="me-3" style="flex-shrink: 0;">
                                                <img src="${itemImage}" alt="${productName}" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                                            </div>` : ''}
                                            <div class="flex-grow-1 me-3">
                                                ${firstLineHtml}
                                                ${detailsHtml}
                                                ${codeInfo ? '<div class="text-primary small fw-semibold mt-1"><i class="ti ti-barcode me-1"></i>' + highlightedCodeInfo + '</div>' : ''}
                                            </div>
                                            <div class="text-end" style="min-width: 100px;">
                                                <div class="fw-bold text-primary mb-1">Rs ${parseFloat(rate).toFixed(2)}</div>
                                                <div class="small">
                                                    <span class="badge bg-${stockColor} bg-opacity-10 text-${stockColor}">
                                                        ${stockIcon ? '<i class="ti ' + stockIcon + ' me-1"></i>' : ''}${stockDisplay} ${unit}
                                                    </span>
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
            
            // Line 3: Barcode/Code (with icon)
            if (itemCode) {
                line3 = '<i class="ti ti-barcode me-1"></i>' + itemCode;
            }
            
            if (line1 || line3) {
                $('#selected-item-details-line1').html(line1 || '&nbsp;');
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
                    // Use total_price if available, otherwise use rate (packing_purchase_rate)
                    const itemRate = response.total_price || response.rate || itemRate || 0;
                    $('#item-rate').val(Math.round(parseFloat(itemRate) || 0));
                    $('#item-quantity').val('1');
                    
                    // Auto-set unit from item's saved unit
                    if (response.unit) {
                        $('#item-unit').val(response.unit);
                    }
                    
                    // Auto-select warehouse if available (from response or from search result)
                    const finalWarehouseId = response.warehouse_id || warehouseId;
                    if (finalWarehouseId) {
                        $('#selected-warehouse-id').val(finalWarehouseId);
                    }
                    
                    // Show item image if available
                    if (response.image) {
                        $('#item-search-image').attr('src', response.image);
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
                // Show only warehouses in current branch in the main list (stock-status-content)
                var showInMainList = function(stock) {
                    if (!selectedBranchId) return true;
                    if (stock.type === 'branch') return (stock.id + '') === selectedBranchId;
                    if (stock.type === 'warehouse') return (stock.branch_id + '') === selectedBranchId;
                    return true;
                };
                stockData.forEach(function(stock) {
                    if (stock.type === 'branch') {
                        if (selectedBranchId && (stock.id + '') !== selectedBranchId) {
                            branchTotals.push(stock);
                        }
                        currentBranchDisplay = stock.display || '';
                    }
                    var unitLabel = (stock.unit || 'Unit').trim();
                    var qty = parseFloat(stock.quantity) || 0;
                    var qtyText = (Number.isInteger(qty) ? qty : qty.toFixed(2)) + ' ' + unitLabel;
                    if (stock.type === 'branch') {
                        if (showInMainList(stock)) {
                            html += `
                            <div class="p-2 mb-1 border-bottom stock-branch-item" data-branch-id="${stock.id}" style="background-color: #fff;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold">${stock.display}</div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="stock-branch-selected-qty fw-bold text-dark">0 ${unitLabel}</span>
                                        <span class="text-muted small">·</span>
                                        <span class="stock-branch-total-rs fw-bold text-primary">Rs 0</span>
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
                        html += `
                            <div class="p-2 mb-1 stock-warehouse-item ${isSelected ? 'bg-primary text-white' : ''}" 
                                 data-warehouse-id="${stock.id}"
                                 data-branch-id="${stock.branch_id}"
                                 data-display="${(stock.display || '').replace(/"/g, '&quot;')}"
                                 data-quantity="${qty}"
                                 data-unit="${(unitLabel || '').replace(/"/g, '&quot;')}"
                                 data-qty-text="${(qtyText || '').replace(/"/g, '&quot;')}"
                                 style="cursor: pointer; transition: all 0.2s; ${isSelected ? '' : 'background-color: #f0f0f0;'}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">${isSelected ? '✓' : ''}</span>
                                        <span class="${isSelected ? 'text-white' : ''}">${stock.display}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="${isSelected ? 'text-white' : 'text-muted'}"><span class="fw-bold">${qtyText}</span></span>
                                        <select class="form-control form-control-sm stock-warehouse-qty-input" style="width: 70px; display: inline-block;" data-warehouse-id="${stock.id}" onclick="event.stopPropagation();" data-unit="${(unitLabel || 'Piece').replace(/"/g, '&quot;')}">${(function(){ var selVal = ''; var opts = '<option value="" selected>-</option>'; for (var i = 1; i <= 1000; i++) { opts += '<option value="'+i+'">'+i+'</option>'; } return opts; })()}</select>
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
                    var totalQ = 0, u = ($checked.first().data('unit') || 'Unit').trim();
                    $checked.each(function(){ totalQ += parseFloat($(this).data('quantity')) || 0; });
                    var qt = (Number.isInteger(totalQ) ? totalQ : totalQ.toFixed(2)) + ' ' + u;
                    var stockColor = totalQ > 10 ? 'text-success' : (totalQ > 0 ? 'text-warning' : 'text-danger');
                    $('#item-search-stock').html('<span class="' + stockColor + '">' + qt + '</span>');
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
                    if (pe.warehouse_id) {
                        var $row = $('#stock-status-list .stock-warehouse-item[data-warehouse-id="' + pe.warehouse_id + '"]');
                        if ($row.length) {
                            $row.siblings('.stock-warehouse-item').removeClass('bg-primary text-white').css('background-color', '#f0f0f0');
                            $row.addClass('bg-primary text-white').css('background-color', '');
                            $('#selected-warehouse-id').val(pe.warehouse_id);
                            if ($('#item-save-warehouse option[value="' + pe.warehouse_id + '"]').length) $('#item-save-warehouse').val(pe.warehouse_id);
                            var $qtyInput = $('#stock-status-list .stock-warehouse-qty-input[data-warehouse-id="' + pe.warehouse_id + '"]');
                            if ($qtyInput.length) $qtyInput.val(pe.quantity);
                            $('#item-quantity').val(pe.quantity); $('#item-quantity-input').val(pe.quantity).hide();
                        } else {
                            $('#selected-warehouse-id').val(pe.warehouse_id);
                            if ($('#item-save-warehouse option[value="' + pe.warehouse_id + '"]').length) $('#item-save-warehouse').val(pe.warehouse_id);
                            $('#item-quantity').val(pe.quantity); $('#item-quantity-input').val(pe.quantity).hide();
                        }
                    }
                    if (pe.rate != null) $('#item-rate').val(pe.rate);
                    if (pe.unit != null) $('#item-unit').val(pe.unit);
                    if (pe.discount != null) { $('#discount-type').val('amount'); $('#item-discount').val(pe.discount); }
                    if (pe.tax_percentage != null) $('#item-tax').val(pe.tax_percentage);
                    if (pe.warranty != null) {
                        var w = (pe.warranty || '').toString().trim().split(/\s+/);
                        $('#warranty-value').val(w[0] || ''); $('#warranty-unit').val(w[1] || '');
                    }
                    pendingEditItem = null;
                }
                if (typeof updateItemLineTotal === 'function') updateItemLineTotal();
                if (typeof updateStockBranchSelectedQty === 'function') updateStockBranchSelectedQty();
            },
            error: function() {
                $('#stock-status-list').html('<p class="text-danger mb-0 small text-center">Error loading stock status</p>');
                $('#stock-status-all-branches').html('<p class="text-muted mb-0 text-center">—</p>');
                if (typeof updateStockStatusListTotal === 'function') updateStockStatusListTotal();
            }
        });
    }
    
    var stockWarehouseUpdating = false; // prevent change handler when we set .prop('checked') programmatically
    // Toggle warehouse selection (multiple allowed)
    function toggleStockWarehouseRow($row) {
        var id = $row.data('warehouse-id') + '';
        var selectedIds = ($('#selected-warehouse-ids').val() || '').split(',').map(function(x){ return x.trim(); }).filter(Boolean);
        var idx = selectedIds.indexOf(id);
        if (idx === -1) selectedIds.push(id); else selectedIds.splice(idx, 1);
        $('#selected-warehouse-ids').val(selectedIds.join(','));
        var firstId = selectedIds[0] || '';
        $('#selected-warehouse-id').val(firstId);
        if (firstId && $('#item-save-warehouse option[value="' + firstId + '"]').length) $('#item-save-warehouse').val(firstId);
        
        stockWarehouseUpdating = true;
        $('.stock-warehouse-item').each(function() {
            var rid = $(this).data('warehouse-id') + '';
            var sel = selectedIds.indexOf(rid) !== -1;
            $(this).removeClass('bg-primary text-white bg-light').css('background-color', '');
            if (sel) $(this).addClass('bg-primary text-white'); else $(this).css('background-color', '#f0f0f0');
            $(this).find('span:first').html(sel ? '✓' : '');
            $(this).find('.stock-warehouse-qty-input').val('');
            var $nameSpan = $(this).find('.d-flex.align-items-center span:eq(1)');
            var $qtyWrap = $(this).find('.d-flex.align-items-center.gap-2 span').first();
            $nameSpan.toggleClass('text-white', sel).toggleClass('text-dark', !sel);
            $qtyWrap.toggleClass('text-white', sel).toggleClass('text-muted', !sel);
        });
        
        var $checked = $('.stock-warehouse-item.bg-primary');
        if ($checked.length) {
            var totalQ = 0, u = ($checked.first().data('unit') || 'Unit').trim();
            $checked.each(function(){ totalQ += parseFloat($(this).data('quantity')) || 0; });
            var qt = (Number.isInteger(totalQ) ? totalQ : totalQ.toFixed(2)) + ' ' + u;
            var stockColor = totalQ > 10 ? 'text-success' : (totalQ > 0 ? 'text-warning' : 'text-danger');
            $('#item-search-stock').html('<span class="' + stockColor + '">' + qt + '</span>');
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
            if (sel) $(this).addClass('bg-primary text-white'); else $(this).css('background-color', '#f0f0f0');
            $(this).find('span:first').html(sel ? '✓' : '');
            $(this).find('.stock-warehouse-qty-input').val('');
            $(this).find('.d-flex.align-items-center span:eq(1)').toggleClass('text-white', sel).toggleClass('text-dark', !sel);
            $(this).find('.d-flex.align-items-center.gap-2 span').first().toggleClass('text-white', sel).toggleClass('text-muted', !sel);
        });
        var $checked = $('.stock-warehouse-item.bg-primary');
        if ($checked.length) {
            var totalQ = 0, u = ($checked.first().data('unit') || 'Unit').trim();
            $checked.each(function(){ totalQ += parseFloat($(this).data('quantity')) || 0; });
            var qt = (Number.isInteger(totalQ) ? totalQ : totalQ.toFixed(2)) + ' ' + u;
            var stockColor = totalQ > 10 ? 'text-success' : (totalQ > 0 ? 'text-warning' : 'text-danger');
            $('#item-search-stock').html('<span class="' + stockColor + '">' + qt + '</span>');
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
                if (editingRowId === null) $('#item-search').val(response.name);
                $('#item-quantity').val('1');
                
                // Show selected item details — same format as dropdown: "Group • 12V • CCA • Vehicle"
                var detailsArr = [];
                if (response.group_name) detailsArr.push(response.group_name);
                if (response.volt) detailsArr.push(response.volt + (response.volt.toString().indexOf('V') !== -1 ? '' : 'V'));
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
                
                // Set rate - use total_price if available, otherwise use rate
                const itemRate = response.total_price || response.rate || 0;
                $('#item-rate').val(Math.round(parseFloat(itemRate) || 0));
                
                // Set unit from item
                $('#item-unit').val(response.unit || 'Unit');
                
                // Auto-select warehouse if available
                if (response.warehouse_id) {
                    $('#selected-warehouse-id').val(response.warehouse_id);
                    var $row = $('.stock-warehouse-item[data-warehouse-id="' + response.warehouse_id + '"]');
                    $row.siblings('.stock-warehouse-item').removeClass('bg-primary text-white').css('background-color', '#f0f0f0');
                    $row.removeClass('bg-light').addClass('bg-primary text-white').css('background-color', '').find('span:first').html('✓');
                    if ($row.length) $('#item-search-warehouse').text($row.data('display') || $row.find('span:eq(1)').text().trim());
                }
                
                // Show item image if available
                if (response.image) {
                    $('#item-search-image').attr('src', response.image);
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
                
                // Load stock status to show warehouse options
                loadItemStockStatus(itemId);
                
                // Load customer history for this item
                loadCustomerHistory(itemId);
                
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
            // Visual feedback
            $(this).addClass('bg-success bg-opacity-10');
            setTimeout(() => $(this).removeClass('bg-success bg-opacity-10'), 500);
        }
    });

    // Hold rate to apply (uses last purchase rate)
    $('#hold-rate-link').on('click', function() {
        if (lastPurchaseRate > 0) {
            $('#item-rate').val(Math.round(parseFloat(lastPurchaseRate) || 0));
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

    // Update branch header "selected total" (sum of all warehouse qty dropdowns in the list)
    function updateStockBranchSelectedQty() {
        var total = 0;
        var unit = 'Piece';
        $('#stock-status-list .stock-warehouse-qty-input').each(function() {
            total += parseFloat($(this).val()) || 0;
            if (!unit && $(this).data('unit')) unit = $(this).data('unit') || 'Piece';
            if (unit === 'Piece' && $(this).data('unit')) unit = $(this).data('unit');
        });
        if ($('#stock-status-list .stock-warehouse-qty-input').length) unit = ($('#stock-status-list .stock-warehouse-qty-input').first().data('unit') || 'Piece').trim();
        var text = (Number.isInteger(total) ? total : total.toFixed(2)) + ' ' + unit;
        $('#stock-status-list .stock-branch-selected-qty').text(text);
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

    // Sync quantity from item-quantity to warehouse inputs (dropdown is 1-1000)
    function syncQuantityToWarehouseInputs() {
        var qty = parseFloat($('#item-quantity').val()) || 0;
        if ($('#item-quantity-input').is(':visible') && $('#item-quantity-input').val()) {
            qty = parseFloat($('#item-quantity-input').val()) || 0;
        }
        qty = Math.max(1, Math.min(1000, Math.round(qty) || 1));
        $('.stock-warehouse-qty-input').val(qty);
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

    // When stock warehouse qty changes: sync that value to #item-quantity; move this row to top when qty is set.
    $(document).on('change', '.stock-warehouse-qty-input', function() {
        var $this = $(this);
        var whId = $this.data('warehouse-id');
        var val = ($this.val() || '').toString().trim();
        if (val && !warehouseQtyFirstSelectDone) {
            $('#stock-status-list .stock-warehouse-qty-input').not($this).val('');
            warehouseQtyFirstSelectDone = true;
        }
        var $row = $this.closest('.stock-warehouse-item');
        if (val && $row.length) {
            var $list = $('#stock-status-list');
            var $afterBranch = $list.find('.stock-branch-item').last();
            if ($afterBranch.length) $row.insertAfter($afterBranch); else $row.prependTo($list);
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
        const rate = parseFloat($('#item-rate').val()) || 0;
        const discount = parseFloat($('#item-discount').val()) || 0;
        const discountType = $('#discount-type').val();
        const taxPercentage = parseFloat($('#item-tax').val()) || 0;
        const rawItemName = $('#item-search').val();
        const itemName = cleanItemName(rawItemName, itemId);
        const warrantyValue = $('#warranty-value').val();
        const warrantyUnit = $('#warranty-unit').val();
        const $whSel = $('#item-save-warehouse');

        if (!itemId || rate <= 0) {
            alert('Please select an item and enter valid rate');
            return;
        }

        // Collect all warehouses that have a quantity selected (dropdown not empty)
        var warehouseLines = [];
        $('#stock-status-list .stock-warehouse-qty-input').each(function() {
            var whId = ($(this).data('warehouse-id') || '').toString();
            var qty = parseFloat($(this).val()) || 0;
            if (!whId || qty <= 0) return;
            var $row = $(this).closest('.stock-warehouse-item');
            var whName = ($row.data('display') || '').replace(/&quot;/g, '"');
            if (!whName && $whSel.find('option[value="' + whId + '"]').length) whName = $whSel.find('option[value="' + whId + '"]').text().trim();
            warehouseLines.push({ warehouse_id: whId, warehouse_name: whName, quantity: qty });
        });

        // Fallback: single warehouse from Save to warehouse / selected-warehouse-id
        if (warehouseLines.length === 0 && currentEntryType !== 'scrap') {
            var warehouseId = ($whSel.val() || '').toString() || ($('#selected-warehouse-id').val() || '').toString();
            var quantity = parseFloat($('#item-quantity').val()) || 0;
            if ($('#item-quantity-input').is(':visible') && $('#item-quantity-input').val()) quantity = parseFloat($('#item-quantity-input').val()) || 0;
            if (!warehouseId || quantity <= 0) {
                alert('Please select at least one warehouse and enter quantity (use the quantity dropdown per warehouse).');
                return;
            }
            var warehouseName = $whSel.find('option:selected').text().trim() || ($('.stock-warehouse-item[data-warehouse-id="' + warehouseId + '"]').first().data('display') || '').replace(/&quot;/g, '"');
            warehouseLines = [{ warehouse_id: warehouseId, warehouse_name: warehouseName, quantity: quantity }];
        }
        if (warehouseLines.length === 0 && currentEntryType === 'scrap') {
            var qty = parseFloat($('#item-quantity').val()) || 0;
            if (qty <= 0) { alert('Please enter quantity'); return; }
            warehouseLines = [{ warehouse_id: null, warehouse_name: null, quantity: qty }];
        }

        if (editingRowId !== null) {
            // Edit mode: single row update (use first selected warehouse)
            var wl = warehouseLines[0] || {};
            var warehouseId = wl.warehouse_id || '';
            var warehouseName = wl.warehouse_name || '';
            var quantity = wl.quantity || parseFloat($('#item-quantity').val()) || 0;
            var discountAmount = discountType === 'percent' ? (quantity * rate * discount) / 100 : discount;
            var subtotal = (quantity * rate) - discountAmount;
            var taxAmount = (subtotal * taxPercentage) / 100;
            var total = subtotal + taxAmount;
            if (currentEntryType === 'scrap') total = -Math.abs(total);
            var idx = purchaseItems.findIndex(function(i) { return i.id === editingRowId; });
            if (idx === -1) { editingRowId = null; return; }
            var updatedItem = {
                id: editingRowId,
                item_id: itemId,
                name: itemName,
                warehouse_id: warehouseId || null,
                warehouse_name: warehouseName || null,
                quantity: quantity,
                unit: unit,
                rate: rate,
                discount: discountAmount,
                tax_percentage: taxPercentage,
                tax_amount: taxAmount,
                total: total,
                warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : null,
                entry_type: currentEntryType || 'purchase'
            };
            purchaseItems[idx] = updatedItem;
            $('#items-tbody tr[data-row-id="' + editingRowId + '"]').remove();
            addItemToTable(updatedItem);
            editingRowId = null;
            pendingEditItem = null;
            calculateTotals();
            syncCartToServer();
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
            if (currentEntryType === 'scrap') total = -Math.abs(total);
            var item = {
                id: itemCounter++,
                item_id: itemId,
                name: itemName,
                warehouse_id: wl.warehouse_id || null,
                warehouse_name: wl.warehouse_name || null,
                quantity: quantity,
                unit: unit,
                rate: rate,
                discount: discountAmount,
                tax_percentage: taxPercentage,
                tax_amount: taxAmount,
                total: total,
                warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : null,
                entry_type: currentEntryType || 'purchase'
            };
            purchaseItems.push(item);
            addItemToTable(item);
        });

        calculateTotals();
        syncCartToServer();
        if (purchaseItems.length > 0) {
            $('#payment-section').show(); $('#payment-amount-row').show();
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

    function addItemToTable(item) {
        $('#empty-items-state').hide();
        $('#items-list').show();
        
        // SCRAP: show total as minus (e.g. Rs -200) with red styling
        const totalVal = parseFloat(item.total);
        const totalDisplay = 'Rs ' + Math.round(totalVal);
        const totalClass = totalVal < 0 ? ' text-danger fw-bold' : '';
        
        const itemName = (item.name || ('Item #' + item.item_id)).replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const warehouseDisplay = (item.warehouse_name || '—').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const qtyUnitRate = item.quantity + ' ' + (item.unit || 'Unit') + ' · Rs ' + Math.round(parseFloat(item.rate));
        const discountTax = 'Rs ' + Math.round(parseFloat(item.discount)) + ' · ' + parseFloat(item.tax_percentage).toFixed(2) + '%';
        const row = `
            <tr class="purchase-item-row" data-item-id="${item.item_id}" data-row-id="${item.id}" data-entry-type="${item.entry_type || 'purchase'}" style="cursor: pointer;">
                <td class="text-muted small">${warehouseDisplay}</td>
                <td>
                    <div class="fw-bold text-dark">${itemName}</div>
                    <div class="text-muted small mt-1 d-block d-md-none">${qtyUnitRate}</div>
                    <div class="text-muted small mt-0 d-block d-md-none">${discountTax}</div>
                </td>
                <td class="d-none d-md-table-cell">${item.quantity}</td>
                <td class="d-none d-md-table-cell">${item.unit}</td>
                <td class="d-none d-md-table-cell">Rs ${Math.round(parseFloat(item.rate))}</td>
                <td class="d-none d-md-table-cell">Rs ${Math.round(parseFloat(item.discount))}</td>
                <td class="d-none d-md-table-cell">${parseFloat(item.tax_percentage).toFixed(2)}%</td>
                <td class="${totalClass}">${totalDisplay}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item" data-row-id="${item.id}">
                        <i class="ti ti-x"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#items-tbody').append(row);
    }

// Remove item
    $(document).on('click', '.remove-item', function(e) {
        e.stopPropagation();
        const rowId = $(this).data('row-id');
        purchaseItems = purchaseItems.filter(item => item.id !== rowId);
        $(this).closest('tr').remove();

        if ($('#items-tbody tr').length === 0) {
            $('#empty-items-state').show();
            $('#items-list').hide();
            $('#payment-section').hide(); $('#payment-amount-row').hide();
        }

        calculateTotals();
        syncCartToServer();
    });

    // Click row to edit (open add-item modal with same data as when item was added)
    $(document).on('click', '#items-tbody tr.purchase-item-row', function(e) {
        if ($(e.target).closest('.remove-item').length) return;
        var rowId = $(this).data('row-id');
        var item = purchaseItems.find(function(i) { return i.id === rowId; });
        if (!item) return;
        editingRowId = rowId;
        pendingEditItem = { warehouse_id: item.warehouse_id, quantity: item.quantity, rate: item.rate, unit: item.unit, discount: item.discount, tax_percentage: item.tax_percentage, warranty: item.warranty };
        $('#selected-item-id').val(item.item_id);
        $('#item-search').val(item.name || '');
        $('#item-rate').val(item.rate != null ? item.rate : '0');
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
        $('#add-item-modal').modal('show');
        loadItemDetails(item.item_id);
    });

    function resetItemModal() {
        editingRowId = null;
        pendingEditItem = null;
        $('#selected-item-id').val('');
        $('#item-search').val('');
        $('#item-quantity').val('');
        $('#item-quantity-input').val('1').hide();
        $('#item-unit').val('');
        $('#item-rate').val('0');
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
    }

    function calculateTotals() {
        let itemTotal = 0;
        purchaseItems.forEach(function(item) {
            itemTotal += parseFloat(item.total);
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
        
        // Set max payment amount to grand total (if negative e.g. all scrap, use 0)
        const grandTotalValue = Math.max(0, parseFloat(grandTotal));
        $('#payment_amount').attr('max', grandTotalValue);
        const currentPaymentAmount = parseFloat($('#payment_amount').val()) || 0;
        if (currentPaymentAmount > grandTotalValue) {
            $('#payment_amount').val(grandTotalValue);
        }
        
        // Update remaining amount
        updateRemainingAmount();
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

    // Form submission
    $('#purchaseForm').on('submit', function(e) {
        e.preventDefault();

        if (purchaseItems.length === 0) {
            alert('Please add at least one item');
            return false;
        }
        
        // Validate payment information
        const paymentMethod = $('#payment_method_id').val();
        const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
        const grandTotal = parseFloat($('#grand-total').text().replace('Rs ', '').replace(/,/g, '')) || 0;
        
        // If payment method is selected, amount must be greater than 0
        if (paymentMethod && paymentAmount <= 0) {
            alert('Please enter a valid payment amount.');
            $('#payment_amount').focus();
            return false;
        }
        
        // If payment amount exceeds grand total
        if (paymentAmount > grandTotal) {
            alert('Payment amount cannot exceed grand total (Rs ' + Math.round(grandTotal) + ')!');
            $('#payment_amount').focus();
            return false;
        }
        
        // If payment method requires bank account but none selected
        if (paymentMethod && paymentAmount > 0) {
            const selectedOption = $('#payment_method_id').find('option:selected');
            const requiresBank = selectedOption.data('requires-bank') == '1';
            if (requiresBank && !$('#bank_account_id').val()) {
                alert('Please select a bank account for this payment method.');
                $('#bank_account_id').focus();
                return false;
            }
        }

        // Prepare items data (include warehouse_id selected when adding item)
        const itemsData = purchaseItems.map(function(item) {
            return {
                item_id: item.item_id,
                warehouse_id: item.warehouse_id || '',
                quantity: item.quantity,
                unit: item.unit,
                rate: item.rate,
                discount: item.discount,
                tax_percentage: item.tax_percentage
            };
        });

        // Add items to form
        const formData = new FormData(this);
        itemsData.forEach(function(item, index) {
            Object.keys(item).forEach(function(key) {
                formData.append(`items[${index}][${key}]`, item[key]);
            });
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
                    // Clear purchase cart: items move from cart to purchase, so remove from UI
                    purchaseItems = [];
                    $('#items-tbody').empty();
                    $('#empty-items-state').show();
                    $('#items-list').hide();
                    $('#payment-section').hide(); $('#payment-amount-row').hide();
                    calculateTotals();
                    alert('Purchase created successfully! Invoice: ' + (response.invoice_no || ''));
                    window.location.href = '{{ route("all_purchases") }}';
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
