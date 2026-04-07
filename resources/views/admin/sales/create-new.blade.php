@extends('layouts.app')

@php
    $isEditSale = isset($sale) && $sale;
@endphp

@section('title', $isEditSale ? 'Edit Sale' : 'Create Sales')

@push('styles')
<style>
@include('admin.partials.payment-panel-totals-styles')
</style>
@endpush

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4 id="page-title-text">{{ $isEditSale ? 'Edit Sale' : 'Create Sales' }}</h4>
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
                        @if($isEditSale)
                            <input type="hidden" name="existing_sale_id" value="{{ $sale->id }}">
                        @endif
                        
                        <!-- ACTIVE BRANCH Selector (Pill-shaped like Gemini design) -->
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center px-3 py-2 rounded-pill" style="border: 1px solid #0d6efd; background: #f8f9fa;">
                                <i class="ti ti-user me-2 text-muted"></i>
                                <span class="fw-bold me-2 text-uppercase" style="font-size: 12px;">ACTIVE BRANCH:</span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-primary p-0 text-decoration-none dropdown-toggle fw-bold" type="button" id="branchDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 14px;">
                                        <span id="selectedBranchName">{{ $isEditSale ? ($sale->branch->branch_name ?? 'Branch') : session('selected_branch_name', 'Select Branch') }}</span>
                                        @if($isEditSale && optional($sale->branch)->branch_code)
                                            <span id="selectedBranchCode"> ({{ $sale->branch->branch_code }})</span>
                                        @elseif(!$isEditSale && session('selected_branch_code'))
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
                            <input type="hidden" name="branch_id" id="salesBranchId" value="{{ $isEditSale ? $sale->branch_id : session('selected_branch_id') }}" required>
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
                                        <span class="text-primary fw-bold" style="font-size: 18px;" id="sales-number">{{ $isEditSale ? e($sale->reference ?? ('INV #' . str_pad((string)$sale->id, 5, '0', STR_PAD_LEFT))) : 'INV #00001' }}</span>
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
                        <input type="hidden" name="sale_date" id="sale_date" value="{{ $isEditSale ? (optional($sale->sale_date)->format('Y-m-d') ?? date('Y-m-d')) : date('Y-m-d') }}" required>
                        <input type="hidden" name="status" id="sale-status" value="{{ $isEditSale ? e($sale->status ?? 'pending') : 'pending' }}">

                        <!-- Vehicle Section (Like Screenshot) -->
                        <!-- Customer Information (Like Screenshot Design) -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3" id="sales-mobile-column">
                                <label class="form-label fw-bold mb-2 text-uppercase" style="font-size: 11px; color: #6c757d;">PARTY NAME / VEHICLE #</label>
                                <div id="customer-branch-display" class="small mb-1" style="font-size: 10px; color: #6c757d; display: none;">
                                    <span class="text-muted">Branch:</span> <span id="customer-branch-name"></span>
                                </div>
                                <div class="party-name-field-wrapper" id="party-name-field-wrapper">
                                    <div class="party-name-field-inner">
                                        <select name="customer_id" id="customer_id" class="form-control select2-customer-search @error('customer_id') is-invalid @enderror" required style="border: none; background: transparent;">
                                            <option></option>
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
                                                data-branch-name="{{ $customer->branch_name ?? optional($customer->branch)->branch_name ?? '—' }}"
                                                data-customer-type="{{ $customer->customer_type ?? 'retail' }}"
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
                                                        data-branch-name="{{ $customer->branch_name ?? optional($customer->branch)->branch_name ?? '—' }}"
                                                        data-customer-type="{{ $customer->customer_type ?? 'retail' }}"
                                                        data-plate-number="{{ $vehicle->plate_number ?? '' }}"
                                                        data-search-text="{{ strtolower($vehicleText . ' ' . $vehicle->plate_number) }}">
                                                    {{ $vehicleText }}
                                                </option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                    </select>
                                    </div>
                                    <div class="party-name-actions">
                                        <button type="button" class="btn-party-action" id="edit-party-btn" title="Edit Customer"><i class="ti ti-edit"></i></button>
                                        <button type="button" class="btn-party-action btn-ledger" id="customer-ledger-btn" title="Customer Ledger Report" style="display: none;"><i class="ti ti-file-text me-1"></i>Ledger</button>
                                        <button type="button" class="btn-party-action btn-remove" id="party-clear-btn" title="Remove party" style="display: none;"><i class="ti ti-x"></i></button>
                                    </div>
                                </div>
                                    @error('customer_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted small mt-1" id="customer-required-hint">Select a party/customer first to enable adding sale items.</div>
                            </div>
                            <div class="col-12 mb-3">
                                <div id="mobile-field-branch-display" class="small mb-1" style="font-size: 10px; color: #6c757d; display: none;"><span class="text-muted">Branch:</span> <span id="mobile-field-branch-name"></span></div>
                                <div class="mobile-field-wrapper">
                                    <div class="mobile-field-inner">
                                        <select name="customer_mobile" id="customer_mobile" class="form-control select2-mobile-search" style="border: none; background: transparent;">
                                            <option></option>
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
                                                @if(!empty($customer->phones[0]))
                                                    <option value="cust-{{ $customer->id }}"
                                                            data-customer-id="{{ $customer->id }}"
                                                            data-name="{{ $customer->names[0] ?? '' }}"
                                                            data-phone="{{ $customer->phones[0] ?? '' }}"
                                                            data-company="{{ $customer->company ?? '' }}"
                                                            data-address="{{ $customer->address ?? '' }}"
                                                            data-area="{{ $customer->area ?? '' }}"
                                                            data-search-text="{{ strtolower($displayText) }}">
                                                        {{ $displayText }}
                                                    </option>
                                                @endif
                                                @if($customer->customerCars && $customer->customerCars->count() > 0)
                                                    @foreach($customer->customerCars as $vehicle)
                                                        @php
                                                            $vehicleText = $customerName . ' - ' . $vehicle->plate_number;
                                                            if($vehicle->make) {
                                                                $vehicleText .= ' (' . $vehicle->make;
                                                                if($vehicle->model) {
                                                                    $vehicleText .= ' ' . $vehicle->model;
                                                                }
                                                                // Append year if available
                                                                if(!empty($vehicle->year)) {
                                                                    $vehicleText .= ' ' . $vehicle->year;
                                                                }
                                                                $vehicleText .= ')';
                                                            }
                                                        @endphp
                                                        <option value="car-{{ $vehicle->id }}"
                                                                data-customer-id="{{ $customer->id }}"
                                                                data-name="{{ $customer->names[0] ?? '' }}"
                                                                data-phone="{{ $customer->phones[0] ?? '' }}"
                                                                data-company="{{ $customer->company ?? '' }}"
                                                                data-address="{{ $customer->address ?? '' }}"
                                                                data-area="{{ $customer->area ?? '' }}"
                                                                data-plate-number="{{ $vehicle->plate_number ?? '' }}"
                                                                data-search-text="{{ strtolower($vehicleText . ' ' . $vehicle->plate_number) }}">
                                                            {{ $vehicleText }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mobile-actions">
                                        <button type="button" class="btn-mobile-edit" id="edit-mobile-btn" title="Edit Customer">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn-mobile-ledger" id="customer-ledger-btn-mobile" title="Customer Ledger Report" style="display: none;"><i class="ti ti-file-text me-1"></i>Ledger</button>
                                        <span id="mobile-customer-balance" class="mobile-customer-balance ms-2" style="display: none;"></span>
                                    </div>
                                </div>
                                <input type="hidden" id="customer_mobile_hidden" name="customer_mobile_hidden" value="">
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

                        <!-- Vehicle section: same order for party name, mobile, or vehicle# search — vehicles first, then ADD / LINK -->
                        <div class="mb-4">
                            <div id="vehicle-display-section" style="display: none;">
                                <p class="text-primary fw-bold mb-2" style="font-size: 11px;">ACTIVE VEHICLES</p>
                                <div id="vehicles-list" class="flex-column gap-2 vehicle-cards-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
                                    <!-- Vehicles will be dynamically added here (summary row only; oil/service fields mount below) -->
                                </div>
                                <div id="vehicle-primary-metrics-panel" class="mt-3 d-none card-like-panel" style="display: none;">
                                    <p class="text-muted fw-bold mb-2 text-uppercase" style="font-size: 10px; letter-spacing: 0.04em;">Oil / service — selected vehicle</p>
                                    <div id="vehicle-primary-metrics-host"></div>
                                </div>
                            </div>
                            <button type="button" class="btn w-100 mt-3" id="add-vehicle-btn" style="background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 12px; padding: 18px; color: #2563eb; font-weight: 900; text-transform: uppercase; font-size: 14px;">
                                <i class="ti ti-car me-2"></i>ADD / LINK VEHICLE DETAILS
                            </button>
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
                            <div id="items-summary-container" class="text-center py-5" style="background: #f8f9fa; border-radius: 8px; min-height: 200px; border: 1px dashed #dee2e6;">
                                <div id="empty-items-state">
                                    <p class="text-muted mb-0" style="font-size: 16px;">No items added yet...</p>
                                </div>
                                <div id="items-list" style="display: none;">
                                    <div class="d-flex justify-content-end align-items-center mb-2 flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm btn-primary" id="sales-print-labels-btn" title="Sab items ke labels print"><i class="ti ti-printer me-1"></i> Print All</button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table mb-0 pehla-items-table" id="sales-items-table">
                                            <thead>
                                                <tr class="pehla-items-thead">
                                                    <th class="pehla-th">WAREHOUSE</th>
                                                    <th class="pehla-th">ITEM</th>
                                                    <th class="pehla-th text-end">TOTAL</th>
                                                    <th class="pehla-th pehla-th-print-select text-center" style="width: 100px;">PRINT / SELECT</th>
                                                    <th class="pehla-th pehla-th-actions"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-tbody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                        <!-- Horizontal Line Separator -->
                        <hr style="border-top: 1px dashed #dee2e6; margin: 20px 0;">

                        <!-- ADD SALE ITEM + Temporary Sale -->
                        <div class="row g-2 mb-4">
                            <div class="col-12 col-md-6">
                                <button type="button" class="btn btn-primary btn-lg w-100" id="add-new-item-btn" disabled style="padding: 20px; font-size: 18px; font-weight: bold;">
                                    <i class="ti ti-plus me-2"></i>ADD SALE ITEM
                                </button>
                            </div>
                            <div class="col-12 col-md-6">
                                <button type="button" class="btn btn-outline-secondary btn-lg w-100" id="temporary-sale-btn" disabled style="padding: 20px; font-size: 15px; font-weight: bold; border-width: 2px;" title="Sell before the product exists in inventory. No stock movement; fix in inventory later.">
                                    <i class="ti ti-bolt me-2"></i>Temporary Sale
                                </button>
                            </div>
                        </div>

                        <!-- Action Buttons Grid: RETURN, CLAIM IN, SCRAP IN, SCRAP SALE -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn w-100" id="return-entry-btn" style="background: #fce7f3; color: #ec4899; border: 1px solid #f9a8d4; padding: 15px; font-weight: bold;">
                                    <i class="ti ti-arrow-back-up me-2"></i>RETURN
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex flex-column gap-2">
                                    <button type="button"
                                            class="btn w-100 d-flex align-items-center justify-content-center"
                                            id="claim-entry-btn"
                                            style="background: #fef3c7; color: #f59e0b; border: 1px solid #fcd34d; padding: 15px; font-weight: bold;">
                                        <span><i class="ti ti-tag me-2"></i>CLAIM IN</span>
                                    </button>

                                    <button type="button"
                                            id="claim-stock-summary-badge"
                                            class="btn btn-sm d-inline-flex align-items-center justify-content-center gap-2 align-self-end"
                                            style="min-width: 0; padding: 6px 14px; border-radius: 999px; background: linear-gradient(135deg, #111827, #1f2937); color: #f9fafb; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.03em; text-transform: uppercase; box-shadow: 0 0 0 1px rgba(148,163,184,0.5); border: none;"
                                            title="View detailed claim stock history"
                                            data-bs-toggle="modal"
                                            data-bs-target="#claim-stock-detail-modal">
                                        <i class="ti ti-clipboard-list" style="font-size: 0.8rem;"></i>
                                        <span>Claim Stock: <span id="claim-stock-summary-text">—</span></span>
                                    </button>

                                    <button type="button"
                                            id="claim-send-stock-summary-badge"
                                            class="btn btn-sm d-inline-flex align-items-center justify-content-center gap-2 align-self-end"
                                            style="min-width: 0; padding: 6px 14px; border-radius: 999px; background: linear-gradient(135deg, #6f42c1, #7c3aed); color: #f9fafb; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.03em; text-transform: uppercase; box-shadow: 0 0 0 1px rgba(167,139,250,0.5); border: none;"
                                            title="View claim send history"
                                            data-bs-toggle="modal"
                                            data-bs-target="#claim-send-stock-detail-modal">
                                        <i class="ti ti-truck-delivery" style="font-size: 0.8rem;"></i>
                                        <span>Claim Send: <span id="claim-send-stock-summary-text">—</span></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex flex-column gap-2">
                                    <button type="button" class="btn w-100" id="scrap-in-btn" style="background: #fed7aa; color: #ea580c; border: 1px solid #fdba74; padding: 15px; font-weight: bold;">
                                        <i class="ti ti-recycle me-2"></i>SCRAP IN
                                    </button>
                                    <button type="button"
                                            id="scrap-stock-summary-badge"
                                            class="btn btn-sm d-inline-flex align-items-center justify-content-center gap-2 align-self-end"
                                            style="min-width: 0; padding: 6px 14px; border-radius: 999px; background: linear-gradient(135deg, #7c2d12, #9a3412); color: #f9fafb; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.03em; text-transform: uppercase; box-shadow: 0 0 0 1px rgba(244,63,94,0.35); border: none;"
                                            title="View detailed scrap stock history"
                                            data-bs-toggle="modal"
                                            data-bs-target="#scrap-stock-detail-modal">
                                        <i class="ti ti-clipboard-list" style="font-size: 0.8rem;"></i>
                                        <span>Scrap Stock: <span id="scrap-stock-summary-text">—</span></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn w-100" id="scrap-sale-btn" style="background: #d1fae5; color: #059669; border: 1px solid #6ee7b7; padding: 15px; font-weight: bold;">
                                    <i class="ti ti-file-text me-2"></i>SCRAP SALE
                                </button>
                            </div>
                        </div>

                        <!-- Payment summary (layout matches Create Purchase — .payment-panel-totals) -->
                        <div class="row mb-4 payment-panel-totals" id="payment-section">
                            <div class="col-12">
                                <div class="total-section">
                                    <div class="total-row">
                                        <p class="mb-0" style="font-size: 10px; font-weight: 700; text-transform: uppercase;">Total Items Amount</p>
                                        <p class="mb-0" style="font-size: 14px; font-weight: 700;">Rs <span id="gross-amount">0</span></p>
                                    </div>
                                    <div class="discount-section">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="discount-label mb-0">Discount (Manual Edit)</p>
                                            <div class="d-flex align-items-center bg-white rounded-lg px-2 border" style="border-color: #bbf7d0 !important;">
                                                <span class="me-2" style="font-size: 10px; font-weight: 900; color: #16a34a; background: #dcfce7; padding: 2px 6px; border-radius: 4px;">Rs</span>
                                                <input type="number" name="discount" id="discount" value="{{ $isEditSale ? number_format((float)($sale->discount ?? 0), 2, '.', '') : '0' }}" min="0" step="0.01" class="bg-transparent text-end border-0 shadow-none discount-amount-input" style="width: 80px; font-weight: 900; color: #16a34a; font-size: 14px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="net-payable">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="net-payable-label mb-0">Net Payable</p>
                                            <p class="net-payable-value mb-0">Rs <span id="net-payable">0</span></p>
                                        </div>
                                    </div>
                                    <div id="cash-received-block" class="received-amount-section">
                                        <p class="received-amount-label mb-2">Cash Received (Amount Rs)</p>
                                        <div id="salesCashPaidWrapper" class="space-y-2">
                                            <div id="cash-received-entries">
                                                <div class="payment-card border-blue-100 no-print purchase-cash-entry-row cash-received-row" data-cash-entry-kind="received">
                                                    <input type="hidden" name="cash_received_is_return[]" class="cash-is-return-input" value="0" autocomplete="off">
                                                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                                        <div class="d-flex align-items-center gap-2 flex-wrap flex-grow-1 min-w-0">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary cash-row-return-toggle flex-shrink-0" title="Mark this line as cash returned to customer">
                                                                <i class="ti ti-corner-up-left me-1" aria-hidden="true"></i> Cash return
                                                            </button>
                                                            <p class="mb-0" style="font-size: 10px; font-weight: 900; color: #374151; text-transform: uppercase;">Cash Entry</p>
                                                            <span class="badge rounded-pill cash-return-badge d-none" style="font-size: 9px; font-weight: 700; background: #fee2e2; color: #b91c1c;">Return to party</span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end min-w-0 purchase-payment-amount-rail">
                                                            <button type="button" class="btn btn-sm btn-outline-primary sales-cash-load-total-row-btn flex-shrink-0" title="Fill this line with the current Total Items Amount">
                                                                <i class="ti ti-bolt me-1" aria-hidden="true"></i> Load total
                                                            </button>
                                                            <div class="d-flex align-items-center purchase-cash-amount-wrap">
                                                                <span class="purchase-cash-prefix text-uppercase">Rs</span>
                                                                <span class="cash-return-amount-prefix" aria-hidden="true" title="Cash returned to customer">_</span>
                                                                <input type="number" name="cash_received[]" class="form-control purchase-cash-input border-0 bg-transparent shadow-none cash-amount-input" value="0" min="0" step="0.01">
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-purchase-cash-row cash-row-close flex-shrink-0" title="Remove"><i class="ti ti-x"></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <label class="d-flex flex-column cursor-pointer bg-white border border-dashed rounded-lg p-2 text-center purchase-cash-pic-label" style="border-color: #bfdbfe;">
                                                            <span class="status-text mb-0" style="font-size: 8px; font-weight: 900; color: #60a5fa; text-transform: uppercase;"><i class="ti ti-camera me-1"></i> Attach Photo</span>
                                                            <input type="file" accept="image/*" class="d-none purchase-cash-pic cash-photo-input" name="cash_photos[]">
                                                        </label>
                                                        <div class="purchase-attach-preview cash-photo-preview mt-2 d-none d-flex align-items-center flex-wrap gap-2">
                                                            <img class="img-thumbnail cash-photo-img" style="max-width: 100px; max-height: 100px; object-fit: cover;" alt="">
                                                            <button type="button" class="btn btn-sm btn-danger remove-cash-photo"><i class="ti ti-x"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="total-row mt-1 pt-2 border-top" style="border-color: #e5e7eb !important;">
                                            <div>
                                                <p class="mb-0" style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #2563eb;">Cash received total <span class="fw-normal text-muted" style="text-transform: none; font-size: 9px;">(net)</span></p>
                                                <p class="mb-0 small text-muted" id="cash-received-breakdown" style="font-size: 9px; display: none;"></p>
                                            </div>
                                            <p class="mb-0 text-end" style="font-size: 14px; font-weight: 700;" id="cash-received-total">Rs 0</p>
                                        </div>
                                        <div class="mt-2 no-print">
                                            <button type="button" class="btn btn-sm w-100" id="add-more-cash-received-btn" style="background-color: #dbeafe; color: #2563eb; border: 1px dashed #93c5fd; border-radius: 12px; padding: 8px; font-size: 9px; font-weight: 900; text-transform: uppercase;">
                                                <i class="ti ti-plus me-1"></i> Add More Cash
                                            </button>
                                        </div>
                                    </div>
                                    <div id="bank-received-block" class="space-y-1 pt-1 border-top" style="border-color: #e5e7eb; padding-top: 8px;">
                                        <p class="mb-2" style="font-size: 9px; font-weight: 900; color: #a855f7; text-transform: uppercase; letter-spacing: 1px; margin-left: 8px;">Bank Received</p>
                                        <div id="salesBankPaidWrapper" class="space-y-2">
                                            <div id="bank-payments-list"></div>
                                        </div>
                                        <div class="total-row mt-1 pt-2 border-top" style="border-color: #e5e7eb !important;">
                                            <p class="mb-0" style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #9333ea;">Bank Received Total</p>
                                            <p class="mb-0" style="font-size: 14px; font-weight: 700;" id="bank-received-total">Rs 0</p>
                                        </div>
                                        <div class="px-2 no-print mt-1">
                                            <button type="button" class="btn btn-sm w-100" id="add-bank-below-btn" style="background-color: #f3e8ff; color: #9333ea; border: 1px dashed #c084fc; border-radius: 12px; padding: 8px; font-size: 9px; font-weight: 900; text-transform: uppercase;">
                                                <i class="ti ti-building-bank me-1"></i> Add Bank Payment
                                            </button>
                                        </div>
                                    </div>
                                    <div id="sales-current-remaining-row" class="total-row" style="color: #ea580c;">
                                        <p class="mb-0" style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #ea580c;">Current Remaining</p>
                                        <p class="mb-0" style="font-size: 14px; font-weight: 700; color: #ea580c;" id="current-remaining">Rs 0</p>
                                    </div>
                                    <div class="sales-previous-balance-section border rounded-3 p-2 px-3" id="sales-previous-balance-section" style="border-color: #fde68a !important;">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                            <div class="flex-shrink-0">
                                                <p class="sales-previous-balance-label mb-1">Previous balance</p>
                                                <p class="mb-0 small text-muted" id="sales-previous-balance-secondary" style="font-size: 10px; max-width: 200px;"></p>
                                            </div>
                                            <div class="text-end flex-grow-1 min-w-0">
                                                <div class="d-flex flex-column align-items-end gap-1">
                                                    <div class="d-flex flex-wrap align-items-baseline justify-content-end gap-2">
                                                        <span class="sales-previous-balance-amount fw-bold" id="sales-previous-balance-amount" style="font-size: 15px;">—</span>
                                                        <span class="text-muted" style="font-size: 11px;">|</span>
                                                        <span class="fw-bold" id="sales-previous-balance-primary-label" style="font-size: 11px;">—</span>
                                                    </div>
                                                    <div class="sales-previous-balance-detail text-muted" id="sales-previous-balance-age" style="font-size: 10px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="previous_balance" id="previous-balance-input" value="0">
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
                            <div class="d-flex flex-wrap align-items-center gap-2 justify-content-end">
                                <a href="{{ route('all_sales') }}" class="btn btn-secondary" style="padding: 12px 30px;">Cancel</a>
                                <button type="button" class="btn btn-outline-primary" id="open-thermal-settings-btn" style="padding: 12px 20px;">
                                    <i class="ti ti-settings me-1"></i>Print Settings
                                </button>
                                <button type="button" class="btn btn-primary" id="bluetooth-print-btn" style="padding: 12px 20px; font-weight: 700;">
                                    <i class="ti ti-printer me-1"></i>Bluetooth Print
                                </button>
                                <button type="submit" class="btn btn-success" id="submit-sale-btn" style="padding: 12px 30px; font-weight: bold;">
                                    <i class="ti ti-check me-1"></i> {{ $isEditSale ? 'Update Sale' : 'Save Sale' }}
                                </button>
                            </div>
                </div>

                        <!-- Hidden fields for order tax, shipping -->
                        <input type="hidden" name="order_tax" id="order_tax" value="{{ $isEditSale ? number_format((float)($sale->order_tax ?? 0), 2, '.', '') : '0' }}">
                        <input type="hidden" name="shipping" id="shipping" value="{{ $isEditSale ? number_format((float)($sale->shipping ?? 0), 2, '.', '') : '0' }}">
                    </form>

                            </div>
                            </div>
                            </div>
    </div>
</div>

<!-- Thermal Print Settings Modal (z-index + body append in JS — same fix as add-item / delivery modals) -->
<div class="modal fade" id="thermal-print-settings-modal" tabindex="-1" aria-labelledby="thermal-print-settings-title" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true" style="z-index: 10065 !important; pointer-events: auto !important;">
    <div class="modal-dialog modal-dialog-centered" style="pointer-events: auto !important;">
        <div class="modal-content" style="pointer-events: auto !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="thermal-print-settings-title">
                    <i class="ti ti-printer me-1"></i>Thermal Print Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Printer Type</label>
                    <select id="thermal-printer-type" class="form-select">
                        <option value="serial">Bluetooth / Serial (COM port — most thermal printers)</option>
                        <option value="bluetooth">Bluetooth BLE only (Web Bluetooth)</option>
                        <option value="usb">USB (WebUSB)</option>
                        <option value="wired">Wired / System print dialog</option>
                    </select>
                    <small class="text-muted d-block mt-1">Most cheap thermal printers use <strong>Classic Bluetooth (SPP)</strong>, not BLE. Pair the printer in Windows, then choose <strong>Bluetooth Serial (COM)</strong> and pick its COM port when prompted. First option only works on BLE printers. Use Chrome or Edge (HTTPS or localhost).</small>
                </div>
                <div class="mb-3" id="thermal-serial-baud-wrap" style="display: none;">
                    <label class="form-label fw-semibold">Serial baud rate</label>
                    <select id="thermal-serial-baud" class="form-select">
                        <option value="9600">9600 (common)</option>
                        <option value="19200">19200</option>
                        <option value="38400">38400</option>
                        <option value="57600">57600</option>
                        <option value="115200">115200</option>
                    </select>
                    <small class="text-muted d-block mt-1">Match your printer manual if the receipt is garbage or blank.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Paper Size</label>
                    <select id="thermal-paper-size" class="form-select">
                        <option value="80">80mm</option>
                        <option value="58">58mm</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Duplicate Printing</label>
                    <select id="thermal-duplicate-count" class="form-select">
                        <option value="1">Single copy</option>
                        <option value="2">2 copies</option>
                        <option value="3">3 copies</option>
                    </select>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="thermal-autocut-enabled" checked>
                    <label class="form-check-label fw-semibold" for="thermal-autocut-enabled">Enable auto-cut</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="thermal-open-invoice-after-save" checked>
                    <label class="form-check-label fw-semibold" for="thermal-open-invoice-after-save">Open invoice page after print fallback</label>
                </div>
                <div class="alert alert-light border mt-3 mb-0 py-2 px-3 small" id="thermal-settings-status">
                    Printer not connected. Click <strong>Connect Printer</strong> before printing.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" id="thermal-connect-printer-btn">
                    <i class="ti ti-plug-connected me-1"></i>Connect Printer
                </button>
                <button type="button" class="btn btn-primary" id="thermal-save-settings-btn">Save Settings</button>
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
                    <i class="ti ti-plus me-2"></i>ADD SALE ITEM
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
                                <div id="selected-item-quality-wrap" class="mt-1 mb-1 d-none"></div>
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
                    <input type="hidden" id="sales-selected-item-type" value="">
                    <input type="hidden" id="sales-selected-part-number" value="">
                    <input type="hidden" id="sales-selected-quality-name" value="">
                    <input type="hidden" id="sales-selected-company-name" value="">
                    <input type="hidden" id="sales-selected-category-name" value="">
                    <input type="hidden" id="sales-selected-product-type-label" value="">
                    <input type="hidden" id="sales-selected-product-title" value="">
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
                <div class="row mb-3 d-none" id="quantity-row-normal">
                    <div class="col-md-6">
                        <input type="number" id="item-quantity" class="form-control" value="1" min="0" step="any" placeholder="Quantity" style="background-color: #f8f9fa; border-radius: 8px;">
                    </div>
                    <div class="col-md-6 d-none">
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
                <div class="row mb-2 d-none" id="quantity-row-oil">
                    <div class="col-12">
                        <div class="border rounded px-2 py-1 bg-light small text-muted" id="item-quantity-oil-summary">= 0 Can total</div>
                        <p class="small text-muted mb-0 mt-1">Quantity: set cans / liters on each warehouse row below.</p>
                    </div>
                </div>
                <input type="hidden" id="item-quantity-cans" value="0">
                <input type="hidden" id="item-quantity-liters" value="0">

                @php $canEditSaleRetailGstRtax = auth()->check() && auth()->user()->role === 'admin'; @endphp
                <!-- Rate & options (purchase-style: can + per liter + retail + warranty / mileage) -->
                <div class="add-item-section mb-3" id="sale-item-rate-options-section">
                    <h6 class="add-item-section-title mb-3"><span class="add-item-step-num">3</span> Rate &amp; options</h6>
                    <div class="row g-3 align-items-start">
                        <div class="col-lg-4 col-md-6" id="sale-item-can-rate-column">
                            <label class="form-label small fw-semibold mb-1" id="sale-item-can-rate-label"><i class="ti ti-shopping-cart me-1"></i>Sale rate (Rs)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">Rs</span>
                                <input type="number" id="item-rate" class="form-control border-start-0" value="0" step="1" min="0" placeholder="0" style="border-radius: 0 8px 8px 0; background-color: #f8f9fa;">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 d-none" id="sale-item-per-liter-column">
                            <label class="form-label small fw-semibold mb-1 text-muted">Per liter price (Rs) <span class="fw-normal">— oil</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">Rs</span>
                                <input type="number" id="sale-item-per-liter-rate" class="form-control no-number-spinner border-start-0" value="" step="0.01" min="0" placeholder="Auto" style="border-radius: 0 8px 8px 0; background-color: #f8f9fa;" title="Per liter sale rate">
                            </div>
                            <small class="text-muted d-block mt-1">Can price ÷ liters per can; use when selling by liter.</small>
                        </div>
                        <div class="col-lg-4 col-md-12 d-none" id="sale-item-retail-price-column" data-admin-only-edit="{{ $canEditSaleRetailGstRtax ? '1' : '0' }}">
                            <label class="form-label fw-bold mb-1 add-item-retail-label">Retail price (Rs)</label>
                            <div class="add-item-retail-box rounded-3 border add-item-retail-box-compact">
                                <div class="add-item-retail-input-row">
                                    <span class="add-item-retail-rs">Rs</span>
                                    <input type="number" id="sale-item-retail-price" class="form-control form-control-sm no-number-spinner" value="" step="1" min="0" placeholder="—" title="Retail base (ex-GST) for sell preview; enter amount then Adjust by % to set sale rate">
                                    <select id="sale-item-tax-percent" class="form-select form-select-sm add-item-gst-select" title="GST %" @if(!$canEditSaleRetailGstRtax) disabled @endif>
                                        <option value="12">12%</option>
                                        <option value="13">13%</option>
                                        <option value="14">14%</option>
                                        <option value="15">15%</option>
                                        <option value="16">16%</option>
                                        <option value="18" selected>18%</option>
                                    </select>
                                    <span class="add-item-rtax-wrap">
                                        <span class="add-item-rtax-label">R.Tax</span>
                                        <input type="number" id="sale-item-rtax-percent" class="form-control form-control-sm add-item-rtax-input" value="0.5" step="0.01" min="0" max="100" title="R.Tax %" aria-label="R.Tax %" @if(!$canEditSaleRetailGstRtax) readonly @endif>
                                        <span class="add-item-rtax-pct">%</span>
                                    </span>
                                </div>
                                <div class="add-item-sell-row">
                                    <div class="add-item-sell-label-wrap">
                                        <span class="add-item-sell-label">Sell price</span>
                                        <span class="add-item-sell-hint">Base + GST + R.Tax</span>
                                    </div>
                                    <span class="add-item-sell-value" id="sale-item-retail-after-calc">—</span>
                                </div>
                                <div class="add-item-pct-row">
                                    <div class="sale-retail-pct-wrap position-relative">
                                        <label class="add-item-pct-label" id="sale-item-retail-pct-list-label" for="sale-item-retail-pct-trigger">Adjust by %</label>
                                        {{-- Native select kept for existing .val() / calculation logic; styled via custom control --}}
                                        <select id="sale-item-retail-percentage" class="sale-retail-pct-native-sync" title="Adjust sell price by percentage" aria-hidden="true" tabindex="-1">
                                            <option value="" selected data-pct-type="zero">—</option>
                                            @for($p = -25; $p <= 25; $p++)
                                                <option value="{{ $p }}" data-pct-type="{{ $p < 0 ? 'minus' : ($p == 0 ? 'zero' : 'plus') }}">{{ $p }}%</option>
                                            @endfor
                                        </select>
                                        <button type="button" class="sale-retail-pct-trigger form-select form-select-sm d-flex align-items-center justify-content-between gap-1 w-100 text-start" id="sale-item-retail-pct-trigger" aria-haspopup="listbox" aria-expanded="false" aria-controls="sale-item-retail-pct-list" title="Adjust sell price by percentage" aria-label="Adjust sell price by percentage">
                                            <span id="sale-item-retail-pct-label" class="sale-retail-pct-trigger-label is-empty">—</span>
                                            <i class="ti ti-chevron-down sale-retail-pct-chevron flex-shrink-0" aria-hidden="true"></i>
                                        </button>
                                        <ul id="sale-item-retail-pct-list" class="sale-retail-pct-list list-unstyled mb-0" role="listbox" aria-labelledby="sale-item-retail-pct-trigger" hidden>
                                            <li role="option" class="sale-retail-pct-option sale-retail-pct-option-empty" data-value="" tabindex="-1">—</li>
                                            @for($p = -25; $p <= 25; $p++)
                                                @php
                                                    $pctOptClass = $p < 0 ? 'sale-retail-pct-option-neg' : ($p === 0 ? 'sale-retail-pct-option-zero' : 'sale-retail-pct-option-pos');
                                                    $pctOptLabel = $p > 0 ? ('+' . $p . '%') : ($p . '%');
                                                @endphp
                                                <li role="option" class="sale-retail-pct-option {{ $pctOptClass }}" data-value="{{ $p }}" tabindex="-1">{{ $pctOptLabel }}</li>
                                            @endfor
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4" id="add-item-modal-warranty-col">
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
                        <div class="col-md-4 d-none" id="add-item-modal-mileage-col">
                            <label class="form-label fw-bold mb-2">Mileage</label>
                            <select id="item-mileage" class="form-control" style="background-color: #f8f9fa; border-radius: 8px;">
                                <option value="">Select Mileage</option>
                                @if(isset($mileages) && $mileages->count() > 0)
                                    @foreach($mileages as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Claim In only: last 5 sales of selected product (all customers) -->
                <div class="mb-3 d-none" id="claim-global-product-history-section">
                    <label class="form-label fw-bold mb-2 d-flex align-items-center">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #fff;">
                            <i class="ti ti-users-group" style="font-size: 12px;"></i>
                        </span>
                        <span>Last 5 Sales of This Product (All Customers)</span>
                    </label>
                    <div id="claim-global-product-history-content" class="p-3 customer-history-box border border-primary border-opacity-25" style="min-height: 72px; max-height: 220px; overflow-y: auto; background: #f8fafc;">
                        <p class="text-muted mb-0 small text-center">Select a product to see the latest 5 sales across all customers</p>
                    </div>
                </div>

                <!-- Customer History Section (Claim In: this customer + selected product) -->
                <div class="mb-3" id="customer-history-section">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label id="customer-history-label" class="form-label fw-bold mb-0 d-flex align-items-center">
                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff;">
                                <i class="ti ti-history" style="font-size: 12px;"></i>
                            </span>
                            <span id="customer-history-label-text">CUSTOMER HISTORY</span>
                        </label>
                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-success" id="hold-rate-link" style="display: none; font-size: 11px;">
                            <i class="ti ti-check me-1"></i>Apply Last Rate
                        </a>
                    </div>
                    <div id="customer-history-content" class="p-3 customer-history-box" style="min-height: 80px; max-height: 200px; overflow-y: auto;">
                        <p class="text-muted mb-0 small text-center">Select item to view customer history</p>
                    </div>
                </div>

                <!-- Last 5 Purchase History (below customer history) / Last 5 Return History in Claim flow -->
                <div class="mb-3" id="purchase-history-section">
                    <label id="purchase-history-label" class="form-label fw-bold mb-2 d-flex align-items-center">
                        <span class="rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff;">
                            <i class="ti ti-shopping-cart" style="font-size: 12px;"></i>
                        </span>
                        <span id="purchase-history-label-text">LAST 5 PURCHASE HISTORY</span>
                    </label>
                    <div id="purchase-history-content" class="p-3 customer-history-box" style="min-height: 60px; max-height: 180px; overflow-y: auto;">
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

                <!-- Warranty-card Proofs (Retail only, per unit quantity) -->
                <div id="warranty-proof-section" class="mb-3 d-none">
                    <div class="border rounded p-3" style="background: #fff7ed; border-color: #fdba74 !important;">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                            <div>
                                <div class="fw-bold" style="color:#9a3412;">Warranty card proof required (Retail)</div>
                                <div class="small text-muted" id="warranty-proof-summary">Add one serial per unit — type, USB barcode scanner, camera scan, or photo (OCR). Any combination.</div>
                            </div>
                            <span class="badge" id="warranty-proof-badge" style="background:#fed7aa;color:#9a3412;">0 / 0</span>
                        </div>
                        <div class="mt-3" id="warranty-proof-units"></div>
                        <div class="text-danger small mt-2 d-none" id="warranty-proof-error"></div>
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

<!-- Temporary Sale: non-stock line; image/voice/text; saved on invoice -->
<div class="modal fade" id="temporary-sale-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true" style="z-index: 10041 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="ti ti-bolt me-2 text-warning"></i>Temporary Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="small text-muted mb-3">Bill an item that is not in inventory yet. This line does <strong>not</strong> change warehouse stock. Add or correct the product in inventory when you have time.</p>
                <div class="alert alert-warning py-2 px-3 small mb-3 d-none" id="temp-sale-mobile-image-hint"><i class="ti ti-device-mobile me-1"></i>On mobile, <strong>one photo is required</strong> (use camera).</div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" id="temp-sale-image-label" for="temp-sale-image">Photo</label>
                    <input type="file" id="temp-sale-image" class="form-control" accept="image/*" capture="environment">
                    <div id="temp-sale-image-preview-wrap" class="mt-2 d-none">
                        <img id="temp-sale-image-preview" src="" alt="" class="img-thumbnail" style="max-height: 100px;">
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="temp-sale-image-clear" type="button">Remove</button>
                    </div>
                </div>
                <div class="mb-3 p-3 rounded border bg-light">
                    <label class="form-label fw-semibold d-block">Voice <span class="text-muted fw-normal">(optional recording, max 15 sec)</span></label>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="temp-sale-voice-record-btn"><i class="ti ti-microphone me-1" id="temp-sale-voice-record-icon"></i><span id="temp-sale-voice-record-label">Record</span></button>
                        <span class="small text-muted" id="temp-sale-voice-timer"></span>
                    </div>
                    <audio id="temp-sale-voice-audio" class="w-100 d-none" controls></audio>
                    <button type="button" class="btn btn-sm btn-link p-0 d-none" id="temp-sale-voice-remove" type="button">Remove recording</button>
                    <hr class="my-2">
                    <label class="form-label small fw-semibold mb-1" for="temp-sale-speak-name-btn">Speak item name (uses device speech-to-text where supported)</label>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="button" class="btn btn-sm btn-primary" id="temp-sale-speak-name-btn"><i class="ti ti-message-2 me-1"></i>Dictate name</button>
                        <span class="small text-muted" id="temp-sale-stt-status"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="temp-sale-item-name">Item name</label>
                    <div class="position-relative" id="temp-sale-item-name-wrap">
                        <input type="text" id="temp-sale-item-name" class="form-control" placeholder="Type to search saved names or enter a new one" maxlength="500" autocomplete="off" title="Saved names are remembered per branch" spellcheck="false">
                        <div id="temp-sale-name-suggestions" class="temp-sale-name-dropdown d-none" role="listbox" aria-label="Saved item names"></div>
                    </div>
                    <div class="form-text" id="temp-sale-name-hint">Required if you do not attach a voice note and do not dictate a name. Matches saved names from past invoices.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" for="temp-sale-voice-transcript">Voice transcript / extra text</label>
                    <textarea id="temp-sale-voice-transcript" class="form-control" rows="2" placeholder="Filled automatically when you dictate, or type here" maxlength="5000"></textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="temp-sale-quality">Quality <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" id="temp-sale-quality" class="form-control" maxlength="255" placeholder="e.g. OEM, German">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold" for="temp-sale-notes">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" id="temp-sale-notes" class="form-control" maxlength="5000" placeholder="Internal note">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label fw-semibold" for="temp-sale-qty">Qty <span class="text-danger">*</span></label>
                        <input type="number" id="temp-sale-qty" class="form-control" min="0.01" step="any" value="1">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold" for="temp-sale-rate">Rate (Rs) <span class="text-danger">*</span></label>
                        <input type="number" id="temp-sale-rate" class="form-control" min="0" step="0.01" value="0">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-semibold" for="temp-sale-line-total">Total</label>
                        <input type="text" id="temp-sale-line-total" class="form-control bg-light" readonly value="0.00">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning text-dark fw-bold" id="temp-sale-add-btn">
                    <i class="ti ti-check me-1"></i>Add to sale
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Warranty Proof Viewer Modal (create sale) — z-index above #add-item-modal (9999) so it stacks on top -->
<div class="modal fade" id="warranty-proof-viewer-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true" style="z-index: 10050 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
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

<!-- Add New Item (Create) Modal - loads item create page in iframe (z-index via CSS: must be > backdrop 9998) -->
<div class="modal fade" id="add-new-item-modal" tabindex="-1" aria-hidden="true">
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
                        <div class="input-group">
                            <select id="sales-vehicle-make" class="form-select" style="border-radius: 8px 0 0 8px; min-height: 46px;" required>
                                <option value="">— Select make —</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="sales-vehicle-add-make-btn" title="Add new make" style="border-radius: 0 8px 8px 0;"><i class="ti ti-plus"></i></button>
                        </div>
                        <p class="small text-muted mb-0 mt-1">Same list as item &quot;Add Vehicle&quot; (car manufacturers).</p>
                </div>
                <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">MODEL / NAME</label>
                        <div class="input-group">
                            <select id="sales-vehicle-model" class="form-select" style="border-radius: 8px 0 0 8px; min-height: 46px;" required disabled>
                                <option value="">— Select model —</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="add-model-btn" title="Add new model" style="border-radius: 0 8px 8px 0;"><i class="ti ti-plus"></i></button>
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
    #add-item-modal #confirm-entry {
        pointer-events: auto !important;
        cursor: pointer !important;
    }
    /* Keep footer above body so buttons are always clickable */
    #add-item-modal .modal-footer {
        position: relative;
        z-index: 10;
        flex-shrink: 0;
    }
    body.modal-open .modal-backdrop { z-index: 9998 !important; }
    /*
     * Add New Item (iframe loads /all/items/create/new): global backdrop is forced to 9998 so #add-item-modal (9999) stays clickable.
     * This modal was z-index 1060 — lower than the backdrop — so the dimmed layer sat ON TOP of the iframe and swallowed all clicks
     * (category type boxes use Alpine @click; nothing fired). Stack above 9998 like #camera-barcode-modal.
     */
    #add-new-item-modal.modal,
    body.modal-open #add-new-item-modal.modal.show {
        z-index: 10050 !important;
    }
    #add-new-item-modal .modal-dialog,
    #add-new-item-modal .modal-content,
    #add-new-item-modal .modal-body,
    #add-new-item-modal iframe {
        pointer-events: auto !important;
    }
    /* Modal title color (match purchase modal) */
    #add-item-modal-title.modal-title--sale { color: #0d6efd; }
    #add-item-modal #sale-item-rate-options-section .add-item-section-title { font-size: 0.95rem; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 0.5rem; }
    #add-item-modal #sale-item-rate-options-section .add-item-step-num { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #dbeafe; color: #1d4ed8; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; }
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

    /* Thermal print settings — must stack above global .modal-backdrop (9998) or UI feels “frozen” */
    #thermal-print-settings-modal.modal,
    body.modal-open #thermal-print-settings-modal.modal.show {
        z-index: 10065 !important;
        pointer-events: auto !important;
    }
    #thermal-print-settings-modal .modal-dialog,
    #thermal-print-settings-modal .modal-content,
    #thermal-print-settings-modal .modal-body,
    #thermal-print-settings-modal .modal-footer,
    #thermal-print-settings-modal .modal-header,
    #thermal-print-settings-modal button,
    #thermal-print-settings-modal input,
    #thermal-print-settings-modal select,
    #thermal-print-settings-modal .form-check-label {
        pointer-events: auto !important;
    }

    /* Hide Mobile No. column on sales form (party dropdown handles selection) */
    #sales-mobile-column {
        display: none !important;
    }

    /* Hide only Branch and Visiting Document in Add Customer modal (sales page); keep Name & Phone visible */
    #addCustomerModal .modal-body .row.g-3 .col-12:has(#customer_branch_id),
    #addCustomerModal .modal-body .row.g-3 .col-md-6:has(#visiting_doc) {
        display: none !important;
    }

    /* Party Name / Vehicle # – two-section flex: text (left) | divider | actions (right) */
    .party-name-field-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 38px;
        height: 38px;
        padding: 0 0 0 14px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background: #fff;
        overflow: hidden;
    }
    .party-name-field-wrapper:focus-within {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    /* Hide Select2's built-in clear "x" – we use our own Remove button only */
    .party-name-field-wrapper .select2-selection__clear {
        display: none !important;
    }
    .party-name-field-inner {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        padding-right: 12px;
    }
    .party-name-field-inner .select2-container {
        width: 100% !important;
    }
    .party-name-field-inner .select2-container .select2-selection--single {
        height: 36px !important;
        border: none !important;
        background: transparent !important;
        padding-left: 0;
    }
    .party-name-field-inner .select2-container .select2-selection__rendered {
        padding-left: 0;
        line-height: 36px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #212529;
        max-width: 100%;
    }
    .party-name-field-inner .select2-container .select2-selection__arrow {
        display: none;
    }
    /* Vertical divider between text and actions */
    .party-name-actions {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        height: 100%;
        padding: 0 14px 0 12px;
        margin-left: 0;
        border-left: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    .party-name-actions .btn-party-action {
        width: 32px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        background: #fff;
        color: #6c757d;
        transition: background 0.15s, color 0.15s;
        flex-shrink: 0;
    }
    .party-name-actions .btn-party-action:hover {
        background: #e9ecef;
        color: #0d6efd;
        border-color: #ced4da;
    }
    .party-name-actions .btn-party-action.btn-ledger {
        min-width: 72px;
        width: auto;
        padding: 0 10px;
        font-size: 12px;
    }
    .party-name-actions .btn-party-action.btn-remove:hover {
        color: #dc3545;
        background: #fff5f5;
        border-color: #f5c6cb;
    }
    /* Highlight Edit button when a party is selected */
    .party-name-actions .btn-party-action.active {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    /* MOBILE NO. – single-line flex: text (left) | edit button (right) */
    .mobile-field-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 38px;
        height: 38px;
        padding: 0 0 0 14px;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background: #fff;
        overflow: hidden;
    }
    .mobile-field-wrapper:focus-within {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .mobile-field-inner {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        padding-right: 12px;
    }
    .mobile-field-inner .select2-container {
        width: 100% !important;
    }
    .mobile-field-inner .select2-container .select2-selection--single {
        height: 36px !important;
        border: none !important;
        background: transparent !important;
        padding-left: 0;
    }
    .mobile-field-inner .select2-container .select2-selection__rendered {
        padding-left: 0;
        line-height: 36px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #212529;
        max-width: 100%;
    }
    .mobile-field-inner .select2-container .select2-selection__arrow {
        display: none;
    }
    .select2-results__option .select2-result-highlight {
        background: rgba(255, 193, 7, 0.45);
        font-weight: 600;
        padding: 0 1px;
        border-radius: 2px;
    }
    .mobile-actions {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        height: 100%;
        padding: 0 10px 0 10px;
        border-left: 1px solid #dee2e6;
        background: #f8f9fa;
    }
    .mobile-actions .btn-mobile-edit {
        width: 32px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        background: #fff;
        color: #6c757d;
        transition: background 0.15s, color 0.15s;
        flex-shrink: 0;
    }
    .mobile-actions .btn-mobile-edit:hover {
        background: #e9ecef;
        color: #0d6efd;
        border-color: #ced4da;
    }
    /* Highlight mobile edit button when a mobile is selected */
    #edit-mobile-btn.active {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
    .mobile-actions {
        gap: 8px;
    }
    .mobile-actions .btn-mobile-ledger {
        height: 26px;
        padding: 0 10px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        background: #fff;
        color: #6c757d;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .mobile-actions .btn-mobile-ledger:hover {
        background: #e9ecef;
        color: #0d6efd;
        border-color: #ced4da;
    }
    .mobile-customer-balance {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        white-space: nowrap;
        color: #495057;
        border: 1px solid transparent;
    }
    .mobile-customer-balance.balance-receivable {
        color: #dc3545;
        border: 1px solid #212529;
        background: #fff5f5;
    }
    @media (max-width: 576px) {
        .party-name-field-wrapper { flex-wrap: nowrap; padding-left: 12px; }
        .party-name-field-inner { padding-right: 8px; }
        .party-name-actions { gap: 8px; padding: 0 10px 0 8px; }
    }
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
                        <div class="input-group">
                            <select id="edit-sales-vehicle-make" class="form-select" style="border-radius: 8px 0 0 8px; min-height: 46px;" required>
                                <option value="">— Select make —</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="edit-sales-vehicle-add-make-btn" title="Add new make" style="border-radius: 0 8px 8px 0;"><i class="ti ti-plus"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-2" style="color: #333; font-size: 13px;">MODEL / NAME</label>
                        <div class="input-group">
                            <select id="edit-sales-vehicle-model" class="form-select" style="border-radius: 8px 0 0 8px; min-height: 46px;" required disabled>
                                <option value="">— Select model —</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="edit-sales-vehicle-add-model-btn" title="Add new model" style="border-radius: 0 8px 8px 0;"><i class="ti ti-plus"></i></button>
                        </div>
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

<!-- 2×1 Label Print View modal (Sales) -->
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
                        <div class="d-flex align-items-center gap-2" id="label-print-qty-wrap">
                            <label class="form-label small mb-0 text-muted">Quantity:</label>
                            <input type="number" id="label-print-qty-input" class="form-control form-control-sm no-number-spinner" min="1" max="500" value="1" style="width: 80px;" title="Labels ki tadad change karein">
                        </div>
                        <div class="form-check form-switch mb-0 ms-2">
                            <input class="form-check-input" type="checkbox" id="label-print-show-price" title="Label par price dikhayein">
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
    /* Hide up/down arrow buttons on cash amount number input */
    .cash-amount-input::-webkit-outer-spin-button,
    .cash-amount-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .cash-amount-input {
        -moz-appearance: textfield;
    }
    .cash-received-row.cash-row-is-return {
        border-left: 3px solid #dc2626 !important;
        background: linear-gradient(90deg, rgba(254, 242, 242, 0.95) 0%, #fff 12px);
    }
    .cash-received-row.cash-row-is-return .cash-row-return-toggle {
        border-color: #dc2626;
        color: #b91c1c;
        background: #fee2e2;
    }
    /* Return line: red Rs box + minus before amount */
    #salesCashPaidWrapper .cash-return-amount-prefix {
        display: none;
        align-items: center;
        font-weight: 900;
        font-size: 1.0625rem;
        color: #b91c1c;
        margin-right: 0.2rem;
        flex-shrink: 0;
        line-height: 1;
        user-select: none;
    }
    #salesCashPaidWrapper .cash-received-row.cash-row-is-return .cash-return-amount-prefix {
        display: inline-flex;
    }
    #salesCashPaidWrapper .cash-received-row.cash-row-is-return .purchase-cash-amount-wrap {
        border-color: #ef4444 !important;
        background: #fef2f2 !important;
        width: auto;
        min-width: 124px;
        max-width: 158px;
    }
    #salesCashPaidWrapper .cash-received-row.cash-row-is-return .purchase-cash-amount-wrap:focus-within {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 0.15rem rgba(220, 38, 38, 0.22);
    }
    #salesCashPaidWrapper .cash-received-row.cash-row-is-return .purchase-cash-prefix {
        color: #b91c1c !important;
    }
    #salesCashPaidWrapper .cash-received-row.cash-row-is-return .cash-amount-input {
        color: #991b1b !important;
    }
    #salesCashPaidWrapper .cash-received-row.cash-row-is-return .cash-amount-input::placeholder {
        color: rgba(153, 27, 27, 0.45);
    }
    .sales-cash-amount-pulse {
        outline: 2px solid #2563eb !important;
        outline-offset: 2px;
        border-radius: 6px;
        transition: outline 0.15s ease;
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
    #stock-status-list .stock-warehouse-item select.stock-warehouse-qty-input {
        min-width: 68px;
        max-width: 88px;
        width: 76px;
        -moz-appearance: auto;
        appearance: auto;
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
    #stock-status-list .stock-warehouse-item.bg-primary,
    #stock-status-list .stock-warehouse-item.row-active {
        background-color: #0d6efd !important;
        color: #fff !important;
        border: 1px solid #0a58ca !important;
    }
    #stock-status-list .stock-warehouse-item.bg-primary .stock-warehouse-qty-labels,
    #stock-status-list .stock-warehouse-item.row-active .stock-warehouse-qty-labels {
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
        width: 76px;
        text-align: center;
    }
    #stock-status-list .stock-warehouse-item .stock-warehouse-extra-input {
        background-color: #fff !important;
        color: #212529 !important;
        border-radius: 6px;
        width: 76px;
        text-align: center;
    }
    #stock-status-list .stock-warehouse-item .stock-warehouse-base-qty-input::placeholder {
        color: #adb5bd;
        opacity: 0.9;
    }
    #stock-status-list .stock-warehouse-item .stock-warehouse-extra-input::placeholder {
        color: #adb5bd;
        opacity: 0.9;
    }
    #stock-status-list .stock-warehouse-item.bg-primary .stock-warehouse-qty-input,
    #stock-status-list .stock-warehouse-item.bg-primary .stock-warehouse-base-qty-input,
    #stock-status-list .stock-warehouse-item.row-active .stock-warehouse-qty-input,
    #stock-status-list .stock-warehouse-item.row-active .stock-warehouse-base-qty-input {
        background-color: rgba(255,255,255,0.95) !important;
        color: #212529 !important;
        border: 1px solid rgba(0,0,0,0.15);
    }
    #stock-status-list .stock-warehouse-item.bg-primary .stock-warehouse-extra-input,
    #stock-status-list .stock-warehouse-item.row-active .stock-warehouse-extra-input {
        background-color: rgba(255,255,255,0.95) !important;
        color: #212529 !important;
        border: 1px solid rgba(0,0,0,0.15);
    }
    /* Branch row */
    #stock-status-list .stock-branch-item {
        background-color: #fff !important;
        min-height: 42px;
    }
    /* Active vehicles: compact (small list) vs full card */
    #vehicles-list.vehicle-cards-container { gap: 0.5rem; }
    #vehicles-list .vehicle-card.vehicle-card-compact {
        margin-bottom: 0.25rem !important;
        border-radius: 8px;
        max-width: 100%;
    }
    #vehicles-list .vehicle-card.vehicle-card-compact .card-body { padding: 0.5rem 0.75rem !important; }
    #vehicles-list .vehicle-card.vehicle-card-compact .vehicle-display-plate p.mb-1.fw-bold.text-uppercase { font-size: 9px !important; }
    #vehicles-list .vehicle-card.vehicle-card-compact .vehicle-plate-text { font-size: 14px !important; }
    #vehicles-list .vehicle-card.vehicle-card-compact .vehicle-make-model-text { font-size: 11px !important; }
    #vehicles-list .vehicle-card.vehicle-card-compact .vehicle-selected-mileage { font-size: 10px !important; }
    #vehicles-list .vehicle-card.vehicle-card-compact:not(.vehicle-card-expanded) .vehicle-metrics { display: none !important; }
    #vehicles-list .vehicle-card.vehicle-card-compact .vehicle-expanded-only { display: none !important; }
    #vehicles-list .vehicle-card.vehicle-card-expanded { width: 100% !important; grid-column: 1 / -1 !important; }
    /* Equal full cards in grid; highlight selection only (no compact row) */
    #vehicles-list .vehicle-card.vehicle-card--selected {
        box-shadow: 0 0 0 2px #2563eb, 0 4px 12px rgba(37, 99, 235, 0.18) !important;
        border-color: #93c5fd !important;
        background: #f0f7ff !important;
    }
    /* Grid = mockup-style summary only; metrics live in #vehicle-primary-metrics-panel */
    #vehicles-list .vehicle-card .vehicle-metrics { display: none !important; }
    #vehicle-primary-metrics-panel .vehicle-metrics { display: block !important; }
    #vehicle-primary-metrics-panel.card-like-panel {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 12px 14px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    #vehicle-primary-metrics-host:empty { min-height: 0; }
    #vehicle-display-section .vehicle-metrics.vehicle-metrics-disabled { pointer-events: none; opacity: 0.75; }
    #vehicle-display-section .vehicle-metrics.vehicle-metrics-disabled .vehicle-metrics-oil-required-notice { opacity: 1; pointer-events: auto; }
    #vehicle-display-section .vehicle-metrics.vehicle-metrics-disabled input { cursor: not-allowed; }
    /* Items summary table (purchase-style: WAREHOUSE | ITEM | RATE | TOTAL | PRINT / SELECT) */
    #sales-items-table.pehla-items-table { border-collapse: collapse; background: #fff; }
    #sales-items-table.pehla-items-table thead { border-bottom: 2px solid #dee2e6; }
    #sales-items-table .pehla-items-thead .pehla-th {
        font-weight: 700; font-size: 0.75rem; color: #495057; text-transform: uppercase;
        padding: 10px 12px; background: transparent; border: none;
    }
    #sales-items-table tbody tr.pehla-items-row {
        background-color: #f8f9fa; border-bottom: 1px solid #e9ecef;
    }
    #sales-items-table tbody tr.pehla-items-row:hover { background-color: #f1f3f5; }
    #sales-items-table .pehla-td-warehouse,
    #sales-items-table .pehla-td-item,
    #sales-items-table .pehla-td-total {
        padding: 10px 12px; vertical-align: middle; border: none; border-bottom: 1px solid #e9ecef;
    }
    #sales-items-table .pehla-td-warehouse { border-right: none; }
    #sales-items-table .pehla-td-warehouse .sales-wh-name {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 999px;
        background-color: #0d6efd;
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    /* Add-item modal: retail price box (Sale – same look as Purchase) */
    #add-item-modal .add-item-retail-label { color: #0d6efd; font-size: 0.9rem; margin-bottom: 0.35rem !important; }
    #add-item-modal .add-item-retail-box { background: #f0fdf4; border-color: #bbf7d0 !important; }
    #add-item-modal .add-item-retail-box-compact { padding: 0.5rem 0.6rem !important; max-width: 100%; width: 100%; box-sizing: border-box; }
    #add-item-modal .add-item-retail-input-row { display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem; }
    #add-item-modal .add-item-retail-input-row .add-item-retail-rs { padding: 0.3rem 0.4rem; font-size: 0.8rem; font-weight: 600; background: #e5e7eb; border: 1px solid #d1d5db; border-radius: 6px; flex-shrink: 0; }
    #add-item-modal .add-item-retail-input-row #sale-item-retail-price { flex: 1 1 4rem; min-width: 4rem; max-width: 6.5rem; font-size: 0.9rem; font-weight: 600; padding: 0.3rem 0.4rem; border-radius: 6px; min-height: 32px; }
    #add-item-modal .add-item-retail-input-row .add-item-gst-select { width: auto; min-width: 4.5rem; font-size: 0.8rem; padding: 0.3rem 0.5rem; border-radius: 6px; min-height: 32px; flex-shrink: 0; }
    #add-item-modal .add-item-rtax-wrap { display: inline-flex; align-items: center; gap: 0.15rem; flex-shrink: 0; }
    #add-item-modal .add-item-rtax-label { font-size: 0.7rem; color: #6b7280; white-space: nowrap; }
    #add-item-modal .add-item-rtax-input { width: 3rem; font-size: 0.8rem; padding: 0.3rem 0.25rem; border-radius: 6px; min-height: 32px; text-align: center; }
    #add-item-modal .add-item-rtax-pct { font-size: 0.75rem; color: #6b7280; }
    #add-item-modal .add-item-sell-row { margin-top: 0.5rem; padding-top: 0.4rem; border-top: 1px solid rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap; }
    #add-item-modal .add-item-sell-label-wrap { display: flex; flex-direction: column; gap: 0; }
    #add-item-modal .add-item-sell-label { font-size: 0.85rem; font-weight: 600; color: #374151; }
    #add-item-modal .add-item-sell-hint { font-size: 0.65rem; color: #9ca3af; margin-top: 0.05rem; }
    #add-item-modal .add-item-sell-value { font-size: 1rem !important; font-weight: 700; padding: 0.35rem 0.6rem !important; border-radius: 8px; text-align: center; background: #059669 !important; color: #fff !important; border: none; min-width: 5rem; }
    #add-item-modal #sale-item-retail-after-calc { font-size: 1rem !important; }
    #add-item-modal .add-item-pct-row { margin-top: 0.4rem; }
    #add-item-modal .add-item-pct-label { display: block; font-size: 0.7rem; color: #6b7280; margin-bottom: 0.15rem; }
    #add-item-modal .sale-retail-pct-native-sync {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
    #add-item-modal .sale-retail-pct-trigger {
        font-size: 0.85rem;
        padding: 0.3rem 0.5rem;
        min-height: 32px;
        border-radius: 6px;
        background-color: #fff;
    }
    #add-item-modal .sale-retail-pct-trigger[aria-expanded="true"] .sale-retail-pct-chevron { transform: rotate(180deg); }
    #add-item-modal .sale-retail-pct-chevron { transition: transform 0.15s ease; font-size: 0.9rem; opacity: 0.65; }
    #add-item-modal .sale-retail-pct-trigger-label { flex: 1 1 auto; min-width: 0; }
    #add-item-modal .sale-retail-pct-trigger-label.is-empty { color: #6b7280; font-weight: 400; }
    #add-item-modal .sale-retail-pct-trigger-label.is-zero { color: #374151; font-weight: 500; }
    #add-item-modal .sale-retail-pct-trigger-label.is-neg { color: #dc2626; font-weight: 700; }
    #add-item-modal .sale-retail-pct-trigger-label.is-pos { color: #2563eb; font-weight: 600; }
    #add-item-modal .sale-retail-pct-list {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 2px);
        z-index: 1085;
        max-height: 240px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        padding: 0.25rem 0;
    }
    #add-item-modal .sale-retail-pct-option {
        padding: 0.45rem 0.75rem;
        font-size: 0.85rem;
        cursor: pointer;
        line-height: 1.3;
        transition: background-color 0.1s ease;
    }
    #add-item-modal .sale-retail-pct-option:hover,
    #add-item-modal .sale-retail-pct-option:focus { outline: none; }
    #add-item-modal .sale-retail-pct-option-empty { color: #6b7280; font-weight: 400; }
    #add-item-modal .sale-retail-pct-option-empty:hover { background-color: rgba(107, 114, 128, 0.1); }
    #add-item-modal .sale-retail-pct-option-zero { color: #374151; font-weight: 500; }
    #add-item-modal .sale-retail-pct-option-zero:hover { background-color: rgba(55, 65, 81, 0.08); }
    #add-item-modal .sale-retail-pct-option-neg { color: #dc2626; font-weight: 700; }
    #add-item-modal .sale-retail-pct-option-neg:hover { background-color: rgba(220, 38, 38, 0.1); }
    #add-item-modal .sale-retail-pct-option-pos { color: #2563eb; font-weight: 600; }
    #add-item-modal .sale-retail-pct-option-pos:hover { background-color: rgba(37, 99, 235, 0.1); }
    #add-item-modal .sale-retail-pct-option[aria-selected="true"] { background-color: rgba(13, 110, 253, 0.08); }

    /* 2×1 Label print styles (shared with Purchase) */
    .label-print-sheet {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        background: #fff;
        padding: 16px;
        border-radius: 8px;
    }
    .label-print-item {
        width: 2in;
        min-width: 2in;
        max-width: 2in;
        height: 1in;
        min-height: 1in;
        max-height: 1in;
        padding: 8px 10px;
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
    .label-print-line1 {
        font-weight: 700;
        font-size: 14px;
        line-height: 1.2;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        flex-shrink: 0;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .label-print-line2 {
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 2px;
        color: #374151;
        flex-shrink: 0;
        max-width: 100%;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        white-space: normal;
    }
    .label-print-rate {
        font-size: 11px;
        color: #333;
        margin-bottom: 2px;
        flex-shrink: 0;
    }
    .label-print-barcode-wrap {
        margin-top: auto;
        flex: 1 1 0;
        min-height: 0;
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
        gap: 2px;
        overflow: hidden;
    }
    .label-print-barcode-wrap canvas {
        max-width: 100%;
        width: 100%;
        height: auto;
        flex-shrink: 1;
        min-height: 0;
        max-height: calc(100% - 11px);
    }
    .label-print-barcode-caption {
        font-size: 8px;
        font-weight: 600;
        line-height: 1.1;
        font-family: ui-monospace, 'Cascadia Mono', Consolas, monospace;
        color: #111;
        max-width: 100%;
        text-align: center;
        padding: 0 1px;
        flex-shrink: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    #label-print-modal-content.label-print-hide-price .label-print-rate {
        display: none !important;
    }
    @media print {
        .label-print-sheet {
            gap: 0 !important;
            padding: 0.25in !important;
            box-shadow: none !important;
        }
        .label-print-item {
            width: 2in !important;
            min-width: 2in !important;
            max-width: 2in !important;
            height: 1in !important;
            min-height: 1in !important;
            max-height: 1in !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            border: none !important;
            border-right: 1px solid #ddd !important;
            border-bottom: 1px solid #ddd !important;
        }
        .label-print-line1 {
            font-weight: 700;
        }
        .label-print-barcode-wrap {
            overflow: hidden !important;
        }
        .label-print-barcode-wrap canvas {
            max-height: none !important;
        }
        #label-print-view-modal .modal-header,
        #label-print-view-modal .d-flex.justify-content-between.px-3 {
            display: none !important;
        }
        #label-print-qty-wrap {
            display: none !important;
        }
        #label-print-view-modal .modal-body {
            padding: 0 !important;
        }
        #label-print-modal-content.label-print-hide-price .label-print-rate {
            display: none !important;
        }
    }
    #sales-items-table .pehla-td-item { border-right: none; }
    #sales-items-table .pehla-td-total { border-right: none; }
    #sales-items-table .pehla-th-print-select,
    #sales-items-table .pehla-td-print-select { padding: 8px 12px; border: none; border-bottom: 1px solid #e9ecef; }
    #sales-items-table .pehla-th-actions,
    #sales-items-table .pehla-td-actions { width: 1%; white-space: nowrap; padding: 8px !important; border: none !important; border-bottom: 1px solid #e9ecef !important; }
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

    /* Temporary Sale — saved item name autocomplete */
    #temp-sale-item-name-wrap .temp-sale-name-dropdown {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 2px);
        z-index: 10055;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    #temp-sale-item-name-wrap .temp-sale-sugg-item {
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        border: none;
        border-bottom: 1px solid #f1f3f5;
        border-radius: 0;
        text-align: left;
        width: 100%;
        background: #fff;
    }
    #temp-sale-item-name-wrap .temp-sale-sugg-item:last-child {
        border-bottom: none;
    }
    #temp-sale-item-name-wrap .temp-sale-sugg-item:hover,
    #temp-sale-item-name-wrap .temp-sale-sugg-item.active {
        background: #fff3cd;
    }
    #temp-sale-item-name-wrap .temp-sale-sugg-item mark {
        padding: 0 0.1em;
        border-radius: 2px;
    }
    #temp-sale-item-name-wrap .temp-sale-sugg-meta {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 2px;
    }
</style>
@endpush

@push('scripts')
<script>
window.customerBranchNames = @json(collect($customers)->keyBy(function($c) { return (string) $c->id; })->map(function($c) { return $c->branch_name ?? optional($c->branch)->branch_name ?? '—'; })->toArray());
</script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
<script>
var salesItems = [];
var vehicles = [];
window.__SALE_TEMPORARY_ITEM_ID__ = @json($temporaryItemId ?? null);
window.__TEMP_SALE_NAME_SEARCH_URL__ = @json(route('sales.temporary_item_names.search'));
@isset($saleEditPayload)
window.__SALE_EDIT_PAYLOAD__ = @json($saleEditPayload);
@endisset
window.__IS_EDIT_SALE__ = @json((bool)($isEditSale ?? false));
</script>
@include('admin.sales.partials.thermal-print-client')
<script>
$(document).ready(function() {
    salesItems = [];
    var itemCounter = 0;
    let editingRowId = null; // when set, confirm-entry updates this row instead of adding new
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

    function moveThermalPrintSettingsModalToBody() {
        var $m = $('#thermal-print-settings-modal');
        if ($m.length) {
            $m.appendTo('body');
            $m.css({ 'z-index': 10065, 'pointer-events': 'auto' });
            $m.find('.modal-dialog, .modal-content, .modal-body, .modal-footer, .modal-header').css('pointer-events', 'auto');
            $m.find('button, input, select, .form-check-label').css('pointer-events', 'auto');
        }
    }
    moveThermalPrintSettingsModalToBody();
    window.addEventListener('load', moveThermalPrintSettingsModalToBody);

    var saleSubmitIntent = 'save';
    var thermalSettingsModalEl = document.getElementById('thermal-print-settings-modal');
    var thermalSettingsModal = thermalSettingsModalEl ? new bootstrap.Modal(thermalSettingsModalEl) : null;
    if (thermalSettingsModalEl) {
        thermalSettingsModalEl.addEventListener('shown.bs.modal', function () {
            var $backdrop = $('.modal-backdrop').last();
            if ($backdrop.length) {
                $('#thermal-print-settings-modal').insertAfter($backdrop);
            }
            $('#thermal-print-settings-modal').css({ 'z-index': 10065, 'pointer-events': 'auto' });
        });
    }

    function setThermalStatus(message, level) {
        var box = $('#thermal-settings-status');
        if (!box.length) return;
        box.removeClass('alert-light alert-success alert-warning alert-danger')
            .addClass('alert-' + (level || 'light'))
            .text(message);
    }

    function toggleThermalSerialBaudRow() {
        var show = $('#thermal-printer-type').val() === 'serial';
        $('#thermal-serial-baud-wrap').toggle(!!show);
    }

    function applyThermalSettingsToUi() {
        var settings = getThermalPrintSettings();
        $('#thermal-printer-type').val(settings.type);
        $('#thermal-serial-baud').val(String(settings.serialBaudRate || 9600));
        $('#thermal-paper-size').val(String(settings.paperSize || '80'));
        $('#thermal-autocut-enabled').prop('checked', !!settings.autoCut);
        $('#thermal-duplicate-count').val(String(settings.duplicateCount || 1));
        $('#thermal-open-invoice-after-save').prop('checked', !!settings.openInvoiceAfterSave);
        toggleThermalSerialBaudRow();
    }

    function readThermalSettingsFromUi() {
        var t = $('#thermal-printer-type').val() || 'serial';
        return {
            type: t,
            thermal_ble_explicit: t === 'bluetooth',
            serialBaudRate: parseInt($('#thermal-serial-baud').val(), 10) || 9600,
            paperSize: $('#thermal-paper-size').val() || '80',
            autoCut: $('#thermal-autocut-enabled').is(':checked'),
            duplicateCount: parseInt($('#thermal-duplicate-count').val(), 10) || 1,
            openInvoiceAfterSave: $('#thermal-open-invoice-after-save').is(':checked')
        };
    }

    $('#thermal-printer-type').on('change', toggleThermalSerialBaudRow);

    $('#open-thermal-settings-btn').on('click', function() {
        applyThermalSettingsToUi();
        setThermalStatus('Review settings and connect printer before printing.', 'light');
        if (thermalSettingsModal) thermalSettingsModal.show();
    });

    $('#thermal-save-settings-btn').on('click', function() {
        var settings = readThermalSettingsFromUi();
        saveThermalPrintSettings(settings);
        setThermalStatus('Settings saved successfully.', 'success');
        if (thermalSettingsModal) {
            setTimeout(function() { thermalSettingsModal.hide(); }, 300);
        }
    });

    $('#thermal-connect-printer-btn').on('click', async function() {
        var settings = readThermalSettingsFromUi();
        saveThermalPrintSettings(settings);
        try {
            if (settings.type === 'bluetooth') {
                if (!navigator.bluetooth) {
                    throw new Error('This browser does not support Web Bluetooth.');
                }
                await connectBluetoothPrinter();
                setThermalStatus('Bluetooth printer connected.', 'success');
                return;
            }
            if (settings.type === 'usb') {
                if (!navigator.usb) {
                    throw new Error('This browser does not support WebUSB.');
                }
                await connectUsbPrinter();
                setThermalStatus('USB printer connected.', 'success');
                return;
            }
            if (settings.type === 'serial') {
                if (!navigator.serial) {
                    throw new Error('Web Serial not supported. Use Chrome or Edge.');
                }
                await connectSerialPrinter();
                setThermalStatus('Serial (COM) port opened — ready to print.', 'success');
                return;
            }
            setThermalStatus('Wired mode uses browser print dialog on save & print.', 'warning');
        } catch (err) {
            setThermalStatus('Connection failed: ' + (err.message || err), 'danger');
        }
    });

    var saleSubmitInFlight = false;
    var saleSubmitRequestUuid = null;

    $('#bluetooth-print-btn').off('click.salePrint').on('click.salePrint', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (saleSubmitInFlight) return;
        saleSubmitIntent = 'print';
        $('#salesForm').trigger('submit.saleSave');
    });
    
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
                if (xhr.status === 404) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Customer not found',
                        text: 'This customer may have been deleted. Please select another customer or refresh the page.',
                        confirmButtonText: 'OK'
                    });
                    // Clear invalid selection so user can pick again
                    $('#customer_id').val(null).trigger('change.select2');
                    $('#customer_mobile').val(null).trigger('change.select2');
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
    
    // Selected plate drives which vehicle is primary for oil/cart sync; all cards stay same full size in the grid
    window.selectedVehiclePlate = null;
    // Plate from the exact option user clicked in party dropdown (Select2 with duplicate values only exposes first option:selected)
    window.lastSelectedPlateFromPartyDropdown = null;

    function normVehiclePlateForSale(plate) {
        return (plate || '').toString().trim().toUpperCase().replace(/\s+/g, '');
    }
    function getPrimaryVehicleCardForSale() {
        var $all = $('#vehicles-list .vehicle-card');
        if (!$all.length) return $();
        var sel = normVehiclePlateForSale(window.selectedVehiclePlate);
        if (sel) {
            var $hit = $all.filter(function() {
                return normVehiclePlateForSale($(this).attr('data-plate-norm')) === sel;
            });
            if ($hit.length) return $hit.first();
        }
        return $all.first();
    }

    function vehicleListCardForMetrics($m) {
        if (!$m || !$m.length) return $();
        var vid = $m.data('vehicle-id');
        return $('#vehicles-list .vehicle-card').filter(function() { return String($(this).data('vehicle-id')) === String(vid); }).first();
    }
    function getPrimaryVehicleMetricsRoot() {
        var $p = $('#vehicle-primary-metrics-host .vehicle-metrics').first();
        if ($p.length) return $p;
        return getPrimaryVehicleCardForSale().find('.vehicle-metrics').first();
    }
    function mountPrimaryVehicleMetricsPanel() {
        var $slot = $('#vehicle-primary-metrics-panel');
        var $host = $('#vehicle-primary-metrics-host');
        var $cur = $host.children('.vehicle-metrics').first();
        if ($cur.length) {
            var oldVid = $cur.data('vehicle-id');
            var $oldCard = $('#vehicles-list .vehicle-card').filter(function() { return String($(this).data('vehicle-id')) === String(oldVid); }).first();
            if ($oldCard.length) $oldCard.append($cur);
            $cur.css('display', 'none');
        }
        $host.empty();
        var $card = getPrimaryVehicleCardForSale();
        if (!$card.length) {
            $slot.hide().removeClass('d-block');
            return;
        }
        var $m = $card.find('.vehicle-metrics').first();
        if (!$m.length) {
            $slot.hide().removeClass('d-block');
            return;
        }
        $m.detach().appendTo($host);
        $slot.show().removeClass('d-none').addClass('d-block');
        $m.css('display', 'block');
    }

    function formatRsIntegerishForBalance(n) {
        var x = Math.abs(parseFloat(n) || 0);
        var s = (x % 1 === 0 ? x : x.toFixed(2)).toString();
        return s.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function resetSalesPreviousBalanceSection() {
        var $sec = $('#sales-previous-balance-section');
        if (!$sec.length) return;
        $sec.removeClass('is-receivable is-advance is-payable is-zero');
        $('#sales-previous-balance-amount').text('—');
        $('#sales-previous-balance-primary-label').text('Select a party');
        $('#sales-previous-balance-secondary').text('');
        $('#sales-previous-balance-age').text('');
        $('#previous-balance-input').val('0');
        if (typeof updatePaymentBalances === 'function') updatePaymentBalances();
    }

    function applySalesPreviousBalanceFromApi(res) {
        var $sec = $('#sales-previous-balance-section');
        if (!$sec.length) return;
        $sec.removeClass('is-receivable is-advance is-payable is-zero');
        if (!res || !res.success) {
            resetSalesPreviousBalanceSection();
            return;
        }
        if (res.signed_balance === undefined && res.balance !== undefined) {
            res.signed_balance = res.balance;
        }
        if (res.display_amount === undefined || res.display_amount === null) {
            res.display_amount = Math.abs(parseFloat(res.signed_balance) || 0);
        }
        if (!res.classification) {
            var s0 = parseFloat(res.signed_balance) || 0;
            if (Math.abs(s0) < 0.005) res.classification = 'zero';
            else if (s0 > 0) res.classification = 'receivable';
            else res.classification = 'advance';
        }
        if (!res.label) {
            res.label = (res.classification === 'zero') ? 'No previous balance' : 'Balance';
        }
        var signed = parseFloat(res.signed_balance) || 0;
        var disp = res.display_amount != null ? parseFloat(res.display_amount) : Math.abs(signed);
        $('#sales-previous-balance-amount').text('Rs ' + formatRsIntegerishForBalance(disp));
        $('#sales-previous-balance-primary-label').text(res.label || '—');
        $('#sales-previous-balance-secondary').text(res.secondary_label || '');
        var ageParts = [];
        if (res.due_age_text) ageParts.push(res.due_age_text);
        if (res.due_since_text) ageParts.push(res.due_since_text);
        $('#sales-previous-balance-age').text(ageParts.join(' · ') || '');
        $('#previous-balance-input').val(signed.toFixed(2));
        var c = res.classification || 'zero';
        if (c === 'receivable') $sec.addClass('is-receivable');
        else if (c === 'advance') $sec.addClass('is-advance');
        else if (c === 'payable_to_customer') $sec.addClass('is-payable');
        else $sec.addClass('is-zero');
        if (typeof updatePaymentBalances === 'function') updatePaymentBalances();
    }

    // Capture plate from the exact option clicked in party dropdown (so search-and-select shows correct vehicle expanded).
    // When user searches again and picks another vehicle (same customer), the select value does not change so 'change' never fires.
    // Trigger change only when value is unchanged so selectedVehiclePlate updates and vehicles list refreshes without double-firing when value actually changes.
    $('#customer_id').on('select2:select', function(evt) {
        const data = evt.params && evt.params.data;
        const el = data && data.element;
        if (el) {
            const plate = ($(el).attr('data-plate-number') || $(el).data('plate-number') || '').toString().trim();
            window.lastSelectedPlateFromPartyDropdown = plate || null;
        }
        const currentVal = $(this).val();
        const selectedVal = data && data.id !== undefined ? String(data.id) : null;
        if (selectedVal !== null && selectedVal === currentVal) {
            // Same customer, different vehicle option selected – change would not fire, so run update now.
            $(this).trigger('change');
        }
    });

    // Customer change handler - auto-fill phone, branch info, and load customer's vehicles from DB
    $('#customer_id').on('change', function() {
        const customerId = $(this).val();
        const selected = $(this).find('option:selected');
        // Prefer plate from the option user actually clicked (select2:select); fallback to selected option for programmatic changes
        const plateFromOption = window.lastSelectedPlateFromPartyDropdown !== undefined && window.lastSelectedPlateFromPartyDropdown !== null
            ? window.lastSelectedPlateFromPartyDropdown
            : (selected.attr('data-plate-number') || selected.data('plate-number') || '').toString().trim();
        if (window.lastSelectedPlateFromPartyDropdown !== undefined) window.lastSelectedPlateFromPartyDropdown = null;
        // If the change was triggered from customer_mobile and the mobile option was "customer-only"
        // (no plate-number), then force selectedVehiclePlate = null even if customer_id currently points to a vehicle option.
        if (typeof window.customerMobileSelectionHasPlate !== 'undefined') {
            window.selectedVehiclePlate = customerId
                ? (window.customerMobileSelectionHasPlate ? (window.selectedVehiclePlateFromMobile || plateFromOption || null) : null)
                : null;
            window.customerMobileSelectionHasPlate = undefined;
        } else {
            window.selectedVehiclePlate = customerId ? (plateFromOption || window.selectedVehiclePlateFromMobile || null) : null;
        }
        if (window.selectedVehiclePlateFromMobile !== undefined) window.selectedVehiclePlateFromMobile = null;
        const name = selected.data('name') || '';
        const phone = selected.data('phone') || '';
        const address = selected.data('address') || '';
        const area = selected.data('area') || '';
        const company = selected.data('company') || '';
        // Use server-provided map so branch name always works (Select2 can make option data unreliable)
        const idStr = customerId ? String(customerId) : '';
        let branchName = (window.customerBranchNames && idStr && window.customerBranchNames[idStr]) ? window.customerBranchNames[idStr] : null;
        if (!branchName) {
            branchName = selected.attr('data-branch-name') || selected.data('branchName');
        }
        if (!branchName && customerId) {
            var opt = $('#customer_id option[value="' + idStr.replace(/"/g, '\\"') + '"]');
            if (opt.length) branchName = opt.attr('data-branch-name') || opt.data('branchName');
        }
        branchName = branchName || '—';
        
        // Show which branch this customer was created in (above party field and above mobile field)
        if (customerId) {
            $('#customer-branch-name').text(branchName);
            $('#customer-branch-display').show();
            $('#customer-ledger-btn').show();
            $('#party-clear-btn').show();
            $('#edit-party-btn').addClass('active');
            $('#customer-ledger-btn-mobile').show();
            $('#mobile-field-branch-name').text(branchName);
            $('#mobile-field-branch-display').show();
            // Fetch and show customer balance (payment-aware): receivable = red, advance / we owe = normal
            var balanceUrl = '{{ url(route("customers.balance", ["customer" => "__ID__"])) }}'.replace('__ID__', encodeURIComponent(customerId));
            $('#mobile-customer-balance').show().removeClass('balance-receivable').text('Balance: …');
            $.get(balanceUrl).done(function(res) {
                if (res && res.success && (res.signed_balance !== undefined || res.balance !== undefined)) {
                    applySalesPreviousBalanceFromApi(res);
                    var signed = parseFloat(res.signed_balance) || 0;
                    var disp = res.display_amount != null ? parseFloat(res.display_amount) : Math.abs(signed);
                    var numStr = (Math.abs(disp) % 1 === 0 ? Math.abs(disp) : Math.abs(disp).toFixed(2)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    var text = 'Balance: ' + (signed < 0 ? '−' : '') + 'Rs ' + numStr;
                    $('#mobile-customer-balance').text(text);
                    if (res.classification === 'receivable') {
                        $('#mobile-customer-balance').addClass('balance-receivable');
                    } else {
                        $('#mobile-customer-balance').removeClass('balance-receivable');
                    }
                } else {
                    $('#mobile-customer-balance').text('Balance: —').removeClass('balance-receivable');
                    resetSalesPreviousBalanceSection();
                }
            }).fail(function() {
                $('#mobile-customer-balance').text('Balance: —').removeClass('balance-receivable');
                resetSalesPreviousBalanceSection();
            });
        } else {
            $('#customer-branch-name').text('');
            $('#customer-branch-display').hide();
            $('#customer-ledger-btn').hide();
            $('#party-clear-btn').hide();
            $('#edit-party-btn').removeClass('active');
            $('#customer-ledger-btn-mobile').hide();
            $('#mobile-field-branch-name').text('');
            $('#mobile-field-branch-display').hide();
            $('#mobile-customer-balance').hide().text('').removeClass('balance-receivable');
            resetSalesPreviousBalanceSection();
        }
        
        // Update mobile dropdown: select by customer-id (phone can be shared by multiple parties)
        function setCustomerMobileSelectByCustomerId(customerIdVal, plateVal) {
            var cid = (customerIdVal || '').toString().trim();
            if (!cid) return;
            var $sel = $('#customer_mobile');
            if (!$sel.length || !$sel.is('select')) return;
            var plate = (plateVal || '').toString().trim().toUpperCase().replace(/\s+/g, '');
            var $opts = $sel.find('option').filter(function() {
                return String($(this).data('customer-id') || '').trim() === cid;
            });
            if (!$opts.length) return;
            var $best = null;
            // Prefer a vehicle row matching plate, otherwise prefer the customer-only row (no plate-number)
            if (plate) {
                $opts.each(function() {
                    var pn = ($(this).data('plate-number') || $(this).attr('data-plate-number') || '').toString().trim().toUpperCase().replace(/\s+/g, '');
                    if (pn && pn === plate) { $best = $(this); return false; }
                });
            }
            if (!$best || !$best.length) {
                $opts.each(function() {
                    var pn2 = ($(this).data('plate-number') || $(this).attr('data-plate-number') || '').toString().trim();
                    if (!pn2) { $best = $(this); return false; }
                });
            }
            if (!$best || !$best.length) $best = $opts.first();
            $sel.val($best.val()).trigger('change.select2');
        }

        if (phone) {
            // If selectedVehiclePlate was forced to null (customer-only from mobile),
            // do NOT fall back to plateFromOption here; otherwise we would auto-switch back to a vehicle option.
            setCustomerMobileSelectByCustomerId(customerId, window.selectedVehiclePlate || '');
            $('#customer_mobile_hidden').val(phone);
        }
        $('#customer_address').val(address);
        $('#customer_area').val(area);
        
        // Load this customer's vehicles from database and show below Add Vehicle button
        if (typeof loadCustomerVehicles === 'function') {
            loadCustomerVehicles(customerId);
        }
    });
    
    // Customer Ledger button: open ledger in new tab (no modal – avoids freeze)
    function openCustomerLedger() {
        var customerId = $('#customer_id').val();
        if (!customerId) return;
        var url = '{{ url(route("customers.ledger.report", ["customer" => "__ID__"])) }}'.replace('__ID__', customerId);
        window.open(url, '_blank', 'noopener,noreferrer');
    }
    $('#customer-ledger-btn').on('click', openCustomerLedger);
    $('#customer-ledger-btn-mobile').on('click', openCustomerLedger);

    // Remove (Clear) party selection
    $('#party-clear-btn').on('click', function() {
        $('#customer_id').val(null).trigger('change.select2');
        // Also immediately reset Edit button highlight
        $('#edit-party-btn').removeClass('active');
    });

    // Plate from the exact option user clicked in mobile dropdown (so search-and-select shows correct vehicle expanded).
    // Trigger change when same value (same phone, different vehicle) so UI updates without double-firing.
    window.lastSelectedPlateFromMobileDropdown = null;
    $('#customer_mobile').on('select2:select', function(evt) {
        const data = evt.params && evt.params.data;
        const el = data && data.element;
        if (el) {
            const plate = ($(el).attr('data-plate-number') || $(el).data('plate-number') || '').toString().trim();
            window.lastSelectedPlateFromMobileDropdown = plate || null;
        }
        const currentVal = $(this).val();
        const selectedVal = data && data.id !== undefined ? String(data.id) : null;
        if (selectedVal !== null && selectedVal === currentVal) {
            $(this).trigger('change');
        }
    });
    
    // Mobile number change handler - auto-fill customer when phone is selected (for select dropdown)
    $('#customer_mobile').on('change', function() {
        const selected = $(this).find('option:selected');
        const customerId = selected.data('customer-id');
        const name = selected.data('name') || '';
        const phone = selected.data('phone') || '';
        const address = selected.data('address') || '';
        const area = selected.data('area') || '';
        
        if (customerId) {
            // Use plate from the option user actually clicked; fallback to selected for programmatic changes
            const plateFromMobile = (window.lastSelectedPlateFromMobileDropdown !== undefined && window.lastSelectedPlateFromMobileDropdown !== null)
                ? window.lastSelectedPlateFromMobileDropdown
                : (selected.data('plate-number') || selected.attr('data-plate-number') || '').toString().trim();
            if (window.lastSelectedPlateFromMobileDropdown !== undefined) window.lastSelectedPlateFromMobileDropdown = null;
            window.customerMobileSelectionHasPlate = !!plateFromMobile;
            if (plateFromMobile) window.selectedVehiclePlateFromMobile = plateFromMobile;
            else window.selectedVehiclePlateFromMobile = null;
            // Update customer dropdown and trigger change so Ledger/Edit/Clear buttons update
            $('#customer_id').val(customerId).trigger('change.select2');
            $('#customer_id').trigger('change');
            $('#customer_address').val(address);
            $('#customer_area').val(area);
            $('#customer_mobile_hidden').val(phone);
            $('#edit-mobile-btn').addClass('active');
        } else if (phone) {
            // Just update the hidden field if phone is selected
            $('#customer_mobile_hidden').val(phone);
            $('#edit-mobile-btn').addClass('active');
        } else {
            $('#edit-mobile-btn').removeClass('active');
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
                // Update claim stock summary next to CLAIM IN button
                loadClaimStockSummary();
                // Update scrap stock summary next to SCRAP IN button
                loadScrapStockSummary();
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

    // Load claim stock summary for branch (show next to CLAIM IN button)
    function loadClaimStockSummary() {
        var branchId = $('#salesBranchId').val();
        var $text = $('#claim-stock-summary-text');
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
                $text.text(res.display || ('0 Piece'));
            },
            error: function() {
                $text.text('—');
            }
        });
    }
    // On page load, if branch already selected, show claim stock next to CLAIM IN
    $(function() {
        if ($('#salesBranchId').val()) loadClaimStockSummary();
    });

    // Load scrap stock summary for branch (show next to SCRAP IN button)
    function loadScrapStockSummary() {
        var branchId = $('#salesBranchId').val();
        var $text = $('#scrap-stock-summary-text');
        if (!$text.length) return;

        if (!branchId) {
            $text.text('—');
            return;
        }

        $.ajax({
            url: '{{ route("sales.scrap.stock.summary") }}',
            method: 'GET',
            data: { branch_id: branchId },
            success: function(res) {
                $text.text(res.display || ('0 Unit'));
            },
            error: function() {
                $text.text('—');
            }
        });
    }

    // On page load, if branch already selected, show scrap stock next to SCRAP IN
    $(function() {
        if ($('#salesBranchId').val()) loadScrapStockSummary();
    });

    // Open detailed claim stock history (used by Claim Stock button)
    function openClaimStockLedger(e) {
        // Safety: don't block default Bootstrap modal behavior
        const branchId = $('#salesBranchId').val();
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before viewing claim stock history.',
                confirmButtonText: 'OK'
            });
            return;
        }

        const itemId = $('#selected-item-id').val() || null;
        const scopeBits = [];
        const branchName = $('#branchName').text() || '';
        if (branchName) scopeBits.push('Branch: ' + branchName);
        if (itemId && $('#item-search').val()) {
            scopeBits.push('Item: ' + $('#item-search').val());
        }

        $('#claim-stock-detail-scope').text(scopeBits.length ? scopeBits.join(' | ') : 'All items in this branch');
        $('#claim-stock-detail-loading').removeClass('d-none');
        $('#claim-stock-detail-content').addClass('d-none');
        $('#claim-stock-detail-tbody').empty();
        $('#claim-stock-detail-empty').addClass('d-none');

        $('#claim-stock-detail-modal').modal('show');
        $.ajax({
            url: '{{ route("sales.claim.stock.detail") }}',
            method: 'GET',
            data: {
                branch_id: branchId,
                item_id: itemId
            },
            success: function(res) {
                $('#claim-stock-detail-loading').addClass('d-none');
                $('#claim-stock-detail-content').removeClass('d-none');

                const records = res.records || [];
                const $tbody = $('#claim-stock-detail-tbody');
                $tbody.empty();
                const salesEditUrlTemplate = '{{ route("sales.edit", ":id") }}';
                const purchasesEditUrlTemplate = '{{ route("purchases.edit", ":id") }}';

                if (!records.length) {
                    $('#claim-stock-detail-empty').removeClass('d-none');
                } else {
                    $('#claim-stock-detail-empty').addClass('d-none');
                    records.forEach(function(r) {
                        const prevStock = (r.previous_stock !== undefined) ? parseFloat(r.previous_stock || 0) : 0;
                        const stockIn = (r.stock_in !== undefined) ? parseFloat(r.stock_in || 0) : Math.max(0, parseFloat(r.quantity || 0));
                        const stockOut = (r.stock_out !== undefined)
                            ? parseFloat(r.stock_out || 0)
                            : (parseFloat(r.quantity || 0) < 0 ? Math.abs(parseFloat(r.quantity || 0)) : 0);
                        const balanceAfter = (r.balance_after !== undefined)
                            ? parseFloat(r.balance_after || 0)
                            : (prevStock + stockIn - stockOut);

                        const fmt = function(v) {
                            if (!isFinite(v)) return '0';
                            const n = Number(v);
                            return (Math.floor(n) === n) ? String(n) : n.toFixed(2).replace(/\.00$/, '');
                        };

                        const stockInCell = stockIn > 0
                            ? '<span class="text-success fw-semibold">' + fmt(stockIn) + '</span>'
                            : '<span class="text-muted">-</span>';

                        const stockOutCell = stockOut > 0
                            ? '<span class="text-danger fw-semibold">' + fmt(stockOut) + '</span>'
                            : '<span class="text-muted">-</span>';

                        const balanceClass = balanceAfter < 0 ? 'text-danger fw-semibold' : 'text-primary fw-semibold';
                        const balanceCell = '<span class="' + balanceClass + '">' + fmt(balanceAfter) + '</span>';
                        const tr = `
                            <tr>
                                <td><span class="d-block">${r.date || ''}</span><span class="d-block small text-muted">${r.time || ''}</span></td>
                                <td>
                                    <span class="d-block">${r.customer_name || ''}</span>
                                    ${r.invoice_no ? `<span class="d-block small text-muted">${r.invoice_no}</span>` : ''}
                                </td>
                                <td>
                                    <span class="d-block">${r.item_name || ''}</span>
                                    ${r.item_code ? `<span class="d-block small text-muted">${r.item_code}</span>` : ''}
                                </td>
                                <td>
                                    <span class="d-block">${r.branch_name || ''}</span>
                                    <span class="d-block small text-muted">${r.warehouse_name || ''}</span>
                                </td>
                                <td class="text-end text-muted text-nowrap">${fmt(prevStock)}</td>
                                <td class="text-end text-nowrap">${stockInCell}</td>
                                <td class="text-end text-nowrap">${stockOutCell}</td>
                                <td class="text-end text-nowrap">${balanceCell}</td>
                                <td>${r.entry_type_label || ''}</td>
                                <td>
                                    <div>${r.remarks || ''}</div>
                                    <div>
                                        ${r.sale_id
                                            ? `<button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="window.location='${salesEditUrlTemplate.replace(':id', r.sale_id)}'">Edit</button>`
                                            : (r.purchase_id
                                                ? `<button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="window.location='${purchasesEditUrlTemplate.replace(':id', r.purchase_id)}'">Edit</button>`
                                                : '')}
                                    </div>
                                </td>
                            </tr>
                        `;
                        $tbody.append(tr);
                    });
                }

                const totals = res.totals || {};
                $('#claim-stock-total-in').text((totals.total_claim_in ?? 0));
                $('#claim-stock-total-sent').text((totals.total_claim_sent ?? 0));
                $('#claim-stock-current').text((totals.current_claim_stock ?? 0));
            },
            error: function() {
                $('#claim-stock-detail-modal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load claim stock history. Please try again.'
                });
            }
        });
    }
    // Expose globally so inline onclick can find it
    window.openClaimStockLedger = openClaimStockLedger;
    // Also bind via jQuery and plain JS to be extra safe
    $(document).on('click', '#claim-stock-summary-badge', openClaimStockLedger);
    // Load button inside modal footer (reload current scope)
    $(document).on('click', '#claim-stock-detail-load-btn', function() {
        if (typeof window.openClaimStockLedger === 'function') {
            window.openClaimStockLedger({});
        }
    });
    document.addEventListener('click', function(e) {
        var target = e.target;
        if (!target) return;
        if (target.id === 'claim-stock-summary-badge' || target.closest && target.closest('#claim-stock-summary-badge')) {
            openClaimStockLedger(e);
        }
    });

    // Open detailed claim send history (only Claim Send rows)
    function openClaimSendStockLedger(e) {
        const branchId = $('#salesBranchId').val();
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before viewing claim send history.',
                confirmButtonText: 'OK'
            });
            return;
        }

        const itemId = $('#selected-item-id').val() || null;
        const scopeBits = [];
        const branchName = $('#branchName').text() || '';
        if (branchName) scopeBits.push('Branch: ' + branchName);
        if (itemId && $('#item-search').val()) {
            scopeBits.push('Item: ' + $('#item-search').val());
        }

        $('#claim-send-stock-detail-scope').text(scopeBits.length ? scopeBits.join(' | ') : 'All items in this branch');
        $('#claim-send-stock-detail-loading').removeClass('d-none');
        $('#claim-send-stock-detail-content').addClass('d-none');
        $('#claim-send-stock-detail-tbody').empty();
        $('#claim-send-stock-detail-empty').addClass('d-none');

        $('#claim-send-stock-detail-modal').modal('show');
        $.ajax({
            url: '{{ route("sales.claim.send.stock.detail") }}',
            method: 'GET',
            data: {
                branch_id: branchId,
                item_id: itemId
            },
            success: function(res) {
                $('#claim-send-stock-detail-loading').addClass('d-none');
                $('#claim-send-stock-detail-content').removeClass('d-none');

                const records = res.records || [];
                const $tbody = $('#claim-send-stock-detail-tbody');
                $tbody.empty();
                const salesEditUrlTemplate = '{{ route("sales.edit", ":id") }}';
                const purchasesEditUrlTemplate = '{{ route("purchases.edit", ":id") }}';

                if (!records.length) {
                    $('#claim-send-stock-detail-empty').removeClass('d-none');
                } else {
                    $('#claim-send-stock-detail-empty').addClass('d-none');
                    records.forEach(function(r) {
                        const prevStock = (r.previous_stock !== undefined) ? parseFloat(r.previous_stock || 0) : 0;
                        const stockIn = (r.stock_in !== undefined) ? parseFloat(r.stock_in || 0) : Math.max(0, parseFloat(r.quantity || 0));
                        const stockOut = (r.stock_out !== undefined)
                            ? parseFloat(r.stock_out || 0)
                            : (parseFloat(r.quantity || 0) < 0 ? Math.abs(parseFloat(r.quantity || 0)) : 0);
                        const balanceAfter = (r.balance_after !== undefined)
                            ? parseFloat(r.balance_after || 0)
                            : (prevStock + stockIn - stockOut);

                        const fmt = function(v) {
                            if (!isFinite(v)) return '0';
                            const n = Number(v);
                            return (Math.floor(n) === n) ? String(n) : n.toFixed(2).replace(/\.00$/, '');
                        };

                        const stockInCell = stockIn > 0
                            ? '<span class="text-success fw-semibold">' + fmt(stockIn) + '</span>'
                            : '<span class="text-muted">-</span>';

                        const stockOutCell = stockOut > 0
                            ? '<span class="text-danger fw-semibold">' + fmt(stockOut) + '</span>'
                            : '<span class="text-muted">-</span>';

                        const balanceClass = balanceAfter < 0 ? 'text-danger fw-semibold' : 'text-primary fw-semibold';
                        const balanceCell = '<span class="' + balanceClass + '">' + fmt(balanceAfter) + '</span>';

                        const tr = `
                            <tr>
                                <td><span class="d-block">${r.date || ''}</span><span class="d-block small text-muted">${r.time || ''}</span></td>
                                <td>
                                    <span class="d-block">${r.customer_name || ''}</span>
                                    ${r.invoice_no ? `<span class="d-block small text-muted">${r.invoice_no}</span>` : ''}
                                </td>
                                <td>
                                    <span class="d-block">${r.item_name || ''}</span>
                                    ${r.item_code ? `<span class="d-block small text-muted">${r.item_code}</span>` : ''}
                                </td>
                                <td>
                                    <span class="d-block">${r.branch_name || ''}</span>
                                    <span class="d-block small text-muted">${r.warehouse_name || ''}</span>
                                </td>
                                <td class="text-end text-muted text-nowrap">${fmt(prevStock)}</td>
                                <td class="text-end text-nowrap">${stockInCell}</td>
                                <td class="text-end text-nowrap">${stockOutCell}</td>
                                <td class="text-end text-nowrap">${balanceCell}</td>
                                <td>${r.entry_type_label || ''}</td>
                                <td>
                                    <div>${r.remarks || ''}</div>
                                    <div>
                                        ${r.sale_id
                                            ? `<button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="window.location='${salesEditUrlTemplate.replace(':id', r.sale_id)}'">Edit</button>`
                                            : (r.purchase_id
                                                ? `<button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="window.location='${purchasesEditUrlTemplate.replace(':id', r.purchase_id)}'">Edit</button>`
                                                : '')}
                                    </div>
                                </td>
                            </tr>
                        `;
                        $tbody.append(tr);
                    });
                }

                const totals = res.totals || {};
                $('#claim-send-stock-total-in').text((totals.total_claim_in ?? 0));
                $('#claim-send-stock-total-sent').text((totals.total_claim_sent ?? 0));
                $('#claim-send-stock-current').text((totals.current_claim_stock ?? 0));
            },
            error: function() {
                $('#claim-send-stock-detail-modal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load claim send history. Please try again.'
                });
            }
        });
    }
    window.openClaimSendStockLedger = openClaimSendStockLedger;
    $(document).on('click', '#claim-send-stock-summary-badge', openClaimSendStockLedger);
    $(document).on('click', '#claim-send-stock-detail-load-btn', function() {
        if (typeof window.openClaimSendStockLedger === 'function') {
            window.openClaimSendStockLedger({});
        }
    });
    document.addEventListener('click', function(e) {
        var target = e.target;
        if (!target) return;
        if (target.id === 'claim-send-stock-summary-badge' || (target.closest && target.closest('#claim-send-stock-summary-badge'))) {
            openClaimSendStockLedger(e);
        }
    });

    // Open detailed scrap stock history (used by Scrap Stock badge)
    function openScrapStockLedger(e) {
        const branchId = $('#salesBranchId').val();
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before viewing scrap stock history.',
                confirmButtonText: 'OK'
            });
            return;
        }

        const itemId = $('#selected-item-id').val() || null;
        const scopeBits = [];
        const branchName = $('#branchName').text() || '';
        if (branchName) scopeBits.push('Branch: ' + branchName);
        if (itemId && $('#item-search').val()) {
            scopeBits.push('Item: ' + $('#item-search').val());
        }

        $('#scrap-stock-detail-scope').text(scopeBits.length ? scopeBits.join(' | ') : 'All scrap items in this branch');
        $('#scrap-stock-detail-loading').removeClass('d-none');
        $('#scrap-stock-detail-content').addClass('d-none');
        $('#scrap-stock-detail-tbody').empty();
        $('#scrap-stock-detail-empty').addClass('d-none');

        $('#scrap-stock-detail-modal').modal('show');
        $.ajax({
            url: '{{ route("sales.scrap.stock.detail") }}',
            method: 'GET',
            data: {
                branch_id: branchId,
                item_id: itemId
            },
            success: function(res) {
                $('#scrap-stock-detail-loading').addClass('d-none');
                $('#scrap-stock-detail-content').removeClass('d-none');

                const records = res.records || [];
                const $tbody = $('#scrap-stock-detail-tbody');
                $tbody.empty();

                if (!records.length) {
                    $('#scrap-stock-detail-empty').removeClass('d-none');
                } else {
                    $('#scrap-stock-detail-empty').addClass('d-none');
                    records.forEach(function(r) {
                        const prevStock = (r.previous_stock !== undefined) ? parseFloat(r.previous_stock || 0) : 0;
                        const stockIn = (r.stock_in !== undefined) ? parseFloat(r.stock_in || 0) : (parseFloat(r.quantity || 0) > 0 ? parseFloat(r.quantity || 0) : 0);
                        const stockOut = (r.stock_out !== undefined)
                            ? parseFloat(r.stock_out || 0)
                            : (parseFloat(r.quantity || 0) < 0 ? Math.abs(parseFloat(r.quantity || 0)) : 0);
                        const balanceAfter = (r.balance_after !== undefined) ? parseFloat(r.balance_after || 0) : (prevStock + stockIn - stockOut);

                        const fmt = function(v) {
                            if (!isFinite(v)) return '0';
                            const n = Number(v);
                            return (Math.floor(n) === n) ? String(n) : n.toFixed(2).replace(/\.00$/, '');
                        };

                        const stockInCell = stockIn > 0
                            ? '<span class="text-success fw-semibold">' + fmt(stockIn) + '</span>'
                            : '<span class="text-muted">-</span>';
                        const stockOutCell = stockOut > 0
                            ? '<span class="text-danger fw-semibold">' + fmt(stockOut) + '</span>'
                            : '<span class="text-muted">-</span>';
                        const balanceClass = balanceAfter < 0 ? 'text-danger fw-semibold' : 'text-primary fw-semibold';
                        const balanceCell = '<span class="' + balanceClass + '">' + fmt(balanceAfter) + '</span>';
                        const tr = `
                            <tr>
                                <td><span class="d-block">${r.date || ''}</span><span class="d-block small text-muted">${r.time || ''}</span></td>
                                <td>${r.customer_name || ''}</td>
                                <td>${r.invoice_no || ''}</td>
                                <td>
                                    <span class="d-block">${r.item_name || ''}</span>
                                    ${r.item_code ? `<span class="d-block small text-muted">${r.item_code}</span>` : ''}
                                </td>
                                <td>${r.branch_name || ''}</td>
                                <td>${r.warehouse_name || ''}</td>
                                <td class="text-end text-muted">${fmt(prevStock)}</td>
                                <td class="text-end">${stockInCell}</td>
                                <td class="text-end">${stockOutCell}</td>
                                <td class="text-end">${balanceCell}</td>
                                <td>${r.entry_type_label || ''}</td>
                                <td>${r.remarks || ''}</td>
                            </tr>
                        `;
                        $tbody.append(tr);
                    });
                }

                const totals = res.totals || {};
                $('#scrap-stock-total-in').text((totals.total_scrap_in ?? 0));
                $('#scrap-stock-total-sent').text((totals.total_scrap_sent ?? 0));
                $('#scrap-stock-current').text((totals.current_scrap_stock ?? 0));
            },
            error: function() {
                $('#scrap-stock-detail-modal').modal('hide');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load scrap stock history. Please try again.'
                });
            }
        });
    }

    window.openScrapStockLedger = openScrapStockLedger;
    $(document).on('click', '#scrap-stock-summary-badge', openScrapStockLedger);

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
        let hoursNum = now.getHours();
        const ampm = hoursNum >= 12 ? 'PM' : 'AM';
        hoursNum = hoursNum % 12;
        hoursNum = hoursNum ? hoursNum : 12; // 0 => 12
        const hours = String(hoursNum).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        $('#currentDateTime').text(`${day}/${month}/${year}, ${hours}:${minutes}:${seconds} ${ampm}`);
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
        const customerId = ($('#customer_id').val() || '').toString().trim();
        
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before adding items.',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (!customerId) {
            Swal.fire({
                icon: 'warning',
                title: 'Customer Required',
                text: 'Please select a party/customer first.',
                confirmButtonText: 'OK'
            });
            if ($('#customer_id').hasClass('select2-hidden-accessible')) {
                $('#customer_id').select2('open');
            } else {
                $('#customer_id').trigger('focus');
            }
            return;
        }
        
        currentEntryType = 'sale';
        $('#customer-history-label-text').text('CUSTOMER HISTORY');
        $('#purchase-history-label-text').text('LAST 5 PURCHASE HISTORY');
        // Open the item details modal directly (search is inside this modal)
        $('#add-item-modal-title').html('<i class="ti ti-plus me-2"></i>ADD SALE ITEM');
        $('#add-item-modal').modal('show');
    });

    // ----- Temporary Sale (non-stock; voice/image/text) -----
    window.isMobileSalesBrowser = function() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent || '');
    };
    window._tempSalePendingImageData = null;
    window._tempSaleVoiceData = null;
    window._tempSaleMediaRecorder = null;
    window._tempSaleMediaStream = null;
    window._tempSaleVoiceChunks = [];
    window._tempSaleVoiceTimer = null;
    window._tempSaleSpeechRec = null;
    window._tempSaleDictationTimer = null;
    window._tempSaleWasRowEdit = false;
    window._tempSaleModalSaved = false;

    function setTemporarySaleVoiceRecordButtonIdle() {
        var $btn = $('#temp-sale-voice-record-btn');
        $btn.removeClass('btn-danger').addClass('btn-outline-danger').prop('disabled', false);
        $('#temp-sale-voice-record-icon').removeClass('ti-square').addClass('ti-microphone');
        $('#temp-sale-voice-record-label').text('Record');
    }

    function setTemporarySaleVoiceRecordButtonStopping() {
        var $btn = $('#temp-sale-voice-record-btn');
        $btn.removeClass('btn-outline-danger').addClass('btn-danger').prop('disabled', false);
        $('#temp-sale-voice-record-icon').removeClass('ti-microphone').addClass('ti-square');
        $('#temp-sale-voice-record-label').text('Stop');
    }

    function stopTemporarySaleVoiceRecording() {
        if (window._tempSaleVoiceTimer) {
            clearInterval(window._tempSaleVoiceTimer);
            window._tempSaleVoiceTimer = null;
        }
        if (window._tempSaleMediaRecorder && window._tempSaleMediaRecorder.state !== 'inactive') {
            try { window._tempSaleMediaRecorder.stop(); } catch (e) { /* ignore */ }
        }
        if (window._tempSaleMediaStream) {
            window._tempSaleMediaStream.getTracks().forEach(function(t) { try { t.stop(); } catch (e) { /* ignore */ } });
            window._tempSaleMediaStream = null;
        }
        window._tempSaleMediaRecorder = null;
        setTemporarySaleVoiceRecordButtonIdle();
        $('#temp-sale-voice-timer').text('');
    }

    function updateTempSaleLineTotal() {
        var q = parseFloat($('#temp-sale-qty').val()) || 0;
        var r = parseFloat($('#temp-sale-rate').val()) || 0;
        var t = q * r;
        if (typeof t !== 'number' || isNaN(t)) t = 0;
        $('#temp-sale-line-total').val(t.toFixed(2));
    }

    function resetTemporarySaleModal() {
        stopTemporarySaleVoiceRecording();
        if (window._tempSaleDictationTimer) {
            clearTimeout(window._tempSaleDictationTimer);
            window._tempSaleDictationTimer = null;
        }
        if (window._tempSaleSpeechRec) {
            try { window._tempSaleSpeechRec.abort(); } catch (e) { /* ignore */ }
            window._tempSaleSpeechRec = null;
        }
        $('#temp-sale-stt-status').text('');
        $('#temp-sale-image').val('');
        window._tempSalePendingImageData = null;
        $('#temp-sale-image-preview-wrap').addClass('d-none');
        $('#temp-sale-image-preview').attr('src', '');
        window._tempSaleVoiceData = null;
        $('#temp-sale-voice-audio').addClass('d-none').attr('src', '');
        $('#temp-sale-voice-remove').addClass('d-none');
        $('#temp-sale-item-name').val('');
        $('#temp-sale-voice-transcript').val('');
        $('#temp-sale-quality').val('');
        $('#temp-sale-notes').val('');
        $('#temp-sale-qty').val(1);
        $('#temp-sale-rate').val(0);
        updateTempSaleLineTotal();
        if (typeof window.hideTempSaleNameSuggestions === 'function') window.hideTempSaleNameSuggestions();
    }

    function syncTemporarySaleMobileImageHint() {
        if (window.isMobileSalesBrowser()) {
            $('#temp-sale-mobile-image-hint').removeClass('d-none');
            $('#temp-sale-image-label').html('Photo <span class="text-danger">*</span> <span class="text-muted fw-normal small">(required on mobile)</span>');
        } else {
            $('#temp-sale-mobile-image-hint').addClass('d-none');
            $('#temp-sale-image-label').html('Photo <span class="text-muted fw-normal">(optional on desktop)</span>');
        }
    }

    $('#temporary-sale-modal').on('show.bs.modal', function() {
        syncTemporarySaleMobileImageHint();
    });

    $('#temporary-sale-btn').on('click', function() {
        var branchId = $('#salesBranchId').val();
        var customerId = ($('#customer_id').val() || '').toString().trim();
        if (!branchId) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first.', confirmButtonText: 'OK' });
            return;
        }
        if (!customerId) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select a party/customer first.', confirmButtonText: 'OK' });
            if ($('#customer_id').hasClass('select2-hidden-accessible')) $('#customer_id').select2('open');
            else $('#customer_id').trigger('focus');
            return;
        }
        window._tempSaleWasRowEdit = false;
        window._tempSaleModalSaved = false;
        editingRowId = null;
        resetTemporarySaleModal();
        $('#temporary-sale-modal').modal('show');
    });

    $('#temp-sale-qty, #temp-sale-rate').on('input change', function() { updateTempSaleLineTotal(); });

    $('#temp-sale-image').on('change', function() {
        var f = this.files && this.files[0];
        if (!f) {
            window._tempSalePendingImageData = null;
            $('#temp-sale-image-preview-wrap').addClass('d-none');
            return;
        }
        if (!/^image\//i.test(f.type)) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Invalid file', text: 'Please choose an image.' });
            $(this).val('');
            return;
        }
        var reader = new FileReader();
        reader.onload = function(ev) {
            var dataUrl = (ev.target && ev.target.result) ? String(ev.target.result) : '';
            window._tempSalePendingImageData = dataUrl || null;
            if (dataUrl) {
                $('#temp-sale-image-preview').attr('src', dataUrl);
                $('#temp-sale-image-preview-wrap').removeClass('d-none');
            }
        };
        reader.readAsDataURL(f);
    });

    $('#temp-sale-image-clear').on('click', function() {
        $('#temp-sale-image').val('');
        window._tempSalePendingImageData = null;
        $('#temp-sale-image-preview').attr('src', '');
        $('#temp-sale-image-preview-wrap').addClass('d-none');
    });

    $('#temp-sale-voice-record-btn').on('click', function() {
        if (window._tempSaleMediaRecorder && window._tempSaleMediaRecorder.state === 'recording') {
            window._tempSaleMediaRecorder.stop();
            return;
        }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'Not supported', text: 'Voice recording is not supported in this browser.' });
            return;
        }
        window._tempSaleVoiceChunks = [];
        navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
            window._tempSaleMediaStream = stream;
            var mime = '';
            if (window.MediaRecorder && MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) mime = 'audio/webm;codecs=opus';
            else if (window.MediaRecorder && MediaRecorder.isTypeSupported('audio/webm')) mime = 'audio/webm';
            window._tempSaleMediaRecorder = mime ? new MediaRecorder(stream, { mimeType: mime }) : new MediaRecorder(stream);
            window._tempSaleMediaRecorder.ondataavailable = function(e) {
                if (e.data && e.data.size > 0) window._tempSaleVoiceChunks.push(e.data);
            };
            window._tempSaleMediaRecorder.onstop = function() {
                if (window._tempSaleVoiceTimer) {
                    clearInterval(window._tempSaleVoiceTimer);
                    window._tempSaleVoiceTimer = null;
                }
                if (window._tempSaleMediaStream) {
                    window._tempSaleMediaStream.getTracks().forEach(function(t) { try { t.stop(); } catch (e) { /* ignore */ } });
                    window._tempSaleMediaStream = null;
                }
                var mimeType = (window._tempSaleMediaRecorder && window._tempSaleMediaRecorder.mimeType) ? window._tempSaleMediaRecorder.mimeType : 'audio/webm';
                var blob = new Blob(window._tempSaleVoiceChunks, { type: mimeType });
                window._tempSaleMediaRecorder = null;
                var fr = new FileReader();
                fr.onload = function() {
                    window._tempSaleVoiceData = (fr.result && String(fr.result)) || null;
                    if (window._tempSaleVoiceData) {
                        $('#temp-sale-voice-remove').removeClass('d-none');
                        var url = URL.createObjectURL(blob);
                        $('#temp-sale-voice-audio').removeClass('d-none').attr('src', url);
                    }
                    setTemporarySaleVoiceRecordButtonIdle();
                    $('#temp-sale-voice-timer').text('');
                };
                fr.readAsDataURL(blob);
            };
            window._tempSaleMediaRecorder.start(200);
            setTemporarySaleVoiceRecordButtonStopping();
            var left = 15;
            $('#temp-sale-voice-timer').text('0:' + (left < 10 ? '0' : '') + left);
            window._tempSaleVoiceTimer = setInterval(function() {
                left -= 1;
                if (left <= 0) {
                    if (window._tempSaleMediaRecorder && window._tempSaleMediaRecorder.state === 'recording') {
                        window._tempSaleMediaRecorder.stop();
                    }
                    return;
                }
                $('#temp-sale-voice-timer').text('0:' + (left < 10 ? '0' : '') + left);
            }, 1000);
            setTimeout(function() {
                if (window._tempSaleMediaRecorder && window._tempSaleMediaRecorder.state === 'recording') {
                    window._tempSaleMediaRecorder.stop();
                }
            }, 15000);
        }).catch(function() {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Microphone', text: 'Allow microphone access to record voice.' });
        });
    });

    $('#temp-sale-voice-remove').on('click', function() {
        window._tempSaleVoiceData = null;
        $('#temp-sale-voice-audio').addClass('d-none').attr('src', '');
        $(this).addClass('d-none');
    });

    $('#temp-sale-speak-name-btn').on('click', function() {
        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'Not available', text: 'Speech-to-text works in Chrome/Edge on desktop and many Android devices. Type the item name instead.' });
            return;
        }
        if (window._tempSaleSpeechRec) {
            try { window._tempSaleSpeechRec.abort(); } catch (e) { /* ignore */ }
        }
        var rec = new SpeechRecognition();
        rec.lang = (navigator.language || 'en-US');
        rec.interimResults = true;
        rec.maxAlternatives = 1;
        rec.continuous = true;
        $('#temp-sale-stt-status').text('Listening… speak the item name');
        rec.onresult = function(e) {
            var t = '';
            for (var i = 0; i < e.results.length; i++) {
                t += e.results[i][0].transcript;
            }
            t = (t || '').trim();
            if (t) {
                $('#temp-sale-item-name').val(t).trigger('input');
                $('#temp-sale-voice-transcript').val(t);
            }
        };
        rec.onerror = function() {
            $('#temp-sale-stt-status').text('');
        };
        rec.onend = function() {
            $('#temp-sale-stt-status').text('');
            window._tempSaleSpeechRec = null;
            if (window._tempSaleDictationTimer) {
                clearTimeout(window._tempSaleDictationTimer);
                window._tempSaleDictationTimer = null;
            }
        };
        window._tempSaleSpeechRec = rec;
        try {
            rec.start();
        } catch (err) {
            $('#temp-sale-stt-status').text('');
            return;
        }
        window._tempSaleDictationTimer = setTimeout(function() {
            try { rec.stop(); } catch (e2) { /* ignore */ }
        }, 15000);
    });

    function normalizeTemporaryText(value) {
        return String(value == null ? '' : value)
            .trim()
            .toLowerCase()
            .replace(/\s+/g, ' ');
    }

    function proceedWithTemporarySaleAdd() {
        if (window._tempSaleAddInProgress) return;
        window._tempSaleAddInProgress = true;
        var $tempAddBtn = $('#temp-sale-add-btn');
        var prevBtnHtml = $tempAddBtn.html();
        $tempAddBtn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>Adding...');
        var tid = window.__SALE_TEMPORARY_ITEM_ID__;
        if (!tid) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Not configured', text: 'Temporary sale is not set up. Run database migrations.' });
            window._tempSaleAddInProgress = false;
            $tempAddBtn.prop('disabled', false).html(prevBtnHtml);
            return;
        }
        var imgData = window._tempSalePendingImageData || null;
        if (window.isMobileSalesBrowser() && !imgData) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Photo required', text: 'On mobile, take at least one picture of the item.' });
            window._tempSaleAddInProgress = false;
            $tempAddBtn.prop('disabled', false).html(prevBtnHtml);
            return;
        }
        var voiceBlob = (window._tempSaleVoiceData || '').trim();
        var transcript = ($('#temp-sale-voice-transcript').val() || '').trim();
        var itemName = ($('#temp-sale-item-name').val() || '').trim();
        if (!voiceBlob && !transcript && !itemName) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Voice or text', text: 'Record a voice note, dictate the name, or type an item name.' });
            window._tempSaleAddInProgress = false;
            $tempAddBtn.prop('disabled', false).html(prevBtnHtml);
            return;
        }
        var qty = parseFloat($('#temp-sale-qty').val()) || 0;
        var rate = parseFloat($('#temp-sale-rate').val()) || 0;
        if (qty <= 0) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Quantity', text: 'Enter quantity greater than zero.' });
            window._tempSaleAddInProgress = false;
            $tempAddBtn.prop('disabled', false).html(prevBtnHtml);
            return;
        }
        if (rate < 0) rate = 0;
        if (imgData && String(imgData).length > 650000) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Image too large', text: 'Use a smaller photo.' });
            window._tempSaleAddInProgress = false;
            $tempAddBtn.prop('disabled', false).html(prevBtnHtml);
            return;
        }
        if (voiceBlob && String(voiceBlob).length > 650000) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Voice clip too large', text: 'Record a shorter note.' });
            window._tempSaleAddInProgress = false;
            $tempAddBtn.prop('disabled', false).html(prevBtnHtml);
            return;
        }
        var quality = ($('#temp-sale-quality').val() || '').trim();
        var notes = ($('#temp-sale-notes').val() || '').trim();
        var discountAmount = 0;
        var taxPct = 0;
        var taxAmount = 0;
        var subtotal = (qty * rate) - discountAmount;
        var total = subtotal + taxAmount;
        var displayName = itemName || transcript || 'Temporary item';
        var thumb = imgData || null;
        var rowId = editingRowId;
        var isEdit = rowId !== null && rowId !== undefined;
        var existingIdx = isEdit ? salesItems.findIndex(function(i) { return i.id === rowId; }) : -1;
        if (isEdit && existingIdx === -1) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'Line not found', text: 'Remove the line and add again.' });
            editingRowId = null;
            window._tempSaleAddInProgress = false;
            $tempAddBtn.prop('disabled', false).html(prevBtnHtml);
            return;
        }

        // Duplicate protection: if same temporary item data is already loaded, merge quantity instead of creating another row.
        var tempNameKey = normalizeTemporaryText(itemName || displayName);
        var tempQualityKey = normalizeTemporaryText(quality);
        var mergedIdx = -1;
        if (!isEdit) {
            mergedIdx = salesItems.findIndex(function (it) {
                if (!it || String(it.entry_type || '') !== 'temporary') return false;
                if (Number(it.item_id || 0) !== Number(tid || 0)) return false;
                var itNameKey = normalizeTemporaryText((it.temporary_item_name || it.name || ''));
                var itQualityKey = normalizeTemporaryText(it.temporary_quality || '');
                var itRate = parseFloat(it.rate) || 0;
                return itNameKey === tempNameKey && itQualityKey === tempQualityKey && Math.abs(itRate - rate) < 0.0001;
            });
        }
        var lineObj = {
            id: isEdit && existingIdx !== -1 ? salesItems[existingIdx].id : itemCounter++,
            item_id: tid,
            name: displayName,
            temporary_item_name: itemName || null,
            temporary_quality: quality || null,
            line_note: notes || null,
            line_image: imgData,
            voice_data: voiceBlob || null,
            voice_transcript: transcript || null,
            item_type: null,
            part_number: null,
            quality_name: null,
            company_name: null,
            category_name: null,
            product_type_label: null,
            product_title: null,
            quantity: qty,
            quantity_display: null,
            unit: 'Unit',
            rate: rate,
            discount: discountAmount,
            tax_percentage: taxPct,
            tax_amount: taxAmount,
            total: total,
            warranty: null,
            entry_type: 'temporary',
            supplier_id: null,
            is_zero_stock: false,
            warehouse_id: null,
            warehouse_name: null,
            branch_name: null,
            quantity_cans: null,
            quantity_base_liters: null,
            quantity_extra_ml: null,
            mileage_id: null,
            mileage_name: null,
            image: thumb,
            warranty_proofs: null
        };
        window._tempSaleModalSaved = true;
        if (isEdit && existingIdx !== -1) {
            salesItems[existingIdx] = lineObj;
            $('#items-tbody tr[data-row-id="' + rowId + '"]').remove();
            editingRowId = null;
            $('#add-item-modal-title').html('<i class="ti ti-plus me-2"></i>ADD SALE ITEM');
        } else if (mergedIdx !== -1) {
            var existingLine = salesItems[mergedIdx];
            existingLine.quantity = (parseFloat(existingLine.quantity) || 0) + qty;
            existingLine.rate = rate;
            existingLine.tax_percentage = taxPct;
            existingLine.discount = discountAmount;
            existingLine.tax_amount = 0;
            existingLine.total = ((parseFloat(existingLine.quantity) || 0) * rate) - discountAmount;
            if (!existingLine.line_image && thumb) existingLine.line_image = thumb;
            if (!existingLine.image && thumb) existingLine.image = thumb;
            if (!existingLine.voice_data && voiceBlob) existingLine.voice_data = voiceBlob;
            if (!existingLine.voice_transcript && transcript) existingLine.voice_transcript = transcript;
            if (!existingLine.temporary_item_name && itemName) existingLine.temporary_item_name = itemName;
            if (!existingLine.temporary_quality && quality) existingLine.temporary_quality = quality;
            if (!existingLine.line_note && notes) existingLine.line_note = notes;
            $('#items-tbody tr[data-row-id="' + existingLine.id + '"]').remove();
            addItemToTable(existingLine);
            editingRowId = null;
        } else {
            salesItems.push(lineObj);
            editingRowId = null;
            addItemToTable(lineObj);
        }
        $('#temporary-sale-modal').modal('hide');
        calculateTotals();
        if (typeof syncCartToServer === 'function') syncCartToServer();
        if (typeof salesItems !== 'undefined' && salesItems.length > 0) {
            $('#payment-section').show();
            if ($('#payment-amount-row').length) $('#payment-amount-row').show();
        }
        if (typeof updateSalesPrintButton === 'function') updateSalesPrintButton();
        setTimeout(function () {
            window._tempSaleAddInProgress = false;
            $tempAddBtn.prop('disabled', false).html(prevBtnHtml);
        }, 120);
    }

    $('#temp-sale-add-btn').on('click', function() { proceedWithTemporarySaleAdd(); });

    $('#temporary-sale-modal').on('hidden.bs.modal', function() {
        stopTemporarySaleVoiceRecording();
        if (window._tempSaleDictationTimer) {
            clearTimeout(window._tempSaleDictationTimer);
            window._tempSaleDictationTimer = null;
        }
        if (window._tempSaleSpeechRec) {
            try { window._tempSaleSpeechRec.abort(); } catch (e) { /* ignore */ }
            window._tempSaleSpeechRec = null;
        }
        if (window._tempSaleWasRowEdit && !window._tempSaleModalSaved) {
            editingRowId = null;
            $('#add-item-modal-title').html('<i class="ti ti-plus me-2"></i>ADD SALE ITEM');
        }
        window._tempSaleWasRowEdit = false;
        window._tempSaleModalSaved = false;
        if (typeof window.hideTempSaleNameSuggestions === 'function') window.hideTempSaleNameSuggestions();
    });

    (function initTempSaleNameAutocomplete() {
        var $inp = $('#temp-sale-item-name');
        var $dd = $('#temp-sale-name-suggestions');
        var debounceTimer = null;
        var sugIndex = -1;
        var lastResults = [];

        function escapeHtml(s) {
            return String(s || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function highlightMatch(displayName, queryRaw) {
            var text = String(displayName || '');
            var q = String(queryRaw || '').trim();
            if (!q) return escapeHtml(text);
            var lower = text.toLowerCase();
            var ql = q.toLowerCase();
            var idx = lower.indexOf(ql);
            if (idx === -1) {
                var collapsed = lower.replace(/\s+/g, ' ');
                var qlc = ql.replace(/\s+/g, ' ');
                idx = collapsed.indexOf(qlc);
                if (idx !== -1) {
                    return escapeHtml(text);
                }
                return escapeHtml(text);
            }
            return escapeHtml(text.slice(0, idx)) + '<mark class="bg-warning text-dark">' + escapeHtml(text.slice(idx, idx + q.length)) + '</mark>' + escapeHtml(text.slice(idx + q.length));
        }

        function hideTempSaleNameSuggestions() {
            sugIndex = -1;
            lastResults = [];
            $dd.addClass('d-none').empty();
        }
        window.hideTempSaleNameSuggestions = hideTempSaleNameSuggestions;

        function fetchTempSaleSuggestions(q) {
            var url = window.__TEMP_SALE_NAME_SEARCH_URL__;
            if (!url) return;
            var branchId = $('#salesBranchId').val();
            if (!branchId) {
                hideTempSaleNameSuggestions();
                return;
            }
            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                data: { branch_id: branchId, q: q },
                success: function(data) {
                    lastResults = (data && data.results) ? data.results : [];
                    renderSuggestions(lastResults, q);
                },
                error: function() {
                    hideTempSaleNameSuggestions();
                }
            });
        }
        window.tempSaleNameFetchSuggestions = fetchTempSaleSuggestions;

        function renderSuggestions(results, q) {
            $dd.empty();
            if (!results || !results.length) {
                $dd.addClass('d-none');
                return;
            }
            results.forEach(function(r, i) {
                var label = highlightMatch(r.display_name, q);
                var metaLine = '';
                if (r.last_rate != null && r.last_rate !== '' && !isNaN(parseFloat(r.last_rate))) {
                    metaLine += 'Last rate: Rs ' + parseFloat(r.last_rate).toFixed(2);
                }
                if (r.last_quality) {
                    metaLine += (metaLine ? ' · ' : '') + 'Quality: ' + escapeHtml(r.last_quality);
                }
                var $btn = $('<button type="button" class="temp-sale-sugg-item list-group-item list-group-item-action" role="option" tabindex="-1"></button>');
                $btn.attr('data-idx', i);
                $btn.html('<span class="fw-semibold">' + label + '</span>' + (metaLine ? '<div class="temp-sale-sugg-meta">' + metaLine + '</div>' : ''));
                $btn.on('mousedown', function(e) {
                    e.preventDefault();
                    applySuggestion(i);
                });
                $dd.append($btn);
            });
            $dd.removeClass('d-none');
            sugIndex = -1;
        }

        function applySuggestion(idx) {
            if (idx < 0 || idx >= lastResults.length) return;
            var r = lastResults[idx];
            if (!r) return;
            $inp.val(r.display_name || '');
            if (r.last_rate != null && r.last_rate !== '' && !isNaN(parseFloat(r.last_rate))) {
                $('#temp-sale-rate').val(parseFloat(r.last_rate));
                if (typeof updateTempSaleLineTotal === 'function') updateTempSaleLineTotal();
            }
            if (r.last_quality) {
                $('#temp-sale-quality').val(r.last_quality);
            }
            hideTempSaleNameSuggestions();
            $inp.trigger('focus');
        }

        $inp.on('input', function() {
            clearTimeout(debounceTimer);
            var v = $(this).val();
            debounceTimer = setTimeout(function() {
                fetchTempSaleSuggestions(v);
            }, 80);
        });

        $inp.on('focus', function() {
            var v = $(this).val();
            fetchTempSaleSuggestions(v);
        });

        $inp.on('keydown', function(e) {
            if ($dd.hasClass('d-none') || !$dd.find('.temp-sale-sugg-item').length) {
                if (e.key === 'Escape') hideTempSaleNameSuggestions();
                return;
            }
            var $items = $dd.find('.temp-sale-sugg-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                sugIndex = Math.min(sugIndex + 1, $items.length - 1);
                $items.removeClass('active');
                if (sugIndex >= 0) $items.eq(sugIndex).addClass('active');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                sugIndex = Math.max(sugIndex - 1, -1);
                $items.removeClass('active');
                if (sugIndex >= 0) $items.eq(sugIndex).addClass('active');
            } else if (e.key === 'Enter') {
                if (sugIndex >= 0) {
                    e.preventDefault();
                    applySuggestion(sugIndex);
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                hideTempSaleNameSuggestions();
            }
        });

        $('#temporary-sale-modal').on('shown.bs.modal', function() {
            var v = ($inp.val() || '').toString();
            fetchTempSaleSuggestions(v);
        });
    })();

    // Enter key: trigger ADD SALE ITEM when not typing in an input/textarea/select and no modal open
    $(document).on('keydown', function(e) {
        if (e.key !== 'Enter' || e.which !== 13) return;
        var $target = $(e.target);
        if ($target.closest('input, textarea, select, [contenteditable="true"]').length) return;
        if ($('.modal.show').length) return;
        e.preventDefault();
        $('#add-new-item-btn').trigger('click');
    });

    // Open item create page in modal when "Add New Item" is clicked from search no-results
    $(document).on('click', '.btn-open-add-item-modal', function(e) {
        e.preventDefault();
        const url = $(this).data('create-url');
        if (!url) {
            return;
        }
        var $iframe = $('#add-new-item-iframe');
        var $saleModal = $('#add-item-modal');
        var $createModal = $('#add-new-item-modal');
        function openCreateInIframe() {
            $iframe.attr('src', url);
            $createModal.modal('show');
        }
        // Avoid two modals + backdrops fighting; wait until ADD SALE ITEM modal fully hides
        if ($saleModal.hasClass('show')) {
            $saleModal.one('hidden.bs.modal', function onSaleModalHidden() {
                openCreateInIframe();
            });
            $saleModal.modal('hide');
        } else {
            openCreateInIframe();
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
            const customerId = ($('#customer_id').val() || '').toString().trim();
            if (!customerId) {
                Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select a party/customer first.', confirmButtonText: 'OK' });
                return;
            }
            $('#add-new-item-modal').modal('hide');
            pendingItemIdAfterUpdate = event.data.itemId;
            currentEntryType = 'sale';
            $('#customer-history-label-text').text('CUSTOMER HISTORY');
            $('#purchase-history-label-text').text('LAST 5 PURCHASE HISTORY');
            $('#add-item-modal-title').html('<i class="ti ti-plus me-2"></i>ADD SALE ITEM');
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
        const customerId = ($('#customer_id').val() || '').toString().trim();
        if (!customerId) {
            Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select a party/customer first.', confirmButtonText: 'OK' });
            return;
        }
        currentEntryType = 'sale';
        $('#customer-history-label-text').text('CUSTOMER HISTORY');
        $('#purchase-history-label-text').text('LAST 5 PURCHASE HISTORY');
        $('#add-item-modal-title').html('<i class="ti ti-plus me-2"></i>ADD SALE ITEM');
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
        const customerId = ($('#customer_id').val() || '').toString().trim();
        
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before adding claim items.',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (!customerId) {
            Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select a party/customer first.', confirmButtonText: 'OK' });
            return;
        }
        
        currentEntryType = 'claim';
        $('#customer-history-label-text').text('This customer\'s history for this product');
        $('#purchase-history-label-text').text('LAST 5 RETURN HISTORY FOR CUSTOMER');
        $('#add-item-modal-title').html('<i class="ti ti-truck-delivery me-2"></i>CLAIM IN');
        $('#stock-status-section-label').html('<i class="ti ti-package me-2"></i>Claim stock');
        $('#add-item-modal').modal('show');
    });

    // Handle "Return" button - same modal as Add Item (like Smart Invoice Return)
    $('#return-entry-btn').on('click', function(e) {
        e.preventDefault();
        const branchId = $('#salesBranchId').val();
        const customerId = ($('#customer_id').val() || '').toString().trim();
        
        if (!branchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Branch Required',
                text: 'Please select a branch first before adding return items.',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (!customerId) {
            Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select a party/customer first.', confirmButtonText: 'OK' });
            return;
        }
        
        currentEntryType = 'return';
        $('#customer-history-label-text').text('CUSTOMER SALE HISTORY');
        $('#purchase-history-label-text').text('LAST 5 RETURN HISTORY FOR CUSTOMER');
        $('#add-item-modal-title').html('<i class="ti ti-arrow-back-up me-2"></i>RETURN');
        $('#stock-status-section-label').html('<i class="ti ti-package me-2"></i>Available stock');
        $('#add-item-modal').modal('show');
    });

    // Handle "Scrap In" button - same modal as Add Item
    $('#scrap-in-btn').on('click', function(e) {
        e.preventDefault();
        const branchId = $('#salesBranchId').val();
        const customerId = ($('#customer_id').val() || '').toString().trim();
        if (!branchId) {
            Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first before adding scrap in items.', confirmButtonText: 'OK' });
            return;
        }
        if (!customerId) {
            Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select a party/customer first.', confirmButtonText: 'OK' });
            return;
        }
        currentEntryType = 'scrap';
        $('#add-item-modal-title').html('<i class="ti ti-recycle me-2"></i>SCRAP IN');
        $('#stock-status-section-label').html('<i class="ti ti-package me-2"></i>Scrap stock');
        $('#add-item-modal').modal('show');
    });

    // Handle "Scrap Sale" button - same modal as Add Item
    $('#scrap-sale-btn').on('click', function(e) {
        e.preventDefault();
        const branchId = $('#salesBranchId').val();
        const customerId = ($('#customer_id').val() || '').toString().trim();
        if (!branchId) {
            Swal.fire({ icon: 'warning', title: 'Branch Required', text: 'Please select a branch first before adding scrap sale items.', confirmButtonText: 'OK' });
            return;
        }
        if (!customerId) {
            Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select a party/customer first.', confirmButtonText: 'OK' });
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
        $('#sales-selected-item-type, #sales-selected-part-number, #sales-selected-quality-name, #sales-selected-company-name, #sales-selected-category-name, #sales-selected-product-type-label, #sales-selected-product-title').val('');
        resetItemQuantitySelect();
        $('#item-unit').val('');
        $('#item-rate').val('0');
        $('#warranty-value').val('');
        $('#warranty-unit').val('');
        $('#item-mileage').val('');
        // Retail panel hidden until an item is loaded (applySaleRetailFromResponse shows it)
        saleBaseRetailPrice = null;
        $('#sale-item-retail-price-column').addClass('d-none');
        $('#sale-item-retail-price').val('');
        $('#sale-item-retail-percentage').val('').trigger('change');
        $('#sale-item-retail-after-calc').text('—');
        // Start with BOTH sections hidden; show later based on item type
        $('#add-item-modal-warranty-col').addClass('d-none');
        $('#add-item-modal-mileage-col').addClass('d-none');
        $('#customer-history-content').html('<p class="text-muted mb-0 small">Select item to view history</p>');
        $('#claim-global-product-history-content').html('<p class="text-muted mb-0 small text-center">Select a product to see the latest 5 sales across all customers</p>');
        var _isClaimModal = (typeof currentEntryType !== 'undefined' && currentEntryType === 'claim');
        $('#claim-global-product-history-section').toggleClass('d-none', !_isClaimModal);
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
        $('#selected-item-quality-wrap').html('').addClass('d-none');
        if (typeof resetSaleItemRateColumnsForModal === 'function') resetSaleItemRateColumnsForModal();
    });

    window._suppressSalePerLiterSync = false;

    function syncSaleItemPerLiterFromCan() {
        if (window._suppressSalePerLiterSync) return;
        if (!$('#sale-item-per-liter-column').length || $('#sale-item-per-liter-column').hasClass('d-none')) return;
        var lpc = parseFloat($('#item-liter-per-can').val()) || 0;
        if (lpc <= 0) return;
        var can = parseFloat($('#item-rate').val()) || 0;
        window._suppressSalePerLiterSync = true;
        $('#sale-item-per-liter-rate').val(can > 0 ? (can / lpc).toFixed(2) : '');
        window._suppressSalePerLiterSync = false;
    }

    function syncSaleItemCanFromPerLiter() {
        if (window._suppressSalePerLiterSync) return;
        if (!$('#sale-item-per-liter-column').length || $('#sale-item-per-liter-column').hasClass('d-none')) return;
        var lpc = parseFloat($('#item-liter-per-can').val()) || 0;
        if (lpc <= 0) return;
        var pl = parseFloat($('#sale-item-per-liter-rate').val()) || 0;
        window._suppressSalePerLiterSync = true;
        $('#item-rate').val(pl > 0 ? Math.round(pl * lpc) : 0);
        window._suppressSalePerLiterSync = false;
    }

    /**
     * Set can + per-liter fields from item API (sales or purchase details).
     * opts.lineCanRate: when editing a line, keep this can rate instead of master sale_price.
     */
    function applySaleItemRatesFromDetailsResponse(response, opts) {
        if (!response) return;
        opts = opts || {};
        var lpc = (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can))) ? parseFloat(response.liter_per_can) : 0;
        $('#item-liter-per-can').val(lpc > 0 ? lpc : '');

        var spb = (response.sale_price_per_base != null && response.sale_price_per_base !== '' && !isNaN(parseFloat(response.sale_price_per_base))) ? parseFloat(response.sale_price_per_base) : 0;
        var salePrice = (response.sale_price != null && response.sale_price !== '' && !isNaN(parseFloat(response.sale_price))) ? parseFloat(response.sale_price) : 0;
        var rateFallback = (response.rate != null && response.rate !== '' && !isNaN(parseFloat(response.rate))) ? parseFloat(response.rate) : 0;
        var totalFallback = (response.total_price != null && response.total_price !== '' && !isNaN(parseFloat(response.total_price))) ? parseFloat(response.total_price) : 0;
        var effectiveCan = salePrice > 0 ? salePrice : (rateFallback > 0 ? rateFallback : totalFallback);
        var lineOverride = opts.lineCanRate;
        if (lineOverride != null && lineOverride !== '' && !isNaN(parseFloat(lineOverride)) && parseFloat(lineOverride) > 0) {
            effectiveCan = parseFloat(lineOverride);
        }

        window._suppressSalePerLiterSync = true;
        if (lpc > 0) {
            $('#sale-item-per-liter-column').removeClass('d-none');
            $('#sale-item-can-rate-label').html('<i class="ti ti-droplet me-1 text-success"></i>Can price (Rs)');
            if (spb > 0 && effectiveCan > 0) {
                $('#item-rate').val(Math.round(effectiveCan));
                $('#sale-item-per-liter-rate').val(spb.toFixed(2));
            } else if (spb > 0) {
                $('#sale-item-per-liter-rate').val(spb.toFixed(2));
                $('#item-rate').val(Math.round(spb * lpc));
            } else if (effectiveCan > 0) {
                $('#item-rate').val(Math.round(effectiveCan));
                $('#sale-item-per-liter-rate').val((effectiveCan / lpc).toFixed(2));
            } else {
                $('#item-rate').val('0');
                $('#sale-item-per-liter-rate').val('');
            }
        } else {
            $('#sale-item-per-liter-column').addClass('d-none');
            $('#sale-item-per-liter-rate').val('');
            $('#sale-item-can-rate-label').html('<i class="ti ti-shopping-cart me-1"></i>Sale rate (Rs)');
            $('#item-rate').val(Math.round(effectiveCan) || 0);
        }
        window._suppressSalePerLiterSync = false;
    }

    function updateSaleModalOilQuantityRowVisibility(response) {
        if (!response) return;
        var literPerCan = (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can))) ? parseFloat(response.liter_per_can) : null;
        if (literPerCan != null && literPerCan > 0) {
            $('#quantity-row-normal').hide();
            $('#quantity-row-oil').removeClass('d-none').show();
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
    }

    function resetSaleItemRateColumnsForModal() {
        $('#sale-item-per-liter-column').addClass('d-none');
        $('#sale-item-per-liter-rate').val('');
        $('#sale-item-can-rate-label').html('<i class="ti ti-shopping-cart me-1"></i>Sale rate (Rs)');
    }

    // Sale modal: retail price formula (same as Purchase – Base + GST + R.Tax, then Adjust by %)
    function updateSaleRetailAfterCalc() {
        var retail = parseFloat($('#sale-item-retail-price').val()) || 0;
        var pctVal = ($('#sale-item-retail-percentage').val() || '').toString().trim();
        var taxPct = parseFloat(String($('#sale-item-tax-percent').val()).replace(/%/g, '')) || 0;
        var rtaxPct = parseFloat($('#sale-item-rtax-percent').val()) || 0.5;
        if (rtaxPct === 0.05) rtaxPct = 0.5;
        var priceAfterGst = retail + Math.round(retail * taxPct / 100);
        var rTaxAmt = Math.round(priceAfterGst * rtaxPct / 100);
        var baseAmount = priceAfterGst + rTaxAmt;
        var withTax;
        if (pctVal === '' || pctVal === '—' || (parseFloat(pctVal) === 0 && pctVal === '0')) { withTax = baseAmount; } else { withTax = baseAmount - (retail * (parseFloat(pctVal) || 0) / 100); }
        var el = $('#sale-item-retail-after-calc');
        if (retail <= 0) {
            el.text('—');
            // Do not clear can rate: staff may use only Can / per-liter fields when retail base is empty
            return;
        }
        var formatted = (Math.round(withTax * 100) / 100).toLocaleString('en-PK', { minimumFractionDigits: 2 });
        el.text('Rs ' + formatted);
        window._suppressSalePerLiterSync = true;
        $('#item-rate').val(Math.round(withTax));
        window._suppressSalePerLiterSync = false;
        syncSaleItemPerLiterFromCan();
    }
    $('#sale-item-retail-price, #sale-item-retail-percentage, #sale-item-tax-percent, #sale-item-rtax-percent').on('input change', updateSaleRetailAfterCalc);

    (function initSaleRetailPctCustomDropdown() {
        if (window._saleRetailPctDropdownInited) return;
        var $modal = $('#add-item-modal');
        var $sel = $('#sale-item-retail-percentage');
        var $trigger = $('#sale-item-retail-pct-trigger');
        var $list = $('#sale-item-retail-pct-list');
        var $label = $('#sale-item-retail-pct-label');
        if (!$trigger.length || !$list.length || !$sel.length) return;
        window._saleRetailPctDropdownInited = true;

        function formatPctLabel(raw) {
            var v = (raw === undefined || raw === null) ? '' : String(raw).trim();
            if (v === '' || v === '—') return { text: '—', cls: 'is-empty' };
            var n = parseInt(v, 10);
            if (isNaN(n)) return { text: '—', cls: 'is-empty' };
            if (n === 0) return { text: '0%', cls: 'is-zero' };
            if (n < 0) return { text: n + '%', cls: 'is-neg' };
            return { text: '+' + n + '%', cls: 'is-pos' };
        }

        function syncTriggerFromSelect() {
            var f = formatPctLabel($sel.val());
            $label.removeClass('is-empty is-zero is-neg is-pos').addClass(f.cls).text(f.text);
            var sv = $sel.val();
            sv = sv === undefined || sv === null ? '' : String(sv);
            $list.find('[role="option"]').each(function() {
                var $li = $(this);
                var dv = $li.attr('data-value');
                if (dv === undefined || dv === null) dv = '';
                var match = String(dv) === sv;
                $li.attr('aria-selected', match ? 'true' : 'false');
            });
        }

        function closePctMenu() {
            $list.prop('hidden', true);
            $trigger.attr('aria-expanded', 'false');
        }

        function openPctMenu() {
            $list.prop('hidden', false);
            $trigger.attr('aria-expanded', 'true');
        }

        $trigger.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($list.prop('hidden')) openPctMenu();
            else closePctMenu();
        });

        $list.on('mousedown', 'li[role="option"]', function(e) {
            e.preventDefault();
        });

        $list.on('click', 'li[role="option"]', function(e) {
            e.preventDefault();
            var v = $(this).attr('data-value');
            if (v === undefined || v === null) v = '';
            $sel.val(v === '' ? '' : String(v));
            $sel.trigger('change');
            syncTriggerFromSelect();
            closePctMenu();
        });

        $(document).on('click.saleRetailPctClose', function(e) {
            if ($list.prop('hidden')) return;
            if ($(e.target).closest('.sale-retail-pct-wrap').length) return;
            closePctMenu();
        });

        $(document).on('keydown.saleRetailPctEsc', function(e) {
            if (e.key !== 'Escape') return;
            if ($list.prop('hidden')) return;
            if (!$modal.is(':visible')) return;
            closePctMenu();
            $trigger.trigger('focus');
        });

        $modal.on('hide.bs.modal.saleRetailPct', function() {
            closePctMenu();
        });

        $sel.on('change.saleRetailPctSync', syncTriggerFromSelect);
        window.syncSaleRetailPctDropdownUI = syncTriggerFromSelect;
        syncTriggerFromSelect();
    })();

    function applySaleRetailFromResponse(response) {
        if (!response) return;
        saleBaseRetailPrice = null;
        $('#sale-item-retail-price').val('');
        $('#sale-item-retail-percentage').val('').trigger('change');
        $('#sale-item-retail-after-calc').text('—');
        if (response.r_tax_percentage != null && !isNaN(parseFloat(response.r_tax_percentage))) {
            $('#sale-item-rtax-percent').val(parseFloat(response.r_tax_percentage));
        }
        if (response.tax_percentage != null && !isNaN(parseFloat(response.tax_percentage))) {
            $('#sale-item-tax-percent').val(parseFloat(response.tax_percentage));
        }
        var base = null;
        if (response.retail_price != null && response.retail_price !== '' && !isNaN(parseFloat(response.retail_price))) {
            base = parseFloat(response.retail_price);
        } else if (response.retail_price_base != null && response.retail_price_base !== '' && !isNaN(parseFloat(response.retail_price_base))) {
            base = parseFloat(response.retail_price_base);
        }
        // Same idea as Purchase add-item: show retail box whenever an item is loaded so Adjust by % + sell preview is available (oil/can included)
        $('#sale-item-retail-price-column').removeClass('d-none');
        if (base != null && base > 0) {
            saleBaseRetailPrice = base;
            $('#sale-item-retail-price').val(Math.round(base));
            if (typeof updateSaleRetailAfterCalc === 'function') updateSaleRetailAfterCalc();
        } else if (typeof syncSaleItemPerLiterFromCan === 'function') {
            syncSaleItemPerLiterFromCan();
        }
    }

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
        // In Claim In mode: show claim stock section immediately with placeholder
        if ($('#add-item-modal-title').text().toLowerCase().indexOf('claim in') !== -1) {
            $('#stock-status-section-label').html('<i class="ti ti-package me-2"></i>Claim stock');
            $('#stock-status-section').show();
            $('#stock-status-content').show();
            $('#stock-status-list').html('<p class="text-muted mb-0 small text-center py-3"><i class="ti ti-package me-1"></i>Search and select an item to see claim stock by warehouse.</p>');
        }
        if (pendingItemIdAfterUpdate) {
            var itemId = pendingItemIdAfterUpdate;
            pendingItemIdAfterUpdate = null;
            pendingQuickBarcode = null;
            openCameraAfterQuickScan = false;
            $('#selected-item-id').val(itemId);
            $.get('{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId))
                .then(function(r) {
                    if (typeof applyItemSearchAndMetaFromDetailsResponse === 'function') {
                        applyItemSearchAndMetaFromDetailsResponse(r, { forceItemSearch: true });
                    } else {
                        $('#item-search').val(r.name || '');
                    }
                    if (typeof applySaleItemRatesFromDetailsResponse === 'function') applySaleItemRatesFromDetailsResponse(r);
                    if (typeof applySaleRetailFromResponse === 'function') applySaleRetailFromResponse(r);
                    if (typeof updateSaleModalOilQuantityRowVisibility === 'function') updateSaleModalOilQuantityRowVisibility(r);
                    applyAddItemModalTypeAndWarrantyMileageCols(r);
                    if (typeof loadItemStockStatus === 'function') loadItemStockStatus(itemId, function() {
                        if (($('#selected-item-id').val() || '').toString().trim() !== String(itemId)) return;
                        try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
                    });
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
    $('#add-item-modal').on('hide.bs.modal', function() {
        editingRowId = null;
        $('#add-item-modal-title').html('<i class="ti ti-plus me-2"></i>ADD SALE ITEM');
        $('#customer-history-label-text').text('CUSTOMER HISTORY');
        $('#purchase-history-label-text').text('LAST 5 PURCHASE HISTORY');
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
            data: { 
                q: barcode, 
                branch_id: branchId, 
                limit: 15, 
                // Force entry_type based on modal title (avoid stale currentEntryType).
                for_sale: (function() {
                    const t = ($('#add-item-modal-title').text() || '').toLowerCase();
                    return (t.indexOf('scrap sale') !== -1 || t.indexOf('add sale item') !== -1) ? 1 : 0;
                })(),
                entry_type: (function() {
                    const t = ($('#add-item-modal-title').text() || '').toLowerCase();
                    if (t.indexOf('claim in') !== -1) return 'claim';
                    if (t.indexOf('scrap in') !== -1) return 'scrap';
                    if (t.indexOf('scrap sale') !== -1) return 'scrap_sale';
                    if (t.indexOf('return') !== -1) return 'return';
                    return 'sale';
                })()
            },
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
                        url: (function() {
                            const baseUrl = '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId);
                            const entryType = (typeof currentEntryType !== 'undefined' && currentEntryType) ? currentEntryType : 'sale';
                            return baseUrl + '?entry_type=' + encodeURIComponent(entryType);
                        })(),
                method: 'GET',
                success: function(response) {
                            if (typeof applySaleItemRatesFromDetailsResponse === 'function') applySaleItemRatesFromDetailsResponse(response);
                            if (typeof applySaleRetailFromResponse === 'function') applySaleRetailFromResponse(response);
                            if (typeof updateSaleModalOilQuantityRowVisibility === 'function') updateSaleModalOilQuantityRowVisibility(response);
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
                            
                            applyAddItemModalTypeAndWarrantyMileageCols(response);
                            if (typeof applyItemSearchAndMetaFromDetailsResponse === 'function') {
                                applyItemSearchAndMetaFromDetailsResponse(response, { forceItemSearch: true });
                            }
                            loadItemStockStatus(itemId, function() {
                                if (($('#selected-item-id').val() || '').toString().trim() !== String(itemId)) return;
                                try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
                            });
                            loadHistoryForItem(itemId);
                },
                error: function() {
                            loadItemStockStatus(itemId, function() {
                                if (($('#selected-item-id').val() || '').toString().trim() !== String(itemId)) return;
                                try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
                            });
                            loadHistoryForItem(itemId);
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
                // Warranty proof: add camera-decoded barcode/QR into unified serial list
                if (window.cameraScanMode === 'warranty') {
                    window.cameraScanMode = 'item';
                    try {
                        tryAddWarrantySerialRaw(String(decodedText).trim(), { fromCamera: true });
                    } catch (e) {}
                    return;
                }

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

    /** Parts / filters / breakpad: [PART] - [PRODUCT TYPE] • [QUALITY] • [BRAND]. */
    function formatSalesPartLineDisplay(pn, productType, qn, comp, titleFallback) {
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

    function salesTableRowDisplayName(item) {
        if (!item) return '';
        if (item.entry_type === 'temporary') {
            var tn = (item.temporary_item_name || item.name || '').toString().trim();
            var tq = (item.temporary_quality || '').toString().trim();
            if (tn && tq) return tn + ' • ' + tq;
            if (tn) return tn;
            var vt = (item.voice_transcript || '').toString().trim();
            if (vt) return vt;
            return 'Temporary item';
        }
        var t = (item.item_type || '').toString().toLowerCase();
        var unitRaw = (item.unit_display != null && String(item.unit_display).trim() !== '')
            ? String(item.unit_display).trim()
            : ((item.unit != null && String(item.unit).trim() !== '') ? String(item.unit).trim() : '');
        var lpcNum = (item.liter_per_can != null && item.liter_per_can !== '' && !isNaN(parseFloat(item.liter_per_can)))
            ? parseFloat(item.liter_per_can)
            : 0;
        var oilLikeByUnit = /(?:\bliter\b|\bltr\b|\bcan\b|\b20w\d{2}\b|\b\d+w\d+\b)/i.test(unitRaw);
        var isOilLike = (t === 'oil') || lpcNum > 0 || oilLikeByUnit;
        if (t === 'parts' || t === 'filters' || t === 'breakpad') {
            var typeLbl = (item.product_type_label || '').toString().trim();
            if (!typeLbl) typeLbl = (item.category_name || '').toString().trim();
            if (/^other$/i.test(typeLbl)) typeLbl = '';
            var formatted = formatSalesPartLineDisplay(
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
        if (isOilLike) {
            var oilHead = (
                item.grade_name ||
                item.grade ||
                item.category_name ||
                item.product_type_label ||
                item.product_title ||
                plainName ||
                ''
            ).toString().trim();
            var oilCompany = (item.company_name || '').toString().trim();
            var oilUnit = '';
            if (item.unit_display != null && String(item.unit_display).trim() !== '') {
                oilUnit = String(item.unit_display).trim();
            } else if (item.unit != null && String(item.unit).trim() !== '') {
                oilUnit = String(item.unit).trim();
            } else if (lpcNum > 0) {
                oilUnit = (Number.isInteger(lpcNum) ? String(lpcNum) : lpcNum.toFixed(1).replace(/\.0$/, '')) + ' LITER';
            }
            if (oilUnit) {
                var unitMatch = oilUnit.match(/(\d+(?:\.\d+)?)\s*(?:liter|ltr|l)\b/i)
                    || oilUnit.match(/\b(?:liter|ltr|l)\s*(\d+(?:\.\d+)?)/i);
                if (unitMatch && unitMatch[1]) {
                    var litersNum = parseFloat(unitMatch[1]);
                    if (!isNaN(litersNum) && litersNum > 0) {
                        oilUnit = (Number.isInteger(litersNum) ? String(litersNum) : litersNum.toFixed(1).replace(/\.0$/, '')) + ' LITER';
                    }
                } else {
                    oilUnit = oilUnit
                        .replace(/^can\s*-\s*/i, '')
                        .replace(/\bltr\b/ig, 'LITER')
                        .replace(/\bliter\b/ig, 'LITER')
                        .trim();
                }
            }
            var oilSegs = [];
            [oilHead, oilCompany, oilUnit].forEach(function(seg) {
                var s = (seg || '').toString().trim();
                if (!s) return;
                var exists = oilSegs.some(function(x) { return x.toLowerCase() === s.toLowerCase(); });
                if (!exists) oilSegs.push(s);
            });
            if (oilSegs.length) {
                return oilSegs.map(function(seg) { return String(seg).toUpperCase(); }).join(' • ');
            }
        }
        var companyName = (item.company_name || '').toString().trim();
        if (companyName && plainName && plainName.toLowerCase().indexOf(companyName.toLowerCase()) === -1) {
            return companyName + ' • ' + plainName;
        }
        return plainName;
    }

    function syncSalesSelectedItemMetaFromAnyDetailsResponse(r) {
        if (!r) return;
        var t = (r.type != null ? String(r.type) : '').toLowerCase().trim();
        $('#sales-selected-item-type').val(t);
        $('#sales-selected-part-number').val((r.part_number != null && String(r.part_number).trim() !== '') ? String(r.part_number).trim() : '');
        $('#sales-selected-quality-name').val((r.quality_name != null && String(r.quality_name).trim() !== '') ? String(r.quality_name).trim() : '');
        $('#sales-selected-company-name').val((r.company_name != null && String(r.company_name).trim() !== '') ? String(r.company_name).trim() : '');
        var cat = (r.category_name != null && String(r.category_name).trim() !== '') ? String(r.category_name).trim() : '';
        $('#sales-selected-category-name').val(cat);
        var pt = (r.product_type_label != null && String(r.product_type_label).trim() !== '') ? String(r.product_type_label).trim() : '';
        if (!pt && cat && !/^other$/i.test(cat)) pt = cat;
        $('#sales-selected-product-type-label').val(pt);
        $('#sales-selected-product-title').val((r.product_title != null && String(r.product_title).trim() !== '') ? String(r.product_title).trim() : '');
    }

    /**
     * After purchases.items.details or sales.items.details: sync line meta hiddens.
     * Parts/filters/breakpad: set #item-search to formatted part line (refined type label from API).
     * Other types: only update #item-search when opts.forceItemSearch (load-by-id, barcode, edit) — not when user picked from search (oil/battery first line must stay).
     */
    function applyItemSearchAndMetaFromDetailsResponse(r, opts) {
        opts = opts || {};
        if (!r) return;
        syncSalesSelectedItemMetaFromAnyDetailsResponse(r);
        var t = String(r.type || '').toLowerCase();
        if (t === 'parts' || t === 'filters' || t === 'breakpad') {
            var typeLab = ($('#sales-selected-product-type-label').val() || '').trim();
            var line = formatSalesPartLineDisplay(r.part_number, typeLab, r.quality_name, r.company_name, r.product_title || r.name);
            if (line) $('#item-search').val(line);
            else if (r.name) $('#item-search').val(r.name);
        } else if (opts.forceItemSearch && r.name) {
            var synFromApi = {
                item_type: r.type,
                name: r.name,
                item_id: r.id,
                part_number: r.part_number,
                product_type_label: r.product_type_label,
                quality_name: r.quality_name,
                company_name: r.company_name,
                category_name: r.category_name,
                product_title: r.product_title
            };
            var disp = (typeof salesTableRowDisplayName === 'function') ? salesTableRowDisplayName(synFromApi) : '';
            $('#item-search').val(disp || r.name);
        }
    }

    function readSalesLineMetaFromForm() {
        return {
            item_type: ($('#sales-selected-item-type').val() || '').trim(),
            part_number: ($('#sales-selected-part-number').val() || '').trim(),
            quality_name: ($('#sales-selected-quality-name').val() || '').trim(),
            company_name: ($('#sales-selected-company-name').val() || '').trim(),
            category_name: ($('#sales-selected-category-name').val() || '').trim(),
            product_type_label: ($('#sales-selected-product-type-label').val() || '').trim(),
            product_title: ($('#sales-selected-product-title').val() || '').trim()
        };
    }

    function salesLineLooksLikeBatterySequence(displayName, item) {
        if (!displayName || displayName.indexOf(' • ') === -1) return false;
        var ty = String(item.item_type || '').toLowerCase();
        if (ty === 'battery') return true;
        return /(\d+\s*V\b|CCA\b|\bPL\b|\bAH\b)/i.test(displayName);
    }

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
            $('#sales-selected-item-type, #sales-selected-part-number, #sales-selected-quality-name, #sales-selected-company-name, #sales-selected-category-name, #sales-selected-product-type-label, #sales-selected-product-title').val('');
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
            // Force entryType based on modal title (avoid stale currentEntryType).
            const modalTitle = ($('#add-item-modal-title').text() || '').toLowerCase();
            var entryTypeForSearch = 'sale';
            if (modalTitle.indexOf('claim in') !== -1) entryTypeForSearch = 'claim';
            else if (modalTitle.indexOf('scrap in') !== -1) entryTypeForSearch = 'scrap';
            else if (modalTitle.indexOf('scrap sale') !== -1) entryTypeForSearch = 'scrap_sale';
            else if (modalTitle.indexOf('return') !== -1) entryTypeForSearch = 'return';
            const forSale = (entryTypeForSearch === 'sale' || entryTypeForSearch === 'scrap_sale') ? 1 : 0;
            $.ajax({
                url: "{{ route('sales.items.ajax.search') }}",
                method: 'GET',
                data: {
                    q: query,
                    branch_id: branchId,
                    limit: 15,
                    for_sale: forSale,
                    entry_type: entryTypeForSearch
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
                            <a href="#" class="btn btn-primary btn-sm fw-bold btn-open-add-item-modal" data-create-url="{{ url(route('all.items.create.new')) }}" style="border-radius: 8px;">
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
                            } else if (result.type === 'warranty_code') {
                                // Warranty-code traceability match (sold sale item)
                                html += `
                                    <div class="p-3 border-bottom item-search-result"
                                         data-type="warranty_code"
                                         data-id="${result.sale_item_id || ''}"
                                         data-item-id="${result.item_id || ''}"
                                         data-sale-id="${result.sale_id || ''}"
                                         data-item-name="${(result.item_name || '').replace(/"/g,'&quot;')}"
                                         data-customer-name="${(result.customer_name || '').replace(/"/g,'&quot;')}"
                                         data-reference="${(result.reference || '').replace(/"/g,'&quot;')}"
                                         data-sale-date="${(result.sale_date || '').toString().replace(/"/g,'&quot;')}"
                                         data-matched-code="${(result.matched_code || '').replace(/"/g,'&quot;')}"
                                         data-branch-name="${(result.branch_name || '').replace(/"/g,'&quot;')}"
                                         data-warehouse-name="${(result.warehouse_name || '').replace(/"/g,'&quot;')}"
                                         data-has-proof="${result.has_proof ? 1 : 0}"
                                         style="cursor:pointer; background: linear-gradient(90deg, rgba(16,185,129,0.08) 0%, rgba(34,197,94,0.04) 100%);">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3 mt-1">
                                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: rgba(16,185,129,0.14);">
                                                    <i class="ti ti-badge text-success"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 me-2" style="min-width:0;">
                                                <div class="fw-bold text-dark mb-1 text-truncate">${result.item_name || 'Item'}</div>
                                                <div class="small text-success fw-600">Matched by warranty code: <span class="fw-bold">${result.matched_code || ''}</span></div>
                                                <div class="small text-muted text-truncate">Customer: ${result.customer_name || '—'} • ${result.reference || ''} • ${result.sale_date || ''}</div>
                                                <div class="small text-muted text-truncate">Branch: ${result.branch_name || '—'} • Warehouse: ${result.warehouse_name || '—'} • Proof: ${result.has_proof ? 'Attached' : 'Not attached'}</div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success-subtle text-success border" style="border-radius:999px;">Trace</span>
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
                                const stock = (result.available_quantity != null && result.available_quantity !== '') ? result.available_quantity : (result.warehouse_quantity != null && result.warehouse_quantity !== '' ? result.warehouse_quantity : (item.on_hand || 0));
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

                    var usePartsOneLineHead = (itemType === 'parts' || itemType === 'filters' || itemType === 'breakpad') && partNumber && !isDummy(partNumber);
                    
                    // Build short details array for search display (includes vehicle)
                    let searchDetails = [];
                    
                    // Common fields (short format) - exclude company for battery (it's on first line)
                    // Parts headline already includes company / category / part — avoid duplicate muted line
                    if (itemType !== 'battery' && company && !usePartsOneLineHead) searchDetails.push(company);
                    if (category && !usePartsOneLineHead) searchDetails.push(category);
                    
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
                        if (partNumber && !isDummy(partNumber) && !usePartsOneLineHead) searchDetails.push(partNumber);
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
                    
                    // Build first line HTML: parts = part - type • quality • brand; oil = Grade•Level•Company•unit + second line category; battery = firstLineParts; else product name
                    let firstLineHtml = '';
                    let firstLineText = productName;
                    if (itemType === 'battery' && firstLineParts.length > 0) {
                        firstLineText = firstLineParts.join(' • ');
                        const highlightedFirstLine = highlightText(firstLineText, query);
                        firstLineHtml = '<div class="battery-type-sequence fw-bold mb-1">' + highlightedFirstLine + '</div>';
                    } else if (usePartsOneLineHead) {
                        var catForParts = (category && !isDummy(category) && !/^other$/i.test(String(category).trim())) ? String(category).trim() : '';
                        var partsHeadLine = formatSalesPartLineDisplay(partNumber, catForParts, quality, company, productName);
                        firstLineText = partsHeadLine || productName;
                        if (partsHeadLine) {
                            firstLineHtml = '<div class="item-search-parts-headline mb-1 text-uppercase">' + highlightText(partsHeadLine, query) + '</div>';
                        } else {
                            firstLineHtml = '<div class="fw-bold text-dark mb-1 text-uppercase">' + highlightText(productName, query) + '</div>';
                        }
                        if (mileageName) {
                            firstLineHtml += '<div class="small text-muted mt-0 text-uppercase">Mileage: ' + highlightText(mileageName, query) + '</div>';
                        }
                    } else if (gradeLevelCompanyLine) {
                        firstLineText = gradeLevelCompanyLineForText || gradeLevelCompanyLine;
                        var partQualityLayoutTypes = itemType === 'oil';
                        const boldSegments = [];
                        if (grade && !isDummy(grade)) boldSegments.push(highlightText(grade, query));
                        if (level && !isDummy(level)) {
                            const safeL = String(level).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                            boldSegments.push('<span class="bg-dark text-white fw-semibold px-1 rounded">' + safeL + '</span>');
                        }
                        if (company && !isDummy(company)) {
                            var brandLine = '<span class="item-search-brand-part">' + highlightText(company, query) + '</span>';
                            if (quality && !isDummy(quality)) {
                                brandLine += ' <span class="item-search-brand-dash text-muted fw-normal"> - </span><span class="product-quality-badge">' + highlightText(quality, query) + '</span>';
                            }
                            boldSegments.push(brandLine);
                        }
                        if (itemType === 'oil' && unitForFirstLine) boldSegments.push(highlightText(unitForFirstLine, query));
                        const boldLineHtml = boldSegments.join(' <span class="text-muted">•</span> ');
                        var partAboveLinePrefix = '';
                        if (partQualityLayoutTypes && partNumber && !isDummy(partNumber)) {
                            const pnTitle = String(partNumber).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
                            partAboveLinePrefix = '<div class="item-search-part-number mb-1 text-uppercase" title="' + pnTitle + '">' + highlightText(partNumber, query) + '</div>';
                        }
                        firstLineHtml = partAboveLinePrefix + '<div class="fw-bold text-dark mb-1 text-uppercase">' + boldLineHtml + '</div>';
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
                    const qualityDataAttr = (quality && !isDummy(quality)) ? String(quality).replace(/&/g, '&amp;').replace(/"/g, '&quot;') : '';
                    var catForPartsData = (itemType === 'parts' || itemType === 'filters' || itemType === 'breakpad') && category && !isDummy(category) && !/^other$/i.test(String(category).trim()) ? String(category).trim() : '';
                    var productTypeLabelData = catForPartsData;
                    var partNumberData = (partNumber && !isDummy(partNumber)) ? String(partNumber).replace(/&/g, '&amp;').replace(/"/g, '&quot;') : '';
                    var companyDataAttr = (company && !isDummy(company)) ? String(company).replace(/&/g, '&amp;').replace(/"/g, '&quot;') : '';
                    var categoryDataAttr = (category && !isDummy(category)) ? String(category).replace(/&/g, '&amp;').replace(/"/g, '&quot;') : '';
                    var productTitleDataAttr = String(productName).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
                    
                    html += `
                        <div class="p-3 border-bottom item-search-result" 
                             data-type="item"
                             data-id="${item.id}" 
                             data-item-type="${String(itemType).replace(/"/g, '&quot;')}"
                             data-part-number="${partNumberData}"
                             data-company-name="${companyDataAttr}"
                             data-category-name="${categoryDataAttr}"
                             data-product-type-label="${String(productTypeLabelData).replace(/"/g, '&quot;')}"
                             data-product-title="${productTitleDataAttr}"
                             data-quality="${qualityDataAttr}"
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
                $('#item-edit-in-modal-btn').show();
                $('#item-search-results').hide();
                if (typeof loadItemStockStatus === 'function') loadItemStockStatus(itemId, function() {
                    if (($('#selected-item-id').val() || '').toString().trim() !== String(itemId)) return;
                    try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
                });
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
            const qualityFromSearch = ($(this).attr('data-quality') || '').trim();
            $('#sales-selected-item-type').val($(this).attr('data-item-type') || '');
            $('#sales-selected-part-number').val($(this).attr('data-part-number') || '');
            $('#sales-selected-company-name').val($(this).attr('data-company-name') || '');
            $('#sales-selected-category-name').val($(this).attr('data-category-name') || '');
            $('#sales-selected-product-type-label').val($(this).attr('data-product-type-label') || '');
            $('#sales-selected-product-title').val($(this).attr('data-product-title') || '');
            $('#sales-selected-quality-name').val(qualityFromSearch);
            
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
        
        if (qualityFromSearch) {
            $('#selected-item-quality-wrap').html('<span class="product-quality-badge">' + escapeHtml(qualityFromSearch) + '</span>').removeClass('d-none');
        } else {
            $('#selected-item-quality-wrap').html('').addClass('d-none');
        }
        
        if (line1 || line2 || line3 || qualityFromSearch) {
                $('#selected-item-details-line1').html(line1 || '&nbsp;');
            $('#selected-item-details-line2').html(line2 || '&nbsp;').toggle(!!line2);
            $('#selected-item-details-line3').html(line3 || '&nbsp;');
                $('#selected-item-details-display').removeClass('d-none');
        } else {
            $('#selected-item-details-display').addClass('d-none');
        }
        
            // Load full item details to get total_price and warehouse (use claim stock when modal is Claim In)
            var detailsEntryType = (typeof currentEntryType !== 'undefined' && currentEntryType) ? currentEntryType : 'sale';
            if ($('#add-item-modal-title').text().toLowerCase().indexOf('claim in') !== -1) detailsEntryType = 'claim';
            $.ajax({
                url: '{{ route("sales.items.details", ":id") }}'.replace(':id', itemId),
                method: 'GET',
                data: {
                    entry_type: detailsEntryType
                },
                success: function(response) {
                    var qn = (response.quality_name != null && String(response.quality_name).trim() !== '') ? String(response.quality_name).trim() : qualityFromSearch;
                    if (qn) {
                        $('#selected-item-quality-wrap').html('<span class="product-quality-badge">' + escapeHtml(qn) + '</span>').removeClass('d-none');
                    } else {
                        $('#selected-item-quality-wrap').html('').addClass('d-none');
                    }
                    if (typeof applyItemSearchAndMetaFromDetailsResponse === 'function') {
                        applyItemSearchAndMetaFromDetailsResponse(response);
                    }
                    if (typeof applySaleItemRatesFromDetailsResponse === 'function') applySaleItemRatesFromDetailsResponse(response);
                    if (typeof applySaleRetailFromResponse === 'function') applySaleRetailFromResponse(response);
                    if (typeof updateSaleModalOilQuantityRowVisibility === 'function') updateSaleModalOilQuantityRowVisibility(response);
                    
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
                    
                    // Warranty / Mileage UI based on item type
                    var isOilItem = (response.liter_per_can != null && response.liter_per_can !== '' && parseFloat(response.liter_per_can) > 0) ||
                        (response.type && String(response.type).toLowerCase() === 'oil');
                    var isBatteryItem = response.type && String(response.type).toLowerCase() === 'battery';
                    window.currentSelectedSaleItemType = response.type ? String(response.type).toLowerCase() : '';

                    if (isOilItem) {
                        // Oil: Mileage only
                        $('#add-item-modal-warranty-col').addClass('d-none');
                        $('#add-item-modal-mileage-col').removeClass('d-none');
                        var mid = response.mileage_id != null && response.mileage_id !== '' ? String(response.mileage_id) : '';
                        $('#item-mileage').val(mid);
                        updateExpandedVehicleMileage();
                    } else if (isBatteryItem) {
                        // Battery: Warranty only
                        $('#add-item-modal-warranty-col').removeClass('d-none');
                        $('#add-item-modal-mileage-col').addClass('d-none');
                        if (response.warranty_value && response.warranty_unit) {
                            $('#warranty-value').val(response.warranty_value);
                            $('#warranty-unit').val(response.warranty_unit);
                        } else {
                            $('#warranty-value').val('');
                            $('#warranty-unit').val('');
                        }
                    } else {
                        // Other types: hide both by default
                        $('#add-item-modal-warranty-col').addClass('d-none');
                        $('#add-item-modal-mileage-col').addClass('d-none');
                        $('#warranty-value').val('');
                        $('#warranty-unit').val('');
                        $('#item-mileage').val('');
                        updateExpandedVehicleMileage();
                    }
                    
                    // Load stock first; then rebuild warranty proof UI (avoids stale warehouse rows / all-qty-zero)
            loadItemStockStatus(itemId, function() {
                        if (($('#selected-item-id').val() || '').toString().trim() !== String(itemId)) return;
                        try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
                    });
            updateExpandedVehicleMileage();
            
                    // Load history (sale+purchase or claim customer sale+return)
            loadHistoryForItem(itemId);
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
                    loadItemStockStatus(itemId, function() {
                        if (($('#selected-item-id').val() || '').toString().trim() !== String(itemId)) return;
                        try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
                    });
                    updateExpandedVehicleMileage();
                    loadHistoryForItem(itemId);
                }
            });
        }
    });
    
    // Load stock status for selected item (optional callback when done, e.g. for edit restore)
    function loadItemStockStatus(itemId, onLoaded) {
        $('#stock-status-section').show();
        $('#stock-status-content').show();
        $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">Loading stock status...</p>');
        
        $.ajax({
            url: (function() {
                const baseUrl = '{{ route("sales.items.stock.status", ":id") }}'.replace(':id', itemId);
                var entryType = (typeof currentEntryType !== 'undefined' && currentEntryType) ? currentEntryType : 'sale';
                if ($('#add-item-modal-title').text().toLowerCase().indexOf('claim in') !== -1) entryType = 'claim';
                const branchId = ($('#salesBranchId').val() || '').toString();
                return baseUrl + '?entry_type=' + encodeURIComponent(entryType) + (branchId ? ('&branch_id=' + encodeURIComponent(branchId)) : '');
            })(),
            method: 'GET',
            success: function(stockData) {
                if (stockData.length === 0) {
                    $('#stock-status-list').html('<p class="text-muted mb-0 small text-center">No stock found</p>');
                    if (typeof onLoaded === 'function') onLoaded();
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
                    var isOilWarehouse = (literPerCan != null && literPerCan > 0);

                    if (isWarehouse) {
                        var lpcText = isOilWarehouse ? (Number.isInteger(literPerCan) ? literPerCan : literPerCan.toFixed(1)) : '';
                        var tag1Html = (isOilWarehouse && lpcText !== '') ? '<span class="warehouse-unit-tag me-1">' + lpcText + ' L PER CAN</span>' : '';
                        var tag2Html = (isOilWarehouse && lpcText !== '' && baseUnitLabel) ? '<span class="warehouse-unit-tag me-2">' + lpcText + ' ' + baseUnitLabel + '</span>' : '';
                        var canLabel = unitLabel || 'Can';
                        var mainQtyDisp = Number.isInteger(qty) ? qty : qty.toFixed(2);
                        var stockLabel = mainQtyDisp + ' ' + canLabel;
                        var dataAttrs = ' data-warehouse-id="' + stock.id + '" data-branch-id="' + (stock.branch_id || '') + '" data-display="' + (stock.display || '').replace(/"/g, '&quot;') + '" data-quantity="' + qty + '" data-unit="' + (unitLabel || '').replace(/"/g, '&quot;') + '" data-base-unit="' + (stock.base_unit || '').replace(/"/g, '&quot;') + '" data-base-unit-multiplier="' + (multVal || '') + '" data-qty-text="' + (qtyText || '').replace(/"/g, '&quot;') + '" data-cartons="' + cartons + '" data-loose-liters="' + openLiters + '" data-liter-per-can="' + (literPerCan != null ? literPerCan : '') + '"';
                        var qtySelectOpts = '<option value="0" selected>0</option>';
                        // Incoming modes (Claim In / Scrap In): allow receiving regardless of current on-hand.
                        // Outgoing modes (Sale/Return/Scrap Sale): cap dropdown by available stock to prevent negative stock.
                        var isIncomingReceive = (typeof currentEntryType !== 'undefined' && (currentEntryType === 'claim' || currentEntryType === 'scrap'));
                        var maxCans = isIncomingReceive ? 1000 : Math.max(0, Math.floor(qty));
                        var maxUiCans = Math.min(100, maxCans);
                        for (var n = 1; n <= maxUiCans; n++) {
                            qtySelectOpts += '<option value="' + n + '">' + n + '</option>';
                        }

                        // For oil items, show Liter + ML controls; for batteries / non-oil, ONLY piece qty select.
                        var baseAndMlInputs = '';
                        if (isOilWarehouse) {
                            baseAndMlInputs =
                                '<input type="number" min="0" step="0.01" class="form-control form-control-sm stock-warehouse-base-qty-input" placeholder="Liter" value="" data-warehouse-id="' + stock.id + '" onclick="event.stopPropagation();" data-multiplier="' + multVal + '">' +
                                '<input type="number" min="0" step="1" class="form-control form-control-sm stock-warehouse-extra-input" placeholder="ML" value="" data-warehouse-id="' + stock.id + '" onclick="event.stopPropagation();">';
                        }

                        return '<div class="d-flex flex-wrap align-items-center gap-2 py-2 mb-1 ' + rowClass + '" ' + dataAttrs + ' style="cursor: pointer; transition: all 0.2s; ' + (isSelected ? '' : 'background-color: #f0f0f0;') + '">' +
                            '<span class="me-2">' + (isSelected ? '✓' : '') + '</span>' +
                            '<span class="' + (isSelected ? 'text-white' : '') + '">' + (stock.display || 'Display') + '</span>' +
                            tag1Html +
                            tag2Html +
                            '<span class="' + (isSelected ? 'text-white' : 'text-dark') + ' me-2" style="font-size: 0.9rem;">' + stockLabel + '</span>' +
                            '<select class="form-control form-control-sm stock-warehouse-qty-input" data-warehouse-id="' + stock.id + '" onclick="event.stopPropagation();" data-unit="' + (unitLabel || 'Piece').replace(/"/g, '&quot;') + '">' + qtySelectOpts + '</select>' +
                            baseAndMlInputs +
                        '</div>';
                    }
                    return '<div class="p-2 mb-1 border-bottom stock-branch-item" style="background-color: #fff;"><div class="d-flex justify-content-between align-items-center"><div class="fw-bold">' + (stock.display || '') + '</div></div></div>';
                }
                
                // In claim mode, add pending claim quantities from current cart so stock list shows DB + session claim
                var isClaimMode = (typeof currentEntryType !== 'undefined' && currentEntryType === 'claim');
                var currentItemId = (typeof itemId !== 'undefined') ? itemId : null;
                function pendingClaimQtyForWarehouse(whId) {
                    if (!isClaimMode || !currentItemId || typeof salesItems === 'undefined') return 0;
                    var sum = 0;
                    for (var i = 0; i < salesItems.length; i++) {
                        var it = salesItems[i];
                        if (it.entry_type === 'claim' && it.item_id == currentItemId && String(it.warehouse_id) === String(whId)) {
                            sum += parseFloat(it.quantity) || 0;
                        }
                    }
                    return sum;
                }
                var branchTotalPending = 0;
                if (isClaimMode && currentItemId) {
                    stockData.forEach(function(stock) {
                        if (stock.type === 'warehouse') branchTotalPending += pendingClaimQtyForWarehouse(stock.id);
                    });
                }
                let html = '';
                stockData.forEach(function(stock) {
                    if (stock.type === 'branch') {
                        var branchStock = stock;
                        if (branchTotalPending > 0) {
                            branchStock = Object.assign({}, stock, { quantity: (parseFloat(stock.quantity) || 0) + branchTotalPending });
                        }
                        html += formatStockRow(branchStock, false);
                    } else if (stock.type === 'warehouse') {
                        var pending = pendingClaimQtyForWarehouse(stock.id);
                        var whStock = stock;
                        if (pending > 0) {
                            whStock = Object.assign({}, stock, { quantity: (parseFloat(stock.quantity) || 0) + pending });
                        }
                        html += formatStockRow(whStock, true);
                    }
                });
                
                $('#stock-status-list').html(html);
                // Auto-select first warehouse row with stock: set qty=1, highlight row, and set selected-warehouse-id
                $('#stock-status-list .stock-warehouse-item').each(function() {
                    var $row = $(this);
                    var $sel = $row.find('select.stock-warehouse-qty-input');
                    if ($sel.length && $sel.find('option[value="1"]').length) {
                        $sel.val('1');
                        $('#selected-warehouse-id').val($row.data('warehouse-id') || '');
                        $row.addClass('bg-primary text-white');
                        $row.find('span').first().text('✓');
                        return false; // only first warehouse with stock
                    }
                });
                if (typeof onLoaded === 'function') onLoaded();
            },
            error: function() {
                $('#stock-status-list').html('<p class="text-danger mb-0 small text-center">Error loading stock status</p>');
                if (typeof onLoaded === 'function') onLoaded();
            }
        });
    }

    // Helper: parse mileage interval in KM from selected mileage option
    function parseSelectedMileageIntervalKm() {
        const $val = $('#item-mileage');
        if (!$val.length) return null;
        const optText = ($val.find('option:selected').text() || '').toString();
        let txt = optText.trim();
        if (!txt) return null;
        // Try to extract a number followed by KM / kilometers
        let m = txt.match(/(\d+(\.\d+)?)\s*(km|kilometer|kilometre|kms?)/i);
        if (m) {
            const num = parseFloat(m[1]);
            if (!isNaN(num) && num > 0) return num;
        }
        if (/^[\d,\.\s]+$/.test(txt) && /\d/.test(txt)) {
            const num = parseFloat(txt.replace(/,/g, '').replace(/\s/g, ''));
            if (!isNaN(num) && num > 0) return num;
        }
        // Fallback: try numeric value of the select itself
        const rawVal = ($val.val() || '').toString().trim();
        const asNum = parseFloat(rawVal);
        if (!isNaN(asNum) && asNum > 0) return asNum;
        return null;
    }

    function getVehicleMileageKmForCard($card) {
        var km = parseSelectedMileageIntervalKm();
        if (km != null && km > 0) return km;
        if (typeof salesItems !== 'undefined' && salesItems.length) {
            var first = salesItems.filter(function(i) { return (i.mileage_id != null && i.mileage_id !== '') || (i.mileage_name != null && i.mileage_name !== ''); })[0];
            if (first) {
                var txt = (first.mileage_name || String(first.mileage_id || '')).toString().trim();
                var n = parseFloat(txt.replace(/[^\d.]/g, '')) || 0;
                if (n > 0) return n;
            }
        }
        if ($card && $card.length) {
            var displayText = ($card.find('.vehicle-selected-mileage-value').text() || '').toString().trim();
            var num = parseFloat(displayText.replace(/[^\d.]/g, '')) || 0;
            if (num > 0) return num;
        }
        return null;
    }

    function refreshAllVehicleOilPlans() {
        var $m = getPrimaryVehicleMetricsRoot();
        if (!$m.length) return;
        updateExpandedVehicleOilPlan($m);
        updateVehicleTargetStatus($m);
    }

    // Only update Next KM from Current KM + Mileage (metrics block; header preview on list card)
    function updateNextKmOnly($metricsOpt) {
        var $m = ($metricsOpt && $metricsOpt.length) ? $metricsOpt : getPrimaryVehicleMetricsRoot();
        if (!$m.length) return;
        var $listCard = vehicleListCardForMetrics($m);
        if (!$listCard.length) $listCard = getPrimaryVehicleCardForSale();
        var mileageKm = getVehicleMileageKmForCard($listCard.length ? $listCard : getPrimaryVehicleCardForSale());
        var $currentInput = $m.find('.vehicle-current-km-input');
        var $nextKmEl = $m.find('.vehicle-next-km-output');
        if (!$nextKmEl.length || !$currentInput.length) return;
        var rawCurrent = ($currentInput.val() || '').toString().replace(/,/g, '').trim();
        if (rawCurrent === '') {
            $nextKmEl.text('');
            if ($listCard.length) updateVehicleCardHeaderOilTargetPreview($listCard);
            return;
        }
        var currentKm = parseFloat(rawCurrent);
        if (isNaN(currentKm) || mileageKm == null || mileageKm <= 0) {
            $nextKmEl.text('--');
            if ($listCard.length) updateVehicleCardHeaderOilTargetPreview($listCard);
            return;
        }
        var nextKm = currentKm + mileageKm;
        try {
            $nextKmEl.text(nextKm.toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' KM');
        } catch (e) {
            $nextKmEl.text(Math.round(nextKm) + ' KM');
        }
        if ($listCard.length) updateVehicleCardHeaderOilTargetPreview($listCard);
    }

    function extractNumericKmFromNextOutputText(text) {
        var t = (text != null ? String(text) : '').replace(/,/g, '').replace(/\s*KM\s*/gi, '').trim();
        if (!t || /^[\s\-–—]+$/i.test(t)) return '';
        var digits = t.replace(/[^\d.]/g, '');
        if (digits === '' || isNaN(parseFloat(digits))) return '';
        return String(Math.round(parseFloat(digits)));
    }

    /** Live header + vehicles[]: show calculated Next KM; if empty, fall back to DB-saved target on card */
    function updateVehicleCardHeaderOilTargetPreview($card) {
        if (!$card || !$card.length) return;
        var vid = $card.data('vehicle-id');
        var km = extractNumericKmFromNextOutputText($card.find('.vehicle-next-km-output').text());
        var $hdr = $card.find('.vehicle-last-oil-target-value');
        if (!$hdr.length) return;
        if (km !== '') {
            $hdr.text(Math.round(parseFloat(km)).toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' KM');
            $card.attr('data-previous-target-km', km);
            if (typeof vehicles !== 'undefined' && vehicles && vehicles.length) {
                var v = vehicles.find(function(ve) { return String(ve.id) === String(vid); });
                if (v) {
                    v.next_km = km;
                    v.previous_target_next_km = km;
                }
            }
            return;
        }
        var saved = ($card.attr('data-db-saved-target-km') || '').toString().replace(/,/g, '').trim();
        var n = parseFloat(saved);
        if (saved !== '' && isFinite(n) && n >= 0) {
            $hdr.text(Math.round(n).toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' KM');
            $card.attr('data-previous-target-km', saved);
        } else {
            $hdr.text('—');
            $card.attr('data-previous-target-km', '');
        }
    }

    function getVehicleMileageKm() {
        return getVehicleMileageKmForCard(getPrimaryVehicleCardForSale());
    }

    // Show selected item mileage on vehicle cards (header) and in expanded card OIL MILEAGE box
    function updateExpandedVehicleMileage() {
        const $val = $('#item-mileage');
        if (!$val.length) return;
        const mileageText = ($val.find('option:selected').text() || '').trim();
        const mileageId = ($val.val() || '').toString().trim();
        const textToShow = mileageText || mileageId || '';

        // Update mileage in header of every vehicle card (so it shows on select/collapsed view too)
        $('.vehicle-card').each(function() {
            const $box = $(this).find('.vehicle-selected-mileage');
            if (!textToShow) {
                $box.hide().find('.vehicle-selected-mileage-value').text('');
            } else {
                $box.show().find('.vehicle-selected-mileage-value').text(textToShow);
            }
        });

        const $mroot = getPrimaryVehicleMetricsRoot();
        if ($mroot.length && textToShow && $mroot.find('.vehicle-oil-capacity-input').length) {
            $mroot.find('.vehicle-oil-capacity-input').val(textToShow);
        }
        refreshAllVehicleOilPlans();
    }

    // True if cart has at least one oil-type item (item with mileage – oil items use mileage)
    function hasOilItemInCart() {
        if (typeof salesItems === 'undefined' || !salesItems.length) return false;
        return salesItems.some(function(i) {
            return (i.mileage_id != null && i.mileage_id !== '') || (i.mileage_name != null && i.mileage_name !== '');
        });
    }

    // Enable or disable vehicle metrics inputs based on whether an oil item is in the cart
    function updateVehicleInputsPermission() {
        var allowed = hasOilItemInCart();
        var $section = $('#vehicle-display-section');
        var $notice = $section.find('.vehicle-metrics-oil-required-notice');
        var $metrics = $section.find('.vehicle-metrics');
        if ($notice.length) {
            if (allowed) {
                $notice.addClass('d-none');
                $metrics.removeClass('vehicle-metrics-disabled');
            } else {
                $notice.removeClass('d-none');
                $metrics.addClass('vehicle-metrics-disabled');
            }
        }
        $section.find('.vehicle-current-km-input, .vehicle-daily-run-km-input, .vehicle-next-date-input, .vehicle-oil-capacity-input, .vehicle-interval-days-input, .vehicle-interval-months-input').prop('readonly', !allowed);
        $section.find('.vehicle-reset-daily-run-btn').prop('disabled', !allowed);
    }

    // Sync vehicle card mileage from first sale item that has mileage (so items list drives vehicle display)
    function syncVehicleMileageFromFirstSaleItem() {
        if (typeof salesItems === 'undefined' || !salesItems.length) {
            $('.vehicle-card').each(function() {
                $(this).find('.vehicle-selected-mileage').hide().find('.vehicle-selected-mileage-value').text('');
            });
            getPrimaryVehicleMetricsRoot().find('.vehicle-oil-capacity-input').val('');
            return;
        }
        var first = salesItems.filter(function(i) { return (i.mileage_id != null && i.mileage_id !== '') || (i.mileage_name != null && i.mileage_name !== ''); })[0];
        var textToShow = (first && (first.mileage_name || first.mileage_id)) ? (first.mileage_name || String(first.mileage_id)) : '';
        $('.vehicle-card').each(function() {
            var $box = $(this).find('.vehicle-selected-mileage');
            if (!textToShow) {
                $box.hide().find('.vehicle-selected-mileage-value').text('');
            } else {
                $box.show().find('.vehicle-selected-mileage-value').text(textToShow);
            }
        });
        var $mroot = getPrimaryVehicleMetricsRoot();
        if ($mroot.length && $mroot.find('.vehicle-oil-capacity-input').length) {
            $mroot.find('.vehicle-oil-capacity-input').val(textToShow || '');
        }
        refreshAllVehicleOilPlans();
    }

    // Format interval days for display: "70 Days" or "1 Day"
    function formatIntervalDaysText(daysNum) {
        var n = parseFloat(daysNum);
        if (isNaN(n) || n < 0) return '--';
        if (n === 0) return '--';
        var d = Math.round(n);
        return d === 1 ? '1 Day' : (d + ' Days');
    }
    // Format interval months for display: "2 Months 10 Days" or "1 Month" or "15 Days" (no decimal months)
    function formatIntervalMonthsText(monthsNum) {
        var m = parseFloat(monthsNum);
        if (isNaN(m) || m < 0) return '--';
        var whole = Math.floor(m);
        var frac = m - whole;
        var remainingDays = Math.round(frac * 30);
        if (remainingDays >= 30) { remainingDays -= 30; whole += 1; }
        var parts = [];
        if (whole > 0) parts.push(whole === 1 ? '1 Month' : (whole + ' Months'));
        if (remainingDays > 0) parts.push(remainingDays === 1 ? '1 Day' : (remainingDays + ' Days'));
        return parts.length ? parts.join(' ') : '--';
    }
    // Update the human-readable Interval display spans from the numeric inputs (call after setting interval values)
    function updateIntervalDisplay($card) {
        var $c = ($card && $card.length) ? $card : getPrimaryVehicleMetricsRoot();
        if (!$c.length) return;
        var daysVal = $c.find('.vehicle-interval-days-input').val();
        var monthsVal = $c.find('.vehicle-interval-months-input').val();
        var daysNum = parseFloat(daysVal != null ? String(daysVal) : '');
        var monthsNum = parseFloat(monthsVal != null ? String(monthsVal) : '');
        var $daysDisp = $c.find('.vehicle-interval-days-display');
        var $monthsDisp = $c.find('.vehicle-interval-months-display');
        if ($daysDisp.length) $daysDisp.text(formatIntervalDaysText(daysNum));
        if ($monthsDisp.length) $monthsDisp.text(formatIntervalMonthsText(monthsNum));
    }

    // Oil math on the .vehicle-metrics block (mounted under grid for selected vehicle)
    function updateExpandedVehicleOilPlan($cardOpt) {
        const $m = ($cardOpt && $cardOpt.length) ? $cardOpt : getPrimaryVehicleMetricsRoot();
        if (!$m.length) return;
        var $listCard = vehicleListCardForMetrics($m);
        if (!$listCard.length) $listCard = getPrimaryVehicleCardForSale();
        const $mileageCard = $listCard.length ? $listCard : getPrimaryVehicleCardForSale();

        const mileageKm = getVehicleMileageKmForCard($mileageCard);
        const $currentInput = $m.find('.vehicle-current-km-input');
        const $dailyInput = $m.find('.vehicle-daily-run-km-input');
        const $daysInput = $m.find('.vehicle-interval-days-input');
        const $monthsInput = $m.find('.vehicle-interval-months-input');
        const $nextKmEl = $m.find('.vehicle-next-km-output');
        const $nextDateInput = $m.find('.vehicle-next-date-input');

        if ($nextKmEl.length) $nextKmEl.text('--');
        if ($nextDateInput.length) $nextDateInput.val('');

        const rawCurrent = ($currentInput.val() || '').toString().replace(/,/g, '').trim();
        const currentKm = rawCurrent === '' ? NaN : parseFloat(rawCurrent);
        if ($nextKmEl.length) {
            if (rawCurrent === '' || !rawCurrent) {
                $nextKmEl.text('');
            } else if (mileageKm != null && mileageKm > 0 && !isNaN(currentKm) && currentKm >= 0) {
                const nextKm = currentKm + mileageKm;
                try {
                    $nextKmEl.text(nextKm.toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' KM');
                } catch (e) {
                    $nextKmEl.text(Math.round(nextKm) + ' KM');
                }
            } else {
                $nextKmEl.text('--');
            }
        }

        var $hdrCard = $listCard.length ? $listCard : getPrimaryVehicleCardForSale();
        if ($hdrCard.length) updateVehicleCardHeaderOilTargetPreview($hdrCard);

        if (!mileageKm) return;

        var days = $daysInput.length ? (parseFloat($daysInput.val()) || 0) : 0;
        var months = $monthsInput.length ? (parseFloat($monthsInput.val()) || 0) : 0;
        var dailyKm = $dailyInput.length ? (parseFloat(($dailyInput.val() || '').toString().replace(/,/g, '')) || 0) : 0;

        var totalDays = 0;
        if (dailyKm > 0) {
            totalDays = mileageKm / dailyKm;
            var calcMonths = totalDays / 30;
            if ($daysInput.length) $daysInput.val(Math.round(totalDays));
            if ($monthsInput.length) $monthsInput.val(Math.round(calcMonths));
            updateIntervalDisplay($m);
            if (totalDays > 0 && $nextDateInput.length) {
                var now = new Date();
                now.setDate(now.getDate() + Math.ceil(totalDays));
                $nextDateInput.val(now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0'));
            }
            return;
        }
        if (dailyKm <= 0 || !$dailyInput.val() || ($dailyInput.val() || '').toString().trim() === '') {
            if ($daysInput.length) $daysInput.val('');
            if ($monthsInput.length) $monthsInput.val('');
            updateIntervalDisplay($m);
            if ($nextDateInput.length) $nextDateInput.val('');
            return;
        }
        totalDays = days + (months * 30);
        if (totalDays > 0 && $dailyInput.length) {
            var calcDaily = mileageKm / totalDays;
            $dailyInput.val(Math.round(calcDaily));
        }
        if (totalDays > 0 && $nextDateInput.length) {
            var now = new Date();
            now.setDate(now.getDate() + Math.ceil(totalDays));
            $nextDateInput.val(now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0'));
        }
    }

    // Compare current KM to previous target Next KM: TIMELY / DUE NOW / LATE ($metrics = .vehicle-metrics block)
    function updateVehicleTargetStatus($metrics) {
        var $m = ($metrics && $metrics.length) ? $metrics : getPrimaryVehicleMetricsRoot();
        if (!$m.length) return;
        var $listCard = vehicleListCardForMetrics($m);
        if (!$listCard.length) $listCard = getPrimaryVehicleCardForSale();
        if (!$listCard.length) return;
        var $banner = $m.find('.vehicle-oil-target-banner');
        if (!$banner.length) return;
        var targetStr = ($listCard.attr('data-db-saved-target-km') || '').toString().replace(/,/g, '').trim();
        var targetKm = targetStr === '' ? NaN : parseFloat(targetStr);
        if (isNaN(targetKm) || targetKm < 0) {
            $banner.addClass('d-none');
            return;
        }
        var currentStr = ($m.find('.vehicle-current-km-input').val() || '').toString().replace(/,/g, '').trim();
        var currentKm = currentStr === '' ? NaN : parseFloat(currentStr);
        var targetFormatted = (Math.round(targetKm).toLocaleString() || targetKm);
        $banner.find('.vehicle-previous-target-km').text(targetFormatted);
        $banner.removeClass('d-none');
        if (currentStr === '' || isNaN(currentKm)) {
            $banner.find('.vehicle-current-reading-km').text('--');
            $banner.find('.vehicle-target-status-badge').removeClass('badge-success badge-info badge-warning badge-danger').addClass('badge-secondary').text('Enter current KM to see status');
            $banner.find('.vehicle-target-status-text').text('');
            $banner.css({ 'background': '#f8f9fa', 'borderColor': '#e0e0e0' });
            return;
        }
        var currentFormatted = (Math.round(currentKm).toLocaleString() || currentKm);
        $banner.find('.vehicle-current-reading-km').text(currentFormatted);
        var $badge = $banner.find('.vehicle-target-status-badge');
        var $text = $banner.find('.vehicle-target-status-text');
        $badge.removeClass('badge-success badge-info badge-warning badge-danger badge-secondary');
        var diff = currentKm - targetKm;
        var tolerance = 1;
        if (diff < -tolerance) {
            $badge.addClass('badge-success').text('TIMELY');
            $text.text('Oil change before target – ' + (Math.round(-diff).toLocaleString()) + ' KM remaining');
            $banner.css({ 'background': '#f0fdf4', 'borderColor': '#16a34a' });
        } else if (diff <= tolerance && diff >= -tolerance) {
            $badge.addClass('badge-info').text('DUE NOW');
            $text.text('Oil change exactly on target KM');
            $banner.css({ 'background': '#eff6ff', 'borderColor': '#2563eb' });
        } else {
            $badge.addClass('badge-danger').text('LATE');
            $text.text('Oil change overdue by ' + (Math.round(diff).toLocaleString()) + ' KM');
            $banner.css({ 'background': '#fef2f2', 'borderColor': '#dc2626' });
        }
    }

    // When Current KM is entered: detect early completion / high usage, compute actual daily average, recalculate Next Date and Interval (D/M)
    function updateVehicleReminderFromCurrentKm($metrics) {
        var $m = ($metrics && $metrics.length) ? $metrics : getPrimaryVehicleMetricsRoot();
        if (!$m.length) return;
        var $listCard = vehicleListCardForMetrics($m);
        if (!$listCard.length) $listCard = getPrimaryVehicleCardForSale();
        var mileageKm = getVehicleMileageKmForCard($listCard.length ? $listCard : getPrimaryVehicleCardForSale());
        if (mileageKm == null || mileageKm <= 0) return;
        var currentStr = ($m.find('.vehicle-current-km-input').val() || '').toString().replace(/,/g, '').trim();
        var currentKm = currentStr === '' ? NaN : parseFloat(currentStr);
        if (isNaN(currentKm) || currentKm < 0) return;

        var targetStr = ($listCard.attr('data-db-saved-target-km') || '').toString().replace(/,/g, '').trim();
        var previousTargetKm = targetStr === '' ? NaN : parseFloat(targetStr);
        var savedDailyStr = ($listCard.attr('data-saved-daily-run-km') || '').toString().replace(/,/g, '').trim();
        var savedDailyRun = savedDailyStr === '' ? NaN : parseFloat(savedDailyStr);
        var prevCurrentStr = ($listCard.attr('data-previous-current-km') || '').toString().replace(/,/g, '').trim();
        var previousCurrentKm = prevCurrentStr === '' ? NaN : parseFloat(prevCurrentStr);
        var previousNextDateStr = ($listCard.attr('data-previous-next-date') || '').toString().trim().substring(0, 10);
        var lastVisitDateStr = ($listCard.attr('data-last-visit-date') || '').toString().trim().substring(0, 10);

        var defaultDays = 90;
        var defaultDailyRun = mileageKm / defaultDays;
        var defaultDailyRounded = Math.round(defaultDailyRun);

        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var currentDateStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');

        var daysPassed = 0;
        if (lastVisitDateStr && lastVisitDateStr.length >= 10) {
            var parts = lastVisitDateStr.split('-');
            var lastVisit = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            lastVisit.setHours(0, 0, 0, 0);
            daysPassed = Math.max(0, Math.ceil((today.getTime() - lastVisit.getTime()) / (24 * 60 * 60 * 1000)));
        }

        var previousNextDate = null;
        if (previousNextDateStr && previousNextDateStr.length >= 10) {
            var p = previousNextDateStr.split('-');
            previousNextDate = new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
            previousNextDate.setHours(0, 0, 0, 0);
        }

        var reachedTarget = !isNaN(previousTargetKm) && currentKm >= previousTargetKm - 1;
        var isEarlyCompletion = false;
        var actualDailyRunKm = NaN;
        var highUsageNotice = '';

        if (reachedTarget && daysPassed >= 1 && !isNaN(previousCurrentKm) && currentKm >= previousCurrentKm) {
            actualDailyRunKm = (currentKm - previousCurrentKm) / daysPassed;
            if (actualDailyRunKm > 0 && isFinite(actualDailyRunKm)) {
                if (previousNextDate && today.getTime() < previousNextDate.getTime()) {
                    isEarlyCompletion = true;
                    var actualRounded = Math.round(actualDailyRunKm);
                    var prevPlanned = (savedDailyRun != null && !isNaN(savedDailyRun) && savedDailyRun > 0) ? Math.round(savedDailyRun) : defaultDailyRounded;
                    highUsageNotice = 'HIGH USAGE – vehicle reached target early. Actual average: ' + (actualRounded.toLocaleString()) + ' KM/day. Previous planned: ' + (prevPlanned.toLocaleString()) + ' KM/day.';
                }
            }
        }

        var dailyRunKm;
        if (isEarlyCompletion && actualDailyRunKm > (savedDailyRun || 0)) {
            dailyRunKm = Math.round(actualDailyRunKm);
        } else if (reachedTarget && !isNaN(actualDailyRunKm) && actualDailyRunKm > 0 && isFinite(actualDailyRunKm)) {
            dailyRunKm = Math.round(actualDailyRunKm);
        } else {
            var diff = isNaN(previousTargetKm) ? 0 : (currentKm - previousTargetKm);
            var tolerance = 1;
            var isLate = diff > tolerance;
            if (isLate) {
                dailyRunKm = defaultDailyRounded;
            } else {
                dailyRunKm = (savedDailyRun != null && !isNaN(savedDailyRun) && savedDailyRun > 0)
                    ? savedDailyRun
                    : defaultDailyRounded;
            }
        }

        var totalDays = mileageKm / dailyRunKm;
        if (totalDays <= 0 || !isFinite(totalDays)) return;

        var $dailyInput = $m.find('.vehicle-daily-run-km-input');
        var $daysInput = $m.find('.vehicle-interval-days-input');
        var $monthsInput = $m.find('.vehicle-interval-months-input');
        var $nextDateInput = $m.find('.vehicle-next-date-input');
        var $highUsageNotice = $m.find('.vehicle-high-usage-notice');
        if (!$dailyInput.length) return;

        $dailyInput.val(Math.round(dailyRunKm));
        if ($daysInput.length) $daysInput.val(Math.round(totalDays));
        if ($monthsInput.length) $monthsInput.val(Math.round(totalDays / 30));
        updateIntervalDisplay($m);
        if ($nextDateInput.length) {
            var now = new Date();
            now.setDate(now.getDate() + Math.ceil(totalDays));
            $nextDateInput.val(now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0'));
        }

        if (highUsageNotice && $highUsageNotice.length) {
            $highUsageNotice.removeClass('d-none').text(highUsageNotice);
        } else {
            $highUsageNotice.addClass('d-none').text('');
        }

        var vehicleId = $m.data('vehicle-id');
        var v = vehicles.find(function(ve) { return String(ve.id) === String(vehicleId); });
        if (v) {
            v.daily_run_km = $dailyInput.val() || '';
            v.interval_days = $daysInput.val() || '';
            v.interval_months = $monthsInput.val() || '';
            v.next_date = $nextDateInput.val() || '';
        }
    }

    // When user manually changes Next Date: recalculate Daily Run KM and Interval (D/M) from remaining days until that date
    function updateFromNextDate($metrics) {
        var $m = ($metrics && $metrics.length) ? $metrics : getPrimaryVehicleMetricsRoot();
        if (!$m.length) return;
        var $listCard = vehicleListCardForMetrics($m);
        if (!$listCard.length) $listCard = getPrimaryVehicleCardForSale();
        var mileageKm = getVehicleMileageKmForCard($listCard.length ? $listCard : getPrimaryVehicleCardForSale());
        if (!mileageKm || mileageKm <= 0) return;
        var $nextDateInput = $m.find('.vehicle-next-date-input');
        var $dailyInput = $m.find('.vehicle-daily-run-km-input');
        var $daysInput = $m.find('.vehicle-interval-days-input');
        var $monthsInput = $m.find('.vehicle-interval-months-input');
        if (!$nextDateInput.length || !$dailyInput.length) return;
        var dateStr = ($nextDateInput.val() || '').toString().trim();
        if (!dateStr || dateStr.length < 10) return;
        var parts = dateStr.split('-');
        if (parts.length < 3) return;
        var nextDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        nextDate.setHours(0, 0, 0, 0);
        var diffMs = nextDate.getTime() - today.getTime();
        var remainingDays = Math.ceil(diffMs / (24 * 60 * 60 * 1000));
        if (remainingDays <= 0) {
            $dailyInput.val('');
            if ($daysInput.length) $daysInput.val('');
            if ($monthsInput.length) $monthsInput.val('');
            updateIntervalDisplay($m);
            return;
        }
        var dailyRunKm = mileageKm / remainingDays;
        $dailyInput.val(Math.round(dailyRunKm));
        var calcMonths = remainingDays / 30;
        if ($daysInput.length) $daysInput.val(Math.round(remainingDays));
        if ($monthsInput.length) $monthsInput.val(Math.round(calcMonths));
        updateIntervalDisplay($m);
    }
    
    // Select warehouse from stock status: never reset row quantity when clicking a row (keep Display/Green Sheet quantities when switching back)
    $(document).on('click', '.stock-warehouse-item', function(e) {
        if ($(e.target).closest('.stock-warehouse-qty-input, .stock-warehouse-base-qty-input, .stock-warehouse-extra-input').length) return;
        var $clicked = $(this);
        $('.stock-warehouse-item').removeClass('bg-primary text-white');
        $('.stock-warehouse-item').each(function() { $(this).find('span').first().text(''); });
        $clicked.addClass('bg-primary text-white');
        $clicked.find('span').first().html('✓');
        const warehouseId = $clicked.data('warehouse-id');
        const warehouseDisplay = $clicked.data('display') || $clicked.find('span').eq(1).text() || 'Warehouse';
        $('#selected-warehouse-id').val(warehouseId);
        $('#item-search-warehouse').text(warehouseDisplay).removeClass('d-none');
        $('body').data('currentWarehouseName', warehouseDisplay);
        // Always sync #item-quantity from clicked row's current values (do not reset row inputs so quantity stays when switching back)
        var cans = parseInt($clicked.find('.stock-warehouse-qty-input').val(), 10) || 0;
        var baseL = parseFloat($clicked.find('.stock-warehouse-base-qty-input').val()) || 0;
        var lpc = parseFloat($clicked.data('liter-per-can')) || parseFloat($('#item-liter-per-can').val()) || 0;
        var q = lpc > 0 ? cans + (baseL / lpc) : (cans || baseL);
        $('#item-quantity').val(q > 0 ? q : '');
        $('#item-quantity-cans').val(cans);
        $('#item-quantity-liters').val(baseL);
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
        // Prevent dropdown clipping/position glitches inside scroll containers.
        // (Native <select> can be affected by overflow in some browser+CSS combos.)
        $('#stock-status-content').css('overflow-y', 'visible');
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
        // Row highlight: when any quantity > 0, show blue highlight (same as selected row)
        if (qty > 0) {
            $row.addClass('row-active');
        } else {
            $row.removeClass('row-active');
        }
        // Make the row the active (selected) one so UI + bound field always sync.
        $('.stock-warehouse-item').removeClass('bg-primary text-white');
        $row.addClass('bg-primary text-white');
        $row.find('span').first().html('✓');
        $('#selected-warehouse-id').val($row.data('warehouse-id') || '');
        var whDisp = $row.data('display') || $row.find('span').eq(1).text() || '';
        if (whDisp) {
            $('#item-search-warehouse').text(whDisp).removeClass('d-none');
            $('body').data('currentWarehouseName', whDisp);
        }
        $('#item-quantity').val(qty > 0 ? qty : '');
        $('#item-quantity-cans').val(cans);
        $('#item-quantity-liters').val(looseLiters);
        if (typeof updateOilQuantityFromInputs === 'function') updateOilQuantityFromInputs();

        // Restore scroll behavior after user picks a value.
        $('#stock-status-content').css('overflow-y', 'auto');
    });

    // Restore scroll behavior when focus leaves the dropdown.
    $(document).on('blur', '.stock-warehouse-qty-input', function() {
        $('#stock-status-content').css('overflow-y', 'auto');
    });
    
    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#item-search, #item-search-results').length) {
            $('#item-search-results').hide();
        }
    });

    // Item search (old method - kept for backward compatibility if needed)
    // Now using YouTube-style search modal above

        // Load item details (Edit flow / legacy search)
    function loadItemDetails(itemId) {
        $.ajax({
            url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            success: function(response) {
                $('#selected-item-id').val(response.id);
                $('#item-edit-in-modal-btn').show();
                if (typeof applyItemSearchAndMetaFromDetailsResponse === 'function') {
                    applyItemSearchAndMetaFromDetailsResponse(response, { forceItemSearch: true });
                } else {
                    $('#item-search').val(response.name);
                }
                
                if (typeof applySaleItemRatesFromDetailsResponse === 'function') applySaleItemRatesFromDetailsResponse(response);
                if (typeof applySaleRetailFromResponse === 'function') applySaleRetailFromResponse(response);
                if (typeof updateSaleModalOilQuantityRowVisibility === 'function') updateSaleModalOilQuantityRowVisibility(response);
                
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
                
                // Warranty / Mileage UI based on item type
                var isOil = (response.liter_per_can != null && response.liter_per_can !== '' && parseFloat(response.liter_per_can) > 0) ||
                    (response.type && String(response.type).toLowerCase() === 'oil');
                var isBattery = response.type && String(response.type).toLowerCase() === 'battery';
                window.currentSelectedSaleItemType = response.type ? String(response.type).toLowerCase() : '';

                if (isOil) {
                    $('#add-item-modal-warranty-col').addClass('d-none');
                    $('#add-item-modal-mileage-col').removeClass('d-none');
                    var mid = response.mileage_id != null && response.mileage_id !== '' ? String(response.mileage_id) : '';
                    $('#item-mileage').val(mid);
                } else if (isBattery) {
                    $('#add-item-modal-warranty-col').removeClass('d-none');
                    $('#add-item-modal-mileage-col').addClass('d-none');
                    if (response.warranty_value && response.warranty_unit) {
                        $('#warranty-value').val(response.warranty_value);
                        $('#warranty-unit').val(response.warranty_unit);
                    } else {
                        $('#warranty-value').val('');
                        $('#warranty-unit').val('');
                    }
                } else {
                    $('#add-item-modal-warranty-col').addClass('d-none');
                    $('#add-item-modal-mileage-col').addClass('d-none');
                    $('#warranty-value').val('');
                    $('#warranty-unit').val('');
                    $('#item-mileage').val('');
                }
                
                // Load stock status to show warehouse options, then warranty proof section (Claim In + retail + battery)
                loadItemStockStatus(itemId, function() {
                    if (($('#selected-item-id').val() || '').toString().trim() !== String(itemId)) return;
                    try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
                });
                
                // Load history for this item (customer sale+return in claim flow, else sale+purchase)
                loadHistoryForItem(itemId);
                
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

    // Load history for selected item: in Claim/Return flow show customer sale + return history; otherwise generic sale + purchase history
    function loadHistoryForItem(itemId) {
        if (typeof currentEntryType !== 'undefined' && (currentEntryType === 'claim' || currentEntryType === 'return')) {
            loadClaimItemHistory(itemId);
        } else {
            loadCustomerHistory(itemId);
            loadPurchaseHistory(itemId);
        }
    }

    // Claim / Return flow: Section 1 = last 5 sales all customers (Claim In only); Section 2 = this customer + product; returns below
    function loadClaimItemHistory(itemId) {
        var customerId = ($('#customer_id').val() || '').toString().trim();
        var isClaim = (typeof currentEntryType !== 'undefined' && currentEntryType === 'claim');
        $('#hold-rate-link').hide();

        var loadSpinner = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary me-2"></div><span class="text-muted small">Loading…</span></div>';

        if (isClaim) {
            $('#claim-global-product-history-content').html(loadSpinner);
        }
        if (customerId) {
            $('#customer-history-content').html(loadSpinner);
            $('#purchase-history-content').html(loadSpinner);
        } else {
            $('#customer-history-content').html('<p class="text-muted mb-0 small text-center">Select a party/customer to view <strong>this customer\'s</strong> sale history for this product</p>');
            $('#purchase-history-content').html('<p class="text-muted mb-0 small text-center">Select a party/customer to view return history</p>');
        }

        var ajaxData = {};
        if (customerId) ajaxData.customer_id = customerId;

        $.ajax({
            url: '{{ route("sales.items.claim.history", ":id") }}'.replace(':id', itemId),
            method: 'GET',
            data: ajaxData,
            success: function(data) {
                var globalList = (data.last_5_all_customers && data.last_5_all_customers.length) ? data.last_5_all_customers : [];
                var saleList = (data.sale_history && data.sale_history.length) ? data.sale_history : [];
                var returnList = (data.return_history && data.return_history.length) ? data.return_history : [];

                // Section 1 — Claim In only (modal section is hidden for Return etc.)
                if (isClaim) {
                    var gHtml = '<div class="fw-bold text-dark mb-2 pb-1" style="border-bottom: 1px solid #c7d2fe;"><i class="ti ti-chart-arrows me-1 text-primary"></i>Latest 5 (all customers)</div><div class="small">';
                    if (globalList.length === 0) {
                        gHtml += '<p class="text-muted mb-0 py-1">No matching sale lines found for this product</p>';
                    } else {
                        globalList.forEach(function(row, gIdx) {
                            var cust = (row.customer_name || '—').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                            var ref = row.reference ? String(row.reference).replace(/</g, '&lt;') : '';
                            var dt = row.sale_date_time || row.sale_date || '';
                            var qty = (row.quantity != null) ? (row.quantity + ' ' + (row.unit || 'Unit')) : '';
                            var bw = [];
                            if (row.branch_name) bw.push('Branch: ' + row.branch_name);
                            if (row.warehouse_name) bw.push('WH: ' + row.warehouse_name);
                            var bwLine = bw.length ? ('<div class="text-muted" style="font-size:0.7rem;">' + bw.join(' • ') + '</div>') : '';
                            var warr = row.has_warranty_images ? '<span class="badge bg-success-subtle text-success border" style="font-size:10px;">Warranty images</span>'
                                : (row.has_warranty_proof ? '<span class="badge bg-info-subtle text-info border" style="font-size:10px;">Warranty on file</span>'
                                : '<span class="badge bg-secondary-subtle text-secondary border" style="font-size:10px;">No warranty</span>');
                            var viewGlobalWarranty = '';
                            if (row.has_warranty_images && row.sale_item_id) {
                                viewGlobalWarranty = '<button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 ms-1 btn-claim-history-view-warranty" data-sale-item-id="' + String(row.sale_item_id).replace(/"/g, '&quot;') + '" style="font-size:10px;vertical-align:middle;position:relative;z-index:2;">View images</button>';
                            }
                            var recentG = (gIdx === 0) ? '<span class="badge bg-primary-subtle text-primary border ms-1" style="font-size:9px;">Latest</span>' : '';
                            gHtml += '<div class="py-2 claim-global-history-item" style="border-bottom:1px dashed #e2e8f0;">';
                            gHtml += '<div class="d-flex justify-content-between align-items-start gap-2">';
                            gHtml += '<div class="flex-grow-1" style="min-width:0;"><div class="fw-600">' + (ref ? '<span class="text-primary">' + ref + '</span>' : 'Sale') + recentG + ' <span class="text-muted fw-normal">• ' + cust + '</span></div>';
                            gHtml += '<div class="text-muted" style="font-size:0.75rem;">' + dt + (qty ? ' • Qty ' + qty : '') + ' • Rs ' + parseFloat(row.rate).toLocaleString() + '</div>';
                            gHtml += bwLine + '<div class="mt-1">' + warr + viewGlobalWarranty + '</div></div>';
                            gHtml += '<div class="text-end flex-shrink-0"><span class="fw-bold text-primary">Rs ' + parseFloat(row.rate).toLocaleString() + '</span></div>';
                            gHtml += '</div></div>';
                        });
                    }
                    gHtml += '</div>';
                    $('#claim-global-product-history-content').html(gHtml);
                }

                window._claimSaleHistoryProofs = {};
                globalList.forEach(function(row) {
                    if (row && row.sale_item_id != null) {
                        window._claimSaleHistoryProofs[String(row.sale_item_id)] = row.warranty_proofs || [];
                    }
                });
                saleList.forEach(function(s) {
                    if (s && s.sale_item_id != null) {
                        window._claimSaleHistoryProofs[String(s.sale_item_id)] = s.warranty_proofs || [];
                    }
                });

                if (!customerId) {
                    return;
                }

                var saleHtml = '<div class="fw-bold text-dark mb-2 pb-1" style="border-bottom: 1px solid #bbf7d0;"><i class="ti ti-user-check me-1 text-success"></i>This customer\'s history for this product</div><div class="sale-history-list small">';
                if (saleList.length === 0) {
                    saleHtml += '<p class="text-muted mb-0 py-1">No prior sales of this product to this customer</p>';
                } else {
                    saleList.forEach(function(sale, idx) {
                        var dateTime = sale.sale_date_time || sale.sale_date || '';
                        var ref = sale.reference ? String(sale.reference) : '';
                        var qty = (sale.quantity != null) ? (sale.quantity + ' ' + (sale.unit || 'Piece')) : '';
                        var branchWh = [];
                        if (sale.branch_name) branchWh.push('Branch: ' + sale.branch_name);
                        if (sale.warehouse_name) branchWh.push('WH: ' + sale.warehouse_name);
                        var branchWhLine = branchWh.length ? ('<div class="text-muted" style="font-size: 0.7rem;">' + branchWh.join(' • ') + '</div>') : '';
                        var codes = (sale.warranty_codes && sale.warranty_codes.length) ? sale.warranty_codes : [];
                        var codesLine = codes.length ? ('<div class="text-muted" style="font-size: 0.7rem;"><span class="fw-semibold text-dark">Warranty codes:</span> ' + codes.map(function(c) { return String(c).replace(/</g,'&lt;'); }).join(', ') + '</div>') : '';
                        var proofStatus = sale.has_warranty_proof ? '<span class="badge bg-success-subtle text-success border" style="font-size:10px;">Proof on file</span>' : '<span class="badge bg-secondary-subtle text-secondary border" style="font-size:10px;">No proof</span>';
                        var hasImg = (sale.warranty_proofs || []).some(function(p) { return p && p.image_url; });
                        var viewBtn = '';
                        if (hasImg && sale.sale_item_id) {
                            viewBtn = '<button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 ms-1 btn-claim-history-view-warranty" data-sale-item-id="' + String(sale.sale_item_id).replace(/"/g, '&quot;') + '" style="font-size:10px;vertical-align:middle;position:relative;z-index:2;">View Warranty</button>';
                        }
                        var recentBadge = (idx === 0) ? '<span class="badge bg-warning text-dark ms-1" style="font-size:9px;">Most recent</span>' : '';
                        saleHtml += '<div class="py-2 sale-history-item" style="border-bottom: 1px dashed #eee; cursor: pointer;" data-rate="' + sale.rate + '">';
                        saleHtml += '<div class="d-flex justify-content-between align-items-start gap-2">';
                        saleHtml += '<div class="flex-grow-1" style="min-width:0;">';
                        saleHtml += '<div class="fw-600">' + (ref ? ('<span class="text-primary">' + ref.replace(/</g,'&lt;') + '</span>') : 'Sale') + recentBadge + ' ' + proofStatus + viewBtn + '</div>';
                        saleHtml += '<div class="text-muted" style="font-size: 0.75rem;">' + dateTime + (qty ? ' • Qty ' + qty : '') + ' • Rs ' + parseFloat(sale.rate).toLocaleString() + '</div>';
                        saleHtml += branchWhLine + codesLine;
                        saleHtml += '</div>';
                        saleHtml += '<div class="text-end flex-shrink-0"><span class="fw-bold text-primary">Rs ' + parseFloat(sale.rate).toLocaleString() + '</span>';
                        if (sale.sale_id) {
                            var invHref = '{{ route("sales.edit", ":id") }}'.replace(':id', sale.sale_id);
                            saleHtml += '<div><a href="' + invHref.replace(/"/g, '&quot;') + '" target="_blank" rel="noopener" class="small" onclick="event.stopPropagation(); return true;">Open invoice</a></div>';
                        }
                        saleHtml += '</div></div></div>';
                    });
                }
                saleHtml += '</div>';
                $('#customer-history-content').html(saleHtml);
                if (saleList.length > 0 && saleList[0].rate) {
                    lastPurchaseRate = parseFloat(saleList[0].rate);
                    $('#hold-rate-link').show();
                }

                var returnHtml = '<div class="fw-bold text-dark mb-2 pb-1" style="border-bottom: 1px solid #e0e0e0;"><i class="ti ti-arrow-back-up me-1"></i>Last 5 Return History for Customer</div><div class="small">';
                if (returnList.length === 0) {
                    returnHtml += '<p class="text-muted mb-0 py-1">No returns for this item with this customer</p>';
                } else {
                    returnList.forEach(function(r) {
                        var ref = r.reference ? (' Ref #' + r.reference) : '';
                        var qty = (r.quantity != null) ? (r.quantity + ' ' + (r.unit || 'Piece')) : '';
                        returnHtml += '<div class="d-flex justify-content-between align-items-center py-1" style="border-bottom: 1px dashed #eee;">';
                        returnHtml += '<div><div class="fw-500">' + (r.return_date_time || r.return_date) + ' — Return</div><div class="text-muted" style="font-size: 0.75rem;">' + qty + ref + '</div></div>';
                        if (r.rate != null) returnHtml += '<div class="text-end"><span class="text-primary">Rs ' + parseFloat(r.rate).toLocaleString() + '</span></div>';
                        returnHtml += '</div>';
                    });
                }
                returnHtml += '</div>';
                $('#purchase-history-content').html(returnHtml);
            },
            error: function(xhr) {
                console.error('Error loading claim history:', xhr);
                if (isClaim) {
                    $('#claim-global-product-history-content').html('<div class="text-center py-2"><p class="text-danger mb-0 small">Could not load all-customer history</p></div>');
                }
                $('#customer-history-content').html('<div class="text-center py-2"><i class="ti ti-alert-circle text-danger fs-24 mb-1" style="display: block;"></i><p class="text-danger mb-0 small">Error loading history</p></div>');
                $('#purchase-history-content').html('<div class="text-center py-2"><i class="ti ti-alert-circle text-danger fs-24 mb-1" style="display: block;"></i><p class="text-danger mb-0 small">Error loading return history</p></div>');
            }
        });
    }

    // Load last 5 sale price history for selected item (non-claim flow)
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
                                <div class="fw-500">${custName}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">${dateTime}</div>
                            </div>
                            <div class="text-end">
                                <div class="small mb-0"><span class="fw-bold text-primary">Rs ${parseFloat(sale.rate).toLocaleString()}</span></div>
                                <div class="text-muted" style="font-size: 0.75rem;">${daysAgo}</div>
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

    // Click on sale/purchase history item to apply that rate (ignore warranty / invoice links)
    $(document).on('click', '.sale-history-item, .purchase-history-item', function(e) {
        if ($(e.target).closest('.btn-claim-history-view-warranty, a, button').length) return;
        const rate = $(this).data('rate');
        if (rate) {
            $('#item-rate').val(Math.round(parseFloat(rate) || 0));
            if (typeof syncSaleItemPerLiterFromCan === 'function') syncSaleItemPerLiterFromCan();
            $(this).addClass('bg-success bg-opacity-10');
            setTimeout(() => $(this).removeClass('bg-success bg-opacity-10'), 500);
        }
    });

    // Hold rate to apply (last sale rate for this item)
    $('#hold-rate-link').on('click', function() {
        if (lastPurchaseRate > 0) {
            $('#item-rate').val(Math.round(parseFloat(lastPurchaseRate) || 0));
            if (typeof syncSaleItemPerLiterFromCan === 'function') syncSaleItemPerLiterFromCan();
        }
    });

    $(document).on('input change', '#add-item-modal #sale-item-per-liter-rate', function() {
        syncSaleItemCanFromPerLiter();
    });
    $(document).on('input', '#add-item-modal #item-rate', function() {
        if (!window._suppressSalePerLiterSync) syncSaleItemPerLiterFromCan();
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

    // ================== Retail Warranty-card Proofs (single scan + multi images) ==================
    // Attachments-based UI: required images = selected quantity (battery + retail)
    window.warrantyProofDraft = window.warrantyProofDraft || []; // attachments: [{ image_data, captured_at, scanned_code, extracted_codes:[], extracting:true/false }]
    window.warrantySerialList = window.warrantySerialList || []; // unified serials (one per unit), USB / camera / manual / OCR
    window.pendingWarrantyScannedCode = window.pendingWarrantyScannedCode || null;
    window.cameraScanMode = window.cameraScanMode || 'item'; // 'item' | 'warranty'

    function isRetailCustomerSelected() {
        try {
            var $opt = $('#customer_id option:selected');
            var t = ($opt.data('customer-type') || 'retail').toString().toLowerCase();
            return t === 'retail';
        } catch (e) {
            return true;
        }
    }

    function isBatteryItemSelected() {
        var t = (window.currentSelectedSaleItemType || '').toString().toLowerCase();
        return t === 'battery';
    }

    /** Sync item type + warranty/mileage columns from purchases.items.details JSON (barcode / pending item paths omit the main search handler). */
    function applyAddItemModalTypeAndWarrantyMileageCols(response) {
        if (!response) return;
        var isOil = (response.liter_per_can != null && response.liter_per_can !== '' && !isNaN(parseFloat(response.liter_per_can)) && parseFloat(response.liter_per_can) > 0) ||
            (response.type && String(response.type).toLowerCase() === 'oil');
        var isBattery = response.type && String(response.type).toLowerCase() === 'battery';
        window.currentSelectedSaleItemType = response.type ? String(response.type).toLowerCase() : '';
        if (isOil) {
            $('#add-item-modal-warranty-col').addClass('d-none');
            $('#add-item-modal-mileage-col').removeClass('d-none');
            var mid = response.mileage_id != null && response.mileage_id !== '' ? String(response.mileage_id) : '';
            $('#item-mileage').val(mid);
        } else if (isBattery) {
            $('#add-item-modal-warranty-col').removeClass('d-none');
            $('#add-item-modal-mileage-col').addClass('d-none');
            if (response.warranty_value && response.warranty_unit) {
                $('#warranty-value').val(response.warranty_value);
                $('#warranty-unit').val(response.warranty_unit);
            } else {
                $('#warranty-value').val('');
                $('#warranty-unit').val('');
            }
        } else {
            $('#add-item-modal-warranty-col').addClass('d-none');
            $('#add-item-modal-mileage-col').addClass('d-none');
            $('#warranty-value').val('');
            $('#warranty-unit').val('');
            $('#item-mileage').val('');
        }
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
        // common brand/dictionary-like words to ignore completely
        var stop = {
            WARRANTY:1, GUARANTEE:1, GUARANTY:1, CARD:1, BATTERY:1, MODEL:1, SERIAL:1, NUMBER:1,
            DATE:1, NAME:1, PHONE:1, ADDRESS:1, MAINTENANCE:1, FREE:1, MAINTENANCEFREE:1,
            READYTOUSE:1, TECHNOLOGY:1, KOREAN:1, MADE:1, IN:1, COMPANY:1, LIMITED:1,
            DAEWOO:1, DAEWOOO:1, EXIDE:1, ATLAS:1, AMARON:1, GS:1, YUASA:1
        };
        if (stop[t]) return true;
        // reject plain words (letters only) like BRANDNAME
        if (/^[A-Z]{3,}$/.test(t)) return true;
        // reject word-like OCR that uses digit substitutions (e.g. VEHIC1E, ON1Y, 5NAP)
        var wordish = t
            .replace(/0/g, 'O')
            .replace(/1/g, 'I')
            .replace(/5/g, 'S')
            .replace(/2/g, 'Z')
            .replace(/6/g, 'G')
            .replace(/8/g, 'B');
        if (stop[wordish]) return true;
        // If it looks like an English-ish word (vowel-heavy) and has <=1 digit, treat as noise
        var digits = (t.match(/\d/g) || []).length;
        var vowels = (wordish.match(/[AEIOU]/g) || []).length;
        if (digits <= 1 && wordish.length >= 5 && vowels >= 2) return true;
        return false;
    }

    function isCodeCandidate(token) {
        var t = toUpperClean(token);
        if (!t) return false;
        if (isRejectedWord(t)) return false;
        // digits only: 2-12
        if (/^\d{2,12}$/.test(t)) return true;
        // uppercase alphanumeric: 4-15 (must not be letters-only)
        if (/^[A-Z0-9]{4,15}$/.test(t) && !/^[A-Z]+$/.test(t)) {
            // extra guard: reject short 5-char “word-ish” junk with only 1 digit (e.g. BRXK5)
            var dcnt = (t.match(/\d/g) || []).length;
            if (t.length <= 5 && dcnt <= 1) return false;
            return true;
        }
        // hyphen/underscore codes: 4-20 (must contain at least one separator)
        if (/^[A-Z0-9\-_]{4,20}$/.test(t) && (t.indexOf('-') !== -1 || t.indexOf('_') !== -1)) return true;
        return false;
    }

    function fuzzyFixToken(token) {
        // Fix common OCR confusions: O<->0, I/L<->1, S<->5, B<->8, G<->6, Z<->2
        var t = (token || '').toString().toUpperCase();
        // keep separators
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
        // Generate a small set of variants (bounded)
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
        // Choose best variant by pattern score
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
        var out = [];
        var seen = {};
        (arr || []).forEach(function(x) {
            var k = normalizeWarrantyCodeJs(x);
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
        // Serial/code-reader behavior: keep ONLY code-like tokens, reject words/phrases.
        // Run fuzzy cleanup first, then apply strict candidate regex filters.
        var cleaned = tokens.map(fuzzyFixToken);
        var codes = uniq(cleaned).filter(isCodeCandidate);
        codes.sort(function(a, b) {
            function score(x) {
                x = String(x || '');
                var s = 0;
                if (/^\d{2,12}$/.test(x)) s += 18;
                if (/[A-Z]/.test(x) && /\d/.test(x)) s += 22;
                if (x.indexOf('-') !== -1 || x.indexOf('_') !== -1) s += 16;
                if (/^\d{2,3}$/.test(x)) s += 10; // short handwritten like 34/528
                s += Math.min(10, x.length);
                return s;
            }
            return score(b) - score(a);
        });
        return codes.slice(0, 30);
    }

    function extractCodesFromTesseractResult(res, minConf) {
        minConf = (minConf == null) ? 60 : minConf;
        var out = [];
        try {
            var words = res && res.data && Array.isArray(res.data.words) ? res.data.words : [];
            words.forEach(function(w) {
                var conf = (w && w.confidence != null) ? Number(w.confidence) : null;
                if (conf != null && conf < minConf) return;
                var txt = (w && w.text != null) ? String(w.text) : '';
                out = out.concat(extractLikelyCodesFromText(txt));
            });
            out = uniq(out);
            if (out.length) return out;
        } catch (e) {}
        try {
            var text = (res && res.data && res.data.text) ? res.data.text : '';
            return extractLikelyCodesFromText(text);
        } catch (e2) {
            return [];
        }
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
                    // cv loads async; wait for runtime init when present
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
            } catch (e) {
                resolve(false);
            }
        });
        return await window.__loadingOpenCv;
    }

    function orderQuadPoints(pts) {
        // pts: [{x,y}*4] -> [tl,tr,br,bl]
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
            for (var r = 0; r < 4; r++) {
                pts.push({ x: best.intPtr(r, 0)[0], y: best.intPtr(r, 0)[1] });
            }
            var ordered = orderQuadPoints(pts);
            var tl = ordered[0], tr = ordered[1], br = ordered[2], bl = ordered[3];
            var widthA = Math.hypot(br.x - bl.x, br.y - bl.y);
            var widthB = Math.hypot(tr.x - tl.x, tr.y - tl.y);
            var maxW = Math.max(widthA, widthB);
            var heightA = Math.hypot(tr.x - br.x, tr.y - br.y);
            var heightB = Math.hypot(tl.x - bl.x, tl.y - bl.y);
            var maxH = Math.max(heightA, heightB);
            maxW = Math.max(320, Math.round(maxW));
            maxH = Math.max(200, Math.round(maxH));

            var srcTri = cv.matFromArray(4, 1, cv.CV_32FC2, [tl.x, tl.y, tr.x, tr.y, br.x, br.y, bl.x, bl.y]);
            var dstTri = cv.matFromArray(4, 1, cv.CV_32FC2, [0, 0, maxW - 1, 0, maxW - 1, maxH - 1, 0, maxH - 1]);
            var M = cv.getPerspectiveTransform(srcTri, dstTri);
            var dst = new cv.Mat();
            cv.warpPerspective(src, dst, M, new cv.Size(maxW, maxH), cv.INTER_LINEAR, cv.BORDER_REPLICATE, new cv.Scalar());

            // adaptive threshold + mild sharpen
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

            // cleanup
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
        canvas.width = cw;
        canvas.height = ch;
        var ctx = canvas.getContext('2d', { willReadFrequently: true });
        ctx.save();
        ctx.translate(cw / 2, ch / 2);
        ctx.rotate((rotateDeg * Math.PI) / 180);
        ctx.drawImage(img, -w / 2, -h / 2);
        ctx.restore();

        // Try document-region crop + perspective warp (OpenCV.js). Non-blocking: if not ready, skip.
        try {
            if (window.cv && window.cv.Mat) {
                var warped = tryWarpDocumentFromCanvas(canvas);
                if (warped) {
                    canvas = warped;
                    cw = canvas.width; ch = canvas.height;
                    ctx = canvas.getContext('2d', { willReadFrequently: true });
                }
            } else {
                // fire-and-forget load so future attempts can warp
                ensureOpenCvLoaded();
            }
        } catch (eWarp) {}

        // Grayscale + contrast boost
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

        // Content bbox by threshold
        id = ctx.getImageData(0, 0, cw, ch);
        d = id.data;
        var minX = cw, minY = ch, maxX = 0, maxY = 0;
        var has = false;
        var sum = 0, cnt = 0;
        for (var s = 0; s < d.length; s += 64 * 4) { sum += d[s]; cnt++; }
        var thr = cnt ? (sum / cnt) : 180;
        thr = Math.min(210, Math.max(115, thr));
        for (var y2 = 0; y2 < ch; y2++) {
            for (var x2 = 0; x2 < cw; x2++) {
                var p = (y2 * cw + x2) * 4;
                var v = d[p];
                if (v < thr) {
                    has = true;
                    if (x2 < minX) minX = x2;
                    if (y2 < minY) minY = y2;
                    if (x2 > maxX) maxX = x2;
                    if (y2 > maxY) maxY = y2;
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
            crop.width = bw;
            crop.height = bh;
            crop.getContext('2d').drawImage(canvas, minX, minY, bw, bh, 0, 0, bw, bh);
            canvas = crop;
            cw = bw; ch = bh;
            ctx = canvas.getContext('2d', { willReadFrequently: true });
        }

        // Light binarize for OCR
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

        // Scale up for OCR
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
            var rot = rotations[i];
            var canvas = preprocessToCanvas(img, rot);
            var pre = canvas.toDataURL('image/png');
            // Multi-pass OCR + region OCR (split into 3 horizontal bands)
            var codesAll = [];
            var opts = { tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_' , tessedit_pageseg_mode: '6' };
            var res1 = await window.Tesseract.recognize(pre, 'eng', opts);
            codesAll = codesAll.concat(extractCodesFromTesseractResult(res1, 70));
            codesAll = codesAll.concat(extractCodesFromTesseractResult(res1, 45));

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
                    codesAll = codesAll.concat(extractCodesFromTesseractResult(resB, 40));
                }
            } catch (eBand) {}

            // Merge + clean + keep high-value candidates only
            var merged = extractLikelyCodesFromText(codesAll.join(' '));

            // Score: prefer hyphenated + mixed alnum + keep 3-digit handwritten if present
            var score = 0;
            merged.forEach(function(c) {
                if (/^[A-Z]{3,6}[-_]\d{2,4}[A-Z]{0,2}$/.test(c)) score += 60;
                else if (/^[A-Z0-9]{5,10}$/.test(c) && /[A-Z]/.test(c) && /\d/.test(c)) score += 30;
                else if (/^\d{4,10}$/.test(c)) score += 18;
                else if (/^\d{3}$/.test(c)) score += 8;
            });
            score += Math.min(30, merged.length * 5);
            if (score > best.score) best = { score: score, codes: merged, text: '' };
        }
        return best;
    }

    function newWarrantyAttachmentId() {
        return 'wp_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 8);
    }

    /** Valid serial: 4–20 digits, or 4–24 alnum + - _ with ≥1 digit (no spaces). */
    function parseAndValidateWarrantySerial(raw) {
        var t = (raw || '').toString().trim();
        if (!t || t.length > 32) return null;
        if (/\s/.test(t)) return null;
        if (!/^[A-Za-z0-9\-_]+$/.test(t)) return null;
        if (/^\d+$/.test(t)) {
            if (t.length < 4 || t.length > 20) return null;
            return t;
        }
        if (t.length < 4 || t.length > 24) return null;
        if (!/\d/.test(t)) return null;
        return t;
    }

    /**
     * @param {string} raw
     * @param {Object} [opts] Optional: silent, skipRender, fromCamera (booleans)
     * @returns {boolean}
     */
    function tryAddWarrantySerialRaw(raw, opts) {
        opts = opts || {};
        var v = parseAndValidateWarrantySerial(raw);
        if (!v) {
            if (opts.fromCamera && typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Invalid serial', text: 'Use valid serial format.' });
            } else if (!opts.silent && typeof toastr !== 'undefined') {
                toastr.warning('Invalid warranty serial');
            }
            return false;
        }
        var req = (window.warrantyRequiredUnits || []).length;
        window.warrantySerialList = window.warrantySerialList || [];
        if (req <= 0) return false;
        if (window.warrantySerialList.length >= req) {
            if (!opts.silent && typeof toastr !== 'undefined') {
                toastr.info('All ' + req + ' serial slots filled. Remove a tag to add another.');
            }
            return false;
        }
        var nk = normalizeWarrantyCodeJs(v);
        if (window.warrantySerialList.some(function(s) { return normalizeWarrantyCodeJs(s) === nk; })) {
            if (!opts.silent && typeof toastr !== 'undefined') toastr.warning('Duplicate serial.');
            return false;
        }
        window.warrantySerialList.push(v);
        if (!opts.skipRender && window.warrantyRequiredUnits && window.warrantyRequiredUnits.length) {
            renderWarrantyProofSection(window.warrantyRequiredUnits);
            validateWarrantyProofSection(window.warrantyRequiredUnits);
        }
        var $in = $('#warranty-serial-input');
        if ($in.length) $in.val('');
        return true;
    }

    function mergeOcrCodesIntoSerialList(codes) {
        var req = (window.warrantyRequiredUnits || []).length;
        if (!req) return;
        window.warrantySerialList = window.warrantySerialList || [];
        (codes || []).forEach(function(c) {
            if (window.warrantySerialList.length >= req) return;
            tryAddWarrantySerialRaw(String(c), { silent: true, skipRender: true });
        });
        if (window.warrantyRequiredUnits && window.warrantyRequiredUnits.length) {
            renderWarrantyProofSection(window.warrantyRequiredUnits);
            validateWarrantyProofSection(window.warrantyRequiredUnits);
        }
    }

    function openWarrantyCameraBarcodeModal() {
        window.cameraScanMode = 'warranty';
        if ($('#camera-barcode-modal').length) {
            $('#camera-barcode-reader').empty().css({ width: '100%', minHeight: '240px' });
            $('#camera-barcode-modal').modal('show');
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'info', title: 'Camera unavailable', text: 'Use Barcode scan to focus the field, then use a USB scanner, or type the serial.' });
            }
        }
    }

    function focusWarrantySerialFieldForUsbScanner() {
        var $el = $('#warranty-serial-input');
        if ($el.length) {
            $el.trigger('focus');
            try { $el[0].select(); } catch (e) {}
        }
        if (typeof toastr !== 'undefined') {
            toastr.info('USB scanner: scan into the field — each code is added on Enter. Or use Camera scan.');
        }
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
            renderWarrantyProofSection(window.warrantyRequiredUnits || []);
            var best = await ocrBestOfRotations(att.image_data);
            var codes = (best && best.codes) ? best.codes : [];
            att.extracted_codes = uniq((att.extracted_codes || []).concat(codes || []));
            att.extracting = false;
            mergeOcrCodesIntoSerialList(att.extracted_codes || []);
        } catch (e) {
            try {
                var i2 = (window.warrantyProofDraft || []).findIndex(function(a) { return a && a._id === attachmentId; });
                if (i2 !== -1 && window.warrantyProofDraft[i2]) window.warrantyProofDraft[i2].extracting = false;
                renderWarrantyProofSection(window.warrantyRequiredUnits || []);
            } catch (_) {}
        }
    }

    function getWarehouseDisplayNameById(warehouseId) {
        if (!warehouseId) return '—';
        var $row = $('#add-item-modal #stock-status-list .stock-warehouse-item[data-warehouse-id="' + warehouseId + '"]');
        if ($row.length) {
            var label = ($row.find('.stock-warehouse-name').text() || $row.text() || '').replace(/\s+/g, ' ').trim();
            return label || 'Warehouse';
        }
        return 'Warehouse';
    }

    function buildWarrantyRequiredUnitsFromPayloads(payloads) {
        // Flatten into requiredUnits length = total integer quantity across payloads.
        // We keep warehouse association but UI is attachment-based (images list).
        var units = [];
        var n = 0;
        (payloads || []).forEach(function(p) {
            var q = parseFloat(p.quantity || 0) || 0;
            var qInt = Math.round(q);
            if (qInt <= 0) return;
            if (Math.abs(q - qInt) > 0.00001) return;
            var whId = (p.warehouseId != null && p.warehouseId !== '') ? String(p.warehouseId) : '';
            var whName = (p.display || '').toString().trim() || getWarehouseDisplayNameById(whId);
            for (var i = 1; i <= qInt; i++) {
                n++;
                units.push({ unit_no: n, warehouse_id: whId || null, warehouse_name: whName });
            }
        });
        return units;
    }

    function renderWarrantyProofSection(requiredUnits) {
        var $section = $('#warranty-proof-section');
        var $units = $('#warranty-proof-units');
        var $badge = $('#warranty-proof-badge');
        var $err = $('#warranty-proof-error');
        if (!$section.length) return;

        if (!isRetailCustomerSelected() || !isBatteryItemSelected() || !requiredUnits || requiredUnits.length === 0) {
            $section.addClass('d-none');
            $units.empty();
            $badge.text('0 / 0');
            $err.addClass('d-none').text('');
            window.warrantyProofDraft = [];
            window.warrantySerialList = [];
            window.pendingWarrantyScannedCode = null;
            return;
        }

        var required = requiredUnits.length;
        window.warrantyProofDraft = window.warrantyProofDraft || [];
        window.warrantySerialList = window.warrantySerialList || [];
        if (window.warrantyProofDraft.length > required) window.warrantyProofDraft = window.warrantyProofDraft.slice(0, required);
        if (window.warrantySerialList.length > required) window.warrantySerialList = window.warrantySerialList.slice(0, required);

        var serialCount = window.warrantySerialList.length;
        var attached = window.warrantyProofDraft.filter(function(a) { return a && a.image_data && String(a.image_data).indexOf('data:image/') === 0; }).length;
        $badge.text(serialCount + ' / ' + required + ' serials');

        var canAddImage = attached < required;
        var html = `
            <div class="mb-3">
                <label for="warranty-serial-input" class="form-label small fw-bold mb-1">Enter or scan warranty serial number</label>
                <div class="d-flex flex-wrap gap-2 align-items-stretch">
                    <input type="text" class="form-control form-control-sm flex-grow-1" id="warranty-serial-input" placeholder="Type serial, USB scanner (Enter), or use buttons below" autocomplete="off" inputmode="text" style="min-width: 180px;">
                    <button type="button" class="btn btn-sm btn-primary" id="warranty-serial-add-btn">Add</button>
                </div>
                <div class="d-flex flex-wrap gap-1 mt-2" id="warranty-serial-chips"></div>
                <div class="small text-muted mt-1">Serials added: <span class="fw-bold text-dark">${serialCount}</span> / ${required}</div>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="warranty-proof-scan-btn" title="Use device camera to read barcode or QR">
                    <i class="ti ti-camera me-1"></i>Scan
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="warranty-proof-barcode-scan-btn" title="Focus field for USB barcode scanner (keyboard wedge)">
                    <i class="ti ti-barcode me-1"></i>Barcode scan
                </button>
                <label class="btn btn-sm btn-outline-secondary mb-0 ${canAddImage ? '' : 'disabled'}" style="cursor:${canAddImage ? 'pointer' : 'not-allowed'};">
                    <i class="ti ti-photo me-1"></i>${canAddImage ? 'Capture / Add Image' : 'Photo limit reached'}
                    <input type="file" class="d-none" id="warranty-proof-image-input" accept="image/*" capture="environment" ${canAddImage ? '' : 'disabled'}>
                </label>
                <span class="small text-muted ms-auto">Photos: <span class="fw-bold">${attached}</span> / ${required} (optional)</span>
            </div>
            <div class="small text-muted mb-2 border-top pt-2">Use <strong>Scan</strong> for camera, <strong>Barcode scan</strong> for USB gun, <strong>Add image</strong> for OCR — all fill the same serial list.</div>
            <div class="row g-2" id="warranty-proof-grid"></div>
            <div class="mt-2 small">
                <div class="fw-bold text-dark">Detected codes (tap to add serial)</div>
                <div id="warranty-extracted-codes" class="text-muted">—</div>
            </div>
        `;
        $units.html(html);
        $section.removeClass('d-none');

        var $chips = $('#warranty-serial-chips');
        window.warrantySerialList.forEach(function(s, si) {
            var safe = String(s).replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            $chips.append(
                '<span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2 me-1 mb-1">' +
                '<span>' + safe + '</span>' +
                '<button type="button" class="btn btn-link p-0 text-danger lh-1 btn-warranty-serial-remove" data-serial-index="' + si + '" title="Remove" style="font-size:14px;">×</button></span>'
            );
        });

        var $grid = $('#warranty-proof-grid');
        var extractedAll = [];
        window.warrantyProofDraft.forEach(function(att, i) {
            if (!att || !att.image_data) return;
            var codes = (att.extracted_codes || []);
            extractedAll = extractedAll.concat(codes || []);
            var extracting = att.extracting === true;
            var ocrLine = (codes && codes.length) ? ('OCR: ' + codes.join(', ')) : 'OCR: No readable code detected';
            var scanLine = att.scanned_code ? ('Scanned: ' + att.scanned_code) : '';
            var metaLine = extracting
                ? '<span class="text-warning">Extracting…</span>'
                : (scanLine ? (scanLine + (codes && codes.length ? '<br>' : '')) : '') + ocrLine;
            $grid.append(`
                <div class="col-6 col-md-4">
                    <div class="border rounded p-2 position-relative" style="background:#fff;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 btn-warranty-attach-remove" data-attach-index="${i}" style="padding:2px 6px; line-height:1;">
                            ×
                        </button>
                        <img src="${String(att.image_data).replace(/"/g,'&quot;')}" class="img-fluid rounded border" style="max-height: 120px; width:100%; object-fit: cover;" />
                        <div class="small mt-1">
                            <div class="fw-bold">Photo ${i + 1}</div>
                            <div class="text-muted" style="font-size: 11px;">${metaLine}</div>
                            <div class="small text-muted mt-1">Tap to add to serial list:</div>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                ${(codes && codes.length ? codes.slice(0, 12) : []).map(function(c){
                                    var safe = String(c).replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                                    return '<button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 warranty-code-chip" data-code="'+safe+'" style="font-size:11px;">'+safe+'</button>';
                                }).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });
        extractedAll = uniq(extractedAll);
        var ec = $('#warranty-extracted-codes');
        if (!extractedAll.length) {
            ec.html('—');
        } else {
            ec.html('<div class="d-flex flex-wrap gap-1">' + extractedAll.map(function(c) {
                var safe = String(c).replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                return '<button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1 warranty-code-chip" data-code="'+safe+'" style="font-size:11px;">'+safe+'</button>';
            }).join('') + '</div>');
        }

        if (serialCount < required) {
            setTimeout(function() {
                var el = document.getElementById('warranty-serial-input');
                if (el && $('#add-item-modal').hasClass('show')) try { el.focus(); } catch (e2) {}
            }, 100);
        }

        validateWarrantyProofSection(requiredUnits);
    }

    function validateWarrantyProofSection(requiredUnits) {
        var $badge = $('#warranty-proof-badge');
        var $err = $('#warranty-proof-error');
        var $confirm = $('#add-item-modal #confirm-entry, #confirm-entry');

        var required = (requiredUnits || []).length;
        var list = window.warrantySerialList || [];

        var seen = {};
        var dup = false;
        var invalid = false;
        list.forEach(function(s) {
            if (!parseAndValidateWarrantySerial(s)) invalid = true;
            var k = normalizeWarrantyCodeJs(s);
            if (!k) return;
            if (seen[k]) dup = true;
            seen[k] = true;
        });

        $badge.text(list.length + ' / ' + required + ' serials');
        var ok = (required > 0 && list.length === required && !dup && !invalid);
        if (!ok) {
            var msg = 'Add exactly ' + required + ' unique warranty serial' + (required === 1 ? '' : 's') + ' (camera, USB scanner, type, or OCR).';
            if (dup) msg = 'Duplicate serial — each unit needs a different code.';
            if (invalid && list.length) msg = 'One or more serials are not in a valid format.';
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

    function getWarrantyProofsForSubmit(requiredUnits) {
        var proofs = [];
        var required = (requiredUnits || []).length;
        var list = window.warrantySerialList || [];
        for (var i = 0; i < required; i++) {
            var u = requiredUnits[i] || {};
            var a = (window.warrantyProofDraft || [])[i] || {};
            var serial = list[i] != null ? String(list[i]).trim() : '';
            proofs.push({
                unit_no: i + 1,
                warehouse_id: u.warehouse_id || null,
                code: serial || null,
                final_code: serial || null,
                scanned_code: a.scanned_code || null,
                extracted_codes: Array.isArray(a.extracted_codes) ? a.extracted_codes : [],
                image_data: (a.image_data || '').toString() || null,
                captured_at: a.captured_at || null
            });
        }
        return proofs;
    }

    $(document).on('click', '#warranty-serial-add-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        tryAddWarrantySerialRaw(($('#warranty-serial-input').val() || '').toString(), {});
    });
    $(document).on('keydown', '#warranty-serial-input', function(e) {
        if (e.which !== 13) return;
        e.preventDefault();
        tryAddWarrantySerialRaw(($(this).val() || '').toString(), {});
    });
    $(document).on('click', '.btn-warranty-serial-remove', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var idx = parseInt($(this).data('serial-index'), 10);
        if (!isFinite(idx) || idx < 0) return;
        window.warrantySerialList = window.warrantySerialList || [];
        window.warrantySerialList.splice(idx, 1);
        renderWarrantyProofSection(window.warrantyRequiredUnits || []);
        validateWarrantyProofSection(window.warrantyRequiredUnits || []);
    });

    // Camera barcode/QR (same list as manual + USB)
    $(document).on('click', '#warranty-proof-scan-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openWarrantyCameraBarcodeModal();
    });
    // USB / keyboard-wedge barcode: focus serial field (scanner types + Enter)
    $(document).on('click', '#warranty-proof-barcode-scan-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        focusWarrantySerialFieldForUsbScanner();
    });
    $(document).on('change', '#warranty-proof-image-input', function() {
        var file = this.files && this.files[0];
        if (!file) return;
        var required = (window.warrantyRequiredUnits || []).length;
        var attached = (window.warrantyProofDraft || []).filter(function(a) { return a && a.image_data && String(a.image_data).indexOf('data:image/') === 0; }).length;
        if (attached >= required) return;
        var reader = new FileReader();
        reader.onload = function(ev) {
            var dataUrl = ev.target && ev.target.result ? String(ev.target.result) : '';
            var attachmentId = newWarrantyAttachmentId();
            var att = {
                _id: attachmentId,
                image_data: dataUrl,
                captured_at: new Date().toISOString(),
                scanned_code: null,
                extracted_codes: [],
                extracting: false
            };
            window.pendingWarrantyScannedCode = null;
            window.warrantyProofDraft.push(att);
            renderWarrantyProofSection(window.warrantyRequiredUnits || []);
            validateWarrantyProofSection(window.warrantyRequiredUnits || []);
            // OCR
            runOcrForAttachmentId(attachmentId);
        };
        reader.readAsDataURL(file);
        // reset input so same file can be selected again
        $(this).val('');
    });
    $(document).on('click', '.btn-warranty-attach-remove', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var idx = parseInt($(this).data('attach-index'), 10);
        if (!isFinite(idx) || idx < 0) return;
        window.warrantyProofDraft.splice(idx, 1);
        renderWarrantyProofSection(window.warrantyRequiredUnits || []);
        validateWarrantyProofSection(window.warrantyRequiredUnits || []);
    });

    $(document).on('click', '.warranty-code-chip', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var code = ($(this).attr('data-code') != null ? $(this).attr('data-code') : $(this).data('code')) || '';
        tryAddWarrantySerialRaw(String(code), {});
    });

    // If customer changes, clear draft and hide (or rebuild on next confirm)
    $('#customer_id').on('change', function() {
        window.warrantyProofDraft = [];
        window.warrantySerialList = [];
        window.warrantyRequiredUnits = [];
        $('#warranty-proof-section').addClass('d-none');
        $('#warranty-proof-units').empty();
        $('#add-item-modal #confirm-entry, #confirm-entry').prop('disabled', false);
        // Claim In: refresh customer+product sale history if an item is already selected
        var selItem = ($('#selected-item-id').val() || '').toString().trim();
        if (selItem && typeof currentEntryType !== 'undefined' && currentEntryType === 'claim') {
            loadHistoryForItem(selItem);
        }
        // Re-show retail+battery warranty UI when party becomes Retail (modal may already have item + stock rows)
        try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
    });

    function rebuildWarrantyProofsFromCurrentWarehouseSelection() {
        if (!isRetailCustomerSelected() || !isBatteryItemSelected()) return;
        var $stockList = $('#add-item-modal #stock-status-list');
        var $warehouseRows = $stockList.find('.stock-warehouse-item');
        if (!$warehouseRows.length) {
            // Fallback: use default quantity input (usually 1) when warehouse rows aren't available yet
            var q = parseFloat($('#item-quantity').val()) || 1;
            var qInt = Math.round(q);
            if (qInt <= 0 || Math.abs(q - qInt) > 0.00001) return;
            var whId = ($('#selected-warehouse-id').val() || '').toString().trim();
            var whDisplay = ($('#item-search-warehouse').text() || '').trim() || ($('body').data('currentWarehouseName') || '').trim() || (whId ? 'Warehouse' : '—');
            var requiredUnits = buildWarrantyRequiredUnitsFromPayloads([{
                warehouseId: whId,
                display: whDisplay !== '—' ? whDisplay : 'Warehouse',
                quantity: qInt
            }]);
            window.warrantyRequiredUnits = requiredUnits;
            renderWarrantyProofSection(requiredUnits);
            if (typeof validateWarrantyProofSection === 'function') validateWarrantyProofSection(requiredUnits);
            return;
        }
        var payloads = [];
        $warehouseRows.each(function() {
            var $row = $(this);
            var cans = parseInt($row.find('select.stock-warehouse-qty-input, input.stock-warehouse-qty-input').val(), 10) || 0;
            if (cans <= 0) return;
            var whAttr = $row.attr('data-warehouse-id');
            var warehouseId = (whAttr != null && whAttr !== '') ? String(whAttr) : ($row.data('warehouseId') != null ? String($row.data('warehouseId')) : '');
            var disp = ($row.attr('data-display') || '').trim();
            if (!disp) disp = ($row.find('span').eq(1).text() || '').trim();
            payloads.push({
                warehouseId: warehouseId,
                display: disp || 'Warehouse',
                quantity: cans
            });
        });
        var requiredUnits = buildWarrantyRequiredUnitsFromPayloads(payloads);
        window.warrantyRequiredUnits = requiredUnits;
        renderWarrantyProofSection(requiredUnits);
    }

    $(document).on('input change', '#add-item-modal #stock-status-list .stock-warehouse-qty-input', function() {
        rebuildWarrantyProofsFromCurrentWarehouseSelection();
    });

    // Also rebuild when fallback quantity (default 1) changes
    $('#item-quantity').on('input change', function() {
        try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
    });

    // Initialize warranty-proof section on modal open (default qty=1) once item type is known
    $('#add-item-modal').on('shown.bs.modal', function() {
        try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
    });

    $(document).on('click', '#add-item-modal #confirm-entry, #confirm-entry', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const itemId = ($('#selected-item-id').val() || '').toString().trim();
        if (!itemId) {
            if (typeof toastr !== 'undefined') toastr.warning('Please select an item first (search and choose from list).');
            else alert('Please select an item first (search and choose from list).');
            $('#item-search').focus();
            return;
        }
        var $stockList = $('#add-item-modal #stock-status-list');
        var $warehouseRows = $stockList.find('.stock-warehouse-item');
        if ($warehouseRows.length === 0) {
            var $section = $('#add-item-modal #stock-status-section');
            if ($section.length && $section.is(':visible')) {
                // Section visible but no rows (e.g. no warehouses) – use form fallback below
            } else {
                loadItemStockStatus(itemId, function() {
                    if (($('#selected-item-id').val() || '').toString().trim() !== String(itemId)) return;
                    try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
                });
                return;
            }
        }
        var hasWarehouseRows = $warehouseRows.length > 0;
        // Multi-warehouse: collect every row that has quantity > 0 (each becomes a separate item entry)
        var warehousePayloads = [];
        var oilValidationFailed = false;
        if (hasWarehouseRows) {
            $warehouseRows.each(function() {
                var $row = $(this);
                var cans = parseInt($row.find('.stock-warehouse-qty-input').val(), 10) || 0;
                var baseLiters = parseFloat($row.find('.stock-warehouse-base-qty-input').val()) || 0;
                var extraML = parseInt($row.find('.stock-warehouse-extra-input').val(), 10) || 0;
                var lpc = parseFloat($row.data('liter-per-can')) || parseFloat($('#item-liter-per-can').val()) || 0;
                var quantity = 0;
                if (lpc > 0) quantity = cans + (baseLiters / lpc);
                else quantity = cans || (baseLiters > 0 ? baseLiters : 0);
                if (quantity <= 0) return;
                var rowUnit = ($row.data('unit') || 'Piece').toString().trim();
                var quantityDisplay;
                if (lpc > 0) {
                    quantityDisplay = (cans > 0 ? cans + ' Can' : '') + (baseLiters > 0 ? (cans > 0 ? ' ' : '') + baseLiters + ' Liter' : '');
                } else {
                    quantityDisplay = (Number.isInteger(quantity) ? quantity : quantity.toFixed(2)) + ' ' + rowUnit;
                }
                if (lpc > 0) {
                    var origCartons = parseInt($row.data('cartons'), 10) || 0;
                    var origLooseL = parseFloat($row.data('loose-liters')) || 0;
                    var origTotalLiters = (origCartons * lpc) + origLooseL;
                    var enteredLiters = (cans * lpc) + baseLiters;
                    if (enteredLiters > origTotalLiters) {
                        var whDisp = ($row.data('display') || 'Warehouse').toString();
                        if (typeof toastr !== 'undefined') toastr.warning('Quantity stock se ziyada hai for ' + whDisp + '. Available: ' + (origTotalLiters % 1 === 0 ? origTotalLiters : origTotalLiters.toFixed(2)) + ' Liter.');
                        else alert('Quantity stock se ziyada hai for ' + whDisp + '. Available: ' + (origTotalLiters % 1 === 0 ? origTotalLiters : origTotalLiters.toFixed(2)) + ' Liter.');
                        oilValidationFailed = true;
                        return false;
                    }
                }
                warehousePayloads.push({
                    warehouseId: $row.data('warehouse-id'),
                    display: ($row.data('display') || $row.find('span').eq(1).text() || 'Display').toString().trim(),
                    cans: cans,
                    baseLiters: baseLiters,
                    extraML: extraML,
                    quantity: quantity,
                    quantityDisplay: quantityDisplay,
                    unit: rowUnit
                });
            });
            if (oilValidationFailed) return;
        }
        // When no warehouse rows in list (e.g. stock section not loaded), allow adding one entry from main form quantity/warehouse
        if (!hasWarehouseRows && itemId) {
            var q = parseFloat($('#item-quantity').val()) || 0;
            if (q <= 0) q = 1;
            var whId = $('#selected-warehouse-id').val() || '';
            var whDisplay = ($('#item-search-warehouse').text() || '').trim() || ($('body').data('currentWarehouseName') || '').trim() || (whId ? 'Warehouse' : '—');
            warehousePayloads.push({
                warehouseId: whId,
                display: whDisplay !== '—' ? whDisplay : 'Warehouse',
                cans: Math.floor(q),
                baseLiters: 0,
                extraML: 0,
                quantity: q,
                quantityDisplay: q % 1 === 0 ? (Math.floor(q) + ' ' + ($('#item-unit').val() || 'Piece')) : (q + ' ' + ($('#item-unit').val() || 'Piece'))
            });
        }
        if (warehousePayloads.length === 0) {
            if (typeof toastr !== 'undefined') toastr.warning('Please select an item and set quantity for at least one warehouse.');
            else alert('Please select an item and set quantity for at least one warehouse.');
            return;
        }

        // Retail + Battery requires warranty proofs per unit quantity before confirming
        if (isRetailCustomerSelected() && isBatteryItemSelected()) {
            var requiredUnits = buildWarrantyRequiredUnitsFromPayloads(warehousePayloads);
            window.warrantyRequiredUnits = requiredUnits;
            if (!requiredUnits.length) {
                Swal.fire({ icon: 'warning', title: 'Warranty Proof Required', text: 'Retail sale requires integer quantity and warranty proof per unit.', confirmButtonText: 'OK' });
                return;
            }
            renderWarrantyProofSection(requiredUnits);
            if (!validateWarrantyProofSection(requiredUnits)) {
                Swal.fire({ icon: 'warning', title: 'Warranty serials required', text: 'Add one unique serial per unit (camera, USB barcode, type, or OCR).', confirmButtonText: 'OK' });
                return;
            }
        } else {
            window.warrantyRequiredUnits = [];
            $('#warranty-proof-section').addClass('d-none');
            $('#add-item-modal #confirm-entry, #confirm-entry').prop('disabled', false);
        }

        const unit = $('#item-unit').val() || '';
        let rate = parseFloat($('#item-rate').val()) || 0;
        const discount = parseFloat($('#item-discount').val()) || 0;
        const discountType = $('#discount-type').val() || 'amount';
        const taxPercentage = parseFloat($('#item-tax').val()) || 0;
        const rawItemName = $('#item-search').val();
        const itemName = typeof cleanItemName === 'function' ? cleanItemName(rawItemName, itemId) : (rawItemName || 'Item');
        const warrantyValue = $('#warranty-value').val();
        const warrantyUnit = $('#warranty-unit').val();
        const mileageId = $('#item-mileage').val() || '';
        const mileageName = ($('#item-mileage option:selected').text() || '').trim();
        const supplierId = $('#item_supplier_id').val();
        const stockText = $('#item-search-stock').text().trim();
        const stockValue = parseFloat(stockText) || 0;
        const isZeroStock = stockValue === 0;
        const branchName = ($('#selectedBranchName').text() || '').trim();

        if (rate <= 0 && itemId) {
            $.get('{{ route("purchases.items.details", ":id") }}'.replace(':id', itemId)).done(function(res) {
                var fetchedRate = parseFloat(res.sale_price || res.rate || 0);
                if (fetchedRate > 0) $('#item-rate').val(Math.round(fetchedRate));
            }).always(function() {
                rate = parseFloat($('#item-rate').val()) || 0;
                if (rate <= 0) {
                    rate = 1;
                    $('#item-rate').val(1);
                    if (typeof toastr !== 'undefined') toastr.info('Sale rate was not set; using 1. Edit the row to set correct rate.');
                }
                doProceed(warehousePayloads);
            });
            return;
        }
        if (rate <= 0) {
            if (typeof toastr !== 'undefined') toastr.warning('Please enter Sale rate.');
            else alert('Please enter Sale rate.');
            $('#item-rate').focus();
            return;
        }
        doProceed(warehousePayloads);
        function doProceed(payloads) {
            if (!payloads || payloads.length === 0) return;

            // Only enforce supplier / zero-stock rules for normal sale/scrap_sale flows.
            var entryType = (typeof currentEntryType !== 'undefined' && currentEntryType) ? currentEntryType : 'sale';
            var requiresSupplierCheck = (entryType === 'sale' || entryType === 'scrap_sale');

            if (requiresSupplierCheck && isZeroStock && !supplierId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Supplier Required',
                    text: 'Stock is 0. Please select a supplier to proceed.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (requiresSupplierCheck && supplierId) {
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
                        addAllWarehouseEntries(payloads);
                    }
                });
                return;
            }

            try {
                addAllWarehouseEntries(payloads);
            } catch (err) {
                console.error('Confirm selection error:', err);
                if (typeof toastr !== 'undefined') toastr.error('Could not add items. Please try again.');
                else alert('Could not add items. Please try again.');
            }
        function addAllWarehouseEntries(payloads) {
            var warrantyProofsByWarehouse = null;
            if (isRetailCustomerSelected() && window.warrantyRequiredUnits && window.warrantyRequiredUnits.length) {
                var allProofs = getWarrantyProofsForSubmit(window.warrantyRequiredUnits);
                warrantyProofsByWarehouse = {};
                allProofs.forEach(function(p) {
                    var key = (p.warehouse_id == null || p.warehouse_id === '') ? '__null__' : String(p.warehouse_id);
                    if (!warrantyProofsByWarehouse[key]) warrantyProofsByWarehouse[key] = [];
                    warrantyProofsByWarehouse[key].push(p);
                });
                // Re-number unit_no per warehouse line (1..N)
                Object.keys(warrantyProofsByWarehouse).forEach(function(k) {
                    warrantyProofsByWarehouse[k].forEach(function(p, i) { p.unit_no = i + 1; });
                });
            }
            var useSupplier = supplierId || null;
            for (var i = 0; i < payloads.length; i++) {
                var p = payloads[i];
                try {
                    var proofsForLine = null;
                    if (warrantyProofsByWarehouse) {
                        var k = (p.warehouseId == null || p.warehouseId === '') ? '__null__' : String(p.warehouseId);
                        proofsForLine = warrantyProofsByWarehouse[k] || null;
                    }
                    proceedWithItemAdd(itemId, p.quantity, (p.unit != null && p.unit !== '' ? p.unit : unit), rate, discount, discountType, taxPercentage, itemName, warrantyValue, warrantyUnit, useSupplier, isZeroStock, p.quantityDisplay || '', p.cans, p.baseLiters, p.extraML, mileageId, mileageName, true, p.warehouseId, p.display, branchName, proofsForLine);
                } catch (err) {
                    console.error('proceedWithItemAdd error for warehouse ' + (p.display || p.warehouseId), err);
                }
            }
            if (typeof resetItemModal === 'function') resetItemModal();
            $('#add-item-modal').modal('hide');
            if (typeof calculateTotals === 'function') calculateTotals();
            if (typeof syncCartToServer === 'function') syncCartToServer();
            if (typeof salesItems !== 'undefined' && salesItems.length > 0) {
                $('#payment-section').show();
                $('#payment-amount-row').show();
            }
        }
        } // end doProceed
    });
    
    // Function to proceed with adding item (quantityCans, quantityBaseLiters, quantityExtraML, mileageId, mileageName optional - for oil/edit restore)
    // addOnly, warehouseIdOverride, warehouseNameOverride, branchNameOverride: when adding multiple warehouses, pass true and overrides so modal is not closed until all are added
    function proceedWithItemAdd(itemId, quantity, unit, rate, discount, discountType, taxPercentage, itemName, warrantyValue, warrantyUnit, supplierId, isZeroStock, quantityDisplay, quantityCans, quantityBaseLiters, quantityExtraML, mileageId, mileageName, addOnly, warehouseIdOverride, warehouseNameOverride, branchNameOverride, warrantyProofs) {
        addOnly = addOnly === true;
        quantity = parseFloat(quantity) || 0;
        rate = parseFloat(rate) || 0;
        discount = parseFloat(discount) || 0;
        taxPercentage = parseFloat(taxPercentage) || 0;
        if (quantity <= 0) quantity = 1;
        const selectedWarehouseId = (warehouseIdOverride != null && warehouseIdOverride !== '') ? String(warehouseIdOverride) : ($('#selected-warehouse-id').val() || '');
        const warehouseName = (warehouseNameOverride != null && warehouseNameOverride !== '') ? String(warehouseNameOverride) : (($('#item-search-warehouse').text() || '').trim() || ($('body').data('currentWarehouseName') || '').trim() || (selectedWarehouseId ? 'Warehouse' : '—'));
        const branchName = (branchNameOverride != null && branchNameOverride !== '') ? String(branchNameOverride) : (($('#selectedBranchName').text() || '').trim());

        let discountAmount = discount;
        if (discountType === 'percent') {
            discountAmount = (quantity * rate * discount) / 100;
        }
        if (typeof discountAmount !== 'number' || isNaN(discountAmount)) discountAmount = 0;

        const subtotal = (quantity * rate) - discountAmount;
        const taxAmount = (subtotal * (taxPercentage || 0)) / 100;
        let total = subtotal + taxAmount;
        if (typeof total !== 'number' || isNaN(total)) total = quantity * rate;
        if (currentEntryType === 'scrap' || currentEntryType === 'claim' || currentEntryType === 'return') {
            total = -Math.abs(total);
        }

        // Edit mode: update existing row
        if (editingRowId !== null) {
            const existingIdx = salesItems.findIndex(function(i) { return i.id === editingRowId; });
            if (existingIdx !== -1) {
                const currentImage = ($('#add-item-modal #item-search-image').attr('src') || '').trim();
                var lineMeta = (typeof readSalesLineMetaFromForm === 'function') ? readSalesLineMetaFromForm() : {};
                var prev = salesItems[existingIdx] || {};
                const updatedItem = {
                    id: editingRowId,
                    item_id: itemId,
                    name: itemName,
                    item_type: lineMeta.item_type || prev.item_type || null,
                    part_number: lineMeta.part_number || prev.part_number || null,
                    quality_name: lineMeta.quality_name || prev.quality_name || null,
                    company_name: lineMeta.company_name || prev.company_name || null,
                    category_name: lineMeta.category_name || prev.category_name || null,
                    product_type_label: lineMeta.product_type_label || prev.product_type_label || null,
                    product_title: lineMeta.product_title || prev.product_title || null,
                    quantity: quantity,
                    quantity_display: quantityDisplay || null,
                    unit: unit,
                    rate: rate,
                    discount: discountAmount,
                    tax_percentage: taxPercentage,
                    tax_amount: taxAmount,
                    total: total,
                    warranty: warrantyValue ? warrantyValue + ' ' + warrantyUnit : null,
                    entry_type: salesItems[existingIdx].entry_type || 'purchase',
                    supplier_id: supplierId || null,
                    is_zero_stock: isZeroStock || false,
                    warehouse_id: (selectedWarehouseId && selectedWarehouseId !== '') ? selectedWarehouseId : null,
                    warehouse_name: warehouseName !== '—' ? warehouseName : null,
                    branch_name: branchName && branchName !== 'Select Branch' ? branchName : null,
                    quantity_cans: quantityCans != null ? quantityCans : (salesItems[existingIdx].quantity_cans),
                    quantity_base_liters: quantityBaseLiters != null ? quantityBaseLiters : (salesItems[existingIdx].quantity_base_liters),
                    quantity_extra_ml: quantityExtraML != null ? quantityExtraML : (salesItems[existingIdx].quantity_extra_ml),
                    mileage_id: mileageId != null && mileageId !== '' ? mileageId : (salesItems[existingIdx].mileage_id),
                    mileage_name: (mileageName != null && mileageName !== '') ? mileageName : (salesItems[existingIdx].mileage_name || null),
                    image: currentImage !== '' ? currentImage : (salesItems[existingIdx].image || null),
                    warranty_proofs: warrantyProofs || (salesItems[existingIdx].warranty_proofs || null)
                };
                salesItems[existingIdx] = updatedItem;
                $('#items-tbody tr[data-row-id="' + editingRowId + '"]').remove();
                addItemToTable(updatedItem);
                syncVehicleMileageFromFirstSaleItem();
                if (typeof updateVehicleInputsPermission === 'function') updateVehicleInputsPermission();
            }
            editingRowId = null;
            $('#add-item-modal-title').html('<i class="ti ti-plus me-2"></i>ADD SALE ITEM');
            if (!addOnly) {
                resetItemModal();
                $('#add-item-modal').modal('hide');
            }
            calculateTotals();
            syncCartToServer();
            return;
        }

        // Add to items array (entry_type: 'purchase' or 'scrap' - same as Smart Invoice)
        const currentImage = ($('#add-item-modal #item-search-image').attr('src') || '').trim();
        var lineMetaNew = (typeof readSalesLineMetaFromForm === 'function') ? readSalesLineMetaFromForm() : {};
        const item = {
            id: itemCounter++,
            item_id: itemId,
            name: itemName,
            item_type: lineMetaNew.item_type || null,
            part_number: lineMetaNew.part_number || null,
            quality_name: lineMetaNew.quality_name || null,
            company_name: lineMetaNew.company_name || null,
            category_name: lineMetaNew.category_name || null,
            product_type_label: lineMetaNew.product_type_label || null,
            product_title: lineMetaNew.product_title || null,
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
            branch_name: branchName && branchName !== 'Select Branch' ? branchName : null,
            quantity_cans: quantityCans != null ? quantityCans : null,
            quantity_base_liters: quantityBaseLiters != null ? quantityBaseLiters : null,
            quantity_extra_ml: quantityExtraML != null ? quantityExtraML : null,
            mileage_id: mileageId != null && mileageId !== '' ? mileageId : null,
            mileage_name: (mileageName != null && mileageName !== '') ? mileageName : null,
            image: currentImage !== '' ? currentImage : null,
            warranty_proofs: warrantyProofs || null
        };

        salesItems.push(item);
        addItemToTable(item);
        syncVehicleMileageFromFirstSaleItem();
        if (typeof updateVehicleInputsPermission === 'function') updateVehicleInputsPermission();
        if (!addOnly) {
            resetItemModal();
            $('#add-item-modal').modal('hide');
        }
        calculateTotals();
        syncCartToServer();
        if (supplierId) {
            $('#sale-status').val('pending');
            $('input[name="status"]').val('pending');
        }
        if (salesItems.length > 0 && $('#sale-status').val() !== 'estimate') {
            $('#payment-section').show();
            if ($('#payment-amount-row').length) $('#payment-amount-row').show();
        }
        // Update Print All / Print selected button label
        updateSalesPrintButton();
    }

    function addItemToTable(item) {
        if (!item || item.item_id == null) return;
        $('#empty-items-state').hide();
        $('#items-list').show();
        
        var displayName = (typeof salesTableRowDisplayName === 'function') ? salesTableRowDisplayName(item) : '';
        if (!displayName) displayName = (typeof cleanItemName === 'function' ? cleanItemName(item.name, item.item_id) : null) || item.name || 'Item';
        if (typeof displayName !== 'string') displayName = String(displayName || 'Item');
        const isBatterySequence = (typeof salesLineLooksLikeBatterySequence === 'function')
            ? salesLineLooksLikeBatterySequence(displayName, item)
            : (displayName && displayName.indexOf(' • ') !== -1);
        var displayNameEsc = displayName.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var itemTypeLower = String(item.item_type || '').toLowerCase();
        var isPartsRow = itemTypeLower === 'parts' || itemTypeLower === 'filters' || itemTypeLower === 'breakpad';
        var nameCellContent = isBatterySequence
            ? '<span class="battery-type-sequence fw-bold">' + displayNameEsc + '</span>'
            : (isPartsRow ? '<span class="item-search-parts-headline fw-bold">' + displayNameEsc + '</span>' : displayNameEsc);
        let typeBadge = '';
        if (item.entry_type === 'scrap') typeBadge = ' <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;">SCRAP</span>';
        else if (item.entry_type === 'scrap_sale') typeBadge = ' <span class="badge bg-success text-white ms-1" style="font-size: 9px;">SCRAP SALE</span>';
        else if (item.entry_type === 'claim') typeBadge = ' <span class="badge ms-1" style="font-size: 9px; background-color: #fef3c7; color: #b45309; border: 1px solid #fcd34d;">CLAIM</span>';
        else if (item.entry_type === 'return') typeBadge = ' <span class="badge bg-danger text-white ms-1" style="font-size: 9px;">RETURN</span>';
        else if (item.entry_type === 'temporary') typeBadge = ' <span class="badge bg-warning text-dark ms-1" style="font-size: 9px;">TEMPORARY</span>';

        var qtyUnitText = item.entry_type === 'temporary'
            ? ((parseFloat(item.quantity) % 1 === 0 ? String(Math.round(parseFloat(item.quantity))) : (parseFloat(item.quantity) || 0).toFixed(2)) + ' ' + (item.unit || 'Unit'))
            : (item.quantity_display || (Math.round(parseFloat(item.quantity)) + ' ' + (item.unit || 'Piece')));
        var rateNum = parseFloat(item.rate) || 0;
        var rateFormatted = rateNum % 1 === 0 ? rateNum.toLocaleString('en-PK', { maximumFractionDigits: 0 }) : rateNum.toFixed(2);
        var itemSecondLine = qtyUnitText + ' • Rs ' + rateFormatted;
        
        const totalVal = parseFloat(item.total);
        const safeTotal = (typeof totalVal === 'number' && !isNaN(totalVal)) ? totalVal : (parseFloat(item.rate) || 0) * (parseFloat(item.quantity) || 0);
        const totalDisplay = 'Rs ' + safeTotal.toFixed(2);
        const totalClass = safeTotal < 0 ? ' text-danger fw-bold' : '';
        
        const whDisplay = (item.warehouse_name || '—').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const whDisplayClean = whDisplay.replace(/\s*\([^)]*\)\s*$/, '');
        const imgSrc = (item.image || '').toString();
        const safeImgSrc = imgSrc ? imgSrc.replace(/"/g, '&quot;') : '';
        const photoHtml = safeImgSrc
            ? `<img src="${safeImgSrc}" alt="" class="rounded me-2 flex-shrink-0" style="width: 42px; height: 42px; object-fit: cover;" onerror="this.onerror=null; this.style.display='none';">`
            : `<span class="rounded d-inline-flex align-items-center justify-content-center bg-light text-muted flex-shrink-0" style="width: 42px; height: 42px;"><i class="ti ti-photo" style="font-size: 1.1rem;"></i></span>`;
        const warehouseTextHtml = `<span class="fw-bold sales-wh-name">${whDisplayClean}</span>`;
        const warehouseCell = `
            <div class="d-flex align-items-center">
                ${photoHtml}
                <div class="ms-2 text-center text-md-start">
                    ${warehouseTextHtml}
                </div>
            </div>
        `;
        
        const printSelectTd = `
            <td class="align-middle pehla-td-print-select sales-row-print-cell text-center" style="white-space: nowrap;">
                <input type="checkbox" class="form-check-input sales-row-verified-cb me-2" title="Select for label print" ${item.verified ? 'checked' : ''}>
                <button type="button" class="btn btn-sm btn-link p-0 sales-row-print-btn" data-row-id="${item.id}" title="Print labels">
                    <i class="ti ti-printer"></i>
                </button>
            </td>
        `;

        const proofImages = (item.warranty_proofs && Array.isArray(item.warranty_proofs))
            ? item.warranty_proofs.filter(function(p) {
                if (!p) return false;
                if (p.image_data && String(p.image_data).indexOf('data:image/') === 0) return true;
                if (p.image_url && String(p.image_url).trim()) return true;
                return false;
            })
            : [];
        const warrantyBtnHtml = proofImages.length > 0
            ? `<button type="button" class="btn btn-sm btn-outline-secondary btn-view-warranty-proofs me-1" data-row-id="${item.id}" title="View warranty card images">
                    <i class="ti ti-camera me-1"></i>Warranty images <span class="badge bg-dark ms-1">${proofImages.length}</span>
               </button>`
            : '';

        const row = `
            <tr class="pehla-items-row sales-item-row-editable" data-item-id="${item.item_id}" data-row-id="${item.id}" data-entry-type="${item.entry_type || 'purchase'}" style="cursor: pointer;" title="Click to edit">
                <td class="align-middle pehla-td-warehouse">${warehouseCell}</td>
                <td class="pehla-td-item"><div class="fw-bold text-dark">${nameCellContent}${typeBadge}</div><div class="text-muted small mt-1">${itemSecondLine.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div></td>
                <td class="pehla-td-total text-end ${totalClass}">${totalDisplay}</td>
                ${printSelectTd}
                <td class="pehla-td-actions">
                    ${warrantyBtnHtml}
                    <button type="button" class="btn btn-sm btn-danger remove-item" data-row-id="${item.id}">
                        <i class="ti ti-x"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#items-tbody').append(row);
    }

    // Stack warranty viewer above #add-item-modal (z-index 9999) + backdrop
    $('#warranty-proof-viewer-modal').on('show.bs.modal', function() {
        $(this).css('z-index', 10050);
    }).on('shown.bs.modal', function() {
        $('.modal-backdrop').last().css('z-index', 10040);
    });

    // View warranty images from Claim In customer+product sale history (server URLs)
    function openClaimHistoryWarrantyGallery(proofs, title) {
        var list = (proofs || []).filter(function(p) { return p && p.image_url; }).map(function(p) {
            return {
                image_data: p.image_url,
                unit_no: p.unit_no,
                code: p.code || '',
                captured_at: null
            };
        });
        if (!list.length) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'No images', text: 'No warranty card images are stored for this sale line.' });
            return;
        }
        window._warrantyViewerState = { proofs: list, idx: 0, item: { name: title || 'Warranty proof' } };
        function fmt(ts) {
            try { return ts ? new Date(ts).toLocaleString() : ''; } catch(e) { return ''; }
        }
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
        $('#warranty-viewer-prev').off('click.claimHist').on('click.claimHist', function() {
            var st = window._warrantyViewerState; if (!st) return;
            st.idx = Math.max(0, st.idx - 1); draw();
        });
        $('#warranty-viewer-next').off('click.claimHist').on('click.claimHist', function() {
            var st = window._warrantyViewerState; if (!st) return;
            st.idx = Math.min(st.proofs.length - 1, st.idx + 1); draw();
        });
    }

    $(document).on('click', '.btn-claim-history-view-warranty', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var sid = $(this).attr('data-sale-item-id');
        if (sid == null || sid === '') sid = $(this).data('saleItemId');
        var proofs = (window._claimSaleHistoryProofs && window._claimSaleHistoryProofs[String(sid)]) || [];
        openClaimHistoryWarrantyGallery(proofs, 'Warranty card (past sale)');
    });

    // Warranty proof viewer (images only) - create sale screen (data URLs or stored image_url)
    function renderWarrantyProofViewer(item) {
        var raw = (item && item.warranty_proofs && Array.isArray(item.warranty_proofs)) ? item.warranty_proofs : [];
        var proofs = raw.map(function(p) {
            if (!p) return null;
            var src = '';
            if (p.image_data && String(p.image_data).indexOf('data:image/') === 0) src = p.image_data;
            else if (p.image_url && String(p.image_url).trim()) src = String(p.image_url).trim();
            if (!src) return null;
            return { image_data: src, unit_no: p.unit_no, code: p.code || '', captured_at: p.captured_at };
        }).filter(Boolean);
        if (!proofs.length) {
            showSaleNotification('No Warranty Images', 'This item has no attached warranty card pictures.', 'info');
            return;
        }
        window._warrantyViewerState = { proofs: proofs, idx: 0, item: item };

        function fmt(ts) {
            try { return ts ? new Date(ts).toLocaleString() : ''; } catch(e) { return ''; }
        }
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
        $('#warranty-viewer-prev').off('click').on('click', function() {
            var st = window._warrantyViewerState; if (!st) return;
            st.idx = Math.max(0, st.idx - 1); draw();
        });
        $('#warranty-viewer-next').off('click').on('click', function() {
            var st = window._warrantyViewerState; if (!st) return;
            st.idx = Math.min(st.proofs.length - 1, st.idx + 1); draw();
        });
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
    $(document).on('click', '.remove-item', function(e) {
        e.stopPropagation();
        const rowId = $(this).data('row-id');
        salesItems = salesItems.filter(item => item.id !== rowId);
        $(this).closest('tr').remove();

        if ($('#items-tbody tr').length === 0) {
            $('#empty-items-state').show();
            $('#items-list').hide();
        }

        calculateTotals();
        syncCartToServer();
        updateSalesPrintButton();
        syncVehicleMileageFromFirstSaleItem();
        if (typeof updateVehicleInputsPermission === 'function') updateVehicleInputsPermission();
    });

    // Click item row to edit: open add-item modal with item data pre-filled (add k doran jo select kia tha wahi edit per b show ho)
    $(document).on('click', '.sales-item-row-editable', function(e) {
        // Agar click Remove / Print / warranty viewer / checkbox / PRINT cell par ho to edit modal na kholen
        if ($(e.target).closest('.remove-item, .btn-view-warranty-proofs, .sales-print-row-btn, .sales-row-print-cell, .sales-row-verified-cb').length) return;
        const rowId = $(this).data('row-id');
        const item = salesItems.find(function(i) { return i.id === rowId; });
        if (!item) return;
        if (item.entry_type === 'temporary') {
            window._tempSaleWasRowEdit = true;
            window._tempSaleModalSaved = false;
            editingRowId = rowId;
            resetTemporarySaleModal();
            $('#temp-sale-item-name').val((item.temporary_item_name || item.name || '').toString());
            $('#temp-sale-voice-transcript').val((item.voice_transcript || '').toString());
            $('#temp-sale-quality').val((item.temporary_quality || '').toString());
            $('#temp-sale-notes').val((item.line_note || '').toString());
            $('#temp-sale-qty').val(item.quantity);
            $('#temp-sale-rate').val(parseFloat(item.rate) || 0);
            $('#temp-sale-image').val('');
            window._tempSalePendingImageData = null;
            var imgT = (item.line_image || item.image || '').toString();
            if (imgT.indexOf('data:image') === 0) {
                window._tempSalePendingImageData = imgT;
                $('#temp-sale-image-preview').attr('src', imgT);
                $('#temp-sale-image-preview-wrap').removeClass('d-none');
            }
            window._tempSaleVoiceData = (item.voice_data || '').toString() || null;
            if (window._tempSaleVoiceData && window._tempSaleVoiceData.indexOf('data:audio') === 0) {
                $('#temp-sale-voice-audio').removeClass('d-none').attr('src', window._tempSaleVoiceData);
                $('#temp-sale-voice-remove').removeClass('d-none');
            }
            syncTemporarySaleMobileImageHint();
            updateTempSaleLineTotal();
            $('#temporary-sale-modal').modal('show');
            return;
        }
        editingRowId = rowId;
        $('#add-item-modal-title').html('<i class="ti ti-edit me-2"></i>EDIT ITEM');
        $('#selected-item-id').val(item.item_id);
        var editSearchVal = (typeof salesTableRowDisplayName === 'function') ? salesTableRowDisplayName(item) : '';
        if (!editSearchVal) editSearchVal = item.name || '';
        $('#item-search').val(editSearchVal);
        $('#item-quantity').val(item.quantity);
        $('#item-unit').val(item.unit || 'Unit');
        $('#item-rate').val(parseFloat(item.rate).toFixed(2));
        $('#item-discount').val(parseFloat(item.discount) || 0);
        $('#discount-type').val('amount');
        $('#item-tax').val(parseFloat(item.tax_percentage) || 0);
        $('#selected-warehouse-id').val(item.warehouse_id || '');
        if (item.warehouse_name) {
            $('#item-search-warehouse').text(item.warehouse_name).removeClass('d-none');
            $('body').data('currentWarehouseName', item.warehouse_name);
        }
        // Oil item: show Can/Liter row and mileage col so same data shows as add
        var isOil = (item.unit === 'Can' || item.quantity_cans != null);
        if (isOil) {
            $('#quantity-row-normal').hide();
            $('#quantity-row-oil').removeClass('d-none').show();
            $('#add-item-modal-warranty-col').addClass('d-none');
            $('#add-item-modal-mileage-col').removeClass('d-none');
            $('#item-quantity-cans').val(item.quantity_cans != null ? item.quantity_cans : Math.floor(parseFloat(item.quantity) || 0));
            $('#item-quantity-liters').val(item.quantity_base_liters != null ? item.quantity_base_liters : 0);
            $('#item-liter-per-can').val('1');
            if (typeof updateOilQuantityFromInputs === 'function') updateOilQuantityFromInputs();
        } else {
            $('#quantity-row-oil').hide();
            $('#quantity-row-normal').show();
            $('#add-item-modal-warranty-col').removeClass('d-none');
            $('#add-item-modal-mileage-col').addClass('d-none');
        }
        $('#empty-items-state').hide();
        $('#items-list').show();
        $('#stock-status-section').show();

        // Restore warranty proof attachments + serial list (edit mode)
        try {
            window.pendingWarrantyScannedCode = null;
            window.warrantyProofDraft = [];
            window.warrantySerialList = [];
            var savedProofs = (item.warranty_proofs && Array.isArray(item.warranty_proofs)) ? item.warranty_proofs : [];
            savedProofs.forEach(function(p) {
                if (!p) return;
                var codeStr = (p.final_code || p.code || p.scanned_code || '').toString().trim();
                var validCode = codeStr ? parseAndValidateWarrantySerial(codeStr) : null;
                if (validCode) window.warrantySerialList.push(validCode);
                if (p.image_data) {
                    window.warrantyProofDraft.push({
                        _id: newWarrantyAttachmentId(),
                        image_data: p.image_data,
                        captured_at: p.captured_at || null,
                        scanned_code: p.scanned_code || p.code || null,
                        extracted_codes: Array.isArray(p.extracted_codes) ? p.extracted_codes : [],
                        extracting: false
                    });
                }
            });
        } catch (e2) {}

        // Fetch item details so EDIT ITEM loads all associated data like purchase (Sale Rate, Mileage, Warranty)
        $.ajax({
            url: '{{ route("purchases.items.details", ":id") }}'.replace(':id', item.item_id),
            method: 'GET',
            success: function(res) {
                if (typeof applySaleItemRatesFromDetailsResponse === 'function') {
                    applySaleItemRatesFromDetailsResponse(res, { lineCanRate: parseFloat(item.rate) });
                }
                if (typeof applySaleRetailFromResponse === 'function') applySaleRetailFromResponse(res);
                if (res.mileage_id != null && res.mileage_id !== '') $('#item-mileage').val(String(res.mileage_id));
                else if (item.mileage_id != null && item.mileage_id !== '') $('#item-mileage').val(String(item.mileage_id));
                if (res.warranty_value && res.warranty_unit) {
                    $('#warranty-value').val(res.warranty_value);
                    $('#warranty-unit').val(res.warranty_unit);
                }
                if (typeof applyItemSearchAndMetaFromDetailsResponse === 'function') {
                    applyItemSearchAndMetaFromDetailsResponse(res, { forceItemSearch: true });
                }
            }
        });

        // Also fetch sales item details so item type (battery/oil) and related UI is restored correctly
        $.ajax({
            url: '{{ route("sales.items.details", ":id") }}'.replace(':id', item.item_id),
            method: 'GET',
            success: function(r2) {
                try {
                    window.currentSelectedSaleItemType = r2 && r2.type ? String(r2.type).toLowerCase() : (window.currentSelectedSaleItemType || '');
                    rebuildWarrantyProofsFromCurrentWarehouseSelection();
                    // If saved proofs had no OCR saved, run OCR per attachment in background
                    (window.warrantyProofDraft || []).forEach(function(att) {
                        if (!att) return;
                        if (att.extracted_codes && att.extracted_codes.length) return;
                        if (!att.image_data) return;
                        runOcrForAttachmentId(att._id);
                    });
                } catch (e3) {}
            }
        });
        loadItemStockStatus(item.item_id, function() {
            // Restore selected warehouse row and Can/Liter/ML so edit shows same data as when added (jo select kia tha wahi load ho)
            var wid = (item.warehouse_id != null && item.warehouse_id !== '') ? String(item.warehouse_id) : null;
            var $row = null;
            if (wid) {
                $row = $('#add-item-modal #stock-status-list .stock-warehouse-item[data-warehouse-id="' + wid + '"]');
            }
            if ((!$row || !$row.length) && item.warehouse_name) {
                $('#add-item-modal #stock-status-list .stock-warehouse-item').each(function() {
                    var d = $(this).attr('data-display') || $(this).data('display') || '';
                    if (d && String(d).toLowerCase().indexOf(String(item.warehouse_name).toLowerCase()) !== -1) {
                        $row = $(this);
                        if ($row.length) { wid = $row.attr('data-warehouse-id') || $row.data('warehouse-id'); return false; }
                    }
                });
            }
            if ($row && $row.length) {
                if (!wid) wid = $row.attr('data-warehouse-id') || $row.data('warehouse-id');
                $('.stock-warehouse-item').removeClass('bg-primary text-white');
                $('.stock-warehouse-item').each(function() { $(this).find('span').first().text(''); });
                $row.addClass('bg-primary text-white');
                $row.find('span').first().html('✓');
                $('#selected-warehouse-id').val(wid);
                var whDisp = $row.attr('data-display') || $row.data('display') || $row.find('span').eq(1).text() || item.warehouse_name || '';
                if (whDisp) {
                    $('#item-search-warehouse').text(whDisp).removeClass('d-none');
                    $('body').data('currentWarehouseName', whDisp);
                }
                var cans = item.quantity_cans != null ? item.quantity_cans : (item.unit === 'Can' ? Math.floor(parseFloat(item.quantity)) : 0);
                var baseL = item.quantity_base_liters != null ? item.quantity_base_liters : 0;
                var extraM = item.quantity_extra_ml != null ? item.quantity_extra_ml : 0;
                var lpc = parseFloat($row.attr('data-liter-per-can') || $row.data('liter-per-can')) || 0;
                if (lpc > 0) {
                    $('#item-liter-per-can').val(lpc);
                    $('#quantity-row-normal').hide();
                    $('#quantity-row-oil').removeClass('d-none').show();
                    $('#add-item-modal-warranty-col').addClass('d-none');
                    $('#add-item-modal-mileage-col').removeClass('d-none');
                    if (typeof syncSaleItemPerLiterFromCan === 'function') syncSaleItemPerLiterFromCan();
                }
                $row.find('.stock-warehouse-qty-input').val(cans);
                $row.find('.stock-warehouse-base-qty-input').val(baseL);
                $row.find('.stock-warehouse-extra-input').val(extraM);
                $('#item-quantity-cans').val(cans);
                $('#item-quantity-liters').val(baseL);
                $('#item-quantity').val(item.quantity);
                if (typeof updateOilQuantityFromInputs === 'function') updateOilQuantityFromInputs();
                if (typeof syncSaleItemPerLiterFromCan === 'function') syncSaleItemPerLiterFromCan();
            }
            try { rebuildWarrantyProofsFromCurrentWarehouseSelection(); } catch (e) {}
        });
        $('#add-item-modal').modal('show');
    });

    // Label print: cache item details (grade/technology) to swap grade for technology on battery labels
    if (typeof window._labelPrintItemDetailCache === 'undefined') {
        window._labelPrintItemDetailCache = {};
    }
    function fetchLabelPrintItemDetail(itemId) {
        var id = String(itemId || '').trim();
        if (!id) return Promise.resolve(null);
        var cache = window._labelPrintItemDetailCache;
        if (cache[id] !== undefined) {
            return Promise.resolve(cache[id]);
        }
        var url = '{{ route("sales.items.details", ":id") }}'.replace(':id', encodeURIComponent(id));
        return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(res) { cache[id] = res; return res; })
            .catch(function() { cache[id] = null; return null; });
    }
    function escapeRegExpForLabel(str) {
        return String(str).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    /** Battery: show Technology (e.g. LED ACID) on label instead of Grade (e.g. A+) when both exist. */
    function applyBatteryLabelTechnologyToName(name, detail) {
        if (!name || !detail) return name;
        if (String(detail.type || '').toLowerCase() !== 'battery') return name;
        var tech = (detail.technology_name != null && detail.technology_name !== '') ? String(detail.technology_name).trim() : '';
        var grade = (detail.grade_name != null && detail.grade_name !== '') ? String(detail.grade_name).trim() : '';
        if (!tech || !grade) return name;
        return String(name).replace(new RegExp(escapeRegExpForLabel(grade), 'gi'), tech);
    }

    function prefetchLabelDetailsForCartItems(items) {
        var ids = [];
        if (items && items.length) {
            items.forEach(function(it) {
                if (it && it.item_id != null && String(it.item_id) !== '') ids.push(String(it.item_id));
            });
        }
        ids = ids.filter(function(id, i, a) { return a.indexOf(id) === i; });
        if (!ids.length) return Promise.resolve({});
        return Promise.all(ids.map(function(id) { return fetchLabelPrintItemDetail(id); })).then(function(results) {
            var map = {};
            ids.forEach(function(id, idx) { map[id] = results[idx]; });
            return map;
        });
    }
    function buildLabelsHtmlForCartItems(items, detailMap) {
        var labelsHtml = '';
        var total = 0;
        (items || []).forEach(function(item) {
            var qty = Math.max(1, Math.min(500, Math.round(parseFloat(item.quantity)) || 1));
            var name = (item.name || ('Item #' + (item.item_id || '')));
            var salePrice = parseFloat(item.rate) || 0;
            var priceText = salePrice > 0 ? ('Rs ' + Math.round(salePrice)) : '';
            var idKey = (item.item_id != null && item.item_id !== '') ? String(item.item_id) : '';
            var detail = idKey && detailMap ? detailMap[idKey] : null;
            var barcodeVal = (detail && detail.bar_code) ? String(detail.bar_code) : ((item.bar_code || item.item_id || '').toString());
            for (var i = 0; i < qty; i++) {
                labelsHtml += buildLabelPrintItemHtml(name, priceText, barcodeVal, detail);
                total++;
            }
        });
        return { html: labelsHtml, total: total };
    }

    // Build one label item HTML: same design as Purchase
    function buildLabelPrintItemHtml(name, priceText, barcodeValue, labelDetail) {
        var raw   = (name || '').toString().replace(/<[^>]*>/g, '').trim();
        if (labelDetail && typeof applyBatteryLabelTechnologyToName === 'function') {
            raw = applyBatteryLabelTechnologyToName(raw, labelDetail);
        }
        var parts = raw.split(/\s*[•·]\s*/).map(function (p) { return p.trim(); }).filter(Boolean);
        var line1 = '';
        var line2 = '';

        if (parts.length >= 2) {
            line1 = (parts[0] + ' . ' + parts[parts.length - 1])
                .toUpperCase()
                .replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            if (parts.length > 2) {
                line2 = parts.slice(1, -1).join(' . ')
                    .replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }
        } else if (parts.length === 1) {
            line1 = parts[0].replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        } else {
            line1 = raw.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') || '—';
        }

        var priceHtml  = priceText ? '<div class="label-print-rate">' + String(priceText).replace(/</g, '&lt;') + '</div>' : '';
        var barcodeRaw = (barcodeValue != null && barcodeValue !== '') ? String(barcodeValue) : '0';
        var barcodeAttr = barcodeRaw.replace(/"/g, '&quot;');
        var barcodeCaptionHtml = barcodeRaw.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var barcodeHtml = '<div class="label-print-barcode-wrap"><canvas class="label-print-barcode" data-barcode="' + barcodeAttr + '"></canvas><div class="label-print-barcode-caption">' + barcodeCaptionHtml + '</div></div>';

        return '<div class="label-print-item">' +
            '<div class="label-print-line1">' + line1 + '</div>' +
            (line2 ? '<div class="label-print-line2">' + line2 + '</div>' : '') +
            priceHtml +
            barcodeHtml +
            '</div>';
    }

    function renderLabelBarcodes() {
        if (typeof JsBarcode === 'undefined') return;
        $('#label-print-modal-content .label-print-barcode').each(function () {
            var val = $(this).data('barcode');
            if (val === '' || val == null) val = '0';
            try {
                JsBarcode(this, String(val), { format: 'CODE128', displayValue: false, margin: 0, height: 22 });
            } catch (e) {
                console.error('JsBarcode error', e);
            }
        });
    }

    // Top print button: Print selected rows or all rows (labels)
    $(document).on('click', '#sales-print-labels-btn', function(e) {
        e.preventDefault();
        var $checkedRows = $('#items-tbody .sales-item-row-editable').has('.sales-row-verified-cb:checked');

        // Helper to open modal with given labelsHtml and count text
        function openLabelsModal(labelsHtml, countText, withQtyControls) {
            if (!labelsHtml.trim()) return;
            $('#label-print-count').text(countText);
            var $content = $('#label-print-modal-content');
            $content.removeData('single-name').removeData('single-rate').removeData('single-item-id')
                .html('<div class="label-print-sheet">' + labelsHtml + '</div>');
            $content.toggleClass('label-print-hide-price', !$('#label-print-show-price').is(':checked'));
            if (typeof renderLabelBarcodes === 'function') renderLabelBarcodes();
            if (withQtyControls) {
                $('#label-print-qty-wrap').removeClass('d-none').addClass('d-flex');
            } else {
                $('#label-print-qty-wrap').addClass('d-none').removeClass('d-flex');
            }
            var labelModalEl = document.getElementById('label-print-view-modal');
            if (labelModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(labelModalEl).show();
            } else {
                $('#label-print-view-modal').modal('show');
            }
        }

        // If some rows are selected -> print only selected
        if ($checkedRows.length > 0) {
            var itemsSel = [];
            $checkedRows.each(function() {
                var rowId = $(this).data('row-id');
                if (typeof salesItems === 'undefined') return;
                var cartItem = salesItems.find(function(i) { return String(i.id) === String(rowId); });
                if (cartItem) itemsSel.push(cartItem);
            });
            if (itemsSel.length === 0) return;
            prefetchLabelDetailsForCartItems(itemsSel).then(function(detailMap) {
                var built = buildLabelsHtmlForCartItems(itemsSel, detailMap);
                if (built.total === 0) return;
                openLabelsModal(built.html, built.total + ' label' + (built.total !== 1 ? 's' : '') + ' (selected)', false);
            }).catch(function() {
                var built = buildLabelsHtmlForCartItems(itemsSel, {});
                if (built.total === 0) return;
                openLabelsModal(built.html, built.total + ' label' + (built.total !== 1 ? 's' : '') + ' (selected)', false);
            });
            return;
        }

        // Otherwise print all items
        if (typeof salesItems === 'undefined' || !salesItems.length) {
            if (typeof toastr !== 'undefined') toastr.warning('Pehle cart mein items add karein.');
            else alert('Pehle cart mein items add karein.');
            return;
        }
        prefetchLabelDetailsForCartItems(salesItems).then(function(detailMap) {
            var built = buildLabelsHtmlForCartItems(salesItems, detailMap);
            if (built.total === 0) {
                if (typeof toastr !== 'undefined') toastr.warning('Koi label nahi bani. Quantity check karein.');
                else alert('Koi label nahi bani. Quantity check karein.');
                return;
            }
            openLabelsModal(built.html, built.total + ' label' + (built.total !== 1 ? 's' : '') + ' (sab items)', false);
        }).catch(function() {
            var built = buildLabelsHtmlForCartItems(salesItems, {});
            if (built.total === 0) {
                if (typeof toastr !== 'undefined') toastr.warning('Koi label nahi bani. Quantity check karein.');
                else alert('Koi label nahi bani. Quantity check karein.');
                return;
            }
            openLabelsModal(built.html, built.total + ' label' + (built.total !== 1 ? 's' : '') + ' (sab items)', false);
        });
    });

    // Per-row 2×1 label print (Sales) – is row ki quantity ke mutabiq thermal sticker print
    // Cell ya button dono par click se kaam kare
    $(document).on('click', '.sales-row-print-btn, .sales-row-print-cell', function(e) {
        e.stopPropagation();
        try {
            console.log('Sales row print clicked');
            var $row = $(this).closest('.sales-item-row-editable');
            if (!$row.length || typeof salesItems === 'undefined') {
                console.warn('Sales row print: no row or salesItems not defined');
                return;
            }

            var rowId = $row.data('row-id');
            var item = salesItems.find(function(i) {
                return String(i.id) === String(rowId);
            });
            console.log('Sales row print item', rowId, item);
            if (!item) return;

            var qty = Math.max(1, Math.min(500, Math.round(parseFloat(item.quantity)) || 1));
            var name = (item.name || ('Item #' + (item.item_id || '')));
            var salePrice = parseFloat(item.rate) || 0;
            var priceText = salePrice > 0 ? ('Rs ' + Math.round(salePrice)) : '';

            function openRowLabelModal(barcodeVal, labelsHtml, detail) {
                $('#label-print-count').text(qty + ' label' + (qty !== 1 ? 's' : ''));
                var $content = $('#label-print-modal-content');
                var displayName = (detail && typeof applyBatteryLabelTechnologyToName === 'function')
                    ? applyBatteryLabelTechnologyToName(String(name || '').replace(/<[^>]*>/g, '').trim(), detail)
                    : name;
                $content
                    .data('single-name', displayName)
                    .data('single-rate', priceText)
                    .data('single-item-id', barcodeVal)
                    .html('<div class="label-print-sheet">' + labelsHtml + '</div>');
                $content.toggleClass('label-print-hide-price', !$('#label-print-show-price').is(':checked'));
                if (typeof renderLabelBarcodes === 'function') renderLabelBarcodes();
                $('#label-print-qty-wrap').removeClass('d-none').addClass('d-flex');
                $('#label-print-qty-input').val(qty).attr('min', 1).attr('max', 500);

                var labelModalEl = document.getElementById('label-print-view-modal');
                if (labelModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(labelModalEl).show();
                } else {
                    $('#label-print-view-modal').modal('show');
                }
            }

            fetchLabelPrintItemDetail(item.item_id).then(function(detail) {
                var barcodeVal = (detail && detail.bar_code) ? String(detail.bar_code) : ((item.bar_code || item.item_id || '').toString());
                var labelsHtml = '';
                for (var i = 0; i < qty; i++) {
                    labelsHtml += buildLabelPrintItemHtml(name, priceText, barcodeVal, detail);
                }
                openRowLabelModal(barcodeVal, labelsHtml, detail);
            }).catch(function() {
                var barcodeVal = (item.bar_code || item.item_id || '').toString();
                var labelsHtml = '';
                for (var j = 0; j < qty; j++) {
                    labelsHtml += buildLabelPrintItemHtml(name, priceText, barcodeVal, null);
                }
                openRowLabelModal(barcodeVal, labelsHtml, null);
            });
        } catch (err) {
            console.error('Sales row print error', err);
            if (typeof toastr !== 'undefined') {
                toastr.error('Label print open nahi ho saka. Console errors check karein.');
            } else {
                alert('Label print open nahi ho saka. Console errors check karein.');
            }
        }
    });

    function resetItemModal() {
        $('#selected-item-id').val('');
        $('#selected-warehouse-id').val('');
        $('#sales-selected-item-type, #sales-selected-part-number, #sales-selected-quality-name, #sales-selected-company-name, #sales-selected-category-name, #sales-selected-product-type-label, #sales-selected-product-title').val('');
        $('#item-edit-in-modal-btn').hide();
        $('#item-search').val('');
        window.currentSelectedSaleItemType = '';
        window.warrantyProofDraft = [];
        window.warrantySerialList = [];
        window.warrantyRequiredUnits = [];
        window.pendingWarrantyScannedCode = null;
        $('#warranty-proof-section').addClass('d-none');
        $('#warranty-proof-units').empty();
        $('#warranty-proof-badge').text('0 / 0');
        $('#warranty-proof-error').addClass('d-none').text('');
        $('#add-item-modal #confirm-entry, #confirm-entry').prop('disabled', false);
        resetItemQuantitySelect();
        $('#item-unit').val('');
        $('#item-rate').val('0');
        $('#warranty-value').val('');
        $('#warranty-unit').val('');
        $('#item-mileage').val('');
        $('#add-item-modal-warranty-col').removeClass('d-none');
        $('#add-item-modal-mileage-col').addClass('d-none');
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
        if (typeof resetSaleItemRateColumnsForModal === 'function') resetSaleItemRateColumnsForModal();
        saleBaseRetailPrice = null;
        $('#sale-item-retail-price-column').addClass('d-none');
        $('#sale-item-retail-price').val('');
        $('#sale-item-retail-percentage').val('').trigger('change');
        $('#sale-item-retail-after-calc').text('—');
    }

    function calculateTotals() {
        let itemTotal = 0;
        salesItems.forEach(function(item) {
            itemTotal += parseFloat(item.total);
        });

        const orderTax = parseFloat($('#order_tax').val()) || 0;
        // Order-level discount: payment panel exposes Rs amount only (#discount)
        const discount = parseFloat($('#discount').val()) || 0;
        const shipping = parseFloat($('#shipping').val()) || 0;

        const grossTotal = itemTotal;
        const grandTotal = itemTotal + orderTax - discount + shipping;
        // Payment section: Net Payable = Total Items Amount - Discount
        const netPayable = grossTotal - discount;

        $('#gross-amount').text(Math.round(parseFloat(grossTotal)));
        $('#net-payable').text(Math.round(parseFloat(netPayable)));
        $('#items-count').text(salesItems.length + ' Items Listed');

        // Match Create Purchase: payment panel stays visible; hide only for Estimates
        if ($('#sale-status').val() === 'estimate') {
            $('#payment-section').hide();
        } else {
            $('#payment-section').show();
        }

        updatePaymentBalances();
    }

    function updatePaymentBalances() {
        const netPayableText = $('#net-payable').text().replace(/Rs\s*/i, '').replace(/,/g, '').trim();
        const netPayable = parseFloat(netPayableText) || 0;

        let cashReceivedGross = 0;
        let cashReturned = 0;
        $('#cash-received-entries .cash-received-row').each(function() {
            const v = parseFloat($(this).find('.cash-amount-input').val()) || 0;
            if ($(this).hasClass('cash-row-is-return')) {
                cashReturned += v;
            } else {
                cashReceivedGross += v;
            }
        });
        const cashNetTowardSale = cashReceivedGross - cashReturned;

        let bankTotal = 0;
        $('input[name="bank_received_amount[]"]').each(function() {
            bankTotal += parseFloat($(this).val()) || 0;
        });
        const totalReceived = cashNetTowardSale + bankTotal;
        let currentRemaining = netPayable - totalReceived;
        const previousBalance = parseFloat($('#previous-balance-input').val()) || 0;
        const finalBalance = currentRemaining + previousBalance;

        $('#cash-received-total').text('Rs ' + Math.round(cashNetTowardSale));
        const $bd = $('#cash-received-breakdown');
        if (cashReturned > 0.005) {
            $bd.text('Received Rs ' + Math.round(cashReceivedGross) + ' − Returned Rs ' + Math.round(cashReturned)).show();
        } else {
            $bd.text('').hide();
        }

        $('#bank-received-total').text('Rs ' + Math.round(bankTotal));

        if (currentRemaining < 0) {
            $('#current-remaining').html('<span class="text-success">Overpaid / Advance (Rs ' + Math.abs(Math.round(currentRemaining)) + ')</span>');
        } else {
            $('#current-remaining').text('Rs ' + Math.round(currentRemaining));
        }

        if (finalBalance < 0) {
            $('#total-final-balance').html('<span class="text-warning">Overpaid / Advance (Rs ' + Math.abs(Math.round(finalBalance)) + ')</span>');
        } else {
            $('#total-final-balance').text('Rs ' + Math.round(finalBalance));
        }

        if (cashReturned > cashReceivedGross + bankTotal + 0.01) {
            $('#cash-received-block').addClass('border border-warning rounded-2');
        } else {
            $('#cash-received-block').removeClass('border border-warning rounded-2');
        }
    }

    function validateSalesCashReturnRule() {
        let cashReceivedGross = 0;
        let cashReturned = 0;
        let bankTotal = 0;
        $('#cash-received-entries .cash-received-row').each(function() {
            const v = parseFloat($(this).find('.cash-amount-input').val()) || 0;
            if ($(this).hasClass('cash-row-is-return')) {
                cashReturned += v;
            } else {
                cashReceivedGross += v;
            }
        });
        $('input[name="bank_received_amount[]"]').each(function() {
            bankTotal += parseFloat($(this).val()) || 0;
        });
        if (cashReturned > cashReceivedGross + bankTotal + 0.01) {
            return {
                ok: false,
                message: 'Cash return cannot exceed cash and bank received on this sale. Add received lines first or reduce the return amount.'
            };
        }
        return { ok: true };
    }

    function applyCashRowReturnState($row, isReturn) {
        const $btn = $row.find('.cash-row-return-toggle');
        const $hid = $row.find('.cash-is-return-input');
        const $badge = $row.find('.cash-return-badge');
        const icon = '<i class="ti ti-corner-up-left me-1" aria-hidden="true"></i>';
        if (isReturn) {
            $row.addClass('cash-row-is-return').attr('data-cash-entry-kind', 'return');
            $hid.val('1');
            $badge.removeClass('d-none');
            $btn.addClass('active').attr('aria-pressed', 'true').html(icon + 'Returning to party');
        } else {
            $row.removeClass('cash-row-is-return').attr('data-cash-entry-kind', 'received');
            $hid.val('0');
            $badge.addClass('d-none');
            $btn.removeClass('active').attr('aria-pressed', 'false').html(icon + 'Cash return');
        }
    }

    $(document).on('click', '.cash-row-return-toggle', function(e) {
        e.preventDefault();
        const $row = $(this).closest('.cash-received-row');
        const nowReturn = !$row.hasClass('cash-row-is-return');
        applyCashRowReturnState($row, nowReturn);
        updatePaymentBalances();
    });

    $(document).on('click', '.sales-cash-load-total-row-btn', function() {
        const gross = parseFloat(String($('#gross-amount').text()).replace(/,/g, '')) || 0;
        const $row = $(this).closest('.cash-received-row');
        const $inp = $row.find('.cash-amount-input');
        if (!$inp.length) return;
        $inp.val(gross % 1 === 0 ? String(Math.round(gross)) : gross.toFixed(2));
        $inp.addClass('sales-cash-amount-pulse');
        setTimeout(function() {
            $inp.removeClass('sales-cash-amount-pulse');
        }, 1400);
        $inp.trigger('focus');
        updatePaymentBalances();
    });

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
        applyCashRowReturnState(clone, false);
        $('#cash-received-entries').append(clone);
        updateCashRowCloseButtons();
        updatePaymentBalances();
    });
    $(document).on('click', '.cash-row-close', function() {
        const rows = $('#cash-received-entries .cash-received-row');
        if (rows.length <= 1) return;
        $(this).closest('.cash-received-row').remove();
        updateCashRowCloseButtons();
        updatePaymentBalances();
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
    // Cash amount (sale cash lines): if empty/zero, focus fills Total Items Amount; else clear literal 0 for typing
    $(document).on('focus', '#cash-received-entries .cash-amount-input', function() {
        const $el = $(this);
        const raw = ($el.val() || '').toString().trim();
        const n = parseFloat(raw);
        const unset = raw === '' || (!isNaN(n) && n === 0);
        if (unset) {
            const gross = parseFloat(String($('#gross-amount').text()).replace(/,/g, '')) || 0;
            if (gross > 0) {
                $el.val(gross % 1 === 0 ? String(Math.round(gross)) : gross.toFixed(2));
                updatePaymentBalances();
                return;
            }
        }
        if ($el.val() === '0' || $el.val() === '0.00') {
            $el.val('');
        }
    });
    $(document).on('click', '#cash-received-entries .purchase-cash-amount-wrap', function(e) {
        if ($(e.target).closest('.cash-amount-input').length) return;
        $(this).find('.cash-amount-input').trigger('focus');
    });
    $(document).on('blur', '.cash-amount-input', function() {
        const $el = $(this);
        if ($el.val() === '' || $el.val() === null) $el.val('0');
        updatePaymentBalances();
    });
    $(document).on('input change', '.cash-amount-input', function() {
        updatePaymentBalances();
    });
    $('#previous-balance-input').on('input change', function() {
        updatePaymentBalances();
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
    $('#add-bank-below-btn').on('click', function() {
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
        const row = $('<div class="bank-payment-row purchase-bank-row d-flex flex-wrap gap-3 align-items-start"></div>');
        row.html(
            '<div class="bank-row-photo-preview flex-shrink-0" style="display: none;"><img class="img-thumbnail" style="max-width: 100px; max-height: 100px; object-fit: cover;" alt="Bank receipt"></div>' +
            '<div class="bank-row-details flex-grow-1" style="min-width: 0;">' +
            '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">' +
            '<p class="mb-0" style="font-size: 10px; font-weight: 900; color: #6b21a8; text-transform: uppercase;">Bank Entry</p>' +
            '<span class="fw-bold" style="color: #9333ea; font-size: 1rem;">Rs ' + (amtNum % 1 === 0 ? amtNum : amtNum.toFixed(2)) + '</span>' +
            '</div>' +
            (reference ? '<div class="mb-2"><span class="text-muted small">Ref: ' + $('<div>').text(reference).html() + '</span></div>' : '') +
            '<div class="d-flex align-items-center justify-content-end flex-wrap gap-2">' +
            '<button type="button" class="btn btn-sm btn-link text-danger p-0 bank-remove-payment" title="Remove"><i class="ti ti-x"></i></button>' +
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
        updatePaymentBalances();
        $('#addBankPaymentModal').modal('hide');
        $('#bank_modal_amount').val('0');
        $('#bank_modal_reference').val('');
        $('#bank_modal_photo').val('');
        $('#bank_modal_photo_name').text('');
        $('#bank_modal_photo_preview').hide().find('#bank_modal_photo_preview_img').attr('src', '');
    });
    $(document).on('click', '.bank-remove-payment', function() {
        $(this).closest('.bank-payment-row').remove();
        updatePaymentBalances();
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

    // Form submission – show notification on any error or validation failure
    function showSaleNotification(title, message, icon) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: icon || 'warning', title: title || 'Notice', text: message, confirmButtonText: 'OK' });
        } else {
            alert((title ? title + '\n\n' : '') + message);
        }
    }
    $('#salesForm').off('submit.saleSave').on('submit.saleSave', function(e) {
        e.preventDefault();
        if (saleSubmitInFlight) return false;
        saleSubmitInFlight = true;
        var submitIntent = saleSubmitIntent || 'save';
        saleSubmitIntent = 'save';
        var $btn = $('#submit-sale-btn');
        var $printBtn = $('#bluetooth-print-btn');
        var btnOriginalHtml = $btn.html();
        var printOriginalHtml = $printBtn.html();
        saleSubmitRequestUuid = ((window.crypto && window.crypto.randomUUID) ? window.crypto.randomUUID() : ('sale-' + Date.now() + '-' + Math.floor(Math.random() * 1000000)));
        $btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i> Saving...');
        if (submitIntent === 'print') {
            $printBtn.prop('disabled', true).html('<i class="ti ti-loader ti-spin me-1"></i> Printing...');
        }
        function restoreBtn() {
            saleSubmitInFlight = false;
            saleSubmitRequestUuid = null;
            $btn.prop('disabled', false).html(btnOriginalHtml);
            $printBtn.prop('disabled', false).html(printOriginalHtml);
        }
        try {
        var items = (typeof salesItems !== 'undefined' && Array.isArray(salesItems)) ? salesItems : [];
        var vehs = (typeof vehicles !== 'undefined' && Array.isArray(vehicles)) ? vehicles : [];

        if (items.length === 0) {
            restoreBtn();
            showSaleNotification('Cannot Save Sale', 'Please add at least one item to the sale.', 'warning');
            return false;
        }
        if (!$('#salesBranchId').val()) {
            restoreBtn();
            showSaleNotification('Cannot Save Sale', 'Please select a branch first.', 'warning');
            $('#salesBranchId').focus();
            return false;
        }
        if (!$('#customer_id').val()) {
            restoreBtn();
            showSaleNotification('Cannot Save Sale', 'Please select party / customer.', 'warning');
            $('#customer_id').focus();
            return false;
        }

        var cashRetCheck = validateSalesCashReturnRule();
        if (!cashRetCheck.ok) {
            restoreBtn();
            showSaleNotification('Cash return', cashRetCheck.message, 'warning');
            return false;
        }
        
        // Add vehicles as hidden inputs before submitting
        // Remove existing vehicle inputs first
        $('input[name^="vehicles["]').remove();
        
        // Sync editable vehicle metrics from DOM to vehicles array before submit
        $('.vehicle-current-km-input, .vehicle-daily-run-km-input, .vehicle-oil-capacity-input, .vehicle-next-date-input, .vehicle-interval-days-input, .vehicle-interval-months-input').each(function() {
            const vehicleId = $(this).data('vehicle-id');
            const val = ($(this).val() != null ? String($(this).val()) : '').trim();
            let name = 'oil_capacity';
            if ($(this).hasClass('vehicle-current-km-input')) name = 'current_km';
            else if ($(this).hasClass('vehicle-daily-run-km-input')) name = 'daily_run_km';
            else if ($(this).hasClass('vehicle-next-date-input')) name = 'next_date';
            else if ($(this).hasClass('vehicle-interval-days-input')) name = 'interval_days';
            else if ($(this).hasClass('vehicle-interval-months-input')) name = 'interval_months';
            const v = vehs.find(function(ve) { return String(ve.id) === String(vehicleId); });
            if (v) v[name] = val;
        });
        // Sync next_km from Next KM output (metrics may be in #vehicle-primary-metrics-host for selected vehicle)
        $('.vehicle-metrics').each(function() {
            const vehicleId = $(this).data('vehicle-id');
            const v = vehs.find(function(ve) { return String(ve.id) === String(vehicleId); });
            if (!v) return;
            var parsed = extractNumericKmFromNextOutputText($(this).find('.vehicle-next-km-output').text());
            if (parsed !== '') {
                v.next_km = parsed;
                v.previous_target_next_km = parsed;
            }
        });
        var intervalKmSubmit = (typeof getVehicleMileageKm === 'function') ? getVehicleMileageKm() : null;
        vehs.forEach(function(v) {
            var nk = (v.next_km != null && v.next_km !== '') ? String(v.next_km).replace(/[^\d.]/g, '') : '';
            if (nk !== '' && !isNaN(parseFloat(nk))) return;
            var curStr = (v.current_km || '').toString().replace(/[^\d.]/g, '');
            var cur = parseFloat(curStr);
            if (intervalKmSubmit != null && intervalKmSubmit > 0 && isFinite(cur) && cur >= 0) {
                v.next_km = String(Math.round(cur + intervalKmSubmit));
                v.previous_target_next_km = v.next_km;
            }
        });

        vehs.forEach(function(vehicle, index) {
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
                name: `vehicles[${index}][car_manufacturer_id]`,
                value: vehicle.car_manufacturer_id != null ? vehicle.car_manufacturer_id : ''
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][car_model_id]`,
                value: vehicle.car_model_id != null ? vehicle.car_model_id : ''
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
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][next_date]`,
                value: vehicle.next_date || ''
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][next_km]`,
                value: (function() {
                    var raw = (vehicle.next_km != null && vehicle.next_km !== '') ? String(vehicle.next_km).replace(/[^\d.]/g, '') : '';
                    if (raw !== '' && !isNaN(parseFloat(raw))) return String(Math.round(parseFloat(raw)));
                    var alt = (typeof saleResolvedOilTargetKmString === 'function') ? saleResolvedOilTargetKmString(vehicle) : '';
                    return alt || '';
                })()
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][interval_days]`,
                value: vehicle.interval_days || ''
            }).appendTo('#salesForm');
            $('<input>').attr({
                type: 'hidden',
                name: `vehicles[${index}][interval_months]`,
                value: vehicle.interval_months || ''
            }).appendTo('#salesForm');
        });
        
        // Validate payment information
        const paymentMethod = $('#payment_method_id').val();
        const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
        const $gt = $('#grand-total');
        const grandTotal = $gt.length ? (parseFloat(($gt.text() || '0').toString().replace(/Rs\s*/gi, '').replace(/,/g, '')) || 0) : 0;
        
        // If payment method is selected and payment amount is greater than 0, validate it
        // Allow payment method to be selected with 0 amount (no payment recorded)
        // Only validate if user actually wants to record a payment (amount > 0)
        if (paymentMethod && paymentAmount > 0) {
            // Payment amount is provided, validate it doesn't exceed grand total
            if (paymentAmount > grandTotal) {
                restoreBtn();
                showSaleNotification('Cannot Save Sale', 'Payment amount cannot exceed grand total (Rs ' + Math.round(grandTotal) + ').', 'warning');
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
                restoreBtn();
                showSaleNotification('Cannot Save Sale', 'Please select a bank account for bank payment.', 'warning');
                $('#bank_account_id').focus();
                return false;
            }
            
            // Check if transaction ID is required for bank methods
            if (isBank && !$('#payment_transaction_id').val()) {
                restoreBtn();
                showSaleNotification('Cannot Save Sale', 'Please enter transaction ID/reference for bank payment.', 'warning');
                $('#payment_transaction_id').focus();
                return false;
            }
        }

        // Auto-populate reference field from sales number if not already filled
        const salesNumberText = ($('#sales-number').text() || '').toString().trim();
        const currentReference = ($('#reference').val() || '').toString().trim();
        if (!currentReference && salesNumberText) {
            // Extract number from sales number (e.g., "SO #00004" -> "SO #00004")
            $('#reference').val(salesNumberText);
        }
        
        // Prepare items data with all required fields
        const itemsData = items.map(function(item) {
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
                entry_type: (item.entry_type != null && item.entry_type !== '') ? item.entry_type : 'purchase',
                supplier_id: item.supplier_id || null,
                is_zero_stock: item.is_zero_stock || false,
                warehouse_id: (item.warehouse_id != null && item.warehouse_id !== '') ? item.warehouse_id : null,
                mileage_id: (item.mileage_id != null && item.mileage_id !== '') ? item.mileage_id : '',
                mileage_name: (item.mileage_name != null && item.mileage_name !== '') ? String(item.mileage_name) : '',
                warranty_proofs: (item.warranty_proofs && Array.isArray(item.warranty_proofs)) ? item.warranty_proofs : null,
                line_note: (item.line_note != null && String(item.line_note).trim() !== '') ? String(item.line_note).trim() : '',
                line_image: (item.line_image != null && String(item.line_image).trim() !== '') ? String(item.line_image).trim() : '',
                temporary_item_name: (item.temporary_item_name != null && String(item.temporary_item_name).trim() !== '') ? String(item.temporary_item_name).trim() : '',
                temporary_quality: (item.temporary_quality != null && String(item.temporary_quality).trim() !== '') ? String(item.temporary_quality).trim() : '',
                voice_transcript: (item.voice_transcript != null && String(item.voice_transcript).trim() !== '') ? String(item.voice_transcript).trim() : '',
                voice_data: (item.voice_data != null && String(item.voice_data).trim() !== '') ? String(item.voice_data).trim() : ''
            };
        });

        // Debug: log payload for Claim In (verify item_id, entry_type, warehouse_id, quantity per line)
        console.log('Sale submit itemsData (for Claim In verify):', {
            branch_id: $('#salesBranchId').val(),
            customer_id: $('#customer_id').val(),
            items: itemsData.map(function(it) {
                return { item_id: it.item_id, entry_type: it.entry_type, warehouse_id: it.warehouse_id, quantity: it.quantity };
            })
        });

        // Add items to form
        const formData = new FormData(this);
        formData.append('submission_uuid', saleSubmitRequestUuid);
        itemsData.forEach(function(item, index) {
            Object.keys(item).forEach(function(key) {
                var val = item[key];
                if (key === 'warranty_proofs') {
                    if (val && Array.isArray(val)) {
                        val.forEach(function(p, j) {
                            if (!p) return;
                            formData.append(`items[${index}][warranty_proofs][${j}][unit_no]`, (p.unit_no != null ? p.unit_no : (j + 1)));
                            formData.append(`items[${index}][warranty_proofs][${j}][warehouse_id]`, (p.warehouse_id != null ? p.warehouse_id : ''));
                            formData.append(`items[${index}][warranty_proofs][${j}][code]`, (p.code != null ? p.code : ''));
                            formData.append(`items[${index}][warranty_proofs][${j}][image_data]`, (p.image_data != null ? p.image_data : ''));
                        });
                    }
                    return;
                }
                if ((key === 'mileage_id' || key === 'mileage_name') && (val == null || val === '')) {
                    return;
                }
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
                console.log(response.message || 'Sale saved:', response);
                if (response.success || response.message) {
                    var saleId = response.sale_id || (response.sale && response.sale.id) || null;
                    var thermalSettings = getThermalPrintSettings();
                    var isPrintIntent = submitIntent === 'print';

                    var finalizeSaleUi = function() {
                        restoreBtn();
                    };

                    if (!isPrintIntent) {
                        // Non-print flow: reset UI and go list page.
                        salesItems = [];
                        $('#items-tbody').empty();
                        $('#empty-items-state').show();
                        $('#items-list').hide();
                        $('#payment-section').hide(); $('#payment-amount-row').hide();
                        calculateTotals();
                        alert((response.message || (window.__IS_EDIT_SALE__ ? 'Sale updated successfully!' : 'Sale created successfully!')) + (response.invoice_no ? (' Invoice: ' + response.invoice_no) : ''));
                        finalizeSaleUi();
                        window.location.href = '{{ route("all_sales") }}';
                        return;
                    }

                    if (!saleId) {
                        finalizeSaleUi();
                        alert('Sale saved but no sale ID received for printing.');
                        return;
                    }

                    (async function() {
                        try {
                            var printResult = await runThermalPrintBySettings(saleId, thermalSettings);
                            finalizeSaleUi();
                            if (typeof Swal !== 'undefined' && printResult && !printResult.fallback) {
                                await Swal.fire({
                                    icon: 'success',
                                    title: 'Printed Successfully',
                                    text: 'Sale saved and receipt sent to printer.',
                                    confirmButtonText: 'OK'
                                });
                            } else if (typeof Swal !== 'undefined') {
                                await Swal.fire({
                                    icon: 'success',
                                    title: 'Saved',
                                    text: 'Sale saved once. Print command completed.',
                                    confirmButtonText: 'OK'
                                });
                            }
                            // Print flow requirement: keep current page/modal state as-is.
                            return;
                        } catch (printErr) {
                            finalizeSaleUi();
                            console.error('Thermal print failed:', printErr);
                            var msg = 'Sale saved but printing failed: ' + (printErr.message || printErr);
                            if (typeof Swal !== 'undefined') {
                                await Swal.fire({
                                    icon: 'warning',
                                    title: 'Print Failed',
                                    text: msg,
                                    confirmButtonText: 'Open Invoice'
                                });
                            } else {
                                alert(msg);
                            }
                            window.open(replaceSaleId(window.__SALE_PRINT_VIEW_TEMPLATE__, saleId), '_blank', 'noopener');
                            return;
                        }
                    })();
                } else {
                    restoreBtn();
                    alert(response.message || 'Sale created but with warnings.');
                    window.location.href = '{{ route("all_sales") }}';
                }
            },
            error: function(xhr) {
                restoreBtn();
                console.error('Sale creation error:', xhr);
                console.error('Response:', xhr.responseJSON);
                var errorMessage = 'Error saving sale. Please check the following:\n\n';
                
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
                } else {
                    errorMessage = 'Network or server error. Please try again.';
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sale Not Saved',
                        html: errorMessage.replace(/\n/g, '<br>'),
                        confirmButtonText: 'OK',
                        width: '600px'
                    });
                } else {
                    alert(errorMessage);
                }
            }
        });
        } catch (err) {
            restoreBtn();
            console.error('Save sale error:', err);
            showSaleNotification('Error', 'Something went wrong. Please try again. ' + (err.message || ''), 'error');
        }
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
            escapeMarkup: function (m) { return m; },
            matcher: function(params, data) {
                // Never show the placeholder option ("SEARCH PARTY NAME / VEHICLE #...") in the dropdown list
                if (!data.id) {
                    return null;
                }

                // If there is no search term, show all non-placeholder options
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
                    return '<button type="button" class="js-add-new-customer-trigger btn btn-primary btn-sm w-100 text-start" style="padding:7px 10px;font-weight:600;"><i class="ti ti-plus me-1"></i>Add New Customer</button>';
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });
        // When party dropdown opens, auto-focus the search box so user can type immediately
        $('#customer_id').on('select2:open', function () {
            setTimeout(function () {
                var searchField = document.querySelector('span.select2-container--open .select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            }, 50);
        });
        // Initial visibility for Ledger and Clear buttons
        if ($('#customer_id').val()) {
            $('#customer-ledger-btn').show();
            $('#customer-ledger-btn-mobile').show();
            $('#party-clear-btn').show();
        } else {
            $('#customer-ledger-btn').hide();
            $('#customer-ledger-btn-mobile').hide();
            $('#party-clear-btn').hide();
        }

        // Enable/disable "ADD SALE ITEM" based on party selection
        function updateAddSaleItemEnabledState() {
            const hasCustomer = !!String($('#customer_id').val() || '').trim();
            $('#add-new-item-btn').prop('disabled', !hasCustomer);
            $('#temporary-sale-btn').prop('disabled', !hasCustomer);
        }
        updateAddSaleItemEnabledState();
        $('#customer_id').on('change', updateAddSaleItemEnabledState);
    }
    
    // Helper: highlight search term in text (for Select2 templateResult)
    function highlightSearchInText(text, term) {
        if (!term || !text || typeof text !== 'string') return text;
        var safe = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        var esc = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var re = new RegExp('(' + esc + ')', 'gi');
        return safe.replace(re, '<mark class="select2-result-highlight">$1</mark>');
    }
    // Initialize Select2 for mobile dropdown – same search as party (PARTY NAME / VEHICLE # or mobile)
    if ($('#customer_mobile').length && $('#customer_mobile').is('select') && !$('#customer_mobile').hasClass('select2-hidden-accessible')) {
        $('#customer_mobile').select2({
            placeholder: 'SEARCH PARTY NAME / VEHICLE # or mobile...',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            dropdownParent: $('#customer_mobile').parent(),
            escapeMarkup: function(m) { return m; },
            templateResult: function(data) {
                if (!data.id) return data.text;
                var term = ($('.select2-container--open .select2-search__field').val() || '').trim();
                var text = (data.text || '').toString();
                return term ? highlightSearchInText(text, term) : text;
            },
            matcher: function(params, data) {
                if (!data.id) {
                    return null;
                }
                if ($.trim(params.term) === '') {
                    return data;
                }
                var term = $.trim(params.term).toLowerCase();
                if (data.text && data.text.toLowerCase().indexOf(term) !== -1) {
                    return data;
                }
                var $option = $(data.element);
                var searchText = $option.data('search-text') || '';
                if (searchText && searchText.indexOf(term) !== -1) {
                    return data;
                }
                var phone = ($option.data('phone') || '').toString().toLowerCase();
                if (phone && phone.indexOf(term) !== -1) {
                    return data;
                }
                return null;
            },
            language: {
                noResults: function() {
                    return '<button type="button" class="js-add-new-customer-trigger btn btn-primary btn-sm w-100 text-start" style="padding:7px 10px;font-weight:600;"><i class="ti ti-plus me-1"></i>Add New Customer</button>';
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });
        // When mobile dropdown opens, auto-focus the search box so user can type immediately
        $('#customer_mobile').on('select2:open', function () {
            setTimeout(function () {
                var searchField = document.querySelector('span.select2-container--open .select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            }, 50);
        });
    }
    // When "Add New Customer" is clicked in party or mobile dropdown: capture search, close dropdowns, open Add Customer modal
    $(document).on('click', '.js-add-new-customer-trigger', function(e) {
        e.preventDefault();
        e.stopPropagation();
        // Capture current search text from whichever Select2 is open (before closing)
        var searchVal = '';
        var $openSearch = $('.select2-container--open .select2-search__field');
        if ($openSearch.length) {
            searchVal = $openSearch.val() || '';
        }
        window.addCustomerFromSearch = (searchVal || '').trim();

        if ($('#customer_id').length && $('#customer_id').hasClass('select2-hidden-accessible')) {
            $('#customer_id').select2('close');
        }
        if ($('#customer_mobile').length && $('#customer_mobile').hasClass('select2-hidden-accessible')) {
            $('#customer_mobile').select2('close');
        }
        $('#addCustomerModal').modal('show');
    });
    // If user presses Enter in any party/mobile search box and "Add New Customer" is shown, trigger that button
    $(document).on('keydown', '.select2-search__field', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            var $open = $('.select2-container--open');
            if (!$open.length) return;
            // Only handle customer_id or customer_mobile dropdowns
            var controls = $(this).attr('aria-controls') || '';
            if (controls.indexOf('select2-customer_id-results') === -1 &&
                controls.indexOf('select2-customer_mobile-results') === -1) {
                return;
            }
            var $addBtn = $open.find('.js-add-new-customer-trigger');
            if ($addBtn.length) {
                e.preventDefault();
                $addBtn.trigger('click');
            }
        }
    });
    // When Add Customer modal opens (from sales page), set branch and prefill Name or Phone from last search
    $('#addCustomerModal').on('show.bs.modal', function() {
        const salesBranchId = $('#salesBranchId').val();
        if (salesBranchId && $('#customer_branch_id').length) {
            $('#customer_branch_id').val(salesBranchId);
        }

        var searchVal = (window.addCustomerFromSearch || '').trim();
        var isPhoneLike = /^[\d\s\-+()]+$/.test(searchVal) && searchVal.replace(/\D/g, '').length >= 5;
        var $nameInput = $('#addCustomerModal input[name="names[]"]').first();
        var $phoneInput = $('#addCustomerModal input[name="phones[]"]').first();

        if (searchVal) {
            if (isPhoneLike) {
                if ($phoneInput.length) {
                    $phoneInput.val(searchVal);
                    setTimeout(function() { $phoneInput.trigger('focus').select(); }, 50);
                }
            } else {
                if ($nameInput.length) {
                    $nameInput.val(searchVal);
                    setTimeout(function() { $nameInput.trigger('focus').select(); }, 50);
                }
            }
        } else if ($nameInput.length) {
            setTimeout(function() { $nameInput.trigger('focus'); }, 50);
        }
        window.addCustomerFromSearch = '';
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
        
        // Visible for Sale / Order; hidden for Estimate (handled by sale-status)
        if ($('#sale-status').val() !== 'estimate') {
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
            $('#payment-section').show();
            
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
            $('#payment-section').show();
        }
        
        console.log('Toggle switched to:', toggleState === 0 ? 'Sale (S)' : toggleState === 1 ? 'Estimate (E)' : 'Order (O)');
    });
    
    // Vehicle Management (vehicles global so form submit can read it)
    vehicles = []; // Array to store vehicles (from DB when customer selected + newly added)

    // Shared vehicle master (same CarManufacturer / CarModel as item "Add Vehicle" fitment)
    var SALES_VEHICLE_MAKES_URL = '{{ route("vehicle.master.makes") }}';
    var SALES_VEHICLE_MODELS_URL = '{{ route("vehicle.master.models") }}';
    var SALES_POST_MANUFACTURER_URL = '{{ route("post.car.manufacturer") }}';
    var SALES_POST_MODEL_URL = '{{ route("post.car.model") }}';

    function salesEscOpt(val) {
        return String(val == null ? '' : val).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    }

    function salesFillVehicleForm($makeSel, $modelSel, manufacturerId, modelId, makeNameFallback, modelNameFallback, onDone) {
        manufacturerId = manufacturerId ? String(manufacturerId) : '';
        modelId = modelId ? String(modelId) : '';
        $modelSel.prop('disabled', true).html('<option value="">' + salesEscOpt('— Select model —') + '</option>');
        $.get(SALES_VEHICLE_MAKES_URL, function(res) {
            if (!res || !res.success || !res.makes) {
                if (onDone) onDone();
                return;
            }
            var h = '<option value="">— Select make —</option>';
            res.makes.forEach(function(m) {
                h += '<option value="' + m.id + '">' + salesEscOpt(m.name) + '</option>';
            });
            $makeSel.html(h);
            if (manufacturerId) {
                $makeSel.val(manufacturerId);
            }
            if (!$makeSel.val() && makeNameFallback) {
                var target = String(makeNameFallback).trim().toLowerCase();
                $makeSel.find('option').each(function() {
                    if ($(this).text().trim().toLowerCase() === target) {
                        $makeSel.val($(this).attr('value'));
                        return false;
                    }
                });
            }
            var mid = $makeSel.val();
            if (!mid) {
                $modelSel.prop('disabled', false);
                if (onDone) onDone();
                return;
            }
            $.get(SALES_VEHICLE_MODELS_URL, { manufacturer_id: mid, context: 'sale' }, function(res2) {
                if (res2 && res2.success && res2.models) {
                    var h2 = '<option value="">— Select model —</option>';
                    res2.models.forEach(function(m) {
                        h2 += '<option value="' + m.id + '">' + salesEscOpt(m.name) + '</option>';
                    });
                    $modelSel.html(h2).prop('disabled', false);
                    if (modelId) {
                        $modelSel.val(modelId);
                    }
                    if (!$modelSel.val() && modelNameFallback) {
                        var t2 = String(modelNameFallback).trim().toLowerCase();
                        $modelSel.find('option').each(function() {
                            if ($(this).text().trim().toLowerCase() === t2) {
                                $modelSel.val($(this).attr('value'));
                                return false;
                            }
                        });
                    }
                } else {
                    $modelSel.prop('disabled', false);
                }
                if (onDone) onDone();
            }).fail(function() {
                $modelSel.prop('disabled', false);
                if (onDone) onDone();
            });
        }).fail(function() {
            if (onDone) onDone();
        });
    }

    function salesRefreshVehicleModelSelect2($modelSel, $modal) {
        if (!$.fn.select2 || !$modal || !$modal.length || !$modelSel || !$modelSel.length) return;
        if ($modelSel.hasClass('select2-hidden-accessible')) {
            try { $modelSel.select2('destroy'); } catch (e) {}
        }
        $modelSel.select2({
            placeholder: $modelSel.find('option:first').text(),
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            dropdownParent: $modal
        });
        salesRebindVehicleAddNewForSelect($modelSel.attr('id'), $modal);
    }

    function salesReloadModelsForMake($makeSel, $modelSel, selectModelId, $modal) {
        var mid = $makeSel.val();
        if ($modal && $modal.length && $modelSel.hasClass('select2-hidden-accessible')) {
            try { $modelSel.select2('destroy'); } catch (e) {}
        }
        $modelSel.html('<option value="">— Select model —</option>').prop('disabled', !mid);
        if (!mid) {
            if ($modal && $modal.length) {
                salesRefreshVehicleModelSelect2($modelSel, $modal);
            }
            return;
        }
        $.get(SALES_VEHICLE_MODELS_URL, { manufacturer_id: mid, context: 'sale' }, function(res2) {
            if (res2 && res2.success && res2.models) {
                var h2 = '<option value="">— Select model —</option>';
                res2.models.forEach(function(m) {
                    h2 += '<option value="' + m.id + '">' + salesEscOpt(m.name) + '</option>';
                });
                $modelSel.html(h2).prop('disabled', false);
                if (selectModelId) {
                    $modelSel.val(String(selectModelId));
                }
            }
            if ($modal && $modal.length) {
                salesRefreshVehicleModelSelect2($modelSel, $modal);
            }
        });
    }

    function salesDestroyVehiclePairSelect2(makeSel, modelSel) {
        [makeSel, modelSel].forEach(function(s) {
            var $x = $(s);
            if ($x.length && $x.hasClass('select2-hidden-accessible')) {
                try { $x.select2('destroy'); } catch (e) {}
            }
        });
    }

    function salesInitVehicleMasterSelect2($modal, makeSelector, modelSelector) {
        if (!$.fn.select2 || !$modal || !$modal.length) return;
        var $make = $(makeSelector);
        var $model = $(modelSelector);
        salesDestroyVehiclePairSelect2(makeSelector, modelSelector);
        if ($make.length) {
            $make.select2({
                placeholder: $make.find('option:first').text(),
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownParent: $modal
            });
        }
        if ($model.length) {
            $model.select2({
                placeholder: $model.find('option:first').text(),
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0,
                dropdownParent: $modal
            });
        }
        salesRebindVehicleAddNewForSelect($make.attr('id'), $modal);
        salesRebindVehicleAddNewForSelect($model.attr('id'), $modal);
    }

    function salesVehicleCheckAddNewRow(selectId, kind, $modal) {
        var $open = $('.select2-container--open');
        if (!$open.length) return;
        var $select = $('#' + selectId);
        if (!$select.length || !$select.next('.select2-container').is($open)) return;
        var $results = $open.find('.select2-results');
        var $msg = $open.find('.select2-results__message');
        var $opts = $open.find('.select2-results__option--selectable:not(.select2-results__option--loading)');
        var $search = $open.find('.select2-search__field');
        if (!$search.length) return;
        var q = ($search.val() || '').trim();
        $open.find('.sales-vehicle-add-new-wrap').remove();
        if (!q) {
            if ($msg.length) $msg.show();
            return;
        }
        if (kind === 'model') {
            var makeSelId = selectId.indexOf('edit-') === 0 ? 'edit-sales-vehicle-make' : 'sales-vehicle-make';
            if (!$('#' + makeSelId).val()) {
                if ($msg.length) $msg.hide();
                $results.append(
                    '<div class="select2-results__option select2-results__message sales-vehicle-add-new-wrap" role="alert">Please select a make first.</div>'
                );
                return;
            }
        }
        var hasNoResults = ($msg.length && $msg.is(':visible')) || ($opts.length === 0 && q.length > 0);
        if (!hasNoResults) {
            if ($msg.length) $msg.show();
            return;
        }
        if ($msg.length) $msg.hide();
        var label = kind === 'make' ? 'Add New Make' : 'Add New Model';
        var btn =
            '<div class="select2-results__option select2-results__option--add-new sales-vehicle-add-new-wrap" style="padding:10px;text-align:center;border-top:1px solid #dee2e6;">' +
            '<button type="button" class="btn btn-sm btn-primary w-100 sales-vehicle-inline-add" data-kind="' + kind + '" data-select-id="' + selectId.replace(/"/g, '&quot;') + '">' +
            label + ': "<span class="sales-v-add-term fw-semibold">' + salesEscOpt(q) + '</span>"' +
            '</button></div>';
        $results.append(btn);
    }

    function salesRebindVehicleAddNewForSelect(selectId, $modal) {
        if (!selectId || !$modal || !$modal.length) return;
        var $sel = $('#' + selectId);
        $sel.off('select2:open.salesVAdd');
        $sel.on('select2:open.salesVAdd', function() {
            var kind = selectId.indexOf('vehicle-model') !== -1 ? 'model' : 'make';
            setTimeout(function() {
                var $search = $('.select2-container--open .select2-search__field');
                $search.off('input.salesVAdd').on('input.salesVAdd', function() {
                    setTimeout(function() {
                        salesVehicleCheckAddNewRow(selectId, kind, $modal);
                    }, 120);
                });
                var iv = setInterval(function() {
                    if (!$('.select2-container--open').length) {
                        clearInterval(iv);
                        return;
                    }
                    salesVehicleCheckAddNewRow(selectId, kind, $modal);
                }, 200);
                $sel.one('select2:close', function() {
                    clearInterval(iv);
                    $search.off('input.salesVAdd');
                    $('.select2-container--open .sales-vehicle-add-new-wrap').remove();
                });
            }, 50);
        });
    }

    $(document).on('mousedown touchstart', '.sales-vehicle-inline-add', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $(document).on('click', '.sales-vehicle-inline-add', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var kind = $(this).data('kind');
        var selectId = $(this).data('select-id');
        var term = $(this).find('.sales-v-add-term').text().trim();
        if (!term) return;
        var token = $('meta[name="csrf-token"]').attr('content');
        if (kind === 'make') {
            $.post(SALES_POST_MANUFACTURER_URL, { _token: token, name: term })
                .done(function(res) {
                    if (res && res.id) {
                        var $sel = $('#' + selectId);
                        $sel.append('<option value="' + res.id + '">' + salesEscOpt(res.name) + '</option>').val(String(res.id)).trigger('change');
                        try { $sel.select2('close'); } catch (err) {}
                        if (typeof toastr !== 'undefined') toastr.success('Make added.');
                    }
                })
                .fail(function(xhr) {
                    var json = xhr.responseJSON || {};
                    if (json.existing_id) {
                        var $sel = $('#' + selectId);
                        var exists = false;
                        $sel.find('option[value="' + json.existing_id + '"]').each(function() { exists = true; });
                        if (!exists) {
                            $sel.append('<option value="' + json.existing_id + '">' + salesEscOpt(json.name || term) + '</option>');
                        }
                        $sel.val(String(json.existing_id)).trigger('change');
                        try { $sel.select2('close'); } catch (err) {}
                        if (typeof toastr !== 'undefined') toastr.info(json.message || 'Already exists; selected existing make.');
                        return;
                    }
                    var msg = json.message || 'Could not add make.';
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                    else alert(msg);
                });
            return;
        }
        if (kind === 'model') {
            var makeSelId = selectId.indexOf('edit-') === 0 ? 'edit-sales-vehicle-make' : 'sales-vehicle-make';
            var mid = $('#' + makeSelId).val();
            if (!mid) {
                if (typeof toastr !== 'undefined') toastr.warning('Please select a make first.');
                else alert('Please select a make first.');
                return;
            }
            $.post(SALES_POST_MODEL_URL, { _token: token, name: term, car_manufacturer_id: mid })
                .done(function(res) {
                    if (res && res.id) {
                        var $modal = selectId.indexOf('edit-') === 0 ? $('#editVehicleSaleModal') : $('#vehicle-modal');
                        salesReloadModelsForMake($('#' + makeSelId), $('#' + selectId), res.id, $modal);
                        try { $('#' + selectId).select2('close'); } catch (err) {}
                        if (typeof toastr !== 'undefined') toastr.success('Model added.');
                    }
                })
                .fail(function(xhr) {
                    var json = xhr.responseJSON || {};
                    if (json.existing_id) {
                        var $modal = selectId.indexOf('edit-') === 0 ? $('#editVehicleSaleModal') : $('#vehicle-modal');
                        salesReloadModelsForMake($('#' + makeSelId), $('#' + selectId), json.existing_id, $modal);
                        try { $('#' + selectId).select2('close'); } catch (err) {}
                        if (typeof toastr !== 'undefined') toastr.info(json.message || 'Already exists; selected existing model.');
                        return;
                    }
                    var msg = json.message || 'Could not add model.';
                    if (typeof toastr !== 'undefined') toastr.error(msg);
                    else alert(msg);
                });
        }
    });

    $(document).on('change', '#sales-vehicle-make', function() {
        salesReloadModelsForMake($('#sales-vehicle-make'), $('#sales-vehicle-model'), null, $('#vehicle-modal'));
    });
    $(document).on('change', '#edit-sales-vehicle-make', function() {
        salesReloadModelsForMake($('#edit-sales-vehicle-make'), $('#edit-sales-vehicle-model'), null, $('#editVehicleSaleModal'));
    });

    $('#sales-vehicle-add-make-btn').on('click', function() {
        var name = window.prompt('New make / brand name:');
        if (!name || !String(name).trim()) return;
        var token = $('meta[name="csrf-token"]').attr('content');
        $.post(SALES_POST_MANUFACTURER_URL, { _token: token, name: String(name).trim() })
            .done(function(res) {
                if (res && res.id) {
                    $('#sales-vehicle-make').append('<option value="' + res.id + '">' + salesEscOpt(res.name) + '</option>').val(String(res.id)).trigger('change');
                    if (typeof toastr !== 'undefined') toastr.success('Make added.');
                }
            })
            .fail(function(xhr) {
                var json = xhr.responseJSON || {};
                if (json.existing_id) {
                    var $sel = $('#sales-vehicle-make');
                    var exists = $sel.find('option[value="' + json.existing_id + '"]').length > 0;
                    if (!exists) {
                        $sel.append('<option value="' + json.existing_id + '">' + salesEscOpt(json.name || name) + '</option>');
                    }
                    $sel.val(String(json.existing_id)).trigger('change');
                    if (typeof toastr !== 'undefined') toastr.info(json.message || 'Already exists; selected existing make.');
                    return;
                }
                var msg = json.message || 'Could not add make.';
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
            });
    });
    $('#edit-sales-vehicle-add-make-btn').on('click', function() {
        var name = window.prompt('New make / brand name:');
        if (!name || !String(name).trim()) return;
        var token = $('meta[name="csrf-token"]').attr('content');
        $.post(SALES_POST_MANUFACTURER_URL, { _token: token, name: String(name).trim() })
            .done(function(res) {
                if (res && res.id) {
                    $('#edit-sales-vehicle-make').append('<option value="' + res.id + '">' + salesEscOpt(res.name) + '</option>').val(String(res.id)).trigger('change');
                    if (typeof toastr !== 'undefined') toastr.success('Make added.');
                }
            })
            .fail(function(xhr) {
                var json = xhr.responseJSON || {};
                if (json.existing_id) {
                    var $sel = $('#edit-sales-vehicle-make');
                    var exists = $sel.find('option[value="' + json.existing_id + '"]').length > 0;
                    if (!exists) {
                        $sel.append('<option value="' + json.existing_id + '">' + salesEscOpt(json.name || name) + '</option>');
                    }
                    $sel.val(String(json.existing_id)).trigger('change');
                    if (typeof toastr !== 'undefined') toastr.info(json.message || 'Already exists; selected existing make.');
                    return;
                }
                var msg = json.message || 'Could not add make.';
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
            });
    });
    $('#add-model-btn').on('click', function() {
        var mid = $('#sales-vehicle-make').val();
        if (!mid) {
            if (typeof toastr !== 'undefined') toastr.warning('Please select a make first.');
            else alert('Please select a make first.');
            return;
        }
        var name = window.prompt('New model name:');
        if (!name || !String(name).trim()) return;
        var token = $('meta[name="csrf-token"]').attr('content');
        $.post(SALES_POST_MODEL_URL, { _token: token, name: String(name).trim(), car_manufacturer_id: mid })
            .done(function(res) {
                if (res && res.id) {
                    salesReloadModelsForMake($('#sales-vehicle-make'), $('#sales-vehicle-model'), res.id, $('#vehicle-modal'));
                    if (typeof toastr !== 'undefined') toastr.success('Model added.');
                }
            })
            .fail(function(xhr) {
                var json = xhr.responseJSON || {};
                if (json.existing_id) {
                    salesReloadModelsForMake($('#sales-vehicle-make'), $('#sales-vehicle-model'), json.existing_id, $('#vehicle-modal'));
                    if (typeof toastr !== 'undefined') toastr.info(json.message || 'Already exists; selected existing model.');
                    return;
                }
                var msg = json.message || 'Could not add model.';
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
            });
    });
    $('#edit-sales-vehicle-add-model-btn').on('click', function() {
        var mid = $('#edit-sales-vehicle-make').val();
        if (!mid) {
            if (typeof toastr !== 'undefined') toastr.warning('Please select a make first.');
            else alert('Please select a make first.');
            return;
        }
        var name = window.prompt('New model name:');
        if (!name || !String(name).trim()) return;
        var token = $('meta[name="csrf-token"]').attr('content');
        $.post(SALES_POST_MODEL_URL, { _token: token, name: String(name).trim(), car_manufacturer_id: mid })
            .done(function(res) {
                if (res && res.id) {
                    salesReloadModelsForMake($('#edit-sales-vehicle-make'), $('#edit-sales-vehicle-model'), res.id, $('#editVehicleSaleModal'));
                    if (typeof toastr !== 'undefined') toastr.success('Model added.');
                }
            })
            .fail(function(xhr) {
                var json = xhr.responseJSON || {};
                if (json.existing_id) {
                    salesReloadModelsForMake($('#edit-sales-vehicle-make'), $('#edit-sales-vehicle-model'), json.existing_id, $('#editVehicleSaleModal'));
                    if (typeof toastr !== 'undefined') toastr.info(json.message || 'Already exists; selected existing model.');
                    return;
                }
                var msg = json.message || 'Could not add model.';
                if (typeof toastr !== 'undefined') toastr.error(msg);
                else alert(msg);
            });
    });
    
    function saleResolvedOilTargetKmString(obj) {
        if (!obj || typeof obj !== 'object') return '';
        var candidates = [obj.last_service_next_km, obj.next_km, obj.previous_target_next_km];
        for (var i = 0; i < candidates.length; i++) {
            var raw = candidates[i];
            if (raw === undefined || raw === null) continue;
            var s = String(raw).replace(/,/g, '').replace(/\s/g, '').trim();
            if (s === '' || s.toLowerCase() === 'null' || s.toLowerCase() === 'undefined') continue;
            var n = parseFloat(s);
            if (!isFinite(n) || n < 0) continue;
            return String(Math.round(n));
        }
        return '';
    }

    function saleResolvedLastVisitCurrentKmString(car) {
        if (!car || typeof car !== 'object') return '';
        var candidates = [car.last_service_current_km, car.current_km, car.previous_current_km];
        for (var i = 0; i < candidates.length; i++) {
            var raw = candidates[i];
            if (raw === undefined || raw === null) continue;
            var s = String(raw).replace(/,/g, '').replace(/\s/g, '').trim();
            if (s === '' || s.toLowerCase() === 'null' || s.toLowerCase() === 'undefined') continue;
            var n = parseFloat(s);
            if (!isFinite(n) || n < 0) continue;
            return String(Math.round(n));
        }
        return '';
    }

    // Load customer's vehicles from database and show below Add Vehicle button
    function loadCustomerVehicles(customerId) {
        if (!customerId) {
            vehicles = [];
            if (typeof displayVehicles === 'function') displayVehicles();
            return;
        }
        $.ajax({
            url: '{{ url("/customers") }}/' + encodeURIComponent(String(customerId)) + '/vehicles',
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(res) {
                var rawList = res && res.vehicles;
                if (rawList && !Array.isArray(rawList) && typeof rawList === 'object') {
                    rawList = Object.keys(rawList).sort(function(a, b) { return parseInt(a, 10) - parseInt(b, 10); }).map(function(k) { return rawList[k]; });
                }
                if (res && res.success && Array.isArray(rawList)) {
                    vehicles = rawList.map(function(car) {
                        var lastNextKm = saleResolvedOilTargetKmString(car);
                        var lastVisitKm = saleResolvedLastVisitCurrentKmString(car);
                        return {
                            id: 'db-' + car.id,
                            dbId: car.id,
                            customerId: String(car.customerId),
                            plateNumber: car.plateNumber || '',
                            make: car.make || '',
                            model: car.model || '',
                            year: car.year || '',
                            car_manufacturer_id: car.car_manufacturer_id != null ? car.car_manufacturer_id : null,
                            car_model_id: car.car_model_id != null ? car.car_model_id : null,
                            oil_capacity: car.oil_capacity || '',
                            current_km: '',
                            daily_run_km: (car.daily_run_km != null && car.daily_run_km !== '') ? String(Math.round(parseFloat(car.daily_run_km))) : '',
                            next_date: car.next_date || '',
                            next_km: lastNextKm,
                            interval_days: (car.interval_days != null && car.interval_days !== '') ? String(Math.round(parseFloat(car.interval_days))) : '',
                            interval_months: (car.interval_months != null && car.interval_months !== '') ? String(Math.round(parseFloat(car.interval_months))) : '',
                            previous_target_next_km: lastNextKm,
                            previous_current_km: lastVisitKm,
                            last_visit_date: (car.last_visit_date != null && car.last_visit_date !== '') ? String(car.last_visit_date).substring(0, 10) : ''
                        };
                    });
                } else {
                    vehicles = [];
                }
                // Name/mobile search sets no plate — default to first vehicle so cards match "vehicle line" selection (one expanded + metrics)
                var sp = (window.selectedVehiclePlate != null && window.selectedVehiclePlate !== undefined) ? String(window.selectedVehiclePlate).trim().replace(/\s+/g, '') : '';
                if (vehicles.length > 0 && !sp) {
                    window.selectedVehiclePlate = vehicles[0].plateNumber || null;
                }
                if (typeof displayVehicles === 'function') displayVehicles();
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
    });
    
    // Move vehicle modal to body when shown (so it is clickable)
    $('#vehicle-modal').on('show.bs.modal', function() {
        $('#vehicle-modal').appendTo('body');
        if ($('#vehicle-form')[0]) {
            $('#vehicle-form')[0].reset();
        }
        $('#sales-vehicle-make').html('<option value="">— Select make —</option>');
        $('#sales-vehicle-model').html('<option value="">— Select model —</option>').prop('disabled', true);
    });
    $('#vehicle-modal').on('shown.bs.modal', function() {
        $('#vehicle-modal').css({ 'pointer-events': 'auto', 'z-index': 9999 });
        $('#vehicle-modal').find('.modal-dialog, .modal-content, .modal-body, .modal-footer, .modal-header').css('pointer-events', 'auto');
        var $backdrop = $('.modal-backdrop').last();
        if ($backdrop.length) $('#vehicle-modal').insertAfter($backdrop);
        salesFillVehicleForm($('#sales-vehicle-make'), $('#sales-vehicle-model'), null, null, '', '', function() {
            salesInitVehicleMasterSelect2($('#vehicle-modal'), '#sales-vehicle-make', '#sales-vehicle-model');
        });
    });
    $('#vehicle-modal').on('hidden.bs.modal', function() {
        salesDestroyVehiclePairSelect2('#sales-vehicle-make', '#sales-vehicle-model');
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
        
        const plateNumber = ($('#vehicle-plate-number').val() || '').toString().trim().toUpperCase();
        const makeId = ($('#sales-vehicle-make').val() || '').toString().trim();
        const modelId = ($('#sales-vehicle-model').val() || '').toString().trim();
        const make = ($('#sales-vehicle-make option:selected').text() || '').toString().trim();
        const model = ($('#sales-vehicle-model option:selected').text() || '').toString().trim();
        const year = ($('#vehicle-year').val() || '').toString().trim();
        
        // Validation
        if (!plateNumber || !makeId || !modelId || !year) {
            alert('Please fill in plate, make, model, and year');
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
            car_manufacturer_id: makeId,
            car_model_id: modelId,
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
                        existingInList.car_manufacturer_id = makeId ? parseInt(makeId, 10) : null;
                        existingInList.car_model_id = modelId ? parseInt(modelId, 10) : null;
                        if (res.vehicle && res.vehicle.id) existingInList.dbId = res.vehicle.id;
                    } else {
                        vehicles.push({
                            id: Date.now(),
                            dbId: (res.vehicle && res.vehicle.id) ? res.vehicle.id : null,
                            customerId: customerId,
                            plateNumber: plateNumber,
                            make: make,
                            model: model,
                            year: year,
                            car_manufacturer_id: makeId ? parseInt(makeId, 10) : null,
                            car_model_id: modelId ? parseInt(modelId, 10) : null
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
            if ($('#vehicle-form')[0]) $('#vehicle-form')[0].reset();
        } else {
            if ($('#vehicle-form')[0]) $('#vehicle-form')[0].reset();
            salesFillVehicleForm($('#sales-vehicle-make'), $('#sales-vehicle-model'), null, null, '', '', function() {
                salesInitVehicleMasterSelect2($('#vehicle-modal'), '#sales-vehicle-make', '#sales-vehicle-model');
                $('#vehicle-plate-number').focus();
            });
            return true;
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
    
    // Display vehicles: show ACTIVE VEHICLES whenever a party is selected (name, mobile, or vehicle#); empty state if none saved yet
    function displayVehicles() {
        const $vehiclesList = $('#vehicles-list');
        const $displaySection = $('#vehicle-display-section');
        const customerIdSel = $('#customer_id').val();

        if (!customerIdSel) {
            $displaySection.hide();
            $vehiclesList.empty();
            $('#vehicle-primary-metrics-host').empty();
            $('#vehicle-primary-metrics-panel').hide().removeClass('d-block');
            return;
        }

        $displaySection.show();

        if (vehicles.length === 0) {
            $('#vehicle-primary-metrics-host').empty();
            $('#vehicle-primary-metrics-panel').hide().removeClass('d-block');
            $vehiclesList.html(
                '<div class="rounded py-4 px-3 text-center text-muted" style="grid-column: 1 / -1; background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 12px;">' +
                '<p class="mb-1 fw-semibold" style="color: #64748b;">No vehicle linked yet for this party.</p>' +
                '<p class="mb-0 small">Use <strong class="text-primary">ADD / LINK VEHICLE DETAILS</strong> below to add plate, make & model.</p>' +
                '</div>'
            );
            setTimeout(function() {
                if (typeof updateVehicleInputsPermission === 'function') updateVehicleInputsPermission();
            }, 0);
            return;
        }

        $('#vehicle-primary-metrics-host').empty();
        $vehiclesList.empty();
        
        const selectedPlateRaw = window.selectedVehiclePlate ? String(window.selectedVehiclePlate).trim().toUpperCase() : null;
        const selectedPlate = selectedPlateRaw ? selectedPlateRaw.replace(/\s+/g, '') : null;
        const norm = function(plate) { return (plate || '').toString().trim().toUpperCase().replace(/\s+/g, ''); };
        let ordered = vehicles.slice();
        if (selectedPlate) {
            const idx = ordered.findIndex(function(v) { return norm(v.plateNumber) === selectedPlate; });
            if (idx !== -1) {
                const selected = ordered[idx];
                ordered = ordered.filter(function(_, i) { return i !== idx; });
                ordered.push(selected);
            }
        }
        ordered.forEach(function(vehicle) {
            const plateNorm = norm(vehicle.plateNumber);
            const isSelectedVehicle = !!(selectedPlate ? (plateNorm === selectedPlate) : (ordered.length === 1));
            
            const dbId = vehicle.dbId || vehicle.id;
            const canEdit = !!vehicle.dbId;
            const selectedClass = isSelectedVehicle ? ' vehicle-card--selected' : '';
            const metricsDisplay = 'block';
            const previousTargetKm = saleResolvedOilTargetKmString(vehicle);
            const targetNum = previousTargetKm !== '' ? parseFloat(previousTargetKm) : NaN;
            const showLastTarget = previousTargetKm !== '' && isFinite(targetNum) && targetNum >= 0;
            const lastTargetFormatted = showLastTarget ? (Math.round(targetNum).toLocaleString(undefined, { maximumFractionDigits: 0 }) || previousTargetKm) : '';
            const savedDailyRunKm = (vehicle.daily_run_km != null && vehicle.daily_run_km !== '') ? String(vehicle.daily_run_km).replace(/,/g, '').trim() : '';
            const previousCurrentKm = (vehicle.previous_current_km != null && vehicle.previous_current_km !== '') ? String(vehicle.previous_current_km).replace(/,/g, '').trim() : '';
            const previousNextDate = (vehicle.next_date != null && vehicle.next_date !== '') ? String(vehicle.next_date).substring(0, 10) : '';
            const lastVisitDate = (vehicle.last_visit_date != null && vehicle.last_visit_date !== '') ? String(vehicle.last_visit_date).substring(0, 10) : '';
            const isReturningVehicle = lastVisitDate !== '';
            let lastOilChangeDateDisplay = '—';
            if (lastVisitDate && lastVisitDate.length >= 10) {
                var _p = lastVisitDate.split('-');
                if (_p.length >= 3) {
                    var _d = new Date(parseInt(_p[0], 10), parseInt(_p[1], 10) - 1, parseInt(_p[2], 10));
                    if (!isNaN(_d.getTime())) lastOilChangeDateDisplay = _d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
                }
            }
            const vehicleCard = `
                <div class="card mb-3 vehicle-card${selectedClass}" data-vehicle-id="${vehicle.id}" data-db-id="${dbId}" data-customer-id="${vehicle.customerId || ''}" data-plate-norm="${plateNorm}" data-db-saved-target-km="${previousTargetKm}" data-previous-target-km="${previousTargetKm}" data-saved-daily-run-km="${savedDailyRunKm}" data-previous-current-km="${previousCurrentKm}" data-previous-next-date="${previousNextDate}" data-last-visit-date="${lastVisitDate}" style="border: 1px solid #e0e0e0; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); background: #f8f9fa; transition: all 0.3s ease;">
                    <div class="card-body p-3">
                        <!-- Top Section - Clickable -->
                        <div class="position-relative mb-0 vehicle-header" style="cursor: pointer;" data-vehicle-id="${vehicle.id}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="vehicle-display-plate" style="flex: 1;">
                                    <p class="mb-1 fw-bold text-uppercase" style="color: #4a90e2; font-size: 11px; letter-spacing: 0.5px;">ACTIVE VEHICLE</p>
                                    <h5 class="mb-1 fw-bold vehicle-plate-text" style="color: #1e3a8a; font-size: 18px; text-transform: uppercase;">${(vehicle.plateNumber || '').toUpperCase()}</h5>
                                    <p class="mb-0 fw-semibold vehicle-make-model-text" style="color: #1e3a8a; font-size: 14px;">${[ (vehicle.make || '').toUpperCase(), (vehicle.model || '').toUpperCase(), vehicle.year ].filter(Boolean).join(' - ')}</p>
                                    <p class="mb-0 mt-2 vehicle-last-oil-target-line" style="font-size: 14px; font-weight: 700; color: #0f766e;"><span class="text-muted" style="font-size: 11px; font-weight: 600;">Last Oil Change Target:</span> <span class="vehicle-last-oil-target-value">${showLastTarget ? (lastTargetFormatted + ' KM') : '—'}</span></p>
                                    <p class="mb-0 mt-1 vehicle-last-oil-date-line" style="font-size: 13px; font-weight: 700; color: #0f766e;"><span class="text-muted" style="font-size: 11px; font-weight: 600;">Last Oil Change Date:</span> <span class="vehicle-last-oil-change-date-value">${lastOilChangeDateDisplay}</span></p>
                                    <div class="vehicle-selected-mileage mt-1" style="display:none; font-size: 12px; color:#6b7280; font-weight:600;">
                                        MILEAGE: <span class="vehicle-selected-mileage-value"></span>
                                    </div>
                                </div>
                                <div class="d-flex gap-1 vehicle-actions">
                                    ${(isReturningVehicle) ? `<button type="button" class="btn btn-sm btn-outline-success repeat-bill-btn" data-vehicle-id="${vehicle.id}" data-customer-id="${vehicle.customerId || ''}" data-plate="${(vehicle.plateNumber || '').toString().replace(/"/g, '&quot;')}" style="border-radius: 999px; padding: 0 10px; height: 28px; display: inline-flex; align-items: center; justify-content: center; z-index: 10;" title="Load last service bill"><i class="ti ti-repeat me-1" style="font-size: 13px;"></i><span style="font-size: 11px; font-weight: 700;">Repeat Bill</span></button>` : ''}
                                    ${canEdit ? `<button type="button" class="btn btn-sm btn-outline-primary edit-vehicle-sale-btn" data-db-id="${vehicle.dbId}" data-customer-id="${vehicle.customerId || ''}" data-plate="${(vehicle.plateNumber || '').toString().replace(/"/g, '&quot;')}" data-make="${(vehicle.make || '').toString().replace(/"/g, '&quot;')}" data-model="${(vehicle.model || '').toString().replace(/"/g, '&quot;')}" data-year="${(vehicle.year || '').toString().replace(/"/g, '&quot;')}" data-car-manufacturer-id="${vehicle.car_manufacturer_id != null ? vehicle.car_manufacturer_id : ''}" data-car-model-id="${vehicle.car_model_id != null ? vehicle.car_model_id : ''}" style="border-radius: 50%; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; z-index: 10;" title="Edit vehicle"><i class="ti ti-edit" style="font-size: 14px;"></i></button>` : ''}
                                    <button type="button" class="btn btn-sm remove-vehicle-btn" data-vehicle-id="${vehicle.id}" style="background: #dc3545; color: white; border-radius: 50%; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; border: none; z-index: 10;">
                                        <i class="ti ti-x" style="font-size: 14px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bottom Section - Metrics (always visible on every card) -->
                        <div class="vehicle-metrics" data-vehicle-id="${vehicle.id}" style="display: ${metricsDisplay};">
                            <!-- Shown when no oil item in cart – blocks typing in Current KM etc. -->
                            <div class="vehicle-metrics-oil-required-notice alert alert-warning py-2 px-3 mb-2 d-none" role="alert" style="font-size: 13px;">
                                <i class="ti ti-info-circle me-1"></i> Add an oil-type item in the cart above to enter Current KM and service data.
                            </div>
                            <!-- Separator Line -->
                            <hr style="margin: 12px 0; border-top: 1px solid #e0e0e0; opacity: 0.5;">
                            <!-- Previous target + Oil status: Timely / Due now / Late -->
                            <div class="vehicle-oil-target-banner rounded p-2 mb-2 d-none" style="border: 1px solid #e0e0e0;">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="vehicle-oil-target-prev text-muted small">Previous Target KM: <strong class="vehicle-previous-target-km">--</strong></span>
                                    <span class="vehicle-oil-target-current text-muted small">Current Reading: <strong class="vehicle-current-reading-km">--</strong></span>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="vehicle-target-status-badge badge px-2 py-1">--</span>
                                    <span class="vehicle-target-status-text small"></span>
                                </div>
                                <div class="vehicle-high-usage-notice mt-2 p-2 rounded d-none small" style="background: #fef3c7; border: 1px solid #f59e0b;"></div>
                            </div>
                            <!-- Metrics Boxes -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="rounded p-2" style="background: #e8f4f8; border: 1px solid #b3d9e6;">
                                        <p class="mb-1 text-uppercase" style="color: #4a90e2; font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">CURRENT KM</p>
                                        <input type="text" class="form-control form-control-sm border-0 p-0 bg-transparent vehicle-current-km-input" data-vehicle-id="${vehicle.id}" placeholder="..." value="${vehicle.current_km || ''}" style="color: #1e3a8a; font-size: 12px; font-weight: bold;">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="rounded p-2" style="background: #f3e8ff; border: 1px solid #d4b3ff;">
                                        <p class="mb-1 text-uppercase" style="color: #9333ea; font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">DAILY RUN KM</p>
                                        <input type="text" class="form-control form-control-sm border-0 p-0 bg-transparent vehicle-daily-run-km-input" data-vehicle-id="${vehicle.id}" placeholder="KM..." value="${vehicle.daily_run_km || ''}" style="color: #1e3a8a; font-size: 12px; font-weight: bold;">
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-4">
                                    <div class="rounded p-2" style="background: #ecfdf3; border: 1px solid #bbf7d0;">
                                        <p class="mb-1 text-uppercase" style="color: #166534; font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">NEXT KM</p>
                                        <div class="small fw-bold vehicle-next-km-output" style="color:#166534;">--</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="rounded p-2" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                                        <p class="mb-1 text-uppercase" style="color: #1d4ed8; font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">NEXT DATE</p>
                                        <input type="date" class="form-control form-control-sm border-0 p-0 bg-transparent vehicle-next-date-input" data-vehicle-id="${vehicle.id}" value="${(vehicle.next_date || '').toString().substring(0, 10)}" style="color:#1d4ed8; font-size: 12px; font-weight: bold;">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="rounded p-2" style="background: #fef3c7; border: 1px solid #fde68a;">
                                        <p class="mb-1 text-uppercase" style="color: #92400e; font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">INTERVAL (D/M)</p>
                                        <input type="number" min="0" step="0.01" class="vehicle-interval-days-input d-none" data-vehicle-id="${vehicle.id}" aria-hidden="true">
                                        <input type="number" min="0" step="0.01" class="vehicle-interval-months-input d-none" data-vehicle-id="${vehicle.id}" aria-hidden="true">
                                        <div class="vehicle-interval-display d-flex flex-column gap-1" style="color:#92400e; font-size: 12px; font-weight: 600;">
                                            <div><span class="vehicle-interval-days-display">--</span></div>
                                            <div><span class="vehicle-interval-months-display">--</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $vehiclesList.append(vehicleCard);
        });
        setTimeout(function() {
            if (typeof mountPrimaryVehicleMetricsPanel === 'function') mountPrimaryVehicleMetricsPanel();
            if (typeof refreshAllVehicleOilPlans === 'function') refreshAllVehicleOilPlans();
            if (typeof updateVehicleInputsPermission === 'function') updateVehicleInputsPermission();
        }, 0);
    }
    
    // Remove vehicle
    $(document).on('click', '.remove-vehicle-btn', function(e) {
        e.stopPropagation(); // Prevent triggering the header click
        const vehicleId = $(this).data('vehicle-id');
        if (confirm('Are you sure you want to remove this vehicle?')) {
            vehicles = vehicles.filter(function(v) { return String(v.id) !== String(vehicleId); });
            displayVehicles();
        }
    });

    function mapRepeatBillLineToCartItem(line, fallbackWarehouseName) {
        var qty = parseFloat(line.quantity || 0);
        if (!isFinite(qty) || qty <= 0) qty = 1;
        var rate = parseFloat(line.rate || 0);
        if (!isFinite(rate)) rate = 0;
        var discount = parseFloat(line.discount || 0);
        if (!isFinite(discount)) discount = 0;
        var taxPct = parseFloat(line.tax_percentage || 0);
        if (!isFinite(taxPct)) taxPct = 0;
        var taxAmt = parseFloat(line.tax_amount || 0);
        if (!isFinite(taxAmt)) taxAmt = 0;
        var total = parseFloat(line.total || 0);
        if (!isFinite(total)) total = (qty * rate) - discount + taxAmt;
        var entry = (line.entry_type || 'sale').toString().trim().toLowerCase();
        if (entry === 'purchase') entry = 'sale';
        return {
            id: itemCounter++,
            item_id: line.item_id,
            name: line.name || ('Item #' + line.item_id),
            item_type: line.item_type || null,
            part_number: line.part_number || null,
            quality_name: line.quality_name || null,
            company_name: line.company_name || null,
            category_name: line.category_name || null,
            product_type_label: line.product_type_label || null,
            product_title: line.product_title || null,
            quantity: qty,
            quantity_display: null,
            unit: line.unit || 'Unit',
            rate: rate,
            discount: discount,
            tax_percentage: taxPct,
            tax_amount: taxAmt,
            total: total,
            warranty: line.warranty || null,
            entry_type: entry,
            supplier_id: null,
            is_zero_stock: false,
            warehouse_id: (line.warehouse_id != null && line.warehouse_id !== '') ? String(line.warehouse_id) : null,
            warehouse_name: line.warehouse_name || fallbackWarehouseName || null,
            branch_name: ($('#selectedBranchName').text() || '').trim() || null,
            quantity_cans: null,
            quantity_base_liters: null,
            quantity_extra_ml: null,
            mileage_id: null,
            mileage_name: null,
            image: null,
            warranty_proofs: null
        };
    }

    function applyRepeatBillItems(resp, fallbackWarehouseName) {
        var lines = (resp && Array.isArray(resp.items)) ? resp.items : [];
        if (!lines.length) return false;
        salesItems = [];
        $('#items-tbody').empty();
        lines.forEach(function(line) {
            var row = mapRepeatBillLineToCartItem(line, fallbackWarehouseName);
            salesItems.push(row);
            addItemToTable(row);
        });
        $('#empty-items-state').hide();
        $('#items-list').show();
        calculateTotals();
        if (typeof syncCartToServer === 'function') syncCartToServer();
        if (typeof updateSalesPrintButton === 'function') updateSalesPrintButton();
        syncVehicleMileageFromFirstSaleItem();
        if (typeof updateVehicleInputsPermission === 'function') updateVehicleInputsPermission();
        if (salesItems.length > 0 && $('#sale-status').val() !== 'estimate') {
            $('#payment-section').show();
            if ($('#payment-amount-row').length) $('#payment-amount-row').show();
        }
        return true;
    }

    $(document).on('click', '.repeat-bill-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var customerId = ($('#customer_id').val() || $btn.data('customer-id') || '').toString().trim();
        var plate = ($btn.data('plate') || '').toString().trim();
        var branchId = ($('#salesBranchId').val() || '').toString().trim();
        if (!customerId) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Select party first', text: 'Please select a customer before repeating bill.' });
            return;
        }
        var doLoad = function() {
            $btn.prop('disabled', true);
            $.ajax({
                url: '{{ route("sales.repeat.latest") }}',
                method: 'GET',
                data: { customer_id: customerId, plate_number: plate, branch_id: branchId || null },
                success: function(resp) {
                    var fallbackWarehouseName = null;
                    try { fallbackWarehouseName = ($('body').data('currentWarehouseName') || '').toString().trim() || null; } catch (e2) {}
                    var ok = applyRepeatBillItems(resp, fallbackWarehouseName);
                    if (!ok) {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', title: 'No items found', text: 'Last bill has no repeatable items.' });
                        return;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Repeat Bill Loaded',
                            text: (resp.reference ? ('Loaded from ' + resp.reference) : 'Last service bill loaded successfully.')
                        });
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not load last service bill.';
                    if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Repeat Bill unavailable', text: msg });
                    else alert(msg);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        };

        if (salesItems && salesItems.length > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: 'Replace current cart?',
                    text: 'Current cart items will be replaced with last service bill items.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, load repeat bill',
                    cancelButtonText: 'Cancel'
                }).then(function(res) {
                    if (res.isConfirmed) doLoad();
                });
            } else if (confirm('Current cart items will be replaced. Continue?')) {
                doLoad();
            }
            return;
        }
        doLoad();
    });
    
    // Update vehicle metrics from editable inputs (keep in sync with vehicles array)
    $(document).on('input change', '.vehicle-current-km-input, .vehicle-daily-run-km-input, .vehicle-oil-capacity-input, .vehicle-interval-days-input, .vehicle-interval-months-input, .vehicle-next-date-input', function() {
        const vehicleId = $(this).data('vehicle-id');
        const $input = $(this);
        const val = ($input.val() != null ? String($input.val()) : '').trim();
        let name = 'oil_capacity';
        if ($input.hasClass('vehicle-current-km-input')) {
            name = 'current_km';
        } else if ($input.hasClass('vehicle-daily-run-km-input')) {
            name = 'daily_run_km';
        } else if ($input.hasClass('vehicle-interval-days-input')) {
            name = 'interval_days';
        } else if ($input.hasClass('vehicle-interval-months-input')) {
            name = 'interval_months';
        } else if ($input.hasClass('vehicle-next-date-input')) {
            name = 'next_date';
        }
        const v = vehicles.find(function(ve) { return String(ve.id) === String(vehicleId); });
        if (v) v[name] = val;

        const $metrics = $input.closest('.vehicle-metrics');
        if (!$metrics.length) return;
        if ($input.hasClass('vehicle-current-km-input')) {
            updateNextKmOnly($metrics);
            updateVehicleTargetStatus($metrics);
            updateVehicleReminderFromCurrentKm($metrics);
        } else if ($input.hasClass('vehicle-next-date-input')) {
            updateFromNextDate($metrics);
            if (v && $metrics.length) {
                v.daily_run_km = $metrics.find('.vehicle-daily-run-km-input').val() || '';
                v.interval_days = $metrics.find('.vehicle-interval-days-input').val() || '';
                v.interval_months = $metrics.find('.vehicle-interval-months-input').val() || '';
            }
            updateVehicleTargetStatus($metrics);
        } else {
            updateExpandedVehicleOilPlan($metrics);
            updateVehicleTargetStatus($metrics);
        }
    });
    
    // Edit vehicle (sales) - open modal and fill form
    $(document).on('click', '.edit-vehicle-sale-btn', function(e) {
        e.stopPropagation();
        window._editVehicleFromEditCustomerId = null;
        const dbId = $(this).data('db-id');
        if (!dbId) return;
        $('#editVehicleSaleId').val(dbId);
        $('#editVehicleSalePlate').val($(this).data('plate') || '');
        $('#editVehicleSaleYear').val($(this).data('year') || '');
        window._salesEditVehiclePendingMid = $(this).data('car-manufacturer-id') || '';
        window._salesEditVehiclePendingMoid = $(this).data('car-model-id') || '';
        window._salesEditVehiclePendingMakeName = $(this).data('make') || '';
        window._salesEditVehiclePendingModelName = $(this).data('model') || '';
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
        $('#editVehicleSaleYear').val($(this).data('year') || '');
        window._salesEditVehiclePendingMid = $(this).data('car-manufacturer-id') || '';
        window._salesEditVehiclePendingMoid = $(this).data('car-model-id') || '';
        window._salesEditVehiclePendingMakeName = $(this).data('make') || '';
        window._salesEditVehiclePendingModelName = $(this).data('model') || '';
        $('#editVehicleSaleModal').modal('show');
    });
    
    // Keep Vehicle Edit modal (and its backdrop) on top when opened over Edit Customer modal; load shared make/model master
    $('#editVehicleSaleModal').on('shown.bs.modal', function() {
        $(this).css('z-index', 1065);
        $('.modal-backdrop').last().css('z-index', 1060);
        var pm = window._salesEditVehiclePendingMid;
        var po = window._salesEditVehiclePendingMoid;
        var pn = window._salesEditVehiclePendingMakeName || '';
        var px = window._salesEditVehiclePendingModelName || '';
        salesFillVehicleForm($('#edit-sales-vehicle-make'), $('#edit-sales-vehicle-model'), pm || null, po || null, pn, px, function() {
            salesInitVehicleMasterSelect2($('#editVehicleSaleModal'), '#edit-sales-vehicle-make', '#edit-sales-vehicle-model');
        });
    });
    $('#editVehicleSaleModal').on('hidden.bs.modal', function() {
        salesDestroyVehiclePairSelect2('#edit-sales-vehicle-make', '#edit-sales-vehicle-model');
    });
    
    // Edit vehicle form submit - PUT to customer-vehicles and refresh list
    var editVehicleUpdateUrlTemplate = '{{ url(route("customer.vehicles.update", ["id" => "__ID__"])) }}';
    $('#editVehicleSaleForm').on('submit', function(e) {
        e.preventDefault();
        const vehicleId = $('#editVehicleSaleId').val();
        if (!vehicleId) return;
        const plate = ($('#editVehicleSalePlate').val() || '').toString().trim();
        const makeId = ($('#edit-sales-vehicle-make').val() || '').toString().trim();
        const modelId = ($('#edit-sales-vehicle-model').val() || '').toString().trim();
        const make = ($('#edit-sales-vehicle-make option:selected').text() || '').toString().trim();
        const model = ($('#edit-sales-vehicle-model option:selected').text() || '').toString().trim();
        const year = ($('#editVehicleSaleYear').val() || '').toString().trim();
        const $btn = $(this).find('button[type="submit"]');
        if (!makeId || !modelId) {
            alert('Please select make and model');
            return;
        }
        $btn.prop('disabled', true);
        $.ajax({
            url: editVehicleUpdateUrlTemplate.replace('__ID__', vehicleId),
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT',
                plate_number: plate,
                make: make,
                model: model,
                year: year,
                car_manufacturer_id: makeId,
                car_model_id: modelId
            },
            success: function() {
                const v = vehicles.find(function(ve) { return String(ve.dbId) === String(vehicleId); });
                if (v) {
                    v.plateNumber = plate;
                    v.make = make;
                    v.model = model;
                    v.year = year;
                    v.car_manufacturer_id = makeId ? parseInt(makeId, 10) : null;
                    v.car_model_id = modelId ? parseInt(modelId, 10) : null;
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
                                html += '<button type="button" class="btn btn-sm btn-outline-primary edit-vehicle-in-edit-customer-btn position-absolute top-0 end-0 m-2" style="padding: 2px 8px; z-index: 5;" title="Edit vehicle" data-car-id="' + carId + '" data-customer-id="' + customerIdForVehicle + '" data-plate="' + (v.plateNumber || '').toString().replace(/"/g, '&quot;') + '" data-make="' + make.replace(/"/g, '&quot;') + '" data-model="' + model.replace(/"/g, '&quot;') + '" data-year="' + year.replace(/"/g, '&quot;') + '" data-car-manufacturer-id="' + (v.car_manufacturer_id != null ? v.car_manufacturer_id : '') + '" data-car-model-id="' + (v.car_model_id != null ? v.car_model_id : '') + '"><i class="ti ti-edit" style="font-size: 14px;"></i></button>';
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
    
    // Select primary vehicle for oil/cart sync; card body click should also select.
    $(document).on('click', '.vehicle-card', function(e) {
        if ($(e.target).closest('.edit-vehicle-sale-btn, .remove-vehicle-btn, .repeat-bill-btn, .vehicle-actions, .vehicle-metrics, input, select, button, a, textarea, label').length) return;
        e.stopPropagation();
        const $vehicleCard = $(this).closest('.vehicle-card');
        const vehicleId = $vehicleCard.data('vehicle-id');
        const v = (typeof vehicles !== 'undefined' && vehicles && vehicles.length)
            ? vehicles.find(function(ve) { return String(ve.id) === String(vehicleId); })
            : null;
        window.selectedVehiclePlate = (v && v.plateNumber != null) ? v.plateNumber : ($vehicleCard.find('.vehicle-plate-text').text() || '').trim();
        $('#vehicles-list .vehicle-card').removeClass('vehicle-card--selected');
        $vehicleCard.addClass('vehicle-card--selected');
        setTimeout(function() {
            if (typeof mountPrimaryVehicleMetricsPanel === 'function') mountPrimaryVehicleMetricsPanel();
            if (typeof syncVehicleMileageFromFirstSaleItem === 'function') syncVehicleMileageFromFirstSaleItem();
            if (typeof updateVehicleInputsPermission === 'function') updateVehicleInputsPermission();
        }, 0);
    });

    // When mileage selection changes in modal, mirror it on expanded vehicle
    $(document).on('change', '#item-mileage', function() {
        updateExpandedVehicleMileage();
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

    @isset($saleEditPayload)
    (function hydrateEditSaleScreen() {
        var payload = window.__SALE_EDIT_PAYLOAD__;
        if (!payload || !payload.items || !payload.items.length) return;
        if (typeof applyRepeatBillItems !== 'function') return;
        applyRepeatBillItems(payload, null);
        var maxId = 0;
        salesItems.forEach(function(s) { if (s.id > maxId) maxId = s.id; });
        itemCounter = maxId + 1;
        if (payload.discount != null) $('#discount').val(String(payload.discount));
        if (payload.order_tax != null) $('#order_tax').val(String(payload.order_tax));
        if (payload.shipping != null) $('#shipping').val(String(payload.shipping));
        if (payload.customer_id) {
            $('#customer_id').val(String(payload.customer_id)).trigger('change');
        }
        if (payload.branch_id) {
            $('#salesBranchId').val(String(payload.branch_id));
        }
        if (payload.sale_date) {
            $('#sale_date').val(payload.sale_date);
        }
        if (payload.status) {
            $('#sale-status').val(payload.status);
        }
        if (typeof calculateTotals === 'function') calculateTotals();
        if (typeof updateSalesPrintButton === 'function') updateSalesPrintButton();
    })();
    @endisset
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

{{-- Full Add Customer Modal (from mobile/party dropdown - open without freezing background) --}}
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
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

<!-- Claim Stock Detail Modal (kept inside same content so it renders on this page) -->
<div class="modal fade" id="claim-stock-detail-modal" tabindex="-1" aria-labelledby="claim-stock-detail-modal-label" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true" style="pointer-events: auto;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="claim-stock-detail-modal-label">
                    <i class="ti ti-package me-2"></i>Claim In History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="claim-stock-detail-loading" class="text-center py-4">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem; border-width: 0.2em;"></div>
                    <p class="mb-0 text-muted small">Loading claim stock history...</p>
                </div>
                <div id="claim-stock-detail-content" class="d-none d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 claim-stock-detail-summary-bar" style="order: 2;">
                        <div class="small text-muted">
                            <span id="claim-stock-detail-scope"></span>
                        </div>
                        <div class="text-end small">
                            <div><strong>Total Claim Received:</strong> <span id="claim-stock-total-in">0</span></div>
                            <div><strong>Total Claim Sent:</strong> <span id="claim-stock-total-sent">0</span></div>
                            <div><strong>Current Claim Stock:</strong> <span id="claim-stock-current">0</span></div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 60vh; order: 1;">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Customer / Supplier</th>
                                    <th>Item</th>
                                    <th>Branch</th>
                                    <th class="text-end text-nowrap">Opening Stock</th>
                                    <th class="text-end text-nowrap">Stock In</th>
                                    <th class="text-end text-nowrap">Stock Out</th>
                                    <th class="text-end text-nowrap">Balance After</th>
                                    <th>Type</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="claim-stock-detail-tbody">
                            </tbody>
                        </table>
                    </div>
                    <p id="claim-stock-detail-empty" class="text-muted small text-center py-3 d-none" style="order: 3;">
                        No claim in history found for the selected scope.
                    </p>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end gap-2 w-100">
                <button type="button" class="btn btn-primary" id="claim-stock-detail-load-btn">Load</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Claim Send Detail Modal -->
<div class="modal fade" id="claim-send-stock-detail-modal" tabindex="-1" aria-labelledby="claim-send-stock-detail-modal-label" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true" style="pointer-events: auto;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="claim-send-stock-detail-modal-label">
                    <i class="ti ti-truck-delivery me-2"></i>Claim Send History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="claim-send-stock-detail-loading" class="text-center py-4">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem; border-width: 0.2em;"></div>
                    <p class="mb-0 text-muted small">Loading claim send history...</p>
                </div>
                <div id="claim-send-stock-detail-content" class="d-none d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 claim-stock-detail-summary-bar" style="order: 2;">
                        <div class="small text-muted">
                            <span id="claim-send-stock-detail-scope"></span>
                        </div>
                        <div class="text-end small">
                            <div><strong>Total Claim Received:</strong> <span id="claim-send-stock-total-in">0</span></div>
                            <div><strong>Total Claim Sent:</strong> <span id="claim-send-stock-total-sent">0</span></div>
                            <div><strong>Current Claim Stock:</strong> <span id="claim-send-stock-current">0</span></div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 60vh; order: 1;">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Customer / Supplier</th>
                                    <th>Item</th>
                                    <th>Branch</th>
                                    <th class="text-end text-nowrap">Opening Stock</th>
                                    <th class="text-end text-nowrap">Stock In</th>
                                    <th class="text-end text-nowrap">Stock Out</th>
                                    <th class="text-end text-nowrap">Balance After</th>
                                    <th>Type</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="claim-send-stock-detail-tbody">
                            </tbody>
                        </table>
                    </div>
                    <p id="claim-send-stock-detail-empty" class="text-muted small text-center py-3 d-none" style="order: 3;">
                        No claim send history found for the selected scope.
                    </p>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end gap-2 w-100">
                <button type="button" class="btn btn-primary" id="claim-send-stock-detail-load-btn">Load</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Scrap Stock Detail Modal -->
<div class="modal fade" id="scrap-stock-detail-modal" tabindex="-1" aria-labelledby="scrap-stock-detail-modal-label" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true" style="pointer-events: auto;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scrap-stock-detail-modal-label">
                    <i class="ti ti-recycle me-2"></i>Scrap Stock History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="scrap-stock-detail-loading" class="text-center py-4">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem; border-width: 0.2em;"></div>
                    <p class="mb-0 text-muted small">Loading scrap stock history...</p>
                </div>
                <div id="scrap-stock-detail-content" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="small text-muted">
                            <span id="scrap-stock-detail-scope"></span>
                        </div>
                        <div class="text-end small">
                            <div><strong>Total Scrap Received:</strong> <span id="scrap-stock-total-in">0</span></div>
                            <div><strong>Total Scrap Sent:</strong> <span id="scrap-stock-total-sent">0</span></div>
                            <div><strong>Current Scrap Stock:</strong> <span id="scrap-stock-current">0</span></div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 60vh;">
                        <table class="table table-sm table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Customer / Supplier</th>
                                    <th>Invoice #</th>
                                    <th>Item</th>
                                    <th>Branch</th>
                                    <th>Warehouse</th>
                                    <th class="text-end">Previous Stock</th>
                                    <th class="text-end">Stock In</th>
                                    <th class="text-end">Stock Out</th>
                                    <th class="text-end">Balance After</th>
                                    <th>Type</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="scrap-stock-detail-tbody">
                            </tbody>
                        </table>
                    </div>
                    <p id="scrap-stock-detail-empty" class="text-muted small text-center py-3 d-none">
                        No scrap stock history found for the selected scope.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection
