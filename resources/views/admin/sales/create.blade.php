@extends('layouts.app')

@section('title', 'Create Sales')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Create Sales</h4>
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
                                            // Find Barki Express branch for auto-select
                                            $barkiBranch = $branches->first(function($branch) {
                                                return stripos($branch->branch_name, 'barki') !== false;
                                            });
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
                        
                        <!-- Modern Invoice Card -->
                        <div class="invoice-card p-5 mb-4">
                            <!-- Header -->
                            <div class="invoice-header">
                                <div class="invoice-header-left">
                                    @php
                                        $branchName = session('selected_branch_name', 'MUBARAK TRADERS');
                                        // Replace specific branch names
                                        if(strtoupper($branchName) === 'BARKI') {
                                            $branchName = 'Barki Express';
                                        }
                                    @endphp
                                    <h1 id="branch-name-title">{{ $branchName }}</h1>
                                    <p class="subtitle">Auto Oil & Spare Parts Specialist</p>
                                    <p class="helpline"><i class="ti ti-phone me-1"></i> {{ setting_value('helpline', '+92-335-08-999-08') }}</p>
                                </div>
                                <div class="invoice-header-right">
                                    <p class="invoice-date" id="currentDateTime">{{ date('d/m/Y, h:i:s A') }}</p>
                                    <div class="d-flex flex-row align-items-center" style="gap: 10px; align-items: center;">
                                        <p class="invoice-number" id="sales-number" style="margin-bottom: 0;">INV #{{ str_pad(1, 5, '0', STR_PAD_LEFT) }}</p>
                                        <button type="button" class="custom-3step-switch switch-sale" id="estimateSwitch" onclick="if(typeof doEstimateSwitchCycle==='function') doEstimateSwitchCycle(); return false;" style="position: relative; width: 80px; height: 30px; border-radius: 15px; cursor: pointer; transition: all 0.3s ease; margin-top: 0; border: none; padding: 0; outline: none; background: #2563eb;">
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
                            
                            <!-- Vehicle Section -->
                            <div class="mb-4">
                                <button type="button" class="btn w-100 mb-3" id="add-vehicle-btn" style="background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 12px; padding: 18px; color: #2563eb; font-weight: 900; text-transform: uppercase; font-size: 14px;">
                                    <i class="ti ti-car me-2"></i>Add / Link Vehicle Details
                                </button>
                                
                                <div id="vehicle-display-section" style="display: none;">
                                    <p class="text-primary fw-bold mb-2" style="font-size: 11px;">ACTIVE VEHICLES</p>
                                    <div id="vehicles-list" class="flex-column gap-2" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                                        <!-- Vehicles will be dynamically added here -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Information -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="modern-label">Party Name / Vehicle #</label>
                                    <div class="position-relative">
                                        <input type="text" id="customer_search" class="modern-input @error('customer_id') is-invalid @enderror" placeholder="Search Customer or Plate..." autocomplete="off" oninput="searchCustomer(this.value)">
                                        <input type="hidden" name="customer_id" id="customer_id" required>
                                        <div id="customerSuggestions" class="suggestions-list hidden"></div>
                                    </div>
                                    <div class="text-muted small mt-1" id="customer-required-hint">Select a party/customer first to enable adding sale items.</div>
                                    @error('customer_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Mobile No.</label>
                                    <input type="text" id="customer_mobile" name="customer_mobile" class="modern-input" placeholder="03xx-xxxxxxx">
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Address</label>
                                    <input type="text" id="customer_address" name="customer_address" class="modern-input" placeholder="Shop/House #">
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Area</label>
                                    <input type="text" id="customer_area" name="customer_area" class="modern-input" placeholder="Location/City">
                                </div>
                            </div>
                            
                            <!-- Hidden sales date field -->
                            <input type="hidden" name="sale_date" id="sale_date" value="{{ date('Y-m-d') }}" required>
                            
                            <!-- Reference (Optional) -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="modern-label">Reference</label>
                                    <input type="text" name="reference" id="reference" class="modern-input" placeholder="Enter reference number" value="{{ $purchaseData['reference'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-check me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif
                            
                            <!-- Items List -->
                            <div id="items-summary-container" class="items-container mb-4">
                                <div id="empty-items-state" class="empty-state">
                                    <p>No items added yet...</p>
                                </div>
                                <div id="items-list" style="display: none;">
                                    <div id="items-tbody">
                                        <!-- Items will be dynamically added here -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Total Summary Section -->
                            <div class="total-section">
                                <div id="breakdownRows" class="mb-2"></div>
                                
                                <div class="total-row">
                                    <p class="mb-0" style="font-size: 10px; font-weight: 700; text-transform: uppercase;">Total Items Amount</p>
                                    <p class="mb-0" style="font-size: 14px; font-weight: 700;">Rs <span id="gross-amount">0</span></p>
                                </div>
                                
                                <div class="discount-section">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="discount-label mb-0">Discount (Manual Edit)</p>
                                        <div class="d-flex align-items-center bg-white rounded-lg px-2 border" style="border-color: #bbf7d0 !important;">
                                            <button type="button" onclick="toggleBillDiscType()" id="billDiscToggle" class="text-[10px] font-black text-green-600 mr-2 bg-green-100 px-1.5 py-0.5 rounded" style="font-size: 10px; font-weight: 900; color: #16a34a; background: #dcfce7; padding: 2px 6px; border-radius: 4px; border: none;">Rs</button>
                                            <input type="number" id="totalBillDiscount" oninput="calculateFinalTotalFromInput()" value="0" class="w-16 bg-transparent font-black text-right outline-none text-green-700" style="width: 64px; background: transparent; font-weight: 900; text-align: right; outline: none; color: #16a34a; border: none; font-size: 14px;">
                                            <input type="hidden" id="totalBillDiscType" value="fixed">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="net-payable">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="net-payable-label mb-0">Net Payable</p>
                                        <p class="net-payable-value mb-0">Rs <span id="net-payable-total">0</span></p>
                                    </div>
                                </div>
                                
                                <div id="cash-received-section" class="received-amount-section">
                                    <p class="received-amount-label mb-2" style="font-size: 9px; font-weight: 900; color: #6b7280; text-transform: uppercase; letter-spacing: 1px;">Cash Received</p>
                                    <div id="cashReceivedWrapper" class="space-y-2">
                                        <div class="payment-card border-blue-100 no-print">
                                            <div class="d-flex justify-content-between align-items-center gap-2">
                                                <p class="mb-0" style="font-size: 10px; font-weight: 900; color: #374151; text-transform: uppercase;">Cash Entry</p>
                                        <div class="d-flex align-items-center bg-white rounded-lg px-2 border" style="border-color: #e5e7eb !important;">
                                                    <span class="text-[10px] font-black text-gray-400 mr-1.5 uppercase" style="font-size: 10px; font-weight: 900; color: #9ca3af; text-transform: uppercase; margin-right: 6px;">Rs</span>
                                                    <input type="number" class="cash-input" oninput="calculateFinalTotalFromInput()" value="0" style="width: 96px; background: transparent; font-weight: 900; text-align: right; outline: none; color: #1f2937; border: none; font-size: 14px;">
                                        </div>
                                            </div>
                                            <div class="mt-2">
                                                <label class="d-flex-1 cursor-pointer bg-white border border-dashed rounded-lg p-2 text-center block transition-all duration-300" style="border-color: #bfdbfe;">
                                                    <p class="status-text mb-0" style="font-size: 8px; font-weight: 900; color: #60a5fa; text-transform: uppercase;"><i class="ti ti-camera me-1"></i> Attach Photo</p>
                                                    <input type="file" accept="image/*" class="d-none cash-input-pic" onchange="handleImagePick(this, 'blue', 'Photo Attached')">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 no-print">
                                        <button type="button" onclick="addCashReceivedRow()" class="btn btn-sm w-100" style="background-color: #dbeafe; color: #2563eb; border: 1px dashed #93c5fd; border-radius: 12px; padding: 8px; font-size: 9px; font-weight: 900; text-transform: uppercase;">
                                            <i class="ti ti-plus me-1"></i> Add More Cash Received
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- BANK RECEIVED -->
                                <div id="bank-received-section" class="space-y-1 pt-1 border-top" style="border-color: #e5e7eb; padding-top: 8px;">
                                    <p class="mb-2" style="font-size: 9px; font-weight: 900; color: #a855f7; text-transform: uppercase; letter-spacing: 1px; margin-left: 8px;">Bank Received</p>
                                    <div id="bankPaymentsWrapper" class="space-y-2"></div>
                                    <div class="px-2 no-print mt-1">
                                        <button type="button" onclick="addBankPaymentRow()" class="btn btn-sm w-100" style="background-color: #f3e8ff; color: #9333ea; border: 1px dashed #c084fc; border-radius: 12px; padding: 8px; font-size: 9px; font-weight: 900; text-transform: uppercase;">
                                            <i class="ti ti-building-bank me-1"></i> Add Bank Payment
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="total-row" style="color: #ea580c;">
                                    <p class="mb-0" style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #ea580c;">Current Remaining</p>
                                    <p class="mb-0" style="font-size: 14px; font-weight: 700; color: #ea580c;">Rs <span id="currentRemainingText">0</span></p>
                                </div>
                                
                                <div class="previous-balance-section">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="previous-balance-label mb-0">Previous Balance</p>
                                        <div class="d-flex align-items-center bg-white rounded-lg px-2 border" style="border-color: #fde68a !important;">
                                            <span class="text-[10px] font-black text-yellow-400 mr-2 uppercase" style="font-size: 10px; font-weight: 900; color: #fbbf24; text-transform: uppercase; margin-right: 8px;">Rs</span>
                                            <input type="number" id="previousBalance" oninput="calculateFinalTotalFromInput()" value="0" class="w-24 bg-transparent font-black text-right outline-none text-yellow-800" style="width: 96px; background: transparent; font-weight: 900; text-align: right; outline: none; color: #92400e; border: none; font-size: 14px;">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grand-total-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="grand-total-label mb-1" id="balanceLabel">Total Final Balance</p>
                                            <p class="item-count mb-0" id="item-count">0 Items Listed</p>
                                        </div>
                                        <div class="text-end">
                                            <span style="font-size: 12px; font-weight: 700; margin-right: 4px;">PKR</span>
                                            <span class="grand-total-value" id="grand-total">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden fields for order tax, discount, shipping -->
                        <input type="hidden" name="order_tax" id="order_tax" value="0">
                        <input type="hidden" name="discount" id="discount" value="0">
                        <input type="hidden" name="shipping" id="shipping" value="0">
                        <input type="hidden" name="status" id="sale-status" value="pending">

                        
                        <!-- Add Item Button -->
                        <div class="mb-4">
                            <button type="button" class="modern-btn-primary pulse-animation" id="add-new-item-btn">
                                <i class="ti ti-plus"></i> Add Sale Item
                            </button>
                        </div>

                        <!-- Action Buttons Grid -->
                        <div class="action-btn-grid mb-4">
                            <button type="button" class="action-btn" id="return-entry-btn" style="background-color: #fee2e2; color: #dc2626; border-color: #fecaca;">
                                <i class="ti ti-arrow-back-up"></i> Return
                            </button>
                            <button type="button" class="action-btn" id="claim-entry-btn" style="background-color: #fef3c7; color: #b45309; border-color: #fde68a;">
                                <i class="ti ti-shield-check"></i> Claim
                            </button>
                            <button type="button" class="action-btn" id="scrap-in-btn" style="background-color: #fed7aa; color: #ea580c; border-color: #fdba74;">
                                <i class="ti ti-recycle"></i> Scrap In
                            </button>
                            <button type="button" class="action-btn" id="scrap-sale-btn" style="background-color: #dcfce7; color: #16a34a; border-color: #bbf7d0;">
                                <i class="ti ti-coins"></i> Scrap Sale
                            </button>
                        </div>
                        
                        <!-- Delivery Buttons -->
                        <div class="d-flex gap-2 mb-4 no-print">
                            <button type="button" class="btn flex-1" id="delivery-entry-btn" style="background-color: #f97316; color: white; border-radius: 16px; padding: 14px; font-weight: 900; text-transform: uppercase; font-size: 11px;">
                                <i class="ti ti-truck me-1"></i> Delivery Entry
                            </button>
                            <button type="button" class="btn" id="share-delivery-btn" style="background-color: #ea580c; color: white; border-radius: 16px; padding: 14px; width: 60px; font-weight: 900;">
                                <i class="ti ti-share"></i>
                            </button>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('all_sales') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success" id="save-sale-btn">
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
<div class="modal fade" id="add-item-modal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel" aria-modal="true" style="z-index: 9999 !important; pointer-events: auto !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="pointer-events: auto !important;">
        <div class="modal-content" style="border-radius: 12px; pointer-events: auto !important;">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title fw-bold" style="color: #2563eb;">
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
                        <input type="text" id="item-search" class="form-control item-search-input text-uppercase" placeholder="e.g. 53495878 Toyota — code, space, then vehicle or keyword" autocomplete="off" title="Type to search or edit product name">
                        <i class="ti ti-search position-absolute item-search-icon" style="right: 16px; top: 50%; transform: translateY(-50%); font-size: 18px; pointer-events: none;"></i>
                        <!-- Search Results Dropdown -->
                        <div id="item-search-results" class="position-absolute w-100 item-search-results-box" style="top: 100%; left: 0; z-index: 1050; max-height: 320px; overflow-y: auto; display: none; margin-top: 8px;">
                            </div>
                        <!-- Selected Item Details Display (below input) -->
                        <div id="selected-item-details-display" class="mt-2 d-none" style="font-size: 0.85rem;">
                            <div class="text-muted mb-1 text-uppercase" id="selected-item-details-line1"></div>
                            <div class="text-muted mb-1" id="selected-item-details-line2"></div>
                            <div class="text-warning fw-semibold" id="selected-item-details-line3"></div>
                        </div>
                        </div>
                        <!-- Item Image Preview -->
                        <div id="item-search-image-preview" class="d-none" style="flex-shrink: 0;">
                            <img id="item-search-image" src="" alt="Item Image" class="rounded border shadow-sm" style="width: 52px; height: 52px; object-fit: cover;">
                            <div id="item-search-stock" class="text-center mt-1" style="font-size: 0.75rem; font-weight: 600;"></div>
                        </div>
                    </div>
                    <input type="hidden" id="selected-item-id">
                    <input type="hidden" id="selected-warehouse-id">
                    
                    <!-- Advanced Filters Panel -->
                    <div id="advanced-filters-panel" class="mt-3 p-3 border rounded" style="background-color: #f8f9fa; display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">Advanced Filters</h6>
                            <button type="button" id="clear-all-filters" class="btn btn-sm btn-link text-danger p-0" style="font-size: 11px;">Clear All</button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Item Type</label>
                                <select class="form-select form-select-sm" id="filter-item-type">
                                    <option value="">All Types</option>
                                    <option value="parts">Parts</option>
                                    <option value="filters">Filters</option>
                                    <option value="breakpad">Break Pad</option>
                                    <option value="oil">Oil</option>
                                    <option value="battery">Battery</option>
                                    <option value="scrap">Scrap</option>
                                    <option value="services">Services</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Category</label>
                                <select class="form-select form-select-sm" id="filter-category">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Part Number</label>
                                <input type="text" class="form-control form-control-sm" id="filter-part-number" placeholder="Filter by part number...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Manufacturer</label>
                                <input type="text" class="form-control form-control-sm" id="filter-manufacturer" placeholder="Filter by manufacturer...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Vehicle Model</label>
                                <input type="text" class="form-control form-control-sm" id="filter-vehicle-model" placeholder="Filter by vehicle model...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Barcode</label>
                                <input type="text" class="form-control form-control-sm" id="filter-barcode" placeholder="Filter by barcode...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Min Price</label>
                                <input type="number" class="form-control form-control-sm" id="filter-min-price" placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-1">Max Price</label>
                                <input type="number" class="form-control form-control-sm" id="filter-max-price" placeholder="0.00" step="0.01">
                            </div>
                        </div>
                        <!-- Active Filter Chips -->
                        <div id="active-filters-chips" class="mt-3 d-flex flex-wrap gap-2" style="min-height: 30px;">
                        </div>
                    </div>
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
                            <option value="">-</option>
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
                                    @for($i = 1; $i <= 30; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <select id="sales-warranty-unit" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
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

                <!-- Warranty-card Proofs (Retail only, per unit quantity) -->
                <div id="warranty-proof-section" class="mb-3 d-none">
                    <div class="border rounded p-3" style="background: #fff7ed; border-color: #fdba74 !important;">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                            <div>
                                <div class="fw-bold" style="color:#9a3412;">Warranty card proof required (Retail)</div>
                                <div class="small text-muted" id="warranty-proof-summary">Please attach warranty card proof for all selected quantity units.</div>
                            </div>
                            <span class="badge" id="warranty-proof-badge" style="background:#fed7aa;color:#9a3412;">0 / 0</span>
                        </div>
                        <div class="mt-3" id="warranty-proof-units"></div>
                        <div class="text-danger small mt-2 d-none" id="warranty-proof-error"></div>
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

<!-- Vehicle Modal -->
<div class="modal fade" id="vehicle-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title fw-bold">
                    <i class="ti ti-car me-2"></i>VEHICLE DETAILS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">Registration Number</label>
                    <input type="text" id="vehicle-reg" class="form-control" placeholder="e.g. LEC-22-1234" style="text-transform: uppercase;">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">Make / Brand</label>
                    <input type="text" id="vehicle-make" class="form-control" placeholder="e.g. Toyota, Honda">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">Model / Name</label>
                    <input type="text" id="vehicle-model" class="form-control" placeholder="e.g. Civic, Corolla">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">Model Year</label>
                    <input type="number" id="vehicle-year" class="form-control" placeholder="e.g. 2022" min="1900" max="2100">
                </div>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold" id="save-vehicle-btn" style="background-color: #0d6efd; border-radius: 8px; padding: 10px 30px;">
                    SAVE VEHICLE
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Warranty Proof Viewer Modal (create sale) -->
<div class="modal fade" id="warranty-proof-viewer-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="warranty-viewer-title">Warranty Proof</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-center">
                    <img id="warranty-viewer-image" src="" alt="Warranty proof" class="img-fluid rounded border" style="max-height: 60vh; width: auto;">
                </div>
                <div class="mt-3 small text-muted">
                    <div id="warranty-viewer-unit" class="fw-bold text-dark"></div>
                    <div id="warranty-viewer-code"></div>
                    <div id="warranty-viewer-time"></div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" id="warranty-viewer-prev">Prev</button>
                <button type="button" class="btn btn-outline-secondary" id="warranty-viewer-next">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Entry Modal -->
<div class="modal fade" id="delivery-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-2" style="background-color: #f97316; color: white;">
                <h5 class="modal-title fw-bold">
                    <i class="ti ti-truck me-2"></i>DELIVERY ENTRY
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="delivery-items-list" class="mb-3" style="display: none;">
                    <label class="form-label fw-bold mb-2">
                        <i class="ti ti-package me-1"></i>Items to Deliver:
                    </label>
                    <div class="card border-primary" style="border-radius: 8px; max-height: 200px; overflow-y: auto;">
                        <div class="card-body p-2">
                            <ul id="delivery-items-ul" class="mb-0" style="list-style: none; padding: 0;">
                                <!-- Items will be populated here -->
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">Delivery Fare (Rs)</label>
                    <input type="number" id="delivery-fare" class="form-control" value="0" step="0.01" min="0" placeholder="Enter delivery charges">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">Rider Mobile Number</label>
                    <input type="tel" id="delivery-rider-mobile" class="form-control" placeholder="03xx-xxxxxxx">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold mb-2 small">
                            <i class="ti ti-truck me-1 text-blue-600"></i>Vehicle Photo
                        </label>
                        <label class="d-block btn btn-outline-primary w-100 p-3 text-center cursor-pointer position-relative" style="border: 2px solid #3b82f6; border-radius: 8px; background-color: #eff6ff; transition: all 0.3s ease; min-height: 100px; display: flex; flex-direction: column; justify-content: center; align-items: center; overflow: hidden;" onmouseover="this.style.backgroundColor='#dbeafe'; this.style.borderColor='#2563eb';" onmouseout="this.style.backgroundColor='#eff6ff'; this.style.borderColor='#3b82f6';">
                            <i class="ti ti-box text-2xl text-blue-600 mb-2 d-block" id="vehicle-icon-placeholder"></i>
                            <p class="mb-0 small fw-bold text-blue-600" id="vehicle-text-placeholder">Vehicle Photo</p>
                            <input type="file" id="vehicle-photo-capture" accept="image/*" class="d-none" onchange="handleVehiclePhoto(this, 'vehicle')">
                            <div id="vehicle-photo-capture-preview" class="position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; display: none; align-items: center; justify-content: center; background-color: rgba(239, 246, 255, 0.95); border-radius: 8px;"></div>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold mb-2 small">
                            <i class="ti ti-user-circle me-1 text-purple-600"></i>Rider Front Photo
                        </label>
                        <label class="d-block btn btn-outline-primary w-100 p-3 text-center cursor-pointer position-relative" style="border: 2px solid #a855f7; border-radius: 8px; background-color: #faf5ff; transition: all 0.3s ease; min-height: 100px; display: flex; flex-direction: column; justify-content: center; align-items: center; overflow: hidden;" onmouseover="this.style.backgroundColor='#f3e8ff'; this.style.borderColor='#9333ea';" onmouseout="this.style.backgroundColor='#faf5ff'; this.style.borderColor='#a855f7';">
                            <i class="ti ti-user-circle text-2xl text-purple-600 mb-2 d-block" id="rider-icon-placeholder"></i>
                            <p class="mb-0 small fw-bold text-purple-600" id="rider-text-placeholder">Rider Photo</p>
                            <input type="file" id="vehicle-rider-photo" accept="image/*" capture="user" class="d-none" onchange="handleVehiclePhoto(this, 'rider')">
                            <div id="vehicle-rider-photo-preview" class="position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; display: none; align-items: center; justify-content: center; background-color: rgba(250, 245, 255, 0.95); border-radius: 8px;"></div>
                        </label>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold mb-2 small">
                            <i class="ti ti-id me-1 text-green-600"></i>ID Card Front Photo
                        </label>
                        <label class="d-block btn btn-outline-primary w-100 p-3 text-center cursor-pointer position-relative" style="border: 2px solid #10b981; border-radius: 8px; background-color: #f0fdf4; transition: all 0.3s ease; min-height: 100px; display: flex; flex-direction: column; justify-content: center; align-items: center; overflow: hidden;" onmouseover="this.style.backgroundColor='#dcfce7'; this.style.borderColor='#059669';" onmouseout="this.style.backgroundColor='#f0fdf4'; this.style.borderColor='#10b981';">
                            <i class="ti ti-id text-2xl text-green-600 mb-2 d-block" id="id-front-icon-placeholder"></i>
                            <p class="mb-0 small fw-bold text-green-600" id="id-front-text-placeholder">ID Card Front</p>
                            <input type="file" id="id-card-front-photo" accept="image/*" class="d-none" onchange="handleVehiclePhoto(this, 'id-front')">
                            <div id="id-card-front-photo-preview" class="position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; display: none; align-items: center; justify-content: center; background-color: rgba(240, 253, 244, 0.95); border-radius: 8px;"></div>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold mb-2 small">
                            <i class="ti ti-id me-1 text-orange-600"></i>ID Card Back Photo
                        </label>
                        <label class="d-block btn btn-outline-primary w-100 p-3 text-center cursor-pointer position-relative" style="border: 2px solid #f97316; border-radius: 8px; background-color: #fff7ed; transition: all 0.3s ease; min-height: 100px; display: flex; flex-direction: column; justify-content: center; align-items: center; overflow: hidden;" onmouseover="this.style.backgroundColor='#ffedd5'; this.style.borderColor='#ea580c';" onmouseout="this.style.backgroundColor='#fff7ed'; this.style.borderColor='#f97316';">
                            <i class="ti ti-id text-2xl text-orange-600 mb-2 d-block" id="id-back-icon-placeholder"></i>
                            <p class="mb-0 small fw-bold text-orange-600" id="id-back-text-placeholder">ID Card Back</p>
                            <input type="file" id="id-card-back-photo" accept="image/*" class="d-none" onchange="handleVehiclePhoto(this, 'id-back')">
                            <div id="id-card-back-photo-preview" class="position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; display: none; align-items: center; justify-content: center; background-color: rgba(255, 247, 237, 0.95); border-radius: 8px;"></div>
                        </label>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label fw-bold mb-2 small">
                            <i class="ti ti-truck-delivery me-1 text-red-600"></i>Current Vehicle Photo (If Vehicle Changed)
                        </label>
                        <label class="d-block btn btn-outline-primary w-100 p-3 text-center cursor-pointer position-relative" style="border: 2px solid #ef4444; border-radius: 8px; background-color: #fef2f2; transition: all 0.3s ease; min-height: 100px; display: flex; flex-direction: column; justify-content: center; align-items: center; overflow: hidden;" onmouseover="this.style.backgroundColor='#fee2e2'; this.style.borderColor='#dc2626';" onmouseout="this.style.backgroundColor='#fef2f2'; this.style.borderColor='#ef4444';">
                            <i class="ti ti-truck-delivery text-2xl text-red-600 mb-2 d-block" id="current-vehicle-icon-placeholder"></i>
                            <p class="mb-0 small fw-bold text-red-600" id="current-vehicle-text-placeholder">Current Vehicle Photo</p>
                            <input type="file" id="current-vehicle-photo" accept="image/*" class="d-none" onchange="handleVehiclePhoto(this, 'current-vehicle')">
                            <div id="current-vehicle-photo-preview" class="position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; display: none; align-items: center; justify-content: center; background-color: rgba(254, 242, 242, 0.95); border-radius: 8px;"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info fw-bold" id="share-delivery-link-btn" style="border-radius: 8px; padding: 10px 20px;">
                    <i class="ti ti-share me-1"></i>Share Link
                </button>
                <button type="button" class="btn btn-primary fw-bold" id="confirm-delivery-btn" style="background-color: #f97316; border-radius: 8px; padding: 10px 30px;">
                    CONFIRM DELIVERY
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rider View Modal (for delivery data submission) -->
<div class="modal fade" id="rider-view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-2" style="background-color: #f97316; color: white;">
                <h5 class="modal-title fw-bold">
                    <i class="ti ti-truck me-2"></i>GODOWN LOADING FORM
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h4 class="fw-bold text-orange-600 mb-2">MUBARAK TRADERS</h4>
                    <span class="badge bg-orange-500 text-white px-3 py-1" style="font-size: 10px; font-weight: 900; text-transform: uppercase;">Godown Loading Form</span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold mb-2">Rider Mobile Number</label>
                    <input type="tel" id="rider-mobile-input" class="form-control" placeholder="03xx-xxxxxxx">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold mb-2 small">Saman ki Photo</label>
                        <label class="d-block border border-dashed border-blue-200 rounded-lg p-3 text-center cursor-pointer" style="background-color: #eff6ff;">
                            <i class="ti ti-box text-2xl text-blue-600 mb-2 d-block"></i>
                            <p class="mb-0 small fw-bold text-blue-600">Item Photo</p>
                            <input type="file" id="rider-item-photo" accept="image/*" class="d-none" onchange="handleRiderFile(this, 'item')">
                            <div id="rider-item-preview" class="mt-2"></div>
                        </label>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold mb-2 small">Rider Face Photo</label>
                        <label class="d-block border border-dashed border-purple-200 rounded-lg p-3 text-center cursor-pointer" style="background-color: #faf5ff;">
                            <i class="ti ti-user-circle text-2xl text-purple-600 mb-2 d-block"></i>
                            <p class="mb-0 small fw-bold text-purple-600">Face Photo</p>
                            <input type="file" id="rider-face-photo" accept="image/*" capture="user" class="d-none" onchange="handleRiderFile(this, 'face')">
                            <div id="rider-face-preview" class="mt-2"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold" id="submit-rider-data-btn" style="background-color: #f97316; border-radius: 8px; padding: 10px 30px;">
                    CONFIRM & SEND DATA
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
    /* ========== Premium Search Filter (unique, beautiful) ========== */
    /* YouTube-style search input */
    .item-search-input {
        background: #fff !important;
        border: 1px solid #ccc !important;
        border-radius: 40px !important;
        padding: 10px 50px 10px 20px !important;
        font-size: 1rem !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1) !important;
    }
    .item-search-input::placeholder {
        color: #999;
        font-weight: 400;
    }
    .item-search-input:hover {
        border-color: #999 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }
    .item-search-input:focus {
        border-color: #1a73e8 !important;
        box-shadow: 0 2px 8px rgba(26, 115, 232, 0.2) !important;
        background: #fff !important;
        outline: none !important;
    }
    .item-search-icon {
        color: #667eea !important;
        opacity: 0.85;
    }
    
    /* YouTube-style results dropdown */
    .item-search-results-box {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        animation: searchResultsIn 0.2s ease-out;
        margin-top: 4px !important;
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
        transition: all 0.2s ease;
    }
    .item-search-result:last-child {
        border-bottom: none !important;
    }
    /* YouTube-style result hover */
    .item-search-result:hover,
    .item-search-result.selected {
        background-color: #f5f5f5 !important;
        cursor: pointer;
    }
    
    .item-search-result {
        border-bottom: 1px solid #f0f0f0;
    }
    
    .item-search-result:last-child {
        border-bottom: none !important;
    }
    
    #item-search-results .p-3 {
        padding: 1rem !important;
    }
    
    #item-search-results .fw-bold.text-dark.mb-1 {
        font-size: 0.95rem;
        line-height: 1.4;
    }
    
    #item-search-results .text-primary.mb-1 {
        font-size: 0.85rem;
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
    mark {
        background: #ffeb3b;
        padding: 2px 4px;
        border-radius: 3px;
    }
    
    /* 3-Step Switch Styles */
    button#estimateSwitch,
    .custom-3step-switch {
        position: relative;
        width: 80px;
        height: 30px;
        background: #6c757d;
        border-radius: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none !important;
        padding: 0 !important;
        outline: none;
    }
    button#estimateSwitch:focus {
        outline: none;
        box-shadow: none;
    }
    
    .custom-3step-switch.switch-sale {
        background: var(--bs-primary, #0d6efd) !important;
    }
    
    .custom-3step-switch.switch-estimate {
        background: var(--bs-warning, #ffc107) !important;
    }
    
    .custom-3step-switch.switch-sale-order {
        background: var(--bs-success, #198754) !important;
    }
    
    .switch-slider {
        position: absolute;
        width: 24px;
        height: 24px;
        background: white;
        border-radius: 50%;
        top: 3px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        pointer-events: none; /* let clicks pass to parent #estimateSwitch */
    }
    
    .switch-slider.switch-position-0 {
        left: 3px !important;
    }
    
    .switch-slider.switch-position-1 {
        left: 28px !important;
    }
    
    .switch-slider.switch-position-2 {
        left: 53px !important;
    }
    
    .switch-indicators {
        position: absolute;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: space-around;
        align-items: center;
        pointer-events: none;
        padding: 0 8px;
    }
    
    .switch-indicators span {
        font-size: 8px;
        color: rgba(255,255,255,0.5);
        font-weight: 600;
    }
    
    /* Ensure modal footer buttons are visible */
    #add-item-modal .modal-footer {
        display: block !important;
        visibility: visible !important;
    }
    
    #add-item-modal .modal-footer .row {
        display: flex !important;
    }
    
    #add-item-modal .modal-footer .col-6 {
        display: block !important;
    }
    
    /* Ensure add-item-modal is clickable (nothing blocking) - high z-index so it's above header/sidebar */
    #add-item-modal.modal,
    body.modal-open #add-item-modal.modal {
        z-index: 9999 !important;
    }
    #add-item-modal .modal-dialog,
    #add-item-modal .modal-content,
    #add-item-modal .modal-body,
    #add-item-modal input,
    #add-item-modal select,
    #add-item-modal button,
    #add-item-modal [tabindex] {
        pointer-events: auto !important;
    }
    #add-item-modal .modal-dialog {
        position: relative;
        z-index: 1;
    }
    #add-item-modal .modal-content {
        position: relative;
        z-index: 1;
    }
    /* When modal is open, ensure backdrop is below modal */
    body.modal-open .modal-backdrop {
        z-index: 9998 !important;
    }
    
    #return-entry-btn,
    #claim-entry-btn,
    #scrap-in-btn,
    #scrap-sale-btn {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Modern Invoice Design Styles */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }
    
    .invoice-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        border-top: 8px solid #2563eb;
    }
    
    .modern-input {
        width: 100%;
        padding: 16px;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 16px;
        font-weight: 800;
        font-size: 18px;
        color: #1f2937;
        transition: all 0.2s;
    }
    
    .modern-input:focus {
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
        margin-bottom: 4px;
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
    }
    
    .invoice-header-left h1 {
        font-size: 32px;
        font-weight: 900;
        color: #1e3a8a;
        text-transform: uppercase;
        line-height: 1;
        letter-spacing: -0.02em;
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
        font-size: 16px;
        color: #6b7280;
        font-weight: bold;
        margin-top: -12px;
    }
    
    .invoice-header-right {
        text-align: right;
    }
    
    .invoice-header-right .invoice-label {
        font-size: 12px;
        color: #9ca3af;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .invoice-header-right .invoice-number {
        font-size: 20px;
        font-weight: 900;
        color: #2563eb;
    }
    
    .invoice-header-right .invoice-date {
        font-size: 14px;
        font-weight: 700;
        color: #374151;
        margin-top: 4px;
    }
    
    .vehicle-card {
        background: rgba(37, 99, 235, 0.05);
        padding: 16px;
        border-radius: 12px;
        border: 1px solid rgba(37, 99, 235, 0.1);
        position: relative;
    }
    
    .vehicle-label {
        font-size: 9px;
        font-weight: 900;
        color: #60a5fa;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    
    .vehicle-reg {
        font-size: 14px;
        font-weight: 900;
        color: #2563eb;
        text-transform: uppercase;
        margin-top: 4px;
    }
    
    .vehicle-details {
        font-size: 11px;
        font-weight: 700;
        color: #1e3a8a;
        text-transform: uppercase;
        margin-top: 2px;
    }
    
    .items-container {
        min-height: 60px;
        padding-top: 12px;
    }
    
    .item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 8px;
        margin-bottom: 12px;
    }
    
    .item-name {
        font-weight: 700;
        font-size: 12px;
        color: #1f2937;
    }
    
    .item-details {
        font-size: 9px;
        color: #9ca3af;
    }
    
    .item-total {
        font-weight: 900;
        font-size: 14px;
        color: #374151;
    }
    
    .total-section {
        padding-top: 16px;
        border-top: 2px dashed #e5e7eb;
    }
    
    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 16px;
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
    }
    
    .discount-section {
        background: #dcfce7;
        border-radius: 12px;
        border: 1px solid #bbf7d0;
        padding: 8px 16px;
        margin: 8px 0;
    }
    
    .discount-label {
        font-size: 10px;
        font-weight: 900;
        color: #16a34a;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .net-payable {
        background: #eff6ff;
        border-radius: 12px;
        border: 1px solid #bfdbfe;
        padding: 6px 16px;
        margin: 8px 0;
    }
    
    .net-payable-label {
        font-size: 10px;
        font-weight: 900;
        color: #1e40af;
        text-transform: uppercase;
    }
    
    .net-payable-value {
        font-size: 14px;
        font-weight: 900;
        color: #1e40af;
    }
    
    .received-amount-section {
        background: #f9fafb;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 8px 16px;
        margin: 8px 0;
    }
    
    .received-amount-label {
        font-size: 10px;
        font-weight: 900;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .payment-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px;
        margin-top: 5px;
    }
    
    .bank-received-card {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px;
        margin-top: 5px;
    }
    
    .previous-balance-section {
        background: #fef3c7;
        border-radius: 12px;
        border: 1px solid #fde68a;
        padding: 8px 16px;
        margin: 8px 0;
    }
    
    .previous-balance-label {
        font-size: 10px;
        font-weight: 900;
        color: #92400e;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .grand-total-card {
        background: #1f2937;
        border-radius: 16px;
        padding: 16px;
        color: white;
        margin-top: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .grand-total-label {
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        opacity: 0.6;
        letter-spacing: 0.1em;
    }
    
    .grand-total-value {
        font-size: 32px;
        font-weight: 900;
        color: #60a5fa;
        letter-spacing: -0.02em;
    }
    
    .item-count {
        font-size: 8px;
        font-weight: 700;
        color: #60a5fa;
    }
    
    .modern-btn-primary {
        width: 100%;
        padding: 20px;
        background: #2563eb;
        color: white;
        border-radius: 24px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        border: none;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .modern-btn-primary:active {
        transform: scale(0.95);
    }
    
    .modern-btn-primary i {
        font-size: 24px;
    }
    
    .action-btn-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .action-btn {
        padding: 14px;
        border-radius: 16px;
        font-weight: 900;
        text-transform: uppercase;
        font-size: 10px;
        border: 2px solid;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .action-btn:active {
        transform: scale(0.95);
    }
    
    .empty-state {
        text-align: center;
        color: #d1d5db;
        font-size: 10px;
        padding: 20px;
        font-style: italic;
    }
    
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
    
    .suggestions-list {
        position: absolute;
        width: 100%;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 110;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        margin-top: 4px;
        top: 100%;
        left: 0;
    }
    
    .suggestion-item {
        padding: 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s;
    }
    
    .suggestion-item:hover {
        background-color: #eff6ff;
    }
    
    .suggestion-item:last-child {
        border-bottom: none;
    }
    
    .hidden {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let salesItems = [];
    let itemCounter = 0;
    
    /* Move add-item-modal to body so it is never behind layout (fixes no-click) */
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

    function getSelectedCustomerId() {
        return (($('#customer_id').val() || '') + '').trim();
    }

    function setAddSaleItemEnabled(enabled) {
        const $btn = $('#add-new-item-btn');
        if (!$btn.length) return;
        $btn.prop('disabled', !enabled);
        $btn.toggleClass('opacity-50', !enabled);
        $btn.toggleClass('cursor-not-allowed', !enabled);
        if (!enabled) {
            $btn.removeClass('pulse-animation');
        } else {
            $btn.addClass('pulse-animation');
        }
    }

    function ensureCustomerSelectedOrWarn() {
        const customerId = getSelectedCustomerId();
        if (customerId) return true;
        if (typeof Swal !== 'undefined' && Swal && Swal.fire) {
            Swal.fire({
                icon: 'warning',
                title: 'Customer Required',
                text: 'Please select a party/customer first.',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Please select a party/customer first.');
        }
        $('#customer_search').trigger('focus');
        return false;
    }

    // Disable Add Sale Item until customer selected
    $(function() {
        setAddSaleItemEnabled(!!getSelectedCustomerId());

        $('#customer_search').on('input', function() {
            // If user clears the visible search, treat as "no customer selected"
            if (!String($(this).val() || '').trim()) {
                $('#customer_id').val('');
                setAddSaleItemEnabled(false);
            }
        });

        $('#add-new-item-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!ensureCustomerSelectedOrWarn()) return;
            currentEntryType = 'sale';
            $('#add-item-modal').modal('show');
        });

        $('#return-entry-btn').on('click', function(e) {
            e.preventDefault();
            if (!ensureCustomerSelectedOrWarn()) return;
            currentEntryType = 'return';
            $('#add-item-modal').modal('show');
        });

        $('#claim-entry-btn').on('click', function(e) {
            e.preventDefault();
            if (!ensureCustomerSelectedOrWarn()) return;
            currentEntryType = 'claim';
            $('#add-item-modal').modal('show');
        });

        $('#scrap-in-btn').on('click', function(e) {
            e.preventDefault();
            if (!ensureCustomerSelectedOrWarn()) return;
            currentEntryType = 'scrap_in';
            $('#add-item-modal').modal('show');
        });

        $('#scrap-sale-btn').on('click', function(e) {
            e.preventDefault();
            if (!ensureCustomerSelectedOrWarn()) return;
            currentEntryType = 'scrap_sale';
            $('#add-item-modal').modal('show');
        });
    });
    
    // Store customer data for search
    const customersData = [
        @foreach($customers as $customer)
        {
            id: {{ $customer->id }},
            name: '{{ $customer->names[0] ?? 'N/A' }}',
            phone: '{{ $customer->phones[0] ?? '' }}',
            company: '{{ $customer->company ?? '' }}',
            address: '{{ $customer->address ?? '' }}',
            area: '{{ $customer->area ?? '' }}',
            customer_type: '{{ $customer->customer_type ?? 'retail' }}',
            displayText: '{{ $customer->names[0] ?? 'N/A' }}@if($customer->company) - {{ $customer->company }}@endif'
        }@if(!$loop->last),@endif
        @endforeach
    ];
    
    // Customer search function
    window.searchCustomer = function(val) {
        const list = $('#customerSuggestions');
        if(!val || val.trim() === '') {
            list.addClass('hidden');
            return;
        }
        
        const searchTerm = val.toLowerCase();
        const matches = customersData.filter(c => 
            c.name.toLowerCase().includes(searchTerm) ||
            c.company.toLowerCase().includes(searchTerm) ||
            c.phone.includes(searchTerm) ||
            c.displayText.toLowerCase().includes(searchTerm)
        );
        
        let html = '';
        if(matches.length > 0) {
            html = matches.map(c => `
                <div class="suggestion-item" onclick="selectCustomer(${c.id}, '${c.name.replace(/'/g, "\\'")}', '${c.phone.replace(/'/g, "\\'")}', '${c.address.replace(/'/g, "\\'")}', '${c.area.replace(/'/g, "\\'")}', '${c.displayText.replace(/'/g, "\\'")}')">
                    <p class="mb-0 fw-bold" style="font-size: 14px;">${c.displayText}</p>
                    ${c.phone ? '<p class="mb-0 text-muted small">' + c.phone + '</p>' : ''}
                </div>
            `).join('');
        } else {
            html = '<div class="suggestion-item text-muted" style="padding: 12px;">No customers found</div>';
        }
        
        list.html(html);
        list.removeClass('hidden');
    };
    
    // Select customer function
    window.selectCustomer = function(id, name, phone, address, area, displayText) {
        try {
            const c = customersData.find(function(x) { return String(x.id) === String(id); });
            window.selectedCustomerType = c && c.customer_type ? String(c.customer_type) : 'retail';
        } catch (e) {
            window.selectedCustomerType = 'retail';
        }
        $('#customer_id').val(id);
        $('#customer_search').val(displayText);
        $('#customer_mobile').val(phone);
        $('#customer_address').val(address);
        $('#customer_area').val(area);
        $('#customerSuggestions').addClass('hidden');

        setAddSaleItemEnabled(true);
        
        // Trigger calculation if needed
        if(typeof calculateFinalTotalFromInput === 'function') {
            calculateFinalTotalFromInput();
        }
    };
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#customer_search, #customerSuggestions').length) {
            $('#customerSuggestions').addClass('hidden');
        }
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

    // Auto-select Barki Express branch on page load (after function is defined)
    setTimeout(function() {
        // Find Barki Express branch from dropdown
        const branchDropdown = $('#branchDropdown').next('.dropdown-menu');
        const barkiBranch = branchDropdown.find('a').filter(function() {
            const branchText = $(this).text().toUpperCase();
            return branchText.includes('BARKI');
        }).first();
        
        if (barkiBranch.length > 0) {
            // Extract branch info from onclick attribute
            const onclickAttr = barkiBranch.attr('onclick');
            if (onclickAttr) {
                // Parse: selectSalesBranch(branchId, 'branchName', 'branchCode')
                const match = onclickAttr.match(/selectSalesBranch\((\d+),\s*'([^']+)',\s*'([^']*)'\)/);
                if (match) {
                    const branchId = parseInt(match[1]);
                    const branchName = match[2];
                    const branchCode = match[3];
                    
                    // Auto-select Barki Express branch
                    selectSalesBranch(branchId, branchName, branchCode);
                }
            }
        }
    }, 800);

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
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        hours = String(hours).padStart(2, '0');
        
        $('#currentDateTime').text(`${day}/${month}/${year}, ${hours}:${minutes}:${seconds} ${ampm}`);
    }
    
    // Store original invoice number and current state (set after DOM ready)
    let originalInvoiceNumber = '';
    let currentState = 0; // 0 = Sale, 1 = Estimate, 2 = Sale Order
    let originalEstimateNumber = null;
    let originalSaleOrderNumber = null;
    
    $(function() {
        var $switch = $('#estimateSwitch');
        if (!$switch.length) return;
        $switch.find('.switch-slider').addClass('switch-position-0');
        $switch.addClass('switch-sale');
        $('#status-icon-box').addClass('bg-primary');
        $('#sales-number').css({'color': '#2563eb', 'margin-bottom': '0'});
        $switch.css('background', '#2563eb');
        $('#add-vehicle-btn').css('color', '#2563eb');
        $('#add-item-modal .modal-title').css('color', '#2563eb');
        $('#add-new-item-btn').css('background', '#2563eb');
        $('#save-sale-btn').attr('style', 'background: #2563eb !important; border-color: #2563eb !important;');
        $('#branch-name-title').css('color', '#2563eb');
        // Load next INV number (separate series) when branch is selected
        if ($('#salesBranchId').val()) {
            $.get('{{ route("sales.next.invoice.number") }}').then(function(response) {
                if (response.number != null) {
                    $('#sales-number').text('INV #' + response.number);
                    originalInvoiceNumber = 'INV #' + response.number;
                } else {
                    originalInvoiceNumber = $('#sales-number').text();
                }
            }).fail(function() { originalInvoiceNumber = $('#sales-number').text(); });
        } else {
            originalInvoiceNumber = $('#sales-number').text();
        }
    });
    
    // Global fallback so S/E/O switch works even if jQuery binding fails (called by onclick + delegated handler)
    window.doEstimateSwitchCycle = function() {
        if (typeof $ === 'undefined') return;
        const salesNumber = $('#sales-number');
        const pageTitle = $('.page-title h4');
        const switchLabel = $('#switch-label');
        const slider = $('#estimateSwitch').find('.switch-slider');
        const iconBox = $('#status-icon-box');
        currentState = (currentState + 1) % 3;
        
        if (currentState === 0) {
            // State 0: Sale/Invoice
            $('#sale-status').val('pending');
            
            // Set colors immediately - Blue
            salesNumber.removeClass('text-warning text-success text-info text-primary');
            salesNumber.css({'color': '#2563eb', 'margin-bottom': '0'});
            $('#estimateSwitch').css('background', '#2563eb');
            $('#add-vehicle-btn').css('color', '#2563eb'); // Update vehicle button color
            $('#add-item-modal .modal-title').css('color', '#2563eb'); // Update modal title color
            $('#add-new-item-btn').css('background', '#2563eb'); // Update ADD SALE ITEM button color
            $('#save-sale-btn').attr('style', 'background: #2563eb !important; border-color: #2563eb !important;'); // Update Save Sale button color
            $('#branch-name-title').css('color', '#2563eb'); // Update branch name title color
            $('#cash-received-section').show(); // Show cash received section for Sale
            $('#bank-received-section').show(); // Show bank received section for Sale
            
            // Fetch next INV number (separate series) from server
            $.ajax({
                url: '{{ route("sales.next.invoice.number") }}',
                method: 'GET',
                success: function(response) {
                    if (response.number != null) {
                        salesNumber.text('INV #' + response.number);
                        originalInvoiceNumber = 'INV #' + response.number;
                    } else if (originalInvoiceNumber && originalInvoiceNumber.includes('INV #')) {
                        salesNumber.text(originalInvoiceNumber);
                    } else {
                        salesNumber.text('INV #00001');
                    }
                    salesNumber.css('color', '#2563eb');
                },
                error: function() {
                    if (originalInvoiceNumber && originalInvoiceNumber.includes('INV #')) {
                        salesNumber.text(originalInvoiceNumber);
                    } else {
                        salesNumber.text('INV #00001');
                    }
                    salesNumber.css('color', '#2563eb');
                }
            });
            
            switchLabel.text('SALE');
            pageTitle.text('Create Sales');
            // Update switch - Position 0, Blue color
            slider.removeClass('switch-position-1 switch-position-2').addClass('switch-position-0');
            $('#estimateSwitch').removeClass('switch-estimate switch-sale-order').addClass('switch-sale');
            // Update icon box color - Blue
            iconBox.removeClass('bg-warning bg-success').addClass('bg-primary');
            
        } else if (currentState === 1) {
            // State 1: Estimate
            $('#sale-status').val('estimate');
            
            // Set colors immediately - Yellow
            salesNumber.removeClass('text-primary text-info text-success text-warning');
            salesNumber.css('color', '#ffc107');
            $('#estimateSwitch').css('background', '#ffc107');
            $('#add-vehicle-btn').css('color', '#ffc107'); // Update vehicle button color
            $('#add-item-modal .modal-title').css('color', '#ffc107'); // Update modal title color
            $('#add-new-item-btn').css('background', '#ffc107'); // Update ADD SALE ITEM button color
            $('#save-sale-btn').attr('style', 'background: #ffc107 !important; border-color: #ffc107 !important;'); // Update Save Sale button color
            $('#branch-name-title').css('color', '#ffc107'); // Update branch name title color
            $('#cash-received-section').hide(); // Hide cash received section for Estimate
            $('#bank-received-section').hide(); // Hide bank received section for Estimate
            
            // Store original invoice number if not already stored
            if (!originalInvoiceNumber || originalInvoiceNumber.includes('EST #') || originalInvoiceNumber.includes('SO #')) {
                originalInvoiceNumber = $('#sales-number').text().replace(/EST #|SO #/g, 'INV #');
            }
            
            // Store estimate number if switching from sale order
            if (salesNumber.text().includes('SO #')) {
                const currentNumber = salesNumber.text().match(/\d+/);
                originalEstimateNumber = currentNumber ? currentNumber[0] : null;
            }
            
            // Fetch next estimate number from server
            $.ajax({
                url: '{{ route("sales.next.estimate.number") }}',
                method: 'GET',
                success: function(response) {
                    if (response.number) {
                        salesNumber.text('EST #' + response.number);
                        originalEstimateNumber = response.number;
                    } else {
                        const currentNumber = salesNumber.text().match(/\d+/);
                        const estNumber = currentNumber ? currentNumber[0] : '00000';
                        salesNumber.text('EST #' + estNumber);
                        originalEstimateNumber = estNumber;
                    }
                    // Ensure color stays yellow
                    salesNumber.css('color', '#ffc107');
                },
                error: function() {
                    const currentNumber = salesNumber.text().match(/\d+/);
                    const estNumber = currentNumber ? currentNumber[0] : '00000';
                    salesNumber.text('EST #' + estNumber);
                    originalEstimateNumber = estNumber;
                    // Ensure color stays yellow
                    salesNumber.css('color', '#ffc107');
                }
            });
            
            switchLabel.text('ESTIMATE');
            pageTitle.text('Create Estimate');
            // Update switch - Position 1, Yellow color
            slider.removeClass('switch-position-0 switch-position-2').addClass('switch-position-1');
            $('#estimateSwitch').removeClass('switch-sale switch-sale-order').addClass('switch-estimate');
            // Update icon box color - Yellow
            iconBox.removeClass('bg-primary bg-success').addClass('bg-warning');
            
        } else if (currentState === 2) {
            // State 2: Sale Order
            $('#sale-status').val('sale_order');
            
            // Set colors immediately - Green
            salesNumber.removeClass('text-primary text-warning text-info text-success');
            salesNumber.css('color', '#198754');
            $('#estimateSwitch').css('background', '#198754');
            $('#add-vehicle-btn').css('color', '#198754'); // Update vehicle button color
            $('#add-item-modal .modal-title').css('color', '#198754'); // Update modal title color
            $('#add-new-item-btn').css('background', '#198754'); // Update ADD SALE ITEM button color
            $('#save-sale-btn').attr('style', 'background: #198754 !important; border-color: #198754 !important;'); // Update Save Sale button color
            $('#branch-name-title').css('color', '#198754'); // Update branch name title color
            $('#cash-received-section').show(); // Show cash received section for Sale Order
            $('#bank-received-section').show(); // Show bank received section for Sale Order
            
            // Store estimate number if switching from estimate
            if (salesNumber.text().includes('EST #')) {
                const currentNumber = salesNumber.text().match(/\d+/);
                originalEstimateNumber = currentNumber ? currentNumber[0] : null;
            }
            
            // Fetch next sale order number from server
            $.ajax({
                url: '{{ route("sales.next.sale.order.number") }}',
                method: 'GET',
                success: function(response) {
                    if (response.number) {
                        salesNumber.text('SO #' + response.number);
                        originalSaleOrderNumber = response.number;
                    } else {
                        const currentNumber = salesNumber.text().match(/\d+/);
                        const soNumber = currentNumber ? currentNumber[0] : '00000';
                        salesNumber.text('SO #' + soNumber);
                        originalSaleOrderNumber = soNumber;
                    }
                    // Ensure color stays green
                    salesNumber.css('color', '#198754');
                },
                error: function() {
                    const currentNumber = salesNumber.text().match(/\d+/);
                    const soNumber = currentNumber ? currentNumber[0] : '00000';
                    salesNumber.text('SO #' + soNumber);
                    originalSaleOrderNumber = soNumber;
                    // Ensure color stays green
                    salesNumber.css('color', '#198754');
                }
            });
            
            switchLabel.text('SALE ORDER');
            pageTitle.text('Create Sale Order');
            // Update switch - Position 2, Green color
            slider.removeClass('switch-position-0 switch-position-1').addClass('switch-position-2');
            $('#estimateSwitch').removeClass('switch-sale switch-estimate').addClass('switch-sale-order');
            // Update icon box color - Green
            iconBox.removeClass('bg-primary bg-warning').addClass('bg-success');
        }
    };

    $(document).on('click', '#estimateSwitch', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (typeof window.doEstimateSwitchCycle === 'function') window.doEstimateSwitchCycle();
    });
    
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
        
        // Perform AJAX search (no branch_id required)
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
                
                items.forEach(itemData => {
                    // Handle branch results
                    if (itemData.type === 'branch') {
                        html += `
                            <div class="p-2 border-bottom branch-search-result" 
                                 data-type="branch"
                                 data-id="${itemData.id}"
                                 style="background-color: #e7f3ff; cursor: pointer; transition: background 0.2s;">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-building me-2 text-primary"></i>
                                    <div>
                                        <div class="fw-bold text-primary">${itemData.display}</div>
                                        <div class="small text-muted">Branch</div>
                                    </div>
                                </div>
                            </div>
                        `;
                        return;
                    }
                    
                    // Handle warehouse results
                    if (itemData.type === 'warehouse') {
                        html += `
                            <div class="p-2 border-bottom warehouse-search-result" 
                                 data-type="warehouse"
                                 data-id="${itemData.id}"
                                 style="background-color: #f0f9ff; cursor: pointer; transition: background 0.2s;">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-archive me-2 text-info"></i>
                                    <div>
                                        <div class="fw-bold text-info">${itemData.display}</div>
                                        <div class="small text-muted">Warehouse${itemData.branch_name ? ' - ' + itemData.branch_name : ''}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                        return;
                    }

                    // Handle warranty-code traceability matches (sold sale items)
                    if (itemData.type === 'warranty_code') {
                        html += `
                            <div class="p-3 border-bottom item-search-result"
                                 data-type="warranty_code"
                                 data-id="${itemData.sale_item_id || ''}"
                                 data-item-id="${itemData.item_id || ''}"
                                 data-sale-id="${itemData.sale_id || ''}"
                                 data-item-name="${(itemData.item_name || '').replace(/"/g,'&quot;')}"
                                 data-customer-name="${(itemData.customer_name || '').replace(/"/g,'&quot;')}"
                                 data-reference="${(itemData.reference || '').replace(/"/g,'&quot;')}"
                                 data-sale-date="${(itemData.sale_date || '').toString().replace(/"/g,'&quot;')}"
                                 data-matched-code="${(itemData.matched_code || '').replace(/"/g,'&quot;')}"
                                 data-branch-name="${(itemData.branch_name || '').replace(/"/g,'&quot;')}"
                                 data-warehouse-name="${(itemData.warehouse_name || '').replace(/"/g,'&quot;')}"
                                 data-has-proof="${itemData.has_proof ? 1 : 0}"
                                 style="cursor:pointer; background: rgba(16,185,129,0.08);">
                                <div class="d-flex justify-content-between">
                                    <div style="min-width:0;">
                                        <div class="fw-bold text-dark text-truncate">${itemData.item_name || 'Item'}</div>
                                        <div class="small text-success fw-semibold">Matched by warranty code: <span class="fw-bold">${itemData.matched_code || ''}</span></div>
                                        <div class="small text-muted text-truncate">Customer: ${itemData.customer_name || '—'} • ${itemData.reference || ''} • ${itemData.sale_date || ''}</div>
                                        <div class="small text-muted text-truncate">Branch: ${itemData.branch_name || '—'} • Warehouse: ${itemData.warehouse_name || '—'} • Proof: ${itemData.has_proof ? 'Attached' : 'Not attached'}</div>
                                    </div>
                                    <div class="text-end ms-2">
                                        <span class="badge bg-success">Trace</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        return;
                    }
                    
                    // Extract item and warehouse information
                    const item = itemData.item || itemData;
                    const itemType = item.type || '';
                    const partNumber = item.partnumber_item ? item.partnumber_item.name : (itemData.part_number || '');
                    const barCode = item.bar_code || itemData.bar_code || '';
                    const shortDisc = (item.short_disc || '').trim();
                    const proDis = (item.pro_dis || '').trim();
                    
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
                    const category = item.category ? item.category.name : (itemData.category_name || '');
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
                    
                    // Warehouse stock and quantity
                    const warehouseQuantity = parseFloat(itemData.warehouse_quantity || item.on_hand || 0);
                    const availableQuantity = parseFloat(itemData.available_quantity || warehouseQuantity);
                    const stock = warehouseQuantity;
                    
                    // Price information - prioritize sale_price
                    const salePrice = parseFloat(itemData.sale_price || item.sale_price || 0);
                    const calculatedPricePerUnit = parseFloat(itemData.calculated_price_per_unit || 0);
                    const pricePerUnit = parseFloat(itemData.price_per_unit || item.price_per_unit || 0);
                    const packingPurchaseRate = parseFloat(itemData.packing_purchase_rate || item.packing_purchase_rate || 0);
                    const totalPrice = parseFloat(itemData.total_price || item.total_price || 0);
                    
                    // Priority: sale_price > calculated_price_per_unit > price_per_unit > packing_purchase_rate > total_price
                    let rate = 0;
                    if (salePrice > 0) {
                        rate = salePrice;
                    } else if (calculatedPricePerUnit > 0) {
                        rate = calculatedPricePerUnit;
                    } else if (pricePerUnit > 0) {
                        rate = pricePerUnit;
                    } else if (packingPurchaseRate > 0) {
                        rate = packingPurchaseRate;
                    } else if (totalPrice > 0) {
                        rate = totalPrice;
                    }
                    
                    const unit = (item.unit_item && (item.unit_item.name || item.unit_item.short_name)) 
                        ? (item.unit_item.name || item.unit_item.short_name) 
                        : (itemData.unit || item.unit || 'Unit');
                    
                    // Update product name: Priority 1 - product_item.name (actual product name)
                    if (product && !isDummy(product)) {
                        productName = product;
                    }
                    
                    // Build first line for battery: Product Name + Plates + Amperes + Company
                    let firstLineParts = [];
                    if (itemType === 'battery') {
                        firstLineParts.push(productName);
                        if (plate) firstLineParts.push(plate + 'PL');
                        if (amperes) firstLineParts.push(amperes + 'AH');
                        if (company) firstLineParts.push(company);
                    }
                    
                    // Build short details array for search display (includes vehicle)
                    let searchDetails = [];
                    
                    // Common fields (short format) - exclude company for battery (it's on first line)
                    if (itemType !== 'battery' && company) searchDetails.push(company);
                    if (category) searchDetails.push(category);
                    
                    // Type-specific details (short format)
                    if (itemType === 'battery') {
                        if (group && !isDummy(group)) searchDetails.push(group);
                        if (volt) searchDetails.push(volt + 'V');
                        if (cca) searchDetails.push(cca + 'CCA');
                        if (technology && !isDummy(technology)) searchDetails.push(technology);
                        if (grade && !isDummy(grade)) searchDetails.push(grade);
                        if (batterySize && !isDummy(batterySize)) searchDetails.push(batterySize);
                    } else if (itemType === 'parts' || itemType === 'filters' || itemType === 'breakpad') {
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
                    
                    // Build first line HTML for battery items
                    let firstLineHtml = '';
                    let firstLineText = productName; // Default to product name
                    if (itemType === 'battery' && firstLineParts.length > 0) {
                        firstLineText = firstLineParts.join(' ');
                        const highlightedFirstLine = highlightText(firstLineText, query);
                        firstLineHtml = '<div class="fw-bold text-dark mb-1">' + highlightedFirstLine + '</div>';
                    } else {
                        // For non-battery items, show product name as before
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
                    const itemImage = item.image || itemData.image || '';
                    
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
                             data-warehouse-id="${itemData.warehouse_id || ''}"
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
            // Skip branches and warehouses
            if (itemData.type === 'branch' || itemData.type === 'warehouse') {
                return;
            }
            
            const item = itemData.item || itemData;
            const warehouseQuantity = parseFloat(itemData.warehouse_quantity || item.on_hand || 0);
            const availableQuantity = parseFloat(itemData.available_quantity || warehouseQuantity);
            
            totalWarehouseStock += warehouseQuantity;
            totalAvailableStock += availableQuantity;
        });
        
        $('#sales-warehouse-stock').text(totalWarehouseStock.toFixed(2) + ' Units');
        $('#sales-shop-stock').text(totalAvailableStock.toFixed(2) + ' Available');
    }
    
    // Select from search results (branch, warehouse, or item) - purchase style
    $(document).on('click', '.branch-search-result, .warehouse-search-result, .item-search-result, .sales-add-item-btn', function(e) {
        e.stopPropagation();
        const resultType = $(this).data('type');
        const resultId = $(this).data('id');
        
        if (resultType === 'branch') {
            // Select branch and reload search
            selectSalesBranch(resultId, $(this).find('.fw-bold').text(), '');
            salesSearchInput.val(''); // Clear search to show all items for this branch
            salesResultsContainer.html(`
                <div class="text-center text-muted py-5">
                    <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                    <p>Start typing to search items or use filters above</p>
                </div>
            `);
            // Trigger search again after branch selection
            setTimeout(function() {
                salesSearchInput.trigger('input');
            }, 500);
            return;
        } else if (resultType === 'warehouse') {
            // Filter by warehouse - reload search with warehouse filter
            const currentQuery = salesSearchInput.val();
            salesSearchInput.val(currentQuery + ' [Warehouse: ' + resultId + ']');
            salesResultsContainer.html(`
                <div class="text-center text-muted py-5">
                    <i class="fas fa-search fa-3x mb-3" style="opacity: 0.3;"></i>
                    <p>Start typing to search items or use filters above</p>
                </div>
            `);
            // Could add warehouse filter here if needed
            return;
        } else if (resultType === 'warranty_code') {
            const itemId = ($(this).data('item-id') || '').toString().trim();
            const itemName = ($(this).data('item-name') || '').toString();
            const saleId = ($(this).data('sale-id') || '').toString();
            const reference = ($(this).data('reference') || '').toString();
            const saleDate = ($(this).data('sale-date') || '').toString();
            const customerName = ($(this).data('customer-name') || '').toString();
            const matchedCode = ($(this).data('matched-code') || '').toString();
            const branchName = ($(this).data('branch-name') || '').toString();
            const warehouseName = ($(this).data('warehouse-name') || '').toString();
            const hasProof = (($(this).data('has-proof') || '').toString() === '1');

            if (itemId) {
                $('#item-search').val(itemName || matchedCode || '');
                $('#selected-item-id').val(itemId);
                $('#item-search-results').hide();
                if (typeof loadItemStockStatus === 'function') loadItemStockStatus(itemId);
            }

            Swal.fire({
                icon: 'info',
                title: 'Warranty match found',
                html: `
                    <div class="text-start">
                        <div class="mb-1"><span class="fw-bold">Matched code:</span> ${matchedCode || '—'}</div>
                        <div class="mb-1"><span class="fw-bold">Customer:</span> ${customerName || '—'}</div>
                        <div class="mb-1"><span class="fw-bold">Invoice:</span> ${reference || '—'}</div>
                        <div class="mb-1"><span class="fw-bold">Sale date:</span> ${saleDate || '—'}</div>
                        <div class="mb-1"><span class="fw-bold">Branch:</span> ${branchName || '—'}</div>
                        <div class="mb-1"><span class="fw-bold">Warehouse:</span> ${warehouseName || '—'}</div>
                        <div class="mb-1"><span class="fw-bold">Warranty proof:</span> ${hasProof ? 'Attached' : 'Not attached'}</div>
                    </div>
                `,
                showCancelButton: !!saleId,
                confirmButtonText: 'Use this item',
                cancelButtonText: 'Open invoice',
            }).then(function(r) {
                if (r.dismiss && r.dismiss.toString() === 'cancel' && saleId) {
                    const url = '{{ route("sales.edit", ":id") }}'.replace(':id', saleId);
                    window.open(url, '_blank');
                }
            });
            return;
        } else if (resultType === 'item') {
            // Select item - load full details
            const card = $(this).closest('.item-search-result');
            const itemId = resultId;
            const itemName = card.data('name');
            const itemFirstLine = card.data('first-line') || itemName; // Use first line text (black text from search result)
            const itemDisplay = card.data('display') || itemName; // Use display string (product name + details)
            const itemDetails = card.data('details') || ''; // All details
            const itemLine1Details = card.data('line1-details') || ''; // Line 1 details (company + volt for battery)
            const itemVehicle = card.data('vehicle') || ''; // Vehicle like "HONDA City"
            const itemCode = card.data('code') || ''; // Barcode/code like "6704861980"
            const itemCca = card.data('cca') || ''; // CCA like "380CCA"
            const itemRate = card.data('rate');
            const itemUnit = card.data('unit');
            const warehouseId = card.data('warehouse-id');
            
            // Close search modal
            salesSearchModal.modal('hide');
        
        // Set item data immediately
        $('#selected-item-id').val(itemId);
        $('#item-search').val(itemFirstLine); // Use first line text like purchase
        $('#selected-warehouse-id').val(warehouseId || '');
        
        // Set price immediately from search results
        if (itemRate && parseFloat(itemRate) > 0) {
            $('#sales-item-rate').val(parseFloat(itemRate).toFixed(2));
        }
        
        // Show item details below input (matching purchase format)
        let line1 = '';
        let line2 = '';
        let line3 = '';
        
        // Line 1: Volt only (remove company like "AGS")
        if (itemLine1Details) {
            // Remove company from line1Details (e.g., "AGS • 12V" -> "12V")
            const parts = itemLine1Details.split('•').map(p => p.trim());
            if (parts.length > 1) {
                line1 = parts.slice(1).join(' • ').trim();
            } else {
                line1 = itemLine1Details;
            }
        } else if (itemDetails) {
            // Fallback: use details without vehicle, CCA, and company
            let detailsOnly = itemDetails;
            if (itemVehicle && detailsOnly.includes(itemVehicle)) {
                detailsOnly = detailsOnly.replace(new RegExp('\\s*•\\s*' + itemVehicle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), '');
            }
            if (itemCca && detailsOnly.includes(itemCca)) {
                detailsOnly = detailsOnly.replace(new RegExp('\\s*•\\s*' + itemCca.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), '');
            }
            const parts = detailsOnly.split('•').map(p => p.trim());
            if (parts.length > 1) {
                detailsOnly = parts.slice(1).join(' • ').trim();
            }
            line1 = detailsOnly.trim();
        }
        
        // Line 2: CCA + Vehicle (e.g., "380CCA • HONDA City" with vehicle in orange)
        if (itemCca && itemVehicle) {
            line2 = itemCca + ' • <span class="text-warning fw-semibold">' + itemVehicle + '</span>';
        } else if (itemCca) {
            line2 = itemCca;
        } else if (itemVehicle) {
            line2 = '<span class="text-warning fw-semibold">' + itemVehicle + '</span>';
        }
        
        // Line 3: Barcode/Code (in orange with icon)
        if (itemCode) {
            line3 = '<i class="ti ti-barcode me-1"></i><span class="text-warning fw-semibold">' + itemCode + '</span>';
        }
        
        // Update selected item details display
        if (line1 || line2 || line3) {
            $('#selected-item-details-line1').html(line1 || '&nbsp;').parent().removeClass('d-none');
            $('#selected-item-details-line2').html(line2 || '&nbsp;');
            $('#selected-item-details-line3').html(line3 || '&nbsp;');
        } else {
            $('#selected-item-details-display').addClass('d-none');
        }
        
        // Load full item details (will update price if needed)
        loadItemDetails(itemId, itemRate);
        
        // Open detail modal
        const modal = $('#add-item-modal');
        modal.removeAttr('aria-hidden');
        modal.attr('aria-modal', 'true');
        modal.modal('show');
    });
    // ========== End YouTube-Style Search Modal ==========

    // Fix aria-hidden warning for all modals
    $('.modal').on('show.bs.modal', function() {
        $(this).removeAttr('aria-hidden');
        $(this).attr('aria-modal', 'true');
    });
    
    $('.modal').on('hidden.bs.modal', function() {
        $(this).attr('aria-hidden', 'true');
        $(this).removeAttr('aria-modal');
    });

    // Reset form when modal opens
    $('#add-item-modal').on('show.bs.modal', function() {
        $('#item-search').prop('readonly', false).prop('disabled', false).attr('readonly', false);
        // Reset form when modal opens
        $('#item-search').val('');
        $('#selected-item-id').val('');
        $('#selected-warehouse-id').val('');
        $('#sales-item-quantity').val('');
        $('#sales-item-unit').val('Can');
        $('#sales-item-rate').val('0');
        $('#sales-warranty-value').val('');
        $('#sales-warranty-unit').val('');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to view history</p>');
        $('#item-search-results').hide();
        $('#stock-status-section').hide();
        $('#stock-status-content').hide();
        
        // Update modal title based on entry type
        const entryType = currentEntryType || 'sale';
        const modalTitle = $('#add-item-modal .modal-title');

        // Scrap context: force search to show ONLY scrap items
        if (entryType === 'scrap_in' || entryType === 'scrap_sale') {
            $('#filter-item-type').val('scrap');
            $('#filter-item-type').prop('disabled', true);
        } else {
            $('#filter-item-type').prop('disabled', false);
        }
        
        // Determine color based on switch state for sale entries
        let saleColor = '#2563eb'; // Default blue
        if (typeof currentState !== 'undefined') {
            if (currentState === 0) {
                saleColor = '#2563eb'; // Blue for Sale
            } else if (currentState === 1) {
                saleColor = '#ffc107'; // Yellow for Estimate
            } else if (currentState === 2) {
                saleColor = '#198754'; // Green for Sale Order
            }
        }
        
        if (entryType === 'return') {
            modalTitle.html('<i class="ti ti-arrow-back-up me-2"></i>RETURN ITEM').css('color', '#dc2626');
        } else if (entryType === 'claim') {
            modalTitle.html('<i class="ti ti-shield-check me-2"></i>CLAIM ITEM').css('color', '#b45309');
        } else if (entryType === 'scrap_in') {
            modalTitle.html('<i class="ti ti-recycle me-2"></i>SCRAP IN').css('color', '#ea580c');
        } else if (entryType === 'scrap_sale') {
            modalTitle.html('<i class="ti ti-coins me-2"></i>SCRAP SALE').css('color', '#16a34a');
        } else {
            // Use switch state color for sale entries
            modalTitle.html('<i class="ti ti-shopping-cart me-2"></i>ITEM DETAILS').css('color', saleColor);
        }
    });
    
    /* Ensure modal is clickable when shown (fix overlay/stacking issues) */
    $('#add-item-modal').on('shown.bs.modal', function() {
        var $m = $(this);
        $m.css({ 'pointer-events': 'auto', 'z-index': 9999 });
        $m.find('.modal-dialog, .modal-content, .modal-body').css('pointer-events', 'auto');
        /* Put modal after backdrop so it is on top in DOM order */
        var $backdrop = $('.modal-backdrop').last();
        if ($backdrop.length && $backdrop.next().get(0) !== $m.get(0)) {
            $m.insertAfter($backdrop);
        }
        setTimeout(function() { $('#item-search').focus(); }, 100);
        /* Force interactive again after a tick (in case backdrop was added after) */
        setTimeout(function() {
            var $b = $('.modal-backdrop').last();
            if ($b.length) $('#add-item-modal').insertAfter($b);
            $('#add-item-modal').css({ 'pointer-events': 'auto', 'z-index': 9999 });
        }, 50);

        // Init warranty-proof UI for default qty=1 (Retail + Battery) without waiting for quantity change
        try {
            if (typeof isRetailCustomerSelected === 'function' && typeof isBatteryItemSelected === 'function') {
                if (isRetailCustomerSelected() && isBatteryItemSelected()) {
                    const qtyInt = (typeof getSelectedQtyIntForWarranty === 'function' ? (getSelectedQtyIntForWarranty() || 1) : 1);
                    if (typeof renderWarrantyProofSection === 'function') renderWarrantyProofSection(qtyInt);
                    if (typeof validateWarrantyProofSection === 'function') validateWarrantyProofSection();
                } else {
                    if (typeof renderWarrantyProofSection === 'function') renderWarrantyProofSection(null);
                }
            }
        } catch (e) {}
    });
    
    /* Move add-item-modal to body when opening so it is above everything (fixes blocked/clicks) */
    $('#add-item-modal').on('show.bs.modal', function() {
        $('#add-item-modal').appendTo('body');
    });
    
    // "ADD SALE ITEM" button click is handled earlier with customer-required guard.
    
    // ========== NEW SIMPLE ITEM SEARCH WITH DEBUGGING ==========
    let searchTimeout = null;
    
    // Search function with full error handling
    function searchItems() {
        try {
            console.log('=== SEARCH FUNCTION CALLED ===');
            const query = $('#item-search').val().trim();
            const resultsDiv = $('#item-search-results');
            
            console.log('Query:', query);
            console.log('Results div found:', resultsDiv.length > 0);
            
            if (resultsDiv.length === 0) {
                console.error('ERROR: #item-search-results div not found!');
                alert('Error: Search results container not found. Please refresh the page.');
                return;
            }
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Hide if empty
            if (query.length < 1) {
                console.log('Query empty, hiding results');
                resultsDiv.hide();
                return;
            }
            
            // Show loading
            console.log('Showing loading...');
            resultsDiv.html('<div class="p-3 text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Searching...</div>').show();
            
            // Debounce - wait 300ms after user stops typing
            searchTimeout = setTimeout(function() {
                try {
                    console.log('Making AJAX request...');
                    const searchUrl = "{{ route('sales.items.ajax.search') }}";
                    console.log('URL:', searchUrl);
                    
                    $.ajax({
                        url: searchUrl,
                        method: 'GET',
                        data: {
                            q: query,
                            limit: 20,
                            entry_type: (currentEntryType === 'scrap_in' || currentEntryType === 'scrap_sale') ? 'scrap' : null
                        },
                        success: function(results) {
                            try {
                                console.log('AJAX Success!');
                                console.log('Results:', results);
                                console.log('Results type:', typeof results);
                                console.log('Results length:', results ? results.length : 0);
                                
                                if (!results || results.length === 0) {
                                    console.log('No results found');
                                    resultsDiv.html('<div class="p-3 text-center text-muted">No items found</div>');
                                    resultsDiv.show();
                                    return;
                                }
                                
                                let html = '';
                                let itemCount = 0;
                                
                                results.forEach(function(result) {
                                    try {
                                        if (result.type === 'item') {
                                            itemCount++;
                                            const item = result.item;
                                            const itemName = (item.product_item && item.product_item.name) || item.short_disc || item.pro_dis || (item.partnumber_item ? item.partnumber_item.name : '') || 'Item #' + item.id;
                                            const price = parseFloat(result.sale_price || item.sale_price || item.price_per_unit || 0);
                                            const stock = parseFloat(result.warehouse_quantity || item.on_hand || 0);
                                            const barcode = item.bar_code || '';
                                            
                                            html += '<div class="p-3 border-bottom item-search-result" data-type="item" data-id="' + item.id + '" data-name="' + itemName.replace(/"/g, '&quot;') + '" data-rate="' + price + '" style="cursor: pointer;">';
                                            html += '<div class="d-flex justify-content-between">';
                                            html += '<div><div class="fw-bold">' + itemName + '</div>';
                                            if (barcode) html += '<div class="small text-muted">Code: ' + barcode + '</div>';
                                            html += '</div>';
                                            html += '<div class="text-end"><div class="fw-bold text-primary">Rs ' + price.toFixed(2) + '</div><div class="small text-muted">Stock: ' + stock + '</div></div>';
                                            html += '</div></div>';
                                        }
                                    } catch (itemError) {
                                        console.error('Error processing item:', itemError);
                                    }
                                });
                                
                                console.log('Items processed:', itemCount);
                                
                                if (html === '') {
                                    resultsDiv.html('<div class="p-3 text-center text-muted">No items found</div>');
                                } else {
                                    resultsDiv.html(html);
                                }
                                resultsDiv.show();
                                console.log('Results displayed successfully');
                            } catch (successError) {
                                console.error('Error in success handler:', successError);
                                resultsDiv.html('<div class="p-3 text-center text-danger">Error processing results: ' + successError.message + '</div>');
                                resultsDiv.show();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('=== AJAX ERROR ===');
                            console.error('Status:', status);
                            console.error('Error:', error);
                            console.error('Status Code:', xhr.status);
                            console.error('Response:', xhr.responseText);
                            console.error('Response JSON:', xhr.responseJSON);
                            
                            let errorMsg = 'Error loading items';
                            if (xhr.status === 404) {
                                errorMsg = 'Search route not found (404). Please check route configuration.';
                            } else if (xhr.status === 500) {
                                errorMsg = 'Server error (500). Please check server logs.';
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            } else if (error) {
                                errorMsg = error;
                            }
                            
                            resultsDiv.html('<div class="p-3 text-center text-danger"><strong>Error:</strong> ' + errorMsg + '<br><small>Status: ' + xhr.status + '</small></div>');
                            resultsDiv.show();
                            
                            // Show alert for debugging
                            alert('Search Error:\n' + errorMsg + '\n\nStatus: ' + xhr.status + '\n\nCheck console for details.');
                        }
                    });
                } catch (ajaxError) {
                    console.error('Error setting up AJAX:', ajaxError);
                    resultsDiv.html('<div class="p-3 text-center text-danger">Error: ' + ajaxError.message + '</div>');
                    resultsDiv.show();
                    alert('Error: ' + ajaxError.message);
                }
            }, 300);
        } catch (error) {
            console.error('=== FATAL ERROR IN searchItems ===');
            console.error('Error:', error);
            alert('Fatal Error in search function: ' + error.message + '\n\nCheck console for details.');
        }
    }
    
    // Trigger search on input with error handling
    $(document).ready(function() {
        console.log('Setting up search event handlers...');
        
        // Check if element exists
        if ($('#item-search').length === 0) {
            console.error('ERROR: #item-search input not found!');
            alert('Error: Search input field not found. Please refresh the page.');
            return;
        }
        
        console.log('Search input found, attaching events...');
        
        // Multiple event handlers for better compatibility
        $(document).on('input', '#item-search', function() {
            console.log('Input event triggered');
            try {
                searchItems();
            } catch (e) {
                console.error('Error in input handler:', e);
                alert('Error: ' + e.message);
            }
        });
        
        $(document).on('keyup', '#item-search', function(e) {
            if (e.keyCode !== 13 && e.keyCode !== 27 && e.keyCode !== 38 && e.keyCode !== 40) {
                console.log('Keyup event triggered, key:', e.keyCode);
                try {
                    searchItems();
                } catch (e) {
                    console.error('Error in keyup handler:', e);
                }
            }
        });
        
        // Test function call
        console.log('Search handlers attached successfully');
        
        // Manual test function (for debugging)
        window.testSearch = function() {
            console.log('=== MANUAL SEARCH TEST ===');
            const testQuery = $('#item-search').val() || 'test';
            $('#item-search').val(testQuery);
            console.log('Setting query to:', testQuery);
            searchItems();
        };
        
        console.log('Test function available: window.testSearch()');
    });
    
    // Hide results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#item-search-wrapper').length && !$(e.target).closest('#item-search-results').length) {
            $('#item-search-results').hide();
        }
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
            
            // Load full item details to get image and stock
            $.ajax({
                url: '{{ route("sales.items.details", ":id") }}'.replace(':id', itemId),
                method: 'GET',
                success: function(response) {
                    window.currentSelectedSaleItemType = response && response.type ? String(response.type).toLowerCase() : '';
                    // If Retail + Battery and default qty=1, show proof UI immediately
                    try {
                        if (typeof isRetailCustomerSelected === 'function' && typeof isBatteryItemSelected === 'function' && isRetailCustomerSelected() && isBatteryItemSelected()) {
                            const qtyInt = (typeof getSelectedQtyIntForWarranty === 'function' ? (getSelectedQtyIntForWarranty() || 1) : 1);
                            if (typeof renderWarrantyProofSection === 'function') renderWarrantyProofSection(qtyInt);
                            if (typeof validateWarrantyProofSection === 'function') validateWarrantyProofSection();
                        } else {
                            if (typeof renderWarrantyProofSection === 'function') renderWarrantyProofSection(null);
                        }
                    } catch(e) {}
                    // Show item image if available
                    if (response.image) {
                        $('#item-search-image').attr('src', response.image);
                        $('#item-search-image-preview').removeClass('d-none');
                    } else {
                        $('#item-search-image-preview').addClass('d-none');
                    }
                    
                    // Show stock below image
                    if (response.stock !== undefined) {
                        const stockValue = parseFloat(response.stock) || 0;
                        const stockColor = stockValue > 10 ? 'text-success' : (stockValue > 0 ? 'text-warning' : 'text-danger');
                        const stockText = stockValue % 1 === 0 ? Math.round(stockValue) : stockValue.toFixed(1);
                        const unit = response.unit || 'Unit';
                        const literPerCan = (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can))) ? parseFloat(response.liter_per_can) : null;
                        let stockHtml = `<span class="${stockColor}">${stockText} ${unit}</span>`;
                        if (literPerCan != null && literPerCan > 0) {
                            const lText = Number.isInteger(literPerCan) ? literPerCan : literPerCan.toFixed(1);
                            stockHtml += `<div class="small text-muted mt-0">${lText} L per can</div>`;
                        }
                        $('#item-search-stock').html(stockHtml);
                    } else {
                        $('#item-search-stock').html('');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading item details:', xhr);
                }
            });
            
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
                        // Warehouse item - display + quantity dropdown (1-100)
                        const isSelected = $('#selected-warehouse-id').val() == stock.id;
                        const unitLabel = (stock.unit || 'Unit').trim();
                        const qty = parseFloat(stock.quantity) || 0;
                        const qtyText = (Number.isInteger(qty) ? qty : qty.toFixed(2)) + ' ' + unitLabel;
                        const mainQtyDisp = Number.isInteger(qty) ? qty : qty.toFixed(2);
                        const whQtyBlock = '<span class="d-block fw-bold ' + (isSelected ? 'text-white' : '') + '">' + mainQtyDisp + ' ' + unitLabel + '</span>';
                        let canOptions = '<option value="" selected>-</option>';
                        const maxCans = Math.max(0, Math.floor(qty));
                        const maxUiCans = Math.min(100, maxCans);
                        for (let i = 1; i <= maxUiCans; i++) { canOptions += '<option value="' + i + '">' + i + '</option>'; }
                        html += `
                            <div class="p-2 mb-1 stock-warehouse-item ${isSelected ? 'bg-primary text-white' : ''}" 
                                 data-warehouse-id="${stock.id}"
                                 data-branch-id="${(stock.branch_id || '')}"
                                 data-display="${(stock.display || '').replace(/"/g, '&quot;')}"
                                 data-quantity="${qty}"
                                 data-unit="${(unitLabel || 'Unit').replace(/"/g, '&quot;')}"
                                 data-qty-text="${(qtyText || '').replace(/"/g, '&quot;')}"
                                 data-cartons="${stock.cartons || 0}"
                                 data-loose-liters="${stock.loose || 0}"
                                 style="cursor: pointer; transition: all 0.2s; ${isSelected ? '' : 'background-color: #f0f0f0;'}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">${isSelected ? '✓' : ''}</span>
                                        <span class="${isSelected ? 'text-white' : ''}">${stock.display || ''}</span>
                                    </div>
                                    <div class="d-flex align-items-end gap-3 flex-wrap">
                                        <div class="text-end stock-warehouse-qty-labels">${whQtyBlock}</div>
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="small mb-1" style="font-size: 0.7rem; font-weight: 600; ${isSelected ? 'color: rgba(255,255,255,0.95);' : 'color: #495057;'}">${unitLabel}</span>
                                            <select class="form-control form-control-sm stock-warehouse-qty-input" style="width: 70px; display: inline-block;" data-warehouse-id="${stock.id}" onclick="event.stopPropagation();" data-unit="${(unitLabel || 'Unit').replace(/"/g, '&quot;')}">${canOptions}</select>
                                        </div>
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
    $(document).on('click', '.stock-warehouse-item', function(e) {
        if ($(e.target).closest('.stock-warehouse-qty-input').length) return;
        // Remove previous selection
        $('.stock-warehouse-item').removeClass('bg-primary text-white');
        $('.stock-warehouse-item').css('background-color', '');
        $('.stock-warehouse-item').find('span.me-2').text('');
        
        // Select this warehouse
        $(this).addClass('bg-primary text-white').css('background-color', '');
        $(this).find('span.me-2').html('✓');
        
        const warehouseId = $(this).data('warehouse-id');
        $('#selected-warehouse-id').val(warehouseId);
    });
    
    // When user selects quantity from warehouse row dropdown, sync to main quantity
    $(document).on('change', '.stock-warehouse-qty-input', function() {
        const val = $(this).val();
        const qty = parseFloat(val) || 0;
        if (qty >= 1) {
            $('#sales-item-quantity').val(qty);
            $('#sales-item-quantity-input').val(qty).hide();
        }
    });
    
    $(document).on('focus', '.stock-warehouse-qty-input', function() {
        const $row = $(this).closest('.stock-warehouse-item');
        if ($row.length && !$row.hasClass('bg-primary')) {
            $row.trigger('click');
        }
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
    
    // Filter state (renamed to avoid conflict with main activeFilters)
    let oldActiveFilters = {};
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
            if (query.length >= 2 || Object.keys(oldActiveFilters).length > 0) {
                performSalesModalSearch();
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
        performSalesModalSearch();
    });
    
    // Filter chip clicks
    $('.filter-chip').on('click', function() {
        const filter = $(this).data('filter');
        const value = $(this).data('value');
        
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            delete oldActiveFilters[filter];
        } else {
            $('.filter-chip[data-filter="' + filter + '"]').removeClass('active');
            $(this).addClass('active');
            oldActiveFilters[filter] = value;
        }
        
        updateClearAllButton();
        performSalesModalSearch();
    });
    
    // Advanced filter changes
    $('#filter-category, #filter-manufacturer, #filter-part-number, #filter-technology, #filter-grade, #filter-volt, #filter-cca, #filter-supplier, #filter-rack, #filter-min-price, #filter-max-price').on('change input', function() {
        const filterId = $(this).attr('id').replace('filter-', '').replace('-', '_');
        const value = $(this).val();
        
        if (value) {
            oldActiveFilters[filterId] = value;
        } else {
            delete oldActiveFilters[filterId];
        }
        
        updateClearAllButton();
        performSalesModalSearch();
    });
    
    // Toggle advanced filters
    advancedFiltersToggle.on('click', function() {
        $('#advancedFiltersPanel').collapse('toggle');
    });
    
    // Clear all filters
    clearAllFiltersBtn.on('click', function() {
        oldActiveFilters = {};
        $('.filter-chip').removeClass('active');
        $('#filter-category, #filter-manufacturer, #filter-part-number, #filter-technology, #filter-grade, #filter-volt, #filter-cca, #filter-supplier, #filter-rack').val('');
        $('#filter-min-price, #filter-max-price').val('');
        updateClearAllButton();
        performSalesModalSearch();
    });
    
    // Update clear all button visibility
    function updateClearAllButton() {
        const hasFilters = Object.keys(oldActiveFilters).length > 0 || searchInput.val().trim().length > 0;
        clearAllFiltersBtn.toggleClass('d-none', !hasFilters);
    }
    
    // Perform search for sales-item-search-modal (renamed to avoid conflict)
    function performSalesModalSearch() {
        const query = searchInput.val().trim();
        
        // Build search params
        const params = {
            q: query,
            limit: 50
        };
        
        // Add active filters
        Object.keys(oldActiveFilters).forEach(key => {
            params[key] = oldActiveFilters[key];
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
                window.currentSelectedSaleItemType = response && response.type ? String(response.type).toLowerCase() : '';
                // If Retail + Battery and default qty=1, show proof UI immediately
                try {
                    if (typeof isRetailCustomerSelected === 'function' && typeof isBatteryItemSelected === 'function' && isRetailCustomerSelected() && isBatteryItemSelected()) {
                        const qtyInt = (typeof getSelectedQtyIntForWarranty === 'function' ? (getSelectedQtyIntForWarranty() || 1) : 1);
                        if (typeof renderWarrantyProofSection === 'function') renderWarrantyProofSection(qtyInt);
                        if (typeof validateWarrantyProofSection === 'function') validateWarrantyProofSection();
                    } else {
                        if (typeof renderWarrantyProofSection === 'function') renderWarrantyProofSection(null);
                    }
                } catch(e) {}
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
    
    // Entry type tracking
    let currentEntryType = 'sale';

    // ================== Retail Warranty-card Proofs (single scan + multi images) ==================
    window.warrantyProofDraft = window.warrantyProofDraft || []; // attachments
    window.warrantyRequiredQty = 0;
    window.currentWarrantyProofsForNextAdd = null;
    window.currentSelectedSaleItemType = window.currentSelectedSaleItemType || '';
    window.pendingWarrantyScannedCode = window.pendingWarrantyScannedCode || null;
    function isRetailCustomerSelected() {
        const t = (window.selectedCustomerType || 'retail').toString().toLowerCase();
        return t === 'retail';
    }
    function isBatteryItemSelected() {
        return (window.currentSelectedSaleItemType || '').toString().toLowerCase() === 'battery';
    }
    function normalizeWarrantyCodeJs(code) {
        return (code || '').toString().trim().toLowerCase();
    }
    function toUpperClean(s) {
        return (s || '').toString().toUpperCase().replace(/[^A-Z0-9\-_]/g, '');
    }
    function isRejectedWord(tok) {
        var t = (tok || '').toString().toUpperCase().trim();
        if (!t) return true;
        var stop = {
            WARRANTY:1, GUARANTEE:1, GUARANTY:1, CARD:1, BATTERY:1, MODEL:1, SERIAL:1, NUMBER:1,
            DATE:1, NAME:1, PHONE:1, ADDRESS:1, MAINTENANCE:1, FREE:1, MAINTENANCEFREE:1,
            READYTOUSE:1, TECHNOLOGY:1, KOREAN:1, MADE:1, IN:1, COMPANY:1, LIMITED:1,
            DAEWOO:1, DAEWOOO:1, EXIDE:1, ATLAS:1, AMARON:1, GS:1, YUASA:1
        };
        if (stop[t]) return true;
        if (/^[A-Z]{3,}$/.test(t)) return true;
        var wordish = t
            .replace(/0/g, 'O')
            .replace(/1/g, 'I')
            .replace(/5/g, 'S')
            .replace(/2/g, 'Z')
            .replace(/6/g, 'G')
            .replace(/8/g, 'B');
        if (stop[wordish]) return true;
        var digits = (t.match(/\d/g) || []).length;
        var vowels = (wordish.match(/[AEIOU]/g) || []).length;
        if (digits <= 1 && wordish.length >= 5 && vowels >= 2) return true;
        return false;
    }
    function isCodeCandidate(token) {
        var t = toUpperClean(token);
        if (!t) return false;
        if (isRejectedWord(t)) return false;
        if (/^\d{2,12}$/.test(t)) return true;
        if (/^[A-Z0-9]{4,15}$/.test(t) && !/^[A-Z]+$/.test(t)) {
            var dcnt = (t.match(/\d/g) || []).length;
            if (t.length <= 5 && dcnt <= 1) return false;
            return true;
        }
        if (/^[A-Z0-9\-_]{4,20}$/.test(t) && (t.indexOf('-') !== -1 || t.indexOf('_') !== -1)) return true;
        return false;
    }
    function fuzzyFixToken(token) {
        var t = (token || '').toString().toUpperCase();
        var chars = t.split('');
        var map = {
            'O': ['O', '0'],
            '0': ['0', 'O'],
            'I': ['I', '1'],
            'L': ['L', '1'],
            '1': ['1', 'I', 'L'],
            'S': ['S', '5'],
            '5': ['5', 'S'],
            'B': ['B', '8'],
            '8': ['8', 'B'],
            'G': ['G', '6'],
            '6': ['6', 'G'],
            'Z': ['Z', '2'],
            '2': ['2', 'Z'],
        };
        var variants = [''];
        for (var i = 0; i < chars.length; i++) {
            var c = chars[i];
            var opts = map[c] || [c];
            var next = [];
            for (var v = 0; v < variants.length; v++) {
                for (var o = 0; o < opts.length; o++) {
                    next.push(variants[v] + opts[o]);
                    if (next.length > 120) break;
                }
                if (next.length > 120) break;
            }
            variants = next;
            if (variants.length > 120) break;
        }
        var best = null;
        var bestScore = -1;
        variants.forEach(function(x) {
            var y = toUpperClean(x);
            if (!y) return;
            var s = 0;
            if (/^[A-Z]{3,6}[-_]\d{2,4}[A-Z]{0,2}$/.test(y)) s += 50;
            if (/[A-Z]/.test(y) && /\d/.test(y)) s += 25;
            if (y.indexOf('-') !== -1 || y.indexOf('_') !== -1) s += 10;
            if (/^\d{4,10}$/.test(y)) s += 18;
            if (/^\d{3}$/.test(y)) s += 6;
            s += Math.min(10, y.length);
            if (s > bestScore) { bestScore = s; best = y; }
        });
        return best || toUpperClean(token);
    }
    function uniq(arr) {
        const out = [];
        const seen = {};
        (arr || []).forEach(function(x) {
            const k = normalizeWarrantyCodeJs(x);
            if (!k) return;
            if (seen[k]) return;
            seen[k] = true;
            out.push(x);
        });
        return out;
    }
    async function ensureTesseractLoaded() {
        if (window.Tesseract && window.Tesseract.recognize) return true;
        return await new Promise(function(resolve) {
            try {
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
                s.async = true;
                s.onload = function() { resolve(!!(window.Tesseract && window.Tesseract.recognize)); };
                s.onerror = function() { resolve(false); };
                document.head.appendChild(s);
            } catch (e) { resolve(false); }
        });
    }
    function extractLikelyCodesFromText(text) {
        var t = (text || '').toString().toUpperCase();
        t = t.replace(/[^A-Z0-9\-_\s]/g, ' ');
        var tokens = t.split(/\s+/).filter(Boolean);
        var cleaned = tokens.map(fuzzyFixToken);
        var codes = uniq(cleaned).filter(isCodeCandidate);
        codes.sort(function(a, b) {
            function score(x) {
                x = String(x || '');
                var s = 0;
                if (/^\d{2,12}$/.test(x)) s += 18;
                if (/[A-Z]/.test(x) && /\d/.test(x)) s += 22;
                if (x.indexOf('-') !== -1 || x.indexOf('_') !== -1) s += 16;
                if (/^\d{2,3}$/.test(x)) s += 10;
                s += Math.min(10, x.length);
                return s;
            }
            return score(b) - score(a);
        });
        return codes.slice(0, 30);
    }
    function newWarrantyAttachmentId() {
        return 'wp_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 8);
    }

    function dataUrlToImage(dataUrl) {
        return new Promise(function(resolve, reject) {
            var img = new Image();
            img.onload = function() { resolve(img); };
            img.onerror = reject;
            img.src = dataUrl;
        });
    }
    function clamp255(x) { return x < 0 ? 0 : (x > 255 ? 255 : x); }
    async function ensureOpenCvLoaded() {
        if (window.cv && window.cv.Mat) return true;
        if (window.__loadingOpenCv) return await window.__loadingOpenCv;
        window.__loadingOpenCv = new Promise(function(resolve) {
            try {
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/opencv.js-webassembly@4.2.0/opencv.js';
                s.async = true;
                s.onload = function() {
                    var tries = 0;
                    var tick = function() {
                        tries++;
                        if (window.cv && typeof window.cv.onRuntimeInitialized === 'function') {
                            var old = window.cv.onRuntimeInitialized;
                            window.cv.onRuntimeInitialized = function() {
                                try { old && old(); } catch (_) {}
                                resolve(true);
                            };
                            return;
                        }
                        if (window.cv && window.cv.Mat) return resolve(true);
                        if (tries > 40) return resolve(false);
                        setTimeout(tick, 150);
                    };
                    tick();
                };
                s.onerror = function() { resolve(false); };
                document.head.appendChild(s);
            } catch (e) { resolve(false); }
        });
        return await window.__loadingOpenCv;
    }
    function orderQuadPoints(pts) {
        var sum = pts.map(function(p){ return p.x + p.y; });
        var diff = pts.map(function(p){ return p.x - p.y; });
        var tl = pts[sum.indexOf(Math.min.apply(null, sum))];
        var br = pts[sum.indexOf(Math.max.apply(null, sum))];
        var tr = pts[diff.indexOf(Math.max.apply(null, diff))];
        var bl = pts[diff.indexOf(Math.min.apply(null, diff))];
        return [tl, tr, br, bl];
    }
    function tryWarpDocumentFromCanvas(srcCanvas) {
        try {
            if (!window.cv || !window.cv.Mat) return null;
            var cv = window.cv;
            var src = cv.imread(srcCanvas);
            var gray = new cv.Mat();
            cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY, 0);
            var blur = new cv.Mat();
            cv.GaussianBlur(gray, blur, new cv.Size(5, 5), 0, 0, cv.BORDER_DEFAULT);
            var edges = new cv.Mat();
            cv.Canny(blur, edges, 50, 150);
            var contours = new cv.MatVector();
            var hierarchy = new cv.Mat();
            cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);
            var best = null;
            var bestArea = 0;
            for (var i = 0; i < contours.size(); i++) {
                var cnt = contours.get(i);
                var peri = cv.arcLength(cnt, true);
                var approx = new cv.Mat();
                cv.approxPolyDP(cnt, approx, 0.02 * peri, true);
                if (approx.rows === 4) {
                    var area = cv.contourArea(approx);
                    if (area > bestArea) {
                        if (best) best.delete();
                        best = approx;
                        bestArea = area;
                    } else {
                        approx.delete();
                    }
                } else {
                    approx.delete();
                }
                cnt.delete();
            }
            if (!best || bestArea < 12000) {
                src.delete(); gray.delete(); blur.delete(); edges.delete(); contours.delete(); hierarchy.delete();
                if (best) best.delete();
                return null;
            }
            var pts = [];
            for (var r = 0; r < 4; r++) pts.push({ x: best.intPtr(r, 0)[0], y: best.intPtr(r, 0)[1] });
            var o = orderQuadPoints(pts);
            var tl = o[0], tr = o[1], br = o[2], bl = o[3];
            var maxW = Math.max(Math.hypot(br.x - bl.x, br.y - bl.y), Math.hypot(tr.x - tl.x, tr.y - tl.y));
            var maxH = Math.max(Math.hypot(tr.x - br.x, tr.y - br.y), Math.hypot(tl.x - bl.x, tl.y - bl.y));
            maxW = Math.max(320, Math.round(maxW));
            maxH = Math.max(200, Math.round(maxH));
            var srcTri = cv.matFromArray(4, 1, cv.CV_32FC2, [tl.x, tl.y, tr.x, tr.y, br.x, br.y, bl.x, bl.y]);
            var dstTri = cv.matFromArray(4, 1, cv.CV_32FC2, [0, 0, maxW - 1, 0, maxW - 1, maxH - 1, 0, maxH - 1]);
            var M = cv.getPerspectiveTransform(srcTri, dstTri);
            var dst = new cv.Mat();
            cv.warpPerspective(src, dst, M, new cv.Size(maxW, maxH), cv.INTER_LINEAR, cv.BORDER_REPLICATE, new cv.Scalar());
            var dstGray = new cv.Mat();
            cv.cvtColor(dst, dstGray, cv.COLOR_RGBA2GRAY, 0);
            var dstThr = new cv.Mat();
            cv.adaptiveThreshold(dstGray, dstThr, 255, cv.ADAPTIVE_THRESH_GAUSSIAN_C, cv.THRESH_BINARY, 25, 10);
            var kernel = cv.matFromArray(3, 3, cv.CV_32F, [0, -1, 0, -1, 5, -1, 0, -1, 0]);
            var sharp = new cv.Mat();
            cv.filter2D(dstThr, sharp, cv.CV_8U, kernel);
            var outCanvas = document.createElement('canvas');
            outCanvas.width = maxW;
            outCanvas.height = maxH;
            cv.imshow(outCanvas, sharp);
            src.delete(); gray.delete(); blur.delete(); edges.delete(); contours.delete(); hierarchy.delete();
            best.delete(); srcTri.delete(); dstTri.delete(); M.delete();
            dst.delete(); dstGray.delete(); dstThr.delete(); kernel.delete(); sharp.delete();
            return outCanvas;
        } catch (e) {
            return null;
        }
    }
    function preprocessToCanvas(img, rotateDeg) {
        var w = img.naturalWidth || img.width;
        var h = img.naturalHeight || img.height;
        var cw = (rotateDeg % 180 === 0) ? w : h;
        var ch = (rotateDeg % 180 === 0) ? h : w;
        var canvas = document.createElement('canvas');
        canvas.width = cw; canvas.height = ch;
        var ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.save();
        ctx.translate(cw / 2, ch / 2);
        ctx.rotate((rotateDeg * Math.PI) / 180);
        ctx.drawImage(img, -w / 2, -h / 2);
        ctx.restore();
        try {
            if (window.cv && window.cv.Mat) {
                var warped = tryWarpDocumentFromCanvas(canvas);
                if (warped) {
                    canvas = warped; cw = canvas.width; ch = canvas.height;
                    ctx = canvas.getContext('2d', { willReadFrequently: true });
                }
            } else {
                ensureOpenCvLoaded();
            }
        } catch (eWarp) {}
        var id = ctx.getImageData(0, 0, cw, ch);
        var d = id.data;
        var factor = 1.45;
        for (var i = 0; i < d.length; i += 4) {
            var r = d[i], g = d[i + 1], b = d[i + 2];
            var y = 0.299 * r + 0.587 * g + 0.114 * b;
            y = (y - 128) * factor + 128;
            y = clamp255(y);
            d[i] = d[i + 1] = d[i + 2] = y;
        }
        ctx.putImageData(id, 0, 0);
        // bbox crop
        id = ctx.getImageData(0, 0, cw, ch);
        d = id.data;
        var minX = cw, minY = ch, maxX = 0, maxY = 0;
        var has = false;
        var sum = 0, cnt = 0;
        for (var s = 0; s < d.length; s += 64 * 4) { sum += d[s]; cnt++; }
        var thr = cnt ? (sum / cnt) : 180;
        thr = Math.min(210, Math.max(115, thr));
        for (var yy = 0; yy < ch; yy++) {
            for (var xx = 0; xx < cw; xx++) {
                var p = (yy * cw + xx) * 4;
                if (d[p] < thr) {
                    has = true;
                    if (xx < minX) minX = xx;
                    if (yy < minY) minY = yy;
                    if (xx > maxX) maxX = xx;
                    if (yy > maxY) maxY = yy;
                }
            }
        }
        if (has) {
            var padX = Math.floor((maxX - minX + 1) * 0.06);
            var padY = Math.floor((maxY - minY + 1) * 0.06);
            minX = Math.max(0, minX - padX);
            minY = Math.max(0, minY - padY);
            maxX = Math.min(cw - 1, maxX + padX);
            maxY = Math.min(ch - 1, maxY + padY);
            var bw = Math.max(1, maxX - minX + 1);
            var bh = Math.max(1, maxY - minY + 1);
            var crop = document.createElement('canvas');
            crop.width = bw; crop.height = bh;
            crop.getContext('2d').drawImage(canvas, minX, minY, bw, bh, 0, 0, bw, bh);
            canvas = crop; cw = bw; ch = bh;
            ctx = canvas.getContext('2d', { willReadFrequently: true });
        }
        // binarize
        id = ctx.getImageData(0, 0, cw, ch);
        d = id.data;
        sum = 0; cnt = 0;
        for (s = 0; s < d.length; s += 64 * 4) { sum += d[s]; cnt++; }
        thr = cnt ? (sum / cnt) : 180;
        thr = Math.min(205, Math.max(110, thr));
        for (i = 0; i < d.length; i += 4) {
            var vv = d[i];
            var out = vv < thr ? 0 : 255;
            d[i] = d[i + 1] = d[i + 2] = out;
        }
        ctx.putImageData(id, 0, 0);
        var maxSide = Math.max(canvas.width, canvas.height);
        if (maxSide < 900) {
            var scale = 1200 / maxSide;
            var up = document.createElement('canvas');
            up.width = Math.round(canvas.width * scale);
            up.height = Math.round(canvas.height * scale);
            var uctx = up.getContext('2d');
            uctx.imageSmoothingEnabled = true;
            uctx.drawImage(canvas, 0, 0, up.width, up.height);
            canvas = up;
        }
        return canvas;
    }
    async function ocrBestOfRotations(dataUrl) {
        var img = await dataUrlToImage(dataUrl);
        var rotations = [0, 90, 180, 270];
        var best = { score: -1, codes: [], text: '' };
        for (var i = 0; i < rotations.length; i++) {
            var canvas = preprocessToCanvas(img, rotations[i]);
            var pre = canvas.toDataURL('image/png');
            var codesAll = [];
            var res = await window.Tesseract.recognize(pre, 'eng', { tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_', tessedit_pageseg_mode: '6' });
            // token extraction from words with confidence if present
            try {
                if (res && res.data && Array.isArray(res.data.words)) {
                    res.data.words.forEach(function(w){
                        if (!w) return;
                        var txt = (w.text || '').toString();
                        var conf = (w.confidence == null ? 0 : Number(w.confidence));
                        if (!isFinite(conf)) conf = 0;
                        if (conf >= 40) codesAll.push(txt);
                    });
                }
            } catch (eW) {}
            var text = (res && res.data && res.data.text) ? res.data.text : '';
            codesAll = codesAll.concat(text.split(/\s+/));

            // Region OCR (3 horizontal bands) to catch model/serial/handwritten
            try {
                var w = canvas.width, h = canvas.height;
                var bands = [
                    { y: 0, h: Math.floor(h * 0.35) },
                    { y: Math.floor(h * 0.30), h: Math.floor(h * 0.40) },
                    { y: Math.floor(h * 0.70), h: h - Math.floor(h * 0.70) },
                ];
                for (var bi = 0; bi < bands.length; bi++) {
                    var b = bands[bi];
                    if (b.h < 40) continue;
                    var c2 = document.createElement('canvas');
                    c2.width = w; c2.height = b.h;
                    c2.getContext('2d').drawImage(canvas, 0, b.y, w, b.h, 0, 0, w, b.h);
                    var preBand = c2.toDataURL('image/png');
                    var resB = await window.Tesseract.recognize(preBand, 'eng', { tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_', tessedit_pageseg_mode: '7' });
                    var textB = (resB && resB.data && resB.data.text) ? resB.data.text : '';
                    codesAll = codesAll.concat(textB.split(/\s+/));
                }
            } catch (eBand) {}

            var codes = extractLikelyCodesFromText(codesAll.join(' '));
            var score = 0;
            codes.forEach(function(c){
                if (/^[A-Z]{3,6}[-_]\d{2,4}[A-Z]{0,2}$/.test(c)) score += 60;
                else if (/^[A-Z0-9]{5,10}$/.test(c) && /[A-Z]/.test(c) && /\d/.test(c)) score += 30;
                else if (/^\d{4,10}$/.test(c)) score += 18;
                else if (/^\d{3}$/.test(c)) score += 8;
            });
            score += Math.min(30, codes.length * 5);
            if (score > best.score) best = { score: score, codes: codes, text: text };
        }
        return best;
    }

    async function runOcrForAttachmentId(attachmentId) {
        try {
            var ok = await ensureTesseractLoaded();
            if (!ok) return;
            var attIndex = (window.warrantyProofDraft || []).findIndex(function(a) { return a && a._id === attachmentId; });
            if (attIndex === -1) return;
            var att = window.warrantyProofDraft[attIndex];
            if (!att || !att.image_data) return;
            att.extracting = true;
            renderWarrantyProofSection(window.warrantyRequiredQty || 0);
            var best = await ocrBestOfRotations(att.image_data);
            var codes = (best && best.codes) ? best.codes : [];
            att.extracted_codes = uniq((att.extracted_codes || []).concat(codes || []));
            att.extracting = false;
            renderWarrantyProofSection(window.warrantyRequiredQty || 0);
        } catch (e) {
            try {
                var i2 = (window.warrantyProofDraft || []).findIndex(function(a) { return a && a._id === attachmentId; });
                if (i2 !== -1 && window.warrantyProofDraft[i2]) window.warrantyProofDraft[i2].extracting = false;
            } catch (_) {}
            renderWarrantyProofSection(window.warrantyRequiredQty || 0);
        }
    }
    function getSelectedQtyIntForWarranty() {
        // Default qty behavior: if dropdown is empty, fallback to quantity input value (even if hidden; default is usually 1)
        let q = parseFloat($('#sales-item-quantity').val());
        if (!isFinite(q) || q <= 0) {
            const qInputVal = parseFloat($('#sales-item-quantity-input').val());
            if (isFinite(qInputVal) && qInputVal > 0) q = qInputVal;
            else q = 0;
        }
        if ($('#sales-item-quantity-input').is(':visible') && $('#sales-item-quantity-input').val()) {
            q = parseFloat($('#sales-item-quantity-input').val()) || q;
        }
        const qInt = Math.round(q);
        if (qInt <= 0) return null;
        if (Math.abs(q - qInt) > 0.00001) return null;
        return qInt;
    }
    function renderWarrantyProofSection(qtyInt) {
        const $section = $('#warranty-proof-section');
        const $units = $('#warranty-proof-units');
        const $badge = $('#warranty-proof-badge');
        const $err = $('#warranty-proof-error');
        const $confirm = $('#confirm-entry');
        if (!isRetailCustomerSelected() || !isBatteryItemSelected() || !qtyInt) {
            window.warrantyProofDraft = [];
            window.warrantyRequiredQty = 0;
            window.pendingWarrantyScannedCode = null;
            $section.addClass('d-none');
            $units.empty();
            $badge.text('0 / 0');
            $err.addClass('d-none').text('');
            $confirm.prop('disabled', false);
            return;
        }
        window.warrantyRequiredQty = qtyInt;
        if (window.warrantyProofDraft.length > qtyInt) window.warrantyProofDraft = window.warrantyProofDraft.slice(0, qtyInt);
        const attached = window.warrantyProofDraft.filter(function(a){ return a && a.image_data && String(a.image_data).indexOf('data:image/')===0; }).length;
        $badge.text(attached + ' / ' + qtyInt);
        const canAddMore = attached < qtyInt;
        $units.html(`
            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="warranty-proof-scan-btn"><i class="ti ti-scan me-1"></i>Scan</button>
                <label class="btn btn-sm btn-outline-secondary mb-0 ${canAddMore ? '' : 'disabled'}" style="cursor:${canAddMore ? 'pointer' : 'not-allowed'};">
                    <i class="ti ti-photo me-1"></i>${canAddMore ? 'Capture / Add Image' : 'Limit reached'}
                    <input type="file" class="d-none" id="warranty-proof-image-input" accept="image/*" capture="environment" ${canAddMore ? '' : 'disabled'}>
                </label>
                <span class="small text-muted ms-auto">Attached: <span class="fw-bold">${attached}</span> / ${qtyInt}</span>
            </div>
            <div class="row g-2" id="warranty-proof-grid"></div>
            <div class="mt-2 small">
                <div class="fw-bold text-dark">Extracted Codes</div>
                <div id="warranty-extracted-codes" class="text-muted">—</div>
            </div>
        `);
        // grid
        const $grid = $('#warranty-proof-grid');
        let extractedAll = [];
        window.warrantyProofDraft.forEach(function(att, i){
            if (!att || !att.image_data) return;
            extractedAll = extractedAll.concat(att.extracted_codes || []);
            const extracting = att.extracting === true;
            const codes = (att.extracted_codes || []);
            const ocrLine = (codes && codes.length) ? ('OCR: ' + codes.join(', ')) : 'OCR: No readable code detected';
            const scanLine = att.scanned_code ? ('Scanned: ' + att.scanned_code) : '';
            const metaLine = extracting
                ? '<span class="text-warning">Extracting…</span>'
                : (scanLine ? (scanLine + (codes && codes.length ? '<br>' : '')) : '') + ocrLine;
            $grid.append(`
                <div class="col-6 col-md-4">
                    <div class="border rounded p-2 position-relative" style="background:#fff;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 btn-warranty-attach-remove" data-attach-index="${i}" style="padding:2px 6px; line-height:1;">×</button>
                        <img src="${String(att.image_data).replace(/\"/g,'&quot;')}" class="img-fluid rounded border" style="max-height:120px;width:100%;object-fit:cover;" />
                        <div class="small mt-1">
                            <div class="fw-bold">Proof ${i+1}</div>
                            <div class="text-muted" style="font-size:11px;">${metaLine}</div>
                            <div class="mt-1">
                                <div class="small fw-bold text-dark">Final code</div>
                                <input type="text" class="form-control form-control-sm warranty-final-code" data-attach-index="${i}" placeholder="Tap to edit / paste code" value="${(att.final_code || '').toString().replace(/\"/g,'&quot;')}" />
                                <div class="small text-muted mt-1">Possible detected codes (tap to apply):</div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    ${(codes && codes.length ? codes.slice(0, 8) : []).map(function(c){
                                        var safe = String(c).replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\"/g,'&quot;');
                                        return '<button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 warranty-code-chip" data-attach-index="'+i+'" data-code="'+safe+'" style="font-size:11px;">'+safe+'</button>';
                                    }).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
        extractedAll = uniq(extractedAll);
        $('#warranty-extracted-codes').html(extractedAll.length ? ('<ul class="mb-0 ps-3">' + extractedAll.map(function(c){return '<li>'+String(c).replace(/</g,'&lt;').replace(/>/g,'&gt;')+'</li>';}).join('') + '</ul>') : '—');
        $section.removeClass('d-none');
        validateWarrantyProofSection();
    }
    function validateWarrantyProofSection() {
        const qtyInt = window.warrantyRequiredQty || 0;
        const $badge = $('#warranty-proof-badge');
        const $err = $('#warranty-proof-error');
        const $confirm = $('#confirm-entry');
        if (!isRetailCustomerSelected() || !isBatteryItemSelected() || !qtyInt) {
            $confirm.prop('disabled', false);
            return true;
        }
        const attached = (window.warrantyProofDraft || []).filter(function(a){ return a && a.image_data && String(a.image_data).indexOf('data:image/')===0; }).length;
        const seen = {};
        let dup = false;
        (window.warrantyProofDraft || []).forEach(function(a){
            if (!a) return;
            const codes = []
                .concat(a.final_code ? [a.final_code] : [])
                .concat(a.scanned_code ? [a.scanned_code] : [])
                .concat((a.extracted_codes || []));
            codes.forEach(function(c){
                const k = normalizeWarrantyCodeJs(c);
                if (!k) return;
                if (seen[k]) dup = true;
                seen[k] = true;
            });
        });
        $badge.text(attached + ' / ' + qtyInt);
        const ok = (attached === qtyInt && !dup);
        if (!ok) {
            let msg = 'Please attach warranty card proof for all selected quantity units.';
            if (dup) msg = 'Duplicate warranty code detected. Please enter unique codes for this item.';
            $err.removeClass('d-none').text(msg);
        } else {
            $err.addClass('d-none').text('');
        }
        if (isRetailCustomerSelected() && isBatteryItemSelected()) {
            $confirm.prop('disabled', !ok);
        } else {
            $confirm.prop('disabled', false);
        }
        return ok;
    }
    function getWarrantyProofsForSubmit(qtyInt) {
        const proofs = [];
        for (let i = 0; i < qtyInt; i++) {
            const a = window.warrantyProofDraft[i] || {};
            const codes = []
                .concat(a.final_code ? [a.final_code] : [])
                .concat(a.scanned_code ? [a.scanned_code] : [])
                .concat((a.extracted_codes || []));
            const firstCode = codes.length ? String(codes[0]).trim() : null;
            proofs.push({
                unit_no: i + 1,
                warehouse_id: ($('#selected-warehouse-id').val() || null),
                code: firstCode,
                final_code: a.final_code || null,
                scanned_code: a.scanned_code || null,
                extracted_codes: Array.isArray(a.extracted_codes) ? a.extracted_codes : [],
                image_data: (a.image_data || '').toString() || null,
                captured_at: a.captured_at || null
            });
        }
        return proofs;
    }
    $(document).on('click', '#warranty-proof-scan-btn', function(e) {
        e.preventDefault(); e.stopPropagation();
        Swal.fire({ icon: 'info', title: 'Scan code', text: 'Scan the warranty code now (USB scanner) or type it. The next attached image will use this scanned code.' })
            .then(function() {
                window.pendingWarrantyScannedCode = null;
            });
        // On classic page we don’t have camera scanner modal; allow USB scan by focusing item-search to capture scanner input if needed
    });
    $(document).on('change', '#warranty-proof-image-input', function() {
        const file = this.files && this.files[0];
        if (!file) return;
        const required = window.warrantyRequiredQty || 0;
        const attached = (window.warrantyProofDraft || []).filter(function(a){ return a && a.image_data && String(a.image_data).indexOf('data:image/')===0; }).length;
        if (attached >= required) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            const attachmentId = newWarrantyAttachmentId();
            const att = {
                _id: attachmentId,
                image_data: ev.target && ev.target.result ? String(ev.target.result) : '',
                captured_at: new Date().toISOString(),
                scanned_code: window.pendingWarrantyScannedCode ? String(window.pendingWarrantyScannedCode).trim() : null,
                extracted_codes: [],
                extracting: false
            };
            window.pendingWarrantyScannedCode = null;
            window.warrantyProofDraft.push(att);
            renderWarrantyProofSection(window.warrantyRequiredQty || 0);
            validateWarrantyProofSection();
            runOcrForAttachmentId(attachmentId);
        };
        reader.readAsDataURL(file);
        $(this).val('');
    });
    $(document).on('click', '.btn-warranty-attach-remove', function(e) {
        e.preventDefault(); e.stopPropagation();
        const idx = parseInt($(this).data('attach-index'), 10);
        if (!isFinite(idx) || idx < 0) return;
        window.warrantyProofDraft.splice(idx, 1);
        renderWarrantyProofSection(window.warrantyRequiredQty || 0);
        validateWarrantyProofSection();
    });
    $(document).on('input', '.warranty-final-code', function() {
        const idx = parseInt($(this).data('attach-index'), 10);
        if (!isFinite(idx) || idx < 0) return;
        window.warrantyProofDraft[idx] = window.warrantyProofDraft[idx] || {};
        window.warrantyProofDraft[idx].final_code = $(this).val();
        validateWarrantyProofSection();
    });
    $(document).on('click', '.warranty-code-chip', function(e) {
        e.preventDefault();
        const idx = parseInt($(this).data('attach-index'), 10);
        const code = $(this).data('code') || '';
        if (!isFinite(idx) || idx < 0) return;
        window.warrantyProofDraft[idx] = window.warrantyProofDraft[idx] || {};
        window.warrantyProofDraft[idx].final_code = String(code);
        $('.warranty-final-code[data-attach-index="'+idx+'"]').val(String(code));
        validateWarrantyProofSection();
    });

    $('#sales-item-quantity, #sales-item-quantity-input').on('input change', function() {
        if (!isRetailCustomerSelected()) return;
        const qtyInt = getSelectedQtyIntForWarranty();
        if (!qtyInt) {
            renderWarrantyProofSection(null);
            return;
        }
        renderWarrantyProofSection(qtyInt);
    });
    
    // Function to add item with entry type
    function addItemWithType(entryType) {
        const itemId = $('#selected-item-id').val();
        let quantity = parseFloat($('#sales-item-quantity').val()) || 0;
        
        // If custom quantity input is visible and has value, use that
        if ($('#sales-item-quantity-input').is(':visible') && $('#sales-item-quantity-input').val()) {
            quantity = parseFloat($('#sales-item-quantity-input').val()) || 0;
        }
        // If user selected quantity from warehouse row dropdown, use that
        const whId = $('#selected-warehouse-id').val();
        if (whId) {
            const whQtyVal = $('#stock-status-list .stock-warehouse-item[data-warehouse-id="' + whId + '"] .stock-warehouse-qty-input').val();
            if (whQtyVal && parseFloat(whQtyVal) >= 1) {
                quantity = parseFloat(whQtyVal);
            }
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
        let total = subtotal + taxAmount;
        
        // For return, claim, scrap_in: make total negative
        if (entryType === 'return' || entryType === 'claim' || entryType === 'scrap_in') {
            total = -Math.abs(total);
        }

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
            warehouse_id: warehouseId || null,
            entry_type: entryType,
            warranty_proofs: (window.currentWarrantyProofsForNextAdd || null)
        };

        salesItems.push(item);
        addItemToTable(item);
        resetItemModal();
        $('#add-item-modal').modal('hide');
        calculateTotals();
        window.currentWarrantyProofsForNextAdd = null;
    }
    
    // Confirm entry - use current entry type or default to 'sale'
    $('#confirm-entry').on('click', function() {
        // Retail + Battery: enforce warranty proofs per unit quantity
        if (isRetailCustomerSelected() && isBatteryItemSelected()) {
            const qtyInt = getSelectedQtyIntForWarranty();
            if (!qtyInt) {
                Swal.fire({ icon: 'warning', title: 'Warranty Proof Required', text: 'Retail sale requires integer quantity and warranty proof per unit.', confirmButtonText: 'OK' });
                return;
            }
            renderWarrantyProofSection(qtyInt);
            if (!validateWarrantyProofSection()) {
                Swal.fire({ icon: 'warning', title: 'Warranty Proof Required', text: 'Please attach warranty card proof for all selected quantity units.', confirmButtonText: 'OK' });
                return;
            }
            window.currentWarrantyProofsForNextAdd = getWarrantyProofsForSubmit(qtyInt);
        } else {
            $('#warranty-proof-section').addClass('d-none');
            window.currentWarrantyProofsForNextAdd = null;
        }
        const entryType = currentEntryType || 'sale';
        addItemWithType(entryType);
        // Reset entry type after adding
        currentEntryType = 'sale';
    });
    
    // Entry-type buttons are handled earlier with customer-required guard.
    
    // Delivery entry - open modal
    $('#delivery-entry-btn').on('click', function() {
        $('#delivery-modal').modal('show');
    });
    
    // Share delivery link - Open delivery modal with items
    $('#share-delivery-btn').on('click', function() {
        // Get current items from salesItems
        const items = salesItems.filter(item => item.type !== 'delivery'); // Exclude delivery items themselves
        
        if (items.length === 0) {
            alert('Please add items first before sharing delivery form!');
            return;
        }
        
        // Store items for sharing
        window.deliveryItemsToShare = items;
        
        // Populate items list in modal
        populateDeliveryItemsList(items);
        
        // Show items list
        $('#delivery-items-list').show();
        
        // Open delivery modal
        $('#delivery-modal').modal('show');
    });
    
    // Function to populate delivery items list
    function populateDeliveryItemsList(items) {
        const itemsList = $('#delivery-items-ul');
        itemsList.empty();
        
        if (items.length === 0) {
            itemsList.append('<li class="text-muted">No items to deliver</li>');
            return;
        }
        
        items.forEach((item, index) => {
            const listItem = $('<li>').addClass('mb-2 p-2 border-bottom').html(`
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${item.name || 'Item ' + (index + 1)}</strong>
                        <br>
                        <small class="text-muted">Qty: ${item.quantity || 1} ${item.unit || 'Unit'}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary">Rs. ${parseFloat(item.total || item.rate || 0).toFixed(2)}</span>
                    </div>
                </div>
            `);
            itemsList.append(listItem);
        });
    }
    
    // Check if page is opened with delivery mode (shared link)
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode');
        const deliveryId = urlParams.get('did');
        const itemsParam = urlParams.get('items');
        
        if (mode === 'delivery' && itemsParam) {
            try {
                const items = JSON.parse(decodeURIComponent(itemsParam));
                
                // Store items for delivery
                window.deliveryItemsToShare = items;
                
                // Populate items list
                populateDeliveryItemsList(items);
                
                // Show items list
                $('#delivery-items-list').show();
                
                // Open delivery modal automatically
                setTimeout(function() {
                    $('#delivery-modal').modal('show');
                }, 500);
            } catch (e) {
                console.error('Error parsing delivery items:', e);
            }
        }
    });
    
    // Share delivery link button in modal
    $('#share-delivery-link-btn').on('click', function() {
        const items = window.deliveryItemsToShare || salesItems.filter(item => item.type !== 'delivery');
        
        if (items.length === 0) {
            alert('No items to share!');
            return;
        }
        
        // Generate shareable link with items data
        const deliveryId = 'DEL_' + Date.now();
        const itemsData = items.map(item => ({
            id: item.item_id || item.id,
            name: item.name,
            quantity: item.quantity,
            unit: item.unit,
            rate: item.rate,
            total: item.total
        }));
        
        // Encode items data in URL
        const itemsEncoded = encodeURIComponent(JSON.stringify(itemsData));
        const baseUrl = window.location.origin + window.location.pathname;
        const shareUrl = baseUrl + '?mode=delivery&did=' + deliveryId + '&items=' + itemsEncoded;
        
        // Copy to clipboard
        const textArea = document.createElement('textarea');
        textArea.value = shareUrl;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        // Show success message
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Link Copied!',
                text: 'Delivery link has been copied to clipboard. Share it with the rider via WhatsApp.',
                timer: 3000,
                showConfirmButton: true
            });
        } else {
            alert('Delivery link copied! Send it to the rider via WhatsApp.\n\n' + shareUrl);
        }
    });
    
    // Confirm delivery entry
    $('#confirm-delivery-btn').on('click', function() {
        // Get vehicle photo details if added
        const vehiclePhoto = $('#vehicle-photo-capture')[0].files[0] || null;
        const vehicleRiderPhoto = $('#vehicle-rider-photo')[0].files[0] || null;
        const idCardFrontPhoto = $('#id-card-front-photo')[0].files[0] || null;
        const idCardBackPhoto = $('#id-card-back-photo')[0].files[0] || null;
        const currentVehiclePhoto = $('#current-vehicle-photo')[0].files[0] || null;
        
        // Store vehicle details in a global variable or form data
        window.deliveryVehicleDetails = {
            vehiclePhoto: vehiclePhoto,
            riderPhoto: vehicleRiderPhoto,
            idCardFrontPhoto: idCardFrontPhoto,
            idCardBackPhoto: idCardBackPhoto,
            currentVehiclePhoto: currentVehiclePhoto
        };
        
        // Continue with existing delivery confirmation logic
        const fare = parseFloat($('#delivery-fare').val()) || 0;
        const riderMobile = $('#delivery-rider-mobile').val() || '';
        
        if (fare > 0) {
            // Add delivery item to sales items
            const deliveryItem = {
                id: itemCounter++,
                item_id: 'DELIVERY_' + Date.now(),
                name: 'Delivery Service' + (riderMobile ? ' (Rider: ' + riderMobile + ')' : ''),
                quantity: 1,
                unit: 'Service',
                rate: fare,
                total: fare,
                entry_type: 'delivery',
                type: 'delivery',
                rider_mobile: riderMobile,
                vehicle_photo: vehiclePhoto,
                vehicle_rider_photo: vehicleRiderPhoto,
                id_card_front_photo: idCardFrontPhoto,
                id_card_back_photo: idCardBackPhoto,
                current_vehicle_photo: currentVehiclePhoto
            };
            
            salesItems.push(deliveryItem);
            addItemToTable(deliveryItem);
            calculateTotals();
            $('#delivery-modal').modal('hide');
            
            // Reset form
            $('#delivery-fare').val('0');
            $('#delivery-rider-mobile').val('');
            $('#vehicle-photo-capture').val('');
            $('#vehicle-rider-photo').val('');
            $('#id-card-front-photo').val('');
            $('#id-card-back-photo').val('');
            $('#current-vehicle-photo').val('');
            $('#vehicle-photo-capture-preview').empty().css('display', 'none');
            $('#vehicle-rider-photo-preview').empty().css('display', 'none');
            $('#id-card-front-photo-preview').empty().css('display', 'none');
            $('#id-card-back-photo-preview').empty().css('display', 'none');
            $('#current-vehicle-photo-preview').empty().css('display', 'none');
            $('#vehicle-icon-placeholder').show();
            $('#vehicle-text-placeholder').show();
            $('#rider-icon-placeholder').show();
            $('#rider-text-placeholder').show();
            $('#id-front-icon-placeholder').show();
            $('#id-front-text-placeholder').show();
            $('#id-back-icon-placeholder').show();
            $('#id-back-text-placeholder').show();
            $('#current-vehicle-icon-placeholder').show();
            $('#current-vehicle-text-placeholder').show();
        } else {
            alert('Please enter delivery fare');
        }
    });
    
    // Handle rider file upload
    window.handleRiderFile = function(input, type) {
        const previewId = type === 'item' ? 'rider-item-preview' : 'rider-face-preview';
        const preview = $('#' + previewId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.html('<img src="' + e.target.result + '" class="img-preview" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">');
                $(input).closest('label').addClass('bg-green-50 border-green-400');
            };
            reader.readAsDataURL(input.files[0]);
        }
    };
    
    // Handle vehicle photo capture and attachment
    window.handleVehiclePhoto = function(input, type) {
        let previewId, iconId, textId;
        if (type === 'vehicle') {
            previewId = 'vehicle-photo-capture-preview';
            iconId = 'vehicle-icon-placeholder';
            textId = 'vehicle-text-placeholder';
        } else if (type === 'rider') {
            previewId = 'vehicle-rider-photo-preview';
            iconId = 'rider-icon-placeholder';
            textId = 'rider-text-placeholder';
        } else if (type === 'id-front') {
            previewId = 'id-card-front-photo-preview';
            iconId = 'id-front-icon-placeholder';
            textId = 'id-front-text-placeholder';
        } else if (type === 'id-back') {
            previewId = 'id-card-back-photo-preview';
            iconId = 'id-back-icon-placeholder';
            textId = 'id-back-text-placeholder';
        } else if (type === 'current-vehicle') {
            previewId = 'current-vehicle-photo-preview';
            iconId = 'current-vehicle-icon-placeholder';
            textId = 'current-vehicle-text-placeholder';
        }
        
        const preview = $('#' + previewId);
        const icon = $('#' + iconId);
        const text = $('#' + textId);
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageUrl = e.target.result;
                preview.html(`
                    <div style="position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                        <img src="${imageUrl}" class="img-preview" style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px; padding: 5px; cursor: pointer;" onclick="window.open('${imageUrl}', '_blank')">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeVehiclePhoto('${type}')" style="position: absolute; top: 5px; right: 5px; width: 30px; height: 30px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                            <i class="ti ti-x" style="font-size: 16px;"></i>
                        </button>
                    </div>
                `);
                preview.css('display', 'flex');
                icon.hide();
                text.hide();
                
                if (type === 'vehicle') {
                    $(input).closest('label').css({
                        'background-color': '#eff6ff',
                        'border-color': '#3b82f6'
                    });
                } else if (type === 'rider') {
                    $(input).closest('label').css({
                        'background-color': '#faf5ff',
                        'border-color': '#a855f7'
                    });
                } else if (type === 'id-front') {
                    $(input).closest('label').css({
                        'background-color': '#f0fdf4',
                        'border-color': '#10b981'
                    });
                } else if (type === 'id-back') {
                    $(input).closest('label').css({
                        'background-color': '#fff7ed',
                        'border-color': '#f97316'
                    });
                } else if (type === 'current-vehicle') {
                    $(input).closest('label').css({
                        'background-color': '#fef2f2',
                        'border-color': '#ef4444'
                    });
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    };
    
    // Remove vehicle photo function
    window.removeVehiclePhoto = function(type) {
        let inputId, previewId, iconId, textId;
        if (type === 'vehicle') {
            inputId = 'vehicle-photo-capture';
            previewId = 'vehicle-photo-capture-preview';
            iconId = 'vehicle-icon-placeholder';
            textId = 'vehicle-text-placeholder';
        } else if (type === 'rider') {
            inputId = 'vehicle-rider-photo';
            previewId = 'vehicle-rider-photo-preview';
            iconId = 'rider-icon-placeholder';
            textId = 'rider-text-placeholder';
        } else if (type === 'id-front') {
            inputId = 'id-card-front-photo';
            previewId = 'id-card-front-photo-preview';
            iconId = 'id-front-icon-placeholder';
            textId = 'id-front-text-placeholder';
        } else if (type === 'id-back') {
            inputId = 'id-card-back-photo';
            previewId = 'id-card-back-photo-preview';
            iconId = 'id-back-icon-placeholder';
            textId = 'id-back-text-placeholder';
        } else if (type === 'current-vehicle') {
            inputId = 'current-vehicle-photo';
            previewId = 'current-vehicle-photo-preview';
            iconId = 'current-vehicle-icon-placeholder';
            textId = 'current-vehicle-text-placeholder';
        }
        
        $('#' + inputId).val('');
        $('#' + previewId).empty().css('display', 'none');
        $('#' + iconId).show();
        $('#' + textId).show();
        
        if (type === 'vehicle') {
            $('#' + inputId).closest('label').css({
                'background-color': '#eff6ff',
                'border-color': '#3b82f6'
            });
        } else if (type === 'rider') {
            $('#' + inputId).closest('label').css({
                'background-color': '#faf5ff',
                'border-color': '#a855f7'
            });
        } else if (type === 'id-front') {
            $('#' + inputId).closest('label').css({
                'background-color': '#f0fdf4',
                'border-color': '#10b981'
            });
        } else if (type === 'id-back') {
            $('#' + inputId).closest('label').css({
                'background-color': '#fff7ed',
                'border-color': '#f97316'
            });
        } else if (type === 'current-vehicle') {
            $('#' + inputId).closest('label').css({
                'background-color': '#fef2f2',
                'border-color': '#ef4444'
            });
        }
    };
    
    // Reset delivery modal when closed
    $('#delivery-modal').on('hidden.bs.modal', function() {
        // Hide items list
        $('#delivery-items-list').hide();
        $('#delivery-items-ul').empty();
        $('#vehicle-photo-capture').val('');
        $('#vehicle-rider-photo').val('');
        $('#id-card-front-photo').val('');
        $('#id-card-back-photo').val('');
        $('#current-vehicle-photo').val('');
        $('#vehicle-photo-capture-preview').empty().css('display', 'none');
        $('#vehicle-rider-photo-preview').empty().css('display', 'none');
        $('#id-card-front-photo-preview').empty().css('display', 'none');
        $('#id-card-back-photo-preview').empty().css('display', 'none');
        $('#current-vehicle-photo-preview').empty().css('display', 'none');
        $('#vehicle-icon-placeholder').show();
        $('#vehicle-text-placeholder').show();
        $('#rider-icon-placeholder').show();
        $('#rider-text-placeholder').show();
        $('#id-front-icon-placeholder').show();
        $('#id-front-text-placeholder').show();
        $('#id-back-icon-placeholder').show();
        $('#id-back-text-placeholder').show();
        $('#current-vehicle-icon-placeholder').show();
        $('#current-vehicle-text-placeholder').show();
        // Reset label styles
        $('#vehicle-photo-capture').closest('label').css({
            'background-color': '#eff6ff',
            'border-color': '#bfdbfe'
        });
        $('#vehicle-rider-photo').closest('label').css({
            'background-color': '#faf5ff',
            'border-color': '#e9d5ff'
        });
        $('#id-card-front-photo').closest('label').css({
            'background-color': '#f0fdf4',
            'border-color': '#86efac'
        });
        $('#id-card-back-photo').closest('label').css({
            'background-color': '#fff7ed',
            'border-color': '#fdba74'
        });
        $('#current-vehicle-photo').closest('label').css({
            'background-color': '#fef2f2',
            'border-color': '#fca5a5'
        });
    });
    
    // Submit rider data
    $('#submit-rider-data-btn').on('click', function() {
        const mobile = $('#rider-mobile-input').val();
        if (!mobile) {
            alert('Mobile number is required!');
            return;
        }
        
        const btn = $(this);
        btn.prop('disabled', true).text('Sending...');
        
        // Here you would typically send data to server
        // For now, just show success message
        setTimeout(function() {
            alert('Data sent successfully!');
            $('#rider-view-modal').modal('hide');
            btn.prop('disabled', false).text('CONFIRM & SEND DATA');
            // Reset form
            $('#rider-mobile-input').val('');
            $('#rider-item-photo').val('');
            $('#rider-face-photo').val('');
            $('#rider-item-preview').empty();
            $('#rider-face-preview').empty();
        }, 1000);
    });
    
    // Share delivery link function
    window.shareDeliveryLink = function() {
        const fareInput = prompt('Delivery Charges Likhen (Optional):', '0');
        if (fareInput === null) return;
        
        const fare = parseFloat(fareInput) || 0;
        const deliveryId = 'DEL_' + Date.now();
        
        // Generate shareable link
        const baseUrl = window.location.origin + window.location.pathname;
        const shareUrl = baseUrl + '?mode=rider&did=' + deliveryId;
        
        // Copy to clipboard
        const textArea = document.createElement('textarea');
        textArea.value = shareUrl;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        alert('Delivery link copied! Send it to the loader via WhatsApp.');
        
        // Store delivery sync data (you can integrate with your backend here)
        console.log('Delivery ID:', deliveryId, 'Fare:', fare);
    };
    
    // Vehicle Management - Multiple vehicles support
    let selectedVehicles = [];
    let vehicleCounter = 0;
    
    // Open vehicle modal
    $('#add-vehicle-btn').on('click', function() {
        $('#vehicle-modal').modal('show');
    });
    
    // Save vehicle (saves to database immediately with selected customer_id)
    $('#save-vehicle-btn').on('click', function() {
        const customerId = $('#customer_id').val();
        if (!customerId) {
            alert('Please select a customer first before adding vehicle');
            return;
        }
        
        const reg = $('#vehicle-reg').val().trim().toUpperCase();
        const make = $('#vehicle-make').val().trim();
        const model = $('#vehicle-model').val().trim();
        const year = $('#vehicle-year').val().trim();
        
        if (!reg) {
            alert('Please enter registration number');
            return;
        }
        if (!make || !model || !year) {
            alert('Please fill in make, model and year');
            return;
        }
        
        const yearNum = parseInt(year);
        if (isNaN(yearNum) || yearNum < 1950 || yearNum > new Date().getFullYear() + 1) {
            alert('Please enter a valid year (1950 to ' + (new Date().getFullYear() + 1) + ')');
            return;
        }
        
        const exists = selectedVehicles.some(v => v.reg === reg);
        if (exists) {
            alert('Vehicle with this registration number already added');
            return;
        }
        
        const payload = {
            customer_id: customerId,
            plate_number: reg,
            make: make,
            model: model,
            year: year,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $.ajax({
            url: '{{ route("customer.vehicles.store") }}',
            type: 'POST',
            data: payload,
            success: function(res) {
                if (res && res.success) {
                    const vehicle = {
                        id: vehicleCounter++,
                        reg: reg,
                        make: make,
                        model: model,
                        year: year
                    };
                    selectedVehicles.push(vehicle);
                    updateVehicleDisplay();
                    $('#vehicle-modal').modal('hide');
                    resetVehicleModal();
                }
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || xhr.statusText || 'Failed to save vehicle';
                alert('Could not save vehicle: ' + msg);
            }
        });
    });
    
    // Remove vehicle
    $(document).on('click', '.remove-vehicle-btn', function() {
        const vehicleId = parseInt($(this).data('vehicle-id'));
        selectedVehicles = selectedVehicles.filter(v => v.id !== vehicleId);
        updateVehicleDisplay();
    });
    
    // Update vehicle display
    function updateVehicleDisplay() {
        const displaySection = $('#vehicle-display-section');
        const vehiclesList = $('#vehicles-list');
        
        if (selectedVehicles.length > 0) {
            vehiclesList.empty();
            
            selectedVehicles.forEach(vehicle => {
                let details = [];
                if (vehicle.make) details.push(vehicle.make);
                if (vehicle.model) details.push(vehicle.model);
                if (vehicle.year) details.push(vehicle.year);
                
                const vehicleHtml = `
                    <div class="p-3 rounded border d-flex justify-content-between align-items-start" style="background-color: #f0f9ff; border-color: #0ea5e9 !important;">
                        <div class="flex-grow-1">
                            <p class="fw-bold text-primary mb-1">${vehicle.reg}</p>
                            <p class="text-muted small mb-0">${details.join(' • ') || 'No additional details'}</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-vehicle-btn" data-vehicle-id="${vehicle.id}">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                `;
                vehiclesList.append(vehicleHtml);
            });
            
            displaySection.show();
        } else {
            displaySection.hide();
        }
    }
    
    // Reset vehicle modal
    function resetVehicleModal() {
        $('#vehicle-reg').val('');
        $('#vehicle-make').val('');
        $('#vehicle-model').val('');
        $('#vehicle-year').val('');
    }
    
    // Reset vehicle modal when it closes
    $('#vehicle-modal').on('hidden.bs.modal', function() {
        resetVehicleModal();
    });

    function addItemToTable(item) {
        $('#empty-items-state').hide();
        $('#items-list').show();
        
        // Entry type badge
        let typeBadge = '';
        const entryType = item.entry_type || item.type || 'sale';
        if (entryType === 'return') {
            typeBadge = ' <span class="badge bg-danger text-white ms-1" style="font-size: 9px;">RETURN</span>';
        } else if (entryType === 'claim') {
            typeBadge = ' <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;">CLAIM</span>';
        } else if (entryType === 'scrap_in') {
            typeBadge = ' <span class="badge bg-orange text-white ms-1" style="font-size: 9px;">SCRAP IN</span>';
        } else if (entryType === 'scrap_sale') {
            typeBadge = ' <span class="badge bg-success text-white ms-1" style="font-size: 9px;">SCRAP SALE</span>';
        } else if (entryType === 'delivery') {
            typeBadge = ' <span class="badge bg-orange text-white ms-1" style="font-size: 9px; background-color: #f97316 !important;">DELIVERY</span>';
        }
        
        // Format total - show negative for return/claim/scrap_in
        const totalValue = parseFloat(item.total);
        const totalDisplay = totalValue < 0 ? 'Rs -' + Math.abs(totalValue).toLocaleString() : 'Rs ' + totalValue.toLocaleString();
        const totalClass = totalValue < 0 ? 'text-danger' : '';
        
        const proofImages = (item.warranty_proofs && Array.isArray(item.warranty_proofs))
            ? item.warranty_proofs.filter(function(p) { return p && p.image_data && String(p.image_data).indexOf('data:image/') === 0; })
            : [];
        const warrantyBtnHtml = proofImages.length > 0
            ? `<button type="button" class="btn btn-sm btn-outline-secondary btn-view-warranty-proofs me-2" data-row-id="${item.id}" title="View warranty proofs">
                    <i class="ti ti-camera"></i> <span class="badge bg-dark ms-1">${proofImages.length}</span>
               </button>`
            : '';

        const row = `
            <div class="item-row" data-item-id="${item.item_id}" data-row-id="${item.id}">
                <div>
                    <h4 class="item-name">${item.name}${typeBadge}</h4>
                    <p class="item-details">${item.quantity} ${item.unit} x Rs ${parseFloat(item.rate).toLocaleString()}</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="item-total ${totalClass}">${totalDisplay}</span>
                    ${warrantyBtnHtml}
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item" data-row-id="${item.id}">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#items-tbody').append(row);
        
        // Update item count
        const itemCount = $('#items-tbody .item-row').length;
        $('#item-count').text(itemCount + ' Items Listed');
    }

    // Warranty proof viewer (images only) - create sale screen
    function renderWarrantyProofViewer(item) {
        var proofs = (item && item.warranty_proofs && Array.isArray(item.warranty_proofs)) ? item.warranty_proofs : [];
        proofs = proofs.filter(function(p) { return p && p.image_data && String(p.image_data).indexOf('data:image/') === 0; });
        if (!proofs.length) {
            Swal.fire({ icon: 'info', title: 'No Warranty Images', text: 'This item has no attached warranty card pictures.' });
            return;
        }
        window._warrantyViewerState = { proofs: proofs, idx: 0, item: item };
        function fmt(ts) { try { return ts ? new Date(ts).toLocaleString() : ''; } catch(e) { return ''; } }
        function draw() {
            var st = window._warrantyViewerState;
            var p = st.proofs[st.idx];
            $('#warranty-viewer-image').attr('src', p.image_data);
            $('#warranty-viewer-title').text((st.item && st.item.name ? st.item.name : 'Warranty Proof') + ' (' + (st.idx + 1) + '/' + st.proofs.length + ')');
            $('#warranty-viewer-unit').text(p.unit_no != null ? ('Unit ' + p.unit_no) : '');
            $('#warranty-viewer-code').text(p.code ? ('Code: ' + p.code) : '');
            $('#warranty-viewer-time').text(p.captured_at ? ('Captured: ' + fmt(p.captured_at)) : '');
            $('#warranty-viewer-prev').prop('disabled', st.idx === 0);
            $('#warranty-viewer-next').prop('disabled', st.idx === st.proofs.length - 1);
        }
        draw();
        $('#warranty-proof-viewer-modal').modal('show');
        $('#warranty-viewer-prev').off('click').on('click', function() { var st = window._warrantyViewerState; if (!st) return; st.idx = Math.max(0, st.idx - 1); draw(); });
        $('#warranty-viewer-next').off('click').on('click', function() { var st = window._warrantyViewerState; if (!st) return; st.idx = Math.min(st.proofs.length - 1, st.idx + 1); draw(); });
    }

    $(document).on('click', '.btn-view-warranty-proofs', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var rowId = $(this).data('row-id');
        var item = (typeof salesItems !== 'undefined' && Array.isArray(salesItems))
            ? salesItems.find(function(x) { return String(x.id) === String(rowId); })
            : null;
        renderWarrantyProofViewer(item);
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        const rowId = $(this).data('row-id');
        salesItems = salesItems.filter(item => item.id !== rowId);
        $(this).closest('.item-row').remove();
        
        if ($('#items-tbody .item-row').length === 0) {
            $('#empty-items-state').show();
            $('#items-list').hide();
        }
        
        // Update item count
        const itemCount = $('#items-tbody .item-row').length;
        $('#item-count').text(itemCount + ' Items Listed');
        
        calculateTotals();
    });

    function resetItemModal() {
        $('#selected-item-id').val('');
        $('#item-search').val('');
        window.warrantyProofDraft = [];
        window.warrantyRequiredQty = 0;
        window.currentWarrantyProofsForNextAdd = null;
        window.pendingWarrantyScannedCode = null;
        $('#warranty-proof-section').addClass('d-none');
        $('#warranty-proof-units').empty();
        $('#warranty-proof-badge').text('0 / 0');
        $('#warranty-proof-error').addClass('d-none').text('');
        $('#confirm-entry').prop('disabled', false);
        $('#sales-item-quantity').val('');
        $('#sales-item-quantity-input').val('1').hide();
        $('#sales-item-unit').val('Can');
        $('#sales-item-rate').val('0');
        $('#sales-warranty-value').val('');
        $('#sales-warranty-unit').val('');
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
        const netPayable = itemTotal - discount;
        const grandTotal = itemTotal + orderTax - discount + shipping;

        $('#gross-amount').text(parseFloat(grossTotal).toLocaleString());
        $('#net-payable-total').text(parseFloat(netPayable).toLocaleString());
        $('#grand-total').text(parseFloat(grandTotal).toLocaleString());
        
        // Set max payment amount to grand total
        const grandTotalValue = parseFloat(grandTotal);
        if ($('#sales_payment_amount').length) {
            $('#sales_payment_amount').attr('max', grandTotalValue);
            if (parseFloat($('#sales_payment_amount').val()) > grandTotalValue) {
                $('#sales_payment_amount').val(grandTotalValue);
            }
        }
        
        // Calculate final totals with received amount and previous balance
        calculateFinalTotalFromInput();
    }
    
    // Toggle discount type between fixed (Rs) and percentage (%)
    function toggleBillDiscType() {
        const btn = $('#billDiscToggle');
        const type = $('#totalBillDiscType');
        if (type.val() === 'fixed') {
            type.val('percent');
            btn.text('%');
        } else {
            type.val('fixed');
            btn.text('Rs');
        }
        calculateFinalTotalFromInput();
    }
    
    // Calculate final totals including received amount and previous balance
    function calculateFinalTotalFromInput() {
        // Get gross total from items
        let itemTotal = 0;
        salesItems.forEach(function(item) {
            itemTotal += parseFloat(item.total);
        });
        
        // Get manual discount
        const manualDiscValue = parseFloat($('#totalBillDiscount').val()) || 0;
        const discType = $('#totalBillDiscType').val();
        
        // Calculate discount amount
        let discountAmount = 0;
        if (discType === 'percent') {
            discountAmount = (itemTotal * manualDiscValue) / 100;
        } else {
            discountAmount = manualDiscValue;
        }
        
        // Update hidden discount field
        $('#discount').val(discountAmount);
        
        // Calculate net payable
        const netPayable = itemTotal - discountAmount;
        
        // Calculate total cash received from all cash inputs
        let totalCash = 0;
        $('.cash-input').each(function() {
            totalCash += parseFloat($(this).val()) || 0;
        });
        
        // Calculate total bank payments
        let totalBank = 0;
        $('.bank-input-amt').each(function() {
            totalBank += parseFloat($(this).val()) || 0;
        });
        
        // Calculate total cash returns
        let totalReturns = 0;
        $('.return-input').each(function() {
            totalReturns += parseFloat($(this).val()) || 0;
        });
        
        // Total received = cash + bank - returns
        const totalReceived = (totalCash + totalBank) - totalReturns;
        
        // Get previous balance
        const preBalance = parseFloat($('#previousBalance').val()) || 0;
        
        // Calculate current remaining
        const currentRemaining = netPayable - totalReceived;
        
        // Calculate final total balance
        const finalTotalBalance = currentRemaining + preBalance;
        
        // Update displays
        $('#gross-amount').text(parseFloat(itemTotal).toLocaleString());
        $('#net-payable-total').text(parseFloat(netPayable).toLocaleString());
        $('#currentRemainingText').text(parseFloat(currentRemaining).toLocaleString());
        
        const grandTotalElem = $('#grand-total');
        grandTotalElem.text(parseFloat(finalTotalBalance).toLocaleString());
        
        // Update balance label and color
        const balanceLabel = $('#balanceLabel');
        if (finalTotalBalance > 0) {
            grandTotalElem.removeClass('text-blue-400').addClass('text-red-500');
            balanceLabel.text('Total Final Balance');
        } else if (finalTotalBalance < 0) {
            grandTotalElem.removeClass('text-red-500').addClass('text-blue-400');
            balanceLabel.text('Change to Return');
        } else if (finalTotalBalance === 0 && netPayable > 0) {
            grandTotalElem.removeClass('text-red-500').addClass('text-blue-400');
            balanceLabel.text('Settled / Fully Paid');
        } else {
            grandTotalElem.removeClass('text-red-500').addClass('text-blue-400');
            balanceLabel.text('Total Final Balance');
        }
    }
    
    // Handle image pick for cash, bank, and return entries
    window.handleImagePick = function(input, activeColor, statusTextLabel) {
        const label = $(input).parent();
        const statusText = label.find('.status-text');
        if (input.files && input.files[0]) {
            label.removeClass('bg-white').removeClass('border-' + activeColor + '-200');
            label.addClass('bg-green-50').addClass('border-green-400');
            statusText.removeClass('text-' + activeColor + '-400');
            statusText.addClass('text-green-600');
            statusText.html('<i class="ti ti-check me-1"></i> ' + statusTextLabel);
        }
    };
    
    // Add new cash received row
    window.addCashReceivedRow = function() {
        const wrapper = $('#cashReceivedWrapper');
        const row = $('<div>').addClass('payment-card border-blue-100 no-print');
        row.html(`
            <div class="d-flex justify-content-between align-items-center gap-2">
                <p class="mb-0" style="font-size: 10px; font-weight: 900; color: #374151; text-transform: uppercase;">Cash Entry</p>
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center bg-white rounded-lg px-2 border" style="border-color: #e5e7eb !important;">
                        <span class="text-[10px] font-black text-gray-400 mr-1.5 uppercase" style="font-size: 10px; font-weight: 900; color: #9ca3af; text-transform: uppercase; margin-right: 6px;">Rs</span>
                        <input type="number" class="cash-input" oninput="calculateFinalTotalFromInput()" value="0" style="width: 96px; background: transparent; font-weight: 900; text-align: right; outline: none; color: #1f2937; border: none; font-size: 14px;">
                    </div>
                    <button type="button" onclick="$(this).closest('.payment-card').remove(); calculateFinalTotalFromInput();" class="text-red-400 p-1">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <label class="d-flex-1 cursor-pointer bg-white border border-dashed rounded-lg p-2 text-center block transition-all duration-300" style="border-color: #bfdbfe;">
                    <p class="status-text mb-0" style="font-size: 8px; font-weight: 900; color: #60a5fa; text-transform: uppercase;"><i class="ti ti-camera me-1"></i> Attach Photo</p>
                    <input type="file" accept="image/*" class="d-none cash-input-pic" onchange="handleImagePick(this, 'blue', 'Photo Attached')">
                </label>
            </div>
        `);
        wrapper.append(row);
    };
    
    // Add bank payment row
    window.addBankPaymentRow = function() {
        const wrapper = $('#bankPaymentsWrapper');
        const row = $('<div>').addClass('bank-received-card border-purple-200 no-print');
        row.html(`
            <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="mb-0" style="font-size: 9px; font-weight: 900; color: #9333ea; text-transform: uppercase; font-style: italic;">Bank Entry</p>
                <button type="button" onclick="$(this).closest('.bank-received-card').remove(); calculateFinalTotalFromInput();" class="text-red-400">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
            <div class="mb-2 bg-white rounded-lg px-2 border" style="border-color: #e9d5ff;">
                <p class="mb-0" style="font-size: 8px; font-weight: 900; color: #6b7280; text-transform: uppercase; font-style: italic;">Select Bank</p>
                <select class="bank-input-name w-100 bg-transparent font-black outline-none" style="color: #9333ea; font-size: 14px; border: none;">
                    <option value="UBL-Pakistan Battery Agency">UBL-Pakistan Battery Agency</option>
                    <option value="Easypaisa Malik Bilal Mubarak">Easypaisa Malik Bilal Mubarak</option>
                    <option value="JazzCash Ghulam Mrtaza">JazzCash Ghulam Mrtaza</option>
                </select>
            </div>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <div class="bg-white rounded-lg px-2 border" style="border-color: #e9d5ff;">
                        <p class="mb-0" style="font-size: 8px; font-weight: 900; color: #6b7280; text-transform: uppercase; font-style: italic;">Ref No.</p>
                        <input type="text" placeholder="Trans ID..." class="bank-input-ref w-100 bg-transparent font-black outline-none" style="color: #9333ea; font-size: 14px; border: none; text-transform: uppercase;">
                    </div>
                </div>
                <div class="col-6">
                    <div class="bg-white rounded-lg px-2 border" style="border-color: #e9d5ff;">
                        <p class="mb-0" style="font-size: 8px; font-weight: 900; color: #6b7280; text-transform: uppercase; font-style: italic;">Amount (Rs)</p>
                        <input type="number" oninput="calculateFinalTotalFromInput()" value="0" class="bank-input-amt w-100 bg-transparent font-black text-right outline-none" style="color: #9333ea; font-size: 14px; border: none;">
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex align-items-center gap-2">
                <label class="flex-1 cursor-pointer bg-white border border-dashed rounded-lg p-2 text-center" style="border-color: #c084fc;">
                    <p class="status-text mb-0" style="font-size: 8px; font-weight: 900; color: #a855f7; text-transform: uppercase;"><i class="ti ti-camera me-1"></i> Receipt (Compulsory)</p>
                    <input type="file" accept="image/*" class="d-none bank-input-pic" onchange="handleImagePick(this, 'purple', 'Receipt Attached')">
                </label>
            </div>
        `);
        wrapper.append(row);
    };
    
    // Add cash return row
    window.addCashReturnRow = function() {
        const wrapper = $('#cashReturnWrapper');
        const row = $('<div>').addClass('payment-card border-red-100 no-print mt-1');
        row.html(`
            <div class="d-flex justify-content-between align-items-center gap-2">
                <p class="mb-0" style="font-size: 10px; font-weight: 900; color: #dc2626; text-transform: uppercase;">Return Entry</p>
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center bg-white rounded-lg px-2 border" style="border-color: #e5e7eb !important;">
                        <span class="text-[10px] font-black text-gray-400 mr-2 uppercase" style="font-size: 10px; font-weight: 900; color: #9ca3af; text-transform: uppercase; margin-right: 8px;">Rs</span>
                        <input type="number" oninput="calculateFinalTotalFromInput()" value="0" class="return-input w-24 bg-transparent font-black text-right outline-none" style="width: 96px; background: transparent; font-weight: 900; text-align: right; outline: none; color: #991b1b; border: none; font-size: 14px;">
                    </div>
                    <button type="button" onclick="$(this).closest('.payment-card').remove(); calculateFinalTotalFromInput();" class="text-red-400 p-1">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <label class="d-flex-1 cursor-pointer bg-white border border-dashed rounded-lg p-2 text-center block transition-all duration-300" style="border-color: #fca5a5;">
                    <p class="status-text mb-0" style="font-size: 8px; font-weight: 900; color: #ef4444; text-transform: uppercase;"><i class="ti ti-camera me-1"></i> Refund Photo</p>
                    <input type="file" accept="image/*" class="d-none return-input-pic" onchange="handleImagePick(this, 'red', 'Return Attached')">
                </label>
            </div>
        `);
        wrapper.append(row);
    };
    
    // Payment method change handler for sales
    $('#sales_payment_method_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const requiresBank = selectedOption.data('requires-bank') == '1';
        
        if (requiresBank) {
            $('#sales_bank_account_wrapper').show();
            $('#sales_bank_account_id').prop('required', true);
        } else {
            $('#sales_bank_account_wrapper').hide();
            $('#sales_bank_account_id').prop('required', false);
            $('#sales_bank_account_id').val('');
        }
    });

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
            vehicles: selectedVehicles.map(vehicle => ({
                registration: vehicle.reg,
                make: vehicle.make || null,
                model: vehicle.model || null,
                year: vehicle.year || null
            })),
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
                warranty_proofs: item.warranty_proofs || null,
                warehouse_id: item.warehouse_id || null,
                entry_type: item.entry_type || 'sale'
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
