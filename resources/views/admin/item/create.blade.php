@extends('layouts.app')
@section('title', 'Create Product')
@section('content')
@push('styles')
<style>
    /* Page background - All white */
    body {
        background-color: #ffffff !important;
    }
    .main-wrapper {
        background-color: #ffffff !important;
        min-height: 100vh;
    }
    .page-wrapper {
        background-color: #ffffff !important;
        min-height: calc(100vh - 60px);
    }
    .content {
        background-color: #ffffff !important;
    }
    .card {
        background-color: #ffffff !important;
    }
    .card-body {
        background-color: #ffffff !important;
    }
    
    /* Universal Modal Styling */
    #universal-add-modal .modal-content {
        background-color: #ffffff !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        border: none !important;
    }
    #universal-add-modal .modal-body {
        background-color: #ffffff !important;
    }
    #universal-add-modal .modal-footer {
        background-color: #ffffff !important;
    }
    
    /* Unit Modal Styling */
    #Unit-add-modal .modal-content {
        border-radius: 12px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        border: none !important;
    }
    #Unit-add-modal .modal-header {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e9ecef !important;
        border-radius: 12px 12px 0 0 !important;
        padding: 20px 25px !important;
    }
    #Unit-add-modal .modal-body {
        background-color: #ffffff !important;
        padding: 25px !important;
    }
    #Unit-add-modal .modal-footer {
        background-color: #ffffff !important;
        border-top: 1px solid #e9ecef !important;
        border-radius: 0 0 12px 12px !important;
        padding: 15px 25px !important;
    }
    #Unit-add-modal .form-group {
        margin-bottom: 15px !important;
    }
    #Unit-add-modal label {
        font-weight: 600 !important;
        color: #495057 !important;
        margin-bottom: 8px !important;
        display: block !important;
    }
    #Unit-add-modal .form-control {
        border: 1px solid #ced4da !important;
        border-radius: 6px !important;
        padding: 10px 15px !important;
        transition: all 0.3s ease !important;
    }
    #Unit-add-modal .form-control:focus {
        border-color: #fe9f43 !important;
        box-shadow: 0 0 0 0.2rem rgba(254, 159, 67, 0.25) !important;
    }
    #Unit-add-modal .form-control.is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
    #Unit-add-modal .base-unit-item {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 8px !important;
    }
    
    input[type="text"],
    input[type="password"],
    input[type="email"],
    input[type="url"],
    input[type="tel"],
    input[type="number"],
    input[type="date"],
    input[type="datetime-local"],
    input[type="month"],
    input[type="week"],
    input[type="time"],
    select,
    textarea {
        font-weight: bold !important;
        font-size: 15px !important;
        text-transform: uppercase !important;
    }

    .type-box {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #ddd;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        background: #f8f9fa;
        font-weight: bold;
        font-size: 18px;
    }
    .type-box:hover {
        border-color: #fe9f43;
        background: #fe9f43;
        color: white;
    }
    .type-box.selected {
        border-color: #fe9f43;
        background: #fe9f43;
        color: white;
        box-shadow: 0 4px 12px #fe962e;
    }
    .field-group {
        display: none;
    }
    .field-group.active {
        display: block;
    }
    .border {
        border: 1px solid #ddd !important;
        border-radius: 8px;
    }
    .input-group .btn i {
        pointer-events: none;
    }
    
    /* Sabhi input-group dropdowns ko upar ki taraf show karne ke liye - High Priority */
    body .input-group .select2-container--open .select2-dropdown,
    body .input-group .select2-container--open.select2-container--below .select2-dropdown,
    body .input-group .select2-container--above .select2-dropdown {
        position: absolute !important;
        bottom: 100% !important;
        top: auto !important;
        left: 0 !important;
        right: auto !important;
        margin-bottom: 5px !important;
        margin-top: 0 !important;
        transform: none !important;
        border-top: 1px solid #aaa !important;
        border-bottom: none !important;
        border-radius: 4px 4px 0 0 !important;
        box-shadow: 0 -4px 6px rgba(0,0,0,0.1) !important;
    }
    
    body .input-group .select2-container--open {
        z-index: 9999 !important;
    }
    
    /* Force above positioning */
    body .input-group .select2-container.select2-container--open {
        position: relative !important;
    }
    
    /* Override Select2's default below positioning */
    body .input-group .select2-container--below.select2-container--open .select2-dropdown {
        bottom: 100% !important;
        top: auto !important;
        margin-top: 0 !important;
        margin-bottom: 5px !important;
    }
    
    /* Part Number dropdown - Force above positioning with maximum priority */
    body #part_number_id + .select2-container.select2-container--open .select2-dropdown,
    body #part_number_id + .select2-container.select2-container--below.select2-container--open .select2-dropdown {
        position: absolute !important;
        bottom: 100% !important;
        top: auto !important;
        left: 0 !important;
        right: auto !important;
        margin-bottom: 5px !important;
        margin-top: 0 !important;
        transform: none !important;
        border-top: 1px solid #aaa !important;
        border-bottom: none !important;
        border-radius: 4px 4px 0 0 !important;
        box-shadow: 0 -4px 6px rgba(0,0,0,0.1) !important;
    }
    
    /* Force Part Number container to use above positioning */
    body #part_number_id + .select2-container.select2-container--open {
        position: relative !important;
        z-index: 9999 !important;
    }
    
    body #part_number_id + .select2-container.select2-container--below.select2-container--open {
        position: relative !important;
        z-index: 9999 !important;
    }
    
    /* Part Number Select2 Dropdown - Position Above */
    #part_number_id + .select2-container {
        position: relative !important;
    }
    
    /* Force dropdown to appear above - High specificity override */
    body .select2-container--open.select2-container--below#part_number_id + .select2-container .select2-dropdown,
    body #part_number_id + .select2-container.select2-container--open .select2-dropdown,
    body #part_number_id + .select2-container.select2-container--open.select2-container--below .select2-dropdown {
        position: absolute !important;
        bottom: 100% !important;
        top: auto !important;
        margin-bottom: 5px !important;
        margin-top: 0 !important;
        transform: translateY(0) !important;
        border-top: 1px solid #aaa !important;
        border-bottom: none !important;
        border-radius: 4px 4px 0 0 !important;
    }
    
    /* Remove bottom border radius, add top border radius */
    body #part_number_id + .select2-container.select2-container--open .select2-dropdown .select2-results {
        border-radius: 4px 4px 0 0 !important;
    }
    
    /* Ensure dropdown appears above the input group */
    .input-group:has(#part_number_id) {
        position: relative;
        z-index: 1;
    }
    
    /* Override Select2's positioning class */
    #part_number_id + .select2-container.select2-container--open {
        z-index: 9999 !important;
    }
    
    /* Responsive adjustments for create.blade.php */
    @media (max-width: 768px) {
        .type-box {
            padding: 15px !important;
            font-size: 14px !important;
        }
        .type-box .fs-1 {
            font-size: 2rem !important;
        }
        .inputswidth {
            width: 100% !important;
        }
        /* Vehicle table - better mobile display */
        #vehicleTable {
            font-size: 11px !important;
            width: 100% !important;
            min-width: 800px !important; /* Ensure table doesn't get too compressed */
        }
        #vehicleTable th,
        #vehicleTable td {
            padding: 0.5rem 0.25rem !important;
            font-size: 11px !important;
            vertical-align: top;
            white-space: nowrap !important; /* Prevent text wrapping */
        }
        #vehicleTable thead th {
            padding: 0.75rem 0.5rem !important;
            background-color: #212529 !important;
            color: #ffffff !important;
        }
        #vehicleTable tbody {
            background-color: #ffffff !important;
        }
        #vehicleTable tbody td {
            background-color: #ffffff !important;
            color: #000000 !important;
        }
        /* Vehicle table header - keep select and button on same line and within cell */
        #vehicleTable thead th {
            white-space: nowrap !important;
            overflow: hidden !important; /* Prevent content from spilling to next column */
            position: relative !important;
            vertical-align: top !important;
        }
        #vehicleTable thead th .d-flex.flex-column {
            min-width: 0;
            width: 100%;
            max-width: 100% !important;
            overflow: hidden !important;
        }
        #vehicleTable thead th .input-group {
            margin-top: 0.25rem;
            flex-wrap: nowrap !important; /* Prevent wrapping to next line */
            display: flex !important;
            white-space: nowrap !important;
            width: 100% !important;
            max-width: 100% !important;
            overflow: hidden !important; /* Prevent overflow */
            box-sizing: border-box !important;
        }
        #vehicleTable thead th .input-group .form-control,
        #vehicleTable thead th .input-group .select2-container {
            border-radius: 0.25rem 0 0 0.25rem;
            flex: 1 1 auto !important;
            min-width: 0 !important; /* Allow shrinking */
            max-width: calc(100% - 40px) !important; /* Leave space for button */
            width: auto !important;
            overflow: hidden !important;
        }
        #vehicleTable thead th .input-group .select2-container {
            flex: 1 1 auto !important;
            max-width: calc(100% - 40px) !important;
        }
        #vehicleTable thead th .input-group .select2-selection {
            max-width: 100% !important;
        }
        #vehicleTable thead th .input-group .select2-selection__rendered {
            max-width: 100% !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        #vehicleTable thead th .input-group .btn {
            padding: 0.25rem 0.5rem;
            border-radius: 0;
            flex-shrink: 0 !important; /* Prevent button from shrinking */
            width: auto !important;
            min-width: 35px !important;
            max-width: 40px !important;
        }
        #vehicleTable thead th .input-group .btn:last-child {
            border-radius: 0 0.25rem 0.25rem 0;
        }
        #vehicleTable thead th .inputswidth {
            min-width: 150px !important; /* Reduced from 200px */
            width: 100% !important;
            max-width: 100% !important;
            flex-wrap: nowrap !important;
            display: flex !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        /* Ensure table-responsive allows horizontal scroll */
        .table-responsive {
            overflow-x: auto !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
            background-color: #ffffff !important;
        }
        /* Fix table wrapper background */
        .table-responsive .table {
            background-color: #ffffff !important;
            margin-bottom: 0;
        }
        /* Year badges - smaller on mobile */
        .badge {
            font-size: 0.65rem !important;
            padding: 4px 8px !important;
        }
        /* Form columns stack properly */
        .row .col-md-4,
        .row .col-md-6 {
            margin-bottom: 1rem;
        }
        /* Modal vehicle form - better mobile layout */
        #vehical-add-modal .modal-body {
            padding: 1rem !important;
        }
        #vehical-add-modal .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
        #vehical-add-modal .col-md-6 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        /* Year range inputs - better mobile layout */
        .year-range-item .col-5 {
            flex: 0 0 48%;
            max-width: 48%;
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        .year-range-item .col-2 {
            flex: 0 0 4%;
            max-width: 4%;
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        /* Image previews - responsive */
        #imagePreview, #imagesPreview img {
            max-width: 100% !important;
            height: auto !important;
        }
        /* Unit info display - stack on mobile */
        #unit-info, #sale-price-info {
            flex-direction: column !important;
            gap: 0.5rem !important;
        }
        #unit-info .form-control,
        #sale-price-info .form-control {
            width: 100% !important;
        }
    }
    @media (max-width: 576px) {
        /* Very small screens */
        .type-box {
            padding: 10px !important;
            font-size: 12px !important;
        }
        .type-box .fs-1 {
            font-size: 1.5rem !important;
        }
        /* On very small screens, allow horizontal scroll instead of hiding columns */
        #vehicleTable {
            min-width: 600px !important;
        }
        .table-responsive {
            overflow-x: scroll !important;
            -webkit-overflow-scrolling: touch;
        }
    }
    
    /* Alignment improvements */
    .row .col-md-4,
    .row .col-md-6,
    .row .col-md-3 {
        margin-bottom: 1rem;
    }
    
    .row .col-md-4.mb-3,
    .row .col-md-6.mb-3,
    .row .col-md-3.mb-3 {
        margin-bottom: 1rem !important;
    }
    
    /* Remove inconsistent margin-top from form fields */
    .row .col-md-4.mt-3,
    .row .col-md-6.mt-3,
    .row .col-md-3.mt-3 {
        margin-top: 0 !important;
        margin-bottom: 1rem !important;
    }
    
    /* Ensure labels align properly */
    .form-label,
    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    /* Input group alignment */
    .input-group {
        width: 100%;
    }
    
    /* Consistent field spacing */
    .field-group .row > div {
        margin-bottom: 1rem;
    }
</style>
@endpush

<div class="content">
    <div class="page-header">
        <div class="add-item d-flex">
            <div class="page-title">
                <h2 class="fw-bold">Create Product  </h2>
            </div>
        </div>
        <ul class="table-top-head">
            <li>
                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header">
                    <i class="ti ti-chevron-up"></i>
                </a>
            </li>
        </ul>
    </div>
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form method="POST" action="{{ route('all.items.store') }}" enctype="multipart/form-data" id="mainItemForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->check() ? auth()->user()->id : '' }}">
                <div class="container" x-data="productForm()">
                    <!-- 4 Clickable Type Boxes -->
                    <div class="row mb-5 g-3">
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'parts' }"
                                @click="selectType('parts')">
                                <i class="ti ti-tool fs-1 d-block mb-2"></i>
                                Parts
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'filters' }"
                                @click="selectType('filters')">
                                <i class="ti ti-filter fs-1 d-block mb-2"></i>
                                Filters
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'breakpad' }"
                                @click="selectType('breakpad')">
                                <i class="ti ti-disc fs-1 d-block mb-2"></i>
                                Break Pad
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'oil' }"
                                @click="selectType('oil')">
                                <i class="ti ti-droplet fs-1 d-block mb-2"></i>
                                Oil
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'battery' }"
                                @click="selectType('battery')">
                                <i class="ti ti-battery fs-1 d-block mb-2"></i>
                                Battery
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'scrap' }"
                                @click="selectType('scrap')">
                                <i class="ti ti-trash fs-1 d-block mb-2"></i>
                                Scrap
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="type-box text-center p-4" :class="{ 'selected': selectedType === 'services' }"
                                @click="selectType('services')">
                                <i class="ti ti-tools fs-1 d-block mb-2"></i>
                                Services
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="type" x-model="selectedType">
                    <!-- Hidden inputs to ensure quality_id and technology are submitted even when fields are hidden -->
                    <input type="hidden" name="quality_id" id="hidden_quality_id" value="">
                    <input type="hidden" name="technology" id="hidden_technology" value="">
                    <div class="row" id="itemFormsContainer">
                        <!-- COMMON FIELDS (Visible after type selection) -->
                        <div class="col-md-12 field-group" :class="{ 'active': selectedType }">
                            <h4 class="mt-3">Item Info:</h4>
                            <div class="row mt-4">
                                <!-- Barcode -->
                                <div class="col-md-4 mb-3">
                                    <label for="itemBarCode">Product Bar Code:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('bar_code') is-invalid @enderror"
                                            name="bar_code" id="itemBarCode" value="{{ old('bar_code') }}" required />
                                        <button type="button" class="btn btn-primary generate-code-btn"
                                            id="generateCodeBtn">
                                            <i data-feather="refresh-cw"></i>
                                        </button>
                                    </div>
                                    @error('bar_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <!-- Part Number -->
                                <div class="col-md-4 mb-3" x-show="selectedType === 'parts' || selectedType === 'filters' || selectedType === 'breakpad' ">
                            <div class="mb-1">
                                        <div class="d-flex justify-content-center align-items-center px-1">
                                            <label for="part_number_id" class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-0 text-center fw-bold dynamic-label" data-original="Part Number:" style="font-weight: 900 !important;">Part Number:</label>
                                            <div id="partNumberStats" class="d-flex gap-1 ms-2" style="display: none !important;">
                                        <!-- Stats badges will be inserted here -->
                                    </div>
                                </div>
                            </div>
                            <div class="input-group inputswidth">
                                <select
                                    class="form-control part_number-select searchable-select @error('part_number_id') is-invalid @enderror"
                                    name="part_number_id" 
                                    id="part_number_id">
                                    <option value="">Select Part Number</option>
                                    @foreach ($partnumbers as $partnumber)
                                    <option value="{{ $partnumber->id??'' }}" 
                                        data-type="{{ $partnumber->type ?? '' }}"
                                        {{ old('part_number_id')==$partnumber->id ? 'selected' : '' }}>
                                        {{ $partnumber->name ?? '-' }}
                                    </option>
                                    @endforeach
                                </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Part Number"
                                        data-fetch-route="{{ route('show.partnumber', ':id') }}"
                                        data-update-route="{{ route('update.partnumber', ':id') }}"
                                        data-delete-route="{{ route('destory.partnumber', ':id') }}"
                                        data-target-select=".part_number-select">
                                        <i data-feather="edit"></i>
                                    </button>
                            </div>
                            @error('part_number_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <!-- Product Name -->
                                <div class="col-md-4 mb-3"
                                    x-show="selectedType === 'parts' || selectedType === 'breakpad'">
                                    <div class="mb-1">
                                        <div class="d-flex justify-content-center align-items-center px-1">
                                            <label for="itemname" class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-0 text-center fw-bold dynamic-label" data-original="Product Name:" style="font-weight: 900 !important;">Product Name:</label>
                                        </div>
                                    </div>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control name-select searchable-select @error('p_id') is-invalid @enderror"
                                            name="p_id" id="product_name_item">
                                            <option value="">Select Product Name</option>
                                            @foreach ($product as $item)
                                            <option value="{{ $item->id }}" 
                                                data-type="{{ $item->type ?? '' }}"
                                                {{ old('p_id')==$item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Product"
                                            data-fetch-route="{{ route('show.product', ':id') }}"
                                            data-update-route="{{ route('update.product', ':id') }}"
                                            data-delete-route="{{ route('destory.product', ':id') }}"
                                            data-target-select=".name-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('p_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <!-- Category -->
                                <div class="col-md-4 mb-3" x-show="selectedType === 'parts' || selectedType === 'oil' || selectedType === 'scrap' || selectedType === 'services' || selectedType === 'filters' || selectedType === 'breakpad' ">
                                    <div class="mb-1">
                                        <div class="d-flex justify-content-center align-items-center px-1">
                                            <label for="category" class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-0 text-center fw-bold dynamic-label" data-original="Category:" style="font-weight: 900 !important;">Category:</label>
                                        </div>
                                    </div>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control category-select searchable-select @error('category_id') is-invalid @enderror"
                                            name="category_id" id="category">
                                            <option value="">Select Category</option>
                                            @foreach ($Categories as $category)
                                            <option value="{{ $category->id }}" 
                                                data-type="{{ $category->type ?? '' }}"
                                                {{ old('category_id')==$category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Category"
                                            data-fetch-route="{{ route('show.category', ':id') }}"
                                            data-update-route="{{ route('update.category', ':id') }}"
                                            data-delete-route="{{ route('destory.category', ':id') }}"
                                            data-target-select=".category-select" data-has-image="1">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <!-- Business Location -->
                                <div class="col-md-4 d-none">
                                    <label for="business_location">Business Location:</label>
                                    <input type="text"
                                        class="form-control @error('business_location') is-invalid @enderror"
                                        name="business_location" id="business_location"
                                        value="{{ old('business_location') }}" />

                                    @error('business_location') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3" x-show="selectedType === 'battery'">
                                    <label for="group_select" class="dynamic-label" data-original="Group Name:">Group Name:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control group-select searchable-select @error('group') is-invalid @enderror"
                                            name="group" id="group_select">
                                            <option value="">Select Group Name</option>
                                            @foreach ($groups as $group)
                                            <option value="{{ $group->id }}" {{ old('group')==$group->id ?
                                                'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        {{-- Add button removed --}}
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit group"
                                            data-fetch-route="{{ route('show.groups', ':id') }}"
                                            data-update-route="{{ route('post.groups.update', ':id') }}"
                                            data-delete-route="{{ route('post.groups.destroy', ':id') }}"
                                            data-target-select=".group-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3"
                                    x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil' || selectedType === 'filters' || selectedType === 'breakpad'">
                                    <div class="mb-1">
                                        <div class="d-flex justify-content-center align-items-center px-1">
                                            <label for="company_parts" class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-0 text-center fw-bold dynamic-label" data-original="Company:" style="font-weight: 900 !important;">Company:</label>
                                        </div>
                                    </div>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control company-select searchable-select @error('company_id') is-invalid @enderror"
                                            name="company_id" id="company_parts">
                                            <option value="">Select Company</option>
                                            @foreach ($Companies as $company)
                                            <option value="{{ $company->id }}" 
                                                data-type="{{ $company->type ?? '' }}"
                                                {{ old('company_id')==$company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Company"
                                            data-fetch-route="{{ route('show.company', ':id') }}"
                                            data-update-route="{{ route('update.company', ':id') }}"
                                            data-delete-route="{{ route('destory.company', ':id') }}"
                                            data-target-select=".company-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <!-- Series/Technology -->
                                <div class="col-md-4 mb-3"  x-show=" selectedType === 'battery' || selectedType === 'oil'">
                                    <label for="technology_select">
                                        <b x-show="selectedType === 'oil'">Technology:</b>
                                        <b x-show="selectedType == 'battery'">Series:</b>
                                    </label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control technology-select searchable-select @error('technology') is-invalid @enderror"
                                            name="technology" id="technology_select">
                                            <option value="">Select</option>
                                            @foreach ($technologies as $tech)
                                            <option value="{{ $tech->id }}" 
                                                data-type="{{ $tech->type ?? '' }}"
                                                {{ old('technology')==$tech->id ? 'selected' : '' }}>
                                                {{ $tech->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        {{-- Add button removed --}}
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit"
                                            x-bind:data-title="selectedType === 'parts' ? 'Edit Technology' : 'Edit Series'"
                                            data-fetch-route="{{ route('show.technology', ':id') }}"
                                            data-update-route="{{ route('update.technology', ':id') }}"
                                            data-delete-route="{{ route('destory.technology', ':id') }}"
                                            data-target-select=".technology-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>

                                    @error('technology')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <!-- PARTS FIELDS -->

                        <div class="field-group" :class="{ 'active': selectedType === 'parts' }">
                            <div class="row  p-3 mt-4">

                                <div class="col-md-4">
                                    <div class="mb-1">
                                        <div class="d-flex justify-content-center align-items-center px-1">
                                            <label for="quality" class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-0 text-center fw-bold dynamic-label" data-original="Quality:" style="font-weight: 900 !important;">Quality:</label>
                                        </div>
                                    </div>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $item)
                                            <option value="{{ $item->id }}" 
                                                data-type="{{ $item->type ?? '' }}"
                                                {{ old('quality_id')==$item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Quality"
                                            data-fetch-route="{{ route('show.quality', ':id') }}"
                                            data-update-route="{{ route('update.quality', ':id') }}"
                                            data-delete-route="{{ route('destory.quality', ':id') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('quality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </div>

                        <!-- FILTERS FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'filters' }">
                            <div class="row  p-3 mt-4">

                                <div class="col-md-4">
                                    <label for="quality_filters" class="dynamic-label" data-original="Quality:">Quality:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality_filters">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $item)
                                            <option value="{{ $item->id }}" 
                                                data-type="{{ $item->type ?? '' }}"
                                                x-show="!selectedType || '{{ $item->type ?? '' }}' === selectedType || '{{ $item->type ?? '' }}' === ''"
                                                {{ old('quality_id')==$item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Quality"
                                            data-fetch-route="{{ route('show.quality', ':id') }}"
                                            data-update-route="{{ route('update.quality', ':id') }}"
                                            data-delete-route="{{ route('destory.quality', ':id') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('quality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </div>

                        <!-- BREAK PAD FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'breakpad' }">
                            <div class="row  p-3 mt-4">

                                <div class="col-md-4">
                                    <label for="quality_breakpad" class="dynamic-label" data-original="Quality:">Quality:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control quality-select searchable-select @error('quality_id') is-invalid @enderror"
                                            name="quality_id" id="quality_breakpad">
                                            <option value="">Select Quality</option>
                                            @foreach ($qualities as $item)
                                            <option value="{{ $item->id }}" {{ old('quality_id')==$item->id ?
                                                'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Quality"
                                            data-fetch-route="{{ route('show.quality', ':id') }}"
                                            data-update-route="{{ route('update.quality', ':id') }}"
                                            data-delete-route="{{ route('destory.quality', ':id') }}"
                                            data-target-select=".quality-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('quality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </div>

                        <!-- BATTERY FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'battery' }">
                            <div class="row p-3 mt-4">
                                <div class="col-md-4">
                                    <label for="plates_scrap">Plates:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control plates-select searchable-select @error('plat_id') is-invalid @enderror"
                                            name="plat_id" id="plates_scrap">
                                            <option value="">Select Plate</option>
                                            @foreach ($platos as $plate)
                                            <option value="{{ $plate->id }}" {{ old('plat_id')==$plate->id ?
                                                'selected' : '' }}>
                                                {{ $plate->name }}PL
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Plate" data-mode="add"
                                            data-route="{{ route('post.platos') }}" data-target-select=".plates-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Plates"
                                            data-fetch-route="{{ route('show.plate', ':id') }}"
                                            data-update-route="{{ route('update.plate', ':id') }}"
                                            data-delete-route="{{ route('destory.plate', ':id') }}"
                                            data-target-select=".plates-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('plat_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="amperes_select">Amperes:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control amperes-select searchable-select @error('amphors') is-invalid @enderror"
                                            name="amphors" id="amperes_select">
                                            <option value="">Select Amperes</option>
                                            @foreach ($amphors as $ampere)
                                            <option value="{{ $ampere->id }}" {{ old('amphors')==$ampere->id ?
                                                'selected' : '' }}>
                                                {{ $ampere->name }}AH
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Amperes" data-mode="add"
                                            data-route="{{ route('post.amphors') }}"
                                            data-target-select=".amperes-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Amperes"
                                            data-fetch-route="{{ route('show.ampere', ':id') }}"
                                            data-update-route="{{ route('update.ampere', ':id') }}"
                                            data-delete-route="{{ route('destory.ampere', ':id') }}"
                                            data-target-select=".amperes-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('amphors')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="volt_select">Volt:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control volt-select searchable-select @error('volt') is-invalid @enderror"
                                            name="volt" id="volt_select">
                                            <option value="">Select Volt</option>
                                            @foreach ($volts as $volt)
                                            <option value="{{ $volt->id }}" {{ old('volt', $volt->name == 12 ? $volt->id
                                                : '') == $volt->id ? 'selected' : '' }}>
                                                {{ $volt->name }} V
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Volt" data-mode="add" data-route="{{ route('post.volts') }}"
                                            data-target-select=".volt-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Volt"
                                            data-fetch-route="{{ route('show.volt', ':id') }}"
                                            data-update-route="{{ route('update.volt', ':id') }}"
                                            data-delete-route="{{ route('destory.volt', ':id') }}"
                                            data-target-select=".volt-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('volt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label for="cca_select">CCA:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control cca-select searchable-select @error('cca') is-invalid @enderror"
                                            name="cca" id="cca_select">
                                            <option value="">Select CCA</option>
                                            @foreach ($ccas as $cca)
                                            <option value="{{ $cca->id }}" {{ old('cca')==$cca->id ? 'selected' : ''
                                                }}>
                                                {{ $cca->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add CCA" data-mode="add" data-route="{{ route('post.cca') }}"
                                            data-target-select=".cca-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>

                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit CCA"
                                            data-fetch-route="{{ route('show.cca', ':id') }}"
                                            data-update-route="{{ route('update.cca', ':id') }}"
                                            data-delete-route="{{ route('destory.cca', ':id') }}"
                                            data-target-select=".cca-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>

                                    @error('cca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mt-3">
                                    <label for="minus_pole_direction_select">Minus Pole Direction:</label>

                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control minus-pole-direction-select searchable-select @error('minus_pole_direction') is-invalid @enderror"
                                            name="minus_pole_direction" id="minus_pole_direction_select">
                                            <option value="">Select Minus Pole Direction</option>
                                            @foreach ($minspols as $mpd)
                                            <option value="{{ $mpd->id }}" {{ old('minus_pole_direction', $mpd->name
                                                ==='L' ? $mpd->id : '') == $mpd->id ? 'selected' : '' }}>
                                                {{ $mpd->name }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Minus Pole Direction" data-mode="add"
                                            data-route="{{ route('post.minuspool') }}"
                                            data-target-select=".minus-pole-direction-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Minus Pole Direction"
                                            data-fetch-route="{{ route('show.minuspool', ':id') }}"
                                            data-update-route="{{ route('update.minuspool', ':id') }}"
                                            data-delete-route="{{ route('destory.minuspool', ':id') }}"
                                            data-target-select=".minus-pole-direction-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>

                                    @error('minus_pole_direction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mt-3">
                                    <label for="Warrenty_select">Warrenty:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control Warrenty-select searchable-select @error('warrenty') is-invalid @enderror"
                                            name="warrenty" id="Warrenty_select">
                                            <option value="">Select Warrenty</option>
                                            @foreach ($warrenties as $warrenty)
                                            <option value="{{ $warrenty->id }}" {{ old('warrenty')==$warrenty->id ?
                                                'selected' : '' }}>
                                                {{ $warrenty->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Warrenty" data-mode="add"
                                            data-route="{{ route('post.warrenty') }}"
                                            data-target-select=".Warrenty-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Warrenty"
                                            data-fetch-route="{{ route('show.warrenty', ':id') }}"
                                            data-update-route="{{ route('update.warrenty', ':id') }}"
                                            data-delete-route="{{ route('destory.warrenty', ':id') }}"
                                            data-target-select=".Warrenty-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('warrenty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mt-3">
                                    <label for="made_in_select">Made In:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control made_in-select searchable-select @error('made_in') is-invalid @enderror"
                                            name="made_in" id="made_in_select">
                                            <option value="">Select Made In</option>
                                            @foreach ($made_ins as $made)
                                            <option value="{{ $made->id }}" {{ old('made_in', $made->name ==='Pakistan'
                                                ? $made->id : '') == $made->id ? 'selected' : '' }}>
                                                {{ $made->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Made In" data-mode="add"
                                            data-route="{{ route('post.made_ins') }}"
                                            data-target-select=".made_in-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Made In"
                                            data-fetch-route="{{ route('show.madeins', ':id') }}"
                                            data-update-route="{{ route('update.madeins', ':id') }}"
                                            data-delete-route="{{ route('destory.madeins', ':id') }}"
                                            data-target-select=".made_in-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('made_in')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-3 d-none">
                                    <label for="battery_code">Battery Code:</label>
                                    <input type="text" class="form-control @error('battery_code') is-invalid @enderror"
                                        name="battery_code" id="battery_code" value="{{ old('battery_code') }}" />
                                    @error('battery_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 d-none">
                                    <label for="serial_number_battery">Serial Number:</label>
                                    <input type="text" class="form-control @error('serial_number') is-invalid @enderror"
                                        name="serial_number" id="serial_number_battery"
                                        value="{{ old('serial_number') }}" />
                                    @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </div>

                        <!-- OIL FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'oil' }">
                            <div class="row p-3 mt-4">
                                <div class="col-md-4">
                                    <label for="grade_select">Grade:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control grade-select searchable-select @error('grade') is-invalid @enderror"
                                            name="grade" id="grade_select">
                                            <option value="">Select Grade</option>
                                            @foreach ($grades as $grade)
                                            <option value="{{ $grade->id }}" {{ old('grade')==$grade->id ?
                                                'selected' : '' }}>
                                                {{ $grade->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Grade" data-mode="add"
                                            data-route="{{ route('post.grade') }}" data-target-select=".grade-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Grade"
                                            data-fetch-route="{{ route('show.grade', ':id') }}"
                                            data-update-route="{{ route('update.grade', ':id') }}"
                                            data-delete-route="{{ route('destory.grade', ':id') }}"
                                            data-target-select=".grade-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>

                                    @error('grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-3 d-none">
                                    <label for="brand_select">Brand:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control brand-select searchable-select @error('brand') is-invalid @enderror"
                                            name="brand" id="brand_select">
                                            <option value="">Select Brand</option>
                                            @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('brand')==$brand->id ?
                                                'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Brand" data-mode="add"
                                            data-route="{{ route('post.item.brand') }}"
                                            data-target-select=".brand-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Brand"
                                            data-fetch-route="{{ route('show.grade', ':id') }}"
                                            data-update-route="{{ route('update.grade', ':id') }}"
                                            data-delete-route="{{ route('destory.grade', ':id') }}" data-has-image="1"
                                            data-target-select=".grade-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="mileage_oil">Mileage:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control mileage-select searchable-select @error('mileage') is-invalid @enderror"
                                            name="mileage" id="mileage_oil">
                                            <option value="">Select Mileage</option>
                                            @foreach ($milleages as $milleage)
                                            <option value="{{ $milleage->id }}" {{ old('mileage')==$milleage->id ?
                                                'selected' : '' }}>
                                                {{ $milleage->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Mileage" data-mode="add"
                                            data-route="{{ route('post.item.mileage') }}"
                                            data-target-select=".mileage-select">
                                            <i data-feather="plus" class="feather-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Mileage"
                                            data-fetch-route="{{ route('show.mileage', ':id') }}"
                                            data-update-route="{{ route('update.mileage', ':id') }}"
                                            data-delete-route="{{ route('destory.mileage', ':id') }}"
                                            data-target-select=".mileage-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('mileage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4 mt-3 ">
                                    <label for="Level_select">Level:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control level-select searchable-select @error('level') is-invalid @enderror"
                                            name="level" id="Level_select">
                                            <option value="">Select Level</option>
                                            @foreach ($levels as $level)
                                            <option value="{{ $level->id }}" {{ old('level')==$level->id ?
                                                'selected' : '' }}>
                                                {{ $level->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Level" data-mode="add"
                                            data-route="{{ route('post.levels') }}" data-target-select=".level-select">
                                            <i data-feather="plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary open-universal-modal"
                                            data-mode="edit" data-title="Edit Level"
                                            data-fetch-route="{{ route('show.level', ':id') }}"
                                            data-update-route="{{ route('update.level', ':id') }}"
                                            data-delete-route="{{ route('destory.level', ':id') }}"
                                            data-target-select=".level-select">
                                            <i data-feather="edit"></i>
                                        </button>
                                    </div>
                                    @error('level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mt-3 d-none">
                                    <label for="formulas_select">Formulas:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control formulas-select searchable-select @error('formulas') is-invalid @enderror"
                                            name="formulas" id="formulas_select">
                                            <option value="">Select Formula</option>
                                            @foreach ($formulas as $formula)
                                            <option value="{{ $formula->id }}" {{ old('formulas')==$formula->id ?
                                                'selected' : '' }}>
                                                {{ $formula->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-primary open-universal-modal"
                                            data-title="Add Formula" data-mode="add"
                                            data-route="{{ route('post.formulas') }}"
                                            data-target-select=".formulas-select">
                                            <i data-feather="plus"></i>
                                        </button>

                                    </div>
                                    @error('formulas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mt-3 d-none">
                                    <label for="serial_number_oil">Serial Number:</label>
                                    <input type="text" class="form-control @error('serial_number') is-invalid @enderror"
                                        name="serial_number" id="serial_number_oil"
                                        value="{{ old('serial_number') }}" />
                                    @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SCRAP FIELDS -->
                        <div class="field-group" :class="{ 'active': selectedType === 'scrap' }">
                            <div class="row p-3 mt-4">
                                {{-- <div class="col-md-4">
                                    <label for="plates_scrap">Plates:</label>
                                    <div class="input-group inputswidth">
                                        <select
                                            class="form-control plates-select searchable-select @error('plato') is-invalid @enderror"
                                            name="plato" id="plates_scrap">
                                            <option value="">Select Plate</option>
                                            @foreach ($platos as $plate)
                                            <option value="{{ $plate->id }}" {{ old('plato')==$plate->id ? 'selected' :
                                                '' }}>
                                {{ $plate->name }}
                                </option>
                                @endforeach
                                </select>
                                <button type="button" class="btn btn-primary open-universal-modal"
                                    data-title="Add Plate" data-mode="add" data-route="{{ route('post.platos') }}"
                                    data-target-select=".plates-select">
                                    <i data-feather="plus" class="feather-plus"></i>
                                </button>
                            </div>
                            @error('plato')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        <div class="col-md-4">
                            <label for="battery_size">Battery Size:</label>
                            <input type="text" class="form-control @error('battery_size') is-invalid @enderror"
                                name="battery_size" id="battery_size" value="{{ old('battery_size') }}" />
                            @error('battery_size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="field-group" :class="{ 'active': selectedType === 'services' }">
                    <div class="row p-3 mt-4">
                        <div class="col-md-4">
                            <label for="Services_scrap">Services:</label>
                            <div class="input-group inputswidth">
                                <select
                                    class="form-control Services-select searchable-select @error('services') is-invalid @enderror"
                                    name="services" id="Services_scrap">
                                    <option value="">Select Services</option>
                                    @foreach ($services as $service)
                                    <option value="{{ $service->id }}" {{ old('services')==$service->id ?
                                                'selected' :
                                                '' }}>
                                        {{ $service->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary open-universal-modal"
                                    data-title="Add Services" data-mode="add" data-route="{{ route('post.services') }}"
                                    data-target-select=".Services-select">
                                    <i data-feather="plus" class="feather-plus"></i>
                                </button>
                                <button type="button" class="btn btn-secondary open-universal-modal" data-mode="edit"
                                    data-title="Edit Services" data-fetch-route="{{ route('show.service', ':id') }}"
                                    data-update-route="{{ route('update.service', ':id') }}"
                                    data-delete-route="{{ route('destory.service', ':id') }}"
                                    data-target-select=".Services-select">
                                    <i data-feather="edit"></i>
                                </button>
                            </div>
                            @error('services')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- COMMON MEDIA & DESCRIPTION -->
                <div class="field-group" :class="{ 'active': selectedType }">
                    <div class="row mt-4">
                        <!-- Unit Management & Price Calculation Section -->
                        <div class="col-12 mt-4"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0 fw-bold">Unit Management & Price Calculation</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Unit Selection -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold mb-2">Select Unit & Conversion:</label>
                                        <div class="input-group">
                                            <select class="form-control form-control-lg searchable-select @error('unit') is-invalid @enderror"
                                                name="unit" id="unit_parts" style="width: 100%;">
                                                <option value="">-- PLEASE SELECT --</option>
                                </select>
                                <button type="button" class="btn btn-secondary" id="editUnitBtn">
                                                <i data-feather="edit"></i> Edit
                                </button>
                                        </div>
                                        @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <!-- Warning Message -->
                                    <div id="priceWarning" class="alert alert-danger mb-3" style="display: none;">
                                        <i class="ti ti-alert-circle me-2"></i>
                                        <strong>WARNING:</strong> SALE PRICE IS LESS THAN COST PRICE (LOSS)
                            </div>

                                    <!-- Price Management Container -->
                                    <div id="priceManagementContainer" class="mb-4 row g-3">
                                        <!-- Cost Price Management -->
                                        <div id="costPriceManagement" class="col-12 col-md-6 mb-0 mb-md-0">
                                            <h6 class="text-success fw-bold mb-3 text-uppercase">Cost Price Management</h6>
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <div class="card border-success bg-light">
                                                        <div class="card-body">
                                                            <label class="form-label small fw-bold text-success text-uppercase mb-1" id="costUnitLabel">Unit Cost:</label>
                                                            <div class="input-group input-group-lg" style="display: flex; align-items: stretch;">
                                                                <span class="input-group-text bg-success text-white fw-bold" style="display: flex; align-items: center; justify-content: center; height: auto; min-height: 100%; padding: 0.5rem 0.75rem;">Rs.</span>
                                                                <input type="number" step="0.01" id="costPrice" name="total_price"
                                                                    class="form-control form-control-lg fw-bold" placeholder="0"
                                                                    oninput="calculateFromUnit('cost')" style="flex: 1; height: 100%;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6" id="baseCostPriceContainer" style="display: none;">
                                                    <div class="card border-success bg-light">
                                                        <div class="card-body">
                                                            <label class="form-label small fw-bold text-success text-uppercase mb-1" id="costBaseLabel">Per Base Cost:</label>
                                                            <div class="input-group input-group-lg" style="display: flex; align-items: stretch;">
                                                                <span class="input-group-text bg-success text-white fw-bold" style="display: flex; align-items: center; justify-content: center; height: auto; min-height: 100%; padding: 0.5rem 0.75rem;">Rs.</span>
                                                                <input type="number" step="0.01" id="baseCostPrice" name="price_per_unit"
                                                                    class="form-control form-control-lg fw-bold" placeholder="0"
                                                                    oninput="calculateFromBase('cost')" style="flex: 1; height: 100%;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sale Price Management -->
                                        <div id="salePriceManagement" class="col-12 col-md-6 mb-0 mb-md-0">
                                            <h6 class="text-warning fw-bold mb-3 text-uppercase">Sale Price Management</h6>
                                            <div class="row g-3">
                                                <div class="col-12 col-md-6">
                                                    <div class="card border-warning bg-light">
                                                        <div class="card-body">
                                                            <label class="form-label small fw-bold text-warning text-uppercase mb-1" id="saleUnitLabel">Unit Sale:</label>
                                                            <div class="input-group input-group-lg" style="display: flex; align-items: stretch;">
                                                                <span class="input-group-text bg-warning text-white fw-bold" style="display: flex; align-items: center; justify-content: center; height: auto; min-height: 100%; padding: 0.5rem 0.75rem;">Rs.</span>
                                                                <input type="number" step="0.01" id="salePrice" name="sale_price"
                                                                    class="form-control form-control-lg fw-bold" placeholder="0"
                                                                    oninput="calculateFromUnit('sale')" style="flex: 1; height: 100%;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6" id="baseSalePriceContainer" style="display: none;">
                                                    <div class="card border-warning bg-light">
                                                        <div class="card-body">
                                                            <label class="form-label small fw-bold text-warning text-uppercase mb-1" id="saleBaseLabel">Per Base Sale:</label>
                                                            <div class="input-group input-group-lg" style="display: flex; align-items: stretch;">
                                                                <span class="input-group-text bg-warning text-white fw-bold" style="display: flex; align-items: center; justify-content: center; height: auto; min-height: 100%; padding: 0.5rem 0.75rem;">Rs.</span>
                                                                <input type="number" step="0.01" id="baseSalePrice" name="sale_price_per_base"
                                                                    class="form-control form-control-lg fw-bold" placeholder="0"
                                                                    oninput="calculateFromBase('sale')" style="flex: 1; height: 100%;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Price Analysis -->
                                    <div id="derivedPricesList" class="mt-4">
                                        <!-- Analysis cards will be dynamically inserted here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Thumbnail -->
                        <!-- Thumbnail (Single Image) -->
                        <div class="col-md-6 mt-3">
                            <label for="imageInput" class="form-label fw-bold">Product Image (Thumbnail)</label>

                            <div class="d-flex gap-2">
                                <!-- Using label to trigger file input - works on all mobile browsers (Chrome, Firefox, etc.) -->
                                <label for="imageInput" class="btn btn-primary flex-fill mb-0" style="cursor: pointer;">
                                    <i data-feather="camera"></i> Take or Choose Photo
                                </label>
                                <input type="file" id="imageInput" name="image" accept="image/*"
                                    class="d-none @error('image') is-invalid @enderror">
                            </div>

                            <!-- Preview -->
                            <div id="previewContainer" class="text-center mt-3" style="display: none;">
                                <div class="position-relative d-inline-block">
                                    <img id="imagePreview" src="https://pdis.co.kr/img/image.jpg" alt="Preview"
                                        class="img-fluid rounded border shadow-sm"
                                        style="max-height: 220px; object-fit: cover;">
                                    <button type="button" id="removeBtn" class="btn btn-danger btn-sm position-absolute"
                                        style="top: 8px; right: 8px;">
                                        <i data-feather="x"></i>
                                    </button>
                                </div>
                            </div>

                            @error('image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Multiple Images -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Product Images (Multiple)</label>
                            <div class="d-flex gap-2">
                                <!-- File input for multiple images - using label for better mobile support -->
                                <label for="imagesInput" class="btn btn-outline-primary flex-fill mb-0" style="cursor: pointer;">
                                    <i data-feather="image"></i> Add More Photos
                                </label>
                                <input type="file" id="imagesInput" name="images[]" accept="image/*" multiple
                                    class="d-none @error('images.*') is-invalid @enderror">
                            </div>

                            <!-- Multiple Images Preview -->
                            <div id="imagesPreviewContainer" class="mt-3" style="display: none;">
                                <div class="d-flex flex-wrap gap-3" id="imagesPreview"></div>
                            </div>

                            @error('images.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="low_stock_parts" class="text-center fw-bold d-block" style="font-weight: 900 !important;">LOW STOCK:</label>
                            <select class="form-control searchable-select @error('l_stock') is-invalid @enderror"
                                name="l_stock" id="low_stock_parts">
                                <option value="">Select Low Stock</option>
                                @for($i = 1; $i <= 1000; $i++)
                                    <option value="{{ $i }}" {{ old('l_stock') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('l_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="maintain_stock_parts" class="text-center fw-bold d-block" style="font-weight: 900 !important;">MAINTAIN STOCK:</label>
                            <select class="form-control searchable-select @error('m_stock') is-invalid @enderror"
                                name="m_stock" id="maintain_stock_parts">
                                <option value="">Select Maintain Stock</option>
                                @for($i = 1; $i <= 1000; $i++)
                                    <option value="{{ $i }}" {{ old('m_stock') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('m_stock') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="on_hand" class="text-center fw-bold d-block" style="font-weight: 900 !important;">OPENING STOCK:</label>
                            <select class="form-control searchable-select @error('on_hand') is-invalid @enderror"
                                name="on_hand" id="on_hand">
                                <option value="">Select Opening Stock</option>
                                @for($i = 1; $i <= 1000; $i++)
                                    <option value="{{ $i }}" {{ old('on_hand') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('on_hand') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <!-- Brochure -->
                        <div class="col-md-4 mt-3">
                            <label for="p_brochure" class="text-center fw-bold d-block" style="font-weight: 900 !important;">PRODUCT BROCHURE (URL):</label>
                            <input type="url" class="form-control @error('p_brochure') is-invalid @enderror"
                                id="p_brochure" name="p_brochure" value="{{ old('p_brochure') }}" />
                            @error('p_brochure') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <!-- Weight -->
                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="weight">Weight:</label>
                            <input type="number" step="0.01" class="form-control @error('weight_for_delivery') is-invalid @enderror"
                                name="weight_for_delivery" id="weight" value="{{ old('weight_for_delivery') }}" placeholder="Enter weight" />
                            @error('weight_for_delivery') <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Weight Unit -->
                        <div class="col-md-4 mt-3"
                            x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'oil'|| selectedType === 'scrap' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <label for="weight_unit">Weight Unit:</label>
                            <select class="form-control searchable-select @error('weight_unit') is-invalid @enderror"
                                name="weight_unit" id="weight_unit">
                                <option value="">Select Weight Unit</option>
                                <optgroup label="Metric System (most used worldwide)">
                                    <option value="ml" {{ old('weight_unit') == 'ml' ? 'selected' : '' }}>MilliLiter (Ml)</option>
                                    <option value="mg" {{ old('weight_unit') == 'mg' ? 'selected' : '' }}>Milligram (mg)</option>
                                    <option value="g" {{ old('weight_unit') == 'g' ? 'selected' : '' }}>Gram (g)</option>
                                    <option value="kg" {{ old('weight_unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                    <option value="quintal" {{ old('weight_unit') == 'quintal' ? 'selected' : '' }}>Quintal (100 kg)</option>
                                    <option value="tonne" {{ old('weight_unit') == 'tonne' ? 'selected' : '' }}>Metric Ton / Tonne (t) = 1000 kg</option>
                                </optgroup>
                                <optgroup label="Imperial / Other Systems">
                                    <option value="oz" {{ old('weight_unit') == 'oz' ? 'selected' : '' }}>Ounce (oz)</option>
                                    <option value="lb" {{ old('weight_unit') == 'lb' ? 'selected' : '' }}>Pound (lb)</option>
                                    <option value="stone" {{ old('weight_unit') == 'stone' ? 'selected' : '' }}>Stone (UK-specific)</option>
                                    <option value="ton_us" {{ old('weight_unit') == 'ton_us' ? 'selected' : '' }}>Ton (US)</option>
                                    <option value="ton_uk" {{ old('weight_unit') == 'ton_uk' ? 'selected' : '' }}>Ton (UK)</option>
                                </optgroup>
                            </select>
                            @error('weight_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        {{-- PART NUMBER SELECT --}}

                        {{-- VEHICLE TABLE --}}
                        <div class="col-md-12" x-show="selectedType === 'parts' || selectedType === 'battery' || selectedType === 'filters' || selectedType === 'breakpad'">
                            <div class="table-responsive mt-4" style="max-height:250px;overflow-x:auto;overflow-y:auto;background-color:#ffffff;">
                                <table class="table table-bordered" id="vehicleTable" style="background-color:#ffffff;">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 50px; text-align: center;">
                                                <div class="d-flex flex-column">
                                                    <div class="mb-2 fw-bold">Select</div>
                                                    <div class="text-center">
                                                        <input type="checkbox" id="selectAllVehicles" style="cursor: pointer;" title="Select All">
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="d-flex flex-column">
                                                    <div class="mb-2 fw-bold">Manufacturer</div>
                                                    <div class="input-group inputswidth">
                                                        <select id="vehicle_manufacturer_select" class="form-control car-manufacturer-select searchable-select" style="font-size: 12px; padding: 4px 8px;">
                                                            <option value="">Select</option>
                                                            @foreach ($carManufacturers as $manufacturer)
                                                            <option value="{{ $manufacturer->id }}">{{ $manufacturer->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-sm btn-secondary open-universal-modal"
                                                            data-mode="edit" data-title="Edit Manufacturer"
                                                            data-fetch-route="{{ route('show.car.manufacturer', ':id') }}"
                                                            data-update-route="{{ route('update.car.manufacturer', ':id') }}"
                                                            data-delete-route="{{ route('destory.car.manufacturer', ':id') }}"
                                                            data-target-select=".car-manufacturer-select">
                                                            <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="d-flex flex-column">
                                                    <div class="mb-2 fw-bold">Model</div>
                                                    <div class="input-group inputswidth">
                                                        <select id="vehicle_model_select" class="form-control car-model-select searchable-select" style="font-size: 12px; padding: 4px 8px;">
                                                            <option value="">Select</option>
                                                            @foreach ($carModels as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-sm btn-secondary open-universal-modal"
                                                            data-mode="edit" data-title="Edit Car Model"
                                                            data-fetch-route="{{ route('show.car.model', ':id') }}"
                                                            data-update-route="{{ route('update.car.model', ':id') }}"
                                                            data-delete-route="{{ route('destory.car.model', ':id') }}"
                                                            data-target-select=".car-model-select">
                                                            <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="d-flex flex-column">
                                                    <div class="mb-2 fw-bold">Country</div>
                                                    <div class="input-group inputswidth">
                                                        <select id="vehicle_country_select" class="form-control car-country-select searchable-select" style="font-size: 12px; padding: 4px 8px;">
                                                            <option value="">Select</option>
                                                            @foreach ($carCountries as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-sm btn-secondary open-universal-modal"
                                                            data-mode="edit" data-title="Edit Country"
                                                            data-fetch-route="{{ route('show.car.country', ':id') }}"
                                                            data-update-route="{{ route('update.car.country', ':id') }}"
                                                            data-delete-route="{{ route('destory.car.country', ':id') }}"
                                                            data-target-select=".car-country-select">
                                                            <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="d-flex flex-column">
                                                    <button type="button" class="btn btn-sm btn-primary open-year-range-modal mb-2" style="font-size: 11px; padding: 0.25rem 0.5rem; width: 100%;">
                                                        <i data-feather="calendar" style="width: 12px; height: 12px;"></i>
                                                        <span style="margin-left: 4px;">Year Range</span>
                                                    </button>
                                                    <div id="selectedYearRangesDisplay" class="border rounded p-2" style="min-height: 40px; max-height: 80px; overflow-y: auto; background-color: #f8f9fa; font-size: 10px; cursor: pointer;" data-show-all="false">
                                                        <div class="text-muted text-center" style="font-size: 10px;">No ranges selected</div>
                                                    </div>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="d-flex flex-column">
                                                    <div class="mb-2 fw-bold">Engine</div>
                                                    <div class="input-group inputswidth">
                                                        <select id="vehicle_engine_select" class="form-control car-engine-select searchable-select" style="font-size: 12px; padding: 4px 8px;">
                                                            <option value="">Select</option>
                                                            @foreach ($engineccs as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-sm btn-secondary open-universal-modal"
                                                            data-mode="edit" data-title="Edit Engine CC"
                                                            data-fetch-route="{{ route('show.engine_cc', ':id') }}"
                                                            data-update-route="{{ route('update.engine_cc', ':id') }}"
                                                            data-delete-route="{{ route('destory.engine_cc', ':id') }}"
                                                            data-target-select=".car-engine-select">
                                                            <i data-feather="edit" style="width: 14px; height: 14px;"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </th>
                                          
                                            <th>
                                                <div class="d-flex flex-column">
                                                    <button type="button" class="btn btn-sm btn-warning clearVehicleFiltersBtn mb-2" style="font-size: 11px; padding: 0.25rem 0.5rem; width: 100%;">
                                                        <i data-feather="x-circle" style="width: 12px; height: 12px;"></i>
                                                        <span style="margin-left: 4px;">Empty</span>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-primary loadVehicleBtn" style="font-size: 11px; padding: 0.25rem 0.5rem; width: 100%;">
                                                        <i data-feather="refresh-cw" style="width: 12px; height: 12px;"></i>
                                                        <span style="margin-left: 4px;">Load</span>
                                                    </button>
                                                </div>
                                            </th>
                                            <th>
                                                <div class="d-flex flex-column">
                                                    <div class="mb-2 fw-bold">Part Number</div>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($Vehis as $car)
                                        <tr data-part="{{ $car->v_part_number_id??'' }}">
                                            <td style="text-align: center;">
                                                <input type="checkbox" class="vehicle-checkbox" 
                                                    data-vehicle-id="{{ $car->id ?? '' }}"
                                                    data-part="{{ $car->v_part_number_id??'' }}"
                                                    data-manufacturer="{{ $car->car_manufacturer }}"
                                                    data-model="{{ $car->car_model_name }}"
                                                    data-engine="{{ $car->engine_cc }}"
                                                    data-country="{{ $car->car_manufactured_country }}"
                                                    style="cursor: pointer;">
                                            </td>
                                            <td>{{ $car->manutacturer_vehical->name ?? '-' }}</td>

                                            <td>{{ $car->model_vehical->name ?? '-' }}</td>
                                            <td>
                                                @if($car->year_ranges && count($car->year_ranges) > 0)
                                                    <div style="display: inline-flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                                                        @foreach($car->year_ranges as $yearRange)
                                                            <span class="badge" style="background-color: #7DD3FC; color: #0C4A6E; padding: 6px 12px; border-radius: 6px; font-weight: 500; font-size: 13px; white-space: nowrap;">{{ $yearRange }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $car->engine_vehical->name ?? '-' }}</td>

                                            <td>{{ $car->country_vehical->name ?? '-' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary editVehicleBtn"
                                                    data-part="{{ $car->v_part_number_id }}"
                                                    data-manufacturer="{{ $car->car_manufacturer }}"
                                                    data-model="{{ $car->car_model_name }}"
                                                    data-engine="{{ $car->engine_cc }}"
                                                    data-country="{{ $car->car_manufactured_country }}"
                                                    data-year-ranges="{{ json_encode($car->year_ranges ?? []) }}"
                                                    data-year-from="{{ $car->year_from }}"
                                                    data-year-to="{{ $car->year_to }}">
                                                    <i class="ti ti-pencil"></i>
                                                </button>
                                            </td>
                                            <td>{{ $car->vehical_part_number->name ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Checkboxes
                        <div class="col-md-12 mt-4 field-group" :class="{ 'active': selectedType }">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="section-box">
                                    <div class="form-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                                            value="1" style="width: 20px; height:20px" {{
                                                    old('is_active', 1) ? 'checked' : '' }} />
                                        <label class="form-check-label ms-1 mt-1" for="isActive">Is
                                            Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="hidden" name="auto_deactive" value="0">
                                        <input class="form-check-input" type="checkbox" name="auto_deactive"
                                            id="autoDeactive" value="1" style="width: 20px; height:20px" {{
                                                    old('auto_deactive', 0) ? 'checked' : '' }} />
                                        <label class="form-check-label ms-1 mt-1" for="autoDeactive">Auto
                                            De-Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="hidden" name="is_dead" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_dead" id="isDead"
                                            value="1" style="width: 20px; height:20px" {{
                                                    old('is_dead', 0) ? 'checked' : '' }} />
                                        <label class="form-check-label ms-1 mt-1" for="isDead">Is Dead
                                            Item</label>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <div class="col-md-12 mt-3">
                            <label for="content" class="form-label fw-bold">Short Description</label>
                            <input name="short_disc" class="form-control" value="{{ old('short_disc') }}" />
                        </div>
                        <!-- Description -->
                        <div class="col-md-12 mt-3">
                            <label for="content" class="form-label fw-bold">Long Description</label>
                            <textarea id="summernote" name="pro_dis"
                                class="form-control">{{ old('pro_dis') }}</textarea>
                        </div>
                    </div>
                </div>
        </div>

        <!-- Submit Buttons -->
        <div class="page-btn d-flex justify-content-end mt-4">
            <a href="{{ route('all.items') }}" class="btn btn-secondary me-2">Cancel</a>
            {{-- <button type="submit" name="action" value="save" class="btn btn-primary">
                            Save
                        </button> --}}
            <button type="submit" name="action" value="save_new" class="btn btn-success">
                Save & New
            </button>
        </div>
    </div>
    </form>
    <h4 id="itemsTableTitle">Last 5 Created Items</h4>
    <div class="table-responsive">
        <table id="searchableTable" class="table table-hover table-center">
            <thead class="thead-primary">
                <tr>
                    <th>Product Image</th>
                    <th>Item Details</th>
                    
                   
                    <th>User Name</th>
                    <th>Actions</th>
                  
                </tr>
            </thead>
            <tbody id="latestItemsTableBody">
                @forelse ($latestItems as $item)
                <tr>

                    <td>
                        <img src="{{ asset($item->image ?? 'assets/img/media/default.png') }}" width="70" height="70"
                            class="rounded item-image" style="cursor:pointer;" data-bs-toggle="modal"
                            data-bs-target="#imageModal"
                            data-src="{{ asset($item->image ?? 'assets/img/media/default.png') }}">
                    </td>
                    <td>  
                        <div class="small">
                            <div> {{ $item->partnumber_item->name ?? '-' }}</div>
                            <div> {{ $item->category->name ?? '-' }}</div>
                            <div> {{ $item->company_item->name ?? '-' }}</div>
                            <div> {{ $item->quality_item->name ?? '-' }}</div>
                        </div>
                    </td>
                   
                    <td>{{ $item->item_user->name??'-' }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-primary  dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                {{-- <li>
                                            <a class="dropdown-item" href="">
                                                <i data-feather="tag" class="me-1"></i> Lable
                                            </a>
                                        </li> --}}
                                <li>
                                    <a class="dropdown-item" href="{{ route('item.show',$item->id) }}">
                                        <i data-feather="eye" class="me-1"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('item.edit',$item->id) }}">
                                        <i data-feather="edit" class="me-1"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" onclick="confirmDelete('delete-form-{{ $item->id }}')"
                                        class="p-2">
                                        <i data-feather="trash-2" class="feather-trash-2"></i> Delete
                                    </a>
                                    <!-- Hidden delete form -->
                                    <form id="delete-form-{{ $item->id }}"
                                        action="{{ route('item.delete', $item->id) }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </li>
                                <hr>
                                {{-- <li>
                                            <a class="dropdown-item" href="#">
                                                <i data-feather="package" class="me-1"></i> Add or Edit Open Stock
                                            </a>
                                        </li> --}}

                                {{-- <li>
                                            <a class="dropdown-item" href="#">
                                                <i data-feather="clock" class="me-1"></i> Product Stock History
                                            </a>
                                        </li> --}}

                                <li>
                                    <a class="dropdown-item text-primary"
                                        href="{{ route('item.duplicate', $item->id) }}">
                                        <i data-feather="copy" class="me-1"></i> Duplicate
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="text-center mt-3" id="allItemsButtonContainer" style="display: none;">
        <button type="button" class="btn btn-primary" id="loadAllItemsBtn">
            <i data-feather="list"></i> All Items
        </button>
    </div>
</div>
</div>
</div>
<div class="modal fade" id="Unit-add-modal">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="Unit-modal-title">Add Unit</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="Unit-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group col-12 mt-2">
                        <label>Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="name" required>
                    </div>
                    <div class="form-group col-12 mt-3">
                        <label>Short Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="short_name" required>
                    </div>
                    <div class="form-group col-12 mt-4">
                        <label>Allow Decimal <span class="text-danger">*</span></label>
                        <select name="allow_decimal" class="form-control" required>
                            <option value="1">Yes</option>
                            <option value="0" selected>No</option>
                        </select>
                    </div>
                    <div class="form-group col-12 mt-3">
                        <label>
                            <input type="checkbox" name="define_base_unit" value="1" id="toggleBaseUnit">
                            Add as multiple of another Unit
                        </label>
                    </div>
                    <div class="col-12 mt-4" id="baseDetails" style="display:none;">
                        <label class="fw-bold mb-2">Base Unit Options:</label>
                        <div id="baseUnitsContainer">
                            <div class="base-unit-item mb-3 p-3 border rounded">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="small">MULTIPLIER</label>
                                        <input type="number" step="0.01" name="base_units[0][multiplier]" class="form-control form-control-sm" placeholder="">
                                    </div>
                                    <div class="col-7">
                                        <label class="small">Base Unit</label>
                                        <select name="base_units[0][base_unit_id]" class="form-control form-control-sm">
                                            <option value="">Select Base Unit</option>
                                            @foreach($units as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm removeBaseUnit" style="display:none;">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="addBaseUnitBtn">
                            <i class="ti ti-plus"></i> Add Another Base Unit
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger d-none" id="deleteUnitBtn">
                        Delete
                    </button>

                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
        </div>

        </form>
    </div>
</div>
</div>

<div class="modal fade" id="vehical-add-modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="vehical-modal-title">Add Vehical</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="vehical-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="container">
                        <div class="row">
                            {{-- 1. Car Manufactured ---------------------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label for="car_manufacturer">Car Manufactured:</label>
                                <div class="input-group inputswidth">
                                    <select
                                        class="form-control car-manufacturer-select searchable-select @error('car_manufacturer') is-invalid @enderror"
                                        name="car_manufacturer" id="car_manufacturer">
                                        <option value="">Select Manufactured</option>
                                        @foreach ($carManufacturers as $manufacturer)
                                        <option value="{{ $manufacturer->id }}" {{
                                            old('car_manufacturer')==$manufacturer->id ?
                                            'selected' : '' }}>
                                            {{ $manufacturer->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Manufacturerd"
                                        data-fetch-route="{{ route('show.car.manufacturer', ':id') }}"
                                        data-update-route="{{ route('update.car.manufacturer', ':id') }}"
                                        data-delete-route="{{ route('destory.car.manufacturer', ':id') }}"
                                        data-target-select=".car-manufacturer-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('car_manufacturer') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- 2. Car Model ------------------------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label for="car_name">Car Model:</label>
                                <div class="input-group inputswidth">
                                    <select
                                        class="form-control car-model-select searchable-select @error('car_model_name') is-invalid @enderror"
                                        name="car_model_name" id="car_model_name">
                                        <option value="">Select Car Model</option>
                                        @foreach ($carModels as $item)
                                        <option value="{{ $item->id }}" {{ old('car_model_name')==$item->id ?
                                            'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Car Model"
                                        data-fetch-route="{{ route('show.car.model', ':id') }}"
                                        data-update-route="{{ route('update.car.model', ':id') }}"
                                        data-delete-route="{{ route('destory.car.model', ':id') }}"
                                        data-target-select=".car-model-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('car_model_name') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- 3. Car Manufactured Country --------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label for="car_manufactured_country">Car Manufactured Country:</label>
                                <div class="input-group inputswidth">
                                    <select
                                        class="form-control car-country-select searchable-select @error('car_manufactured_country') is-invalid @enderror"
                                        name="car_manufactured_country" id="car_manufactured_country">
                                        <option value="">Select Country</option>
                                        @foreach ($carCountries as $item)
                                        <option value="{{ $item->id }}" {{ old('car_manufactured_country')==$item->id ?
                                            'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Country"
                                        data-fetch-route="{{ route('show.car.country', ':id') }}"
                                        data-update-route="{{ route('update.car.country', ':id') }}"
                                        data-delete-route="{{ route('destory.car.country', ':id') }}"
                                        data-target-select=".car-country-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('car_manufactured_country') <div class="invalid-feedback">{{ $message }}
                                </div> @enderror
                            </div>
                            {{-- 4. Engine CC ------------------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label for="engine_cc">Engin CC:</label>
                                <div class="input-group inputswidth">
                                    <select
                                        class="form-control car-engine-select searchable-select @error('engine_cc') is-invalid @enderror"
                                        name="engine_cc" id="engine_cc">
                                        <option value="">Select Engine CC</option>
                                        @foreach ($engineccs as $item)
                                        <option value="{{ $item->id }}" {{ old('engine_cc')==$item->id ?
                                            'selected' : '' }}>
                                            {{ $item->name }} CC
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Engine CC"
                                        data-fetch-route="{{ route('show.engine_cc', ':id') }}"
                                        data-update-route="{{ route('update.engine_cc', ':id') }}"
                                        data-delete-route="{{ route('destory.engine_cc', ':id') }}"
                                        data-target-select=".car-engine-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('engine_cc') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- 6. Part Number ------------------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label for="part_number">Part Number: <span class="text-danger">*</span></label>
                                <div class="input-group inputswidth">
                                    <select
                                        class="form-control part_number-select searchable-select @error('v_part_number_id') is-invalid @enderror"
                                        name="v_part_number_id" id="part_number" required>
                                        <option value="">Select Part Number</option>
                                        @foreach ($partnumbers as $item)
                                        <option value="{{ $item->id }}" {{ old('v_part_number_id')==$item->id ?
                                            'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-secondary open-universal-modal"
                                        data-mode="edit" data-title="Edit Part Number"
                                        data-fetch-route="{{ route('show.partnumber', ':id') }}"
                                        data-update-route="{{ route('update.partnumber', ':id') }}"
                                        data-delete-route="{{ route('destory.partnumber', ':id') }}"
                                        data-target-select=".part_number-select">
                                        <i data-feather="edit"></i>
                                    </button>
                                </div>
                                @error('v_part_number_id') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- 5. YEAR RANGES ------------------------------------------------- --}}
                            <div class="col-md-6 mt-3">
                                <label><strong>YEAR RANGES</strong></label>
                                <div id="yearRangesContainer">
                                    <div class="year-range-item mb-2">
                                        <div class="row g-2">
                                            <div class="col-5">
                                                <select class="form-control year-from-select" name="year_from[]">
                                                    <option value="">From Year</option>
                                                    @for($year = 1900; $year <= 2100; $year++)
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="col-5">
                                                <select class="form-control year-to-select" name="year_to[]">
                                                    <option value="">To Year</option>
                                                    @for($year = 1900; $year <= 2100; $year++)
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" class="btn btn-danger btn-sm removeYearRange" style="display: none;">X</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="addYearRangeBtn" class="btn btn-primary btn-sm mt-2">
                                    + ADD ANOTHER YEAR RANGE
                                </button>
                                @error('carmanufactured_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                    {{-- <div class="table-responsive mt-5" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-bordered table-striped" id="vehicleTable">
                            <thead class="bg-primary fixed">
                                <tr>
                                    <th>Vehical Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($Vehicals as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                    </table>
                </div> --}}
        </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteVehicleBtn" style="display: none;">
                    Delete
                </button>

                <button type="submit" class="btn btn-primary" data-action="save">
                    Save
                </button>

                <button type="submit" class="btn btn-success" data-action="save_new">
                    Save & New
                </button>
            </div>

        <input type="hidden" id="submit_type" name="submit_type" value="save">
        </form>
    </div>
</div>
</div>
<!-- Universal Modal -->
<div class="modal fade" id="universal-add-modal" tabindex="-1" aria-labelledby="universal-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background-color: #ffffff !important; border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px 25px; border-bottom: none;">
                <div class="d-flex align-items-center w-100">
                    <div class="me-3" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="ti ti-plus" style="font-size: 20px;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0 fw-bold" id="universal-modal-title" style="color: white; font-size: 20px;">ADD NEW ENTRY</h4>
                        <small class="text-white-50" id="universal-modal-subtitle" style="font-size: 12px;">SMART ASSET REGISTRY</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.8; font-size: 18px;"></button>
                </div>
            </div>
            <form id="universal-form" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" id="universal-type" value="">
                <div class="modal-body" style="background-color: #ffffff !important; padding: 30px;">
                    <div class="form-group mb-4">
                        <label class="form-label fw-semibold mb-2" style="color: #495057; font-size: 14px;">
                            <i class="ti ti-tag me-2 text-primary"></i>Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               name="name" 
                               id="universal-name" 
                               required
                               placeholder="Search or Enter Name..."
                               style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 15px; transition: all 0.3s;"
                               onfocus="this.style.borderColor='#ff6b35'; this.style.boxShadow='0 0 0 0.2rem rgba(255, 107, 53, 0.25)'"
                               onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'">
                        <div class="invalid-feedback" id="universal-name-error"></div>
                    </div>
                    <div class="form-group mt-4" id="image-field" style="display: none;">
                        <label class="form-label fw-semibold mb-2" style="color: #495057; font-size: 14px;">
                            <i class="ti ti-photo me-2 text-primary"></i>Image
                        </label>
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-grow-1">
                                <input type="file" 
                                       class="form-control form-control-lg" 
                                       name="image" 
                                       id="universal-image" 
                                       accept="image/*"
                                       style="border-radius: 8px; border: 2px solid #e9ecef; padding: 12px 15px; font-size: 14px;">
                                <small class="text-muted d-block mt-2">
                                    <i class="ti ti-info-circle me-1"></i>Supported formats: JPG, PNG, GIF (Max 2MB)
                                </small>
                            </div>
                            <div class="text-center">
                                <img id="universal-image-preview" 
                                     src="" 
                                     alt="Preview"
                                     style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 2px solid #e9ecef; display:none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                <div id="universal-image-placeholder" style="width: 120px; height: 120px; background: #f8f9fa; border-radius: 10px; border: 2px dashed #dee2e6; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                    <i class="ti ti-photo" style="font-size: 32px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #ffffff !important; padding: 20px 30px; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                    <button type="button" class="btn btn-danger btn-lg d-none me-2" id="universal-delete-btn" style="border-radius: 8px; padding: 10px 20px; font-weight: 500;">
                        <i class="ti ti-trash me-2"></i>Delete
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg" id="universal-save-btn" style="border-radius: 8px; padding: 12px 30px; font-weight: 600; background: #ff6b35; border: none; box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4); color: white; font-size: 16px;">
                        <i class="ti ti-check me-2"></i><span>SAVE ENTRY</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Year Range Selector Modal -->
<div class="modal fade" id="yearRangeModal" tabindex="-1" aria-labelledby="yearRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="yearRangeModalLabel">Select Year Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="filterYearRangesContainer">
                    <div class="filter-year-range-item mb-2">
                        <div class="row g-2">
                            <div class="col-5">
                                <label class="form-label small">From Year</label>
                                <select class="form-control filter-year-from">
                                    <option value="">Select Year</option>
                                    @php
                                        $currentYear = date('Y');
                                        for($year = $currentYear; $year >= 1980; $year--) {
                                            echo '<option value="' . $year . '">' . $year . '</option>';
                                        }
                                    @endphp
                                </select>
                                <div class="invalid-feedback" style="display: none; font-size: 10px;">From Year cannot be greater than To Year</div>
                            </div>
                            <div class="col-5">
                                <label class="form-label small">To Year</label>
                                <select class="form-control filter-year-to">
                                    <option value="">Select Year</option>
                                    @php
                                        $currentYear = date('Y');
                                        for($year = $currentYear; $year >= 1980; $year--) {
                                            echo '<option value="' . $year . '">' . $year . '</option>';
                                        }
                                    @endphp
                                </select>
                                <div class="invalid-feedback" style="display: none; font-size: 10px;">To Year cannot be less than From Year</div>
                            </div>
                            <div class="col-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm removeFilterYearRange" style="display: none;">X</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm mt-2" id="addMoreFilterYearRange">
                    <i data-feather="plus" style="width: 14px; height: 14px;"></i>
                    Add More Year Range
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="applyYearRangeFilter">Apply Filter</button>
            </div>
        </div>
    </div>
</div>
<audio id="deleteSound" src="{{ asset('deleteaudio_ubWu5Ok3.mp3') }}" preload="auto"></audio>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        let currentUnitId = null;
        /* =========================
           ADD / UPDATE UNIT
        ==========================*/
        $("#Unit-form").off("submit").on("submit", function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Check if modal is actually visible
            if (!$('#Unit-add-modal').hasClass('show')) {
                return false;
            }
            
            // Validation: Check required fields
            const name = $('#Unit-form [name="name"]').val().trim();
            const shortName = $('#Unit-form [name="short_name"]').val().trim();
            
            if (!name || name === '') {
                toastr.error('Please enter Unit Name');
                $('#Unit-form [name="name"]').focus();
                $('#Unit-form [name="name"]').addClass('is-invalid');
                return false;
            } else {
                $('#Unit-form [name="name"]').removeClass('is-invalid');
            }
            
            if (!shortName || shortName === '') {
                toastr.error('Please enter Short Name');
                $('#Unit-form [name="short_name"]').focus();
                $('#Unit-form [name="short_name"]').addClass('is-invalid');
                return false;
            } else {
                $('#Unit-form [name="short_name"]').removeClass('is-invalid');
            }
            
            // Remove items without selected base unit (preserve order as entered by user)
            $('#baseUnitsContainer .base-unit-item').each(function() {
                const $item = $(this);
                const baseUnitId = $item.find('select[name*="[base_unit_id]"]').val();
                if (!baseUnitId || baseUnitId === '') {
                    $item.remove();
                }
            });
            
            // Preserve the order as entered by user - do NOT sort or auto-sequence multipliers
            
            let formData = new FormData(this);
            let url = currentUnitId ?
                `/units/${currentUnitId}` :
                `{{ route('post.units') }}`;
            if (currentUnitId) {
                formData.append('_method', 'PUT');
            }
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        // Reload units dropdown (this will show all base unit variations)
                        loadUnitsForDropdown();
                        
                        // Select the newly created/updated unit after dropdown reloads
                        setTimeout(function() {
                            if (res.unit && res.unit.id) {
                                const unitId = res.unit.id;
                                
                                // Wait for dropdown to fully load
                                setTimeout(function() {
                                    // Find and select the first base unit variation (lowest multiplier)
                                    let found = false;
                                    let firstOption = null;
                                    let lowestMultiplier = Infinity;
                                    
                                    $('#unit_parts option').each(function() {
                                        const optionUnitId = $(this).attr('data-unit-id');
                                        if (optionUnitId == unitId) {
                                            const multiplier = parseFloat($(this).attr('data-multiplier')) || 0;
                                            if (multiplier < lowestMultiplier) {
                                                lowestMultiplier = multiplier;
                                                firstOption = $(this);
                                            }
                                            found = true;
                                        }
                                    });
                                    
                                    // Select the first variation (lowest multiplier)
                                    if (firstOption) {
                                        $('#unit_parts').val(firstOption.val()).trigger('change');
                                    } else if (found) {
                                        // Fallback: select any variation
                                $('#unit_parts option').each(function() {
                                    if ($(this).attr('data-unit-id') == unitId) {
                                        $('#unit_parts').val($(this).val()).trigger('change');
                                                return false; // break
                                    }
                                });
                            }
                                    
                                    // If still not found, reload dropdown again
                                    if (!found) {
                                        setTimeout(function() {
                                            loadUnitsForDropdown();
                                            setTimeout(function() {
                                                $('#unit_parts option').each(function() {
                                                    if ($(this).attr('data-unit-id') == unitId) {
                                                        $('#unit_parts').val($(this).val()).trigger('change');
                                                        return false;
                                                    }
                                                });
                        }, 500);
                                        }, 1000);
                                    }
                                }, 500);
                            }
                        }, 1000);
                        
                        $('#Unit-add-modal').modal('hide');
                        $('#Unit-form')[0].reset();
                        currentUnitId = null;
                        
                        // Only show success message if modal was actually open
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Unit saved successfully'
                        });
                        // 🔊 Play save sound when unit is saved
                        if (typeof playSaveSound === 'function') {
                            playSaveSound();
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Unit save error', xhr);
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                        // Remove invalid class from fields if duplicate error
                        if (xhr.responseJSON.message.includes('name already exists')) {
                            $('#Unit-form [name="name"]').addClass('is-invalid');
                        }
                        if (xhr.responseJSON.message.includes('short name already exists')) {
                            $('#Unit-form [name="short_name"]').addClass('is-invalid');
                        }
                    } else {
                        toastr.error('Failed to save unit. Please try again.');
                    }
                }
            });
        });
        /* =========================
           EDIT UNIT
        ==========================*/
        $('#editUnitBtn').on('click', function() {
            let selected = $('#unit_parts option:selected');
            if (!selected.val()) {
                Swal.fire('Select Unit', 'Please select a unit first', 'warning');
                return;
            }
            
            // Extract unit ID from option value (format: unitId_baseUnitId or unitId_main)
            const selectedValue = selected.val();
            const unitIdFromOption = selected.attr('data-unit-id') || selectedValue.split('_')[0];
            currentUnitId = unitIdFromOption;
            
            $('#Unit-modal-title').text('Edit Unit');
            $('#deleteUnitBtn').removeClass('d-none');
            
            // Fetch unit details via AJAX
            $.ajax({
                url: `/units/${currentUnitId}`,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(res) {
                    if (res && res.unit) {
                        const unit = res.unit;
                        $('#Unit-form [name="name"]').val(unit.name);
                        $('#Unit-form [name="short_name"]').val(unit.short_name);
                        
                        // Set allow_decimal value (1 for Yes, 0 for No)
                        // Handle both boolean and numeric values
                        let allowDecimalValue;
                        if (unit.allow_decimal === true || unit.allow_decimal === 1 || unit.allow_decimal === '1') {
                            allowDecimalValue = '1';
                        } else {
                            allowDecimalValue = '0';
                        }
                        
                        const $allowDecimalSelect = $('#Unit-form [name="allow_decimal"]');
                        
                        // First, deselect all options
                        $allowDecimalSelect.find('option').prop('selected', false);
                        
                        // Then select the correct option
                        $allowDecimalSelect.find('option[value="' + allowDecimalValue + '"]').prop('selected', true);
                        $allowDecimalSelect.val(allowDecimalValue);
                        
                        // Force update the native select element
                        const nativeSelect = $allowDecimalSelect[0];
                        if (nativeSelect) {
                            nativeSelect.value = allowDecimalValue;
                            // Trigger change event to ensure UI updates
                            nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        
                        // Log for debugging
                        console.log('Setting allow_decimal to:', allowDecimalValue, 'from unit.allow_decimal:', unit.allow_decimal);
                        
                        // Clear existing base units
                        $('#baseUnitsContainer').empty();
                        baseUnitIndex = 0;
                        
                        // Check if unit has base units
                        const hasBaseUnits = unit.base_units && unit.base_units.length > 0;
                        
                        // Auto-check define_base_unit checkbox ONLY if base units exist
                        if (hasBaseUnits) {
                            $('#Unit-form [name="define_base_unit"]').prop('checked', true);
                            $('#baseDetails').show();
                            
                            // Load base units
                            unit.base_units.forEach(function(baseUnit) {
                                const multiplier = baseUnit.pivot?.multiplier || baseUnit.multiplier || 1;
                                // baseUnit.id is the actual unit ID from the baseUnits relationship
                                addBaseUnitRow(baseUnit.id, multiplier);
                            });
                            // Update labels for all loaded base units
                            setTimeout(function() {
                                $('#baseUnitsContainer select[name*="[base_unit_id]"]').each(function() {
                                    if ($(this).val()) {
                                        updateMultiplierLabel(this);
                                    }
                                });
                            }, 150);
                        } else {
                            // No base units - uncheck checkbox and hide base details
                            $('#Unit-form [name="define_base_unit"]').prop('checked', false);
                            $('#baseDetails').hide();
                        }
                        
                        updateUnitRemoveButtons();
                        
                        // Show modal first
                        $('#Unit-add-modal').modal('show');
                        
                        // After modal is shown, re-verify allow_decimal value and update multiplier inputs
                        setTimeout(function() {
                            // Re-set allow_decimal value after modal is shown to ensure it persists
                            const $allowDecimalSelect = $('#Unit-form [name="allow_decimal"]');
                            $allowDecimalSelect.find('option[value="' + allowDecimalValue + '"]').prop('selected', true);
                            $allowDecimalSelect.val(allowDecimalValue);
                            if ($allowDecimalSelect[0]) {
                                $allowDecimalSelect[0].value = allowDecimalValue;
                            }
                            
                            // Trigger change event to ensure all related functions run
                            $allowDecimalSelect.trigger('change');
                            updateMultiplierInputs();
                        }, 300);
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching unit:', xhr);
                    // Fallback to using option data
                    const unitName = selected.attr('data-unit-name') || selected.text().split('(')[0].trim();
                    const shortName = selected.attr('data-unit-short') || '';
                    $('#Unit-form [name="name"]').val(unitName);
                    $('#Unit-form [name="short_name"]').val(shortName);
                    $('#Unit-form [name="allow_decimal"]').val(selected.attr('data-allow-decimal') || '0');
                    
                    // Check if there's a base unit from the selected option
                    const baseUnitId = selected.attr('data-base-unit-id');
                    if (baseUnitId) {
                        $('#Unit-form [name="define_base_unit"]').prop('checked', true);
                        $('#baseDetails').show();
                        $('#baseUnitsContainer').empty();
                        baseUnitIndex = 0;
                        const multiplier = selected.attr('data-multiplier') || 1;
                        addBaseUnitRow(baseUnitId, multiplier);
                    } else {
                        $('#Unit-form [name="define_base_unit"]').prop('checked', false);
                        $('#baseDetails').hide();
                    }
                    
                    $('#Unit-add-modal').modal('show');
                }
            });
        });
        
        // Helper function to add base unit row
        function addBaseUnitRow(baseUnitId, multiplier) {
            const container = $('#baseUnitsContainer');
            const newItem = $(`
                <div class="base-unit-item mb-3 p-3 border rounded">
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="small">MULTIPLIER</label>
                            <input type="number" step="0.01" name="base_units[${baseUnitIndex}][multiplier]" class="form-control form-control-sm" value="${multiplier || ''}" placeholder="">
                        </div>
                        <div class="col-7">
                            <label class="small">Base Unit</label>
                            <select name="base_units[${baseUnitIndex}][base_unit_id]" class="form-control form-control-sm">
                                <option value="">Select Base Unit</option>
                                @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm removeBaseUnit" style="display:none;">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
            container.append(newItem);
            
            // Set selected value
            if (baseUnitId) {
                newItem.find('select[name="base_units[' + baseUnitIndex + '][base_unit_id]"]').val(baseUnitId);
                // Update label after setting value
                setTimeout(function() {
                    updateMultiplierLabel(newItem.find('select[name="base_units[' + baseUnitIndex + '][base_unit_id]"]')[0]);
                }, 50);
            }
            
            baseUnitIndex++;
        }
        /* =========================
           DELETE UNIT
        ==========================*/
        $('#deleteUnitBtn').on('click', function() {
            if (!currentUnitId) return;
            Swal.fire({
                title: 'Are you sure?',
                text: 'This unit will be deleted',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/units/${currentUnitId}`,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('input[name=_token]').val()
                        },
                        success: function(res) {
                            if (res.success) {
                                // 🔊 Play delete sound
                                const audio = document.getElementById(
                                'deleteSound');
                                audio.currentTime = 0;
                                audio.play();
                                
                                // Remove all options related to this unit (including base unit variations)
                                $('#unit_parts option').each(function() {
                                    const optionUnitId = $(this).attr('data-unit-id');
                                    if (optionUnitId == currentUnitId) {
                                        $(this).remove();
                                    }
                                });
                                
                                // Reset selected value if deleted unit was selected
                                const currentSelected = $('#unit_parts option:selected');
                                if (!currentSelected.val() || currentSelected.attr('data-unit-id') == currentUnitId) {
                                    $('#unit_parts').val('').trigger('change');
                                }
                                
                                // Fully refresh the dropdown to ensure consistency
                                loadUnitsForDropdown();
                                
                                $('#Unit-add-modal').modal('hide');
                                $('#Unit-form')[0].reset();
                                currentUnitId = null;
                                Swal.fire('Deleted!', 'Unit deleted successfully',
                                    'success');
                            }
                        }
                    });
                }
            });
        });
        /* =========================
           RESET MODAL
        ==========================*/
        $('#Unit-add-modal').on('hidden.bs.modal', function() {
            $('#Unit-form')[0].reset();
            // Set allow_decimal to "No" by default
            $('#Unit-form [name="allow_decimal"]').val('0');
            $('#deleteUnitBtn').addClass('d-none');
            $('#Unit-modal-title').text('Add Unit');
            currentUnitId = null;
            
            // Reset base units container
            $('#baseUnitsContainer').empty();
            baseUnitIndex = 0;
            // Load units and add one empty base unit row
            $.ajax({
                url: '{{ route("api.units.search") }}',
                data: { search: '' },
                success: function(data) {
                    // Get unique units
                    const uniqueUnits = {};
                    data.forEach(function(item) {
                        if (!uniqueUnits[item.id]) {
                            uniqueUnits[item.id] = {
                                id: item.id,
                                name: item.name,
                                short_name: item.short_name,
                                display_text: item.name + ' (' + item.short_name + ')'
                            };
                        }
                    });
                    const unitsArray = Object.values(uniqueUnits);
                    
                    // Build options HTML
                    let optionsHtml = '<option value="">Select Base Unit</option>';
                    unitsArray.forEach(function(unit) {
                        optionsHtml += '<option value="' + unit.id + '">' + unit.display_text + '</option>';
                    });
                    
            const container = $('#baseUnitsContainer');
            container.html(`
                <div class="base-unit-item mb-3 p-3 border rounded">
                    <div class="row g-2">
                                <div class="col-4">
                            <label class="small">Multiplier</label>
                                    <input type="number" step="0.01" name="base_units[0][multiplier]" class="form-control form-control-sm" placeholder="">
                        </div>
                                <div class="col-7">
                            <label class="small">Base Unit</label>
                            <select name="base_units[0][base_unit_id]" class="form-control form-control-sm">
                                        ${optionsHtml}
                            </select>
                        </div>
                        <div class="col-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm removeBaseUnit" style="display:none;">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
                }
            });
            
            // Add validation to the initial multiplier input
            const initialInput = container.find('input[name*="[multiplier]"]')[0];
            if (initialInput) {
                addMultiplierValidation(initialInput);
            }
            
            // Update multiplier inputs based on allow_decimal setting
            setTimeout(function() {
                updateMultiplierInputs();
            }, 100);
            
            $('#baseDetails').hide();
            $('#toggleBaseUnit').prop('checked', false);
        });
    });
</script>
<script>
    document.getElementById('imageInput').addEventListener('change', function(evt) {
        const file = evt.target.files[0];
        const imgURL = URL.createObjectURL(file);
        const imgPreview = document.getElementById('imagePreview');
        imgPreview.src = imgURL;
        imgPreview.style.display = 'block';
    });
    document.getElementById('toggleBaseUnit').addEventListener("change", function() {
        document.getElementById('baseDetails').style.display = this.checked ? "block" : "none";
    });
    
    // Function to update multiplier inputs based on allow_decimal setting
    function updateMultiplierInputs() {
        const allowDecimal = $('#Unit-form [name="allow_decimal"]').val();
        const container = document.getElementById('baseUnitsContainer');
        if (!container) return;
        
        const multiplierInputs = container.querySelectorAll('input[name*="[multiplier]"]');
        multiplierInputs.forEach(function(input) {
            if (allowDecimal === '0' || allowDecimal === 0) {
                // No decimal allowed - set step to 1 and remove any decimal values
                input.setAttribute('step', '1');
                input.setAttribute('min', '1');
                // Remove decimal part if exists
                if (input.value && input.value.includes('.')) {
                    input.value = Math.floor(parseFloat(input.value));
                }
            } else {
                // Decimal allowed - set step to 0.01
                input.setAttribute('step', '0.01');
                input.removeAttribute('min');
            }
        });
    }
    
    // Listen for allow_decimal changes
    $(document).on('change', '#Unit-form [name="allow_decimal"]', function() {
        updateMultiplierInputs();
    });
    
    // Add Base Unit functionality for Unit Modal
    let baseUnitIndex = 1;
    document.getElementById('addBaseUnitBtn')?.addEventListener('click', function() {
        const container = document.getElementById('baseUnitsContainer');
        if (!container) return;
        
        // Get the last base unit item to clone selected value from (only base unit, not multiplier)
        const lastItem = container.querySelector('.base-unit-item:last-child');
        let clonedBaseUnitId = '';
        
        if (lastItem) {
            const lastBaseUnitSelect = lastItem.querySelector('select[name*="[base_unit_id]"]');
            if (lastBaseUnitSelect && lastBaseUnitSelect.value) {
                clonedBaseUnitId = lastBaseUnitSelect.value;
            }
        }
        
        // Load units dynamically via AJAX
        $.ajax({
            url: '{{ route("api.units.search") }}',
            data: { search: '' },
            success: function(data) {
                // Get unique units
                const uniqueUnits = {};
                data.forEach(function(item) {
                    if (!uniqueUnits[item.id]) {
                        uniqueUnits[item.id] = {
                            id: item.id,
                            name: item.name,
                            short_name: item.short_name,
                            display_text: item.name + ' (' + item.short_name + ')'
                        };
                    }
                });
                const unitsArray = Object.values(uniqueUnits);
                
                // Build options HTML
                let optionsHtml = '<option value="">Select Base Unit</option>';
                unitsArray.forEach(function(unit) {
                    optionsHtml += '<option value="' + unit.id + '">' + unit.display_text + '</option>';
                });
        
        const newItem = document.createElement('div');
        newItem.className = 'base-unit-item mb-3 p-3 border rounded';
                newItem.style.opacity = '0'; // Start hidden for fade-in
        newItem.innerHTML = `
            <div class="row g-2">
                        <div class="col-4">
                            <label class="small">MULTIPLIER</label>
                            <input type="number" step="0.01" name="base_units[${baseUnitIndex}][multiplier]" class="form-control form-control-sm" value="" placeholder="">
                        </div>
                        <div class="col-7">
                            <label class="small">Base Unit</label>
                            <select name="base_units[${baseUnitIndex}][base_unit_id]" class="form-control form-control-sm">
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="col-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm removeBaseUnit">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(newItem);
                
                // Set the cloned base unit value if available
                if (clonedBaseUnitId) {
                    const newSelect = newItem.querySelector('select[name*="[base_unit_id]"]');
                    if (newSelect) {
                        newSelect.value = clonedBaseUnitId;
                        // Update label after setting value
                        setTimeout(function() {
                            updateMultiplierLabel(newSelect);
                        }, 50);
                    }
                }
                
                // Fade in the new item
                setTimeout(() => {
                    newItem.style.transition = 'opacity 0.3s ease-in-out';
                    newItem.style.opacity = '1';
                }, 10);
                
                // Add unique multiplier validation to the new input
                const newMultiplierInput = newItem.querySelector('input[name*="[multiplier]"]');
                if (newMultiplierInput) {
                    addMultiplierValidation(newMultiplierInput);
                    // Update step based on allow_decimal setting
                    updateMultiplierInputs();
                }
                
                baseUnitIndex++;
                updateUnitRemoveButtons();
                
                // Smooth scroll to the newly added item
                setTimeout(function() {
                    newItem.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }, 100);
            },
            error: function() {
                // Fallback to empty options if API fails
                const newItem = document.createElement('div');
                newItem.className = 'base-unit-item mb-3 p-3 border rounded';
                newItem.style.opacity = '0';
                newItem.innerHTML = `
                    <div class="row g-2">
                        <div class="col-4">
                    <label class="small">Multiplier</label>
                            <input type="number" step="0.01" name="base_units[${baseUnitIndex}][multiplier]" class="form-control form-control-sm" value="" placeholder="">
                </div>
                        <div class="col-7">
                    <label class="small">Base Unit</label>
                    <select name="base_units[${baseUnitIndex}][base_unit_id]" class="form-control form-control-sm">
                        <option value="">Select Base Unit</option>
                    </select>
                </div>
                <div class="col-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm removeBaseUnit">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newItem);
                
                if (clonedBaseUnitId) {
                    const newSelect = newItem.querySelector('select[name*="[base_unit_id]"]');
                    if (newSelect) {
                        newSelect.value = clonedBaseUnitId;
                    }
                }
                
                setTimeout(() => {
                    newItem.style.transition = 'opacity 0.3s ease-in-out';
                    newItem.style.opacity = '1';
                }, 10);
                
                const newMultiplierInput = newItem.querySelector('input[name*="[multiplier]"]');
                if (newMultiplierInput) {
                    addMultiplierValidation(newMultiplierInput);
                    updateMultiplierInputs();
                }
                
        baseUnitIndex++;
        updateUnitRemoveButtons();
                
                setTimeout(function() {
                    newItem.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }, 100);
            }
        });
    });
    
    // Function to update multiplier label based on selected base unit
    function updateMultiplierLabel(selectElement) {
        const $select = $(selectElement);
        const $item = $select.closest('.base-unit-item');
        const $label = $item.find('label.small').first(); // Get the multiplier label
        const selectedOption = $select.find('option:selected');
        
        if (selectedOption.val() && selectedOption.val() !== '') {
            const baseUnitName = selectedOption.text().trim();
            // Update label to show "PER [Base Unit Name]" (without MULTIPLIER)
            $label.text('PER ' + baseUnitName.toUpperCase());
        } else {
            // Reset to default if no base unit selected
            $label.text('MULTIPLIER');
        }
    }
    
    // Function to add unique multiplier validation to an input
    function addMultiplierValidation(inputElement) {
        // Limit to 2 decimal places
        inputElement.addEventListener('input', function() {
            const value = this.value;
            if (value.includes('.')) {
                const parts = value.split('.');
                if (parts[1] && parts[1].length > 2) {
                    this.value = parseFloat(value).toFixed(2);
                }
            }
            validateMultiplierUniqueness(this);
        });
        
        inputElement.addEventListener('blur', function() {
            const value = this.value;
            if (value && value.includes('.')) {
                const parts = value.split('.');
                if (parts[1] && parts[1].length > 2) {
                    this.value = parseFloat(value).toFixed(2);
                }
            }
            validateMultiplierUniqueness(this);
        });
        
        // Handle Enter key press - trigger "Add Another Base Unit" button
        inputElement.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                e.stopPropagation();
                
                // Trigger the "Add Another Base Unit" button
                const addBtn = document.getElementById('addBaseUnitBtn');
                if (addBtn) {
                    addBtn.click();
                }
                
                return false;
            }
        });
    }
    
    // Event delegation for base unit select changes - update multiplier label
    $(document).on('change', '#baseUnitsContainer select[name*="[base_unit_id]"]', function() {
        updateMultiplierLabel(this);
    });
    
    // Function to validate multiplier uniqueness
    function validateMultiplierUniqueness(inputElement) {
        const container = document.getElementById('baseUnitsContainer');
        if (!container) return;
        
        const currentValue = inputElement.value.trim();
        if (!currentValue) {
            // Clear error if value is empty
            inputElement.classList.remove('is-invalid');
            const errorMsg = inputElement.parentElement.querySelector('.multiplier-error');
            if (errorMsg) {
                errorMsg.remove();
            }
            return;
        }
        
        const allItems = container.querySelectorAll('.base-unit-item');
        let isDuplicate = false;
        
        allItems.forEach(function(item) {
            const multiplierInput = item.querySelector('input[name*="[multiplier]"]');
            if (multiplierInput && multiplierInput !== inputElement && multiplierInput.value.trim() === currentValue) {
                isDuplicate = true;
            }
        });
        
        // Remove previous error message
        const existingError = inputElement.parentElement.querySelector('.multiplier-error');
        if (existingError) {
            existingError.remove();
        }
        
        if (isDuplicate) {
            inputElement.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'multiplier-error text-danger small mt-1';
            errorDiv.textContent = 'This multiplier value already exists. Please enter a unique value.';
            inputElement.parentElement.appendChild(errorDiv);
        } else {
            inputElement.classList.remove('is-invalid');
        }
    }
    
    // Remove Base Unit functionality for Unit Modal
    function updateUnitRemoveButtons() {
        const container = document.getElementById('baseUnitsContainer');
        if (!container) return;
        
        const allItems = container.querySelectorAll('.base-unit-item');
        allItems.forEach(function(item) {
            const removeBtn = item.querySelector('.removeBaseUnit');
            if (allItems.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }
    
    // Event delegation for remove buttons in Unit Modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('.removeBaseUnit') && e.target.closest('#baseUnitsContainer')) {
            const removedItem = e.target.closest('.base-unit-item');
            const removedMultiplierInput = removedItem.querySelector('input[name*="[multiplier]"]');
            const removedMultiplierValue = removedMultiplierInput ? removedMultiplierInput.value.trim() : '';
            
            // Remove the item
            removedItem.remove();
            updateUnitRemoveButtons();
            
            // Re-validate all remaining multiplier inputs after removal
            const container = document.getElementById('baseUnitsContainer');
            if (container) {
                const allInputs = container.querySelectorAll('input[name*="[multiplier]"]');
                
                // First, clear any duplicate values that match the removed value
                allInputs.forEach(function(input) {
                    if (input.value.trim() === removedMultiplierValue && removedMultiplierValue !== '') {
                        input.value = '';
                        input.classList.remove('is-invalid');
                        // Remove error message if exists
                        const errorMsg = input.parentElement.querySelector('.multiplier-error');
                        if (errorMsg) {
                            errorMsg.remove();
                        }
                    }
                });
                
                // Then validate all remaining inputs for uniqueness
                allInputs.forEach(function(input) {
                    validateMultiplierUniqueness(input);
                });
            }
        }
    });
    
    // Initialize remove buttons on page load
    updateUnitRemoveButtons();
    
    // Add validation to existing multiplier inputs on page load
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('baseUnitsContainer');
        if (container) {
            const existingInputs = container.querySelectorAll('input[name*="[multiplier]"]');
            existingInputs.forEach(function(input) {
                addMultiplierValidation(input);
            });
        }
    });
    
    // Also add validation when modal is shown (in case inputs are added dynamically before)
    // Auto-generate short name from name input
    $(document).on('input', '#Unit-form [name="name"]', function() {
        const nameValue = $(this).val();
        const shortNameInput = $('#Unit-form [name="short_name"]');
        
        // Only auto-fill if short name is empty or matches previous name's short form
        if (!shortNameInput.data('manually-edited')) {
            // Generate short name: take first 3-4 characters or first letter of each word
            let shortName = '';
            if (nameValue) {
                const trimmedName = nameValue.trim().toLowerCase();
                // Check for specific unit names
                if (trimmedName === 'can' || trimmedName.startsWith('can ')) {
                    shortName = 'CN';
                } else if (trimmedName === 'piece' || trimmedName.startsWith('piece ')) {
                    shortName = 'PCS';
                } else if (trimmedName === 'liter' || trimmedName.startsWith('liter ')) {
                    shortName = 'LTR';
                } else {
                    const words = nameValue.trim().split(/\s+/);
                    if (words.length > 1) {
                        // If multiple words, take first letter of each word
                        shortName = words.map(word => word.charAt(0).toUpperCase()).join('');
                    } else {
                        // If single word, take first 3-4 characters
                        shortName = nameValue.trim().substring(0, 4).toUpperCase();
                    }
                }
            }
            shortNameInput.val(shortName);
        }
    });
    
    // Track manual edits to short name
    $(document).on('input', '#Unit-form [name="short_name"]', function() {
        $(this).data('manually-edited', true);
        // Remove invalid class if field has value
        if ($(this).val().trim() !== '') {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Remove invalid class when name field is filled
    $(document).on('input', '#Unit-form [name="name"]', function() {
        if ($(this).val().trim() !== '') {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Format name on blur: capitalize first letter of each word
    $(document).on('blur', '#Unit-form [name="name"]', function() {
        let nameValue = $(this).val().trim();
        if (nameValue) {
            // Capitalize first letter of each word
            nameValue = nameValue.split(' ').map(word => {
                return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
            }).join(' ');
            $(this).val(nameValue);
        }
    });
    
    // Reset manual edit flag when modal opens in add mode
    $('#Unit-add-modal').on('shown.bs.modal', function() {
        // Set allow_decimal to "No" by default if in add mode (not edit mode)
        const modalTitle = $('#Unit-modal-title').text();
        const isEditMode = modalTitle === 'Edit Unit' && currentUnitId;
        
        // Only reset in add mode, NEVER in edit mode
        if (!isEditMode) {
            const $allowDecimalSelect = $('#Unit-form [name="allow_decimal"]');
            const currentValue = $allowDecimalSelect.val();
            
            // Only reset if value is empty or not set (to avoid overwriting edit mode values)
            if (!currentValue || currentValue === '' || currentValue === null) {
                $allowDecimalSelect.val('0');
                if ($allowDecimalSelect[0]) {
                    $allowDecimalSelect[0].value = '0';
                }
                // Ensure the option is visually selected
                $allowDecimalSelect.find('option[value="0"]').prop('selected', true);
            }
            $('#Unit-form [name="short_name"]').data('manually-edited', false);
        }
        
        // Update Base Unit dropdowns when modal opens
        $.ajax({
            url: '{{ route("api.units.search") }}',
            data: { search: '' },
            success: function(data) {
                const uniqueUnits = {};
                data.forEach(function(item) {
                    if (!uniqueUnits[item.id]) {
                        uniqueUnits[item.id] = {
                            id: item.id,
                            name: item.name,
                            short_name: item.short_name,
                            display_text: item.name + ' (' + item.short_name + ')'
                        };
                    }
                });
                updateBaseUnitDropdowns(uniqueUnits);
            }
        });
        
        // Update multiplier inputs based on allow_decimal setting
        setTimeout(function() {
            updateMultiplierInputs();
        }, 100);
        
        const container = document.getElementById('baseUnitsContainer');
        if (container) {
            const existingInputs = container.querySelectorAll('input[name*="[multiplier]"]');
            existingInputs.forEach(function(input) {
                // Remove existing listeners to avoid duplicates
                const newInput = input.cloneNode(true);
                input.parentNode.replaceChild(newInput, input);
                addMultiplierValidation(newInput);
            });
        }
    });
    
    // Initialize unit dropdown
    $(document).ready(function() {
        // Set initial state - hide base unit containers and make sections side by side
        $('#baseCostPriceContainer').hide();
        $('#baseSalePriceContainer').hide();
        $('#costPriceManagement').removeClass('col-12 mb-4').addClass('col-12 col-md-6 mb-0');
        $('#salePriceManagement').removeClass('col-12 mb-4').addClass('col-12 col-md-6 mb-0');
        $('#priceManagementContainer').addClass('row g-3');
        
        // Load units from API
        loadUnitsForDropdown();
        
        // Handle unit change
        $('#unit_parts').on('change', handleUnitChange);
        
        // Initialize searchable-select if the class exists
        if ($.fn.select2 && $('#unit_parts').hasClass('searchable-select')) {
            $('#unit_parts').select2({
                placeholder: '-- PLEASE SELECT --',
                allowClear: true,
                width: '100%'
            });
        }
    });
    
    // Load units with conversions from API
    function loadUnitsForDropdown() {
        $.ajax({
            url: '{{ route("api.units.search") }}',
            data: { search: '' },
            success: function(data) {
                // Store current value before clearing
                const currentValue = $('#unit_parts').val();
                
                // Clear and rebuild options
                $('#unit_parts').empty().append('<option value="">-- PLEASE SELECT --</option>');
                
                // Keep track of unique unit IDs to avoid duplicates
                const uniqueUnits = {};
                
                data.forEach(function(item) {
                    const optionId = item.id + '_' + (item.base_unit_id || 'main');
                    const option = new Option(item.display_text, optionId, false, false);
                    option.setAttribute('data-unit-id', item.id);
                    option.setAttribute('data-unit-name', item.name);
                    option.setAttribute('data-unit-short', item.short_name);
                    option.setAttribute('data-base-unit-id', item.base_unit_id || '');
                    option.setAttribute('data-base-unit-name', item.base_unit_name || '');
                    option.setAttribute('data-multiplier', item.multiplier || '');
                    option.setAttribute('data-decimal-places', item.decimal_places || 0);
                    option.setAttribute('data-allow-decimal', item.allow_decimal || 0);
                    $('#unit_parts').append(option);
                    
                    // Store unique units for Base Unit dropdown
                    if (!uniqueUnits[item.id]) {
                        uniqueUnits[item.id] = {
                            id: item.id,
                            name: item.name,
                            short_name: item.short_name,
                            display_text: item.name + ' (' + item.short_name + ')'
                        };
                    }
                });
                
                // Update Base Unit dropdowns with unique units
                updateBaseUnitDropdowns(uniqueUnits);
                
                // Trigger Select2 update to refresh the dropdown
                if ($.fn.select2 && $('#unit_parts').hasClass('select2-hidden-accessible')) {
                    $('#unit_parts').trigger('change.select2');
                }
            }
        });
    }
    
    // Function to update all Base Unit dropdowns in the Unit modal
    function updateBaseUnitDropdowns(unitsMap) {
        // Convert unitsMap object to array
        const unitsArray = Object.values(unitsMap);
        
        // Update all Base Unit dropdowns in the modal
        $('#baseUnitsContainer select[name*="[base_unit_id]"]').each(function() {
            const $select = $(this);
            const currentValue = $select.val(); // Store current selection
            
            // Clear existing options except the first one
            $select.find('option:not(:first)').remove();
            
            // Add all units
            unitsArray.forEach(function(unit) {
                const option = new Option(unit.display_text, unit.id, false, false);
                $select.append(option);
            });
            
            // Restore previous selection if it still exists
            if (currentValue) {
                $select.val(currentValue);
            }
        });
    }
    
    // Handle unit selection change
    function handleUnitChange() {
        const selectedOption = $('#unit_parts option:selected');
        if (!selectedOption.val() || selectedOption.val() === '') {
            resetPriceFields();
            return;
        }
        
        const unitName = selectedOption.attr('data-unit-name');
        const baseUnitName = selectedOption.attr('data-base-unit-name');
        const multiplier = parseFloat(selectedOption.attr('data-multiplier')) || 1;
        const decimalPlaces = parseInt(selectedOption.attr('data-decimal-places')) || 2;
        
        // Update labels
        $('#costUnitLabel').text(`${unitName} COST:`);
        $('#saleUnitLabel').text(`${unitName} SALE:`);
        
        // Check if base unit exists
        const hasBaseUnit = baseUnitName && multiplier && multiplier > 0;
        
        if (hasBaseUnit) {
            // Show base unit fields
            $('#costBaseLabel').text(`PER ${baseUnitName} COST:`);
            $('#saleBaseLabel').text(`PER ${baseUnitName} SALE:`);
            $('#baseCostPriceContainer').show();
            $('#baseSalePriceContainer').show();
            
            // Make Cost and Sale sections full width (stacked vertically)
            $('#costPriceManagement').removeClass('col-12 col-md-6 mb-0').addClass('col-12 mb-4');
            $('#salePriceManagement').removeClass('col-12 col-md-6 mb-0').addClass('col-12 mb-4');
        } else {
            // Hide base unit fields
            $('#costBaseLabel').text(`PER ${unitName} COST:`);
            $('#saleBaseLabel').text(`PER ${unitName} SALE:`);
            $('#baseCostPriceContainer').hide();
            $('#baseSalePriceContainer').hide();
            
            // Make Cost and Sale sections side by side
            $('#costPriceManagement').removeClass('col-12 mb-4').addClass('col-12 col-md-6 mb-0');
            $('#salePriceManagement').removeClass('col-12 mb-4').addClass('col-12 col-md-6 mb-0');
        }
        
        // Reset prices
        $('#costPrice, #baseCostPrice, #salePrice, #baseSalePrice').val('');
        syncPrices();
    }
    
    // Calculate from unit price
    function calculateFromUnit(type) {
        const selectedOption = $('#unit_parts option:selected');
        if (!selectedOption.val()) return;
        
        const multiplier = parseFloat(selectedOption.attr('data-multiplier')) || 1;
        const baseUnitName = selectedOption.attr('data-base-unit-name');
        const decimalPlaces = parseInt(selectedOption.attr('data-decimal-places')) || 2;
        
        const unitInput = type === 'cost' ? $('#costPrice') : $('#salePrice');
        const baseInput = type === 'cost' ? $('#baseCostPrice') : $('#baseSalePrice');
        const unitVal = parseFloat(unitInput.val()) || 0;
        
        if (baseUnitName && multiplier > 0) {
            const baseVal = (unitVal / multiplier).toFixed(decimalPlaces);
            baseInput.val(baseVal);
        } else {
            baseInput.val(unitVal.toFixed(decimalPlaces));
        }
        
        syncPrices();
    }
    
    // Calculate from base price
    function calculateFromBase(type) {
        const selectedOption = $('#unit_parts option:selected');
        if (!selectedOption.val()) return;
        
        const multiplier = parseFloat(selectedOption.attr('data-multiplier')) || 1;
        const baseUnitName = selectedOption.attr('data-base-unit-name');
        const decimalPlaces = parseInt(selectedOption.attr('data-decimal-places')) || 2;
        
        const unitInput = type === 'cost' ? $('#costPrice') : $('#salePrice');
        const baseInput = type === 'cost' ? $('#baseCostPrice') : $('#baseSalePrice');
        const baseVal = parseFloat(baseInput.val()) || 0;
        
        if (baseUnitName && multiplier > 0) {
            const unitVal = (baseVal * multiplier).toFixed(decimalPlaces);
            unitInput.val(unitVal);
        } else {
            unitInput.val(baseVal.toFixed(decimalPlaces));
        }
        
        syncPrices();
    }
    
    // Sync prices and show analysis
    function syncPrices() {
        const selectedOption = $('#unit_parts option:selected');
        const salePrice = parseFloat($('#salePrice').val()) || 0;
        const costPrice = parseFloat($('#costPrice').val()) || 0;
        const list = $('#derivedPricesList');
        const warning = $('#priceWarning');
        
        list.empty();
        
        // Show warning if sale < cost
        if (salePrice > 0 && costPrice > 0 && salePrice < costPrice) {
            warning.show();
            $('#costPrice, #salePrice, #baseCostPrice, #baseSalePrice').closest('.card').addClass('border-danger');
        } else {
            warning.hide();
            $('#costPrice, #salePrice, #baseCostPrice, #baseSalePrice').closest('.card').removeClass('border-danger');
        }
        
        if (!selectedOption.val() || (salePrice === 0 && costPrice === 0)) return;
        
        const unitName = selectedOption.attr('data-unit-name');
        const baseUnitName = selectedOption.attr('data-base-unit-name');
        const multiplier = parseFloat(selectedOption.attr('data-multiplier')) || 1;
        const decimalPlaces = parseInt(selectedOption.attr('data-decimal-places')) || 2;
        
        const totalProfit = salePrice - costPrice;
        const totalMargin = salePrice > 0 ? ((totalProfit / salePrice) * 100).toFixed(1) : 0;
        const isTotalLoss = totalProfit < 0;
        
        // Full Unit Analysis Card
        list.append(`
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">FULL UNIT ANALYSIS: ${unitName}</span>
                    <span class="badge ${isTotalLoss ? 'bg-danger' : 'bg-success'}">${isTotalLoss ? 'LOSS' : 'Margin'}: ${totalMargin}%</span>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <p class="small text-muted mb-1">TOTAL COST</p>
                            <p class="fw-bold text-success h5">Rs. ${costPrice.toFixed(decimalPlaces)}</p>
                        </div>
                        <div class="col-4">
                            <p class="small text-muted mb-1">TOTAL SALE</p>
                            <p class="fw-bold text-warning h5">Rs. ${salePrice.toFixed(decimalPlaces)}</p>
                        </div>
                        <div class="col-4">
                            <p class="small text-muted mb-1">TOTAL PROFIT</p>
                            <p class="fw-bold h5 ${totalProfit >= 0 ? 'text-primary' : 'text-danger'}">Rs. ${totalProfit.toFixed(decimalPlaces)}</p>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Base Unit Analysis Card (if base unit exists)
        if (baseUnitName && multiplier > 0) {
            const baseSale = (salePrice / multiplier);
            const baseCost = (costPrice / multiplier);
            const profit = baseSale - baseCost;
            const margin = baseSale > 0 ? ((profit / baseSale) * 100).toFixed(1) : 0;
            const isLoss = profit < 0;
            
            list.append(`
                <div class="card border-secondary mb-3 ${isLoss ? 'border-danger' : ''}">
                    <div class="card-header ${isLoss ? 'bg-danger text-white' : 'bg-secondary text-white'} d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Analysis per ${baseUnitName}</span>
                        <span class="badge ${isLoss ? 'bg-danger' : 'bg-warning text-dark'}">${isLoss ? 'LOSS' : 'Margin'}: ${margin}%</span>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <p class="small text-muted mb-1">COST/${baseUnitName}</p>
                                <p class="fw-bold text-success h5">Rs. ${baseCost.toFixed(decimalPlaces)}</p>
                            </div>
                            <div class="col-4">
                                <p class="small text-muted mb-1">SALE/${baseUnitName}</p>
                                <p class="fw-bold text-warning h5">Rs. ${baseSale.toFixed(decimalPlaces)}</p>
                            </div>
                            <div class="col-4">
                                <p class="small text-muted mb-1">PROFIT/${baseUnitName}</p>
                                <p class="fw-bold h5 ${profit >= 0 ? 'text-primary' : 'text-danger'}">Rs. ${profit.toFixed(decimalPlaces)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
    }
    
    // Reset price fields
    function resetPriceFields() {
        $('#costPrice, #baseCostPrice, #salePrice, #baseSalePrice').val('');
        $('#derivedPricesList').empty();
        $('#priceWarning').hide();
        
        // Reset layout to default (side by side, hide base units)
        $('#baseCostPriceContainer').hide();
        $('#baseSalePriceContainer').hide();
        $('#costPriceManagement').removeClass('col-12 mb-4').addClass('col-12 col-md-6 mb-0');
        $('#salePriceManagement').removeClass('col-12 mb-4').addClass('col-12 col-md-6 mb-0');
        $('#priceManagementContainer').addClass('row g-3');
    }
</script>
<script>
    $(document).ready(function() {
        $("#vehicleTable tbody tr").hide();
    });

    // Common function to filter vehicle table
    function filterVehicleTable() {
        // Get filter values from table headers
        let selectedManufacturer = $('.car-manufacturer-select').val();
        let selectedModel = $('.car-model-select').val();
        let selectedEngine = $('.car-engine-select').val();
        let selectedCountry = $('.car-country-select').val();
        let selectedPartNumber = $('#part_number_id').val();
        
        // Get selected year ranges
        let selectedYearRanges = $('#selectedYearRangesDisplay').data('all-ranges') || [];
        
        // Hide all rows first
        $("#vehicleTable tbody tr").hide();
        
        // Filter rows based on selected criteria
        $("#vehicleTable tbody tr").each(function() {
            let $row = $(this);
            let shouldShow = true;
            
            // Check Manufacturer
            if (selectedManufacturer) {
                let rowManufacturer = $row.find('.editVehicleBtn').data('manufacturer');
                if (rowManufacturer != selectedManufacturer) {
                    shouldShow = false;
                }
            }
            
            // Check Model
            if (shouldShow && selectedModel) {
                let rowModel = $row.find('.editVehicleBtn').data('model');
                if (rowModel != selectedModel) {
                    shouldShow = false;
                }
            }
            
            // Check Engine
            if (shouldShow && selectedEngine) {
                let rowEngine = $row.find('.editVehicleBtn').data('engine');
                if (rowEngine != selectedEngine) {
                    shouldShow = false;
                }
            }
            
            // Check Country
            if (shouldShow && selectedCountry) {
                let rowCountry = $row.find('.editVehicleBtn').data('country');
                if (rowCountry != selectedCountry) {
                    shouldShow = false;
                }
            }
            
            // Check Part Number
            if (shouldShow && selectedPartNumber) {
                let rowPartNumber = $row.data('part') || $row.find('.editVehicleBtn').data('part');
                if (rowPartNumber != selectedPartNumber) {
                    shouldShow = false;
                }
            }
            
            // Check Year Ranges
            if (shouldShow && selectedYearRanges.length > 0) {
                let yearCell = $row.find('td:nth-child(3)');
                let yearText = yearCell.text();
                let yearMatch = false;
                
                // Extract year ranges from table cell
                let tableRanges = [];
                if (yearText.includes('-')) {
                    let rangeMatches = yearText.match(/\d{4}(-\d{4})?/g) || [];
                    rangeMatches.forEach(function(range) {
                        let rangeParts = range.split('-');
                        let rangeFrom = parseInt(rangeParts[0]);
                        let rangeTo = rangeParts[1] ? parseInt(rangeParts[1]) : rangeFrom;
                        tableRanges.push({ from: rangeFrom, to: rangeTo });
                    });
                } else {
                    let yearMatch = yearText.match(/\d{4}/);
                    if (yearMatch) {
                        let year = parseInt(yearMatch[0]);
                        tableRanges.push({ from: year, to: year });
                    }
                }
                
                // Check if any selected range overlaps with table ranges
                selectedYearRanges.forEach(function(selectedRange) {
                    let rangeParts = selectedRange.split('-');
                    let selectedFrom = parseInt(rangeParts[0]);
                    let selectedTo = rangeParts.length > 1 ? parseInt(rangeParts[1]) : selectedFrom;
                    
                    tableRanges.forEach(function(tableRange) {
                        if (tableRange.from <= selectedTo && tableRange.to >= selectedFrom) {
                            yearMatch = true;
                        }
                    });
                });
                
                if (!yearMatch) {
                    shouldShow = false;
                }
            }
            
            // Show row if it matches all criteria
            if (shouldShow) {
                $row.show();
            }
        });
    }

    // Clear All Vehicle Filters Button
    $(document).on('click', '.clearVehicleFiltersBtn', function() {
        // Clear all Select2 dropdowns
        $('#vehicle_manufacturer_select').val('').trigger('change');
        $('#vehicle_model_select').val('').trigger('change');
        $('#vehicle_country_select').val('').trigger('change');
        $('#vehicle_engine_select').val('').trigger('change');
        
        // Clear part number if exists
        if ($('#part_number_id').length) {
            $('#part_number_id').val('').trigger('change');
        }
        
        // Clear year ranges
        $('#selectedYearRangesDisplay').data('all-ranges', []);
        $('#selectedYearRangesDisplay').attr('data-show-all', 'false');
        $('#selectedYearRangesDisplay').html('<div class="text-muted text-center" style="font-size: 10px;">No ranges selected</div>');
        
        // Clear any selected year ranges from modal if open
        if ($('#yearRangeModal').length) {
            $('#yearRangeModal input[type="checkbox"]:checked').prop('checked', false);
        }
        
        // Show all rows in table
        filterVehicleTable();
        
        toastr.success('All filters cleared successfully.');
    });

    // Select All Vehicles Checkbox
    $(document).on('change', '#selectAllVehicles', function() {
        const isChecked = $(this).is(':checked');
        $('.vehicle-checkbox').prop('checked', isChecked);
    });
    
    // Individual checkbox change - update select all checkbox
    $(document).on('change', '.vehicle-checkbox', function() {
        const totalCheckboxes = $('.vehicle-checkbox').length;
        const checkedCheckboxes = $('.vehicle-checkbox:checked').length;
        $('#selectAllVehicles').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Function to collect checked vehicle IDs
    function getCheckedVehicleIds() {
        const checkedVehicles = [];
        $('.vehicle-checkbox:checked').each(function() {
            const vehicleId = $(this).data('vehicle-id');
            if (vehicleId) {
                checkedVehicles.push(vehicleId);
            }
        });
        return checkedVehicles;
    }
    

    // Load Vehicle Button - Filter table and save if no match found
    $(document).on('click', '.loadVehicleBtn', function() {
        // Check if part number is selected first
        let selectedPartNumber = $('#part_number_id').val();
        if (!selectedPartNumber || selectedPartNumber.trim() === '') {
            toastr.warning('Please select Part Number first.');
            return;
        }
        
        // First filter the table
        filterVehicleTable();
        
        // Count visible rows
        let visibleRows = $("#vehicleTable tbody tr:visible").length;
        
        // If no vehicles match, save the vehicle
        if (visibleRows === 0) {
            // Get all filter values
            let selectedManufacturer = $('.car-manufacturer-select').val();
            let selectedModel = $('.car-model-select').val();
            let selectedEngine = $('.car-engine-select').val();
            let selectedCountry = $('.car-country-select').val();
            let selectedYearRanges = $('#selectedYearRangesDisplay').data('all-ranges') || [];
            
            // Check if all required fields are filled
            if (!selectedManufacturer || !selectedModel || !selectedEngine || !selectedCountry || selectedYearRanges.length === 0) {
                toastr.warning('Please fill all fields (Manufacturer, Model, Engine, Country, and Year Ranges) before loading.');
                return;
            }
            
            // Prepare form data
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('car_manufacturer', selectedManufacturer);
            formData.append('car_model_name', selectedModel);
            formData.append('engine_cc', selectedEngine);
            formData.append('car_manufactured_country', selectedCountry);
            formData.append('v_part_number_id', selectedPartNumber);
            
            // Add year ranges
            selectedYearRanges.forEach(function(range) {
                let rangeParts = range.split('-');
                let fromYear = rangeParts[0];
                let toYear = rangeParts.length > 1 ? rangeParts[1] : rangeParts[0];
                formData.append('year_from[]', fromYear);
                formData.append('year_to[]', toYear);
            });
            
            // Save vehicle via AJAX
            $.ajax({
                url: "{{ route('post.product_vehical') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.errors && res.errors.length > 0) {
                        res.errors.forEach(function(error) {
                            toastr.error(error);
                        });
                        return;
                    }
                    
                    if (res.vehicles && res.vehicles.length > 0) {
                        toastr.success(res.message || "Vehicle saved and loaded successfully!");
                        
                        // Add vehicles to table
                        let vehicleGroups = {};
                        res.vehicles.forEach(function(v) {
                            let key = `${v.v_part_number_id}-${v.car_manufacturer}-${v.car_model_name}-${v.engine_cc}-${v.car_manufactured_country}`;
                            if (!vehicleGroups[key]) {
                                vehicleGroups[key] = {
                                    v_part_number_id: v.v_part_number_id,
                                    car_manufacturer: v.car_manufacturer,
                                    car_model_name: v.car_model_name,
                                    engine_cc: v.engine_cc,
                                    car_manufactured_country: v.car_manufactured_country,
                                    manutacturer_vehical: v.manutacturer_vehical,
                                    model_vehical: v.model_vehical,
                                    engine_vehical: v.engine_vehical,
                                    country_vehical: v.country_vehical,
                                    vehical_part_number: v.vehical_part_number,
                                    yearRanges: []
                                };
                            }
                            if (v.year_from && v.year_to) {
                                let yearStr = v.year_from == v.year_to ? v.year_from : v.year_from + '-' + v.year_to;
                                if (vehicleGroups[key].yearRanges.indexOf(yearStr) === -1) {
                                    vehicleGroups[key].yearRanges.push(yearStr);
                                }
                            }
                        });
                        
                        // Add new vehicles to table
                        Object.keys(vehicleGroups).forEach(function(key) {
                            let group = vehicleGroups[key];
                            
                            // Check if row already exists
                            let exists = false;
                            $("#vehicleTable tbody tr").each(function() {
                                let $row = $(this);
                                if ($row.data('part') == group.v_part_number_id &&
                                    $row.find('.editVehicleBtn').data('manufacturer') == group.car_manufacturer &&
                                    $row.find('.editVehicleBtn').data('model') == group.car_model_name &&
                                    $row.find('.editVehicleBtn').data('engine') == group.engine_cc &&
                                    $row.find('.editVehicleBtn').data('country') == group.car_manufactured_country) {
                                    exists = true;
                                    return false;
                                }
                            });
                            
                            if (!exists) {
                                // Build year ranges HTML
                                let yearRangesHtml = '';
                                if (group.yearRanges.length > 0) {
                                    group.yearRanges.sort(function(a, b) {
                                        let aFrom = parseInt(a.split('-')[0]);
                                        let bFrom = parseInt(b.split('-')[0]);
                                        return aFrom - bFrom;
                                    });
                                    yearRangesHtml = '<div style="display: inline-flex; flex-wrap: wrap; gap: 6px; align-items: center;">';
                                    group.yearRanges.forEach(function(range) {
                                        yearRangesHtml += `<span class="badge" style="background-color: #7DD3FC; color: #0C4A6E; padding: 6px 12px; border-radius: 6px; font-weight: 500; font-size: 13px; white-space: nowrap;">${range}</span>`;
                                    });
                                    yearRangesHtml += '</div>';
                                } else {
                                    yearRangesHtml = '<span class="badge bg-secondary">-</span>';
                                }
                                
                                // Add new row
                                let newRow = `
                                    <tr data-part="${group.v_part_number_id}">
                                        <td>${group.manutacturer_vehical?.name || '-'}</td>
                                        <td>${group.model_vehical?.name || '-'}</td>
                                        <td>${yearRangesHtml}</td>
                                        <td>${group.engine_vehical?.name || '-'}</td>
                                        <td>${group.country_vehical?.name || '-'}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary editVehicleBtn"
                                                data-part="${group.v_part_number_id}"
                                                data-manufacturer="${group.car_manufacturer}"
                                                data-model="${group.car_model_name}"
                                                data-engine="${group.engine_cc}"
                                                data-country="${group.car_manufactured_country}"
                                                data-year-ranges='${JSON.stringify(group.yearRanges)}'
                                                data-year-from="${group.yearRanges.length > 0 ? group.yearRanges[0].split('-')[0] : ''}"
                                                data-year-to="${group.yearRanges.length > 0 ? (group.yearRanges[0].includes('-') ? group.yearRanges[0].split('-')[1] : group.yearRanges[0]) : ''}">
                                                <i class="ti ti-pencil"></i>
                                            </button>
                                        </td>
                                        <td>${group.vehical_part_number?.name || '-'}</td>
                                    </tr>
                                `;
                                $("#vehicleTable tbody").prepend(newRow);
                            }
                        });
                        
                        // Filter again to show the new vehicle
                        filterVehicleTable();
                    }
                },
                error: function(xhr) {
                    let response = xhr.responseJSON;
                    if (response && response.errors && Array.isArray(response.errors)) {
                        response.errors.forEach(function(error) {
                            toastr.error(error);
                        });
                    } else {
                        toastr.error(response?.message || 'Error saving vehicle');
                    }
                }
            });
        }
    });

    // Auto-filter on dropdown change - Manufacturer
    $(document).on('change', '.car-manufacturer-select', function() {
        filterVehicleTable();
    });

    // Auto-filter on dropdown change - Model
    $(document).on('change', '.car-model-select', function() {
        filterVehicleTable();
    });

    // Auto-filter on dropdown change - Engine
    $(document).on('change', '.car-engine-select', function() {
        filterVehicleTable();
    });

    // Auto-filter on dropdown change - Country
    $(document).on('change', '.car-country-select', function() {
        filterVehicleTable();
    });

    // Auto-filter on Part Number change
    $(document).on('change', '#part_number_id', function() {
        filterVehicleTable();
    });
</script>
<script>
    // Year Range Modal Handler
    $(document).on('click', '.open-year-range-modal', function() {
        $('#yearRangeModal').modal('show');
    });

    // Function to update year range display (show 2 by default, all on click)
    function updateYearRangeDisplay() {
        let displayBox = $('#selectedYearRangesDisplay');
        let allRanges = displayBox.data('all-ranges') || [];
        let showAll = displayBox.attr('data-show-all') === 'true';
        
        if (allRanges.length === 0) {
            displayBox.html('<div class="text-muted text-center" style="font-size: 10px;">No ranges selected</div>');
            return;
        }
        
        let rangesToShow = showAll ? allRanges : allRanges.slice(0, 2);
        let html = '<div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center;">';
        
        rangesToShow.forEach(function(range) {
            html += '<span class="badge" style="background-color: #7DD3FC; color: #0C4A6E; padding: 4px 8px; border-radius: 4px; font-size: 10px;">' + range + '</span>';
        });
        
        // Show "+X more" indicator if not showing all and there are more than 2
        if (!showAll && allRanges.length > 2) {
            html += '<span class="badge bg-secondary" style="padding: 4px 8px; border-radius: 4px; font-size: 10px; cursor: pointer;">+' + (allRanges.length - 2) + ' more</span>';
        }
        
        html += '</div>';
        displayBox.html(html);
    }

    // Toggle display on click - show all ranges when clicked
    $(document).on('click', '#selectedYearRangesDisplay', function(e) {
        let displayBox = $(this);
        let allRanges = displayBox.data('all-ranges') || [];
        
        if (allRanges.length > 2) {
            let showAll = displayBox.attr('data-show-all') === 'true';
            displayBox.attr('data-show-all', showAll ? 'false' : 'true');
            updateYearRangeDisplay();
        }
    });

    // Function to update remove buttons visibility for filter year ranges
    function updateFilterYearRangeRemoveButtons() {
        let ranges = $('#filterYearRangesContainer .filter-year-range-item');
        ranges.each(function(index) {
            let removeBtn = $(this).find('.removeFilterYearRange');
            if (ranges.length > 1) {
                removeBtn.show();
            } else {
                removeBtn.hide();
            }
        });
    }

    // Add More Year Range in Filter Modal
    $(document).on('click', '#addMoreFilterYearRange', function() {
        let container = $('#filterYearRangesContainer');
        let newRange = $('<div class="filter-year-range-item mb-2"></div>');
        
        // Build year options in descending order (current year to 1980)
        let yearOptions = '';
        let currentYear = new Date().getFullYear();
        for (let year = currentYear; year >= 1980; year--) {
            yearOptions += `<option value="${year}">${year}</option>`;
        }
        
        newRange.html(`
            <div class="row g-2">
                <div class="col-5">
                    <label class="form-label small">From Year</label>
                    <select class="form-control filter-year-from">
                        <option value="">Select Year</option>
                        ${yearOptions}
                    </select>
                    <div class="invalid-feedback" style="display: none; font-size: 10px;">From Year cannot be greater than To Year</div>
                </div>
                <div class="col-5">
                    <label class="form-label small">To Year</label>
                    <select class="form-control filter-year-to">
                        <option value="">Select Year</option>
                        ${yearOptions}
                    </select>
                    <div class="invalid-feedback" style="display: none; font-size: 10px;">To Year cannot be less than From Year</div>
                </div>
                <div class="col-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm removeFilterYearRange">X</button>
                </div>
            </div>
        `);
        
        container.append(newRange);
        updateFilterYearRangeRemoveButtons();
        
        // Add validation handlers for the new range
        attachYearRangeValidation(newRange);
        
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    });

    // Function to attach year range validation
    function attachYearRangeValidation($container) {
        let $fromSelect = $container.find('.filter-year-from');
        let $toSelect = $container.find('.filter-year-to');
        let $fromError = $fromSelect.next('.invalid-feedback');
        let $toError = $toSelect.next('.invalid-feedback');
        
        // Validate when From Year changes
        $fromSelect.on('change', function() {
            let fromYear = $(this).val();
            let toYear = $toSelect.val();
            
            if (fromYear && toYear && parseInt(fromYear) > parseInt(toYear)) {
                $(this).addClass('is-invalid');
                $fromError.show();
                $toSelect.addClass('is-invalid');
                $toError.show();
            } else {
                $(this).removeClass('is-invalid');
                $fromError.hide();
                if (!toYear || parseInt(fromYear) <= parseInt(toYear)) {
                    $toSelect.removeClass('is-invalid');
                    $toError.hide();
                }
            }
        });
        
        // Validate when To Year changes
        $toSelect.on('change', function() {
            let fromYear = $fromSelect.val();
            let toYear = $(this).val();
            
            if (fromYear && toYear && parseInt(fromYear) > parseInt(toYear)) {
                $(this).addClass('is-invalid');
                $toError.show();
                $fromSelect.addClass('is-invalid');
                $fromError.show();
            } else {
                $(this).removeClass('is-invalid');
                $toError.hide();
                if (!fromYear || parseInt(fromYear) <= parseInt(toYear)) {
                    $fromSelect.removeClass('is-invalid');
                    $fromError.hide();
                }
            }
        });
    }


    // Remove Year Range from Filter Modal
    $(document).on('click', '.removeFilterYearRange', function() {
        $(this).closest('.filter-year-range-item').remove();
        updateFilterYearRangeRemoveButtons();
    });

    // Apply Year Range Filter
    $(document).on('click', '#applyYearRangeFilter', function() {
        // Auto-fix year ranges sequence (swap if From > To)
        $('#filterYearRangesContainer .filter-year-range-item').each(function() {
            let $fromSelect = $(this).find('.filter-year-from');
            let $toSelect = $(this).find('.filter-year-to');
            let fromYear = $fromSelect.val();
            let toYear = $toSelect.val();
            
            if (fromYear && toYear) {
                let from = parseInt(fromYear);
                let to = parseInt(toYear);
                
                // If From > To, swap them
                if (from > to) {
                    $fromSelect.val(to);
                    $toSelect.val(from);
                    // Remove validation errors
                    $fromSelect.removeClass('is-invalid');
                    $toSelect.removeClass('is-invalid');
                    $fromSelect.next('.invalid-feedback').hide();
                    $toSelect.next('.invalid-feedback').hide();
                }
            }
        });
        
        // Collect all year ranges from the modal
        let filterRanges = [];
        let displayRanges = [];
        $('#filterYearRangesContainer .filter-year-range-item').each(function() {
            let fromYear = $(this).find('.filter-year-from').val();
            let toYear = $(this).find('.filter-year-to').val();
            
            if (fromYear || toYear) {
                let from = fromYear ? parseInt(fromYear) : 1980;
                let to = toYear ? parseInt(toYear) : new Date().getFullYear();
                filterRanges.push({
                    from: from,
                    to: to
                });
                // Format for display
                if (from === to) {
                    displayRanges.push(from.toString());
                } else {
                    displayRanges.push(from + '-' + to);
                }
            }
        });
        
        // Update display box - store all ranges in data attribute
        let displayBox = $('#selectedYearRangesDisplay');
        displayBox.data('all-ranges', displayRanges);
        displayBox.attr('data-show-all', 'false');
        
        if (displayRanges.length > 0) {
            updateYearRangeDisplay();
        } else {
            displayBox.html('<div class="text-muted text-center" style="font-size: 10px;">No ranges selected</div>');
        }
        
        $('#yearRangeModal').modal('hide');
        
        // Auto-filter table using common filter function
        filterVehicleTable();
    });
    
    // Reset filter when modal is closed
    $('#yearRangeModal').on('hidden.bs.modal', function() {
        // Reset to single empty range
        let yearOptions = '';
        let currentYear = new Date().getFullYear();
        for (let year = currentYear; year >= 1980; year--) {
            yearOptions += `<option value="${year}">${year}</option>`;
        }
        
        let $newRange = $(`
            <div class="filter-year-range-item mb-2">
                <div class="row g-2">
                    <div class="col-5">
                        <label class="form-label small">From Year</label>
                        <select class="form-control filter-year-from">
                            <option value="">Select Year</option>
                            ${yearOptions}
                        </select>
                        <div class="invalid-feedback" style="display: none; font-size: 10px;">From Year cannot be greater than To Year</div>
                    </div>
                    <div class="col-5">
                        <label class="form-label small">To Year</label>
                        <select class="form-control filter-year-to">
                            <option value="">Select Year</option>
                            ${yearOptions}
                        </select>
                        <div class="invalid-feedback" style="display: none; font-size: 10px;">To Year cannot be less than From Year</div>
                    </div>
                    <div class="col-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm removeFilterYearRange" style="display: none;">X</button>
                    </div>
                </div>
            </div>
        `);
        
        $('#filterYearRangesContainer').html($newRange);
        attachYearRangeValidation($newRange);
    });

    // Attach validation when modal opens
    $('#yearRangeModal').on('shown.bs.modal', function() {
        $('#filterYearRangesContainer .filter-year-range-item').each(function() {
            attachYearRangeValidation($(this));
        });
    });
</script>
<script>
    // Part number is now a simple text input - no filtering needed
</script>
<script>
    // Allow modal to open without part number validation
    // When modal opens, pre-fill Part Number from the input field if available
    // Also ensure all dropdowns are properly initialized with Select2
    $('#vehical-add-modal').on('shown.bs.modal', function() {
        let outsidePart = $('#part_number_id').val();
        if (outsidePart && outsidePart.trim() !== '') {
            $('#part_number').val(outsidePart.trim()).trigger('change');
            $('#part_number').removeClass('is-invalid');
        }
        
        // Initialize Select2 for all modal dropdowns if not already initialized
        const modalDropdowns = ['#car_manufacturer', '#car_model_name', '#engine_cc', '#car_manufactured_country', '#part_number'];
        const select2Config = {
            dropdownParent: $('#vehical-add-modal'),
            allowClear: false,
            width: '100%'
        };
        
        modalDropdowns.forEach(function(selector) {
            const $select = $(selector);
            if ($select.hasClass('searchable-select') && $select.next('.select2-container').length === 0) {
                $select.select2(select2Config);
            }
        });
        
        // Add focus handlers for all dropdowns when they open
        modalDropdowns.forEach(function(selector) {
            const $select = $(selector);
            if ($select.length) {
                // Remove existing handlers to avoid duplicates
                $select.off('select2:opening.modalFocus select2:open.modalFocus');
                
                // Focus search input when dropdown opens
                $select.on('select2:opening.modalFocus', function(e) {
                    setTimeout(function() {
                        const $searchInput = $('.select2-container--open .select2-search__field');
                        if ($searchInput.length) {
                            $searchInput[0].focus();
                            $searchInput[0].select();
                        }
                    }, 10);
                });
                
                // Also handle when dropdown is fully open
                $select.on('select2:open.modalFocus', function(e) {
                    function focusSearchInput() {
                        const $container = $select.next('.select2-container');
                        if ($container.length && $container.hasClass('select2-container--open')) {
                            const $searchInput = $container.find('.select2-search__field');
                            if ($searchInput.length && $searchInput.length > 0) {
                                const searchInput = $searchInput[0];
                                if (searchInput) {
                                    // Ensure input is enabled and focusable
                                    searchInput.removeAttribute('readonly');
                                    searchInput.removeAttribute('disabled');
                                    searchInput.style.pointerEvents = 'auto';
                                    searchInput.style.cursor = 'text';
                                    
                                    // Focus and select
                                    searchInput.focus();
                                    searchInput.select();
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
                    
                    // Try multiple times with different delays
                    setTimeout(focusSearchInput, 0);
                    setTimeout(focusSearchInput, 10);
                    setTimeout(focusSearchInput, 30);
                    setTimeout(focusSearchInput, 50);
                    setTimeout(focusSearchInput, 100);
                });
            }
        });
    });
    
    // Reset modal title to "Add Vehical" when modal is hidden
    $('#vehical-add-modal').on('hidden.bs.modal', function() {
        $('#vehical-modal-title').text('Add Vehical');
        $('#deleteVehicleBtn').hide();
        $('#deleteVehicleBtn').removeData('vehicle-config');
    });

    // Remove error styling when part number is entered (both fields)
    $(document).on('input change', '#part_number, #part_number_id', function() {
        if ($(this).val() && $(this).val().trim() !== '') {
            $(this).removeClass('is-invalid');
            // Also remove error from the other field
            $('#part_number, #part_number_id').removeClass('is-invalid');
        }
    });
</script>
<script>
    // Set hidden input based on which submit button was clicked
    $("#vehical-form button[type=submit]").on("click", function() {
        $("#submit_type").val($(this).data("action"));
    });
</script>
<script>
    $("#vehical-form").off("submit").on("submit", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Check if modal is actually visible
        if (!$('#vehical-add-modal').hasClass('show')) {
            return false;
        }
        
        let form = this;

        // Get part number from modal or outside field (optional now)
        let partNumber = $('#part_number').val();
        let outsidePartNumber = $('#part_number_id').val();

        // Use outside part number if modal part number is not set
        if (!partNumber || partNumber.trim() === '') {
            if (outsidePartNumber && outsidePartNumber.trim() !== '') {
                partNumber = outsidePartNumber.trim();
                $('#part_number').val(partNumber).trigger('change');
            }
        }

        // Remove error styling if part number is entered
        $('#part_number').removeClass('is-invalid');
        $('#part_number_id').removeClass('is-invalid');

        let formData = new FormData(form);
        
        // Ensure part number is included in formData if available
        if (partNumber && partNumber.trim() !== '') {
            formData.set('v_part_number_id', partNumber);
        } else if (outsidePartNumber && outsidePartNumber.trim() !== '') {
            formData.set('v_part_number_id', outsidePartNumber.trim());
        }
        
        let submitType = $("#submit_type").val();
        let outsidePart = $('#part_number_id').val();
        $.ajax({
            url: "{{ route('post.product_vehical') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                // Only show success message if there's a valid response with vehicles
                if (!res) {
                    console.error('No response from server');
                    toastr.error('No response from server');
                    return;
                }
                
                if (res.errors && res.errors.length > 0) {
                    // Display overlap errors
                    res.errors.forEach(function(error) {
                        toastr.error(error);
                    });
                    return;
                }
                
                if (res.duplicate_years?.length) {
                    toastr.warning("Already exists for year(s): " + res.duplicate_years.join(', '));
                } else if (res.vehicles && res.vehicles.length > 0) {
                    // Only show success if vehicles were actually saved and modal is visible
                    if ($('#vehical-add-modal').hasClass('show')) {
                        toastr.success(res.message || "Vehicle saved successfully!");
                        // 🔊 Play save sound when vehicle is saved
                        if (typeof playSaveSound === 'function') {
                            playSaveSound();
                        }
                    }
                } else if (res.message && $('#vehical-add-modal').hasClass('show')) {
                    // If there's a message but no vehicles, show it (might be a warning)
                    toastr.info(res.message);
                }

                // Add/update vehicles in table without page reload
                if (res.vehicles && res.vehicles.length > 0) {
                    // Group vehicles by config (part, manufacturer, model, engine, country)
                    let vehicleGroups = {};
                    res.vehicles.forEach(function(v) {
                        let key = `${v.v_part_number_id}-${v.car_manufacturer}-${v.car_model_name}-${v.engine_cc}-${v.car_manufactured_country}`;
                        if (!vehicleGroups[key]) {
                            vehicleGroups[key] = {
                                v_part_number_id: v.v_part_number_id,
                                car_manufacturer: v.car_manufacturer,
                                car_model_name: v.car_model_name,
                                engine_cc: v.engine_cc,
                                car_manufactured_country: v.car_manufactured_country,
                                manutacturer_vehical: v.manutacturer_vehical,
                                model_vehical: v.model_vehical,
                                engine_vehical: v.engine_vehical,
                                country_vehical: v.country_vehical,
                                vehical_part_number: v.vehical_part_number,
                                yearRanges: []
                            };
                        }
                        // Add year range
                        if (v.year_from && v.year_to) {
                            let yearStr = v.year_from == v.year_to ? v.year_from : v.year_from + '-' + v.year_to;
                            if (vehicleGroups[key].yearRanges.indexOf(yearStr) === -1) {
                                vehicleGroups[key].yearRanges.push(yearStr);
                            }
                        }
                    });

                    // Check if vehicle group already exists in table, if yes update it, else add new
                    Object.keys(vehicleGroups).forEach(function(key) {
                        let group = vehicleGroups[key];

                        // Find existing row by matching all config fields
                        let existingRow = null;
                        $("#vehicleTable tbody tr").each(function() {
                            let $row = $(this);
                            if ($row.data('part') == group.v_part_number_id &&
                                $row.find('.editVehicleBtn').data('manufacturer') == group.car_manufacturer &&
                                $row.find('.editVehicleBtn').data('model') == group.car_model_name &&
                                $row.find('.editVehicleBtn').data('engine') == group.engine_cc &&
                                $row.find('.editVehicleBtn').data('country') == group.car_manufactured_country) {
                                existingRow = $row;
                                return false; // break loop
                            }
                        });

                        // Build year ranges display with light blue badges (sorted by year)
                        let yearRangesHtml = '';
                        if (group.yearRanges.length > 0) {
                            // Sort year ranges by 'from' year
                            group.yearRanges.sort(function(a, b) {
                                let aFrom = parseInt(a.split('-')[0]);
                                let bFrom = parseInt(b.split('-')[0]);
                                return aFrom - bFrom;
                            });

                            yearRangesHtml = '<div style="display: inline-flex; flex-wrap: wrap; gap: 6px; align-items: center;">';
                            group.yearRanges.forEach(function(range) {
                                yearRangesHtml += `<span class="badge" style="background-color: #7DD3FC; color: #0C4A6E; padding: 6px 12px; border-radius: 6px; font-weight: 500; font-size: 13px; white-space: nowrap;">${range}</span>`;
                            });
                            yearRangesHtml += '</div>';
                        } else {
                            yearRangesHtml = '<span class="badge bg-secondary">-</span>';
                        }

                        if (existingRow && existingRow.length > 0) {
                            // Update existing row
                            existingRow.find('td:eq(0)').text(group.manutacturer_vehical?.name || '-');
                            existingRow.find('td:eq(1)').text(group.model_vehical?.name || '-');
                            existingRow.find('td:eq(2)').html(yearRangesHtml);
                            existingRow.find('td:eq(3)').text(group.engine_vehical?.name || '-');
                            existingRow.find('td:eq(4)').text(group.country_vehical?.name || '-');

                            // Update edit button data attributes - store first year range for backward compatibility
                            let editBtn = existingRow.find('.editVehicleBtn');
                            editBtn.attr('data-part', group.v_part_number_id);
                            editBtn.attr('data-manufacturer', group.car_manufacturer);
                            editBtn.attr('data-model', group.car_model_name);
                            editBtn.attr('data-engine', group.engine_cc);
                            editBtn.attr('data-country', group.car_manufactured_country);
                            // Store all year ranges as JSON string
                            editBtn.attr('data-year-ranges', JSON.stringify(group.yearRanges));
                            // For backward compatibility, also store first range
                            if (group.yearRanges.length > 0) {
                                let firstRange = group.yearRanges[0];
                                let rangeParts = firstRange.split('-');
                                editBtn.attr('data-year-from', rangeParts[0]);
                                editBtn.attr('data-year-to', rangeParts.length > 1 ? rangeParts[1] : rangeParts[0]);
                            }
                        } else {
                            // Add new row
                            let firstYearFrom = '';
                            let firstYearTo = '';
                            if (group.yearRanges.length > 0) {
                                let firstRange = group.yearRanges[0];
                                let rangeParts = firstRange.split('-');
                                firstYearFrom = rangeParts[0];
                                firstYearTo = rangeParts.length > 1 ? rangeParts[1] : rangeParts[0];
                            }

                            $("#vehicleTable tbody").append(`
                                <tr data-part="${group.v_part_number_id}">
                                    <td>${group.manutacturer_vehical?.name || '-'}</td>
                                    <td>${group.model_vehical?.name || '-'}</td>
                                    <td>${yearRangesHtml}</td>
                                    <td>${group.engine_vehical?.name || '-'}</td>
                                    <td>${group.country_vehical?.name || '-'}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary editVehicleBtn"
                                            data-part="${group.v_part_number_id}"
                                            data-manufacturer="${group.car_manufacturer}"
                                            data-model="${group.car_model_name}"
                                            data-engine="${group.engine_cc}"
                                            data-country="${group.car_manufactured_country}"
                                            data-year-ranges="${JSON.stringify(group.yearRanges)}"
                                            data-year-from="${firstYearFrom}"
                                            data-year-to="${firstYearTo}">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        }
                    });
                }

                // Reset the form
                form.reset();
                // Clear year ranges
                let yearOptions = '';
                for (let year = 1900; year <= 2100; year++) {
                    yearOptions += `<option value="${year}">${year}</option>`;
                }
                $("#yearRangesContainer").html(`
                    <div class="year-range-item mb-2">
                        <div class="row g-2">
                            <div class="col-5">
                                <select class="form-control year-from-select" name="year_from[]">
                                    <option value="">From Year</option>
                                    ${yearOptions}
                                </select>
                            </div>
                            <div class="col-5">
                                <select class="form-control year-to-select" name="year_to[]">
                                    <option value="">To Year</option>
                                    ${yearOptions}
                                </select>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-danger btn-sm removeYearRange" style="display: none;">X</button>
                            </div>
                        </div>
                    </div>
                `);
                updateRemoveButtons();

                // Close modal or keep open based on submit type
                if (submitType === 'save') {
                    if (outsidePart) {
                        $("#part_number").val(outsidePart).trigger('change');
                    } else {
                        $("#part_number").val('').trigger('change');
                    }
                    $("#vehical-add-modal").modal('hide');
                } else if (submitType === 'save_new') {
                    $("#part_number").val('').trigger('change');
                }
            },
            error: function(xhr) {
                let response = xhr.responseJSON;
                if (response && response.errors && Array.isArray(response.errors)) {
                    response.errors.forEach(function(error) {
                        toastr.error(error);
                    });
                } else {
                    let msg = response?.message || 'Something went wrong!';
                    toastr.error(msg);
                }
            }
        });
    });
</script>
<script>
    $(document).on('click', '.editVehicleBtn', function() {
        // Change modal title to "Edit Vehical"
        $('#vehical-modal-title').text('Edit Vehical');
        
        // Show delete button and store vehicle configuration for deletion
        $('#deleteVehicleBtn').show();
        $('#deleteVehicleBtn').data('vehicle-config', {
            part: $(this).data('part'),
            manufacturer: $(this).data('manufacturer'),
            model: $(this).data('model'),
            engine: $(this).data('engine'),
            country: $(this).data('country')
        });
        
        let yearRangesData = $(this).data('year-ranges');
        let yearRanges = [];

        // Parse year ranges from JSON data attribute
        if (yearRangesData) {
            try {
                yearRanges = typeof yearRangesData === 'string' ? JSON.parse(yearRangesData) : yearRangesData;
            } catch(e) {
                console.error('Error parsing year ranges:', e);
                // Fallback to year-from and year-to
                let yearFrom = $(this).data('year-from');
                let yearTo = $(this).data('year-to');
                if (yearFrom && yearTo) {
                    yearRanges = [yearFrom == yearTo ? yearFrom.toString() : yearFrom + '-' + yearTo];
                }
            }
        } else {
            // Fallback to year-from and year-to if data-year-ranges not available
            let yearFrom = $(this).data('year-from');
            let yearTo = $(this).data('year-to');
            if (yearFrom && yearTo) {
                yearRanges = [yearFrom == yearTo ? yearFrom.toString() : yearFrom + '-' + yearTo];
            }
        }

        // Initialize/Refresh Select2 for all modal dropdowns with proper configuration
        const modalDropdowns = ['#car_manufacturer', '#car_model_name', '#engine_cc', '#car_manufactured_country', '#part_number'];
        const select2Config = {
            dropdownParent: $('#vehical-add-modal'),
            allowClear: false,
            width: '100%'
        };
        
        modalDropdowns.forEach(function(selector) {
            const $select = $(selector);
            if ($select.hasClass('searchable-select')) {
                // Destroy existing Select2 if it exists
                if ($select.next('.select2-container').length > 0) {
                    $select.select2('destroy');
                }
                // Initialize Select2 with modal parent
                $select.select2(select2Config);
            }
        });
        
        // Set values after Select2 initialization
        $('#car_manufacturer').val($(this).data('manufacturer')).trigger('change');
        $('#car_model_name').val($(this).data('model')).trigger('change');
        $('#engine_cc').val($(this).data('engine')).trigger('change');
        $('#part_number').val($(this).data('part')).trigger('change');
        $('#car_manufactured_country').val($(this).data('country')).trigger('change');

        // Build year range HTML
        let yearRangeHtml = '';

        // Generate year options
        let yearOptions = '';
        for (let year = 1900; year <= 2100; year++) {
            yearOptions += `<option value="${year}">${year}</option>`;
        }

        if (yearRanges.length > 0) {
            // Populate with existing year ranges
            yearRanges.forEach(function(rangeStr) {
                let fromYear = '';
                let toYear = '';

                // Parse range string (e.g., "2014-2021" or "2015")
                if (rangeStr.includes('-')) {
                    let parts = rangeStr.split('-');
                    fromYear = parts[0].trim();
                    toYear = parts[1].trim();
                } else {
                    fromYear = rangeStr.trim();
                    toYear = rangeStr.trim();
                }

                // Build options with selected year
                let fromOptions = '<option value="">From Year</option>';
                let toOptions = '<option value="">To Year</option>';
                for (let year = 1900; year <= 2100; year++) {
                    fromOptions += `<option value="${year}" ${fromYear == year ? 'selected' : ''}>${year}</option>`;
                    toOptions += `<option value="${year}" ${toYear == year ? 'selected' : ''}>${year}</option>`;
                }

                yearRangeHtml += `
                    <div class="year-range-item mb-2">
                        <div class="row g-2">
                            <div class="col-5">
                                <select class="form-control year-from-select" name="year_from[]">
                                    ${fromOptions}
                                </select>
                            </div>
                            <div class="col-5">
                                <select class="form-control year-to-select" name="year_to[]">
                                    ${toOptions}
                                </select>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-danger btn-sm removeYearRange">X</button>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            // No ranges, show empty range input
            yearRangeHtml = `
                <div class="year-range-item mb-2">
                    <div class="row g-2">
                        <div class="col-5">
                            <select class="form-control year-from-select" name="year_from[]">
                                <option value="">From Year</option>
                                ${yearOptions}
                            </select>
                        </div>
                        <div class="col-5">
                            <select class="form-control year-to-select" name="year_to[]">
                                <option value="">To Year</option>
                                ${yearOptions}
                            </select>
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-danger btn-sm removeYearRange" style="display: none;">X</button>
                        </div>
                    </div>
                </div>
            `;
        }

        $('#yearRangesContainer').html(yearRangeHtml);
        updateRemoveButtons();
        
        // Add focus handlers for modal dropdowns when they open
        setTimeout(function() {
            modalDropdowns.forEach(function(selector) {
                const $select = $(selector);
                if ($select.length) {
                    // Remove existing handlers to avoid duplicates
                    $select.off('select2:opening.modalFocus select2:open.modalFocus');
                    
                    // Focus search input when dropdown opens
                    $select.on('select2:opening.modalFocus', function(e) {
                        // Delay to ensure Select2 has initialized the search input
                        setTimeout(function() {
                            const $searchInput = $('.select2-container--open .select2-search__field');
                            if ($searchInput.length) {
                                $searchInput[0].focus();
                                $searchInput[0].select();
                            }
                        }, 10);
                    });
                    
                    // Also handle when dropdown is fully open
                    $select.on('select2:open.modalFocus', function(e) {
                        function focusSearchInput() {
                            const $container = $select.next('.select2-container');
                            if ($container.length && $container.hasClass('select2-container--open')) {
                                const $searchInput = $container.find('.select2-search__field');
                                if ($searchInput.length && $searchInput.length > 0) {
                                    const searchInput = $searchInput[0];
                                    if (searchInput) {
                                        // Ensure input is enabled and focusable
                                        searchInput.removeAttribute('readonly');
                                        searchInput.removeAttribute('disabled');
                                        searchInput.style.pointerEvents = 'auto';
                                        searchInput.style.cursor = 'text';
                                        
                                        // Focus and select
                                        searchInput.focus();
                                        searchInput.select();
                                        return true;
                                    }
                                }
                            }
                            return false;
                        }
                        
                        // Try multiple times with different delays
                        setTimeout(focusSearchInput, 0);
                        setTimeout(focusSearchInput, 10);
                        setTimeout(focusSearchInput, 30);
                        setTimeout(focusSearchInput, 50);
                        setTimeout(focusSearchInput, 100);
                    });
                }
            });
        }, 100);
        
        $('#vehical-add-modal').modal('show');
    });

    // Delete Vehicle Button
    $(document).on('click', '#deleteVehicleBtn', function() {
        let config = $(this).data('vehicle-config');
        if (!config || !config.part || !config.manufacturer || !config.model || !config.engine || !config.country) {
            toastr.error('Unable to delete vehicle. Configuration data is missing.');
            return;
        }
        
        if (!confirm('Are you sure you want to delete this vehicle configuration? This action cannot be undone.')) {
            return;
        }
        
        // Delete all vehicles with this configuration
        $.ajax({
            url: "{{ route('vehical.delete.by.config') }}",
            type: "POST",
            data: {
                _token: '{{ csrf_token() }}',
                v_part_number_id: config.part,
                car_manufacturer: config.manufacturer,
                car_model_name: config.model,
                engine_cc: config.engine,
                car_manufactured_country: config.country
            },
            success: function(res) {
                // Play delete sound
                let deleteSound = document.getElementById('deleteSound');
                if (deleteSound) {
                    deleteSound.play().catch(function(error) {
                        console.log('Error playing delete sound:', error);
                    });
                }
                
                toastr.success('Vehicle deleted successfully!');
                
                // Remove matching rows from table
                $("#vehicleTable tbody tr").each(function() {
                    let $row = $(this);
                    let rowPart = $row.data('part');
                    let rowManufacturer = $row.find('.editVehicleBtn').data('manufacturer');
                    let rowModel = $row.find('.editVehicleBtn').data('model');
                    let rowEngine = $row.find('.editVehicleBtn').data('engine');
                    let rowCountry = $row.find('.editVehicleBtn').data('country');
                    
                    if (rowPart == config.part &&
                        rowManufacturer == config.manufacturer &&
                        rowModel == config.model &&
                        rowEngine == config.engine &&
                        rowCountry == config.country) {
                        $row.remove();
                    }
                });
                
                // Close modal
                $('#vehical-add-modal').modal('hide');
            },
            error: function(xhr) {
                let response = xhr.responseJSON;
                toastr.error(response?.message || 'Error deleting vehicle');
            }
        });
    });
</script>
<script>
    // Function to update remove buttons visibility
    function updateRemoveButtons() {
        let ranges = document.querySelectorAll('.year-range-item');
        ranges.forEach((range, index) => {
            let removeBtn = range.querySelector('.removeYearRange');
            if (ranges.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }

    // Add year range
    document.getElementById('addYearRangeBtn').addEventListener('click', function() {
        let container = document.getElementById('yearRangesContainer');
        let newRange = document.createElement('div');
        newRange.className = 'year-range-item mb-2';

        // Build year options
        let yearOptions = '';
        for (let year = 1900; year <= 2100; year++) {
            yearOptions += `<option value="${year}">${year}</option>`;
        }

        newRange.innerHTML = `
            <div class="row g-2">
                <div class="col-5">
                    <select class="form-control year-from-select" name="year_from[]">
                        <option value="">From Year</option>
                        ${yearOptions}
                    </select>
                </div>
                <div class="col-5">
                    <select class="form-control year-to-select" name="year_to[]">
                        <option value="">To Year</option>
                        ${yearOptions}
                    </select>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm removeYearRange">X</button>
                </div>
            </div>
        `;
        container.appendChild(newRange);
        updateRemoveButtons();
    });

    // Remove year range
    $(document).on('click', '.removeYearRange', function() {
        $(this).closest('.year-range-item').remove();
        updateRemoveButtons();
    });

    // Initialize remove buttons on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateRemoveButtons();
    });
</script>
<script>
    document.getElementById("part_number").addEventListener("keydown", function(e) {
        if (e.key === " ") {
            e.preventDefault();
            this.value += "-";
        }
    });
    document.addEventListener("DOMContentLoaded", function() {
        const checkboxes = document.querySelectorAll('.form-check-input');
        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    checkboxes.forEach((cb) => {
                        if (cb !== this) {
                            cb.checked = false;
                        }
                    });
                }
            });
        });
    });
</script>
<script>

    document.addEventListener('alpine:init', () => {
        Alpine.data('productForm', () => ({
            selectedType: localStorage.getItem('selectedType') || '{{ old("type") }}' || '',
            init() {
                // Filter dropdowns on initial load if type is already selected
                if (this.selectedType) {
                    setTimeout(() => {
                        updateLabelsWithType(this.selectedType);
                        filterDropdownsByType(this.selectedType);
                    }, 500);
                }
                
                // Watch for selectedType changes and filter dropdown options
                this.$watch('selectedType', (newType, oldType) => {
                    // If type is actually changing (not just initializing), clear all fields
                    if (oldType && oldType !== newType) {
                        clearAllFormFields();
                    }
                    // Update all labels with selected type
                    updateLabelsWithType(newType);
                    // Filter all dropdowns based on selected type
                    filterDropdownsByType(newType);
                    // Update required attributes based on field visibility
                    updateRequiredFields(newType);
                });
                
                // Initial update of required fields
                setTimeout(() => {
                    updateRequiredFields(this.selectedType);
                }, 500);
            },
            selectType(type) {
                // If type is changing (not the same), clear all form fields
                if (this.selectedType && this.selectedType !== type) {
                    clearAllFormFields();
                }
                
                this.selectedType = type;
                localStorage.setItem('selectedType', type);
                
                // Update all labels with selected type
                updateLabelsWithType(type);
                
                // Filter dropdowns by selected type
                filterDropdownsByType(type);
                
                // Update required attributes based on field visibility
                updateRequiredFields(type);
                
                // Load items by type when type changes
                if (type) {
                    loadItemsByType(type);
                } else {
                    loadAllItems();
                }
            }
        }));
    });

    // Function to update required fields based on type visibility
    function updateRequiredFields(type) {
        // Define which types show which fields (based on x-show conditions)
        const categoryTypes = ['parts', 'oil', 'scrap', 'services', 'filters', 'breakpad'];
        const companyTypes = ['parts', 'battery', 'oil', 'filters', 'breakpad'];
        const qualityTypes = ['parts']; // Quality shows only for parts
        const unitTypes = ['parts', 'battery', 'oil', 'scrap', 'filters', 'breakpad'];
        
        // Category field - required when visible
        const $category = $('#category');
        if ($category.length) {
            const $categoryContainer = $category.closest('.col-md-4');
            const isVisible = type && categoryTypes.includes(type);
            const isActuallyVisible = $categoryContainer.is(':visible') && !$categoryContainer.hasClass('d-none');
            
            if (isVisible && isActuallyVisible) {
                $category.prop('required', true);
                $category.attr('required', 'required');
            } else {
                $category.prop('required', false);
                $category.removeAttr('required');
            }
        }
        
        // Company field - required when visible
        const $company = $('#company_parts');
        if ($company.length) {
            const $companyContainer = $company.closest('.col-md-4');
            const isVisible = type && companyTypes.includes(type);
            const isActuallyVisible = $companyContainer.is(':visible') && !$companyContainer.hasClass('d-none');
            
            if (isVisible && isActuallyVisible) {
                $company.prop('required', true);
                $company.attr('required', 'required');
            } else {
                $company.prop('required', false);
                $company.removeAttr('required');
            }
        }
        
        // Quality field - required when visible (only for parts)
        const $quality = $('#quality');
        if ($quality.length) {
            const $qualityContainer = $quality.closest('.col-md-4');
            const isVisible = type && qualityTypes.includes(type);
            const isActuallyVisible = $qualityContainer.is(':visible') && !$qualityContainer.hasClass('d-none');
            
            if (isVisible && isActuallyVisible) {
                $quality.prop('required', true);
                $quality.attr('required', 'required');
            } else {
                $quality.prop('required', false);
                $quality.removeAttr('required');
            }
        }
        
        // Unit field - required when visible
        const $unit = $('#unit_parts');
        if ($unit.length) {
            const $unitContainer = $unit.closest('.col-12, .field-group').first();
            const isVisible = type && unitTypes.includes(type);
            const isActuallyVisible = $unitContainer.is(':visible') && !$unitContainer.hasClass('d-none');
            
            if (isVisible && isActuallyVisible) {
                $unit.prop('required', true);
                $unit.attr('required', 'required');
            } else {
                $unit.prop('required', false);
                $unit.removeAttr('required');
            }
        }
    }

    // Function to load items by type (with limit option)
    function loadItemsByType(type, loadAll = false) {
        const routeBase = '{{ route("items.by.type", ":type") }}';
        let routeUrl = routeBase.replace(':type', type);
        
        // Add 'all' parameter if loading all items
        if (loadAll) {
            routeUrl += '?all=true';
        }
        
        $.ajax({
            url: routeUrl,
            type: 'GET',
            success: function(response) {
                if (response.success && response.items) {
                    updateItemsTable(response.items);
                    // Update table title and button visibility
                    const typeNames = {
                        'parts': 'Parts',
                        'battery': 'Battery',
                        'oil': 'Oil',
                        'scrap': 'Scrap',
                        'services': 'Services',
                        'filters': 'Filters',
                        'breakpad': 'Break Pad'
                    };
                    
                    if (loadAll) {
                        // Show all items
                        $('#itemsTableTitle').text(`All ${typeNames[type] || type.toUpperCase()} Items (${response.total || response.items.length})`);
                        $('#allItemsButtonContainer').hide();
                    } else {
                        // Show last 5 items
                        $('#itemsTableTitle').text(`Last 5 ${typeNames[type] || type.toUpperCase()} Items`);
                        // Show "All Items" button if there are 5 items (might be more)
                        if (response.items.length >= 5) {
                            $('#allItemsButtonContainer').show();
                            // Re-initialize feather icons for the button
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                        } else {
                            $('#allItemsButtonContainer').hide();
                        }
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading items:', xhr);
                // Fallback: show all items
                loadAllItems();
            }
        });
    }

    // Function to load all items of selected type
    function loadAllItemsByType() {
        const savedType = localStorage.getItem('selectedType');
        if (savedType) {
            loadItemsByType(savedType, true);
        }
    }

    // Function to load all items (initial load)
    function loadAllItems() {
        $('#itemsTableTitle').text('Last 5 Created Items');
        $('#allItemsButtonContainer').hide();
        // Table is already populated by server, no need to reload
    }

    // Handle "All Items" button click
    $(document).on('click', '#loadAllItemsBtn', function() {
        loadAllItemsByType();
    });

    // Function to update labels with selected type
    function updateLabelsWithType(selectedType) {
        // Capitalize first letter of type for display
        const typeDisplay = selectedType ? selectedType.charAt(0).toUpperCase() + selectedType.slice(1) : '';
        
        // Update all labels with dynamic-label class
        $('.dynamic-label').each(function() {
            const $label = $(this);
            const originalText = $label.data('original') || $label.text().replace(/^[A-Za-z]+\s+/, '');
            
            // Store original if not already stored
            if (!$label.data('original')) {
                $label.data('original', originalText);
            }
            
            // Update label text
            if (selectedType && typeDisplay) {
                $label.text(typeDisplay + ' ' + originalText);
            } else {
                $label.text(originalText);
            }
        });
    }
    
    // Function to filter dropdowns by selected type
    function filterDropdownsByType(selectedType) {
        console.log('Filtering dropdowns by type:', selectedType);
        
        // List of all dropdowns that need filtering (using class selectors)
        const dropdowns = [
            '.company-select',
            '.technology-select',
            '.quality-select',
            '.name-select',
            '.category-select',
            '.part_number-select',
            '.group-select',
            '.plates-select',
            '.amperes-select',
            '.volt-select',
            '.cca-select',
            '.minus-pole-direction-select',
            '.Warrenty-select',
            '.made_in-select',
            '.grade-select',
            '.brand-select',
            '.mileage-select',
            '.level-select',
            '.formulas-select',
            '.Services-select',
            '.car-manufacturer-select',
            '.car-model-select',
            '.car-engine-select',
            '.car-country-select'
        ];

        dropdowns.forEach(selector => {
            $(selector).each(function() {
                const $select = $(this);
                const selectId = $select.attr('id');
                const currentValue = $select.val();
                const isSelect2 = $select.hasClass('select2-hidden-accessible');
                
                // Store current value before filtering
                let selectedValue = currentValue;
                
                // Show/hide options based on type
                let visibleCount = 0;
                let hasVisibleOptions = false;
                
                $select.find('option').each(function() {
                    const $option = $(this);
                    const optionType = $option.data('type') || '';
                    const optionValue = $option.val();
                    
                    // Show option if:
                    // 1. It's the empty/default option (always show)
                    // 2. No type is selected (show all)
                    // 3. Option type matches selected type
                    // 4. Option has no type (backward compatibility - show it)
                    const shouldShow = optionValue === '' || !selectedType || optionType === selectedType || optionType === '';
                    
                    if (shouldShow) {
                        $option.show().prop('disabled', false);
                        if (optionValue !== '') {
                            visibleCount++;
                            hasVisibleOptions = true;
                        }
                    } else {
                        // Hide and disable options that don't match the type
                        $option.hide().prop('disabled', true);
                    }
                });

                // If current selected value is hidden or disabled, clear selection
                const $selectedOption = $select.find('option:selected');
                if ($selectedOption.length && ($selectedOption.is(':hidden') || $selectedOption.prop('disabled'))) {
                    selectedValue = '';
                    $select.val('').trigger('change');
                }

                // For Select2, we need to properly refresh to show filtered options
                if (isSelect2) {
                    try {
                        // Close Select2 if it's open
                        if ($select.next('.select2-container').hasClass('select2-container--open')) {
                            $select.select2('close');
                        }
                        
                        // Destroy and reinitialize Select2 to reflect filtered options
                        $select.select2('destroy');
                        $select.removeClass('select2-hidden-accessible');
                        $select.next('.select2-container').remove();
                        
                        // Reinitialize Select2 with same options and type filtering
                        setTimeout(() => {
                            const select2Config = {
                                placeholder: 'Please Select',
                                allowClear: true,
                                width: '100%',
                                // Add matcher to filter by type during search
                                matcher: function(params, data) {
                                    // If no search term, show all visible options
                                    if (!params.term || params.term.trim() === '') {
                                        const $option = $(data.element);
                                        const optionType = $option.data('type') || '';
                                        // Show if no type selected, or type matches, or option has no type
                                        if (!selectedType || optionType === selectedType || optionType === '') {
                                            return data;
                                        }
                                        return null;
                                    }
                                    
                                    // If there's a search term, filter by both text and type
                                    const term = params.term.toLowerCase();
                                    const text = data.text ? data.text.toLowerCase() : '';
                                    const $option = $(data.element);
                                    const optionType = $option.data('type') || '';
                                    
                                    // Check if text matches search term
                                    const textMatches = text.indexOf(term) > -1;
                                    
                                    // Check if type matches (if type is selected)
                                    const typeMatches = !selectedType || optionType === selectedType || optionType === '';
                                    
                                    // Return data only if both text and type match
                                    if (textMatches && typeMatches) {
                                        return data;
                                    }
                                    
                                    return null;
                                }
                            };
                            
                            // Special handling for part_number_id - force dropdown above
                            if (selectId === 'part_number_id') {
                                select2Config.dropdownPosition = 'above';
                            }
                            
                            $select.select2(select2Config);
                            
                            // Restore selection if it's still valid
                            if (selectedValue) {
                                const $optionToSelect = $select.find(`option[value="${selectedValue}"]:not(:hidden):not([disabled])`);
                                if ($optionToSelect.length) {
                                    $select.val(selectedValue).trigger('change');
                                }
                            }
                        }, 100);
                    } catch(e) {
                        console.error('Error refreshing Select2 for', selectId, e);
                        // Fallback: just trigger change
                        $select.trigger('change');
                    }
                } else {
                    // For regular selects, just trigger change
                    $select.trigger('change');
                }
            });
        });
        
    }
    
    // Function to clear all form fields when type changes
    function clearAllFormFields() {
        console.log('Clearing all form fields due to type change');
        
        // Clear all select/dropdown fields
        const dropdowns = [
            '.searchable-select',
            'select[name="p_id"]',
            'select[name="category_id"]',
            'select[name="part_number_id"]',
            'select[name="company_id"]',
            'select[name="technology"]',
            'select[name="quality_id"]',
            'select[name="group"]',
            'select[name="unit"]',
            'select[name="vehical_id"]',
            'select[name="car_manufacturer"]',
            'select[name="car_model_name"]',
            'select[name="engine_cc"]',
            'select[name="car_manufactured_country"]',
            'select[name="v_part_number_id"]'
        ];
        
        dropdowns.forEach(selector => {
            $(selector).each(function() {
                const $select = $(this);
                const isSelect2 = $select.hasClass('select2-hidden-accessible');
                
                // Clear the value
                $select.val('').trigger('change');
                
                // If it's Select2, close it if open
                if (isSelect2) {
                    try {
                        if ($select.next('.select2-container').hasClass('select2-container--open')) {
                            $select.select2('close');
                        }
                    } catch(e) {
                        // Ignore errors
                    }
                }
            });
        });
        
        // Clear all text inputs (except hidden inputs and specific fields that should persist)
        $('input[type="text"], input[type="number"], textarea').each(function() {
            const $input = $(this);
            const inputName = $input.attr('name') || '';
            const inputId = $input.attr('id') || '';
            
            // Skip hidden inputs, type input, and specific fields
            if ($input.is(':hidden') || 
                inputName === 'type' || 
                inputName === 'user_id' ||
                inputId === 'hidden_quality_id' ||
                inputId === 'hidden_technology') {
                return;
            }
            
            // Clear the input
            $input.val('').trigger('change').trigger('input');
        });
        
        // Clear file inputs
        $('input[type="file"]').each(function() {
            $(this).val('');
        });
        
        // Clear checkboxes (except specific ones)
        $('input[type="checkbox"]').each(function() {
            const $checkbox = $(this);
            const checkboxName = $checkbox.attr('name') || '';
            
            // Skip if it's a specific checkbox that should persist
            if (checkboxName === 'status' || checkboxName === 'is_active') {
                return;
            }
            
            $checkbox.prop('checked', false).trigger('change');
        });
        
        // Clear radio buttons
        $('input[type="radio"]').prop('checked', false).trigger('change');
        
        // Reset any custom form states
        if (typeof window.activeSelectSearch !== 'undefined') {
            window.activeSelectSearch = null;
        }
        if (typeof window.lastSearchTerm !== 'undefined') {
            window.lastSearchTerm = {};
        }
    }
    
    // Helper function to get current selected type
    function getCurrentSelectedType() {
        try {
            const alpineComponent = Alpine.$data(document.querySelector('[x-data*="productForm"]'));
            if (alpineComponent && alpineComponent.selectedType) {
                return alpineComponent.selectedType;
            }
        } catch(e) {
            // Alpine might not be ready yet
        }
        
        // Fallback: try to get from hidden input
        const typeInput = document.querySelector('input[name="type"]');
        if (typeInput) {
            return typeInput.value;
        }
        
        return localStorage.getItem('selectedType') || '';
    }
    
    // Initialize Select2 with type filtering for all searchable-select dropdowns on page load
    $(document).ready(function() {
        // Wait a bit for Alpine.js to initialize
        setTimeout(function() {
            const selectedType = getCurrentSelectedType();
            
            // Initialize Select2 for all searchable-select dropdowns that aren't already initialized
            $('.searchable-select').each(function() {
                const $select = $(this);
                const selectId = $select.attr('id');
                
                // Skip if already initialized
                if ($select.hasClass('select2-hidden-accessible')) {
                    return;
                }
                
                // Skip part_number_id and unit_parts as they have special initialization
                if (selectId === 'part_number_id' || selectId === 'unit_parts') {
                    return;
                }
                
                // Initialize Select2 with type-based matcher
                const select2Config = {
                    placeholder: 'Please Select',
                    allowClear: true,
                    width: '100%',
                    matcher: function(params, data) {
                        const currentType = getCurrentSelectedType();
                        
                        // If no search term, show all visible options
                        if (!params.term || params.term.trim() === '') {
                            const $option = $(data.element);
                            const optionType = $option.data('type') || '';
                            // Show if no type selected, or type matches, or option has no type
                            if (!currentType || optionType === currentType || optionType === '') {
                                return data;
                            }
                            return null;
                        }
                        
                        // If there's a search term, filter by both text and type
                        const term = params.term.toLowerCase();
                        const text = data.text ? data.text.toLowerCase() : '';
                        const $option = $(data.element);
                        const optionType = $option.data('type') || '';
                        
                        // Check if text matches search term
                        const textMatches = text.indexOf(term) > -1;
                        
                        // Check if type matches (if type is selected)
                        const typeMatches = !currentType || optionType === currentType || optionType === '';
                        
                        // Return data only if both text and type match
                        if (textMatches && typeMatches) {
                            return data;
                        }
                        
                        return null;
                    }
                };
                
                // Special handling for part_number_id - force dropdown above
                if (selectId === 'part_number_id') {
                    select2Config.dropdownPosition = 'above';
                }
                
                $select.select2(select2Config);
            });
        }, 1000);
    });

    // Function to update the items table
    function updateItemsTable(items) {
        const tbody = $('#latestItemsTableBody');
        tbody.empty();

        if (items.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="4" class="text-center">No items found for this type.</td>
                </tr>
            `);
            return;
        }

        items.forEach(function(item) {
            const activeBadge = item.is_active 
                ? '<span class="badge bg-success">Active</span>' 
                : '<span class="badge bg-danger">Inactive</span>';

            const csrfToken = $('input[name="_token"]').val();
            
            // Build item details HTML
            const itemDetails = `
                <div class="small">
                    <div> ${item.part_number || '-'}</div>
                    <div> ${item.category_name || '-'}</div>
                    <div> ${item.company_name || '-'}</div>
                    <div> ${item.quality_name || '-'}</div>
                </div>
            `;

            const row = `
                <tr>
                    <td>
                        <img src="${item.image}" width="70" height="70"
                            class="rounded item-image" style="cursor:pointer;" data-bs-toggle="modal"
                            data-bs-target="#imageModal"
                            data-src="${item.image}">
                    </td>
                    <td>
                        ${itemDetails}
                    </td>
                   
                    <td>${item.user_name}</td>
                     <td>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="${item.show_url}">
                                        <i data-feather="eye" class="me-1"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="${item.edit_url}">
                                        <i data-feather="edit" class="me-1"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" onclick="confirmDelete('delete-form-${item.id}')"
                                        class="p-2">
                                        <i data-feather="trash-2" class="feather-trash-2"></i> Delete
                                    </a>
                                    <form id="delete-form-${item.id}"
                                        action="${item.delete_url}" method="POST"
                                        style="display: none;">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                    </form>
                                </li>
                                <hr>
                                <li>
                                    <a class="dropdown-item text-primary" href="${item.duplicate_url}">
                                        <i data-feather="copy" class="me-1"></i> Duplicate
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    // Load items by type on page load if type is already selected
    $(document).ready(function() {
        const savedType = localStorage.getItem('selectedType');
        if (savedType) {
            // Wait a bit for Alpine.js to initialize
            setTimeout(function() {
                loadItemsByType(savedType, false);
            }, 300);
        } else {
            // Hide button if no type is selected
            $('#allItemsButtonContainer').hide();
        }
    });
    $(document).ready(function() {
        feather.replace();
        
        // Update required fields on page load after Alpine initializes
        setTimeout(function() {
            const initialType = localStorage.getItem('selectedType') || '{{ old("type") }}' || '';
            if (initialType) {
                updateRequiredFields(initialType);
            }
        }, 1000);
        // Generate random barcode
        function generateRandomItemCode() {
            const digits = '0123456789';
            let result = '';
            for (let i = 0; i < 10; i++) {
                result += digits.charAt(Math.floor(Math.random() * digits.length));
            }
            return result;
        }
        if (!$('#itemBarCode').val()) {
            $('#itemBarCode').val(generateRandomItemCode());
        }
        $(document).on('click', '.generate-code-btn', function() {
            $('#itemBarCode').val(generateRandomItemCode());
        });
        // Thumbnail handler
        function initializeThumbnailHandler() {
            const imageInput = $('#imageInput')[0];
            const preview = $('#imagePreview')[0];
            const container = $('#previewContainer')[0];
            const removeBtn = $('#removeBtn')[0];
            const defaultImg = "https://pdis.co.kr/img/image.jpg";
            if (!imageInput || !preview || !container || !removeBtn) return;

            // Using label for file input - works on all mobile browsers (Chrome, Firefox, etc.)
            // Label automatically triggers the input when clicked, preserving camera/gallery options
            // No need for JavaScript click handler when using label

            imageInput.onchange = function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        container.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            };
            removeBtn.onclick = () => {
                preview.src = defaultImg;
                imageInput.value = '';
                container.style.display = 'none';
            };
        }
        // Multiple images handler
        function initializeImagesHandler() {
            const input = $('#imagesInput')[0];
            const preview = $('#imagesPreview')[0];
            const container = $('#imagesPreviewContainer')[0];
            let allFiles = [];
            if (!input || !preview || !container) return;

            // Using label for file input - works on all mobile browsers (Chrome, Firefox, etc.)
            // Label automatically triggers the input when clicked, preserving camera/gallery options
            // No need for JavaScript click handler when using label
            input.onchange = function() {
                const newFiles = Array.from(this.files);
                // add newly selected files to previous files
                allFiles = [...allFiles, ...newFiles];
                // update input.files
                const dt = new DataTransfer();
                allFiles.forEach(f => dt.items.add(f));
                input.files = dt.files;
                // show preview
                preview.innerHTML = "";
                container.style.display = "block";
                allFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = e => {
                        const div = document.createElement('div');
                        div.className = 'position-relative';
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-fluid rounded border';
                        img.style.maxHeight = '150px';
                        const remove = document.createElement('button');
                        remove.type = 'button';
                        remove.className = 'btn btn-danger btn-sm position-absolute';
                        remove.style.top = '5px';
                        remove.style.right = '5px';
                        remove.innerHTML = '<i data-feather="x"></i>';
                        remove.onclick = () => {
                            allFiles.splice(index, 1);
                            const dt2 = new DataTransfer();
                            allFiles.forEach(f => dt2.items.add(f));
                            input.files = dt2.files;
                            div.remove();
                            if (allFiles.length === 0) container.style.display = 'none';
                        };
                        div.appendChild(img);
                        div.appendChild(remove);
                        preview.appendChild(div);
                        feather.replace();
                    };
                    reader.readAsDataURL(file);
                });
            };
        }
        initializeThumbnailHandler();
        initializeImagesHandler();
        // Universal Modal
        let currentTargetSelect = null;
        let currentEditId = null;
        let deleteRoute = null;
        
        // =========================
        // Universal "Add New" Modal Behavior for Searchable Dropdowns
        // NEW APPROACH: Direct and Simple
        
        // =========================
        // PART NUMBER SEARCH & ADD FUNCTIONALITY (React-style)
        // =========================
        
        // Function to check and show "Add New Part Number" button
        function checkAndShowAddPartNumberButton() {
            const $openSelect2 = $('.select2-container--open');
            if ($openSelect2.length) {
                // Verify this is the part number dropdown
                const $partNumberSelect = $('#part_number_id');
                const $partNumberContainer = $partNumberSelect.next('.select2-container');
                if (!$partNumberContainer.is($openSelect2)) {
                    return; // Not the part number dropdown
                }
                
                const $noResultsMsg = $openSelect2.find('.select2-results__message');
                const $results = $openSelect2.find('.select2-results__option--selectable:not(.select2-results__option--loading)');
                const $searchInput = $openSelect2.find('.select2-search__field');
                const $resultsContainer = $openSelect2.find('.select2-results');
                
                if ($searchInput.length && $searchInput.val()) {
                    const searchVal = $searchInput.val().trim();
                    // Show button if no results message is visible OR no selectable options found
                    const hasNoResults = ($noResultsMsg.length && $noResultsMsg.is(':visible')) || 
                                        ($results.length === 0 && searchVal.length > 0);
                    
                    if (hasNoResults && searchVal.length > 0) {
                        // Hide the default "No results found" message
                        if ($noResultsMsg.length) {
                            $noResultsMsg.hide();
                        }
                        
                        // Check if Add New button already exists in results container
                        let $addNewBtnInDropdown = $resultsContainer.find('.add-new-part-number-btn');
                        
                        if (!$addNewBtnInDropdown.length && $resultsContainer.length) {
                            // Create and add the button inside Select2 results container
                            const buttonHtml = `
                                <div class="select2-results__option select2-results__option--add-new" style="padding: 10px; text-align: center; border-top: 1px solid #ddd;">
                                    <button type="button" class="btn btn-success btn-sm w-100 add-new-part-number-btn open-universal-modal" 
                                            data-title="Add Part Number" 
                                            data-mode="add"
                                            data-route="{{ route('post.partnumber') }}"
                                            data-target-select=".part_number-select"
                                            style="background: #f97316; border: none; box-shadow: 0 4px 14px 0 rgba(249, 115, 22, 0.2);">
                                        <i data-feather="plus" class="feather-plus me-1"></i>
                                        Add "<span class="part-number-search-term fw-bold">${searchVal}</span>"
                                    </button>
                                </div>
                            `;
                            $resultsContainer.append(buttonHtml);
                            
                            // Initialize feather icons for the new button
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                            
                            // Update search term in button
                            $resultsContainer.find('.part-number-search-term').text(searchVal);
                        } else if ($addNewBtnInDropdown.length) {
                            // Update search term if button already exists
                            $resultsContainer.find('.part-number-search-term').text(searchVal);
                        }
                    } else if ($results.length > 0) {
                        // Hide button if results are found
                        $resultsContainer.find('.add-new-part-number-btn').closest('.select2-results__option--add-new').remove();
                        
                        // Show default message if it was hidden
                        if ($noResultsMsg.length) {
                            $noResultsMsg.show();
                        }
                    }
                } else {
                    // Hide button if search is empty
                    $openSelect2.find('.select2-results').find('.add-new-part-number-btn').closest('.select2-results__option--add-new').remove();
                    
                    // Show default message if it was hidden
                    if ($noResultsMsg.length) {
                        $noResultsMsg.show();
                    }
                }
            }
        }
        
        // When part number dropdown opens - positioning handled by general code, only handle "Add New" button
        $(document).on('select2:opening', '#part_number_id', function(e) {
            // Focus search input as soon as dropdown starts opening
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    const $searchInput = $('#part_number_id').next('.select2-container').find('.select2-search__field');
                    if ($searchInput.length) {
                        $searchInput[0].focus();
                        $searchInput[0].select();
                    }
                });
            });
        });
        
        $(document).on('select2:open', '#part_number_id', function(e) {
            // General code (line 4395) will handle positioning automatically
            // We only need to handle "Add New Part Number" button functionality here
            
            // Immediately focus search input - multiple attempts to ensure it works
            function focusSearchInput() {
                const $partNumberContainer = $('#part_number_id').next('.select2-container');
                if ($partNumberContainer.length) {
                    const $searchInput = $partNumberContainer.find('.select2-search__field');
                    if ($searchInput.length && $searchInput.length > 0) {
                        const searchInput = $searchInput[0];
                        if (searchInput) {
                            // Focus and select to ensure it's ready for typing
                            searchInput.focus();
                            searchInput.select();
                            return true;
                        }
                    }
                }
                return false;
            }
            
            // Try immediately with requestAnimationFrame for better timing
            requestAnimationFrame(function() {
                focusSearchInput();
            });
            
            // Try multiple times with different delays to ensure it works
            setTimeout(function() {
                focusSearchInput();
            }, 0);
            
            setTimeout(function() {
                focusSearchInput();
            }, 10);
            
            setTimeout(function() {
                focusSearchInput();
            }, 30);
            
            setTimeout(function() {
                focusSearchInput();
            }, 50);
            
            setTimeout(function() {
                const $searchInput = $('.select2-container--open .select2-search__field');
                if ($searchInput.length) {
                    $searchInput[0].focus();
                    $searchInput[0].select();
                    
                    // Real-time check for no results
                    $searchInput.off('input.partNumberSearch').on('input.partNumberSearch', function() {
                        setTimeout(function() {
                            checkAndShowAddPartNumberButton();
                        }, 300);
                    });
                    
                    // Handle Enter key press for Part Number - trigger Add New button or open modal
                    $searchInput.off('keydown.partNumberEnter').on('keydown.partNumberEnter', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            const searchText = $(this).val().trim();
                            
                            // Check if Add New button exists
                            const $addNewBtn = $('.select2-container--open .add-new-part-number-btn');
                            if ($addNewBtn.length) {
                                // Trigger the button click
                                $addNewBtn.trigger('mousedown');
                                $addNewBtn.trigger('click');
                                return false;
                            } else if (searchText) {
                                // If button doesn't exist but there's search text, check for no results
                                const $openSelect2 = $('.select2-container--open');
                                const $noResultsMsg = $openSelect2.find('.select2-results__message');
                                const $results = $openSelect2.find('.select2-results__option--selectable:not(.select2-results__option--loading)');
                                const hasNoResults = ($noResultsMsg.length && $noResultsMsg.is(':visible')) || 
                                                    ($results.length === 0 && searchText.length > 0);
                                
                                if (hasNoResults) {
                                    // Close Select2 dropdown
                                    $('#part_number_id').select2('close');
                                    
                                    // Store search term
                                    if (!window.lastSearchTerm) {
                                        window.lastSearchTerm = {};
                                    }
                                    window.lastSearchTerm['part_number_id'] = searchText;
                                    window.activeSelectSearch = {
                                        selectId: 'part_number_id',
                                        searchTerm: searchText,
                                        hasNoResults: true
                                    };
                                    window.partNumberSubtitle = 'PART NUMBER:';
                                    
                                    // Open universal modal
                                    const $addButton = $('#part_number_id').closest('.input-group').find('.open-universal-modal[data-mode="add"]');
                                    if ($addButton.length) {
                                        $addButton.trigger('click');
                                    } else {
                                        // Fallback: manually configure and open modal for part number
                                        const currentTargetSelect = '#part_number_id';
                                        const $select = $(currentTargetSelect);
                                        
                                        // Set modal configuration
                                        $('#universal-add-modal').attr('data-select-id', currentTargetSelect);
                                        $('#universal-modal-title').text('Add Part Number');
                                        $('#universal-subtitle').text('PART NUMBER:');
                                        
                                        // Set form action
                                        const $form = $('#universal-form');
                                        $form.attr('data-route', '{{ route("post.partnumber") }}');
                                        $form.attr('data-target-select', '.part_number-select');
                                        
                                        // Pre-fill the name field with search text
                                        $('#universal-name').val(searchText);
                                        
                                        // Set the type if available
                                        const currentSelectedType = getCurrentSelectedType();
                                        if (currentSelectedType) {
                                            $('#universal-type').val(currentSelectedType);
                                        }
                                        
                                        // Store current target select for form submission
                                        window.currentTargetSelect = currentTargetSelect;
                                        
                                        // Open modal
                                        $('#universal-add-modal').modal('show');
                                        
                                        // Focus input with multiple strategies
                                        function focusUniversalInput() {
                                            const $input = $('#universal-name');
                                            if ($input.length) {
                                                $input[0].focus();
                                                if (searchText) {
                                                    requestAnimationFrame(function() {
                                                        $input[0].select();
                                                        const len = $input.val().length;
                                                        if ($input[0].setSelectionRange) {
                                                            $input[0].setSelectionRange(len, len);
                                                        }
                                                    });
                                                }
                                            }
                                        }
                                        
                                        setTimeout(focusUniversalInput, 100);
                                        setTimeout(focusUniversalInput, 300);
                                        setTimeout(focusUniversalInput, 500);
                                        
                                        $('#universal-add-modal').one('shown.bs.modal', function() {
                                            requestAnimationFrame(function() {
                                                requestAnimationFrame(function() {
                                                    focusUniversalInput();
                                                });
                                            });
                                        });
                                    }
                                    return false;
                                }
                            }
                        }
                    });
                    
                    // Monitor Select2 results for "No results found" - check every 200ms
                    let checkNoResultsInterval = setInterval(function() {
                        const $openSelect2 = $('.select2-container--open');
                        if ($openSelect2.length) {
                            checkAndShowAddPartNumberButton();
                        } else {
                            clearInterval(checkNoResultsInterval);
                        }
                    }, 200);
                    
                    // Clear interval when dropdown closes
                    $(document).one('select2:close', '#part_number_id', function() {
                        clearInterval(checkNoResultsInterval);
                    });
                }
            }, 100);
        
            // Also listen to Select2 results update event
            $(document).off('select2:results:message', '#part_number_id').on('select2:results:message', '#part_number_id', function(e) {
                setTimeout(function() {
                    checkAndShowAddPartNumberButton();
                }, 100);
            });
        });
        
        // When product name dropdown opens - auto focus search input
        $(document).on('select2:opening', '#product_name_item', function(e) {
            // Focus search input as soon as dropdown starts opening
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    const $searchInput = $('#product_name_item').next('.select2-container').find('.select2-search__field');
                    if ($searchInput.length) {
                        $searchInput[0].focus();
                        $searchInput[0].select();
                    }
                });
            });
        });
        
        $(document).on('select2:open', '#product_name_item', function(e) {
            // Immediately focus search input - multiple attempts to ensure it works
            function focusSearchInput() {
                const $productNameContainer = $('#product_name_item').next('.select2-container');
                if ($productNameContainer.length) {
                    const $searchInput = $productNameContainer.find('.select2-search__field');
                    if ($searchInput.length && $searchInput.length > 0) {
                        const searchInput = $searchInput[0];
                        if (searchInput) {
                            // Focus and select to ensure it's ready for typing
                            searchInput.focus();
                            searchInput.select();
                            return true;
                        }
                    }
                }
                return false;
                    }
                    
            // Try immediately with requestAnimationFrame for better timing
            requestAnimationFrame(function() {
                focusSearchInput();
            });
            
            // Try multiple times with different delays to ensure it works
            setTimeout(function() {
                focusSearchInput();
            }, 0);
            
            setTimeout(function() {
                focusSearchInput();
            }, 10);
            
            setTimeout(function() {
                focusSearchInput();
            }, 30);
            
            setTimeout(function() {
                focusSearchInput();
            }, 50);
            
            setTimeout(function() {
                const $searchInput = $('.select2-container--open .select2-search__field');
                if ($searchInput.length) {
                    $searchInput[0].focus();
                    $searchInput[0].select();
                    
                    // Note: Enter key handling is now done in the generic dropdown handler
                    // This ensures consistent behavior across all dropdowns
                }
            }, 100);
        });
        
        // =========================
        // GENERIC "ADD NEW" FUNCTIONALITY FOR ALL DROPDOWNS
        // =========================
        
        // Generic function to check and show "Add New" button for any dropdown
        function checkAndShowAddNewButtonForDropdown(selectId, buttonConfig) {
            const $openSelect2 = $('.select2-container--open');
            if ($openSelect2.length) {
                // Verify this is the correct dropdown
                const $select = $('#' + selectId);
                if (!$select.length) return;
                
                const $selectContainer = $select.next('.select2-container');
                if (!$selectContainer.is($openSelect2)) {
                    return; // Not the correct dropdown
                }
                
                const $noResultsMsg = $openSelect2.find('.select2-results__message');
                const $results = $openSelect2.find('.select2-results__option--selectable:not(.select2-results__option--loading)');
                const $searchInput = $openSelect2.find('.select2-search__field');
                const $resultsContainer = $openSelect2.find('.select2-results');
                
                if ($searchInput.length && $searchInput.val() && buttonConfig) {
                    const searchVal = $searchInput.val().trim();
                    const hasNoResults = ($noResultsMsg.length && $noResultsMsg.is(':visible')) || 
                                        ($results.length === 0 && searchVal.length > 0);
                    
                    if (hasNoResults && searchVal.length > 0) {
                        // Hide the default "No results found" message
                        if ($noResultsMsg.length) {
                            $noResultsMsg.hide();
                        }
                        
                        // Check if Add New button already exists
                        let $addNewBtnInDropdown = $resultsContainer.find('.add-new-dropdown-btn[data-select-id="' + selectId + '"]');
                        
                        if (!$addNewBtnInDropdown.length && $resultsContainer.length) {
                            // Create and add the button
                            const buttonHtml = `
                                <div class="select2-results__option select2-results__option--add-new" style="padding: 10px; text-align: center; border-top: 1px solid #ddd;">
                                    <button type="button" class="btn btn-success btn-sm w-100 add-new-dropdown-btn open-universal-modal" 
                                            data-select-id="${selectId}"
                                            data-title="${buttonConfig.title}" 
                                            data-mode="add"
                                            data-route="${buttonConfig.route}"
                                            data-target-select="${buttonConfig.targetSelect}"
                                            ${buttonConfig.hasImage ? 'data-has-image="1"' : ''}
                                            style="background: #f97316; border: none; box-shadow: 0 4px 14px 0 rgba(249, 115, 22, 0.2);">
                                        <i data-feather="plus" class="feather-plus me-1"></i>
                                        Add "<span class="dropdown-search-term fw-bold">${searchVal}</span>"
                                    </button>
                                </div>
                            `;
                            $resultsContainer.append(buttonHtml);
                            
                            // Initialize feather icons
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                            
                            // Update search term
                            $resultsContainer.find('.dropdown-search-term').text(searchVal);
                        } else if ($addNewBtnInDropdown.length) {
                            // Update search term if button already exists
                            $resultsContainer.find('.dropdown-search-term').text(searchVal);
                        }
                    } else if ($results.length > 0) {
                        // Hide button if results are found
                        $resultsContainer.find('.add-new-dropdown-btn[data-select-id="' + selectId + '"]').closest('.select2-results__option--add-new').remove();
                        
                        // Show default message
                        if ($noResultsMsg.length) {
                            $noResultsMsg.show();
                        }
                    }
                } else {
                    // Hide button if search is empty
                    $openSelect2.find('.select2-results').find('.add-new-dropdown-btn[data-select-id="' + selectId + '"]').closest('.select2-results__option--add-new').remove();
                    
                    // Show default message
                    if ($noResultsMsg.length) {
                        $noResultsMsg.show();
                    }
                }
            }
        }
        
        // Auto focus search input for Category, Company, Quality, and Unit dropdowns
        $(document).on('select2:opening', '#category, #company_parts, #quality, #unit_parts', function(e) {
            const selectId = $(this).attr('id');
            // Focus search input as soon as dropdown starts opening
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    const $searchInput = $('#' + selectId).next('.select2-container').find('.select2-search__field');
                    if ($searchInput.length) {
                        $searchInput[0].focus();
                        $searchInput[0].select();
                    }
                });
            });
        });
        
        // Generic handler for all dropdowns (Product Name, Category, Company, Quality, Unit, etc.)
        // This handles all searchable-select dropdowns except part_number_id (which has its own handler)
        $(document).on('select2:open', '.searchable-select:not(#part_number_id)', function(e) {
            const $select = $(this);
            const selectId = $select.attr('id');
            
            if (!selectId) return;
            
            // Auto focus search input for Category, Company, Quality, and Unit
            if (selectId === 'category' || selectId === 'company_parts' || selectId === 'quality' || selectId === 'unit_parts') {
                function focusSearchInput() {
                    const $container = $select.next('.select2-container');
                    if ($container.length) {
                        const $searchInput = $container.find('.select2-search__field');
                        if ($searchInput.length && $searchInput.length > 0) {
                            const searchInput = $searchInput[0];
                            if (searchInput) {
                                searchInput.focus();
                                searchInput.select();
                                return true;
                            }
                        }
                    }
                    return false;
                }
                
                requestAnimationFrame(function() {
                    focusSearchInput();
                });
                
                setTimeout(function() {
                    focusSearchInput();
                }, 0);
                
                setTimeout(function() {
                    focusSearchInput();
                }, 10);
                
                setTimeout(function() {
                    focusSearchInput();
                }, 30);
                
                setTimeout(function() {
                    focusSearchInput();
                }, 50);
                
                setTimeout(function() {
                const $searchInput = $('.select2-container--open .select2-search__field');
                if ($searchInput.length) {
                        $searchInput[0].focus();
                        $searchInput[0].select();
                    }
                }, 100);
            }
            
            // Get button configuration from the existing Edit button's sibling Add button (if exists) or from data attributes
            let buttonConfig = null;
            
            // Try to find Add button in the same input-group
            const $addButton = $select.closest('.input-group').find('.open-universal-modal[data-mode="add"]');
            if ($addButton.length) {
                buttonConfig = {
                    title: $addButton.data('title') || 'Add New',
                    route: $addButton.data('route') || '',
                    targetSelect: $addButton.data('target-select') || '',
                    hasImage: $addButton.data('has-image') == 1
                };
            } else {
                // Fallback: Create config based on select ID
                const configMap = {
                    'product_name_item': {
                        title: 'Add Product',
                        route: '{{ route("post.product") }}',
                        targetSelect: '.name-select'
                    },
                    'part_number_id': {
                        title: 'Add Part Number',
                        route: '{{ route("post.partnumber") }}',
                        targetSelect: '.part_number-select'
                    },
                    'category': {
                        title: 'Add Category',
                        route: '{{ route("post.item.category") }}',
                        targetSelect: '.category-select',
                        hasImage: true
                    },
                    'company_parts': {
                        title: 'Add Company',
                        route: '{{ route("post.companies") }}',
                        targetSelect: '.company-select'
                    },
                    'quality': {
                        title: 'Add Quality',
                        route: '{{ route("post.qualities") }}',
                        targetSelect: '.quality-select'
                    },
                    'quality_filters': {
                        title: 'Add Quality',
                        route: '{{ route("post.qualities") }}',
                        targetSelect: '.quality-select'
                    },
                    'quality_breakpad': {
                        title: 'Add Quality',
                        route: '{{ route("post.qualities") }}',
                        targetSelect: '.quality-select'
                    },
                    'unit_parts': {
                        title: 'Add Unit',
                        route: '{{ route("post.units") }}',
                        targetSelect: '#unit_parts'
                    },
                    'vehicle_manufacturer_select': {
                        title: 'Add Manufacturer',
                        route: '{{ route("post.car.manufacturer") }}',
                        targetSelect: '.car-manufacturer-select'
                    },
                    'vehicle_model_select': {
                        title: 'Add Car Model',
                        route: '{{ route("post.car.model") }}',
                        targetSelect: '.car-model-select'
                    },
                    'vehicle_country_select': {
                        title: 'Add Country',
                        route: '{{ route("post.car.country") }}',
                        targetSelect: '.car-country-select'
                    },
                    'vehicle_engine_select': {
                        title: 'Add Engine CC',
                        route: '{{ route("post.engine.cc") }}',
                        targetSelect: '.car-engine-select'
                    },
                    'car_manufacturer': {
                        title: 'Add Manufacturer',
                        route: '{{ route("post.car.manufacturer") }}',
                        targetSelect: '.car-manufacturer-select'
                    },
                    'car_model_name': {
                        title: 'Add Car Model',
                        route: '{{ route("post.car.model") }}',
                        targetSelect: '.car-model-select'
                    },
                    'car_manufactured_country': {
                        title: 'Add Country',
                        route: '{{ route("post.car.country") }}',
                        targetSelect: '.car-country-select'
                    },
                    'engine_cc': {
                        title: 'Add Engine CC',
                        route: '{{ route("post.engine.cc") }}',
                        targetSelect: '.car-engine-select'
                    },
                    'part_number': {
                        title: 'Add Part Number',
                        route: '{{ route("post.partnumber") }}',
                        targetSelect: '.part_number-select'
                    }
                };
                
                if (configMap[selectId]) {
                    buttonConfig = configMap[selectId];
                }
            }
            
            if (!buttonConfig) return;
            
            // Monitor for no results and show Add New button
            setTimeout(function() {
                const $searchInput = $('.select2-container--open .select2-search__field');
                if ($searchInput.length) {
                    // Real-time check for no results
                    $searchInput.off('input.dropdownSearch').on('input.dropdownSearch', function() {
                        setTimeout(function() {
                            checkAndShowAddNewButtonForDropdown(selectId, buttonConfig);
                        }, 300);
                    });
                    
                    // Handle Enter key press for ALL dropdowns - trigger Add New button or open modal
                    // This works for all dropdowns that have buttonConfig (unit_parts, product_name_item, category, company_parts, quality, etc.)
                    $searchInput.off('keydown.dropdownEnter').on('keydown.dropdownEnter', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            const searchText = $(this).val().trim();
                            
                            // Check if Add New button exists in dropdown
                            const $addNewBtn = $('.select2-container--open .add-new-dropdown-btn[data-select-id="' + selectId + '"]');
                            if ($addNewBtn.length) {
                                // Trigger the button click
                                $addNewBtn.trigger('mousedown');
                                $addNewBtn.trigger('click');
                                return false;
                            } else if (searchText && buttonConfig) {
                                // If button doesn't exist but there's search text and buttonConfig, check for no results
                                const $openSelect2 = $('.select2-container--open');
                                const $noResultsMsg = $openSelect2.find('.select2-results__message');
                                const $results = $openSelect2.find('.select2-results__option--selectable:not(.select2-results__option--loading)');
                                const hasNoResults = ($noResultsMsg.length && $noResultsMsg.is(':visible')) || 
                                                    ($results.length === 0 && searchText.length > 0);
                                
                                if (hasNoResults) {
                                    // Close Select2 dropdown
                                    $('#' + selectId).select2('close');
                                    
                                    // Store search term
                                    if (!window.lastSearchTerm) {
                                        window.lastSearchTerm = {};
                                    }
                                    window.lastSearchTerm[selectId] = searchText;
                                    window.activeSelectSearch = {
                                        selectId: selectId,
                                        searchTerm: searchText,
                                        hasNoResults: true
                                    };
                                    
                                    // Set specific subtitles for known dropdowns
                                    if (selectId === 'product_name_item') {
                                        window.productNameSubtitle = 'PRODUCT NAME:';
                                    } else if (selectId === 'part_number_id') {
                                        window.partNumberSubtitle = 'PART NUMBER:';
                                    }
                                    
                                    // Open universal modal - try to find Add button first
                                    const $addButton = $('#' + selectId).closest('.input-group').find('.open-universal-modal[data-mode="add"]');
                                    if ($addButton.length) {
                                        $addButton.trigger('click');
                                    } else {
                                        // Fallback: manually open modal with config
                                        // The modal handlers will use activeSelectSearch to pre-fill the form
                                        $('#universal-add-modal').modal('show');
                                    }
                                    return false;
                                }
                            }
                        }
                    });
                    
                    // Monitor Select2 results for "No results found" - check every 200ms
                    let checkNoResultsInterval = setInterval(function() {
                const $openSelect2 = $('.select2-container--open');
                if ($openSelect2.length) {
                            checkAndShowAddNewButtonForDropdown(selectId, buttonConfig);
                        } else {
                            clearInterval(checkNoResultsInterval);
                        }
                    }, 200);
                    
                    // Clear interval when dropdown closes
                    $(document).one('select2:close', '#' + selectId, function() {
                        clearInterval(checkNoResultsInterval);
                        // Remove Enter key handler when dropdown closes (works for all dropdowns)
                        $searchInput.off('keydown.dropdownEnter');
                        // Remove button from inside Select2 dropdown
                        const $selectContainer = $('#' + selectId).next('.select2-container');
                        if ($selectContainer.length) {
                            $selectContainer.find('.select2-results').find('.add-new-dropdown-btn[data-select-id="' + selectId + '"]').closest('.select2-results__option--add-new').remove();
                            
                            // Show default message if it was hidden
                            const $noResultsMsg = $selectContainer.find('.select2-results__message');
                            if ($noResultsMsg.length) {
                                $noResultsMsg.show();
                            }
                        }
                    });
                }
                }, 100);
            });
        
        // Handle click on generic Add New buttons inside dropdowns
        $(document).on('mousedown touchstart', '.add-new-dropdown-btn', function(e) {
            const $button = $(this);
            const $searchTermSpan = $button.find('.dropdown-search-term');
            const selectId = $button.data('select-id');
            
            if ($searchTermSpan.length && selectId) {
                const searchText = $searchTermSpan.text().trim();
                if (searchText) {
                    if (!window.lastSearchTerm) {
                        window.lastSearchTerm = {};
                    }
                    window.lastSearchTerm[selectId] = searchText;
                    
                    window.activeSelectSearch = {
                        selectId: selectId,
                        searchTerm: searchText,
                        hasNoResults: true
                    };
                }
            }
        });
        
        // Load stats when part number is selected (React-style stats)
        $(document).on('change', '#part_number_id', function() {
            const partNumberId = $(this).val();
            const $statsContainer = $('#partNumberStats');
            
            if (partNumberId && partNumberId !== '') {
                // Fetch stats
                $.ajax({
                    url: '{{ route("items.count.by.part.number", ":id") }}'.replace(':id', partNumberId),
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.details && response.details.length > 0) {
                            let statsHtml = '';
                            response.details.slice(0, 2).forEach(function(detail) {
                                statsHtml += `<span class="text-[8px] font-black text-teal-600 uppercase bg-teal-50 px-1.5 py-0.5 rounded border border-teal-100 me-1">${detail}</span>`;
                            });
                            $statsContainer.html(statsHtml).css('display', 'flex !important').show();
                        } else {
                            $statsContainer.hide();
                        }
                    },
                    error: function() {
                        $statsContainer.hide();
                    }
                });
            } else {
                $statsContainer.hide();
            }
        });
        
        // Hide "Add New Part Number" button when dropdown closes
        $(document).on('select2:close', '#part_number_id', function() {
            // Remove button from inside Select2 dropdown
            const $partNumberContainer = $('#part_number_id').next('.select2-container');
            if ($partNumberContainer.length) {
                $partNumberContainer.find('.select2-results').find('.add-new-part-number-btn').closest('.select2-results__option--add-new').remove();
                
                // Show default message if it was hidden
                const $noResultsMsg = $partNumberContainer.find('.select2-results__message');
                if ($noResultsMsg.length) {
                    $noResultsMsg.show();
                }
            }
        });
        
        // =========================
        // INTERCEPT PLUS BUTTON CLICK EARLY
        // Capture search term at the exact moment of click
        // Also handle Add New button inside Select2 dropdown
        // =========================
        $(document).on('mousedown touchstart', '.open-universal-modal, .add-btn, .add-new-part-number-btn, .add-new-dropdown-btn', function(e) {
            // Special handling for Add New button inside Select2 dropdown
            const $button = $(this);
            
            // Handle Part Number button
            if ($button.hasClass('add-new-part-number-btn')) {
                // Close Select2 dropdown immediately
                $('#part_number_id').select2('close');
                
                const $searchTermSpan = $button.find('.part-number-search-term');
                if ($searchTermSpan.length) {
                    const searchText = $searchTermSpan.text().trim();
                    if (searchText) {
                        const selectId = 'part_number_id';
                        if (!window.lastSearchTerm) {
                            window.lastSearchTerm = {};
                        }
                        window.lastSearchTerm[selectId] = searchText;
                        window.activeSelectSearch = {
                            selectId: selectId,
                            searchTerm: searchText,
                            hasNoResults: true
                        };
                        
                        // Set subtitle to "PART NUMBER:" for Part Number section
                        window.partNumberSubtitle = 'PART NUMBER:';
                    }
                }
            }
            
            // Handle generic dropdown buttons - close Select2 dropdown
            if ($button.hasClass('add-new-dropdown-btn')) {
                const selectId = $button.data('select-id');
                if (selectId && selectId !== 'unit_parts') {
                    $('#' + selectId).select2('close');
                }
            }
            
            // Handle generic dropdown buttons (Product Name, Category, Company, etc.)
            if ($button.hasClass('add-new-dropdown-btn')) {
                const $searchTermSpan = $button.find('.dropdown-search-term');
                const selectId = $button.data('select-id');
                
                // Special handling for unit_parts - open Unit modal instead of universal modal
                if (selectId === 'unit_parts') {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const searchText = $searchTermSpan.length ? $searchTermSpan.text().trim() : '';
                    
                    // Close Select2 dropdown
                    $('#unit_parts').select2('close');
                    
                    // Open Unit modal
                    $('#Unit-add-modal').modal('show');
                    
                    // Pre-fill name field if search text exists
                    if (searchText) {
                        setTimeout(function() {
                            $('#Unit-form [name="name"]').val(searchText).focus();
                            $('#Unit-form [name="short_name"]').data('manually-edited', false);
                            // Trigger input to auto-generate short name
                            $('#Unit-form [name="name"]').trigger('input');
                        }, 300);
                    }
                    
                    return false;
                }
                
                if ($searchTermSpan.length && selectId) {
                    const searchText = $searchTermSpan.text().trim();
                    if (searchText) {
                        if (!window.lastSearchTerm) {
                            window.lastSearchTerm = {};
                        }
                        window.lastSearchTerm[selectId] = searchText;
                        window.activeSelectSearch = {
                            selectId: selectId,
                            searchTerm: searchText,
                            hasNoResults: true
                        };
                        
                        // Set subtitle for Product Name
                        if (selectId === 'product_name_item') {
                            window.productNameSubtitle = 'PRODUCT NAME:';
                        }
                    }
                }
            }
            // Capture search term BEFORE the click event fires
            const $openSelect2 = $('.select2-container--open');
            if ($openSelect2.length) {
                // Check if "No results found" is visible
                const $noResultsMsg = $openSelect2.find('.select2-results__message');
                const hasNoResults = $noResultsMsg.length && $noResultsMsg.is(':visible');
                
                const $searchInput = $openSelect2.find('.select2-search__field');
                if ($searchInput.length && $searchInput.val()) {
                    const searchVal = $searchInput.val().trim();
                    
                    // Find the select - try multiple methods
                    let $select = $openSelect2.prev('select.searchable-select');
                    
                    // If not found, try to find by checking which select has this container
                    if (!$select.length) {
                        $('.searchable-select').each(function() {
                            const $s = $(this);
                            const $container = $s.next('.select2-container');
                            if ($container.length && $container.is($openSelect2)) {
                                $select = $s;
                                return false; // break
                            }
                        });
                    }
                    
                    // If still not found, try to find select near the button
                    if (!$select.length) {
                        const $button = $(this);
                        $select = $button.closest('.input-group').find('select.searchable-select').first();
                    }
                    
                    if ($select.length) {
                        const selectId = $select.attr('id') || $select.attr('name') || 'default';
                        activeSelectSearch = {
                            select: $select,
                            searchTerm: searchVal,
                            selectId: selectId
                        };
                        lastSearchTerm[selectId] = searchVal;
                        
                        // If "No results found" is visible, this is high priority
                        if (hasNoResults) {
                            // Store with higher priority flag
                            activeSelectSearch.hasNoResults = true;
                        }
                    }
                }
            }
        });
        
        // =========================
        // OPEN ADD / EDIT MODAL
        // Universal support for both .open-universal-modal and .add-btn
        // =========================
        $(document).on('click', '.open-universal-modal, .add-btn', function(e) {
            const $button = $(this);
            const selectId = $button.data('select-id');
            
            // Special handling for unit_parts - open Unit modal instead of universal modal
            if (selectId === 'unit_parts' && $button.hasClass('add-new-dropdown-btn')) {
                e.preventDefault();
                e.stopPropagation();
                
                const $searchTermSpan = $button.find('.dropdown-search-term');
                const searchText = $searchTermSpan.length ? $searchTermSpan.text().trim() : '';
                
                // Close Select2 dropdown
                $('#unit_parts').select2('close');
                
                // Open Unit modal
                $('#Unit-add-modal').modal('show');
                
                // Pre-fill name field if search text exists
                if (searchText) {
                    setTimeout(function() {
                        $('#Unit-form [name="name"]').val(searchText).focus();
                        $('#Unit-form [name="short_name"]').data('manually-edited', false);
                        // Trigger input to auto-generate short name
                        $('#Unit-form [name="name"]').trigger('input');
                    }, 300);
                }
                
                return false;
            }
            
            const mode = $button.data('mode'); // add | edit
            const title = $button.data('title');
            const hasImage = $button.data('has-image') == 1;
            currentTargetSelect = $button.data('target-select');
            
            // Special handling for Add New button inside Select2 dropdown
            // Capture search term from button text immediately on click
            if ($button.hasClass('add-new-part-number-btn')) {
                const $button = $(this);
                const $searchTermSpan = $button.find('.part-number-search-term');
                if ($searchTermSpan.length) {
                    const searchText = $searchTermSpan.text().trim();
                    if (searchText) {
                        // Store search term for later use in the modal
                        if (typeof lastSearchTerm === 'undefined') {
                            window.lastSearchTerm = {};
                        }
                        const selectId = 'part_number_id';
                        lastSearchTerm[selectId] = searchText;
                        
                        // Also store in activeSelectSearch
                        if (typeof activeSelectSearch === 'undefined') {
                            window.activeSelectSearch = {};
                        }
                        activeSelectSearch.selectId = selectId;
                        activeSelectSearch.searchTerm = searchText;
                        activeSelectSearch.hasNoResults = true;
                        
                        // Set subtitle to "PART NUMBER:" for Part Number section
                        window.partNumberSubtitle = 'PART NUMBER:';
                    }
                }
            }
            
            // Handle Product Name button
            if ($button.hasClass('add-new-dropdown-btn')) {
                const selectId = $button.data('select-id');
                if (selectId === 'product_name_item') {
                    window.productNameSubtitle = 'PRODUCT NAME:';
                }
            }
            
            // =========================
            // CAPTURE SELECTED TYPE FROM ALPINE.JS
            // =========================
            let selectedType = '';
            try {
                // Try to get selectedType from Alpine.js component
                const alpineComponent = Alpine.$data(document.querySelector('[x-data*="productForm"]'));
                if (alpineComponent && alpineComponent.selectedType) {
                    selectedType = alpineComponent.selectedType;
                } else {
                    // Fallback: try to get from hidden input
                    const typeInput = document.querySelector('input[name="type"]');
                    if (typeInput) {
                        selectedType = typeInput.value;
                    }
                }
            } catch (e) {
                // Fallback: try to get from hidden input
                const typeInput = document.querySelector('input[name="type"]');
                if (typeInput) {
                    selectedType = typeInput.value;
                }
            }
            
            // Set type in universal form and show type selection
            if (mode === 'add') {
                $('#universal-type-selection').show();
                // Uncheck all checkboxes first
                $('.universal-type-checkbox').prop('checked', false).closest('label').css({
                    'background': 'white',
                    'border-color': '#e9ecef'
                });
                
                // Check the selected type checkbox
                if (selectedType) {
                    $(`.universal-type-checkbox[value="${selectedType}"]`).prop('checked', true);
                    $(`.universal-type-checkbox[value="${selectedType}"]`).closest('label').css({
                        'background': '#fff4f0',
                        'border-color': '#ff6b35'
                    });
                    $('#universal-type').val(selectedType);
                } else {
                    $('#universal-type').val('');
                }
            } else {
                // Show type selection in edit mode as well
                $('#universal-type-selection').show();
                // Don't clear the type value in edit mode - it will be set from fetched data
            }
            
            // =========================
            // CAPTURE SEARCH TERM FIRST (before form reset)
            // NEW SIMPLE APPROACH
            // =========================
            let searchTerm = '';
            if (mode === 'add') {
                // Find the associated select element
                let $select = null;
                if (currentTargetSelect) {
                    $select = $(currentTargetSelect);
                } else {
                    // Fallback: find select near the button
                    const $button = $(this);
                    $select = $button.closest('.input-group').find('select.searchable-select').first();
                }
                
                if ($select && $select.length) {
                    const selectId = $select.attr('id') || $select.attr('name') || 'default';
                    
                    // Get currently open Select2 dropdown first
                    const $openSelect2 = $('.select2-container--open');
                    
                    // For part number: Get from "Add New Part Number" button text inside dropdown if visible
                    if (selectId === 'part_number_id' && $openSelect2.length) {
                        const $addNewBtnInDropdown = $openSelect2.find('.add-new-part-number-btn');
                        if ($addNewBtnInDropdown.length) {
                            const $searchTerm = $addNewBtnInDropdown.find('.part-number-search-term');
                            if ($searchTerm.length) {
                                const btnText = $searchTerm.text().trim();
                            if (btnText) {
                                searchTerm = btnText;
                            }
                        }
                        }
                    }
                    
                    // Fallback: Get from currently open Select2 dropdown search input
                    if (!searchTerm && $openSelect2.length) {
                            const $searchInput = $openSelect2.find('.select2-search__field');
                            if ($searchInput.length && $searchInput.val()) {
                                searchTerm = $searchInput.val().trim();
                        }
                    }
                    
                    // For other dropdowns: Get from generic "Add New" button text inside dropdown if visible
                    if (!searchTerm && $openSelect2.length) {
                        const $addNewBtnInDropdown = $openSelect2.find('.add-new-dropdown-btn[data-select-id="' + selectId + '"]');
                        if ($addNewBtnInDropdown.length) {
                            const $searchTerm = $addNewBtnInDropdown.find('.dropdown-search-term');
                            if ($searchTerm.length) {
                                const btnText = $searchTerm.text().trim();
                                if (btnText) {
                                    searchTerm = btnText;
                                }
                            }
                        }
                    }
                    
                    // Another fallback: Get from button's own text (if clicked button is inside dropdown)
                    if (!searchTerm) {
                        const $clickedButton = $(this);
                        if ($clickedButton.hasClass('add-new-part-number-btn')) {
                            const $searchTerm = $clickedButton.find('.part-number-search-term');
                            if ($searchTerm.length) {
                                const btnText = $searchTerm.text().trim();
                                if (btnText) {
                                    searchTerm = btnText;
                                }
                            }
                        } else if ($clickedButton.hasClass('add-new-dropdown-btn')) {
                            const $searchTerm = $clickedButton.find('.dropdown-search-term');
                            if ($searchTerm.length) {
                                const btnText = $searchTerm.text().trim();
                                if (btnText) {
                                    searchTerm = btnText;
                                }
                            }
                        }
                    }
                }
            }
            
            // Reset form
            $('#universal-modal-title').text(mode === 'add' ? 'ADD NEW ENTRY' : title);
            // Set subtitle based on section
            let subtitle = 'SMART ASSET REGISTRY';
            if (mode === 'add' && window.partNumberSubtitle) {
                subtitle = window.partNumberSubtitle;
                // Clear the flag after using it
                window.partNumberSubtitle = null;
            } else if (mode === 'add' && window.productNameSubtitle) {
                subtitle = window.productNameSubtitle;
                // Clear the flag after using it
                window.productNameSubtitle = null;
            } else if (mode !== 'add') {
                subtitle = 'Update the details below';
            }
            $('#universal-modal-subtitle').text(subtitle);
            $('#universal-name').val('').removeClass('is-invalid');
            $('#universal-name-error').text('');
            $('#universal-image').val('');
            $('#universal-image-preview').hide().attr('src', '');
            $('#universal-image-placeholder').show();
            currentEditId = null;
            // Image field toggle
            if (hasImage) {
                $('#image-field').removeClass('d-none').show();
            } else {
                $('#image-field').addClass('d-none').hide();
            }
            
            // Handle type checkboxes change
            $('.universal-type-checkbox').off('change').on('change', function() {
                const $label = $(this).closest('label');
                if ($(this).is(':checked')) {
                    $label.css({
                        'background': '#fff4f0',
                        'border-color': '#ff6b35'
                    });
                    // Set the first checked type as the main type
                    const checkedTypes = $('.universal-type-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();
                    if (checkedTypes.length > 0) {
                        $('#universal-type').val(checkedTypes[0]); // Use first checked type
                    }
                } else {
                    $label.css({
                        'background': 'white',
                        'border-color': '#e9ecef'
                    });
                    // Update type if no checkboxes are checked
                    const checkedTypes = $('.universal-type-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();
                    if (checkedTypes.length === 0) {
                        $('#universal-type').val('');
                    } else {
                        $('#universal-type').val(checkedTypes[0]);
                    }
                }
            });
            
            // =========================
            // ADD MODE
            // =========================
            if (mode === 'add') {
                $('#universal-form')
                    .attr('action', $(this).data('route'))
                    .attr('method', 'POST');
                $('#universal-delete-btn').addClass('d-none');
                $('#universal-save-btn').html('<i class="ti ti-check me-2"></i><span>SAVE ENTRY</span>');
                
                // Pre-fill the modal input with the captured search term
                // Try multiple sources for search term
                let finalSearchTerm = searchTerm;
                
                // If search term not found, try to get from button's text (for Add New button inside dropdown)
                if (!finalSearchTerm) {
                    const $clickedButton = $(this);
                    if ($clickedButton.hasClass('add-new-part-number-btn')) {
                        const $searchTermSpan = $clickedButton.find('.part-number-search-term');
                        if ($searchTermSpan.length) {
                            finalSearchTerm = $searchTermSpan.text().trim();
                        }
                    } else if ($clickedButton.hasClass('add-new-dropdown-btn')) {
                        const $searchTermSpan = $clickedButton.find('.dropdown-search-term');
                        if ($searchTermSpan.length) {
                            finalSearchTerm = $searchTermSpan.text().trim();
                        }
                    }
                }
                
                // Check lastSearchTerm storage
                if (!finalSearchTerm) {
                    const selectId = $select ? ($select.attr('id') || $select.attr('name')) : '';
                    if (selectId && typeof lastSearchTerm !== 'undefined' && lastSearchTerm[selectId]) {
                        finalSearchTerm = lastSearchTerm[selectId];
                        delete lastSearchTerm[selectId];
                    }
                }
                
                // Check activeSelectSearch
                if (!finalSearchTerm && typeof activeSelectSearch !== 'undefined' && activeSelectSearch.searchTerm) {
                    finalSearchTerm = activeSelectSearch.searchTerm;
                }
                
                // Set the search term in the name field
                if (finalSearchTerm) {
                    $('#universal-name').val(finalSearchTerm);
                }
                
                // Close Select2 dropdowns before opening modal
                if ($button.hasClass('add-new-part-number-btn')) {
                    $('#part_number_id').select2('close');
                } else if ($button.hasClass('add-new-dropdown-btn')) {
                    const selectId = $button.data('select-id');
                    if (selectId) {
                        $('#' + selectId).select2('close');
                    }
                }
                
                // Open modal and focus input
                $('#universal-add-modal').modal('show');
                
                // Multiple strategies to ensure focus works
                function focusInput() {
                    const $input = $('#universal-name');
                    if ($input.length) {
                        $input[0].focus();
                        // Select all text for easy editing if search term exists
                        if (finalSearchTerm) {
                            requestAnimationFrame(function() {
                                $input[0].select();
                                // Ensure cursor is at end for better UX
                                if ($input[0].setSelectionRange) {
                                    const len = $input.val().length;
                                    $input[0].setSelectionRange(len, len);
                                }
                            });
                        }
                    }
                }
                
                // Try multiple times with different delays to ensure focus works
                setTimeout(focusInput, 100);
                setTimeout(focusInput, 300);
                setTimeout(focusInput, 500);
                
                // Also focus when modal is fully shown
                $('#universal-add-modal').one('shown.bs.modal', function() {
                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            focusInput();
                        });
                    });
                });
            }
            // =========================
            // EDIT MODE
            // =========================
            if (mode === 'edit') {
                const $select = $(currentTargetSelect);
                const selectedId = $select.val();
                if (!selectedId) {
                    alert('Please select an item to edit');
                    return;
                }
                const fetchRoute = $(this).data('fetch-route').replace(':id', selectedId);
                const updateRoute = $(this).data('update-route').replace(':id', selectedId);
                deleteRoute = $(this).data('delete-route').replace(':id', selectedId);
                currentEditId = selectedId;
                $('#universal-form')
                    .attr('action', updateRoute)
                    .attr('method', 'POST');
                $('#universal-save-btn').text('Update');
                $('#universal-delete-btn').removeClass('d-none');
                // Fetch existing data
                $.get(fetchRoute, function(res) {
                    $('#universal-name').val(res.name);
                    
                    // Set type in hidden field for edit mode
                    // Special handling for Part Number - always set to "parts"
                    const isPartNumber = updateRoute && updateRoute.includes('part/number');
                    if (isPartNumber) {
                        $('#universal-type').val('parts');
                    } else if (res.type) {
                        $('#universal-type').val(res.type);
                    } else {
                        $('#universal-type').val('');
                    }
                    
                    // Image preview (edit mode)
                    if (hasImage && res.image) {
                        $('#image-field').removeClass('d-none').show();
                        $('#universal-image-preview')
                            .attr('src', '/' + res.image)
                            .show();
                        $('#universal-image-placeholder').hide();
                    } else {
                        $('#universal-image-preview').hide();
                        $('#universal-image-placeholder').show();
                    }
                    $('#universal-add-modal').modal('show');
                    setTimeout(function() {
                        $('#universal-name').focus();
                    }, 300);
                });
            }
        });
        // =========================
        // KEYBOARD HANDLERS FOR UNIVERSAL MODAL
        // =========================
        // Handle keyboard events in universal modal
        $(document).on('keydown', '#universal-add-modal', function(e) {
            // Only handle if universal modal is open and visible
            if ($('#universal-add-modal').hasClass('show') && $('#universal-add-modal').is(':visible')) {
                const $target = $(e.target);
                const isTextInput = $target.is('input[type="text"], input[type="number"], textarea');
                const isNameField = $target.attr('id') === 'universal-name';
                const isTypeCheckbox = $target.hasClass('universal-type-checkbox');
                
                // Handle Enter key - submit form
                if (e.key === 'Enter' || e.keyCode === 13) {
                    // If Enter is pressed in name field, outside input fields, or on type checkbox, submit form
                    if (isNameField || isTypeCheckbox || !isTextInput) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Trigger save button click
                        const $saveBtn = $('#universal-save-btn');
                        if ($saveBtn.length && $saveBtn.is(':visible') && !$saveBtn.prop('disabled')) {
                            $saveBtn.trigger('click');
                        }
                        return false;
                    }
                }
                
                // Handle Delete key - trigger delete button (only in edit mode)
                if (e.key === 'Delete' || e.keyCode === 46) {
                    // Don't trigger if user is typing in an input field
                    if (!isTextInput) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Trigger delete button click (only if visible - means edit mode)
                        const $deleteBtn = $('#universal-delete-btn');
                        if ($deleteBtn.length && $deleteBtn.is(':visible') && !$deleteBtn.hasClass('d-none')) {
                            $deleteBtn.trigger('click');
                        }
                        return false;
                    }
                }
                
                // Handle ESC key - close modal
                if (e.key === 'Escape' || e.keyCode === 27) {
                    // Don't trigger if user is typing in an input field
                    if (!isTextInput) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Trigger close button click
                        const $closeBtn = $('#universal-add-modal').find('.btn-close');
                        if ($closeBtn.length) {
                            $closeBtn.trigger('click');
                        } else {
                            // Fallback: close modal directly
                            $('#universal-add-modal').modal('hide');
                        }
                        return false;
                    }
                }
            }
        });
        
        // =========================
        // IMAGE LIVE PREVIEW
        // =========================
        $('#universal-image').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#universal-image-preview')
                        .attr('src', e.target.result)
                        .show();
                };
                reader.readAsDataURL(file);
            }
        });
        $('#universal-form').off('submit').on('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Check if modal is actually visible
            if (!$('#universal-add-modal').hasClass('show')) {
                return false;
            }
            
            // Check if form has action attribute (should be set when modal opens)
            const formAction = $(this).attr('action');
            if (!formAction || formAction === '' || formAction === '#') {
                console.error('Form action not set');
                return false;
            }
            
            const formData = new FormData(this);
            if (currentEditId) {
                formData.append('_method', 'PUT');
            }
            
            // Get the checked type from checkboxes (use first checked, or the hidden field value)
            const checkedType = $('.universal-type-checkbox:checked').first().val();
            if (checkedType) {
                formData.set('type', checkedType);
            } else if ($('#universal-type').val()) {
                formData.set('type', $('#universal-type').val());
            }
            
            $.ajax({
                url: formAction,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (!res || !res.id) {
                        console.error('Invalid response', res);
                        if (res && res.message) {
                            toastr.error(res.message);
                        }
                        return;
                    }
                    const option = new Option(res.name, res.id, true, true);
                    const $select = $(currentTargetSelect);
                    $select.find(`option[value="${res.id}"]`).remove();
                    $select.append(option).val(res.id).trigger('change');
                    $('#universal-add-modal').modal('hide');
                    $('#universal-form')[0].reset();
                    // 🔊 Play save sound when dropdown option is saved
                    if (typeof playSaveSound === 'function') {
                        playSaveSound();
                    }
                },
                error: function(xhr) {
                    console.error('AJAX error', xhr);
                    const response = xhr.responseJSON;
                    if (response && response.errors) {
                        if (response.errors.name) {
                            $('#universal-name').addClass('is-invalid');
                            $('#universal-name-error').text(response.errors.name[0]);
                        }
                        if (response.message) {
                            toastr.error(response.message);
                        }
                    } else if (response && response.message) {
                        toastr.error(response.message);
                    } else {
                        toastr.error('An error occurred. Please try again.');
                    }
                }
            });
        });
        $('#universal-delete-btn').on('click', function() {
            if (!confirm('Are you sure?')) return;
            $.ajax({
                url: deleteRoute,
                method: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('input[name=_token]').val()
                },
                success: function() {
                    // 🔊 Play delete sound ONE time
                    const audio = document.getElementById('deleteSound');
                    if (audio) {
                        audio.currentTime = 0; // reset if previously played
                        audio.play();
                    }
                    // Remove option from select
                    $(currentTargetSelect)
                        .find(`option[value="${currentEditId}"]`)
                        .remove()
                        .trigger('change');
                    // Close modal
                    $('#universal-add-modal').modal('hide');
                }
            });
        });
        // Dynamic Subcategory Load
        $(document).on('change', 'select.category-select', function() {
            const subSelect = $('#subcategory');
            const catId = $(this).val();
            subSelect.empty().append('<option value="">Select Subcategory</option>');
            if (catId) {
                $.getJSON(`{{ url('admin/categories') }}/${catId}/subcategories`, function(data) {
                    data.forEach(item => {
                        subSelect.append(
                            `<option value="${item.id}">${item.name}</option>`);
                    });
                });
            }
        });
        // Restore subcategory on validation error
        setTimeout(() => {
            if ($('#category').val()) {
                $('#category').trigger('change');
            }
            setTimeout(() => {
                const $select = $('#subcategory');
                const oldVal = $select.data('old-subcat');
                if (oldVal && $select.find(`option[value="${oldVal}"]`).length) {
                    $select.val(oldVal);
                }
            }, 500);
        }, 100);

        // =========================
        // Function to show items in SweetAlert modal
        function showPartNumberItemsModal(items, partNumberId) {
            const csrfToken = $('input[name="_token"]').val();
            let itemsHtml = '';
            
            if (items.length === 0) {
                itemsHtml = '<tr><td colspan="7" class="text-center">No items found</td></tr>';
            } else {
                items.forEach(function(item) {
                    const activeBadge = item.is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';

                    itemsHtml += `
                        <tr>
                            <td>
                                <img src="${item.image}" width="50" height="50"
                                    class="rounded item-image-modal" style="cursor:pointer;"
                                    data-src="${item.image}">
                            </td>
                            <td>${item.bar_code || '-'}</td>
                            <td>${item.product_name || '-'}</td>
                            <td><span class="badge bg-info">${item.type || '-'}</span></td>
                            <td>${item.category_name || '-'}</td>
                            <td>${item.company_name || '-'}</td>
                            <td>${activeBadge}</td>
                            <td>
                                <a href="${item.show_url}" class="btn btn-sm btn-info" target="_blank" title="View">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="${item.edit_url}" class="btn btn-sm btn-primary" target="_blank" title="Edit">
                                    <i data-feather="edit"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
            }

            const modalHtml = `
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th>Image</th>
                                <th>Bar Code</th>
                                <th>Product Name</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                </div>
            `;

            Swal.fire({
                title: `Items Using This Part Number (${items.length})`,
                html: modalHtml,
                width: '95%',
                customClass: {
                    popup: 'swal-wide'
                },
                showCloseButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Close',
                confirmButtonColor: '#3085d6',
                didOpen: () => {
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                    // Handle image click to show larger view
                    $('.item-image-modal').on('click', function() {
                        const imgSrc = $(this).data('src');
                        Swal.fire({
                            imageUrl: imgSrc,
                            imageWidth: '80%',
                            imageAlt: 'Item Image',
                            showCloseButton: true,
                            showConfirmButton: false
                        });
                    });
                }
            });
        }

        // =========================
        // ENSURE quality_id AND technology ARE SUBMITTED ON FORM SUBMIT
        // This ensures they are sent even if fields are hidden by Alpine.js
        // =========================
        // Function to reset form after successful save
        function resetFormAfterSave() {
            // Small delay to ensure success message is shown
            setTimeout(function() {
                // Clear all form fields
                $('#mainItemForm')[0].reset();
            
            // Clear Select2 values
            $('.searchable-select').val(null).trigger('change');
            
            // Clear vehicle checkboxes
            $('.vehicle-checkbox').prop('checked', false);
            $('#selectAllVehicles').prop('checked', false);
            
            // Clear year ranges in vehicle modal
            $('#yearRangesContainer').html('');
            $('#selectedYearRangesDisplay').html('<div class="text-muted text-center" style="font-size: 10px;">No ranges selected</div>');
            $('#selectedYearRangesDisplay').data('all-ranges', []);
            
            // Clear vehicle table filters
            $('.car-manufacturer-select').val(null).trigger('change');
            $('.car-model-select').val(null).trigger('change');
            $('.car-engine-select').val(null).trigger('change');
            $('.car-country-select').val(null).trigger('change');
            
            // Reset Alpine.js selected type
            if (window.Alpine && document.querySelector('[x-data*="productForm"]')) {
                try {
                    const alpineComponent = Alpine.$data(document.querySelector('[x-data*="productForm"]'));
                    if (alpineComponent && alpineComponent.selectType) {
                        alpineComponent.selectType('');
                    }
                } catch(e) {
                    localStorage.removeItem('selectedType');
                }
            }
            
            // Clear file inputs
            $('input[type="file"]').val('');
            
            // Clear any images preview
            $('.item-image-preview').remove();
            
            // Reset all hidden inputs
            $('input[type="hidden"]').not('[name="_token"]').not('[name="user_id"]').val('');
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        $('#mainItemForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Extract unit ID from composite value (unit_id_base_unit_id format)
            const unitSelectVal = $('#unit_parts').val();
            if (unitSelectVal && unitSelectVal.includes('_')) {
                const unitId = unitSelectVal.split('_')[0];
                // Remove existing unit hidden input
                $(this).find('input[type="hidden"][name="unit"]').remove();
                // Set the actual unit ID
                $('#unit_parts').val(unitId);
            }
            
            // Get quality_id from any visible quality field
            const qualityVal = $('#quality').val() || $('#quality_filters').val() || $('#quality_breakpad').val() || '';
            
            // Remove any existing hidden quality_id inputs to avoid duplicates
            $(this).find('input[type="hidden"][name="quality_id"]').remove();
            
            // Add hidden input if quality_id has a value
            if (qualityVal) {
                $(this).append('<input type="hidden" name="quality_id" value="' + qualityVal + '">');
            }
            
            // Get technology from any visible technology field
            const techVal = $('#technology_select').val() || $('#technology_oil_select').val() || '';
            
            // Remove any existing hidden technology inputs to avoid duplicates
            $(this).find('input[type="hidden"][name="technology"]').remove();
            
            // Add hidden input if technology has a value
            if (techVal) {
                $(this).append('<input type="hidden" name="technology" value="' + techVal + '">');
            }
            
            // Collect checked vehicle IDs from vehicle table
            const checkedVehicleIds = getCheckedVehicleIds();
            
            // Remove any existing hidden vehical_id inputs to avoid duplicates
            $(this).find('input[type="hidden"][name="vehical_id[]"], input[type="hidden"][name="vehical_id"]').remove();
            
            // Add hidden inputs for each checked vehicle ID
            if (checkedVehicleIds.length > 0) {
                checkedVehicleIds.forEach(function(vehicleId) {
                    $(this).append('<input type="hidden" name="vehical_id[]" value="' + vehicleId + '">');
                }.bind(this));
            }
            
            // Create FormData for AJAX submission
            const formData = new FormData(this);
            const submitBtn = $(this).find('button[type="submit"]');
            const originalBtnText = submitBtn.html();
            
            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="ti ti-loader spinner"></i> Saving...');
            
            // Submit via AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    
                    // Check if response is JSON or HTML (redirect response)
                    let isJson = false;
                    let jsonResponse = null;
                    
                    try {
                        // Try to parse as JSON if response is a string
                        if (typeof response === 'string') {
                            jsonResponse = JSON.parse(response);
                            isJson = true;
                        } else if (response && typeof response === 'object') {
                            jsonResponse = response;
                            isJson = true;
                        }
                    } catch(e) {
                        // Not JSON, likely HTML redirect response
                        isJson = false;
                    }
                    
                    // Show success message and play sound
                    if (isJson && jsonResponse) {
                        if (jsonResponse.success || jsonResponse.message) {
                            // Show success message with item count if available
                            let message = jsonResponse.message || 'Item saved successfully!';
                            if (jsonResponse.items_count && jsonResponse.items_count > 1) {
                                message = jsonResponse.items_count + ' items saved successfully!';
                            }
                            toastr.success(message);
                            
                            // 🔊 Play save sound
                            if (typeof playSaveSound === 'function') {
                                playSaveSound();
                            }
                            
                            // Handle redirect if provided (for Save & New)
                            if (jsonResponse.redirect) {
                                // Reset form first, then redirect after delay
                                resetFormAfterSave();
                                setTimeout(function() {
                                    window.location.href = jsonResponse.redirect;
                                }, 1500);
                            } else {
                                // Reset form after successful save (stay on same page - no redirect)
                                resetFormAfterSave();
                            }
                        }
                    } else {
                        // HTML response - likely a redirect page, show success anyway
                        toastr.success('Item saved successfully!');
                        // 🔊 Play save sound
                        if (typeof playSaveSound === 'function') {
                            playSaveSound();
                        }
                        
                        // Reset form
                        resetFormAfterSave();
                    }
                },
                error: function(xhr) {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    
                    // Handle validation errors
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessages = [];
                        
                        $.each(errors, function(field, messages) {
                            if (Array.isArray(messages)) {
                                errorMessages = errorMessages.concat(messages);
                            } else {
                                errorMessages.push(messages);
                            }
                            
                            // Highlight invalid fields
                            const $field = $('[name="' + field + '"]');
                            if ($field.length) {
                                $field.addClass('is-invalid');
                                const errorHtml = '<div class="invalid-feedback">' + (Array.isArray(messages) ? messages[0] : messages) + '</div>';
                                $field.closest('.input-group, .form-group, .col-md-4, .col-md-6').find('.invalid-feedback').remove();
                                $field.after(errorHtml);
                            }
                        });
                        
                        // Show first error message
                        if (errorMessages.length > 0) {
                            toastr.error(errorMessages[0]);
                        }
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error saving item. Please try again.');
                    }
                }
            });
        });
    });
</script>
<script>
    // Sabhi dropdowns ko upar ki taraf show karne ke liye - Aggressive Approach
    $(document).ready(function() {
        // Function to force dropdown above
        function forceDropdownAbove($select) {
            const $container = $select.next('.select2-container');
            const $dropdown = $container.find('.select2-dropdown');
            
            // Special aggressive handling for Part Number dropdown
            if ($select.attr('id') === 'part_number_id') {
                // Always force above class, even if dropdown not fully open yet
                $container.removeClass('select2-container--below').addClass('select2-container--above');
                
                if ($dropdown.length) {
                    // Get container width
                    const containerWidth = $container.outerWidth() || $select.outerWidth();
                    
                    // Force position above with !important via inline style
                    $dropdown.attr('style', 
                        'position: absolute !important; ' +
                        'bottom: 100% !important; ' +
                        'top: auto !important; ' +
                        'left: 0 !important; ' +
                        'right: auto !important; ' +
                        'width: ' + containerWidth + 'px !important; ' +
                        'margin-bottom: 5px !important; ' +
                        'margin-top: 0 !important; ' +
                        'transform: none !important; ' +
                        'border-top: 1px solid #aaa !important; ' +
                        'border-bottom: none !important; ' +
                        'border-radius: 4px 4px 0 0 !important; ' +
                        'box-shadow: 0 -4px 6px rgba(0,0,0,0.1) !important; ' +
                        'display: block !important;'
                    );
                }
                
                // Also force container positioning
                $container.css({
                    'position': 'relative',
                    'z-index': '9999'
                });
            } else if ($dropdown.length && $container.hasClass('select2-container--open')) {
                // For other dropdowns, normal handling
                $container.removeClass('select2-container--below').addClass('select2-container--above');
                
                // Get container width
                const containerWidth = $container.outerWidth();
                
                // Force position above with !important via inline style
                $dropdown.attr('style', 
                    'position: absolute !important; ' +
                    'bottom: 100% !important; ' +
                    'top: auto !important; ' +
                    'left: 0 !important; ' +
                    'right: auto !important; ' +
                    'width: ' + containerWidth + 'px !important; ' +
                    'margin-bottom: 5px !important; ' +
                    'margin-top: 0 !important; ' +
                    'transform: none !important; ' +
                    'border-top: 1px solid #aaa !important; ' +
                    'border-bottom: none !important; ' +
                    'border-radius: 4px 4px 0 0 !important; ' +
                    'box-shadow: 0 -4px 6px rgba(0,0,0,0.1) !important;'
                );
            }
        }
        
        // Jab bhi koi searchable-select dropdown open ho (input-group ke andar)
        $(document).on('select2:open', '.input-group .searchable-select', function(e) {
            const $select = $(this);
            
            // Special priority for Part Number dropdown
            if ($select.attr('id') === 'part_number_id') {
                // Force immediately with higher priority
            forceDropdownAbove($select);
            setTimeout(function() { forceDropdownAbove($select); }, 0);
                setTimeout(function() { forceDropdownAbove($select); }, 5);
                setTimeout(function() { forceDropdownAbove($select); }, 10);
                setTimeout(function() { forceDropdownAbove($select); }, 20);
                setTimeout(function() { forceDropdownAbove($select); }, 50);
                setTimeout(function() { forceDropdownAbove($select); }, 100);
                setTimeout(function() { forceDropdownAbove($select); }, 200);
                
                // Continuous monitoring with faster interval for Part Number
                const positionInterval = setInterval(function() {
                    const $container = $select.next('.select2-container');
                    if ($container.hasClass('select2-container--open')) {
                        // Force remove below class and add above class FIRST
                        $container.removeClass('select2-container--below').addClass('select2-container--above');
                        // Then force positioning
                        forceDropdownAbove($select);
                        
                        // Also directly manipulate dropdown if it exists
                        const $dropdown = $container.find('.select2-dropdown');
                        if ($dropdown.length) {
                            const containerWidth = $container.outerWidth() || $select.outerWidth();
                            $dropdown.css({
                                'position': 'absolute',
                                'bottom': '100%',
                                'top': 'auto',
                                'left': '0',
                                'right': 'auto',
                                'width': containerWidth + 'px',
                                'margin-bottom': '5px',
                                'margin-top': '0',
                                'transform': 'none',
                                'border-top': '1px solid #aaa',
                                'border-bottom': 'none',
                                'border-radius': '4px 4px 0 0',
                                'box-shadow': '0 -4px 6px rgba(0,0,0,0.1)',
                                'display': 'block'
                            });
                        }
                    } else {
                        clearInterval(positionInterval);
                    }
                }, 10);
                
                // Dropdown close hone par interval clear karo
                $(document).one('select2:close', $select, function() {
                    clearInterval(positionInterval);
                });
            } else {
                // For other dropdowns, use normal timing
                forceDropdownAbove($select);
            setTimeout(function() { forceDropdownAbove($select); }, 0);
            setTimeout(function() { forceDropdownAbove($select); }, 10);
            setTimeout(function() { forceDropdownAbove($select); }, 50);
            setTimeout(function() { forceDropdownAbove($select); }, 100);
            setTimeout(function() { forceDropdownAbove($select); }, 200);
            
            // Continuous monitoring - dropdown open rehne tak
            const positionInterval = setInterval(function() {
                const $container = $select.next('.select2-container');
                if ($container.hasClass('select2-container--open')) {
                    forceDropdownAbove($select);
                } else {
                    clearInterval(positionInterval);
                }
            }, 30);
            
            // Dropdown close hone par interval clear karo
            $(document).one('select2:close', $select, function() {
                clearInterval(positionInterval);
            });
            }
        });
        
        // Also handle when Select2 tries to reposition
        $(document).on('select2:selecting select2:opening', '.input-group .searchable-select', function() {
            const $select = $(this);
            
            // Special handling for Part Number dropdown
            if ($select.attr('id') === 'part_number_id') {
                // Force immediately before Select2 positions it
                const $container = $select.next('.select2-container');
                if ($container.length) {
                    $container.removeClass('select2-container--below').addClass('select2-container--above');
                }
            setTimeout(function() { forceDropdownAbove($select); }, 0);
                setTimeout(function() { forceDropdownAbove($select); }, 5);
                setTimeout(function() { forceDropdownAbove($select); }, 10);
            } else {
                setTimeout(function() { forceDropdownAbove($select); }, 0);
            }
        });
        
        // MutationObserver for Part Number dropdown - Watch for DOM changes and force positioning
        const partNumberSelect = document.getElementById('part_number_id');
        if (partNumberSelect) {
            const observer = new MutationObserver(function(mutations) {
                const $select = $('#part_number_id');
                const $container = $select.next('.select2-container');
                
                if ($container.hasClass('select2-container--open')) {
                    // Force remove below class and add above class
                    $container.removeClass('select2-container--below').addClass('select2-container--above');
                    
                    // Force positioning
                    forceDropdownAbove($select);
                    
                    // Also directly manipulate dropdown
                    const $dropdown = $container.find('.select2-dropdown');
                    if ($dropdown.length) {
                        const containerWidth = $container.outerWidth() || $select.outerWidth();
                        $dropdown.css({
                            'position': 'absolute',
                            'bottom': '100%',
                            'top': 'auto',
                            'left': '0',
                            'right': 'auto',
                            'width': containerWidth + 'px',
                            'margin-bottom': '5px',
                            'margin-top': '0',
                            'transform': 'none',
                            'border-top': '1px solid #aaa',
                            'border-bottom': 'none',
                            'border-radius': '4px 4px 0 0',
                            'box-shadow': '0 -4px 6px rgba(0,0,0,0.1)',
                            'display': 'block'
                        });
                    }
                }
            });
            
            // Observe the select element and its container
            $(document).ready(function() {
                setTimeout(function() {
                    const $select = $('#part_number_id');
                    const $container = $select.next('.select2-container');
                    if ($container.length) {
                        observer.observe($container[0], {
                            attributes: true,
                            attributeFilter: ['class', 'style'],
                            childList: true,
                            subtree: true
                        });
                    }
                }, 500);
            });
        }
    });
</script>
<style>
    .swal-wide {
        max-width: 1400px !important;
    }
</style>
@endpush
