@extends('layouts.app')

@section('title', 'Create Sales')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Sales</h4>
            <h6>Add new sales order</h6>
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
                    <form action="{{ route('sales.store') }}" method="POST" id="salesForm">
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
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="selectSalesBranch({{ $branch->id }}, '{{ $branch->branch_name }}', '{{ $branch->branch_code ?? '' }}')">
                                                {{ $branch->branch_name }} 
                                                @if($branch->branch_code) ({{ $branch->branch_code }}) @endif
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <input type="hidden" name="branch_id" id="salesBranchId" value="{{ session('selected_branch_id') }}" required>
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
                                        <span class="text-primary fw-bold" style="font-size: 16px;" id="sales-number">INV #{{ str_pad(1, 5, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <div style="font-size: 13px; color: #6c757d;">
                                        <span id="currentDateTime">{{ date('d/m/Y, H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Hidden sales date field -->
                        <input type="hidden" name="sale_date" id="sale_date" value="{{ date('Y-m-d') }}" required>
                        <!-- Customer Information (Like Gemini Design) -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">CUSTOMER NAME</label>
                                <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required style="border-radius: 6px;">
                                    <option value="">Party Name</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                                data-name="{{ $customer->names[0] ?? '' }}" 
                                                data-phone="{{ $customer->phones[0] ?? '' }}"
                                                data-company="{{ $customer->company ?? '' }}"
                                                data-address="{{ $customer->address ?? '' }}"
                                                data-area="{{ $customer->area ?? '' }}">
                                            {{ $customer->names[0] ?? 'N/A' }} @if($customer->company) - {{ $customer->company }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">MOBILE NUMBER</label>
                                <input type="text" id="customer_mobile" name="customer_mobile" class="form-control" placeholder="03xx..." style="border-radius: 6px;">
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">ADDRESS</label>
                                <input type="text" id="customer_address" name="customer_address" class="form-control" placeholder="Shop/House #" style="border-radius: 6px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">AREA</label>
                                <input type="text" id="customer_area" name="customer_area" class="form-control" placeholder="Location/City" style="border-radius: 6px;">
                            </div>
                        </div>
                        <!-- Reference (Optional) -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">REFERENCE</label>
                                <input type="text" name="reference" id="reference" class="form-control" placeholder="Enter reference number" value="{{ $purchaseData['reference'] ?? '' }}" style="border-radius: 6px;">
                            </div>
                        </div>
                        
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-check me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif
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
                            <button type="button" class="btn btn-primary btn-lg" id="add-new-item-btn" data-bs-toggle="modal" data-bs-target="#add-item-modal">
                                <i class="ti ti-plus me-2"></i>ADD NEW ITEM
                            </button>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('all_sales') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check me-1"></i> Save Sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- YouTube-Style Search & Filter Modal for Sales -->
<div class="modal fade" id="sales-item-search-modal" tabindex="-1" aria-labelledby="salesItemSearchModalLabel" aria-hidden="true">
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
                        <input type="text" id="sales-item-search-input" class="form-control form-control-lg ps-5" 
                            placeholder="Search by product name, barcode, category, part number, vehicle, model, year, specifications..." 
                            style="border-radius: 24px; border: 2px solid #e0e0e0;">
                        <i class="fas fa-search position-absolute" style="left: 20px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                        <button type="button" id="sales-clear-search" class="btn btn-link position-absolute d-none" 
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
                        <button type="button" class="btn btn-sm" id="sales-advanced-filters-toggle" style="border-radius: 16px; white-space: nowrap;">
                            <i class="fas fa-filter me-1"></i> More Filters
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger d-none" id="sales-clear-all-filters" style="border-radius: 16px; white-space: nowrap;">
                            <i class="fas fa-times me-1"></i> Clear All
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters Panel (Collapsible) -->
                <div class="collapse" id="salesAdvancedFiltersPanel">
                    <div class="p-4 border-bottom" style="background: #fff;">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Category</label>
                                <select class="form-select form-select-sm" id="sales-filter-category">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Manufacturer</label>
                                <select class="form-select form-select-sm" id="sales-filter-manufacturer">
                                    <option value="">All Manufacturers</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Part Number</label>
                                <select class="form-select form-select-sm" id="sales-filter-part-number">
                                    <option value="">All Part Numbers</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Technology</label>
                                <select class="form-select form-select-sm" id="sales-filter-technology">
                                    <option value="">All Technologies</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Grade</label>
                                <select class="form-select form-select-sm" id="sales-filter-grade">
                                    <option value="">All Grades</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Volt</label>
                                <select class="form-select form-select-sm" id="sales-filter-volt">
                                    <option value="">All Volts</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">CCA</label>
                                <select class="form-select form-select-sm" id="sales-filter-cca">
                                    <option value="">All CCAs</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Supplier</label>
                                <select class="form-select form-select-sm" id="sales-filter-supplier">
                                    <option value="">All Suppliers</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Rack</label>
                                <select class="form-select form-select-sm" id="sales-filter-rack">
                                    <option value="">All Racks</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Min Price</label>
                                <input type="number" class="form-control form-control-sm" id="sales-filter-min-price" placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Max Price</label>
                                <input type="number" class="form-control form-control-sm" id="sales-filter-max-price" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Info -->
                <div class="row g-2 px-4 py-3 border-bottom" style="background: #f8f9fa;">
                    <div class="col-6">
                        <div class="p-2 rounded" style="background-color: #f0fff4; border: 1px solid #d1fae5;">
                            <small class="text-success fw-bold d-block mb-1" style="font-size: 0.7rem;">WAREHOUSE</small>
                            <div class="fw-bold text-success" id="sales-warehouse-stock">0 Units</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background-color: #fffaf0; border: 1px solid #feebc8;">
                            <small class="text-warning fw-bold d-block mb-1" style="font-size: 0.7rem; color: #c05621 !important;">SHOP</small>
                            <div class="fw-bold" style="color: #c05621;" id="sales-shop-stock">0 Units</div>
                        </div>
                    </div>
                </div>

                <!-- Results Container -->
                <div class="p-4" style="max-height: 400px; overflow-y: auto;">
                    <div id="sales-search-results-container">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                            <p>Start typing to search items or use filters above</p>
                        </div>
                    </div>
                    <div id="sales-no-results" class="text-center text-muted py-5 d-none">
                        <i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.3;"></i>
                        <p>No items found. Try adjusting your search or filters.</p>
                    </div>
                    <div id="sales-loading-results" class="text-center py-5 d-none">
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
                        <input type="text" id="item-search" class="form-control" placeholder="Search by product name, barcode, category, part number..." autocomplete="off" style="background-color: #f8f9fa; border-radius: 8px;">
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
                        <select id="sales-item-quantity" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
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
                        <input type="number" id="sales-item-quantity-input" class="form-control mt-2" value="1" min="0.01" step="0.01" placeholder="Or enter custom quantity" style="background-color: #f8f9fa; border-radius: 8px; display: none;">
                        <small class="text-muted" style="font-size: 11px;">Select or enter quantity</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">UNIT</label>
                        <select id="sales-item-unit" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
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
                            <input type="number" id="sales-item-rate" class="form-control" value="0" step="0.01" min="0" placeholder="0" style="background-color: #f8f9fa; border-radius: 8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">WARRANTY</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <select id="sales-warranty-value" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
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
                                <select id="sales-warranty-unit" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
                                    <option value="Days">Days</option>
                                    <option value="Weeks">Weeks</option>
                                    <option value="Months">Months</option>
                                    <option value="Years">Years</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer History Section -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0">
                            <i class="ti ti-history me-2"></i>CUSTOMER HISTORY
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
@endsection
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
    let salesItems = [];
    let itemCounter = 0;

    // Customer change handler
    $('#customer_id').on('change', function() {
        const selected = $(this).find('option:selected');
        const name = selected.data('name') || '';
        const phone = selected.data('phone') || '';
        const address = selected.data('address') || '';
        const area = selected.data('area') || '';
        
        $('#customer_mobile').val(phone);
        $('#customer_address').val(address);
        $('#customer_area').val(area);
    });

    // Branch selection for sales
    function selectSalesBranch(branchId, branchName, branchCode) {
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
        $('#salesBranchId').val(branchId);
        
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
                salesItems = [];
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
    window.selectSalesBranch = selectSalesBranch;

    // Load warehouse info for selected branch
    function loadBranchWarehouseInfo(branchId) {
        $.ajax({
            url: '{{ route("warehouses.by.branch", ":id") }}'.replace(':id', branchId),
            method: 'GET',
            success: function(warehouse) {
                if (warehouse && !warehouse.error) {
                    // Warehouse info loaded successfully
                    console.log('Warehouse loaded:', warehouse);
                } else {
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
        const branchId = $('#salesBranchId').val();
        if (branchId) {
            loadBranchWarehouseInfo(branchId);
        }
        
        // Start updating date/time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
    });

    // ========== YouTube-Style Search Modal Functionality ==========
    const salesSearchInput = $('#sales-item-search-input');
    const salesClearSearchBtn = $('#sales-clear-search');
    const salesSearchModal = $('#sales-item-search-modal');
    const salesResultsContainer = $('#sales-search-results-container');
    const salesNoResults = $('#sales-no-results');
    const salesLoadingResults = $('#sales-loading-results');
    const salesAdvancedFiltersToggle = $('#sales-advanced-filters-toggle');
    const salesClearAllFiltersBtn = $('#sales-clear-all-filters');
    
    // Filter state
    let salesActiveFilters = {};
    let salesFilterOptions = {};
    let salesSearchTimeout = null;
    
    // Initialize: Load filter options when modal opens
    salesSearchModal.on('show.bs.modal', function() {
        if (Object.keys(salesFilterOptions).length === 0) {
            loadSalesFilterOptions();
        }
        salesSearchInput.focus();
        
        // Clear previous search and show initial state
        salesSearchInput.val('');
        salesClearSearchBtn.addClass('d-none');
        salesResultsContainer.html(`
            <div class="text-center text-muted py-5">
                <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                <p>Start typing to search items or use filters above</p>
            </div>
        `);
        salesNoResults.hide();
        salesLoadingResults.hide();
    });
    
    // Load filter options
    function loadSalesFilterOptions() {
        $.ajax({
            url: "{{ route('sales.filter.options') }}",
            success: function(data) {
                salesFilterOptions = data;
                populateSalesFilterDropdowns(data);
            },
            error: function(xhr) {
                console.error('Error loading filter options:', xhr);
            }
        });
    }
    
    // Populate filter dropdowns
    function populateSalesFilterDropdowns(data) {
        if (data.categories) {
            data.categories.forEach(cat => {
                $('#sales-filter-category').append(`<option value="${cat.id}">${cat.name}</option>`);
            });
        }
        if (data.manufacturers) {
            data.manufacturers.forEach(man => {
                $('#sales-filter-manufacturer').append(`<option value="${man.id}">${man.name}</option>`);
            });
        }
        if (data.part_numbers) {
            data.part_numbers.forEach(pn => {
                $('#sales-filter-part-number').append(`<option value="${pn.id}">${pn.name}</option>`);
            });
        }
        if (data.technologies) {
            data.technologies.forEach(tech => {
                $('#sales-filter-technology').append(`<option value="${tech.id}">${tech.name}</option>`);
            });
        }
        if (data.grades) {
            data.grades.forEach(grade => {
                $('#sales-filter-grade').append(`<option value="${grade.id}">${grade.name}</option>`);
            });
        }
        if (data.volts) {
            data.volts.forEach(volt => {
                $('#sales-filter-volt').append(`<option value="${volt.id}">${volt.name}</option>`);
            });
        }
        if (data.ccas) {
            data.ccas.forEach(cca => {
                $('#sales-filter-cca').append(`<option value="${cca.id}">${cca.name}</option>`);
            });
        }
        if (data.suppliers) {
            data.suppliers.forEach(supplier => {
                $('#sales-filter-supplier').append(`<option value="${supplier}">${supplier}</option>`);
            });
        }
        if (data.racks) {
            data.racks.forEach(rack => {
                $('#sales-filter-rack').append(`<option value="${rack}">${rack}</option>`);
            });
        }
    }
    
    // Live search with debounce
    salesSearchInput.on('input', function() {
        const query = $(this).val().trim();
        salesClearSearchBtn.toggleClass('d-none', !query);
        
        clearTimeout(salesSearchTimeout);
        salesSearchTimeout = setTimeout(function() {
            if (query.length >= 2 || Object.keys(salesActiveFilters).length > 0) {
                performSalesSearch();
            } else {
                salesResultsContainer.html(`
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                        <p>Start typing to search items or use filters above</p>
                    </div>
                `);
            }
        }, 500);
    });
    
    // Clear search
    salesClearSearchBtn.on('click', function() {
        salesSearchInput.val('');
        $(this).addClass('d-none');
        performSalesSearch();
    });
    
    // Filter chip clicks
    $('.filter-chip').on('click', function() {
        const filter = $(this).data('filter');
        const value = $(this).data('value');
        
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            delete salesActiveFilters[filter];
        } else {
            $('.filter-chip[data-filter="' + filter + '"]').removeClass('active');
            $(this).addClass('active');
            salesActiveFilters[filter] = value;
        }
        
        updateSalesClearAllButton();
        performSalesSearch();
    });
    
    // Advanced filter changes
    $('#sales-filter-category, #sales-filter-manufacturer, #sales-filter-part-number, #sales-filter-technology, #sales-filter-grade, #sales-filter-volt, #sales-filter-cca, #sales-filter-supplier, #sales-filter-rack, #sales-filter-min-price, #sales-filter-max-price').on('change input', function() {
        const filterId = $(this).attr('id').replace('sales-filter-', '').replace('-', '_');
        const value = $(this).val();
        
        if (value) {
            salesActiveFilters[filterId] = value;
        } else {
            delete salesActiveFilters[filterId];
        }
        
        updateSalesClearAllButton();
        performSalesSearch();
    });
    
    // Toggle advanced filters
    salesAdvancedFiltersToggle.on('click', function() {
        $('#salesAdvancedFiltersPanel').collapse('toggle');
    });
    
    // Clear all filters
    salesClearAllFiltersBtn.on('click', function() {
        salesActiveFilters = {};
        $('.filter-chip').removeClass('active');
        $('#sales-filter-category, #sales-filter-manufacturer, #sales-filter-part-number, #sales-filter-technology, #sales-filter-grade, #sales-filter-volt, #sales-filter-cca, #sales-filter-supplier, #sales-filter-rack').val('');
        $('#sales-filter-min-price, #sales-filter-max-price').val('');
        salesSearchInput.val('');
        salesClearSearchBtn.addClass('d-none');
        updateSalesClearAllButton();
        performSalesSearch();
    });
    
    // Update clear all button visibility
    function updateSalesClearAllButton() {
        const hasFilters = Object.keys(salesActiveFilters).length > 0 || salesSearchInput.val().trim().length > 0;
        salesClearAllFiltersBtn.toggleClass('d-none', !hasFilters);
    }
    
    // Perform search
    function performSalesSearch() {
        const query = salesSearchInput.val().trim();
        
        // Build search params
        const params = {
            q: query,
            limit: 50
        };
        
        // Add active filters
        Object.keys(salesActiveFilters).forEach(key => {
            params[key] = salesActiveFilters[key];
        });
        
        // Show loading
        salesResultsContainer.hide();
        salesNoResults.hide();
        salesLoadingResults.show();
        
        // Get selected branch ID
        const branchId = $('#salesBranchId').val();
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
            url: "{{ route('sales.items.ajax.search') }}",
            data: params,
            success: function(items) {
                salesLoadingResults.hide();
                
                if (items.length === 0) {
                    salesNoResults.show();
                    salesResultsContainer.hide();
                    return;
                }
                
                salesNoResults.hide();
                salesResultsContainer.show();
                
                let html = '';
                const searchTerm = query.toLowerCase();
                const regex = searchTerm ? new RegExp(searchTerm.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&'), 'gi') : null;
                
                items.forEach(itemData => {
                    // Extract item and warehouse information
                    const item = itemData.item || itemData;
                    const itemName = itemData.item_name || item.short_disc || item.pro_dis || item.bar_code || 'N/A';
                    const partNumber = itemData.part_number || item.partnumber_item?.name || '';
                    const manufacturer = item.vehical_item?.manutacturer_vehical?.name || '';
                    const model = item.vehical_item?.model_vehical?.name || '';
                    const yearFrom = item.vehical_item?.year_from || '';
                    const yearTo = item.vehical_item?.year_to || '';
                    const yearDisplay = yearFrom && yearTo ? `${yearFrom}-${yearTo}` : (yearFrom || yearTo || '');
                    
                    // Warehouse stock and quantity
                    const warehouseQuantity = parseFloat(itemData.warehouse_quantity || 0);
                    const availableQuantity = parseFloat(itemData.available_quantity || 0);
                    const cartons = parseInt(itemData.cartons || 0);
                    const loose = parseFloat(itemData.loose || 0);
                    
                    // Price information - prioritize sale_price
                    const salePrice = parseFloat(itemData.sale_price || item.sale_price || 0);
                    const calculatedPricePerUnit = parseFloat(itemData.calculated_price_per_unit || 0);
                    const totalCost = parseFloat(itemData.total_cost || 0);
                    const pricePerUnit = parseFloat(itemData.price_per_unit || item.price_per_unit || 0);
                    const packingPurchaseRate = parseFloat(itemData.packing_purchase_rate || item.packing_purchase_rate || 0);
                    
                    // Priority: sale_price > calculated_price_per_unit > price_per_unit > packing_purchase_rate
                    let displayPrice = 0;
                    if (salePrice > 0) {
                        displayPrice = salePrice;
                    } else if (calculatedPricePerUnit > 0) {
                        displayPrice = calculatedPricePerUnit;
                    } else if (pricePerUnit > 0) {
                        displayPrice = pricePerUnit;
                    } else if (packingPurchaseRate > 0) {
                        displayPrice = packingPurchaseRate;
                    }
                    
                    const barCode = itemData.bar_code || item.bar_code || '';
                    const serialNumber = itemData.serial_number || item.serial_number || '';
                    const categoryName = itemData.category_name || (item.category ? item.category.name : '');
                    const unit = itemData.unit || item.unit || 'Unit';
                    
                    // Highlight search term
                    let displayItemName = itemName;
                    let displayPartNumber = partNumber;
                    let displayManufacturer = manufacturer;
                    let displayModel = model;
                    let displayYear = yearDisplay;
                    let displayCategory = categoryName;
                    
                    if (regex) {
                        displayItemName = itemName.replace(regex, match => `<mark>${match}</mark>`);
                        displayPartNumber = partNumber.replace(regex, match => `<mark>${match}</mark>`);
                        displayManufacturer = manufacturer.replace(regex, match => `<mark>${match}</mark>`);
                        displayModel = model.replace(regex, match => `<mark>${match}</mark>`);
                        displayYear = yearDisplay.replace(regex, match => `<mark>${match}</mark>`);
                        displayCategory = categoryName.replace(regex, match => `<mark>${match}</mark>`);
                    }
                    
                    html += `
                        <div class="item-card" data-id="${item.id}" 
                             data-name="${itemName.replace(/"/g, '&quot;')}"
                             data-price="${displayPrice}"
                             data-stock="${warehouseQuantity}"
                             data-warehouse-id="${itemData.warehouse_id || ''}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">${displayItemName}</h6>
                                    ${displayPartNumber ? `<div class="small text-muted mb-1"><i class="fas fa-tag me-1"></i>${displayPartNumber}</div>` : ''}
                                    ${displayCategory ? `<div class="small text-info mb-1"><i class="fas fa-folder me-1"></i>${displayCategory}</div>` : ''}
                                    <div class="small text-muted mb-2">
                                        ${displayManufacturer ? displayManufacturer + ' ' : ''}${displayModel}${displayYear ? ' (' + displayYear + ')' : ''}
                                    </div>
                                    <div class="d-flex gap-3 small mb-2">
                                        ${barCode ? `<span><i class="fas fa-barcode me-1"></i>${barCode}</span>` : ''}
                                        ${serialNumber ? `<span><i class="fas fa-hashtag me-1"></i>${serialNumber}</span>` : ''}
                                    </div>
                                    <div class="d-flex gap-3 small">
                                        <span class="text-${warehouseQuantity > 0 ? 'success' : 'danger'}">
                                            <i class="fas fa-box me-1"></i>Stock: ${warehouseQuantity.toFixed(2)} ${unit}
                                        </span>
                                        ${cartons > 0 || loose > 0 ? `<span class="text-info">
                                            <i class="fas fa-cubes me-1"></i>${cartons}C | ${loose.toFixed(2)}L
                                        </span>` : ''}
                                        ${availableQuantity !== warehouseQuantity ? `<span class="text-warning">
                                            <i class="fas fa-lock me-1"></i>Available: ${availableQuantity.toFixed(2)}
                                        </span>` : ''}
                                    </div>
                                    ${totalCost > 0 ? `<div class="small text-muted mt-1">
                                        <i class="fas fa-calculator me-1"></i>Total Cost: Rs ${totalCost.toFixed(2)}
                                    </div>` : ''}
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary mb-1">Rs ${displayPrice.toFixed(2)}</div>
                                    ${pricePerUnit > 0 && pricePerUnit !== displayPrice ? `<div class="small text-muted mb-1">Per Unit: Rs ${pricePerUnit.toFixed(2)}</div>` : ''}
                                    <button class="btn btn-sm btn-primary sales-add-item-btn">
                                        <i class="fas fa-plus me-1"></i>Select
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                salesResultsContainer.html(html);
                
                // Update stock info
                updateSalesStockInfo(items);
            },
            error: function(xhr) {
                salesLoadingResults.hide();
                console.error('Search error:', xhr);
                salesResultsContainer.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading items. Please try again.
                    </div>
                `);
            }
        });
    }
    
    // Update stock info
    function updateSalesStockInfo(items) {
        let totalWarehouseStock = 0;
        let totalAvailableStock = 0;
        
        items.forEach(itemData => {
            const item = itemData.item || itemData;
            const warehouseQuantity = parseFloat(itemData.warehouse_quantity || item.on_hand || 0);
            const availableQuantity = parseFloat(itemData.available_quantity || warehouseQuantity);
            
            totalWarehouseStock += warehouseQuantity;
            totalAvailableStock += availableQuantity;
        });
        
        $('#sales-warehouse-stock').text(totalWarehouseStock.toFixed(2) + ' Units');
        $('#sales-shop-stock').text(totalAvailableStock.toFixed(2) + ' Available');
    }
    
    // Add item to sales detail modal
    $(document).on('click', '.item-card, .sales-add-item-btn', function(e) {
        e.stopPropagation();
        const card = $(this).closest('.item-card');
        const itemId = card.data('id');
        const itemName = card.data('name');
        const itemPrice = card.data('price');
        const itemStock = card.data('stock');
        const warehouseId = card.data('warehouse-id');
        
        // Close search modal
        salesSearchModal.modal('hide');
        
        // Set item data immediately
        $('#selected-item-id').val(itemId);
        $('#item-search').val(itemName);
        $('#selected-warehouse-id').val(warehouseId || '');
        
        // Set price immediately from search results
        if (itemPrice && parseFloat(itemPrice) > 0) {
            $('#sales-item-rate').val(parseFloat(itemPrice).toFixed(2));
        }
        
        // Load full item details (will update price if needed)
        loadItemDetails(itemId, itemPrice);
        
        // Open detail modal
        $('#add-item-modal').modal('show');
    });
    // ========== End YouTube-Style Search Modal ==========

    // Reset form when modal opens
    $('#add-item-modal').on('show.bs.modal', function() {
        // Reset form when modal opens
        $('#item-search').val('');
        $('#selected-item-id').val('');
        $('#selected-warehouse-id').val('');
        $('#sales-item-quantity').val('1');
        $('#sales-item-unit').val('Can');
        $('#sales-item-rate').val('0');
        $('#sales-warranty-value').val('');
        $('#sales-warranty-unit').val('Days');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to view history</p>');
        $('#item-search-results').hide();
        $('#stock-status-section').hide();
        $('#stock-status-content').hide();
    });
    
    // Product name search with dropdown (Only Items)
    let itemSearchTimeout = null;
    $('#item-search').on('input', function() {
        const query = $(this).val().trim();
        const branchId = $('#salesBranchId').val();
        const resultsDiv = $('#item-search-results');
        
        // Clear previous timeout
        clearTimeout(itemSearchTimeout);
        
        // Minimum 1 character to search
        if (query.length < 1) {
            resultsDiv.hide();
            $('#selected-item-id').val('');
            return;
        }
        
        // Check if branch is selected
        if (!branchId) {
            resultsDiv.html('<div class="p-3 text-warning text-center"><i class="ti ti-alert-circle me-1"></i> Please select a branch first</div>');
            resultsDiv.show();
            return;
        }
        
        // Debounce search
        itemSearchTimeout = setTimeout(function() {
            $.ajax({
                url: "{{ route('sales.items.ajax.search') }}",
                method: 'GET',
                data: {
                    q: query,
                    branch_id: branchId,
                    limit: 50
                },
                success: function(results) {
                    console.log('Search results:', results);
                    
                    // Handle error response
                    if (results && results.error) {
                        resultsDiv.html(`<div class="p-3 text-warning text-center">${results.error}</div>`);
                        resultsDiv.show();
                        return;
                    }
                    
                    if (!results || results.length === 0) {
                        resultsDiv.html('<div class="p-3 text-muted text-center">No results found</div>');
                        resultsDiv.show();
                        return;
                    }
                    
                    let html = '';
                    let itemCount = 0;
                    
                    // Only show items, skip branches and warehouses
                    results.forEach(function(result) {
                        if (result.type === 'item') {
                            itemCount++;
                                // Item result - use the data from backend
                                const item = result.item;
                                const itemName = result.item_name || item.short_disc || item.pro_dis || item.bar_code || 'N/A';
                                const partNumber = result.part_number || item.partnumber_item?.name || '';
                                const manufacturer = item.vehical_item?.manutacturer_vehical?.name || '';
                                const model = item.vehical_item?.model_vehical?.name || '';
                                
                                // Get price from result data
                                const salePrice = parseFloat(result.sale_price || item.sale_price || 0);
                                const calculatedPrice = parseFloat(result.calculated_price_per_unit || 0);
                                const pricePerUnit = parseFloat(result.price_per_unit || item.price_per_unit || 0);
                                const packingRate = parseFloat(result.packing_purchase_rate || item.packing_purchase_rate || 0);
                                
                                // Priority: sale_price > calculated_price_per_unit > price_per_unit > packing_purchase_rate
                                let displayPrice = 0;
                                if (salePrice > 0) {
                                    displayPrice = salePrice;
                                } else if (calculatedPrice > 0) {
                                    displayPrice = calculatedPrice;
                                } else if (pricePerUnit > 0) {
                                    displayPrice = pricePerUnit;
                                } else if (packingRate > 0) {
                                    displayPrice = packingRate;
                                }
                                
                                const warehouseQuantity = parseFloat(result.warehouse_quantity || 0);
                                const warehouseId = result.warehouse_id || '';
                                
                                let displayName = itemName;
                                if (partNumber) displayName += ' - ' + partNumber;
                                if (manufacturer) displayName += ' ' + manufacturer;
                                if (model) displayName += ' ' + model;
                                
                                html += `
                                    <div class="p-2 border-bottom item-search-result" 
                                         data-type="item"
                                         data-id="${item.id}" 
                                         data-name="${displayName.replace(/"/g, '&quot;')}"
                                         data-rate="${displayPrice}"
                                         data-unit="${result.unit || item.unit || 'Unit'}"
                                         data-warehouse-id="${warehouseId}"
                                         style="cursor: pointer; transition: background 0.2s; padding-left: 30px;">
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-package me-2 text-muted" style="font-size: 12px;"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">${displayName}</div>
                                                <div class="small text-muted">
                                                    ${item.bar_code ? 'Barcode: ' + item.bar_code : ''}
                                                    ${warehouseQuantity > 0 ? ' | Stock: ' + warehouseQuantity.toFixed(2) : ''}
                                                    ${displayPrice > 0 ? ' | Price: Rs ' + displayPrice.toFixed(2) : ''}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                        }
                    });
                    
                    if (itemCount === 0) {
                        resultsDiv.html('<div class="p-3 text-muted text-center">No items found</div>');
                    } else {
                        resultsDiv.html(html);
                    }
                    resultsDiv.show();
                },
                error: function(xhr) {
                    console.error('Search error:', xhr);
                    let errorMsg = 'Error searching items';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    resultsDiv.html(`<div class="p-3 text-danger text-center">${errorMsg}</div>`);
                    resultsDiv.show();
                }
            });
        }, 300);
    });
    
    // Select from search results (only items now)
    $(document).on('click', '.item-search-result', function() {
        const resultType = $(this).data('type');
        const resultId = $(this).data('id');
        
        if (resultType === 'item') {
            // Select item
            const itemId = resultId;
            const itemName = $(this).data('name');
            const itemRate = $(this).data('rate');
            const itemUnit = $(this).data('unit');
            const warehouseId = $(this).data('warehouse-id');
            
            $('#item-search').val(itemName);
            $('#selected-item-id').val(itemId);
            $('#selected-warehouse-id').val(warehouseId || '');
            $('#sales-item-rate').val(parseFloat(itemRate || 0).toFixed(2));
            $('#sales-item-unit').val(itemUnit || 'Unit');
            $('#item-search-results').hide();
            
            // Load stock status
            loadItemStockStatus(itemId);
            
            // Load customer history
            loadCustomerHistory(itemId);
        }
    });
    
    // Load stock status for selected item
    function loadItemStockStatus(itemId) {
        $('#stock-status-section').show();
        $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">Loading stock status...</p>');
        
        $.ajax({
            url: '{{ route("sales.items.stock.status", ":id") }}'.replace(':id', itemId),
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

    // Load item details
    function loadItemDetails(itemId, preloadedPrice = null) {
        $.ajax({
            url: '{{ route("sales.items.details", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(response) {
                $('#selected-item-id').val(response.id);
                $('#item-search').val(response.name);
                
                // Use preloaded price if available, otherwise use response rate
                const salePrice = preloadedPrice || response.rate || response.sale_price || 0;
                $('#sales-item-rate').val(parseFloat(salePrice).toFixed(2));
                
                $('#sales-item-unit').val(response.unit || 'Can');
                
                // Load customer history for this item
                loadCustomerHistory(itemId);
                
                // Load stock status
                loadItemStockStatus(itemId);
                
                $('#item-search-results').hide();
            },
            error: function() {
                // If API fails, at least keep the preloaded price
                if (preloadedPrice) {
                    $('#sales-item-rate').val(parseFloat(preloadedPrice).toFixed(2));
                }
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
                        <span>Last Sale: Rs 1,250</span>
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
        // Get the last sale rate from history and apply it
        const historyText = $('#customer-history-content').text();
        const rateMatch = historyText.match(/Rs\s*([\d,]+)/);
        if (rateMatch) {
            const rate = rateMatch[1].replace(/,/g, '');
            $('#sales-item-rate').val(parseFloat(rate).toFixed(2));
        }
    });

    // Old code below - keeping for reference but will be replaced
    // Elements
    const searchInput = $('#item-search-input');
    const clearSearchBtn = $('#clear-search');
    const modal = $('#add-sales-new');
    const resultsContainer = $('#search-results-container');
        const noResults = $('#no-results');
    const loadingResults = $('#loading-results');
    const advancedFiltersToggle = $('#advanced-filters-toggle');
    const clearAllFiltersBtn = $('#clear-all-filters');
    
    // Filter state
    let activeFilters = {};
    let filterOptions = {};
    let searchTimeout = null;
    
    // Initialize: Load filter options and open modal
    $('#product-search').on('click', function() {
        modal.modal('show');
        if (Object.keys(filterOptions).length === 0) {
            loadFilterOptions();
        }
    });
    
    // Load filter options
    function loadFilterOptions() {
        $.ajax({
            url: "{{ route('sales.filter.options') }}",
            success: function(data) {
                filterOptions = data;
                populateFilterDropdowns(data);
            },
            error: function(xhr) {
                console.error('Error loading filter options:', xhr);
            }
        });
    }
    
    // Populate filter dropdowns
    function populateFilterDropdowns(data) {
        // Categories
        if (data.categories) {
            data.categories.forEach(cat => {
                $('#filter-category').append(`<option value="${cat.id}">${cat.name}</option>`);
            });
        }
        
        // Manufacturers
        if (data.manufacturers) {
            data.manufacturers.forEach(man => {
                $('#filter-manufacturer').append(`<option value="${man.id}">${man.name}</option>`);
            });
        }
        
        // Part Numbers
        if (data.part_numbers) {
            data.part_numbers.forEach(pn => {
                $('#filter-part-number').append(`<option value="${pn.id}">${pn.name}</option>`);
            });
        }
        
        // Technologies
        if (data.technologies) {
            data.technologies.forEach(tech => {
                $('#filter-technology').append(`<option value="${tech.id}">${tech.name}</option>`);
            });
        }
        
        // Grades
        if (data.grades) {
            data.grades.forEach(grade => {
                $('#filter-grade').append(`<option value="${grade.id}">${grade.name}</option>`);
            });
        }
        
        // Volts
        if (data.volts) {
            data.volts.forEach(volt => {
                $('#filter-volt').append(`<option value="${volt.id}">${volt.name}</option>`);
            });
        }
        
        // CCAs
        if (data.ccas) {
            data.ccas.forEach(cca => {
                $('#filter-cca').append(`<option value="${cca.id}">${cca.name}</option>`);
            });
        }
        
        // Suppliers
        if (data.suppliers) {
            data.suppliers.forEach(supplier => {
                $('#filter-supplier').append(`<option value="${supplier}">${supplier}</option>`);
            });
        }
        
        // Racks
        if (data.racks) {
            data.racks.forEach(rack => {
                $('#filter-rack').append(`<option value="${rack}">${rack}</option>`);
            });
        }
    }
    
    // Live search with debounce
    searchInput.on('input', function() {
        const query = $(this).val().trim();
        clearSearchBtn.toggleClass('d-none', !query);
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (query.length >= 2 || Object.keys(activeFilters).length > 0) {
                performSearch();
            } else {
                resultsContainer.html(`
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                        <p>Start typing to search items or use filters above</p>
                    </div>
                `);
            }
        }, 500);
    });
    
    // Clear search
    clearSearchBtn.on('click', function() {
        searchInput.val('');
        $(this).addClass('d-none');
        performSearch();
    });
    
    // Filter chip clicks
    $('.filter-chip').on('click', function() {
        const filter = $(this).data('filter');
        const value = $(this).data('value');
        
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            delete activeFilters[filter];
        } else {
            $('.filter-chip[data-filter="' + filter + '"]').removeClass('active');
            $(this).addClass('active');
            activeFilters[filter] = value;
        }
        
        updateClearAllButton();
        performSearch();
    });
    
    // Advanced filter changes
    $('#filter-category, #filter-manufacturer, #filter-part-number, #filter-technology, #filter-grade, #filter-volt, #filter-cca, #filter-supplier, #filter-rack, #filter-min-price, #filter-max-price').on('change input', function() {
        const filterId = $(this).attr('id').replace('filter-', '').replace('-', '_');
        const value = $(this).val();
        
        if (value) {
            activeFilters[filterId] = value;
        } else {
            delete activeFilters[filterId];
        }
        
        updateClearAllButton();
        performSearch();
    });
    
    // Toggle advanced filters
    advancedFiltersToggle.on('click', function() {
        $('#advancedFiltersPanel').collapse('toggle');
    });
    
    // Clear all filters
    clearAllFiltersBtn.on('click', function() {
        activeFilters = {};
        $('.filter-chip').removeClass('active');
        $('#filter-category, #filter-manufacturer, #filter-part-number, #filter-technology, #filter-grade, #filter-volt, #filter-cca, #filter-supplier, #filter-rack').val('');
        $('#filter-min-price, #filter-max-price').val('');
        updateClearAllButton();
        performSearch();
    });
    
    // Update clear all button visibility
    function updateClearAllButton() {
        const hasFilters = Object.keys(activeFilters).length > 0 || searchInput.val().trim().length > 0;
        clearAllFiltersBtn.toggleClass('d-none', !hasFilters);
    }
    
    // Perform search
    function performSearch() {
        const query = searchInput.val().trim();
        
        // Build search params
        const params = {
            q: query,
            limit: 50
        };
        
        // Add active filters
        Object.keys(activeFilters).forEach(key => {
            params[key] = activeFilters[key];
        });
        
        // Show loading
        resultsContainer.hide();
            noResults.hide();
        loadingResults.show();
        
        // Perform AJAX search
            $.ajax({
                url: "{{ route('sales.items.ajax.search') }}",
            data: params,
                success: function(items) {
                loadingResults.hide();
                
                    if (items.length === 0) {
                        noResults.show();
                    resultsContainer.hide();
                        return;
                }
                
                noResults.hide();
                resultsContainer.show();
                
                let html = '';
                const searchTerm = query.toLowerCase();
                const regex = searchTerm ? new RegExp(searchTerm.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&'), 'gi') : null;
                
                items.forEach(item => {
                    const partNumber = item.partnumber_item?.name || 'N/A';
                    const manufacturer = item.vehical_item?.manutacturer_vehical?.name || '';
                    const model = item.vehical_item?.model_vehical?.name || '';
                    // Fix: Use year_from and year_to instead of carmanufactured_year
                    const yearFrom = item.vehical_item?.year_from || '';
                    const yearTo = item.vehical_item?.year_to || '';
                    const yearDisplay = yearFrom && yearTo ? `${yearFrom}-${yearTo}` : (yearFrom || yearTo || '');
                    const price = item.sale_price || 0;
                    const stock = item.on_hand || 0;
                    const barCode = item.bar_code || '';
                    const serialNumber = item.serial_number || '';
                    
                    // Highlight search term
                    let displayPartNumber = partNumber;
                    let displayManufacturer = manufacturer;
                    let displayModel = model;
                    let displayYear = String(year);
                    
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
                                    <div class="fw-bold text-primary mb-1">$${parseFloat(price).toFixed(2)}</div>
                                    <button class="btn btn-sm btn-primary add-item-btn">
                                        <i class="fas fa-plus me-1"></i>Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                
                    resultsContainer.html(html);
                
                // Update stock info
                updateStockInfo(items);
                },
                error: function(xhr) {
                loadingResults.hide();
                console.error('Search error:', xhr);
                resultsContainer.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading items. Please try again.
                    </div>
                `);
            }
        });
    }
    
    // Update stock info
    function updateStockInfo(items) {
        let warehouseStock = 0;
        let shopStock = 0;
        
        items.forEach(item => {
            const stock = item.on_hand || 0;
            // You can customize this logic based on your business rules
            warehouseStock += stock * 0.7; // Example: 70% in warehouse
            shopStock += stock * 0.3; // Example: 30% in shop
        });
        
        $('#warehouse-stock').text(Math.round(warehouseStock) + ' Units');
        $('#shop-stock').text(Math.round(shopStock) + ' Units');
    }
    
    // Add item to sales detail modal (instead of directly to table)
    $(document).on('click', '.item-card, .add-item-btn', function(e) {
        e.stopPropagation();
        const card = $(this).closest('.item-card');
        const itemId = card.data('id');
        const itemName = card.data('name');
        const itemPrice = card.data('price');
        const itemStock = card.data('stock');
        
        // Close search modal
        modal.modal('hide');
        
        // Load item details and open detail modal
        loadSalesItemDetails(itemId);
        
        // Open detail modal
        $('#add-item-modal').modal('show');
    });
    
    // Load item details for sales
    function loadSalesItemDetails(itemId) {
        $.ajax({
            url: '{{ route("sales.items.details", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(response) {
                $('#sales-selected-item-id').val(response.id);
                $('#sales-item-name').val(response.name);
                $('#sales-item-rate').val(parseFloat(response.rate || 0).toFixed(2));
                $('#sales-item-unit').val(response.unit || 'Can');
                
                // Load stock status
                loadSalesItemStockStatus(itemId);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load item details'
                });
            }
        });
    }
    
    // Load stock status for selected item (Sales)
    function loadSalesItemStockStatus(itemId) {
        $('#sales-stock-status-section').show();
        $('#sales-stock-status-list').html('<p class="text-muted mb-0 small text-center">Loading stock status...</p>');
        
        $.ajax({
            url: '{{ route("sales.items.stock.status", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(stockData) {
                if (stockData.length === 0) {
                    $('#sales-stock-status-list').html('<p class="text-muted mb-0 small text-center">No stock found</p>');
                    return;
                }
                
                let html = '';
                stockData.forEach(function(stock) {
                    if (stock.type === 'branch') {
                        // Branch total
                        html += `
                            <div class="p-2 mb-1 border-bottom sales-stock-branch-item" style="background-color: #fff;">
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
                        const isSelected = $('#sales-selected-warehouse-id').val() == stock.id;
                        html += `
                            <div class="p-2 mb-1 sales-stock-warehouse-item ${isSelected ? 'bg-primary text-white' : ''}" 
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
                
                $('#sales-stock-status-list').html(html);
            },
            error: function() {
                $('#sales-stock-status-list').html('<p class="text-danger mb-0 small text-center">Error loading stock status</p>');
            }
        });
    }
    
    // Toggle stock status expand/collapse (Sales)
    $('#sales-stock-status-toggle').on('dblclick', function() {
        $('#sales-stock-status-content').slideToggle();
    });
    
    // Select warehouse from stock status (Sales)
    $(document).on('click', '.sales-stock-warehouse-item', function() {
        // Remove previous selection
        $('.sales-stock-warehouse-item').removeClass('bg-primary text-white').addClass('bg-light');
        $('.sales-stock-warehouse-item').find('span:first').text('');
        
        // Select this warehouse
        $(this).removeClass('bg-light').addClass('bg-primary text-white');
        $(this).find('span:first').html('✓');
        
        const warehouseId = $(this).data('warehouse-id');
        $('#sales-selected-warehouse-id').val(warehouseId);
    });
    
    // Confirm entry
    $('#confirm-entry').on('click', function() {
        const itemId = $('#selected-item-id').val();
        let quantity = parseFloat($('#sales-item-quantity').val()) || 0;
        
        // If custom quantity input is visible and has value, use that
        if ($('#sales-item-quantity-input').is(':visible') && $('#sales-item-quantity-input').val()) {
            quantity = parseFloat($('#sales-item-quantity-input').val()) || 0;
        }
        
        const unit = $('#sales-item-unit').val();
        const rate = parseFloat($('#sales-item-rate').val()) || 0;
        const discount = parseFloat($('#item-discount').val()) || 0;
        const discountType = $('#discount-type').val();
        const taxPercentage = parseFloat($('#item-tax').val()) || 0;
        const itemName = $('#item-search').val();
        const warrantyValue = $('#sales-warranty-value').val();
        const warrantyUnit = $('#sales-warranty-unit').val();

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

        // Get warehouse ID
        const warehouseId = $('#selected-warehouse-id').val();

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
            warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : null,
            warehouse_id: warehouseId || null
        };

        salesItems.push(item);
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
        salesItems = salesItems.filter(item => item.id !== rowId);
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
        $('#sales-item-quantity').val('1');
        $('#sales-item-quantity-input').val('1').hide();
        $('#sales-item-unit').val('Can');
        $('#sales-item-rate').val('0');
        $('#sales-warranty-value').val('');
        $('#sales-warranty-unit').val('Days');
        $('#item-discount').val('0');
        $('#discount-type').val('amount');
        $('#item-tax').val('0');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to see history</p>');
        $('#hold-rate-link').hide();
        $('#search-results').hide();
        $('#stock-status-section').hide();
        $('#stock-status-content').hide();
    }

    function calculateTotals() {
        let itemTotal = 0;
        salesItems.forEach(function(item) {
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

    // Quantity dropdown change - show custom input if "Qty" selected
    $('#sales-item-quantity').on('change', function() {
        if ($(this).val() === '1' && $(this).find('option:selected').text() === 'Qty') {
            $('#sales-item-quantity-input').show().focus();
        } else {
            $('#sales-item-quantity-input').hide();
        }
    });

    // Use custom quantity input if provided
    $('#sales-item-quantity-input').on('input', function() {
        const customQty = $(this).val();
        if (customQty && customQty > 0) {
            $('#sales-item-quantity').val(customQty);
        }
    });
    
    // Remove item from table
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        
        // Add empty row if table is empty
        if ($('#sales-items-body tr').length === 0) {
            $('#sales-items-body').html('<tr><td colspan="9" class="text-center text-muted">No items added yet</td></tr>');
        }
    });
    
    // Calculate totals when quantity/discount/tax changes
    $(document).on('input', '.qty, .discount, .tax', function() {
        const row = $(this).closest('tr');
        const qty = parseFloat(row.find('.qty').val()) || 0;
        const price = parseFloat(row.find('td').eq(2).text().replace('$', '')) || 0;
        const discount = parseFloat(row.find('.discount').val()) || 0;
        const taxPercent = parseFloat(row.find('.tax').val()) || 0;
        
        const subtotal = (price * qty) - discount;
        const taxAmount = (subtotal * taxPercent) / 100;
        const total = subtotal + taxAmount;
        
        row.find('.tax-amount').text('$' + taxAmount.toFixed(2));
        row.find('.unit-cost').text('$' + (subtotal / qty).toFixed(2));
        row.find('.total-cost').text('$' + total.toFixed(2));
        });
    });
    
    // Form submission handler
    $('#salesForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Form submitted, salesItems:', salesItems);
        
        // Validate customer
        const customerId = $('#customer_id').val();
        if (!customerId) {
            Swal.fire({
                icon: 'error',
                title: 'Customer Required',
                text: 'Please select a customer first.'
            });
            return false;
        }
        
        // Validate branch selection
        const branchId = $('#salesBranchId').val();
        if (!branchId) {
            Swal.fire({
                icon: 'error',
                title: 'Branch Required',
                text: 'Please select a branch first.'
            });
            return false;
        }
        
        // Validate items
        if (!salesItems || salesItems.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'No Items',
                text: 'Please add at least one item to the sale.'
            });
            return false;
        }
        
        // Prepare form data
        const formData = {
            _token: '{{ csrf_token() }}',
            customer_id: $('#customer_id').val(),
            branch_id: branchId,
            sale_date: $('#sale_date').val(),
            reference: $('#reference').val(),
            status: $('input[name="status"]').val(),
            order_tax: $('#order_tax').val() || 0,
            discount: $('#discount').val() || 0,
            shipping: $('#shipping').val() || 0,
            items: salesItems.map(item => ({
                item_id: item.item_id,
                quantity: item.quantity,
                unit: item.unit,
                rate: item.rate,
                discount: item.discount,
                tax_percentage: item.tax_percentage,
                tax_amount: item.tax_amount,
                total: item.total,
                warranty: item.warranty || null,
                warehouse_id: item.warehouse_id || null
            }))
        };
        
        // Show loading
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we create your sale.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Debug: Log form data
        console.log('Submitting sale:', formData);
        console.log('Sales items:', salesItems);
        
        // Submit via AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Sale created successfully:', response);
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Sale created successfully!',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '{{ route("all_sales") }}';
                });
            },
            error: function(xhr) {
                console.error('Sale creation error:', xhr);
                let errorMessage = 'Failed to create sale. Please try again.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                } else if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
        
        return false;
    });
    
    // Load purchase items if coming from purchase conversion
    @if(isset($purchaseData) && $purchaseData)
    $(document).ready(function() {
        const purchaseData = @json($purchaseData);
        
        if (purchaseData && purchaseData.items && purchaseData.items.length > 0) {
            // Clear existing items
            salesItems = [];
            $('#items-tbody').empty();
            
            // Get warehouse ID for the branch
            const branchId = purchaseData.branch_id || $('#salesBranchId').val();
            let warehouseId = null;
            
            // Fetch warehouse for this branch
            if (branchId) {
                $.ajax({
                    url: '{{ route("warehouses.by.branch", ":id") }}'.replace(':id', branchId),
                    method: 'GET',
                    async: false, // Synchronous to get warehouse before loading items
                    success: function(warehouse) {
                        if (warehouse && !warehouse.error && warehouse.id) {
                            warehouseId = warehouse.id;
                            $('#selected-warehouse-id').val(warehouseId);
                        }
                    },
                    error: function() {
                        console.warn('Could not fetch warehouse for branch:', branchId);
                    }
                });
            }
            
            // Load each item from purchase
            purchaseData.items.forEach(function(itemData) {
                // Fetch item details to get name
                $.ajax({
                    url: '{{ route("sales.items.details", ":id") }}'.replace(':id', itemData.item_id),
                    method: 'GET',
                    async: false, // Synchronous to maintain order
                    success: function(itemDetails) {
                        const item = {
                            id: itemCounter++,
                            item_id: itemData.item_id,
                            name: itemDetails.name || 'Item #' + itemData.item_id,
                            quantity: parseFloat(itemData.quantity),
                            unit: itemData.unit || 'Unit',
                            rate: parseFloat(itemData.rate),
                            discount: parseFloat(itemData.discount || 0),
                            tax_percentage: parseFloat(itemData.tax_percentage || 0),
                            tax_amount: parseFloat(itemData.tax_amount || 0),
                            total: parseFloat(itemData.total),
                            warranty: null,
                            warehouse_id: warehouseId
                        };
                        
                        salesItems.push(item);
                        addItemToTable(item);
                    },
                    error: function() {
                        // If item details fetch fails, still add with basic info
                        const item = {
                            id: itemCounter++,
                            item_id: itemData.item_id,
                            name: 'Item #' + itemData.item_id,
                            quantity: parseFloat(itemData.quantity),
                            unit: itemData.unit || 'Unit',
                            rate: parseFloat(itemData.rate),
                            discount: parseFloat(itemData.discount || 0),
                            tax_percentage: parseFloat(itemData.tax_percentage || 0),
                            tax_amount: parseFloat(itemData.tax_amount || 0),
                            total: parseFloat(itemData.total),
                            warranty: null,
                            warehouse_id: warehouseId
                        };
                        
                        salesItems.push(item);
                        addItemToTable(item);
                    }
                });
            });
            
            // Update totals
            calculateTotals();
            
            // Set order tax, discount, shipping if available
            if (purchaseData.order_tax) {
                $('#order_tax').val(purchaseData.order_tax);
            }
            if (purchaseData.discount) {
                $('#discount').val(purchaseData.discount);
            }
            if (purchaseData.shipping) {
                $('#shipping').val(purchaseData.shipping);
            }
            
            // Recalculate totals after setting tax/discount/shipping
            calculateTotals();
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Purchase Items Loaded',
                text: purchaseData.items.length + ' items loaded from purchase. Please select a customer to complete the sale.',
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
    @endif
</script>
@endpush
