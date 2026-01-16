@extends('layouts.app')

@section('title', 'Create Purchase')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Purchase</h4>
            <h6>Add new purchase order</h6>
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
                    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
                        @csrf
                        
                        <!-- ACTIVE BRANCH Selector (Pill-shaped like Gemini design) -->
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill" style="border: 1px solid #0d6efd; background: #f8f9fa;">
                                <i class="ti ti-user me-2 text-muted"></i>
                                <span class="fw-bold me-2 text-uppercase" style="font-size: 12px;">ACTIVE BRANCH:</span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-primary p-0 text-decoration-none dropdown-toggle fw-bold" type="button" id="branchDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 14px;">
                                        @php
                                            $defaultBranchName = session('selected_branch_name');
                                            $defaultBranchCode = session('selected_branch_code');
                                            
                                            // If no branch in session, get logged-in user's branch
                                            if (!$defaultBranchName && auth()->check()) {
                                                $userBranch = \App\Models\Branch::where('user_id', auth()->id())
                                                    ->where('status', 'active')
                                                    ->first();
                                                if ($userBranch) {
                                                    $defaultBranchName = $userBranch->branch_name;
                                                    $defaultBranchCode = $userBranch->branch_code;
                                                }
                                            }
                                        @endphp
                                        <span id="selectedBranchName">{{ $defaultBranchName ?? 'Select Branch' }}</span>
                                        @if($defaultBranchCode)
                                            <span id="selectedBranchCode"> ({{ $defaultBranchCode }})</span>
                                        @endif
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="branchDropdown">
                                        @php
                                            // Filter branches based on user role
                                            if (auth()->check() && auth()->user()->role === 'admin') {
                                                // Admin can see all active branches
                                                $branches = \App\Models\Branch::where('status', 'active')->get();
                                            } else {
                                                // Regular users can only see their own branch
                                                $branches = \App\Models\Branch::where('user_id', auth()->id())
                                                    ->where('status', 'active')
                                                    ->get();
                                            }
                                            
                                            $currentBranchId = session('selected_branch_id');
                                            
                                            // If no branch in session, get logged-in user's branch
                                            if (!$currentBranchId && auth()->check()) {
                                                $userBranch = \App\Models\Branch::where('user_id', auth()->id())
                                                    ->where('status', 'active')
                                                    ->first();
                                                if ($userBranch) {
                                                    $currentBranchId = $userBranch->id;
                                                    // Set in session for display
                                                    if (!session('selected_branch_id')) {
                                                        session([
                                                            'selected_branch_id' => $userBranch->id,
                                                            'selected_branch_name' => $userBranch->branch_name,
                                                            'selected_branch_code' => $userBranch->branch_code
                                                        ]);
                                                    }
                                                }
                                            }
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
                            <input type="hidden" name="branch_id" id="purchaseBranchId" value="{{ session('selected_branch_id', $currentBranchId ?? '') }}" required>
                        </div>

                        <!-- Business Information Panel (Like Gemini Design) -->
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
                                        <span class="text-primary fw-bold" style="font-size: 16px;" id="purchase-number">INV #{{ str_pad(\App\Models\Purchase::max('id') + 1 ?? 1, 5, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <div style="font-size: 13px; color: #6c757d;">
                                        <span id="currentDateTime">{{ date('d/m/Y, H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden purchase date field -->
                        <input type="hidden" name="purchase_date" id="purchase_date" value="{{ date('Y-m-d') }}" required>

                        <!-- Supplier/Customer Information (Like Gemini Design) -->
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
                                                data-area="{{ $supplier->area ?? '' }}">
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
                                <input type="text" id="supplier_mobile" name="supplier_mobile" class="form-control" placeholder="03xx..." style="border-radius: 6px;">
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
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">REFERENCE</label>
                                <input type="text" name="reference" id="reference" class="form-control" placeholder="Enter reference number" style="border-radius: 6px;">
                            </div>
                        </div>

                        <!-- Items Summary Section -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">ITEMS SUMMARY</h5>
                            <div id="items-summary-container" class="text-center py-5" style="background: #f8f9fa; border-radius: 8px; min-height: 200px;">
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
                            </div>
                        </div>

                        <!-- Amount Summary -->
                        <div class="row mb-4">
                            <div class="col-md-6 offset-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">GROSS AMOUNT</span>
                                    <span class="fw-bold" id="gross-amount">Rs 0</span>
                                </div>
                                <div class="bg-primary text-white p-3 rounded mb-3">
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

                        <!-- Hidden fields for order tax, discount, shipping -->
                        <input type="hidden" name="order_tax" id="order_tax" value="0">
                        <input type="hidden" name="discount" id="discount" value="0">
                        <input type="hidden" name="shipping" id="shipping" value="0">
                        <input type="hidden" name="status" value="pending">

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
                                <i class="ti ti-check me-1"></i> Save Purchase
                            </button>
                        </div>
                    </form>
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
                <h5 class="modal-title fw-bold">
                    <i class="ti ti-shopping-cart me-2"></i>ITEM DETAILS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Product Name (Searchable/Selectable) -->
                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">PRODUCT NAME</label>
                    <div class="position-relative">
                        <input type="text" id="item-search" class="form-control" placeholder="Search or select product..." autocomplete="off" style="background-color: #f8f9fa; border-radius: 8px;">
                        <i class="ti ti-search position-absolute" style="right: 15px; top: 50%; transform: translateY(-50%); color: #999; pointer-events: none;"></i>
                        <!-- Search Results Dropdown -->
                        <div id="item-search-results" class="position-absolute w-100 bg-white border rounded shadow-lg" style="top: 100%; left: 0; z-index: 1050; max-height: 300px; overflow-y: auto; display: none; margin-top: 5px;">
                        </div>
                    </div>
                    <input type="hidden" id="selected-item-id">
                    <input type="hidden" id="selected-warehouse-id">
                </div>
                
                <!-- STOCK STATUS Section (Shows when item is selected) -->
                <div id="stock-status-section" class="mb-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0">
                            <i class="ti ti-settings me-2"></i>STOCK STATUS
                        </label>
                        <small class="text-muted" style="cursor: pointer;" id="stock-status-toggle">
                            DOUBLE-CLICK TO EXPAND
                        </small>
                    </div>
                    <div id="stock-status-content" class="border rounded p-2" style="background-color: #f8f9fa; max-height: 200px; overflow-y: auto; display: none;">
                        <div id="stock-status-list">
                            <p class="text-muted mb-0 small text-center">Loading stock status...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quantity and Unit Row -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">QUANTITY</label>
                        <select id="item-quantity" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
                            <option value="1">Qty</option>
                            <option value="0.5">0.5</option>
                            <option value="1">1</option>
                            <option value="1.5">1.5</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="150">150</option>
                            <option value="200">200</option>
                            <option value="250">250</option>
                            <option value="300">300</option>
                            <option value="400">400</option>
                            <option value="500">500</option>
                            <option value="600">600</option>
                            <option value="700">700</option>
                            <option value="800">800</option>
                            <option value="900">900</option>
                            <option value="1000">1000</option>
                        </select>
                        <input type="number" id="item-quantity-input" class="form-control mt-2" value="1" min="0.01" step="0.01" placeholder="Or enter custom quantity" style="background-color: #f8f9fa; border-radius: 8px; display: none;">
                        <small class="text-muted" style="font-size: 11px;">Select or enter quantity</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">UNIT</label>
                        <select id="item-unit" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
                            <option value="Can">Can</option>
                            <option value="Unit">Unit</option>
                            <option value="Box">Box</option>
                            <option value="Piece">Piece</option>
                            <option value="Kg">Kg</option>
                            <option value="Liter">Liter</option>
                            <option value="Pack">Pack</option>
                            <option value="Set">Set</option>
                        </select>
                    </div>
                </div>

                <!-- Sale Rate and Warranty Row -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">SALE RATE</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rs</span>
                            <input type="number" id="item-rate" class="form-control" value="0" step="0.01" min="0" placeholder="0" style="background-color: #f8f9fa; border-radius: 8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">WARRANTY</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <select id="warranty-value" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
                                    <option value="">-</option>
                                    <option value="7">7</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="60">60</option>
                                    <option value="90">90</option>
                                    <option value="180">180</option>
                                    <option value="365">365</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <select id="warranty-unit" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
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
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0">
                            <i class="ti ti-history me-2"></i>PURCHASE HISTORY
                        </label>
                        <a href="javascript:void(0)" class="text-primary text-decoration-none" id="hold-rate-link" style="display: none;">
                            Hold Rate to Apply
                        </a>
                    </div>
                    <div id="customer-history-content" class="p-3" style="background-color: #f8f9fa; border-radius: 8px; min-height: 60px; max-height: 150px; overflow-y: auto;">
                        <p class="text-muted mb-0 small">Select item to view history</p>
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
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold" id="confirm-entry" style="background-color: #0d6efd; border-radius: 8px; padding: 10px 30px;">
                    CONFIRM SELECTION
                </button>
            </div>
        </div>
    </div>
</div>

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
    mark {
        background: #ffeb3b;
        padding: 2px 4px;
        border-radius: 3px;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let purchaseItems = [];
    let itemCounter = 0;

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
        
        $('#supplier_mobile').val(phone);
        $('#supplier_address').val(address);
        $('#supplier_area').val(area);
    });
    
    // Auto-select supplier name when phone number is entered
    let supplierPhoneTimeout = null;
    $('#supplier_mobile').on('input', function() {
        const phone = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(supplierPhoneTimeout);
        
        // Only search if phone has at least 3 characters
        if (phone.length < 3) {
            return;
        }
        
        // Debounce search
        supplierPhoneTimeout = setTimeout(function() {
            $.ajax({
                url: '{{ route("purchases.suppliers.search.phone") }}',
                method: 'GET',
                data: { phone: phone },
                success: function(suppliers) {
                    if (suppliers.length > 0) {
                        // Auto-select first matching supplier
                        const supplier = suppliers[0];
                        $('#supplier_id').val(supplier.id);
                        
                        // Update address and area if available
                        if (supplier.address) {
                            $('#supplier_address').val(supplier.address);
                        }
                        if (supplier.area) {
                            $('#supplier_area').val(supplier.area);
                        }
                        
                        // Trigger change event to ensure all handlers fire
                        $('#supplier_id').trigger('change');
                    }
                },
                error: function(xhr) {
                    console.error('Error searching suppliers:', xhr);
                }
            });
        }, 500); // Wait 500ms after user stops typing
    });

    // Auto-select logged-in user's branch on page load if not already selected
    $(document).ready(function() {
        const currentBranchId = $('#purchaseBranchId').val();
        const currentBranchName = $('#selectedBranchName').text().trim();
        
        // If no branch is selected, try to get user's branch from PHP
        @if(auth()->check() && !session('selected_branch_id'))
            @php
                $userBranch = \App\Models\Branch::where('user_id', auth()->id())
                    ->where('status', 'active')
                    ->first();
            @endphp
            @if($userBranch)
                // Auto-select user's branch
                selectPurchaseBranch({{ $userBranch->id }}, '{{ $userBranch->branch_name }}', '{{ $userBranch->branch_code ?? '' }}');
            @endif
        @endif
    });
    
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
                
                // Clear any existing items from table
                purchaseItems = [];
                $('#items-tbody').empty();
                $('#empty-items-state').show();
                $('#items-list').hide();
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
        const branchId = $('#purchaseBranchId').val();
        if (branchId) {
            loadBranchWarehouseInfo(branchId);
        }
        
        // Start updating date/time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
    });

    // Handle "Add New Item" button click - check branch first
    $('#add-new-item-btn').on('click', function() {
        const branchId = $('#purchaseBranchId').val();
        
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before adding items.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Open the modal
        $('#add-item-modal').modal('show');
    });
    
    // Reset form when modal opens
    $('#add-item-modal').on('show.bs.modal', function() {
        const branchId = $('#purchaseBranchId').val();
        
        // Reset form when modal opens
        $('#item-search').val('');
        $('#selected-item-id').val('');
        $('#selected-warehouse-id').val('');
        $('#item-quantity').val('1');
        $('#item-unit').val('Unit');
        $('#item-rate').val('0');
        $('#warranty-value').val('');
        $('#warranty-unit').val('Days');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to view history</p>');
        $('#item-search-results').hide();
        $('#stock-status-section').hide();
        $('#stock-status-content').hide();
        
        // Focus on search input
        setTimeout(function() {
            $('#item-search').focus();
        }, 300);
    });
    
    // Product name search with dropdown
    let itemSearchTimeout = null;
    $('#item-search').on('input', function() {
        const query = $(this).val().trim();
        const branchId = $('#purchaseBranchId').val();
        const resultsDiv = $('#item-search-results');
        
        // Clear previous timeout
        clearTimeout(itemSearchTimeout);
        
        if (query.length < 2) {
            resultsDiv.hide();
            $('#selected-item-id').val('');
            return;
        }
        
        // Debounce search
        itemSearchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('purchases.items.ajax.search') }}",
                method: 'GET',
                data: {
                    q: query,
                    branch_id: branchId,
                    limit: 10
                },
                success: function(results) {
                    if (results.length === 0) {
                        resultsDiv.html('<div class="p-3 text-muted text-center">No results found</div>');
                    } else {
                        let html = '';
                        results.forEach(function(result) {
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
                                // Item result
                                const item = result.item;
                                const itemName = item.short_disc || item.pro_dis || item.bar_code || 'N/A';
                                const partNumber = item.partnumber_item?.name || '';
                                const manufacturer = item.vehical_item?.manutacturer_vehical?.name || '';
                                const model = item.vehical_item?.model_vehical?.name || '';
                                
                                let displayName = itemName;
                                if (partNumber) displayName += ' - ' + partNumber;
                                if (manufacturer) displayName += ' ' + manufacturer;
                                if (model) displayName += ' ' + model;
                                
                                html += `
                                    <div class="p-2 border-bottom item-search-result" 
                                         data-type="item"
                                         data-id="${item.id}" 
                                         data-name="${displayName.replace(/"/g, '&quot;')}"
                                         data-rate="${item.packing_purchase_rate || 0}"
                                         data-unit="${item.unit || 'Unit'}"
                                         style="cursor: pointer; transition: background 0.2s; padding-left: 30px;">
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-package me-2 text-muted" style="font-size: 12px;"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">${displayName}</div>
                                                <div class="small text-muted">
                                                    ${item.bar_code ? 'Barcode: ' + item.bar_code : ''}
                                                    ${item.on_hand ? ' | Stock: ' + item.on_hand : ''}
                                                    ${result.warehouse_id ? ' | Warehouse ID: ' + result.warehouse_id : ''}
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
                error: function() {
                    resultsDiv.html('<div class="p-3 text-danger text-center">Error searching items</div>');
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
            const itemRate = $(this).data('rate');
            const itemUnit = $(this).data('unit');
            const warehouseId = $(this).closest('.item-search-result').data('warehouse-id');
            
            $('#item-search').val(itemName);
            $('#selected-item-id').val(itemId);
            $('#item-unit').val(itemUnit || 'Unit');
            $('#item-search-results').hide();
            
            // Load full item details to get total_price and warehouse
            $.ajax({
                url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
                method: 'GET',
                success: function(response) {
                    // Use total_price if available, otherwise use rate
                    const itemRate = response.total_price || response.rate || itemRate || 0;
                    $('#item-rate').val(parseFloat(itemRate).toFixed(2));
                    
                    // Auto-select warehouse if available (from response or from search result)
                    const finalWarehouseId = response.warehouse_id || warehouseId;
                    if (finalWarehouseId) {
                        $('#selected-warehouse-id').val(finalWarehouseId);
                    }
                    
                    // Load stock status to show warehouse options and auto-select
                    loadItemStockStatus(itemId);
                    
                    // Load purchase history
                    loadCustomerHistory(itemId);
                },
                error: function() {
                    // Fallback to basic data if API fails
                    $('#item-rate').val(parseFloat(itemRate || 0).toFixed(2));
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
        $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">Loading stock status...</p>');
        
        $.ajax({
            url: '{{ route("purchases.items.stock.status", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(stockData) {
                if (stockData.length === 0) {
                    $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">No stock found</p>');
                    return;
                }
                
                let html = '';
                stockData.forEach(function(stock) {
                    if (stock.type === 'branch') {
                        // Branch total
                        html += `
                            <div class="p-2 mb-1 border-bottom stock-branch-item" style="background-color: #fff;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold">${stock.display}</div>
                                    <div class="text-muted">
                                        <span class="fw-bold">${stock.cartons}C</span> | <span class="fw-bold">${stock.loose}L</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (stock.type === 'warehouse') {
                        // Warehouse item - check if selected
                        const isSelected = $('#selected-warehouse-id').val() == stock.id;
                        html += `
                            <div class="p-2 mb-1 stock-warehouse-item ${isSelected ? 'bg-primary text-white' : ''}" 
                                 data-warehouse-id="${stock.id}"
                                 data-branch-id="${stock.branch_id}"
                                 style="cursor: pointer; transition: all 0.2s; ${isSelected ? '' : 'background-color: #f0f0f0;'}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">${isSelected ? '✓' : ''}</span>
                                        <span class="${isSelected ? 'text-white' : ''}">${stock.display}</span>
                                    </div>
                                    <div class="${isSelected ? 'text-white' : 'text-muted'}">
                                        <span class="fw-bold">${stock.cartons} C</span> | <span class="fw-bold">${stock.loose} L</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });
                
                $('#stock-status-list').html(html);
            },
            error: function() {
                $('#stock-status-list').html('<p class="text-danger mb-0 small text-center">Error loading stock status</p>');
            }
        });
    }
    
    // Toggle stock status expand/collapse
    $('#stock-status-toggle').on('dblclick', function() {
        $('#stock-status-content').slideToggle();
    });
    
    // Select warehouse from stock status
    $(document).on('click', '.stock-warehouse-item', function() {
        // Remove previous selection
        $('.stock-warehouse-item').removeClass('bg-primary text-white').addClass('bg-light');
        $('.stock-warehouse-item').find('span:first').text('');
        
        // Select this warehouse
        $(this).removeClass('bg-light').addClass('bg-primary text-white');
        $(this).find('span:first').html('✓');
        
        const warehouseId = $(this).data('warehouse-id');
        $('#selected-warehouse-id').val(warehouseId);
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
                $('#item-search').val(response.name);
                
                // Set rate - use total_price if available, otherwise use rate
                const itemRate = response.total_price || response.rate || 0;
                $('#item-rate').val(parseFloat(itemRate).toFixed(2));
                
                // Set unit from item
                $('#item-unit').val(response.unit || 'Unit');
                
                // Auto-select warehouse if available
                if (response.warehouse_id) {
                    $('#selected-warehouse-id').val(response.warehouse_id);
                    // Update warehouse selection in stock status
                    $('.stock-warehouse-item[data-warehouse-id="' + response.warehouse_id + '"]')
                        .removeClass('bg-light')
                        .addClass('bg-primary text-white')
                        .find('span:first')
                        .html('✓');
                }
                
                // Load stock status to show warehouse options
                loadItemStockStatus(itemId);
                
                // Load customer history for this item
                loadCustomerHistory(itemId);
                
                $('#search-results').hide();
            }
        });
    }

    // Load customer history for selected item
    function loadCustomerHistory(itemId) {
        // TODO: Implement customer history API call
        // For now, show placeholder
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Loading history...</p>');
        $('#hold-rate-link').hide();
        
        // Simulate history loading (replace with actual API call)
        setTimeout(function() {
            $('#customer-history-content').html(`
                <div class="small">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Last Purchase: Rs 1,250</span>
                        <span class="text-muted">2 days ago</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Avg Rate: Rs 1,200</span>
                        <span class="text-muted">Last 30 days</span>
                    </div>
                </div>
            `);
            $('#hold-rate-link').show();
        }, 500);
    }

    // Hold rate to apply
    $('#hold-rate-link').on('click', function() {
        // Get the last purchase rate from history and apply it
        const historyText = $('#customer-history-content').text();
        const rateMatch = historyText.match(/Rs\s*([\d,]+)/);
        if (rateMatch) {
            const rate = rateMatch[1].replace(/,/g, '');
            $('#item-rate').val(parseFloat(rate).toFixed(2));
        }
    });

    // Suggest rate button
    $('#suggest-rate').on('click', function() {
        const itemId = $('#selected-item-id').val();
        if (itemId) {
            loadItemDetails(itemId);
        }
    });

    // Quantity dropdown change - show custom input if "Qty" selected
    $('#item-quantity').on('change', function() {
        if ($(this).val() === '1' && $(this).find('option:selected').text() === 'Qty') {
            $('#item-quantity-input').show().focus();
        } else {
            $('#item-quantity-input').hide();
        }
    });

    // Use custom quantity input if provided
    $('#item-quantity-input').on('input', function() {
        const customQty = $(this).val();
        if (customQty && customQty > 0) {
            $('#item-quantity').val(customQty);
        }
    });

    // Confirm entry
    $('#confirm-entry').on('click', function() {
        const itemId = $('#selected-item-id').val();
        let quantity = parseFloat($('#item-quantity').val()) || 0;
        
        // If custom quantity input is visible and has value, use that
        if ($('#item-quantity-input').is(':visible') && $('#item-quantity-input').val()) {
            quantity = parseFloat($('#item-quantity-input').val()) || 0;
        }
        
        const unit = $('#item-unit').val();
        const rate = parseFloat($('#item-rate').val()) || 0;
        const discount = parseFloat($('#item-discount').val()) || 0;
        const discountType = $('#discount-type').val();
        const taxPercentage = parseFloat($('#item-tax').val()) || 0;
        const itemName = $('#item-search').val();
        const warrantyValue = $('#warranty-value').val();
        const warrantyUnit = $('#warranty-unit').val();

        if (!itemId || quantity <= 0 || rate <= 0) {
            alert('Please select an item and enter valid quantity and rate');
            return;
        }

        // Calculate discount amount
        let discountAmount = discount;
        if (discountType === 'percent') {
            discountAmount = (quantity * rate * discount) / 100;
        }

        // Calculate totals
        const subtotal = (quantity * rate) - discountAmount;
        const taxAmount = (subtotal * taxPercentage) / 100;
        const total = subtotal + taxAmount;

        // Add to items array
        const item = {
            id: itemCounter++,
            item_id: itemId,
            name: itemName,
            quantity: quantity,
            unit: unit,
            rate: rate,
            discount: discountAmount,
            tax_percentage: taxPercentage,
            tax_amount: taxAmount,
            total: total,
            warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : null
        };

        purchaseItems.push(item);
        addItemToTable(item);
        resetItemModal();
        $('#add-item-modal').modal('hide');
        calculateTotals();
    });

    function addItemToTable(item) {
        $('#empty-items-state').hide();
        $('#items-list').show();
        
        const row = `
            <tr data-item-id="${item.item_id}" data-row-id="${item.id}">
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>${item.unit}</td>
                <td>Rs ${parseFloat(item.rate).toFixed(2)}</td>
                <td>Rs ${parseFloat(item.discount).toFixed(2)}</td>
                <td>${parseFloat(item.tax_percentage).toFixed(2)}%</td>
                <td>Rs ${parseFloat(item.total).toFixed(2)}</td>
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
    $(document).on('click', '.remove-item', function() {
        const rowId = $(this).data('row-id');
        purchaseItems = purchaseItems.filter(item => item.id !== rowId);
        $(this).closest('tr').remove();
        
        if ($('#items-tbody tr').length === 0) {
            $('#empty-items-state').show();
            $('#items-list').hide();
        }
        
        calculateTotals();
    });

    function resetItemModal() {
        $('#selected-item-id').val('');
        $('#item-search').val('');
        $('#item-quantity').val('1');
        $('#item-quantity-input').val('1').hide();
        $('#item-unit').val('Can');
        $('#item-rate').val('0');
        $('#warranty-value').val('');
        $('#warranty-unit').val('Days');
        $('#item-discount').val('0');
        $('#discount-type').val('amount');
        $('#item-tax').val('0');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to see history</p>');
        $('#hold-rate-link').hide();
        $('#search-results').hide();
    }

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

    // Form submission
    $('#purchaseForm').on('submit', function(e) {
        e.preventDefault();

        if (purchaseItems.length === 0) {
            alert('Please add at least one item');
            return;
        }

        // Prepare items data
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
            data: formData,
            processData: false,
            contentType: false,
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
                    alert('Error saving purchase. Please try again.');
                }
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
