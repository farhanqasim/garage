@extends('layouts.app')

@section('title', 'Create Sales')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4 id="page-title-text">Create Sales</h4>
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
                    <form action="{{ route('sales.store') }}" method="POST" id="salesForm" enctype="multipart/form-data">
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
                        
                        <!-- Business Information Panel (Like Screenshot Design) -->
                        <div class="mb-4 p-4 rounded" id="salesDocTypePanel" style="border: 1px solid #0d6efd; background: #ffffff;">
                            <div class="row align-items-center">
                                <!-- Left Side: Company Information -->
                                <div class="col-md-6">
                                    <a href="{{ route('home') }}" class="text-decoration-none d-inline-block" title="Home par jayein">
                                        <h2 class="mb-1 fw-bold" style="color: #0d6efd; font-size: 28px; line-height: 1.2;">{{ setting_value('logo_text', 'BARKI EXPRESS') }}</h2>
                                    </a>
                                    <p class="mb-2" style="color: #0d6efd; font-size: 14px; font-weight: 500;">AUTO OIL & SPARE PARTS SPECIALIST</p>
                                    <p class="mb-0" style="color: #6c757d; font-size: 13px;">
                                        <i class="ti ti-phone me-1"></i>HELPLINE: <span id="helplineNumber" style="color: #0d6efd; font-weight: 500;">{{ setting_value('helpline', '+92-335-08-999-08') }}</span>
                                    </p>
                                </div>
                                
                                <!-- Right Side: Invoice Details -->
                                <div class="col-md-6 text-end">
                                    <div class="mb-2" style="font-size: 13px; color: #6c757d;">
                                        <span id="currentDateTime">{{ date('d/m/Y, h:i:s A') }}</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-end gap-2" style="flex-wrap: wrap;">
                                        <span class="text-primary fw-bold" style="font-size: 18px;" id="sales-number">INV #00001</span>
                                        <!-- S/E/O Toggle Switch -->
                                        <div class="d-inline-block position-relative" style="vertical-align: middle;">
                                            <div class="switch-container" style="position: relative; width: 80px; height: 30px; background: #2563eb; border-radius: 15px; cursor: pointer; transition: all 0.3s ease;" id="estimate-order-toggle">
                                                <div class="switch-slider" style="position: absolute; width: 24px; height: 24px; background: white; border-radius: 50%; top: 3px; left: 3px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
                                                <div class="switch-indicators" style="position: absolute; width: 100%; height: 100%; display: flex; justify-content: space-around; align-items: center; pointer-events: none;">
                                                    <span style="font-size: 8px; color: rgba(255,255,255,0.5);">S</span>
                                                    <span style="font-size: 8px; color: rgba(255,255,255,0.5);">E</span>
                                                    <span style="font-size: 8px; color: rgba(255,255,255,0.5);">O</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                            
                        <!-- Hidden sale date field -->
                        <input type="hidden" name="sale_date" id="sale_date" value="{{ date('Y-m-d') }}" required>
                        <input type="hidden" name="status" id="sale-status" value="pending">

                        <!-- Vehicle Section (Like Screenshot) -->
                            <div class="mb-4">
                                <button type="button" class="btn w-100 mb-3" id="add-vehicle-btn" style="background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 12px; padding: 18px; color: #2563eb; font-weight: 900; text-transform: uppercase; font-size: 14px;">
                                <i class="ti ti-car me-2"></i>ADD / LINK VEHICLE DETAILS
                                </button>
                                
                                <div id="vehicle-display-section" style="display: none;">
                                    <p class="text-primary fw-bold mb-2" style="font-size: 11px;">ACTIVE VEHICLES</p>
                                    <div id="vehicles-list" class="flex-column gap-2" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                                        <!-- Vehicles will be dynamically added here -->
                                    </div>
                                </div>
                            </div>
                            
                        <!-- Customer Information (Like Screenshot Design) -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">PARTY NAME / VEHICLE #</label>
                                <div id="customer-branch-display" class="small mb-1" style="font-size: 10px; color: #6c757d; display: none;">
                                    <span class="text-muted">Branch:</span> <span id="customer-branch-name"></span>
                                </div>
                                <div class="input-group">
                                    <select name="customer_id" id="customer_id" class="form-control select2-customer-search @error('customer_id') is-invalid @enderror" required style="border-radius: 6px 0 0 6px;">
                                        <option value="" selected>SEARCH PARTY NAME / VEHICLE #...</option>
                                    @foreach($customers as $customer)
                                        @php
                                            $customerName = $customer->names[0] ?? 'N/A';
                                            $displayText = $customerName;
                                            if($customer->company) {
                                                $displayText .= ' - ' . $customer->company;
                                            }
                                            if(!empty($customer->phones[0])) {
                                                $displayText .= ' - ' . $customer->phones[0];
                                            }
                                        @endphp
                                        <option value="{{ $customer->id }}" 
                                                data-name="{{ $customer->names[0] ?? '' }}" 
                                                data-phone="{{ $customer->phones[0] ?? '' }}"
                                                data-company="{{ $customer->company ?? '' }}"
                                                data-address="{{ $customer->address ?? '' }}"
                                                data-area="{{ $customer->area ?? '' }}"
                                                data-branch-name="{{ optional($customer->branch)->branch_name ?? '—' }}"
                                                data-search-text="{{ strtolower($displayText) }}">
                                            {{ $displayText }}
                                        </option>
                                        @if($customer->customerCars && $customer->customerCars->count() > 0)
                                            @foreach($customer->customerCars as $vehicle)
                                                @php
                                                    $vehicleText = $customerName . ' - ' . $vehicle->plate_number;
                                                    if($vehicle->make) {
                                                        $vehicleText .= ' (' . $vehicle->make;
                                                        if($vehicle->model) {
                                                            $vehicleText .= ' ' . $vehicle->model;
                                                        }
                                                        $vehicleText .= ')';
                                                    }
                                                @endphp
                                                <option value="{{ $customer->id }}" 
                                                        data-name="{{ $customer->names[0] ?? '' }}" 
                                                        data-phone="{{ $customer->phones[0] ?? '' }}"
                                                        data-company="{{ $customer->company ?? '' }}"
                                                        data-address="{{ $customer->address ?? '' }}"
                                                        data-area="{{ $customer->area ?? '' }}"
                                                        data-branch-name="{{ optional($customer->branch)->branch_name ?? '—' }}"
                                                        data-search-text="{{ strtolower($vehicleText . ' ' . $vehicle->plate_number) }}">
                                                    {{ $vehicleText }}
                                                </option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" id="edit-party-btn" style="border-radius: 0 6px 6px 0; border-left: 0;" title="Edit Customer">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                </div>
                                    @error('customer_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">MOBILE NO.</label>
                                <div class="input-group">
                                    <select name="customer_mobile" id="customer_mobile" class="form-control select2-mobile-search" style="border-radius: 6px 0 0 6px;">
                                        <option value="">Search Mobile Number...</option>
                                        @foreach($customers as $customer)
                                            @if(!empty($customer->phones[0]))
                                                <option value="{{ $customer->phones[0] }}" 
                                                        data-customer-id="{{ $customer->id }}"
                                                        data-name="{{ $customer->names[0] ?? '' }}"
                                                        data-phone="{{ $customer->phones[0] ?? '' }}"
                                                        data-company="{{ $customer->company ?? '' }}"
                                                        data-address="{{ $customer->address ?? '' }}"
                                                        data-area="{{ $customer->area ?? '' }}">
                                                    {{ $customer->phones[0] }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" id="edit-mobile-btn" style="border-radius: 0 6px 6px 0; border-left: 0;" title="Edit Customer">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                </div>
                                <input type="hidden" id="customer_mobile_hidden" name="customer_mobile_hidden" value="">
                                </div>
                                </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">ADDRESS</label>
                                <input type="text" id="customer_address" name="customer_address" class="form-control" placeholder="Shop/House #" style="border-radius: 6px;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">AREA</label>
                                <input type="text" id="customer_area" name="customer_area" class="form-control" placeholder="Location/City" style="border-radius: 6px;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">REFERENCE</label>
                                <input type="text" name="reference" id="reference" class="form-control" placeholder="Enter reference number" style="border-radius: 6px;">
                            </div>
                        </div>
                        
                        <!-- Items Summary Section (Like Screenshot) -->
                        <div class="mb-4">
                            <!-- Quick Barcode Scan (above items list) -->
                            <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background: #f8f9fa; border: 1px solid #dee2e6;">
                                <i class="ti ti-barcode ms-2" style="font-size: 1.25rem; color: #2563eb;"></i>
                                <input type="text" id="quick-barcode-input" class="form-control" placeholder="USB barcode scanner or type here, then Enter..." style="border-radius: 8px; border: 1px solid #dee2e6;" autocomplete="off">
                                <button type="button" class="btn btn-outline-primary flex-shrink-0" id="quick-scan-camera-btn" title="Open camera to scan">
                                    <i class="ti ti-scan"></i> Scan
                                </button>
                            </div>
                            <div id="items-summary-container" class="text-center py-5" style="background: #fff; border-radius: 8px; min-height: 200px; border: 1px dashed #dee2e6;">
                                <div id="empty-items-state">
                                    <p class="text-muted mb-0" style="font-size: 16px;">No items added yet...</p>
                                </div>
                                <div id="items-list" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead id="salesItemsThead">
                                                <tr>
                                                    <th>Warehouse</th>
                                                    <th>Item</th>
                                                    <th>Rate</th>
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
                            
                        <!-- Horizontal Line Separator -->
                        <hr style="border-top: 1px dashed #dee2e6; margin: 20px 0;">

                        <!-- Amount Summary (Like Screenshot) -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold text-dark">TOTAL ITEMS AMOUNT</span>
                                    <span class="fw-bold text-dark" id="gross-amount">Rs 0</span>
                                </div>
                                
                                <!-- DISCOUNT (MANUAL EDIT) - Green Bar -->
                                <div class="d-flex justify-content-between align-items-center p-3 mb-3 rounded" style="background: #d1f2eb; border: 1px solid #a3e4d7;">
                                    <span class="fw-bold text-success">DISCOUNT (MANUAL EDIT)</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-success fw-bold">Rs</span>
                                        <input type="number" name="discount" id="discount" class="form-control discount-amount-input" step="1" min="0" value="0" style="width: 140px; text-align: right; border: 1px solid #a3e4d7; background: #fff; font-weight: bold; font-size: 1.25rem; color: #0d9488;">
                                    </div>
                                </div>
                                
                                <!-- NET PAYABLE - Blue Bar -->
                                <div class="d-flex justify-content-between align-items-center p-3 mb-3 rounded" style="background: #dbeafe; border: 1px solid #93c5fd;">
                                    <span class="fw-bold text-primary">NET PAYABLE</span>
                                    <span class="fw-bold text-primary" id="net-payable" style="font-size: 18px;">Rs 0</span>
                                </div>

                                <!-- CASH RECEIVED - Below NET PAYABLE (photo + add more) -->
                                <div class="mt-3 p-3 rounded" id="cash-received-block" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <h6 class="fw-bold mb-3 text-primary"><i class="ti ti-cash me-1"></i>CASH RECEIVED</h6>
                                    <div id="cash-received-entries">
                                        <div class="cash-received-row mb-3 p-3 rounded position-relative" style="background: #fff; border: 1px solid #e2e8f0;">
                                            <button type="button" class="btn btn-sm btn-outline-danger cash-row-close position-absolute" style="top: 8px; right: 8px;" title="Remove"><i class="ti ti-x"></i></button>
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                                <label class="form-label fw-bold mb-0">Amount (Rs)</label>
                                                <input type="number" name="cash_received[]" class="form-control cash-amount-input" step="1" min="0" value="0" style="width: 140px; text-align: right;" placeholder="0">
                                            </div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-primary attach-cash-photo-btn"><i class="ti ti-camera me-1"></i>Attach Photo</button>
                                                <input type="file" class="d-none cash-photo-input" accept="image/*" name="cash_photos[]">
                                                <div class="cash-photo-preview d-flex align-items-center d-none">
                                                    <img class="img-thumbnail cash-photo-img" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                                                    <button type="button" class="btn btn-sm btn-danger ms-2 remove-cash-photo"><i class="ti ti-x"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary w-100" id="add-more-cash-received-btn" style="background: #dbeafe; color: #2563eb; border: 1px solid #93c5fd;">
                                        <i class="ti ti-plus me-1"></i>+ ADD MORE CASH
                                    </button>
                                </div>

                                <!-- BANK RECEIVED - Cash block jaisa design, modal se multiple payments -->
                                <div class="mt-3 p-3 rounded" id="bank-received-block" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <h6 class="fw-bold mb-3" style="color: #9333ea;"><i class="ti ti-building-bank me-1"></i>BANK RECEIVED</h6>
                                    <div id="bank-payments-list" class="mb-3"></div>
                                    <button type="button" class="btn btn-primary w-100" id="add-bank-below-btn" style="background: #f3e8ff; color: #9333ea; border: 1px solid #c084fc;">
                                        <i class="ti ti-plus me-1"></i>+ ADD BANK PAYMENT
                                    </button>
                                </div>
                                    </div>
                                </div>
                                
                        <!-- Payment Section (Like Screenshot) -->
                        <div class="row mb-4" id="payment-section" style="display: none;">
                            <!-- CASH RECEIVED Section -->
                            <div class="col-md-6 mb-4">
                                <div class="card" style="border: 1px solid #dee2e6;">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">CASH RECEIVED</h6>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label fw-bold mb-0">CASH ENTRY</label>
                                                <input type="number" name="cash_entry" id="cash_entry" class="form-control" step="0.01" min="0" value="0" style="width: 150px; text-align: right;" placeholder="RS 0">
                                        </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="attach-cash-photo" style="font-size: 12px;">
                                                <i class="ti ti-camera me-1"></i>ATTACH PHOTO
                                            </button>
                                            <input type="file" name="cash_photo" id="cash_photo" class="d-none" accept="image/*">
                                            <div id="cash_photo_preview" class="mt-2" style="display: none;">
                                                <img id="cash_photo_preview_img" src="" alt="Cash Photo Preview" class="img-thumbnail" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                                <button type="button" class="btn btn-sm btn-danger mt-2" id="remove_cash_photo">
                                                    <i class="ti ti-x"></i> Remove
                                                </button>
                                            </div>
                                            </div>
                                        <button type="button" class="btn btn-primary w-100" id="add-more-cash-btn" style="background: #dbeafe; color: #2563eb; border: 1px solid #93c5fd;">
                                            <i class="ti ti-plus me-1"></i>+ ADD MORE CASH RECEIVED
                                        </button>
                                    </div>
                                    </div>
                                </div>
                                
                            <!-- BANK RECEIVED Section -->
                            <div class="col-md-6 mb-4">
                                <div class="card" style="border: 1px solid #dee2e6;">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">BANK RECEIVED</h6>
                                        <button type="button" class="btn btn-primary w-100" id="add-bank-payment-btn" style="background: #f3e8ff; color: #9333ea; border: 1px solid #c084fc;">
                                            <i class="ti ti-plus me-1"></i>+ ADD BANK PAYMENT
                                        </button>
                                    </div>
                                </div>
                                </div>
                                
                            <!-- CURRENT REMAINING and PREVIOUS BALANCE -->
                            <div class="col-12">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-warning">CURRENT REMAINING</span>
                                            <span class="fw-bold text-warning" id="current-remaining" style="font-size: 18px;">Rs 0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background: #fff3cd; border: 1px solid #ffc107;">
                                            <span class="fw-bold text-dark">PREVIOUS BALANCE</span>
                                            <input type="number" name="previous_balance" id="previous-balance-input" class="form-control" step="0.01" min="0" value="0" style="width: 150px; text-align: right; border: none; background: transparent; font-weight: bold;" placeholder="RS 0">
                                        </div>
                                    </div>
                                        </div>
                                    </div>
                                </div>
                                
                        <!-- Hidden payment fields -->
                        <input type="hidden" name="payment_method_id" id="payment_method_id" value="">
                        <input type="hidden" name="payment_amount" id="payment_amount" value="0">
                        <input type="hidden" name="bank_account_id" id="bank_account_id" value="">
                        <input type="hidden" name="payment_transaction_id" id="payment_transaction_id" value="">
                        <input type="hidden" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="payment_notes" id="payment_notes" value="">

                        <!-- Bottom Section (Like Screenshot) -->
                        <!-- TOTAL FINAL BALANCE Bar -->
                        <div class="mb-4 p-3 rounded" style="background: #1e3a8a; color: white;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                    <h5 class="mb-0 fw-bold" style="color: #fff; font-weight: 700;">TOTAL FINAL BALANCE</h5>
                                    <p class="mb-0" style="font-size: 14px; opacity: 0.9;" id="items-count">0 Items Listed</p>
                                        </div>
                                        <div class="text-end">
                                    <span class="me-2" style="font-size: 14px;">PKR</span>
                                    <span class="fw-bold" style="font-size: 24px;" id="total-final-balance">0</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Large ADD SALE ITEM Button -->
                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-primary btn-lg w-100" id="add-new-item-btn" style="padding: 20px; font-size: 18px; font-weight: bold;">
                                <i class="ti ti-plus me-2"></i>ADD SALE ITEM
                            </button>
                        </div>

                        <!-- Action Buttons Grid (Like Screenshot) -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn w-100" id="return-entry-btn" style="background: #fce7f3; color: #ec4899; border: 1px solid #f9a8d4; padding: 15px; font-weight: bold;">
                                    <i class="ti ti-arrow-back-up me-2"></i>RETURN
                            </button>
                        </div>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn w-100" id="claim-entry-btn" style="background: #fef3c7; color: #f59e0b; border: 1px solid #fcd34d; padding: 15px; font-weight: bold;">
                                    <i class="ti ti-tag me-2"></i>CLAIM
                            </button>
                        </div>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn w-100" id="scrap-in-btn" style="background: #fed7aa; color: #ea580c; border: 1px solid #fdba74; padding: 15px; font-weight: bold;">
                                    <i class="ti ti-recycle me-2"></i>SCRAP IN
                            </button>
                        </div>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn w-100" id="scrap-sale-btn" style="background: #d1fae5; color: #059669; border: 1px solid #6ee7b7; padding: 15px; font-weight: bold;">
                                    <i class="ti ti-file-text me-2"></i>SCRAP SALE
                        </button>
                    </div>
                </div>

                        <!-- Bottom Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <button type="button" class="btn" id="delivery-entry-btn" style="background: #ea580c; color: white; border: none; padding: 12px 20px; font-weight: bold;">
                                    <i class="ti ti-truck me-2"></i>DELIVERY ENTRY
                        </button>
                                <button type="button" class="btn btn-outline-secondary ms-2" style="padding: 12px 20px;">
                                    <i class="ti ti-share"></i>
                        </button>
                            </div>
                            <div>
                                <a href="{{ route('all_sales') }}" class="btn btn-secondary me-2" style="padding: 12px 30px;">Cancel</a>
                                <button type="submit" class="btn btn-success" id="submit-sale-btn" style="padding: 12px 30px; font-weight: bold;">
                                    <i class="ti ti-check me-1"></i> Save Sale
                        </button>
                    </div>
                </div>

                        <!-- Hidden fields for order tax, shipping -->
                        <input type="hidden" name="order_tax" id="order_tax" value="0">
                        <input type="hidden" name="shipping" id="shipping" value="0">
                        <input type="hidden" name="status" value="pending">
                    </form>
                            </div>
                            </div>
                            </div>
    </div>
</div>

<!-- Add Item Modal - ITEM DETAIL BOX -->
<div class="modal fade" id="add-item-modal" tabindex="-1" aria-hidden="true" style="z-index: 9999 !important; pointer-events: auto !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="pointer-events: auto !important;">
        <div class="modal-content" style="border-radius: 12px; pointer-events: auto !important;">
            <div class="modal-header border-0 pb-2">
                <h5 class="modal-title fw-bold modal-title--sale" id="add-item-modal-title">
                    <i class="ti ti-shopping-cart me-2"></i>ITEM DETAILS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <!-- Barcode (same as purchase modal) -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="ti ti-barcode me-1"></i>Barcode</label>
                    <div class="d-flex gap-2">
                        <input type="text" id="barcode-scan-input" class="form-control" placeholder="Barcode scan karein ya type karein..." autocomplete="off">
                        <button type="button" class="btn btn-outline-primary flex-shrink-0" id="open-camera-scan-btn" title="Camera se barcode scan karein"><i class="ti ti-camera me-1"></i> Camera</button>
                    </div>
                </div>
                <!-- Product Name (same style as purchase modal) -->
                <div class="mb-3" id="item-search-wrapper">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="ti ti-search me-1"></i>Product name</label>
                    <div class="d-flex align-items-start gap-2">
                        <div class="position-relative flex-grow-1">
                        <input type="text" id="item-search" class="form-control item-search-input text-uppercase" placeholder="Search item… or barcode" autocomplete="off" title="Type to search or edit product name">
                        <i class="ti ti-search position-absolute item-search-icon" style="right: 16px; top: 50%; transform: translateY(-50%); font-size: 18px; pointer-events: none;"></i>
                        <!-- Search Results Dropdown -->
                        <div id="item-search-results" class="position-absolute w-100 item-search-results-box" style="top: 100%; left: 0; z-index: 1050; max-height: 320px; overflow-y: auto; display: none; margin-top: 8px;">
                            </div>
                        <!-- Selected Item Details (same box style as purchase) -->
                        <div id="selected-item-details-display" class="mt-2 d-none rounded border px-2 py-2 d-flex align-items-center justify-content-between gap-2" style="font-size: 0.85rem; background: linear-gradient(135deg, #f8f9fc 0%, #f0f2f8 100%);">
                            <div class="flex-grow-1 min-width-0">
                                <div class="small text-uppercase fw-semibold text-secondary mb-1" style="font-size: 10px;">Product detail</div>
                                <div class="small text-muted mt-1 mb-1 text-uppercase" id="selected-item-details-line1"></div>
                                <div class="small text-primary fw-semibold mt-1 mb-1" id="selected-item-details-line2" style="display: none;"></div>
                                <div class="text-primary small fw-semibold mt-1" id="selected-item-details-line3"></div>
                            </div>
                            <button type="button" class="btn btn-primary flex-shrink-0" id="item-edit-in-modal-btn" title="Edit selected item" style="display: none; white-space: nowrap;">
                                <i class="ti ti-edit"></i> Edit
                            </button>
                        </div>
                        </div>
                        <!-- Item Image Preview -->
                        <div id="item-search-image-preview" class="d-none" style="flex-shrink: 0;">
                            <img id="item-search-image" src="" alt="Item Image" class="rounded border shadow-sm" style="width: 52px; height: 52px; object-fit: cover;">
                            <div id="item-search-stock" class="text-center mt-1" style="font-size: 0.75rem; font-weight: 600;"></div>
                            <div id="item-search-warehouse" class="text-center mt-0 d-none" style="font-size: 0.65rem; color: #6c757d;"></div>
                        </div>
                    </div>
                    <input type="hidden" id="selected-item-id">
                    <input type="hidden" id="selected-warehouse-id">
                </div>
                
                <!-- Stock status (same label style as purchase: Available stock) -->
                <div id="stock-status-section" class="mb-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0" id="stock-status-section-label">
                            <i class="ti ti-package me-2"></i>Available stock
                        </label>
                    </div>
                    <div id="stock-status-content" class="border rounded p-2" style="background-color: #f8f9fa; max-height: 200px; overflow-y: auto;">
                        <div id="stock-status-list">
                            <p class="text-muted mb-0 small text-center">Loading stock status...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quantity and Unit Row -->
                <input type="hidden" id="item-liter-per-can" value="">
                <div class="row mb-3" id="quantity-row-normal">
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2"><i class="ti ti-shopping-cart me-1"></i>Quantity</label>
                        <input type="number" id="item-quantity" class="form-control" value="1" min="0" step="any" placeholder="Quantity" style="background-color: #f8f9fa; border-radius: 8px;">
                        <small class="text-muted" style="font-size: 11px;">Enter quantity</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">Unit</label>
                        <select id="item-unit" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
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
                    </div>
                </div>
                <input type="hidden" id="item-quantity-cans" value="0">
                <input type="hidden" id="item-quantity-liters" value="0">

                <!-- Sale Rate and Warranty Row -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2"><i class="ti ti-shopping-cart me-1"></i>Sale rate</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rs</span>
                            <input type="number" id="item-rate" class="form-control" value="0" step="1" min="0" placeholder="0" style="background-color: #f8f9fa; border-radius: 8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold mb-2">Warranty</label>
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

                <!-- Customer History Section -->
                <div class="mb-3" id="customer-history-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0 d-flex align-items-center">
                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff;">
                                <i class="ti ti-history" style="font-size: 12px;"></i>
                            </span>
                            CUSTOMER HISTORY
                        </label>
                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-success" id="hold-rate-link" style="display: none; font-size: 11px;">
                            <i class="ti ti-check me-1"></i>Apply Last Rate
                        </a>
                    </div>
                    <div id="customer-history-content" class="p-3 customer-history-box" style="min-height: 80px; max-height: 200px; overflow-y: auto;">
                        <p class="text-muted mb-0 small text-center">Select item to view customer history</p>
                    </div>
                </div>

                <!-- Last 5 Purchase History (below customer history) -->
                <div class="mb-3" id="purchase-history-section">
                    <label class="form-label fw-bold mb-2 d-flex align-items-center">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff;">
                            <i class="ti ti-shopping-cart" style="font-size: 12px;"></i>
                        </span>
                        LAST 5 PURCHASE HISTORY
                    </label>
                    <div id="purchase-history-content" class="p-3 customer-history-box" style="min-height: 60px; max-height: 180px; overflow-y: auto;">
                        <p class="text-muted mb-0 small text-center">Select item to view purchase history</p>
                    </div>
                </div>

                <!-- SUPPLIER SELECTION (Shows when stock is 0) -->
                <div id="supplier-selection-section" class="mb-3" style="display: none;">
                    <label class="form-label fw-bold mb-2 d-flex align-items-center">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff;">
                            <i class="ti ti-truck" style="font-size: 12px;"></i>
                        </span>
                        SELECT SUPPLIER (Stock is 0)
                    </label>
                    <select name="item_supplier_id" id="item_supplier_id" class="form-control" style="border-radius: 6px;">
                        <option value="">Select Supplier...</option>
                        @if(isset($suppliers))
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" 
                                        data-name="{{ $supplier->names[0] ?? '' }}" 
                                        data-phone="{{ $supplier->phones[0] ?? '' }}"
                                        data-company="{{ $supplier->company ?? '' }}">
                                    {{ $supplier->names[0] ?? 'N/A' }}@if($supplier->company) - {{ $supplier->company }}@endif @if(!empty($supplier->phones[0])) - {{ $supplier->phones[0] }}@endif
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <small class="text-muted d-block mt-1">
                        <i class="ti ti-info-circle me-1"></i>Selecting a supplier will mark this sale as <strong>PENDING</strong> and stock will <strong>NOT</strong> be updated.
                    </small>
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
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 10px 24px;">Cancel</button>
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

<!-- Vehicle Modal -->
<div class="modal fade" id="vehicle-modal" tabindex="-1" aria-hidden="true" style="z-index: 9999 !important; pointer-events: auto !important;">
    <div class="modal-dialog modal-dialog-centered" style="pointer-events: auto !important;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; pointer-events: auto !important;">
            <!-- Blue Header -->
            <div class="modal-header border-0 pb-0" style="background: #0d6efd; padding: 15px 20px;">
                <h5 class="modal-title fw-bold text-white text-uppercase mb-0" style="font-size: 16px; letter-spacing: 0.5px;">
                    VEHICLE DETAILS
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 1;"></button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <form id="vehicle-form">
                <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">REG # / NUMBER</label>
                        <input type="text" id="vehicle-plate-number" class="form-control" placeholder="PLATE # (LEC-22-1234)" style="text-transform: uppercase; border-radius: 8px; padding: 12px; border: 1px solid #dee2e6;" required>
                </div>
                <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">MAKE / BRAND</label>
                        <input type="text" id="vehicle-make" class="form-control" placeholder="Toyota, Honda..." style="border-radius: 8px; padding: 12px; border: 1px solid #dee2e6;" required>
                </div>
                <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">MODEL / NAME</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="vehicle-model" class="form-control flex-grow-1" placeholder="Civic, Corolla..." style="border-radius: 8px; padding: 12px; border: 1px solid #dee2e6;" required>
                            <button type="button" class="btn" id="add-model-btn" style="background: #0d6efd; color: white; border-radius: 8px; width: 50px; height: 50px; padding: 0; display: flex; align-items: center; justify-content: center; border: none;">
                                <i class="ti ti-plus" style="font-size: 20px;"></i>
                </button>
                    </div>
                </div>
                <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">MODEL YEAR</label>
                        <input type="text" id="vehicle-year" class="form-control" placeholder="e.g. 2022" style="border-radius: 8px; padding: 12px; border: 1px solid #dee2e6;" required>
                </div>
                </form>
                </div>
            <div class="modal-footer border-0 pt-0 pb-3 px-3" style="flex-direction: column; gap: 10px;">
                <button type="button" class="btn btn-primary fw-bold w-100" id="save-vehicle-btn" style="background-color: #0d6efd; border-radius: 8px; padding: 14px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; border: none;">
                    SAVE & APPLY
                </button>
                <button type="button" class="btn btn-outline-primary fw-bold w-100" id="save-add-another-btn" style="border: 2px solid #0d6efd; color: #0d6efd; background: white; border-radius: 8px; padding: 14px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                    SAVE & ADD ANOTHER
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Vehicle Edit modal must open on top of Edit Customer modal when both are open */
    #editVehicleSaleModal.modal { z-index: 1065 !important; }
    body.modal-open #editVehicleSaleModal.modal.show { z-index: 1065 !important; }
    /* Ensure backdrop for vehicle edit is above customer edit modal (Bootstrap appends backdrops to body) */
    .modal-backdrop.show:last-of-type { z-index: 1060 !important; }
    /* Ensure add-item-modal is clickable on create/sale/new - move to body via JS */
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
    #add-item-modal .modal-footer,
    #add-item-modal .modal-header,
    #add-item-modal .modal-footer button,
    #add-item-modal .modal-header .btn-close {
        pointer-events: auto !important;
    }
    /* Keep footer above body so buttons are always clickable */
    #add-item-modal .modal-footer {
        position: relative;
        z-index: 10;
        flex-shrink: 0;
    }
    body.modal-open .modal-backdrop { z-index: 9998 !important; }
    /* Modal title color (match purchase modal) */
    #add-item-modal-title.modal-title--sale { color: #0d6efd; }
    /* Camera barcode modal when opened from add-item modal must be on top */
    #camera-barcode-modal.modal.show { z-index: 10000 !important; }
    #camera-barcode-modal .modal-dialog,
    #camera-barcode-modal .modal-content { pointer-events: auto !important; }
    /* Ensure all modals and their content are clickable when shown */
    .modal.show .modal-dialog,
    .modal.show .modal-content,
    .modal.show .modal-body,
    .modal.show .modal-footer,
    .modal.show .modal-header,
    .modal.show button,
    .modal.show input,
    .modal.show select { pointer-events: auto !important; }
    /* Vehicle modal - same clickable fix as add-item-modal (above backdrop 9998) */
    #vehicle-modal.modal,
    body.modal-open #vehicle-modal.modal.show {
        z-index: 9999 !important;
        pointer-events: auto !important;
    }
    #vehicle-modal .modal-dialog,
    #vehicle-modal .modal-content,
    #vehicle-modal .modal-body,
    #vehicle-modal .modal-footer,
    #vehicle-modal .modal-header,
    #vehicle-modal button,
    #vehicle-modal input,
    #vehicle-modal select { pointer-events: auto !important; }
    /* Delivery modal - same clickable fix (above backdrop 9998) */
    #delivery-modal.modal,
    body.modal-open #delivery-modal.modal.show {
        z-index: 9999 !important;
        pointer-events: auto !important;
    }
    #delivery-modal .modal-dialog,
    #delivery-modal .modal-content,
    #delivery-modal .modal-body,
    #delivery-modal .modal-footer,
    #delivery-modal .modal-header,
    #delivery-modal button,
    #delivery-modal input,
    #delivery-modal select,
    #delivery-modal label { pointer-events: auto !important; }
</style>
<!-- Edit Vehicle Modal (sales - edit existing vehicle from vehicles-list) -->
<div class="modal fade" id="editVehicleSaleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold">Edit Vehicle</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editVehicleSaleForm">
                <input type="hidden" id="editVehicleSaleId" value="">
                <div class="modal-body" style="padding: 20px;">
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">REG # / NUMBER</label>
                        <input type="text" id="editVehicleSalePlate" class="form-control" placeholder="PLATE #" style="text-transform: uppercase; border-radius: 8px; padding: 12px; border: 1px solid #dee2e6;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">MAKE / BRAND</label>
                        <input type="text" id="editVehicleSaleMake" class="form-control" placeholder="Toyota, Honda..." style="border-radius: 8px; padding: 12px; border: 1px solid #dee2e6;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">MODEL / NAME</label>
                        <input type="text" id="editVehicleSaleModel" class="form-control" placeholder="Civic, Corolla..." style="border-radius: 8px; padding: 12px; border: 1px solid #dee2e6;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">MODEL YEAR</label>
                        <input type="text" id="editVehicleSaleYear" class="form-control" placeholder="e.g. 2022" style="border-radius: 8px; padding: 12px; border: 1px solid #dee2e6;" required maxlength="4">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-3 px-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Update Vehicle</button>
                </div>
            </form>
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
                <div id="camera-barcode-reader" style="width: 100%; height: 280px; border-radius: 8px; overflow: hidden; background: #000;"></div>
                <p class="small text-muted mb-0 mt-2 text-center">Point camera at barcode or QR code</p>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Entry Modal -->
<div class="modal fade" id="delivery-modal" tabindex="-1" aria-hidden="true" style="z-index: 9999 !important; pointer-events: auto !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="pointer-events: auto !important;">
        <div class="modal-content" style="border-radius: 12px; pointer-events: auto !important;">
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

@push('styles')
<style>
    /* Hide up/down arrow buttons on cash amount number input */
    .cash-amount-input::-webkit-outer-spin-button,
    .cash-amount-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .cash-amount-input {
        -moz-appearance: textfield;
    }
    /* Stock warehouse row: Display + black unit tags + 1 Can + 3 number inputs (screenshot design) */
    #stock-status-list .stock-warehouse-item .stock-warehouse-qty-input::-webkit-outer-spin-button,
    #stock-status-list .stock-warehouse-item .stock-warehouse-qty-input::-webkit-inner-spin-button,
    #stock-status-list .stock-warehouse-item .stock-warehouse-base-qty-input::-webkit-outer-spin-button,
    #stock-status-list .stock-warehouse-item .stock-warehouse-base-qty-input::-webkit-inner-spin-button,
    #stock-status-list .stock-warehouse-item .stock-warehouse-extra-input::-webkit-outer-spin-button,
    #stock-status-list .stock-warehouse-item .stock-warehouse-extra-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    #stock-status-list .stock-warehouse-item .stock-warehouse-qty-input,
    #stock-status-list .stock-warehouse-item .stock-warehouse-base-qty-input,
    #stock-status-list .stock-warehouse-item .stock-warehouse-extra-input {
        -moz-appearance: textfield;
    }
    #stock-status-list .stock-warehouse-item {
        background-color: #e9ecef !important;
        cursor: pointer;
        min-height: 42px;
        color: #212529 !important;
        border: 1px solid #dee2e6 !important;
    }
    #stock-status-list .stock-warehouse-item .stock-warehouse-qty-labels {
        color: #495057 !important;
    }
    #stock-status-list .stock-warehouse-item.bg-primary {
        background-color: #0d6efd !important;
        color: #fff !important;
        border: 1px solid #0a58ca !important;
    }
    #stock-status-list .stock-warehouse-item.bg-primary .stock-warehouse-qty-labels {
        color: #fff !important;
    }
    /* Black unit tags (4 L PER CAN, 4 Liter) */
    #stock-status-list .stock-warehouse-item .warehouse-unit-tag {
        background-color: #000 !important;
        color: #fff !important;
        border: none;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    #stock-status-list .stock-warehouse-item .stock-warehouse-qty-input,
    #stock-status-list .stock-warehouse-item .stock-warehouse-base-qty-input {
        background-color: #fff !important;
        color: #212529 !important;
        border-radius: 6px;
        width: 56px;
        text-align: center;
    }
    #stock-status-list .stock-warehouse-item .stock-warehouse-extra-input {
        background-color: #fff !important;
        color: #212529 !important;
        border-radius: 6px;
        width: 56px;
        text-align: center;
    }
    #stock-status-list .stock-warehouse-item.bg-primary .stock-warehouse-qty-input,
    #stock-status-list .stock-warehouse-item.bg-primary .stock-warehouse-base-qty-input {
        background-color: rgba(255,255,255,0.95) !important;
        color: #212529 !important;
        border: 1px solid rgba(0,0,0,0.15);
    }
    #stock-status-list .stock-warehouse-item.bg-primary .stock-warehouse-extra-input {
        background-color: rgba(255,255,255,0.95) !important;
        color: #212529 !important;
        border: 1px solid rgba(0,0,0,0.15);
    }
    /* Branch row */
    #stock-status-list .stock-branch-item {
        background-color: #fff !important;
        min-height: 42px;
    }
    .edit-name-display .name-first-word { font-size: 0.875rem; font-weight: 600; color: #212529; }
    .edit-name-display .name-rest { font-size: 0.875rem; color: #212529; font-weight: 400; }
    .edit-name-display:empty::before { content: attr(data-placeholder); color: #6c757d; }
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
    
    /* ========== Customer History Styling ========== */
    .customer-history-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 12px;
    }
    
    .customer-history-item {
        transition: all 0.2s ease;
        border-radius: 6px;
        padding: 8px !important;
        margin: 2px 0;
    }
    
    .customer-history-item:hover {
        background: rgba(16, 185, 129, 0.1) !important;
        transform: translateX(4px);
    }
    
    .customer-history-summary {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
        padding: 10px;
    }
</style>
@endpush

@push('scripts')
<script>
window.customerBranchNames = @json(collect($customers)->keyBy('id')->map(function($c) { return optional($c->branch)->branch_name ?? '—'; })->toArray());
</script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
$(document).ready(function() {
    let salesItems = [];
    let itemCounter = 0;
    // Entry type: 'sale' (default) or 'scrap' - same modal as Smart Invoice Scrap In
    let currentEntryType = 'sale';
    
    /* Move add-item-modal to body so it is clickable (fixes no-click on create/sale/new) */
    function moveAddItemModalToBody() {
        var $modal = $('#add-item-modal');
        if ($modal.length) {
            $modal.appendTo('body');
            $modal.css({ 'z-index': 9999, 'pointer-events': 'auto' });
            $modal.find('.modal-dialog, .modal-content, .modal-body, .modal-footer, .modal-header').css('pointer-events', 'auto');
            $modal.find('button, input, select, a.btn').css('pointer-events', 'auto');
        }
    }
    moveAddItemModalToBody();
    window.addEventListener('load', moveAddItemModalToBody);
    
    // ---------- Sales items array ----------
    // Helper to clean item name (remove Lorem Ipsum or dummy text)
    function cleanItemName(name, itemId) {
        if (!name) return 'Item #' + itemId;
        const lower = name.toLowerCase();
        if (lower.indexOf('lorem') !== -1 || lower.indexOf('dummy') !== -1 || lower.indexOf('simply') !== -1 || name.length > 150) {
            return 'Item #' + itemId;
        }
        return name.length > 80 ? name.substring(0, 77) + '...' : name;
    }
    
    // Initialize items array
    salesItems = [];
    
    // On page load, update empty state hint based on branch selection
    const initialBranchId = $('#salesBranchId').val();
    if (initialBranchId) {
        $('#empty-state-hint').text('Click "Add Item" to add items to cart');
        } else {
        $('#empty-state-hint').text('Select a branch first, then add items');
    }

    // ========== Item Search is handled in the add-item-modal ==========
    // Search functionality is already implemented in the #item-search input within #add-item-modal

    // Edit button handlers - open customer edit modal
    $(document).on('click', '#edit-party-btn, #edit-mobile-btn', function() {
        let customerId = $('#customer_id').val();
        if (!customerId && $('#customer_mobile').length) {
            const selected = $('#customer_mobile').find('option:selected');
            if (selected.length && selected.val()) {
                customerId = selected.data('customer-id');
            }
        }
        if (!customerId) {
            Swal.fire({
                icon: 'warning',
                title: 'No Customer Selected',
                text: 'Please select a customer first (by name or mobile) before editing.',
                confirmButtonText: 'OK'
            });
            return;
        }
        loadCustomerForEdit(customerId);
    });
    
    // Function to load customer data and open edit modal
    function loadCustomerForEdit(customerId) {
        // Show loading
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fetch customer data via AJAX (Laravel route for correct base URL)
        const editDataUrl = '{{ route("customers.edit.data", ["id" => "__ID__"]) }}'.replace('__ID__', encodeURIComponent(customerId));
        $.ajax({
            url: editDataUrl,
            method: 'GET',
            success: function(response) {
                Swal.close();
                if (response.success && response.customer) {
                    // Populate modal with customer data
                    populateEditModal(response.customer);
                    // Open modal
                    $('#editCustomerModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load customer data.',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                // If route doesn't exist, try alternative approach
                if (xhr.status === 404) {
                    // Redirect to customers page as fallback
                    const baseUrl = window.location.origin;
                    const customersUrl = baseUrl + '/all/customers';
                    window.location.href = customersUrl;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load customer data.',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    }
    
    // Helpers for name display: first word large + capital (pehla lafaz capital), rest small
    function escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function capitaliseFirstWord(w) {
        if (!w) return '';
        const s = String(w).trim();
        return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
    }
    // Name format: Pehla word Capital, baaki small (e.g. Bilal ahmed khan)
    function nameWithFirstWordCapital(text) {
        if (!text || !String(text).trim()) return '';
        const t = String(text).trim();
        const i = t.indexOf(' ');
        if (i === -1) return capitaliseFirstWord(t);
        const rest = t.slice(i + 1).toLowerCase();
        return capitaliseFirstWord(t.slice(0, i)) + (rest ? ' ' + rest : '');
    }
    function formatNameFirstWordLarge(text) {
        if (!text || !String(text).trim()) return '';
        const t = String(text).trim();
        const i = t.indexOf(' ');
        if (i === -1) return '<span class="name-first-word">' + escapeHtml(capitaliseFirstWord(t)) + '</span>';
        const firstWord = capitaliseFirstWord(t.slice(0, i));
        const rest = t.slice(i + 1).toLowerCase();
        return '<span class="name-first-word">' + escapeHtml(firstWord) + '</span> <span class="name-rest">' + escapeHtml(rest) + '</span>';
    }
    function getDisplayText(el) { return (el && (el.textContent || el.innerText || '').replace(/\s+/g, ' ').trim()) || ''; }

    // Base URL for form actions (avoids "Not Found" when app is in subfolder e.g. /trader/public)
    const customersUpdateUrlTemplate = '{{ url(route("customers.update", ["customer" => "__ID__"])) }}';
    // Function to populate edit modal with customer data
    function populateEditModal(customer) {
        // Update form action URL with customer ID (use full URL so submit works in subfolder)
        const customerId = $('#customer_id').val();
        $('#editCustomerForm').attr('action', customersUpdateUrlTemplate.replace('__ID__', customerId));
        
        const firstName = (customer.names && Array.isArray(customer.names) && customer.names.length > 0) ? (customer.names[0] || '') : (customer.names || '');
        $('#edit_customer_names').val(firstName);
        $('#edit_customer_names_display').html(formatNameFirstWordLarge(firstName));
        
        if (customer.phones && Array.isArray(customer.phones) && customer.phones.length > 0) {
            $('#edit_customer_phones').val(customer.phones[0] || '');
        } else if (customer.phones) {
            $('#edit_customer_phones').val(customer.phones || '');
        }
        
        $('#edit_customer_company').val(customer.company || '');
        $('#edit_customer_email').val(customer.email || '');
        $('#edit_customer_address').val(customer.address || '');
        $('#edit_customer_area').val(customer.area || '');
        if ($('#edit_customer_branch_id').length) {
            $('#edit_customer_branch_id').val(customer.branch_id || '');
        }
        
        // Display existing profile image below (preview neeche)
        if (customer.profile_img) {
            const imgUrl = customer.profile_img.startsWith('http') ? customer.profile_img : '/' + customer.profile_img;
            $('#edit_profile_img_display').attr('src', imgUrl);
            $('#edit_profile_img_preview').show();
        } else {
            $('#edit_profile_img_preview').hide();
        }
        
        // Display existing visiting doc below (preview neeche)
        $('#edit_visiting_doc_preview').empty();
        if (customer.visiting_doc) {
            const docUrl = customer.visiting_doc.startsWith('http') ? customer.visiting_doc : '/' + customer.visiting_doc;
            const isImg = /\.(jpe?g|png|gif|webp)$/i.test(docUrl) || customer.visiting_doc.toLowerCase().indexOf('image') !== -1;
            if (isImg) {
                $('#edit_visiting_doc_preview').html('<img src="' + docUrl + '" alt="Document" class="img-fluid rounded" style="max-height: 180px;">').show();
            } else {
                $('#edit_visiting_doc_preview').html('<a href="' + docUrl + '" target="_blank" class="btn btn-sm btn-outline-primary">View Current Document</a>').show();
            }
        }
        
        // Clear existing additional name/phone fields and add them
        $('#edit_namePhoneContainer .name-phone-row').not(':first').remove();
        if (customer.names && Array.isArray(customer.names) && customer.names.length > 1) {
            for (let i = 1; i < customer.names.length; i++) {
                const phone = customer.phones && customer.phones[i] ? customer.phones[i] : '';
                addNamePhoneField(customer.names[i], phone);
            }
        }
        updateRemoveButtons();

        // Load and show this customer's vehicles (same card style as Add Vehicle on sales form)
        const vehiclesUrl = '{{ url(route("customer.vehicles.index", ["customer" => "__ID__"])) }}'.replace('__ID__', customerId);
        $('#edit-customer-vehicles-list').html('<span class="text-muted">Loading vehicles…</span>');
        $.get(vehiclesUrl).done(function(res) {
            const $list = $('#edit-customer-vehicles-list');
            if (res.success && res.vehicles && res.vehicles.length > 0) {
                let html = '<div class="d-flex flex-column gap-2" style="display: grid !important; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">';
                res.vehicles.forEach(function(v) {
                    const plate = (v.plateNumber || '—').toString();
                    const make = (v.make || '').toString();
                    const model = (v.model || '').toString();
                    const year = (v.year || '—').toString();
                    const carId = (v.id || '').toString();
                    const customerIdForVehicle = (v.customerId || customerId || '').toString();
                    const plateEsc = (v.plateNumber || '').toString().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const makeEsc = make.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const modelEsc = model.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const yearEsc = year.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    html += '<div class="card mb-0 edit-customer-vehicle-card position-relative" style="border: 1px solid #e0e0e0; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: #f8f9fa;">';
                    html += '<div class="card-body p-3">';
                    html += '<button type="button" class="btn btn-sm btn-outline-primary edit-vehicle-in-edit-customer-btn position-absolute top-0 end-0 m-2" style="padding: 4px 10px; z-index: 5;" title="Edit vehicle" data-car-id="' + carId + '" data-customer-id="' + customerIdForVehicle + '" data-plate="' + plateEsc + '" data-make="' + makeEsc + '" data-model="' + modelEsc + '" data-year="' + yearEsc + '"><i class="ti ti-edit me-1" style="font-size: 14px;"></i>Edit</button>';
                    html += '<p class="mb-1 fw-bold text-uppercase" style="color: #4a90e2; font-size: 11px; letter-spacing: 0.5px;">ACTIVE VEHICLE</p>';
                    html += '<h6 class="mb-1 fw-bold vehicle-card-plate" style="color: #1e3a8a; font-size: 16px;">' + plate + '</h6>';
                    html += '<p class="mb-0 fw-semibold vehicle-card-make-model" style="color: #1e3a8a; font-size: 13px;">' + (make && model ? make.toUpperCase() + ' ' + model.toUpperCase() : (make || model || '—').toString().toUpperCase()) + '</p>';
                    html += '<p class="mb-0 small text-muted mt-1 vehicle-card-year">Year: ' + year + '</p>';
                    html += '</div></div>';
                });
                html += '</div>';
                $list.html(html);
            } else {
                $list.html('<span class="text-muted">No vehicles added yet.</span>');
            }
        }).fail(function() {
            $('#edit-customer-vehicles-list').html('<span class="text-muted">No vehicles added yet.</span>');
        });
    }
    
    // Function to add more name & phone fields (name: first word large, rest small)
    function addNamePhoneField(name = '', phone = '') {
        const fieldHtml = `
            <div class="row g-3 mb-3 align-items-end name-phone-row">
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="hidden" name="names[]" class="edit-name-hidden">
                        <div contenteditable="true" class="form-control edit-name-display" data-placeholder="Enter name" style="min-height: 38px; font-size: 0.875rem;">${formatNameFirstWordLarge(name) || ''}</div>
                        <button type="button" class="btn btn-danger remove-row">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">WhatsApp Number</label>
                    <input type="text" name="phones[]" class="form-control" placeholder="Enter phone number" value="${phone || ''}">
                </div>
            </div>
        `;
        $('#edit_namePhoneContainer').append(fieldHtml);
        updateRemoveButtons();
    }
    
    // Update remove button visibility
    function updateRemoveButtons() {
        const rows = $('#edit_namePhoneContainer .name-phone-row');
        rows.each(function(index) {
            const removeBtn = $(this).find('.remove-row');
            if (rows.length > 1) {
                removeBtn.show();
            } else {
                removeBtn.hide();
            }
        });
    }
    
    // Handle "Add More Name & Phone" button
    $('#edit_add_more_name_phone').on('click', function() {
        addNamePhoneField();
    });
    
    // Handle remove row button
    $(document).on('click', '#edit_namePhoneContainer .remove-row', function() {
        $(this).closest('.name-phone-row').remove();
        updateRemoveButtons();
    });
    // Edit name display: typing/backspace normal rakhne ke liye input pe sirf sync, format sirf blur pe
    $(document).on('input', '.edit-name-display', function() {
        const text = getDisplayText(this);
        $(this).siblings('.edit-name-hidden').val(text);
    });
    $(document).on('blur', '.edit-name-display', function() {
        const text = getDisplayText(this);
        const normalised = nameWithFirstWordCapital(text);
        $(this).siblings('.edit-name-hidden').val(normalised);
        $(this).html(formatNameFirstWordLarge(text));
    });
    $('#editCustomerForm').on('submit', function() {
        $('#edit_namePhoneContainer .edit-name-display').each(function() {
            const text = getDisplayText(this);
            $(this).siblings('.edit-name-hidden').val(nameWithFirstWordCapital(text));
        });
    });
    
    // Handle profile image preview – photo neeche show
    $('#edit_profile_img').on('change', function(e) {
        const file = e.target.files[0];
        $('#edit_profile_img_preview').hide();
        if (file && file.type.indexOf('image/') === 0) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                $('#edit_profile_img_display').attr('src', ev.target.result);
                $('#edit_profile_img_preview').show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle visiting document preview – image ho to neeche photo, warna document name
    $('#edit_visiting_doc').on('change', function(e) {
        const file = e.target.files[0];
        const $preview = $('#edit_visiting_doc_preview');
        $preview.empty();
        if (file) {
            if (file.type.indexOf('image/') === 0) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    $preview.html('<img src="' + ev.target.result + '" alt="Document" class="img-fluid rounded" style="max-height: 180px;">').show();
                };
                reader.readAsDataURL(file);
            } else {
                $preview.html('<span class="text-muted small">Document selected: ' + (file.name || '') + '</span>').show();
            }
        }
    });
    
    // Customer change handler - auto-fill phone and load customer's vehicles from DB
    // Use 'select2:select' event for Select2 compatibility
    $('#customer_id').on('change select2:select', function() {
        const customerId = $(this).val();
        const selected = $(this).find('option:selected');
        const name = selected.data('name') || '';
        const phone = selected.data('phone') || '';
        const address = selected.data('address') || '';
        const area = selected.data('area') || '';
        // Use server-provided map so branch name always works (Select2 can make option data unreliable)
        const branchName = (window.customerBranchNames && customerId && window.customerBranchNames[customerId]) ? window.customerBranchNames[customerId] : (selected.attr('data-branch-name') || selected.data('branchName') || '—');
        
        // Show which branch this customer was created in
        if (customerId) {
            $('#customer-branch-name').text(branchName);
            $('#customer-branch-display').show();
        } else {
            $('#customer-branch-name').text('');
            $('#customer-branch-display').hide();
        }
        
        // Update mobile dropdown if it's a select
        if (phone && $('#customer_mobile').is('select')) {
            $('#customer_mobile').val(phone).trigger('change.select2');
            $('#customer_mobile_hidden').val(phone);
        } else if (phone) {
        $('#customer_mobile').val(phone);
        }
        $('#customer_address').val(address);
        $('#customer_area').val(area);
        
        // Load this customer's vehicles from database and show below Add Vehicle button
        if (typeof loadCustomerVehicles === 'function') {
            loadCustomerVehicles(customerId);
        }
    });
    
    // Mobile number change handler - auto-fill customer when phone is selected (for select dropdown)
    $('#customer_mobile').on('change select2:select', function() {
        const selected = $(this).find('option:selected');
        const customerId = selected.data('customer-id');
        const name = selected.data('name') || '';
        const phone = selected.data('phone') || '';
        const address = selected.data('address') || '';
        const area = selected.data('area') || '';
        
        if (customerId) {
            // Update customer dropdown
            $('#customer_id').val(customerId).trigger('change.select2');
            $('#customer_address').val(address);
            $('#customer_area').val(area);
            $('#customer_mobile_hidden').val(phone);
        } else if (phone) {
            // Just update the hidden field if phone is selected
            $('#customer_mobile_hidden').val(phone);
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
                
                // Update empty state hint (branch is now selected)
                $('#empty-state-hint').text('Click "Add Item" to add items to cart');
                
                // Load purchase cart for this branch
                loadPurchaseCart();
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
                    var whDisplay = warehouse.warehouse_name + (warehouse.warehouse_code ? ' (' + warehouse.warehouse_code + ')' : '');
                    $('#warehouseName').text(whDisplay);
                    $('#warehouseItemsCount').text(warehouse.items_count || 0);
                    $('#branchWarehouseInfo').show();
                    $('body').data('currentWarehouseName', whDisplay);
                } else {
                    $('#branchWarehouseInfo').hide();
                    $('body').removeData('currentWarehouseName');
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
        const branchId = $('#salesBranchId').val();
        if (branchId) {
            loadBranchWarehouseInfo(branchId);
        }
        
        // Start updating date/time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Sales invoice number is already set in template
    });

    // Handle "Add New Item" button click - check branch first
    $('#add-new-item-btn').on('click', function() {
        const branchId = $('#salesBranchId').val();
        
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before adding items.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        currentEntryType = 'sale';
        // Open the item details modal directly (search is inside this modal)
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

    // When item is updated inside iframe, show add-item-modal so user can add it to sale
    var pendingItemIdAfterUpdate = null;
    window.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'ITEM_UPDATED' && event.data.itemId) {
            $('#add-new-item-modal').modal('hide');
            pendingItemIdAfterUpdate = event.data.itemId;
            $('#add-item-modal-title').html('<i class="ti ti-shopping-cart me-2"></i>ITEM DETAILS');
            $('#add-item-modal').modal('show');
        }
    });

    // Quick barcode scan (above items list): open Add Item modal and run barcode search
    function openAddItemModalForQuickScan(barcodeOrCamera) {
        const branchId = $('#salesBranchId').val();
        if (!branchId) {
            Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first before adding items.', confirmButtonText: 'OK' });
            return;
        }
        currentEntryType = 'sale';
        $('#add-item-modal-title').html('<i class="ti ti-shopping-cart me-2"></i>ITEM DETAILS');
        if (barcodeOrCamera === 'camera') {
            openCameraAfterQuickScan = true;
        } else {
            pendingQuickBarcode = (barcodeOrCamera || '').trim();
        }
        $('#add-item-modal').modal('show');
    }
    // Support physical USB barcode scanner: scanner sends keys fast then Enter; read value after a short delay so full barcode is captured
    $('#quick-barcode-input').on('keydown', function(e) {
        if (e.which !== 13) return;
        e.preventDefault();
        var $input = $(this);
        setTimeout(function() {
            const barcode = $input.val().trim();
            if (barcode.length >= 2) openAddItemModalForQuickScan(barcode);
            $input.val('');
        }, 50);
    });
    var quickBarcodeInputTimeout = null;
    $('#quick-barcode-input').on('input', function() {
        const barcode = $(this).val().trim();
        clearTimeout(quickBarcodeInputTimeout);
        if (barcode.length >= 3) {
            quickBarcodeInputTimeout = setTimeout(function() { openAddItemModalForQuickScan(barcode); }, 400);
        }
    });
    // Scan button: open camera only (no Add Item modal). After scan, Add Item modal will open with barcode.
    var cameraOpenedFromQuickScan = false;
    $('#quick-scan-camera-btn').on('click', function() {
        var branchId = $('#salesBranchId').val();
        if (!branchId) {
            Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first.', confirmButtonText: 'OK' });
            return;
        }
        cameraOpenedFromQuickScan = true;
        if (typeof Html5Qrcode === 'undefined') {
            alert('Camera scanner library not loaded.');
            cameraOpenedFromQuickScan = false;
            return;
        }
        $('#camera-barcode-reader').empty().css({ width: '100%', minHeight: '240px' });
        $('#camera-barcode-modal').modal('show');
    });

    // Handle "Scrap In" button - same modal as Add Item (like Smart Invoice Scrap In)
    // Handle "Claim" button - same modal as Add Item (like Smart Invoice Claim)
    $('#claim-entry-btn').on('click', function(e) {
        e.preventDefault();
        const branchId = $('#salesBranchId').val();
        
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before adding claim items.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        currentEntryType = 'claim';
        $('#add-item-modal-title').html('<i class="ti ti-truck-delivery me-2"></i>CLAIM RETURN');
        $('#add-item-modal').modal('show');
    });

    // Handle "Return" button - same modal as Add Item (like Smart Invoice Return)
    $('#return-entry-btn').on('click', function(e) {
        e.preventDefault();
        const branchId = $('#salesBranchId').val();
        
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

    // Handle "Scrap In" button - same modal as Add Item
    $('#scrap-in-btn').on('click', function(e) {
        e.preventDefault();
        const branchId = $('#salesBranchId').val();
        if (!branchId) {
            Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first before adding scrap in items.', confirmButtonText: 'OK' });
            return;
        }
        currentEntryType = 'scrap';
        $('#add-item-modal-title').html('<i class="ti ti-recycle me-2"></i>SCRAP IN');
        $('#add-item-modal').modal('show');
    });

    // Handle "Scrap Sale" button - same modal as Add Item
    $('#scrap-sale-btn').on('click', function(e) {
        e.preventDefault();
        const branchId = $('#salesBranchId').val();
        if (!branchId) {
            Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first before adding scrap sale items.', confirmButtonText: 'OK' });
            return;
        }
        currentEntryType = 'scrap_sale';
        $('#add-item-modal-title').html('<i class="ti ti-file-text me-2"></i>SCRAP SALE');
        $('#add-item-modal').modal('show');
    });

    // Reset quantity to default 1 and hide oil row
    function resetItemQuantitySelect() {
        $('#quantity-row-oil').hide();
        $('#quantity-row-normal').show();
        $('#item-quantity').val('1');
        $('#item-quantity-cans').val(0);
        $('#item-quantity-liters').val(0);
        $('#item-quantity-oil-summary').text('= 0 Can total');
        $('#item-liter-per-can').val('');
    }

    // Reset form when modal opens
    $('#add-item-modal').on('show.bs.modal', function() {
        $('#add-item-modal').appendTo('body');
        const branchId = $('#salesBranchId').val();
        
        // Reset form when modal opens
        $('#item-search').val('');
        $('#selected-item-id').val('');
        $('#selected-warehouse-id').val('');
        resetItemQuantitySelect();
        $('#item-unit').val('');
        $('#item-rate').val('0');
        $('#warranty-value').val('');
        $('#warranty-unit').val('');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to view history</p>');
        $('#purchase-history-content').html('<p class="text-muted mb-0 small">Select item to view purchase history</p>');
        $('#item-search-results').hide();
        $('#item-edit-in-modal-btn').hide();
        $('#stock-status-section').hide();
        $('#stock-status-content').hide();
        $('#barcode-scan-input').val('');
        // Hide image preview
        $('#item-search-image-preview').addClass('d-none');
        $('#item-search-image').attr('src', '');
        $('#item-search-stock').html('');
        $('#item-search-warehouse').text($('body').data('currentWarehouseName') || '');
        // Hide selected item details display
        $('#selected-item-details-display').addClass('d-none');
        $('#selected-item-details-line1').html('');
        $('#selected-item-details-line2').html('');
        $('#selected-item-details-line3').html('');
    });
        
    // Focus on search input when modal is fully shown (and handle quick barcode from main page)
    var pendingQuickBarcode = null;
    var openCameraAfterQuickScan = false;
    $('#add-item-modal').on('shown.bs.modal', function() {
        $('#add-item-modal').css({ 'pointer-events': 'auto', 'z-index': 9999 });
        $('#add-item-modal').find('.modal-dialog, .modal-content, .modal-body, .modal-footer, .modal-header').css('pointer-events', 'auto');
        $('#add-item-modal').find('button, input, select, a.btn').css('pointer-events', 'auto');
        var $backdrop = $('.modal-backdrop').last();
        if ($backdrop.length) $('#add-item-modal').insertAfter($backdrop);
        $('#item-search').prop('readonly', false).prop('disabled', false).attr('readonly', false);
        if (pendingItemIdAfterUpdate) {
            var itemId = pendingItemIdAfterUpdate;
            pendingItemIdAfterUpdate = null;
            pendingQuickBarcode = null;
            openCameraAfterQuickScan = false;
            $('#selected-item-id').val(itemId);
            $.get('{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId))
                .then(function(r) {
                    $('#item-search').val(r.name || '');
                    if (typeof loadItemStockStatus === 'function') loadItemStockStatus(itemId);
                    $('#item-edit-in-modal-btn').show();
                })
                .catch(function() { $('#item-search').trigger('input'); });
        }
        setTimeout(function() {
            if (pendingItemIdAfterUpdate) return;
            if (pendingQuickBarcode) {
                runBarcodeSearch(pendingQuickBarcode);
                pendingQuickBarcode = null;
                $('#quick-barcode-input').val('');
            } else if (openCameraAfterQuickScan) {
                openCameraAfterQuickScan = false;
                $('#camera-barcode-modal').modal('show');
            } else {
                $('#item-search').focus();
            }
        }, 100);
    });
    
    // Shared: run barcode search and auto-select if single item (used by Enter key and camera scan)
    function runBarcodeSearch(barcode) {
        if (!barcode) return;
        $('#item-search').val(barcode);
        const branchId = $('#salesBranchId').val();
        const resultsDiv = $('#item-search-results');
        
        resultsDiv.html(`
            <div class="p-4 text-center">
                <div class="spinner-border text-primary mb-2" style="width: 2rem; height: 2rem; border-width: 0.2em;" role="status"></div>
                <p class="mb-0 text-muted fw-500">Searching by barcode...</p>
            </div>
        `).show();
        
        $.ajax({
            url: "{{ route('sales.items.ajax.search') }}",
            method: 'GET',
            data: { q: barcode, branch_id: branchId, limit: 15, for_sale: 1 },
            success: function(results) {
                const itemResults = results.filter(function(r) { return r.type === 'item'; });
                if (itemResults.length === 1) {
                    const result = itemResults[0];
                    const item = result.item;
                    const itemId = item.id;
                    const itemName = (item.short_disc && item.short_disc.toLowerCase().indexOf('lorem') === -1) ? item.short_disc : ((item.pro_dis && item.pro_dis.toLowerCase().indexOf('lorem') === -1) ? item.pro_dis : (item.bar_code || (item.partnumber_item ? item.partnumber_item.name : '') || 'Item #' + item.id));
                    const itemRate = (result.sale_price > 0 ? result.sale_price : (result.calculated_price_per_unit > 0 ? result.calculated_price_per_unit : (item.sale_price > 0 ? item.sale_price : (item.packing_purchase_rate || item.total_price || 0))));
                    const unit = (item.unit_item && (item.unit_item.name || item.unit_item.short_name)) ? (item.unit_item.name || item.unit_item.short_name) : 'Unit';
                    const warehouseId = result.warehouse_id || '';
                    
                    $('#item-search').val(itemName);
                    $('#selected-item-id').val(itemId);
                    $('#item-edit-in-modal-btn').show();
                    $('#item-unit').val(unit);
                    $('#item-rate').val(Math.round(parseFloat(itemRate) || 0));
                    $('#item-search-results').hide();
                    $('#barcode-scan-input').val('');
                    
            $.ajax({
                        url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
                method: 'GET',
                success: function(response) {
                            $('#item-rate').val(Math.round(parseFloat(response.sale_price || response.rate || response.total_price || itemRate) || 0));
                            if (response.unit) $('#item-unit').val(response.unit);
                            if (response.warehouse_id || warehouseId) $('#selected-warehouse-id').val(response.warehouse_id || warehouseId);
                            
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
                                
                                // Show supplier selection if stock is 0
                                if (stockValue === 0) {
                                    $('#supplier-selection-section').show();
                                    $('#item_supplier_id').prop('required', true);
                                } else {
                                    $('#supplier-selection-section').hide();
                                    $('#item_supplier_id').prop('required', false).val('');
                                }
                            } else {
                                $('#item-search-stock').html('');
                                $('#supplier-selection-section').hide();
                                $('#item_supplier_id').prop('required', false).val('');
                            }
                            
                            loadItemStockStatus(itemId);
                            loadCustomerHistory(itemId);
                loadPurchaseHistory(itemId);
                },
                error: function() {
                            loadItemStockStatus(itemId);
                            loadCustomerHistory(itemId);
                loadPurchaseHistory(itemId);
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
    
    // Enter in main search also runs search immediately (for scanner typing into search box)
    $(document).on('keydown', '#item-search', function(e) {
        if (e.which === 13) {
            e.preventDefault();
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
        // Clear any previous instance so we always start fresh
        if (cameraBarcodeScanner) {
            try { stopCameraScanner(); } catch (e) {}
            cameraBarcodeScanner = null;
        }
        var startScan = function() {
            if (cameraBarcodeScanner) return;
            var readerEl = document.getElementById('camera-barcode-reader');
            if (!readerEl) return;
            var w = readerEl.offsetWidth, h = readerEl.offsetHeight;
            if (w < 50 || h < 50) {
                setTimeout(startScan, 200);
                return;
            }
            cameraBarcodeScanner = new Html5Qrcode('camera-barcode-reader');
            // Scan area: wider box for barcodes, enough height for QR
            var config = { fps: 15, qrbox: function(vw, vh) { return { width: Math.min(300, vw), height: Math.min(140, Math.floor(vh * 0.5)) }; } };
            if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
                config.formatsToSupport = [Html5QrcodeSupportedFormats.QR_CODE, Html5QrcodeSupportedFormats.EAN_13, Html5QrcodeSupportedFormats.EAN_8, Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39];
            }
            function onScanSuccess(decodedText) {
                if (!decodedText) return;
                stopCameraScanner();
                $('#camera-barcode-modal').modal('hide');
                if (typeof cameraOpenedFromQuickScan !== 'undefined' && cameraOpenedFromQuickScan) {
                    cameraOpenedFromQuickScan = false;
                    openAddItemModalForQuickScan(decodedText);
                } else {
                    runBarcodeSearch(decodedText);
                }
            }
            function onScanError() {}
            var cameraConfig = { facingMode: 'environment' };
            cameraBarcodeScanner.start(cameraConfig, config, onScanSuccess, onScanError).catch(function(err) {
                // Desktop often has no "environment" camera; try default camera
                cameraConfig = {};
                cameraBarcodeScanner.start(cameraConfig, config, onScanSuccess, onScanError).catch(function(err2) {
                    cameraBarcodeScanner = null;
                    $('#camera-barcode-modal').modal('hide');
                    var msg = (err2 && err2.message) ? err2.message : (err && err.message) ? err.message : 'Camera failed.';
                    if (msg.indexOf('NotAllowedError') !== -1 || msg.indexOf('Permission') !== -1) msg = 'Camera permission denied. Allow camera in browser settings.';
                    alert(msg);
                });
            });
        };
        setTimeout(startScan, 450);
    });
    
    $('#camera-barcode-modal').on('hidden.bs.modal', function() {
        stopCameraScanner();
        cameraBarcodeScanner = null;
    });
    
    // Edit selected item: open item edit page in new tab
    $(document).on('click', '#item-edit-in-modal-btn', function() {
        var itemId = ($('#selected-item-id').val() || '').toString().trim();
        if (!itemId) return;
        var editUrl = '{{ url("item/edit") }}' + '/' + itemId;
        var returnTo = (window.location.pathname || '').replace(/\/+$/, '');
        if (returnTo) editUrl += (editUrl.indexOf('?') !== -1 ? '&' : '?') + 'return_to=' + encodeURIComponent(returnTo);
        window.open(editUrl, '_blank');
    });

    // Product name search with dropdown - COMPREHENSIVE SEARCH
    let itemSearchTimeout = null;
    
    // Use event delegation to ensure it works even if modal is dynamically loaded
    $(document).on('input', '#item-search', function() {
        const query = $(this).val().trim();
        const branchId = $('#salesBranchId').val();
        const resultsDiv = $('#item-search-results');
        
        // Clear previous timeout
        clearTimeout(itemSearchTimeout);
        
        if (query.length < 2) {
            resultsDiv.hide();
            $('#selected-item-id').val('');
            $('#item-edit-in-modal-btn').hide();
            // Hide image preview when search is cleared
            $('#item-search-image-preview').addClass('d-none');
            $('#item-search-image').attr('src', '');
            $('#item-search-stock').html('');
            // Hide selected item details display when search is cleared
            $('#selected-item-details-display').addClass('d-none');
            $('#selected-item-details-line1').html('');
            $('#selected-item-details-line2').html('');
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
                url: "{{ route('sales.items.ajax.search') }}",
                method: 'GET',
                data: {
                    q: query,
                    branch_id: branchId,
                    limit: 15,
                    for_sale: 1  // Sale hisaab: only items with sale price
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
                                // Item result - comprehensive display with all type-based details (same as purchase search)
                                const item = result.item;
                                const itemType = (item.type || '').toString().toLowerCase();
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
                                    const grade = item.grade_item ? item.grade_item.name : '';
                                    const volt = item.volt_item ? item.volt_item.name : '';
                                    const cca = item.cca_item ? item.cca_item.name : '';
                                    const group = item.group_item ? item.group_item.name : '';
                                    const madeIn = item.made_in_item ? item.made_in_item.name : '';
                                    const level = (item.level_item && item.level_item.name) ? String(item.level_item.name).trim() : '';
                                    const mileageName = (item.mileage_item && item.mileage_item.name) ? String(item.mileage_item.name).trim() : '';
                                    const batterySize = item.battery_size || '';
                    const plate = item.plate_item ? item.plate_item.name : '';
                    const amperes = item.amphors_item ? item.amphors_item.name : '';
                                const stock = item.on_hand || 0;
                                // Sale price in results: prefer API sale_price / calculated_price_per_unit, then item.sale_price
                                const rate = (result.sale_price > 0 ? result.sale_price : (result.calculated_price_per_unit > 0 ? result.calculated_price_per_unit : (item.sale_price > 0 ? item.sale_price : (item.packing_purchase_rate || item.total_price || 0))));
                                    const unit = (item.unit_item && (item.unit_item.name || item.unit_item.short_name))
                        ? (item.unit_item.name || item.unit_item.short_name) 
                                    : 'Unit';
                    
                    // Parse liter-per-can for oil/can (same as purchase)
                    let literPerCan = null;
                    const unitStr = (unit || '').toString();
                    const literMatch = unitStr.match(/(\d+(?:\.\d+)?)\s*(?:liter|ltr|L)\b/i) || unitStr.match(/\b(?:liter|ltr|L)\s*(\d+(?:\.\d+)?)/i);
                    if (literMatch) literPerCan = parseFloat(literMatch[1]);
                    else if (item.filling != null && item.filling !== '' && !isNaN(parseFloat(item.filling))) literPerCan = parseFloat(item.filling);
                    if (apiLiterPerCan != null && apiLiterPerCan > 0) literPerCan = apiLiterPerCan;
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
                                    // Plates and amperes are on first line, not here
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
                    
                    // Order: Grade • Level • Company (oil). Append unit (e.g. 4 Liter). Second line = category.
                    const gradeLevelCompanyParts = [];
                    if (grade && !isDummy(grade)) gradeLevelCompanyParts.push(grade);
                    if (level && !isDummy(level)) gradeLevelCompanyParts.push(level);
                    if (company && !isDummy(company)) gradeLevelCompanyParts.push(company);
                    if (itemType === 'oil' && unitForFirstLine) gradeLevelCompanyParts.push(unitForFirstLine);
                    const gradeLevelCompanyLine = gradeLevelCompanyParts.length > 0 ? gradeLevelCompanyParts.join(' • ') : '';
                    
                    // Build first line HTML: oil = Grade•Level•Company•unit + second line category; battery = firstLineParts; else product name
                    let firstLineHtml = '';
                    let firstLineText = productName;
                    if (itemType === 'battery' && firstLineParts.length > 0) {
                        firstLineText = firstLineParts.join(' • ');
                        const highlightedFirstLine = highlightText(firstLineText, query);
                        firstLineHtml = '<div class="battery-type-sequence fw-bold mb-1">' + highlightedFirstLine + '</div>';
                    } else if (gradeLevelCompanyLine) {
                        firstLineText = gradeLevelCompanyLine;
                        const highlighted = highlightText(gradeLevelCompanyLine, query);
                        firstLineHtml = '<div class="fw-bold text-dark mb-1 text-uppercase">' + highlighted + '</div>';
                        const secondLineParts = [];
                        if (category && !isDummy(category)) secondLineParts.push(category);
                        if (mileageName) secondLineParts.push('Mileage: ' + mileageName);
                        const secondLineContent = secondLineParts.length > 0
                            ? secondLineParts.join(' • ')
                            : highlightText(productName, query);
                        firstLineHtml += '<div class="small text-muted mt-0 text-uppercase">' + secondLineContent + '</div>';
                    } else {
                        const highlightedProductName = highlightText(productName, query);
                        firstLineHtml = '<div class="fw-bold text-dark mb-1">' + highlightedProductName + '</div>';
                    }
                    
                    // Oil/Can: show "X L per can" when unit is e.g. Can - 3 Liter
                    const literPerCanHtml = (literPerCan != null && literPerCan > 0)
                        ? ('<div class="small text-info mt-0">Can = ' + (Number.isInteger(literPerCan) ? literPerCan : literPerCan.toFixed(1)) + ' L</div>')
                        : '';
                    
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
                             data-liter-per-can="${(literPerCan != null && literPerCan > 0) ? literPerCan : ''}"
                             style="cursor: pointer; transition: all 0.2s ease; background: white;">
                            <div class="d-flex justify-content-between align-items-start">
                                ${itemImage ? `<div class="me-3" style="flex-shrink: 0;">
                                    <img src="${itemImage}" alt="${productName}" class="rounded border" style="width: 60px; height: 60px; object-fit: cover;">
                                </div>` : ''}
                                <div class="flex-grow-1 me-3">
                                    ${firstLineHtml}
                                    ${literPerCanHtml}
                                    ${detailsHtml}
                                    ${codeInfo ? '<div class="text-primary small fw-semibold mt-1"><i class="ti ti-barcode me-1"></i>' + highlightedCodeInfo + '</div>' : ''}
                                    </div>
                                <div class="text-end" style="min-width: 100px;">
                                    <div class="fw-bold text-primary mb-1">Rs ${parseFloat(rate).toFixed(2)}</div>
                                    <div class="small">
                                        <span class="badge bg-${stockColor} bg-opacity-10 text-${stockColor}">
                                            ${stockIcon ? '<i class="ti ' + stockIcon + ' me-1"></i>' : ''}${stockDisplay} ${unit}
                                        </span>
                                        ${(literPerCan != null && literPerCan > 0) ? ('<div class="small text-muted mt-1">' + (Number.isInteger(literPerCan) ? literPerCan : literPerCan.toFixed(1)) + ' L per can</div>') : ''}
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
            selectSalesBranch(resultId, $(this).find('.fw-bold').text(), '');
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
            const warehouseId = $(this).closest('.item-search-result').data('warehouse-id');
            
            // Set input value: Use first line text (the black text from search result)
            $('#item-search').val(itemFirstLine);
        $('#selected-item-id').val(itemId);
            $('#item-edit-in-modal-btn').show();
            $('#item-quantity').val('1');
            $('#item-unit').val(itemUnit || 'Unit');
            $('#item-search-results').hide();
            
            // Show item details below input (matching image format)
        let line1 = '';
        let line2 = '';
        let line3 = '';
        
        // Line 1: Volt only (remove company like "AGS")
        if (itemLine1Details) {
            // Remove company from line1Details (e.g., "AGS • 12V" -> "12V")
                // Split by bullet and take everything after the first part (company)
            const parts = itemLine1Details.split('•').map(p => p.trim());
            if (parts.length > 1) {
                    // Remove first part (company) and join the rest
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
                    detailsOnly = detailsOnly.replace(new RegExp('\\s*•\s*' + itemCca.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), '');
            }
                // Remove company from start if present (first part before first bullet)
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
        
        if (line1 || line2 || line3) {
                $('#selected-item-details-line1').html(line1 || '&nbsp;');
            $('#selected-item-details-line2').html(line2 || '&nbsp;').toggle(!!line2);
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
                    // Prefer sale_price for sales; then rate (from API), total_price, or row rate
                    const itemRate = response.sale_price || response.rate || response.total_price || 0;
                    $('#item-rate').val(Math.round(parseFloat(itemRate) || 0));
                    const literPerCan = (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can))) ? parseFloat(response.liter_per_can) : null;
                    $('#item-liter-per-can').val(literPerCan != null && literPerCan > 0 ? literPerCan : '');
                    if (literPerCan != null && literPerCan > 0) {
                        $('#quantity-row-normal').hide();
                        $('#quantity-row-oil').show();
                        $('#item-quantity-cans').val(0);
                        $('#item-quantity-liters').val(0);
                        $('#item-quantity').val(0);
                        $('#item-unit').val(response.unit || 'Can');
                        $('#item-quantity-oil-summary').text('= 0 Can total');
                    } else {
                        $('#quantity-row-oil').hide();
                        $('#quantity-row-normal').show();
                        $('#item-quantity').val('1');
                        $('#item-unit').val(response.unit || 'Unit');
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
                    
                    // Show stock below image (X Can Y Liter/ML for oil)
                    if (response.stock !== undefined) {
                        const stockValue = parseFloat(response.stock) || 0;
                        const unit = response.unit || 'Unit';
                        const lpc = (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can))) ? parseFloat(response.liter_per_can) : null;
                        let stockHtml = '';
                        if (lpc != null && lpc > 0 && stockValue > 0) {
                            const fullCans = Math.floor(stockValue);
                            const loose = stockValue % 1;
                            const openLiters = loose * lpc;
                            const openML = Math.round(openLiters * 1000);
                            const canText = fullCans + ' Can';
                            const looseText = openML >= 1000 ? (openLiters.toFixed(1).replace(/\.0$/, '') + ' Liter') : (openML + ' ML');
                            stockHtml = `<span class="text-dark fw-500">${canText}${openML > 0 ? ' ' + looseText : ''}</span>`;
                            if (openML > 0 && openML >= 1000) {
                                stockHtml += `<span class="small text-muted d-block" style="font-size: 0.65rem;">${openML} ML</span>`;
                            }
                            stockHtml += `<div class="small text-muted mt-0">${Number.isInteger(lpc) ? lpc : lpc.toFixed(1)} L per can</div>`;
                            $('#item-search-stock').html(stockHtml);
                        } else {
                            const stockColor = stockValue > 10 ? 'text-success' : (stockValue > 0 ? 'text-warning' : 'text-danger');
                            const stockText = stockValue % 1 === 0 ? Math.round(stockValue) : stockValue.toFixed(1);
                            stockHtml = `<span class="${stockColor}">${stockText} ${unit}</span>`;
                            if (lpc != null && lpc > 0) {
                                const lText = Number.isInteger(lpc) ? lpc : lpc.toFixed(1);
                                stockHtml += `<div class="small text-muted mt-0">${lText} L per can</div>`;
                            }
                            $('#item-search-stock').html(stockHtml);
                        }
                        
                        // Show supplier selection if stock is 0
                        if (stockValue === 0) {
                            $('#supplier-selection-section').show();
                            $('#item_supplier_id').prop('required', true);
                        } else {
                            $('#supplier-selection-section').hide();
                            $('#item_supplier_id').prop('required', false).val('');
                        }
                    } else {
                        $('#item-search-stock').html('');
                        $('#supplier-selection-section').hide();
                        $('#item_supplier_id').prop('required', false).val('');
                    }
                    
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
                loadPurchaseHistory(itemId);
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
                loadPurchaseHistory(itemId);
                }
            });
        }
    });
    
    // Load stock status for selected item
    function loadItemStockStatus(itemId) {
        $('#stock-status-section').show();
        $('#stock-status-content').show();
        $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">Loading stock status...</p>');
        
        $.ajax({
            url: '{{ route("purchases.items.stock.status", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(stockData) {
                if (stockData.length === 0) {
                    $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">No stock found</p>');
                    return;
                }
                
                function formatStockRow(stock, isWarehouse) {
                    var unitLabel = (stock.unit || 'Unit').trim();
                    var qty = parseFloat(stock.quantity) || 0;
                    var qtyText = (Number.isInteger(qty) ? qty : qty.toFixed(2)) + ' ' + unitLabel;
                    var cartons = parseInt(stock.cartons, 10) || 0;
                    var loose = parseFloat(stock.loose) || 0;
                    var literPerCan = (stock.base_unit === 'Liter' && stock.base_unit_multiplier) ? parseFloat(stock.base_unit_multiplier) : null;
                    var openLiters = 0;
                    if (literPerCan !== null) {
                        var looseLitersVal = parseFloat(stock.loose_liters);
                        if (!isNaN(looseLitersVal)) openLiters = looseLitersVal;
                        else if (loose > 0) openLiters = loose * literPerCan;
                    }
                    var baseUnitLabel = (stock.base_unit || 'Liter').trim();
                    var multVal = (literPerCan != null && literPerCan > 0) ? literPerCan : 0;
                    var isSelected = isWarehouse && ($('#selected-warehouse-id').val() == stock.id);
                    var rowClass = isWarehouse ? 'stock-warehouse-item' : 'stock-branch-item';
                    if (isWarehouse && isSelected) rowClass += ' bg-primary text-white';
                    var textClass = (isWarehouse && isSelected) ? 'text-white' : 'text-muted';

                    if (isWarehouse) {
                        var lpcText = (literPerCan != null && literPerCan > 0) ? (Number.isInteger(literPerCan) ? literPerCan : literPerCan.toFixed(1)) : '';
                        var tag1Html = (lpcText !== '') ? '<span class="warehouse-unit-tag me-1">' + lpcText + ' L PER CAN</span>' : '';
                        var tag2Html = (lpcText !== '' && baseUnitLabel) ? '<span class="warehouse-unit-tag me-2">' + lpcText + ' ' + baseUnitLabel + '</span>' : '';
                        var canLabel = unitLabel || 'Can';
                        var mainQtyDisp = Number.isInteger(qty) ? qty : qty.toFixed(2);
                        var stockLabel = mainQtyDisp + ' ' + canLabel;
                        var dataAttrs = ' data-warehouse-id="' + stock.id + '" data-branch-id="' + (stock.branch_id || '') + '" data-display="' + (stock.display || '').replace(/"/g, '&quot;') + '" data-quantity="' + qty + '" data-unit="' + (unitLabel || '').replace(/"/g, '&quot;') + '" data-base-unit="' + (stock.base_unit || '').replace(/"/g, '&quot;') + '" data-base-unit-multiplier="' + (multVal || '') + '" data-qty-text="' + (qtyText || '').replace(/"/g, '&quot;') + '" data-cartons="' + cartons + '" data-loose-liters="' + openLiters + '" data-liter-per-can="' + (literPerCan != null ? literPerCan : '') + '"';
                        return '<div class="d-flex flex-wrap align-items-center gap-2 py-2 mb-1 ' + rowClass + '" ' + dataAttrs + ' style="cursor: pointer; transition: all 0.2s; ' + (isSelected ? '' : 'background-color: #f0f0f0;') + '">' +
                            '<span class="me-2">' + (isSelected ? '✓' : '') + '</span>' +
                            '<span class="' + (isSelected ? 'text-white' : '') + '">' + (stock.display || 'Display') + '</span>' +
                            tag1Html +
                            tag2Html +
                            '<span class="' + (isSelected ? 'text-white' : 'text-dark') + ' me-2" style="font-size: 0.9rem;">' + stockLabel + '</span>' +
                            '<input type="number" min="0" step="1" class="form-control form-control-sm stock-warehouse-qty-input" placeholder="0" value="0" data-warehouse-id="' + stock.id + '" onclick="event.stopPropagation();" data-unit="' + (unitLabel || 'Piece').replace(/"/g, '&quot;') + '">' +
                            '<input type="number" min="0" step="0.01" class="form-control form-control-sm stock-warehouse-base-qty-input" placeholder="" value="" data-warehouse-id="' + stock.id + '" onclick="event.stopPropagation();" data-multiplier="' + multVal + '">' +
                            '<input type="number" min="0" step="1" class="form-control form-control-sm stock-warehouse-extra-input" placeholder="" value="" data-warehouse-id="' + stock.id + '" onclick="event.stopPropagation();">' +
                        '</div>';
                    }
                    return '<div class="p-2 mb-1 border-bottom stock-branch-item" style="background-color: #fff;"><div class="d-flex justify-content-between align-items-center"><div class="fw-bold">' + (stock.display || '') + '</div></div></div>';
                }
                
                let html = '';
                stockData.forEach(function(stock) {
                    if (stock.type === 'branch') {
                        html += formatStockRow(stock, false);
                    } else if (stock.type === 'warehouse') {
                        html += formatStockRow(stock, true);
                    }
                });
                
                $('#stock-status-list').html(html);
            },
            error: function() {
                $('#stock-status-list').html('<p class="text-danger mb-0 small text-center">Error loading stock status</p>');
            }
        });
    }
    
    // Select warehouse from stock status
    $(document).on('click', '.stock-warehouse-item', function(e) {
        if ($(e.target).closest('.stock-warehouse-qty-input, .stock-warehouse-base-qty-input, .stock-warehouse-extra-input').length) return;
        $('.stock-warehouse-item').removeClass('bg-primary text-white');
        $('.stock-warehouse-item').each(function() { $(this).find('span').first().text(''); });
        $(this).addClass('bg-primary text-white');
        $(this).find('span').first().html('✓');
        const warehouseId = $(this).data('warehouse-id');
        const warehouseDisplay = $(this).data('display') || $(this).find('span').eq(1).text() || 'Warehouse';
        $('#selected-warehouse-id').val(warehouseId);
        $('#item-search-warehouse').text(warehouseDisplay).removeClass('d-none');
        $('body').data('currentWarehouseName', warehouseDisplay);
        $(this).find('.stock-warehouse-qty-input').val('0');
        $(this).find('.stock-warehouse-base-qty-input').val('');
        $(this).find('.stock-warehouse-extra-input').val('');
        $('#item-quantity').val('');
        $('#item-quantity-cans').val(0);
        $('#item-quantity-liters').val(0);
        if (typeof updateOilQuantityFromInputs === 'function') updateOilQuantityFromInputs();
    });

    $(document).on('focus', '.stock-warehouse-qty-input, .stock-warehouse-base-qty-input, .stock-warehouse-extra-input', function() {
        var $row = $(this).closest('.stock-warehouse-item');
        if ($row.length && !$row.hasClass('bg-primary')) {
            $('.stock-warehouse-item').removeClass('bg-primary text-white');
            $('.stock-warehouse-item').each(function() { $(this).find('span').first().text(''); });
            $row.addClass('bg-primary text-white');
            $row.find('span').first().html('✓');
            $('#selected-warehouse-id').val($row.data('warehouse-id') || '');
            var whDisp = $row.data('display') || $row.find('span').eq(1).text() || '';
            if (whDisp) {
                $('#item-search-warehouse').text(whDisp).removeClass('d-none');
                $('body').data('currentWarehouseName', whDisp);
            }
        }
    });
    $(document).on('change', '.stock-warehouse-qty-input, .stock-warehouse-base-qty-input, .stock-warehouse-extra-input', function() {
        var $row = $(this).closest('.stock-warehouse-item');
        if (!$row.length) return;
        var cans = parseInt($row.find('.stock-warehouse-qty-input').val(), 10) || 0;
        var baseLiters = parseFloat($row.find('.stock-warehouse-base-qty-input').val()) || 0;
        var lpc = parseFloat($row.data('liter-per-can')) || 0;
        var looseLiters = 0;
        if (lpc > 0) {
            looseLiters = baseLiters;
        }
        var qty = cans;
        if (lpc > 0 && (cans > 0 || looseLiters > 0)) {
            qty = cans + (looseLiters / lpc);
        }
        if ($row.hasClass('bg-primary')) {
            $('#item-quantity').val(qty > 0 ? qty : '');
            $('#item-quantity-cans').val(cans);
            $('#item-quantity-liters').val(looseLiters);
            if (typeof updateOilQuantityFromInputs === 'function') updateOilQuantityFromInputs();
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
                $('#item-search').val(response.name);
                
                // Set rate - prefer sale_price for sales
                const itemRate = response.sale_price || response.rate || response.total_price || 0;
                $('#item-rate').val(Math.round(parseFloat(itemRate) || 0));
                const literPerCan = (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can))) ? parseFloat(response.liter_per_can) : null;
                $('#item-liter-per-can').val(literPerCan != null && literPerCan > 0 ? literPerCan : '');
                if (literPerCan != null && literPerCan > 0) {
                    $('#quantity-row-normal').hide();
                    $('#quantity-row-oil').show();
                    $('#item-quantity-cans').val(0);
                    $('#item-quantity-liters').val(0);
                    $('#item-quantity').val(0);
                    $('#item-unit').val(response.unit || 'Can');
                    $('#item-quantity-oil-summary').text('= 0 Can total');
                } else {
                    $('#quantity-row-oil').hide();
                    $('#quantity-row-normal').show();
                    $('#item-quantity').val('1');
                    $('#item-unit').val(response.unit || 'Unit');
                }
                
                // Auto-select warehouse
                if (response.warehouse_id) {
                    $('#selected-warehouse-id').val(response.warehouse_id);
                    $('.stock-warehouse-item').removeClass('bg-primary text-white');
                    $('.stock-warehouse-item').each(function() { $(this).find('span').first().text(''); });
                    $('.stock-warehouse-item[data-warehouse-id="' + response.warehouse_id + '"]')
                        .addClass('bg-primary text-white')
                        .find('span').first()
                        .html('✓');
                }
                
                // Show item image if available
                if (response.image) {
                    $('#item-search-image').attr('src', response.image);
                    $('#item-search-image-preview').removeClass('d-none');
                } else {
                    $('#item-search-image-preview').addClass('d-none');
                }
                
                // Show stock below image (for oil items: X Can Y Liter / Y ML)
                if (response.stock !== undefined) {
                    const stockValue = parseFloat(response.stock) || 0;
                    const unit = response.unit || 'Unit';
                    const lpc = (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can))) ? parseFloat(response.liter_per_can) : null;
                    let stockHtml = '';
                    if (lpc != null && lpc > 0 && stockValue > 0) {
                        const fullCans = Math.floor(stockValue);
                        const loose = stockValue % 1;
                        const openLiters = loose * lpc;
                        const openML = Math.round(openLiters * 1000);
                        const canText = fullCans + ' Can';
                        const looseText = openML >= 1000 ? (openLiters.toFixed(1).replace(/\.0$/, '') + ' Liter') : (openML + ' ML');
                        stockHtml = `<span class="text-dark fw-500">${canText}${openML > 0 ? ' ' + looseText : ''}</span>`;
                        if (openML > 0 && openML >= 1000) {
                            stockHtml += `<span class="small text-muted d-block" style="font-size: 0.65rem;">${openML} ML</span>`;
                        }
                        stockHtml += `<div class="small text-muted mt-0">${Number.isInteger(lpc) ? lpc : lpc.toFixed(1)} L per can</div>`;
                        $('#item-search-stock').html(stockHtml);
                    } else {
                        const stockColor = stockValue > 10 ? 'text-success' : (stockValue > 0 ? 'text-warning' : 'text-danger');
                        const stockText = stockValue % 1 === 0 ? Math.round(stockValue) : stockValue.toFixed(1);
                        stockHtml = `<span class="${stockColor}">${stockText} ${unit}</span>`;
                        if (lpc != null && lpc > 0) {
                            const lText = Number.isInteger(lpc) ? lpc : lpc.toFixed(1);
                            stockHtml += `<div class="small text-muted mt-0">${lText} L per can</div>`;
                        }
                        $('#item-search-stock').html(stockHtml);
                    }
                    
                    // Show supplier selection if stock is 0
                    if (stockValue === 0) {
                        $('#supplier-selection-section').show();
                        $('#item_supplier_id').prop('required', true);
                    } else {
                        $('#supplier-selection-section').hide();
                        $('#item_supplier_id').prop('required', false).val('');
                    }
                } else {
                    $('#item-search-stock').html('');
                    $('#supplier-selection-section').hide();
                    $('#item_supplier_id').prop('required', false).val('');
                }
                
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
                loadPurchaseHistory(itemId);
                
                $('#search-results').hide();
            }
        });
    }
    
    // Oil quantity: sync Cans + Liters to #item-quantity (total in cans) and summary
    function updateOilQuantityFromInputs() {
        const lpc = parseFloat($('#item-liter-per-can').val()) || 0;
        if (lpc <= 0) return;
        const cans = parseInt($('#item-quantity-cans').val(), 10) || 0;
        const liters = parseFloat($('#item-quantity-liters').val()) || 0;
        const totalCans = cans + (liters / lpc);
        $('#item-quantity').val(totalCans > 0 ? totalCans : 0);
        const totalLiters = (cans * lpc) + liters;
        let sum = '= ' + (totalCans > 0 ? totalCans.toFixed(3).replace(/\.?0+$/, '') : '0') + ' Can total';
        if (totalLiters > 0) sum += ' (' + (totalLiters % 1 === 0 ? totalLiters : totalLiters.toFixed(2)) + ' L)';
        $('#item-quantity-oil-summary').text(sum);
    }
    $(document).on('input change', '#item-quantity-cans, #item-quantity-liters', function() {
        if ($('#quantity-row-oil').is(':visible')) updateOilQuantityFromInputs();
    });

    // Load last 5 sale price history for selected item
    let lastPurchaseRate = 0;
    function loadCustomerHistory(itemId) {
        $('#customer-history-content').html(`
            <div class="text-center py-2">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                <span class="text-muted small">Loading sale history...</span>
            </div>
        `);
        $('#hold-rate-link').hide();

        $.ajax({
            url: '{{ route("sales.items.sale.history", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(data) {
                if (!data.history || data.history.length === 0) {
                    $('#customer-history-content').html(`
                        <div class="text-center py-2">
                            <i class="ti ti-history-off text-muted fs-24 mb-1" style="display: block;"></i>
                            <p class="text-muted mb-0 small">No sale history for this item</p>
                        </div>
                    `);
                    $('#hold-rate-link').hide();
                    return;
                }

                lastPurchaseRate = data.history[0] ? data.history[0].rate : 0;

                let html = `<div class="fw-bold text-dark mb-2 pb-1" style="border-bottom: 1px solid #e0e0e0;"><i class="ti ti-receipt me-1"></i>Last 5 Sale Price History</div><div class="sale-history-list small">`;
                data.history.forEach(function(sale) {
                    const daysAgo = sale.days_ago === 0 ? 'Today' : (sale.days_ago === 1 ? '1 day ago' : sale.days_ago + ' days ago');
                    const custName = (sale.customer_name || 'Walk-in').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                    const dateTime = sale.sale_date_time || sale.sale_date || '';
                    html += `
                        <div class="d-flex justify-content-between align-items-center py-1 sale-history-item" style="border-bottom: 1px dashed #eee; cursor: pointer;" data-rate="${sale.rate}">
                            <div>
                                <div class="fw-500">${custName} <span class="text-muted">(${sale.quantity} ${sale.unit})</span></div>
                                <div class="text-muted" style="font-size: 0.75rem;">${dateTime ? dateTime + ' · ' : ''}${daysAgo}</div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-primary">Rs ${parseFloat(sale.rate).toLocaleString()}</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';

                $('#customer-history-content').html(html);
                $('#hold-rate-link').show();
            },
            error: function(xhr) {
                console.error('Error loading sale history:', xhr);
                $('#customer-history-content').html(`
                    <div class="text-center py-2">
                        <i class="ti ti-alert-circle text-danger fs-24 mb-1" style="display: block;"></i>
                        <p class="text-danger mb-0 small">Error loading history</p>
                    </div>
                `);
            }
        });
    }
    
    // Load last 5 purchase history for selected item (below customer/sale history)
    function loadPurchaseHistory(itemId) {
        $('#purchase-history-content').html(`
            <div class="text-center py-2">
                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                <span class="text-muted small">Loading purchase history...</span>
            </div>
        `);
        $.ajax({
            url: '{{ route("purchases.items.purchase.history", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(data) {
                if (!data.history || data.history.length === 0) {
                    $('#purchase-history-content').html(`
                        <div class="text-center py-2">
                            <i class="ti ti-shopping-cart-off text-muted fs-24 mb-1" style="display: block;"></i>
                            <p class="text-muted mb-0 small">No purchase history for this item</p>
                        </div>
                    `);
                    return;
                }
                let html = '<div class="small">';
                data.history.slice(0, 5).forEach(function(purchase) {
                    const daysAgo = purchase.days_ago === 0 ? 'Today' : (purchase.days_ago === 1 ? '1 day ago' : purchase.days_ago + ' days ago');
                    const name = (purchase.supplier_name || 'N/A').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                    const dateTime = purchase.created_at || purchase.purchase_date || '';
                    html += `
                        <div class="d-flex justify-content-between align-items-center py-1 purchase-history-item" style="border-bottom: 1px dashed #eee; cursor: pointer;" data-rate="${purchase.rate}">
                            <div>
                                <div class="fw-500">${name} <span class="text-muted">(${purchase.quantity} ${purchase.unit})</span></div>
                                <div class="text-muted" style="font-size: 0.75rem;">${dateTime ? dateTime + ' · ' : ''}${daysAgo}</div>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold text-primary">Rs ${parseFloat(purchase.rate).toLocaleString()}</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                $('#purchase-history-content').html(html);
            },
            error: function() {
                $('#purchase-history-content').html(`
                    <div class="text-center py-2">
                        <i class="ti ti-alert-circle text-danger fs-24 mb-1" style="display: block;"></i>
                        <p class="text-danger mb-0 small">Error loading purchase history</p>
                    </div>
                `);
            }
        });
    }

    // Click on sale/purchase history item to apply that rate
    $(document).on('click', '.sale-history-item, .purchase-history-item', function() {
        const rate = $(this).data('rate');
        if (rate) {
            $('#item-rate').val(Math.round(parseFloat(rate) || 0));
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

    $('#item-quantity').on('change', function() {
        // Keep for any side effects; oil quantity sync is via updateOilQuantityFromInputs
    });

    // Confirm entry (use event delegation so it works after modal is moved to body)
    $(document).on('click', '#confirm-entry', function() {
        const itemId = $('#selected-item-id').val();
        var quantityDisplay = '';
        var $sel = $('.stock-warehouse-item.bg-primary');
        if ($sel.length) {
            var cans = parseInt($sel.find('.stock-warehouse-qty-input').val(), 10) || 0;
            var baseLiters = parseFloat($sel.find('.stock-warehouse-base-qty-input').val()) || 0;
            var lpc = parseFloat($sel.data('liter-per-can')) || 0;
            $('#item-quantity-cans').val(cans);
            $('#item-quantity-liters').val(baseLiters);
            if (typeof updateOilQuantityFromInputs === 'function') updateOilQuantityFromInputs();
            if (cans > 0 || baseLiters > 0) quantityDisplay = (cans > 0 ? cans + ' Can' : '') + (baseLiters > 0 ? (cans > 0 ? ' ' : '') + baseLiters + ' Liter' : '');
            if (lpc > 0) {
                var origCartons = parseInt($sel.data('cartons'), 10) || 0;
                var origLooseL = parseFloat($sel.data('loose-liters')) || 0;
                var origTotalLiters = (origCartons * lpc) + origLooseL;
                var enteredLiters = (cans * lpc) + baseLiters;
                if (enteredLiters > origTotalLiters) {
                    alert('Quantity stock se ziyada hai. Available: ' + (origTotalLiters % 1 === 0 ? origTotalLiters : origTotalLiters.toFixed(2)) + ' Liter.');
                    return;
                }
            }
        }
        let quantity = parseFloat($('#item-quantity').val()) || 0;
        
        const unit = $('#item-unit').val();
        const rate = parseFloat($('#item-rate').val()) || 0;
        const discount = parseFloat($('#item-discount').val()) || 0;
        const discountType = $('#discount-type').val();
        const taxPercentage = parseFloat($('#item-tax').val()) || 0;
        const rawItemName = $('#item-search').val();
        const itemName = cleanItemName(rawItemName, itemId);
        const warrantyValue = $('#warranty-value').val();
        const warrantyUnit = $('#warranty-unit').val();
        const supplierId = $('#item_supplier_id').val();
        const stockText = $('#item-search-stock').text().trim();
        const stockValue = parseFloat(stockText) || 0;
        const isZeroStock = stockValue === 0;

        if (!itemId || quantity <= 0 || rate <= 0) {
            alert('Please select an item and enter valid quantity and rate');
            return;
        }
        
        // If stock is 0, require supplier selection
        if (isZeroStock && !supplierId) {
            Swal.fire({
                icon: 'warning',
                title: 'Supplier Required',
                text: 'Stock is 0. Please select a supplier to proceed.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // If supplier is selected, ask for confirmation
        if (supplierId) {
            const supplierName = $('#item_supplier_id option:selected').text();
            Swal.fire({
                icon: 'question',
                title: 'Confirm Supplier Selection',
                html: `You have selected supplier: <strong>${supplierName}</strong><br><br>This sale will be marked as <strong>PENDING</strong> and stock will <strong>NOT</strong> be updated.`,
                showCancelButton: true,
                confirmButtonText: 'Yes, Confirm',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    proceedWithItemAdd(itemId, quantity, unit, rate, discount, discountType, taxPercentage, itemName, warrantyValue, warrantyUnit, supplierId, isZeroStock, quantityDisplay);
                }
            });
            return;
        }
        
        // If no supplier, proceed normally
        proceedWithItemAdd(itemId, quantity, unit, rate, discount, discountType, taxPercentage, itemName, warrantyValue, warrantyUnit, null, false, quantityDisplay);
    });
    
    // Function to proceed with adding item
    function proceedWithItemAdd(itemId, quantity, unit, rate, discount, discountType, taxPercentage, itemName, warrantyValue, warrantyUnit, supplierId, isZeroStock, quantityDisplay) {

        // Calculate discount amount
        let discountAmount = discount;
        if (discountType === 'percent') {
            discountAmount = (quantity * rate * discount) / 100;
        }

        // Calculate totals
        const subtotal = (quantity * rate) - discountAmount;
        const taxAmount = (subtotal * taxPercentage) / 100;
        let total = subtotal + taxAmount;
        // SCRAP amount should be minus (reduces grand total)
        if (currentEntryType === 'scrap') {
            total = -Math.abs(total);
        }

        // Add to items array (entry_type: 'purchase' or 'scrap' - same as Smart Invoice)
        const selectedWarehouseId = $('#selected-warehouse-id').val();
        const warehouseName = ($('#item-search-warehouse').text() || '').trim() || ($('body').data('currentWarehouseName') || '').trim() || (selectedWarehouseId ? 'Warehouse' : '—');
        const branchName = ($('#selectedBranchName').text() || '').trim();
        const item = {
            id: itemCounter++,
            item_id: itemId,
            name: itemName,
            quantity: quantity,
            quantity_display: quantityDisplay || null,
            unit: unit,
            rate: rate,
            discount: discountAmount,
            tax_percentage: taxPercentage,
            tax_amount: taxAmount,
            total: total,
            warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : null,
            entry_type: currentEntryType || 'purchase',
            supplier_id: supplierId || null,
            is_zero_stock: isZeroStock || false,
            warehouse_id: (selectedWarehouseId && selectedWarehouseId !== '') ? selectedWarehouseId : null,
            warehouse_name: warehouseName !== '—' ? warehouseName : null,
            branch_name: branchName && branchName !== 'Select Branch' ? branchName : null
        };

        salesItems.push(item);
        addItemToTable(item);
        resetItemModal();
        $('#add-item-modal').modal('hide');
        calculateTotals();
        syncCartToServer();
        
        // If supplier is selected, mark sale as pending
        if (supplierId) {
            $('#sale-status').val('pending');
            $('input[name="status"]').val('pending');
        }
        
        // Show payment section when items added
        if (salesItems.length > 0) {
            $('#payment-section').show(); $('#payment-amount-row').show();
        }
    }

    function addItemToTable(item) {
        $('#empty-items-state').hide();
        $('#items-list').show();
        
        // Clean item name before display (avoid Lorem Ipsum or dummy text); battery sequence = same highlight (dark blue, bold)
        const displayName = cleanItemName(item.name, item.item_id);
        const isBatterySequence = (displayName && displayName.indexOf(' • ') !== -1);
        const nameCellContent = isBatterySequence ? '<span class="battery-type-sequence fw-bold">' + (displayName.replace(/</g, '&lt;').replace(/>/g, '&gt;')) + '</span>' : (displayName.replace(/</g, '&lt;').replace(/>/g, '&gt;'));
        let typeBadge = '';
        if (item.entry_type === 'scrap') typeBadge = ' <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;">SCRAP</span>';
        else if (item.entry_type === 'scrap_sale') typeBadge = ' <span class="badge bg-success text-white ms-1" style="font-size: 9px;">SCRAP SALE</span>';
        else if (item.entry_type === 'claim') typeBadge = ' <span class="badge bg-info text-white ms-1" style="font-size: 9px;">CLAIM</span>';
        else if (item.entry_type === 'return') typeBadge = ' <span class="badge bg-danger text-white ms-1" style="font-size: 9px;">RETURN</span>';
        
        // SCRAP: show total as minus (e.g. Rs -200.00) with red styling
        const totalVal = parseFloat(item.total);
        const totalDisplay = 'Rs ' + totalVal.toFixed(2);
        const totalClass = totalVal < 0 ? ' text-danger fw-bold' : '';
        
        const whDisplay = (item.warehouse_name || '—').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const branchDisplay = (item.branch_name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const warehouseCell = branchDisplay
            ? `<span class="d-block">${whDisplay}</span><span class="small text-muted">${branchDisplay}</span>`
            : whDisplay;
        
        const row = `
            <tr data-item-id="${item.item_id}" data-row-id="${item.id}" data-entry-type="${item.entry_type || 'purchase'}">
                <td class="align-middle">${warehouseCell}</td>
                <td>${displayName}${typeBadge}${item.quantity_display ? '<br><span class="text-muted small">' + item.quantity_display + '</span>' : ''}</td>
                <td>Rs ${parseFloat(item.rate).toFixed(2)}</td>
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
    $(document).on('click', '.remove-item', function() {
        const rowId = $(this).data('row-id');
        salesItems = salesItems.filter(item => item.id !== rowId);
        $(this).closest('tr').remove();
        
        if ($('#items-tbody tr').length === 0) {
            $('#empty-items-state').show();
            $('#items-list').hide();
            $('#payment-section').hide(); $('#payment-amount-row').hide();
        }
        
        calculateTotals();
        syncCartToServer();
    });

    function resetItemModal() {
        $('#selected-item-id').val('');
        $('#selected-warehouse-id').val('');
        $('#item-edit-in-modal-btn').hide();
        $('#item-search').val('');
        resetItemQuantitySelect();
        $('#item-unit').val('');
        $('#item-rate').val('0');
        $('#warranty-value').val('');
        $('#warranty-unit').val('');
        $('#item-discount').val('0');
        $('#discount-type').val('amount');
        $('#item-tax').val('0');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to see history</p>');
        $('#purchase-history-content').html('<p class="text-muted mb-0 small">Select item to view purchase history</p>');
        $('#hold-rate-link').hide();
        $('#search-results').hide();
        $('#supplier-selection-section').hide();
        $('#item_supplier_id').val('').prop('required', false);
        $('#item-search-stock').html('');
        $('#item-search-image-preview').addClass('d-none');
    }

    function calculateTotals() {
        let itemTotal = 0;
        salesItems.forEach(function(item) {
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
        const netPayable = grandTotal;

        // Update displays
        $('#gross-amount').text('Rs ' + Math.round(parseFloat(grossTotal)));
        $('#net-payable').text('Rs ' + Math.round(parseFloat(netPayable)));
        $('#total-final-balance').text(Math.round(parseFloat(netPayable)));
        $('#items-count').text(salesItems.length + ' Items Listed');
        
        // Show/hide payment section based on items
        if (salesItems.length > 0) {
            $('#payment-section').show();
        } else {
            $('#payment-section').hide();
        }
        
        // Calculate current remaining
        const cashEntry = parseFloat($('#cash_entry').val()) || 0;
        const previousBalance = parseFloat($('#previous-balance-input').val()) || 0;
        const currentRemaining = netPayable - cashEntry + previousBalance;
        $('#current-remaining').text('Rs ' + parseFloat(currentRemaining).toFixed(2));
        
        // Update payment amount (use cash entry as payment amount)
        $('#payment_amount').val(cashEntry);
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
    
    // ----- Cash Received (below NET PAYABLE): Add more, Attach photo, Remove photo, Close row -----
    function updateCashRowCloseButtons() {
        const rows = $('#cash-received-entries .cash-received-row');
        rows.find('.cash-row-close').toggle(rows.length > 1);
    }
    updateCashRowCloseButtons();
    $(document).on('click', '#add-more-cash-received-btn', function() {
        const firstRow = $('#cash-received-entries .cash-received-row').first();
        if (!firstRow.length) return;
        const clone = firstRow.clone();
        clone.find('.cash-amount-input').val(0);
        clone.find('.cash-photo-input').val('');
        clone.find('.cash-photo-preview').addClass('d-none').find('img').attr('src', '');
        $('#cash-received-entries').append(clone);
        updateCashRowCloseButtons();
    });
    $(document).on('click', '.cash-row-close', function() {
        const rows = $('#cash-received-entries .cash-received-row');
        if (rows.length <= 1) return;
        $(this).closest('.cash-received-row').remove();
        updateCashRowCloseButtons();
    });
    $(document).on('click', '.attach-cash-photo-btn', function() {
        $(this).closest('.cash-received-row').find('.cash-photo-input').click();
    });
    $(document).on('change', '.cash-photo-input', function() {
        const row = $(this).closest('.cash-received-row');
        const file = this.files && this.files[0];
        const preview = row.find('.cash-photo-preview');
        const img = row.find('.cash-photo-img');
        if (file && file.type.indexOf('image') === 0) {
            const reader = new FileReader();
            reader.onload = function(e) { img.attr('src', e.target.result); preview.removeClass('d-none'); };
            reader.readAsDataURL(file);
        } else { preview.addClass('d-none'); img.attr('src', ''); }
    });
    $(document).on('click', '.remove-cash-photo', function() {
        const row = $(this).closest('.cash-received-row');
        row.find('.cash-photo-input').val('');
        row.find('.cash-photo-preview').addClass('d-none').find('.cash-photo-img').attr('src', '');
    });
    // Cash amount: on focus clear 0; on blur if empty set 0
    $(document).on('focus', '.cash-amount-input', function() {
        const $el = $(this);
        if ($el.val() === '0' || $el.val() === '0.00') $el.val('');
    });
    $(document).on('blur', '.cash-amount-input', function() {
        const $el = $(this);
        if ($el.val() === '' || $el.val() === null) $el.val('0');
    });
    // Discount input: on focus clear 0, on blur set 0 if empty
    $('#discount').on('focus', function() {
        const $el = $(this);
        if ($el.val() === '0' || $el.val() === '0.00') $el.val('');
    }).on('blur', function() {
        const $el = $(this);
        if ($el.val() === '' || $el.val() === null) $el.val('0');
    });
    
    // Bank Received: ADD BANK PAYMENT opens modal for multiple payments
    $('#add-bank-below-btn, #add-bank-payment-btn').on('click', function() {
        $('#bank_modal_amount').val('0');
        $('#bank_modal_reference').val('');
        $('#bank_modal_photo').val('');
        $('#bank_modal_photo_name').text('');
        $('#bank_modal_photo_preview').hide().find('#bank_modal_photo_preview_img').attr('src', '');
        $('#addBankPaymentModal').modal('show');
    });
    $('#addBankPaymentModal').on('shown.bs.modal', function() {
        var $amt = $('#bank_modal_amount');
        $amt.focus();
        if ($amt.val() === '0' || $amt.val() === '0.00') $amt.val('');
    });
    $('#bank_modal_amount').on('focus', function() {
        if ($(this).val() === '0' || $(this).val() === '0.00') $(this).val('');
    }).on('blur', function() {
        if ($(this).val() === '' || $(this).val() === null) $(this).val('0');
    });
    $('#bank_modal_photo_btn').on('click', function() { $('#bank_modal_photo').click(); });
    $('#bank_modal_photo').on('change', function() {
        const file = this.files && this.files[0];
        if (file && file.type.indexOf('image') === 0) {
            $('#bank_modal_photo_name').text(file.name);
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#bank_modal_photo_preview_img').attr('src', e.target.result);
                $('#bank_modal_photo_preview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#bank_modal_photo_name').text('');
            $('#bank_modal_photo_preview').hide();
        }
    });
    $('#bank_modal_add_btn').on('click', function() {
        const amount = $('#bank_modal_amount').val().trim();
        const amtNum = parseFloat(amount) || 0;
        const reference = $('#bank_modal_reference').val().trim();
        if (amtNum <= 0) {
            alert('Please enter amount (Rs) greater than 0.');
            return;
        }
        const fileInput = document.getElementById('bank_modal_photo');
        const hasFile = fileInput.files && fileInput.files[0];
        const file = hasFile ? fileInput.files[0] : null;
        const index = $('#bank-payments-list .bank-payment-row').length;
        const row = $('<div class="bank-payment-row mb-3 p-3 rounded position-relative d-flex flex-wrap gap-3 align-items-start" style="background: #fff; border: 1px solid #e2e8f0;"></div>');
        row.html(
            '<div class="bank-row-photo-preview flex-shrink-0" style="display: none;"><img class="img-thumbnail" style="max-width: 100px; max-height: 100px; object-fit: cover;" alt="Bank receipt"></div>' +
            '<div class="bank-row-details flex-grow-1" style="min-width: 0;">' +
            '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">' +
            '<label class="form-label fw-bold mb-0">Amount (Rs)</label>' +
            '<span class="fw-bold" style="color: #9333ea; font-size: 1rem;">Rs ' + (amtNum % 1 === 0 ? amtNum : amtNum.toFixed(2)) + '</span>' +
            '</div>' +
            (reference ? '<div class="mb-2"><span class="text-muted small">Ref: ' + $('<div>').text(reference).html() + '</span></div>' : '') +
            '<div class="d-flex align-items-center justify-content-end flex-wrap gap-2">' +
            '<button type="button" class="btn btn-sm btn-outline-danger bank-remove-payment"><i class="ti ti-x me-1"></i>Remove</button>' +
            '</div>' +
            '</div>'
        );
        row.append('<input type="hidden" name="bank_received_amount[]" value="' + amtNum + '">');
        row.append('<input type="hidden" name="bank_reference[]" value="' + $('<div>').text(reference).html() + '">');
        if (hasFile) {
            $(fileInput).attr('name', 'bank_photo[]').attr('id', 'bank_photo_' + index).addClass('d-none');
            row.append(fileInput);
            var newInput = $('<input type="file" id="bank_modal_photo" class="d-none" accept="image/*">');
            $('#bank_modal_photo_btn').after(newInput);
            var reader = new FileReader();
            reader.onload = function(e) {
                row.find('.bank-row-photo-preview img').attr('src', e.target.result);
                row.find('.bank-row-photo-preview').show();
            };
            reader.readAsDataURL(file);
        }
        $('#bank-payments-list').append(row);
        $('#addBankPaymentModal').modal('hide');
        $('#bank_modal_amount').val('0');
        $('#bank_modal_reference').val('');
        $('#bank_modal_photo').val('');
        $('#bank_modal_photo_name').text('');
        $('#bank_modal_photo_preview').hide().find('#bank_modal_photo_preview_img').attr('src', '');
    });
    $(document).on('click', '.bank-remove-payment', function() {
        $(this).closest('.bank-payment-row').remove();
    });
    
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
        const methodCode = selectedOption.data('method-code') || '';
        const isCash = methodCode.toLowerCase() === 'cash';
        const isBank = methodCode.toLowerCase() === 'bank' || requiresBank;
        
        // Show/hide transaction ID field (required for bank methods)
        if (isBank && $(this).val()) {
            $('#transaction_id_wrapper').show();
            $('#payment_transaction_id').prop('required', true);
        } else {
            $('#transaction_id_wrapper').hide();
            $('#payment_transaction_id').prop('required', false);
            $('#payment_transaction_id').val('');
        }
        
        // Show/hide bank account based on payment method (not payment amount)
        if (isBank && $(this).val()) {
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
    
    // Update remaining amount when payment amount changes
    // Bank account visibility is controlled by payment method selection, not payment amount
    $('#payment_amount').on('input change', function() {
        updateRemainingAmount();
    });
    
    // Set payment amount to grand total on load
    $(document).ready(function() {
        calculateTotals();
        updateRemainingAmount();
        
        // Fetch supplier balance if supplier is already selected
        const selectedCustomerId = $('#customer_id').val();
        if (selectedCustomerId) {
            $('#customer_id').trigger('change');
        }
    });

    // Form submission
    $('#salesForm').on('submit', function(e) {
        e.preventDefault();

        if (salesItems.length === 0) {
            alert('Please add at least one item');
            return false;
        }
        
        // Add vehicles as hidden inputs before submitting
        // Remove existing vehicle inputs first
        $('input[name^="vehicles["]').remove();
        
        // Sync editable vehicle metrics from DOM to vehicles array before submit
        $('.vehicle-current-km-input, .vehicle-daily-run-km-input, .vehicle-oil-capacity-input').each(function() {
            const vehicleId = $(this).data('vehicle-id');
            const val = $(this).val().trim();
            const name = $(this).hasClass('vehicle-current-km-input') ? 'current_km' : ($(this).hasClass('vehicle-daily-run-km-input') ? 'daily_run_km' : 'oil_capacity');
            const v = vehicles.find(function(ve) { return String(ve.id) === String(vehicleId); });
            if (v) v[name] = val;
        });
        
        vehicles.forEach(function(vehicle, index) {
            // Add new inputs with customer_id
            const customerId = vehicle.customerId || $('#customer_id').val();
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][customer_id]`,
                value: customerId
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][plate_number]`,
                value: vehicle.plateNumber
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][make]`,
                value: vehicle.make
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][model]`,
                value: vehicle.model
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][year]`,
                value: vehicle.year
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][oil_capacity]`,
                value: vehicle.oil_capacity || ''
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][current_km]`,
                value: vehicle.current_km || ''
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][daily_run_km]`,
                value: vehicle.daily_run_km || ''
            }).appendTo('#salesForm');
        });
        
        // Validate payment information
        const paymentMethod = $('#payment_method_id').val();
        const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
        const grandTotal = parseFloat($('#grand-total').text().replace('Rs ', '').replace(/,/g, '')) || 0;
        
        // If payment method is selected and payment amount is greater than 0, validate it
        // Allow payment method to be selected with 0 amount (no payment recorded)
        // Only validate if user actually wants to record a payment (amount > 0)
        if (paymentMethod && paymentAmount > 0) {
            // Payment amount is provided, validate it doesn't exceed grand total
            if (paymentAmount > grandTotal) {
                alert('Payment amount cannot exceed grand total (Rs ' + Math.round(grandTotal) + ')!');
                $('#payment_amount').focus();
                return false;
            }
        }
        
        // If payment method is selected but amount is 0 or negative, that's fine (no payment)
        // We don't need to validate this case - allow sales without payment
        
        // If payment method requires bank account but none selected
        if (paymentMethod && paymentAmount > 0) {
            const selectedOption = $('#payment_method_id').find('option:selected');
            const requiresBank = selectedOption.data('requires-bank') == '1';
            const methodCode = selectedOption.data('method-code') || '';
            const isBank = methodCode.toLowerCase() === 'bank' || requiresBank;
            
            // Check if bank account is required (for bank methods)
            if (isBank && !$('#bank_account_id').val()) {
                alert('Please select a bank account for bank payment.');
                $('#bank_account_id').focus();
                return false;
            }
            
            // Check if transaction ID is required for bank methods
            if (isBank && !$('#payment_transaction_id').val()) {
                alert('Please enter transaction ID/reference for bank payment.');
                $('#payment_transaction_id').focus();
                return false;
            }
        }

        // Auto-populate reference field from sales number if not already filled
        const salesNumberText = $('#sales-number').text().trim();
        const currentReference = $('#reference').val().trim();
        if (!currentReference && salesNumberText) {
            // Extract number from sales number (e.g., "SO #00004" -> "SO #00004")
            $('#reference').val(salesNumberText);
        }
        
        // Prepare items data with all required fields
        const itemsData = salesItems.map(function(item) {
            // Calculate tax_amount if not present
            const rate = parseFloat(item.rate) || 0;
            const quantity = parseFloat(item.quantity) || 0;
            const discount = parseFloat(item.discount) || 0;
            const taxPercentage = parseFloat(item.tax_percentage) || 0;
            
            // Calculate subtotal after discount
            const subtotal = (rate * quantity) - discount;
            
            // Calculate tax amount
            const taxAmount = (subtotal * taxPercentage) / 100;
            
            // Calculate total (subtotal + tax)
            const total = subtotal + taxAmount;
            
            return {
                item_id: item.item_id,
                quantity: quantity,
                unit: item.unit || 'Unit',
                rate: rate,
                discount: discount,
                tax_percentage: taxPercentage,
                tax_amount: taxAmount,
                total: total,
                supplier_id: item.supplier_id || null,
                is_zero_stock: item.is_zero_stock || false,
                warehouse_id: (item.warehouse_id != null && item.warehouse_id !== '') ? item.warehouse_id : null
            };
        });

        // Add items to form
        const formData = new FormData(this);
        itemsData.forEach(function(item, index) {
            Object.keys(item).forEach(function(key) {
                var val = item[key];
                formData.append(`items[${index}][${key}]`, (val != null && val !== '') ? val : '');
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
                console.log('Sale created successfully:', response);
                if (response.success || response.message) {
                    // Clear sales items
                    salesItems = [];
                    $('#items-tbody').empty();
                    $('#empty-items-state').show();
                    $('#items-list').hide();
                    $('#payment-section').hide(); $('#payment-amount-row').hide();
                    calculateTotals();
                    alert('Sale created successfully! Invoice: ' + (response.invoice_no || response.sale?.invoice_no || ''));
                    window.location.href = '{{ route("all_sales") }}';
        } else {
                    alert(response.message || 'Sale created but with warnings.');
                    window.location.href = '{{ route("all_sales") }}';
                }
            },
            error: function(xhr) {
                console.error('Sale creation error:', xhr);
                console.error('Response:', xhr.responseJSON);
                let errorMessage = 'Error saving sale. Please check the following:\n\n';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        // Format validation errors nicely
                        const errors = xhr.responseJSON.errors;
                        const errorList = [];
                        
                        Object.keys(errors).forEach(function(key) {
                            const fieldName = key.replace(/_/g, ' ').replace(/\./g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            errors[key].forEach(function(error) {
                                errorList.push('• ' + fieldName + ': ' + error);
                            });
                        });
                        
                        if (errorList.length > 0) {
                            errorMessage = 'Validation Errors:\n\n' + errorList.join('\n');
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                } else if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Failed',
                    html: errorMessage.replace(/\n/g, '<br>'),
                    confirmButtonText: 'OK',
                    width: '600px'
                });
            }
        });
    });

    // Initialize date picker
    if ($('#sale_date').length) {
        $('#sale_date').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true
        });
    }
    
    // Initialize Select2 for customer dropdown
    if ($('#customer_id').length && !$('#customer_id').hasClass('select2-hidden-accessible')) {
        $('#customer_id').select2({
            placeholder: 'SEARCH PARTY NAME / VEHICLE #...',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0, // Always show search box
            dropdownCssClass: 'select2-dropdown-party-search',
            matcher: function(params, data) {
                // If there is no search term, return all data
                if ($.trim(params.term) === '') {
                    return data;
                }
                
                // Normalize search term
                var term = $.trim(params.term).toLowerCase();
                
                // Check if search term matches the option text
                if (data.text && data.text.toLowerCase().indexOf(term) !== -1) {
                    return data;
                }
                
                // Check if search term matches data-search-text attribute
                var $option = $(data.element);
                var searchText = $option.data('search-text') || '';
                if (searchText && searchText.indexOf(term) !== -1) {
                    return data;
                }
                
                // No match
                return null;
            },
            language: {
                noResults: function() {
                    return "No customer or vehicle found";
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });
    }
    
    // Initialize Select2 for mobile number dropdown (with "Add New Customer" when no results)
    if ($('#customer_mobile').length && $('#customer_mobile').is('select') && !$('#customer_mobile').hasClass('select2-hidden-accessible')) {
        $('#customer_mobile').select2({
            placeholder: 'Search Mobile Number...',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            dropdownParent: $('#customer_mobile').parent(),
            escapeMarkup: function(m) { return m; },
            language: {
                noResults: function() {
                    return '<span class="js-add-new-customer-trigger" style="cursor:pointer;display:block;padding:10px 12px;color:#0d6efd;font-weight:600;"><i class="ti ti-plus me-1"></i>Add New Customer</span>';
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });
    }
    // When "Add New Customer" is clicked in mobile dropdown: close dropdown and open full Add Customer modal
    $(document).on('click', '.js-add-new-customer-trigger', function() {
        $('#customer_mobile').select2('close');
        $('#addCustomerModal').modal('show');
    });
    // When Add Customer modal opens (from sales page), set branch to current sales branch so new customer gets correct branch
    $('#addCustomerModal').on('show.bs.modal', function() {
        const salesBranchId = $('#salesBranchId').val();
        if (salesBranchId && $('#customer_branch_id').length) {
            $('#customer_branch_id').val(salesBranchId);
        }
    });
    // Full Add Customer form (#customerForm inside #addCustomerModal) - submit via AJAX then reload
    $(document).on('submit', '#addCustomerModal #customerForm', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Adding...');
        $.ajax({
            url: $form.attr('action') || '{{ route("customers.store") }}',
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function() {
                $('#addCustomerModal').modal('hide');
                $form[0].reset();
                $btn.prop('disabled', false).html('Add Customer');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Customer added', text: 'You can now select them from the dropdown.', confirmButtonText: 'OK' }).then(function() { location.reload(); });
                } else { location.reload(); }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('Add Customer');
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || (xhr.responseJSON && xhr.responseJSON.errors && JSON.stringify(xhr.responseJSON.errors)) || 'Failed to add customer.';
                if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonText: 'OK' });
                else alert(msg);
            }
        });
    });
    
    // 3-in-One Toggle Switch Handler (S/E/O)
    // Position 1 (left, 3px) = Sale (S) - Blue (#2563eb)
    // Position 2 (middle, 28px) = Estimate (E) - Yellow (#ffc107)
    // Position 3 (right, 53px) = Order (O) - Green (#198754)
    let toggleState = 0; // 0 = Sale, 1 = Estimate, 2 = Order
    
    // Initialize toggle to Sale (S) position on page load
    function initializeToggleToSale() {
        const $toggle = $('#estimate-order-toggle');
        const $slider = $toggle.find('.switch-slider');
        
        // Set to Sale (S) position - left side
        $slider.css('left', '3px');
        $toggle.css('background', '#2563eb');
        $('#sale-status').val('pending');
        
        // Fetch next INV number (separate series) from server
        const $salesNumber = $('#sales-number');
        $.get('{{ route("sales.next.invoice.number") }}').then(function(r) {
            if (r.number != null) $salesNumber.text('INV #' + r.number);
        }).fail(function() { $salesNumber.text('INV #00001'); });
        
        // Update page title and button
        if ($('#page-title-text').length) {
            $('#page-title-text').text('Create Sales');
        }
        
        if ($('#submit-sale-btn').length) {
            $('#submit-sale-btn').html('<i class="ti ti-check me-1"></i> Save Sale');
        }
        
        // Show payment section if items are added
        if (salesItems.length > 0) {
            $('#payment-section').show();
        }
    }
    
    // Initialize on page load: set INV number from server if branch selected
    $(document).ready(function() {
        initializeToggleToSale();
        if ($('#salesBranchId').val()) {
            $.get('{{ route("sales.next.invoice.number") }}').then(function(r) {
                if (r.number != null) $('#sales-number').text('INV #' + r.number);
            });
        }
    });
    
    $('#estimate-order-toggle').on('click', function() {
        toggleState = (toggleState + 1) % 3; // Cycle through 0, 1, 2
        const $toggle = $(this);
        const $slider = $toggle.find('.switch-slider');
        const $salesNumber = $('#sales-number');
        
        if (toggleState === 0) {
            // Position 1: Sale (S) - left position (blue) — separate INV series
            $slider.css('left', '3px');
            $toggle.css('background', '#2563eb');
            $('#sale-status').val('pending');
            $('input[name="status"]').val('pending');
            $.get('{{ route("sales.next.invoice.number") }}').then(function(r) {
                if (r.number != null) $salesNumber.text('INV #' + r.number);
            }).fail(function() { $salesNumber.text('INV #00001'); });
            $('#page-title-text').text('Create Sales');
            $('#submit-sale-btn').html('<i class="ti ti-check me-1"></i> Save Sale');
            if (salesItems.length > 0) $('#payment-section').show();
            
        } else if (toggleState === 1) {
            // Position 2: Estimate (E) - middle position (yellow) — separate EST series
            $slider.css('left', '28px');
            $toggle.css('background', '#ffc107');
            $('#sale-status').val('estimate');
            $('input[name="status"]').val('estimate');
            $.get('{{ route("sales.next.estimate.number") }}').then(function(r) {
                if (r.number != null) $salesNumber.text('EST #' + r.number);
            }).fail(function() { $salesNumber.text('EST #00001'); });
            $('#page-title-text').text('Create Estimate');
            $('#submit-sale-btn').html('<i class="ti ti-check me-1"></i> Save Estimate');
            $('#payment-section').hide();
            
        } else if (toggleState === 2) {
            // Position 3: Order (O) - right position (green) — separate SO series
            $slider.css('left', '53px');
            $toggle.css('background', '#198754');
            $('#sale-status').val('sale_order');
            $('input[name="status"]').val('sale_order');
            $.get('{{ route("sales.next.sale.order.number") }}').then(function(r) {
                if (r.number != null) $salesNumber.text('SO #' + r.number);
            }).fail(function() { $salesNumber.text('SO #00001'); });
            $('#page-title-text').text('Create Sale Order');
            $('#submit-sale-btn').html('<i class="ti ti-check me-1"></i> Save Sale Order');
            if (salesItems.length > 0) $('#payment-section').show();
        }
        
        console.log('Toggle switched to:', toggleState === 0 ? 'Sale (S)' : toggleState === 1 ? 'Estimate (E)' : 'Order (O)');
    });
    
    // Vehicle Management
    let vehicles = []; // Array to store vehicles (from DB when customer selected + newly added)
    
    // Load customer's vehicles from database and show below Add Vehicle button
    function loadCustomerVehicles(customerId) {
        if (!customerId) {
            vehicles = [];
            if (typeof displayVehicles === 'function') displayVehicles();
            return;
        }
        $.ajax({
            url: '{{ url("/customers") }}/' + customerId + '/vehicles',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res && res.success && Array.isArray(res.vehicles)) {
                    vehicles = res.vehicles.map(function(car) {
                        return {
                            id: 'db-' + car.id,
                            dbId: car.id,
                            customerId: String(car.customerId),
                            plateNumber: car.plateNumber || '',
                            make: car.make || '',
                            model: car.model || '',
                            year: car.year || '',
                            oil_capacity: car.oil_capacity || '',
                            current_km: car.current_km || '',
                            daily_run_km: car.daily_run_km || ''
                        };
                    });
                } else {
                    vehicles = [];
                }
                if (typeof displayVehicles === 'function') displayVehicles();
                // Auto-expand first vehicle so it shows like "ACTIVE VEHICLE" with metrics (as when user clicks)
                if (vehicles.length > 0) {
                    setTimeout(function() {
                        const $firstCard = $('#vehicles-list .vehicle-card').first();
                        if ($firstCard.length) {
                            const $metrics = $firstCard.find('.vehicle-metrics');
                            $firstCard.addClass('vehicle-card-expanded').css({ 'width': '100%', 'grid-column': '1 / -1' });
                            $metrics.show();
                        }
                    }, 50);
                }
            },
            error: function() {
                vehicles = [];
                if (typeof displayVehicles === 'function') displayVehicles();
            }
        });
    }
    
    // Open vehicle modal
    $('#add-vehicle-btn').on('click', function() {
        // Check if customer is selected first
        const customerId = $('#customer_id').val();
        if (!customerId) {
            alert('Please select a customer first before adding vehicle');
            $('#customer_id').focus();
            return;
        }
        
        $('#vehicle-modal').appendTo('body');
        $('#vehicle-modal').modal('show');
        // Reset form
        $('#vehicle-form')[0].reset();
    });
    
    // Move vehicle modal to body when shown (so it is clickable)
    $('#vehicle-modal').on('show.bs.modal', function() {
        $('#vehicle-modal').appendTo('body');
    });
    $('#vehicle-modal').on('shown.bs.modal', function() {
        $('#vehicle-modal').css({ 'pointer-events': 'auto', 'z-index': 9999 });
        $('#vehicle-modal').find('.modal-dialog, .modal-content, .modal-body, .modal-footer, .modal-header').css('pointer-events', 'auto');
        var $backdrop = $('.modal-backdrop').last();
        if ($backdrop.length) $('#vehicle-modal').insertAfter($backdrop);
    });
    
    // Function to save vehicle (saves to database immediately with selected customer_id)
    function saveVehicle(closeModal = true) {
        // Check if customer is selected first
        const customerId = $('#customer_id').val();
        if (!customerId) {
            alert('Please select a customer first before adding vehicle');
            $('#customer_id').focus();
            return false;
        }
        
        const plateNumber = $('#vehicle-plate-number').val().trim().toUpperCase();
        const make = $('#vehicle-make').val().trim();
        const model = $('#vehicle-model').val().trim();
        const year = $('#vehicle-year').val().trim();
        
        // Validation
        if (!plateNumber || !make || !model || !year) {
            alert('Please fill in all required fields');
            return false;
        }
        
        // Validate year format
        const yearNum = parseInt(year);
        if (isNaN(yearNum) || yearNum < 1950 || yearNum > new Date().getFullYear() + 1) {
            alert('Please enter a valid year (1950 to ' + (new Date().getFullYear() + 1) + ')');
            return false;
        }
        
        // Check if vehicle with same plate number already in list
        const existingInList = vehicles.find(v => v.plateNumber === plateNumber);
        if (existingInList && !confirm('Vehicle with this plate number already in list. Update it?')) {
            return false;
        }
        
        // Save to database immediately (selected customer's ID)
        const savePayload = {
            customer_id: customerId,
            plate_number: plateNumber,
            make: make,
            model: model,
            year: year,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        let saveSucceeded = false;
        $.ajax({
            url: '{{ route("customer.vehicles.store") }}',
            type: 'POST',
            data: savePayload,
            async: false,
            success: function(res) {
                if (res && res.success) {
                    saveSucceeded = true;
                    if (existingInList) {
                        existingInList.customerId = customerId;
                        existingInList.make = make;
                        existingInList.model = model;
                        existingInList.year = year;
                        if (res.vehicle && res.vehicle.id) existingInList.dbId = res.vehicle.id;
                    } else {
                        vehicles.push({
                            id: Date.now(),
                            dbId: (res.vehicle && res.vehicle.id) ? res.vehicle.id : null,
                            customerId: customerId,
                            plateNumber: plateNumber,
                            make: make,
                            model: model,
                            year: year
                        });
                    }
                }
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || (xhr.responseJSON && xhr.responseJSON.errors) ? JSON.stringify(xhr.responseJSON.errors) : (xhr.statusText || 'Failed to save vehicle');
                alert('Could not save vehicle: ' + msg);
            }
        });
        
        if (!saveSucceeded) return false;
        
        // Update display
        displayVehicles();
        
        if (closeModal) {
            $('#vehicle-modal').modal('hide');
            $('#vehicle-form')[0].reset();
        } else {
            $('#vehicle-form')[0].reset();
            $('#vehicle-plate-number').focus();
        }
        
        return true;
    }
    
    // Save vehicle and close modal
    $('#save-vehicle-btn').on('click', function() {
        saveVehicle(true);
    });
    
    // Save vehicle and add another
    $('#save-add-another-btn').on('click', function() {
        saveVehicle(false);
    });
    
    // Add model button (for future functionality)
    $('#add-model-btn').on('click', function() {
        // This can be used to add a new model or search for models
        alert('Add model functionality - can be implemented later');
    });
    
    // Display vehicles
    function displayVehicles() {
        const $vehiclesList = $('#vehicles-list');
        const $displaySection = $('#vehicle-display-section');
        
        if (vehicles.length === 0) {
            $displaySection.hide();
            return;
        }
        
        $displaySection.show();
        $vehiclesList.empty();
        
        vehicles.forEach(function(vehicle) {
            const dbId = vehicle.dbId || vehicle.id;
            const canEdit = !!vehicle.dbId;
            const vehicleCard = `
                <div class="card mb-3 vehicle-card" data-vehicle-id="${vehicle.id}" data-db-id="${dbId}" data-customer-id="${vehicle.customerId || ''}" style="border: 1px solid #e0e0e0; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: #f8f9fa; transition: all 0.3s ease;">
                    <div class="card-body p-3">
                        <!-- Top Section - Clickable -->
                        <div class="position-relative mb-0 vehicle-header" style="cursor: pointer;" data-vehicle-id="${vehicle.id}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="vehicle-display-plate" style="flex: 1;">
                                    <p class="mb-1 fw-bold text-uppercase" style="color: #4a90e2; font-size: 11px; letter-spacing: 0.5px;">ACTIVE VEHICLE</p>
                                    <h5 class="mb-1 fw-bold vehicle-plate-text" style="color: #1e3a8a; font-size: 18px;">${vehicle.plateNumber || ''}</h5>
                                    <p class="mb-0 fw-semibold vehicle-make-model-text" style="color: #1e3a8a; font-size: 14px;">${(vehicle.make || '').toUpperCase()} ${(vehicle.model || '').toUpperCase()}</p>
                                </div>
                                <div class="d-flex gap-1 vehicle-actions">
                                    ${canEdit ? `<button type="button" class="btn btn-sm btn-outline-primary edit-vehicle-sale-btn" data-db-id="${vehicle.dbId}" data-customer-id="${vehicle.customerId || ''}" data-plate="${vehicle.plateNumber || ''}" data-make="${vehicle.make || ''}" data-model="${vehicle.model || ''}" data-year="${vehicle.year || ''}" style="border-radius: 50%; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; z-index: 10;" title="Edit vehicle"><i class="ti ti-edit" style="font-size: 14px;"></i></button>` : ''}
                                    <button type="button" class="btn btn-sm remove-vehicle-btn" data-vehicle-id="${vehicle.id}" style="background: #dc3545; color: white; border-radius: 50%; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; z-index: 10;">
                                        <i class="ti ti-x" style="font-size: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bottom Section - Metrics (Hidden by default) -->
                        <div class="vehicle-metrics" data-vehicle-id="${vehicle.id}" style="display: none;">
                            <!-- Separator Line -->
                            <hr style="margin: 12px 0; border-top: 1px solid #e0e0e0; opacity: 0.5;">
                            
                            <!-- Metrics Boxes -->
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="rounded p-2" style="background: #f0f0f0; border: 1px solid #e0e0e0;">
                                        <p class="mb-1 text-uppercase" style="color: #666; font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">OIL CAPACITY</p>
                                        <input type="text" class="form-control form-control-sm border-0 p-0 bg-transparent vehicle-oil-capacity-input" data-vehicle-id="${vehicle.id}" placeholder="0 KM" value="${vehicle.oil_capacity || ''}" style="color: #1e3a8a; font-size: 12px; font-weight: bold;">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="rounded p-2" style="background: #e8f4f8; border: 1px solid #b3d9e6;">
                                        <p class="mb-1 text-uppercase" style="color: #4a90e2; font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">CURRENT KM</p>
                                        <input type="text" class="form-control form-control-sm border-0 p-0 bg-transparent vehicle-current-km-input" data-vehicle-id="${vehicle.id}" placeholder="..." value="${vehicle.current_km || ''}" style="color: #1e3a8a; font-size: 12px; font-weight: bold;">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="rounded p-2" style="background: #f3e8ff; border: 1px solid #d4b3ff;">
                                        <p class="mb-1 text-uppercase" style="color: #9333ea; font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">DAILY RUN KM</p>
                                        <input type="text" class="form-control form-control-sm border-0 p-0 bg-transparent vehicle-daily-run-km-input" data-vehicle-id="${vehicle.id}" placeholder="KM..." value="${vehicle.daily_run_km || ''}" style="color: #1e3a8a; font-size: 12px; font-weight: bold;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $vehiclesList.append(vehicleCard);
        });
    }
    
    // Remove vehicle
    $(document).on('click', '.remove-vehicle-btn', function(e) {
        e.stopPropagation(); // Prevent triggering the header click
        const vehicleId = parseInt($(this).data('vehicle-id'));
        if (confirm('Are you sure you want to remove this vehicle?')) {
            vehicles = vehicles.filter(v => v.id !== vehicleId);
            displayVehicles();
        }
    });
    
    // Update vehicle metrics from editable inputs (keep in sync with vehicles array)
    $(document).on('input change', '.vehicle-current-km-input, .vehicle-daily-run-km-input, .vehicle-oil-capacity-input', function() {
        const vehicleId = $(this).data('vehicle-id');
        const $input = $(this);
        const val = $input.val().trim();
        const name = $input.hasClass('vehicle-current-km-input') ? 'current_km' : ($input.hasClass('vehicle-daily-run-km-input') ? 'daily_run_km' : 'oil_capacity');
        const v = vehicles.find(function(ve) { return String(ve.id) === String(vehicleId); });
        if (v) v[name] = val;
    });
    
    // Edit vehicle (sales) - open modal and fill form
    $(document).on('click', '.edit-vehicle-sale-btn', function(e) {
        e.stopPropagation();
        window._editVehicleFromEditCustomerId = null;
        const dbId = $(this).data('db-id');
        if (!dbId) return;
        $('#editVehicleSaleId').val(dbId);
        $('#editVehicleSalePlate').val($(this).data('plate') || '');
        $('#editVehicleSaleMake').val($(this).data('make') || '');
        $('#editVehicleSaleModel').val($(this).data('model') || '');
        $('#editVehicleSaleYear').val($(this).data('year') || '');
        $('#editVehicleSaleModal').modal('show');
    });
    
    // Edit vehicle from Edit Customer modal - open same modal, on success refresh edit-customer vehicles list
    $(document).on('click', '.edit-vehicle-in-edit-customer-btn', function(e) {
        e.preventDefault();
        const carId = $(this).data('car-id');
        const customerIdForRefresh = $(this).data('customer-id') || $('#customer_id').val();
        if (!carId) return;
        window._editVehicleFromEditCustomerId = customerIdForRefresh;
        $('#editVehicleSaleId').val(carId);
        $('#editVehicleSalePlate').val($(this).data('plate') || '');
        $('#editVehicleSaleMake').val($(this).data('make') || '');
        $('#editVehicleSaleModel').val($(this).data('model') || '');
        $('#editVehicleSaleYear').val($(this).data('year') || '');
        $('#editVehicleSaleModal').modal('show');
    });
    
    // Keep Vehicle Edit modal (and its backdrop) on top when opened over Edit Customer modal
    $('#editVehicleSaleModal').on('shown.bs.modal', function() {
        $(this).css('z-index', 1065);
        $('.modal-backdrop').last().css('z-index', 1060);
    });
    
    // Edit vehicle form submit - PUT to customer-vehicles and refresh list
    $('#editVehicleSaleForm').on('submit', function(e) {
        e.preventDefault();
        const vehicleId = $('#editVehicleSaleId').val();
        if (!vehicleId) return;
        const plate = $('#editVehicleSalePlate').val().trim();
        const make = $('#editVehicleSaleMake').val().trim();
        const model = $('#editVehicleSaleModel').val().trim();
        const year = $('#editVehicleSaleYear').val().trim();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);
        $.ajax({
            url: '/customer-vehicles/' + vehicleId,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT',
                plate_number: plate,
                make: make,
                model: model,
                year: year
            },
            success: function() {
                const v = vehicles.find(function(ve) { return String(ve.dbId) === String(vehicleId); });
                if (v) {
                    v.plateNumber = plate;
                    v.make = make;
                    v.model = model;
                    v.year = year;
                }
                if (typeof displayVehicles === 'function') displayVehicles();
                $('#editVehicleSaleModal').modal('hide');
                // If edit was opened from Edit Customer modal, refresh that modal's vehicles list
                const refreshCustomerId = window._editVehicleFromEditCustomerId;
                if (refreshCustomerId) {
                    window._editVehicleFromEditCustomerId = null;
                    const vehiclesUrl = '{{ url(route("customer.vehicles.index", ["customer" => "__ID__"])) }}'.replace('__ID__', refreshCustomerId);
                    $.get(vehiclesUrl).done(function(res) {
                        const $list = $('#edit-customer-vehicles-list');
                        if (!$list.length) return;
                        if (res.success && res.vehicles && res.vehicles.length > 0) {
                            let html = '<div class="d-flex flex-column gap-2" style="display: grid !important; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">';
                            res.vehicles.forEach(function(v) {
                                const plate = (v.plateNumber || '—').toString();
                                const make = (v.make || '').toString();
                                const model = (v.model || '').toString();
                                const year = (v.year || '—').toString();
                                const carId = (v.id || '').toString();
                                const customerIdForVehicle = (v.customerId || refreshCustomerId || '').toString();
                                html += '<div class="card mb-0 edit-customer-vehicle-card position-relative" style="border: 1px solid #e0e0e0; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: #f8f9fa;">';
                                html += '<div class="card-body p-3">';
                                html += '<button type="button" class="btn btn-sm btn-outline-primary edit-vehicle-in-edit-customer-btn position-absolute top-0 end-0 m-2" style="padding: 2px 8px; z-index: 5;" title="Edit vehicle" data-car-id="' + carId + '" data-customer-id="' + customerIdForVehicle + '" data-plate="' + (v.plateNumber || '').toString().replace(/"/g, '&quot;') + '" data-make="' + make.replace(/"/g, '&quot;') + '" data-model="' + model.replace(/"/g, '&quot;') + '" data-year="' + year.replace(/"/g, '&quot;') + '"><i class="ti ti-edit" style="font-size: 14px;"></i></button>';
                                html += '<p class="mb-1 fw-bold text-uppercase" style="color: #4a90e2; font-size: 11px; letter-spacing: 0.5px;">ACTIVE VEHICLE</p>';
                                html += '<h6 class="mb-1 fw-bold vehicle-card-plate" style="color: #1e3a8a; font-size: 16px;">' + plate + '</h6>';
                                html += '<p class="mb-0 fw-semibold vehicle-card-make-model" style="color: #1e3a8a; font-size: 13px;">' + (make && model ? make.toUpperCase() + ' ' + model.toUpperCase() : (make || model || '—').toString().toUpperCase()) + '</p>';
                                html += '<p class="mb-0 small text-muted mt-1 vehicle-card-year">Year: ' + year + '</p>';
                                html += '</div></div>';
                            });
                            html += '</div>';
                            $list.html(html);
                        }
                    });
                }
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseText || 'Failed to update vehicle.');
                alert(msg);
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });
    
    // Toggle vehicle metrics on header click and expand card width (ignore clicks on Edit/Remove)
    $(document).on('click', '.vehicle-header', function(e) {
        if ($(e.target).closest('.edit-vehicle-sale-btn, .remove-vehicle-btn').length) return;
        e.stopPropagation();
        const vehicleId = $(this).data('vehicle-id');
        const $vehicleCard = $(this).closest('.vehicle-card');
        const $metrics = $(`.vehicle-metrics[data-vehicle-id="${vehicleId}"]`);
        
        // Check if card is already expanded
        const isExpanded = $vehicleCard.hasClass('vehicle-card-expanded');
        
        // Collapse all other expanded cards first
        $('.vehicle-card-expanded').not($vehicleCard).each(function() {
            $(this).removeClass('vehicle-card-expanded').css({
                'width': '',
                'grid-column': ''
            });
            const otherVehicleId = $(this).data('vehicle-id');
            $(`.vehicle-metrics[data-vehicle-id="${otherVehicleId}"]`).slideUp(300);
        });
        
        if (isExpanded) {
            // Collapse: restore original width
            $vehicleCard.removeClass('vehicle-card-expanded').css({
                'width': '',
                'grid-column': ''
            });
            $metrics.slideUp(300);
        } else {
            // Expand: make it full width
            $vehicleCard.addClass('vehicle-card-expanded').css({
                'width': '100%',
                'grid-column': '1 / -1' // Span all columns
            });
            $metrics.slideDown(300);
        }
    });
    
    // Add vehicles to form data on submit (hook into existing form submission)
    $(document).on('submit', '#salesForm', function(e) {
        // Add vehicles as hidden inputs before submitting
        // Remove existing vehicle inputs first
        $('input[name^="vehicles["]').remove();
        
        // Sync editable vehicle metrics from DOM to vehicles array before submit
        $('.vehicle-current-km-input, .vehicle-daily-run-km-input, .vehicle-oil-capacity-input').each(function() {
            const vehicleId = $(this).data('vehicle-id');
            const val = $(this).val().trim();
            const name = $(this).hasClass('vehicle-current-km-input') ? 'current_km' : ($(this).hasClass('vehicle-daily-run-km-input') ? 'daily_run_km' : 'oil_capacity');
            const v = vehicles.find(function(ve) { return String(ve.id) === String(vehicleId); });
            if (v) v[name] = val;
        });
        
        vehicles.forEach(function(vehicle, index) {
            // Add new inputs with customer_id
            const customerId = vehicle.customerId || $('#customer_id').val();
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][customer_id]`,
                value: customerId
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][plate_number]`,
                value: vehicle.plateNumber
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][make]`,
                value: vehicle.make
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][model]`,
                value: vehicle.model
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][year]`,
                value: vehicle.year
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][oil_capacity]`,
                value: vehicle.oil_capacity || ''
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][current_km]`,
                value: vehicle.current_km || ''
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][daily_run_km]`,
                value: vehicle.daily_run_km || ''
            }).appendTo('#salesForm');
        });
    });
    
    // ========== Delivery Entry Functions ==========
    
    // Delivery entry - open modal (move to body so modal is clickable)
    $('#delivery-entry-btn').on('click', function() {
        $('#delivery-modal').appendTo('body');
        $('#delivery-modal').modal('show');
    });
    
    // Move delivery modal to body when shown and ensure it is above backdrop
    $('#delivery-modal').on('show.bs.modal', function() {
        $('#delivery-modal').appendTo('body');
    });
    $('#delivery-modal').on('shown.bs.modal', function() {
        $('#delivery-modal').css({ 'pointer-events': 'auto', 'z-index': 9999 });
        $('#delivery-modal').find('.modal-dialog, .modal-content, .modal-body, .modal-footer, .modal-header').css('pointer-events', 'auto');
        var $backdrop = $('.modal-backdrop').last();
        if ($backdrop.length) $('#delivery-modal').insertAfter($backdrop);
    });
    
    // Handle worker profile photo upload
    window.handleWorkerProfilePhoto = function(input) {
        const preview = $('#worker-profile-photo-preview');
        const icon = $('#worker-profile-icon-placeholder');
        const text = $('#worker-profile-text-placeholder');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageUrl = e.target.result;
                $('#worker-profile-preview-img').attr('src', imageUrl);
                preview.css('display', 'flex');
                icon.hide();
                text.hide();
            };
            reader.readAsDataURL(input.files[0]);
        }
    };
    
    // Click handler for worker profile upload label
    $(document).on('click', 'label[for="worker-profile-photo"], label:has(#worker-profile-photo)', function(e) {
        e.preventDefault();
        $('#worker-profile-photo').click();
    });
    
    // Handle vehicle photo uploads
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
    };
    
    // Confirm delivery entry
    $('#confirm-delivery-btn').on('click', function() {
        // Get worker profile photo
        const workerProfilePhoto = $('#worker-profile-photo')[0].files[0] || null;
        let workerName = $('#worker-name').val().trim() || '';
        const workerMobile = $('#worker-mobile').val().trim() || '';
        
        // Save worker profile to database if profile photo or name/mobile is provided
        if (workerProfilePhoto || workerName || workerMobile) {
            const formData = new FormData();
            if (workerProfilePhoto) {
                formData.append('profile_img', workerProfilePhoto);
            }
            // Name is required by controller, so provide default if not given
            if (!workerName) {
                workerName = 'Delivery Worker ' + new Date().getTime();
            }
            formData.append('name', workerName);
            if (workerMobile) {
                formData.append('mobile', workerMobile);
            }
            const branchId = $('#salesBranchId').val();
            if (branchId) {
                formData.append('branch_id', branchId);
            }
            
            // Save worker profile via AJAX
            $.ajax({
                url: '/car-wash/workers',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Worker profile saved:', response);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Worker Saved!',
                            text: 'Worker profile has been saved successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error saving worker profile:', xhr);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to save worker profile. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }
        
        // Get vehicle photo details if added
        const vehiclePhoto = $('#vehicle-photo-capture')[0].files[0] || null;
        const vehicleRiderPhoto = $('#vehicle-rider-photo')[0].files[0] || null;
        const idCardFrontPhoto = $('#id-card-front-photo')[0].files[0] || null;
        const idCardBackPhoto = $('#id-card-back-photo')[0].files[0] || null;
        const currentVehiclePhoto = $('#current-vehicle-photo')[0].files[0] || null;
        
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
                worker_name: workerName,
                worker_mobile: workerMobile,
                worker_profile: workerProfilePhoto,
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
            $('#worker-name').val('');
            $('#worker-mobile').val('');
            $('#worker-profile-photo').val('');
            $('#vehicle-photo-capture').val('');
            $('#vehicle-rider-photo').val('');
            $('#id-card-front-photo').val('');
            $('#id-card-back-photo').val('');
            $('#current-vehicle-photo').val('');
            $('#worker-profile-photo-preview').css('display', 'none');
            $('#worker-profile-icon-placeholder').show();
            $('#worker-profile-text-placeholder').show();
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
    
    // Reset delivery modal when closed
    $('#delivery-modal').on('hidden.bs.modal', function() {
        // Hide items list
        $('#delivery-items-list').hide();
        $('#delivery-items-ul').empty();
        // Reset worker fields
        $('#worker-name').val('');
        $('#worker-mobile').val('');
        $('#worker-profile-photo').val('');
        $('#worker-profile-photo-preview').css('display', 'none');
        $('#worker-profile-icon-placeholder').show();
        $('#worker-profile-text-placeholder').show();
        // Reset delivery fields
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
    });
});
</script>
@endpush

{{-- Add Bank Payment Modal (multiple bank payments) --}}
<div class="modal fade" id="addBankPaymentModal" tabindex="-1" aria-labelledby="addBankPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #faf5ff; border-bottom: 1px solid #e9d5ff;">
                <h5 class="modal-title fw-bold" id="addBankPaymentModalLabel" style="color: #9333ea;"><i class="ti ti-building-bank me-1"></i>Add Bank Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size: 12px; color: #6b7280;">AMOUNT (Rs)</label>
                    <input type="number" id="bank_modal_amount" class="form-control" step="1" min="0" value="0" placeholder="0" style="border-radius: 6px; text-align: right;">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size: 12px; color: #6b7280;">REFERENCE NUMBER</label>
                    <input type="text" id="bank_modal_reference" class="form-control" placeholder="Enter reference / transaction ID" style="border-radius: 6px;">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size: 12px; color: #6b7280;">ATTACH PHOTO</label>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="bank_modal_photo_btn">
                            <i class="ti ti-camera me-1"></i>Choose Photo
                        </button>
                        <input type="file" id="bank_modal_photo" class="d-none" accept="image/*">
                        <span id="bank_modal_photo_name" class="text-muted small"></span>
                    </div>
                    <div id="bank_modal_photo_preview" class="mt-2" style="display: none;">
                        <img id="bank_modal_photo_preview_img" src="" alt="Preview" class="img-thumbnail" style="max-width: 120px; max-height: 120px; object-fit: cover;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="bank_modal_add_btn" style="background: #9333ea; border-color: #9333ea;">
                    <i class="ti ti-plus me-1"></i>Add Payment
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Full Add Customer Modal (from mobile dropdown "Add New Customer" - same as customers page) --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="addCustomerModalLabel">Add Customer</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @include('admin.customers.modals.create-customer-form')
        </div>
    </div>
</div>

{{-- Customer Edit Modal (no overlay/backdrop so page stays visible) --}}
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="editCustomerModalLabel">Edit Customer</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCustomerForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3 p-3">
                        <!-- Branch -->
                        <div class="col-12">
                            <label for="edit_customer_branch_id" class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="edit_customer_branch_id" class="form-select" required>
                                <option value="">Select branch</option>
                                @if(isset($branches) && $branches->isNotEmpty())
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->branch_name }}{{ $b->branch_code ? ' (' . $b->branch_code . ')' : '' }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <!-- Visiting Document -->
                        <div class="col-md-6">
                            <label for="edit_visiting_doc" class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Visiting Document</label>
                            <input type="file" name="visiting_doc" id="edit_visiting_doc" accept=".pdf,.doc,.docx,image/*" class="form-control">
                            <small class="form-text text-muted d-block mb-1">Upload visiting card or document (PDF, DOC, DOCX, or image).</small>
                            <div class="mt-2 p-2 border rounded bg-light" style="min-height: 60px;">
                                <small class="text-muted d-block mb-1" style="font-size: 10px;">Preview</small>
                                <div id="edit_visiting_doc_preview" class="edit-file-preview"></div>
                            </div>
                        </div>

                        <!-- Profile Image -->
                        <div class="col-md-6">
                            <label for="edit_profile_img" class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Profile Image</label>
                            <input type="file" name="profile_img" id="edit_profile_img" accept="image/*" class="form-control">
                            <small class="form-text text-muted d-block mb-1">Click to upload profile image</small>
                            <div class="mt-2 p-2 border rounded bg-light" style="min-height: 60px;">
                                <small class="text-muted d-block mb-1" style="font-size: 10px;">Preview</small>
                                <div id="edit_profile_img_preview" class="edit-file-preview" style="display: none;">
                                    <img id="edit_profile_img_display" src="" alt="Profile Preview" class="img-fluid rounded" style="max-height: 180px;">
                                </div>
                            </div>
                        </div>

                        <!-- Name & Phone -->
                        <div class="col-12">
                            <div id="edit_namePhoneContainer">
                                <div class="row g-3 mb-3 align-items-end name-phone-row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="hidden" name="names[]" id="edit_customer_names" class="edit-name-hidden" required>
                                            <div contenteditable="true" class="form-control edit-name-display" id="edit_customer_names_display" data-placeholder="Enter name" style="min-height: 38px; font-size: 0.875rem;"></div>
                                            <button type="button" class="btn btn-danger remove-row" style="display:none;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">WhatsApp Number</label>
                                        <input type="text" name="phones[]" id="edit_customer_phones" class="form-control" placeholder="Enter phone number" required>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="edit_add_more_name_phone" class="btn btn-sm btn-primary">
                                <i class="ti ti-plus me-1"></i> Add More Name & Phone
                            </button>
                        </div>

                        <!-- Company -->
                        <div class="col-md-6">
                            <label for="edit_customer_company" class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Company</label>
                            <input type="text" name="company" id="edit_customer_company" class="form-control">
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="edit_customer_email" class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Email</label>
                            <input type="email" name="email" id="edit_customer_email" class="form-control">
                        </div>

                        <!-- Address -->
                        <div class="col-md-6">
                            <label for="edit_customer_address" class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Address</label>
                            <input type="text" name="address" id="edit_customer_address" class="form-control">
                        </div>

                        <!-- Area -->
                        <div class="col-md-6">
                            <label for="edit_customer_area" class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Area</label>
                            <input type="text" name="area" id="edit_customer_area" class="form-control">
                        </div>

                        <!-- Customer Vehicles (loaded when modal opens; scroll down to see — click Edit on a vehicle to change it) -->
                        <div class="col-12 mt-3" id="edit-customer-vehicles-section">
                            <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">Customer Vehicles</label>
                            <p class="small text-muted mb-2" style="font-size: 11px;">Vehicles listed below. Click <strong>Edit</strong> on a vehicle to open the edit form.</p>
                            <div id="edit-customer-vehicles-container" class="border rounded p-3 bg-light" style="min-height: 60px;">
                                <div id="edit-customer-vehicles-list" class="small">
                                    <span class="text-muted">Loading vehicles…</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function runWhenJQueryReady() {
  if (typeof window.jQuery === 'undefined') {
    window.setTimeout(runWhenJQueryReady, 30);
    return;
  }
  var $ = window.jQuery;
  // Handle customer edit form submission
  $(document).ready(function() {
    $('#editCustomerForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        
        // Show loading
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        const formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(response) {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Customer updated successfully!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#editCustomerModal').modal('hide');
                    // Reload page to refresh customer data
                    location.reload();
                });
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
                
                let errorMessage = 'Failed to update customer.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
            }
        });
    });
  });
})();
</script>

@endsection
